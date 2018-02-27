<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Result\ResultInterface;
use App\Repositories\Survey\SurveyInterface;
use App\Http\Controllers\Controller;
use App\Repositories\Invite\InviteInterface;
use App\Repositories\Setting\SettingInterface;
use App\Repositories\Question\QuestionInterface;
use App\Http\Requests\AnswerRequest;
use Carbon\Carbon;
use Exception;
use LRedis;
use DB;

class ResultController extends Controller
{
    protected $resultRepository;
    protected $surveyRepository;
    protected $inviteReposirory;
    protected $settingReposirory;
    protected $questionRepository;

    public function __construct(
        ResultInterface $resultRepository,
        SurveyInterface $surveyRepository,
        InviteInterface $inviteReposirory,
        SettingInterface $settingReposirory,
        QuestionInterface $questionRepository
    ) {
        $this->resultRepository = $resultRepository;
        $this->surveyRepository = $surveyRepository;
        $this->inviteReposirory = $inviteReposirory;
        $this->settingReposirory = $settingReposirory;
        $this->questionRepository = $questionRepository;
    }

    public function result($token, AnswerRequest $request)
    {
        $isSuccess = false;
        $isRunToBottom = true;
        $answers = $request->get('answer');
        $emailUser = $request->get('email-answer');
        $data = [];
        $message = '';
        $flag = false;
        $survey = $this->surveyRepository->where('token', $token)->first();
        $emailResults = $this->questionRepository
            ->getResultByQuestionIds($survey->id)
            ->lists('email')
            ->toArray();
        $settings = $this->settingReposirory
            ->where('survey_id', $survey->id)
            ->whereIn('key', array_only(config('settings.key'), [
                'limitAnswer',
                'tailMail',
                'requireOnce',
            ]))
            ->get();
        $requireOnce = $settings
            ->where('key', config('settings.key.requireOnce'))
            ->first()
            ->value;
        $listTailMail = $settings
            ->where('key', config('settings.key.tailMail'))
            ->first()
            ->value;
        $tailMailUser = substr($emailUser, strpos($emailUser, '@'));

        if ($listTailMail && !in_array($tailMailUser, explode(',', $listTailMail))) {
            $isRunToBottom = false;
            $message = trans('survey.validate.invalid_mail');
        }

        if ($requireOnce && in_array($emailUser, $emailResults)) {
            $isRunToBottom = false;
            $message = trans('survey.validate.run_out');
            $flag = true;
        }

        if ($isRunToBottom) {
            if (!$answers) {
                return redirect()
                    ->action(($survey->feature) ? 'AnswerController@answerPublic' : 'AnswerController@answerPrivate', $survey->token)
                    ->with('message-fail', trans('result.not_answer'));
            }

            $invite = $this->inviteReposirory
                ->where([
                    'recevier_id' => auth()->id(),
                    'survey_id' => $survey->id,
                ])
                ->orWhere(function ($query) use ($survey) {
                    $query->where([
                        'survey_id' => $survey->id,
                        'mail' => auth()->check() ? auth()->user()->email : null,
                    ]);
                })
                ->first();

            if ($survey->feature
                || (!$survey->feature && auth()->id() && $invite)
                || auth()->id() == $survey->user_id
            ) {
                foreach ($answers as $answer) {
                    if (!is_array($answer)) {
                        $answer = [$answer => $answer];
                    }

                    foreach ($answer as $key => $value) {
                        //  Set default email and name if user not login or don't have setting require email, name or both.
                        if (!auth()->check() && !$request->get('name-answer') && !$emailUser) {
                            $setName = config('settings.name_unidentified');
                            $setEmail = config('settings.email_unidentified');
                        } else {
                            $setName = $request->get('name-answer') ?: (
                                auth()->check() ? auth()->user()->name : config('settings.name_unidentified')
                            );
                            $setEmail = $emailUser ?: (
                                auth()->check() ? auth()->user()->email: config('settings.email_unidentified')
                            );
                        }

                        if ($value = trim($value)) {
                            $data[] = [
                                'sender_id' => auth()->id(),
                                'recevier_id' => $survey->user_id,
                                'answer_id' => $key,
                                'content' => $value,
                                'name' => $setName,
                                'email' => $setEmail,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                        }
                    }
                }

                $isSuccess = true;
            }

            if (!$data) {
                return redirect()
                    ->action(($survey->feature) ? 'AnswerController@answerPublic' : 'AnswerController@answerPrivate', $survey->token)
                    ->with('message-fail', trans('result.answer_not_have'));
            }

            DB::beginTransaction();
            try {
                if (!empty($data)
                    && $this->resultRepository->multiCreate($data)
                ) {

                    $decreaseNumber = $settings
                        ->where('key', config('settings.key.limitAnswer'))
                        ->first();

                    if ($decreaseNumber && $decreaseNumber->value) {
                        $decreaseNumber->update(['value' => --$decreaseNumber->value]);
                    }

                    if ($invite && $invite->status) {
                        $isSuccess = $invite->update(['status' => config('survey.invite.old')]);
                    }
                }

                if (!$isSuccess) {
                    throw new Exception;
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
            }
        } else {
            return redirect()
                ->action(($survey->feature) ? 'AnswerController@answerPublic' : 'AnswerController@answerPrivate', $survey->token)
                ->with(!$flag ? 'message-validate-tailmail' : 'message-fail', $message);
        }

        if (!$isSuccess) {
            return redirect()
                ->action($survey->feature
                    ? 'AnswerController@answerPublic'
                    : 'AnswerController@answerPrivate', $survey->token)
                ->with('message-fail', trans_choice('messages.object_created_unsuccessfully', 3));
        }

        $getCharts = $this->surveyRepository->viewChart($survey->token);
        $listUserAnswer = $this->surveyRepository->getUserAnswer($token);
        $status = $getCharts['status'];
        $charts = $getCharts['charts'];
        // TODO:
        // $redis = LRedis::connection();
        // $redis->publish('answer', json_encode([
        //     'success' => true,
        //     'surveyId' => $survey->id,
        //     'viewChart' => view('user.result.chart', compact('status', 'charts'))->render(),
        //     'viewDetailResult' => view('user.result.detail-result', compact('survey'))->render(),
        //     'viewUserAnswer' => view('user.result.users-answer', compact('listUserAnswer', 'survey'))->render(),
        // ]));

        return redirect()->action('ResultController@show', [
            'name' => auth()->check() ? auth()->user()->name : null,
            'survey' => $survey->title,
        ]);
    }

    public function show($survey, $name = null)
    {
        return view('user.pages.answer_complete', compact('name', 'survey'));
    }
}
