$(document).ready(function() {
    var error = $('.data').attr('data-error');
    var sumImageSize = 0;
    var obj = {};
    var arrayQuestion = [];
    var arrayAnswer = [];
    var arrayImageAnswer = [];
    var arrayImageQuestion = [];
    var maxFileSize = 1000;
    var maxTotalSize = 8000;
    var slts = { // selectors
        msg: '.modal-message',
        preview: '.img-pre-option',
        url : '.photo-tb-url-txt',
        upload: '.photo-tb-upload',
        link: '.add-image-by-link',
        modal: '#modal-id',
        q: { // question
            open: '.glyphicon-picture',
            image: '.image-question',
            file: '.fileImg',
            video: '.question-video-url',
            imgU: '.question-img-url',
            imgDiv: '.content-image-question',
        },
        a: { // answer
            open: '.picture-answer',
            image: '.image-answer',
            file: '.fileImgAnswer',
            video: '.answer-video-url',
            imgU: '.answer-img-url',
            imgDiv: '.content-image-answer',
        },
    };

    function elasticArea() {
        $('.js-elasticArea').each(function(index, element) {
            var elasticElement = element,
                $elasticElement = $(element),
                initialHeight = initialHeight || $elasticElement.height(),
                delta = parseInt($elasticElement.css('paddingBottom')) + parseInt($elasticElement.css('paddingTop')) || 0,
                resize = function() {
                    $elasticElement.height(initialHeight);
                    $elasticElement.height(elasticElement.scrollHeight - delta);
                };

            $elasticElement.on('input change keyup', resize);
            resize();
        });
    };

    (function() {
        elasticArea();

        $('.container-add-question').off('focus').on('focus', '.js-elasticArea', function () {
            var elasticElement = $(this).get(0),
                $elasticElement = $(this),
                initialHeight = 0,
                delta = parseInt($elasticElement.css('paddingBottom')) + parseInt($elasticElement.css('paddingTop')) || 0,
                resize = function () {
                    $elasticElement.height(initialHeight);
                    $elasticElement.height(elasticElement.scrollHeight - delta);
                };

            $elasticElement.on('input change keyup', resize);
            resize();
        });
    })();

    function addAnwser($this) {
        var url = $this.attr('url');
        var type = $this.attr('typeId');
        var number = parseInt($($this).attr('id-as'));
        var trash = parseInt($('.question' + number).attr('trash'));
        var numberAnswer = (parseInt($('.question' + number).attr('temp-qs')) + 1);
        var otherQuestionTypeId = $this.parent('div').find('span:regex(class, (other))').attr('typeid');
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                'number': number,
                'numberAnswer': numberAnswer,
            },
            dataType: 'json',
            async: false,
            success: function(response) {
                 if (response.success) {
                    $('.temp-other' + number +':first').before(response.data);
                    $('.question' + number).attr('temp-qs', numberAnswer);
                    $('.question' + number).attr('trash', trash + 1);
                    $('.question' + number)
                        .find('textarea:regex(name, ^txt-question\\[answers\\]\\[.*\\]\\[.*\\]\\[' + otherQuestionTypeId + '\\])')
                        .attr('name', 'txt-question[answers][' + number + '][' + (numberAnswer + 1) + '][' + otherQuestionTypeId + '])');
                } else {
                    var data = {
                        message: error,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
            }
        });
    }

    function addOtherButton($this, class_temp) {
        var number = parseInt($this.parent().parent().find(class_temp).attr('id-as'));
        var type = $this.attr('typeId');
        var url = $this.attr('url');
        $.post(
            url,
            {
                'number': number,
            },
            function(response) {
                if (response.success) {
                    var trash = parseInt($('.question' + number).attr('trash'));
                    $('.question' + number).attr('trash', trash + 1);
                    $('.temp-other' + number + ':first').before(response.data);
                    $('.question' + number)
                        .find('textarea:regex(name, ^txt-question\\[answers\\]\\[.*\\]\\[.*\\]\\[' + type + '\\])')
                        .attr('name', 'txt-question[answers][' + number + '][' + trash + '][' + type + '])');
                } else {
                    var data = {
                        message: error,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
        });
        $this.hide();
    }

    function addQuestion($this) {
        var number = parseInt($('.data').attr('data-number')) + 1;
        var number_qs = parseInt($('.data').attr('data-question')) + 1;
        var type = $this.attr('typeId');
        var url = $this.attr('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                'number': number,
                'type': type,
            },
            dataType: 'json',
            async: false,
            success: function(response) {
                 if (response.success) {
                    $('.hide').before(response.data);
                    $('.data').attr('data-number', number);
                    $('.data').attr('data-question', number_qs);
                    $('.div-finish').css('display', 'block');
                } else {
                     var data = {
                         message: error,
                         buttonText: Lang.get('js.button.ok'),
                     };
                     alertDanger(data);
                }
            }
        });
    }

    function checkTypeImage(input) {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'svg'];
        var fileName = input.name;
        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);

        if ($.inArray(fileNameExt. toLowerCase(), fileExtension) == -1){

           return false;
        }

        return true;
    }

    function readURL(input) {

        if (input.files && input.files[0]) {
            setPreviewImage('');
            var fileSize = input.files[0].size/1024;

            if (!checkTypeImage(input.files[0])) {
                showMessage(data.msg.notImg);
            } else if (fileSize > maxFileSize) {
                showMessage(data.msg.large);
            } else if (sumImageSize + fileSize > maxTotalSize) {
                showMessage(data.msg.totalSize);
            } else {
                var reader = new FileReader();
                reader.onload = function (e) {
                    setPreviewImage(e.target.result);
                }
                reader.readAsDataURL(input.files[0]);

                return fileSize;
            }
        }
    }

    function showMessage (message, type) {
        var m = $(slts.msg)
        m.text(message).show();

        if (type == 'success') {
            m.removeClass('text-danger').addClass('text-success');
        } else {
            m.removeClass('text-success').addClass('text-danger');
        }
    }

    function removeMessage () {
        $(slts.msg).text('').hide()
    }

    // validate image url
    function checkTimeLoadImage (e, t, i) {
        var o, i = i || 3e3,
            n = !1,
            r = new Image;
        r.onerror = r.onabort = function() {
            n || (clearTimeout(o), t('error'))
        }, r.onload = function() {
            n || (clearTimeout(o), t('success'))
        }, r.src = e, o = setTimeout(function() {
            n = !0, t('timeout')
        }, i)
    }

    // set preview image src
    function setPreviewImage(src) {
        $(slts.preview).attr('src', src);
    }

    // validate video url, support youtube and vimeo
    function validateVideoUrl(url) {
        url.match(/(http:\/\/|https:\/\/|)(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

        if (RegExp.$3.indexOf('youtu') > -1) {
            var type = 'youtube';
        } else if (RegExp.$3.indexOf('vimeo') > -1) {
            var type = 'vimeo';
        } else {
            return false;
        }

        return {
            type: type,
            id: RegExp.$6
        };
    }

    // reset file field
    function resetImageField (field) {
        $(field).wrap('<form>').closest('form').get(0).reset();
        $(field).unwrap();
    }

    function getVimeoThumb (id, callback) {
        $.getJSON('http://www.vimeo.com/api/v2/video/' + id + '.json?callback=?', {format: 'json'}, function(data) {
            callback(data[0].thumbnail_large);
        });
    }

    function addMediabyUrl (questionId) {
        var url = $(slts.url).val().trim();
        var returnData = { url: url };

        if (url == '') {
            returnData.type = 'error';
            showMessage(data.hint);
        } else {

            result = validateVideoUrl(url);

            if (result) { // is video url
                var src;
                returnData.type = 'video';

                if (result.type == 'youtube') {
                    src = 'https://img.youtube.com/vi/' + result.id + '/hqdefault.jpg'
                    setPreviewImage(src);
                    showMessage(data.msg.yt, 'success');
                    returnData.image = src;

                } else {
                    getVimeoThumb(result.id, function (data) {
                        setPreviewImage(data);
                    });
                    showMessage(data.msg.vm, 'success');
                    returnData.id = result.id;
                }
            } else {
                returnData.type = 'image';
                checkTimeLoadImage(url, function(result) {
                    if (result == 'success') { // is image url
                        setPreviewImage(url);
                        showMessage(data.msg.img, 'success');

                    } else if (result == 'error') { // is not image url
                        setPreviewImage('');
                        showMessage(data.msg.false);
                    } else { // timeout
                        setPreviewImage('');
                        showMessage(data.msg.timeout);
                    }
                });
            }
        }
        return returnData;
    }

    $(document).on('click', '.add-radio', function() {
        addAnwser($(this));
    });

    $('.package-question').on('click', '.add-checkbox', function() {
        addAnwser($(this));
    });

    $('.package-question').on('click', '.add-radio-other',  function() {
        var class_temp = '.add-radio';
        addOtherButton($(this), class_temp);
    });

    $('.package-question').on('click', '.add-checkbox-other', function() {
        var class_temp = '.add-checkbox';
        addOtherButton($(this), class_temp);
    });

    $('.package-question').on('click', '#radio-button', function() {
        addQuestion($(this));
    });

    $('.package-question').on('click', '#checkbox-button', function() {
        addQuestion($(this));
    });

    $('.package-question').on('click', '#text-button', function() {
        addQuestion($(this));
    });

    $('.package-question').on('click', '#time-button', function() {
        addQuestion($(this));
    });

    $('.package-question').on('click', '#date-button', function() {
        addQuestion($(this));
    });

    $(document).on('click', '.title-question', function() {
        $(this).children('.choose-action').fadeIn(1700);
        $('.title-question').css('box-shadow', '').css('border-radius', '');
        $(this).css('box-shadow', '0px 2px 10px rgba(119, 132, 130, 0.56)')
            .css('border-radius', '5px');
    });

    function clickYes (imageField, videoUrl, imgUrl, imgSrc, imgDiv, returnData, id) {
        $(slts.modal).modal('hide');

        if (returnData != null && returnData.type != 'error') {
            $(videoUrl + id, imgUrl + id).attr('value', '');


            if (returnData.type == 'video') {
                resetImageField(imageField + id);
                $(imgDiv + id).css('display', 'block');
                $(videoUrl + id).attr('value', returnData.url);
                if (returnData.image) { // is youtube video
                    $(imgSrc + id).attr('src', returnData.image);
                    $(imgUrl + id).attr('value', returnData.image);
                }
                if (returnData.id) { // is vimeo video
                    getVimeoThumb(returnData.id, function (data) {
                        $(imgSrc + id).attr('src', data);
                        $(imgUrl + id).attr('value', data);
                    });
                }
            } else if (returnData.type == 'image') {
                resetImageField(imageField + id);
                var src = returnData.url;
                checkTimeLoadImage(src, function(result) {
                    if (result == 'success') { // is image url
                        $(imgSrc + id).attr('src', src);
                        $(imgUrl + id).attr('value', src);
                        $(imgDiv + id).css('display', 'block');
                    }
                });
            } else if (returnData.type == 'upload') {
                $(imgSrc + id).attr('src', $(slts.preview).attr('src'));
                $(imgDiv + id).css('display', 'block');
                obj[id] = returnData.size;
                sumImageSize = 0;
                $.each(obj,function() {
                    sumImageSize += this;
                });
            }
        }
        returnData = null;
    }

    $(document).on('click', slts.q.open, function() {
        idQuestion = $(this).siblings('.glyphicon-trash').attr('id-question');
        var returnData = null;
        var oldImage = $(slts.q.image + idQuestion).attr('src');
        setPreviewImage(''), removeMessage();
        $(slts.url).val(null);
        $(slts.modal).modal().attr('data-id', idQuestion);

        if (oldImage != data.defaultImg) {
            setPreviewImage(oldImage);
        }

        $(slts.upload).unbind().on('click', function () {
            removeMessage();
            $(slts.q.file + idQuestion).click();
        });

        $(document).on('change', slts.q.file + idQuestion, function () {
            var check = readURL(this);

            if (check) {
                returnData = { type: 'upload', size: check };
            }
        });

        $(slts.link).unbind().on('click', function () {
            removeMessage(), setPreviewImage('');
            returnData = addMediabyUrl(idQuestion);
        })

        $('.btn-yes').unbind().on('click', function(e) {
            clickYes(
                slts.q.file,
                slts.q.video,
                slts.q.imgU,
                slts.q.image,
                slts.q.imgDiv,
                returnData,
                idQuestion
            );
        });
    });

    $(document).on('click', '.remove-image-question', function() {
        var idQuestion = $(this).attr('id-question');
        arrayImageQuestion.push(idQuestion);
        $(slts.q.imgDiv + idQuestion).fadeOut(500);
        resetImageField(slts.q.file + idQuestion);
        $(slts.q.imgU + idQuestion + ', .question-video-url' + idQuestion).attr('value', '');
        delete obj[idQuestion];
        setTimeout(function() {
            $(slts.q.image + idQuestion).attr('src', data.defaultImg);
            $(slts.q.imgDiv + idQuestion).css('display', 'none');
        }, 1000);
    });

    $('.package-question').on('click', '.btn-change-survey', function() {
        $('input[name=del-question]').val(arrayQuestion);
        $('input[name=del-answer]').val(arrayAnswer);
        $('input[name=del-question-image]').val(arrayImageQuestion);
        $('input[name=del-answer-image]').val(arrayImageAnswer);
    });

    $(document).on('click', '.show-multi-history', function() {
        var url = $(this).attr('data-url');
        var username = $(this).attr('data-username');

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                'username': username,
            },
            async: false,
            success: function(response) {
                if (response.success) {
                    $('body').css('overflow', 'hidden');
                    $('.popup-user-answer').css('display', 'block');
                    $('.popup-content-history').append(response.data);
                } else {
                    var data = {
                        message: error,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
            }
        });
    });

    $(document).on('click', '.hidden-result', function() {
        $('body').css('overflow', 'auto');
        $('.popup-user-answer').css('display', 'none');
        $('.popup-content-history').empty();
    });

    $(document).on('click', slts.a.open, function() {
        var idAnswer = $(this).siblings('.glyphicon-remove').attr('id-as');
        var returnData = null;
        var oldImage = $(slts.a.image + idAnswer).attr('src');
        setPreviewImage(''), removeMessage();
        $(slts.url).val(null);
        $(slts.modal).modal().attr('data-id', idAnswer);

        if (oldImage != data.defaultImg) {
            setPreviewImage(oldImage);
        }

        $(slts.upload).unbind().on('click', function () {
            removeMessage();
            $(slts.a.file + idAnswer).click();
        });

        $(document).on('change', slts.a.file + idAnswer, function () {
            var check = readURL(this);

            if (check) {
                returnData = { type: 'upload', size: check };
            }
        });

        $(slts.link).unbind().on('click', function () {
            removeMessage(), setPreviewImage('');
            returnData = addMediabyUrl(idAnswer);
        })

        $('.btn-yes').unbind().on('click', function(e) {
            clickYes(
                slts.a.file,
                slts.a.video,
                slts.a.imgU,
                slts.a.image,
                slts.a.imgDiv,
                returnData,
                idAnswer
            );
        });
    });

    $('.package-question').on('click', '.remove-image-answer', function() {
        var idAnswer = $(this).attr('id-answer');
        arrayImageAnswer.push($(this).attr('data-answerid'));
        $(slts.a.imgDiv + idAnswer).fadeOut(500);
        resetImageField(slts.a.file + idAnswer);
        $(slts.a.imgU + idAnswer + ', .answer-video-url' + idAnswer).attr('value', '');
        delete obj[idAnswer];

        setTimeout(function() {
            $(slts.a.image + idAnswer).attr('src', data.defaultImg);
            $(slts.a.imgDiv + idAnswer).css('display', 'none');
        }, 1000);
    });

    $('.package-question').on('click', '.btn-require-answer', function() {
        addQuestion($(this));
    });

    $('.package-question').on('click', '.glyphicon-trash', function() {
        var number_qs = parseInt($('.data').attr('data-question')) - 1;
        var idQuestion = $(this).attr('id-question');
        var sum = 0;
        arrayQuestion.push(idQuestion);

        if(number_qs == 0) {
            $('.div-finish').css('display', 'none');
        }

        delete obj[idQuestion];
        var objSize = Object.keys(obj).length;

        for (var i = 0; i < objSize; i++) {
            if (obj[idQuestion + i]) {
                delete obj[idQuestion + i];
            }
        }

        $('.data').attr('data-question', number_qs);
        $('.question' + idQuestion).removeClass('animate zoomIn');
        $('.question' + idQuestion).addClass('animate fadeOutDown');
        setTimeout(function() {
          $('.question' + idQuestion).remove();
        }, 1000);
    });

    $(document).on('change', '.button-file-hidden', function() {
         readURL(this, '.img-avatar');
    });

    $('.package-question').on('click', '.btn-remove-answer', function() {
        var number = parseInt($(this).attr('num'));
        var idAnswer = $(this).attr('id-as');
        var idDelete = $(this).attr('data-answerId');
        var trash = parseInt($('.question' + number).attr('trash'));

        if (trash > 2) {
            arrayAnswer.push(idDelete);
            delete obj[idAnswer];
            $('.question' + number).attr('trash', trash - 1);
            $('.clear-as' + idAnswer).remove();
            $('.qs-as' + idAnswer).remove();
        }
    });

    $('.package-question').on('click', '.remove-other', function() {
        var idQuestion = $(this).attr('id-qs');
        var trash = parseInt($('.question' + idQuestion).attr('trash'));
        var idDelete = $(this).attr('data-answerId');

        if (trash > 2) {
            arrayAnswer.push(idDelete);
            $('.question' + idQuestion).attr('trash', trash - 1);
            $('.temp-other' + idQuestion + ':last').remove();
            $('.answer-other' + idQuestion).remove();
            $('.other' + idQuestion).show();
        }
    });

    $(document).on('click', '.bt-action', function() {
        var url = $(this).attr('url');
        window.location = url;
    });

    $('.container-survey').on('click', '.option-add', function() {
        var url = $(this).attr('url');
        var temp_as = $(this).attr('temp-as');
        var temp_qs = $(this).attr('temp-qs');

        if ($(this).prop('checked')) {
            $.post(
                url,
                {
                    'idQuestion': temp_qs,
                    'idAnswer': temp_as,
                },
                function(response) {
                    if (response.success) {
                        $('.append-as' + temp_qs).html(response.data);
                        elasticArea();
                    } else {
                        var data = {
                            message: error,
                            buttonText: Lang.get('js.button.ok'),
                        };
                        alertDanger(data);
                    }
            });
        } else {
            $('.input' + temp_qs).remove();
        }
    });

    $(document).on('click', '.option-choose', function() {
        var temp_qs = $(this).attr('temp-qs');
        $('.input' + temp_qs).remove();
    });

    $('.image-frame').on('mouseover', function() {
        $(this).unbind().width($(this).find('.images').width() + 10);
    });

    // view image or video in survey
    $('.text').unbind().on('click', function() {
        var modal = $('#view-media');
        var mBody = modal.find('.modal-body');
        var vUrl = $(this).attr('data-video');
        modal.modal();
        if (vUrl) {
            var vData = validateVideoUrl(vUrl);
            var vSrc;
            if (vData.type == 'youtube') {
                vSrc = 'http://www.youtube.com/embed/' + vData.id;
            } else {
                vSrc = 'https://player.vimeo.com/video/' + vData.id;
            }
            mBody.html('<div class="videoWrapper"><iframe width="560" height="349" src="' + vSrc + '" frameborder="0" allowfullscreen></iframe>');
            vUrl = null;
        } else {
            iSrc = $(this).parents('.image-frame').find('img').attr('src');
            mBody.html('<img src="' + iSrc + '" class="img-pre-option">');
        }
        modal.on('hide.bs.modal', function(e) {
            mBody.empty();
        });
    });

    $(document).on('change', function() {
        $('#wrapped').validate();
        $('input.validate').each(function(){
            $(this).rules('add', {
               required: true,
               maxlength: 255
            });
        });
    });
});
