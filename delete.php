<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    \message($lang_common['No view']);
}

$id = isset($_GET['id']) ? (int) ($_GET['id']) : 0;
if ($id < 1) {
    \message($lang_common['Bad request']);
}

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id);
if (!$result) {
    \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
if (!$db->num_rows($result)) {
    \message($lang_common['Bad request']);
}

$cur_post = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators']) ? \unserialize($cur_post['moderators'], ['allowed_classes' => false]) : [];
$is_admmod = (PUN_ADMIN == $pun_user['g_id'] || (PUN_MOD == $pun_user['g_id'] && \array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Determine whether this post is the "topic post" or not
$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['tid'].' ORDER BY posted LIMIT 1');
if (!$result) {
    \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
$topic_post_id = $db->result($result);

$is_topic_post = ($id == $topic_post_id) ? true : false;

// Do we have permission to edit this post?
if ((!$pun_user['g_delete_posts'] || (!$pun_user['g_delete_topics'] && $is_topic_post) || $cur_post['poster_id'] != $pun_user['id'] || 1 == $cur_post['closed']) && !$is_admmod) {
    \message($lang_common['No permission']);
}

// Load the delete.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/delete.php';

if (isset($_POST['delete'])) {
    /*
    if($is_admmod)
    {confirm_referrer('delete.php');}
    */

    include PUN_ROOT.'include/search_idx.php';

    if ($is_topic_post) {
        // Delete the topic and all of it's posts
        \delete_topic($cur_post['tid']);
        \update_forum($cur_post['fid']);
        \redirect('viewforum.php?id='.$cur_post['fid'], $lang_delete['Topic del redirect']);
    } else {
        // Delete just this one post
        \delete_post($id, $cur_post['tid']);
        \update_forum($cur_post['fid']);

        \redirect('viewtopic.php?id='.$cur_post['tid'], $lang_delete['Post del redirect']);
    }
}

$page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_delete['Delete post'];

require_once PUN_ROOT.'header.php';

include_once PUN_ROOT.'include/parser.php';
$cur_post['message'] = \parse_message($cur_post['message'], $cur_post['hide_smilies']);

echo '<div class="linkst">
<div class="inbox">
<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li> &#187; <a href="viewforum.php?id='.$cur_post['fid'].'">'.\pun_htmlspecialchars($cur_post['forum_name']).'</a></li><li> &#187; '.\pun_htmlspecialchars($cur_post['subject']).'</li></ul>
</div>
</div>
<div class="blockform">
<h2><span>'.$lang_delete['Delete post'].'</span></h2>
<div class="box">
<form method="post" action="delete.php?id='.$id.'">
<div class="inform">
<fieldset>
<legend class="warntext">'.$lang_delete['Warning'].'</legend>
<div class="infldset">
<div class="postmsg">
<p>'.$lang_common['Author'].': <strong>'.\pun_htmlspecialchars($cur_post['poster']).'</strong></p>
'.$cur_post['message'].'
</div>
</div>
</fieldset>
</div>
<p><input type="submit" name="delete" value="'.$lang_delete['Delete'].'" /><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>
</form>
</div>
</div>';

require_once PUN_ROOT.'footer.php';
