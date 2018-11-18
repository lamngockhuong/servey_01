$(document).ready(function() {
    var error = $('.data').attr('data-error');
    var notice = $('.data').attr('data-confirm');
    var link = $('.data').attr('data-link');
    var email_confirm = $('.data').attr('data-email-invalid');
    var format_date = $('.data').attr('data-format-date');
    var format_datetime = $('.data').attr('data-format-datetime');
    var deadline = $('.frm-deadline').val();

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.8&appId=1853033838292892";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    $(document).on('mousedown', '.glyphicon.glyphicon-sort', function(){
        $('.container-add-question').addClass('sortable');
        $('.sortable').sortable();
        $('.sortable').disableSelection();
    });

    $('.alert-message').delay(5000).slideUp(300);

    $('.save-survey').on('click', function () {
        var surveyId = $(this).attr('survey-id');
        var url = $(this).attr('data-url');
        var feature = $(this).attr('feature');
        var userId = $(this).attr('user-id');
        var answer = $('#wrapped').serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;

            return obj;
        });

        $.post(
            url,
            {
                'surveyId': surveyId,
                'answer': answer,
                'feature': feature,
                'userId': userId,
            },
            function (response){
                if (response.success) {
                    $('.save-message-fail').css('display', 'none');
                    $('.save-message-success').css('display', 'block');
                    $('.save-message-success').html(response.message);
                } else {
                    $('.save-message-success').css('display', 'none');
                    $('.save-message-fail').css('display', 'block');
                    $('.save-message-fail').html(response.message);
                }

                $('.alert-message').delay(5000).slideUp(500);
        });
    });

    function copyToClipboard(element) {
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val($.trim($(element).text())).select();
        document.execCommand('copy');
        $temp.remove();
    }

    $(document).on('click', '.btn-copy-link', function() {
        copyToClipboard('.link-share');
    });

    $(document).on('click', '.btn-copy-link-complete', function() {
        copyToClipboard('.tag-link-answer');
    });

    $('.show-survey').on('click', function () {
        var url = $(this).attr('data-url');
        var surveyId = $(this).attr('survey-id');

        $.get(
            url,
            {
                'surveyId': surveyId,
            },
            function (response) {
                if (response.success) {
                    $('.save-message-fail').css('display', 'none');
                    $('.save-message-success').css('display', 'block');
                    $('.save-message-success').html(response.message);
                    $('#container-survey').html(response.view);
                } else {
                    $('.save-message-success').css('display', 'none');
                    $('.save-message-fail').css('display', 'block');
                    $('.save-message-fail').html(response.message);
                }

                $('.alert-message').delay(5000).slideUp(500);
        });
    });

    $('.setting-limit').on('click', '.qtyplus', function() {
        var getNumber = parseInt($('.quantity-limit').val());

        if (!isNaN(getNumber)) {
            $('.quantity-limit').val(getNumber + 1);
        } else {
            $('.quantity-limit').val(1);
        }
    });

    $('.excel').on('click', '.export-excel', function() {
        $('.exportExcel').click();
    });

    $('.excel').on('click', '.export-PDF', function() {
        $('.exportPDF').click();
    });

    $('.setting-limit').on('click', '.qtyminus', function() {
        var getNumber = parseInt($('.quantity-limit').val());

        if (!isNaN(getNumber) && getNumber > 1) {
            $('.quantity-limit').val(getNumber - 1);
        } else {
            $('.quantity-limit').val(1);
        }
    });

    $('.container-setting').on('click', '#reminder-periodically', function () {
        $('.setting-reminder').toggle(1200);
        $('.option-choose-reminder.input-radio').prop('checked', false);
        $('.tail-reminder').slideUp();
        $('.frm-tailreminder').val('');
    });

    $('.container-setting').on('click', '#requirement-answer', function() {
        $('.setting-requirement').toggle(1200);
        $('.option-choose-answer.input-radio').prop('checked', false);
        $('#require-tail-email').prop('checked', false);
        $('#require-oneTime').prop('checked', false);
        $('.div-option-require').slideUp();
        $('.tail-email').slideUp();
        $('.frm-tailmail').tagsinput('removeAll');
    });

    $('.container-setting').on('click', '#limit-answer', function() {
        $('.setting-limit').toggle(1000);
        $('.quantity-limit').val('');
    });

    $(document).on('click', '.tab-choose', function() {
        var value = $(this).val();
        $('.content-' + value).css('display', 'block');

        for (var i = 1; i <= 4; i++) {
            if (i != value) {
                $('.content-' + i).css('display', 'none');
            }
        }
    });

    $('#countries').on('change', function () {
        var url = $(this).attr('data-url');
        var lang = $(this).val();

        $.get(
            url,
            {
                'locale': lang,
            },
            function (response) {
                if (response.success) {
                    window.location.href = response.urlBack;
                } else {
                    var data = {
                        message: error,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
        });
    });

    $(document).on('click', '.submit-answer', function() {
        var flag = true;

        $('.box-orther').each(function () {
            if(!$(this).val()) {
                flag = false;
                $(this).siblings('.error').fadeIn();
            }

            setTimeout(function() {
                $('.error').fadeOut();
            }, 4000);
        })

        if ($('input[name=name-answer]').length) {
            var username  = $('input[name=name-answer]').val();

            if (!username) {
                $('.require-name').fadeIn();

                setTimeout(function() {
                    $('.require-name').fadeOut();
                }, 4000);

                flag = false;
            }
        }

        var optionSetting = $('.required-user').attr('data-option-setting').val();

        if ($('input[name=email-answer]').length && optionSetting != 4) {
            var mailUser  = $('input[name=email-answer]').val();

            if (mailUser) {
                var tailMail = $('.required-user').attr('data-require').split(',');
                var mailUser = '@' + mailUser.split('@')[1];

                if (jQuery.inArray(mailUser, tailMail) != -1) {
                    return flag;
                }

                $('.validate-email').fadeIn();

                setTimeout(function() {
                    $('.validate-email').fadeOut();
                }, 4000);

                flag = false;
            } else {
                $('.require-email').fadeIn();

                setTimeout(function() {
                    $('.require-email').fadeOut();
                }, 4000);

                flag = false;
            }
        }

        return flag;
    });

    $('input[type=submit]').prop('disabled', false);

    $(document).on('click', '.active-answer', function() {
        $('.container-list-answer').fadeIn();
        $('.container-list-result').fadeOut();
    });

    $(document).on('click', '.active-result', function() {
        $('.container-list-result').fadeIn();
        $('.container-list-answer').fadeOut();
    });

    $('.image-type-option').hover(
        function() {
            $(this).siblings('span').css('display', 'block');
        }, function() {
            $(this).siblings('span').css('display', 'none');
        }
    );

    $('#countries').msDropDown();

    setTimeout(function() {
        $('.complete-image').addClass('jello');
    }, 1000);

    $('#middle-wizard').on('click', '.choose-image', function() {
        $('.button-file-hidden').click();
    });

    $(document).on('click', '.tag-send-email', function() {
        $('.popupBackground').css('display', 'block');
        var url = $(this).attr('data-url');
        var link = $(this).attr('data-link');
        var type = $(this).attr('data-type');
        $('.frm-submit-mail').attr('action', url);
        $('.share-facebook').attr('data-href', link);
        $('.link-share').html(link);

        if (type == 1) {
            $('.share-link-public').fadeIn();
        }
    });

    $('.detail-survey').on('click', '.btn-close-survey', function(event, object) {
        if (!object) {
            var data = {
                message: $(this).data('message-confirm'),
                cancelText: Lang.get('js.button.cancel'),
                confirmText: Lang.get('js.button.yes')
            };
            confirmWarning(event, '.detail-survey .btn-close-survey', data);
        } else {
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                type: 'POST',
                async: false,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        $('.show-message').html(`<div class="alert col-md-4 col-md-offset-4
                            animated fadeInDown alert-danger alert-message">${response.message}</div >`);
                    }
                }
            });
        }
    });

    function validateTimeDeadline(value) {
        var today = new Date();
        var dateChoose = value;
        var formatDate = $('.data').attr('data-format-datetime');

        if (formatDate == 'DD-MM-YYYY hh:mm A') {
            dateChoose = dateChoose.split('-')[1] + '-' + dateChoose.split('-')[0] + dateChoose.substring(5);
        }

        var dealineTime = new Date(Date.parse(dateChoose));
        var validateTime = dealineTime.getTime() - today.getTime();

        if (!dealineTime.length && validateTime < 1800000) {
            return false;
        }

        return true;
    }

    $('.detail-survey').on('click', '.btn-open-survey', function (event, object) {
        var deadlineDisabled = $('#deadline').is(':disabled');

        if (!deadlineDisabled && !validateTimeDeadline($('#deadline').val())) {
            $('.show-message').html(`<div class="alert col-md-4 col-md-offset-4
            animated fadeInDown alert-danger alert-message">${Lang.get('js.survey.save_deadline_fail')}</div >`);
            $('#deadline').focus();
            return;
        }

        if (!object) {
            var data = {
                message: $(this).data('message-confirm'),
                cancelText: Lang.get('js.button.cancel'),
                confirmText: Lang.get('js.button.yes')
            };
            confirmWarning(event, '.detail-survey .btn-open-survey', data);
        } else {
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                data: {
                    deadline: $('#deadline').val(),
                    change_deadline: !deadlineDisabled,
                },
                type: 'POST',
                async: false,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        $('.show-message').html(`<div class="alert col-md-4 col-md-offset-4
                            animated fadeInDown alert-danger alert-message">${response.message}</div >`);
                    }
                }
            });
        }
    });

    $('.detail-survey').on('click', '.btn-remove-survey', function(event, object) {
        if (!object) {
            var data = {
                message: notice,
                cancelText: Lang.get('js.button.cancel'),
                confirmText: Lang.get('js.button.yes')
            };
            confirmDanger(event, '.detail-survey .btn-remove-survey', data);
        } else {
            var url = $(this).attr('data-url');
            var idSurvey = $(this).attr('id-survey');
            var redirect = $(this).attr('redirect');
            $.post(
                url,
                {
                    'idSurvey': idSurvey,
                },
                function(response) {

                    if (response.success) {
                        window.location.href = redirect;
                    } else {
                        $('.show-message').html(`<div class="alert col-md-4 col-md-offset-4
                            animated fadeInDown alert-danger alert-message">${response.error}</div >`);
                    }
                }
            );
        }
    });

    $('.detail-survey').on('click', '.btn-duplicate-survey', function (event, object) {
        if (!object) {
            var data = {
                message: $(this).data('message-confirm'),
                cancelText: Lang.get('js.button.cancel'),
                confirmText: Lang.get('js.button.yes')
            };
            confirmInfo(event, '.detail-survey .btn-duplicate-survey', data);
        } else {
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                type: 'POST',
                async: false,
                success: function (response) {
                    if (response.success) {
                        window.open(response.url, '_blank');
                    } else {
                        $('.show-message').html(`<div class="alert col-md-4 col-md-offset-4
                            animated fadeInDown alert-danger alert-message">${response.message}</div >`);
                    }
                }
            });
        }
    });

    $('.container-setting').on('click', '#require-tail-email', function() {
        if ($(this).prop('checked')) {
            $('.tail-email').slideDown();
        } else {
            $('.tail-email').slideUp();
            $('.frm-tailmail').tagsinput('removeAll');
        }
    });

    $('.container-setting').on('click', '.option-choose-reminder', function () {
        $('.tail-reminder').slideDown();
    });

    $('.container-setting').on('click', '#reminder-by-week', function () {
        $('.frm-tailreminder').val(nextTime('week'));
    });

    $('.container-setting').on('click', '#reminder-by-month', function () {
        $('.frm-tailreminder').val(nextTime('month'));
    });

    $('.container-setting').on('click', '#reminder-by-quarter', function () {
        $('.frm-tailreminder').val(nextTime('quarter'));
    });

    function nextTime($type) {
        var startTime = $('#starttime').val();
        var nextTime = new Date();

        if (startTime.length) {
            startTime = startTime.split('-')[1] + '-' + startTime.split('-')[0] + startTime.substring(5);
            nextTime = new Date(Date.parse(startTime));
        }

        switch ($type) {
            case 'week':
                nextTime.setDate(nextTime.getDate() + 7);
                break;
            case 'month':
                nextTime.setMonth(nextTime.getMonth() + 1);
                break;
            case 'quarter':
                nextTime.setMonth(nextTime.getMonth() + 3);
                break;
            default:
                break;
        }

        nextTime = moment(nextTime).format('DD-MM-YYYY hh:mm A');

        return nextTime;
    }

    $('#wrapped-update').submit(function() {
       var values = $(this).serializeArray();

        if (values.length == 5) {
           $('.create-question-validate').addClass('animated fadeInDown').css('display', 'block');

           return false;
        }

        var _temp = $('.update-content-survey').find('input[type="text"]');

        return (!_temp['length'] || _temp['valid']());
    });

    $('.container-setting').on('change', '.option-choose-answer', function() {
        if ($(this).val() == 1 || $(this).val() == 3) {
            $('.div-option-require').slideDown();
        } else {
            $('.div-option-require').slideUp();
            $('.tail-email').slideUp();
            $('.frm-tailmail').val('');
            $('#require-tail-email').prop('checked', false);
            $('#require-oneTime').prop('checked', false);
            $('.frm-tailmail').tagsinput('removeAll');
        }
    });

    $('.datetimepicker').datetimepicker({
        format: format_datetime
    });

    $('.frm-deadline').datetimepicker({
        format: format_datetime,
        defaultDate: deadline,
    });

    $('.frm-date-2').datetimepicker({
        format: format_date
    });

    $('.frm-datepicker').datetimepicker({
        format: format_date
    });

    $('.frm-time').datetimepicker({
        format: 'LT'
    });

    $(document).on('click', '.submit-survey.submit', function() {
        $('.loader').show();
    });

    $(window).bind('load', function() {
        $('.loader').fadeOut('slow');
    });

    $(document).on('click', '.view-result', function() {
        window.location.href = $(this).attr('data-url');
    });

    $(document).on('click', '.menu-wizard', function() {
        $('.menu-wizard').removeClass('active-menu');
        $(this).addClass('active-menu');
    });

    $('.alert-message').delay(3000).slideUp(300);

    $('.tab-save-info').submit(function() {
        var _tempTitle = $('.frm-title');

        return (!_tempTitle['length'] || _tempTitle['valid']());
    });

    $(document).on('click', '.show-question-history', function() {
        $(this).addClass('hide-question-history');
        $(this).removeClass('show-question-history');
        $('.question-updated').slideToggle("slow");
        $('.answer-updated').slideToggle("slow");
        $(this).children().addClass('glyphicon-eye-close');
        $(this).children().removeClass('glyphicon-eye-open');
    });

    $(document).on('click', '.hide-question-history', function() {
        $(this).removeClass('hide-question-history');
        $(this).addClass('show-question-history');
        $('.question-updated').slideToggle("slow");
        $('.answer-updated').slideToggle("slow");
        $(this).children().removeClass('glyphicon-eye-close');
        $(this).children().addClass('glyphicon-eye-open');
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        var id = $('.tab-pane.active').attr('id');
        var url = $(this).attr('href');
        getAjax(id, url, page);
    });

    function getAjax(id, url, page) {
        $.get(
            url,
            {
                viewId: id
            },
            function(response) {
                if (response.success) {
                    $('#' + id).html(response.view);
                    location.hash = page;
                } else {
                    location.href = url.split('?page=')[0];
                    var data = {
                        message: response.messageFail,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
        });
    }

    $('#updateInfo').on('change', function() {
        $.validator.addMethod('regex', function(value, element, regexpr) {
            return regexpr.test(value);
        }, $('#updateInfo').attr('transEmailError'));

        $.validator.addMethod('filesize', function(value, element, param) {
            // param = size (en bytes)
            // element = element to validate (<input>)
            // value = value of the element (file name)
            return this.optional(element) || (element.files[0].size / 1024 <= param)
        });

        $('#updateInfo').validate({
            rules: {
                email: {
                    regex: /^[a-zA-Z][a-zA-Z0-9_\.]{2,255}@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}$/,
                    maxlength: 255
                },
                name: {
                    maxlength: 255,
                    required: true
                },
                password: {
                    minlength: 6
                },
                password_confirmation: {
                    minlength: 6,
                    equalTo : "#password"
                },
                imageUser: {
                    filesize: 2048,
                    extension: 'bmp',
                    type: 'image/jpeg,image/png,image/gif,image/x-ms-bmp',
                    message: $('#updateInfo').attr('transFileError')
                },
                address: {
                    maxlength: 255
                },
                phone: {
                    number: true
                }
            }
        });
    });

    $('#registerUser').on('change', function() {
        $.validator.addMethod('regex', function(value, element, regexpr) {
            return regexpr.test(value);
        }, $('#registerUser').attr('transEmailError'));

        $.validator.addMethod('filesize', function(value, element, param) {
            /**
            * param = size (en bytes)
            * element = element to validate (<input>)
            * value = value of the element (file name)
            */
            return this.optional(element) || (element.files[0].size / 1024 <= param)
        });

        $('#registerUser').validate({
            rules: {
                email: {
                    required: true,
                    regex: /^[a-zA-Z][a-zA-Z0-9_\.]{2,255}@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}$/,
                    maxlength: 255
                },
                name: {
                    maxlength: 255,
                    required: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirmation: {
                    required: true,
                    minlength: 6,
                    equalTo : "#password"
                },
                image: {
                    filesize: 2048,
                    extension: 'bmp',
                    type: 'image/jpeg,image/png,image/gif,image/x-ms-bmp',
                    message: $('#registerUser').attr('transFileError')
                }
            }
        });
    });

    $('.tag-edit-link-survey').on('click', function () {
        $('.show-public-link').addClass('hidden');
        $('.form-edit-link-survey').removeClass('hidden');
        $('#public-link-survey').focus().val($('#public-link-survey').val());
    });

    $('#bt-cancel-edit-link').on('click', function () {
        $('.form-edit-link-survey').addClass('hidden');
        $('.show-public-link').removeClass('hidden');
    });

    $('#set-link-by-slug').on('click', function () {
        var title = $('#title').val();
        $('#public-link-survey').val(toSlug(title));
    })

    function toSlug(str)
    {
        str = str.toLowerCase();
        str = str.replace(/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/g, 'a');
        str = str.replace(/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/g, 'e');
        str = str.replace(/(ì|í|ị|ỉ|ĩ)/g, 'i');
        str = str.replace(/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/g, 'o');
        str = str.replace(/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/g, 'u');
        str = str.replace(/(ỳ|ý|ỵ|ỷ|ỹ)/g, 'y');
        str = str.replace(/(đ)/g, 'd');
        str = str.replace(/([^0-9a-z-\s])/g, '');
        str = str.replace(/(\s+)/g, '-');
        str = str.replace(/^-+/g, '');
        str = str.replace(/-+$/g, '');

        return str;
    }

    $('#public-link-survey').keyup(function () {
        var token = toSlug($(this).val());
        var url = $(this).attr('verify-url');
        var pre_token = $('#public-link-survey').attr('pre-token');

        if (token == pre_token) {
            $('#error-link').addClass('hidden');
            $('#correct-link').removeClass('hidden');
        } else {
            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'json',
                data: {
                    token: token,
                },
                success: function (data) {
                    if (!data) {
                        $('#error-link').addClass('hidden');
                        $('#correct-link').removeClass('hidden');
                    } else {
                        $('#correct-link').addClass('hidden');
                        $('#error-link').removeClass('hidden');
                    }
                }
            });
        }
    });

    $('#bt-update-link-survey').on('click', function (event, object) {
        if (!object) {
            var data = {
                message: Lang.get('js.survey.update_survey_link'),
                cancelText: Lang.get('js.button.cancel'),
                confirmText: Lang.get('js.button.yes')
            };
            confirmWarning(event, '#bt-update-link-survey', data);
        } else {
            var token = toSlug($('#public-link-survey').val());
            var pre_token = $('#public-link-survey').attr('pre-token');

            if (token == pre_token) {
                $('.form-edit-link-survey').addClass('hidden');
                $('.show-public-link').removeClass('hidden');
            } else {
                var survey_id = $(this).attr('id-survey');
                var url = $(this).attr('data-url');
                $.ajax({
                    type: 'POST',
                    url: url,
                    dataType: 'json',
                    data: {
                        survey_id: survey_id,
                        token: token,
                    },
                    success: function (data) {
                        $('.form-edit-link-survey').addClass('hidden');
                        $('.public-link-survey').text(data);
                        $('.public-link-survey').attr('href', data);
                        $('#public-link-survey').attr('pre-token', token);
                        $('.show-public-link').removeClass('hidden');
                    },
                    error: function (data) {
                        $('#token-link-messages').removeClass('hidden');
                        $('#token-link-messages').text(data.responseJSON['token'][0]);
                    }
                });
            }
        }
    });

    var surveyId = $('.deadline-survey').attr('data-survey');
    var url = $('.deadline-survey').attr('data-url');

    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        data: {
            survey_id: surveyId,
        },
        success: function (data) {
            if (data) {
                var clock = $('.countdown-deadline-survey').FlipClock(data, {
                    clockFace: 'DailyCounter',
                    countdown: true,
                });
            } else {
                $('.deadline-survey').addClass('hidden');
            }
        }
    });

    $('.bootstrap-tagsinput input[type=text]').bind('click keyup', function(e) {
        var url = $('#invitation-email').data('url');
        var keyword = $(this).val();
        var emails = $('input:text[name=emails]').tagsinput('items');

        $.ajax({
            type:'POST',
            url: url,
            dataType: 'json',
            data: {
                keyword: keyword,
                emails: emails,
            },
            success: function (users) {
                $('.box-suggestion-email').removeClass('hidden');
                $('.box-suggestion-email').empty();

                users.data.forEach(user => {
                    $('.box-suggestion-email').append(`
                        <li class="email-suggest-item"><i class="fa fa-envelope"></i> ${user.email}</li>
                    `)
                });

                $('.email-suggest-item').on('mouseover', function () {
                    $('.bootstrap-tagsinput input[type=text]').focus().val($(this).text().trim());
                });

                $('.email-suggest-item').on('mouseout', function () {
                    $('.bootstrap-tagsinput input[type=text]').val('').focus();
                });

                $(document).click(function () {
                    $('.box-suggestion-email').addClass('hidden');
                });
            }
        })
    });
});
