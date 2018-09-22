<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/fileup.php';
require PUN_ROOT . 'include/file_upload.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
}


$page_title = $pun_config['o_board_title'] . ' / ' . $lang_common['Attachments'];
define('PUN_ALLOW_INDEX', 0);
define('ATTACHMENTS_PER_PAGE', $pun_user['disp_posts']);

require_once PUN_ROOT . 'wap/header.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/fileup.php';

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

if ($user_id) {
    $result = $db->query('
        SELECT u.username, u.group_id, u.num_files, u.file_bonus, g.g_id, g.g_file_limit, g.g_title
        FROM `' . $db->prefix . 'users` AS u
        JOIN `' . $db->prefix . 'groups` AS g ON (u.group_id=g.g_id)
        WHERE u.id=' . $user_id
    ) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        message('No user by that ID registered.');
    }
    $user = $db->fetch_assoc($result);
}

$fid_list = $categories = $forums = array();

// get available forum list
$result = $db->query('
    SELECT f.id AS fid, f.forum_name, f.moderators, fp.file_download
    FROM `' . $db->prefix . 'forums` AS f
    LEFT JOIN `' . $db->prefix . 'forum_perms` AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
    WHERE fp.read_forum IS NULL OR fp.read_forum=1
    ORDER BY f.id
') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
while ($cur_forum = $db->fetch_assoc($result)) {
    $fid_list[] = $cur_forum['fid'];

    // we have to calculate download rights for every forum
    $mods_array = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();
    $is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;
    $can_download = $is_admmod || (!$cur_forum['file_download'] && $pun_user['g_file_download'] == 1) || $cur_forum['file_download'] == 1;

    $forums[$cur_forum['fid']] = array(
        'forum_name' => $cur_forum['forum_name'],
        'can_download' => $can_download
    );
}
$fid_list = implode(',', $fid_list);
unset($can_download);

// get category list for cache
$result = $db->query('SELECT id, cat_name FROM ' . $db->prefix . 'categories') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while ($cur_category = $db->fetch_assoc($result)) {
    $categories[$cur_category['id']] = $cur_category['cat_name'];
}

if (!$fid_list) {
    $num_rows = 0;
} else {
    // get number of topics and which we have to start from
    $result = $db->query('
        SELECT COUNT(1)
        FROM ' . $db->prefix . 'attachments AS a
        INNER JOIN ' . $db->prefix . 'topics AS t ON a.topic_id=t.id
        INNER JOIN ' . $db->prefix . 'forums AS f ON f.id = t.forum_id
        WHERE f.id in (' . $fid_list . ') ' . ($user_id ? (' AND (a.poster_id=' . $user_id . ')') : '')
    ) or error('Unable to fetch topic count', __FILE__, __LINE__, $db->error());
    $num_rows = $db->fetch_row($result);
    $num_rows = $num_rows[0];
}
// Determine the attachment offset (based on $_GET['p'])
$num_pages = ceil($num_rows / ATTACHMENTS_PER_PAGE);

$p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
$start_from = ATTACHMENTS_PER_PAGE * ($p - 1);

// Generate paging links
$user_cond = $user_id ? 'user_id=' . $user_id : '';
$paging_links = paginate($num_pages, $p, 'filemap.php?' . $user_cond);

$attachments = array();
if ($fid_list) {
    // loop through topics
    $result = $db->query('
        SELECT f.cat_id,
        t.forum_id, t.id AS tid, t.subject, t.last_post, t.poster, t.posted,
        a.id AS id, a.mime, a.uploaded, a.image_dim, a.filename, a.downloads, a.location, a.size
        FROM ' . $db->prefix . 'attachments AS a
        INNER JOIN ' . $db->prefix . 'topics AS t ON a.topic_id=t.id
        INNER JOIN ' . $db->prefix . 'forums AS f ON f.id = t.forum_id
        INNER JOIN ' . $db->prefix . 'categories AS c ON f.cat_id = c.id
        WHERE f.id in (' . $fid_list . ') ' . ($user_id ? (' AND (a.poster_id=' . $user_id . ')') : '') . '
        ORDER BY c.disp_position, f.disp_position, f.cat_id, t.forum_id, t.last_post desc, a.filename' .
            ((!isset($_GET['action']) || $_GET['action'] != 'all') ? ' LIMIT ' . $start_from . ',' . ATTACHMENTS_PER_PAGE : '')
    ) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

    while ($row = $db->fetch_assoc($result)) {
        // can user download this attachment? it depends on per-forum permissions
        $row['can_download'] = $forums[$row['forum_id']]['can_download'];
        $attachments[$row['id']][] = $row;
    }
}

$smarty->assign('lang_fu', $lang_fu);
$smarty->assign('page_title', $page_title);
$smarty->assign('user', @$user);
$smarty->assign('attachments', $attachments);
$smarty->assign('categories', $categories);
$smarty->assign('forums', $forums);
$smarty->assign('paging_links', $paging_links);

$smarty->display('filemap.tpl');
