function vote(user, karma)
{
    $.ajax({
        async: true,
        type: 'GET',
        url: 'karma.php?to=' + user + '&vote=' + karma
    });
}


function karmaPlus(user, karma)
{
    $("span.karma_" + user).empty();
    $("span.num_" + user).text(karma + 1);
    vote(user, 1);
}


function karmaMinus(user, karma)
{
    $("span.karma_" + user).empty();
    $("span.num_" + user).text(karma - 1);
    vote(user, -1);
}