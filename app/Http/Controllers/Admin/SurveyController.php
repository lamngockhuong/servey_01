<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected $surveyRepository;

    public function __construct(SurveyInterface $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    public function index()
    {
        $surveyAll = $this->surveyRepository->paginate(config('settings.paginate'));
        $surveys = $surveyAll->where('feature', config('settings.not_feature'));
        $surveyFeatures = $surveyAll->where('feature', config('settings.feature'));

        return view('admin.pages.surveys.list', compact('surveys', 'surveyFeatures', 'surveyAll'));
    }

    public function update($value, Request $request)
    {
        $surveys = [];

        if ($request->get('checkbox-survey-change')) {
            $surveys = $request->get('checkbox-survey-change');
        } else {
            $surveys = $request->get('checkbox-survey-update');
        }

        if ($this->surveyRepository->multiUpdate('id', $surveys, ['feature' => $value])) {
            return redirect()->action('Admin\SurveyController@index')
                ->with('message-success', trans('admin.update.success'));
        }

        return redirect()->action('Admin\SurveyController@index')->with('message-fail', trans('admin.update.fail'));
    }

    public function destroySurvey(Request $request)
    {
        if ($request->ajax()) {
            $idSurvey = $request->get('idSurvey');
            $this->surveyRepository->delete($idSurvey);
        }

        return redirect()->action('Admin\SurveyController@index')->with('message-fail', trans('admin.delete_fail'));
    }
}
