<div class="step3 wizard-hidden step row wizard-step">
    <div class="question-send-for">
        <h3>
            {{ trans('home.send_email_for') }}
        </h3>
        <ul class="data-list-2">
            <li>
                {!! Form::text('emails', '', [
                    'class' => 'form-emails',
                    'data-role' => 'tagsinput',
                    'placeholder' => trans('info.sender_email'),
                    'id' => 'invitation-email',
                    'data-url' => action('SurveyController@getMailSuggestion'),
                ]) !!}
                <ul class="box-suggestion-email">
                </ul>
            </li>
        </ul>
    </div>
    <div class="wizard-hidden validate-email animated fadeInDown row">
        <div class="col-md-6 col-md-offset-3">
            <div class="alert alert-warning warning-login-register">
                {{ trans('temp.email_invalid') }}
            </div>
        </div>
    </div>
    <div class="wizard-hidden validate-exists-reminder-email row">
        <div class="col-md-6 col-md-offset-3">
            <div class="alert alert-warning warning-login-register">
                {{ trans('temp.exists_reminder_email') }}
            </div>
        </div>
    </div>
</div>
