<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';


if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

// Load the userlist.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/userlist.php';

// Load the search.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/search.php';


// Determine if we are allowed to view post counts
$show_post_count = ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) ? true : false;

$username = (isset($_GET['username']) && $pun_user['g_search_users'] == 1) ? pun_trim($_GET['username']) : '';
$show_group = (!isset($_GET['show_group']) || intval($_GET['show_group']) < -1 && intval($_GET['show_group']) > 2) ? -1 : intval($_GET['show_group']);
$sort_by = (!isset($_GET['sort_by']) || $_GET['sort_by'] != 'username' && $_GET['sort_by'] != 'registered' && ($_GET['sort_by'] != 'num_posts' || !$show_post_count)) ? 'username' : $_GET['sort_by'];
$sort_dir = (!isset($_GET['sort_dir']) || $_GET['sort_dir'] != 'ASC' && $_GET['sort_dir'] != 'DESC') ? 'ASC' : mb_strtoupper($_GET['sort_dir']);


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['User list'];
if ($pun_user['g_search_users'] == 1) {
    $focus_element = array('userlist', 'username');
}

define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT . 'wap/header.php';


echo '<div class="con"><strong>'.$lang_search['User search'].'</strong></div>
<form method="get" action="userlist.php?">
<div class="input">
<strong>'.$lang_ul['User find legend'].'</strong><br/>';
if ($pun_user['g_search_users'] == 1) {
    echo $lang_common['Username'].'<br /><input type="text" name="username" value="'.pun_htmlspecialchars($username).'" maxlength="25" /><br />';
}
echo $lang_ul['User group'].'<br /><select name="show_group"><option value="-1"' . (($show_group == -1) ? ' selected="selected"' : '') . '>'.$lang_ul['All users'].'</option>';

$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.PUN_GUEST.' ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result)) {
    if ($cur_group['g_id'] == $show_group) {
        echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.pun_htmlspecialchars($cur_group['g_title']).'</option>';
    } else {
        echo '<option value="'.$cur_group['g_id'].'">'.pun_htmlspecialchars($cur_group['g_title']).'</option>';
    }
}


echo '</select><br />
'.$lang_search['Sort by'].'<br />
<select name="sort_by"><option value="username"';
if($sort_by == 'username') {
    echo ' selected="selected"';
}
echo '>'.$lang_common['Username'].'</option>
<option value="registered"';
if ($sort_by == 'registered') {
    echo ' selected="selected"';
}
echo '>'.$lang_common['Registered'].'</option>';
if ($show_post_count) {
    echo '<option value="num_posts"';
    if ($sort_by == 'num_posts') {
        echo ' selected="selected"';
    }
    echo '>'.$lang_ul['No of posts'].'</option>';
}
echo '</select><br />
'.$lang_search['Sort order'].'<br />
<select name="sort_dir"><option value="ASC"';
if ($sort_dir == 'ASC') {
    echo ' selected="selected">';
}
echo $lang_search['Ascending'].'</option><option value="DESC"';
if ($sort_dir == 'DESC') {
    echo ' selected="selected"';
}
echo '>'.$lang_search['Descending'].'</option></select></div>
<div class="input2">'.$lang_ul['User search info'].'</div>
<div class="go_to"><input type="submit" name="search" value="'.$lang_common['Submit'].'" accesskey="s" /></div></form>';

// Create any SQL for the WHERE clause
$where_sql = array();
$like_command = 'LIKE';

if ($pun_user['g_search_users'] == 1 && $username) {
    $where_sql[] = 'u.username ' . $like_command . ' \'' . $db->escape(str_replace('*', '%', $username)) . '\'';
}
if ($show_group > -1) {
    $where_sql[] = 'u.group_id='.$show_group;
}

// Fetch user count
$result = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'users AS u WHERE u.id>1'.(($where_sql) ? ' AND '.implode(' AND ', $where_sql) : '')) or error('Unable to fetch user list count', __FILE__, __LINE__, $db->error());
$num_users = $db->result($result);


// Determine the user offset (based on $_GET['p'])
$num_pages = ceil($num_users / 50);

if (isset($_GET['action']) && $_GET['action'] == 'all') {
    $p = $num_pages + 1;
    $start_from = -1;
} else {
    $_GET['p'] = intval($_GET['p']);
    $p = ($_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
    $start_from = 50 * ($p - 1);
}


// Generate paging links
$paging_links = $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'userlist.php?username=' . urlencode($username) . '&amp;show_group=' . $show_group . '&amp;sort_by=' . $sort_by . '&amp;sort_dir=' . mb_strtoupper($sort_dir), 0);
$j = false;


echo '<div class="con"><strong>'.$lang_common['User list'].'</strong></div>
<div class="navlinks">
'.$lang_common['Username'];

if ($show_post_count) {
    echo ' | ' . $lang_common['Posts'];
}
echo ' | ' .$lang_common['Title'].' | '.$lang_common['Registered'].'</div>';


// Grab the users
$result = $db->query('SELECT u.id, u.username, u.title, u.num_posts, u.registered, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1'.(!empty($where_sql) ? ' AND '.implode(' AND ', $where_sql) : '').' ORDER BY '.$sort_by.' '.$sort_dir.', u.id ASC ' . ($start_from != -1 ? 'LIMIT '.$start_from.', 50' : '')) or error('Unable to fetch user list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    while ($user_data = $db->fetch_assoc($result)) {
        $user_title_field = get_title($user_data);

        echo '<div class="' . (($j = !$j) ? 'in' : 'in2') . '"><strong><a href="profile.php?id='.$user_data['id'].'">'.pun_htmlspecialchars($user_data['username']).'</a></strong> ';
        if ($show_post_count) {
            echo ' ['.$user_data['num_posts'].'] ';
        }
        echo $user_title_field.' ('.format_time($user_data['registered'], true).')</div>';
    }
} else {
    echo '<div class="msg">' . $lang_search['No hits']. '</div>';
}

echo '<div class="con">'.$paging_links.'</div>';

require_once PUN_ROOT.'wap/footer.php';

?>
