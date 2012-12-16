<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

$pun_xhtml = stripos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') ? 'application/xhtml+xml' : 'text/html';

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: ' . gmdate('r') . ' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-Type: ' . $pun_xhtml . '; charset=UTF-8');

// Load the template
if (defined('PUN_ADMIN_CONSOLE')) {
    $tpl_main = file_get_contents(PUN_ROOT . 'include/template/admin.tpl');
} else if (defined('PUN_HELP')) {
    $tpl_main = file_get_contents(PUN_ROOT . 'include/template/help.tpl');
} else {
    $tpl_main = file_get_contents(PUN_ROOT . 'include/template/main.tpl');
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


// START XHTML MIME
$tpl_main = str_replace('<pun_xhtml>', $pun_xhtml, $tpl_main);
// EMD XHTML MIME


// START SUBST - <pun_content_direction>
$tpl_main = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_main);
// END SUBST - <pun_content_direction>


// START SUBST - <pun_rssname>
$tpl_main = str_replace('<pun_rssname>', $pun_config['o_board_title'], $tpl_main);
// END SUBST - <pun_rssname>

// START SUBST - <pun_rss>
$tpl_main = str_replace('<pun_rss>', PUN_ROOT . 'rss.xml', $tpl_main);
// END SUBST - <pun_rss>



// START SUBST - <pun_head>
ob_start();

// Is this a page that we want search index spiders to index?
if (!defined('PUN_ALLOW_INDEX')) {
    echo '<meta name="robots" content="noindex, follow"/>';
}


echo '<title>' . $page_title . '</title><link rel="stylesheet" type="text/css" href="' . PUN_ROOT . 'style/' . $pun_user['style'] . '.css" /><link rel="stylesheet" type="text/css" href="' . PUN_ROOT . 'style/imports/elektra.css" />';


if (defined('PUN_ADMIN_CONSOLE')) {
    echo '<link rel="stylesheet" type="text/css" href="' . PUN_ROOT . 'style/imports/base_admin.css" />';
}


if (isset($required_fields)) {
    // Output JavaScript to validate form (make sure required fields are filled out)
    $js = '<script type="text/javascript">reqField="' . $lang_common['required field'] . '";reqFormLang={';
    while (list($elem_orig, $elem_trans) = each($required_fields)) {
        $js .= $elem_orig . ':"' . addslashes(str_replace('&nbsp;', ' ', $elem_trans)) . '",';
    }
    echo rtrim($js, ',') . '};</script><script type="text/javascript" src="' . PUN_ROOT . 'js/required.js"></script>';
}


$basename = basename($_SERVER['PHP_SELF']);

if (@$jsHelper) {
    $jsHelper->add(PUN_ROOT . 'js/jquery.js');
}

if (in_array($basename, array('post.php', 'viewtopic.php', 'edit.php'))) {
    echo '<script type="text/javascript" src="' . PUN_ROOT . 'js/board.js"></script>';
}

if (in_array($basename, array('message_list.php', 'moderate.php'))) {
    echo '<script type="text/javascript" src="' . PUN_ROOT . 'js/check.js"></script>';
}


if ($basename == 'filemap.php') {
    echo '<style type="text/css">
#map div{padding-top: 3px; padding-bottom: 2px;}
#map .cat{padding-left: 30px; background: url(' . PUN_ROOT . 'img/folder_icon.gif) no-repeat 10px 2px; font-weight: bold}
#map .frm{padding-left: 46px; background: url(' . PUN_ROOT . 'img/folder_icon.gif) no-repeat 26px 2px; font-weight: bold}
#map .tpc{padding-left: 62px; background: url(' . PUN_ROOT . 'img/doc_icon.gif) no-repeat 42px 8px}
#map .att{padding-left: 82px; background: url(' . PUN_ROOT . 'img/attach_icon.gif) no-repeat 68px 2px}
</style>';
}


$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
if (strpos($user_agent, 'msie') !== false && strpos($user_agent, 'windows') !== false && strpos($user_agent, 'opera') === false) {
    echo '<script type="text/javascript" src="' . PUN_ROOT . 'style/imports/minmax.js"></script>';
}

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_head>', $tpl_temp, $tpl_main);
ob_end_clean();


// END SUBST - <pun_head>

// START SUBST - <body>
if (isset($focus_element)) {
    $tpl_main = str_replace('<body onload="', '<body onload="document.getElementById(\'' . $focus_element[0] . '\').' . $focus_element[1] . '.focus();', $tpl_main);
    $tpl_main = str_replace('<body>', '<body onload="document.getElementById(\'' . $focus_element[0] . '\').' . $focus_element[1] . '.focus();">', $tpl_main);
}
// END SUBST - <body>


if (isset($hint_box)) {
    $tpl_temp = $hint_box;
} else {
    $tpl_temp = null;
}


// START SUBST - <pun_page>
$tpl_main = str_replace('<pun_page>', htmlspecialchars(basename($_SERVER['PHP_SELF'], '.php')), $tpl_main);
// END SUBST - <pun_title>


// START SUBST - <pun_logo>
$tpl_main = str_replace('<pun_logo>', '<img src="' . PUN_ROOT . 'img/punbb.gif" alt="logo" />', $tpl_main);
// END SUBST - <pun_logo>


// START SUBST - <pun_title>
$tpl_main = str_replace('<pun_title>', '<h1><span>' . pun_htmlspecialchars($pun_config['o_board_title']) . '</span></h1>', $tpl_main);
// END SUBST - <pun_title>


// START SUBST - <pun_desc>
$tpl_main = str_replace('<pun_desc>', '<p><span>' . $pun_config['o_board_desc'] . '</span></p>', $tpl_main);
// END SUBST - <pun_desc>


// START SUBST - <pun_navlinks>
$tpl_main = str_replace('<pun_navlinks>', '<div id="brdmenu" class="inbox">' . generate_navlinks() . '</div>', $tpl_main);
// END SUBST - <pun_navlinks>


// START SUBST - <pun_status>
if ($pun_user['is_guest']) {
    /// MOD PRINTABLE TOPIC BEGIN
    $tpl_temp .= '<div id="brdwelcome" class="inbox"><ul class="conl"><li>' . $lang_common['Not logged in'] . '</li></ul>';

    /// MOD PRINTABLE TOPIC BEGIN
    if ($basename == 'viewtopic.php' && $id) {
        $tpl_temp .= '<ul class="conr"><li><span class="printable"><a href="viewprintable.php?id=' . $id . '">' . $lang_common['Print version'] . '</a></span></li></ul><div class="clearer"></div></div>';
    } else {
        $tpl_temp .= '<div class="clearer"></div></div>';
    }
    /// MOD PRINTABLE TOPIC END
} else {
    $tpl_temp .= '<div id="brdwelcome" class="inbox"><ul class="conl"><li>' . $lang_common['Logged in as'] . ' <strong>' . pun_htmlspecialchars($pun_user['username']) . '</strong></li><li>' . $lang_common['Last visit'] . ': ' . format_time($pun_user['last_visit']) . '</li>';

    if ($pun_user['g_id'] < PUN_GUEST) {
        $result_header = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'reports WHERE zapped IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

        if ($db->result($result_header)) {
            $tpl_temp .= '<li class="reportlink"><strong><a href="' . PUN_ROOT . 'admin_reports.php">' . $lang_admin['New reports'] . '</a></strong></li>';
        }

        if ($pun_config['o_maintenance'] == 1) {
            $tpl_temp .= '<li class="maintenancelink"><strong><a href="' . PUN_ROOT . 'admin_options.php#maintenance">' . $lang_admin['maintenance'] . '</a></strong></li>';
        }
    }
    // PMS MOD BEGIN
    include PUN_ROOT . 'include/pms/header_new_messages.php';
    // PMS MOD END

    if (in_array($basename, array('index.php', 'search.php'))) {
        $tpl_temp .= '</ul><ul class="conr"><li><a href="search.php?action=show_new">' . $lang_common['Show new posts'] . '</a></li><li><a href="misc.php?action=markread">' . $lang_common['Mark all as read'] . '</a></li></ul><div class="clearer"></div></div>';
    } elseif ($basename == 'viewforum.php') {
        // REAL MARK TOPICS AS READ MOD	BEGIN
        $tpl_temp .= '</ul><ul class="conr"><li><a href="misc.php?action=markread&amp;fid=' . $id . '">' . $lang_common['Mark all as read'] . '</a></li></ul><div class="clearer"></div></div>';
        // REAL MARK TOPICS AS READ MOD	END
    } else {
        /// MOD PRINTABLE TOPIC BEGIN
        if ($basename == 'viewtopic.php') {
            $tpl_temp .= '</ul><ul class="conr"><li><span class="printable"><a href="viewprintable.php?id=' . $id . '">' . $lang_common['Print version'] . '</a></span></li></ul><div class="clearer"></div></div>';
            /// MOD PRINTABLE TOPIC END
        } else {
            $tpl_temp .= '</ul><div class="clearer"></div></div>';
        }
    }
}


$tpl_main = str_replace('<pun_status>', $tpl_temp, $tpl_main);
$tpl_temp .= '<div id="announce" class="block"><h2><span>RSS</span></h2><div class="box"><div class="inbox"><div><a href="' . PUN_ROOT . 'rss.xml">RSS</a></div></div></div>';

// END SUBST - <pun_status>


// START SUBST - <pun_announcement>
if ($pun_config['o_announcement'] == 1) {
    ob_start();

    echo '<div id="announce" class="block"><h2><span>' . $lang_common['Announcement'] . '</span></h2><div class="box"><div class="inbox"><div>' . $pun_config['o_announcement_message'] . '</div></div></div></div>';

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
