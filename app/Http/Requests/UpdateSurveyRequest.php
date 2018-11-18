<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $realTime = Carbon::now()->addMinutes(30)->format(trans('temp.format_with_trans'));

        return [
            'start_time' => 'date_format:' . trans('temp.format_with_trans'),
            'deadline' => 'date_format:' . trans('temp.format_with_trans') . '|after:start_time|after:' . $realTime,
            'title' => 'required|max:255',
        ];
    }

    public function messages()
    {
        $deadline = trans('survey.deadline');
        $title = trans('survey.title');

        return [
            'deadline.date_format' => trans('validation.date_format', [
                'attribute' => $deadline,
                'format' => trans('temp.format_with_trans'),
            ]),
            'deadline.after' => trans('validation.after', [
                'attribute' => $deadline,
                'date' => Carbon::now()->addMinutes(30)->format(trans('temp.format_with_trans')),
            ]),
            'title.required' => trans('validation.filled', [
                'attribute' => $title,
            ]),
            'title.max' => trans('validation.max.string', [
                'attribute' => $title,
                'max' => 255,
            ]),
        ];
    }
}
