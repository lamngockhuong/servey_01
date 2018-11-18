<div class="step1 wizard-hidden step wizard-step current">
    <div class="row">
        <h3 class="label-header col-md-10 wizard-header">
            {{ trans('info.enter_info') }}
        </h3>
        <div class="col-md-6">
            <ul class="data-list">
                <li>
                    <div class="container-infor">
                        {!! Html::image(config('settings.image_path_system') . 'email1.png', '') !!}
                        {!! Form::text('email', (Auth::user() ? Auth::user()->email : old('email')), [
                            'id' => 'email',
                            'class' => 'form-control',
                            'placeholder' => trans('info.email'),
                            (auth()->check() && auth()->user()->email) ? 'readonly' : null,
                        ]) !!}
                    </div>
                </li>
                {!! Form::text('website', old('website'), [
                    'id' => 'website',
                ]) !!}
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="data-list">
                <li>
                    <div class="container-infor">
                        {!! Html::image(config('settings.image_path_system') . 'name.png', '') !!}
                        {!! Form::text('name', ((Auth::user()) ? Auth::user()->name : ''), [
                            'placeholder' => trans('info.name'),
                            'id' => 'name',
                            'class' => 'form-control',
                            (auth()->check() && auth()->user()->name) ? 'readonly' : null,
                        ]) !!}
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="frm-textarea container-infor">
            {!! Html::image(config('settings.image_path_system') . 'title1.png', '') !!}
            {!! Form::textarea('title', old('title'), [
                'class' => 'js-elasticArea form-control',
                'placeholder' => trans('info.title'),
            ]) !!}
        </div>
    </div>

    <div class="row">
        <div class="frm-textarea container-infor starttime-infor">
            {!! Html::image(config('settings.image_path_system') . 'date.png', '') !!}
            {!! Form::text('start_time', old('start_time'), [
                'placeholder' =>  trans('info.starttime'),
                'id' => 'starttime',
                'class' => 'frm-starttime datetimepicker form-control',
            ]) !!}
        </div>
    </div>

    <div class="row">
        <div class="frm-textarea container-infor dealine-infor">
            {!! Html::image(config('settings.image_path_system') . 'date.png', '') !!}
            {!! Form::text('deadline', old('deadline'), [
                'placeholder' =>  trans('info.duration'),
                'id' => 'deadline',
                'class' => 'frm-deadline datetimepicker form-control',
            ]) !!}
        </div>
    </div>

    <div class="row">
        <div class="frm-textarea container-infor">
            {!! Html::image(config('settings.image_path_system') . 'description.png', '') !!}
            {!! Form::textarea('description', old('description'), [
                'class' => 'js-elasticArea form-control',
                'placeholder' => trans('info.description'),
            ]) !!}
        </div>
    </div>
</div>
