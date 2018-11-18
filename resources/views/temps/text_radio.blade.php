<div class="clear clear-as{{ $number . $numberAnswer }}"></div>
<div class="div-content-answer qs-as{{ $number . $numberAnswer }}">
    <div class="row">
        <div class="col-md-1 col-xs-1"><i class="fa fa-circle-o"></i></div>
        <div class="col-md-9 col-xs-7">
            <div class="div-text-answer">
                {!! Form::textarea("txt-question[answers][$number][$numberAnswer][" . config('survey.type_radio') . "]", isset($answer->content) ? $answer->content : '', [
                    'class' => 'js-elasticArea form-control textarea-question',
                    'placeholder' => trans('home.enter_answer_here'),
                ]) !!}
            </div>
        </div>
        <div class="remove-answer col-md-2 col-xs-4">
            {!! Html::image(config('settings.image_system') . 'img-answer.png', '', [
                'class' => 'picture-answer',
            ]) !!}
            @include('temps.answer_hidden_field')
            <a class="glyphicon glyphicon-remove btn-remove-answer" id-as="{{ $number . $numberAnswer }}" num="{{ $number }}">
            </a>
        </div>
    </div>
    <div class="content-image-answer{{ $number . $numberAnswer }} div-image-answer animated slideInDown">
        {!! Html::image(config('temp.image_default'), '', [
            'class' => 'set-image-answer image-answer' . $number . $numberAnswer,
        ]) !!}
        <span class="remove-image-answer glyphicon glyphicon-remove" id-answer="{{ $number . $numberAnswer }}"></span>
    </div>
</div>
