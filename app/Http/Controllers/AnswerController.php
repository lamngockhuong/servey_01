<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use App\Traits\ClientInformation;
use Carbon\Carbon;
use Session;
use Cookie;
use Response;
use App\Http\Requests\UpdateLinkSurveyRequest;

class AnswerController extends Controller
{
    use ClientInformation;

    protected $surveyRepository;

    public function __construct(SurveyInterface $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    public function answer($token, $view = 'detail', $isPublic = true)
    {
        if (Cookie::get('client_ip') === null) {
            Cookie::queue('client_ip', $this->getClientIp(), config('settings.cookie.timeout.one_day'));

            return redirect(url()->current());
        }

        $survey = $this->surveyRepository->with('settings')
            ->where(($view == 'detail') ? 'token_manage' : 'token', $token)
            ->first();

        if (!$survey
            || !in_array($view, ['detail', 'answer'])
            || ($view == 'answer' && $survey->feature != $isPublic)
            || ($view == 'detail' && $survey->user_id && auth()->check() && $survey->user_id != auth()->id())
        ) {
            return view('errors.404');
        }

        $settings = $survey->settings->pluck('value', 'key')->all();
        if ($settings[config('settings.key.requireAnswer')] == config('settings.require.loginWsm') &&
            (!auth()->user() || !auth()->user()->checkLoginWsm())) {
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
        $history = ($view == 'answer') ? $this->surveyRepository->getHistory(auth()->id(), Cookie::get('client_ip'), $survey->id, ['type' => 'history']) : null;
        $canAnswer = count($history['results']) < $settings[config('settings.key.limitAnswer')] || !$settings[config('settings.key.limitAnswer')];
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
                        'canAnswer'
                    ));
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

    public function showMultiHistory(Request $request, $surveyId, $userId = null, $email = null, $name = null, $clientIp = null)
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
        $history  = $this->surveyRepository->getHistory($userId, $clientIp, $surveyId, $options);

        return [
            'success' => true,
            'data' => view('user.pages.view-result-user', compact('history', 'survey', 'username'))->render(),
        ];
    }

    public function updateLinkSurvey(UpdateLinkSurveyRequest $request)
    {
        if ($request->ajax()) {
            $array['token'] = $request->input('token');
            $surveyId = $request->input('survey_id');
            $survey = $this->surveyRepository->update($surveyId, $array);
            $publicLink = action('AnswerController@answerPublic', ['token' => $survey->token]);

            return response()->json($publicLink);
        }
    }

    public function verifyLinkSurvey(Request $request)
    {
        if ($request->ajax()) {
            $isExisted = $this->surveyRepository->checkExist($request->input('token'));

            return response()->json($isExisted);
        }
    }

    public function getDeadline(Request $request)
    {
        try {
            if ($request->ajax()) {
                $surveyId = $request->input('survey_id');
                $survey = $this->surveyRepository->findOrFail($surveyId);

                if ($survey->status) {
                    $now = date('Y-m-d H:i:s');
                    $timeCountDown = strtotime($survey->deadline) - strtotime($now);

                    if ($timeCountDown > 0) {
                        return response()->json($timeCountDown);
                    }
                }

                return response()->json(config('survey.time_to_countdown_default'));
            }
        } catch (Exception $e) {
            return response()->json(config('survey.time_to_countdown_default'));
        }
    }
}
