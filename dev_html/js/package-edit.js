var maintainer_list_data = [];
var maintainer_list_ul = [];

function init_maintainer(val) {
    gravatar_url = 'https://secure.gravatar.com/avatar/' + val.gravatar_id;
    gravatar_img = '<img src="' + gravatar_url + '" width="20" />';
    new_li = '<li id="' + val.handle + '">' + '<span class="name">' + gravatar_img +  val.name  + '</span>&nbsp;' +'<span class="handle">' +   val.handle + '</span>&nbsp;' + val.role;
    new_li += '<span class="cmd"><a href="#" onclick="remove_maintainer(' + "'" + val.handle + "'); return false;" + '"' + '>remove</a></span></li>';
    maintainer_list_ul.push(new_li);
    maintainer_list_data.push(val);
}

function add_maintainer(val) {
    gravatar_url = 'https://secure.gravatar.com/avatar/' + val.gravatar_id;
    gravatar_img = '<img src="' + gravatar_url + '" width="20" />'
    maintainer_list_data.push(val);

    new_li = '<li id="' + val.handle + '">' + '<span class="name">' + gravatar_img +  val.name  + '</span>&nbsp;' +'<span class="handle">' +   val.handle + '</span>&nbsp;' + val.role + '</li>';
    new_li += '<a href="#" onclick="remove_maintainer">remove</a> ';
    $('#maintainer_list').append(new_li);
}

function remove_maintainer(handle)
{
    var to_delete;
    $.each(maintainer_list_data, function(key, val) {
            if (val.handle && val.handle == handle) {
                $('#' + val.handle).closest('li').remove();
                to_delete = key;
                return false;
            }
        }
    );
    maintainer_list_data.splice(to_delete, 1);
    return;
}

$(document).ready(function() {
    var newmaintainer_selected = null;
    
    $.getJSON('/maintainer-json.php?package=' + $('#package_name').val(), function(data) {

        $.each(data, function(key, val) {
            init_maintainer(val);
        });

        $('#maintainer_list').append( maintainer_list_ul.join('') );
        maintainer_list_ul = [];
    });

    $("#newname").autocomplete(
        {
            select: function (event, ui) {
                newmaintainer_selected = ui.item.value;
                $('#newname').val(ui.item.value.handle);
                return false;
            },
            source: "/searchhandle.php",
            dataType: "json",
            minLength: 2
        });

    $('#btn-add').click(function(){
        var newname = $('#newname').val();
        var newrole = $('#role').val();

        if (newrole.length < 5 &&  newname.length < 2) {
            return false;
            
        }
        key_to_delete = 0;
        $.each(maintainer_list_data, function(key, val) {
                if (val.handle && val.handle == newname) {
                    $('#' + val.handle).closest('li').remove();
                    key_to_delete = 1;
                }
            }
        );
        if (key_to_delete > 0) {
            maintainer_list_data.splice(key_to_delete, 1);
        }
        newmaintainer_selected.role = newrole;
        add_maintainer(newmaintainer_selected);
        $('#newname').val('');
        $('#role').val('');
    });

    $('#btn-remove').click(function(){
        $('#maintainers option:selected').each( function() {
            $(this).remove();
        });
    });

    $('#btn-save').click(function(){
        maintainer_get = '';
        role_get = '';
        $.each(maintainer_list_data, function(key, val) {
            maintainer_get += '&maintainer[]=' + val.handle;
            role_get += '&role[]=' + val.role;
        });

        $('#maintainers *').attr('selected','');
        $.get("/package-maintainer.php?package=" + $('#package_name').val() + maintainer_get + role_get, function(data){alert(data)})
                   .success(function() {alert("Saved");})
                   .error(function(data){alert("Failed" + data);});

    });
});
