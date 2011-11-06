if (typeof poll !== "object") {
    poll = {};
}
poll.frmloaded = false;

poll.lng = ({
    'create_poll': 'Создание опроса',
    'fields_requered': 'Следующие поля должны быть заполнены:',
    'quest': 'Вопрос',
    'list_answers': 'Список ответов',
    'answer_must_select': 'Хотя бы один ответ должен быть выбран!',
    'create': 'Создать',
    'delete': 'Удалить'
});

$(document).ready(function () {
    poll.remove();
    $("#apinsert").modalBox({
        ajax:"ajax.server.php?poll=gcfrm",
        boxTimer: 0
    });
});

poll.remove = function() {
    $("#apcreate").text(poll.lng.create).unbind('click').modalBox({title:poll.lng.create_poll, height: 600, width: 600, ajax: "ajax.server.php?poll=gcfrm"});
    $("#ppreview").slideUp(200).empty();
};

poll.pForm = function() {
    var b = '<br />', e = 0, d = $("#pdescription").val(), q = $("#pquestions").val();
    var s = poll.lng.fields_requered + b;

    if (!d) {
        e++;
        s += e + ". " + poll.lng.quest + b;
    }

    if (!q) {
        e++;
        s += e + ". " + poll.lng.list_answers + b;
    }

    if (e !== 0) {
        $("#warning").html(s).show();
        return false;
    }

    var l = q.split("\n");
    $("#ppreview").empty().append('<p><strong>' + d + '<strong></p>').append('<ol id="poansw"></ol><br class="clearb" />');

    for (var i = 0; i < l.length; i++) {
        $("<li></li>").text(l[i]).appendTo($("#poansw"));
    }

    $("#has_poll").val(1);

    $("#apcreate").unbind('click');
    $('#post').append('<input type="hidden" name="polldata" id="polldata" value="' + $('#pollcreate').serialize() + '" />');
    poll.frmloaded = false;
    $("#apcreate").text(poll.lng['delete']);
    $("#apcreate").click(poll.remove);
    $.modalBox.hideBox(function () {
        $("#ppreview").slideDown(200);
    });

    return false;
};

poll.vote = function (id) {
    var p = ".p_cnt_" + id;
    var t = $(p + " input").attr("type");

    if (!$(p + " input:" + t + ":checked").prop("checked")) {
        $("#warning").html(poll.lng.answer_must_select).show();
        return false;
    }

    $(p + " table td").each(function () {
        $(this).css({opacity: "0.4"});
    });
    $("#warning").empty();
    $(p).css('position', 'relative').prepend('<div class="poll-overlay"><img src="style/img/loading.gif" alt=""/></div>');

    $.ajax({
        type: "POST",
        url: "ajax.server.php?poll=sres",
        dataType: "html",
        cache: false,
        data: {"p": id, "q": $(p + " :" + t + ":checked").serialize()},
        success: function (data) {
            $(p).html(data);
        }
    });
};