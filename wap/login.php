<?php

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('r').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability

if (isset($_GET['action'])) {
    define('PUN_QUIET_VISIT', 1);
}

define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'wap/header.php';

// Load the login.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/login.php';

if (isset($_POST['form_sent']) && 'in' == @$_GET['action']) {
    $form_username = trim($_POST['req_username']);
    $form_password = trim($_POST['req_password']);

    $username_sql = 'username="'.$db->escape($form_username).'"';

    $result = $db->query('SELECT id, group_id, password, save_pass FROM '.$db->prefix.'users WHERE '.$username_sql) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    list($user_id, $group_id, $db_password_hash, $save_pass) = $db->fetch_row($result);

    $authorized = false;

    if ($db_password_hash) {
        $sha1_in_db = (40 == strlen($db_password_hash));
        $sha1_available = (function_exists('sha1') || function_exists('mhash'));

        $form_password_hash = pun_hash($form_password); // This could result in either an SHA-1 or an MD5 hash (depends on $sha1_available)

        if ($sha1_in_db && $sha1_available && $db_password_hash == $form_password_hash) {
            $authorized = true;
        } elseif (!$sha1_in_db && $db_password_hash == md5($form_password)) {
            $authorized = true;

            if ($sha1_available) { // There's an MD5 hash in the database, but SHA1 hashing is available, so we update the DB
                $db->query('UPDATE '.$db->prefix.'users SET password=\''.$form_password_hash.'\' WHERE id='.$user_id) or error('Unable to update user password', __FILE__, __LINE__, $db->error());
            }
        }
    }

    if (!$authorized) {
        // wap_message($lang_login['Wrong user/pass'] . ' <a href="login.php?action=forget">' . $lang_login['Forgotten pass'] . '</a>');
        wap_message($lang_login['Wrong user/pass']);
    }

    // Update the status if this is the first time the user logged in
    if (PUN_UNVERIFIED == $group_id) {
        $db->query('UPDATE '.$db->prefix.'users SET group_id='.$pun_config['o_default_user_group'].' WHERE id='.$user_id) or error('Unable to update user status', __FILE__, __LINE__, $db->error());
    }

    // Remove this users guest entry from the online list
    $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

    $expire = (1 == $save_pass) ? time() + 31536000 : 0;
    pun_setcookie($user_id, $form_password_hash, $expire);

    wap_redirect($_POST['redirect_url']);
} elseif ('out' == @$_GET['action']) {
    if ($pun_user['is_guest'] || @$_GET['id'] != $pun_user['id'] || @$_GET['csrf_token'] != sha1($pun_user['id'].sha1(get_remote_address()))) {
        wap_redirect('index.php', 302);
    }

    // Remove user from "users online" list.
    $db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$pun_user['id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

    // Update last_visit (make sure there's something to update it with)
    if (isset($pun_user['logged'])) {
        $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$pun_user['logged'].' WHERE id='.$pun_user['id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
    }

    pun_setcookie(1, md5(uniqid(mt_rand(), true)), time() + 31536000);
    wap_redirect('index.php');
} elseif ('forget' == @$_GET['action'] || 'forget_2' == @$_GET['action']) {
    if (!$pun_user['is_guest']) {
        wap_redirect('index.php', 302);
    }

    if (isset($_POST['form_sent'])) {
        include PUN_ROOT.'include/email.php';

        // Validate the email-address
        $email = strtolower(trim($_POST['req_email']));
        if (!is_valid_email($email)) {
            wap_message($lang_common['Invalid e-mail']);
        }

        $result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE email=\''.$db->escape($email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result)) {
            // Load the "activate password" template
            $mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/activate_password.tpl'));

            // The first row contains the subject
            $first_crlf = strpos($mail_tpl, "\n");
            $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
            $mail_message = trim(substr($mail_tpl, $first_crlf));

            // Do the generic replacements first (they apply to all e-mails sent out here)
            $mail_message = str_replace('<base_url>', $pun_config['o_base_url'].'/', $mail_message);
            $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

            // Loop through users we found
            while ($cur_hit = $db->fetch_assoc($result)) {
                // Generate a new password and a new password activation code
                $new_password = random_pass(8);
                $new_password_key = random_pass(8);

                $db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.pun_hash($new_password).'\', activate_key=\''.$new_password_key.'\' WHERE id='.$cur_hit['id']) or error('Unable to update activation data', __FILE__, __LINE__, $db->error());

                // Do the user specific replacements to the template
                $cur_mail_message = str_replace('<username>', $cur_hit['username'], $mail_message);
                $cur_mail_message = str_replace('<activation_url>', $pun_config['o_base_url'].'/profile.php?id='.$cur_hit['id'].'&action=change_pass&key='.$new_password_key, $cur_mail_message);
                $cur_mail_message = str_replace('<new_password>', $new_password, $cur_mail_message);

                pun_mail($email, $mail_subject, $cur_mail_message);
            }

            wap_message($lang_login['Forget mail'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.');
        } else {
            wap_message($lang_login['No e-mail match'].' '.htmlspecialchars($email).'.');
        }
    }

    $page_title = $pun_config['o_board_title'].' / '.$lang_login['Request pass'];

    $smarty->assign('page_title', $page_title);
    $smarty->assign('lang_login', $lang_login);

    $smarty->display('login.forget.tpl');
    exit();
}

if (!$pun_user['is_guest']) {
    wap_redirect('index.php', 302);
}

// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to index.php after login)
$redirect_url = (isset($_SERVER['HTTP_REFERER']) && preg_match('#^'.preg_quote($pun_config['o_base_url'], '#').'/(.*?)\.php#i', $_SERVER['HTTP_REFERER'])) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php';

$page_title = $pun_config['o_board_title'].' / '.$lang_common['Login'];

$smarty->assign('page_title', $page_title);
$smarty->assign('lang_login', $lang_login);
$smarty->assign('redirect_url', $redirect_url);

$smarty->display('login.tpl');
