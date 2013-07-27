function goto_inbox() {
    opener.window.location.assign('message_list.php?box=0');
    window.close();
}
function goto_this_inbox() {
    window.resizeTo(700, 500);
    window.location.assign('message_list.php');
}
function go_read_msg(id) {
    window.resizeTo(800, 800);
    window.location.assign('message_list.php?id=' + id + '&p=1&box=0');
}