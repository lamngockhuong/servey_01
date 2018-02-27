<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use Carbon\Carbon;
use Session;

class AnswerController extends Controller
{
    protected $surveyRepository;

    public function __construct(SurveyInterface $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    public function answer($token, $view = 'detail', $isPublic = true)
    {
        $survey = $this->surveyRepository->with('settings')
            ->where(($view == 'detail') ? 'token_manage' : 'token', $token)
            ->first();
        $settings = $survey->settings->pluck('value', 'key')->all();

        if (!$survey
            || !in_array($view, ['detail', 'answer'])
            || ($view == 'answer' && $survey->feature != $isPublic)
            || ($view == 'detail' && $survey->user_id && auth()->check() && $survey->user_id != auth()->id())
        ) {
            return view('errors.404');
        }

        if ($settings[config('settings.key.requireAnswer')] == config('settings.require.loginWsm') && (!auth()->user() || !auth()->user()->checkLoginWsm())) {
            Session::put('nextUrl', $_SERVER['REQUEST_URI']);

            return view('user.pages.login_wsm');
        }

        if (!$isPublic && $survey->user_id && !auth()->check()) {
            return redirect()->action('Auth\LoginController@getLogin');
        }

        if ($view == 'detail' && $survey->user_id && $survey->user_id != auth()->id()) {
            return redirect()->action('SurveyController@index')
                ->with('message-fail', trans_choice('messages.permisstion', 0));
        }

        $access = $this->surveyRepository->getSettings($survey->id);
        $listUserAnswer = $this->surveyRepository->getUserAnswer($token);
        $history = ($view == 'answer') ? $this->surveyRepository->getHistory(auth()->id(), $survey->id, ['type' => 'history']) : null;
        $getCharts = $this->surveyRepository->viewChart($survey->token);
        $status = $getCharts['status'];
        $charts = $getCharts['charts'];
        $check = $this->surveyRepository->checkSurveyCanAnswer([
            'surveyId' => $survey->id,
            'deadline' => $survey->deadline,
            'status' => $survey->status,
            'type' => $isPublic,
            'userId' => auth()->id(),
            'email' => auth()->check() ? auth()->user()->email : null,
        ]);

        if ($survey) {
            if ($survey->user_id == auth()->id() || $check) {
                $results = $history['results'];
                $history = $history['history'];
                $tempAnswers = ($results && !$results->isEmpty()) ? $results : null;

                return view(($view == 'detail')
                    ? 'user.pages.detail-survey'
                    : 'user.pages.answer', compact(
                        'survey',
                        'status',
                        'charts',
                        'access',
                        'history',
                        'listUserAnswer',
                        'tempAnswers',
                        'token'
                    )
                );
            }
        }

        return redirect()->action('SurveyController@index')
            ->with('message-fail', trans_choice('messages.permisstion', ($view == 'detail') ? 0 : 1));
    }

    public function answerPublic($token)
    {
        return $this->answer($token, 'answer', true);
    }

    public function answerPrivate($token)
    {
        return $this->answer($token, 'answer', false);
    }

    public function show($token)
    {
        return $this->answer($token, 'detail');
    }

    public function showMultiHistory(Request $request, $surveyId, $userId = null, $email = null, $name = null)
    {
        if (!$request->ajax()) {
            return [
                'success' => false,
            ];
        }

        $survey = $this->surveyRepository->find($surveyId);

        if (!$survey) {
            return action('SurveyController@index')
                ->with('message-fail', trans_choice('messages.load_fail', 1));
        }

        $options = [
            'type' => 'result',
            'email' => $email,
            'name' => $name,
        ];
        $username = $request->get('username');
        $history  = $this->surveyRepository->getHistory($userId, $surveyId, $options);

        return [
            'success' => true,
            'data' => view('user.pages.view-result-user', compact('history', 'survey', 'username'))->render(),
        ];
    }

    public function userAnswerResults(Request $request, $token, $type)
    {
        if (!$request->ajax()) {
            return ['success' => false];
        }

        $survey = $this->surveyRepository->with('settings')
            ->where('token_manage', $token)
            ->first();
        $settings = $survey->settings->pluck('value', 'key')->all();

        if (!$survey || ($survey->user_id && auth()->check() && $survey->user_id != auth()->id())) {
            return ['success' => false];
        }

        if ($settings[config('settings.key.requireAnswer')] == config('settings.require.loginWsm') && (!auth()->user() || !auth()->user()->checkLoginWsm())) {
            return ['success' => false];
        }

        if ($survey->user_id && !auth()->check()) {
            return ['success' => false];
        }

        if ($survey->user_id && $survey->user_id != auth()->id()) {
            return ['success' => false];
        }

        $listUserAnswer = $this->surveyRepository->getUserAnswerByType($token, $type);

        $data = view('user.result.users-answer', compact('listUserAnswer', 'survey'))->render();

        return [
            'success' => true,
            'survey_id' => $survey->id,
            'data' => $data,
        ];
    }
}
