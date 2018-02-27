<?php

namespace App\Repositories\Survey;

use DB;
use Exception;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Like\LikeInterface;
use App\Repositories\Invite\InviteInterface;
use App\Repositories\Setting\SettingInterface;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use App\Models\Survey;

class SurveyRepository extends BaseRepository implements SurveyInterface
{
    protected $likeRepository;
    protected $questionRepository;
    protected $inviteRepository;
    protected $settingRepository;

    public function __construct(
        Survey $survey,
        QuestionInterface $question,
        LikeInterface $like,
        InviteInterface $invite,
        SettingInterface $setting
    ) {
        parent::__construct($survey);
        $this->likeRepository = $like;
        $this->inviteRepository = $invite;
        $this->questionRepository = $question;
        $this->settingRepository = $setting;
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            $ids = is_array($ids) ? $ids : [$ids];
            $this->inviteRepository->deleteBySurveyId($ids);
            $this->likeRepository->deleteBySurveyId($ids);
            $this->questionRepository->deleteBySurveyId($ids);
            parent::delete($ids);
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function getResutlSurvey($token)
    {
        $survey = $this->where('token', $token)->first();

        if (!$survey) {
            return view('errors.503');
        }

        $datasInput = $this->inviteRepository->getResult($survey->id);
        $questions = $datasInput['questions'];
        $temp = [];
        $results = [];

        if (empty($datasInput['results']->toArray())) {
            return $results = false;
        }

        foreach ($questions as $key => $question) {
            $answers = $datasInput['answers']->where('question_id', $question->id);
            $idTemp = null;
            $total = 0;

            foreach ($answers as $answer) {
                $total = $datasInput['results']
                    ->whereIn('answer_id', $answers->pluck('id')->toArray())
                    ->pluck('id')
                    ->toArray();
                $answerResult = $datasInput['results']
                    ->whereIn('answer_id', $answer->id)
                    ->pluck('id')
                    ->toArray();

                if (count($total)) {
                    $temp[] = [
                        'answerId' => $answer->id,
                        'content' => (in_array($answer->type, [
                                config('survey.type_time'),
                                config('survey.type_text'),
                                config('survey.type_date'),
                            ]))
                            ? $datasInput['results']->whereIn('answer_id', $answer->id)
                            : $answer->trim_content,
                        'percent' => (count($total)) ? (double)(count($answerResult) * 100) / (count($total)) : 0,
                    ];
                } else {
                    $idTemp = $answer->id;
                }
            }

            if (!$total) {
                $temp[] = [
                    'answerId' => $idTemp,
                    'content' => collect([0 => ['content' => trans('result.not_answer')]]),
                    'percent' => 0,
                ];
            }

            $results[] = [
                'question' => $question,
                'answers' => $temp,
            ];
            $temp = [];
        }

        return $results;
    }

    public function createSurvey(
        $inputs,
        array $settings,
        array $arrayQuestionWithAnswer,
        array $questionsRequired,
        array $images,
        array $imageUrl,
        array $videoUrl,
        $locale
    ) {
        $surveyInputs = $inputs->only([
            'user_id',
            'mail',
            'title',
            'feature',
            'token',
            'token_manage',
            'status',
            'deadline',
            'description',
            'user_name',
        ]);

        // if the lang is english will be format from M-D-Y to M/D/Y
        if ($inputs['deadline']) {
            $inputs['deadline'] = $surveyInputs['deadline'] = Carbon::parse(in_array($locale, config('settings.sameFormatDateTime'))
                ? str_replace('-', '/', $surveyInputs['deadline'])
                : $surveyInputs['deadline'])
                ->toDateTimeString();
        }

        $surveyInputs['status'] = config('survey.status.avaiable');
        $surveyInputs['deadline'] = ($inputs['deadline']) ?: null;
        $surveyInputs['description'] = ($inputs['description']) ?: null;
        $surveyInputs['created_at'] = $surveyInputs['updated_at'] = Carbon::now();
        $surveyId = parent::create($surveyInputs->toArray());

        if (!$surveyId) {
            return false;
        }

        //(1,5) That is the settings quantity for a survey
        foreach (range(1, 4) as $key) {
            if (!array_has($settings, $key)) {
                $settings[$key] = null;
            }
        }

        if (!$settings[config('settings.key.tailMail')]) {
            $settings[config('settings.key.tailMail')] = null;
        }

        $this->settingRepository->createMultiSetting($settings, $surveyId);
        $txtQuestion = $arrayQuestionWithAnswer;
        $questions = $txtQuestion['question'];
        $answers = $txtQuestion['answers'];
        $this->questionRepository
            ->createMultiQuestion(
                $surveyId,
                $questions,
                $answers,
                $images,
                $imageUrl,
                $videoUrl,
                $questionsRequired
            );

        return $surveyId;
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

    public function listsSurvey($userId, $email = null)
    {
        $invites = $inviteIds = $this->inviteRepository
            ->where('recevier_id', $userId)
            ->orWhere('mail', $email);
        $surveys = $surveyIds = $this->where('user_id', $userId)->orWhere('mail', $email);
        $settings = $this->checkCloseSurvey($inviteIds, $surveyIds);
        $invites = $invites
            ->orderBy('id', 'desc')
            ->paginate(config('settings.paginate'));
        $surveys = $surveys
            ->orderBy('id', 'desc')
            ->paginate(config('settings.paginate'));

        return compact('surveys', 'surveys', 'settings');
    }

    public function checkSurveyCanAnswer(array $inputs)
    {
        $date = ($inputs['deadline']) ? Carbon::parse($inputs['deadline'])->gt(Carbon::now()) : true;
        $invite = true;
        $email = $inputs['email'];
        $surveyId = $inputs['surveyId'];

        if (!$date || !$inputs['status']) {
            return false;
        } elseif (!$inputs['type']) {
            $invite = $this->inviteRepository
                ->where('recevier_id', $inputs['userId'])
                ->where('survey_id', $inputs['surveyId'])
                ->orWhere(function ($query) use ($email, $surveyId) {
                    $query->where('mail', $email)->where('survey_id', $surveyId);
                })
                ->exists();
        }

        return $invite;
    }

    public function getSettings($surveyId)
    {
        if (!$surveyId) {
            return [];
        }

        return $this->settingRepository
            ->where('survey_id', $surveyId)
            ->lists('value', 'key')
            ->toArray();
    }

    public function getHistory($userId, $surveyId, array $options)
    {
        if (!$userId && $options['type'] == 'history' || !$surveyId) {
            return [
                'history' => [],
                'results' => [],
            ];
        }

        if ($options['type'] == 'history') {
            $results = $this->questionRepository
                ->getResultByQuestionIds($surveyId)
                ->where('sender_id', $userId)
                ->get();
        } else {
            $email = $options['email'];
            $name = $options['name'];
            $results = $this->questionRepository
                ->getResultByQuestionIds($surveyId)
                ->where(function($query) use ($userId, $email) {
                    $query->where('sender_id', $userId)
                        ->orWhere('email', $email);
                })
                ->get()
                ->toArray();

            if (empty($email) && $name) {
                $results = $this->questionRepository
                    ->getResultByQuestionIds($surveyId)
                    ->where(function($query) use ($userId, $name) {
                        $query->where('sender_id', $userId)
                            ->orWhere('name', $name);
                    })
                    ->get()
                    ->toArray();
            }

            $collection = collect($results);

            return $collection->groupBy('created_at')->toArray();
        }

        if (!$results) {
            return [];
        }

        $history = [];
        $maxCreate = $results->max('created_at');

        foreach ($results as $key => $value) {
            if ($options['type'] == 'history' && $value->created_at == $maxCreate) {
                $history[$value->answer_id] = $value->content;
            }
        }

        return [
            'history' => $history,
            'results' => $results,
        ];
    }

    public function getUserAnswer($token)
    {
        $survey = $this->where('token', $token)->orWhere('token_manage', $token)->first();

        if (!$survey) {
            return false;
        }

        $results = $this->questionRepository
            ->getResultByQuestionIds($survey->id)
            ->with('sender');
        $results = $results->distinct('created_at')->get([
            'created_at',
            'name',
            'email',
            'sender_id',
        ])
        ->toArray();

        if (!$results) {
            return [];
        }
        /*
            Get all user answer survey and group by user id.
            Sender_id can be null.
        */
        $collection = collect($results)->groupBy('sender_id')->toArray();
        //  Get users login when anwser survey with key = user id.
        $userLogin = collect($collection)->except([''])->toArray();
        /*
            Get users not login when answer survey and group by email.
            Email can be set default because user don't need enter email.
        */
        $settings = $this->getSettings($survey->id);

        if ($settings[config('settings.key.requireAnswer')] == config('settings.require.name')) {
            $userNotLogin = in_array('', array_keys($collection))
                ? collect($collection[''])->groupBy('name')->toArray()
                : [];
        } else {
            $userNotLogin = in_array('', array_keys($collection))
                ? collect($collection[''])->groupBy('email')->toArray()
                : [];
        }


        return array_merge($userLogin, $userNotLogin);
    }

    public function getUserAnswerByType($token, $type)
    {
        $survey = $this->where('token', $token)->orWhere('token_manage', $token)->first();

        if (!$survey) {
            return false;
        }

        switch ($type) {
            case config('settings.survey_result_users.all'):
                return $this->getUserAnswer($token);
            case config('settings.survey_result_users.invited'):
                $results = $this->questionRepository
                    ->getResultFollowInvitedUserByQuestionIds($survey->id)
                    ->with('sender');
                $results = $results->distinct('created_at')->get([
                    'created_at',
                    'name',
                    'email',
                    'sender_id',
                ])->all();

                if (!$results) {
                    return [];
                }

                // TODO:

                $collection = collect($results)->groupBy('sender_id')->toArray();

                return $$collection;
            case config('settings.survey_result_users.invited_answered'):
                $results = $this->questionRepository
                    ->getResultByQuestionIds($survey->id)
                    ->with('sender');
                $results = $results->distinct('created_at')->get([
                    'created_at',
                    'name',
                    'email',
                    'sender_id',
                ])
                ->toArray();

                if (!$results) {
                    return [];
                }

                $collection = collect($results)->groupBy('sender_id')->toArray();
                //  Get users login when anwser survey with key = user id.
                $userLogin = collect($collection)->except([''])->toArray();

                return $userLogin;
            case config('settings.survey_result_users.invited_not_answered'):
                $results = $this->questionRepository
                    ->getResultByQuestionIds($survey->id)
                    ->with('sender');
                $results = $results->distinct('created_at')->get([
                    'created_at',
                    'name',
                    'email',
                    'sender_id',
                ])
                ->toArray();

                if (!$results) {
                    return [];
                }

                $collection = collect($results)->groupBy('sender_id')->toArray();
                //  Get users login when anwser survey with key = user id.
                $userLogin = collect($collection)->except([''])->toArray();

                return $userLogin;
        }
    }

    private function chart(array $inputs)
    {
        $results = [];

        foreach ($inputs as $key => $value) {
            $results[] = [
                'answer' => $value['content'],
                'percent' => $value['percent'],
            ];
        }

        return $results;
    }

    public function viewChart($token)
    {
        $results = $this->getResutlSurvey($token);
        $charts = [];

        if (!$results) {
            return [
                'charts' => null,
                'status' => false,
            ];
        }

        foreach ($results as $key => $value) {
            $charts[] = [
                'question' => $value['question'],
                'chart' => ($this->chart($value['answers'])) ?: null,
            ];
        }

        return [
            'charts' => $charts,
            'status' => true,
        ];
    }

    public function exportExcel($id)
    {
        $survey = $this->model->find($id);

        $results = [];
        $questions = $survey->questions()->with('results.answer')->get()->all();
        $numberResults = count($survey->questions->first()->results()->get()->all());
        $numberQuestion = count($questions);

        for ($i = 0; $i < $numberResults; $i ++) {
            $question = [];
            for ($j = 0; $j < $numberQuestion; $j ++) {
                if (isset ($questions[$j]['results'][$i])) {
                    $question[] = $questions[$j]['results'][$i];
                }
            }

            $results[] = $question;
        }

        return [
            'questions' => $questions,
            'results' => $results,
        ];
    }
}
