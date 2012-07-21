<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'wap/header.php';

// REAL MARK TOPIC AS READ MOD BEGIN
if (!$pun_user['is_guest']) {
    $result = $db->query('DELETE FROM `' . $db->prefix . 'log_topics` WHERE log_time < ' . ($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']) . ' AND user_id=' . $pun_user['id']) or error('Unable to delete marked as read topic info', __FILE__, __LINE__, $db->error());
}

function is_reading($log_time, $last_post)
{
    if ($log_time > $last_post) {
        return true;
    }
    return false;
}
// REAL MARK TOPIC AS READ MOD END

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

$id = intval($_GET['id']);
if ($id < 1) {
    wap_message($lang_common['Bad request']);
}

// Load the viewforum.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/forum.php';

// Fetch some info about the forum
$result = $db->query('SELECT f.forum_name, f.redirect_url, f.moderators, f.num_topics, f.sort_by, fp.post_topics, lf.log_time, f.id as forum_id FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') LEFT JOIN '.$db->prefix.'log_forums AS lf ON (lf.user_id='.$pun_user['id'].' AND lf.forum_id=f.id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);

// REAL MARK TOPIC AS READ MOD BEGIN
if (!$pun_user['is_guest'] && !$cur_forum['log_time']) {
    $result = $db->query('INSERT INTO '.$db->prefix.'log_forums (user_id, forum_id, log_time) VALUES ('.$pun_user['id'].', '.$cur_forum['forum_id'].', '.$_SERVER['REQUEST_TIME'].')') or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query('UPDATE '.$db->prefix.'log_forums SET log_time='.$_SERVER['REQUEST_TIME'].' WHERE forum_id='.$cur_forum['forum_id'].' AND user_id='.$pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
}
// REAL MARK TOPIC AS READ MOD END


// Is this a redirect forum? In that case, redirect!
if ($cur_forum['redirect_url']) {
    header('Location: ' . $cur_forum['redirect_url'], true, 301);
    exit;
}

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = array();
if ($cur_forum['moderators']) {
    $mods_array = unserialize($cur_forum['moderators']);
}

$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($cur_forum['num_topics'] / $pun_user['disp_topics']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_topics'] * ($p - 1);

// Generate paging links
if (isset($_GET['action']) && $_GET['action'] == 'all') {
    $p = $num_pages + 1;
    $pun_user['disp_topics'] = $cur_forum['num_topics'];
}

// Fetch list of topics to display on this page
if ($pun_user['is_guest'] || !$pun_config['o_show_dot']) {
    // Without "the dot"
    // REAL MARK TOPIC AS READ MOD BEGIN
    $sql = '
        SELECT t.id, t.poster, t.has_poll, t.subject, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, lt.log_time, lf.mark_read
        FROM '.$db->prefix.'topics AS t
        LEFT JOIN '.$db->prefix.'log_topics AS lt ON lt.user_id='.$pun_user['id'].' AND lt.topic_id=t.id
        LEFT JOIN '.$db->prefix.'log_forums AS lf ON lf.forum_id=t.forum_id AND lf.user_id='.$pun_user['id'].'
        WHERE t.forum_id='.$id.'
        ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == 1) ? 'posted' : 'last_post').' DESC
        LIMIT '.$start_from.', '.$pun_user['disp_topics'];
    // REAL MARK TOPIC AS READ MOD END
} else {
    // With "the dot"
    // REAL MARK TOPIC AS READ MOD BEGIN
    $sql = '
        SELECT p.poster_id AS has_posted, t.has_poll, t.id, t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, lt.log_time, lf.mark_read
        FROM '.$db->prefix.'topics AS t
        LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id='.$pun_user['id'].'
        LEFT JOIN '.$db->prefix.'log_topics AS lt ON lt.user_id='.$pun_user['id'].' AND lt.topic_id=t.id
        LEFT JOIN '.$db->prefix.'log_forums AS lf ON lf.forum_id=t.forum_id AND lf.user_id='.$pun_user['id'].'
        WHERE t.forum_id='.$id.'
        GROUP BY t.id
        ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == 1) ? 'posted' : 'last_post').' DESC
        LIMIT '.$start_from.', '.$pun_user['disp_topics'];
    // REAL MARK TOPIC AS READ MOD END
}

$result = $db->query($sql) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

// If there are topics in this forum.
if ($db->num_rows($result)) {
    
    while ($cur_topic = $db->fetch_assoc($result)) {
        
        $cur_topic['num_pages_topic'] = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);
        $topics[] = $cur_topic;
    }
}

if ($pun_config['o_quickjump']) {
    
    require_once PUN_ROOT . 'include/quickjump.php';
}

$smarty->assign('is_admmod', $is_admmod);

$smarty->assign('pun_user', $pun_user);

$smarty->assign('cur_forum', $cur_forum);

$smarty->assign('page_title', $pun_config['o_board_title'] . ': ' . $cur_forum['forum_name']);

$smarty->assign('conditions', $conditions);

$smarty->assign('lang_forum', $lang_forum);
$smarty->assign('lang_common', $lang_common);

$smarty->assign('topics', $topics);

$smarty->assign('forum_id', $id);
$smarty->assign('id', $id);

$smarty->assign('start_from', $start_from);

$smarty->assign('p', $p);

$smarty->assign('paging_links', $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewforum.php?id='.$id));

$smarty->assign('basename', baseName($_SERVER['PHP_SELF']));

$smarty->display('viewforum.tpl');

?>
