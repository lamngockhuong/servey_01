<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
@section('js-var')
    <script>
        var data = {
            defaultImg: '{{ asset(config('temp.image_default')) }}',
            hint: '{{ trans('home.url_hint') }}',
            msg : {
                yt : '{{ trans('home.msg.yt') }}',
                vm : '{{ trans('home.msg.vm') }}',
                img : '{{ trans('home.msg.img') }}',
                false : '{{ trans('home.msg.false') }}',
                timeout : '{{ trans('home.msg.time_out') }}',
                large: '{{ trans('home.msg.large') }}',
                notImg: '{{ trans('home.msg.not_img') }}',
                totalSize: '{{ trans('home.msg.total_err') }}'
            },
            validate : {
                required : '{{ trans('validation.msg.required') }}',
                remote : '{{ trans('validation.msg.remote') }}',
                email : '{{ trans('validation.msg.email') }}',
                url : '{{ trans('validation.msg.url') }}',
                date : '{{ trans('validation.msg.date') }}',
                dateISO : '{{ trans('validation.msg.dateISO') }}',
                number : '{{ trans('validation.msg.number') }}',
                digits : '{{ trans('validation.msg.digits') }}',
                creditcard : '{{ trans('validation.msg.creditcard') }}',
                equalTo : '{{ trans('validation.msg.equalTo') }}',
                maxlength : '{{ trans('validation.msg.maxlength') }}',
                minlength : '{{ trans('validation.msg.minlength') }}',
                rangelength : '{{ trans('validation.msg.rangelength') }}',
                range: '{{ trans('validation.msg.range') }}',
                max: '{{ trans('validation.msg.max') }}',
                min: '{{ trans('validation.msg.min') }}',
                tailmail: '{{ trans('validation.msg.tailmail') }}',
                invalid_mail: '{{ trans('validation.msg.invalid_mail') }}'
            },
        }
    </script>
@show
{{ Html::script(asset('/bower/jquery/dist/jquery.min.js')) }}
{{ Html::script(elixir('/js/app.js')) }}
{{ Html::script(elixir('/js/messages.js')) }}
{{ Html::script(asset('/bower/highcharts/highcharts.js')) }}
{{ Html::script(asset('/bower/highcharts/highcharts-3d.js')) }}
{{ Html::script(asset('/bower/highcharts/js/modules/exporting.js')) }}
{{ Html::script(asset('/bower/angularjs/angular.min.js')) }}
{{ Html::script(asset('/bower/typeahead.js/dist/typeahead.bundle.min.js')) }}
{{ Html::script(asset('/bower/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js')) }}
{{ Html::script(elixir('/user/js/step-wizard.js')) }}
{{ Html::script(asset('/bower/jquery-ui/jquery-ui.js')) }}
{{ Html::script(elixir('/user/js/jquery.wizard.js')) }}
{{ Html::script(elixir('/user/js/check.min.js')) }}
{{ Html::script(elixir('/user/js/validate.js')) }}
{{ Html::script(asset('/bower/jquery.placeholder/jquery.placeholder.js')) }}
{{ Html::script(elixir('/user/js/jquery.bxslider.min.js')) }}
{{ Html::script(asset('/bower/owl.carousel/dist/owl.carousel.min.js')) }}
{{ Html::script(elixir('/user/js/functions.js')) }}
{{ Html::script(asset('/bower/socket.io-client/dist/socket.io.js')) }}
{{ Html::script(asset('/bower/fancyBox/source/jquery.fancybox.pack.js')) }}
{{ Html::script(asset('/bower/fancyBox/source/helpers/jquery.fancybox-media.js')) }}
{{ Html::script(asset('/bower/moment/min/moment.min.js')) }}
{{ Html::script(asset('/bower/bootstrap/dist/js/bootstrap.min.js')) }}
{{ Html::script(asset('/bower/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')) }}
{{ Html::script(asset('/bower/ms-Dropdown/js/msdropdown/jquery.dd.min.js')) }}
{{ Html::script(asset('/bower/bootstrap-sweetalert/dist/sweetalert.min.js')) }}
{{ Html::script(elixir('/user/js/alert.js')) }}
{{ Html::script(elixir('/user/js/question.js')) }}
{{ Html::script(elixir('/user/js/component.js')) }}
{{ Html::script(elixir('/user/js/socket.js')) }}
{{ Html::script(elixir('/user/js/flipclock.min.js')) }}
