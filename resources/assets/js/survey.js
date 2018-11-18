$(document).ready(function() {
    var url = $('.url-token').data('route');

    $(document).on('click', '.remove-sva', function() {
        var idSurvey = $(this).attr('id-survey');
        $.post(
            url + '/admin/destroy-survey',
            {
                'idSurvey': idSurvey,
            },
            function(response) {
                $('.sva' + idSurvey).remove();
        });
    });

    $(document).on('click', '.remove-svb', function() {
        var idSurvey = $(this).attr("id-survey");
        $.post(
            url + '/admin/destroy-survey',
            {
                'idSurvey': idSurvey,
            },
            function(response) {
                $(".svb" + idSurvey).remove();
        });
    });

    $('.alert-message').delay(3000).slideUp(300);

    $('.btn-view-feedback').on('click', function () {
        var url = $(this).attr('url');
        var callback = $(this).attr('data-url');
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');

        $.post(
            url,
            {
                'id': id,
                'status': status,
            },
            function (response) {
                if (response.success) {
                    window.location = callback;
                } else {
                    var data = {
                        message: error,
                        buttonText: Lang.get('js.button.ok'),
                    };
                    alertDanger(data);
                }
        });
    });
});


