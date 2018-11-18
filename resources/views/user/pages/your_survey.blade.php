<div>
    @if (!$surveys->isEmpty())
        <table class="table-list-survey table table-hover table-result-user table-bordered">
            <thead>
                <tr>
                    <th>{{ trans('survey.index') }}</th>
                    <th>{{ trans('survey.name') }}</th>
                    <th>{{ trans('survey.date_create') }}</th>
                    <th>{{ trans('survey.send') }}</th>
                    <th>{{ trans('survey.share') }}</th>
                    <th>{{ trans('survey.setting') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surveys as $survey)
                    @if ($survey->status && $survey->isOpen && !in_array($survey->id, $settings))
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ action(($survey->feature)
                                    ? 'AnswerController@answerPublic'
                                    : 'AnswerController@answerPrivate', [
                                        'token' => $survey->token,
                                ]) }}">
                                {{ $survey->title }}
                                </a>
                            </td>
                            <td>
                                {{ Carbon\Carbon::parse($survey->created_at)->format(trans('temp.format.date')) }}
                            </td>
                                <td>
                                    <a class="tag-send-email"
                                        data-url="{{ action('SurveyController@inviteUser', [
                                            'id' => $survey->id,
                                            'type' => config('settings.return.view'),
                                        ]) }}"
                                        data-type="{{ $survey->feature }}"
                                        data-link="{{ action('AnswerController@answerPublic', $survey->token) }}">
                                        <span class="glyphicon glyphicon-send"></span>
                                        {{ trans('survey.send') }}
                                    </a>
                                </td>
                                @if ($survey->feature)
                                    <td>
                                        <div class="fb-share-button"
                                            data-href="{{ action('AnswerController@answerPublic', $survey->token) }}"
                                            data-layout="button_count"
                                            data-size="small"
                                            data-mobile-iframe="true">
                                            <a class="fb-xfbml-parse-ignore"
                                                target="_blank"
                                                href="{{ action('AnswerController@answerPublic', $survey->token) }}">
                                                {{ trans('survey.share') }}
                                            </a>
                                        </div>
                                    </td>
                                @else
                                    <td>{{ trans('survey.private') }}</td>
                                @endif
                            <td class="text-center">
                                <a href="{{ action('AnswerController@show', [
                                    'token' => $survey->token_manage,
                                    'type' => $survey->feature,
                                ]) }}" class="glyphicon glyphicon-cog"></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        {{ $surveys->links('pagination.default') }}
    @else
        <div class="alert alert-warning">
            {{ trans('messages.not_have_results') }}
        </div>
    @endif
</div>
