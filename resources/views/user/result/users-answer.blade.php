@if ($listUserAnswer)
    <table class="table-result-user table table-bordered">
        <thead class="thead-default">
            <tr>
                <th>
                    {{ trans('user.name') }}
                </th>
                <th>
                    {{ trans('user.email') }}
                </th>
                <th>
                    {{ trans('user.date') }}
                </th>
                <th>
                    {{ trans('user.detail') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listUserAnswer as $user)
                @php $info = head($user) @endphp
                <tr>
                    <td>
                        {!! Html::image((!empty($info['sender_id']) && isset($info['sender']['image']))
                            ? $info['sender']['image']
                            : config('settings.image_user_default'), '', [
                                'class' => 'image-avatar',
                        ]) !!}
                        {{ $info['name'] ?: trans('user.incognito') }}
                    </td>
                    <td>
                        {{ $info['email'] ?: trans('user.incognito') }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($info['created_at'])->format(trans('temp.format.date')) }}
                    </td>
                    <td>
                        <a class="show-multi-history" data-url="{{ action('AnswerController@showMultiHistory', [
                            'surveyId' => $survey->id,
                            'createdAt' => $info['created_at'],
                            'userId' => $info['sender_id'],
                            'email' => $info['email'],
                            'name' => $info['name'],
                            'clientIp' => $info['client_ip'],
                        ]) }}" data-username="{{ $info['name'] ?: trans('user.incognito') }}">{{ trans('survey.link') }}</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="alert alert-warning">
        {{ trans('messages.not_have_results') }}
    </div>
@endif
