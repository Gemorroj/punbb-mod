<?php

define('PUN_ROOT', '../');
require_once PUN_ROOT . 'include/common.php';

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

// Load the userlist.php language file
require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/userlist.php';

// Load the search.php language file
require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/search.php';


// Determine if we are allowed to view post counts
$show_post_count = ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) ? true : false;

$username = (isset($_GET['username']) && $pun_user['g_search_users'] == 1) ? pun_trim($_GET['username']) : '';
$show_group = (!isset($_GET['show_group']) || intval($_GET['show_group']) < -1 && intval($_GET['show_group']) > 2) ? -1 : intval($_GET['show_group']);
$sort_by = (!isset($_GET['sort_by']) || $_GET['sort_by'] != 'username' && $_GET['sort_by'] != 'registered' && ($_GET['sort_by'] != 'num_posts' || !$show_post_count)) ? 'username' : $_GET['sort_by'];
$sort_dir = (!isset($_GET['sort_dir']) || $_GET['sort_dir'] != 'ASC' && $_GET['sort_dir'] != 'DESC') ? 'ASC' : mb_strtoupper($_GET['sort_dir']);


$page_title = $pun_config['o_board_title'] . ' / ' . $lang_common['User list'];
if ($pun_user['g_search_users'] == 1) {
    $focus_element = array('userlist', 'username');
}

define('PUN_ALLOW_INDEX', 1);

$result = $db->query('SELECT g_id, g_title FROM `' . $db->prefix . 'groups` WHERE g_id!=' . PUN_GUEST . ' ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result)) {
    while ($cur_group = $db->fetch_assoc($result)) {

        $groups[] = $cur_group;
    }
}

// Create any SQL for the WHERE clause
$where_sql = array();
$like_command = 'LIKE';

if ($pun_user['g_search_users'] == 1 && $username) {
    $where_sql[] = 'u.username ' . $like_command . ' \'' . $db->escape(str_replace('*', '%', $username)) . '\'';
}
if ($show_group > -1) {
    $where_sql[] = 'u.group_id=' . $show_group;
}

// Fetch user count
$result = $db->query('SELECT COUNT(1) FROM `' . $db->prefix . 'users` AS u WHERE u.id>1' . (($where_sql) ? ' AND ' . implode(' AND ', $where_sql) : '')) or error('Unable to fetch user list count', __FILE__, __LINE__, $db->error());
$num_users = $db->result($result);

// Determine the user offset (based on $_GET['p'])
$num_pages = ceil($num_users / 50);

if (isset($_GET['action']) && $_GET['action'] == 'all') {
    $p = $num_pages + 1;
    $start_from = -1;
} else {
    $p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
    $start_from = 50 * ($p - 1);
}

// Generate paging links

$paging_links = paginate(
    $num_pages,
    $p,
    'userlist.php?username=' . urlencode($username) . '&amp;show_group=' . $show_group . '&amp;sort_by=' . $sort_by . '&amp;sort_dir=' . mb_strtoupper($sort_dir)
);

//$smarty->assign('paging_links', $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'userlist.php?username=' . urlencode($username) . '&amp;show_group=' . $show_group . '&amp;sort_by=' . $sort_by . '&amp;sort_dir=' . mb_strtoupper($sort_dir), 0));

// Grab the users
$result = $db->query('SELECT u.id, u.username, u.title, u.num_posts, u.registered, g.g_id, g.g_user_title FROM `' . $db->prefix . 'users` AS u LEFT JOIN `' . $db->prefix . 'groups` AS g ON g.g_id=u.group_id WHERE u.id>1' . (!empty($where_sql) ? ' AND ' . implode(' AND ', $where_sql) : '') . ' ORDER BY ' . $sort_by . ' ' . $sort_dir . ', u.id ASC ' . ($start_from != -1 ? 'LIMIT ' . $start_from . ', 50' : '')) or error('Unable to fetch user list', __FILE__, __LINE__, $db->error());
$users = array();
if ($db->num_rows($result)) {
    while ($user_data = $db->fetch_assoc($result)) {
        $users[] = $user_data;
    }
}

require_once PUN_ROOT . 'wap/header.php';

$smarty->assign('page_title', $page_title);
$smarty->assign('username', $username);
$smarty->assign('show_group', $show_group);
$smarty->assign('sort_by', $sort_by);
$smarty->assign('sort_dir', $sort_dir);


$smarty->assign('show_post_count', $show_post_count);


$smarty->assign('lang_ul', $lang_ul);
$smarty->assign('lang_search', $lang_search);
$smarty->assign('p', $p);
$smarty->assign('paging_links', $paging_links);
$smarty->assign('groups', $groups);
$smarty->assign('users', $users);

$smarty->display('userlist.tpl');
