<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    \message($lang_common['No view']);
}

// Load the userlist.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/userlist.php';

// Load the search.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/search.php';

// Determine if we are allowed to view post counts
$show_post_count = (1 == $pun_config['o_show_post_count'] || $pun_user['g_id'] < PUN_GUEST) ? true : false;

$username = (isset($_GET['username']) && 1 == $pun_user['g_search_users']) ? \trim($_GET['username']) : '';
$show_group = (!isset($_GET['show_group']) || ((int) $_GET['show_group'] < -1 && (int) $_GET['show_group'] > 2)) ? -1 : (int) ($_GET['show_group']);
$sort_by = (!isset($_GET['sort_by']) || ('username' !== $_GET['sort_by'] && 'registered' !== $_GET['sort_by'] && ('num_posts' !== $_GET['sort_by'] || !$show_post_count))) ? 'username' : $_GET['sort_by'];
$sort_dir = (!isset($_GET['sort_dir']) || ('ASC' !== $_GET['sort_dir'] && 'DESC' !== $_GET['sort_dir'])) ? 'ASC' : \strtoupper($_GET['sort_dir']);

$page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_common['User list'];
if (1 == $pun_user['g_search_users']) {
    $focus_element = ['userlist', 'username'];
}

\define('PUN_ALLOW_INDEX', 1);

require_once PUN_ROOT.'header.php';

echo '<div class="blockform"><h2><span>'.$lang_search['User search'].'</span></h2>
<div class="box">
<form id="userlist" method="get" action="userlist.php?">
<div class="inform">
<fieldset>
<legend>'.$lang_ul['User find legend'].'</legend>
<div class="infldset">';
if (1 == $pun_user['g_search_users']) {
    echo '<label class="conl">'.$lang_common['Username'].'<br /><input type="text" name="username" value="'.\pun_htmlspecialchars($username).'" size="25" maxlength="25" /><br /></label>';
}
echo '<label class="conl">'.$lang_ul['User group'].'<br /><select name="show_group"><option value="-1"';
if (-1 == $show_group) {
    echo ' selected="selected"';
}
echo '>'.$lang_ul['All users'].'</option>';

$result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` WHERE g_id!='.PUN_GUEST.' ORDER BY g_id') || \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result)) {
    if ($cur_group['g_id'] == $show_group) {
        echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    } else {
        echo '<option value="'.$cur_group['g_id'].'">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    }
}

echo '</select>
<br /></label>
<label class="conl">'.$lang_search['Sort by'].'<br /><select name="sort_by">
<option value="username"';
if ('username' == $sort_by) {
    echo ' selected="selected"';
}
echo '>'.$lang_common['Username'].'</option><option value="registered"';
if ('registered' == $sort_by) {
    echo ' selected="selected"';
}
echo '>'.$lang_common['Registered'].'</option>';
if ($show_post_count) {
    echo '<option value="num_posts"';
    if ('num_posts' == $sort_by) {
        echo ' selected="selected"';
    }
    echo '>'.$lang_ul['No of posts'].'</option>';
}
echo '</select><br /></label><label class="conl">'.$lang_search['Sort order'].'<br /><select name="sort_dir"><option value="ASC"';
if ('ASC' == $sort_dir) {
    echo ' selected="selected"';
}
echo '>'.$lang_search['Ascending'].'</option><option value="DESC"';
if ('DESC' == $sort_dir) {
    echo ' selected="selected"';
}
echo '>'.$lang_search['Descending'].'</option></select><br /></label>
<p class="clearb">'.$lang_ul['User search info'].'</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="search" value="'.$lang_common['Submit'].'" accesskey="s" /></p>
</form>
</div>
</div>';

// Create any SQL for the WHERE clause
$where_sql = [];
$like_command = 'LIKE';

if (1 == $pun_user['g_search_users'] && $username) {
    $where_sql[] = 'u.username '.$like_command.' \''.$db->escape(\str_replace('*', '%', $username)).'\'';
}
if ($show_group > -1) {
    $where_sql[] = 'u.group_id='.$show_group;
}

// Fetch user count
$result = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'users AS u WHERE u.id>1'.(($where_sql) ? ' AND '.\implode(' AND ', $where_sql) : '')) || \error('Unable to fetch user list count', __FILE__, __LINE__, $db->error());
$num_users = $db->result($result);

// Determine the user offset (based on $_GET['p'])
$num_pages = \ceil($num_users / 50);

if (isset($_GET['action']) && 'all' == $_GET['action']) {
    $p = $num_pages + 1;
    $start_from = -1;
} else {
    $p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
    $start_from = 50 * ($p - 1);
}

// Generate paging links
$paging_links = $lang_common['Pages'].': '.\paginate($num_pages, $p, 'userlist.php?username='.\urlencode($username).'&amp;show_group='.$show_group.'&amp;sort_by='.$sort_by.'&amp;sort_dir='.\strtoupper($sort_dir));

echo '<div class="linkst"><div class="inbox"><p class="pagelink">'.$paging_links.'</p></div></div>
<div id="users1" class="blocktable">
<h2><span>'.$lang_common['User list'].'</span></h2>
<div class="box">
<div class="inbox">
<table cellspacing="0"><thead>
<tr>
<th class="tcl" scope="col">'.$lang_common['Username'].'</th>
<th class="tc2" scope="col">'.$lang_common['Title'].'</th>';
if ($show_post_count) {
    echo '<th class="tc3" scope="col">'.$lang_common['Posts'].'</th>';
}
echo '<th class="tcr" scope="col">'.$lang_common['Registered'].'</th></tr></thead><tbody>';

// Grab the users
$result = $db->query('SELECT u.id, u.username, u.title, u.num_posts, u.registered, g.g_id, g.g_user_title FROM `'.$db->prefix.'users` AS u LEFT JOIN `'.$db->prefix.'groups` AS g ON g.g_id=u.group_id WHERE u.id>1'.(!empty($where_sql) ? ' AND '.\implode(' AND ', $where_sql) : '').' ORDER BY '.$sort_by.' '.$sort_dir.', u.id ASC '.(-1 != $start_from ? 'LIMIT '.$start_from.', 50' : '')) || \error('Unable to fetch user list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    while ($user_data = $db->fetch_assoc($result)) {
        $user_title_field = \get_title($user_data);

        echo '<tr><td class="tcl"><a href="profile.php?id='.$user_data['id'].'">'.\pun_htmlspecialchars($user_data['username']).'</a></td><td class="tc2">'.$user_title_field.'</td>';
        if ($show_post_count) {
            echo '<td class="tc3">'.$user_data['num_posts'].'</td>';
        }
        echo '<td class="tcr">'.\format_time($user_data['registered'], true).'</td></tr>';
    }
} else {
    echo '<tr><td class="tcl" colspan="'.(($show_post_count) ? 4 : 3).'">'.$lang_search['No hits'].'</td></tr>';
}

echo '</tbody></table>
</div>
</div>
</div>
<div class="linksb"><div class="inbox"><p class="pagelink">'.$paging_links.'</p></div></div>';

require_once PUN_ROOT.'footer.php';
