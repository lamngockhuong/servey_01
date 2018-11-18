jQuery.expr[':'].regex = function (elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels, '')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

$(document).ready(function() {
    var error = $('.data').attr('data-error');
    var notice = $('.data').attr('data-confirm');
    var email_confirm = $('.data').attr('data-email-invalid');
    var formatDate = $('.data').attr('data-format-datetime');

    function validateEmail(email) {
        var re = /^[a-zA-Z][a-zA-Z0-9_\.]{2,255}@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}$/;

        return re.test(email);
    }

    function validateTailmail(email) {
        var text = /^@[a-zA-Z0-9]{2,}(\.[a-zA-Z0-9]{2,4}){1,2}$/i;

        return text.test(email);
    }

    jQuery.validator.addMethod('moreThan30Minutes', function (value, element) {
        var today = new Date();
        var dateChoose = value;

        if (formatDate == 'DD-MM-YYYY hh:mm A') {
            dateChoose = dateChoose.split('-')[1] + '-' + dateChoose.split('-')[0] + dateChoose.substring(5);
        }

        var dealineTime = new Date(Date.parse(dateChoose));
        var validateTime = dealineTime.getTime() - today.getTime();

        if (!dealineTime.length && validateTime < 1800000) {
            return false;
        }

        return true;
    }, Lang.get('js.survey.more_than_30_minutes'));

    jQuery.validator.addMethod('afterStartTime', function (value, element) {
        var startTime = $('#starttime').val();

        if (!startTime.length) {
            return true;
        }

        var dateChoose = value;

        if (formatDate == 'DD-MM-YYYY hh:mm A') {
            startTime = startTime.split('-')[1] + '-' + startTime.split('-')[0] + startTime.substring(5);
            dateChoose = dateChoose.split('-')[1] + '-' + dateChoose.split('-')[0] + dateChoose.substring(5);
        }

        startTime = new Date(Date.parse(startTime));
        var dealineTime = new Date(Date.parse(dateChoose));
        var validateTime = dealineTime.getTime() - startTime.getTime();

        if (!dealineTime.length && validateTime <= 0) {
            return false;
        }

        return true;
    }, Lang.get('js.survey.after_start_time'));

    $.validator.addMethod('questionunique', function (value, element) {
        var parentForm = $(element).closest('form');
        var timeRepeated = 0;
        if (value.trim()) {
            $(parentForm.find('textarea:regex(name, ^txt-question\\[question\\]\\[.*\\])')).each(function () {
                if ($(this).val() === value) {
                    timeRepeated++;
                }
            });
        }

        return timeRepeated === 1 || timeRepeated === 0;

    }, Lang.get('js.survey.duplicate_question'));

    $.validator.addMethod('answersunique', function (value, element) {
        var parentForm = $(element).closest('li');
        var timeRepeated = 0;
        if (value.trim()) {
            $(parentForm.find('textarea:regex(name, ^txt-question\\[answers\\]\\[.*\\]\\[.*\\]\\[(1|2)\\])')).each(function () {
                if ($(this).val() === value) {
                    timeRepeated++;
                }
            });
        }

        return timeRepeated === 1 || timeRepeated === 0;

    }, Lang.get('js.survey.duplicate_answer'));

    var form = $('#survey_container #wrapped');
    form.validate({
        rules: {
            email: {
                required: true,
                email: true,
                maxlength: 255,
            },
            name: {
                required: true,
                maxlength: 255,
            },
            title: {
                required: true,
                maxlength: 255,
            },
            deadline: {
                moreThan30Minutes: true,
                afterStartTime: true,
            },
        },
    });

    $('#survey_container').wizard({
        stepsWrapper: "#middle-wizard",
        beforeForward: function( event, state ) {
            switch (state.stepIndex) {
                case 2: {
                    $('html, body').animate({scrollTop: 0}, 500);

                    if($('.data').attr('data-question') == 0) {
                        $('.create-question-validate')
                            .css('display', 'block')
                            .removeClass('fadeOutUp')
                            .addClass('animated fadeInDown');

                        return false;
                    }

                    $('textarea:regex(name, ^txt-question\\[question\\]\\[.*\\])').each(function () {
                        $(this).rules('add', {
                            required: true,
                            maxlength: 255,
                            questionunique: true,
                        });
                    });

                    $('textarea:regex(name, ^txt-question\\[answers\\]\\[.*\\]\\[.*\\]\\[(1|2)\\])').each(function () {
                        $(this).rules('add', {
                            required: true,
                            maxlength: 255,
                            answersunique: true,
                        });
                    });

                    break;
                }

                case 3: {
                    $('html, body').animate({scrollTop: 0}, 1000);
                    var flag = temp = true;
                    var c1 = $('#requirement-answer').is(':checked');
                    var c2 = $('.option-choose-answer').is(':checked');
                    var c3 = $('#limit-answer').is(':checked');
                    var c4 = $('.quantity-limit').val();
                    var c5 = isNaN(c4);
                    var c6 = $('#require-tail-email').is(':checked');
                    var c7 = $('.frm-tailmail').val();
                    var c8 = $('#reminder-periodically').is(':checked');
                    var c9 = $('.option-choose-reminder').is(':checked');
                    var c10 = $('.frm-tailreminder').val();
                    var tailMails = $('.frm-tailmail').tagsinput('items');
                    var maxLimit = $('.data').attr('data-max-limit');
                    tailMails.forEach(function (tailemail) {

                        if (!validateTailmail(tailemail)) {
                            temp = false;
                        }
                    });

                    if ((c1 && !c2) ){
                        $('.validate-requirement-answer')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        flag = false;
                    }

                    if ((c3 && !c4.length) || (c3 && c4.length && (c5 || (parseInt(c4) < 1 || parseInt(c4) > maxLimit)))) {
                        $('.validate-limit-answer')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        flag = false;
                    }

                    if (c6 && !c7.length) {
                        $('.validate-tailmail')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        $('.content-validate-tailmail').html(data.validate.tailmail);
                        flag = false;
                    }

                    if (c6 && c7.length && !temp) {
                        $('.validate-tailmail')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        $('.content-validate-tailmail').html(data.validate.invalid_mail);
                        flag = false;
                    }

                    if (c8 && !c9) {
                        $('.validate-reminder-periodically')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        flag = false;
                    }

                    if (c9 && !c10.length) {
                        $('.validate-reminder-periodically-time')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);
                        flag = false;
                    }

                    return flag;
                }

                case 4: {
                    var c8 = $('#reminder-periodically').is(':checked');
                    var emails = $('input:text[name=emails]').tagsinput('items');
                    var flag = true;

                    emails.forEach(function (email) {
                        if (!validateEmail(email)) {
                            flag = false;
                        }
                    });

                    if (emails.length != 0 && !flag) {
                        $('.validate-email').css('display', 'block')

                        return false;
                    }

                    if (c8 && !emails.length) {
                        $('.validate-exists-reminder-email')
                            .css('display', 'block')
                            .addClass('animated fadeInDown')
                            .delay(3000)
                            .slideUp(1000);

                        return false;
                    }

                    $('.validate-email').css('display', 'none');

                    return flag;
                }

                default: {
                    break;
                }
            }

            if (!form.valid()) {
                return false;
            }
        }
    });

    $(document).on('click', '.btn-save-setting', function() {
        var flag = temp = true;
        var c1 = $('#requirement-answer').is(':checked');
        var c2 = $('.option-choose-answer').is(':checked');
        var c3 = $('#limit-answer').is(':checked');
        var c4 = $('.quantity-limit').val();
        var c5 = isNaN(c4);
        var c6 = $('#require-tail-email').is(':checked');
        var c7 = $('.frm-tailmail').val();
        var c8 = $('#reminder-periodically').is(':checked');
        var c9 = $('.option-choose-reminder').is(':checked');
        var c10 = $('.frm-tailreminder').val();
        var tailMails = $('.frm-tailmail').tagsinput('items');
        var maxLimit = $('.data').attr('data-max-limit');
        tailMails.forEach(function (tailemail) {

            if (!validateTailmail(tailemail)) {
                temp = false;
            }
        });

        if ((c1 && !c2) ){
            $('.validate-requirement-answer')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            flag = false;
        }

        if ((c3 && !c4.length) || (c3 && c4.length && (c5 || (parseInt(c4) < 1) || parseInt(c4) > maxLimit))) {
            $('.validate-limit-answer')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            flag = false;
        }

        if (c6 && !c7.length) {
            $('.validate-tailmail')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            $('.content-validate-tailmail').html(data.validate.tailmail);
            flag = false;
        }

        if (c6 && c7.length && !temp) {
            $('.validate-tailmail')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            $('.content-validate-tailmail').html(data.validate.invalid_mail);
            flag = false;
        }

        if (c8 && !c9) {
            $('.validate-reminder-periodically')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            flag = false;
        }

        if (c9 && !c10.length) {
            $('.validate-reminder-periodically-time')
                .css('display', 'block')
                .addClass('animated fadeInDown')
                .delay(3000)
                .slideUp(1000);
            flag = false;
        }

        return flag;
    });

    $(document).on('click', '.btn-close-popup', function() {
        $('.popupBackground').fadeOut(500);
        $('.link-share').html('');
        $('.share-facebook').attr('data-href', '');
        setTimeout(function() {
            $('.popupBackground').css('display', 'none');
        }, 500);
    });

    $(document).on('click', '.image-type-option', function() {
        $('.create-question-validate')
            .removeClass('fadeInDown')
            .addClass('fadeOutUp');
        $('.create-question-validate').slideUp();
        setTimeout(function() {
            $('.create-question-validate').slideUp().css('display', 'none');
        }, 1000);
    });

    $(document).on('click', '.btn-send-mail', function() {
        var emails = $('.frm-mail-user').tagsinput('items');
        var input_email = $('.input-email').val();
        var flag = true;

        emails.forEach(function (email) {

            if (!validateEmail(email)) {
                flag = false;
            }
        });

        if ((emails.length != 0 && !flag)
            || !emails.length
            || !input_email.length
            || !validateEmail(input_email)
            ) {
            $('.validate-send-email').css('display', 'block');

            setTimeout(function() {
                $('.validate-send-email').slideUp(1000);
            }, 4000);

            return false;
        }

        return true;
    });

    $(document).on('click', '.submit-survey', function() {
        if(!$('.quantity-limit').val().length) {
            $('.setting-limit').remove();
        }
    });

    $('#tab-save-info').on('click', '.btn-save-survey', function() {
        var _tempTitle = $('.frm-title');

        return (!_tempTitle['length'] || _tempTitle['valid']());
    });
});
