<?php
session_start();

define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';
require_once PUN_ROOT . 'wap/header.php';

// If we are logged in, we shouldn't be here
if (!$pun_user['is_guest']) {
    wap_redirect('index.php', 302);
}

// Load the registration.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/registration.php';

// Load the registration.php/profile.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/prof_reg.php';

// Profile
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/profile.php';

if (!$pun_config['o_regs_allow']) {
    wap_message($lang_registration['No new regs']);
}


// User pressed the cancel button
if (@$_GET['cancel']) {
    wap_redirect('index.php');
} elseif ($pun_config['o_rules'] == 1 && !$_GET['agree'] && !$_POST['form_sent']) {
    $page_title = $pun_config['o_board_title'] . ' / ' . $lang_registration['Register'];

    $smarty->assign('page_title', $page_title);
    $smarty->assign('lang_registration', $lang_registration);

    $smarty->display('registration.agree.tpl');
    exit();
} elseif (isset($_POST['form_sent'])) {
    // Check that someone from this IP didn't register a user within the last hour (DoS prevention)
    $result = $db->query('SELECT 1 FROM ' . $db->prefix . 'users WHERE registration_ip=\'' . get_remote_address() . '\' AND registered>' . (time() - $pun_config['o_timeout_reg'])) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


    if ($db->num_rows($result)) {
        wap_message($lang_registration['Timeout']);
    }

    // IMAGE VERIFICATION MOD BEGIN
    // Image verifcation
    if ($pun_config['o_regs_verify_image'] == 1) {
        // Make sure what they submitted is not empty
        if (!trim($_POST['req_image_'])) {
            unset($_SESSION['captcha_keystring']);
            wap_message($lang_registration['Text mismatch']);
        }


        if ($_SESSION['captcha_keystring'] != strtolower(trim($_POST['req_image_']))) {
            unset($_SESSION['captcha_keystring']);
            wap_message($lang_registration['Text mismatch']);
        }
        if (!$_SESSION['captcha_keystring']) {
            unset($_SESSION['captcha_keystring']);
            wap_message($lang_common['Bad request']);
        }
        unset($_SESSION['captcha_keystring']);
    }
    // IMAGE VERIFICATION MOD END


    $username = pun_trim($_POST['req_username']);
    $email1 = strtolower(trim($_POST['req_email1']));

    if ($pun_config['o_regs_verify'] == 1) {
        $email2 = strtolower(trim($_POST['req_email2']));

        $password1 = random_pass(mt_rand(8, 9));
        $password2 = $password1;
    } else {
        $password1 = trim($_POST['req_password1']);
        $password2 = trim($_POST['req_password2']);
    }

    // Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
    $username = preg_replace('#\s+#s', ' ', $username);

    // Validate username and passwords
    if (mb_strlen($username) < 2) {
        wap_message($lang_prof_reg['Username too short']);
    } elseif (mb_strlen($username) > 25) { // This usually doesn't happen since the form element only accepts 25 characters
        wap_message($lang_common['Bad request']);
    } elseif (mb_strlen($password1) < 4) {
        wap_message($lang_prof_reg['Pass too short']);
    } elseif ($password1 != $password2) {
        wap_message($lang_prof_reg['Pass not match']);
    } elseif (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) {
        wap_message($lang_prof_reg['Username guest']);
    } elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) {
        wap_message($lang_prof_reg['Username IP']);
    } elseif ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, "'") !== false && strpos($username, '"') !== false) {
        wap_message($lang_prof_reg['Username reserved chars']);
    } elseif (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) {
        wap_message($lang_prof_reg['Username BBCode']);
    }

    // Check username for any censored words
    if ($pun_config['o_censoring'] == 1) {
        // If the censored username differs from the username
        if (censor_words($username) != $username) {
            wap_message($lang_registration['Username censor']);
        }
    }

    // Check that the username (or a too similar username) is not already registered
    $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE UPPER(username)=UPPER(\'' . $db->escape($username) . '\') OR UPPER(username)=UPPER(\'' . $db->escape(preg_replace('/[^\w]/', '', $username)) . '\')') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result)) {
        $busy = $db->result($result);
        wap_message($lang_registration['Username dupe 1'] . ' ' . pun_htmlspecialchars($busy) . '. ' . $lang_registration['Username dupe 2']);
    }


    // Validate e-mail
    require PUN_ROOT . 'include/email.php';

    if (!is_valid_email($email1)) {
        wap_message($lang_common['Invalid e-mail']);
    }
    if ($pun_config['o_regs_verify'] == 1) {
        if ($email1 !== $email2) {
            wap_message($lang_registration['E-mail not match']);
        }

        if (!is_email_not_spammer($email1)) {
            wap_message($lang_registration['E-mail is spammer']);
        }
    }

    if (!is_ip_not_spammer(get_remote_address())) {
        wap_message($lang_registration['IP is spammer']);
    }


    // Check it it's a banned e-mail address
    if (is_banned_email($email1)) {
        if (!$pun_config['p_allow_banned_email']) {
            wap_message($lang_prof_reg['Banned e-mail']);
        }

        $banned_email = true; // Used later when we send an alert e-mail
    } else {
        $banned_email = false;
    }

    // Check if someone else already has registered with that e-mail address
    $dupe_list = array();

    $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE email=\'' . $email1 . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        if (!$pun_config['p_allow_dupe_email']) {
            wap_message($lang_prof_reg['Dupe e-mail']);
        }

        while ($cur_dupe = $db->fetch_assoc($result)) {
            $dupe_list[] = $cur_dupe['username'];
        }
    }

    // Make sure we got a valid language string
    if ($_POST['language']) {
        $language = preg_replace('#[\.\\\/]#', '', $_POST['language']);
        if (!file_exists(PUN_ROOT . 'lang/' . $language . '/common.php')) {
            wap_message($lang_common['Bad request']);
        }
    } else {
        $language = $pun_config['o_default_lang'];
    }

    $timezone = round($_POST['timezone'], 1);
    $save_pass = ($_POST['save_pass'] != 1) ? 0 : 1;

    $email_setting = intval($_POST['email_setting']);
    if ($email_setting < 0 || $email_setting > 2) {
        $email_setting = 1;
    }

    // Insert the new user into the database. We do this now to get the last inserted id for later use.
    $now = time();

    $initial_group_id = (!$pun_config['o_regs_verify']) ? $pun_config['o_default_user_group'] : PUN_UNVERIFIED;
    $password_hash = pun_hash($password1);

    $sex = intval($_POST['req_sex']);

    // Add the user
    $db->query('
        INSERT INTO ' . $db->prefix . 'users (
            username, group_id, password, sex, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit
        ) VALUES(
            \'' . $db->escape($username) . '\', ' . $initial_group_id . ', \'' . $password_hash . '\', \'' . $sex . '\', \'' . $email1 . '\', ' . $email_setting . ', ' . $save_pass . ', ' . $timezone . ' , \'' . $db->escape($language) . '\', \'' . $pun_config['o_default_style'] . '\', ' . $now . ', \'' . get_remote_address() . '\', ' . $now . '
        )
    ') or error('Unable to create user', __FILE__, __LINE__, $db->error());
    $new_uid = $db->insert_id();

    // If we previously found out that the e-mail was banned
    if ($banned_email && $pun_config['o_mailing_list']) {
        $mail_subject = 'Alert - Banned e-mail detected';
        $mail_message = 'User "' . $username . '" registered with banned e-mail address: ' . $email1 . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] . '/profile.php?id=' . $new_uid . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" . '(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // If we previously found out that the e-mail was a dupe
    if ($dupe_list && $pun_config['o_mailing_list']) {
        $mail_subject = 'Alert - Duplicate e-mail detected';
        $mail_message = 'User "' . $username . '" registered with an e-mail address that also belongs to: ' . implode(', ', $dupe_list) . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] . '/profile.php?id=' . $new_uid . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" . '(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // Should we alert people on the admin mailing list that a new user has registered?
    if ($pun_config['o_regs_report'] == 1) {
        $mail_subject = 'Alert - New registration';
        $mail_message = 'User "' . $username . '" registered in the forums at ' . $pun_config['o_base_url'] . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] . '/profile.php?id=' . $new_uid . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" . '(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // Must the user verify the registration or do we log him/her in right now?
    if ($pun_config['o_regs_verify'] == 1) {
        // Load the "welcome" template
        $mail_tpl = trim(file_get_contents(PUN_ROOT . 'lang/' . $pun_user['language'] . '/mail_templates/welcome.tpl'));

        // The first row contains the subject
        $first_crlf = strpos($mail_tpl, "\n");
        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
        $mail_message = trim(substr($mail_tpl, $first_crlf));

        $mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
        $mail_message = str_replace('<base_url>', $pun_config['o_base_url'] . '/', $mail_message);
        $mail_message = str_replace('<username>', $username, $mail_message);
        $mail_message = str_replace('<password>', $password1, $mail_message);
        $mail_message = str_replace('<login_url>', $pun_config['o_base_url'] . '/login.php', $mail_message);
        $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer'], $mail_message);

        pun_mail($email1, $mail_subject, $mail_message);

        wap_message($lang_registration['Reg e-mail'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.', true);
    }

    pun_setcookie($new_uid, $password_hash, ($save_pass) ? $now + 31536000 : 0);

    wap_redirect('index.php');
}

$languages = array();
$d = dir(PUN_ROOT . 'lang');
while (($entry = $d->read()) !== false) {
    if ($entry[0] != '.' && is_dir(PUN_ROOT . 'lang/' . $entry) && file_exists(PUN_ROOT . 'lang/' . $entry . '/common.php')) {
        $languages[] = $entry;
    }
}
$d->close();

$page_title = $pun_config['o_board_title'] . ' / ' . $lang_registration['Register'];
$smarty->assign('page_title', $page_title);

$smarty->assign('lang_registration', $lang_registration);
$smarty->assign('lang_profile', $lang_profile);
$smarty->assign('lang_prof_reg', $lang_prof_reg);
$smarty->assign('languages', $languages);

$smarty->display('registration.tpl');
