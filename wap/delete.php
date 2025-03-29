<?php

\define('PUN_ROOT', '../');

require_once PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    \wap_message($lang_common['No view']);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (1 > $id) {
    \wap_message($lang_common['Bad request']);
}

// Fetch some info about the post, the topic and the forum
$result = $db->query(
    'SELECT `f`.`id` AS `fid`, '
        .'`f`.`forum_name`, '
        .'`f`.`moderators`, '
        .'`f`.`redirect_url`, '
        .'`fp`.`post_replies`, '
        .'`fp`.`post_topics`, '
        .'`t`.`id` AS `tid`, '
        .'`t`.`subject`, '
        .'`t`.`posted`, '
        .'`t`.`closed`, '
        .'`p`.`poster`, '
        .'`p`.`poster_id`, '
        .'`p`.`message`, '
        .'`p`.`hide_smilies` '
        .'FROM `'.$db->prefix.'posts` AS `p` '
        .'INNER JOIN `'.$db->prefix.'topics` AS `t` '
        .'ON `t`.`id`=`p`.`topic_id` '
        .'INNER JOIN `'.$db->prefix.'forums` AS `f` '
        .'ON `f`.`id`=`t`.`forum_id` '
        .'LEFT JOIN `'.$db->prefix.'forum_perms` AS `fp` '
        .'ON (`fp`.`forum_id`=`f`.`id` '
        .'AND `fp`.`group_id`='.$pun_user['g_id'].') '
        .'WHERE (`fp`.`read_forum` IS NULL OR `fp`.`read_forum`=1) '
        .'AND `p`.`id`='.$id
);
if (!$result) {
    \error(
        'Unable to fetch post info',
        __FILE__,
        __LINE__,
        $db->error()
    );
}
if (!$db->num_rows($result)) {
    \wap_message($lang_common['Bad request']);
}

$cur_post = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators']) ? \unserialize($cur_post['moderators'], ['allowed_classes' => false]) : [];
$is_admmod = (PUN_ADMIN == $pun_user['g_id'] || (PUN_MOD == $pun_user['g_id'] && \array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Determine whether this post is the "topic post" or not
$result = $db->query(
    'SELECT `id` '
        .'FROM `'.$db->prefix.'posts` '
        .'WHERE `topic_id`='.$cur_post['tid'].' '
        .'ORDER BY `posted` '
        .'LIMIT 1;'
);
if (!$result) {
    \error(
        'Unable to fetch post info',
        __FILE__,
        __LINE__,
        $db->error()
    );
}
$topic_post_id = $db->result($result);

$is_topic_post = ($id == $topic_post_id);

// Do we have permission to edit this post?
if ((!$pun_user['g_delete_posts']
     || (!$pun_user['g_delete_topics']
         && $is_topic_post)
     || $cur_post['poster_id'] != $pun_user['id']
     || 1 == $cur_post['closed'])
    && !$is_admmod
) {
    \wap_message($lang_common['No permission']);
}

if (isset($_POST['delete'])) {
    require_once PUN_ROOT.'include/search_idx.php';

    if ($is_topic_post) {
        // Delete the topic and all of it's posts
        \delete_topic($cur_post['tid']);
        \update_forum($cur_post['fid']);

        \wap_redirect('viewforum.php?id='.$cur_post['fid']);
    } else {
        // Delete just this one post
        \delete_post($id, $cur_post['tid']);
        \update_forum($cur_post['fid']);

        \wap_redirect('viewtopic.php?id='.$cur_post['tid']);
    }
}

// Load the delete.php language file
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/delete.php';

require_once PUN_ROOT.'wap/header.php';

require_once PUN_ROOT.'include/parser.php'; // parser.php будет использоваться в шаблоне.

$page_title = $pun_config['o_board_title'].' / '.$lang_delete['Delete post'];
$cur_post['message'] = \parse_message($cur_post['message'], $cur_post['hide_smilies'], $id);

$smarty->assign('page_title', $page_title);
$smarty->assign('cur_post', $cur_post);
$smarty->assign('id', $id);
$smarty->assign('lang_delete', $lang_delete);

$smarty->display('delete.tpl');
