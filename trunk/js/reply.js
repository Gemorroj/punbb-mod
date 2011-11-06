/*
 * This file is part of Elektra File Upload mod
 *
 */


function insert_text(open, close)
{
	var msgfield = document.forms.post.req_message;
	// IE support
	if (document.selection && document.selection.createRange)
	{
		msgfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		msgfield.focus();
	}
	// Moz support
	else if (msgfield.selectionStart || msgfield.selectionStart == '0')
	{
		var startPos = msgfield.selectionStart;
		var endPos = msgfield.selectionEnd;
		msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
		msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
		msgfield.focus();
	}
	// Fallback support for other browsers
	else
	{
		msgfield.value += open + close;
		msgfield.focus();
	}
	return false;
}


function toggleOne(id, display_kind)
{
	if (null == id || id == "") return false;
	if (null == display_kind || display_kind == "") display_kind = "block";
	var obj = document.getElementById(id);
	obj.style.display = (obj.style.display == "none")? display_kind: "none";
	return false;
}

function toggle(Hide1, Show1, Hide2, Show2, Hide3, Show3)
{
	toggleOne(Hide1, "inline");
	toggleOne(Show1, "inline");

	toggleOne(Hide2);
	toggleOne(Show2);

	toggleOne(Hide3);
	toggleOne(Show3);
}


function tag_url()
{
	var enterURL = prompt(text_enter_url, "http://");
	var enterTITLE = prompt(text_enter_url_name, text_enter_title);

	if (!enterURL)
	{
		//alert("Error! " + error_no_url);
		return false;
	}
	if (!enterTITLE) {
		return insert_text("[url]" + enterURL + "[/url]", "", false);
    }
    else
    {
        return insert_text("[url=" + enterURL + "]" + enterTITLE + "[/url]", "", false);
    }
}

function tag_image()
{
	var enterURL = prompt(text_enter_image, "http://");

	if (!enterURL)
	{
		//alert("Error! "+error_no_url);
		return false;
	}
	return insert_text("[img]" + enterURL + "[/img]", "", false);
}

function tag_email()
{
	var emailAddress = prompt(text_enter_email, "");
	if (!emailAddress)
	{
		//alert(error_no_email);
		return false;
	}
	return insert_text("[email]" + emailAddress + "[/email]", "", false);
}