<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TempController extends Controller
{
    public function addTemp(Request $request, $type)
    {
        if (!$request->ajax()) {
            return [
                'success' => false,
            ];
        }

        $number = $request->get('number');
        $data = '';

        switch ($type) {
            case config('temp.radio_answer'):
                $param = $request->only('number', 'numberAnswer');
                $data = view('temps.text_radio', $param)
                    ->render();
                break;
            case config('temp.other_radio'):
                $data = view('temps.text_other_radio', compact('number'))
                    ->render();
                break;
            case config('temp.checkbox_answer'):
                $param = $request->only('number', 'numberAnswer');
                $data = view('temps.text_checkbox', $param)
                    ->render();
                break;
            case config('temp.other_checkbox'):
                $data = view('temps.text_other_checkbox', compact('number'))
                    ->render();
                break;
            case config('temp.radio_question'):
                $data = view('temps.radio_question', compact('number'))
                    ->render();
                break;
            case config('temp.checkbox_question'):
                $data = view('temps.checkbox_question', compact('number'))
                    ->render();
                break;
            case config('temp.text_question'):
                $data = view('temps.text_question', compact('number'))
                    ->render();
                break;
            case config('temp.time_question'):
                $data = view('temps.time_question', compact('number'))
                    ->render();
                break;
            case config('temp.date_question'):
                $data = view('temps.date_question', compact('number'))
                    ->render();
                break;
            case config('temp.text_other'):
                $idQuestion = $request->get('idQuestion');
                $idAnswer = $request->get('idAnswer');
                $tempAnswers = null;
                $data = view('temps.text_other', compact('idAnswer', 'idQuestion', 'tempAnswers'))->render();
                break;
            case config('temp.get_popup'):
                $data = view('temps.temp-popup')
                    ->render();
                break;
            default:
                $data = view('temps.error');
                break;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
