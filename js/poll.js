if (typeof poll !== "object") {
    poll = {};
}
poll.frmloaded = false;

poll.lng = ({
    'create_poll': 'Создание опроса',
    'fields_required': 'Следующие поля должны быть заполнены:',
    'quest': 'Вопрос',
    'list_answers': 'Список ответов',
    'answer_must_select': 'Хотя бы один ответ должен быть выбран!',
    'create': 'Создать',
    'delete': 'Удалить'
});

poll.remove = function () {
    $("#apcreate").text(poll.lng.create).off('click').modalBox({
        'title': poll.lng.create_poll, 'height': 600, 'width': 600, 'ajax': "ajax.server.php?poll=gcfrm"
    });
    $("#ppreview").slideUp(200).empty();
};

poll.pForm = function () {
    var b = '<br />', e = 0, d = $("#pdescription").val(), q = $("#pquestions").val();
    var s = poll.lng.fields_required + b;

    if (!d) {
        e++;
        s += e + ". " + poll.lng.quest + b;
    }

    if (!q) {
        e++;
        s += e + ". " + poll.lng.list_answers + b;
    }

    if (e !== 0) {
        $("#warning").html(s).show("fast");
        return false;
    }

    var $p = $('<p><strong></strong></p>').find('strong').text(d);
    $("#ppreview").empty().append($p).append('<ol id="poansw"></ol><br class="clearb" />');

    var l = q.split("\n");
    var $to = $("#poansw");
    for (var i = 0, all = l.length; i < all; i++) {
        $("<li></li>").text(l[i]).appendTo($to);
    }

    poll.frmloaded = false;

    $("#has_poll").val(1);

    $('#post').append($('<input type="hidden" name="polldata" id="polldata" value="" />').val($('#pollcreate').serialize()));
    $("#apcreate").off('click').text(poll.lng['delete']).click(poll.remove);
    $.modalBox.hideBox(function () {
        $("#ppreview").slideDown(200);
    });

    return false;
};

poll.vote = function (id) {
    var $p = $(".p_cnt_" + id);
    var $ch = $p.find("input:checked");

    if ($ch.length < 1) {
        $("#warning").html(poll.lng.answer_must_select).show("fast");
        return false;
    }

    $p.find("table td").css('opacity', 0.4);
    $("#warning").empty();
    $p.css('position', 'relative').prepend('<div class="poll-overlay"><img src="style/img/busy.gif" alt=""/></div>');

    $.ajax({
        type: "POST",
        url: "ajax.server.php?poll=sres",
        dataType: "html",
        cache: false,
        data: {"p": id, "q": $ch.serialize()},
        success: function (data) {
            $p.html(data);
        }
    });
};

$(function () {
    poll.remove();
    $("#apinsert").modalBox({
        ajax: "ajax.server.php?poll=gcfrm",
        boxTimer: 0
    });
});
