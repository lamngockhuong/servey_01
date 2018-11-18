<?php

namespace App\Repositories\Survey;

use DB;
use Exception;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Like\LikeInterface;
use App\Repositories\Invite\InviteInterface;
use App\Repositories\Setting\SettingInterface;
use App\Repositories\BaseRepository;
use App\Models\Question;
use Carbon\Carbon;
use App\Models\Survey;

class SurveyRepository extends BaseRepository implements SurveyInterface
{
    public function __construct(Survey $survey)
    {
        parent::__construct($survey);
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            $ids = is_array($ids) ? $ids : [$ids];
            app(InviteInterface::class)->deleteBySurveyId($ids);
            app(LikeInterface::class)->deleteBySurveyId($ids);
            app(QuestionInterface::class)->deleteBySurveyId($ids);
            app(SettingInterface::class)->deleteBySurveyId($ids);
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

        $datasInput = app(InviteInterface::class)->getResult($survey->id);
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
            'start_time',
            'next_reminder_time',
            'deadline',
            'description',
            'user_name',
        ]);

        // if the lang is english will be format from M-D-Y to M/D/Y
        if ($inputs['start_time']) {
            $inputs['start_time'] = $surveyInputs['start_time'] = Carbon::parse(in_array($locale, config('settings.sameFormatDateTime'))
                ? str_replace('-', '/', $surveyInputs['start_time'])
                : $surveyInputs['start_time'])
                ->toDateTimeString();
        }

        if ($inputs['deadline']) {
            $inputs['deadline'] = $surveyInputs['deadline'] = Carbon::parse(in_array($locale, config('settings.sameFormatDateTime'))
                ? str_replace('-', '/', $surveyInputs['deadline'])
                : $surveyInputs['deadline'])
                ->toDateTimeString();
        }

        if ($inputs['next_reminder_time']) {
            $inputs['next_reminder_time'] = $surveyInputs['next_reminder_time'] = Carbon::parse(in_array($locale, config('settings.sameFormatDateTime'))
                ? str_replace('-', '/', $surveyInputs['next_reminder_time'])
                : $surveyInputs['next_reminder_time'])
                ->toDateTimeString();
        }

        $surveyInputs['status'] = config('survey.status.available');
        $surveyInputs['start_time'] = $inputs['start_time'] ?: null;
        $surveyInputs['deadline'] = $inputs['deadline'] ?: null;
        $surveyInputs['next_reminder_time'] = $inputs['next_reminder_time'] ?: null;
        $surveyInputs['description'] = $inputs['description'] ?: null;
        $surveyInputs['created_at'] = $surveyInputs['updated_at'] = Carbon::now();
        $surveyId = parent::create($surveyInputs->toArray());

        if (!$surveyId) {
            return false;
        }

        // (1,6) That is the settings quantity for a survey
        foreach (range(1, 6) as $key) {
            if (!array_has($settings, $key)) {
                $settings[$key] = null;
            }
        }

        if (!$settings[config('settings.key.tailMail')]) {
            $settings[config('settings.key.tailMail')] = null;
        }

        app(SettingInterface::class)->createMultiSetting($settings, $surveyId);
        $txtQuestion = $arrayQuestionWithAnswer;
        $questions = $txtQuestion['question'];
        $answers = $txtQuestion['answers'];
        app(QuestionInterface::class)
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

        return app(SettingInterface::class)
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
        $invites = $inviteIds = app(InviteInterface::class)
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
            $invite = app(InviteInterface::class)
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

        return app(SettingInterface::class)
            ->where('survey_id', $surveyId)
            ->lists('value', 'key')
            ->toArray();
    }

    public function getHistory($userId, $clientIp, $surveyId, array $options)
    {
        if (!$surveyId) {
            return [
                'history' => [],
                'results' => [],
            ];
        }


        if ($userId) {
            if ($options['type'] == 'history') {
                $results = app(QuestionInterface::class)
                    ->getResultByQuestionIds($surveyId)
                    ->where('sender_id', $userId)
                    ->get();
            } else {
                $email = $options['email'];
                $name = $options['name'];
                $results = app(QuestionInterface::class)
                    ->getResultByQuestionIds($surveyId)
                    ->where(
                        function ($query) use ($userId, $clientIp, $email) {
                            $query->where('sender_id', $userId)
                                ->orWhere(
                                    function ($query) use ($clientIp, $email) {
                                        $query->where('email', $email)
                                            ->where('client_ip', $clientIp);
                                    }
                                );
                        }
                    )
                    ->get()
                    ->toArray();

                if (empty($email) && $name) {
                    $results = app(QuestionInterface::class)
                        ->getResultByQuestionIds($surveyId)
                        ->where(
                            function ($query) use ($userId, $clientIp, $name) {
                                $query->where('sender_id', $userId)
                                    ->orWhere(
                                        function ($query) use ($clientIp, $name) {
                                            $query->where('name', $name)
                                                ->where('client_ip', $clientIp);
                                        }
                                    );
                            }
                        )
                        ->get()
                        ->toArray();
                }

                $collection = collect($results);

                return $collection->groupBy('created_at')->toArray();
            }
        } else {
            if ($options['type'] == 'history') {
                $results = app(QuestionInterface::class)
                    ->getResultByQuestionIds($surveyId)
                    ->where('sender_id', null)
                    ->where('client_ip', $clientIp)
                    ->get();
            }
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

        $results = app(QuestionInterface::class)
            ->getResultByQuestionIds($survey->id)
            ->with('sender');
        $results = $results->distinct('created_at')->get([
            'created_at',
            'name',
            'email',
            'sender_id',
            'client_ip',
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

        $userNotLogin = in_array('', array_keys($collection))
                ? collect(collect($collection['']))->groupBy('client_ip')->toArray()
                : [];

        return array_merge($userLogin, $userNotLogin);
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

        $checkRequireAnswer = $survey->settings()->where('key', config('settings.key.requireAnswer'))->pluck('value', 'key')->all();
        $results = [];
        $questions = $survey->questions()->with('results.answer')->get()->all();
        $numberResults = count($survey->questions->first()->results()->get()->all());
        $numberQuestion = count($questions);

        for ($i = 0; $i < $numberResults; $i ++) {
            $question = [];
            for ($j = 0; $j < $numberQuestion; $j ++) {
                if (isset($questions[$j]['results'][$i])) {
                    $question = $questions[$j]['results'][$i];
                }
            }

            $results[] = $question;
        }

        return [
            'questions' => $questions,
            'results' => $results,
            'checkRequireAnswer' => $checkRequireAnswer,
        ];
    }

    public function duplicateSurvey($survey)
    {
        $survey->load('questions', 'settings');
        $newSurvey = $survey->replicate();

        $token = md5(uniqid(rand(), true));
        $tokenManage = md5(uniqid(rand(), true));

        $newSurvey->token = $token;
        $newSurvey->token_manage = $tokenManage;

        $newSurvey->deadline = null;
        $newSurvey->status = config('survey.status.block');

        $newSurvey->push();

        foreach ($survey->getRelations() as $relation => $items) {
            foreach ($items as $item) {
                $newItemRelation = $newSurvey->{$relation}()->create($item->toArray());

                if ($newItemRelation instanceof Question) {
                    $item->duplicate($newItemRelation);
                }
            }
        }

        return $newSurvey;
    }

    public function checkExist($token)
    {
        return $this->model->where('token', $token)->exists();
    }
}
