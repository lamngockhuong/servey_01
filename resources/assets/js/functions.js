$(function() {
    $(window)['scroll'](function() {
        if ($(this)['scrollTop']() != 0) {
            $('#toTop')['fadeIn']()
        } else {
            $('#toTop')['fadeOut']()
        }
    });
    $('#toTop')['click'](function() {
        $('body,html')['animate']({
            scrollTop: 0
        }, 500)
    });

    $('.survey-result a').on('click', function () {
        $('.loader').show();
        var url = $(this).data('url');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function (response) {
                if (response.success) {
                    var surveyId = response.survey_id;
                    $('.tab-users-answer' + surveyId + ' .table').html(response.data);
                } else {
                    alert(error);
                }
                $('.loader').fadeOut('slow');
            },
        });
    });
});
if (window['innerWidth'] < 770) {
    $('button.forward, button.backword')['click'](function() {
        $('html, body')['animate']({
            scrollTop: 115
        }, 'slow');
        return false
    })
};
if (window['innerWidth'] < 500) {
    $('button.forward, button.backword')['click'](function() {
        $('html, body')['animate']({
            scrollTop: 245
        }, 'slow');
        return false
    })
};
if (window['innerWidth'] < 340) {
    $('button.forward, button.backword')['click'](function() {
        $('html, body')['animate']({
            scrollTop: 280
        }, 'slow');
        return false
    })
};

jQuery(function(_0xae65x1) {
    _0xae65x1('#survey_container')['wizard']({
        stepsWrapper: '#wrapped',
        submit: '.submit',
        beforeSelect: function(_0xae65x4, _0xae65x5) {
            if (_0xae65x1('input#website')['val']()['length'] != 0) {
                return false
            };
             if (!_0xae65x5['isMovingForward']) {
                return true
            };
            var _temp = _0xae65x1(this)['wizard']('state')['step']['find'](':input[type="email"]');
            var _0xae65x6 = _0xae65x1(this)['wizard']('state')['step']['find'](':input[type="text"]');

            return (!_temp['length'] || !!_temp['valid']()) && (!_0xae65x6['length'] || !!_0xae65x6['valid']())
        }
    })['validate'];
    _0xae65x1('#progressbar')['progressbar']();
    _0xae65x1('#survey_container')['wizard']({
        afterSelect: function(_0xae65x4, _0xae65x5) {
            _0xae65x1('#progressbar')['progressbar']('value', _0xae65x5['percentComplete']);
            _0xae65x1('#location')['text']('(' + _0xae65x5['stepsComplete'] + '/' + _0xae65x5['stepsPossible'] + ')')
        }
    })
});
$(document)['ready'](function() {
    $('.btn-responsive-menu')['click'](function() {
        $('#top-nav')['slideToggle'](400)
    });
    $('input.check_radio')['iCheck']({
        checkboxClass: 'icheckbox_square-aero',
        radioClass: 'iradio_square-aero'
    });
    $('input, textarea')['placeholder']();
    $('#owl-demo')['owlCarousel']({
        items: 4,
        itemsDesktop: [1199, 3],
        itemsDesktopSmall: [979, 3]
    })
});
$('.latest-tweets')['each'](function() {
    $(this)['tweet']({
        username: $(this)['data']('username'),
        join_text: 'auto',
        avatar_size: 0,
        count: $(this)['data']('number'),
        auto_join_text_default: ' we said,',
        auto_join_text_ed: ' we',
        auto_join_text_ing: ' we were',
        auto_join_text_reply: ' we replied to',
        auto_join_text_url: '',
        loading_text: ' loading tweets...',
        modpath: './twitter/'
    })
});
$('.latest-tweets')['find']('ul')['addClass']('slider');
if ($()['bxSlider']) {
    var $this = $('.latest-tweets');
    $('.latest-tweets .slider')['bxSlider']({
        mode: $this['data']('mode') != 'undefined' ? $this['data']('mode') : 'horizontal',
        speed: $this['data']('speed') != 'undefined' ? $this['data']('speed') : 2000,
        controls: $this['data']('controls') != 'undefined' != 'undefined' ? $this['data']('controls') : true,
        nextSelector: $this['data']('nextselector') != 'undefined' ? $this['data']('nextselector') : '',
        prevSelector: $this['data']('prevselector') != 'undefined' ? $this['data']('prevselector') : '',
        pager: $this['data']('pager') != 'undefined' ? $this['data']('pager') : true,
        pagerSelector: $this['data']('pagerselector') != 'undefined' ? $this['data']('pagerselector') : '',
        pagerCustom: $this['data']('pagercustom') != 'undefined' ? $this['data']('pagercustom') : '',
        auto: $this['data']('auto') != 'undefined' ? $this['data']('auto') : true,
        autoHover: $this['data']('autoHover') != 'undefined' ? $this['data']('autoHover') : true,
        adaptiveHeight: $this['data']('adaptiveheight') != 'undefined' ? $this['data']('adaptiveheight') : true,
        useCSS: $this['data']('useCSS') != 'undefined' ? $this['data']('useCSS') : false,
        nextText: '<i class="icon-angle-right">',
        prevText: '<i class="icon-angle-left">',
        preloadImages: 'all',
        responsive: true
    })
}
