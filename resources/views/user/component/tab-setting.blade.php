 <div class="detail-survey">
    {!! Form::open(['action' => ['SettingController@update',
        'id' => $survey->id,
        'token' => $survey->token_manage,
    ]]) !!}
        <div class="container-setting">
            <div class="label-title-setting">
                {{ trans('survey.setting_survey') }}
            </div>
            <div class="content-setting">
                <div class="setting-label">
                    {{ trans('survey.required_answer') }}
                </div>
                <div class="setting-option row">
                    <div class="col-md-2">
                        <div class="slideThree">
                            {{ Form::checkbox('setting[' . config('settings.key.requireAnswer') . ']', '', '', [
                                'id' => 'requirement-answer',
                                ($access[config('settings.key.requireAnswer')]) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('requirement-answer', ' ') }}
                        </div>
                    </div>
                </div>
                <div class="setting-option row">
                    <div class="setting-requirement col-md-12 row {{ $access[config('settings.key.requireAnswer')] ? '' : 'div-hidden' }}">
                        {{-- require wsm --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.requireAnswer') . ']', config('settings.require.loginWsm'), '', [
                                        'id' => 'require-wsm',
                                        'class' => 'option-choose-answer input-radio',
                                        ($access[config('settings.key.requireAnswer')] == config('settings.require.loginWsm')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('require-wsm', ' ', [
                                        'class' => 'label-radio',
                                    ]) }}
                                    <div class="check"><div class="inside"></div></div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.require.login_wsm') }}</div>
                            </div>
                        </div>
                        {{-- email --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.requireAnswer') . ']', config('settings.require.email'), '', [
                                        'id' => 'option-choose-email',
                                        'class' => 'option-choose-answer input-radio',
                                        ($access[config('settings.key.requireAnswer')] == config('settings.require.email')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('option-choose-email', ' ', [
                                        'class' => 'label-radio',
                                    ]) }}
                                    <div class="check"><div class="inside"></div></div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.require.email') }}</div>
                            </div>
                        </div>
                        {{-- name --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.requireAnswer') . ']', config('settings.require.name'), '', [
                                        'id' => 'option-choose-name',
                                        'class' => 'option-choose-answer input-radio',
                                        ($access[config('settings.key.requireAnswer')] == config('settings.require.name')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('option-choose-name', ' ', [
                                        'class' => 'label-radio',
                                    ]) }}
                                    <div class="check"><div class="inside"></div></div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.require.name') }}</div>
                            </div>
                        </div>
                        {{-- name and email --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.requireAnswer') . ']', config('settings.require.both'), '', [
                                        'id' => 'option-choose-both',
                                        'class' => 'option-choose-answer input-radio',
                                        ($access[config('settings.key.requireAnswer')] == config('settings.require.both')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('option-choose-both', ' ', [
                                        'class' => 'label-radio',
                                    ]) }}
                                    <div class="check"><div class="inside"></div></div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.require.email_and_name') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="validate-requirement-answer row">
                        <div class="col-md-6">
                            <div class="alert alert-warning warning-center">
                                {{ trans('survey.validate.choose_option') }}
                            </div>
                        </div>
                    </div>
                    <div class="div-option-require row
                        {{ in_array($access[config('settings.key.requireAnswer')], [
                            config('settings.require.email'),
                            config('settings.require.both'),
                        ]) ? '' : 'div-hidden' }}">
                        <div class="col-md-1"></div>
                        <div class="squaredThree col-md-1">
                            {{ Form::checkbox('setting[' . config('settings.key.requireOnce') . ']', config('settings.key.requireOnce'), '', [
                                'id' => 'require-oneTime',
                                ($access[config('settings.key.requireOnce')] == config('settings.key.requireOnce')) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('require-oneTime', ' ') }}
                        </div>
                        <div class="tag-once col-md-5">{{ trans('survey.require_once') }}</div>
                    </div>
                    <div class="div-option-require row
                        {{ in_array($access[config('settings.key.requireAnswer')], [
                            config('settings.require.email'),
                            config('settings.require.both'),
                        ]) ? '' : 'div-hidden' }}">
                        <div class="col-md-1"></div>
                        <div class="squaredThree col-md-1">
                            {{ Form::checkbox('setting[' . config('settings.key.tailMail') . ']', '', '', [
                                'id' => 'require-tail-email',
                                ($access[config('settings.key.tailMail')]) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('require-tail-email', ' ') }}
                        </div>
                        <div class="tag-once col-md-5">{{ trans('survey.tail_mail') }}</div>
                    </div>
                    <div class="clear"></div>
                    <div class="tail-email {{ ($access[config('settings.key.tailMail')]) ? '' : 'div-hidden' }}">
                        {{ Form::text('setting[' . config('settings.key.tailMail') . ']', $access[config('settings.key.tailMail')], [
                            'placeholder' => trans('survey.placeholder.example'),
                            'class' => 'frm-tailmail',
                            'data-role' => 'tagsinput',
                        ]) }}
                    </div>
                    <div class="validate-tailmail div-hidden row">
                        <div class="col-md-6">
                            <div class="alert alert-warning warning-center content-validate-tailmail">
                                {{ trans('survey.validate.tailmail') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="image-tab-setting">
                {!! Html::image(config('settings.image_system') . 'setup3.png', '',[
                    'class' => 'animated flipInX'
                ]) !!}
            </div>
            <div>
                <div class="setting-label">
                    {{ trans('survey.replies_limits') }}
                </div>
                <div class="setting-option row">
                    <div class="col-md-2">
                        <div class="slideThree">
                            {{ Form::checkbox('setting[' . config('settings.key.limitAnswer') . ']', '', '', [
                                'id' => 'limit-answer',
                                ($access[config('settings.key.limitAnswer')]) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('limit-answer', ' ') }}
                        </div>
                    </div>
                    <div class="setting-limit col-md-4 {{ ($access[config('settings.key.limitAnswer')]) ? '' : 'div-hidden' }}">
                        <div class="qty-buttons">
                            {{ Form::button('', [
                                'class' => 'qtyplus',
                            ]) }}
                            {!! Form::text('setting[' . config('settings.key.limitAnswer') . ']', $access[config('settings.key.limitAnswer')], [
                                'class' => 'quantity-limit qty form-control required',
                                'placeholder' => trans('survey.require.none'),
                            ]) !!}
                            {{ Form::button('', [
                                'class' => 'qtyminus',
                            ]) }}
                            <span>{{ trans('survey.click_here') }}!</span>
                        </div>
                    </div>
                </div>
                <div class="validate-limit-answer row">
                    <div class="col-md-6">
                        <div class="alert alert-warning warning-center">
                            {{ trans('survey.validate.validate_number') }}
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="setting-label">{{ trans('survey.hide_result') }}</div>
                <div class="setting-option row">
                    <div class="col-md-2">
                        <div class="slideThree">
                            {{ Form::checkbox('setting[' . config('settings.key.hideResult') . ']',  config('settings.key.hideResult'), '', [
                                'id' => 'hidden-answer',
                                ($access[config('settings.key.hideResult')]) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('hidden-answer', ' ') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-setting">
                <div class="setting-label">
                    {{ trans('survey.reminder_periodically') }}
                </div>
                <div class="setting-option row">
                    <div class="col-md-2">
                        <div class="slideThree">
                            {{ Form::checkbox('setting[' . config('settings.key.reminder') . ']', '', '', [
                                'id' =>'reminder-periodically',
                                ($access[config('settings.key.reminder')]) ? 'checked' : '',
                            ]) }}
                            {{ Form::label('reminder-periodically', ' ') }}
                        </div>
                    </div>
                </div>
                <div class="setting-option row">
                    <div class="setting-reminder {{ $access[config('settings.key.reminder')] ? '' : 'div-hidden' }}">
                        {{-- reminder by week --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.reminder') . ']',
                                        config('settings.reminder.week'), '', [
                                        'id' => 'reminder-by-week',
                                        'class' => 'option-choose-reminder input-radio',
                                        ($access[config('settings.key.reminder')] == config('settings.reminder.week')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('reminder-by-week',' ', ['class' => 'label-radio']) }}
                                    <div class="check">
                                        <div class="inside"></div>
                                    </div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.reminder.week') }}</div>
                            </div>
                        </div>
                        {{-- reminder by month --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.reminder') . ']',
                                        config('settings.reminder.month'), '', [
                                        'id' => 'reminder-by-month',
                                        'class' => 'option-choose-reminder input-radio',
                                        ($access[config('settings.key.reminder')] == config('settings.reminder.month')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('reminder-by-month', ' ', ['class' => 'label-radio']) }}
                                    <div class="check">
                                        <div class="inside"></div>
                                    </div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.reminder.month') }}</div>
                            </div>
                        </div>
                        {{-- reminder by quarter --}}
                        <div class="col-md-12">
                            <div class="type-radio-answer row">
                                <div class="box-radio col-md-1">
                                    {{ Form::radio('setting[' . config('settings.key.reminder') . ']',
                                        config('settings.reminder.quarter'), '', [
                                        'id' => 'reminder-by-quarter',
                                        'class' => 'option-choose-reminder input-radio',
                                        ($access[config('settings.key.reminder')] == config('settings.reminder.quarter')) ? 'checked' : '',
                                    ]) }}
                                    {{ Form::label('reminder-by-quarter', ' ', ['class' => 'label-radio']) }}
                                    <div class="check">
                                        <div class="inside"></div>
                                    </div>
                                </div>
                                <div class="col-md-8">{{ trans('survey.reminder.quarter') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tail-reminder {{ $access[config('settings.key.reminder')] ? '' : 'div-hidden' }}">
                <div class="next-time-reminder-label">@lang('survey.next_time_reminder')</div>
                {{ Form::text('next_reminder_time',
                    old('next_reminder_time',
                    $survey->next_reminder_time
                    ? Carbon\Carbon::parse($survey->next_reminder_time)->format(trans('temp.format_with_trans'))
                    : ''), [
                        'class' => 'frm-tailreminder datetimepicker',
                    ])
                }}
            </div>
            <div class="validate-reminder-periodically row">
                <div class="col-md-6">
                    <div class="alert alert-warning warning-center">
                        {{ trans('survey.validate.choose_reminder') }}
                    </div>
                </div>
            </div>
            <div class="validate-reminder-periodically-time row">
                <div class="col-md-6">
                    <div class="alert alert-warning warning-center">
                        {{ trans('survey.validate.next_time_reminder') }}
                    </div>
                </div>
            </div>
            <div>
                <div class="setting-label">
                    <a>{{ trans('info.private') }}</a>
                    {{ trans('info.this_survey') }}?
                </div>
                <div class="setting-option row">
                    <div class="col-md-2">
                        <div class="slideThree">
                            {{ Form::checkbox('feature', $survey->feature, '', [
                                'id' => 'feature',
                                ($survey->feature) ? '' : 'checked',
                            ]) }}
                            {{ Form::label('feature', ' ') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="setting-save row">
                <div class="col-md-4 col-md-offset-4">
                    {!! Form::submit(trans('survey.save'), [
                        'class' => 'btn-save-setting  btn-action',
                    ]) !!}
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</div>
