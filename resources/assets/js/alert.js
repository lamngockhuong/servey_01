function baseConfirm(event, element, data) {
    event.preventDefault();

    data.title = '';
    data.text = data.message;
    data.closeOnConfirm = true;
    data.allowOutsideClick = true;
    data.showCancelButton = true;
    data.cancelButtonText = data.cancelText;
    data.cancelButtonClass = "btn-default";
    data.confirmButtonText = data.confirmText;

    swal(data, function (isConfirm) {
        if (isConfirm) {
            $(element).trigger('click', {});
        }
    });
}

function baseAlert(data) {
    data.title = '';
    data.text = data.message;
    data.confirmButtonText = data.buttonText;
    data.allowOutsideClick = true;

    swal(data);
}

function confirmInfo(event, element, data) {
    data.type = 'info';
    data.confirmButtonClass = "btn-info";
    baseConfirm(event, element, data);
}

function confirmWarning(event, element, data) {
    data.type = 'warning';
    data.confirmButtonClass = "btn-warning";
    baseConfirm(event, element, data);
}

function confirmDanger(event, element, data) {
    data.type = 'error';
    data.confirmButtonClass = "btn-danger";
    baseConfirm(event, element, data);
}

function alertInfo(data) {
    data.type = 'info';
    data.confirmButtonClass = "btn-info";
    baseAlert(data);
}

function alertWarning(data) {
    data.type = 'warning';
    data.confirmButtonClass = "btn-warning";
    baseAlert(data);
}

function alertDanger(data) {
    data.type = 'error';
    data.confirmButtonClass = "btn-danger";
    baseAlert(data);
}
