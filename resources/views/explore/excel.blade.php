<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    <table class="table-result table">
        <thead class="thead-default">
            <tr>
                <th>
                    {{ trans('survey.timestamps') }}
                </th>

                @if (in_array($data['checkRequireAnswer'][config('settings.key.requireAnswer')], [
                    config('settings.require.email'),
                    config('settings.require.both'),
                    config('settings.require.loginWsm'),
                ])) 
                    <th>{{ trans('user.email') }}</th>
                @endif

                @if (in_array($data['checkRequireAnswer'][config('settings.key.requireAnswer')], [
                    config('settings.require.name'),
                    config('settings.require.both'),
                    config('settings.require.loginWsm'),
                ]))
                    <th>{{ trans('user.name') }}</th>
                @endif

                @foreach ($data['questions'] as $question)
                    <th>{!! $question['content'] !!}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (count($data['results']))
                @foreach ($data['results'] as $result)
                    <tr>
                        <td>{{ $result['created_at'] }}</td>
                        @if (in_array($data['checkRequireAnswer'][config('settings.key.requireAnswer')], [
                            config('settings.require.email'),
                            config('settings.require.both'),
                            config('settings.require.loginWsm'),
                        ]))
                            <td>{{ $result['email'] }}</td>
                        @endif

                        @if (in_array($data['checkRequireAnswer'][config('settings.key.requireAnswer')], [
                            config('settings.require.name'),
                            config('settings.require.both'),
                            config('settings.require.loginWsm'),
                        ]))
                            <td>{{ $result['name'] }}</td>
                        @endif

                        @if (in_array($result['answer']['type'], [
                            config('survey.type_radio'),
                            config('survey.type_checkbox'),
                            config('survey.type_other_radio'),
                            config('survey.type_other_checkbox'),
                        ]))
                            <td>{{ cleanTextForExport($result['answer']['content']) }}</td>
                        @elseif ($result['answer']['type'] != config('survey.type_radio')
                            && $result['answer']['type'] != config('survey.type_checkbox'))
                                <td>{{ cleanTextForExport($result['content']) }}</td>
                        @endif
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>
</html>
