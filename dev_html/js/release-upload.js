var afterUploaded = function(resp) {
    response = jQuery.parseJSON(resp);
    if (response[0].error) {
        error_msg = 'The uploaded packages have errors: <ul>';
        $.each(response[0].error, function(index, value) {
                error_msg += '<li>' + value + '</li>';
            }
            
        );
        error_msg += '</ul>';
        $('#messages').html(error_msg);
    } else {
        message =
        $('#messages').html('Uploaded successfully.');
        $('#release_confirm').load('/release-confirm.php?json=1');
    }
};

$(document).ready(function() {
    $('input[type="file"]').dropUpload({
        'uploadUrl'         : '/release-upload2.php',
        'uploaded'         : afterUploaded,
        'dropClass'      : 'file-drop',
        'dropHoverClass' : 'file-drop-hover',
        'defaultText'       : 'Drop your package(s) here',
        'hoverText'         : 'Let begin the release(s)!'
    });
});

function confirm_release(release_name_md5)
{
    url = '/release-confirm.php?json=1&name=' + release_name_md5;
    var jqxhr = $.get(url, function(data) {
        $('#btn_' + release_name_md5).html(data);
        $('#btn_' + release_name_md5).removeClass('release_action_view');
        $('#btn_' + release_name_md5).addClass('release_action_released');
    })
}

function cancel_release(release_name_md5)
{
    url = '/release-confirm.php?json=1&name=' + release_name_md5 + "&cancel=1";
    var jqxhr = $.get(url, function(data) {
        $('#btn_' + release_name_md5).html(data);
        $('#btn_' + release_name_md5).removeClass('release_action_view');
        $('#btn_' + release_name_md5).addClass('release_action_released');
    })
}