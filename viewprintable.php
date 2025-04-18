<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (!$pun_user['g_read_board']) {
    \message($lang_common['No view']);
}

$id = (int) @$_GET['id'];
if ($id < 1) {
    \message($lang_common['Bad request']);
}

// Load the viewtopic.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

// Fetch some info about the topic
// if (!$pun_user['is_guest'])
// MOD: MARK TOPICS AS READ - 1 LINE MODIFIED CODE FOLLOWS
//	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.last_post, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
// else

$result = $db->query('SELECT t.subject, t.num_replies, f.id AS forum_id, f.forum_name, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE t.id='.$id);
if (!$result) {
    \error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
}

if (!$db->num_rows($result)) {
    \message($lang_common['Bad request']);
}

$cur_topic = $db->fetch_assoc($result);

$page_title = \pun_htmlspecialchars($pun_config['o_board_title'].' / '.$cur_topic['subject']);

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="'.PUN_ROOT.'style/imports/printable.css" type="text/css">
<title>'.$page_title.'</title>
</head>
<body>
<table class="links">
<tr><td>
<strong> &#187; '.$pun_config['o_board_title'].'</strong> &#160; '.$pun_config['o_base_url'].'/index.php<br />
<strong> &#187; '.$cur_topic['forum_name'].'</strong> &#160; '.$pun_config['o_base_url'].'/viewforum.php?id='.$cur_topic['forum_id'].'<br />
<strong> &#187; '.$cur_topic['subject'].'</strong> &#160; '.$pun_config['o_base_url'].'/viewtopic.php?id='.$id.'
</td>
</tr>
</table><br />
<table align="center" cellspacing="0" cellpadding="3">
<tbody>';

include_once PUN_ROOT.'include/parser.php';

// Retrieve the posts (and their respective poster)
$result = $db->query('SELECT p.poster AS username, p.message, p.posted FROM '.$db->prefix.'posts AS p WHERE p.topic_id='.$id.' ORDER BY p.id');
if (!$result) {
    \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
while ($cur_post = $db->fetch_assoc($result)) {
    // Perform the main parsing of the message (BBCode, smilies, censor words etc)
    echo '<tr><td style="border-bottom:0;"><strong>'.\pun_htmlspecialchars($cur_post['username']).' &#187; '.\format_time($cur_post['posted']).'</strong></td></tr><tr><td style="border-bottom:1px solid #333;">'.\parse_message($cur_post['message'], true).'</td></tr>';
}

echo '</tbody></table></body></html>';
