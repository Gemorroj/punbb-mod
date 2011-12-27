<?php
session_start();

define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';


// If we are logged in, we shouldn't be here
if (!$pun_user['is_guest']) {
    header('Location: index.php', true, 301);
    exit;
}

// Load the register.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';

// Load the register.php/profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';

// Profile
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';

if (!$pun_config['o_regs_allow']) {
    wap_message($lang_register['No new regs']);
}


// User pressed the cancel button
if ($_GET['cancel']) {
    wap_redirect('index.php');
} else if ($pun_config['o_rules'] == 1 && !$_GET['agree'] && !$_POST['form_sent']) {
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_register['Register'];
    require_once PUN_ROOT.'wap/header.php';


echo '
<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <strong>'.$lang_register['Forum rules'].'</strong></div>
<div class="info">'.$lang_register['Rules legend'].'</div>
<form method="get" action="register.php?">
<div class="input">
'.$pun_config['o_rules_message'].'</div>
<div class="go_to">
<input type="submit" name="agree" value="'.$lang_register['Agree'].'" />
<input type="submit" name="cancel" value="'.$lang_register['Cancel'].'" />
</div></form>';

    require_once PUN_ROOT.'wap/footer.php';
} else if (isset($_POST['form_sent'])) {
    // Check that someone from this IP didn't register a user within the last hour (DoS prevention)
    $result = $db->query('SELECT 1 FROM '.$db->prefix.'users WHERE registration_ip=\''.get_remote_address().'\' AND registered>'.(time() - $pun_config['o_timeout_reg'])) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


    if ($db->num_rows($result)) {
        wap_message($lang_register['Timeout']);
    }

    // IMAGE VERIFICATION MOD BEGIN
    // Image verifcation
    if ($pun_config['o_regs_verify_image'] == 1) {
        // Make sure what they submitted is not empty
        if (!trim($_POST['req_image_'])) {
            unset($_SESSION['captcha_keystring']);
            wap_message($lang_register['Text mismatch']);
        }


        if ($_SESSION['captcha_keystring'] != strtolower(trim($_POST['req_image_']))) {
            unset($_SESSION['captcha_keystring']);
            wap_message($lang_register['Text mismatch']);
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

        $password1 = random_pass(mt_rand(8,9));
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
    } else if (mb_strlen($username) > 25) { // This usually doesn't happen since the form element only accepts 25 characters
        wap_message($lang_common['Bad request']);
    } else if (mb_strlen($password1) < 4) {
        wap_message($lang_prof_reg['Pass too short']);
    } else if ($password1 != $password2) {
        wap_message($lang_prof_reg['Pass not match']);
    } else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) {
        wap_message($lang_prof_reg['Username guest']);
    } else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) {
        wap_message($lang_prof_reg['Username IP']);
    } else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, "'") !== false && strpos($username, '"') !== false) {
        wap_message($lang_prof_reg['Username reserved chars']);
    } else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) {
        wap_message($lang_prof_reg['Username BBCode']);
    }

    // Check username for any censored words
    if ($pun_config['o_censoring'] == 1) {
        // If the censored username differs from the username
        if (censor_words($username) != $username) {
            wap_message($lang_register['Username censor']);
        }
    }

    // Check that the username (or a too similar username) is not already registered
    $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE UPPER(username)=UPPER(\''.$db->escape($username).'\') OR UPPER(username)=UPPER(\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\')') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result)) {
        $busy = $db->result($result);
        wap_message($lang_register['Username dupe 1'].' '.pun_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2']);
    }


    // Validate e-mail
    require PUN_ROOT.'include/email.php';

    if (!is_valid_email($email1)) {
        wap_message($lang_common['Invalid e-mail']);
    } else if ($pun_config['o_regs_verify'] == 1 && $email1 != $email2) {
        wap_message($lang_register['E-mail not match']);
    }

    // Check it it's a banned e-mail address
    if (is_banned_email($email1)) {
        if (!$pun_config['p_allow_banned_email']) {
            wap_message($lang_prof_reg['Banned e-mail']);
        }

        $banned_email = true;	// Used later when we send an alert e-mail
    } else {
        $banned_email = false;
    }

    // Check if someone else already has registered with that e-mail address
    $dupe_list = array();

    $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE email=\''.$email1.'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
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
        if (!file_exists(PUN_ROOT.'lang/'.$language.'/common.php')) {
            wap_message($lang_common['Bad request']);
        }
    } else {
        $language = $pun_config['o_default_lang'];
    }

    $timezone = round($_POST['timezone'], 1);
    $save_pass = ($_POST['save_pass'] != 1) ? 0 : 1;

    $email_setting = intval($_POST['email_setting']);
    if ($email_setting < 0 || $email_setting > 2){
        $email_setting = 1;
    }

    // Insert the new user into the database. We do this now to get the last inserted id for later use.
    $now = time();

    $intial_group_id = (!$pun_config['o_regs_verify']) ? $pun_config['o_default_user_group'] : PUN_UNVERIFIED;
    $password_hash = pun_hash($password1);

    $sex = intval($_POST['req_sex']);

    // Add the user
    $db->query('
        INSERT INTO '.$db->prefix.'users (
            username, group_id, password, sex, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit
        ) VALUES(
            \''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$sex.'\', \''.$email1.'\', '.$email_setting.', '.$save_pass.', '.$timezone.' , \''.$db->escape($language).'\', \''.$pun_config['o_default_style'].'\', '.$now.', \''.get_remote_address().'\', '.$now.'
        )
    ') or error('Unable to create user', __FILE__, __LINE__, $db->error());
    $new_uid = $db->insert_id();

    // If we previously found out that the e-mail was banned
    if ($banned_email && $pun_config['o_mailing_list']) {
        $mail_subject = 'Alert - Banned e-mail detected';
        $mail_message = 'User "'.$username.'" registered with banned e-mail address: '.$email1."\n\n".'User profile: '.$pun_config['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // If we previously found out that the e-mail was a dupe
    if ($dupe_list && $pun_config['o_mailing_list']) {
        $mail_subject = 'Alert - Duplicate e-mail detected';
        $mail_message = 'User "'.$username.'" registered with an e-mail address that also belongs to: '.implode(', ', $dupe_list)."\n\n".'User profile: '.$pun_config['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // Should we alert people on the admin mailing list that a new user has registered?
    if ($pun_config['o_regs_report'] == 1) {
        $mail_subject = 'Alert - New registration';
        $mail_message = 'User "'.$username.'" registered in the forums at '.$pun_config['o_base_url']."\n\n".'User profile: '.$pun_config['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // Must the user verify the registration or do we log him/her in right now?
    if ($pun_config['o_regs_verify'] == 1) {
        // Load the "welcome" template
        $mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/welcome.tpl'));

        // The first row contains the subject
        $first_crlf = strpos($mail_tpl, "\n");
        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
        $mail_message = trim(substr($mail_tpl, $first_crlf));

        $mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
        $mail_message = str_replace('<base_url>', $pun_config['o_base_url'].'/', $mail_message);
        $mail_message = str_replace('<username>', $username, $mail_message);
        $mail_message = str_replace('<password>', $password1, $mail_message);
        $mail_message = str_replace('<login_url>', $pun_config['o_base_url'].'/login.php', $mail_message);
        $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

        pun_mail($email1, $mail_subject, $mail_message);

        wap_message($lang_register['Reg e-mail'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.', true);
    }

    pun_setcookie($new_uid, $password_hash, ($save_pass) ? $now + 31536000 : 0);

    wap_redirect('index.php');
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_register['Register'];

// Image Verification mod BEGIN
//
// Original code:
//$required_fields = array('req_username' => $lang_common['Username'], 'req_password1' => $lang_common['Password'], 'req_password2' => $lang_prof_reg['Confirm pass'], 'req_email1' => $lang_common['E-mail'], 'req_email2' => $lang_common['E-mail'].' 2');

$required_fields = array(
    'req_image_' => $lang_register['Image text'],
    'req_username' => $lang_common['Username'],
    'req_password1' => $lang_common['Password'],
    'req_password2' => $lang_prof_reg['Confirm pass'],
    'req_email1' => $lang_common['E-mail'],
    'req_email2' => $lang_common['E-mail'] . ' 2'
);


// Image Verification mod end
$focus_element = array('register', 'req_username');
require_once PUN_ROOT.'wap/header.php';


echo '
<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <strong>'.$lang_register['Register'].'</strong></div>
<form method="post" action="register.php?action=register">
<div class="msg">'.$lang_register['Desc 1'].'</div>
<div class="in2">'.$lang_register['Desc 2'].'</div>
<div class="input2">
<strong>'.$lang_register['Username legend'].'</strong>
<input type="hidden" name="form_sent" value="1" /><br/>
<strong>'.$lang_common['Username'].'</strong><br />
<input type="text" name="req_username" maxlength="25" /><br />
<strong>'.$lang_profile['sex'].'</strong>
<select name="req_sex"><option value="1">'.$lang_profile['m'].'</option><option value="0">'.$lang_profile['w'].'</option></select>
</div>
';
if (!$pun_config['o_regs_verify']) {
    echo '
<div class="input">
<strong>'.$lang_register['Pass legend 1'].'</strong><br/>
<strong>'.$lang_common['Password'].'</strong><br />
<input type="password" name="req_password1" maxlength="16" /><br />
<strong>'.$lang_prof_reg['Confirm pass'].'</strong><br />
<input type="password" name="req_password2" maxlength="16" /><br />
'.$lang_register['Pass info'] . '</div>';
}

// IMAGE VERIFICATION MOD BEGIN
if ($pun_config['o_regs_verify_image'] == 1) {
    echo '
<div class="input2">
<strong>'.$lang_register['Image verification'].'</strong><br/>
<img src="'.$pun_config['o_base_url'].'/include/captcha/captcha.php?'.session_name().'='.session_id().'" alt=""/><br />
<strong>'.$lang_register['Image text'].'</strong><br />
<input type="text" name="req_image_" maxlength="4"/><br />
'.$lang_register['Image info'] . '</div>';
}
// IMAGE VERIFICATION MOD END

echo '
<div class="input"><strong>';
if ($pun_config['o_regs_verify'] == 1) {
    echo $lang_prof_reg['E-mail legend 2'];
} else {
    echo $lang_prof_reg['E-mail legend'];
}
echo '</strong><br/>';
if ($pun_config['o_regs_verify'] == 1) {
    echo $lang_register['E-mail info'].'<br/>';
}
echo '
<strong>'.$lang_common['E-mail'].'</strong><br />
<input type="text" name="req_email1" maxlength="50" /><br />';
if ($pun_config['o_regs_verify'] == 1) {
    echo '
    <strong>'.$lang_register['Confirm e-mail'].'</strong><br />
    <input type="text" name="req_email2" maxlength="50" />';
}
echo '</div>
<div class="input2">
<strong>'.$lang_prof_reg['Localisation legend'].'</strong><br/>
'.$lang_prof_reg['Timezone'].': '.$lang_prof_reg['Timezone info'].'<br/>
<select name="timezone">';
?>
<option value="-12"<?php if ($pun_config['o_server_timezone'] == -12 ) echo ' selected="selected"' ?>>-12</option>
<option value="-11"<?php if ($pun_config['o_server_timezone'] == -11) echo ' selected="selected"' ?>>-11</option>
<option value="-10"<?php if ($pun_config['o_server_timezone'] == -10) echo ' selected="selected"' ?>>-10</option>
<option value="-9.5"<?php if ($pun_config['o_server_timezone'] == -9.5) echo ' selected="selected"' ?>>-9.5</option>
<option value="-9"<?php if ($pun_config['o_server_timezone'] == -9 ) echo ' selected="selected"' ?>>-09</option>
<option value="-8.5"<?php if ($pun_config['o_server_timezone'] == -8.5) echo ' selected="selected"' ?>>-8.5</option>
<option value="-8"<?php if ($pun_config['o_server_timezone'] == -8 ) echo ' selected="selected"' ?>>-08 PST</option>
<option value="-7"<?php if ($pun_config['o_server_timezone'] == -7 ) echo ' selected="selected"' ?>>-07 MST</option>
<option value="-6"<?php if ($pun_config['o_server_timezone'] == -6 ) echo ' selected="selected"' ?>>-06 CST</option>
<option value="-5"<?php if ($pun_config['o_server_timezone'] == -5 ) echo ' selected="selected"' ?>>-05 EST</option>
<option value="-4"<?php if ($pun_config['o_server_timezone'] == -4 ) echo ' selected="selected"' ?>>-04 AST</option>
<option value="-3.5"<?php if ($pun_config['o_server_timezone'] == -3.5) echo ' selected="selected"' ?>>-3.5</option>
<option value="-3"<?php if ($pun_config['o_server_timezone'] == -3 ) echo ' selected="selected"' ?>>-03 ADT</option>
<option value="-2"<?php if ($pun_config['o_server_timezone'] == -2 ) echo ' selected="selected"' ?>>-02</option>
<option value="-1"<?php if ($pun_config['o_server_timezone'] == -1) echo ' selected="selected"' ?>>-01</option>
<option value="0"<?php if ($pun_config['o_server_timezone'] == 0) echo ' selected="selected"' ?>>00 GMT</option>
<option value="1"<?php if ($pun_config['o_server_timezone'] == 1) echo ' selected="selected"' ?>>+01 CET</option>
<option value="2"<?php if ($pun_config['o_server_timezone'] == 2 ) echo ' selected="selected"' ?>>+02</option>
<option value="3"<?php if ($pun_config['o_server_timezone'] == 3 ) echo ' selected="selected"' ?>>+03</option>
<option value="3.5"<?php if ($pun_config['o_server_timezone'] == 3.5 ) echo ' selected="selected"' ?>>+03.5</option>
<option value="4"<?php if ($pun_config['o_server_timezone'] == 4 ) echo ' selected="selected"' ?>>+04</option>
<option value="4.5"<?php if ($pun_config['o_server_timezone'] == 4.5 ) echo ' selected="selected"' ?>>+04.5</option>
<option value="5"<?php if ($pun_config['o_server_timezone'] == 5 ) echo ' selected="selected"' ?>>+05</option>
<option value="5.5"<?php if ($pun_config['o_server_timezone'] == 5.5 ) echo ' selected="selected"' ?>>+05.5</option>
<option value="6"<?php if ($pun_config['o_server_timezone'] == 6 ) echo ' selected="selected"' ?>>+06</option>
<option value="6.5"<?php if ($pun_config['o_server_timezone'] == 6.5 ) echo ' selected="selected"' ?>>+06.5</option>
<option value="7"<?php if ($pun_config['o_server_timezone'] == 7 ) echo ' selected="selected"' ?>>+07</option>
<option value="8"<?php if ($pun_config['o_server_timezone'] == 8 ) echo ' selected="selected"' ?>>+08</option>
<option value="9"<?php if ($pun_config['o_server_timezone'] == 9 ) echo ' selected="selected"' ?>>+09</option>
<option value="9.5"<?php if ($pun_config['o_server_timezone'] == 9.5 ) echo ' selected="selected"' ?>>+09.5</option>
<option value="10"<?php if ($pun_config['o_server_timezone'] == 10) echo ' selected="selected"' ?>>+10</option>
<option value="10.5"<?php if ($pun_config['o_server_timezone'] == 10.5 ) echo ' selected="selected"' ?>>+10.5</option>
<option value="11"<?php if ($pun_config['o_server_timezone'] == 11) echo ' selected="selected"' ?>>+11</option>
<option value="11.5"<?php if ($pun_config['o_server_timezone'] == 11.5 ) echo ' selected="selected"' ?>>+11.5</option>
<option value="12"<?php if ($pun_config['o_server_timezone'] == 12 ) echo ' selected="selected"' ?>>+12</option>
<option value="13"<?php if ($pun_config['o_server_timezone'] == 13 ) echo ' selected="selected"' ?>>+13</option>
<option value="14"<?php if ($pun_config['o_server_timezone'] == 14 ) echo ' selected="selected"' ?>>+14</option>
</select>
<?php

echo '</div>
<div class="input">';

$languages = array();
$d = dir(PUN_ROOT.'lang');
while (($entry = $d->read()) !== false) {
    if ($entry[0] != '.' && is_dir(PUN_ROOT.'lang/'.$entry) && file_exists(PUN_ROOT.'lang/'.$entry.'/common.php')) {
        $languages[] = $entry;
    }
}
$d->close();

// Only display the language selection box if there's more than one language available
if (sizeof($languages) > 1) {
    echo '<strong>'.$lang_prof_reg['Language'].'</strong>: '.$lang_prof_reg['Language info'].'<br/><select name="language">';

    while (list(, $temp) = @each($languages)) {
        if ($pun_config['o_default_lang'] == $temp) {
            echo '<option value="'.$temp.'" selected="selected">'.$temp.'</option>';
        } else {
            echo '<option value="'.$temp.'">'.$temp.'</option>';
        }
    }

    echo '</select>';
}

echo '</div>
<div class="input2">
<strong>'.$lang_prof_reg['Privacy options legend'].'</strong><br/>
'.$lang_prof_reg['E-mail setting info'].'<br/>
<input type="radio" name="email_setting" value="0" />'.$lang_prof_reg['E-mail setting 1'].'<br />
<input type="radio" name="email_setting" value="1" checked="checked" />'.$lang_prof_reg['E-mail setting 2'].'<br />
<input type="radio" name="email_setting" value="2" />'.$lang_prof_reg['E-mail setting 3'].'<br />
'.$lang_prof_reg['Save user/pass info'].'<br/>
<input type="checkbox" name="save_pass" value="1" checked="checked" />'.$lang_prof_reg['Save user/pass'].'</div>
<div class="go_to">
<input type="submit" name="register" value="'.$lang_register['Register'].'" />
</div></form>';


require_once PUN_ROOT.'wap/footer.php';
?>