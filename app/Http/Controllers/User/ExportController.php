<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use Excel;
use PDF;
use Exception;

class ExportController extends Controller
{
    protected $surveyRepository;

    public function __construct(SurveyInterface $survey)
    {
        $this->surveyRepository = $survey;
    }

    public function export($id, $type)
    {
        try {
            $survey = $this->surveyRepository->find($id);
            $data = $this->surveyRepository->exportExcel($id);
            $title = mb_substr($survey->title, 0, 31);

            if(!$survey || !$type) {
                throw new Exception("Error Processing Request", 1);
            }

            if ($type == 'excel') {
                return Excel::create($title, function($excel) use ($title, $data) {
                    $excel->sheet($title, function($sheet) use ($data) {
                        $sheet->loadView('explore.excel', compact('data'));
                        $sheet->setOrientation('landscape');
                    });
                })->export('xls');
            } 

            if ($type == 'pdf') {
                return PDF::loadView('explore.PDF', compact('survey'))->download($survey->title . '.pdf');
            }
        } catch (Exception $e) {
            return redirect()->action('AnswerController@show', $survey->token_manage)
                ->with('message-fail', trans_choice('messages.load_fail', 2, [
                    'format' => $type,
                ]));
        }
    }
}
