<li class="title-question animated zoomIn row question{{ $number }}"
    question="{{ $number }}"
    temp-qs="{{ config('temp.temp_radio') }}"
    trash="{{ config('temp.trash_question_radio') }}"
>
    <div>
        <div class="row">
            <div class="text-question col-md-10">
                {!! Form::textarea("txt-question[question][$number]", '', [
                    'class' => 'js-elasticArea form-control textarea-question validate',
                    'placeholder' => trans('home.enter_question_here'),
                ]) !!}
            </div>
            <div class="col-md-2">
                <div class="img-trash row">
                    <a class="glyphicon glyphicon-picture col-md-3"></a>
                    @include('temps.question_hidden_field')
                    <a class="glyphicon glyphicon-trash col-md-1" id-question="{{ $number }}"></a>
                    <a class="glyphicon glyphicon-sort col-md-3"></a>
                </div>
            </div>
        </div>
    </div>
    <div class="content-image-question{{ $number }} div-image-question animated slideInDown">
        {!! Html::image(config('temp.image_default'), '', [
            'class' => 'set-image image-question' . $number,
        ]) !!}
        <span class="remove-image-question glyphicon glyphicon-remove" id-question="{{ $number }}"></span>
    </div>
    <div class="clear clear-as{{ $number }}0"></div>
    <div class="div-content-answer qs-as{{ $number }}0">
        <div class="row">
            <div class="col-md-1 col-xs-1"><i class="fa fa-circle-o"></i></div>
            <div class="col-md-9 col-xs-7">
                <div class="div-text-answer">
                    {!! Form::textarea("txt-question[answers][$number][0][" . config('survey.type_radio') . "]", isset($answer->content) ? $answer->content : '', [
                        'class' => 'js-elasticArea form-control textarea-question validate',
                        'placeholder' => trans('home.enter_answer_here'),
                    ]) !!}
                </div>
            </div>
            <div class="remove-answer col-md-2 col-xs-4">
                {!! Html::image(config('settings.image_path_system') . 'img-answer.png', '', [
                    'class' => 'picture-answer',
                ]) !!}
                @include('temps.answer_hidden_field', ['numberAnswer' => 0])
                <a class="glyphicon glyphicon-remove btn-remove-answer" id-as="{{ $number }}0" num="{{ $number }}">
                </a>
            </div>
        </div>
        <div class="content-image-answer{{ $number }}0 div-image-answer animated slideInDown">
            {!! Html::image(config('temp.image_default'), '', [
                'class' => 'set-image-answer image-answer' . $number . '0',
            ]) !!}
            <span class="remove-image-answer glyphicon glyphicon-remove" id-answer="{{ $number }}0"></span>
        </div>
    </div>
    <div class="clear clear-as{{ $number }}1"></div>
    <div class="div-content-answer qs-as{{ $number }}1">
        <div class="row">
            <div class="col-md-1 col-xs-1"><i class="fa fa-circle-o"></i></div>
            <div class="col-md-9 col-xs-7">
                <div class="div-text-answer">
                    {!! Form::textarea("txt-question[answers][$number][1][" . config('survey.type_radio') . "]", '', [
                        'class' => 'js-elasticArea form-control textarea-question validate',
                        'placeholder' => trans('home.enter_answer_here'),
                    ]) !!}
                </div>
            </div>
            <div class="remove-answer col-md-2 col-xs-4">
                {!! Html::image(config('settings.image_path_system') . 'img-answer.png', '', [
                    'class' => 'picture-answer',
                ]) !!}
                @include('temps.answer_hidden_field', ['numberAnswer' => 1])
                <a class="glyphicon glyphicon-remove btn-remove-answer" id-as="{{ $number }}1" num="{{ $number }}"></a>
            </div>
        </div>
        <div class="content-image-answer{{ $number }}1 div-image-answer animated slideInDown">
            {!! Html::image(config('temp.image_default'), '', [
                'class' => 'set-image-answer image-answer' . $number . '1',
            ]) !!}
            <span class="remove-image-answer glyphicon glyphicon-remove" id-answer="{{ $number }}1"></span>
        </div>
    </div>
    <div class="clear temp-other{{ $number }}"></div>
    <div class="choose-action row">
        <div class="col-md-1"></div>
        <div class="col-md-7">
            <span class="add-radio" id-as="{{ $number }}"
                typeId="{{ config('survey.type_radio') }}"
                url="{{ action('TempController@addTemp', config('temp.radio_answer')) }}">
                {{ trans('home.add_option') }}
            </span>
            <span class="add-radio-other other{{ $number }}"
                typeId="{{ config('survey.type_other_radio') }}"
                url="{{ action('TempController@addTemp', config('temp.other_radio')) }}">
                <span class="span-other">{{ trans('home.or') }}</span>
                {{ trans('home.add_other') }}
            </span>
        </div>
        <div class="col-md-3" class="div-require">
            <ul class="data-list">
                <li>
                    <div class="row">
                        <div class="col-md-6 col-xs-4 time-text">
                            <strong>{{ trans('temp.require') }}?</strong>
                        </div>
                        <div class="col-md-5 col-xs-4">
                            <div class="checkbox">
                                {{ Form::checkbox("checkboxRequired[question][$number]", $number, '', [
                                    'id' => 'radio' . $number,
                                ]) }}
                                {{ Form::label('radio' . $number, ' ') }}
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</li>
