<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT . 'lang/Russian/admin.php';


if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

if (isset($_POST['form_sent'])) {
//confirm_referrer('admin_permissions.php');

    $form = array_map('intval', $_POST['form']);

    while (list($key, $input) = @each($form)) {
// Only update values that have changed
        if (array_key_exists('p_' . $key, $pun_config) && $pun_config['p_' . $key] != $input) {
            $db->query('UPDATE ' . $db->prefix . 'config SET conf_value=' . $input . ' WHERE conf_name=\'p_' . $db->escape($key) . '\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
        }
    }

// Regenerate the config cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_config_cache();

    redirect('admin_permissions.php', $lang_admin['Updated'] . ' ' . $lang_admin['Redirect']);
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Permissions';
require_once PUN_ROOT . 'header.php';
generate_admin_menu('permissions');

?>
<div class="blockform">
    <h2><span><?php print $lang_admin['Permissions']; ?></span></h2>

    <div class="box">
        <form method="post" action="admin_permissions.php">
            <p class="submittop"><input type="submit" name="save" value="<?php print $lang_admin['Upd']; ?>"/></p>

            <div class="inform">
                <input type="hidden" name="form_sent" value="1"/>
                <fieldset>
                    <legend><?php print $lang_admin['Num posts']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">BBCode</th>
                                <td>
                                    <input type="radio" name="form[message_bbcode]"
                                           value="1"<?php if ($pun_config['p_message_bbcode'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[message_bbcode]"
                                                                                               value="0"<?php if (!$pun_config['p_message_bbcode']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions bbcode about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions img']; ?></th>
                                <td>
                                    <input type="radio" name="form[message_img_tag]"
                                           value="1"<?php if ($pun_config['p_message_img_tag'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[message_img_tag]"
                                                                                               value="0"<?php if (!$pun_config['p_message_img_tag']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions img about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions mess caps']; ?></th>
                                <td>
                                    <input type="radio" name="form[message_all_caps]"
                                           value="1"<?php if ($pun_config['p_message_all_caps'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[message_all_caps]"
                                                                                               value="0"<?php if (!$pun_config['p_message_all_caps']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions mess caps about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions theme caps']; ?></th>
                                <td>
                                    <input type="radio" name="form[subject_all_caps]"
                                           value="1"<?php if ($pun_config['p_subject_all_caps'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[subject_all_caps]"
                                                                                               value="0"<?php if (!$pun_config['p_subject_all_caps']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions theme caps about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions guest email']; ?></th>
                                <td>
                                    <input type="radio" name="form[force_guest_email]"
                                           value="1"<?php if ($pun_config['p_force_guest_email'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[force_guest_email]"
                                                                                               value="0"<?php if (!$pun_config['p_force_guest_email']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions guest email about']; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend><?php print $lang_admin['Sig']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">BBCode</th>
                                <td>
                                    <input type="radio" name="form[sig_bbcode]"
                                           value="1"<?php if ($pun_config['p_sig_bbcode'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[sig_bbcode]"
                                                                                               value="0"<?php if (!$pun_config['p_sig_bbcode']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions sig bbcode about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions img']; ?></th>
                                <td>
                                    <input type="radio" name="form[sig_img_tag]"
                                           value="1"<?php if ($pun_config['p_sig_img_tag'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[sig_img_tag]"
                                                                                               value="0"<?php if (!$pun_config['p_sig_img_tag']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions sig img about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions sig caps']; ?></th>
                                <td>
                                    <input type="radio" name="form[sig_all_caps]"
                                           value="1"<?php if ($pun_config['p_sig_all_caps'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[sig_all_caps]"
                                                                                               value="0"<?php if (!$pun_config['p_sig_all_caps']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions sig caps about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions sig len']; ?></th>
                                <td>
                                    <input type="text" name="form[sig_length]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['p_sig_length'] ?>"/>
                                    <span><?php print $lang_admin['Permissions sig len about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions sig lin']; ?></th>
                                <td>
                                    <input type="text" name="form[sig_lines]" size="3" maxlength="3"
                                           value="<?php echo $pun_config['p_sig_lines'] ?>"/>
                                    <span><?php print $lang_admin['Permissions sig lin about']; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend><?php print $lang_admin['Moderators']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions mod edit']; ?></th>
                                <td>
                                    <input type="radio" name="form[mod_edit_users]"
                                           value="1"<?php if ($pun_config['p_mod_edit_users'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[mod_edit_users]"
                                                                                               value="0"<?php if (!$pun_config['p_mod_edit_users']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions mod edit about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions mod rename']; ?></th>
                                <td>
                                    <input type="radio" name="form[mod_rename_users]"
                                           value="1"<?php if ($pun_config['p_mod_rename_users'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[mod_rename_users]"
                                                                                               value="0"<?php if (!$pun_config['p_mod_rename_users']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions mod rename about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions mod repass']; ?></th>
                                <td>
                                    <input type="radio" name="form[mod_change_passwords]"
                                           value="1"<?php if ($pun_config['p_mod_change_passwords'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[mod_change_passwords]"
                                                                                               value="0"<?php if (!$pun_config['p_mod_change_passwords']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions mod repass about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions mod ban']; ?></th>
                                <td>
                                    <input type="radio" name="form[mod_ban_users]"
                                           value="1"<?php if ($pun_config['p_mod_ban_users'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[mod_ban_users]"
                                                                                               value="0"<?php if (!$pun_config['p_mod_ban_users']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions mod ban about']; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend><?php print $lang_admin['Reg']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions email ban']; ?></th>
                                <td>
                                    <input type="radio" name="form[allow_banned_email]"
                                           value="1"<?php if ($pun_config['p_allow_banned_email'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[allow_banned_email]"
                                                                                               value="0"<?php if (!$pun_config['p_allow_banned_email']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions email ban about']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Permissions email double']; ?></th>
                                <td>
                                    <input type="radio" name="form[allow_dupe_email]"
                                           value="1"<?php if ($pun_config['p_allow_dupe_email'] == 1) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['Yes']; ?></strong> <input type="radio"
                                                                                               name="form[allow_dupe_email]"
                                                                                               value="0"<?php if (!$pun_config['p_allow_dupe_email']) echo ' checked="checked"' ?> />
                                    <strong><?php print $lang_admin['No']; ?></strong>
                                    <span><?php print $lang_admin['Permissions email double about']; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="save" value="<?php print $lang_admin['Upd']; ?>"/></p>
        </form>
    </div>
</div>
<div class="clearer"></div>
</div>
<?php
require_once PUN_ROOT . 'footer.php';
