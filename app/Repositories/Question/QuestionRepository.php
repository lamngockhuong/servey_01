<?php

namespace App\Repositories\Question;

use App\Repositories\Answer\AnswerInterface;
use App\Repositories\BaseRepository;
use App\Models\Answer;
use DB;
use Exception;
use App\Models\Question;
use Illuminate\Support\Collection;

class QuestionRepository extends BaseRepository implements QuestionInterface
{
    protected $answerRepository;

    public function __construct(Question $question)
    {
        parent::__construct($question);
    }

    public function deleteBySurveyId($surveyIds)
    {
        $ids = is_array($surveyIds) ? $surveyIds : [$surveyIds];
        $questions = $this->whereIn('survey_id', $ids)->lists('id')->toArray();
        app(AnswerInterface::class)->deleteByQuestionId($questions);
        parent::delete($questions);
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            $ids = is_array($ids) ? $ids : [$ids];
            app(AnswerInterface::class)->deleteByQuestionId($ids);
            parent::delete($ids);
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }
    }

    public function createMultiQuestion(
        $survey,
        $questions,
        $answers,
        $image,
        $imageUrl,
        $videoUrl,
        $required = null
    ) {
        $questionsAdd = [];
        $answersAdd = [];
        $image = [
            'question' => (array_get($image, 'question')) ? $image['question']: [],
            'answers' => (array_get($image, 'answers')) ? $image['answers'] : [],
        ];

        // check the serial number of arrays answer questions coincide with the array or not
        if (array_keys($questions) !== array_keys($answers)) {
            return false;
        }

        if (empty($required)) {
            $required = [];
        }

        $sequence = 0;

        foreach ($questions as $key => $value) {
            if (!strlen($value) && $questions) {
                $value = config('survey.question_default');
            }

            $questionsAdd[] = [
                'content' => $value,
                'survey_id' => $survey,
                'image' => array_get($image['question'], $key)
                    ? $this->uploadImage($image['question'][$key], config('settings.image_question_path'))
                    : $this->uploadImageUrl(array_get($imageUrl, 'question.' . $key), config('settings.image_question_path')),
                'required' => in_array($key, $required),
                'sequence' => $sequence,
                'video' => array_get($videoUrl['question'], $key),
            ];

            $sequence++;
        }

        if ($this->multiCreate($questionsAdd)) {
            $questionIds = $this
                ->where('survey_id', $survey)
                ->lists('id')
                ->toArray();

            foreach (array_keys($questions) as $number => $index) {
                foreach ($answers[$index] as $key => $value) {
                    $type = array_keys($value)[0];

                    switch ($type) {
                        case config('survey.type_other_radio'): case config('survey.type_other_checkbox'):
                            $temp = trans('temp.other');
                            break;
                        case config('survey.type_text'):
                            $temp = trans('temp.text');
                            break;
                        case config('survey.type_time'):
                            $temp = trans('temp.time');
                            break;
                        case config('survey.type_date'):
                            $temp = trans('temp.date');
                            break;
                        default:
                            $temp = $value[$type];
                            break;
                    }

                    // checking the answers in the question have image and the answer is have any image
                    $isHaveImage = (array_get($image['answers'], $index)
                        && array_get($image['answers'][$index], $key));
                    $answersAdd[] = [
                        'content' => $temp,
                        'question_id' => $questionIds[$number],
                        'type' => $type,
                        'image' => ($isHaveImage )
                            ? $this->uploadImage($image['answers'][$index][$key], config('settings.image_answer_path'))
                            : $this->uploadImageUrl(array_get($imageUrl, 'answers.' . $index . '.' . $key), config('settings.image_answer_path')),
                        'video' => array_get($videoUrl, 'answers.' . $index . '.' . $key),
                    ];
                }
            }

            if (app(AnswerInterface::class)->multiCreate($answersAdd)) {
                return true;
            }
        }

        return false;
    }

    public function getQuestionIds($surveyId)
    {
        return $this->where('survey_id', $surveyId)->lists('id')->toArray();
    }

    public function getResultByQuestionIds($surveyId, $time = null)
    {
        $questionIds = $this->getQuestionIds($surveyId);

        if (!$time) {
            return app(AnswerInterface::class)->getResultByAnswer($questionIds, null, true);
        }

        return app(AnswerInterface::class)->getResultByAnswer($questionIds, $time);
    }

    private function createOrUpdateQuestion(array $data)
    {
        $checkboxRequired = $data['checkboxRequired'];
        $imagesQuestion = $data['imagesQuestion'];
        $imagesAnswer = $data['imagesAnswer'];
        $dataUpdate = [];
        $dataUpdate['content'] = $data['questionContent'];
        $dataUpdate['sequence'] = $data['indexQuestion'];
        $collectQuestion = $data['collectQuestion'];
        $maxUpdateQuestion = $data['maxUpdateQuestion'];
        $deleteImageIds = $data['deleteImageIds'];
        $surveyId = $data['surveyId'];
        $questionId = $data['questionId'];
        $answers = $data['answers'];
        $isEdit = $data['isEdit'];
        $deleteImageIdsAnswer = $data['deleteImageIdsAnswer'];
        $imageUrlQuestion = $data['imageUrlQuestion'];
        $imageUrlAnswer = $data['imageUrlAnswer'];
        $videoUrlQuestion = $data['videoUrlQuestion'];
        $videoUrlAnswer = $data['videoUrlAnswer'];

        if (in_array($questionId, $deleteImageIds)) {
            $dataUpdate['image'] = null;
        }

        $dataUpdate['required'] = array_key_exists($questionId, $checkboxRequired) ? 1 : 0;

        if ($imagesQuestion && array_key_exists($questionId, $imagesQuestion)) {
            $dataUpdate['image'] = $this->uploadImage($imagesQuestion[$questionId], config('settings.image_question_path'));
        }

        if ($imageUrlQuestion) {
            $dataUpdate['image'] = $this->uploadImageUrl($imageUrlQuestion, config('settings.image_question_path'));
        }

        if ($videoUrlQuestion) {
            $dataUpdate['video'] = $videoUrlQuestion;
        }

        $modelQuestion = $collectQuestion->where('id', $questionId)->first();

        if ($modelQuestion) {
            $modelQuestion->fill($dataUpdate);
            /*
            * if update the old question
            * create new question with the input content
            * update old question field update = -1
            * create new answers have same answers of old question with field update = 0
            * update old answers of old question with field update = -1
            */
            if ($field = $modelQuestion->getDirty()) {
                if (head(array_keys($field)) != 'sequence') {
                    $newQuestion = $this->firstOrCreate([
                        'sequence' => $dataUpdate['sequence'],
                        'survey_id' => $surveyId,
                        'content' => $modelQuestion->content,
                        'image' => $modelQuestion->image_update,
                        'video' => $modelQuestion->video,
                        'required' => $modelQuestion->required,
                        'update' => $maxUpdateQuestion + 1,
                        'clone_id' => ($modelQuestion->clone_id) ?: $modelQuestion->id,
                    ]);
                    $modelQuestion = $modelQuestion->fill($modelQuestion->getOriginal());
                    $modelQuestion->update = config('survey.update.change');
                    $modelQuestion->save();
                    app(AnswerInterface::class)->newQuery(new Answer());
                    $oldAnswers = app(AnswerInterface::class)
                        ->where('question_id', $modelQuestion->id)
                        ->where('update', '>=', 0)
                        ->orderBy('type')
                        ->get();
                    $update = $oldAnswers->max('update') + 1;
                    $dataInput = [];
                    $loop = 0;
                    $cloneAnswers = $oldAnswers->whereIn('type', [
                        config('survey.type_radio'),
                        config('survey.type_checkbox'),
                        config('survey.type_text'),
                        config('survey.type_time'),
                        config('survey.type_date'),
                    ]);

                    foreach ($cloneAnswers->values() as $answer) {
                        $answer->fill([
                            'content' => $answers[$modelQuestion->id][$loop][$answer->type],
                        ]);
                        $answer->update = config('survey.update.change');
                        $answer->save();
                        $content = in_array($answer->type, [
                            config('survey.type_other_radio'),
                            config('survey.type_other_checkbox'),
                        ]) ? trans('temp.other') : $answer->content;
                        $dataInput[] = [
                            'question_id' => $newQuestion->id,
                            'content' => $answer->getDirty() ? $answer->content : $content,
                            'type' => $answer->type,
                            'image' => !in_array($answer->id, $deleteImageIdsAnswer) ? $answer->image_update : null,
                            'update' => $update,
                            'clone_id' => $answer->clone_id ?: $answer->id,
                        ];
                        $answers[$modelQuestion->id] = array_except($answers[$modelQuestion->id], $loop);
                        $loop++;
                    }

                    $answer = $oldAnswers->whereIn('type', [
                        config('survey.type_other_radio'),
                        config('survey.type_other_checkbox'),
                    ]);

                    if (!$answer->isEmpty()) {
                        $answer = $answer->first();
                        $dataInput[] = [
                            'question_id' => $newQuestion->id,
                            'content' => trans('temp.other'),
                            'type' => $answer->type,
                            'image' => null,
                            'update' => $update,
                            'clone_id' => $answer->clone_id ?: $answer->id,
                        ];
                        $answers[$modelQuestion->id] = array_except($answers[$modelQuestion->id], last(array_keys($answers[$modelQuestion->id])));
                        $answer->update = config('survey.update.change');
                        $answer->save();
                    }

                    if ($createAnswer = $answers[$modelQuestion->id]) {
                        $checkImagesAnswerCreate = ($imagesAnswer && array_key_exists($modelQuestion->id, $imagesAnswer));

                        foreach ($createAnswer as $key => $value) {
                            $checkHaveImage = ($checkImagesAnswerCreate && array_key_exists($answerIndex, $imagesAnswer[$questionId]));
                            $content = in_array(array_keys($value), [
                                config('survey.type_other_radio'),
                                config('survey.type_other_checkbox'),
                            ]) ? trans('temp.other') : head($value);
                            $dataInput[] = [
                                'question_id' => $newQuestion->id,
                                'content' => $content,
                                'type' => head(array_keys($value)),
                                'image' => $checkHaveImage
                                    ? app(AnswerInterface::class)->uploadImage($imagesAnswer[$questionId][$answerIndex], config('settings.image_answer_path'))
                                    : null,
                                'update' => $update,
                                'clone_id' => 0, // dataInput must be same key
                            ];
                            $answers[$modelQuestion->id] = array_except($answers[$modelQuestion->id], $key);
                        }
                    }

                    app(AnswerInterface::class)->multiCreate($dataInput);
                    app(AnswerInterface::class)->multiUpdate('question_id', $modelQuestion->id, [
                        'update' => config('survey.update.change'),
                    ]);
                } else {
                    $modelQuestion->save();
                }

                $isEdit = true;
            }
        } else {
            // not found record in collection then insert question
            $modelQuestion = $this->firstOrCreate([
                'sequence' => $dataUpdate['sequence'],
                'survey_id' => $surveyId,
                'content' => $dataUpdate['content'],
                'image' => array_get($dataUpdate, 'image'),
                'video' => array_get($dataUpdate, 'video'),
                'required' => $dataUpdate['required'],
                'update' => $maxUpdateQuestion + 1,
            ]);

            // insert answers after insert question
            $dataInput = [];
            $checkImagesAnswerCreate = ($imagesAnswer && array_key_exists($questionId, $imagesAnswer));

            foreach ($answers[$questionId] as $answerIndex => $content) {
                $checkHaveImage = ($checkImagesAnswerCreate && array_key_exists($answerIndex, $imagesAnswer[$questionId]));
                $dataInput[] = [
                    'question_id' => $modelQuestion->id,
                    'content' => head($content),
                    'type' => head(array_keys($content)),
                    'image' => $checkHaveImage
                        ? app(AnswerInterface::class)->uploadImage($imagesAnswer[$questionId][$answerIndex], config('settings.image_answer_path'))
                        : app(AnswerInterface::class)->uploadImageUrl(array_get($imageUrlAnswer, $answerIndex), config('settings.image_answer_path')),
                    'video' => array_get($videoUrlAnswer, $answerIndex),
                ];
            }

            app(AnswerInterface::class)->multiCreate($dataInput);
            $answers = array_except($answers, [$questionId]);
            $isEdit = true;
        }

        return [
            'success' => true,
            'answers' => $answers,
            'isEdit' => $isEdit,
        ];
    }

    private function sliptString($value)
    {
        return array_where(explode(',', $value), function($value, $key) {
            return !empty($value);
        });
    }

    /*
    * This function modify question and answer of survey
    * Return the emails of users answer the survey if survey have answer
    */
    public function updateSurvey(array $inputs, $surveyId)
    {
        $idsDeletesQuestion = [];
        $idsDeletesAnswer = [];
        $isEdit = false;

        if (!$surveyId) {
            return false;
        }

        if ($inputs['del-question']) {
            $idsDeletesQuestion = $this->sliptString($inputs['del-question']);

            if ($idsDeletesQuestion) {
                $this->multiUpdate('id', $idsDeletesQuestion, [
                    'update' => config('survey.update.delete'),
                ]);
                app(AnswerInterface::class)->multiUpdate('question_id', $idsDeletesQuestion, [
                    'update' => config('survey.update.delete'),
                ]);
                $isEdit = true;
            }
        }

        if ($inputs['del-answer']) {
            $idsDeletesAnswer = $this->sliptString($inputs['del-answer']);

            if ($idsDeletesAnswer) {
                app(AnswerInterface::class)->newQuery(new Answer());
                app(AnswerInterface::class)->multiUpdate('id', $idsDeletesAnswer, [
                    'update' => config('survey.update.delete'),
                ]);
                $isEdit = true;
            }
        }

        $this->newQuery(new Question()); // new query after update question
        app(AnswerInterface::class)->newQuery(new Answer());// new query after update answer
        $collectQuestion = $questionIds = $this->where('survey_id', $surveyId)
            ->whereNotIn('id', $idsDeletesQuestion)
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ]);
        $this->newQuery(new Question()); // new query after select question
        $questions = $inputs['txt-question']['question']; // the questions get by request in controller
        $answers = $inputs['txt-question']['answers']; // the answers get by request in controller
        $checkboxRequired = $inputs['checkboxRequired']['question'] ?: []; // ids question required get by request in controller
        $images = $inputs['image']; // image of answer and question get by requset in controller
        $imagesQuestion = ($images && array_key_exists('question', $images)) ? $images['question'] : [];
        $imagesAnswer = ($images && array_key_exists('answers', $images)) ? $images['answers'] : [];
        // Note: sai logic, lay het file upload thay vi lay 1 file theo id trong loop

        $collectAnswer = app(AnswerInterface::class)
            ->whereIn('question_id', $questionIds->pluck('id')->toArray())
            ->whereNotIn('id', $idsDeletesAnswer)
            ->whereNotIn('update', [
                config('survey.update.change'),
                config('survey.update.delete'),
            ])
            ->get()
            ->groupBy('question_id');
        app(AnswerInterface::class)->newQuery(new Answer()); // new query after select answer
        $collectQuestion = $collectQuestion->get();
        $indexQuestion = 0;
        $maxUpdateQuestion = $collectQuestion->max('update');
        // check if remove all answer and question
        $collectAnswer = $collectAnswer->isEmpty() ? collect([]) : $collectAnswer;
        $collectQuestion = $collectAnswer->isEmpty() ? collect([]) : $collectQuestion;
        $deleteImageIdsQuestion = !empty($inputs['del-question-image']) ? $this->sliptString($inputs['del-question-image']) : [];
        $deleteImageIdsAnswer = !empty($inputs['del-answer-image']) ? $this->sliptString($inputs['del-answer-image']) : [];

        foreach ($questions as $questionId => $questionContent) {
            $data = [
                'collectQuestion' => $collectQuestion,
                'indexQuestion' => $indexQuestion,
                'checkboxRequired' => $checkboxRequired,
                'imagesQuestion' => $imagesQuestion,
                'deleteImageIds' => $deleteImageIdsQuestion,
                'imagesAnswer' => $imagesAnswer,
                'maxUpdateQuestion' => $maxUpdateQuestion,
                'surveyId' => $surveyId,
                'questionId' => $questionId,
                'questionContent' => $questionContent,
                'answers' => $answers,
                'isEdit' => $isEdit,
                'deleteImageIdsAnswer' => $deleteImageIdsAnswer,
                'imageUrlQuestion' => array_get($inputs, 'image-url.question.' . $questionId),
                'imageUrlAnswer' => array_get($inputs, 'image-url.answers.' . $questionId),
                'videoUrlQuestion' => array_get($inputs, 'video-url.question.' . $questionId),
                'videoUrlAnswer' => array_get($inputs, 'video-url.answers.' . $questionId),
            ];

            $questionsResult = $this->createOrUpdateQuestion($data);
            $isEdit = $questionsResult['isEdit'];
            $indexQuestion++;
            // insert or update answer after create or update question
            $answersInQuestion = $collectAnswer->has($questionId)
                ? $collectAnswer[$questionId]->whereIn('type', [config('survey.type_radio'), config('survey.type_checkbox')])
                : collect([]);

            if ($answers[$questionId] && $answersInQuestion && !$answersInQuestion->isEmpty()) {
                $data = [
                    'questionId' => $questionId,
                    'answersInQuestion' => $answersInQuestion,
                    'collectAnswer' => $collectAnswer,
                    'imagesAnswer' => $imagesAnswer,
                    'deleteImageIds' => $deleteImageIdsAnswer,
                    'isEdit' => $isEdit,
                    'imageUrlQuestion' => array_get($inputs, 'image-url.question.' . $questionId),
                    'imageUrlAnswer' => array_get($inputs, 'image-url.answers.' . $questionId),
                    'videoUrlQuestion' => array_get($inputs, 'video-url.question.' . $questionId),
                    'videoUrlAnswer' => array_get($inputs, 'video-url.answers.' . $questionId),
                ];
                $answersResult = app(AnswerInterface::class)->createOrUpdateAnswer($questionsResult['answers'], $data);
                $answers = $answersResult['answers'];
                $isEdit = $answersResult['isEdit'];
            }
        }

        $success = [];
        app(AnswerInterface::class)->newQuery(new Answer());
        $this->newQuery(new Question());
        $questionIds = $this->where('survey_id', $surveyId)->lists('id')->toArray();
        $success['emails'] = app(AnswerInterface::class)->getResultByAnswer($questionIds, null, true)
            ->where('email', '<>', (string)config('settings.email_unidentified'))
            ->get(['email'])
            ->unique('email')
            ->values()
            ->reduce(function ($carry, $item) {
                $carry[] = $item->email;

                return $carry;
            }, []);
        $success['isEdit'] = $isEdit;

        return $success;
    }
}
