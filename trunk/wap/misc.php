<?php
if (isset($_GET['action'])) {
    define('PUN_QUIET_VISIT', 1);
}

define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';


// Load the misc.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;

// REAL MARK TOPIC AS READ MOD BEGIN
$mark_forum_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0; // wap_message($lang_common['Bad request']);
// REAL MARK TOPIC AS READ MOD END

if ($action == 'rules') {
    // Load the register.php language file
    require PUN_ROOT . 'lang/' . $pun_user['language'] . '/register.php';

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_register['Forum rules'];
    require_once PUN_ROOT.'wap/header.php';
    echo '
    <div class="con"><strong>' . $lang_register['Forum rules'] . '</strong></div>
    <div class="msg">' . $pun_config['o_rules_message'] . '</div>';
    require_once PUN_ROOT . 'wap/footer.php';
} else if ($action == 'markread') {
    if ($pun_user['is_guest']) {
        wap_message($lang_common['No permission']);
    }

    // fix problem with null $pun_user['logged']
    $now = time();
    if (!$pun_user['logged']) {
        $pun_user['logged'] = $now;
    }
    // end fix

    $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$pun_user['logged'].' WHERE id='.$pun_user['id']) or error('Unable to update user last visit data', __FILE__, __LINE__, $db->error());
    
    // REAL MARK TOPIC AS READ MOD BEGIN
    if ($mark_forum_id > 0) {
        // mark one forum
        $result = $db->query('UPDATE '.$db->prefix.'log_forums SET mark_read='.$now.' WHERE forum_id='.$mark_forum_id.' AND user_id='.$pun_user['id']);// or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
        if (!$db->affected_rows()) {
            $db->query('INSERT INTO '.$db->prefix."log_forums (user_id, forum_id, log_time, mark_read) VALUES ('".$pun_user['id']."', '".$cur_forum['forum_id']."', '".$now."', '".$now."' )") or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
        }
        $db->query('DELETE FROM '.$db->prefix.'log_topics WHERE forum_id='.$mark_forum_id.' AND user_id='.$pun_user['id']) or error('Unable to delete marked as read topic info', __FILE__, __LINE__, $db->error());
    } else {
        // mark all forums
        $db->query('DELETE FROM '.$db->prefix.'log_topics WHERE user_id='.$pun_user['id']) or error('Unable to delete marked topics info', __FILE__, __LINE__, $db->error());
        $db->query('DELETE FROM '.$db->prefix.'log_forums WHERE user_id='.$pun_user['id']) or error('Unable to delete marked forums info', __FILE__, __LINE__, $db->error());
        $db->query('INSERT INTO '.$db->prefix.'log_forums (forum_id) SELECT f.id FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE fp.read_forum IS NULL OR fp.read_forum=1') or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE '.$db->prefix.'log_forums SET mark_read='.$now.' , log_time='.$now.', user_id='.$pun_user['id'].' WHERE user_id = 0') or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
    }
    // REAL MARK TOPIC AS READ MOD END

    wap_redirect('index.php');
} else if (isset($_GET['email'])) {
    if ($pun_user['is_guest']) {
        wap_message($lang_common['No permission']);
    }

    $recipient_id = intval($_GET['email']);
    if ($recipient_id < 2) {
        wap_message($lang_common['Bad request']);
    }

    $result = $db->query('SELECT username, email, email_setting FROM '.$db->prefix.'users WHERE id='.$recipient_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }

    list($recipient, $recipient_email, $email_setting) = $db->fetch_row($result);

    if ($email_setting == 2 && $pun_user['g_id'] > PUN_MOD) {
        wap_message($lang_misc['Form e-mail disabled']);
    }

    if (isset($_POST['form_sent'])) {
        // Clean up message and subject from POST
        $subject = pun_trim($_POST['req_subject']);
        $message = pun_trim($_POST['req_message']);

        if (!$subject) {
            wap_message($lang_misc['No e-mail subject']);
        } else if (!$message) {
            wap_message($lang_misc['No e-mail message']);
        } else if(mb_strlen($message) > 65535) {
            wap_message($lang_misc['Too long e-mail message']);
        }

        // Load the "form e-mail" template
        $mail_tpl = trim(file_get_contents(PUN_ROOT . 'lang/' . $pun_user['language'] . '/mail_templates/form_email.tpl'));

        // The first row contains the subject
        $first_crlf = strpos($mail_tpl, "\n");
        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
        $mail_message = trim(substr($mail_tpl, $first_crlf));

        $mail_subject = str_replace('<mail_subject>', $subject, $mail_subject);
        $mail_message = str_replace('<sender>', $pun_user['username'], $mail_message);
        $mail_message = str_replace('<board_title>', $pun_config['o_board_title'], $mail_message);
        $mail_message = str_replace('<mail_message>', $message, $mail_message);
        $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer'], $mail_message);

        require_once PUN_ROOT.'include/email.php';

        pun_mail($recipient_email, $mail_subject, $mail_message, '"' . str_replace('"', '', $pun_user['username']) . '" <' . $pun_user['email'] . '>');

        wap_redirect(htmlspecialchars($_POST['redirect_url']));
    }


    // Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to the users profile after the e-mail is sent)
    $redirect_url = (isset($_SERVER['HTTP_REFERER']) && preg_match('#^' . preg_quote($pun_config['o_base_url']) . '/(.*?)\.php#i', $_SERVER['HTTP_REFERER'])) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php';

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_misc['Send e-mail to'] . ' ' . pun_htmlspecialchars($recipient);
    $required_fields = array('req_subject' => $lang_misc['E-mail subject'], 'req_message' => $lang_misc['E-mail message']);
    $focus_element = array('email', 'req_subject');
    require_once PUN_ROOT.'wap/header.php';


    echo '
    <div class="con">'.$lang_misc['Send e-mail to'].' <strong>'.pun_htmlspecialchars($recipient).'</strong></div>
    <form method="post" action="misc.php?email='.$recipient_id.'">
    <div class="input">
    <strong>'.$lang_misc['Write e-mail'].'</strong><br/>
    <input type="hidden" name="form_sent" value="1" />
    <input type="hidden" name="redirect_url" value="'.$redirect_url.'" />
    '.$lang_misc['E-mail subject'].'<br />
    <input type="text" name="req_subject" maxlength="70" tabindex="1" /><br />
    '.$lang_misc['E-mail message'].'<br/>
    <textarea name="req_message" rows="4" cols="24" tabindex="2"></textarea><br />
    '.$lang_misc['E-mail disclosure note'].'</div>
    <div class="go_to">
    <input type="submit" name="submit" value="'.$lang_common['Submit'].'" tabindex="3" accesskey="s" />
    </div></form>';

    require_once PUN_ROOT.'wap/footer.php';
} else if (isset($_GET['report'])) {
    if ($pun_user['is_guest']) {
        wap_message($lang_common['No permission']);
    }

    $post_id = intval($_GET['report']);
    if ($post_id < 1) {
        wap_message($lang_common['Bad request']);
    }

    if (isset($_POST['form_sent'])) {
        // Clean up reason from POST
        $reason = pun_linebreaks(pun_trim($_POST['req_reason']));
        if (!$reason) {
            wap_message($lang_misc['No reason']);
        }

        // Get the topic ID
        $result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
        if (!$db->num_rows($result)) {
            wap_message($lang_common['Bad request']);
        }

        $topic_id = $db->result($result);

        // Get the subject and forum ID
        $result = $db->query('SELECT subject, forum_id FROM '.$db->prefix.'topics WHERE id='.$topic_id) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
        if (!$db->num_rows($result)) {
            wap_message($lang_common['Bad request']);
        }

        list($subject, $forum_id) = $db->fetch_row($result);

        // Should we use the internal report handling?
        if (!$pun_config['o_report_method'] || $pun_config['o_report_method'] == 2) {
            $db->query('INSERT INTO '.$db->prefix.'reports (post_id, topic_id, forum_id, reported_by, created, message) VALUES('.$post_id.', '.$topic_id.', '.$forum_id.', '.$pun_user['id'].', '.time().', \''.$db->escape($reason).'\')' ) or error('Unable to create report', __FILE__, __LINE__, $db->error());
        }

        // Should we e-mail the report?
        if ($pun_config['o_report_method'] == 1 || $pun_config['o_report_method'] == 2) {
            // We send it to the complete mailing-list in one swoop
            if ($pun_config['o_mailing_list']) {
                $mail_subject = 'Report('.$forum_id.') - \''.$subject.'\'';
                $mail_message = 'User \''.$pun_user['username'].'\' has reported the following message:'."\n".$pun_config['o_base_url'].'/viewtopic.php?pid='.$post_id.'#p'.$post_id."\n\n".'Reason:'."\n".$reason;

                require_once PUN_ROOT.'include/email.php';

                pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
            }
        }

        wap_redirect('viewtopic.php?pid='.$post_id.'#p'.$post_id);
    }


    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_misc['Report post'];
    $required_fields = array('req_reason' => $lang_misc['Reason']);
    $focus_element = array('report', 'req_reason');
    require_once PUN_ROOT.'wap/header.php';


    echo 
    '<div class="red">'.$lang_misc['Report post'].'</div>
    <form method="post" action="misc.php?report='.$post_id.'">
    <div class="input">    
    <strong>'.$lang_misc['Reason desc'].'</strong><br/>
    <input type="hidden" name="form_sent" value="1" />
    '.$lang_misc['Reason'].'<br />
    <textarea name="req_reason" rows="4" cols="24"></textarea></div>
    <div class="go_to"><input type="submit" name="submit" value="'.$lang_common['Submit'].'" accesskey="s" /></div></form>
    ';

    require_once PUN_ROOT.'wap/footer.php';
} else if (isset($_GET['subscribe'])) {
    if ($pun_user['is_guest'] || $pun_config['o_subscriptions'] != 1) {
        wap_message($lang_common['No permission']);
    }

    $topic_id = intval($_GET['subscribe']);
    if ($topic_id < 1) {
        wap_message($lang_common['Bad request']);
    }

    // Make sure the user can view the topic
    $result = $db->query('SELECT 1 FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$topic_id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }

    $result = $db->query('SELECT 1 FROM '.$db->prefix.'subscriptions WHERE user_id='.$pun_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        wap_message($lang_misc['Already subscribed']);
    }

    $db->query('INSERT INTO '.$db->prefix.'subscriptions (user_id, topic_id) VALUES('.$pun_user['id'].' ,'.$topic_id.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());

    wap_redirect('viewtopic.php?id='.$topic_id);
} else if (isset($_GET['unsubscribe'])) {
    if ($pun_user['is_guest'] || $pun_config['o_subscriptions'] != 1) {
        wap_message($lang_common['No permission']);
    }

    $topic_id = intval($_GET['unsubscribe']);
    if ($topic_id < 1) {
        wap_message($lang_common['Bad request']);
    }

    $result = $db->query('SELECT 1 FROM '.$db->prefix.'subscriptions WHERE user_id='.$pun_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_misc['Not subscribed']);
    }

    $db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE user_id='.$pun_user['id'].' AND topic_id='.$topic_id) or error('Unable to remove subscription', __FILE__, __LINE__, $db->error());

    wap_redirect('viewtopic.php?id='.$topic_id);
} else {
    wap_message($lang_common['Bad request']);
}

?>