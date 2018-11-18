$(document).ready(function () {
    var url = $('.url-token').data('route');
    var userId;
    var userEmail;

    $(document).on('click', '#bt-request', function () {
        userId = $(this).attr('userId');
        userEmail = $(this).attr('userEmail');
        $('.bt-send-request').attr('data-user-email', userEmail);
        $('#emailUser').html(userEmail);
        $( "#form-request" ).toggle( 'slow' );
    });

    $(document).on('click', '#bt-cancel', function () {
        var url = $(this).attr('request-url');
        var id = $(this).attr('requestId');
        var tableType = $(this).attr('table-type');

        $.post(
            url,
            {
                'requestId': id,
            },
            function(response) {

                if (response.success) {
                    $('#table-' + tableType).load(location.href + " #table-" + tableType + ">*", "");
                } else {
                    alert('Fail to cancel request');
                }
        });
    });

    $(document).on('click', '#bt-active', function () {
        var url = $(this).attr('data-url');
        var tableType = $(this).attr('table-type');

        $.post(
            url,
            {
            },
            function(response) {

                if (response.success) {
                    $('#table-' + tableType).load(location.href + " #table-" + tableType + ">*", "");
                } else {
                    alert('Fail to active user');
                }
        });
    });

    $(document).on('click', '#bt-block-supperadmin', function () {
        var url = $(this).attr('data-url');
        var tableType = $(this).attr('table-type');
        $.post(
            url,
            {
            },
            function(response) {

                if (response.success) {
                    $('#table-' + tableType).load(location.href + " #table-" + tableType + ">*", "");
                } else {
                    alert('Fail to block user');
                }
        });
    });

    $(document).on('click', '#bt-request-delete', function () {
        var url = $(this).attr('data-url');
        var id = $(this).attr('request-id');
        var href = $(this).attr('url');

        $.post(
            url,
            {
                'requestId': id,
            },
            function(response) {

                if (response.success) {
                    window.location.href = href;
                } else {
                    alert('Fail to delete request');
                }
        });
    });

    $(document).on('click', '#bt-request-accept', function () {
        var url = $(this).attr('data-url');
        var href = $(this).attr('url');

        $.post(
            url,
            {
            },
            function(response) {

                if (response.success) {
                    window.location.href = href;
                } else {
                    alert('Fail to accept request');
                }
        });
    });

    $(document).on('click', '.bt-send-cancel', function () {
        $('#requestContent').val('');
        $("#form-request" ).fadeOut();
        $('input:radio[name=data-option]').removeAttr("checked");
    });

    $('body').on('click', '.bt-send-request', function () {
        var content = $('#requestContent').val();
        var url = $(this).attr('data-url');
        var type = $('input[name=data-option]:checked').val();
        var href = $(this).attr('data-href');

        if(!type || !content){
            alert("Let's choose option");
        } else {
            $.post(
                url,
                {
                    'emailUser': userEmail,
                    'content': content,
                    'type': type
                },
                function(response) {

                    if (response.success) {
                        $( "#form-request" ).toggle( 'slow' );
                        window.location.href = href;
                    } else {
                      alert('Fail to send request');
                    }
            });
        }
    });
});
