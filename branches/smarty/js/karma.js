function _vote(user, karma) {
    $("span.karma_" + user).empty();

    var num = $("span.num_" + user);
    var oldKarma = Number(num.html());

    num.html('<img src="img/busy.gif" alt="" />');

    $.get('karma.php?to=' + user + '&vote=' + karma, function (data) {
        num.html(data ? oldKarma + karma : 'Ошибка');
    });
}


function karmaPlus(user) {
    _vote(user, 1);
}


function karmaMinus(user) {
    _vote(user, -1);
}