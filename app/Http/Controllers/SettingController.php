<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Setting\SettingInterface;
use Exception;
use LRedis;
use DB;
use App\Http\Requests\SettingRequest;
use Predis\Connection\ConnectionException;
use Carbon\Carbon;
use Session;

class SettingController extends Controller
{
    protected $settingRepository;
    protected $surveyRepository;

    public function __construct(
        SurveyInterface $surveyRepository,
        SettingInterface $settingRepository
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->settingRepository = $settingRepository;
    }

    public function update(SettingRequest $request, $surveyId, $token)
    {
        $value = $request->only([
            'setting',
            'feature',
        ]);

        $value['next_reminder_time'] = null;

        if ($request->get('next_reminder_time')) {
            $value['next_reminder_time'] = Carbon::parse(in_array(Session::get('locale'), config('settings.sameFormatDateTime'))
                ? str_replace('-', '/', $request->get('next_reminder_time'))
                : $request->get('next_reminder_time'))
                ->toDateTimeString();
        }

        DB::beginTransaction();
        try {
            $this->settingRepository->update($surveyId, $value);
            $this->surveyRepository->update($surveyId, [
                'feature' => empty($value['feature']) ? config('settings.feature') : config('settings.not_feature'),
                'next_reminder_time' => $value['next_reminder_time'],
            ]);
            DB::commit();
            $redis = LRedis::connection();
            $redis->publish('update', json_encode([
                'success' => true,
                'surveyId' => $surveyId,
            ]));

            return redirect()->action('AnswerController@show', $token)
                ->with('message-fail', trans('survey.update_fail'));
        } catch (ConnectionException $e) {
        } catch (Exception $e) {
            DB::rollback();
        }

        return redirect()->action('AnswerController@show', $token)
            ->with('message', trans('survey.update_success'));
    }
}
