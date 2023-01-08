/**
 * @param {HTMLElement} o
 * @param {String} s
 */
function insAtCaret(o, s) {
    o.focus();

    if ("selection" in document) {
        var range = document.selection.createRange();
        if (range.parentElement() !== o) {
            return;
        }
        range.text = s;
        range.select();
    } else if ("selectionStart" in o) {
        o.value = o.value.substring(0, o.selectionStart) + s + o.value.substring(o.selectionEnd, o.value.length);
        var r = o.selectionStart + s.length;
        o.setSelectionRange(r, r);
    } else {
        o.value += s;
    }
    o.focus();
}


function expandField(f, h) {
    f = $(f);
    f.height(f.height() + h);
}


function pasteQ(p, n) {
    var t = '', x = '';

    if (document.getSelection) {
        t = document.getSelection();
    } else if (document.selection) {
        t = document.selection.createRange();
        x = t.parentElement().tagName.toUpperCase();
        t = t.text;
    } else if (window.getSelection) {
        t = window.getSelection();
    }

    if (t !== '' && x !== 'TEXTAREA') {
        t = "[quote=" + n + "]" + t + "[/quote]\n";
        insAtCaret(document.forms.post.req_message, t);
    } else if (p !== '') {
        var $postquote = $("#p" + p + " .postquote:last");
        $postquote.find("a").hide();
        $postquote.append('<img id="busy' + p + '" src="style/img/busy.gif" alt="loading" />');
        $.ajax({
            type: "GET",
            url: "ajax.server.php?quote=" + p,
            dataType: "text",
            success: function (d) {
                insAtCaret(document.forms.post.req_message, d);
                $("#busy" + p).hide();
                $("#p" + p + " .postquote:last a").show();
                expandField('#req_message', 50);
            }
        });
    }
}


function pasteN(t) {
    var o = document.forms.post.req_message;
    if (t !== '' && o) {
        insAtCaret(o, "[b]" + t + "[/b]\n");
    }
}


function resizeTextarea(dpixels) {
    var box = document.forms.post.req_message;
    var cur_height = parseInt(box.style.height, 10) ? parseInt(box.style.height, 10) : 180;
    var new_height = cur_height + dpixels;
    if (new_height > 0) {
        box.style.height = new_height + "px";
    }
}
