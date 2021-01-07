<?php

\define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

$id = isset($_GET['id']) ? \intval($_GET['id']) : 0;
if ($id < 1) {
    wap_message($lang_common['Bad request']);
}

// Load the viewtopic.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

// Fetch some info about the topic
//if (!$pun_user['is_guest'])
// MOD: MARK TOPICS AS READ - 1 LINE MODIFIED CODE FOLLOWS
//	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.last_post, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
//else

$result = $db->query('SELECT t.subject, t.num_replies, f.id AS forum_id, f.forum_name, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE t.id='.$id) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_topic = $db->fetch_assoc($result);

$page_title = $pun_config['o_board_title'].' / '.$cur_topic['subject'];

include_once PUN_ROOT.'include/parser.php';

// Retrieve the posts (and their respective poster)
$result = $db->query('SELECT p.poster AS username, p.id, p.message, p.posted FROM '.$db->prefix.'posts AS p WHERE p.topic_id='.$id.' ORDER BY p.id') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$posts = array();
while ($cur_post = $db->fetch_assoc($result)) {
    if (1 == $pun_config['o_censoring']) {
        $cur_post['message'] = censor_words($cur_post['message']);
    }
    $cur_post['message'] = parse_message($cur_post['message'], true, $cur_post['id']);
    $posts[] = $cur_post;
}

require PUN_ROOT.'wap/header.php';

$smarty->assign('page_title', $page_title);
$smarty->assign('posts', $posts);
$smarty->assign('cur_topic', $cur_topic);
$smarty->assign('id', $id);

$smarty->display('viewprintable.tpl');
