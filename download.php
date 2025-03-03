<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    \message($lang_common['No view']);
}

if (!isset($_GET['aid'])) {
    \error('Invalid image parameters', __FILE__, __LINE__);
}
$aid = (int) $_GET['aid'];

// Retrieve attachment info and permissions
$result_attach = $db->query(
    '
    SELECT a.filename, a.location, a.mime, p.poster_id, f.moderators, fp.file_download
    FROM '.$db->prefix.'attachments AS a
    INNER JOIN '.$db->prefix.'posts AS p ON p.id=a.post_id
    INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id
    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id='.$pun_user['g_id'].')
    WHERE a.id='.$aid
) || \error('Unable to fetch if there were any attachments to the post', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result_attach)) {
    \error('There are no attachment or access denied', __FILE__, __LINE__);
}

[$file, $location, $mime, $poster_id, $moderators, $file_download] = $db->fetch_row($result_attach);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = [];
if ($moderators) {
    $mods_array = \unserialize($moderators, ['allowed_classes' => false]);
}
$is_admmod = (PUN_ADMIN == $pun_user['g_id'] || (PUN_MOD == $pun_user['g_id'] && \array_key_exists($pun_user['username'], $mods_array))) ? true : false;
$can_download = (!$file_download && 1 == $pun_user['g_file_download']) || 1 == $file_download || $is_admmod;

// author of post always can download his attachments
// other users can has rights or not

$is_image = \preg_match('/^image\/(?:.*)$/i', $mime);

if (!$can_download && !($poster_id == $pun_user['id'])) {
    if ($is_image) {
        // show noaccess icon instead of image
        \download(PUN_ROOT.$pun_config['file_thumb_path'].'err_access.gif', 'err_access.gif', 'image/gif');
    } else {
        \message('Access denied');
    }
}

if (!\is_file($location)) {
    \error($location.' - this file does not exist', __FILE__, __LINE__);
}

$db->query('UPDATE `'.$db->prefix.'attachments` SET `downloads` = `downloads` + 1 WHERE `id`='.$aid) || \error('Unable to update download counter', __FILE__, __LINE__, $db->error());

\download($location, $file, $mime);
