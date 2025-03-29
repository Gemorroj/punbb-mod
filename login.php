<?php

// Send no-cache headers
\header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
\header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
\header('Cache-Control: post-check=0, pre-check=0', false);

if (isset($_GET['action'])) {
    \define('PUN_QUIET_VISIT', 1);
}

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

// Load the login.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/login.php';

if (isset($_POST['form_sent']) && 'in' === $_GET['action']) {
    $form_username = \trim($_POST['req_username']);
    $form_password = \trim($_POST['req_password']);

    $username_sql = 'username=\''.$db->escape($form_username).'\'';

    $result = $db->query('SELECT id, group_id, password, save_pass FROM '.$db->prefix.'users WHERE '.$username_sql);
    if (!$result) {
        \error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    }
    [$user_id, $group_id, $db_password_hash, $save_pass] = $db->fetch_row($result);

    $authorized = false;

    if ($db_password_hash) {
        $sha1_in_db = (40 == \strlen($db_password_hash));
        $sha1_available = (\function_exists('sha1') || \function_exists('mhash'));

        $form_password_hash = \pun_hash($form_password); // This could result in either an SHA-1 or an MD5 hash (depends on $sha1_available)

        if ($sha1_in_db && $sha1_available && $db_password_hash == $form_password_hash) {
            $authorized = true;
        } elseif (!$sha1_in_db && $db_password_hash == \md5($form_password)) {
            $authorized = true;

            if ($sha1_available) { // There's an MD5 hash in the database, but SHA1 hashing is available, so we update the DB
                $db->query('UPDATE '.$db->prefix.'users SET password=\''.$form_password_hash.'\' WHERE id='.$user_id) || \error('Unable to update user password', __FILE__, __LINE__, $db->error());
            }
        }
    }

    if (!$authorized) {
        \message($lang_login['Wrong user/pass'].' <a href="login.php?action=forget">'.$lang_login['Forgotten pass'].'</a>');
    }

    // Update the status if this is the first time the user logged in
    if (PUN_UNVERIFIED == $group_id) {
        $db->query('UPDATE '.$db->prefix.'users SET group_id='.$pun_config['o_default_user_group'].' WHERE id='.$user_id) || \error('Unable to update user status', __FILE__, __LINE__, $db->error());
    }

    // Remove this users guest entry from the online list
    $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(\get_remote_address()).'\'') || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

    $expire = (1 == $save_pass) ? \time() + 31536000 : 0;
    \pun_setcookie($user_id, $form_password_hash, $expire);

    \redirect(\pun_htmlspecialchars($_POST['redirect_url']), $lang_login['Login redirect']);
} elseif (isset($_GET['action']) && 'out' == $_GET['action']) {
    if ($pun_user['is_guest'] || $_GET['id'] != $pun_user['id'] || $_GET['csrf_token'] != \sha1($pun_user['id'].\sha1(\get_remote_address()))) {
        \redirect('index.php', '', 302);
    }

    // Remove user from "users online" list.
    $db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$pun_user['id']) || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

    // Update last_visit (make sure there's something to update it with)
    if (isset($pun_user['logged'])) {
        $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$pun_user['logged'].' WHERE id='.$pun_user['id']) || \error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
    }

    \pun_setcookie(1, \md5(\uniqid(\mt_rand(), true)), \time() + 31536000);

    \redirect('index.php', $lang_login['Logout redirect']);
} elseif (isset($_GET['action']) && ('forget' == $_GET['action'] || 'forget_2' == $_GET['action'])) {
    if (!$pun_user['is_guest']) {
        \redirect('index.php', '', 302);
    }

    if (isset($_POST['form_sent'])) {
        include PUN_ROOT.'include/email.php';

        // Validate the email-address
        $email = \strtolower(\trim($_POST['req_email']));
        if (!\is_valid_email($email)) {
            \message($lang_common['Invalid e-mail']);
        }

        $result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE email=\''.$db->escape($email).'\'');
        if (!$result) {
            \error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
        }

        if ($db->num_rows($result)) {
            // Load the "activate password" template
            $mail_tpl = \trim(\file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/activate_password.tpl'));

            // The first row contains the subject
            $first_crlf = \strpos($mail_tpl, "\n");
            $mail_subject = \trim(\substr($mail_tpl, 8, $first_crlf - 8));
            $mail_message = \trim(\substr($mail_tpl, $first_crlf));

            // Do the generic replacements first (they apply to all e-mails sent out here)
            $mail_message = \str_replace('<base_url>', $pun_config['o_base_url'].'/', $mail_message);
            $mail_message = \str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

            // Loop through users we found
            while ($cur_hit = $db->fetch_assoc($result)) {
                // Generate a new password and a new password activation code
                $new_password = \random_pass(8);
                $new_password_key = \random_pass(8);

                $db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.\pun_hash($new_password).'\', activate_key=\''.$new_password_key.'\' WHERE id='.$cur_hit['id']) || \error('Unable to update activation data', __FILE__, __LINE__, $db->error());

                // Do the user specific replacements to the template
                $cur_mail_message = \str_replace('<username>', $cur_hit['username'], $mail_message);
                $cur_mail_message = \str_replace('<activation_url>', $pun_config['o_base_url'].'/profile.php?id='.$cur_hit['id'].'&action=change_pass&key='.$new_password_key, $cur_mail_message);
                $cur_mail_message = \str_replace('<new_password>', $new_password, $cur_mail_message);

                \pun_mail($email, $mail_subject, $cur_mail_message);
            }

            \message($lang_login['Forget mail'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.');
        } else {
            \message($lang_login['No e-mail match'].' '.\htmlspecialchars($email).'.');
        }
    }

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_login['Request pass'];
    $required_fields = ['req_email' => $lang_common['E-mail']];
    $focus_element = ['request_pass', 'req_email'];

    require_once PUN_ROOT.'header.php';

    echo '<div class="blockform">
<h2><span>'.$lang_login['Request pass'].'</span></h2>
<div class="box">
<form id="request_pass" method="post" action="login.php?action=forget_2" onsubmit="return process_form(this);">
<div class="inform">
<fieldset>
<legend>'.$lang_login['Request pass legend'].'</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<input id="req_email" type="email" name="req_email" size="50" maxlength="50" />
<p>'.$lang_login['Request pass info'].'</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="request_pass" value="'.$lang_common['Submit'].'" /><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT.'footer.php';
}

if (!$pun_user['is_guest']) {
    \redirect('index.php', '', 302);
}

// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to index.php after login)
$redirect_url = (isset($_SERVER['HTTP_REFERER']) && \preg_match('#^'.\preg_quote($pun_config['o_base_url'], '#').'/(.*?)\.php#i', $_SERVER['HTTP_REFERER'])) ? \htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php';

$page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_common['Login'];
$required_fields = ['req_username' => $lang_common['Username'], 'req_password' => $lang_common['Password']];
$focus_element = ['login', 'req_username'];

require_once PUN_ROOT.'header.php';

echo '<div class="blockform">
<h2><span>'.$lang_common['Login'].'</span></h2>
<div class="box">
<form id="login" method="post" action="login.php?action=in" onsubmit="return process_form(this)">
<div class="inform">
<fieldset>
<legend>'.$lang_login['Login legend'].'</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<input type="hidden" name="redirect_url" value="'.$redirect_url.'" />
<label class="conl"><strong>'.$lang_common['Username'].'</strong><br /><input type="text" name="req_username" size="25" maxlength="25" /><br /></label>
<label class="conl"><strong>'.$lang_common['Password'].'</strong><br /><input type="password" name="req_password" size="16" maxlength="16" /><br /></label>
<p class="clearb">'.$lang_login['Login info'].'</p>
<p><a href="registration.php">'.$lang_login['Not registered'].'</a>
<a href="login.php?action=forget">'.$lang_login['Forgotten pass'].'</a></p>
</div>
</fieldset>
</div>
<p><input type="submit" name="login" value="'.$lang_common['Login'].'" /></p>
</form>
</div>
</div>';

require_once PUN_ROOT.'footer.php';
