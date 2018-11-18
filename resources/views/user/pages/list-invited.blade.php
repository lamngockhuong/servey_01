<div>
    <table class="table-invited table table-hover {{ replaceEmail(auth()->user()->email) }} table-result-user table-bordered">
        @forelse ($invites as $key => $invite)
            @if ($loop->first)
                <thead>
                    <tr>
                        <th>{{ trans('survey.name') }}</th>
                        <th>{{ trans('survey.reciever_date') }}</th>
                        <th>{{ trans('survey.sender') }}</th>
                        <th>{{ trans('survey.status') }}</th>
                        <th>{{ trans('survey.detail') }}</th>
                    </tr>
                </thead>
                <tbody>
            @endif
            <tr>
                <td>
                    {{ ++$key }}.
                    <a href="{{ action(($invite->survey->feature)
                        ? 'AnswerController@answerPublic'
                        : 'AnswerController@answerPrivate', [
                            'token' => $invite->survey->token,
                    ]) }}">
                        {{ $invite->survey->sub_title }}
                    </a>
                </td>
                <td>
                    {{ Carbon\Carbon::parse($invite->created_at)->format(trans('temp.format.date')) }}
                </td>
                <td>
                    @if ($invite->sender_id)
                        {{ Html::image($invite->sender->image, '',[
                            'class' => 'image-avatar',
                        ]) }}
                    @else
                        {{ Html::image(config('settings.image_user_default'), '',[
                            'class' => 'image-avatar',
                        ]) }}
                    @endif
                    {{ str_limit($invite->survey->user_name, config('settings.name_length_default')) }}
                </td>
                <td>
                    {!! ($invite->status) ? "<span class='glyphicon glyphicon-remove-sign'></span>" . trans('survey.not_yet')
                    : "<span class='glyphicon glyphicon-ok-sign'></span>" . trans('survey.answered') !!}
                </td>
                <td>
                    <?php $deadline = $invite->survey->deadline; ?>
                    @if (in_array($invite->survey_id, $settings)
                    || ($deadline && !Carbon\Carbon::parse($deadline)->gt(Carbon\Carbon::now()) ))
                        {{ trans('survey.closed') }}
                    @else
                        <a href="{{ action(($invite->survey->feature)
                            ? 'AnswerController@answerPublic'
                            : 'AnswerController@answerPrivate', [
                                'token' => $invite->survey->token,
                        ]) }}">
                            {{ trans('survey.link') }}
                        </a>
                    @endif
                </td>
            </tr>
            @if ($loop->last)
                </tbody>
            @endif
        @empty
            <div class="alert alert-warning">
                {{ trans('messages.not_have_results') }}
            </div>
        @endforelse
    </table>
    {{ $invites->links('pagination.default') }}
</div>
