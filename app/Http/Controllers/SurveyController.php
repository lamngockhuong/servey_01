<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Invite\InviteInterface;
use App\Repositories\Setting\SettingInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendMail;
use Carbon\Carbon;
use LRedis;
use Mail;
use DB;
use Validator;
use Exception;
use Session;
use App\Models\Survey;
use App\Http\Requests\UpdateSurveyRequest;
use Predis\Connection\ConnectionException;

class SurveyController extends Controller
{
    use DispatchesJobs;

    protected $surveyRepository;
    protected $questionRepository;
    protected $inviteRepository;
    protected $settingRepository;
    protected $userRepository;

    public function __construct(
        SurveyInterface $surveyRepository,
        QuestionInterface $questionRepository,
        InviteInterface $inviteRepository,
        SettingInterface $settingRepository,
        UserInterface $userRepository
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->questionRepository = $questionRepository;
        $this->inviteRepository = $inviteRepository;
        $this->settingRepository = $settingRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return view('user.pages.home');
    }

    public function checkCloseSurvey($inviteIds, $surveyIds)
    {
        $ids = array_merge(
            $inviteIds->lists('survey_id')->toArray(),
            $surveyIds->lists('id')->toArray()
        );

        return $this->settingRepository
            ->whereIn('survey_id', $ids)
            ->where([
                'key' => config('settings.key.limitAnswer'),
                'value' => 0,
            ])
            ->lists('survey_id')
            ->toArray();
    }

    public function listSurveyUser(Request $request)
    {
        $invites = $inviteIds = $this->inviteRepository
            ->where('recevier_id', auth()->id())
            ->orWhere('mail', auth()->user()->email);
        $surveys = $surveyIds = $this->surveyRepository
            ->where('user_id', auth()->id());
        $settings = $this->checkCloseSurvey($inviteIds, $surveyIds);
        $invites = $invites
            ->orderBy('id', 'desc')
            ->paginate(config('settings.paginate'));
        $surveys = $surveys
            ->where('status', config('survey.status.available'))
            ->whereNotIn('id', $settings)
            ->where(function ($query) {
                return $query->where('deadline', '>', Carbon::now()->toDateTimeString())->orWhereNull('deadline');
            })
            ->orderBy('id', 'desc')
            ->paginate(config('settings.paginate'));
        $this->surveyRepository->newQuery(new Survey());
        $surveyCloses = $this->surveyRepository
            ->where('user_id', auth()->id())
            ->where(function ($query) use ($settings) {
                $query->where('status', config('survey.status.block'))
                    ->orWhere('deadline', '<', Carbon::now()->toDateTimeString())
                    ->orWhereIn('id', $settings);
            })
            ->orderBy('id', 'desc')
            ->paginate(config('settings.paginate'));

        if ($request->ajax()) {
            try {
                $viewId = $request->get('viewId');
                $view = null;

                if ($viewId == 'profile-v') {
                    $view = view('user.pages.list-invited', compact('invites', 'settings'))->render();
                } elseif ($viewId == 'home-v') {
                    $view = view('user.pages.your_survey', compact('surveys', 'settings'))->render();
                } else {
                    $view = view('user.pages.list_survey_close', compact('surveyCloses', 'settings'))->render();
                }

                if (!$view) {
                    return response()->json([
                        'success' => false,
                        'messageFail' => trans('messages.paginate_fail'),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'view' => $view,
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'messageFail' => trans('messages.paginate_fail'),
                ]);
            }
        }

        return view('user.pages.list-survey', compact('surveys', 'invites', 'settings', 'surveyCloses'));
    }

    public function delete(Request $request)
    {
        if (!$request->ajax()) {
            return [
                'success' => false,
            ];
        }

        $surveyId = $request->get('idSurvey');
        $this->surveyRepository->delete($surveyId);

        try {
            $redis = LRedis::connection();
            $redis->publish('delete', json_encode([
                'success' => true,
                'surveyId' => $surveyId,
            ]));
        } catch (ConnectionException $e) {
        }

        return [
            'success' => true,
        ];
    }

    public function open($id, Request $request)
    {
        if ($request->ajax()) {
            try {
                $survey = $this->surveyRepository->find($id);
                $changeDeadline = $request->get('change_deadline') === 'true' ? true: false;

                if ($changeDeadline && !empty($request->get('deadline'))) {
                    $deadline = Carbon::parse(in_array(Session::get('locale'), config('settings.sameFormatDateTime'))
                        ? str_replace('-', '/', $request->get('deadline'))
                        : $request->get('deadline'))
                        ->toDateTimeString();
                    $this->surveyRepository->update($id, ['status' => config('survey.status.available'), 'deadline' => $deadline]);
                } else {
                    $this->surveyRepository->update($id, ['status' => config('survey.status.available')]);
                }

                $redis = LRedis::connection();
                $redis->publish('open', $id);
            } catch (ConnectionException $e) {
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => trans('home.error'),
                ];
            }

            return [
                'success' => true,
            ];
        }
    }

    public function close($id, Request $request)
    {
        if ($request->ajax()) {
            try {
                $survey = $this->surveyRepository->find($id);

                if ($survey->is_expired) {
                    throw new Exception();
                }

                $this->surveyRepository->update($id, ['status' => config('survey.status.block')]);
                $redis = LRedis::connection();
                $redis->publish('close', $id);
            } catch (ConnectionException $e) {
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => trans('home.error'),
                ];
            }

            return [
                'success' => true,
            ];
        }
    }

    public function duplicate($id, Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();

            try {
                $survey = $this->surveyRepository->find($id);
                $newSurvey = $this->surveyRepository->duplicateSurvey($survey);
                DB::commit();

                $redis = LRedis::connection();
                $redis->publish('duplicate', $id);
            } catch (ConnectionException $e) {
            } catch (Exception $e) {
                DB::rollback();

                return [
                    'success' => false,
                    'message' => trans('home.error'),
                ];
            }

            Session::flash('message', trans('messages.duplicate_successfully'));
            return [
                'success' => true,
                'url' => action('AnswerController@show', ['token' => $newSurvey->token_manage]),
            ];
        }
    }

    public function show($token)
    {
        $surveys = $this->surveyRepository->where('token', $token)->first();

        if (!$surveys) {
            return view('errors.404');
        }

        return view('user.pages.answer', compact('surveys'));
    }

    public function updateSurvey(UpdateSurveyRequest $request, $id)
    {
        try {
            $survey = $this->surveyRepository->find($id);

            if ($survey->status == config('survey.status.available')) {
                throw new Exception('Can not update the survey information when status is available');
            }

            $isSuccess = false;
            $data = $request->only([
                'title',
                'description',
            ]);

            $data['start_time'] = null;
            $data['deadline'] = null;

            if ($request->get('start_time')) {
                $data['start_time'] = Carbon::parse(in_array(Session::get('locale'), config('settings.sameFormatDateTime'))
                    ? str_replace('-', '/', $request->get('start_time'))
                    : $request->get('start_time'))
                    ->toDateTimeString();
            }

            if ($request->get('deadline')) {
                $data['deadline'] = Carbon::parse(in_array(Session::get('locale'), config('settings.sameFormatDateTime'))
                    ? str_replace('-', '/', $request->get('deadline'))
                    : $request->get('deadline'))
                    ->toDateTimeString();
            }

            DB::beginTransaction();
            if ($survey && !$this->surveyRepository->update($id, $data)) {
                throw new Exception("Error Processing Request", 1);
            }

            DB::commit();

            $redis = LRedis::connection();
            $redis->publish('update', json_encode([
                'success' => true,
                'surveyId' => $id,
            ]));
        } catch (ConnectionException $e) {
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->action('AnswerController@show', $survey->token_manage)
                ->with('message-fail', trans_choice('messages.object_updated_unsuccessfully', 1));
        }

        return redirect()->action('AnswerController@show', $survey->token_manage)
                ->with('message', trans_choice('messages.object_updated_successfully', 1));
    }

    public function updateSurveyContent(Request $request, $surveyId, $token)
    {
        DB::beginTransaction();
        try {
            $survey = null;

            if ($token) {
                $survey = $this->surveyRepository->where('token_manage', $token)->first();
            }

            if (!$survey) {
                return view('errors.404');
            }

            if ($survey->update == config('survey.maxEdit')) {
                return redirect()->action('AnswerController@show', $token)
                    ->with('message-fail', trans_choice('messages.object_updated_unsuccessfully', 1));
            }

            $inputs = $request->only([
                'txt-question',
                'checkboxRequired',
                'required-question',
                'image',
                'image-url',
                'video-url',
                'del-question',
                'del-answer',
                'del-question-image',
                'del-answer-image',
            ]);
            $inputs['image-url'] = $this->removeEmptyValue($inputs['image-url']);
            $inputs['video-url'] = $this->removeEmptyValue($inputs['video-url']);
            $validator = $this->makeValidator([
                'txt-question' => $inputs['txt-question'],
                'image' => $inputs['image'],
            ]);
            $validator = Validator::make($request->all(), $validator);

            if ($validator->fails()) {
                return redirect()->action('AnswerController@show', $token)
                    ->with('message-fail', trans_choice('messages.object_updated_unexicute', 1));
            }

            $results = $this->questionRepository->updateSurvey($inputs, $surveyId);

            if (!$results['isEdit']) {
                return redirect()->action('AnswerController@show', $token)
                    ->with('message-fail', trans_choice('messages.object_updated_unexicute', 1));
            }

            $this->surveyRepository->update($survey->id, [
                'update' => $survey->update + 1,
            ]);
            $mailInput = [
                'title' => $survey->title,
                'description' => $survey->description,
                'link' => action($survey->feature
                        ? 'AnswerController@answerPublic'
                        : 'AnswerController@answerPrivate', [
                            'token' => $survey->token,
                        ]),
                'name' => $survey->user_name,
                'email' => $results['emails'],
            ];
            $job = (new SendMail(collect($mailInput), 'reAnswer'))
                ->onConnection('database')
                ->onQueue('emails');
            $this->dispatch($job);

            DB::commit();

            $redis = LRedis::connection();
            $redis->publish('update', json_encode([
                'success' => true,
                'surveyId' => $surveyId,
            ]));

            return redirect()->action('AnswerController@show', $token)
                ->with('message', trans_choice('messages.object_updated_successfully', 1));
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->action('AnswerController@show', $token)
                ->with('message-fail', trans_choice('messages.object_updated_unsuccessfully', 1));
        }
    }

    private function makeValidator(array $inputs, $flage = false)
    {
        $images = $inputs['image'];
        $validator = [];

        foreach ($inputs['txt-question']['question'] as $questionIndex => $content) {
            $validator['txt-question.question.' . $questionIndex] = 'required|max:255';

            if ($images && array_key_exists('question', $images) && array_key_exists($questionIndex, $images['question'])) {
                $validator['image.quesion.' . $questionIndex] = 'image|mimes:jpg,jpeg,png,gif,bmp,svg|max:1000';
            }

            foreach ($inputs['txt-question']['answers'][$questionIndex] as $answerIndex => $answer) {
                $type = head(array_keys($answer));
                if (in_array($type, [
                    config('survey.type_radio'),
                    config('survey.type_checkbox'),
                ])) {
                    $validator['txt-question.answers.' . $questionIndex] = 'array';
                    $validator['txt-question.answers.' . $questionIndex . '.' . $answerIndex . '.' . $type] = 'required|max:255|distinct';
                }

                if ($images
                    && array_key_exists('answers', $images)
                    && array_key_exists($questionIndex, $images['answers'])
                    && array_key_exists($answerIndex, $images['answers'][$questionIndex])
                ) {
                    $validator['image.answers.' . $questionIndex . '.' . $answerIndex] = 'image|mimes:jpg,jpeg,png,gif,bmp,svg|max:1000';
                }
            }
        }

        if ($flage) {
            $validator['email'] = 'required|email|max:255';
            $validator['name'] = 'required|max:255';
            $validator['emails'] = 'max:255
                |regex:/^([a-zA-Z][a-zA-Z0-9_\.]{2,255}@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}[,]{0,1}[,]{0,1}[\s]*)+(?<!,)(?<!\s)$/';
            $validator['title'] = 'required|max:255';
            $realTime = Carbon::now()->addMinutes(30)->format(trans('temp.format_with_trans'));
            $validator['start_time'] = 'date_format:' . trans('temp.format_with_trans');
            $validator['deadline'] = 'date_format:' . trans('temp.format_with_trans') . '|after:start_time|after:' . $realTime;
            $validator['setting.' . config('settings.key.limitAnswer')] = 'numeric
                |digits_between:1,' . config('settings.max_limit');
            $validator['setting.' . config('settings.key.tailMail')] = 'max:255
                |regex:/^(@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}[,]{0,1}[,]{0,1}[\s]*)+(?<!,)(?<!\s)$/';
        }

        return $validator;
    }

    private function removeEmptyValue(array $array)
    {
        if (array_key_exists('question', $array)) {
            $array['question'] = array_filter($array['question']);
        }

        if (array_key_exists('answers', $array)) {
            foreach (array_keys($array['answers']) as $key) {
                $array['answers'][$key] = array_filter($array['answers'][$key]);
            }
        }

        return $array;
    }

    public function create(Request $request)
    {
        $value = $request->only([
            'title',
            'feature',
            'start_time',
            'next_reminder_time',
            'deadline',
            'description',
            'txt-question',
            'checkboxRequired',
            'email',
            'emails',
            'setting',
            'name',
            'image',
            'image-url',
            'video-url',
        ]);

        $validator = $this->makeValidator([
            'txt-question' => $value['txt-question'],
            'image' => $value['image'],
        ], true);
        $validator = Validator::make($request->all(), $validator);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('message-fail', trans_choice('messages.object_created_unsuccessfully', 1));
        }

        if (!strlen($value['title'])) {
            $value['title'] = config('survey.title_default');
        }

        $token = md5(uniqid(rand(), true));
        $tokenManage = md5(uniqid(rand(), true));
        DB::beginTransaction();
        try {
            $inputs = collect([
                'user_id' => (auth()->id()) ?: null,
                'mail' => (!auth()->id()) ? $value['email'] : null,
                'title' => $value['title'],
                'feature' => empty($value['feature']) ? config('settings.feature') : config('settings.not_feature'),
                'token' => $token,
                'token_manage' => $tokenManage,
                'status' => $value['deadline'],
                'start_time' => $value['start_time'],
                'next_reminder_time' => $value['next_reminder_time'],
                'deadline' => $value['deadline'],
                'description' => $value['description'],
                'user_name' => $value['name'],
            ]);
            $survey = $this->surveyRepository->createSurvey(
                $inputs,
                ($value['setting']) ?: [],
                $value['txt-question'],
                ($value['checkboxRequired']['question']) ?: [],
                ($value['image']) ?: [],
                $this->removeEmptyValue($value['image-url']),
                $this->removeEmptyValue($value['video-url']),
                Session::get('locale')
            );

            if ($survey) {
                $inputInfo = $request->only([
                    'name',
                    'email',
                    'title',
                    'description',
                ]);
                $inputInfo['link'] = action($inputs['feature']
                    ? 'AnswerController@answerPublic'
                    : 'AnswerController@answerPrivate', [
                        'token' => $token,
                    ]);
                $inputInfo['linkManage'] = action('AnswerController@show', [
                    'tokenManage' => $tokenManage,
                ]);
                $job = (new SendMail(collect($inputInfo), 'mailManage'))
                    ->onConnection('database')
                    ->onQueue('emails');
                $isSuccess = ($this->dispatch($job) && $this->inviteUser($request, $survey, config('settings.return.bool')));

                if (!$isSuccess) {
                    throw new Exception('Create Survey is Fail.');
                }
            }

            DB::commit();

            return redirect()->action('SurveyController@complete', $tokenManage);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->action('SurveyController@index')
                ->withInput()
                ->with('message-fail', trans_choice('messages.object_created_unsuccessfully', 1));
        }
    }

    public function complete($token)
    {
        if (!$token) {
            return view('errors.404');
        }

        $survey = $this->surveyRepository->where('token_manage', $token)->first();

        if (!$survey) {
            return view('errors.404');
        }

        $mail = auth()->check() ? auth()->user()->email : $survey->mail;

        return view('user.pages.complete', compact('survey', 'mail'));
    }

    public function inviteUser(Request $request, $surveyId, $type)
    {
        // type nếu là bool thì hàm inviteUser sẽ trả về true hoặc flase, view thì hàm inviteUser sẽ trả về action('SurveyController@listSurveyUser')
        $isSuccess = false;
        $data['email'] = $request->get(($type == config('settings.return.bool')) ? 'email' : 'emailUser') ?: auth()->user()->email;
        $data['emails'] = $request->get(($type == config('settings.return.bool')) ? 'emails' : 'emailsUser');
        $data['feature'] = $request->get('feature');

        if (empty($data['emails'])) {
            return true;
        }

        $data['emails'] = explode(',', $data['emails']);

        if ($data['emails'] && $surveyId) {
            $survey = $this->surveyRepository->find($surveyId);
            $data['name'] = $survey->user_name;
            $invite = $this->inviteRepository->invite(auth()->id(), $data['emails'], $surveyId);
            $data['feature'] = ($type == config('settings.return.bool')) ? $data['feature'] : $survey->feature;

            if ($invite) {
                $inputInfo = [
                    'name' => auth()->check() ? auth()->user()->name : $data['name'],
                    'email' => $data['emails'],
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'link' => action($survey->feature
                        ? 'AnswerController@answerPublic'
                        : 'AnswerController@answerPrivate', [
                            'token' => $survey->token,
                        ]),
                    'emailSender' => $data['email'],
                ];
                $job = (new SendMail(collect($inputInfo), 'mailInvite'))
                    ->onConnection('database')
                    ->onQueue('emails');
                $this->dispatch($job);
                $isSuccess = true;
                try {
                    $redis = LRedis::connection();
                    $redis->publish('invite', json_encode([
                        'success' => $isSuccess,
                        'emails' => replaceEmail($data['emails']),
                        'viewInvite' => view('user.component.invited-user', compact('survey'))->render(),
                    ]));
                } catch (ConnectionException $e) {
                }
            }
        }

        if (!auth()->check() && $type == config('settings.return.view')) {
            return $isSuccess
                ? redirect()->action('AnswerController@show', $survey->token_manage)
                    ->with('message', trans('survey.invite_success'))
                : redirect()->action('AnswerController@show', $survey->token_manage)
                    ->with('message-fail', trans('survey.invite_fail'));
        }

        return ($type == config('setttings.return.bool')) ? $isSuccess : ($isSuccess)
            ? redirect()->action('SurveyController@listSurveyUser')
                ->with('message', trans('survey.invite_success'))
            : redirect()->action('SurveyController@listSurveyUser')
                ->with('message-fail', trans('survey.invite_fail'));
    }

    public function getMailSuggestion(Request $request)
    {  
        if ($request->ajax()) {
                $data = $request->only('keyword', 'emails');
                $users = $this->userRepository->findEmail($data, Auth()->user()->id);
     
                return response()->json($users);
        } 
    }
}
