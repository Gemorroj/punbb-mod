<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: ' . gmdate('r') . ' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-Type: text/html; charset=' . $lang_common['lang_encoding']);


// Load the template
if (defined('PUN_HELP')) {
    $tpl_main = file_get_contents(PUN_ROOT . 'include/template/help.tpl');
} else {
    $tpl_main = file_get_contents(PUN_ROOT . 'include/template/wap_main.tpl');
}


// START SUBST - <pun_include "*">
while (preg_match('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_main, $cur_include)) {
    if (!file_exists(PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2])) {
        error('Unable to process user include ' . htmlspecialchars($cur_include[0]) . ' from template main.tpl. There is no such file in folder /include/user/', __FILE__, __LINE__);
    }

    ob_start();
    include_once PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2];
    $tpl_temp = ob_get_contents();
    $tpl_main = str_replace($cur_include[0], $tpl_temp, $tpl_main);
    ob_end_clean();
}
// END SUBST - <pun_include "*">


// START SUBST - <pun_content_direction>
$tpl_main = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_main);
// END SUBST - <pun_content_direction>


// START SUBST - <pun_char_encoding>
$tpl_main = str_replace('<pun_char_encoding>', $lang_common['lang_encoding'], $tpl_main);
// END SUBST - <pun_char_encoding>


// START SUBST - <pun_rssname>
$tpl_main = str_replace('<pun_rssname>', $pun_config['o_board_title'], $tpl_main);
// END SUBST - <pun_rssname>

// START SUBST - <pun_rss>
$tpl_main = str_replace('<pun_rss>', PUN_ROOT . 'rss.xml', $tpl_main);
// END SUBST - <pun_rss>


// START SUBST - <pun_head>
ob_start();

/*
// Is this a page that we want search index spiders to index?
if(!defined('PUN_ALLOW_INDEX'))
{echo '<meta name="ROBOTS" content="NOINDEX, FOLLOW" />';}
*/

echo '<title>' . $page_title . '</title><link rel="stylesheet" type="text/css" href="' . PUN_ROOT . 'style_wap/' . $pun_user['style_wap'] . '.css" />';


$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_head>', $tpl_temp, $tpl_main);
ob_end_clean();

// END SUBST - <body>
if (isset($hint_box)) {
    $tpl_temp = $hint_box;
} else {
    $tpl_temp = null;
}
// START SUBST - <pun_page>
$tpl_main = str_replace('<pun_page>', htmlspecialchars(basename($_SERVER['PHP_SELF'], '.php')), $tpl_main);
// END SUBST - <pun_title>


// START SUBST - <pun_title>
//$tpl_main = str_replace('<pun_title>', '<div class="blocktable">' . pun_htmlspecialchars($pun_config['o_board_title']) . '</div>', $tpl_main);
$tpl_main = str_replace('<pun_title>', '', $tpl_main);
// END SUBST - <pun_title>

$basename = basename($_SERVER['PHP_SELF']);

// START SUBST - <pun_navlinks>
//$tpl_main = str_replace('<pun_navlinks>', '<div id="brdmenu" class="con"><a href="index.php">' . $lang_common['Index'] . '</a></div>', $tpl_main);
//$tpl_main = str_replace('<pun_navlinks>', '', $tpl_main);
// END SUBST - <pun_navlinks>

//Logo на главной
if ($basename == 'index.php') {
    $tpl_main = str_replace('<pun_hd>', '<div class="hd"><img src="' . PUN_ROOT . 'style_wap/' . $pun_user['style_wap'] . '/logo.gif" title="' . $lang_common['Forum'] . ' ' . $_SERVER['HTTP_HOST'] . '" alt="' . $lang_common['Forum'] . ' ' . $_SERVER['HTTP_HOST'] . '"/></div>', $tpl_main);
} else {
    $tpl_main = str_replace('<pun_hd>', '', $tpl_main);
}

// START SUBST - <pun_desc>//Описание форума на главной
if ($basename == 'index.php' && $pun_config['o_board_desc']) {
    $tpl_main = str_replace('<pun_desc>', '<div class="hd_bott">' . pun_htmlspecialchars($pun_config['o_board_desc']) . '</div>', $tpl_main);
} else {
    $tpl_main = str_replace('<pun_desc>', '', $tpl_main);
}
// END SUBST - <pun_desc>

// START SUBST - <pun_status>// Вы зашли как
if ($pun_user['is_guest'] && $basename == 'index.php') {
    $tpl_temp .= '<div class="con">' . $lang_common['Not logged in'] . '</div>';
} /*else if ($basename == 'index.php') {
    $tpl_temp .= '<div class="con">' . $lang_common['Logged in as'] . ' ' . pun_htmlspecialchars($pun_user['username']) . '</div>';
}*/

if ($pun_user['g_id'] < PUN_GUEST) {
    $result_header = $db->query('SELECT COUNT(1) FROM `' . $db->prefix . 'reports` WHERE `zapped` IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

    if ($db->result($result_header)) {
        $tpl_temp .= '<div class="con"><a href="' . PUN_ROOT . 'admin_reports.php">' . $lang_admin['New reports'] . '</a></div>';
    }

    if ($pun_config['o_maintenance'] == 1) {
        $tpl_temp .= '<div class="con"><a href="' . PUN_ROOT . 'admin_options.php#maintenance">' . $lang_admin['maintenance'] . '</a></div>';
    }
}
// PMS MOD BEGIN
require PUN_ROOT . 'include/pms/wap_header_new_messages.php';
// PMS MOD END


// WAP MOD
$tpl_main = str_replace('<div id="punwrap">', '<div>', $tpl_main);
$tpl_main = str_replace('<div id="brdheader" class="block">', '<div>', $tpl_main);
$tpl_main = str_replace('<div class="box">', '<div>', $tpl_main);
$tpl_main = str_replace('<div id="brdtitle" class="inbox">', '', $tpl_main);
$tpl_main = str_replace('<pun_js_helper>', '', $tpl_main);
$tpl_main = str_replace('<pun_navlinks>', '', $tpl_main);
// END WAP MOD


$tpl_main = str_replace('<pun_status>', $tpl_temp, $tpl_main);
$tpl_temp .= '<div class="in"><strong>RSS</strong><div class="box"><div><a href="' . PUN_ROOT . 'rss.xml">RSS</a></div></div>';

// END SUBST - <pun_status>


// START SUBST - <pun_announcement>
//Объявление только на главной
if ($basename == 'index.php' && $pun_config['o_announcement'] == 1) {
    ob_start();

    echo '<div class="incqbox">' . $lang_common['Announcement'] . '</div><div class="msg">' . $pun_config['o_announcement_message'] . '</div>';

    $tpl_temp = trim(ob_get_contents());
    $tpl_main = str_replace('<pun_announcement>', $tpl_temp, $tpl_main);
    ob_end_clean();
} else {
    $tpl_main = str_replace('<pun_announcement>', '', $tpl_main);
}
// END SUBST - <pun_announcement>



// START SUBST - <pun_main>
ob_start();

define('PUN_HEADER', 1);


if ($basename == 'profile.php' || $basename == 'search.php' || $basename == 'userlist.php' || $basename == 'uploads.php' || $basename == 'message_list.php' || $basename == 'message_send.php' || $basename == 'message_delete.php' || $basename == 'help.php' || $basename == 'misc.php' || $basename == 'filemap.php' || $basename == 'karma.php') {
    echo '<div class="con"><a href="index.php">' . $lang_common['Index'] . '</a></div>';
}

?>
