function insAtCaret(o, s)
{
    o.focus();
    if (typeof(document.selection) !== 'undefined') {
        var r = document.selection.createRange();
        if (r.parentElement() != o) {
            return;
        }
        r.text = s;
        r.select();
    } else if (typeof(o.selectionStart) !== 'undefined') {
        st = o.selectionStart;
        o.value = o.value.substr(0, st) + s + o.value.substr(o.selectionEnd, o.value.length);
        st += s.length;
        o.setSelectionRange(st, st);
    } else {
        o.value += s;
    }
    o.focus();
}


function expandField(f, h)
{
    $(f).height($(f).height() + h);
}


function pasteQ(p, n)
{
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


function pasteN(t)
{
    var o = document.forms.post.req_message;
    if (t !== '' && o) {
        insAtCaret(o, "[b]" + t + "[/b]\n");
    }
}