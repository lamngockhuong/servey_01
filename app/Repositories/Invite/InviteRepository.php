<?php

namespace App\Repositories\Invite;

use App\Repositories\BaseRepository;
use App\Repositories\Answer\AnswerInterface;
use App\Repositories\Result\ResultInterface;
use App\Repositories\Question\QuestionInterface;
use App\Models\Invite;
use App\Repositories\User\UserInterface;
use DB;
use Exception;
use Carbon\Carbon;

class InviteRepository extends BaseRepository implements InviteInterface
{
    public function __construct(Invite $invite)
    {
        parent::__construct($invite);
    }

    public function deleteBySurveyId($surveyId)
    {
        $surveyId = is_array($surveyId) ? $surveyId : [$surveyId];
        parent::delete($this->whereIn('survey_id', $surveyId)->lists('id')->toArray());
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            $ids = is_array($ids) ? $ids : [$ids];
            $invite = $this->whereIn('id', $ids)->get();
            $senderId = $invite->recevier_id;
            $surveyId = $invite->survey_id;
            $questions = app(QuestionInterface::class)
                ->where('survey_id', $surveyId)
                ->lists('id')
                ->toArray();
            $answerIds = app(AnswerInterface::class)
                ->whereIn('question_id', $questions)
                ->lists('id')
                ->toArray();
            app(ResultInterface::class)
                ->where('sender_id', $senderId)
                ->whereIn('answer_id', $answerIds)
                ->delete();
            parent::delete($ids);
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function getResult($surveyId)
    {
        $charts = [];
        $charts['questions'] = $questions = app(QuestionInterface::class)
            ->where('survey_id', $surveyId)
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ])
            ->get();
        $charts['answers'] = $answers = app(AnswerInterface::class)
            ->whereIn('question_id', $questions->pluck('id')->toArray())
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ])
            ->get();
        $charts['results'] = $results = app(ResultInterface::class)
            ->whereIn('answer_id', $answers->pluck('id')->toArray())
            ->get();

        return $charts;
    }

    public function invite($senderId, array $recevier, $surveyId)
    {
        DB::beginTransaction();
        try {
            $usersAvailable = app(UserInterface::class)->whereIn('email', $recevier)->lists('email', 'id');
            $inputsAvailable = [];

            foreach ($usersAvailable as $id => $email) {
                $inputsAvailable[] = [
                    'sender_id' => ($senderId) ?: null,
                    'recevier_id' => $id,
                    'survey_id' => $surveyId,
                    'status' => config('survey.invite.new'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'mail' => $email,
                ];
            }

            $users = array_diff($recevier, $usersAvailable->toArray());
            $inputsUser = [];

            foreach ($users as $user) {
                $inputsUser[] = [
                    'sender_id' => $senderId,
                    'survey_id' => $surveyId,
                    'mail' => $user,
                    'status' => config('survey.invite.new'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if ($this->multiCreate($inputsUser) && $this->multiCreate($inputsAvailable)) {
                DB::commit();

                return true;
            }
        } catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }
}
