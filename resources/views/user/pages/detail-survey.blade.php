@extends('user.master')
@section('content')
    <div id="survey_container" class="survey_container animated zoomIn wizard" novalidate="novalidate">
        <div id="top-wizard" class="top-wizard{{ $survey->id }}">
            <div class="container-menu-wizard row">
                <div class="menu-wizard{{ $survey->id }} menu-wizard active-answer active-menu">
                    {{ trans('home.detail_survey') }}
                </div>
            </div>
            <div class="shadow"></div>
        </div>
        <div class="container-list-answer">
            <div class="del-survey{{ $survey->id }} del-survey animated zoomIn">
                {!! Html::image(config('settings.image_system') . 'remove.png', '', [
                    'class' => 'img-remove-survey',
                ]) !!}
                 <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="alert alert-danger warning-center">
                            <span class="glyphicon glyphicon-alert"></span>
                            {{ trans('result.sorry_user') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="middel-content-detail detail-survey{{ $survey->id }} wizard-branch wizard-wrapper">
                <div class="row show-message">
                    @if (Session::has('message'))
                        <div class="alert col-md-4 col-md-offset-4 animated fadeInDown alert-info alert-message">
                            {!! Session::get('message') !!}
                        </div>
                    @endif
                    @if (Session::has('message-fail'))
                        <div class="alert col-md-4 col-md-offset-4 animated fadeInDown alert-danger alert-message">
                            {!! Session::get('message-fail') !!}
                        </div>
                    @endif
                </div>
                <div class="tab-detail-survey tab-class">
                    <section class="tabs">
                        {{ Form::radio('radio-set', config('temp.radio.label_info'), '', [
                            'id' => 'tab-1',
                            'class' => 'tab-choose tab-selector-1',
                            'checked' => 'checked',
                        ]) }}
                        {{ Form::label('tab-1', trans('survey.information'), [
                            'class' => 'label tab-label-1',
                        ]) }}
                        {{ Form::radio('radio-set', config('temp.radio.label_setting'), '', [
                            'id' => 'tab-2',
                            'class' => 'tab-choose tab-selector-2',
                        ]) }}
                        {{ Form::label('tab-2', trans('survey.setting'), [
                            'class' => 'label tab-label-2',
                        ]) }}
                        {{ Form::radio('radio-set', config('temp.radio.label_result'), '', [
                            'id' => 'tab-3',
                            'class' => 'tab-choose tab-selector-3',
                        ]) }}
                        {{ Form::label('tab-3', trans('survey.result'), [
                            'class' => 'label tab-label-3',
                        ]) }}
                        {{ Form::radio('radio-set', config('temp.radio.label_detail_result'), '', [
                            'id' => 'tab-4',
                            'class' => 'tab-choose tab-selector-4',
                        ]) }}
                        {{ Form::label('tab-4', trans('survey.update_question'), [
                            'class' => 'label tab-label-4',
                        ]) }}
                        <div class="clear-shadow"></div>
                        <div class="content">
                            <div class="content-1">
                                @include('user.component.tab-info')
                            </div>
                            <div class="content-2 div-hidden">
                                @include('user.component.tab-setting')
                            </div>
                            <div class="content-3 div-hidden">
                                 <div class="div-result-survey">
                                    <div class="div-table-list">
                                        <div class="table-list-row row">
                                            <div class="col-md-3">
                                                <ul class="nav nav-tabs tabs-left sideways">
                                                    <li class=" active">
                                                        <a href="#home-v" data-toggle="tab" style="">
                                                            <span class="glyphicon glyphicon-adjust"></span>
                                                            {{  trans('survey.overview') }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#profile-v" data-toggle="tab">
                                                            <span class="glyphicon glyphicon-asterisk"></span>
                                                            {{  trans('survey.see_detail') }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#messages-v" data-toggle="tab">
                                                            <span class="glyphicon glyphicon-list"></span>
                                                            {{ trans('survey.users_anwser') }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#settings-v" data-toggle="tab">
                                                            <span class="glyphicon glyphicon-export"></span>
                                                            {{ trans('survey.export') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="tab-content">
                                                    <div class="tab-chart{{ $survey->id }} tab-pane active" id="home-v">
                                                        @include('user.result.chart')
                                                    </div>
                                                    <div class="tab-detail-result{{ $survey->id }} tab-pane" id="profile-v">
                                                        @include('user.result.detail-result')
                                                    </div>
                                                    <div class="tab-users-answer{{ $survey->id }} tab-pane" id="messages-v">
                                                        @include('user.result.users-answer')
                                                    </div>
                                                    <div class="tab-pane" id="settings-v">
                                                        <div class="excel row">
                                                            <div class="export-excel col-md-4">
                                                                {!! Html::image(config('settings.image_path_system') . 'excel.png', '') !!}
                                                                <span>
                                                                    {{ trans('survey.exportExecl') }}
                                                                </span>
                                                            </div>
                                                            <div class="col-md-1"></div>
                                                        </div>
                                                        {!! Form::open(['action' => [
                                                                'User\ExportController@export',
                                                                'id' => $survey->id,
                                                                'type'=>'excel'
                                                            ],
                                                            'method' => 'GET',
                                                        ]) !!}
                                                            {!! Form::submit('', [
                                                                'class' => 'div-hidden exportExcel',
                                                            ]) !!}
                                                        {!! Form::close() !!}
                                                        {!! Form::open(['action' => [
                                                                'User\ExportController@export',
                                                                'id' => $survey->id,
                                                                'type'=>'PDF'
                                                            ],
                                                            'method' => 'GET',
                                                        ]) !!}
                                                            {!! Form::submit('', [
                                                                'class' => 'div-hidden exportPDF',
                                                            ]) !!}
                                                        {!! Form::close() !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content-4 div-hidden">
                                @include('user.component.tab-update-survey')
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div id="bottom-wizard bottom-wizard-anwser">
                <div class="shadow-bottom"></div>
            </div>
        </div>
    </div>
@endsection
