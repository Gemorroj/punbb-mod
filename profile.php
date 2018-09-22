<?php
define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 2) {
    message($lang_common['Bad request']);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$pun_user['g_read_board'] && ($action != 'change_pass' || !isset($_GET['key']))) {
    message($lang_common['No view']);
}

// Load the profile.php/registration.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/prof_reg.php';

// Load the profile.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/profile.php';



if ($action == 'change_pass') {
    if (isset($_GET['key'])) {
        // If the user is already logged in we shouldn't be here :)
        if (!$pun_user['is_guest']) {
            redirect('index.php', '');
        }

        $key = $_GET['key'];

        $result = $db->query('SELECT activate_string, activate_key FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch new password', __FILE__, __LINE__, $db->error());
        list($new_password_hash, $new_password_key) = $db->fetch_row($result);

        if (!$key || $key != $new_password_key) {
            message($lang_profile['Pass key bad'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.');
        } else {
            $db->query('UPDATE ' . $db->prefix . 'users SET password=\'' . $new_password_hash . '\', activate_string=NULL, activate_key=NULL WHERE id=' . $id) or error('Unable to update password', __FILE__, __LINE__, $db->error());
            message($lang_profile['Pass updated'], true);
        }
    }

    // Make sure we are allowed to change this users password
    if ($pun_user['id'] != $id) {
        if ($pun_user['g_id'] > PUN_MOD) { // A regular user trying to change another users password?
            message($lang_common['No permission']);
        } elseif ($pun_user['g_id'] == PUN_MOD) {
            // A moderator trying to change a users password?
            $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
            if (!$db->num_rows($result)) {
                message($lang_common['Bad request']);
            }

            if (!$pun_config['p_mod_edit_users'] || !$pun_config['p_mod_change_passwords'] || $db->result($result) < PUN_GUEST) {
                message($lang_common['No permission']);
            }
        }
    }

    if (isset($_POST['form_sent'])) {
        $old_password = isset($_POST['req_old_password']) ? trim($_POST['req_old_password']) : '';
        $new_password1 = trim($_POST['req_new_password1']);
        $new_password2 = trim($_POST['req_new_password2']);

        if ($new_password1 != $new_password2) {
            message($lang_prof_reg['Pass not match']);
        }
        if (strlen($new_password1) < 4) {
            message($lang_prof_reg['Pass too short']);
        }

        $result = $db->query('SELECT password, save_pass FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch password', __FILE__, __LINE__, $db->error());
        list($db_password_hash, $save_pass) = $db->fetch_row($result);

        $authorized = false;

        if ($db_password_hash) {
            $sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
            $sha1_available = (function_exists('sha1') || function_exists('mhash')) ? true : false;

            $old_password_hash = pun_hash($old_password); // This could result in either an SHA-1 or an MD5 hash

            if (($sha1_in_db && $sha1_available && $db_password_hash == $old_password_hash) || (!$sha1_in_db && $db_password_hash == md5($old_password)) || $pun_user['g_id'] < PUN_GUEST) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            message($lang_profile['Wrong pass']);
        }

        $new_password_hash = pun_hash($new_password1);

        $db->query('UPDATE ' . $db->prefix . 'users SET password=\'' . $new_password_hash . '\' WHERE id=' . $id) or error('Unable to update password', __FILE__, __LINE__, $db->error());
        if ($pun_user['id'] == $id) {
            $expire = ($save_pass == 1) ? $_SERVER['REQUEST_TIME'] + 31536000 : 0;
            pun_setcookie($pun_user['id'], $new_password_hash, $expire);
        }

        redirect('profile.php?section=essentials&amp;id=' . $id, $lang_profile['Pass updated redirect']);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
    $required_fields = array('req_old_password' => $lang_profile['Old pass'], 'req_new_password1' => $lang_profile['New pass'], 'req_new_password2' => $lang_profile['Confirm new pass']);
    $focus_element = array('change_pass', (($pun_user['g_id'] > PUN_MOD) ? 'req_old_password' : 'req_new_password1'));
    require_once PUN_ROOT . 'header.php';


    echo '<div class="blockform">
<h2><span>' . $lang_profile['Change pass'] . '</span></h2>
<div class="box">
<form id="change_pass" method="post" action="profile.php?action=change_pass&amp;id=' . $id . '" onsubmit="return process_form(this);">
<div class="inform">
<input type="hidden" name="form_sent" value="1" />
<fieldset>
<legend>' . $lang_profile['Change pass legend'] . '</legend>
<div class="infldset">';
    if ($pun_user['g_id'] > PUN_MOD) {
        echo '<label><strong>' . $lang_profile['Old pass'] . '</strong><br /><input type="password" name="req_old_password" size="16" maxlength="16" /><br /></label>';
    }
    echo '<label class="conl"><strong>' . $lang_profile['New pass'] . '</strong><br />
<input type="password" name="req_new_password1" size="16" maxlength="16" /><br /></label>
<label class="conl"><strong>' . $lang_profile['Confirm new pass'] . '</strong><br />
<input type="password" name="req_new_password2" size="16" maxlength="16" /><br /></label>
<div class="clearb"></div>
</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" /><a href="javascript:history.go(-1);">' . $lang_common['Go back'] . '</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} elseif ($action == 'change_email') {
    // Make sure we are allowed to change this users e-mail
    if ($pun_user['id'] != $id) {
        if ($pun_user['g_id'] > PUN_MOD) { // A regular user trying to change another users e-mail?
            message($lang_common['No permission']);
        } elseif ($pun_user['g_id'] == PUN_MOD) {
            // A moderator trying to change a users e-mail?
            $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
            if (!$db->num_rows($result)) {
                message($lang_common['Bad request']);
            }

            if (!$pun_config['p_mod_edit_users'] || $db->result($result) < PUN_GUEST) {
                message($lang_common['No permission']);
            }
        }
    }

    if (isset($_GET['key'])) {
        $key = $_GET['key'];

        $result = $db->query('SELECT activate_string, activate_key FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch activation data', __FILE__, __LINE__, $db->error());
        list($new_email, $new_email_key) = $db->fetch_row($result);

        if (!$key || $key != $new_email_key) {
            message($lang_profile['E-mail key bad'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.');
        } else {
            $db->query('UPDATE ' . $db->prefix . 'users SET email=activate_string, activate_string=NULL, activate_key=NULL WHERE id=' . $id) or error('Unable to update e-mail address', __FILE__, __LINE__, $db->error());
            message($lang_profile['E-mail updated'], true);
        }
    } elseif (isset($_POST['form_sent'])) {
        if (pun_hash($_POST['req_password']) != $pun_user['password']) {
            message($lang_profile['Wrong pass']);
        }

        include_once PUN_ROOT . 'include/email.php';

        // Validate the email-address
        $new_email = strtolower(trim($_POST['req_new_email']));
        if (!is_valid_email($new_email)) {
            message($lang_common['Invalid e-mail']);
        }
        // Check it it's a banned e-mail address
        if (is_banned_email($new_email)) {
            if (!$pun_config['p_allow_banned_email']) {
                message($lang_prof_reg['Banned e-mail']);
            } elseif ($pun_config['o_mailing_list']) {
                $mail_subject = 'Alert - Banned e-mail detected';
                $mail_message = 'User "' . $pun_user['username'] .
                    '" changed to banned e-mail address: ' . $new_email . "\n\n" . 'User profile: ' .
                    $pun_config['o_base_url'] . '/profile.php?id=' . $id . "\n\n" . '-- ' . "\n" .
                    'Forum Mailer' . "\n" . '(Do not reply to this message)';

                pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
            }
        }

        // Check if someone else already has registered with that e-mail address
        $result = $db->query('SELECT id, username FROM ' . $db->prefix .
            'users WHERE email=\'' . $db->escape($new_email) . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
        if ($db->num_rows($result)) {
            if (!$pun_config['p_allow_dupe_email']) {
                message($lang_prof_reg['Dupe e-mail']);
            } elseif ($pun_config['o_mailing_list']) {
                while ($cur_dupe = $db->fetch_assoc($result)) {
                    $dupe_list[] = $cur_dupe['username'];
                }

                $mail_subject = 'Alert - Duplicate e-mail detected';
                $mail_message = 'User "' . $pun_user['username'] .
                    '" changed to an e-mail address that also belongs to: ' . implode(', ', $dupe_list) .
                    "\n\n" . 'User profile: ' . $pun_config['o_base_url'] . '/profile.php?id=' . $id .
                    "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" . '(Do not reply to this message)';

                pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
            }
        }


        $new_email_key = random_pass(8);

        $db->query('UPDATE ' . $db->prefix . 'users SET activate_string=\'' . $db->
            escape($new_email) . '\', activate_key=\'' . $new_email_key . '\' WHERE id=' . $id) or
            error('Unable to update activation data', __FILE__, __LINE__, $db->error());

        // Load the "activate e-mail" template
        $mail_tpl = trim(file_get_contents(PUN_ROOT . 'lang/' . $pun_user['language'] .
            '/mail_templates/activate_email.tpl'));

        // The first row contains the subject
        $first_crlf = strpos($mail_tpl, "\n");
        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
        $mail_message = trim(substr($mail_tpl, $first_crlf));

        $mail_message = str_replace('<username>', $pun_user['username'], $mail_message);
        $mail_message = str_replace('<base_url>', $pun_config['o_base_url'], $mail_message);
        $mail_message = str_replace('<activation_url>', $pun_config['o_base_url'] .
            '/profile.php?action=change_email&id=' . $id . '&key=' . $new_email_key, $mail_message);
        $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' .
            $lang_common['Mailer'], $mail_message);

        pun_mail($new_email, $mail_subject, $mail_message);

        message($lang_profile['Activate e-mail sent'] . ' <a href="mailto:' . $pun_config['o_admin_email'] .
            '">' . $pun_config['o_admin_email'] . '</a>.', true);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
    $required_fields = array('req_new_email' => $lang_profile['New e-mail'],
        'req_password' => $lang_common['Password']);
    $focus_element = array('change_email', 'req_new_email');
    require_once PUN_ROOT . 'header.php';

    echo '<div class="blockform">
<h2><span>' . $lang_profile['Change e-mail'] . '</span></h2>
<div class="box">
<form id="change_email" method="post" action="profile.php?action=change_email&amp;id=' .
        $id . '" onsubmit="return process_form(this)">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['E-mail legend'] . '</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<label><strong>' . $lang_profile['New e-mail'] .
        '</strong><br /><input type="text" name="req_new_email" size="50" maxlength="50" /><br /></label>
<label><strong>' . $lang_common['Password'] .
        '</strong><br /><input type="password" name="req_password" size="16" maxlength="16" /><br /></label>
<p>' . $lang_profile['E-mail instructions'] . '</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="new_email" value="' . $lang_common['Submit'] .
        '" /><a href="javascript:history.go(-1)">' . $lang_common['Go back'] . '</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} elseif ($action == 'upload_avatar' || $action == 'upload_avatar2') {
    if (!$pun_config['o_avatars']) {
        message($lang_profile['Avatars disabled']);
    }

    if ($pun_user['id'] != $id && $pun_user['g_id'] > PUN_MOD) {
        message($lang_common['No permission']);
    }

    if (isset($_POST['form_sent'])) {
        if (!isset($_FILES['req_file'])) {
            message($lang_profile['No file']);
        }

        $uploaded_file = $_FILES['req_file'];

        // Make sure the upload went smooth
        if (isset($uploaded_file['error'])) {
            switch ($uploaded_file['error']) {
                case 1: // UPLOAD_ERR_INI_SIZE
                case 2: // UPLOAD_ERR_FORM_SIZE
                    message($lang_profile['Too large ini']);
                    break;

                case 3: // UPLOAD_ERR_PARTIAL
                    message($lang_profile['Partial upload']);
                    break;

                case 4: // UPLOAD_ERR_NO_FILE
                    message($lang_profile['No file']);
                    break;

                case 6: // UPLOAD_ERR_NO_TMP_DIR
                    message($lang_profile['No tmp directory']);
                    break;

                default:
                    // No error occured, but was something actually uploaded?
                    if (!$uploaded_file['size']) {
                        message($lang_profile['No file']);
                    }
                    break;
            }
        }

        if (is_uploaded_file($uploaded_file['tmp_name'])) {
            $allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png',
                'image/x-png');
            if (!in_array($uploaded_file['type'], $allowed_types)) {
                message($lang_profile['Bad type']);
            }

            // Make sure the file isn't too big
            if ($uploaded_file['size'] > $pun_config['o_avatars_size']) {
                message($lang_profile['Too large'] . ' ' . $pun_config['o_avatars_size'] . ' ' .
                    $lang_profile['bytes'] . '.');
            }

            // Determine type
            $extensions = null;
            if ($uploaded_file['type'] == 'image/gif') {
                $extensions = array('.gif', '.jpg', '.png');
            } elseif ($uploaded_file['type'] == 'image/jpeg' || $uploaded_file['type'] == 'image/pjpeg') {
                $extensions = array('.jpg', '.gif', '.png');
            } else {
                $extensions = array('.png', '.gif', '.jpg');
            }

            // Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions.
            if (!@move_uploaded_file($uploaded_file['tmp_name'], PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp')) {
                message($lang_profile['Move failed'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.');
            }

            // Now check the width/height
            list($width, $height, $type, ) = getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] .
                '/' . $id . '.tmp');
            if (!$width || !$height || $width > $pun_config['o_avatars_width'] || $height >
                $pun_config['o_avatars_height']
            ) {
                @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp');
                message($lang_profile['Too wide or high'] . ' ' . $pun_config['o_avatars_width'] .
                    'x' . $pun_config['o_avatars_height'] . ' ' . $lang_profile['pixels'] . '.');
            } elseif ($type == 1 && $uploaded_file['type'] != 'image/gif') {
                // Prevent dodgy uploads
                @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp');
                message($lang_profile['Bad type']);
            }

            // Delete any old avatars and put the new one in place
            @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0]);
            @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[1]);
            @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[2]);
            @rename(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp', PUN_ROOT .
                $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0]);
            @chmod(
                PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0],
                0644
            );
        } else {
            message($lang_profile['Unknown failure']);
        }

        // Enable use_avatar (seems sane since the user just uploaded an avatar)
        $db->query('UPDATE ' . $db->prefix . 'users SET use_avatar=1 WHERE id=' . $id) or
            error('Unable to update avatar state', __FILE__, __LINE__, $db->error());

        redirect('profile.php?section=personality&amp;id=' . $id, $lang_profile['Avatar upload redirect']);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
    $required_fields = array('req_file' => $lang_profile['File']);
    $focus_element = array('upload_avatar', 'req_file');
    require_once PUN_ROOT . 'header.php';


    echo '<div class="blockform">
<h2><span>' . $lang_profile['Upload avatar'] . '</span></h2>
<div class="box">
<form id="upload_avatar" method="post" enctype="multipart/form-data" action="profile.php?action=upload_avatar2&amp;id=' .
        $id . '" onsubmit="return process_form(this)">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Upload avatar legend'] . '</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<input type="hidden" name="MAX_FILE_SIZE" value="' . $pun_config['o_avatars_size'] .
        '" />
<label><strong>' . $lang_profile['File'] .
        '</strong><br /><input name="req_file" type="file" size="40" /><br /></label>
<p>' . $lang_profile['Avatar desc'] . ' ' . $pun_config['o_avatars_width'] .
        ' x ' . $pun_config['o_avatars_height'] . ' ' . $lang_profile['pixels'] . ' ' .
        $lang_common['and'] . ' ' . $pun_config['o_avatars_size'] . ' ' . $lang_profile['bytes'] .
        ' (' . ceil($pun_config['o_avatars_size'] / 1024) . ' kb).</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="upload" value="' . $lang_profile['Upload'] .
        '" /><a href="javascript:history.go(-1)">' . $lang_common['Go back'] . '</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} elseif ($action == 'delete_avatar') {
    if ($pun_user['id'] != $id && $pun_user['g_id'] > PUN_MOD) {
        message($lang_common['No permission']);
    }

    // confirm_referrer('profile.php');

    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg');
    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png');
    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif');

    // Disable use_avatar
    $db->query('UPDATE ' . $db->prefix . 'users SET use_avatar=0 WHERE id=' . $id) or
        error('Unable to update avatar state', __FILE__, __LINE__, $db->error());

    redirect('profile.php?section=personality&amp;id=' . $id, $lang_profile['Avatar deleted redirect']);
} elseif (isset($_POST['update_group_membership'])) {
    if ($pun_user['g_id'] > PUN_ADMIN) {
        message($lang_common['No permission']);
    }

    //confirm_referrer('profile.php');

    $new_group_id = intval($_POST['group_id']);

    $db->query('UPDATE ' . $db->prefix . 'users SET group_id=' . $new_group_id .
        ' WHERE id=' . $id) or error(
            'Unable to change user group',
            __FILE__,
            __LINE__,
        $db->error()
        );

    // If the user was a moderator or an administrator, we remove him/her from the moderator list in all forums as well
    if ($new_group_id > PUN_MOD) {
        $result = $db->query('SELECT id, moderators FROM ' . $db->prefix . 'forums') or
            error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

        while ($cur_forum = $db->fetch_assoc($result)) {
            $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) :
                array();

            if (in_array($id, $cur_moderators)) {
                $username = array_search($id, $cur_moderators);
                unset($cur_moderators[$username]);
                $cur_moderators = ($cur_moderators) ? '\'' . $db->escape(serialize($cur_moderators)) . '\'' : 'NULL';

                $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=' . $cur_moderators . ' WHERE id=' . $cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
            }
        }
    }

    redirect('profile.php?section=admin&amp;id=' . $id, $lang_profile['Group membership redirect']);
} elseif (isset($_POST['update_forums'])) {
    if ($pun_user['g_id'] > PUN_ADMIN) {
        message($lang_common['No permission']);
    }

    //confirm_referrer('profile.php');

    // Get the username of the user we are processing
    $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE id=' .
        $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    $username = $db->result($result);

    $moderator_in = (isset($_POST['moderator_in'])) ? array_keys($_POST['moderator_in']) :
        array();

    // Loop through all forums
    $result = $db->query('SELECT id, moderators FROM ' . $db->prefix . 'forums') or
        error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

    while ($cur_forum = $db->fetch_assoc($result)) {
        $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();
        // If the user should have moderator access (and he/she doesn't already have it)
        if (in_array($cur_forum['id'], $moderator_in) && !in_array($id, $cur_moderators)) {
            $cur_moderators[$username] = $id;
            ksort($cur_moderators);

            $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=\'' . $db->escape(serialize($cur_moderators)) . '\' WHERE id=' . $cur_forum['id']) or error(
                'Unable to update forum',
                __FILE__,
                __LINE__,
                $db->error()
            );
        } // If the user shouldn't have moderator access (and he/she already has it)
        elseif (!in_array($cur_forum['id'], $moderator_in) && in_array($id, $cur_moderators)) {
            unset($cur_moderators[$username]);
            $cur_moderators = ($cur_moderators) ? '\'' . $db->escape(serialize($cur_moderators)) . '\'' : 'NULL';

            $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=' . $cur_moderators . ' WHERE id=' . $cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
        }
    }

    redirect('profile.php?section=admin&amp;id=' . $id, $lang_profile['Update forums redirect']);
} elseif (isset($_POST['ban'])) {
    if ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_ban_users'])) {
        message($lang_common['No permission']);
    }

    redirect('admin_bans.php?add_ban=' . $id, $lang_profile['Ban redirect']);
} elseif (isset($_POST['delete_user']) || isset($_POST['delete_user_comply'])) {
    if ($pun_user['g_id'] > PUN_ADMIN) {
        message($lang_common['No permission']);
    }

    //confirm_referrer('profile.php');

    // Get the username and group of the user we are deleting
    $result = $db->query('SELECT group_id, username FROM ' . $db->prefix .
        'users WHERE id=' . $id) or error(
            'Unable to fetch user info',
            __FILE__,
        __LINE__,
            $db->error()
        );
    list($group_id, $username) = $db->fetch_row($result);

    if ($group_id == PUN_ADMIN) {
        message('Administrators cannot be deleted. In order to delete this user, you must first move him/her to a different user group.');
    }

    if (isset($_POST['delete_user_comply'])) {
        // If the user is a moderator or an administrator, we remove him/her from the moderator list in all forums as well
        if ($group_id < PUN_GUEST) {
            $result = $db->query('SELECT id, moderators FROM ' . $db->prefix . 'forums') or
                error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

            while ($cur_forum = $db->fetch_assoc($result)) {
                $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) :
                    array();

                if (in_array($id, $cur_moderators)) {
                    unset($cur_moderators[$username]);
                    $cur_moderators = ($cur_moderators) ? '\'' . $db->escape(serialize($cur_moderators)) . '\'' : 'NULL';

                    $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=' . $cur_moderators . ' WHERE id=' . $cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
                }
            }
        }

        // Delete any subscriptions
        $db->query('DELETE FROM ' . $db->prefix . 'subscriptions WHERE user_id=' . $id) or
            error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());

        // Remove him/her from the online list (if they happen to be logged in)
        $db->query('DELETE FROM ' . $db->prefix . 'online WHERE user_id=' . $id) or
            error('Unable to remove user from online list', __FILE__, __LINE__, $db->error());

        // Should we delete all posts made by this user?
        if (isset($_POST['delete_posts'])) {
            include PUN_ROOT . 'include/search_idx.php';
            @set_time_limit(0);

            // Find all posts made by this user
            $result = $db->query('SELECT p.id, p.topic_id, t.forum_id FROM ' . $db->prefix .
                'posts AS p INNER JOIN ' . $db->prefix .
                'topics AS t ON t.id=p.topic_id INNER JOIN ' . $db->prefix .
                'forums AS f ON f.id=t.forum_id WHERE p.poster_id=' . $id) or error(
                    'Unable to fetch posts',
                __FILE__,
                    __LINE__,
                    $db->error()
                );
            if ($db->num_rows($result)) {
                while ($cur_post = $db->fetch_assoc($result)) {
                    // Determine whether this post is the "topic post" or not
                    $result2 = $db->query('SELECT id FROM ' . $db->prefix . 'posts WHERE topic_id=' .
                        $cur_post['topic_id'] . ' ORDER BY posted LIMIT 1') or error(
                            'Unable to fetch post info',
                        __FILE__,
                            __LINE__,
                            $db->error()
                        );

                    if ($db->result($result2) == $cur_post['id']) {
                        delete_topic($cur_post['topic_id']);
                    } else {
                        delete_post($cur_post['id'], $cur_post['topic_id']);
                    }

                    update_forum($cur_post['forum_id']);
                }
            }
        } else {
            // Set all his/her posts to guest
            $db->query('UPDATE ' . $db->prefix . 'posts SET poster_id=1 WHERE poster_id=' .
                $id) or error('Unable to update posts', __FILE__, __LINE__, $db->error());

            // Set all his/her attachments to guest
            $db->query('UPDATE ' . $db->prefix .
                'attachments SET poster_id=1 WHERE poster_id=' . $id) or error(
                    'Unable to update attachments',
                __FILE__,
                    __LINE__,
                    $db->error()
                );
        }


        // Delete the user
        $db->query('DELETE FROM ' . $db->prefix . 'users WHERE id=' . $id) or error(
            'Unable to delete user',
            __FILE__,
            __LINE__,
            $db->error()
        );

        // PMS MOD BEGIN
        include PUN_ROOT . 'include/pms/profile_delete.php';
        // PMS MOD END

        // Delete user avatar
        if (file_exists($pun_config['o_avatars_dir'] . '/' . $id . '.gif')) {
            unlink($pun_config['o_avatars_dir'] . '/' . $id . '.gif');
        }
        if (file_exists($pun_config['o_avatars_dir'] . '/' . $id . '.jpg')) {
            unlink($pun_config['o_avatars_dir'] . '/' . $id . '.jpg');
        }
        if (file_exists($pun_config['o_avatars_dir'] . '/' . $id . '.png')) {
            unlink($pun_config['o_avatars_dir'] . '/' . $id . '.png');
        }

        redirect('index.php', $lang_profile['User delete redirect']);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
    require_once PUN_ROOT . 'header.php';


    echo '<div class="blockform">
<h2><span>' . $lang_profile['Confirm delete user'] . '</span></h2>
<div class="box">
<form id="confirm_del_user" method="post" action="profile.php?id=' . $id . '">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Confirm delete legend'] . '</legend>
<div class="infldset">
<p>' . $lang_profile['Confirmation info'] . ' ' . pun_htmlspecialchars($username) .
        '.</p>
<div class="rbox">
<label><input type="checkbox" name="delete_posts" value="1" checked="checked" />' .
        $lang_profile['Delete posts'] . '<br /></label>
</div>
<p class="warntext"><strong>' . $lang_profile['Delete warning'] . '</strong></p>
</div>
</fieldset>
</div>
<p><input type="submit" name="delete_user_comply" value="' . $lang_profile['Delete'] .
        '" /><a href="javascript:history.go(-1)">' . $lang_common['Go back'] . '</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} elseif (isset($_POST['form_sent'])) {
    // Fetch the user group of the user we are editing
    $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        message($lang_common['Bad request']);
    }

    $group_id = $db->result($result);

    if ($pun_user['id'] != $id && ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] ==
        PUN_MOD && !$pun_config['p_mod_edit_users']) || ($pun_user['g_id'] == PUN_MOD &&
        $group_id < PUN_GUEST))
    ) {
        message($lang_common['No permission']);
    }

    /*
    if ($pun_user['g_id'] < PUN_GUEST){
    confirm_referrer('profile.php');
    }
    */

    // Extract allowed elements from $_POST['form']
    function extract_elements($allowed_elements)
    {
        $form = array();

        foreach ($_POST['form'] as $key => $value) {
            if (in_array($key, $allowed_elements)) {
                $form[$key] = $value;
            }
        }

        return $form;
    }

    $username_updated = false;

    // Validate input depending on section
    switch ($_GET['section']) {
        case 'essentials':
            $form = extract_elements(array('timezone', 'language'));

            if ($pun_user['g_id'] < PUN_GUEST) {
                $form['admin_note'] = trim($_POST['admin_note']);

                // Are we allowed to change usernames?
                if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && $pun_config['p_mod_rename_users'] == 1)) {
                    $form['username'] = trim($_POST['req_username']);
                    $old_username = trim($_POST['old_username']);

                    if (mb_strlen($form['username']) < 2) {
                        message($lang_prof_reg['Username too short']);
                    } elseif (mb_strlen($form['username']) > 25) { // This usually doesn't happen since the form element only accepts 25 characters
                        message($lang_common['Bad request']);
                    } elseif (!strcasecmp($form['username'], 'Guest') || !strcasecmp($form['username'], $lang_common['Guest'])) {
                        message($lang_prof_reg['Username guest']);
                    } elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $form['username'])) {
                        message($lang_prof_reg['Username IP']);
                    } elseif (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $form['username'])) {
                        message($lang_prof_reg['Username BBCode']);
                    }

                    // Check that the username is not already registered
                    $result = $db->query('SELECT 1 FROM `' . $db->prefix .
                        'users` WHERE `username`="' . $db->escape($form['username']) . '" AND `id`<>' .
                        $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

                    if ($db->num_rows($result)) {
                        message($lang_profile['Dupe username']);
                    }

                    if ($form['username'] != $old_username) {
                        $username_updated = true;
                    }
                }

                // We only allow administrators to update the post count
                if ($pun_user['g_id'] == PUN_ADMIN) {
                    $form['num_posts'] = abs($_POST['num_posts']);
                    $form['num_files'] = abs($_POST['num_files']);
                    $form['file_bonus'] = abs($_POST['file_bonus']);
                }
            }

            if (!$pun_config['o_regs_verify'] || $pun_user['g_id'] < PUN_GUEST) {
                include_once PUN_ROOT . 'include/email.php';

                // Validate the email-address
                $form['email'] = strtolower(trim($_POST['req_email']));
                if (!is_valid_email($form['email'])) {
                    message($lang_common['Invalid e-mail']);
                }
            }

            // Make sure we got a valid language string
            if (isset($form['language'])) {
                $form['language'] = preg_replace('#[\.\\\/]#', '', $form['language']);
                if (!file_exists(PUN_ROOT . 'lang/' . $form['language'] . '/common.php')) {
                    message($lang_common['Bad request']);
                }
            }
            break;


        case 'personal':
            $_POST['form']['birthday'] = intval($_POST['day']) . '.' . intval($_POST['month']) .
                '.' . intval($_POST['year']);
            if ($_POST['form']['birthday'] == '0.0.0') {
                $_POST['form']['birthday'] = '';
            }

            $form = extract_elements(array('realname', 'url', 'location', 'sex', 'birthday'));

            if ($pun_user['g_id'] == PUN_ADMIN) {
                $form['title'] = trim($_POST['title']);
            } elseif ($pun_user['g_set_title'] == 1) {
                $form['title'] = trim($_POST['title']);

                if ($form['title']) {
                    // A list of words that the title may not contain
                    // If the language is English, there will be some duplicates, but it's not the end of the world
                    $forbidden = array('Member', 'Moderator', 'Administrator', 'Banned', 'Guest', $lang_common['Member'],
                            $lang_common['Moderator'], $lang_common['Administrator'], $lang_common['Banned'],
                            $lang_common['Guest']);

                    if (in_array($form['title'], $forbidden)) {
                        message($lang_profile['Forbidden title']);
                    }
                }
            }

            // Add http:// if the URL doesn't contain it already
            if ($form['url'] && strpos(strtolower($form['url']), 'http://') !== 0) {
                $form['url'] = 'http://' . $form['url'];
            }
            break;


        case 'messaging':
            $form = extract_elements(array('jabber', 'icq', 'msn', 'aim', 'yahoo'));

            // If the ICQ UIN contains anything other than digits it's invalid
            if ($form['icq'] && !intval($form['icq'])) {
                message($lang_prof_reg['Bad ICQ']);
            }
            break;


        case 'personality':
            $form = extract_elements(array('use_avatar'));

            // Clean up signature from POST
            $form['signature'] = pun_linebreaks(trim($_POST['signature']));

            // Validate signature
            if (mb_strlen($form['signature']) > $pun_config['p_sig_length']) {
                message($lang_prof_reg['Sig too long'] . ' ' . $pun_config['p_sig_length'] . ' ' . $lang_prof_reg['characters'] . '.');
            } elseif (substr_count($form['signature'], "\n") > ($pun_config['p_sig_lines'] - 1)) {
                message($lang_prof_reg['Sig too many lines'] . ' ' . $pun_config['p_sig_lines'] . ' ' . $lang_prof_reg['lines'] . '.');
            } elseif ($form['signature'] && !$pun_config['p_sig_all_caps'] && mb_strtoupper($form['signature']) == $form['signature'] && $pun_user['g_id'] > PUN_MOD) {
                $form['signature'] = ucwords(mb_strtolower($form['signature']));
            }

            // Validate BBCode syntax
            if ($pun_config['p_sig_bbcode'] == 1 && strpos($form['signature'], '[') !== false && strpos($form['signature'], ']') !== false) {
                include_once PUN_ROOT . 'include/parser.php';
                $form['signature'] = preparse_bbcode($form['signature'], $foo, true);
            }

            if ($form['use_avatar'] != 1) {
                $form['use_avatar'] = 0;
            }
            break;


        case 'display':
            // REAL MARK TOPIC AS READ MOD BEGIN
            //
            // ORIGINAL
            // $form = extract_elements(array('disp_topics', 'disp_posts', 'show_smilies', 'show_img', 'show_img_sig', 'show_avatars', 'show_sig', 'style'));
            $form = extract_elements(array('disp_topics', 'disp_posts', 'show_smilies',
                'show_img', 'show_img_sig', 'show_avatars', 'show_sig', 'style', 'mark_after',
                'show_bbpanel_qpost'));
            // REAL MARK TOPIC AS READ MOD END
            if (!$form['disp_topics']) {
                $form['disp_topics'] = null;
            }
            if ($form['disp_topics'] && intval($form['disp_topics']) < 3) {
                $form['disp_topics'] = 3;
            }
            if ($form['disp_topics'] && intval($form['disp_topics']) > 75) {
                $form['disp_topics'] = 75;
            }
            if (!$form['disp_posts']) {
                $form['disp_posts'] = null;
            }
            if ($form['disp_posts'] && intval($form['disp_posts']) < 3) {
                $form['disp_posts'] = 3;
            }
            if ($form['disp_posts'] && intval($form['disp_posts']) > 75) {
                $form['disp_posts'] = 75;
            }

            // REAL MARK TOPIC AS READ MOD BEGIN
            if ((int)@$form['mark_after'] > 100) {
                $form['mark_after'] = 1296000;
            } else {
                $form['mark_after'] *= 86400;
            }
            // REAL MARK TOPIC AS READ MOD END

            if ($form['show_bbpanel_qpost'] != 1) {
                $form['show_bbpanel_qpost'] = 0;
            }
            if ($form['show_smilies'] != 1) {
                $form['show_smilies'] = 0;
            }
            if ($form['show_img'] != 1) {
                $form['show_img'] = 0;
            }
            if ($form['show_img_sig'] != 1) {
                $form['show_img_sig'] = 0;
            }
            if ($form['show_avatars'] != 1) {
                $form['show_avatars'] = 0;
            }
            if ($form['show_sig'] != 1) {
                $form['show_sig'] = 0;
            }
            break;


        case 'privacy':
            $form = extract_elements(array('email_setting', 'save_pass', 'notify_with_post'));

            $form['email_setting'] = intval($form['email_setting']);
            if ($form['email_setting'] < 0 && $form['email_setting'] > 2) {
                $form['email_setting'] = 1;
            }

            if ($form['save_pass'] != 1) {
                $form['save_pass'] = 0;
            }

            if ($form['notify_with_post'] != 1) {
                $form['notify_with_post'] = 0;
            }

            // If the save_pass setting has changed, we need to set a new cookie with the appropriate expire date
            if ($pun_user['id'] == $id && $form['save_pass'] != $pun_user['save_pass']) {
                $result = $db->query('SELECT `password` FROM `' . $db->prefix .
                    'users` WHERE id=' . $id) or error(
                        'Unable to fetch user password hash',
                    __FILE__,
                        __LINE__,
                        $db->error()
                    );
                pun_setcookie($id, $db->result($result), ($form['save_pass'] == 1) ? $_SERVER['REQUEST_TIME'] +
                    31536000 : 0);
            }
            break;


        default:
            message($lang_common['Bad request']);
            break;
    }


    // Singlequotes around non-empty values and NULL for empty values
    $temp = array();
    foreach ($form as $key => $input) {
        $value = ($input !== null) ? '\'' . $db->escape($input) . '\'' : 'NULL';
        $temp[] = $key . '=' . $value;
    }

    if (!$temp) {
        message($lang_common['Bad request']);
    }


    $db->query('UPDATE `' . $db->prefix . 'users` SET ' . implode(',', $temp) . ' WHERE `id`=' . $id) or error('Unable to update profile', __FILE__, __LINE__, $db->error());

    // If we changed the username we have to update some stuff
    if ($username_updated) {
        $db->query('UPDATE ' . $db->prefix . 'posts SET poster=\'' . $db->escape($form['username']) . '\' WHERE poster_id=' . $id) or error('Unable to update posts', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE ' . $db->prefix . 'topics SET poster=\'' . $db->escape($form['username']) . '\' WHERE poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update topics', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE ' . $db->prefix . 'topics SET last_poster=\'' . $db->escape($form['username']) . '\' WHERE last_poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update topics', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE ' . $db->prefix . 'forums SET last_poster=\'' . $db->escape($form['username']) . '\' WHERE last_poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update forums', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE ' . $db->prefix . 'online SET ident=\'' . $db->escape($form['username']) . '\' WHERE ident=\'' . $db->escape($old_username) . '\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());

        // If the user is a moderator or an administrator we have to update the moderator lists
        $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
        $group_id = $db->result($result);

        if ($group_id < PUN_GUEST) {
            $result = $db->query('SELECT `id`, `moderators` FROM `' . $db->prefix . 'forums`') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

            while ($cur_forum = $db->fetch_assoc($result)) {
                $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();

                if (in_array($id, $cur_moderators)) {
                    unset($cur_moderators[$old_username]);
                    $cur_moderators[$form['username']] = $id;
                    ksort($cur_moderators);

                    $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=\'' . $db->escape(serialize($cur_moderators)) . '\' WHERE id=' . $cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
                }
            }
        }
    }

    redirect('profile.php?section=' . $_GET['section'] . '&amp;id=' . $id, $lang_profile['Profile redirect']);
}

// REAL MARK TOPIC AS READ MOD BEGIN
$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.sex, u.birthday, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.use_avatar, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.save_pass, u.notify_with_post, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.language, u.style, u.num_posts, u.num_files, u.file_bonus, u.last_post, u.registered, u.registration_ip, u.admin_note, g.g_id, g.g_user_title, u.mark_after, u.show_bbpanel_qpost FROM `' . $db->prefix . 'users` AS u LEFT JOIN `' . $db->prefix . 'groups` AS g ON g.g_id=u.group_id WHERE u.id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
// REAL MARK TOPIC AS READ MOD END


if (!$db->num_rows($result)) {
    message($lang_common['Bad request']);
}

$user = $db->fetch_assoc($result);

$last_post = format_time($user['last_post']);

if ($user['signature']) {
    include_once PUN_ROOT . 'include/parser.php';
    $parsed_signature = parse_signature($user['signature']);
}


//if($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST)
//{
$q = $db->fetch_row($db->query(
    '
    SELECT COUNT(1), (SELECT COUNT(1) FROM `' . $db->prefix . 'karma` WHERE `vote` = "-1" AND `to` = ' . $id . ') FROM `' . $db->prefix . 'karma` WHERE `vote` = "1" AND `to` = ' . $id
));

$karma['plus'] = intval($q[0]);
$karma['minus'] = intval($q[1]);
$karma['karma'] = $karma['plus'] - $karma['minus'];
unset($q);
//}

$karma = $karma['karma'] . ' (+' . $karma['plus'] . '/-' . $karma['minus'] . ') - <a href="karma.php?id=' . $id . '">' . $lang_common['Show karma'] . '</a>';


// View or edit?
if (isset($_GET['preview']) or ($pun_user['id'] != $id && ($pun_user['g_id'] >
    PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_edit_users']) ||
    ($pun_user['g_id'] == PUN_MOD && $user['g_id'] < PUN_GUEST)))
) {
    if (!$user['email_setting'] && !$pun_user['is_guest']) {
        $email_field = '<a href="mailto:' . rawurlencode($user['email']) . '">' . pun_htmlspecialchars($user['email']) . '</a>';
    } elseif ($user['email_setting'] == 1 && !$pun_user['is_guest']) {
        $email_field = '<a href="misc.php?email=' . $id . '">' . $lang_common['Send e-mail'] . '</a>';
    } else {
        $email_field = $lang_profile['Private'];
    }

    $user_title_field = get_title($user);

    if ($user['url']) {
        $user['url'] = pun_htmlspecialchars($user['url']);

        if ($pun_config['o_censoring'] == 1) {
            $user['url'] = censor_words($user['url']);
        }

        $url = '<a href="' . $user['url'] . '">' . $user['url'] . '</a>';
    } else {
        $url = $lang_profile['Unknown'];
    }

    if ($pun_config['o_avatars'] == 1) {
        if ($user['use_avatar'] == 1) {
            if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif')) {
                $avatar_field = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif" ' . $img_size[3] . ' alt="" />';
            } elseif ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg')) {
                $avatar_field = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg" ' . $img_size[3] . ' alt="" />';
            } elseif ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png')) {
                $avatar_field = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png" ' . $img_size[3] . ' alt="" />';
            } else {
                $avatar_field = $lang_profile['No avatar'];
            }
        } else {
            $avatar_field = $lang_profile['No avatar'];
        }
    }

    $posts_field = $files_field = '';


    if ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
        $posts_field = $user['num_posts'];
        $files_field = $user['num_files'];
    }
    if ($pun_user['g_search'] == 1) {
        $posts_field .= (($posts_field) ? ' - <a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] . '</a>' : '');
        $files_field .= (($files_field) ? ' - <a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] . '</a>' : '');
    }

    if ($user['sex'] == 1) {
        $user['sex'] = $lang_profile['m'];
    } else {
        $user['sex'] = $lang_profile['w'];
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
    define('PUN_ALLOW_INDEX', 1);
    require_once PUN_ROOT . 'header.php';


    echo '<div id="viewprofile" class="block">
<h2><span>' . $lang_common['Profile'] . '</span></h2>
<div class="box">
<div class="fakeform">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Section personal'] . '</legend>
<div class="infldset">
<dl>
<dt>' . $lang_common['Username'] . ': </dt>
<dd>' . pun_htmlspecialchars($user['username']) . ' (' . $user['sex'] . ')</dd>';

    if ($user['birthday']) {
        echo '<dt>' . $lang_profile['birthday'] . ': </dt><dd>' . $user['birthday'] . '</dd>';
    }

    echo '<dt>' . $lang_common['Title'] . ': </dt><dd>';
    if ($pun_config['o_censoring'] == 1) {
        echo censor_words($user_title_field);
    } else {
        echo $user_title_field;
    }
    echo '</dd><dt>' . $lang_profile['Realname'] . ': </dt><dd>';
    echo ($user['realname']) ? pun_htmlspecialchars(($pun_config['o_censoring'] == 1) ? censor_words($user['realname']) : $user['realname']) : $lang_profile['Unknown'];
    echo '</dd><dt>' . $lang_profile['Location'] . ': </dt><dd>';
    echo ($user['location']) ? pun_htmlspecialchars(($pun_config['o_censoring'] == 1) ? censor_words($user['location']) : $user['location']) : $lang_profile['Unknown'];
    echo '</dd>
<dt>' . $lang_profile['Website'] . ': </dt>
<dd>' . $url . ' </dd>
<dt>' . $lang_common['E-mail'] . ': </dt>
<dd>' . $email_field . '</dd>';

    // PMS MOD BEGIN
    include PUN_ROOT . 'include/pms/profile_send.php';
    // PMS MOD END

    echo '</dl>
<div class="clearer"></div>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Section messaging'] . '</legend>
<div class="infldset">
<dl>
<dt>' . $lang_profile['Jabber'] . ': </dt><dd>';
    echo ($user['jabber']) ? pun_htmlspecialchars($user['jabber']) : $lang_profile['Unknown'];
    echo '</dd><dt>' . $lang_profile['ICQ'] . ': </dt><dd>';
    echo ($user['icq']) ? $user['icq'] : $lang_profile['Unknown'];
    echo '</dd><dt>' . $lang_profile['MSN'] . ': </dt><dd>';
    echo ($user['msn']) ? pun_htmlspecialchars(($pun_config['o_censoring'] == 1) ? censor_words($user['msn']) : $user['msn']) : $lang_profile['Unknown'];
    echo '</dd><dt>' . $lang_profile['AOL IM'] . ': </dt><dd>';
    echo ($user['aim']) ? pun_htmlspecialchars(($pun_config['o_censoring'] == 1) ? censor_words($user['aim']) : $user['aim']) : $lang_profile['Unknown'];
    echo '</dd><dt>' . $lang_profile['Yahoo'] . ': </dt><dd>';
    echo ($user['yahoo']) ? pun_htmlspecialchars(($pun_config['o_censoring'] == 1) ? censor_words($user['yahoo']) : $user['yahoo']) : $lang_profile['Unknown'];
    echo '</dd>
</dl>
<div class="clearer"></div>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Section personality'] . '</legend>
<div class="infldset">
<dl>';
    if ($pun_config['o_avatars'] == 1) {
        echo '<dt>' . $lang_profile['Avatar'] . ': </dt><dd>' . $avatar_field . '</dd>';
    }
    echo '<dt>' . $lang_profile['Signature'] . ': </dt><dd><div>';
    echo isset($parsed_signature) ? $parsed_signature : $lang_profile['No sig'];
    echo '</div></dd>
</dl>
<div class="clearer"></div>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['User activity'] . '</legend>
<div class="infldset">
<dl>
<dt>' . $lang_common['Posts'] . ': </dt><dd>' . $posts_field . '</dd>
<dt>' . $lang_common['Files'] . ': </dt><dd>' . $files_field . '</dd>';

    if ($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
        echo '<dt>' . $lang_common['Karma'] . ': </dt><dd>' . $karma . '</dd>';
    }

    echo '<dt>' . $lang_common['Last post'] . ': </dt><dd>' . $last_post . '</dd>
<dt>' . $lang_common['Registered'] . ': </dt><dd>' . format_time($user['registered'], true) . '</dd>
</dl>
<div class="clearer"></div>
</div>
</fieldset>
</div>
</div>
</div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} else {
    if (!$_GET['section'] || $_GET['section'] == 'essentials') {
        if ($pun_user['g_id'] < PUN_GUEST) {
            if ($pun_user['g_id'] == PUN_ADMIN || $pun_config['p_mod_rename_users'] == 1) {
                $username_field = '<input type="hidden" name="old_username" value="' .
                    pun_htmlspecialchars($user['username']) . '" /><label><strong>' . $lang_common['Username'] .
                    '</strong><br /><input type="text" name="req_username" value="' .
                    pun_htmlspecialchars($user['username']) .
                    '" size="25" maxlength="25" /><br /></label>';
            } else {
                $username_field = '<p>' . $lang_common['Username'] . ': ' . pun_htmlspecialchars($user['username']) . '</p>';
            }

            $email_field = '<label><strong>' . $lang_common['E-mail'] .
                '</strong><br /><input type="text" name="req_email" value="' . pun_htmlspecialchars($user['email']) .
                '" size="40" maxlength="50" /><br /></label>';

            if ($pun_user['g_id'] == PUN_ADMIN) {
                $email_field .= '<p><a target="_blank" href="http://www.stopforumspam.com/search?q=' . rawurlencode($user['email']) . '">' . $lang_common['Find email in stop forum spam'] . '</a></p>';
            }

            $email_field .= '<p><a href="misc.php?email=' . $id . '">' . $lang_common['Send e-mail'] . '</a></p>';

            // PMS MOD BEGIN
            include PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
            $email_field .= '<p><a href="message_send.php?id=' . $id . '">' . $lang_pms['Quick message'] . '</a></p>';
        // PMS MOD END
        } else {
            $username_field = '<p>' . $lang_common['Username'] . ': ' . pun_htmlspecialchars($user['username']) .
                '</p>';

            if ($pun_config['o_regs_verify'] == 1) {
                $email_field = '<p>' . $lang_common['E-mail'] . ': ' . pun_htmlspecialchars($user['email']) .
                    ' - <a href="profile.php?action=change_email&amp;id=' . $id . '">' . $lang_profile['Change e-mail'] .
                    '</a></p>';
            } else {
                $email_field = '<label><strong>' . $lang_common['E-mail'] .
                    '</strong><br /><input type="text" name="req_email" value="' . pun_htmlspecialchars($user['email']) .
                    '" size="40" maxlength="50" /><br /></label>';
            }
        }

        if ($pun_user['g_id'] == PUN_ADMIN) {
            $posts_field = '<label>' . $lang_common['Posts'] .
                '<br /><input type="text" name="num_posts" value="' . $user['num_posts'] .
                '" size="8" maxlength="8" /><br /></label>
<p><a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] .
                '</a></p>';
            $files_field = '<label>' . $lang_common['Files'] .
                '<br /><input type="text" name="num_files"  value="' . $user['num_files'] .
                '" size="8" maxlength="8" /><br /></label>
<label>' . $lang_common['Bonus'] .
                '<br /><input type="text" name="file_bonus" value="' . $user['file_bonus'] .
                '" size="8" maxlength="8" /><br /></label>
<p><a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] .
                '</a></p>';
        } elseif ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
            $posts_field = '<p>' . $lang_common['Posts'] . ': ' . $user['num_posts'] .
                ' - <a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] .
                '</a></p>';
            $files_field = '<p>' . $lang_common['Files'] . ': ' . $user['num_files'] .
                ' - <a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] .
                '</a></p>';
        } else {
            $posts_field = '<p><a href="search.php?action=show_user&amp;user_id=' . $id .
                '">' . $lang_profile['Show posts'] . '</a></p>';
            $files_field = '<p><a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] .
                '</a></p>';
        }

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        $required_fields = array('req_username' => $lang_common['Username'], 'req_email' =>
        $lang_common['E-mail']);
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('essentials');


        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section essentials'] .
            '</span></h2>
<div class="box">
<form id="profile1" method="post" action="profile.php?section=essentials&amp;id=' .
            $id . '" onsubmit="return process_form(this)">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Username and pass legend'] . '</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />' . $username_field;
        if ($pun_user['id'] == $id || $pun_user['g_id'] == PUN_ADMIN || ($user['g_id'] >
            PUN_MOD && $pun_config['p_mod_change_passwords'] == 1)
        ) {
            echo '<p><a href="profile.php?action=change_pass&amp;id=' . $id . '">' . $lang_profile['Change pass'] . '</a></p>';
        }
        echo '</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_prof_reg['E-mail legend'] . '</legend>
<div class="infldset">' . $email_field . '</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_prof_reg['Localisation legend'] . '</legend>
<div class="infldset">
<label>' . $lang_prof_reg['Timezone'] . ': ' . $lang_prof_reg['Timezone info'] .
            '<br />'; ?>
    <select name="form[timezone]">
    <option value="-12"<?php if ($user['timezone'] == -12) {
                echo ' selected="selected"';
            } ?>>-12
    </option>
    <option value="-11"<?php if ($user['timezone'] == -11) {
                echo ' selected="selected"';
            } ?>>-11
    </option>
    <option value="-10"<?php if ($user['timezone'] == -10) {
                echo ' selected="selected"';
            } ?>>-10
    </option>
    <option value="-9.5"<?php if ($user['timezone'] == -9.5) {
                echo ' selected="selected"';
            } ?>>-09.5
    </option>
    <option value="-9"<?php if ($user['timezone'] == -9) {
                echo ' selected="selected"';
            } ?>>-09
    </option>
    <option value="-8.5"<?php if ($user['timezone'] == -8.5) {
                echo ' selected="selected"';
            } ?>>-08.5
    </option>
    <option value="-8"<?php if ($user['timezone'] == -8) {
                echo ' selected="selected"';
            } ?>>-08 PST
    </option>
    <option value="-7"<?php if ($user['timezone'] == -7) {
                echo ' selected="selected"';
            } ?>>-07 MST
    </option>
    <option value="-6"<?php if ($user['timezone'] == -6) {
                echo ' selected="selected"';
            } ?>>-06 CST
    </option>
    <option value="-5"<?php if ($user['timezone'] == -5) {
                echo ' selected="selected"';
            } ?>>-05 EST
    </option>
    <option value="-4"<?php if ($user['timezone'] == -4) {
                echo ' selected="selected"';
            } ?>>-04 AST
    </option>
    <option value="-3.5"<?php if ($user['timezone'] == -3.5) {
                echo ' selected="selected"';
            } ?>>-03.5
    </option>
    <option value="-3"<?php if ($user['timezone'] == -3) {
                echo ' selected="selected"';
            } ?>>-03 ADT
    </option>
    <option value="-2"<?php if ($user['timezone'] == -2) {
                echo ' selected="selected"';
            } ?>>-02
    </option>
    <option value="-1"<?php if ($user['timezone'] == -1) {
                echo ' selected="selected"';
            } ?>>-01
    </option>
    <option value="0"<?php if ($user['timezone'] == 0) {
                echo ' selected="selected"';
            } ?>>00 GMT
    </option>
    <option value="1"<?php if ($user['timezone'] == 1) {
                echo ' selected="selected"';
            } ?>>+01 CET
    </option>
    <option value="2"<?php if ($user['timezone'] == 2) {
                echo ' selected="selected"';
            } ?>>+02
    </option>
    <option value="3"<?php if ($user['timezone'] == 3) {
                echo ' selected="selected"';
            } ?>>+03
    </option>
    <option value="3.5"<?php if ($user['timezone'] == 3.5) {
                echo ' selected="selected"';
            } ?>>+03.5
    </option>
    <option value="4"<?php if ($user['timezone'] == 4) {
                echo ' selected="selected"';
            } ?>>+04
    </option>
    <option value="4.5"<?php if ($user['timezone'] == 4.5) {
                echo ' selected="selected"';
            } ?>>+04.5
    </option>
    <option value="5"<?php if ($user['timezone'] == 5) {
                echo ' selected="selected"';
            } ?>>+05
    </option>
    <option value="5.5"<?php if ($user['timezone'] == 5.5) {
                echo ' selected="selected"';
            } ?>>+05.5
    </option>
    <option value="6"<?php if ($user['timezone'] == 6) {
                echo ' selected="selected"';
            } ?>>+06
    </option>
    <option value="6.5"<?php if ($user['timezone'] == 6.5) {
                echo ' selected="selected"';
            } ?>>+06.5
    </option>
    <option value="7"<?php if ($user['timezone'] == 7) {
                echo ' selected="selected"';
            } ?>>+07
    </option>
    <option value="8"<?php if ($user['timezone'] == 8) {
                echo ' selected="selected"';
            } ?>>+08
    </option>
    <option value="9"<?php if ($user['timezone'] == 9) {
                echo ' selected="selected"';
            } ?>>+09
    </option>
    <option value="9.5"<?php if ($user['timezone'] == 9.5) {
                echo ' selected="selected"';
            } ?>>+09.5
    </option>
    <option value="10"<?php if ($user['timezone'] == 10) {
                echo ' selected="selected"';
            } ?>>+10
    </option>
    <option value="10.5"<?php if ($user['timezone'] == 10.5) {
                echo ' selected="selected"';
            } ?>>+10.5
    </option>
    <option value="11"<?php if ($user['timezone'] == 11) {
                echo ' selected="selected"';
            } ?>>+11
    </option>
    <option value="11.5"<?php if ($user['timezone'] == 11.5) {
                echo ' selected="selected"';
            } ?>>+11.5
    </option>
    <option value="12"<?php if ($user['timezone'] == 12) {
                echo ' selected="selected"';
            } ?>>+12
    </option>
    <option value="13"<?php if ($user['timezone'] == 13) {
                echo ' selected="selected"';
            } ?>>+13
    </option>
    <option value="14"<?php if ($user['timezone'] == 14) {
                echo ' selected="selected"';
            } ?>>+14
    </option>
    </select>
    <br/></label>
    <?php
        $languages = array();
        $d = opendir(PUN_ROOT . 'lang');
        while (false !== ($entry = readdir($d))) {
            if ($entry[0] != '.' && is_dir(PUN_ROOT . 'lang/' . $entry) && file_exists(PUN_ROOT . 'lang/' . $entry . '/common.php')) {
                $languages[] = $entry;
            }
        }
        closedir($d);

        // Only display the language selection box if there's more than one language available
        if (count($languages) > 1) {
            natsort($languages);
            echo '<label>' . $lang_prof_reg['Language'] . ': ' . $lang_prof_reg['Language info'] . '<br /><select name="form[language]">';


            foreach ($languages as $temp) {
                if ($user['language'] == $temp) {
                    echo '<option value="' . $temp . '" selected="selected">' . $temp . '</option>';
                } else {
                    echo '<option value="' . $temp . '">' . $temp . '</option>';
                }
            }


            echo '</select><br /></label>';
        }


        echo '</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['User activity'] . '</legend>
<div class="infldset">
<p>' . $lang_common['Registered'] . ': ' . format_time($user['registered'], true);
        if ($pun_user['g_id'] < PUN_GUEST) {
            echo ' (<a href="moderate.php?get_host=' . pun_htmlspecialchars($user['registration_ip']) . '">' . pun_htmlspecialchars($user['registration_ip']) . '</a>)';
        }
        echo '</p><p>' . $lang_common['Last post'] . ': ' . $last_post . '</p>';
        if ($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
            echo '<p>' . $lang_common['Karma'] . ': ' . $karma . '</p>';
        }
        echo $posts_field . $files_field;
        if ($pun_user['g_id'] < PUN_GUEST) {
            echo '<label>' . $lang_profile['Admin note'] . '<br /><input id="admin_note" type="text" name="admin_note" value="' . pun_htmlspecialchars($user['admin_note']) . '" size="30" maxlength="30" /><br /></label>';
        }
        echo '</div></fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" />' . $lang_profile['Instructions'] . '</p>
</form>
</div>
</div>';
    } elseif ($_GET['section'] == 'personal') {
        if ($pun_user['g_set_title'] == 1) {
            $title_field = '<label>' . $lang_common['Title'] . ' (<em>' . $lang_profile['Leave blank'] . '</em>)<br /><input type="text" name="title" value="' . pun_htmlspecialchars($user['title']) . '" size="30" maxlength="50" /><br /></label>';
        }

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('personal');
        $birthday = explode('.', $user['birthday']);

        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section personal'] . '</span></h2>
<div class="box">
<form id="profile2" method="post" action="profile.php?section=personal&amp;id=' . $id . '">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Personal details legend'] . '</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<label>' . $lang_profile['sex'] . '<br/>
<select name="form[sex]">';


        if ($user['sex'] == 1) {
            echo '<option value="1">' . $lang_profile['m'] . '</option><option value="0">' . $lang_profile['w'] . '</option>';
        } else {
            echo '<option value="0">' . $lang_profile['w'] . '</option><option value="1">' . $lang_profile['m'] . '</option>';
        }

        echo '</select><br/></label>
<label>' . $lang_profile['birthday'] . '<br/>
<input type="text" value="' . $birthday[0] . '" name="day" title="' . $lang_profile['day'] .
            '" size="2" maxlength="2"/>.<input type="text" value="' . $birthday[1] .
            '" name="month" title="' . $lang_profile['month'] .
            '" size="2" maxlength="2"/>.<input type="text" value="' . $birthday[2] .
            '" name="year" title="' . $lang_profile['year'] .
            '" size="4" maxlength="4"/><br/></label>

<label>' . $lang_profile['Realname'] .
            '<br /><input type="text" name="form[realname]" value="' . pun_htmlspecialchars($user['realname']) .
            '" size="40" maxlength="40" /><br /></label>' . @$title_field . '<label>' . $lang_profile['Location'] .
            '<br /><input type="text" name="form[location]" value="' . pun_htmlspecialchars($user['location']) .
            '" size="30" maxlength="30" /><br /></label>
<label>' . $lang_profile['Website'] .
            '<br /><input type="text" name="form[url]" value="' . pun_htmlspecialchars($user['url']) .
            '" size="50" maxlength="80" /><br /></label>
</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" />' .
            $lang_profile['Instructions'] . '</p>
</form>
</div>
</div>';
    } elseif ($_GET['section'] == 'messaging') {
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('messaging');


        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section messaging'] .
            '</span></h2>
<div class="box">
<form id="profile3" method="post" action="profile.php?section=messaging&amp;id=' .
            $id . '">
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Contact details legend'] . '</legend>
<div class="infldset">
<input type="hidden" name="form_sent" value="1" />
<label>' . $lang_profile['Jabber'] .
            '<br /><input id="jabber" type="text" name="form[jabber]" value="' .
            pun_htmlspecialchars($user['jabber']) .
            '" size="40" maxlength="75" /><br /></label>
<label>' . $lang_profile['ICQ'] .
            '<br /><input id="icq" type="text" name="form[icq]" value="' . $user['icq'] .
            '" size="12" maxlength="12" /><br /></label>
<label>' . $lang_profile['MSN'] .
            '<br /><input id="msn" type="text" name="form[msn]" value="' .
            pun_htmlspecialchars($user['msn']) .
            '" size="40" maxlength="50" /><br /></label>
<label>' . $lang_profile['AOL IM'] .
            '<br /><input id="aim" type="text" name="form[aim]" value="' .
            pun_htmlspecialchars($user['aim']) .
            '" size="20" maxlength="30" /><br /></label>
<label>' . $lang_profile['Yahoo'] .
            '<br /><input id="yahoo" type="text" name="form[yahoo]" value="' .
            pun_htmlspecialchars($user['yahoo']) .
            '" size="20" maxlength="30" /><br /></label>
</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" />' . $lang_profile['Instructions'] . '</p>
</form>
</div>
</div>';
    } elseif ($_GET['section'] == 'personality') {
        $avatar_field = '<a href="profile.php?action=upload_avatar&amp;id=' . $id . '">' .
            $lang_profile['Change avatar'] . '</a>';
        if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif')) {
            $avatar_format = 'gif';
        } elseif ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg')) {
            $avatar_format = 'jpg';
        } elseif ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png')) {
            $avatar_format = 'png';
        } else {
            $avatar_field = '<a href="profile.php?action=upload_avatar&amp;id=' . $id . '">' . $lang_profile['Upload avatar'] . '</a>';
        }

        // Display the delete avatar link?
        if ($img_size) {
            $avatar_field .= ' <a href="profile.php?action=delete_avatar&amp;id=' . $id . '">' . $lang_profile['Delete avatar'] . '</a>';
        }

        if ($user['signature']) {
            $signature_preview = '<p>' . $lang_profile['Sig preview'] . '</p><div class="postsignature"><hr />' . $parsed_signature . ' </div>';
        } else {
            $signature_preview = '<p>' . $lang_profile['No sig'] . '</p>' . "\n";
        }

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('personality');


        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section personality'] . '</span></h2>
<div class="box">
<form id="profile4" method="post" action="profile.php?section=personality&amp;id=' . $id . '">
<div><input type="hidden" name="form_sent" value="1" /></div>';
        if ($pun_config['o_avatars']) {
            echo '<div class="inform">
<fieldset id="profileavatar">
<legend>' . $lang_profile['Avatar legend'] . '</legend>
<div class="infldset">';
            if (isset($avatar_format)) {
                echo '<img src="' . $pun_config['o_avatars_dir'] . '/' . $id . '.' . $avatar_format . '" ' . $img_size[3] . ' alt="" />';
            }
            echo '<p>' . $lang_profile['Avatar info'] . '</p>
<div class="rbox">
<label><input type="checkbox" name="form[use_avatar]" value="1"';
            if ($user['use_avatar']) {
                echo ' checked="checked"';
            }
            echo ' />' . $lang_profile['Use avatar'] . '<br /></label>
</div>
<p class="clearb">' . $avatar_field . '</p>
</div>
</fieldset>
</div>';
        }
        echo '<div class="inform">
<fieldset>
<legend>' . $lang_profile['Signature legend'] . '</legend>
<div class="infldset">
<p>' . $lang_profile['Signature info'] . '</p>
<div class="txtarea">
<label>' . $lang_profile['Sig max length'] . ': ' . $pun_config['p_sig_length'] . ' / ' . $lang_profile['Sig max lines'] . ': ' . $pun_config['p_sig_lines'] . '<br />
<textarea name="signature" rows="4" cols="65">' . pun_htmlspecialchars($user['signature']) . '</textarea><br /></label>
</div>
<ul class="bblinks">
<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;">' . $lang_common['BBCode'] . '</a>: ';
        echo ($pun_config['p_sig_bbcode']) ? $lang_common['on'] : $lang_common['off'];
        echo '</li><li><a href="help.php#img" onclick="window.open(this.href); return false;">' . $lang_common['img tag'] . '</a>: ';
        echo ($pun_config['p_sig_img_tag']) ? $lang_common['on'] : $lang_common['off'];
        echo '</li><li><a href="help.php#smilies" onclick="window.open(this.href); return false;">' . $lang_common['Smilies'] . '</a>: ';
        echo ($pun_config['o_smilies_sig']) ? $lang_common['on'] : $lang_common['off'];
        echo '</li></ul>' . $signature_preview . '</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" />' . $lang_profile['Instructions'] . '</p>
</form>
</div>
</div>';
    } elseif ($_GET['section'] == 'display') {
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('display');

        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section display'] . '</span></h2>
<div class="box">
<form id="profile5" method="post" action="profile.php?section=display&amp;id=' . $id . '">
<div><input type="hidden" name="form_sent" value="1" /></div>';

        $styles = array();
        $d = opendir(PUN_ROOT . 'style');
        while (false !== ($entry = readdir($d))) {
            $info = pathinfo($entry);
            if ($info['extension'] == 'css') {
                $styles[] = $info['filename'];
            }
        }
        closedir($d);

        // Only display the style selection box if there's more than one style available
        switch (count($styles)) {
            case 1:
                echo '<div><input type="hidden" name="form[style]" value="' . $styles[0] . '" /></div>';
                break;

            default:
                natsort($styles);

                echo '<div class="inform">
<fieldset>
<legend>' . $lang_profile['Style legend'] . '</legend>
<div class="infldset">
<label>' . $lang_profile['Style info'] . '<br />
<select name="form[style]">';

                foreach ($styles as $temp) {
                    if ($user['style'] == $temp) {
                        echo '<option value="' . $temp . '" selected="selected">' . str_replace('_', ' ', $temp) . '</option>';
                    } else {
                        echo '<option value="' . $temp . '">' . str_replace('_', ' ', $temp) . '</option>';
                    }
                }

                echo '</select><br/></label></div></fieldset></div>';
                break;
        }

        echo '<div class="inform">
<fieldset>
<legend>' . $lang_profile['Post display legend'] . '</legend>
<div class="infldset">
<p>' . $lang_profile['Post display info'] . '</p>
<div class="rbox">
<label><input type="checkbox" name="form[show_smilies]" value="1"';
        if ($user['show_smilies'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_profile['Show smilies'] . '<br /></label>
<label><input type="checkbox" name="form[show_sig]" value="1"';
        if ($user['show_sig'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_profile['Show sigs'] . '<br /></label>';
        if ($pun_config['o_avatars'] == 1) {
            echo '<label><input type="checkbox" name="form[show_avatars]" value="1"';
            if ($user['show_avatars'] == 1) {
                echo ' checked="checked"';
            }
            echo ' />' . $lang_profile['Show avatars'] . '<br /></label>';
        }
        echo '<label><input type="checkbox" name="form[show_img]" value="1"';
        if ($user['show_img'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_profile['Show images'] . '<br /></label>
<label><input type="checkbox" name="form[show_img_sig]" value="1"';
        if ($user['show_img_sig'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_profile['Show images sigs'] . '<br /></label>
</div>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Pagination legend'] . '</legend>
<div class="infldset">
<label class="conl">' . $lang_profile['Topics per page'] .
            '<br /><input type="text" name="form[disp_topics]" value="' . $user['disp_topics'] .
            '" size="6" maxlength="3" /><br /></label>
<label class="conl">' . $lang_profile['Posts per page'] .
            '<br /><input type="text" name="form[disp_posts]" value="' . $user['disp_posts'] .
            '" size="6" maxlength="3" /><br /></label>
<p class="clearb">' . $lang_profile['Paginate info'] . ' ' . $lang_profile['Leave blank'] .
            '</p>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_profile['Mark as read legend'] . '</legend>
<div class="infldset">
<label class="conl">' . $lang_profile['Mark as read after'] .
            '<br /><input type="text" name="form[mark_after]" value="' . ($user['mark_after'] /
            86400) . '" size="6" maxlength="3" /><br /></label>
<p class="clearb"></p>
</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_common['Submit'] .
            '" /> ' . $lang_profile['Instructions'] . '</p>
</form>
</div>
</div>';
    } elseif ($_GET['section'] == 'privacy') {
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('privacy');


        echo '<div class="blockform">
    <h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section privacy'] .
            '</span></h2>
    <div class="box">
    <form id="profile6" method="post" action="profile.php?section=privacy&amp;id=' .
            $id . '">
    <div class="inform">
    <fieldset>
    <legend>' . $lang_prof_reg['Privacy options legend'] . '</legend>
    <div class="infldset">
    <input type="hidden" name="form_sent" value="1" />
    <p>' . $lang_prof_reg['E-mail setting info'] . '</p>
    <div class="rbox">
    <label><input type="radio" name="form[email_setting]" value="0"';
        if (!$user['email_setting']) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_prof_reg['E-mail setting 1'] . '<br /></label>
    <label><input type="radio" name="form[email_setting]" value="1"';
        if ($user['email_setting'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_prof_reg['E-mail setting 2'] . '<br /></label>
    <label><input type="radio" name="form[email_setting]" value="2"';
        if ($user['email_setting'] == 2) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_prof_reg['E-mail setting 3'] . '<br /></label>
    </div>
    <p>' . $lang_prof_reg['Save user/pass info'] . '</p>
    <div class="rbox">
    <label><input type="checkbox" name="form[save_pass]" value="1"';
        if ($user['save_pass'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_prof_reg['Save user/pass'] . '<br /></label>
    </div>
    <p>' . $lang_profile['Notify full info'] . '</p>
    <div class="rbox">
    <label><input type="checkbox" name="form[notify_with_post]" value="1"';
        if ($user['notify_with_post'] == 1) {
            echo ' checked="checked"';
        }
        echo ' />' . $lang_profile['Notify full'] . '<br /></label>
    </div>
    </div>
    </fieldset>
    </div>
    <p><input type="submit" name="update" value="' . $lang_common['Submit'] . '" />' . $lang_profile['Instructions'] . '</p>
    </form>
    </div>
    </div>';
    } elseif ($_GET['section'] == 'admin') {
        if ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_ban_users'])) {
            message($lang_common['Bad request']);
        }

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Profile'];
        require_once PUN_ROOT . 'header.php';

        generate_profile_menu('admin');

        echo '<div class="blockform">
<h2><span>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section admin'] . '</span></h2>
<div class="box">
<form id="profile7" method="post" action="profile.php?section=admin&amp;id=' . $id . '&amp;action=foo">
<div class="inform">
<input type="hidden" name="form_sent" value="1" />
<fieldset>';


        if ($pun_user['g_id'] == PUN_MOD) {
            echo '<legend>' . $lang_profile['Delete ban legend'] . '</legend>
<div class="infldset">
<p><input type="submit" name="ban" value="' . $lang_profile['Ban user'] . '" /></p>
</div>
</fieldset>
</div>';
        } else {
            if ($pun_user['id'] != $id) {
                echo '<legend>' . $lang_profile['Group membership legend'] . '</legend><div class="infldset"><select id="group_id" name="group_id">';


                $result = $db->query('SELECT g_id, g_title FROM `' . $db->prefix . 'groups` WHERE g_id!=' . PUN_GUEST . ' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

                while ($cur_group = $db->fetch_assoc($result)) {
                    if ($cur_group['g_id'] == $user['g_id'] || ($cur_group['g_id'] == $pun_config['o_default_user_group'] && !$user['g_id'])) {
                        echo '<option value="' . $cur_group['g_id'] . '" selected="selected">' . pun_htmlspecialchars($cur_group['g_title']) . '</option>';
                    } else {
                        echo '<option value="' . $cur_group['g_id'] . '">' . pun_htmlspecialchars($cur_group['g_title']) . '</option>';
                    }
                }


                echo '</select><input type="submit" name="update_group_membership" value="' . $lang_profile['Save'] . '" /></div></fieldset></div><div class="inform"><fieldset>';
            }

            echo '<legend>' . $lang_profile['Delete ban legend'] . '</legend><div class="infldset"><input type="submit" name="delete_user" value="' . $lang_profile['Delete user'] . '" /> <input type="submit" name="ban" value="' . $lang_profile['Ban user'] . '" /></div></fieldset></div>';

            if ($user['g_id'] == PUN_MOD || $user['g_id'] == PUN_ADMIN) {
                echo '<div class="inform"><fieldset><legend>' . $lang_profile['Set mods legend'] . '</legend><div class="infldset"><p>' . $lang_profile['Moderator in info'] . '</p>';


                $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.moderators FROM ' . $db->prefix . 'categories AS c INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

                $cur_category = 0;
                while ($cur_forum = $db->fetch_assoc($result)) {
                    if ($cur_forum['cid'] != $cur_category) {
                        // A new category since last iteration?
                        if ($cur_category) {
                            echo '</div></div>';
                        }

                        echo '<div class="conl"><p><strong>' . $cur_forum['cat_name'] . '</strong></p><div class="rbox">';
                        $cur_category = $cur_forum['cid'];
                    }

                    $moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();

                    echo '<label><input type="checkbox" name="moderator_in[' . $cur_forum['fid'] . ']" value="1"' . ((in_array($id, $moderators)) ? ' checked="checked"' : '') . ' />' . pun_htmlspecialchars($cur_forum['forum_name']) . '<br /></label>';
                }

                if ($cur_category) {
                    echo '</div></div>';
                }
                echo '<br class="clearb" /><input type="submit" name="update_forums" value="' . $lang_profile['Update forums'] . '" /></div></fieldset></div>';
            }
        }


        echo '</form></div></div>';
    }


    echo '<div class="clearer"></div></div>';


    require_once PUN_ROOT . 'footer.php';
}
