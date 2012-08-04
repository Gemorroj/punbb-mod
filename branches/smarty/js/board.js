function insAtCaret(o, s) {
    var r;
    o.focus();

    if ("selection" in document) {
        r = document.selection.createRange();
        if (r.parentElement() !== o) {
            return;
        }
        r.text = s;
        r.select();
    } else if ("selectionStart" in o) {
        r = o.selectionStart;
        o.value = o.value.substr(0, r) + s + o.value.substr(o.selectionEnd, o.value.length);
        r += s.length;
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
        $("#p" + p + " .postquote:last a").hide();
        $("#p" + p + " .postquote:last").append('<img id="busy' + p + '" src="img/busy.gif" alt="loading" />');
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


function ctrlSend(e) {
    e = e || window.event;

    if (e.ctrlKey === true && (e.keyCode === 10 || e.keyCode === 13)) {
        e.cancelBubble = true;
        e.returnValue = false;

        if (e.stopPropagation) {
            e.stopPropagation();
            e.preventDefault();
        }

        e.target.form.submit.click();
    }
    //return true;
}


function resizeTextarea(dpixels) {
    var box = (document.all) ? document.all.req_message : document.forms.post.req_message;
    var cur_height = parseInt(box.style.height, 10) ? parseInt(box.style.height, 10) : 180;
    var new_height = cur_height + dpixels;
    if (new_height > 0) {
        box.style.height = new_height + "px";
    }
}