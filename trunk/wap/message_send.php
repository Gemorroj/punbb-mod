<?php
define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';

if(!$pun_config['o_pms_enabled'] || $pun_user['is_guest'] || !$pun_user['g_pm']){
    wap_message($lang_common['No permission']);
}

// Load the post.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

if(isset($_POST['form_sent'])){
    //confirm_referrer('message_send.php');
    // Flood protection
    if($pun_user['g_id'] > PUN_GUEST){
        $result = $db->query('SELECT posted FROM '.$db->prefix.'messages WHERE sender_id='.$pun_user['id'].' ORDER BY id DESC LIMIT 1') or error('Unable to fetch message time for flood protection', __FILE__, __LINE__, $db->error());
        if(list($last) = $db->fetch_row($result)){
            if(($_SERVER['REQUEST_TIME'] - $last) < $pun_user['g_post_flood']) {
                wap_message($lang_pms['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_pms['Flood end']);
            }
        }
    }
    
    // Get userid
    $result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
    $user = $db->fetch_assoc($result);
    if(!$user) {
        wap_message($lang_pms['No user']);
    }
    $result = $db->query('SELECT messages_enable FROM '.$db->prefix.'users WHERE id='. $user['id']) or error('Unable to get message status for user'. $id, __FILE__, __LINE__, $db->error());
    $result = $db->fetch_assoc($result);
    if(!$result['messages_enable']){
        wap_message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
    }
    
    
    // Smileys
    if($_POST['hide_smilies']){
        $smilies = 0;
    } else {
        $smilies = 1;
    }
    
    // Check subject
    $subject = pun_trim($_POST['req_subject']);
    if (!$subject) {
        wap_message($lang_post['No subject']);
    } else if(mb_strlen($subject) > 70) {
        wap_message($lang_post['Too long subject']);
    } else if (!$pun_config['p_subject_all_caps'] && mb_strtoupper($subject) == $subject && $pun_user['g_id'] > PUN_GUEST) {
        $subject = ucwords(mb_strtolower($subject));
    }

    // Clean up message from POST
    $message = pun_linebreaks(pun_trim($_POST['req_message']));

    // Check message
    if (!$message) {
        wap_message($lang_post['No message']);
    } else if(mb_strlen($message) > 65535) {
        wap_message($lang_post['Too long message']);
    } else if (!$pun_config['p_message_all_caps'] && mb_strtoupper($message) == $message && $pun_user['g_id'] > PUN_GUEST) {
        $message = ucwords(strtolower($message));
    }

    // Validate BBCode syntax
    if ($pun_config['p_message_bbcode'] == 1 && strpos($message, '[') !== false && strpos($message, ']') !== false) {
        include_once PUN_ROOT.'include/parser.php';
        $message = preparse_bbcode($message, $errors);
    }
    if (isset($errors)) {
        wap_message($errors[0]);
    }

    // Get userid
    $result = $db->query('SELECT u.id, u.username, u.group_id, g.g_pm_limit, u.messages_enable FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id!=1 AND u.username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
    
    
    //$result = $db->query('SELECT id, username, group_id FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
    $result = $db->query('SELECT u.id, u.username, u.group_id, g.g_pm_limit, u.messages_enable FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id!=1 AND u.username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
    
    
    // Send message
    if(list($id,$user,$status,$group_pm_limit,$messages_enable) = $db->fetch_row($result)){
        if(!$messages_enable){
            wap_message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
        }
        
        //if(list($id,$user,$status) = $db->fetch_row($result)){
        //if(list($id,$user,$status,$group_pm_limit) = $db->fetch_row($result)){
        // Check inbox status
        if($pun_user['g_pm_limit'] && $pun_user['g_id'] > PUN_GUEST && $status > PUN_GUEST){
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'messages WHERE owner='.$id) or error('Unable to get message count for the receiver', __FILE__, __LINE__, $db->error());
            list($count) = $db->fetch_row($result);
            
            
            //if($count >= $pun_user['g_pm_limit'])
            if($count >= $group_pm_limit){
                wap_message($lang_pms['Inbox full']);
            }
            
            // Also check users own box
            if(isset($_POST['savemessage']) && intval($_POST['savemessage']) == 1) {
                $result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']) or error('Unable to get message count the sender', __FILE__, __LINE__, $db->error());
                list($count) = $db->fetch_row($result);
                if($count >= $pun_user['g_pm_limit']){
                    wap_message($lang_pms['Sent full']);
                }
            }
        }
        
        // "Send" message
        $db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted, popup) VALUES(
        \''.$id.'\',
        \''.$db->escape($subject).'\',
        \''.$db->escape($message).'\',
        \''.$db->escape($pun_user['username']).'\',
        \''.$pun_user['id'].'\',
        \''.get_remote_address().'\',
        \''.$smilies.'\',
        \'0\',
        \'0\',
        \''.$_SERVER['REQUEST_TIME'].'\',
        \'0\'
        )') or error('Unable to send message', __FILE__, __LINE__, $db->error());
        
        // Save an own copy of the message
        if(isset($_POST['savemessage'])){
            $db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted, popup) VALUES(
            \''.$pun_user['id'].'\',
            \''.$db->escape($subject).'\',
            \''.$db->escape($message).'\',
            \''.$db->escape($user).'\',
            \''.$id.'\',
            \''.get_remote_address().'\',
            \''.$smilies.'\',
            \'1\',
            \'1\',
            \''.$_SERVER['REQUEST_TIME'].'\',
            \'1\'
            )') or error('Unable to send message', __FILE__, __LINE__, $db->error());
        }
    } else {
        wap_message($lang_pms['No user']);
    }
    
    $topic_redirect = intval($_POST['topic_redirect']);
    $from_profile = intval(@$_POST['from_profile']);;
    if($from_profile) {
        wap_redirect('profile.php?id='.$from_profile);
    } else if($topic_redirect) {
        wap_redirect('viewtopic.php?id='.$topic_redirect);
    } else {
        wap_redirect('message_list.php');
    }
} else {
$id = intval(@$_GET['id']);

if($id > 0){
    $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
    if(!$db->num_rows($result)){
        wap_message($lang_common['Bad request']);
    }
    list($username) = $db->fetch_row($result);
}

if(isset($_GET['reply']) || isset($_GET['quote'])){
    $r = @intval(@$_GET['reply']);
    $q = @intval(@$_GET['quote']);
    
    // Get message info
    empty($r) ? $id = $q : $id = $r;
    $result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id.' AND owner='.$pun_user['id']) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }
    $message = $db->fetch_assoc($result);
    
    // Quote the message
    if(isset($_GET['quote'])){
        $quote = '[quote='.$message['sender'].']'.$message['message'].'[/quote]';
    }
    // Add subject
    $subject = 'RE: '.$message['subject'];
}

$action = $lang_pms['Send a message'];
$form = '<form method="post" id="post" action="message_send.php?action=send">';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$action;
$form_name = 'post';

$cur_index = 1;


if($pun_user['messages_enable'] != 1){
    wap_message($lang_pms['PM disabled'] . ' <a href="message_list.php?&box=2">'. $lang_pms['Options PM'] .'</a>');
}
$required_fields = array('req_message' => $lang_common['Message'], 'req_subject' => $lang_common['Subject'], 'req_username' => $lang_pms['Send to']);

require_once PUN_ROOT.'wap/header.php';

echo '<div class="incqbox" style="margin:1%;padding:2pt;">
<a href="message_list.php?box=0">'.$lang_pms['Inbox'].'</a><br/>
<a href="message_list.php?box=1">'.$lang_pms['Outbox'].'</a><br/>
<a href="message_list.php?box=2">'.$lang_pms['Options'].'</a><br/>
<a href="message_send.php">'.$lang_pms['New message'].'</a><br/>
</div>';

?>
<div><strong><?php echo $action; ?></strong><br/></div>
<div class="input">
<?php echo $form; ?>
<div>
<fieldset>
<legend><?php echo $lang_common['Write message legend'] ?><br/></legend>
<input type="hidden" name="form_sent" value="1" />
<input type="hidden" name="topic_redirect" value="<?php echo isset($_GET['tid']) ? intval($_GET['tid']) : ''; ?>" />
<input type="hidden" name="topic_redirect" value="<?php echo isset($_POST['from_profile']) ? $from_profile : ''; ?>" />
<input type="hidden" name="form_user" value="<?php echo (!$pun_user['is_guest']) ? pun_htmlspecialchars($pun_user['username']) : 'Guest'; ?>" />
<strong><?php echo $lang_pms['Send to']; ?></strong><br />
<?php echo '<input type="text" name="req_username" maxlength="25" value="'.pun_htmlspecialchars(@$username).'" tabindex="'.($cur_index++).'" />'; ?><br />
<strong><?php echo $lang_common['Subject']; ?></strong><br />
<input class="longinput" type='text' name='req_subject' value='<?php echo pun_htmlspecialchars($subject); ?>' maxlength="70" tabindex='<?php echo $cur_index++; ?>' /><br />
<strong><?php echo $lang_common['Message']; ?></strong><br />
<textarea name="req_message" rows="4" cols="24" tabindex="<?php echo $cur_index++; ?>"><?php echo pun_htmlspecialchars($quote); ?></textarea><br />
<?php
$checkboxes = array();

if($pun_config['o_smilies'] == 1){
    $checkboxes[] = '<input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['hide_smilies']) ? ' checked="checked"' : '').' />'.$lang_post['Hide smilies'];
}

$checkboxes[] = '<input type="checkbox" name="savemessage" value="1" checked="checked" tabindex="'.($cur_index++).'" />'.$lang_pms['Save message'];

if($checkboxes){
    echo implode('<br/>', $checkboxes).'<br/></fieldset>';
}

echo '<br/>
<input type="submit" name="submit" value="'.$lang_pms['Send'].'" tabindex="'.($cur_index++).'" accesskey="s" />
</div>
</form>
</div>';

require_once PUN_ROOT.'wap/footer.php';
}
?>