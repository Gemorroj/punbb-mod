<?php

\define('PUN_ROOT', '../');

require_once PUN_ROOT.'include/common.php';

function is_reading($log_time, $last_post)
{
    return $log_time > $last_post;
}

//+ REAL MARK TOPIC AS READ MOD
if (!$pun_user['is_guest']) {
    $result = $db->query(
        'DELETE '
        .'FROM `'.$db->prefix.'log_topics` '
        .'WHERE `log_time` < '.($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']).' '
        .'AND `user_id`='.$pun_user['id']
    ) or error('Unable to delete marked as read topic info', __FILE__, __LINE__, $db->error());
}
//- REAL MARK TOPIC AS READ MOD

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (1 > $id) {
    wap_message($lang_common['Bad request']);
}

// Fetch some info about the forum
$result = $db->query(
    'SELECT `f`.`forum_name`, '
.'`f`.`redirect_url`, '
.'`f`.`moderators`, '
.'`f`.`num_topics`, '
.'`f`.`sort_by`, '
.'`fp`.`post_topics`, '
.'`lf`.`log_time`, '
.'`f`.`id` AS `forum_id` '
.'FROM `'.$db->prefix.'forums` AS `f` '
.'LEFT JOIN `'.$db->prefix.'forum_perms` AS `fp` '
.'ON (`fp`.`forum_id`=`f`.`id` AND `fp`.`group_id`='.$pun_user['g_id'].') '
.'LEFT JOIN `'.$db->prefix.'log_forums` AS `lf` '
.'ON (`lf`.`user_id`='.$pun_user['id'].' AND `lf`.`forum_id`=`f`.`id`) '
.'WHERE (`fp`.`read_forum` IS NULL OR `fp`.`read_forum`=1) '
.'AND `f`.`id`='.$id
)
or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);

//+ REAL MARK TOPIC AS READ MOD
if (!($pun_user['is_guest'] || $cur_forum['log_time'])) {
    $result = $db->query(
        'INSERT INTO `'.$db->prefix.'log_forums` '
    .'(`user_id`, `forum_id`, `log_time`) '
    .'VALUES ('.$pun_user['id'].', '
    .$cur_forum['forum_id'].', '
    .$_SERVER['REQUEST_TIME'].')'
    )
    or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query(
        'UPDATE `'.$db->prefix.'log_forums` '
    .'SET `log_time`='.$_SERVER['REQUEST_TIME'].' '
    .'WHERE `forum_id`='.$cur_forum['forum_id'].' '
    .'AND `user_id`='.$pun_user['id']
    )
    or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
}
//- REAL MARK TOPIC AS READ MOD

// Is this a redirect forum? In that case, redirect!
if ($cur_forum['redirect_url']) {
    wap_redirect($cur_forum['redirect_url']);
}

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = [];
if ($cur_forum['moderators']) {
    $mods_array = \unserialize($cur_forum['moderators']);
}

$is_admmod = false;
if (PUN_ADMIN == $pun_user['g_id']
    || (PUN_MOD == $pun_user['g_id']
        && \array_key_exists(
            $pun_user['username'],
            $mods_array
        ))
    ) {
    $is_admmod = true;
}

//+ Pagination
// Determine the topic offset (based on $_GET['p'])
$num_pages = \ceil($cur_forum['num_topics'] / $pun_user['disp_topics']);
$p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
$start_from = $pun_user['disp_topics'] * ($p - 1);
// Generate paging links
if ('all' == @$_GET['action']) {
    $p = $num_pages + 1;
    $pun_user['disp_topics'] = $cur_forum['num_topics'];
    $start_from = 0;
}

$paging_links = paginate($num_pages, $p, 'viewforum.php?id='.$id);
//- Pagination

// Fetch list of topics to display on this page
//+ REAL MARK TOPIC AS READ MOD
// ......................... Without "the dot"
if ($pun_user['is_guest'] || !$pun_config['o_show_dot']) {
    $sql = 'SELECT `t`.`id`, '
         .'`t`.`poster`, '
         .'`t`.`has_poll`, '
         .'`t`.`subject`, '
         .'`t`.`posted`, '
         .'`t`.`last_post`, '
         .'`t`.`last_post_id`, '
         .'`t`.`last_poster`, '
         .'`t`.`num_views`, '
         .'`t`.`num_replies`, '
         .'`t`.`closed`, '
         .'`t`.`sticky`, '
         .'`t`.`moved_to`, '
         .'`lt`.`log_time`, '
         .'`lf`.`mark_read` '
         .'FROM `'.$db->prefix.'topics` AS `t` '
         .'LEFT JOIN `'.$db->prefix.'log_topics` AS `lt` '
         .'ON `lt`.`user_id`='.$pun_user['id'].' AND `lt`.`topic_id`=`t`.`id` '
         .'LEFT JOIN `'.$db->prefix.'log_forums` AS `lf` '
         .'ON `lf`.`forum_id`=`t`.`forum_id` AND `lf`.`user_id`='.$pun_user['id'].' '
         .'WHERE `t`.`forum_id`='.$id.' '
         .'ORDER BY `sticky` DESC, '.(1 == $cur_forum['sort_by'] ? 'posted' : 'last_post').' DESC '
         .'LIMIT '.$start_from.', '.$pun_user['disp_topics'];
} else {
    $sql = 'SELECT `t`.`id`, '
         .'`t`.`poster`, '
         .'`t`.`has_poll`, '
         .'`t`.`subject`, '
         .'`t`.`posted`, '
         .'`t`.`last_post`, '
         .'`t`.`last_post_id`, '
         .'`t`.`last_poster`, '
         .'`t`.`num_views`, '
         .'`t`.`num_replies`, '
         .'`t`.`closed`, '
         .'`t`.`sticky`, '
         .'`t`.`moved_to`, '
         .'`lt`.`log_time`, '
         .'`lf`.`mark_read`, '
         .'`p`.`poster_id` AS `has_posted` '
         .'FROM `'.$db->prefix.'topics` AS `t` '
         .'LEFT JOIN `'.$db->prefix.'log_topics` AS `lt` '
         .'ON `lt`.`user_id`='.$pun_user['id'].' AND `lt`.`topic_id`=`t`.`id` '
         .'LEFT JOIN `'.$db->prefix.'log_forums` AS `lf` '
         .'ON `lf`.`forum_id`=`t`.`forum_id` AND `lf`.`user_id`='.$pun_user['id'].' '
         .'LEFT JOIN `'.$db->prefix.'posts` AS `p` '
         .'ON `t`.`id`=`p`.`topic_id` AND `p`.`poster_id`='.$pun_user['id'].' '
         .'WHERE `t`.`forum_id`='.$id.' '
         .'GROUP BY `t`.`id` '
         .'ORDER BY `sticky` DESC, '.(1 == $cur_forum['sort_by'] ? 'posted' : 'last_post').' DESC '
         .'LIMIT '.$start_from.', '.$pun_user['disp_topics'];
}
//- REAL MARK TOPIC AS READ

$result = $db->query($sql) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

// If there are topics in this forum.
$topics = [];
if ($db->num_rows($result)) {
    while ($cur_topic = $db->fetch_assoc($result)) {
        // Pagination in topics on index page.
        $num_pages_topic = \ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);
        if (1 < $num_pages_topic) {
            $cur_topic['paging_links'] = paginate($num_pages_topic, -1, 'viewtopic.php?id='.$cur_topic['id']);
        }
        if (1 == $pun_config['o_censoring']) {
            $cur_topic['subject'] = censor_words($cur_topic['subject']);
        }
        $topics[] = $cur_topic;
    }
}

//+ Language
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/forum.php';
//- Language

// Template Manager aka Smarty
// Механизм проверки ящика сообщений, отчетов...
require_once PUN_ROOT.'wap/header.php';

$page_title = $pun_config['o_board_title'].' / '.$cur_forum['forum_name'];
$smarty->assign('page_title', $page_title);

if (1 == $pun_config['o_quickjump']) {
    $forum_id = $id;
    $smarty->assign('quickjump', include PUN_ROOT.'include/wap_quickjump.php');
}

$smarty->assign('is_admmod', $is_admmod);
$smarty->assign('cur_forum', $cur_forum);
$smarty->assign('lang_forum', $lang_forum);
$smarty->assign('topics', $topics);
$smarty->assign('forum_id', $id);
$smarty->assign('id', $id);
$smarty->assign('start_from', $start_from);
$smarty->assign('p', $p);
$smarty->assign('paging_links', $paging_links);

$smarty->display('viewforum.tpl');
