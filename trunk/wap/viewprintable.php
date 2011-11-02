<?php
define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';


if(!$pun_user['g_read_board']){
message($lang_common['No view']);
}

$id = intval($_GET['id']);
if($id < 1){
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

if(!$db->num_rows($result)){
wap_message($lang_common['Bad request']);
}

$cur_topic = $db->fetch_assoc($result);

$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / '.$cur_topic['subject']);

print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="'.$lang_common['lang_direction'].'">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$lang_common['lang_encoding'].'" />
<link rel="stylesheet" href="'.PUN_ROOT.'style/imports/printable.css" type="text/css">
<title>'.$page_title.'</title>
</head>
<body>
<table class="links" align="center">
<tr><td>
<strong> &raquo; '.$pun_config['o_board_title'].'</strong><br />'.$pun_config['o_base_url'].'/index.php<br />
<strong> &raquo; '.$cur_topic['forum_name'].'</strong><br /> '.$pun_config['o_base_url'].'/viewforum.php?id='.$cur_topic['forum_id'].'<br />
<strong> &raquo; '.$cur_topic['subject'].'</strong><br /> '.$pun_config['o_base_url'].'/viewtopic.php?id='.$id.'
</td>
</tr>
</table><br />
<table align="center" cellspacing="0" cellpadding="3">
<tbody>';

include_once PUN_ROOT.'include/parser.php';

// Retrieve the posts (and their respective poster)
$result = $db->query('SELECT p.poster AS username, p.message, p.posted FROM '.$db->prefix.'posts AS p WHERE p.topic_id='.$id.' ORDER BY p.id') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
while($cur_post = $db->fetch_assoc($result))
{
$username = pun_htmlspecialchars($cur_post['username']);

// Perform the main parsing of the message (BBCode, smilies, censor words etc)
$cur_post['message'] = parse_message($cur_post['message'], true);

print '<tr><td style="border-bottom: 0px;">
<strong>'.$username.' &raquo; '.format_time($cur_post['posted']).'</strong></td></tr>
<tr><td style="border-bottom: 1px solid #333;">'.$cur_post['message'].'</td></tr>';

}

print '</tbody></table></body></html>';
?>