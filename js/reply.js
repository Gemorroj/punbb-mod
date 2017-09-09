/*
 * This file is part of Elektra File Upload mod
 */
function insert_text(open, close) {
    var msgField = document.forms.post.req_message;

    if ('selectionStart' in msgField) {
        var startPos = msgField.selectionStart;
        var endPos = msgField.selectionEnd;
        msgField.value = msgField.value.substring(0, startPos) + open + msgField.value.substring(startPos, endPos) + close + msgField.value.substring(endPos, msgField.value.length);
        msgField.selectionStart = msgField.selectionEnd = endPos + open.length + close.length;
        msgField.focus();
    } else {
        // Fallback support
        msgField.value += open + close;
        msgField.focus();
    }
    return false;
}

function toggle(Hide1, Show1, Hide2, Show2, Hide3, Show3) {
    if (Hide1) {
        $("#" + Hide1).slideToggle(100);
    }
    if (Show1) {
        $("#" + Show1).slideToggle(100);
    }
    if (Hide2) {
        $("#" + Hide2).slideToggle(100);
    }
    if (Show2) {
        $("#" + Show2).slideToggle(100);
    }
    if (Hide3) {
        $("#" + Hide3).slideToggle(100);
    }
    if (Show3) {
        $("#" + Show3).slideToggle(100);
    }
}


function tag_url() {
    var enterURL = window.prompt(text_enter_url, "http://");
    var enterTITLE = window.prompt(text_enter_url_name, text_enter_title);

    if (!enterURL) {
        //alert("Error! " + error_no_url);
        return false;
    }
    if (!enterTITLE) {
        return insert_text("[url]" + enterURL + "[/url]", "");
    } else {
        return insert_text("[url=" + enterURL + "]" + enterTITLE + "[/url]", "");
    }
}

function tag_image() {
    var enterURL = window.prompt(text_enter_image, "http://");

    if (!enterURL) {
        //alert("Error! "+error_no_url);
        return false;
    }
    return insert_text("[img]" + enterURL + "[/img]", "");
}

function tag_email() {
    var emailAddress = window.prompt(text_enter_email, "");
    if (!emailAddress) {
        //alert(error_no_email);
        return false;
    }
    return insert_text("[email]" + emailAddress + "[/email]", "");
}
