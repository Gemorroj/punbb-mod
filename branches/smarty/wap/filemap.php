<?php
define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/file_upload.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' &#187; '.$lang_common['Attachments']);
define('PUN_ALLOW_INDEX', 0);
define('ATTACHMENTS_PER_PAGE', $pun_user['disp_posts']);

require_once PUN_ROOT.'wap/header.php';

$user_id = intval($_GET['user_id']);

if (isset($_GET['user_id'])) {
    $result = $db->query('
        SELECT u.username, u.group_id, u.num_files, u.file_bonus, g.g_id, g.g_file_limit, g.g_title
        FROM '.$db->prefix.'users AS u
        JOIN '.$db->prefix.'groups AS g ON (u.group_id=g.g_id)
        WHERE u.id='.$user_id
    ) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        message('No user by that ID registered.');
    }
    $user = $db->fetch_assoc($result);

    $userstat = $lang_common['Username'].': <strong>'.pun_htmlspecialchars($user['username']).'</strong><br/>';
    /*
    if($user['g_id'] != PUN_ADMIN){
    $userstat .= 'Limits for "'.pun_htmlspecialchars($user['g_title']).'": '.$user['g_file_limit'].'<br/>
    Personal bonus: '.$user['file_bonus'].'<br/>';
    }
    */
    $userstat .= $lang_common['Files'].': '.$user['num_files'].'<br/>';
}

$fid_list = $categories = $forums = array();

// get available forum list
$result = $db->query('
    SELECT f.id AS fid, f.forum_name, f.moderators, fp.file_download
    FROM '.$db->prefix.'forums AS f
    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
    WHERE fp.read_forum IS NULL OR fp.read_forum=1
    ORDER BY f.id
') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
while($cur_forum = $db->fetch_assoc($result)){
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
$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while ($cur_category = $db->fetch_assoc($result)){
    $categories[$cur_category['id']] = $cur_category['cat_name'];
}

if(!$fid_list){
    $num_rows = 0;
} else {
    // get number of topics and which we have to start from
    $result = $db->query('SELECT COUNT(*)
    FROM
    '.$db->prefix.'attachments AS a INNER JOIN
    '.$db->prefix.'topics AS t ON a.topic_id=t.id INNER JOIN
    '.$db->prefix.'forums AS f ON f.id = t.forum_id
    WHERE f.id in ('.$fid_list.') '.(isset($_GET['user_id'])? (' AND (a.poster_id='.$user_id.')'): '') ) or
    error('Unable to fetch topic count', __FILE__, __LINE__, $db->error());
    $num_rows = $db->fetch_row($result);
    $num_rows = $num_rows[0];
}
// Determine the attachment offset (based on $_GET['p'])
$num_pages = ceil($num_rows / ATTACHMENTS_PER_PAGE);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = ATTACHMENTS_PER_PAGE * ($p - 1);

// Generate paging links
$user_cond = isset($_GET['user_id'])? ('user_id='.$user_id): '';
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'filemap.php?'.$user_cond);

$attachments = array();
if($fid_list){
    // loop through topics
    $result = $db->query('
        SELECT f.cat_id,
        t.forum_id, t.id AS tid, t.subject, t.last_post, t.poster, t.posted,
        a.id AS id, a.mime, a.uploaded, a.image_dim, a.filename, a.downloads, a.location, a.size
        FROM '.$db->prefix.'attachments AS a
        INNER JOIN '.$db->prefix.'topics AS t ON a.topic_id=t.id
        INNER JOIN '.$db->prefix.'forums AS f ON f.id = t.forum_id
        INNER JOIN '.$db->prefix.'categories AS c ON f.cat_id = c.id
        WHERE f.id in ('.$fid_list.') '.(isset($_GET['user_id'])? (' AND (a.poster_id='.$user_id.')'): '').'
        ORDER BY c.disp_position, f.disp_position, f.cat_id, t.forum_id, t.last_post desc, a.filename' .
        ((!isset($_GET['action']) || $_GET['action'] != 'all') ? ' LIMIT '.$start_from.','.ATTACHMENTS_PER_PAGE : '')
    ) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

    while ($row = $db->fetch_assoc($result)) {
        // can user download this attachment? it depends on per-forum permissions
        $row['can_download'] = $forums[$row['forum_id']]['can_download'];
        $attachments[] = $row;
    }
}

$cur_category = $cur_forum = $cur_topic = 0;
$j = false;
/*
// insert popup info panel & its data (javascript)
if($pun_config['file_popup_info'] == '1')
{include PUN_ROOT.'include/attach/popup_data.php';}
*/

if (isset($_GET['user_id'])) {
    echo '<div class="con">'.$lang_common['Member'].' - '.$lang_common['Info'].'</div><div class="msg">'.$userstat.'</div>';
}

$image_height = $pun_config['file_preview_height'];
$image_width = $pun_config['file_preview_width'];

foreach ($attachments as $row) {
    if ($row['cat_id'] != $cur_category || $row['forum_id'] != $cur_forum) {
        echo '<div class="cat">'.pun_htmlspecialchars($categories[$row['cat_id']]).' &#187; <a href="viewforum.php?id='.$row['forum_id'].'">'.pun_htmlspecialchars($forums[$row['forum_id']]['forum_name']).'</a></div>';
        $cur_category = $row['cat_id'];
        $cur_forum = $row['forum_id'];
    }

     // A new topic since last iteration?
    if ($row['tid'] != $cur_topic) {
        echo '<div class="in"><a href="viewtopic.php?id='.$row['tid'].'">'.pun_htmlspecialchars($row['subject']).'</a> <span class="small">('.format_time($row['posted']).')</span></div>';
        $cur_topic = $row['tid'];
    }

    $att_info = '('.round($row['size']/1024,1).'kb, '.((preg_match('|^image/(.*)$|i', $row['mime'], $regs))? ($regs[1].' '.$row['image_dim'].', ') : '').'downloads: '.$row['downloads'].')';

    echo '<div class="' . (($j = !$j) ? 'msg' : 'msg2') . '">';

    if ($row['can_download']) {
        echo '<a href="'.PUN_ROOT.'download.php?aid='.$row['id'].'">'.pun_htmlspecialchars($row['filename']).'</a> <span style="font-style:italic;font-size:smaller;">'.$att_info.'</span>';
    } else {
        echo pun_htmlspecialchars($row['filename']).' '.$att_info;
    }
    echo '</div>';
}

echo '<div class="con">'.$paging_links.'</div>';


$footer_style = 'index';
require_once PUN_ROOT.'wap/footer.php';
?>