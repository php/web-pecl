// +----------------------------------------------------------------------+
// | PEAR Web site version 1.0                                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Martin Jansen <mj@php.net>                                  |
// +----------------------------------------------------------------------+

function stripName(name) {
    pos = name.indexOf("(");
    return name.substr(0, pos-1);
}

function getRole() {
    for (z = 0; z < document.form.role.length; z++) {
        if (document.form.role.options[z].selected == true) {
            return document.form.role.options[z].value;
        }
    }
    return "lead";
}

function addMaintainer() {
    for (i = 0; i < document.form.accounts.length; i++) {
        if (document.form.accounts.options[i].selected == true) {
            name = stripName(document.form.accounts.options[i].text);
            role = getRole();
            handle = document.form.accounts.options[i].value;
            value = handle + "||" + role;
            item = new Option(name + " (" + handle + ", " + role + ")", value);
            document.form['maintainers[]'].options[document.form['maintainers[]'].length] = item;
        }
    }
}

 function removeMaintainer() {
    for (i = 0; i < document.form['maintainers[]'].length; i++) {
        field = document.form['maintainers[]'].options[i];
        if (field.selected == true) {
            if (document.form['maintainers[]'].length == 1) {
                alert('Removing the only maintainer is not possible!');
                return;
            }
            if (confirm('Do you really want to remove ' + field.text + '?')) {
                document.form['maintainers[]'].options[i] = null;
            }
        }
    }
}

function beforeSubmit() {
    for (i = 0; i < document.form['maintainers[]'].length; i++) {
        field = document.form['maintainers[]'].options[i].selected = true;
    }
}

function activateAdd() { 
    document.form.add.disabled = false; 
    document.form.role.disabled = false;
}

function activateRemove() { 
    document.form.remove.disabled = false;
}
