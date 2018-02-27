<?php

namespace App\Repositories\Survey;

interface SurveyInterface
{
    public function delete($ids);

    public function getResutlSurvey($token);

    public function createSurvey(
        $inputs,
        array $settings,
        array $arrayQuestionWithAnswer,
        array $questionsRequired,
        array $images,
        array $imageUrl,
        array $videoUrl,
        $locale
    );

    public function checkCloseSurvey($inviteIds, $surveyIds);

    public function listsSurvey($userId, $email = null);

    public function getSettings($surveyId);

    public function getHistory($userId, $surveyId, array $options);

    public function getUserAnswer($token);

    public function getUserAnswerByType($token, $type);

    public function exportExcel($id);
}
