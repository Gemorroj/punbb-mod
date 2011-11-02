<?php
define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}


if ($to = intval($_GET['to'])) {
    vote($to, intval($_GET['vote']));
    $pid = intval($_GET['pid']);
    wap_redirect('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/viewtopic.php?pid=' . $pid . '#p' . $pid);
    exit;
}


$id = intval($_GET['id']);
$q = $db->fetch_row($db->query('
    SELECT COUNT(1), (SELECT COUNT(1) FROM `' . $db->prefix . 'karma` WHERE `vote` = "-1" AND `to` = ' . $id . ') FROM `' . $db->prefix . 'karma` WHERE `vote` = "1" AND `to` = ' . $id
));

$karma['plus'] = intval($q[0]);
$karma['minus'] = intval($q[1]);
$karma['karma'] = $karma['plus'] - $karma['minus'];
unset($q);

$num_hits = $karma['plus'] + $karma['minus'];

$num_pages = ceil($num_hits / $pun_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];

$start = ($p - 1) * $pun_user['disp_posts'];
if ($_GET['action'] == 'all') {
    $p = $num_pages + 1;
    $pun_user['disp_posts'] = $num_hits;
    $start = 0;
}

$username = pun_htmlspecialchars($db->result($db->query('SELECT `username` FROM `' . $db->prefix . 'users` WHERE `id` = ' . $id), 0));
$str = '';


if ($num_hits) {
    $q = $db->query('
        SELECT `karma`.*, `users`.`username` AS `from`
        FROM `' . $db->prefix . 'karma` AS `karma`
        LEFT JOIN `' . $db->prefix . 'users` AS `users` ON `users`.`id` = `karma`.`id`
        WHERE `karma`.`to` = ' . $id . '
        ORDER BY `karma`.`time` DESC
        LIMIT ' . $start . ',' . $pun_user['disp_posts']
    );

    $str.= '<div class="blocktable"><div class="box"><table border="1"><tr><th>' . $lang_common['Username'] . '</th><th>' . $lang_common['Vote'] . '</th><th>' . $lang_common['Date'] . '</th></tr>';

    while ($result = $db->fetch_assoc($q)) {
        if ($result['from']) {
        	$user = '<a href="profile.php?id=' . $result['id'] . '">' . pun_htmlspecialchars($result['from']) . '</a>';
        } else {
        	$user = $lang_common['Deleted'];
        }
    	$str.= '<tr><td>' . $user . '</td><td>' . ($result['vote'] > 0 ? '<strong style="color: green;">+</strong>' : '<strong style="color: red;">-</strong>') . '</td><td>' . format_time($result['time']) . '</td></tr>';
    }

    $str.= '</table></div></div><div class="con">' . $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'karma.php?id=' . $id) . '<br/></div>';
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Karma'] . ' - ' . $username . ' (' . $karma['karma'] . ')';

require_once PUN_ROOT . 'wap/header.php';

echo $str;

$footer_style = 'index';
require_once PUN_ROOT.  'wap/footer.php';

?>