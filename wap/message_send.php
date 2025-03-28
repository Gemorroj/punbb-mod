<?php

\define('PUN_ROOT', '../');

require_once PUN_ROOT.'include/common.php';

if (!$pun_config['o_pms_enabled'] || $pun_user['is_guest'] || !$pun_user['g_pm']) {
    \wap_message($lang_common['No permission']);
}

// Load the post.php language file
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

require_once PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

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
                \wap_message($lang_pms['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_pms['Flood end']);
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
        \wap_message($lang_pms['No user']);
    }
    $result = $db->query('SELECT messages_enable FROM '.$db->prefix.'users WHERE id='.$user['id']);
    if (!$result) {
        \error('Unable to get message status for user'.$id, __FILE__, __LINE__, $db->error());
    }
    $result = $db->fetch_assoc($result);
    if (!$result['messages_enable']) {
        \wap_message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
    }

    // Smileys
    if (@$_POST['hide_smilies']) {
        $smilies = 0;
    } else {
        $smilies = 1;
    }

    // Check subject
    $subject = \trim($_POST['req_subject']);
    if (!$subject) {
        \wap_message($lang_post['No subject']);
    } elseif (\mb_strlen($subject) > 70) {
        \wap_message($lang_post['Too long subject']);
    } elseif (!$pun_config['p_subject_all_caps'] && \mb_strtoupper($subject) == $subject && $pun_user['g_id'] > PUN_GUEST) {
        $subject = \ucwords(\mb_strtolower($subject));
    }

    // Clean up message from POST
    $message = \pun_linebreaks(\trim($_POST['req_message']));

    // Check message
    if (!$message) {
        \wap_message($lang_post['No message']);
    } elseif (\mb_strlen($message) > 65535) {
        \wap_message($lang_post['Too long message']);
    } elseif (!$pun_config['p_message_all_caps'] && \mb_strtoupper($message) == $message && $pun_user['g_id'] > PUN_GUEST) {
        $message = \ucwords(\strtolower($message));
    }

    // Validate BBCode syntax
    if (1 == $pun_config['p_message_bbcode'] && \str_contains($message, '[') && \str_contains($message, ']')) {
        include_once PUN_ROOT.'include/parser.php';
        $message = \preparse_bbcode($message, $errors);
    }
    if (isset($errors)) {
        \wap_message($errors[0]);
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
            \wap_message($lang_pms['Receiver'].' '.$_POST['req_username'].' '.$lang_pms['Disable options']);
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
                \wap_message($lang_pms['Inbox full']);
            }

            // Also check users own box
            if (isset($_POST['savemessage']) && 1 == (int) $_POST['savemessage']) {
                $result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']);
                if (!$result) {
                    \error('Unable to get message count the sender', __FILE__, __LINE__, $db->error());
                }
                [$count] = $db->fetch_row($result);
                if ($count >= $pun_user['g_pm_limit']) {
                    \wap_message($lang_pms['Sent full']);
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
        \wap_message($lang_pms['No user']);
    }

    $topic_redirect = (int) $_POST['topic_redirect'];
    $from_profile = (int) (@$_POST['from_profile']);

    if ($from_profile) {
        \wap_redirect('profile.php?id='.$from_profile);
    } elseif ($topic_redirect) {
        \wap_redirect('viewtopic.php?id='.$topic_redirect);
    } else {
        \wap_redirect('message_list.php');
    }
} else {
    $id = isset($_GET['id']) ? (int) ($_GET['id']) : 0;

    if ($id > 0) {
        $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id);
        if (!$result) {
            \error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
        }
        if (!$db->num_rows($result)) {
            \wap_message($lang_common['Bad request']);
        }
        [$username] = $db->fetch_row($result);
    }

    if (isset($_GET['reply']) || isset($_GET['quote'])) {
        $r = (int) @$_GET['reply'];
        $q = (int) @$_GET['quote'];

        // Get message info
        empty($r) ? $id = $q : $id = $r;
        $result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id.' AND owner='.$pun_user['id']);
        if (!$result) {
            \error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
        }
        if (!$db->num_rows($result)) {
            \wap_message($lang_common['Bad request']);
        }
        $message = $db->fetch_assoc($result);

        // Quote the message
        if (isset($_GET['quote'])) {
            $quote = '[quote='.$message['sender'].']'.$message['message'].'[/quote]';
        }
        // Add subject
        $subject = 'RE:' === \substr($message['subject'], 0, 3) ? $message['subject'] : 'RE: '.$message['subject'];
    }

    if (1 != $pun_user['messages_enable']) {
        \wap_message($lang_pms['PM disabled'].' <a href="message_list.php?&box=2">'.$lang_pms['Options PM'].'</a>');
    }

    require_once PUN_ROOT.'wap/header.php';

    $page_title = $pun_config['o_board_title'].' / '.$lang_pms['Send a message'];
    $smarty->assign('page_title', $page_title);

    $smarty->assign('username', @$username);
    $smarty->assign('subject', @$subject);
    $smarty->assign('quote', @$quote);
    $smarty->assign('lang_pms', $lang_pms);
    $smarty->assign('lang_post', $lang_post);

    $smarty->display('message_send.tpl');
}
