<table class="table-list-survey table table-hover table-result-user table-bordered">
    @if ($surveyCloses)
            <thead>
                <tr>
                    <th>{{ trans('survey.index') }}</th>
                    <th>{{ trans('survey.name') }}</th>
                    <th>{{ trans('survey.date_create') }}</th>
                    <th>{{ trans('survey.status') }}</th>
                    <th>{{ trans('survey.setting') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surveyCloses as $survey)
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
                        <td class="td-servey-closed">
                            {{ trans('survey.closed') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ action('AnswerController@show', [
                                'token' => $survey->token_manage,
                                'type' => $survey->feature,
                            ]) }}" class="glyphicon glyphicon-cog"></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @else
            <div class="alert alert-warning">
                {{ trans('messages.not_have_results') }}
            </div>
        @endif
    </tbody>
</table>
{{ $surveyCloses->links('pagination.default') }}
