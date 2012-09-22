<?php

define('PUN_ROOT', '../');

require_once(PUN_ROOT . 'include/common.php');

if ($pun_user['g_id'] <> PUN_MOD
    && $pun_user['g_id'] <> PUN_ADMIN
) {
    wap_message($lang_common['No permission']);
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
} else {
    wap_message($lang_common['Bad request']);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    wap_message($lang_common['Bad request']);
}

switch ($action) {
    default:
        wap_message($lang_common['Bad request']);
        exit();
    break;

    case 'allow':
        $result = $db->query(
                'SELECT `s`.`post_id` AS `pid`, '
                . '`t`.`forum_id` AS `fid`, '
                . '`t`.`id` AS `tid` '
                . '`s`.`message`, '
                . 'FROM `' . $db->prefix . 'spam_repository` AS `s` '
                . 'LEFT JOIN `' . $db->prefix . 'posts` AS `p` '
                . 'ON `p`.`id` = `s`.`post_id` '
                . 'LEFT JOIN `' . $db->prefix . 'topics` AS `t` '
                . 'ON `t`.`id` = `p`.`topic_id` '
                . 'WHERE `s`.`id`=' . $id)
        or error('Unable check spam info',
                 __FILE__,
                 __LINE__,
                 $db->error());
    break;

    case 'deny':
        $result = $db->query(
                'SELECT `s`.`post_id` AS `pid`, '
                . '`t`.`forum_id` AS `fid`, '
                . '`t`.`id` AS `tid` '
                . 'FROM `' . $db->prefix . 'spam_repository` AS `s` '
                . 'LEFT JOIN `' . $db->prefix . 'posts` AS `p` '
                . 'ON `p`.`id` = `s`.`post_id` '
                . 'LEFT JOIN `' . $db->prefix . 'topics` AS `t` '
                . 'ON `t`.`id` = `p`.`topic_id` '
                . 'WHERE `s`.`id`=' . $id)
        or error('Unable check spam info',
                 __FILE__,
                 __LINE__,
                 $db->error());

        $db->query(
        'DELETE '
        . 'FROM `' . $db->prefix . 'spam_repository` '
        . 'WHERE `id`=' . $id)
        or error('Unable to delete from spam_repository',
                 __FILE__,
                 __LINE__,
                 $db->error());
    break;
}

$spam_res = $db->fetch_assoc($result);

// Determine whether this post is the "topic post" or not
$result = $db->query(
        'SELECT `id` '
        . 'FROM `' . $db->prefix . 'posts` '
        . 'WHERE `topic_id`=' . $spam_res['tid'] . ' '
        . 'ORDER BY `posted` '
        . 'LIMIT 1;')
or error('Unable to fetch post info',
         __FILE__,
         __LINE__,
         $db->error());
$topic_post_id = $db->result($result);

require_once(PUN_ROOT . 'include/search_idx.php');

if ($spam_res['pid'] == $topic_post_id) {
    // Delete the topic and all of it's posts
    delete_topic($spam_res['tid']);
    $redirectId = $pun_config['spam_fid'];
} else {
    // Delete just this one post
    delete_post($spam_res['pid'], $spam_res['tid']);
    $redirectId = $spam_res['tid'];
}

update_forum($spam_res['fid']);
wap_redirect('viewtopic.php?id=' . $redirectId);
