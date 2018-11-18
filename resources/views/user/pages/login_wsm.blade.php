@extends('user.master')
@section('content')
    <div class="survey_container animated zoomIn wizard" novalidate="novalidate" >
        <div class="top-wizard-register">
            <strong class="tag-wizard-top">
                {{ trans('login.login') }}
            </strong>
            <div class="shadow"></div>
        </div>
        <div id="wizard-login middle-wizard" class="wizard-branch wizard-wrapper">
            <div class="step wizard-step current">
                <div class="container-login row">
                    <h3 class="label-header col-md-10 wizard-header col-md-offset-1">
                        {{ trans('login.require_login_wsm') }}
                    </h3>
                    <div class="col-md-8 col-md-offset-4">
                        <ul class="data-list social-bookmarks clearfix">
                            <li class="framgia">
                                <a href="{{ action('User\SocialAuthController@redirect', config('settings.framgia')) }}">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            @if (Session::has('message'))
                                <div class="alert alert-warning warning-login-register">
                                    <p>{{ Session::get('message') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-info-web')
    @include('user.blocks.info-web')
@endsection
