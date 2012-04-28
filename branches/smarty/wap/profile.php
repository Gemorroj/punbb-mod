<?php
define('PUN_ROOT', '../');
require_once PUN_ROOT . 'include/common.php';
require_once PUN_ROOT . 'wap/header.php';

$id = intval(@$_GET['id']);
if ($id < 2) {
    wap_message($lang_common['Bad request']);
}

if (!$pun_user['g_read_board'] && ($_GET['action'] != 'change_pass' || !isset($_GET['key']))) {
    wap_message($lang_common['No view']);
}

// Load the profile.php/register.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/prof_reg.php';

// Load the profile.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/profile.php';


if ($_GET['action'] == 'change_pass') {
    if (isset($_GET['key'])) {
        // If the user is already logged in we shouldn't be here :)
        if (!$pun_user['is_guest']) {
            header('Location: index.php', true, 301);
            exit;
        }

        $key = $_GET['key'];

        $result = $db->query('SELECT activate_string, activate_key FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch new password', __FILE__, __LINE__, $db->error());
        list($new_password_hash, $new_password_key) = $db->fetch_row($result);

        if (!$key || $key != $new_password_key) {
            wap_message($lang_profile['Pass key bad'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.');
        } else {
            $db->query('UPDATE ' . $db->prefix . 'users SET password=\'' . $new_password_hash . '\', activate_string=NULL, activate_key=NULL WHERE id=' . $id) or error('Unable to update password', __FILE__, __LINE__, $db->error());

            wap_message($lang_profile['Pass updated'], true);
        }
    }

    // Make sure we are allowed to change this users password
    if ($pun_user['id'] != $id) {
        if ($pun_user['g_id'] > PUN_MOD) { // A regular user trying to change another users password?
            wap_message($lang_common['No permission']);
        } else
            if ($pun_user['g_id'] == PUN_MOD) {
                // A moderator trying to change a users password?
                $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                if (!$db->num_rows($result)) {
                    wap_message($lang_common['Bad request']);
                }

                if (!$pun_config['p_mod_edit_users'] || !$pun_config['p_mod_change_passwords'] ||
                    $db->result($result) < PUN_GUEST) {
                    wap_message($lang_common['No permission']);
                }
            }
    }

    if (isset($_POST['form_sent'])) {
        /*
        if($pun_user['g_id'] < PUN_GUEST)
        {confirm_referrer('profile.php');}
        */

        $old_password = isset($_POST['req_old_password']) ? trim($_POST['req_old_password']) : '';
        $new_password1 = trim($_POST['req_new_password1']);
        $new_password2 = trim($_POST['req_new_password2']);

        if ($new_password1 != $new_password2) {
            wap_message($lang_prof_reg['Pass not match']);
        }
        if (strlen($new_password1) < 4) {
            wap_message($lang_prof_reg['Pass too short']);
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
            wap_message($lang_profile['Wrong pass']);
        }

        $new_password_hash = pun_hash($new_password1);

        $db->query('UPDATE ' . $db->prefix . 'users SET password=\'' . $new_password_hash . '\' WHERE id=' . $id) or error('Unable to update password', __FILE__, __LINE__, $db->error());

        if ($pun_user['id'] == $id) {
            $expire = ($save_pass == 1) ? $_SERVER['REQUEST_TIME'] + 31536000 : 0;
            pun_setcookie($pun_user['id'], $new_password_hash, $expire);
        }

        wap_redirect('profile.php?section=essentials&amp;id=' . $id);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' &#187; ' . $lang_profile['Change pass'];
    $required_fields = array('req_old_password' => $lang_profile['Old pass'], 'req_new_password1' => $lang_profile['New pass'], 'req_new_password2' => $lang_profile['Confirm new pass']);
    $focus_element = array('change_pass', (($pun_user['g_id'] > PUN_MOD) ? 'req_old_password' : 'req_new_password1'));

//change_pass
$smarty->assign('id', $id);
$smarty->assign('pun_user', $pun_user);
$smarty->assign('pun_start', $pun_start);
$smarty->assign('lang_profile', $lang_profile);
$smarty->assign('lang_common', $lang_common);
$smarty->display('profile.password.tpl');
exit();

} else if ($_GET['action'] == 'change_email') {
        // Make sure we are allowed to change this users e-mail
        if ($pun_user['id'] != $id) {
            if ($pun_user['g_id'] > PUN_MOD) { // A regular user trying to change another users e-mail?
                wap_message($lang_common['No permission']);
            } else if ($pun_user['g_id'] == PUN_MOD) {
                    // A moderator trying to change a users e-mail?
                    $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' .
                        $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                    if (!$db->num_rows($result)) {
                        wap_message($lang_common['Bad request']);
                    }

                    if (!$pun_config['p_mod_edit_users'] || $db->result($result) < PUN_GUEST) {
                        wap_message($lang_common['No permission']);
                    }
                }
        }

        if ($_GET['key']) {
            $key = $_GET['key'];

            $result = $db->query('SELECT activate_string, activate_key FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch activation data', __FILE__, __LINE__, $db->error());
            list($new_email, $new_email_key) = $db->fetch_row($result);

            if ($key != $new_email_key) {
                wap_message($lang_profile['E-mail key bad'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.');
            } else {
                $db->query('UPDATE ' . $db->prefix . 'users SET email=activate_string, activate_string=NULL, activate_key=NULL WHERE id=' . $id) or error('Unable to update e-mail address', __FILE__, __LINE__, $db->error());

                wap_message($lang_profile['E-mail updated'], true);
            }
        } else if ($_POST['form_sent']) {
                if (pun_hash($_POST['req_password']) != $pun_user['password']) {
                    wap_message($lang_profile['Wrong pass']);
                }

                include_once PUN_ROOT . 'include/email.php';

                // Validate the email-address
                $new_email = strtolower(trim($_POST['req_new_email']));
                if (!is_valid_email($new_email)) {
                    wap_message($lang_common['Invalid e-mail']);
                }

                // Check it it's a banned e-mail address
                if (is_banned_email($new_email)) {
                    if (!$pun_config['p_allow_banned_email']) {
                        wap_message($lang_prof_reg['Banned e-mail']);
                    } else if ($pun_config['o_mailing_list']) {
                            $mail_subject = 'Alert - Banned e-mail detected';
                            $mail_message = 'User \'' . $pun_user['username'] . '\' changed to banned e-mail address: ' .
                                $new_email . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] .
                                '/profile.php?id=' . $id . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" .
                                '(Do not reply to this message)';

                            pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
                        }
                }

                // Check if someone else already has registered with that e-mail address
                $result = $db->query('SELECT id, username FROM ' . $db->prefix .
                    'users WHERE email=\'' . $db->escape($new_email) . '\'') or error('Unable to fetch user info',
                    __FILE__, __LINE__, $db->error());
                if ($db->num_rows($result)) {
                    if (!$pun_config['p_allow_dupe_email']) {
                        wap_message($lang_prof_reg['Dupe e-mail']);
                    } else
                        if ($pun_config['o_mailing_list']) {
                            while ($cur_dupe = $db->fetch_assoc($result)) {
                                $dupe_list[] = $cur_dupe['username'];
                            }

                            $mail_subject = 'Alert - Duplicate e-mail detected';
                            $mail_message = 'User \'' . $pun_user['username'] . '\' changed to an e-mail address that also belongs to: ' .
                                implode(', ', $dupe_list) . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] .
                                '/profile.php?id=' . $id . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" .
                                '(Do not reply to this message)';

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
                $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer'], $mail_message);
                pun_mail($new_email, $mail_subject, $mail_message);

                wap_message($lang_profile['Activate e-mail sent'] . ' <a href="mailto:' . $pun_config['o_admin_email'] .
                    '">' . $pun_config['o_admin_email'] . '</a>.', true);
            }

        $page_title = $pun_config['o_board_title'] . ' > ' . $lang_common['Profile'] . ' > ' . $lang_profile['Change e-mail'];
        $required_fields = array('req_new_email' => $lang_profile['New e-mail'], 'req_password' => $lang_common['Password']);
        $focus_element = array('change_email', 'req_new_email');

    //change_email
    $smarty->assign('page_title', $page_title);
    $smarty->assign('id', $id);
    $smarty->assign('pun_user', $pun_user);
    $smarty->assign('lang_profile', $lang_profile);
    $smarty->assign('lang_common', $lang_common);
    $smarty->display('profile.email.tpl');
    exit();
    
    } else if ($_GET['action'] == 'upload_avatar' || $_GET['action'] == 'upload_avatar2') {
            if (!$pun_config['o_avatars']) {
                wap_message($lang_profile['Avatars disabled']);
            }

            if ($pun_user['id'] != $id && $pun_user['g_id'] > PUN_MOD) {
                wap_message($lang_common['No permission']);
            }

            if ($_POST['form_sent']) {
                if (!$_FILES['req_file']) {
                    wap_message($lang_profile['No file']);
                }

                $uploaded_file = $_FILES['req_file'];

                // Make sure the upload went smooth
                if ($uploaded_file['error']) {
                    switch ($uploaded_file['error']) {
                        case 1: // UPLOAD_ERR_INI_SIZE
                        case 2: // UPLOAD_ERR_FORM_SIZE
                            wap_message($lang_profile['Too large ini']);
                            break;

                        case 3: // UPLOAD_ERR_PARTIAL
                            wap_message($lang_profile['Partial upload']);
                            break;

                        case 4: // UPLOAD_ERR_NO_FILE
                            wap_message($lang_profile['No file']);
                            break;

                        case 6: // UPLOAD_ERR_NO_TMP_DIR
                            wap_message($lang_profile['No tmp directory']);
                            break;

                        default:
                            // No error occured, but was something actually uploaded?
                            if (!$uploaded_file['size']) {
                                wap_message($lang_profile['No file']);
                            }
                            break;
                    }
                }

                if (is_uploaded_file($uploaded_file['tmp_name'])) {
                    $allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png',
                        'image/x-png');
                    if (!in_array($uploaded_file['type'], $allowed_types)) {
                        wap_message($lang_profile['Bad type']);
                    }

                    // Make sure the file isn't too big
                    if ($uploaded_file['size'] > $pun_config['o_avatars_size']) {
                        wap_message($lang_profile['Too large'] . ' ' . $pun_config['o_avatars_size'] .
                            ' ' . $lang_profile['bytes'] . '.');
                    }

                    // Determine type
                    $extensions = null;
                    if ($uploaded_file['type'] == 'image/gif') {
                        $extensions = array('.gif', '.jpg', '.png');
                    } else
                        if ($uploaded_file['type'] == 'image/jpeg' || $uploaded_file['type'] ==
                            'image/pjpeg') {
                            $extensions = array('.jpg', '.gif', '.png');
                        } else {
                            $extensions = array('.png', '.gif', '.jpg');
                        }

                        // Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions.
                        if (!@move_uploaded_file($uploaded_file['tmp_name'], PUN_ROOT . $pun_config['o_avatars_dir'] .
                            '/' . $id . '.tmp')) {
                            wap_message($lang_profile['Move failed'] . ' <a href="mailto:' . $pun_config['o_admin_email'] .
                                '">' . $pun_config['o_admin_email'] . '</a>.');
                        }

                    // Now check the width/height
                    list($width, $height, $type, ) = getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] .
                        '/' . $id . '.tmp');
                    if (!$width || !$height || $width > $pun_config['o_avatars_width'] || $height >
                        $pun_config['o_avatars_height']) {
                        @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp');
                        wap_message($lang_profile['Too wide or high'] . ' ' . $pun_config['o_avatars_width'] .
                            'x' . $pun_config['o_avatars_height'] . ' ' . $lang_profile['pixels'] . '.');
                    } else
                        if ($type == 1 && $uploaded_file['type'] != 'image/gif')
                            // Prevent dodgy uploads
                            {
                            @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp');
                            wap_message($lang_profile['Bad type']);
                        }

                    // Delete any old avatars and put the new one in place
                    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0]);
                    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[1]);
                    @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[2]);
                    @rename(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.tmp', PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0]);
                    @chmod(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . $extensions[0], 0644);
                } else {
                    wap_message($lang_profile['Unknown failure']);
                }

                // Enable use_avatar (seems sane since the user just uploaded an avatar)
                $db->query('UPDATE ' . $db->prefix . 'users SET use_avatar=1 WHERE id=' . $id) or
                    error('Unable to update avatar state', __FILE__, __LINE__, $db->error());

                wap_redirect('profile.php?section=personality&amp;id=' . $id);
            }
            
            //upload_avatar
            $page_title = $pun_config['o_board_title'] . ' > ' . $lang_common['Profile'] . ' > ' . $lang_profile['Upload avatar'];
            $required_fields = array('req_file' => $lang_profile['File']);
            $focus_element = array('upload_avatar', 'req_file');
            
            $smarty->assign('page_title', $page_title);
            $smarty->assign('id', $id);
            $smarty->assign('pun_user', $pun_user);
            $smarty->assign('pun_config', $pun_config);
            $smarty->assign('lang_profile', $lang_profile);
            $smarty->assign('lang_common', $lang_common);
            $smarty->display('profile.avatar.tpl');
            exit();
            
        } else if ($_GET['action'] == 'delete_avatar') {
                if ($pun_user['id'] != $id && $pun_user['g_id'] > PUN_MOD) {
                    wap_message($lang_common['No permission']);
                }

                //confirm_referrer('profile.php');

                @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg');
                @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png');
                @unlink(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif');

                // Disable use_avatar
                $db->query('UPDATE ' . $db->prefix . 'users SET use_avatar=0 WHERE id=' . $id) or
                    error('Unable to update avatar state', __FILE__, __LINE__, $db->error());

                wap_redirect('profile.php?section=personality&amp;id=' . $id);
            } else if (isset($_POST['update_group_membership'])) {
                    if ($pun_user['g_id'] > PUN_ADMIN) {
                        wap_message($lang_common['No permission']);
                    }

                    //confirm_referrer('profile.php');

                    $new_group_id = intval($_POST['group_id']);

                    $db->query('UPDATE ' . $db->prefix . 'users SET group_id=' . $new_group_id .
                        ' WHERE id=' . $id) or error('Unable to change user group', __FILE__, __LINE__,
                        $db->error());

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

                    wap_redirect('profile.php?section=admin&amp;id=' . $id);
                } else if ($_POST['update_forums']) {
                        if ($pun_user['g_id'] > PUN_ADMIN) {
                            wap_message($lang_common['No permission']);
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
                            $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) :
                                array();
                            // If the user should have moderator access (and he/she doesn't already have it)
                            if (in_array($cur_forum['id'], $moderator_in) && !in_array($id, $cur_moderators)) {
                                $cur_moderators[$username] = $id;
                                ksort($cur_moderators);

                                $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=\'' . $db->escape(serialize
                                    ($cur_moderators)) . '\' WHERE id=' . $cur_forum['id']) or error('Unable to update forum',
                                    __FILE__, __LINE__, $db->error());
                            }
                            // If the user shouldn't have moderator access (and he/she already has it)
                            else
                                if (!in_array($cur_forum['id'], $moderator_in) && in_array($id, $cur_moderators)) {
                                    unset($cur_moderators[$username]);
                                    $cur_moderators = ($cur_moderators) ? '\'' . $db->escape(serialize($cur_moderators)) . '\'' : 'NULL';

                                    $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=' . $cur_moderators . ' WHERE id=' . $cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
                                }
                        }

                        wap_redirect('profile.php?section=admin&amp;id=' . $id);
                    } else if ($_POST['ban']) {
                            if ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_ban_users'])) {
                                wap_message($lang_common['No permission']);
                            }

                            wap_redirect(PUN_ROOT . 'admin_bans.php?add_ban=' . $id);
                        } else if ($_POST['delete_user'] || $_POST['delete_user_comply']) {
                                if ($pun_user['g_id'] > PUN_ADMIN) {
                                    wap_message($lang_common['No permission']);
                                }

                                //confirm_referrer('profile.php');

                                // Get the username and group of the user we are deleting
                                $result = $db->query('SELECT group_id, username FROM ' . $db->prefix .
                                    'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__,
                                    __LINE__, $db->error());
                                list($group_id, $username) = $db->fetch_row($result);

                                if ($group_id == PUN_ADMIN) {
                                    wap_message('Administrators cannot be deleted. In order to delete this user, you must first move him/her to a different user group.');
                                }

                                if ($_POST['delete_user_comply']) {
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
                                    if ($_POST['delete_posts']) {
                                        include PUN_ROOT . 'include/search_idx.php';
                                        @set_time_limit(0);

                                        // Find all posts made by this user
                                        $result = $db->query('SELECT p.id, p.topic_id, t.forum_id FROM ' . $db->prefix .
                                            'posts AS p INNER JOIN ' . $db->prefix .
                                            'topics AS t ON t.id=p.topic_id INNER JOIN ' . $db->prefix .
                                            'forums AS f ON f.id=t.forum_id WHERE p.poster_id=' . $id) or error('Unable to fetch posts',
                                            __FILE__, __LINE__, $db->error());
                                        if ($db->num_rows($result)) {
                                            while ($cur_post = $db->fetch_assoc($result)) {
                                                // Determine whether this post is the "topic post" or not
                                                $result2 = $db->query('SELECT id FROM ' . $db->prefix . 'posts WHERE topic_id=' .
                                                    $cur_post['topic_id'] . ' ORDER BY posted LIMIT 1') or error('Unable to fetch post info',
                                                    __FILE__, __LINE__, $db->error());

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
                                            'attachments SET poster_id=1 WHERE poster_id=' . $id) or error('Unable to update attachments',
                                            __FILE__, __LINE__, $db->error());
                                    }


                                    // Delete the user
                                    $db->query('DELETE FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to delete user',
                                        __FILE__, __LINE__, $db->error());

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

                                    wap_redirect('index.php');
                                }
                                
                                //Delete user
                                $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' &#187; ' . $lang_profile['Delete user']. ' ' . pun_htmlspecialchars($username);
                                
                                $smarty->assign('page_title', $page_title);
                                $smarty->assign('id', $id);
                                $smarty->assign('pun_user', $pun_user);
                                $smarty->assign('lang_profile', $lang_profile);
                                $smarty->assign('username', $username);
                                $smarty->display('profile.delete.tpl');
                                exit();
                                
                            } else
                                if ($_POST['form_sent']) {
                                    // Fetch the user group of the user we are editing
                                    $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' .
                                        $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                                    if (!$db->num_rows($result)) {
                                        wap_message($lang_common['Bad request']);
                                    }

                                    $group_id = $db->result($result);

                                    if ($pun_user['id'] != $id && ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] ==
                                        PUN_MOD && !$pun_config['p_mod_edit_users']) || ($pun_user['g_id'] == PUN_MOD &&
                                        $group_id < PUN_GUEST))) {
                                        wap_message($lang_common['No permission']);
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

                                        while (list($key, $value) = @each($_POST['form'])) {
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
                                            {
                                                $form = extract_elements(array('timezone', 'language'));

                                                if ($pun_user['g_id'] < PUN_GUEST) {
                                                    $form['admin_note'] = trim($_POST['admin_note']);

                                                    // Are we allowed to change usernames?
                                                    if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && $pun_config['p_mod_rename_users'] ==
                                                        1)) {
                                                        $form['username'] = trim($_POST['req_username']);
                                                        $old_username = trim($_POST['old_username']);

                                                        if (mb_strlen($form['username']) < 2) {
                                                            wap_message($lang_prof_reg['Username too short']);
                                                        } else
                                                            if (mb_strlen($form['username']) > 25) {
                                                                // This usually doesn't happen since the form element only accepts 25 characters
                                                                wap_message($lang_common['Bad request']);
                                                            } else
                                                                if (!strcasecmp($form['username'], 'Guest') || !strcasecmp($form['username'], $lang_common['Guest'])) {
                                                                    wap_message($lang_prof_reg['Username guest']);
                                                                } else
                                                                    if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $form['username'])) {
                                                                        wap_message($lang_prof_reg['Username IP']);
                                                                    } else
                                                                        if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i',
                                                                            $form['username'])) {
                                                                            wap_message($lang_prof_reg['Username BBCode']);
                                                                        }

                                                        // Check that the username is not already registered
                                                        $result = $db->query('SELECT 1 FROM ' . $db->prefix . 'users WHERE username=\'' .
                                                            $db->escape($form['username']) . '\' AND id!=' . $id) or error('Unable to fetch user info',
                                                            __FILE__, __LINE__, $db->error());
                                                        if ($db->num_rows($result)) {
                                                            wap_message($lang_profile['Dupe username']);
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
                                                        wap_message($lang_common['Invalid e-mail']);
                                                    }
                                                }

                                                // Make sure we got a valid language string
                                                if ($form['language']) {
                                                    $form['language'] = preg_replace('#[\.\\\/]#', '', $form['language']);
                                                    if (!file_exists(PUN_ROOT . 'lang/' . $form['language'] . '/common.php')) {
                                                        wap_message($lang_common['Bad request']);
                                                    }
                                                }

                                                break;
                                            }

                                        case 'personal':
                                            {
                                                $_POST['form']['birthday'] = intval($_POST['day']) . '.' . intval($_POST['month']) .
                                                    '.' . intval($_POST['year']);
                                                if ($_POST['form']['birthday'] == '0.0.0') {
                                                    $_POST['form']['birthday'] = null;
                                                }

                                                $form = extract_elements(array('realname', 'url', 'location', 'sex', 'birthday'));

                                                if ($pun_user['g_id'] == PUN_ADMIN) {
                                                    $form['title'] = trim($_POST['title']);
                                                } else
                                                    if ($pun_user['g_set_title'] == 1) {
                                                        $form['title'] = trim($_POST['title']);

                                                        if ($form['title']) {
                                                            // A list of words that the title may not contain
                                                            // If the language is English, there will be some duplicates, but it's not the end of the world
                                                            $forbidden = array('Member', 'Moderator', 'Administrator', 'Banned', 'Guest', $lang_common['Member'],
                                                                $lang_common['Moderator'], $lang_common['Administrator'], $lang_common['Banned'],
                                                                $lang_common['Guest']);

                                                            if (in_array($form['title'], $forbidden)) {
                                                                wap_message($lang_profile['Forbidden title']);
                                                            }
                                                        }
                                                    }

                                                // Add http:// if the URL doesn't contain it already
                                                if ($form['url'] && strpos(strtolower($form['url']), 'http://') !== 0) {
                                                    $form['url'] = 'http://' . $form['url'];
                                                }

                                                break;
                                            }

                                        case 'messaging':
                                            {
                                                $form = extract_elements(array('jabber', 'icq', 'msn', 'aim', 'yahoo'));

                                                // If the ICQ UIN contains anything other than digits it's invalid
                                                if ($form['icq'] && !intval($form['icq'])) {
                                                    wap_message($lang_prof_reg['Bad ICQ']);
                                                }

                                                break;
                                            }

                                        case 'personality':
                                            {
                                                $form = extract_elements(array('use_avatar'));

                                                // Clean up signature from POST
                                                $form['signature'] = pun_linebreaks(trim($_POST['signature']));

                                                // Validate signature
                                                if (mb_strlen($form['signature']) > $pun_config['p_sig_length']) {
                                                    wap_message($lang_prof_reg['Sig too long'] . ' ' . $pun_config['p_sig_length'] .
                                                        ' ' . $lang_prof_reg['characters'] . '.');
                                                } else if (substr_count($form['signature'], "\n") > ($pun_config['p_sig_lines'] - 1)) {
                                                        wap_message($lang_prof_reg['Sig too many lines'] . ' ' . $pun_config['p_sig_lines'] .
                                                            ' ' . $lang_prof_reg['lines'] . '.');
                                                    } else
                                                        if ($form['signature'] && !$pun_config['p_sig_all_caps'] && mb_strtoupper($form['signature']) ==
                                                            $form['signature'] && $pun_user['g_id'] > PUN_MOD) {
                                                            $form['signature'] = ucwords(mb_strtolower($form['signature']));
                                                        }

                                                // Validate BBCode syntax
                                                if ($pun_config['p_sig_bbcode'] && strpos($form['signature'], '[') !== false && strpos($form['signature'], ']') !== false) {
                                                    include_once PUN_ROOT . 'include/parser.php';
                                                    $form['signature'] = preparse_bbcode($form['signature'], $foo, true);
                                                }

                                                if ($form['use_avatar'] != 1) {
                                                    $form['use_avatar'] = 0;
                                                }
                                                break;
                                            }

                                        case 'display':
                                            {

                                                // REAL MARK TOPIC AS READ MOD BEGIN
                                                $form = extract_elements(array('disp_topics', 'disp_posts', 'show_smilies',
                                                    'show_img', 'show_img_sig', 'show_avatars', 'show_sig', 'style_wap',
                                                    'mark_after', 'show_bbpanel_qpost'));
                                                // REAL MARK TOPIC AS READ MOD END
                                                if ($form['disp_topics'] && intval($form['disp_topics']) < 3)
                                                    $form['disp_topics'] = 3;
                                                if ($form['disp_topics'] && intval($form['disp_topics']) > 75)
                                                    $form['disp_topics'] = 75;
                                                if ($form['disp_posts'] && intval($form['disp_posts']) < 3)
                                                    $form['disp_posts'] = 3;
                                                if ($form['disp_posts'] && intval($form['disp_posts']) > 75)
                                                    $form['disp_posts'] = 75;

                                                // REAL MARK TOPIC AS READ MOD BEGIN
                                                if (intval(@$form['mark_after']) > 100) {
                                                    $form['mark_after'] = 1296000;
                                                } else {
                                                    $form['mark_after'] = $form['mark_after'] * 86400;
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
                                            }

                                        case 'privacy':
                                            {
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
                                                    $result = $db->query('SELECT password FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user password hash', __FILE__, __LINE__, $db->error());
                                                    pun_setcookie($id, $db->result($result), ($form['save_pass'] == 1) ? $_SERVER['REQUEST_TIME'] + 31536000 : 0);
                                                }

                                                break;
                                            }

                                        default:
                                            wap_message($lang_common['Bad request']);
                                    }


                                    // Singlequotes around non-empty values and NULL for empty values
                                    $temp = array();
                                    while (list($key, $input) = each($form)) {
                                        $value = ($input !== null) ? '\'' . $db->escape($input) . '\'' : 'NULL';
                                        $temp[] = $key . '=' . $value;
                                    }

                                    if (!$temp) {
                                        wap_message($lang_common['Bad request']);
                                    }

                                    $db->query('UPDATE `' . $db->prefix . 'users` SET ' . implode(',', $temp) . ' WHERE `id`=' . $id) or error('Unable to update profile', __FILE__, __LINE__, $db->error());

                                    // If we changed the username we have to update some stuff
                                    if ($username_updated) {
                                        $db->query('UPDATE ' . $db->prefix . 'posts SET poster=\'' . $db->escape($form['username']) .
                                            '\' WHERE poster_id=' . $id) or error('Unable to update posts', __FILE__,
                                            __LINE__, $db->error());
                                        $db->query('UPDATE ' . $db->prefix . 'topics SET poster=\'' . $db->escape($form['username']) .
                                            '\' WHERE poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update topics',
                                            __FILE__, __LINE__, $db->error());
                                        $db->query('UPDATE ' . $db->prefix . 'topics SET last_poster=\'' . $db->escape($form['username']) .
                                            '\' WHERE last_poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update topics',
                                            __FILE__, __LINE__, $db->error());
                                        $db->query('UPDATE ' . $db->prefix . 'forums SET last_poster=\'' . $db->escape($form['username']) .
                                            '\' WHERE last_poster=\'' . $db->escape($old_username) . '\'') or error('Unable to update forums',
                                            __FILE__, __LINE__, $db->error());
                                        $db->query('UPDATE ' . $db->prefix . 'online SET ident=\'' . $db->escape($form['username']) .
                                            '\' WHERE ident=\'' . $db->escape($old_username) . '\'') or error('Unable to update online list',
                                            __FILE__, __LINE__, $db->error());

                                        // If the user is a moderator or an administrator we have to update the moderator lists
                                        $result = $db->query('SELECT group_id FROM ' . $db->prefix . 'users WHERE id=' . $id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                                        $group_id = $db->result($result);

                                        if ($group_id < PUN_GUEST) {
                                            $result = $db->query('SELECT id, moderators FROM ' . $db->prefix . 'forums') or
                                                error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

                                            while ($cur_forum = $db->fetch_assoc($result)) {
                                                $cur_moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) :
                                                    array();

                                                if (in_array($id, $cur_moderators)) {
                                                    unset($cur_moderators[$old_username]);
                                                    $cur_moderators[$form['username']] = $id;
                                                    ksort($cur_moderators);

                                                    $db->query('UPDATE ' . $db->prefix . 'forums SET moderators=\'' . $db->escape(serialize
                                                        ($cur_moderators)) . '\' WHERE id=' . $cur_forum['id']) or error('Unable to update forum',
                                                        __FILE__, __LINE__, $db->error());
                                                }
                                            }
                                        }
                                    }

                                    wap_redirect('profile.php?section=' . htmlspecialchars($_GET['section']) . '&amp;id=' . $id);
                                }

// REAL MARK TOPIC AS READ MOD BEGIN
$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.sex, u.birthday, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.use_avatar, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.save_pass, u.notify_with_post, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.language, u.style_wap, u.num_posts, u.num_files, u.file_bonus, u.last_post, u.registered, u.registration_ip, u.admin_note, g.g_id, g.g_user_title, u.mark_after, u.show_bbpanel_qpost FROM ' .
    $db->prefix . 'users AS u LEFT JOIN ' . $db->prefix .
    'groups AS g ON g.g_id=u.group_id WHERE u.id=' . $id) or error('Unable to fetch user info',
    __FILE__, __LINE__, $db->error());
// REAL MARK TOPIC AS READ MOD END


if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$user = $db->fetch_assoc($result);

$last_post = format_time($user['last_post']);

if ($user['signature']) {
    include_once PUN_ROOT . 'include/parser.php';
    $parsed_signature = parse_signature($user['signature']);
}


//if($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST)
//{
$q = $db->fetch_row($db->query('
    SELECT COUNT(1), (SELECT COUNT(1) FROM `' . $db->prefix . 'karma` WHERE `vote` = "-1" AND `to` = ' . $id . ') FROM `' . $db->prefix . 'karma` WHERE `vote` = "1" AND `to` = ' . $id
));

$karma['plus'] = intval($q[0]);
$karma['minus'] = intval($q[1]);
$karma['karma'] = $karma['plus'] - $karma['minus'];
unset($q);
//}

// View or edit?
if (isset($_GET['preview']) or ($pun_user['id'] != $id && ($pun_user['g_id'] >
    PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_edit_users']) ||
    ($pun_user['g_id'] == PUN_MOD && $user['g_id'] < PUN_GUEST)))) {
    if (!$user['email_setting'] && !$pun_user['is_guest']) {
        $email_field = '<a href="mailto:' . $user['email'] . '">' . $user['email'] . '</a>';
    } else
        if ($user['email_setting'] == 1 && !$pun_user['is_guest']) {
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
            } else
                if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg')) {
                    $avatar_field = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg" ' . $img_size[3] . ' alt="" />';
                } else
                    if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png')) {
                        $avatar_field = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png" ' . $img_size[3] . ' alt="" />';
                    } else {
                        $avatar_field = $lang_profile['No avatar'];
                    }
        } else {
            $avatar_field = $lang_profile['No avatar'];
        }
    }

//view Profile
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' &#187; ' . $lang_profile['Preview'];
    define('PUN_ALLOW_INDEX', 1);
    //preview
    $smarty->assign('id', $id);
    $smarty->assign('pun_user', $pun_user);
    $smarty->assign('pun_config', $pun_config);
    $smarty->assign('pun_start', $pun_start);
    $smarty->assign('lang_profile', $lang_profile);
    $smarty->assign('lang_common', $lang_common);
    
    $smarty->assign('user', $user);
    $smarty->assign('karma', $karma);
    $smarty->assign('last_post', $last_post);
    
    $smarty->assign('posts_field', $posts_field);
    $smarty->assign('user_title_field', $user_title_field);
    
    $smarty->display('profile.view.tpl');
    exit();
}
//profile general
else {
    if (!$_GET['section'] || $_GET['section'] == 'essentials') {
        if ($pun_user['g_id'] < PUN_GUEST) {
            if ($pun_user['g_id'] == PUN_ADMIN || $pun_config['p_mod_rename_users'] == 1) {
                $username_field = '
                <input type="hidden" name="old_username" value="' . pun_htmlspecialchars($user['username']) . '" />
                <strong>' . $lang_common['Username'] . '</strong><br />
                <input type="text" name="req_username" value="' . pun_htmlspecialchars($user['username']) . '" maxlength="25" /><br />';
            } else {
                $username_field = $lang_common['Username'] . ': ' . pun_htmlspecialchars($user['username']);
            }

            $email_field = '<strong>' . $lang_common['E-mail'] . '</strong><br />
            <input type="text" name="req_email" value="' . $user['email'] . '" maxlength="50" /><br />
            <a href="misc.php?email=' . $id . '">' . $lang_common['Send e-mail'] . '</a><br/>';

            // PMS MOD BEGIN
            include PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
            $email_field .= '<a href="message_send.php?id=' . $id . '">' . $lang_pms['Quick message'] . '</a><br/>';
            // PMS MOD END


        } else {
            $username_field = $lang_common['Username'] . ': ' . pun_htmlspecialchars($user['username']);

            if ($pun_config['o_regs_verify'] == 1) {
                $email_field = $lang_common['E-mail'] . ': ' . $user['email'] . ' - <a href="profile.php?action=change_email&amp;id=' . $id . '">' . $lang_profile['Change e-mail'] . '</a>';
            } else {
                $email_field = '<strong>' . $lang_common['E-mail'] . '</strong><br />
                <input type="text" name="req_email" value="' . $user['email'] . '" maxlength="50" /><br />';
            }
        }

        if ($pun_user['g_id'] == PUN_ADMIN) {
            $posts_field = $lang_common['Posts'] . ': <input type="text" name="num_posts" value="' . $user['num_posts'] . '" size="3" maxlength="8" /> - <a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] . '</a><br/>';
            $files_field = $lang_common['Files'] . ': <input type="text" name="num_files"  value="' . $user['num_files'] . '" size="3" maxlength="8" /> - <a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] . '</a><br/>
' . $lang_common['Bonus'] . ': <input type="text" name="file_bonus" value="' . $user['file_bonus'] . '" size="3" maxlength="8" /><br />';
        } else
            if ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
                $posts_field = $lang_common['Posts'] . ': ' . $user['num_posts'] . ' - <a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] . '</a><br/>';
                $files_field = $lang_common['Files'] . ': ' . $user['num_files'] . ' - <a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] . '</a><br/>';
            } else {
                $posts_field = '<a href="search.php?action=show_user&amp;user_id=' . $id . '">' . $lang_profile['Show posts'] . '</a>';
                $files_field = '<a href="filemap.php?user_id=' . $id . '">' . $lang_profile['Show files'] . '</a>';
            }

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section essentials'];
        $required_fields = array('req_username' => $lang_common['Username'], 'req_email' => $lang_common['E-mail']);
        require_once PUN_ROOT . 'wap/header.php';

        wap_generate_profile_menu('essentials');


echo '<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section essentials'] . '</strong></div>
<form method="post" action="profile.php?section=essentials&amp;id=' . $id . '">
<div class="input">
<input type="hidden" name="form_sent" value="1" />' . $username_field;

        if ($pun_user['id'] == $id || $pun_user['g_id'] == PUN_ADMIN || ($user['g_id'] >
            PUN_MOD && $pun_config['p_mod_change_passwords'] == 1)) {
            echo '<a href="profile.php?action=change_pass&amp;id=' . $id . '">' . $lang_profile['Change pass'] . '</a><br/>';
        }

echo '</div>
<div class="input2">
<strong>' . $lang_prof_reg['E-mail legend'] . '</strong><br/>
' . $email_field . '</div>
<div class="input">
<strong>' . $lang_prof_reg['Localisation legend'] . '</strong><br/>
' . $lang_prof_reg['Timezone'] . ': ' . $lang_prof_reg['Timezone info'] . '<br/>
<select name="form[timezone]">';
?>
<option value="-12"<?php if ($user['timezone'] == -12)
            echo ' selected="selected"' ?>>-12</option>
<option value="-11"<?php if ($user['timezone'] == -11)
                echo ' selected="selected"' ?>>-11</option>
<option value="-10"<?php if ($user['timezone'] == -10)
                    echo ' selected="selected"' ?>>-10</option>
<option value="-9.5"<?php if ($user['timezone'] == -9.5)
                        echo ' selected="selected"' ?>>-09.5</option>
<option value="-9"<?php if ($user['timezone'] == -9)
                            echo ' selected="selected"' ?>>-09</option>
<option value="-8.5"<?php if ($user['timezone'] == -8.5)
                                echo ' selected="selected"' ?>>-08.5</option>
<option value="-8"<?php if ($user['timezone'] == -8)
                                    echo ' selected="selected"' ?>>-08 PST</option>
<option value="-7"<?php if ($user['timezone'] == -7)
                                        echo ' selected="selected"' ?>>-07 MST</option>
<option value="-6"<?php if ($user['timezone'] == -6)
                                            echo ' selected="selected"' ?>>-06 CST</option>
<option value="-5"<?php if ($user['timezone'] == -5)
                                                echo ' selected="selected"' ?>>-05 EST</option>
<option value="-4"<?php if ($user['timezone'] == -4)
                                                    echo ' selected="selected"' ?>>-04 AST</option>
<option value="-3.5"<?php if ($user['timezone'] == -3.5)
                                                        echo ' selected="selected"' ?>>-03.5</option>
<option value="-3"<?php if ($user['timezone'] == -3)
                                                            echo ' selected="selected"' ?>>-03 ADT</option>
<option value="-2"<?php if ($user['timezone'] == -2)
                                                                echo ' selected="selected"' ?>>-02</option>
<option value="-1"<?php if ($user['timezone'] == -1)
                                                                    echo ' selected="selected"' ?>>-01</option>
<option value="0"<?php if ($user['timezone'] == 0)
                                                                        echo ' selected="selected"' ?>>00 GMT</option>
<option value="1"<?php if ($user['timezone'] == 1)
                                                                            echo ' selected="selected"' ?>>+01 CET</option>
<option value="2"<?php if ($user['timezone'] == 2)
                                                                                echo ' selected="selected"' ?>>+02</option>
<option value="3"<?php if ($user['timezone'] == 3)
                                                                                    echo ' selected="selected"' ?>>+03</option>
<option value="3.5"<?php if ($user['timezone'] == 3.5)
                                                                                        echo ' selected="selected"' ?>>+03.5</option>
<option value="4"<?php if ($user['timezone'] == 4)
                                                                                            echo ' selected="selected"' ?>>+04</option>
<option value="4.5"<?php if ($user['timezone'] == 4.5)
                                                                                                echo ' selected="selected"' ?>>+04.5</option>
<option value="5"<?php if ($user['timezone'] == 5)
                                                                                                    echo ' selected="selected"' ?>>+05</option>
<option value="5.5"<?php if ($user['timezone'] == 5.5)
                                                                                                        echo ' selected="selected"' ?>>+05.5</option>
<option value="6"<?php if ($user['timezone'] == 6)
                                                                                                            echo ' selected="selected"' ?>>+06</option>
<option value="6.5"<?php if ($user['timezone'] == 6.5)
                                                                                                                echo ' selected="selected"' ?>>+06.5</option>
<option value="7"<?php if ($user['timezone'] == 7)
                                                                                                                    echo ' selected="selected"' ?>>+07</option>
<option value="8"<?php if ($user['timezone'] == 8)
                                                                                                                        echo ' selected="selected"' ?>>+08</option>
<option value="9"<?php if ($user['timezone'] == 9)
                                                                                                                            echo ' selected="selected"' ?>>+09</option>
<option value="9.5"<?php if ($user['timezone'] == 9.5)
                                                                                                                                echo ' selected="selected"' ?>>+09.5</option>
<option value="10"<?php if ($user['timezone'] == 10)
                                                                                                                                    echo ' selected="selected"' ?>>+10</option>
<option value="10.5"<?php if ($user['timezone'] == 10.5)
                                                                                                                                        echo ' selected="selected"' ?>>+10.5</option>
<option value="11"<?php if ($user['timezone'] == 11)
                                                                                                                                            echo ' selected="selected"' ?>>+11</option>
<option value="11.5"<?php if ($user['timezone'] == 11.5)
                                                                                                                                                echo ' selected="selected"' ?>>+11.5</option>
<option value="12"<?php if ($user['timezone'] == 12)
                                                                                                                                                    echo ' selected="selected"' ?>>+12</option>
<option value="13"<?php if ($user['timezone'] == 13)
                                                                                                                                                        echo ' selected="selected"' ?>>+13</option>
<option value="14"<?php if ($user['timezone'] == 14)
                                                                                                                                                            echo ' selected="selected"' ?>>+14</option>
</select>
<?php
                                                                                                                                                            $languages = array();
        $d = dir(PUN_ROOT . 'lang');
        while (($entry = $d->read()) !== false) {
            if ($entry[0] != '.' && is_dir(PUN_ROOT . 'lang/' . $entry) && file_exists(PUN_ROOT . 'lang/' . $entry . '/common.php')) {
                $languages[] = $entry;
            }
        }
        $d->close();

        // Only display the language selection box if there's more than one language available
        if (sizeof($languages) > 1) {
            natsort($languages);

            echo '</div>
<div class="input2"><strong>'.$lang_prof_reg['Language'] . '</strong>: ' . $lang_prof_reg['Language info'] . '<br/>
<select name="form[language]">';

            while (list(, $temp) = @each($languages)) {
                if ($user['language'] == $temp) {
                    echo '<option value="' . $temp . '" selected="selected">' . $temp . '</option>';
                } else {
                    echo '<option value="' . $temp . '">' . $temp . '</option>';
                }
            }

            echo '</select>';

        }

        echo '</div>
<div class="input">
<strong>' . $lang_profile['User activity'] . '</strong><br/>
' . $lang_common['Registered'] . ': ' . format_time($user['registered'], true);
        if ($pun_user['g_id'] < PUN_GUEST) {
            echo ' (<a href="moderate.php?get_host=' . $user['registration_ip'] . '">' . $user['registration_ip'] . '</a>)';
        }
        echo '<br/>' . $lang_common['Last post'] . ': ' . $last_post . '<br/>' . $karma . $posts_field . $files_field;
        if ($pun_user['g_id'] < PUN_GUEST) {
            echo '</div>
<div class="in">' . $lang_profile['Admin note'] . '<input type="text" name="admin_note" value="' . pun_htmlspecialchars($user['admin_note']) . '" maxlength="30" />';
        }
        echo '</div>
<div class="go_to">
<input type="submit" name="update" value="' . $lang_common['Submit'] . '" />
</div></form>';
    } else if ($_GET['section'] == 'personal') {
            if ($pun_user['g_set_title'] == 1) {
                $title_field = $lang_common['Title'] . ' (<em>' . $lang_profile['Leave blank'] . '</em>)<br /><input type="text" name="title" value="' . pun_htmlspecialchars($user['title']) . '" maxlength="50" /><br />';
            }


//profile personal
            $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section personal'];
            require_once PUN_ROOT . 'wap/header.php';

            wap_generate_profile_menu('personal');
            $birthday = explode('.', $user['birthday']);

echo '<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section personal'] . '</strong></div>
<form method="post" action="profile.php?section=personal&amp;id=' . $id . '">
<div class="input">
<strong>' . $lang_profile['Personal details legend'] . '</strong><br/>
<input type="hidden" name="form_sent" value="1" />' . $lang_profile['sex'] . '<br/>
<select name="form[sex]">';

            if ($user['sex'] == 1) {
                echo '<option value="1">' . $lang_profile['m'] . '</option><option value="0">' . $lang_profile['w'] . '</option>';
            } else {
                echo '<option value="0">' . $lang_profile['w'] . '</option><option value="1">' . $lang_profile['m'] . '</option>';
            }

            echo '</select><br/>
' . $lang_profile['birthday'] . '<br/>
<input type="text" value="' . $birthday[0] . '" name="day" title="' . $lang_profile['day'] . '" size="2" maxlength="2"/>.<input type="text" value="' . $birthday[1] . '" name="month" title="' . $lang_profile['month'] . '" size="2" maxlength="2"/>.<input type="text" value="' . $birthday[2] . '" name="year" title="' . $lang_profile['year'] . '" size="4" maxlength="4"/><br/>
' . $lang_profile['Realname'] . '<br />
<input type="text" name="form[realname]" value="' . pun_htmlspecialchars($user['realname']) . '" maxlength="40" /><br />' . @$title_field . $lang_profile['Location'] . '<br />
<input type="text" name="form[location]" value="' . pun_htmlspecialchars($user['location']) . '" maxlength="30" /><br />
' . $lang_profile['Website'] . '<br />
<input type="text" name="form[url]" value="' . pun_htmlspecialchars($user['url']) . '" maxlength="80" /></div>
<div class="go_to">
<input type="submit" name="update" value="' . $lang_common['Submit'] . '" /></div></form>';

        } else if ($_GET['section'] == 'messaging') {
//pagers
                $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section messaging'];
                require_once PUN_ROOT . 'wap/header.php';

                wap_generate_profile_menu('messaging');


echo '<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section messaging'] . '</strong></div>
<form method="post" action="profile.php?section=messaging&amp;id=' . $id . '">
<div class="input">
<strong>' . $lang_profile['Contact details legend'] . '</strong><br/>
<input type="hidden" name="form_sent" value="1" />
' . $lang_profile['Jabber'] . '<br />
<input type="text" name="form[jabber]" value="' . pun_htmlspecialchars($user['jabber']) . '" maxlength="75" /><br />
' . $lang_profile['ICQ'] . '<br />
<input type="text" name="form[icq]" value="' . $user['icq'] . '" maxlength="12" /><br />
' . $lang_profile['MSN'] . '<br />
<input type="text" name="form[msn]" value="' . pun_htmlspecialchars($user['msn']) . '" maxlength="50" /><br />
' . $lang_profile['AOL IM'] . '<br />
<input type="text" name="form[aim]" value="' . pun_htmlspecialchars($user['aim']) . '" maxlength="30" /><br />
' . $lang_profile['Yahoo'] . '<br />
<input type="text" name="form[yahoo]" value="' . pun_htmlspecialchars($user['yahoo']) . '" maxlength="30" /></div>
<div class="go_to"><input type="submit" name="update" value="' . $lang_common['Submit'] . '" /></div></form>';

            } else if ($_GET['section'] == 'personality') {
                    $avatar_field = '<a href="profile.php?action=upload_avatar&amp;id=' . $id . '">' . $lang_profile['Change avatar'] . '</a> |';
                    if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.gif')) {
                        $avatar_format = 'gif';
                    } else
                        if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.jpg')) {
                            $avatar_format = 'jpg';
                        } else
                            if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.png')) {
                                $avatar_format = 'png';
                            } else {
                                $avatar_field = '<a class="but" href="profile.php?action=upload_avatar&amp;id=' . $id . '">' . $lang_profile['Upload avatar'] . '</a>';
                            }

                            // Display the delete avatar link?
                            if ($img_size) {
                                $avatar_field .= ' <a href="profile.php?action=delete_avatar&amp;id=' . $id . '">' . $lang_profile['Delete avatar'] . '</a>';
                            }

                    if ($user['signature']) {
                        $signature_preview = $lang_profile['Sig preview'] . '<div class="hr">' . $parsed_signature . '</div>';
                    } else {
                        $signature_preview = $lang_profile['No sig'];
                    }
//avatar Signature
                    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section personality'];
                    require_once PUN_ROOT . 'wap/header.php';

                    wap_generate_profile_menu('personality');


echo '<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section personality'] . '</strong></div>
<form method="post" action="profile.php?section=personality&amp;id=' . $id . '">
<div class="input">
<input type="hidden" name="form_sent" value="1" />';
                    if ($pun_config['o_avatars'] == 1) {
                        echo '<strong>' . $lang_profile['Avatar legend'] . '</strong>
                        <div class="zag_in">';
                        if ($avatar_format) {
                            echo '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $id . '.' . $avatar_format . '" ' . $img_size[3] . ' alt="" />';
                        }
                        echo $lang_profile['Avatar info'] . '<br />
                        <input type="checkbox" name="form[use_avatar]" value="1"';
                        if ($user['use_avatar'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo ' />' . $lang_profile['Use avatar'] . '</div>
                        ' . $avatar_field . '</div>';
                    }
echo '<div class="input2"><strong>' . $lang_profile['Signature legend'] . '</strong><br/>
<span class="sub">' . $lang_profile['Signature info'] . '</span><br/>
' . $lang_profile['Sig max length'] . ': ' . $pun_config['p_sig_length'] . ' / ' . $lang_profile['Sig max lines'] . ': ' . $pun_config['p_sig_lines'] . '<br />
<textarea name="signature" rows="4" cols="24">' . pun_htmlspecialchars($user['signature']) . '</textarea><br />';
                    echo '<a href="help.php?id=3">' . $lang_common['Smilies'] . '</a> ';
                    if ($pun_config['o_smilies_sig']) {
                        echo '<span class="green">' . $lang_common['on_m'] . '</span>;';
                    } else {
                        echo '<span class="grey">' . $lang_common['off_m'] . '</span>;';
                    }
                    echo ' <a href="help.php?id=1">' . $lang_common['BBCode'] . '</a> ';
                    if ($pun_config['p_sig_bbcode']) {
                        echo '<span class="green">' . $lang_common['on_m'] . '</span>;';
                    } else {
                        echo '<span class="grey">' . $lang_common['off_m'] . '</span>;';
                    }
                    echo ' <a href="help.php?id=4">' . $lang_common['img tag'] . '</a> ';
                    if ($pun_config['p_sig_img_tag']) {
                        echo '<span class="green">' . $lang_common['on_m'] . '</span>';
                    } else {
                        echo '<span class="grey">' . $lang_common['off_m'] . '</span>';
                    }

                   
echo '</div>
<div class="input">' . $signature_preview . '</div>
<div class="go_to">
<input type="submit" name="update" value="' . $lang_common['Submit'] . '" /></div></form>';
                }
//profile-display
                 else if ($_GET['section'] == 'display') {
                        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section display'];
                        require_once PUN_ROOT . 'wap/header.php';

                        wap_generate_profile_menu('display');

echo '<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section display'] . '</strong></div>
<form method="post" action="profile.php?section=display&amp;id=' . $id . '">
<div class="input">
<input type="hidden" name="form_sent" value="1" />';


                        $styles = array();
                        $d = dir(PUN_ROOT . 'style_wap');
                        while (($entry = $d->read()) !== false) {
                            if (substr($entry, strlen($entry) - 4) == '.css') {
                                $styles[] = substr($entry, 0, strlen($entry) - 4);
                            }
                        }
                        $d->close();

                        // Only display the style selection box if there's more than one style available
                        switch (sizeof($styles)) {
                                //////////////
                            case 1:
                                echo '<input type="hidden" name="form[style_wap]" value="' . $styles[0] . '" />';
                                break;

                            default:
                                natsort($styles);

                                echo '<strong>' . $lang_profile['Style legend'] . '</strong><br/>
' . $lang_profile['Style info'] . '<br />
<select name="form[style_wap]">';


                                while (list(, $temp) = @each($styles)) {
                                    if ($user['style_wap'] == $temp) {
                                        echo '
                                        <option value="' . $temp . '" selected="selected">' . str_replace('_', ' ', $temp) . '</option>';
                                    } else {
                                        echo '
                                        <option value="' . $temp . '">' . str_replace('_', ' ', $temp) . '</option>';
                                    }
                                }

                                echo '</select></div>';

                                break;
                                ///////////
                        }

echo '<div class="input2">
<strong>' . $lang_profile['Post display legend'] . '</strong><br/>' . $lang_profile['Post display info'] . '<br/><input type="checkbox" name="form[show_smilies]" value="1"';
                        if ($user['show_smilies'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo ' />' . $lang_profile['Show smilies'] . '<br /><input type="checkbox" name="form[show_sig]" value="1"';
                        if ($user['show_sig'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo ' />' . $lang_profile['Show sigs'] . '<br />';
                        if ($pun_config['o_avatars'] == 1) {
                            echo '<input type="checkbox" name="form[show_avatars]" value="1"';
                            if ($user['show_avatars'] == 1) {
                                echo ' checked="checked"';
                            }
                            echo ' />' . $lang_profile['Show avatars'] . '<br />';
                        }
                        echo '<input type="checkbox" name="form[show_img]" value="1"';
                        if ($user['show_img'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo ' />' . $lang_profile['Show images'] . '<br /><input type="checkbox" name="form[show_img_sig]" value="1"';
                        if ($user['show_img_sig'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo ' />' . $lang_profile['Show images sigs'] . '</div>
<div class="input">
<strong>' . $lang_profile['Pagination legend'] . '</strong><br/>
' . $lang_profile['Topics per page'] . '<br />
<input type="text" name="form[disp_topics]" value="' . $user['disp_topics'] . '" maxlength="3" /><br />
' . $lang_profile['Posts per page'] . '<br />
<input type="text" name="form[disp_posts]" value="' . $user['disp_posts'] . '" maxlength="3" /><br />
' . $lang_profile['Paginate info'] . ' ' . $lang_profile['Leave blank'] . '</div>
<div class="input2">
<strong>' . $lang_profile['Mark as read legend'] . '</strong><br/>
' . $lang_profile['Mark as read after'] . '<br />
<input type="text" name="form[mark_after]" value="' . ($user['mark_after'] / 86400) . '" maxlength="3" /></div>
<div class="go_to">
<input type="submit" name="update" value="' . $lang_common['Submit'] . '" />
</div></form>
';
//profile-privacy
                    } else if ($_GET['section'] == 'privacy') {
                            $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section privacy'];
                            require_once PUN_ROOT . 'wap/header.php';

                            wap_generate_profile_menu('privacy');


echo '
<div class="con"><strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section privacy'] . '</strong></div>
<form method="post" action="profile.php?section=privacy&amp;id=' . $id . '">
<div class="input">
<strong>' . $lang_prof_reg['Privacy options legend'] . '</strong><br/>
<input type="hidden" name="form_sent" value="1" />
' . $lang_prof_reg['E-mail setting info'] . '<br/>
<input type="radio" name="form[email_setting]" value="0"';
                            if (!$user['email_setting']) {
                                echo ' checked="checked"';
                            }
                            echo ' />' . $lang_prof_reg['E-mail setting 1'] . '<br />
                            <input type="radio" name="form[email_setting]" value="1"';
                            if ($user['email_setting'] == 1) {
                                echo ' checked="checked"';
                            }
                            echo ' />' . $lang_prof_reg['E-mail setting 2'] . '<br />
                            <input type="radio" name="form[email_setting]" value="2"';
                            if ($user['email_setting'] == 2) {
                                echo ' checked="checked"';
                            }
echo ' />' . $lang_prof_reg['E-mail setting 3'] . '</div>
<div class="input2">' . $lang_prof_reg['Save user/pass info'] . '<br/>
<input type="checkbox" name="form[save_pass]" value="1"';
                            if ($user['save_pass'] == 1) {
                                echo ' checked="checked"';
                            }
echo ' />' . $lang_prof_reg['Save user/pass'] . '</div>
<div class="input2">' . $lang_profile['Notify full info'] . '<br/>
<input type="checkbox" name="form[notify_with_post]" value="1"';
                            if ($user['notify_with_post'] == 1) {
                                echo ' checked="checked"';
                            }
echo ' />' . $lang_profile['Notify full'] . '</div>
<div class="go_to"><input type="submit" name="update" value="' . $lang_common['Submit'] . '" /></div></form>
';
//Section admin
                        } else if ($_GET['section'] == 'admin') {
                                if ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_ban_users'])) {
                                    wap_message($lang_common['Bad request']);
                                }

                                $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_common['Profile'] . ' - ' . $lang_profile['Section admin'];
                                require_once PUN_ROOT . 'wap/header.php';

                                wap_generate_profile_menu('admin');


echo '<div class="con">
<strong>' . pun_htmlspecialchars($user['username']) . ' - ' . $lang_profile['Section admin'] . '</strong></div>
<form method="post" action="profile.php?section=admin&amp;id=' . $id . '&amp;action=foo">
<div class="input">
<input type="hidden" name="form_sent" value="1" />';

                                if ($pun_user['g_id'] == PUN_MOD) {
                                    echo '<strong>' . $lang_profile['Delete ban legend'] . '</strong><br/>
                                    <input type="submit" name="ban" value="' . $lang_profile['Ban user'] . '" /><br/>';
                                } else {
                                    if ($pun_user['id'] != $id) {
                                        echo '<strong>' . $lang_profile['Group membership legend'] . '</strong><br/>
                                        <select name="group_id">';

                                        $result = $db->query('SELECT `g_id`, `g_title` FROM `' . $db->prefix .
                                            'groups` WHERE `g_id`!=' . PUN_GUEST . ' ORDER BY `g_title`') or error('Unable to fetch user group list',
                                            __FILE__, __LINE__, $db->error());

                                        while ($cur_group = $db->fetch_assoc($result)) {
                                            if ($cur_group['g_id'] == $user['g_id'] || ($cur_group['g_id'] == $pun_config['o_default_user_group'] &&
                                                !$user['g_id'])) {
                                                echo '<option value="' . $cur_group['g_id'] . '" selected="selected">' .
                                                    pun_htmlspecialchars($cur_group['g_title']) . '</option>';
                                            } else {
                                                echo '<option value="' . $cur_group['g_id'] . '">' . pun_htmlspecialchars($cur_group['g_title']) . '</option>';
                                            }
                                        }


                                        echo '</select><input type="submit" name="update_group_membership" value="' . $lang_profile['Save'] . '" />';
                                    }

                                    echo '</div><div class="input2"> <strong>' . $lang_profile['Delete ban legend'] . '</strong><br/><input type="submit" name="delete_user" value="' . $lang_profile['Delete user'] . '" /> <input type="submit" name="ban" value="' . $lang_profile['Ban user'] . '" />';

                                    if ($user['g_id'] == PUN_MOD || $user['g_id'] == PUN_ADMIN) {
                                        echo '</div><div class="input"><strong>' . $lang_profile['Set mods legend'] . '</strong><br/>' . $lang_profile['Moderator in info'] . '<br/>';

                                        $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.moderators FROM ' .
                                            $db->prefix . 'categories AS c INNER JOIN ' . $db->prefix .
                                            'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or
                                            error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

                                        $cur_category = 0;
                                        while ($cur_forum = $db->fetch_assoc($result)) {
                                            if ($cur_forum['cid'] != $cur_category) // A new category since last iteration?
                                                {
                                                echo '<strong>' . $cur_forum['cat_name'] . '</strong><br/>';
                                                $cur_category = $cur_forum['cid'];
                                            }

                                            $moderators = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();

                                            echo '<input type="checkbox" name="moderator_in[' . $cur_forum['fid'] . ']" value="1"';
                                            if (in_array($id, $moderators)) {
                                                echo ' checked="checked"';
                                            }
                                            echo ' />' . pun_htmlspecialchars($cur_forum['forum_name']) . '<br />';
                                        }


                                        echo '</div><div class="go_to"><input type="submit" name="update_forums" value="' . $lang_profile['Update forums'] . '" />';

                                    }
                                }

                                echo '</div></form>';
                            }

    require_once PUN_ROOT . 'wap/footer.php';
}

?>
