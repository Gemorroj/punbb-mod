<?php
define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/file_upload.php';

/* Mod InstantQuote */
// require_once PUN_ROOT.'quote.common.php';
/* // Mod InstantQuote */

require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';


if(!$pun_user['g_read_board']){
wap_message($lang_common['No view']);
}


$pid = @intval(@$_GET['id']);
if($pid < 1){
wap_message($lang_common['Bad request']);
}

// Load the viewtopic.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';


// If a post ID is specified we determine topic ID and page number so we can redirect to the correct message
$result = $db->query('SELECT `topic_id` FROM `'.$db->prefix.'posts` WHERE `id`='.$pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if(!$db->num_rows($result)){
wap_message($lang_common['Bad request']);
}

$id = $db->result($result);

// Determine on what page the post is located (depending on $pun_user['disp_posts'])
$result = $db->query('SELECT `id` FROM `'.$db->prefix.'posts` WHERE `topic_id`='.$id.' ORDER BY `posted`') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$num_posts = $db->num_rows($result);


for($i=0; $i<$num_posts; ++$i)
{
$cur_id = $db->result($result, $i);
if($cur_id == $pid){
	break;
}
}

++$i;	// we started at 0

$_GET['p'] = ceil($i / $pun_user['disp_posts']);



// Fetch some info about the topic
if(!$pun_user['is_guest']){
$result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, s.user_id AS is_subscribed, lt.log_time FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') LEFT JOIN '.$db->prefix.'log_topics AS lt ON (lt.user_id='.$pun_user['id'].' AND lt.topic_id=t.id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
}
else{
$result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
}

if(!$db->num_rows($result)){
wap_message($lang_common['Bad request']);
}

$cur_topic = $db->fetch_assoc($result);

// REAL MARK TOPIC AS READ MOD BEGIN
if(!$pun_user['is_guest']){
$cur_time = time();
$message_stack = array();
if($cur_topic['log_time'] == null){
$result = $db->query('INSERT INTO '.$db->prefix.'log_topics (user_id, forum_id, topic_id, log_time) VALUES ('.$pun_user['id'].', '.$cur_topic['forum_id'].', '.$id.', '.$cur_time.')') or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
}
else{
$result = $db->query('UPDATE '.$db->prefix.'log_topics SET forum_id='.$cur_topic['forum_id'].', log_time='.$cur_time.' WHERE topic_id='.$id.' AND user_id='.$pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
}

$result = $db->query('SELECT t.id, t.last_post, lt.log_time FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'log_topics AS lt ON lt.topic_id=t.id AND lt.user_id='.$pun_user['id'].' WHERE t.forum_id = '.$cur_topic['forum_id'].' AND t.last_post > '.$cur_time.'-'.$pun_user['mark_after'].' ') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

$find_new = false;
while($topic = $db->fetch_assoc($result)){
if((!$topic['log_time'] && $topic['last_post'] > $pun_user['last_visit']) || ($topic['log_time'] < $topic['last_post'] && $topic['last_post'] > $pun_user['last_visit'])){
$find_new = true;
break;
}
}

if(!$find_new){
$cur_time = $cur_time + 10;
$result = $db->query('UPDATE '.$db->prefix.'log_forums SET log_time='.$cur_time .' WHERE forum_id='.$cur_topic['forum_id'].' AND user_id='.$pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
if($db->affected_rows()<1 ){
$result = $db->query('INSERT INTO '.$db->prefix.'log_forums (user_id, forum_id, log_time) VALUES ('.$pun_user['id'].', '.$cur_topic['forum_id'].', '.$cur_time.')');
$dberror = $db->error();
if($dberror['error_no'] && $dberror['error_no']!=1062){
error('Unable to insert reading_mark info.', __FILE__, __LINE__, $db->error());
}
}

}
}
// REAL MARK TOPIC AS READ MOD END


// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_topic['moderators']) ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Can we or can we not post replies?
if(!$cur_topic['closed'])
{
if((!$cur_topic['post_replies'] && ($pun_user['g_post_replies'] == 1 || $pun_user['g_post_replies'] == 2)) || $cur_topic['post_replies'] == 1 || $is_admmod){
$post_link = '<a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
}
else{
$post_link = '&#160;';
}
}
else
{
$post_link = $lang_topic['Topic closed'];

if($is_admmod){
$post_link .= ' / <a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
}
}

// Can we or can we not download attachments?
$can_download = (!$cur_topic['file_download'] && $pun_user['g_file_download'] == 1) || $cur_topic['file_download'] == 1 || $is_admmod;

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_posts'] * ($p - 1);

// Generate paging links
/// MOD VIEW ALL PAGES IN ONE BEGIN
// ORIGINAL
//$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewtopic.php?id='.$id);
if($_GET['action'] == 'all'){
$p = ($num_pages + 1);
}
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewtopic.php?id='.$id);
if($_GET['action'] == 'all' && !$pid){
$pun_user['disp_posts'] = $cur_topic['num_replies'] + 1;
}
/// MOD VIEW ALL PAGES IN ONE END

if($pun_config['o_censoring'] == 1)
{$cur_topic['subject'] = censor_words($cur_topic['subject']);}


// !$pun_user['is_guest'] && - Ёто поебень
$quickpost = false;
if($pun_config['o_quickpost'] == 1 &&
// !$pun_user['is_guest'] &&
($cur_topic['post_replies'] == 1 || (!$cur_topic['post_replies'] && $pun_user['g_post_replies'] == 1)) && (!$cur_topic['closed'] || $is_admmod))
{
$required_fields = array('req_message' => $lang_common['Message']);
$quickpost = true;
}

if(!$pun_user['is_guest'] && $pun_config['o_subscriptions'] == 1)
{
if($cur_topic['is_subscribed']){
// I apologize for the variable naming here. It's a mix of subscription and action I guess :-)
$subscraction = '<div class="con">'.$lang_topic['Is subscribed'].' - <a href="misc.php?unsubscribe='.$id.'">'.$lang_topic['Unsubscribe'].'</a></div>';
}
else{
$subscraction = '<div class="con"><a href="misc.php?subscribe='.$id.'">'.$lang_topic['Subscribe'].'</a></div>';
}
}
else{
$subscraction = null;
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / '.$cur_topic['subject']);

define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT.'wap/header.php';


print '<div class="con"><a href="index.php">'.$lang_common['Index'].'</a> / <a href="viewforum.php?id='.$cur_topic['forum_id'].'">'.pun_htmlspecialchars($cur_topic['forum_name']).'</a> / '.pun_htmlspecialchars($cur_topic['subject']).'<br/></div>';


include_once PUN_ROOT.'include/parser.php';

// !!!
// hcs AJAX POLL MOD BEGIN
if($pun_config['poll_enabled'] == 1){
include PUN_ROOT.'include/poll/poll.inc.php';

if($cur_topic['has_poll']){

if($_POST['pollid']){

if(is_array($_POST['poll_vote'])){
foreach($_POST['poll_vote'] as $var){
$q.= $var.'='.intval($var).'&';
}
$q = substr($q, 0 , -1);
}
else{
$q = intval($_POST['poll_vote']);
}


$Poll->vote($_POST['pollid'], $q);
}


$Poll->wap_showPoll($cur_topic['has_poll'], true);

}
}
// hcs AJAX POLL MOD END


$bg_switch = true; // Used for switching background color in posts
$post_count = 0; // Keep track of post numbers

// Retrieve the posts (and their respective poster/online status)

/// MOD ANTISPAM BEGIN
if ($pun_config['antispam_enabled'] == 1 && $is_admmod){
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online, spam.pattern, spam.id AS spam_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) LEFT JOIN '.$db->prefix.'spam_repository AS spam ON spam.post_id=p.id WHERE p.id='.$pid, true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
else{
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id='.$pid, true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
/// MOD ANTISPAM END

$posts = $pids = array();
$cur_post = $db->fetch_assoc($result);
$posts[] = $cur_post;
$pids[] = $cur_post['id'];
$db->free_result($result);


// Retrieve the attachments
require PUN_ROOT.'include/attach/fetch.php';

foreach($posts as $cur_post)
{
$post_count++;
$signature = $is_online = $user_avatar = '';
$post_actions = $user_contacts = $user_info = array();

// If the poster is a registered user.
if($cur_post['poster_id'] > 1){

if($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST){

$karma = $db->fetch_row($db->query('SELECT SUM(`vote`), (SELECT 1 FROM `'.$db->prefix.'karma` WHERE `id`='.$pun_user['id'].' AND `to`='.$cur_post['poster_id'].' LIMIT 1) FROM `'.$db->prefix.'karma` WHERE `to` = '.$cur_post['poster_id'], false));
$karma[0] = intval($karma[0]);

if($pun_user['is_guest'] || $karma[1]){
$karma = ' ('.$karma[0].')';
}
else{
$karma = ' ('.$karma[0].') <a href="karma.php?to='.$cur_post['poster_id'].'&amp;vote=1&amp;pid='.$cur_post['id'].'">+</a>/<a href="karma.php?to='.$cur_post['poster_id'].'&amp;vote=-1&amp;pid='.$cur_post['id'].'">-</a>';
}

}


$username = '<a href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>'.$karma;

$user_title = get_title($cur_post);

if($pun_config['o_censoring'] == 1){
$user_title = censor_words($user_title);
}

// Format the online indicator
if($cur_post['is_online'] == $cur_post['poster_id']){
$is_online = '<span class="red">'.$lang_topic['Online'].'</span>';
}

if($pun_config['o_avatars'] == 1 && $cur_post['use_avatar'] == 1 && $pun_user['show_avatars'])
{
if($img_size = @getimagesize('../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif'))
{$user_avatar = '</td><td><img src="../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif" '.$img_size[3].' alt="" />';}
else if($img_size = @getimagesize('../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg'))
{$user_avatar = '</td><td><img src="../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg" '.$img_size[3].' alt="" />';}
else if($img_size = @getimagesize('../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png'))
{$user_avatar = '</td><td><img src="../'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png" '.$img_size[3].' alt="" />';}
}
else
{$user_avatar = null;}


// MOD: QUICK QUOTE - 1 LINE FOLLOWING CODE ADDED
$user_contacts[] = '<a href="profile.php?id='.$cur_post['poster_id'].'">'.$lang_common['Profile'].'</a>';
// QUICK QUOTE MOD END


if($pun_user['g_id'] < PUN_GUEST)
{
$user_info[] = 'IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';

if($cur_post['admin_note'])
{$user_info[] = $lang_topic['Note'].': <strong>'.pun_htmlspecialchars($cur_post['admin_note']).'</strong>';}
}
}
else // If the poster is a guest (or a user that has been deleted)
{
$username = pun_htmlspecialchars($cur_post['username']);
$user_title = get_title($cur_post);

if($pun_user['g_id'] < PUN_GUEST)
{$user_info[] = 'IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';}

if($pun_config['o_show_user_info'] == 1 && $cur_post['poster_email'] && !$pun_user['is_guest'])
{$user_contacts[] = '<a href="mailto:'.$cur_post['poster_email'].'">'.$lang_common['E-mail'].'</a>';}
}

// Generation post action array (quote, edit, delete etc.)
if(!$is_admmod)
{
/*
if(!$pun_user['is_guest']){
$post_actions[] = '<a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>';
}
*/

if(!$cur_topic['closed'])
{
if($cur_post['poster_id'] == $pun_user['id'])
{
if((($start_from + $post_count) == 1 && $pun_user['g_delete_topics'] == 1) || (($start_from + $post_count) > 1 && $pun_user['g_delete_posts'] == 1))
{$post_actions[] = '<a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>';}
if($pun_user['g_edit_posts'] == 1)
{$post_actions[] = '<a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>';}
}

// MOD: QUICK REPLY - FOLLOWING "IF" CODE BLOCK MODIFIED
if((!$cur_topic['post_replies'] && $pun_user['g_post_replies'] == 1) || $cur_topic['post_replies'] == 1)
{$post_actions[] = '<a href="post.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Post reply'].'</a>';}
// QUICK QUOTE MOD END
}
}
else
{
// MOD: QUICK REPLY - 1 LINE FOLLOWING CODE MODIFIED
$post_actions[] = '<a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>'.$lang_topic['Link separator'].' <a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>'.$lang_topic['Link separator'].' <a href="post.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Post reply'].'</a>';
// QUICK QUOTE MOD END
}

// Switch the background color for every message.
$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';


// Perform the main parsing of the message (BBCode, smilies, censor words etc)
$cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies'], $cur_post['id']);

// Do signature parsing/caching
if($cur_post['signature'] && $pun_user['show_sig'])
{
if(isset($signature_cache[$cur_post['poster_id']]))
{$signature = $signature_cache[$cur_post['poster_id']];}
else
{
$signature = parse_signature($cur_post['signature']);
$signature_cache[$cur_post['poster_id']] = $signature;
}
}

$cur_post['message'] = str_replace('<h4>'.$lang_common['Code'].':</h4>','<div class="red">'.$lang_common['Code'].'<br/>',$cur_post['message']);
$cur_post['message'] = str_replace('<div class="codebox"><div class="incqbox">',null,$cur_post['message']);

$cur_post['message'] = str_replace('</table></div></div></div>','</table></div></div>',$cur_post['message']);

//$cur_post['message'] = preg_replace('/<div class="scrollbox".*>/iU','<div style="margin:2pt;">',$cur_post['message']);
//$cur_post['message'] = str_replace('<code>',null,$cur_post['message']);
//$cur_post['message'] = str_replace('</code></div></div></div>','</code></div>',$cur_post['message']);
//$cur_post['message'] = str_replace('<span style="color: #000000">'.chr(10).'<span style="color: #0000BB">','<span style="color: #000000"><span style="color: #0000BB">',$cur_post['message']);
//$cur_post['message'] = str_replace('</span>'.chr(10).'</code>','</span></code>',$cur_post['message']);


print '<table class="msg2"><tr><td><div id="p'.$cur_post['id'].'"><strong><a href="viewtopic.php?pid='.$cur_post['id'].'#p'.$cur_post['id'].'">#'.($start_from + $post_count).'</a><br/>'.format_time($cur_post['posted']).'<br/></strong><strong>'.$username.'</strong>';

if($cur_post['poster_id']>1 && $is_online){
echo '<br/>'.$is_online;
}

echo '</div>'.$user_avatar.'</td></tr></table>';
if($str = implode($lang_topic['Link separator'], $post_actions)){
	$str = '<div class="con">'.$str.' <br/></div>';
}

echo '<table class="msg"><tr><td>';


/*
echo sizeof($post_actions) ? '<span class="con">'.implode($lang_topic['Link separator'], $post_actions).' <br/></span>' : '';
*/

echo '</td></tr><tr><td>'.$cur_post['message'].'</td></tr><tr><td>';



$save_attachments = $attachments;
$attachments = array_filter($attachments, 'filter_attachments_of_post');
if(sizeof($attachments)){
include PUN_ROOT.'include/attach/wap_view_attachments.php';
}
$attachments = $save_attachments;


/// MOD ANTISPAM BEGIN
if($is_admmod){
if(isset($cur_post['spam_id'])){
$result = $db->query('SELECT `pattern` FROM `'.$db->prefix.'spam_repository` WHERE `id`='.$cur_post['spam_id'], true) or error('Unable to get spam_pattern for message', __FILE__, __LINE__, $db->error());
$spam = $db->fetch_assoc($result);
print '<hr /><br />'.$lang_misc['Antispam pattern'].' - '.pun_htmlspecialchars($spam['pattern']).'<br /><br /> / <a href="#">'.$lang_misc['Antispam tread'].'</a> / <a href="#">'.$lang_misc['Antispam del'].'</a><hr />';
}
}
/// MOD ANTISPAM END

if($cur_post['edited']){
echo '<div><em>'.$lang_topic['Last edit'].' '.pun_htmlspecialchars($cur_post['edited_by']).' ('.format_time($cur_post['edited']).')</em></div>';
}

if($signature)
{echo '<div><hr />'.$signature.'</div>';}


print '</td></tr></table>'.$str;

}

print '<p class="con">'.$paging_links.'</p>';


if($pun_user['g_post_replies']){
print '<div class="blocktable"><strong><a class="in" href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a></strong></div>';
}


// Display quick post if enabled
if($quickpost)
{
if(!$pun_user['is_guest'])
{$form_user = pun_htmlspecialchars($pun_user['username']);}
else
{$form_user = 'Guest';}


print '<div><strong>'.$lang_topic['Quick post'].'</strong><br/></div><div class="input">
<form method="post" action="post.php?tid='.$id.'">
<div><fieldset><legend>'.$lang_common['Write message legend'].'<br/></legend>';

if($pun_config['o_antiflood']){
print '<input type="hidden" name="form_t" value="'.time().'" />';
}

print '<input type="hidden" name="form_sent" value="1" /><input type="hidden" name="form_user" value="'.$form_user.'" />';

// ¬вод имени дл€ гостей
if($pun_user['is_guest'])
{print $lang_common['Username'].'<br/><input type="text" name="req_username" tabindex="1" /><br/>';}

print '<textarea name="req_message" rows="4" cols="24" tabindex="1"></textarea><br/>';

if($is_admmod)
{print '<input type="checkbox" name="merge" value="1" checked="checked" />'.$lang_post['Merge posts'];}

print '</fieldset><br/>
<input type="submit" name="submit" tabindex="2" value="'.$lang_common['Submit'].'" accesskey="s" />
</div>
</form>
</div>';
}

// Increment "num_views" for topic
$db->query('UPDATE LOW_PRIORITY '.$db->prefix.'topics SET num_views=num_views+1 WHERE id='.$id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

$forum_id = $cur_topic['forum_id'];
$footer_style = 'viewtopic';
require_once PUN_ROOT.'wap/footer.php';
?>