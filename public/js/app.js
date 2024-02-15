function isEmptyElement( el ){
    return !$.trim(el.html());
}

function showMainProcessing()
{
    $('#processing-overlay').addClass('show');
}
function stopMainProcessing()
{
    let processTime;
    const mainLoading = $('#processing-overlay');
    const timeLoading = mainLoading.data('timer');
    let stopProcessTime = (timeLoading != undefined) ? timeLoading : 1000;
    processTime = setTimeout(function() {
        mainLoading.removeClass('show');
        clearTimeout(processTime);
    }, stopProcessTime);
}

function createAlert(msg, c) {
    let ic = 'question-circle';

    if (c == 'danger') {
        ic = 'times-circle';
    } else if (c == 'success') {
        ic = 'check-circle';
    } else if (c == 'warning') {
        ic = 'exclamation-circle';
    } else if (c == 'info') {
        ic = 'info-circle';
    }

    return '<div class="alert alert-' + c + ' alert-dismissible p-2" style="--bs-gutter-x:0.5rem"><div class="d-flex"><i class="flex-grow-0 flex-shrink-0 fs-3 px-2 me-2 fa-solid fa-' + ic + '"></i><div class="d-block flex-fill">' + msg + '</div><button type="button" class="btn-close p-0 position-static flex-grow-0 flex-shrink-0 lh-1 ms-2" data-bs-dismiss="alert" style="background: none; font-size:12px;"><i class="fas fa-times"></i></button></div></div>';
}

function changeViewPassword(sender, initialize)
{
    if (!(sender instanceof jQuery)) sender = $(sender);

    const input = $(sender.data('target'));
    const labelIcon = $('.pwd-icon', sender);
    const openIcon = input.data('icon-open');
    const closeIcon = input.data('icon-close');
    const isOpened = (input.attr('type') == 'text');

    if (initialize != true) {
        if (isOpened) {
            input.attr({type: 'password'});
            labelIcon.removeClass(closeIcon).addClass(openIcon).attr({title: 'Perlihatkan password'});
        } else {
            input.attr({type: 'text'});
            labelIcon.removeClass(openIcon).addClass(closeIcon).attr({title: 'Sembunyikan password'});
        }
    } else {
        labelIcon.removeClass(isOpened ? closeIcon : openIcon).addClass(initialize ? openIcon : closeIcon).attr({title: initialize ? 'Perlihatkan password' : 'Sembunyikan password'});
    }
}

function initPasswordView()
{
    $('.pwd-view:not(.initialized)').each(function(n, p) {
        changeViewPassword(p, true);
    });
}

function submitForm(f, tm)
{
    const frm = $(f);
    const data = frm.serialize();
    const url = frm.attr('action');
    const msg = $(tm);
    showMainProcessing();
    $.post({
        url: url,
        data: data
    }).done(function(respon) {
        window.location.replace(respon);
    }).fail(function(respon) {
        msg.empty().html(respon.responseText);
    }).always(function(respon) {
        stopMainProcessing();
    });

    return false;
}

function submitFormUpload(f, tm)
{
    const frm = $(f);
    const data = new FormData(frm[0]);
    const url = frm.attr('action');
    const msg = $(tm);
    showMainProcessing();
    $.post({
        url: url,
        data: data,
        cache: false,
        contentType: false,
        processData: false
    }).done(function(respon) {
        window.location.replace(respon);
    }).fail(function(respon) {
        msg.empty().html(respon.responseText);
    }).always(function(respon) {
        stopMainProcessing();
    });

    return false;
}

$(function() {
    $('.sidebar-toggler, .sidebar-overlay').on('click', function() {
        $('body').toggleClass('sb-toggled');
    });

    stopMainProcessing();

    // $(document).on('click', '.btn-captcha', function(e) {
    //     $.get({
    //         url: '/reload-captcha'
    //     }).done(function(respon) {
    //         $('#img-captcha').attr({'src': respon});
    //     });
    // });

    // $(document).on('submit', 'form.form-modal', function(e) {
    //     return false;
    // });

    $(document).on('submit', 'form[method="POST"]', function(e) {
        const frm = $(this);

        if (frm.hasClass('disable-submit')) {
            showMainProcessing();
        } else {
            const frmEnc = frm.attr('enctype');
            let alertC = frm.data('alert-container');

            if ((alertC == undefined) || (alertC == '')) {
                alertC = 'alert-container';
                const c = $('<div/>').attr({id: alertC}).addClass('d-block');
                alertC = '#' + alertC;
                frm.data('alert-container', alertC);
                frm.before(c);
            }

            if (frmEnc == 'multipart/form-data') {
                return submitFormUpload(this, alertC);
            } else {
                return submitForm(this, alertC);
            }
        }
    }).on('click', '.btn-href', function(e) {
        const url = $(this).data('href');
        if ((url != undefined) && (url != '') && (url != '#')) {
            window.location.href = url;
        }
        return false;
    }).on('click', '.pwd-view', function(e) {
        changeViewPassword(this, false);
    });

    $('.modal').on('show.bs.modal', function(e) {
        const target = $(e.relatedTarget);
        const url = target.data('modal-url');
        if ((url != undefined) && (url != '') && (url != '#')) {
            const dlg = $('.modal-dialog', $(this)).empty();
            $.get({
                url: url
            }).done(function(respon) {
                dlg.html(respon);
                initPasswordView();
            }).fail(function(respon) {
                const content = '<div class="modal-content"><div class="modal-header py-2 px-3"><span class="fw-bold small">Error ' + respon.status + '</span><button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;"><i class="fa-solid fa-times"></i></button></div><div class="modal-body text-center">' + respon.responseText + '</div>' +
                '<div class="modal-footer py-1"><button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times me-1"></i>Tutup</button></div></div>';
                dlg.html(content);
            });
        }
    });

    $('.image-zoom').on('click', function(e) {
        $(this).toggleClass('zoom');
    });

    initPasswordView();
});
