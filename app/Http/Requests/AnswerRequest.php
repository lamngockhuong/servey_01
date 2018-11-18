<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Answer\AnswerInterface;
use App\Repositories\Setting\SettingInterface;

class AnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected $surveyRepository;
    protected $questionRepository;
    protected $answerRepository;
    protected $questionsId;
    protected $settingRepository;
    protected $answerId;
    protected $setting;

    public function __construct(
        SurveyInterface $surveyRepository,
        QuestionInterface $questionRepository,
        AnswerInterface $answerRepository,
        SettingInterface $settingRepository,
        Request $request
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->settingRepository = $settingRepository;
        $this->questionsId = $this->getQuestionId($request->token);
        $this->answerId = $this->getAnswerId($request->token);
        $this->setting = $this->getSetting($request->token);
    }

    public function getQuestionId($token)
    {
        $survey = $this->surveyRepository
            ->where('token', $token)
            ->first()
            ->id;

        if (!$survey) {
            return [];
        }

        return $this->questionRepository
            ->where('survey_id', $survey)
            ->where('required', config('settings.required.true'))
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ])
            ->pluck('id')
            ->toArray();
    }

    public function getAnswerId($token)
    {
        $question = $this->getQuestionId($token);

        if (!$question) {
            return [];
        }

        return $this->answerRepository
            ->whereIn('question_id', $question)
            ->whereIn('type', [
                config('survey.type_time'),
                config('survey.type_date'),
                config('survey.type_text'),
            ])
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ])
            ->pluck('id', 'question_id')
            ->toArray();
    }

    public function getSetting($token)
    {
        $survey = $this->surveyRepository
            ->where('token', $token)
            ->first()
            ->id;

        if (!$survey) {
            return null;
        }

        $setting = $this->settingRepository
            ->where('survey_id', $survey)
            ->where('key', config('settings.key.requireAnswer'))
            ->first();

        return ($setting) ? $setting->value : null;
    }

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
        $rules = [];
        $answers = $this->answerId;

        foreach ($this->questionsId as $key => $question) {
            if (in_array($question, array_keys($answers))) {
                $rules['answer.' . $question . '.' . $answers[$question]] = 'required|max:255';
            } else {
                $rules['answer.' . $question] = 'required|max:255';
            }
        }

        if ($this->setting) {
            switch ($this->setting) {
                case config('settings.require.name'):
                    $rules['name-answer'] = 'required|max:40';
                    break;
                case config('settings.require.email'):
                    $rules['email-answer'] = 'required|email';
                    break;
                case config('settings.require.both'):
                    $rules['email-answer'] = 'required|email';
                    $rules['name-answer'] = 'required|max:40';
                    break;
                default:
                    break;
            }
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        $answers = $this->answerId;

        foreach ($this->questionsId as $question) {
            if (in_array($question, array_keys($answers))) {
                $messages['answer.' . $question . '.' . $answers[$question] . '.required'] = trans('messages.required');
                $messages['answer.' . $question . '.' . $answers[$question] . '.max'] = trans('messages.max');
            } else {
                $messages['answer.' . $question . '.required'] = trans('messages.required');
                $messages['answer.' . $question . '.max'] = trans('messages.max');
            }
        }

        if ($this->setting) {
            switch ($this->setting) {
                case config('settings.require.name'):
                    $messages['name-answer.required'] = trans('validation.required', [
                        'attribute' => class_basename(User::class)
                    ]);
                    $messages['name-answer.max'] = trans('validation.max.string', [
                        'attribute' => class_basename(User::class)
                    ]);
                    break;
                case config('settings.require.email'):
                    $messages['email-answer.required'] = trans('validation.required', [
                        'attribute' => class_basename(User::class)
                    ]);
                    $messages['email-answer.email'] = trans('validation.email', [
                        'attribute' => class_basename(User::class)
                    ]);
                    break;
                case config('settings.require.both'):
                    $messages['name-answer.required'] = trans('validation.required', [
                        'attribute' => class_basename(User::class)
                    ]);
                    $messages['email-answer.required'] = trans('validation.required', [
                        'attribute' => class_basename(User::class)
                    ]);
                    $messages['name-answer.max'] = trans('validation.max.string', [
                        'attribute' => class_basename(User::class)
                    ]);
                    $messages['email-answer.email'] = trans('validation.email', [
                        'attribute' => class_basename(User::class)
                    ]);
                    break;
                default:
                    break;
            }
        }

        return $messages;
    }
}
