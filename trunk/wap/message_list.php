<?php
define('PUN_ROOT', '../');

require PUN_ROOT . 'include/common.php';
include_once PUN_ROOT . 'include/parser.php';

if (!$pun_config['o_pms_enabled'] || !$pun_user['g_pm']) {
    wap_message($lang_common['No permission']);
}

if ($pun_user['is_guest']) {
    wap_message($lang_common['Login required']);
}

// Load the message.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';

$box = intval($_GET['box']);
if ($box != 1 && $box != 2) {
    $box = 0;
}

switch($box){
    case 0:
        $name = $lang_pms['Inbox'];
        break;


    case 1:
        $name = $lang_pms['Outbox'];
        break;


    case 2:
        $name = $lang_pms['Options'];
        break;
}

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);

//$name plus the link to the other box
$page_name = $name;

// Delete multiple posts
if (isset($_POST['delete_messages']) || isset($_POST['delete_messages_comply'])) {
if (isset($_POST['delete_messages_comply'])) {
    //Check this is legit
    //confirm_referrer('message_list.php');

    if (preg_match('/[^0-9,]/', $_POST['messages']) || !trim($_POST['messages'])) {
        wap_message($lang_common['Bad request']);
    }

    // Delete messages
    $db->query('DELETE FROM '.$db->prefix.'messages WHERE id IN(' . $_POST['messages'] . ') AND owner=\''.$pun_user['id'].'\'') or error('Unable to delete messages.', __FILE__, __LINE__, $db->error());
    wap_redirect('message_list.php?box=' . intval($_POST['box']));
}
else
{
$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_pms['Multidelete'];
$idlist = is_array($_POST['delete_messages']) ? array_map('intval', $_POST['delete_messages']) : array();
require_once PUN_ROOT.'wap/header.php';

echo '<div class="input">
<form method="post" action="message_list.php?">
<div>
<fieldset>
<legend>'.$lang_pms['Delete messages comply'].'<br/></legend>
<input type="hidden" name="messages" value="'.htmlspecialchars(implode(',', array_values($idlist))).'"/>
<input type="hidden" name="box" value="'.intval($_POST['box']).'"/>
<input type="submit" name="delete_messages_comply" value="'.$lang_pms['Delete'].'" />
</fieldset>
</div>
</form>
</div>';

require_once PUN_ROOT.'wap/footer.php';
}
}

// Mark all messages as read
else if(isset($_GET['action']) && $_GET['action'] == 'markall') {
    $db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE owner='.$pun_user['id']) or error('Unable to update message status', __FILE__, __LINE__, $db->error());
    //$p = (!isset($_GET['p']) || $_GET['p'] <= 1) ? 1 : $_GET['p'];
    wap_redirect('message_list.php?box='.$box.'&amp;p='.$p);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_pms['Private Messages'].' - '.$name;


if($box<2)
{
// Get message count
$result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'messages WHERE status='.$box.' AND owner='.$pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
list($num_messages) = $db->fetch_row($result);

//What page are we on?
$num_pages = ceil($num_messages / $pun_config['o_pms_mess_per_page']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $pun_config['o_pms_mess_per_page'] * ($p - 1);
if ($_GET['action'] != 'all') {
    $limit = 'LIMIT '.$start_from.','.$pun_config['o_pms_mess_per_page'];
}
}

require_once PUN_ROOT.'wap/header.php';

echo '<div class="incqbox" style="margin:1%;padding:2pt;">
<a href="message_list.php?box=0">'.$lang_pms['Inbox'].'</a><br/>
<a href="message_list.php?box=1">'.$lang_pms['Outbox'].'</a><br/>
<a href="message_list.php?box=2">'.$lang_pms['Options'].'</a><br/>
<a href="message_send.php">'.$lang_pms['New message'].'</a><br/>
</div>';


if($box<2)
{
//Are we viewing a PM?
if(isset($_GET['id']))
{
//Yes! Lets get the details
$id = intval($_GET['id']);

// Set user
$result = $db->query('SELECT status,owner FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to get message status', __FILE__, __LINE__, $db->error());
list($status, $owner) = $db->fetch_row($result);
$status == 0 ? $where = 'u.id=m.sender_id' : $where = 'u.id=m.owner';

$result = $db->query('SELECT m.id AS mid,m.subject,m.sender_ip,m.message,m.smileys,m.posted,m.showed,u.id,u.group_id as g_id,g.g_user_title,u.username,u.registered,u.email,u.title,u.url,u.icq,u.msn,u.aim,u.yahoo,u.location,u.use_avatar,u.email_setting,u.num_posts,u.admin_note,u.signature,o.user_id AS is_online FROM '.$db->prefix.'messages AS m,'.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.idle=0) LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id = g.g_id WHERE '.$where.' AND m.id='.$id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
$cur_post = $db->fetch_assoc($result);

if($owner != $pun_user['id']){
wap_message($lang_common['No permission']);
}

if(!$cur_post['showed']){
$db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE id='.$id) or error('Unable to update message info', __FILE__, __LINE__, $db->error());
}

if($cur_post['id'] > 0)
{
$username = '<a href="profile.php?id='.$cur_post['id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';
$user_title = get_title($cur_post);

if ($pun_config['o_censoring'] == 1) {
    $user_title = censor_words($user_title);
}

// Format the online indicator
$is_online = ($cur_post['is_online'] == $cur_post['id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : $lang_topic['Offline'];

if($pun_config['o_avatars'] == 1 && $cur_post['use_avatar'] == 1 && $pun_user['show_avatars'])
{
if($img_size = @getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.gif')){
$user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.gif" '.$img_size[3].' alt="" />';
}
else if($img_size = @getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.jpg')){
$user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.jpg" '.$img_size[3].' alt="" />';
}
else if($img_size = @getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.png')){
$user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['id'].'.png" '.$img_size[3].' alt="" />';
}
}
else{
$user_avatar = null;
}

// We only show location, register date, post count and the contact links if "Show user info" is enabled
if($pun_config['o_show_user_info'] == 1)
{
if($cur_post['location'])
{
if($pun_config['o_censoring'] == 1)
$cur_post['location'] = censor_words($cur_post['location']);

$user_info[] = $lang_topic['From'].': '.pun_htmlspecialchars($cur_post['location']);
}

$user_info[] = $lang_common['Registered'].': '.date($pun_config['o_date_format'], $cur_post['registered']);

if ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST)
$user_info[] = $lang_common['Posts'].': '.$cur_post['num_posts'];

// Now let's deal with the contact links (E-mail and URL)
if((!$cur_post['email_setting'] && !$pun_user['is_guest']) || $pun_user['g_id'] < PUN_GUEST){
$user_contacts[] = '<a href="mailto:'.$cur_post['email'].'">'.$lang_common['E-mail'].'</a>';
}
else if($cur_post['email_setting'] == 1 && !$pun_user['is_guest']){
$user_contacts[] = '<a href="misc.php?email='.$cur_post['id'].'">'.$lang_common['E-mail'].'</a>';
}
include PUN_ROOT.'include/pms/viewtopic_PM-link.php';
if($cur_post['url']){
$user_contacts[] = '<a href="'.pun_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a>';
}
}

//Moderator and Admin stuff
if($pun_user['g_id'] < PUN_GUEST)
{
$user_info[] = 'IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['sender_ip'].'</a>';

if($cur_post['admin_note']){
$user_info[] = $lang_topic['Note'].': <strong>'.pun_htmlspecialchars($cur_post['admin_note']).'</strong>';
}
}
// Generation post action array (reply, delete etc.)
if(!$status){
$post_actions[] = '<a href="message_send.php?id='.$cur_post['id'].'&amp;reply='.$cur_post['mid'].'">'.$lang_pms['Reply'].'</a>';
}

$post_actions[] = '<a href="message_delete.php?id='.$cur_post['mid'].'&amp;box='.$box.'&amp;p='.$p.'">'.$lang_pms['Delete'].'</a>';

if(!$status){
$post_actions[] = '<a href="message_send.php?id='.$cur_post['id'].'&amp;quote='.$cur_post['mid'].'">'.$lang_pms['Quote'].'</a>';
}

}
// If the sender has been deleted
else
{
$result = $db->query('SELECT id,sender,message,posted FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
$cur_post = $db->fetch_assoc($result);

$username = pun_htmlspecialchars($cur_post['sender']);
$user_title = 'Deleted User';

$post_actions[] = '<a href="message_delete.php?id='.$cur_post['id'].'&amp;box='.$box.'&amp;p='.$p.'">'.$lang_pms['Delete'].'</a>';

$is_online = $lang_topic['Offline'];
}

// Perform the main parsing of the message (BBCode, smilies, censor words etc)
$cur_post['smileys'] = isset($cur_post['smileys']) ? $cur_post['smileys'] : $pun_user['show_smilies'];
$cur_post['message'] = parse_message($cur_post['message'], intval(!$cur_post['smileys']));

// Do signature parsing/caching
if(isset($cur_post['signature']) && $pun_user['show_sig']){
$signature = parse_signature($cur_post['signature']);
}


echo '<table class="msg2">
<tr><td>
<strong>'.format_time($cur_post['posted']).'<br/>'.$username.'<br/></strong>';
if($user_info){
echo implode('<br/>', $user_info);
}
if($cur_post['id'] > 1){
echo '<br/>'.$is_online;
}
echo '</td><td>'.@$user_avatar.'</td></tr>
</table>
<table class="msg"><tr><td>'.$cur_post['message'].'</td></tr>
<tr><td><span class="con">';
if($post_actions){
echo implode($lang_topic['Link separator'], $post_actions);
}
print '</span></td></tr></table>';

}

echo '<div class="input">
<form method="post" action="message_list.php?">
<div class="blocktable">
<strong>'.$name.'<br/></strong>
<div class="box">
<table>
<tr>';

if($pun_user['g_pm_limit'] && $pun_user['g_id'] > PUN_GUEST){
// Get total message count
$result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
list($tot_messages) = $db->fetch_row($result);
$proc = ceil($tot_messages / $pun_user['g_pm_limit'] * 100);
$status = ' - '.$lang_pms['Status'].' '.$proc.'%';
}
else{
$status = null;
}

echo '<th>'.$lang_pms['Subject'].$status.'</th><th>';
if(!$box){
echo $lang_pms['Sender'];
}
else{
echo $lang_pms['Receiver'];
}

echo '</th><th>'.$lang_pms['Date'].'</th><th>'.$lang_pms['Delete'].'</th></tr>';

// Fetch messages
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id'].' AND status='.$box.' ORDER BY posted DESC '.$limit) or error('Unable to fetch messages list for forum', __FILE__, __LINE__, $db->error());
$new_messages = $messages_exist = false;

// If there are messages in this folder.
if($all = $db->num_rows($result))
{
$messages_exist = true;
while($cur_mess = $db->fetch_assoc($result))
{
/*
$icon_text = $lang_common['Normal icon'];
$icon_type = 'icon';
if($cur_mess['showed'] == '0')
{
$icon_text .= ' '.$lang_common['New icon'];
$icon_type = 'icon inew';
}
*/

($new_messages == false && !$cur_mess['showed']) ? $new_messages = true : null;

$subject = '<a href="message_list.php?id='.$cur_mess['id'].'&amp;p='.$p.'&amp;box='.$box.'">'.pun_htmlspecialchars($cur_mess['subject']).'</a>';

if(isset($_GET['id'])){
if($cur_mess['id'] == $_GET['id']){
$subject = '<strong>'.$subject.'</strong>';
}
}

echo '<tr>
<td>
<div class="mes">'.$subject.'<br/></div>
</td>
<td><a href="profile.php?id='.$cur_mess['sender_id'].'">'.$cur_mess['sender'].'</a></td>
<td>'.format_time($cur_mess['posted']).'</td>
<td><input type="checkbox" name="delete_messages[]" value="'.$cur_mess['id'].'"/></td>
</tr>';

}
}
else{
echo '<tr class="blocktable"><td colspan="4" class="red">'.$lang_pms['No messages'].'</td></tr>';
}

if($_GET['action'] == 'all'){
$p = $num_pages+1;
}


echo '</table>
</div>
<input type="hidden" name="box" value="' . $box . '"/>
'.($all ? '<input type="submit" value="' . $lang_pms['Delete'] . '"/>' : '').'
</div>
</form>
</div>
<div class="con">'.$lang_common['Pages'].': '.paginate($num_pages, $p, 'message_list.php?box='.$box).'<br/></div>';

if(isset($_GET['id']))
{$forum_id = $id;}
}
else
{
if(isset($_POST['update']))
{
isset($_POST['popup_enable']) ? $popup=1 : $popup=0;
isset($_POST['messages_enable']) ? $msg_enable=1 : $msg_enable=0;
$db->query('UPDATE '.$db->prefix.'users SET popup_enable='. $popup . ', messages_enable='. $msg_enable.' WHERE id='. $pun_user['id']) or error('Unable to update Private Messsage options', __FILE__, __LINE__, $db->error());
}

$result = $db->query('SELECT popup_enable, messages_enable FROM '.$db->prefix.'users WHERE id='. $pun_user['id']) or error('Unable to fetch user info for Private Messsage options', __FILE__, __LINE__, $db->error());
if(!$db->num_rows($result)){
wap_message($lang_common['Bad request']);
}
$user = $db->fetch_assoc($result);

echo '<div><strong>'.$name.'</strong></div>
<div class="input">
<form method="post" action="message_list.php?box=2">
<div>
<input type="hidden" name="form_sent" value="1" />
<fieldset>
<legend>'.$lang_pms['Options PM'].'<br/></legend>
<input type="checkbox" name="popup_enable" value="1"';
if($user['popup_enable'] == 1){
echo ' checked="checked"';
}
echo ' />'.$lang_pms['Use popup'].'<br /><input type="checkbox" name="messages_enable" value="1"';
if($user['messages_enable'] == 1){
echo ' checked="checked"';
}
echo ' />'.$lang_pms['Use messages'].'<br />
</fieldset>
<br/>
<input type="submit" name="update" value="'.$lang_pms['Send'].'" />
</div>
</form>
</div>';

}

$footer_style = 'message_list';
require_once PUN_ROOT.'wap/footer.php';
?>