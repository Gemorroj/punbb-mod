<?php
define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
	wap_message($lang_common['No view']);
}

// Load the index.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/index.php';

if ($pun_config['o_pms_enabled'] && $pun_user['g_pm'] == 1)
require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';

require_once PUN_ROOT . 'wap/header.php';

//$page_title = pun_htmlspecialchars($pun_config['o_board_title']);
$smarty->assign('page_title', $pun_config['o_board_title']);

define('PUN_ALLOW_INDEX', 1);
//require_once PUN_ROOT . 'wap/header.php';

// REAL MARK TOPIC AS READ MOD BEGIN
// под вопросом!
if (!$pun_user['is_guest']) {
    $db->query('DELETE FROM `' . $db->prefix . 'log_forums` WHERE log_time < ' . ($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']) . ' AND user_id=' . $pun_user['id']) or error('Unable to delete marked as read forum info', __FILE__, __LINE__, $db->error());
}
// REAL MARK TOPIC AS READ MOD END

// Print the categories and forums


// Add Topic Title Info to Last Post column MOD BEGIN
$result = $db->query('
    SELECT c.id AS cid,
    c.cat_name,
    f.id AS fid,
    f.forum_name,
    f.forum_desc,
    f.redirect_url,
    f.moderators,
    f.num_topics,
    f.num_posts,
    f.last_post,
    f.last_post_id,
    lf.log_time,
    lf.mark_read,
    f.last_poster,
    t.subject,
    p.poster_id

    FROM ' . $db->prefix . 'categories AS c
    INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id
    LEFT JOIN ' . $db->prefix . 'topics AS t ON f.last_post_id=t.last_post_id
    LEFT JOIN ' . $db->prefix . 'log_forums AS lf ON lf.user_id=' . $pun_user['id'] . ' AND lf.forum_id=f.id
    LEFT JOIN ' . $db->prefix . 'posts AS p ON f.last_post_id=p.id
    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')

    WHERE fp.read_forum IS NULL OR fp.read_forum=1

    ORDER BY c.disp_position, c.id, f.disp_position
', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
// REAL MARK TOPIC AS READ MOD END

while ($cur_forum = $db->fetch_assoc($result)) {
    
    $forums[] = $cur_forum;
}

$smarty->assign('forums', $forums);

$smarty->assign('lang_index', $lang_index);
$smarty->assign('lang_common', $lang_common);

$smarty->assign('lang_pms', $lang_pms);

$smarty->assign('pun_config', $pun_config);
$smarty->assign('pun_user', $pun_user);

$smarty->assign('basename', baseName($_SERVER['PHP_SELF']));

// Collect some statistics from the database
$result = $db->query('SELECT COUNT(1) - 1 FROM `' . $db->prefix . 'users` LIMIT 1') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['total_users'] = $db->result($result);
$result = $db->query('SELECT `id`, `username` FROM `' . $db->prefix . 'users` ORDER BY `registered` DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
$stats['last_user'] = $db->fetch_assoc($result);
$result = $db->query('SELECT SUM(`num_topics`), SUM(`num_posts`) FROM `' . $db->prefix . 'forums` LIMIT 1') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);

//echo '<div class="navlinks">' . generate_wap_navlinks() . '</div>
//echo '<div class="incqbox">' . $lang_index['No of users'] . ': ' . $stats['total_users'] . '<br/>' . $lang_index['No of topics'] . ': ' . $stats['total_topics'] . '<br/>' . $lang_index['No of posts'] . ': ' . $stats['total_posts'] . '<br/>';

$smarty->assign('stats', $stats);

$num_users = 0;
if ($pun_config['o_users_online'] == 1) {
    // Fetch users online info and generate strings for output
    $result = $db->query('SELECT user_id, ident FROM ' . $db->prefix . 'online WHERE idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    while ($pun_user_online = $db->fetch_assoc($result)) {
        if ($pun_user_online['user_id'] > 1) {
            
            $users[] = $pun_user_online;
        } else {
            
        	++$num_guests;
        }
    }

    $num_users = sizeof($users);
    
    if ($num_users > 0) {
        $smarty->assign('num_users', $num_users);
        $smarty->assign('users', $users);
    }
    
    if ($num_guests) $smarty->assign('num_guests', $num_guests);
}

$smarty->assign('logout', sha1($pun_user['id'] . sha1(get_remote_address())));
$smarty->display('index.tpl');
exit();