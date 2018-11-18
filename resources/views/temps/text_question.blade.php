<li class="title-question animated zoomIn row question{{ $number }}" question="{{ $number }}">
    <div>
        <div class="row">
            <div class="text-question col-md-10">
                {!! Form::textarea("txt-question[question][$number]", '', [
                    'class' => 'js-elasticArea form-control textarea-question validate',
                    'placeholder' => trans('home.enter_question_here'),
                    'required' => true,
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
    <div class="clear"></div>
    <div class="div-content-answer container-text">
        <div class="div-clock col-md-1 col-xs-1"><i class="fa fa-file-text-o"></i></div>
        <div class="col-md-7 col-xs-10">
            <div class="text-empty">
                {!! Form::textarea("txt-question[answers][$number][][" . config('survey.type_text') . "]", '', [
                    'class' => 'js-elasticArea form-control textarea-question',
                    'placeholder' => trans('temp.text'),
                    'readonly' => true,
                ]) !!}
            </div>
        </div>
        <div class="col-md-4 col-xs-12 div-required">
            <div class="row">
                <div class="col-md-5 col-xs-4 time-text">
                    <strong>{{ trans('temp.require') }}?</strong>
                </div>
                <div class="col-md-5 col-xs-5">
                    <div class="checkbox">
                        {{ Form::checkbox("checkboxRequired[question][$number]", $number, '', [
                            'id' => 'text' . $number,
                        ]) }}
                        {{ Form::label('text' . $number, ' ') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
