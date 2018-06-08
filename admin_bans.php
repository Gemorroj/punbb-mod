<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT . 'lang/Russian/admin.php';


if ($pun_user['g_id'] > PUN_MOD || ($pun_user['g_id'] == PUN_MOD && !$pun_config['p_mod_ban_users'])) {
    message($lang_common['No permission']);
}


// Add/edit a ban (stage 1)
if (isset($_REQUEST['add_ban']) || isset($_GET['edit_ban'])) {

    if (isset($_GET['add_ban']) || isset($_POST['add_ban'])) {
        // If the id of the user to ban was provided through GET (a link from profile.php)
        if (isset($_GET['add_ban'])) {
            $add_ban = intval($_GET['add_ban']);
            if ($add_ban < 2) {
                message($lang_common['Bad request']);
            }

            $user_id = $add_ban;

            $result = $db->query('SELECT group_id, username, email FROM ' . $db->prefix . 'users WHERE id=' . $user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
            if ($db->num_rows($result)) {
                list($group_id, $ban_user, $ban_email) = $db->fetch_row($result);
            } else {
                message($lang_admin['bans_id_no']);
            }
        } else {
            // Otherwise the username is in POST
            $ban_user = trim($_POST['new_ban_user']);

            if ($ban_user) {
                $result = $db->query('SELECT id, group_id, username, email FROM ' . $db->prefix . 'users WHERE username=\'' . $db->escape($ban_user) . '\' AND id>1') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                if ($db->num_rows($result)) {
                    list($user_id, $group_id, $ban_user, $ban_email) = $db->fetch_row($result);
                } else {
                    message($lang_admin['bans_name_no']);
                }
            }
        }

        // Make sure we're not banning an admin
        if (isset($group_id) && $group_id == PUN_ADMIN) {
            message(pun_htmlspecialchars($ban_user) . ' - ' . $lang_admin['bans_admin']);
        }

        // If we have a $user_id, we can try to find the last known IP of that user
        if (isset($user_id)) {
            $result = $db->query('SELECT poster_ip FROM ' . $db->prefix . 'posts WHERE poster_id=' . $user_id . ' ORDER BY posted DESC LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
            $ban_ip = ($db->num_rows($result)) ? $db->result($result) : '';
        }

        $mode = 'add';
    } else {
        // We are editing a ban
        $ban_id = intval($_GET['edit_ban']);
        if ($ban_id < 1) {
            message($lang_common['Bad request']);
        }

        $result = $db->query('SELECT username, ip, email, message, expire FROM ' . $db->prefix . 'bans WHERE id=' . $ban_id) or error('Unable to fetch ban info', __FILE__, __LINE__, $db->error());
        if ($db->num_rows($result)) {
            list($ban_user, $ban_ip, $ban_email, $ban_message, $ban_expire) = $db->fetch_row($result);
        } else {
            message($lang_common['Bad request']);
        }

        $ban_expire = ($ban_expire) ? date('Y-m-d', $ban_expire) : '';
        $mode = 'edit';
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Bans';
    $focus_element = array('bans2', 'ban_user');
    require_once PUN_ROOT . 'header.php';

    generate_admin_menu('bans');

    echo '<div class="blockform">
<h2><span>' . $lang_admin['bans_more'] . '</span></h2>
<div class="box">
<form id="bans2" method="post" action="admin_bans.php?">
<div class="inform">
<input type="hidden" name="mode" value="' . $mode . '" />';

    if ($mode == 'edit') {
        echo '<input type="hidden" name="ban_id" value="' . $ban_id . '" />';
    }

    echo '<fieldset>
<legend>' . $lang_admin['bans_ip_mail'] . '</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">' . $lang_admin['Username'] . '</th>
<td>
<input type="text" name="ban_user" size="25" maxlength="25" value="' . pun_htmlspecialchars($ban_user) . '" />
<span>' . $lang_admin['bans_username'] . '</span>
</td>
</tr>
<tr>
<th scope="row">' . $lang_admin['IP'] . '</th>
<td>
<input type="text" name="ban_ip" size="45" maxlength="255" value="' . $ban_ip . '" />
<span>' . $lang_admin['bans_ip'];

    if ($ban_user && isset($user_id)) {
        echo ' <a href="admin_users.php?ip_stats=' . $user_id . '">Statisics IP</a>';
    }

    echo '</span>
</td>
</tr>
<tr>
<th scope="row">' . $lang_admin['bans_mail_domain'] . '</th>
<td>
<input type="text" name="ban_email" size="40" maxlength="50" value="' . strtolower($ban_email) . '" />
<span>' . $lang_admin['bans_mail_domain_about'] . '</span>
</td>
</tr>
</table>
<p class="topspace"><strong class="warntext">' . $lang_admin['bans_ip_warning'] . '</strong></p>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>' . $lang_admin['bans_mess'] . '</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">' . $lang_admin['bans_mess_ban'] . '</th>
<td>
<input type="text" name="ban_message" size="50" maxlength="255" value="' . pun_htmlspecialchars($ban_message) . '" />
<span>' . $lang_admin['bans_mess_ban_about'] . '</span>
</td>
</tr>
<tr>
<th scope="row">' . $lang_admin['bans_date'] . '</th>
<td>
<input type="text" name="ban_expire" size="17" maxlength="10" value="' . $ban_expire . '" />
<span>' . $lang_admin['bans_date_about'] . '</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="add_edit_ban" value="' . $lang_admin['Upd'] . '" /></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

    require_once PUN_ROOT . 'footer.php';
} else if (isset($_POST['add_edit_ban'])) {
    // Add/edit a ban (stage 2)
    //confirm_referrer('admin_bans.php');

    $ban_user = trim($_POST['ban_user']);
    $ban_ip = trim($_POST['ban_ip']);
    $ban_email = strtolower(trim($_POST['ban_email']));
    $ban_message = trim($_POST['ban_message']);
    $ban_expire = trim($_POST['ban_expire']);

    if (!$ban_user && !$ban_ip && !$ban_email) {
        message($lang_admin['bans_no']);
    } else if (strtolower($ban_user) == 'guest') {
        message($lang_admin['bans_guest']);
    }

    // Validate IP/IP range (it's overkill, I know)
    if ($ban_ip) {
        $ban_ip = preg_replace('/[\s]{2,}/', ' ', $ban_ip);
        $addresses = explode(' ', $ban_ip);
        $addresses = array_map('trim', $addresses);

        for ($i = 0, $all = count($addresses); $i < $all; ++$i) {
            $octets = explode('.', $addresses[$i]);

            for ($c = 0, $all2 = count($octets); $c < $all2; ++$c) {
                $octets[$c] = (strlen($octets[$c]) > 1) ? ltrim($octets[$c], '0') : $octets[$c];

                if ($c > 3 || preg_match('/[^0-9]/', $octets[$c]) || intval($octets[$c]) > 255) {
                    message($lang_admin['bans_fail_ip']);
                }
            }

            $cur_address = implode('.', $octets);
            $addresses[$i] = $cur_address;
        }

        $ban_ip = implode(' ', $addresses);
    }

    include PUN_ROOT . 'include/email.php';
    if ($ban_email && !is_valid_email($ban_email)) {
        if (!preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/', $ban_email)) {
            message($lang_admin['bans_fail_mail_domain']);
        }
    }

    if ($ban_expire && $ban_expire != 'Never') {
        $ban_expire = strtotime($ban_expire);

        if ($ban_expire == -1 || $ban_expire <= $_SERVER['REQUEST_TIME']) {
            message($lang_admin['bans_fail_date']);
        }
    } else {
        $ban_expire = 'NULL';
    }

    $ban_user = ($ban_user) ? '\'' . $db->escape($ban_user) . '\'' : 'NULL';
    $ban_ip = ($ban_ip) ? '\'' . $db->escape($ban_ip) . '\'' : 'NULL';
    $ban_email = ($ban_email) ? '\'' . $db->escape($ban_email) . '\'' : 'NULL';
    $ban_message = ($ban_message) ? '\'' . $db->escape($ban_message) . '\'' : 'NULL';

    if ($_POST['mode'] == 'add') {
        $db->query('INSERT INTO ' . $db->prefix . 'bans (username, ip, email, message, expire) VALUES(' . $ban_user . ', ' . $ban_ip . ', ' . $ban_email . ', ' . $ban_message . ', ' . $ban_expire . ')') or error('Unable to add ban', __FILE__, __LINE__, $db->error());
    } else {
        $db->query('UPDATE ' . $db->prefix . 'bans SET username=' . $ban_user . ', ip=' . $ban_ip . ', email=' . $ban_email . ', message=' . $ban_message . ', expire=' . $ban_expire . ' WHERE id=' . intval($_POST['ban_id'])) or error('Unable to update ban', __FILE__, __LINE__, $db->error());
    }

    // Regenerate the bans cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_bans_cache();

    redirect('admin_bans.php', $lang_admin['Updated'] . ' ' . $lang_admin['Redirect']);
} else if (isset($_GET['del_ban'])) {
    // Remove a ban
    //confirm_referrer('admin_bans.php');

    $ban_id = intval($_GET['del_ban']);
    if ($ban_id < 1) {
        message($lang_common['Bad request']);
    }

    $db->query('DELETE FROM ' . $db->prefix . 'bans WHERE id=' . $ban_id) or error('Unable to delete ban', __FILE__, __LINE__, $db->error());

    // Regenerate the bans cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_bans_cache();

    redirect('admin_bans.php', $lang_admin['Updated'] . ' ' . $lang_admin['Redirect']);
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Bans';
$focus_element = array('bans', 'new_ban_user');
require_once PUN_ROOT . 'header.php';

generate_admin_menu('bans');


echo '<div class="blockform">
<h2><span>' . $lang_admin['bans_new'] . '</span></h2>
<div class="box">
<form id="bans" method="post" action="admin_bans.php?action=more">
<div class="inform">
<fieldset>
<legend>' . $lang_admin['Add'] . '</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">' . $lang_admin['Username'] . '<div><input type="submit" name="add_ban" value="' . $lang_admin['Add'] . '" /></div></th>
<td>
<input type="text" name="new_ban_user" size="25" maxlength="25" />
<span>' . $lang_admin['bans_new_about'] . '</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
</form>
</div>
<h2 class="block2"><span>' . $lang_admin['bans_list'] . '</span></h2>
<div class="box">
<div class="fakeform">';


$result = $db->query('SELECT id, username, ip, email, message, expire FROM ' . $db->prefix . 'bans ORDER BY id') or error('Unable to fetch ban list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    while ($cur_ban = $db->fetch_assoc($result)) {
        $expire = format_time($cur_ban['expire'], true);

        echo '<div class="inform"><fieldset><legend>' . $lang_admin['bans_date_finish'] . ' ' . $expire . '</legend><div class="infldset"><table cellspacing="0">';

        if ($cur_ban['username']) {
            echo '<tr><th>' . $lang_admin['Username'] . '</th><td>' . pun_htmlspecialchars($cur_ban['username']) . '</td></tr>';
        }

        if ($cur_ban['email']) {
            echo '<tr><th>E-mail</th><td>' . $cur_ban['email'] . '</td></tr>';
        }

        if ($cur_ban['ip']) {
            echo '<tr><th>' . $lang_admin['IP'] . '</th><td>' . $cur_ban['ip'] . '</td></tr>';
        }

        if ($cur_ban['message']) {
            echo '<tr><th>' . $lang_admin['bans_a'] . '</th><td>' . pun_htmlspecialchars($cur_ban['message']) . '</td></tr>';
        }

        echo '</table><p class="linkactions"><a href="admin_bans.php?edit_ban=' . $cur_ban['id'] . '">' . $lang_admin['Modif'] . '</a> - <a href="admin_bans.php?del_ban=' . $cur_ban['id'] . '">' . $lang_admin['Del'] . '</a></p></div></fieldset></div>';
    }
} else {
    echo '<p>' . $lang_admin['bans_fail'] . '</p>';
}

echo '</div></div></div><div class="clearer"></div></div>';


require_once PUN_ROOT . 'footer.php';
