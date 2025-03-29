<?php
\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (!$pun_config['o_pms_enabled'] || $pun_user['is_guest'] || !$pun_user['g_pm']) {
    \message($lang_common['No permission']);
}

// Load the post.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

if (isset($_POST['form_sent'])) {
    // confirm_referrer('message_send.php');
    // Flood protection
    if ($pun_user['g_id'] > PUN_GUEST) {
        $result = $db->query('SELECT posted FROM '.$db->prefix.'messages WHERE sender_id='.$pun_user['id'].' ORDER BY id DESC LIMIT 1');
        if (!$result) {
            \error('Unable to fetch message time for flood protection', __FILE__, __LINE__, $db->error());
        }
        if ([$last] = $db->fetch_row($result)) {
            if (($_SERVER['REQUEST_TIME'] - $last) < $pun_user['g_post_flood']) {
                \message($lang_pms['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_pms['Flood end']);
            }
        }
    }

    // Get userid
    $result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.$db->escape($_POST['req_username']).'\'');
    if (!$result) {
        \error('Unable to get user id', __FILE__, __LINE__, $db->error());
    }
    $user = $db->fetch_assoc($result);
    if (!$user) {
        \message($lang_pms['No user']);
    }
    $result = $db->query('SELECT messages_enable FROM '.$db->prefix.'users WHERE id='.$user['id']);
    if (!$result) {
        \error('Unable to get message status for user'.$id, __FILE__, __LINE__, $db->error());
    }
    $result = $db->fetch_assoc($result);
    if (!$result['messages_enable']) {
        \message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
    }

    // Smileys
    if ($_POST['hide_smilies']) {
        $smilies = 0;
    } else {
        $smilies = 1;
    }

    // Check subject
    $subject = \trim($_POST['req_subject']);
    if (!$subject) {
        \message($lang_post['No subject']);
    } elseif (\mb_strlen($subject) > 70) {
        \message($lang_post['Too long subject']);
    } elseif (!$pun_config['p_subject_all_caps'] && \mb_strtoupper($subject) == $subject && $pun_user['g_id'] > PUN_GUEST) {
        $subject = \ucwords(\mb_strtolower($subject));
    }

    // Clean up message from POST
    $message = \pun_linebreaks(\trim($_POST['req_message']));

    // Check message
    if (!$message) {
        \message($lang_post['No message']);
    } elseif (\mb_strlen($message) > 65535) {
        \message($lang_post['Too long message']);
    } elseif (!$pun_config['p_message_all_caps'] && \mb_strtoupper($message) == $message && $pun_user['g_id'] > PUN_GUEST) {
        $message = \ucwords(\mb_strtolower($message));
    }

    // Validate BBCode syntax
    if (1 == $pun_config['p_message_bbcode'] && \str_contains($message, '[') && \str_contains($message, ']')) {
        include_once PUN_ROOT.'include/parser.php';
        $message = \preparse_bbcode($message, $errors);
    }
    if (isset($errors)) {
        \message($errors[0]);
    }

    // Get userid
    $result = $db->query('SELECT u.id, u.username, u.group_id, g.g_pm_limit, u.messages_enable FROM `'.$db->prefix.'users` AS u INNER JOIN `'.$db->prefix.'groups` AS g ON u.group_id=g.g_id WHERE u.id!=1 AND u.username=\''.$db->escape($_POST['req_username']).'\'');
    if (!$result) {
        \error('Unable to get user id', __FILE__, __LINE__, $db->error());
    }

    // $result = $db->query('SELECT id, username, group_id FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
    $result = $db->query('SELECT u.id, u.username, u.group_id, g.g_pm_limit, u.messages_enable FROM `'.$db->prefix.'users` AS u INNER JOIN `'.$db->prefix.'groups` AS g ON u.group_id=g.g_id WHERE u.id!=1 AND u.username=\''.$db->escape($_POST['req_username']).'\'');
    if (!$result) {
        \error('Unable to get user id', __FILE__, __LINE__, $db->error());
    }

    // Send message
    if ([$id, $user, $status, $group_pm_limit, $messages_enable] = $db->fetch_row($result)) {
        if (!$messages_enable) {
            \message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
        }

        // if(list($id,$user,$status) = $db->fetch_row($result)){
        // if(list($id,$user,$status,$group_pm_limit) = $db->fetch_row($result)){
        // Check inbox status
        if ($pun_user['g_pm_limit'] && $pun_user['g_id'] > PUN_GUEST && $status > PUN_GUEST) {
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'messages WHERE owner='.$id);
            if (!$result) {
                \error('Unable to get message count for the receiver', __FILE__, __LINE__, $db->error());
            }
            [$count] = $db->fetch_row($result);

            // if($count >= $pun_user['g_pm_limit'])
            if ($count >= $group_pm_limit) {
                \message($lang_pms['Inbox full']);
            }

            // Also check users own box
            if (isset($_POST['savemessage']) && 1 == (int) $_POST['savemessage']) {
                $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']);
                if (!$result) {
                    \error('Unable to get message count the sender', __FILE__, __LINE__, $db->error());
                }
                [$count] = $db->fetch_row($result);
                if ($count >= $pun_user['g_pm_limit']) {
                    \message($lang_pms['Sent full']);
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
        \''.\get_remote_address().'\',
        \''.$smilies.'\',
        \'0\',
        \'0\',
        \''.$_SERVER['REQUEST_TIME'].'\',
        \'0\'
        )') || \error('Unable to send message', __FILE__, __LINE__, $db->error());

        // Save an own copy of the message
        if (isset($_POST['savemessage'])) {
            $db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted, popup) VALUES(
            \''.$pun_user['id'].'\',
            \''.$db->escape($subject).'\',
            \''.$db->escape($message).'\',
            \''.$db->escape($user).'\',
            \''.$id.'\',
            \''.\get_remote_address().'\',
            \''.$smilies.'\',
            \'1\',
            \'1\',
            \''.$_SERVER['REQUEST_TIME'].'\',
            \'1\'
            )') || \error('Unable to send message', __FILE__, __LINE__, $db->error());
        }
    } else {
        \message($lang_pms['No user']);
    }

    $topic_redirect = (int) $_POST['topic_redirect'];
    $from_profile = (int) (@$_POST['from_profile']);
    if ($from_profile) {
        \redirect('profile.php?id='.$from_profile, $lang_pms['Sent redirect']);
    } elseif ($topic_redirect) {
        \redirect('viewtopic.php?id='.$topic_redirect, $lang_pms['Sent redirect']);
    } else {
        \redirect('message_list.php', $lang_pms['Sent redirect']);
    }
} else {
    $id = isset($_GET['id']) ? (int) ($_GET['id']) : 0;

    if ($id > 0) {
        $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id);
        if (!$result) {
            \error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
        }
        if (!$db->num_rows($result)) {
            \message($lang_common['Bad request']);
        }
        [$username] = $db->fetch_row($result);
    }

    if (isset($_GET['reply']) || isset($_GET['quote'])) {
        $r = (int) (@$_GET['reply']);
        $q = (int) (@$_GET['quote']);

        // Get message info
        empty($r) ? $id = $q : $id = $r;
        $result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id.' AND owner='.$pun_user['id']);
        if (!$result) {
            \error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
        }
        if (!$db->num_rows($result)) {
            \message($lang_common['Bad request']);
        }
        $message = $db->fetch_assoc($result);

        // Quote the message
        if (isset($_GET['quote'])) {
            $quote = '[quote='.$message['sender'].']'.$message['message'].'[/quote]';
        }

        // Add subject
        $subject = 'RE: '.$message['subject'];
    }

    $action = $lang_pms['Send a message'];
    $form = '<form method="post" id="post" name="post" action="message_send.php?action=send" onsubmit="return process_form(this)">';

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$action;
    $form_name = 'post';

    if (1 != $pun_user['messages_enable']) {
        \message($lang_pms['PM disabled'].' <a href="message_list.php?&amp;box=2">'.$lang_pms['Options PM'].'</a>');
    }
    $required_fields = ['req_message' => $lang_common['Message'], 'req_subject' => $lang_common['Subject'], 'req_username' => $lang_pms['Send to']];

    require_once PUN_ROOT.'header.php'; ?>
<div id="profile" class="block2col">
    <div class="blockmenu">
        <h2><span><?php echo $lang_pms['Private Messages']; ?></span></h2>

        <div class="box">
            <div class="inbox">
                <ul>
                    <li><a href="message_list.php?box=0"><?php echo $lang_pms['Inbox']; ?></a></li>
                    <li><a href="message_list.php?box=1"><?php echo $lang_pms['Outbox']; ?></a></li>
                    <li><a href="message_list.php?box=2"><?php echo $lang_pms['Options']; ?></a></li>
                    <li class="isactive"><a href="message_send.php"><?php echo $lang_pms['New message']; ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="blockform">
        <h2><span><?php echo $action; ?></span></h2>

        <div class="box">
            <?php echo $form; ?>
            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_common['Write message legend']; ?></legend>
                    <div class="infldset txtarea">
                        <input type="hidden" name="form_sent" value="1"/>
                        <input type="hidden" name="topic_redirect"
                               value="<?php echo isset($_GET['tid']) ? (int) ($_GET['tid']) : ''; ?>"/>
                        <input type="hidden" name="topic_redirect"
                               value="<?php echo isset($_POST['from_profile']) ? $from_profile : ''; ?>"/>
                        <input type="hidden" name="form_user"
                               value="<?php echo (!$pun_user['is_guest']) ? \pun_htmlspecialchars($pun_user['username']) : 'Guest'; ?>"/>
                        <label>
                            <strong><?php echo $lang_pms['Send to']; ?></strong><br/>
                            <input type="text" name="req_username" size="25" maxlength="25" value="<?php echo isset($username) ? \pun_htmlspecialchars($username) : ''; ?>" />
                        <br/></label>
                        <label>
                            <strong><?php echo $lang_common['Subject']; ?></strong><br/>
                            <input class="longinput"
                                    type="text"
                                    name="req_subject"
                                    value="<?php echo isset($subject) ? \pun_htmlspecialchars($subject) : ''; ?>"
                                    size="80"
                                    maxlength="70"
                                    /><br/>
                        </label>
                        <label><strong><?php echo $lang_common['Message']; ?></strong><br/>
                            <textarea name="req_message" rows="12" cols="92"><?php echo isset($quote) ? \pun_htmlspecialchars($quote) : ''; ?></textarea><br/>
                        </label>
                        <ul class="bblinks">
                            <li>
                                <a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode']; ?></a>: <?php echo (1 == $pun_config['p_message_bbcode']) ? $lang_common['on'] : $lang_common['off']; ?>
                            </li>
                            <li>
                                <a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag']; ?></a>: <?php echo (1 == $pun_config['p_message_img_tag']) ? $lang_common['on'] : $lang_common['off']; ?>
                            </li>
                            <li>
                                <a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies']; ?></a>: <?php echo (1 == $pun_config['o_smilies']) ? $lang_common['on'] : $lang_common['off']; ?>
                            </li>
                        </ul>
                    </div>
                </fieldset>
<?php
$checkboxes = [];

    if (1 == $pun_config['o_smilies']) {
        $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" '.(isset($_POST['hide_smilies']) ? 'checked="checked"' : '').' />'.$lang_post['Hide smilies'];
    }

    $checkboxes[] = '<label><input type="checkbox" name="savemessage" value="1" checked="checked" />'.$lang_pms['Save message'];

    if ($checkboxes) {
        echo '</div>
<div class="inform">
<fieldset>
<legend>'.$lang_common['Options'].'</legend>
<div class="infldset">
<div class="rbox">
'.\implode('<br/></label>', $checkboxes).'<br /></label>
</div>
</div>
</fieldset>';
    }

    echo '</div>
<p><input type="submit" name="submit" value="'.$lang_pms['Send'].'" accesskey="s" /><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

    require_once PUN_ROOT.'footer.php';
}
