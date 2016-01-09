/*
 * This file is part of Elektra File Upload mod
 *
 */
function insert_text(open, close) {
    var msgfield = document.forms.post.req_message;

    if (document.selection && document.selection.createRange) {
        // IE support
        msgfield.focus();
        var sel = document.selection.createRange();
        sel.text = open + sel.text + close;
        msgfield.focus();
    } else if (msgfield.selectionStart || msgfield.selectionStart == '0') {
        // Moz support
        var startPos = msgfield.selectionStart;
        var endPos = msgfield.selectionEnd;
        msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
        msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
        msgfield.focus();
    } else {
        // Fallback support for other browsers
        msgfield.value += open + close;
        msgfield.focus();
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