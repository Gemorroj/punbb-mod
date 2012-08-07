<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/file_upload.php';


if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}


$id = @intval(@$_GET['id']);
if ($id < 1) {
    wap_message($lang_common['Bad request']);
}


// Fetch some info about the post, the topic and the forum
$result = $db->query('
    SELECT f.id AS fid,
    f.forum_name,
    f.moderators,
    f.redirect_url,
    fp.post_replies,
    fp.post_topics,
    fp.file_upload,
    fp.file_download,
    fp.file_limit,
    t.id AS tid,
    t.subject,
    t.posted,
    t.closed,
    p.poster,
    p.poster_id,
    p.message,
    p.hide_smilies
    FROM ' . $db->prefix . 'posts AS p
    INNER JOIN ' . $db->prefix . 'topics AS t ON t.id=p.topic_id
    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
    WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id=' . $id
) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_post = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators']) ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Determine whether this post is the "topic post" or not
$result = $db->query('SELECT id FROM ' . $db->prefix . 'posts WHERE topic_id=' . $cur_post['tid'] . ' ORDER BY posted LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$topic_post_id = $db->result($result);

$can_edit_subject = ($id == $topic_post_id && ((!$pun_user['g_edit_subjects_interval'] || ($_SERVER['REQUEST_TIME'] - $cur_post['posted']) < $pun_user['g_edit_subjects_interval']) || $is_admmod)) ? true : false;

// have we permission to attachments?
$can_download = (!$cur_post['file_download'] && $pun_user['g_file_download'] == 1) || $cur_post['file_download'] == 1 || $is_admmod;
$can_upload = (!$cur_post['file_upload'] && $pun_user['g_file_upload'] == 1) || $cur_post['file_upload'] == 1 || $is_admmod;
if ($pun_user['is_guest']) {
    $file_limit = 0;
} else {
    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'topics AS t INNER JOIN ' . $db->prefix . 'attachments AS a ON t.id=a.topic_id WHERE t.forum_id=' . $cur_post['fid'] . ' AND a.poster_id=' . $pun_user['id']) or error('Unable to attachments count', __FILE__, __LINE__, $db->error());
    $uploaded_to_forum = $db->fetch_row($result);
    $uploaded_to_forum = $uploaded_to_forum[0];

    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'attachments AS a WHERE a.post_id=' . $id) or error('Unable to attachments count', __FILE__, __LINE__, $db->error());
    $uploaded_to_post = $db->fetch_row($result);
    $uploaded_to_post = $uploaded_to_post[0];

    $forum_file_limit = ($cur_post['file_limit']) ? intval($cur_post['file_limit']) : intval($pun_user['g_file_limit']);

    $global_file_limit = $pun_user['g_file_limit'] + $pun_user['file_bonus'];

    $topic_file_limit = intval($pun_config['file_max_post_files']);

    if ($pun_user['g_id'] == PUN_ADMIN) {
        // just unlimited
        $file_limit = 100;
    } else {
        $file_limit = min($forum_file_limit - $uploaded_to_forum, $global_file_limit - $pun_user['num_files'], $topic_file_limit - $uploaded_to_post);
    }
}

if (!$is_admmod && ($id != $topic_post_id && $pun_config['file_first_only'] == 1)) {
    $can_upload = false;
}

// Do we have permission to edit this post?
if ((!$pun_user['g_edit_posts'] || $cur_post['poster_id'] != $pun_user['id'] || $cur_post['closed'] == 1) && !$is_admmod) {
    wap_message($lang_common['No permission']);
}

// Load the post.php/edit.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/post.php';

// Start with a clean slate
$errors = array();

$hide_smilies = @$_POST['hide_smilies'];
if ($hide_smilies != 1) {
    $hide_smilies = 0;
}

if (isset($_POST['form_sent'])) {
    /*
    if ($is_admmod) {
        confirm_referrer('edit.php');
    }
    */

    // If it is a topic it must contain a subject
    if ($can_edit_subject) {
        $subject = pun_trim($_POST['req_subject']);

        if (!$subject) {
            $errors[] = $lang_post['No subject'];
        } else if (mb_strlen($subject) > 70) {
            $errors[] = $lang_post['Too long subject'];
        } else if (!$pun_config['p_subject_all_caps'] && mb_strtoupper($subject) == $subject && $pun_user['g_id'] > PUN_MOD) {
            $subject = ucwords(mb_strtolower($subject));
        }
    }

    // Clean up message from POST
    $message = pun_linebreaks(pun_trim($_POST['req_message']));

    if (!$message) {
        $errors[] = $lang_post['No message'];
    } else if (mb_strlen($message) > 65535) {
        $errors[] = $lang_post['Too long message'];
    } else if (!$pun_config['p_message_all_caps'] && mb_strtoupper($message) == $message && $pun_user['g_id'] > PUN_MOD) {
        $message = ucwords(mb_strtolower($message));
    }


    // Validate BBCode syntax
    if ($pun_config['p_message_bbcode'] == 1 && strpos($message, '[') !== false && strpos($message, ']') !== false) {
        include_once PUN_ROOT . 'include/parser.php';
        $message = preparse_bbcode($message, $errors);
    }


    /// MOD ANTISPAM BEGIN
    //require PUN_ROOT.'include/antispam/antispam_start.php';
    /// MOD ANTISPAM END


    // Did everything go according to plan?
    if (!$errors && !isset($_POST['preview'])) {
        $edited_sql = (!isset($_POST['silent']) || !$is_admmod) ? $edited_sql = ', edited=' . time() . ', edited_by=\'' . $db->escape($pun_user['username']) . '\'' : '';

        include PUN_ROOT . 'include/search_idx.php';

        if ($can_edit_subject) {
            // Update the topic and any redirect topics
            $db->query('UPDATE ' . $db->prefix . 'topics SET subject=\'' . $db->escape($subject) . '\' WHERE id=' . $cur_post['tid'] . ' OR moved_to=' . $cur_post['tid']) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

            // We changed the subject, so we need to take that into account when we update the search words
            update_search_index('edit', $id, $message, $subject);
        } else {
            update_search_index('edit', $id, $message);
        }

        // Update the post
        $db->query('UPDATE ' . $db->prefix . 'posts SET message=\'' . $db->escape($message) . '\', hide_smilies=\'' . $hide_smilies . '\'' . $edited_sql . ' WHERE id=' . $id) or error('Unable to update post', __FILE__, __LINE__, $db->error());

        $uploaded = $deleted = 0;
        $attach_result = process_deleted_files($id, $deleted) . process_uploaded_files($cur_post['tid'], $id, $uploaded);

        // If the posting user is logged in, increment his/her post count
        if (!$pun_user['is_guest'] && ($uploaded - $deleted) != 0) {
            $db->query('UPDATE LOW_PRIORITY ' . $db->prefix . 'users SET num_files=num_files+' . ($uploaded - $deleted) . ' WHERE id=' . $pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
        }

        /// MOD ANTISPAM BEGIN
        //require PUN_ROOT.'include/antispam/antispam_end.php';
        /// MOD ANTISPAM END

        generate_rss();

        wap_redirect('viewtopic.php?pid=' . $id . '#p' . $id);
    }
}

require_once PUN_ROOT . 'wap/header.php';

$preview_message = '';
if (@$_POST['preview']) {
    include_once PUN_ROOT . 'include/parser.php';
    $preview_message = parse_message($message, $hide_smilies);
}

//+ Attachments//
$num_to_upload = min($file_limit, 20);
$smarty->assign('num_to_upload', $num_to_upload);
$smarty->assign('can_download', $can_download);
$smarty->assign('can_upload', $can_upload);
//$smarty->assign('upload_to_post', $upload_to_post);

if ($uploaded_to_post) {
    // Retrieve the attachments
    $smarty->assign('basename', basename(__FILE__));
    $cur_post['id'] = $id;
    require_once PUN_ROOT . 'include/attach/fetch.php';
    $smarty->assign('lang_fu', $lang_fu);
    $smarty->assign('attachments', $attachments);
}
//- Attachments//

$smarty->assign('cur_post', $cur_post);
$smarty->assign('lang_post', $lang_post);
$smarty->assign('preview_message', $preview_message);
$smarty->assign('message', $message);
$smarty->assign('id', $id);
$smarty->assign('can_edit_subject', $can_edit_subject);
$smarty->assign('cur_index', $cur_index);

$smarty->display('edit.tpl');