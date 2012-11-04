<?php

// Это utf-8
// Cookie stuff!
function check_cookie(&$pun_user)
{
    global $db, $pun_config, $cookie_name, $cookie_seed;

    $expire = time() + 31536000; // The cookie expires after a year

    // We assume it's a guest
    $cookie = array('user_id' => 1, 'password_hash' => 'Guest');

    // If a cookie is set, we get the user_id and password hash from it
    /*
    if (isset($_COOKIE[$cookie_name]) && preg_match('/a:2:{i:0;s:\d+:"(\d+)";i:1;s:\d+:"([0-9a-f]+)";}/', $_COOKIE[$cookie_name], $matches)) {
        list(, $cookie['user_id'], $cookie['password_hash']) = $matches;
    }
    */
    if (isset($_COOKIE[$cookie_name])) {
        list($cookie['user_id'], $cookie['password_hash']) = unserialize($_COOKIE[$cookie_name]);
    }

    if ($cookie['user_id'] > 1) {
        // Check if there's a user with the user ID and password hash from the cookie
        $result = $db->query('
            SELECT u.*, g.*, o.logged, o.idle
            FROM ' . $db->prefix . 'users AS u
            INNER JOIN ' . $db->prefix . 'groups AS g ON u.group_id=g.g_id
            LEFT JOIN ' . $db->prefix . 'online AS o ON o.user_id=u.id
            WHERE u.id=' . intval($cookie['user_id'])
        ) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
        $pun_user = $db->fetch_assoc($result);

        // If user authorisation failed
        if (!isset($pun_user['id']) || md5($cookie_seed . $pun_user['password']) !== $cookie['password_hash']) {
            pun_setcookie(1, md5(uniqid(mt_rand(), true)), $expire);
            set_default_user();

            return;
        }

        // Set a default language if the user selected language no longer exists
        if (!@file_exists(PUN_ROOT . 'lang/' . $pun_user['language'])) {
            $pun_user['language'] = $pun_config['o_default_lang'];
        }

        // Set a default style if the user selected style no longer exists
        if (!@file_exists(PUN_ROOT . 'style/' . $pun_user['style'] . '.css')) {
            $pun_user['style'] = $pun_config['o_default_style'];
        }

        // Set a default style if the user selected style no longer exists
        // if (!@file_exists(PUN_ROOT . 'style_wap/' . $pun_user['style_wap'] . '.css')) {
            // $pun_user['style_wap'] = $pun_config['o_default_style_wap'];
        // }
        if (!@is_file(PUN_ROOT . '/include/template/wap/' . $pun_user['style_wap'] . '/style.css')) {
            $pun_user['style_wap'] = $pun_config['o_default_style_wap'];
        }

        if (!$pun_user['disp_topics']) {
            $pun_user['disp_topics'] = $pun_config['o_disp_topics_default'];
        }

        if (!$pun_user['disp_posts']) {
            $pun_user['disp_posts'] = $pun_config['o_disp_posts_default'];
        }

        // Define this if you want this visit to affect the online list and the users last visit data
        if (!defined('PUN_QUIET_VISIT')) {
            // Update the online list
            if (!$pun_user['logged']) {
                $pun_user['logged'] = $_SERVER['REQUEST_TIME'];

                $db->query('REPLACE INTO ' . $db->prefix . 'online (user_id, ident, logged) VALUES(' . $pun_user['id'] . ', \'' . $db->escape($pun_user['username']) . '\', ' . $pun_user['logged'] . ')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
            } else {
                // Special case: We've timed out, but no other user has browsed the forums since we timed out
                if ($pun_user['logged'] < ($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_visit'])) {
                    $db->query('UPDATE ' . $db->prefix . 'users SET last_visit=' . $pun_user['logged'] . ' WHERE id=' . $pun_user['id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
                    $pun_user['last_visit'] = $pun_user['logged'];
                }

                $idle_sql = ($pun_user['idle'] == 1) ? ', idle=0' : '';
                $db->query('UPDATE ' . $db->prefix . 'online SET logged=' . $_SERVER['REQUEST_TIME'] . $idle_sql . ' WHERE user_id=' . $pun_user['id']) or error('Unable to update online list', __FILE__, __LINE__, $db->error());
            }
        }

        $pun_user['is_guest'] = false;
    } else {
        set_default_user();
    }
}


//
// Fill $pun_user with default values (for guests)
//
function set_default_user()
{
    global $db, $pun_user, $pun_config;
    $remote_addr = get_remote_address();

    // Fetch guest user
    $result = $db->query('SELECT u.*, g.*, o.logged FROM ' . $db->prefix . 'users AS u INNER JOIN ' . $db->prefix . 'groups AS g ON u.group_id=g.g_id LEFT JOIN ' . $db->prefix . 'online AS o ON o.ident=\'' . $remote_addr . '\' WHERE u.id=1') or error('Unable to fetch guest information', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        exit('Unable to fetch guest information. The table \'' . $db->prefix . 'users\' must contain an entry with id = 1 that represents anonymous users.');
    }

    $pun_user = $db->fetch_assoc($result);

    // Update online list
    if (!$pun_user['logged']) {
        $pun_user['logged'] = $_SERVER['REQUEST_TIME'];

        $db->query('REPLACE INTO ' . $db->prefix . 'online (user_id, ident, logged) VALUES(1, \'' . $db->escape($remote_addr) . '\', ' . $pun_user['logged'] . ')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
    } else {
        $db->query('UPDATE ' . $db->prefix . 'online SET logged=' . $_SERVER['REQUEST_TIME'] . ' WHERE ident=\'' . $db->escape($remote_addr) . '\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());
    }

    $pun_user['disp_topics'] = $pun_config['o_disp_topics_default'];
    $pun_user['disp_posts'] = $pun_config['o_disp_posts_default'];
    $pun_user['timezone'] = $pun_config['o_server_timezone'];
    $pun_user['language'] = $pun_config['o_default_lang'];
    $pun_user['style'] = $pun_config['o_default_style'];
    $pun_user['style_wap'] = $pun_config['o_default_style_wap'];
    $pun_user['is_guest'] = true;
}


//
// Set a cookie, PunBB style!
//
function pun_setcookie($user_id, $password_hash, $expire)
{
    global $cookie_name, $cookie_path, $cookie_domain, $cookie_secure, $cookie_seed;
    return setcookie($cookie_name, serialize(array($user_id, md5($cookie_seed . $password_hash))), $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
}


//
// Check whether the connecting user is banned (and delete any expired bans while we're at it)
//
function check_bans()
{
    global $db, $pun_config, $lang_common, $pun_user, $pun_bans;

    // Admins aren't affected
    if ($pun_user['g_id'] == PUN_ADMIN || !$pun_bans) {
        return;
    }

    // Add a dot at the end of the IP address to prevent banned address 192.168.0.5 from matching e.g. 192.168.0.50
    $user_ip = get_remote_address() . '.';
    $bans_altered = false;

    foreach ($pun_bans as $cur_ban) {
        // Has this ban expired?
        if ($cur_ban['expire'] && $cur_ban['expire'] <= $_SERVER['REQUEST_TIME']) {
            $db->query('DELETE FROM ' . $db->prefix . 'bans WHERE id=' . $cur_ban['id']) or error('Unable to delete expired ban', __FILE__, __LINE__, $db->error());
            $bans_altered = true;
            continue;
        }

        if ($cur_ban['username'] && !strcasecmp($pun_user['username'], $cur_ban['username'])) {
            $db->query('DELETE FROM ' . $db->prefix . 'online WHERE ident=\'' . $db->escape($pun_user['username']) . '\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
            message($lang_common['Ban message'] . ' ' . (($cur_ban['expire']) ? $lang_common['Ban message 2'] . ' ' . mb_strtolower(format_time($cur_ban['expire'], true)) . '. ' : '') . (($cur_ban['message']) ? $lang_common['Ban message 3'] . '<br /><br /><strong>' . pun_htmlspecialchars($cur_ban['message']) . '</strong><br /><br />' : '<br /><br />') . $lang_common['Ban message 4'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.', true);
        }

        if ($cur_ban['ip']) {
            $cur_ban_ips = explode(' ', $cur_ban['ip']);

            for ($i = 0, $all = sizeof($cur_ban_ips); $i < $all; ++$i) {
                $cur_ban_ips[$i] = $cur_ban_ips[$i] . '.';

                if (substr($user_ip, 0, strlen($cur_ban_ips[$i])) == $cur_ban_ips[$i]) {
                    $db->query('DELETE FROM ' . $db->prefix . 'online WHERE ident=\'' . $db->escape($pun_user['username']) . '\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
                    message($lang_common['Ban message'] . ' ' . (($cur_ban['expire']) ? $lang_common['Ban message 2'] . ' ' . mb_strtolower(format_time($cur_ban['expire'], true)) . '. ' : '') . (($cur_ban['message']) ? $lang_common['Ban message 3'] . '<br /><br /><strong>' . pun_htmlspecialchars($cur_ban['message']) . '</strong><br /><br />' : '<br /><br />') . $lang_common['Ban message 4'] . ' <a href="mailto:' . $pun_config['o_admin_email'] . '">' . $pun_config['o_admin_email'] . '</a>.', true);
                }
            }
        }
    }

    // If we removed any expired bans during our run-through, we need to regenerate the bans cache
    if ($bans_altered) {
        include_once PUN_ROOT . 'include/cache.php';
        generate_bans_cache();
    }
}


//
// Update "Users online"
//
function update_users_online()
{
    global $db, $pun_config, $pun_user;

    // Fetch all online list entries that are older than "o_timeout_online"
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'online WHERE logged<' . ($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_online'])) or error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
    while ($cur_user = $db->fetch_assoc($result)) {
        // If the entry is a guest, delete it
        if ($cur_user['user_id'] == 1) {
            $db->query('DELETE FROM ' . $db->prefix . 'online WHERE ident=\'' . $db->escape($cur_user['ident']) . '\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
        } else {
            // If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
            if ($cur_user['logged'] < ($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_visit'])) {
                $db->query('UPDATE ' . $db->prefix . 'users SET last_visit=' . $cur_user['logged'] . ' WHERE id=' . $cur_user['user_id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
                $db->query('DELETE FROM ' . $db->prefix . 'online WHERE user_id=' . $cur_user['user_id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
            } else {
                if (!$cur_user['idle']) {
                    $db->query('UPDATE ' . $db->prefix . 'online SET idle=1 WHERE user_id=' . $cur_user['user_id']) or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
                }
            }
        }
    }
}


//
// Generate the "navigator" that appears at the top of every page
//
function generate_navlinks()
{
    global $pun_config, $lang_common, $pun_user, $lang_pms;

    // Index and Userlist should always be displayed
    $links[] = '<li id="navindex"><a href="index.php">' . $lang_common['Index'] . '</a>';
    $links[] = '<li id="navuserlist"><a href="userlist.php">' . $lang_common['User list'] . '</a>';

    if ($pun_config['o_rules'] == 1) {
        $links[] = '<li id="navrules"><a href="misc.php?action=rules">' . $lang_common['Rules'] . '</a>';
    }
//-для гостя
    if ($pun_user['is_guest']) {
        if ($pun_user['g_search'] == 1) {
            $links[] = '<li id="navsearch"><a href="search.php">' . $lang_common['Search'] . '</a>';
        }

        if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
            $links[] = '<li id="nauploads"><a href="uploads.php">' . $lang_common['Uploader'] . '</a>';
        }

        $links[] = '<li id="navwap"><a href="wap/">' . $lang_common['WAP'] . '</a>';
        $links[] = '<li id="navregister"><a href="register.php">' . $lang_common['Register'] . '</a>';
        $links[] = '<li id="navlogin"><a href="login.php">' . $lang_common['Login'] . '</a>';

        $info = $lang_common['Not logged in'];
    } else {
        // PMS MOD BEGIN//для юзеров
        include PUN_ROOT . 'include/pms/functions_navlinks.php';

        if ($pun_user['g_id'] > PUN_MOD) {
            if ($pun_user['g_search'] == 1) {
                $links[] = '<li id="navsearch"><a href="search.php">' . $lang_common['Search'] . '</a>';
            }
            $links[] = '<li id="navprofile"><a href="profile.php?id=' . $pun_user['id'] . '">' . $lang_common['Profile'] . '</a>';

            if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
                $links[] = '<li id="navuploads"><a href="uploads.php">' . $lang_common['Uploader'] . '</a>';
            }

            $links[] = '<li id="navfilemap"><a href="filemap.php">' . $lang_common['Attachments'] . '</a>';
            $links[] = '<li id="navwap"><a href="wap/">' . $lang_common['WAP'] . '</a>';
            $links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id=' . $pun_user['id'] . '&amp;csrf_token=' . sha1($pun_user['id'] . sha1(get_remote_address())) . '">' . $lang_common['Logout'] . '</a>';
        } else { //для админов
            $links[] = '<li id="navsearch"><a href="search.php">' . $lang_common['Search'] . '</a>';
            $links[] = '<li id="navprofile"><a href="profile.php?id=' . $pun_user['id'] . '">' . $lang_common['Profile'] . '</a>';
            $links[] = '<li id="navadmin"><a href="admin_index.php">' . $lang_common['Admin'] . '</a>';
            $links[] = '<li id="navuploads"><a href="uploads.php">' . $lang_common['Uploader'] . '</a>';
            $links[] = '<li id="navfilemap"><a href="filemap.php">' . $lang_common['Attachments'] . '</a>';
            $links[] = '<li id="navwap"><a href="wap/">' . $lang_common['WAP'] . '</a>';
            $links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id=' . $pun_user['id'] . '&amp;csrf_token=' . sha1($pun_user['id'] . sha1(get_remote_address())) . '">' . $lang_common['Logout'] . '</a>';
        }

        // PMS MOD END
    }

    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = sizeof($extra_links[1]); $i < $all; ++$i) {
                array_splice($links, $extra_links[1][$i], 0, array('<li id="navextra' . ($i + 1) . '">' . $extra_links[2][$i]));
            }
        }
    }

    return '<ul>' . implode($lang_common['Link separator'] . '</li>', $links) . '</li></ul>';
}


function generate_wap_navlinks()
{
    global $pun_config, $lang_common, $pun_user, $lang_pms;


    // Index and Userlist should always be displayed
    $links['userlist.php'] = $lang_common['User list'];

    if ($pun_config['o_rules'] == 1) {
        $links['misc.php?action=rules'] = $lang_common['Rules'];
    }

    if ($pun_user['is_guest']) {
        if ($pun_user['g_search'] == 1) {
            $links['search.php'] = $lang_common['Search'];
        }

        if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
            $links['uploads.php'] = $lang_common['Uploader'];
        }

        $info = $lang_common['Not logged in'];
    } else {
        if ($pun_user['g_id'] > PUN_MOD) {
            if ($pun_user['g_search'] == 1) {
                $links['search.php'] = $lang_common['Search'];
            }

            if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
                $links['uploads.php'] = $lang_common['Uploader'];
            }

            $links['filemap.php'] = $lang_common['Attachments'];
        } else {
            $links['search.php'] = $lang_common['Search'];
            $links['uploads.php'] = $lang_common['Uploader'];
            $links['filemap.php'] = $lang_common['Attachments'];
        }
        // PMS MOD END
    }

    $out = array();
    foreach ($links as $k => $link) {
        $out[] = '<option value="' . $k . '">' . $link . '</option>';
    }


    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = sizeof($extra_links[1]); $i < $all; ++$i) {
                if (preg_match('!<a[^>]+href="?\'?([^ "\'>]+)"?\'?[^>]*>([^<>]*?)</a>!is', $extra_links[2][$i], $row)) {
                    array_splice($out, $extra_links[1][$i], 0, array('<option value="' . $row[1] . '">' . $row[2] . '</option>'));
                }
            }
        }
    }


    return '<form id="qjump" action="redirect.php" method="get"><div><select name="r" onchange="window.location.assign(\'' . $pun_config['o_base_url'] . '/wap/redirect.php?r=\'+this.options[this.selectedIndex].value)">' . implode('', $out) . '</select> <input type="submit" value="' . $lang_common['Go'] . '" accesskey="g" /></div></form>';
}


//верхняя Wap-навигация//редактировать в индексе
function generate_wap_1_navlinks()
{
    global $pun_config, $lang_common, $pun_user, $lang_pms;

    // Index and Userlist should always be displayed
    if ($pun_user['is_guest']) {
        //для гостя
        $links[] = '<a href="login.php">' . $lang_common['Login'] . '</a> ';
        $links[] = ' <a href="register.php">' . $lang_common['Register'] . '</a>';

        $info = $lang_common['Not logged in'];
    } else {
        if ($pun_user['g_id'] > PUN_MOD) {
            //для юзеров

            $links[] = '<a href="profile.php?id=' . $pun_user['id'] . '">' . $lang_common['Profile'] . ' (<span style="font-weight: bold">' . pun_htmlspecialchars($pun_user['username']) . '</span>)</a>';
            // PMS MOD BEGIN           
            include PUN_ROOT . 'include/pms/functions_wap_navlinks.php';
            // PMS MOD END
            $links[] = '<a href="login.php?action=out&amp;id=' . $pun_user['id'] . '&amp;csrf_token=' . sha1($pun_user['id'] . sha1(get_remote_address())) . '">' . $lang_common['Logout'] . '</a>';
        } else {
            //для админов

            $links[] = '<a href="profile.php?id=' . $pun_user['id'] . '">' . $lang_common['Profile'] . ' (<span style="font-weight: bold">' . pun_htmlspecialchars($pun_user['username']) . '</span>)</a>';
            // PMS MOD BEGIN
            include PUN_ROOT . 'include/pms/functions_wap_navlinks.php';
            // PMS MOD END
            $links[] = '<a href="../admin_index.php">' . $lang_common['Admin_m'] . '</a>';
            $links[] = '<a href="login.php?action=out&amp;id=' . $pun_user['id'] . '&amp;csrf_token=' . sha1($pun_user['id'] . sha1(get_remote_address())) . '">' . $lang_common['Logout'] . '</a>';
        }
    }

    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = sizeof($extra_links[1]); $i < $all; ++$i) {
                array_splice($links, $extra_links[1][$i], 0, array('' . ($i + 1) . '">' . $extra_links[2][$i]));
            }
        }
    }

    //сборка верхнего меню
    return implode($lang_common['Link separator'] . '|', $links);
}


//
// Display the profile navigation menu
//
function generate_profile_menu($page = '')
{
    global $lang_profile, $pun_config, $pun_user, $id;

    echo '<div id="profile" class="block2col">
<div class="blockmenu">
<h2><span>' . $lang_profile['Profile menu'] . '</span></h2>
<div class="box">
<div class="inbox">
<ul>
<li';
    if ($page == 'essentials') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=essentials&amp;id=' . $id . '">' . $lang_profile['Section essentials'] . '</a></li><li';
    if ($page == 'personal') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=personal&amp;id=' . $id . '">' . $lang_profile['Section personal'] . '</a></li><li';
    if ($page == 'messaging') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=messaging&amp;id=' . $id . '">' . $lang_profile['Section messaging'] . '</a></li><li';
    if ($page == 'personality') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=personality&amp;id=' . $id . '">' . $lang_profile['Section personality'] . '</a></li><li';
    if ($page == 'display') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=display&amp;id=' . $id . '">' . $lang_profile['Section display'] . '</a></li><li';
    if ($page == 'privacy') {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=privacy&amp;id=' . $id . '">' . $lang_profile['Section privacy'] . '</a></li>';
    if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && $pun_config['p_mod_ban_users'] == 1)) {
        echo '<li';
        if ($page == 'admin') {
            echo ' class="isactive"';
        }
        echo '><a href="profile.php?section=admin&amp;id=' . $id . '">' . $lang_profile['Section admin'] . '</a></li>';
    }
    echo '<li><a href="profile.php?id=' . $id . '&amp;preview=1">' . $lang_profile['Preview'] . '</a></li></ul></div></div></div>';

    return;
}


/**
 * Перенесено в файл: include/template/wap/{$theme}/tpls/profile.navi.tpl
 **
function wap_generate_profile_menu($page = '')
{
global $lang_profile, $pun_config, $pun_user, $id;

echo '<div class="navlinks">
<a href="profile.php?section=essentials&amp;id=' . $id . '">' . $lang_profile['Section essentials'] . '</a> |
<a href="profile.php?section=personal&amp;id=' . $id . '">' . $lang_profile['Section personal'] . '</a> |
<a href="profile.php?section=messaging&amp;id=' . $id . '">' . $lang_profile['Section messaging'] . '</a> |
<a href="profile.php?section=personality&amp;id=' . $id . '">' . $lang_profile['Section personality'] . '</a> |
<a href="profile.php?section=display&amp;id=' . $id . '">' . $lang_profile['Section display'] . '</a> |
<a href="profile.php?section=privacy&amp;id=' . $id . '">' . $lang_profile['Section privacy'] . '</a> |';

if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && $pun_config['p_mod_ban_users'] == 1)) {
echo ' <strong><a href="profile.php?section=admin&amp;id=' . $id . '">' . $lang_profile['Section admin'] . '</a></strong> |';
}

echo '<strong><a href="profile.php?preview=1&amp;id=' . $id . '">' . $lang_profile['Preview'] . '</a></strong></div>';

return;
}
 */

//
// Update posts, topics, last_post, last_post_id and last_poster for a forum
//
function update_forum($forum_id)
{
    global $db;

    $result = $db->query('SELECT COUNT(1), SUM(num_replies) FROM ' . $db->prefix . 'topics WHERE forum_id=' . $forum_id) or error('Unable to fetch forum topic count', __FILE__, __LINE__, $db->error());
    list($num_topics, $num_posts) = $db->fetch_row($result);

    $num_posts = $num_posts + $num_topics; // $num_posts is only the sum of all replies (we have to add the topic posts)

    $result = $db->query('SELECT last_post, last_post_id, last_poster FROM ' . $db->prefix . 'topics WHERE forum_id=' . $forum_id . ' AND moved_to IS NULL ORDER BY last_post DESC LIMIT 1') or error('Unable to fetch last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        // There are topics in the forum
        list($last_post, $last_post_id, $last_poster) = $db->fetch_row($result);

        $db->query('UPDATE ' . $db->prefix . 'forums SET num_topics=' . $num_topics . ', num_posts=' . $num_posts . ', last_post=' . $last_post . ', last_post_id=' . $last_post_id . ', last_poster=\'' . $db->escape($last_poster) . '\' WHERE id=' . $forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    } else {
        // There are no topics
        $db->query('UPDATE ' . $db->prefix . 'forums SET num_topics=' . $num_topics . ', num_posts=' . $num_posts . ', last_post=NULL, last_post_id=NULL, last_poster=NULL WHERE id=' . $forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    }
}

//
// Delete a topic and all of it's posts
//
function delete_topic($topic_id)
{
    global $db;

    // Delete the topic and any redirect topics
    $db->query('DELETE FROM ' . $db->prefix . 'topics WHERE id=' . $topic_id . ' OR moved_to=' . $topic_id) or error('Unable to delete topic', __FILE__, __LINE__, $db->error());

    // Create a list of the post ID's in this topic
    $post_ids = null;
    $result = $db->query('SELECT id FROM ' . $db->prefix . 'posts WHERE topic_id=' . $topic_id) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
    while ($row = $db->fetch_row($result)) {
        $post_ids .= ($post_ids) ? ',' . $row[0] : $row[0];
    }

    // Make sure we have a list of post ID's
    if ($post_ids) {
        strip_search_index($post_ids);

        // Delete attachments
        include_once PUN_ROOT . 'include/file_upload.php';
        delete_post_attachments($post_ids);

        // Delete posts in topic
        $db->query('DELETE FROM ' . $db->prefix . 'posts WHERE topic_id=' . $topic_id) or error('Unable to delete posts', __FILE__, __LINE__, $db->error());
    }

    // Delete any subscriptions for this topic
    $db->query('DELETE FROM ' . $db->prefix . 'subscriptions WHERE topic_id=' . $topic_id) or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());
}


//
// Delete a single post
//
function delete_post($post_id, $topic_id)
{
    global $db;

    $result = $db->query('SELECT `id`, `poster`, `posted` FROM `' . $db->prefix . 'posts` WHERE `topic_id` = ' . $topic_id . ' ORDER BY `id` DESC LIMIT 2') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    list($last_id, $poster,) = $db->fetch_row($result);
    list($second_last_id, $second_poster, $second_posted) = $db->fetch_row($result);

    // Delete the post
    $db->query('DELETE FROM `' . $db->prefix . 'posts` WHERE `id` = ' . $post_id) or error('Unable to delete post', __FILE__, __LINE__, $db->error());

    strip_search_index($post_id);

    include_once PUN_ROOT . 'include/file_upload.php';
    delete_post_attachments($post_id);

    // Count number of replies in the topic
    $result = $db->query('SELECT COUNT(1) FROM `' . $db->prefix . 'posts` WHERE `topic_id`=' . $topic_id) or error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
    $num_replies = $db->result($result, 0) - 1;

    // уменьшаем кол-во постов
    $db->query('UPDATE `' . $db->prefix . 'users` SET `num_posts` = `num_posts` - 1 WHERE `username` = "' . $db->escape($poster) . '" LIMIT 1');


    // If the message we deleted is the most recent in the topic (at the end of the topic)
    if ($last_id == $post_id) {
        // If there is a $second_last_id there is more than 1 reply to the topic
        if ($second_last_id) {
            $db->query('UPDATE `' . $db->prefix . 'topics` SET `last_post`=' . $second_posted . ', `last_post_id`=' . $second_last_id . ', `last_poster`=\'' . $db->escape($second_poster) . '\', `num_replies`=' . $num_replies . ' WHERE `id`=' . $topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
        } else {
            // We deleted the only reply, so now last_post/last_post_id/last_poster is posted/id/poster from the topic itself
            $db->query('UPDATE `' . $db->prefix . 'topics` SET `last_post`=posted, `last_post_id`=id, `last_poster`=poster, `num_replies`=' . $num_replies . ' WHERE `id`=' . $topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
        }
    } else {
        // Otherwise we just decrement the reply counter
        $db->query('UPDATE `' . $db->prefix . 'topics` SET `num_replies`=' . $num_replies . ' WHERE `id`=' . $topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
    }
}

//
// Replace censored words in $text
//
function censor_words($text)
{
    global $db;
    static $search_for, $replace_with;

    // If not already built in a previous call, build an array of censor words and their replacement text
    if (!$search_for) {
        $result = $db->query('SELECT search_for, replace_with FROM ' . $db->prefix . 'censoring') or error('Unable to fetch censor word list', __FILE__, __LINE__, $db->error());
        $num_words = $db->num_rows($result);

        $search_for = array();
        for ($i = 0; $i < $num_words; ++$i) {
            list($search_for[$i], $replace_with[$i]) = $db->fetch_row($result);
            // FIX UTF REGULAR EXPRESSIONS BUG BEGIN
            // ORIGINAL:
            // $search_for[$i] = '/\b('.str_replace('\*', '\w*?', preg_quote($search_for[$i], '/')).')\b/i';
            $search_for[$i] = '/(?<=^|\s)(' . str_replace('\*', '[' . ALPHANUM . ']*?', preg_quote($search_for[$i], '/')) . ')(?=$|\s)/iu';
            // FIX UTF REGULAR EXPRESSIONS BUG END
        }
    } else {
        $text = substr(preg_replace($search_for, $replace_with, ' ' . $text . ' '), 1, -1);
    }

    return $text;
}


//
// Determines the correct title for $user
// $user must contain the elements 'username', 'title', 'posts', 'g_id' and 'g_user_title'
//
function get_title($user)
{
    global $db, $pun_config, $pun_bans, $lang_common;
    static $ban_list, $pun_ranks;

    // If not already built in a previous call, build an array of lowercase banned usernames
    if (!$ban_list) {
        $ban_list = array();

        foreach ($pun_bans as $cur_ban) {
            $ban_list[] = mb_strtolower($cur_ban['username']);
        }
    }

    // If not already loaded in a previous call, load the cached ranks
    if ($pun_config['o_ranks'] == 1 && !$pun_ranks) {
        @include PUN_ROOT . 'cache/cache_ranks.php';
        if (!defined('PUN_RANKS_LOADED')) {
            include_once PUN_ROOT . 'include/cache.php';
            generate_ranks_cache();
            include PUN_ROOT . 'cache/cache_ranks.php';
        }
    }


    if ($user['title']) {
        // If the user has a custom title
        $user_title = pun_htmlspecialchars($user['title']);
    } else if (in_array(mb_strtolower(@$user['username']), $ban_list)) {
        // If the user is banned
        $user_title = $lang_common['Banned'];
    } else if ($user['g_user_title']) {
        // If the user group has a default user title
        $user_title = pun_htmlspecialchars($user['g_user_title']);
    } else if ($user['g_id'] == PUN_GUEST) {
        // If the user is a guest
        $user_title = $lang_common['Guest'];
    } else {
        // Are there any ranks?
        if ($pun_config['o_ranks'] == 1 && $pun_ranks) {
            @reset($pun_ranks);
            while (list(, $cur_rank) = @each($pun_ranks)) {
                if (intval($user['num_posts']) >= $cur_rank['min_posts']) {
                    $user_title = pun_htmlspecialchars($cur_rank['rank']);
                }
            }
        }

        // If the user didn't "reach" any rank (or if ranks are disabled), we assign the default
        if (!$user_title) {
            $user_title = $lang_common['Member'];
        }
    }

    return $user_title;
}


//
// Generate a string with numbered links (for multipage scripts)
//
function paginate($num_pages, $cur_page, $link_to)
{
    /// MOD VIEW ALL PAGES IN ONE BEGIN
    global $lang_common;

    $active_all = true;

    // If $cur_page > $num_pages, we show link to all pages
    if ($cur_page > $num_pages) {
        $active_all = false;
        $link_to_all = true;
        $cur_page--;
    }
    /// MOD VIEW ALL PAGES IN ONE END

    $pages = array();
    $link_to_all = false;

    // If $cur_page == -1, we link to all pages (used in viewforum.php)
    if ($cur_page == -1) {
        $cur_page = 1;
        $link_to_all = true;
    }

    if ($num_pages <= 1) {
        $pages = array('<strong>1</strong>');
    } else {
        if ($cur_page > 3) {
            $pages[] = '<a href="' . $link_to . '&amp;p=1">1</a>';
            if ($cur_page != 4) {
                $pages[] = '&#x2026;';
            }
        }

        // Don't ask me how the following works. It just does, OK? :-)
        for ($current = $cur_page - 2, $stop = $cur_page + 3; $current < $stop; ++$current) {
            if ($current < 1 || $current > $num_pages) {
                continue;
            } else if ($current != $cur_page || $link_to_all) {
                $pages[] = '<a href="' . $link_to . '&amp;p=' . $current . '">' . $current . '</a>';
            } else {
                $pages[] = '<strong>' . $current . '</strong>';
            }
        }

        if ($cur_page <= ($num_pages - 3)) {
            if ($cur_page != ($num_pages - 3)) {
                $pages[] = '&#x2026;';
            }

            $pages[] = '<a href="' . $link_to . '&amp;p=' . $num_pages . '">' . $num_pages . '</a>';
        }

        /// MOD VIEW ALL PAGES IN ONE BEGIN
        if (!$active_all) {
            $pages[] = $lang_common['All'];
        } else {
            $pages[] = '<a href="' . $link_to . '&amp;action=all">' . $lang_common['All'] . '</a>';
        }
        /// MOD VIEW ALL PAGES IN ONE END
    }

    return implode(' ', $pages);
}


//
// Display a message
//
function message($message, $no_back_link = false)
{
    global $db, $pun_user, $lang_common, $pun_config, $pun_start, $tpl_main;

    if (!defined('PUN_HEADER')) {
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Info'];
        require_once PUN_ROOT . 'header.php';
    }

    echo '<div id="msg" class="block">
<h2><span>' . $lang_common['Info'] . '</span></h2>
<div class="box">
<div class="inbox">
<p>' . $message . '</p>';
    if (!$no_back_link) {
        echo '<p><a href="javascript:history.go(-1)">' . $lang_common['Go back'] . '</a></p>';
    }
    echo '</div></div></div>';

    require_once PUN_ROOT . 'footer.php';
    exit;
}


function wap_message($message, $no_back_link = false)
{
    global $db, $pun_user, $lang_common, $pun_config, $pun_start, $tpl_main, $smarty;

    if (!defined('PUN_HEADER')) {
        require_once PUN_ROOT . 'wap/header.php';
    }
    
    if (! isset($page_title)) {
        
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_common['Info'];
    }
    
    $smarty->assign('page_title', $page_title);
    $smarty->assign('message', $message);
    $smarty->assign('pun_user', $pun_user);
    $smarty->assign('no_back_link', $no_back_link);
    $smarty->display('message.tpl');
    exit;
}


//
// Format a time string according to $time_format and timezones
//
function format_time($timestamp, $date_only = false)
{
    global $pun_config, $lang_common, $pun_user;

    if (!$timestamp) {
        return $lang_common['Never'];
    }

    $diff = ($pun_user['timezone'] - $pun_config['o_server_timezone']) * 3600;
    $timestamp += $diff;

    $date = date($pun_config['o_date_format'], $timestamp);
    $today = date($pun_config['o_date_format'], $_SERVER['REQUEST_TIME'] + $diff);
    $yesterday = date($pun_config['o_date_format'], $_SERVER['REQUEST_TIME'] + $diff - 86400);

    if ($date == $today) {
        $date = $lang_common['Today'];
    } else if ($date == $yesterday) {
        $date = $lang_common['Yesterday'];
    }

    if (!$date_only) {
        return $date . ' ' . date($pun_config['o_time_format'], $timestamp);
    } else {
        return $date;
    }
}


//
// Make sure that HTTP_REFERER matches $pun_config['o_base_url']/$script
//
function confirm_referrer($script)
{
    global $pun_config, $lang_common, $_SERVER;

    if (!preg_match('#^' . preg_quote(str_ireplace('www.', '', $pun_config['o_base_url']) . '/' . $script, '#') . '#i', str_ireplace('www.', '', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')))) {
        message($lang_common['Bad referrer']);
    }
}

//
// Generate a random password of length $len
//
function random_pass($len)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $password = null;
    for ($i = 0; $i < $len; ++$i) {
        $password .= substr($chars, (mt_rand() % strlen($chars)), 1);
    }

    return $password;
}


//
// Compute a hash of $str
// Uses sha1()
function pun_hash($str)
{
    if (function_exists('sha1')) {
        return sha1($str);
    } else if (function_exists('mhash')) {
        return bin2hex(mhash(MHASH_SHA1, $str));
    } else {
        return md5($str);
    }
}


//
// Try to determine the correct remote IP-address
function get_remote_address()
{
    return $_SERVER['REMOTE_ADDR'];
}


//
// Equivalent to htmlspecialchars(), but allows &#[0-9]+ (for unicode)
//
function pun_htmlspecialchars($str)
{
    return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str));
}


//
// Convert \r\n and \r to \n
function pun_linebreaks($str)
{
    return str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
}


//
// A more aggressive version of trim()
function pun_trim($str)
{
    // UTF-8
    //return preg_replace('/(^\s+)|(\s+$)/us', '', $str);
    return trim($str);
}


function pun_show_avatar()
{
    global $pun_config, $pun_user, $cur_post;

    $user_avatar = '';
    if ($pun_config['o_avatars'] == 1 && $cur_post['use_avatar'] == 1 && $pun_user['show_avatars']) {
        if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.gif')) {
            $user_avatar = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.gif" alt="" />';
        } else if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.jpg')) {
            $user_avatar = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.jpg" alt="" />';
        } else if ($img_size = @getimagesize(PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.png')) {
            $user_avatar = '<img src="' . PUN_ROOT . $pun_config['o_avatars_dir'] . '/' . $cur_post['poster_id'] . '.png" alt="" />';
        }
    }

    return $user_avatar;
}


//
// Display a message when board is in maintenance mode
//
function maintenance_message()
{
    global $db, $pun_config, $lang_common, $pun_user;

    // Deal with newlines, tabs and multiple spaces
    $message = str_replace(array("\t", ' ', ' '), array('&#160; &#160; ', '&#160; ', ' &#160;'), $pun_config['o_maintenance_message']);


    // Load the maintenance template
    $tpl_maint = trim(file_get_contents(PUN_ROOT . 'include/template/maintenance.tpl'));


    // START SUBST - <pun_include "*">
    while (preg_match('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_maint, $cur_include)) {
        if (!file_exists(PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2])) {
            error('Unable to process user include ' . htmlspecialchars($cur_include[0]) . ' from template maintenance.tpl. There is no such file in folder /include/user/', __FILE__, __LINE__);
        }

        ob_start();
        include PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2];
        $tpl_temp = ob_get_contents();
        $tpl_maint = str_replace($cur_include[0], $tpl_temp, $tpl_maint);
        ob_end_clean();
    }
    // END SUBST - <pun_include "*">

    // START SUBST - <pun_content_direction>
    $tpl_maint = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_maint);
    // END SUBST - <pun_content_direction>


    // START SUBST - <pun_head>
    ob_start();

    echo '<title>' . pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Maintenance'] . '</title><link rel="stylesheet" type="text/css" href="' . PUN_ROOT . 'style/' . $pun_user['style'] . '.css" />';

    $tpl_temp = trim(ob_get_contents());
    $tpl_maint = str_replace('<pun_head>', $tpl_temp, $tpl_maint);
    ob_end_clean();
    // END SUBST - <pun_head>


    // START SUBST - <pun_maint_heading>
    $tpl_maint = str_replace('<pun_maint_heading>', $lang_common['Maintenance'], $tpl_maint);
    // END SUBST - <pun_maint_heading>


    // START SUBST - <pun_maint_message>
    $tpl_maint = str_replace('<pun_maint_message>', $message, $tpl_maint);
    // END SUBST - <pun_maint_message>


    // End the transaction
    $db->end_transaction();

    // Close the db connection (and free up any result data)
    $db->close();

    exit($tpl_maint);
}


//
// Display $message and redirect user to $destination_url
//
function redirect($destination_url, $message = '', $redirect_code = 302)
{
    global $db, $pun_config, $lang_common, $pun_user;

    // Prefix with o_base_url (unless there's already a valid URI)
    if (strpos($destination_url, 'http://') !== 0 && strpos($destination_url, 'https://') !== 0 && strpos($destination_url, 'ftp://') !== 0 && strpos($destination_url, 'ftps://') !== 0 && strpos($destination_url, '/') !== 0) {
        $destination_url = $pun_config['o_base_url'] . '/' . $destination_url;
    }

    // Do a little spring cleaning
    $destination_url = preg_replace('/([\r\n])|(%0[ad])|(;[\s]*data[\s]*:)/i', '', $destination_url);

    // If the delay is 0 seconds, we might as well skip the redirect all together
    if (!$pun_config['o_redirect_delay'] || !$message) {
        header('Location: ' . str_replace('&amp;', '&', $destination_url), true, $redirect_code);
        exit;
    }

    // Load the redirect template
    $tpl_redir = trim(file_get_contents(PUN_ROOT . 'include/template/redirect.tpl'));


    // START SUBST - <pun_include "*">
    while (preg_match('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_redir, $cur_include)) {
        if (!file_exists(PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2])) {
            error('Unable to process user include ' . htmlspecialchars($cur_include[0]) . ' from template redirect.tpl. There is no such file in folder /include/user/', __FILE__, __LINE__);
        }

        ob_start();
        include PUN_ROOT . 'include/user/' . $cur_include[1] . '.' . $cur_include[2];
        $tpl_temp = ob_get_contents();
        $tpl_redir = str_replace($cur_include[0], $tpl_temp, $tpl_redir);
        ob_end_clean();
    }
    // END SUBST - <pun_include "*">


    // START SUBST - <pun_content_direction>
    $tpl_redir = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_redir);
    // END SUBST - <pun_content_direction>


    // START SUBST - <pun_head>
    ob_start();


    echo '<meta http-equiv="refresh" content="' . $pun_config['o_redirect_delay'] . '; url=' . str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $destination_url) . '" />
<title>' . pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_common['Redirecting'] . '</title>
<link rel="stylesheet" type="text/css" href="style/' . $pun_user['style'] . '.css" />';


    $tpl_temp = trim(ob_get_contents());
    $tpl_redir = str_replace('<pun_head>', $tpl_temp, $tpl_redir);
    ob_end_clean();
    // END SUBST - <pun_head>


    // START SUBST - <pun_redir_heading>
    $tpl_redir = str_replace('<pun_redir_heading>', $lang_common['Redirecting'], $tpl_redir);
    // END SUBST - <pun_redir_heading>


    // START SUBST - <pun_redir_text>
    $tpl_temp = $message . '<br /><br />' . '<a href="' . $destination_url . '">' . $lang_common['Click redirect'] . '</a>';
    $tpl_redir = str_replace('<pun_redir_text>', $tpl_temp, $tpl_redir);
    // END SUBST - <pun_redir_text>


    // START SUBST - <pun_footer>
    ob_start();

    // End the transaction
    $db->end_transaction();

    // Display executed queries (if enabled)
    if (defined('PUN_SHOW_QUERIES')) {
        display_saved_queries();
    }

    $tpl_temp = trim(ob_get_contents());
    $tpl_redir = str_replace('<pun_footer>', $tpl_temp, $tpl_redir);
    ob_end_clean();
    // END SUBST - <pun_footer>


    // Close the db connection (and free up any result data)
    $db->close();
    header('Content-Type: text/html; charset=UTF-8');
    exit($tpl_redir);
}


function wap_redirect($destination_url, $redirect_code = 301)
{
    // Prefix with o_base_url (unless there's already a valid URI)
    if (strpos($destination_url, 'http://') !== 0 && strpos($destination_url, 'https://') !== 0 && strpos($destination_url, 'ftp://') !== 0 && strpos($destination_url, 'ftps://') !== 0 && strpos($destination_url, '/') !== 0) {
        //echo $destination_url . "\n";
        $destination_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $destination_url;
        //echo $destination_url;
    }

    header('Location: ' . $destination_url, true, $redirect_code);
    exit;
}


//
// Display a simple error message
//
function error($message, $file, $line, $db_error = array())
{
    global $pun_config;

    // Set a default title if the script failed before $pun_config could be populated
    if (!$pun_config) {
        $pun_config['o_board_title'] = 'PunBB mod Gemorroj';
    }

    // Empty output buffer and stop buffering
    @ob_end_clean();

    // "Restart" output buffering if we are using ob_gzhandler (since the gzip header is already sent)
    if ($pun_config['o_gzip'] && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false)) {
        ob_start('ob_gzhandler');
    }

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>' . pun_htmlspecialchars($pun_config['o_board_title']) . ' / Error</title>
<style type="text/css">
body {margin: 10% 20% auto 20%; font: 10px Verdana, Arial, Helvetica, sans-serif}
#errorbox {border: 1px solid #B84623}
h2 {margin: 0; color: #FFFFFF; background-color: #B84623; font-size: 1.1em; padding: 5px 4px}
#errorbox div {padding: 6px 5px; background-color: #F1F1F1}
</style>
</head>
<body>
<div id="errorbox">
<h2>An error was encountered</h2>
<div>';


    if (defined('PUN_DEBUG')) {
        echo '<strong>File:</strong> ' . $file . '<br /><strong>Line:</strong> ' . $line . '<br /><br /><strong>PunBB reported</strong>: ' . $message;

        if ($db_error) {
            echo '<br /><br /><strong>Database reported:</strong> ' . pun_htmlspecialchars($db_error['error_msg']) . (($db_error['error_no']) ? ' (Errno: ' . $db_error['error_no'] . ')' : '');

            if ($db_error['error_sql']) {
                echo '<br /><br /><strong>Failed query:</strong> ' . pun_htmlspecialchars($db_error['error_sql']);
            }
        }
    } else {
        echo 'Error: <strong>' . $message . '.</strong>';
    }

    echo '</div>
</div>
</body>
</html>';

    // If a database connection was established (before this error) we close it
    if ($db_error) {
        $GLOBALS['db']->close();
    }

    exit;
}

// DEBUG FUNCTIONS BELOW

//
// Display executed queries (if enabled)
//
function display_saved_queries()
{
    global $db, $lang_common;

    // Get the queries so that we can print them out
    $saved_queries = $db->get_saved_queries();

    echo '<div id="debug" class="blocktable">
<h2><span>' . $lang_common['Debug table'] . '</span></h2>
<div class="box">
<div class="inbox">
<table cellspacing="0">
<thead>
<tr>
<th class="tcl" scope="col">Time (s)</th>
<th class="tcr" scope="col">Query</th>
</tr>
</thead>
<tbody>';

    $query_time_total = 0.0;
    while (list(, $cur_query) = @each($saved_queries)) {
        $query_time_total += $cur_query[1];
        echo '<tr><td class="tcl">' . (($cur_query[1]) ? $cur_query[1] : ' ') . '</td><td class="tcr">' . pun_htmlspecialchars($cur_query[0]) . '</td></tr>';
    }
    echo '<tr>
<td class="tcl" colspan="2">Total query time: ' . $query_time_total . ' s</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>';

}


//
// Dump contents of variable(s)
//
function dump()
{
    echo '<pre>';

    $num_args = func_num_args();

    for ($i = 0; $i < $num_args; ++$i) {
        print_r(func_get_arg($i));
        echo "\n\n";
    }

    echo '</pre>';
    exit;
}


// MOD CONVENIENT FORUM URL BEGIN

function convert_forum_url(&$text)
{
    global $db, $pun_config;

    function replace($query, $pattern, $text)
    {
        global $db;
        if (preg_match_all($pattern, $text, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $pid) {
                $result = $db->query($query . $pid[1]) or error('Unable execute query for convert urls', __FILE__, __LINE__, $db->error());
                if ($result) {
                    $subject = $db->result($result);
                    $text = preg_replace('/(?<=^|\s)' . str_replace('/', '\/', str_replace('?', '\?', str_replace('.', '\.', $pid[0]))) . '\b/', '[url=' . $pid[0] . ']' . $subject . '[/url]', $text, 1);
                }
            }
        }
        return $text;
    }

    // Convert viewtopic
    $url = str_replace('/', '\/', str_replace('.', '\.', $pun_config['o_base_url'] . '/viewtopic.php\?'));
    $text = replace('SELECT t.subject FROM ' . $db->prefix . 'posts AS p INNER JOIN ' . $db->prefix . 'topics AS t ON t.id = p.topic_id WHERE p.id=', '/(?<=^|\s)' . $url . 'pid=([0-9]+)#p[0-9]+\b/', $text);
    $text = replace('SELECT subject FROM ' . $db->prefix . 'topics WHERE id=', '/(?<=^|\s)' . $url . 'id=([0-9]+)\b/', $text);

    // Convert profile
    $url = str_replace('/', '\/', str_replace('.', '\.', $pun_config['o_base_url'] . '/profile.php\?'));
    $text = replace('SELECT username FROM ' . $db->prefix . 'users WHERE id=', '/(?<=^|\s)' . $url . 'id=([0-9]+)\b/', $text);

    // Convert viewforum
    $url = str_replace('/', '\/', str_replace('.', '\.', $pun_config['o_base_url'] . '/viewforum.php\?'));
    $text = replace('SELECT forum_name FROM ' . $db->prefix . 'forums WHERE id=', '/(?<=^|\s)' . $url . 'id=([0-9]+)\b/', $text);
}

// MOD CONVENIENT FORUM URL END


function clear_empty_multiline($text)
{
    return preg_replace("/\n\n\n+/m", "\n\n", $text);
}


function generate_rss()
{
    global $db, $pun_config;

    // for wap parser
    if (!function_exists('parse_message')) {
        include_once PUN_ROOT . 'include/parser.php';
    }

    $rss = fopen(PUN_ROOT . 'rss.xml', 'wb');

    fputs($rss, '<?xml version="1.0" encoding="utf-8"?>' . "\r\n" .
        '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' .
        '<channel>' .
        '<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" href="' . $pun_config['o_base_url'] . '/rss.xml" type="application/rss+xml" />' .
        '<title>' . $pun_config['o_board_title'] . '</title>' .
        '<link>' . $pun_config['o_base_url'] . '</link>' .
        '<description>' . $pun_config['o_board_desc'] . '</description>' .
        '<pubDate>' . date('r') . '</pubDate>' .
        '<generator>RSS Generator</generator>' . "\r\n");

    //$onlysubforum = 'WHERE t.forum_id=1'; //do not delete
    $onlysubforum = '';

    $result = $db->query('
        SELECT t.id, t.poster, t.subject, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_replies, p.message, p.poster, g.forum_name, g.id as forum_id
        FROM ' . $db->prefix . 'topics AS t
        INNER JOIN ' . $db->prefix . 'posts AS p ON p.topic_id=t.id ' . $onlysubforum . '
        LEFT JOIN ' . $db->prefix . 'forums AS g ON t.forum_id=g.id
        GROUP BY p.topic_id
        ORDER BY posted DESC LIMIT 0, 10
    ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result)) {
        while ($cur_topic = $db->fetch_assoc($result)) {
            fputs($rss, '<item>' .
                '<title>' . $cur_topic['subject'] . '</title>' .
                '<link>' . $pun_config['o_base_url'] . '/viewtopic.php?id=' . $cur_topic['id'] . '</link>' .
                '<comments>' . $pun_config['o_base_url'] . '/viewtopic.php?pid=' . $cur_topic['last_post_id'] . '#p' . $cur_topic['last_post_id'] . '</comments>' .
                '<pubDate>' . date('r', $cur_topic['posted']) . '</pubDate>' .
                '<dc:creator>' . $cur_topic['poster'] . '</dc:creator>' .
                '<category>' . $cur_topic['forum_name'] . '</category>' .
                '<guid>' . $pun_config['o_base_url'] . '/viewforum.php?id=' . $cur_topic['forum_id'] . '&amp;' . mt_rand() . '</guid>' .
                '<description><![CDATA[' . parse_message($cur_topic['message'], 1) . ']]></description>' .
                '</item>' . "\r\n");
        }
    }

    fputs($rss, '</channel></rss>');
    fclose($rss);
}


function vote($to = 0, $vote = 1)
{
    global $db, $pun_user;

    $vote = (($vote == 1) ? 1 : -1);
    $q = $db->query('SELECT 1 FROM `' . $db->prefix . 'karma` WHERE `id`=' . $pun_user['id'] . ' AND `to`=' . intval($to)) or error('Error', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($q)) {
        message('Error');
    }

    return $db->query('INSERT INTO `' . $db->prefix . 'karma` SET `id`=' . $pun_user['id'] . ', `to`=' . intval($to) . ', `vote`="' . $vote . '", `time`=' . $_SERVER['REQUEST_TIME']) or error('Error', __FILE__, __LINE__, $db->error());
}


class getf
{
    // содержимое
    public $data;
    // имя
    public $file;
    // mime
    public $mime;
    // кодировка
    public $charset;
    // аттач
    public $attach;

    public function mime($file)
    {
        // если есть Fileinfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mime = finfo_file($finfo, $file);
            finfo_close($finfo);
        }
        // если нет, тавим MIME в зависимости от расширения
        if (!$this->mime) {
            $info = pathinfo($file);

            switch (strtolower($info['extension'])) {
                case 'jar':
                    $this->mime = 'application/java-archive';
                    break;

                case 'jad':
                    $this->mime = 'text/vnd.sun.j2me.app-descriptor';
                    break;

                case 'cab':
                    $this->mime = 'application/vnd.ms-cab-compressed';
                    break;

                case 'sis':
                    $this->mime = 'application/vnd.symbian.install';
                    break;

                case 'zip':
                    $this->mime = 'application/x-zip';
                    break;

                case 'rar':
                    $this->mime = 'application/x-rar-compressed';
                    break;

                case '7z':
                    $this->mime = 'application/x-7z-compressed';
                    break;

                case 'gz':
                case 'tgz':
                    $this->mime = 'application/x-gzip';
                    break;

                case 'bz':
                case 'bz2':
                    $this->mime = 'application/x-bzip';
                    break;

                case 'jpg':
                case 'jpe':
                case 'jpeg':
                    $this->mime = 'image/jpeg';
                    break;

                case 'gif':
                    $this->mime = 'image/gif';
                    break;

                case 'png':
                    $this->mime = 'image/png';
                    break;


                case 'js':
                case 'asp':
                case 'txt':
                case 'dat':
                case 'php':
                case 'php5':
                case 'htm':
                case 'html':
                case 'wml':
                case 'css':
                    $this->mime = 'text/plain';
                    break;

                case 'mmf':
                    $this->mime = 'application/x-smaf';
                    break;

                case 'mid':
                    $this->mime = 'audio/mid';
                    break;

                case 'mp3':
                    $this->mime = 'audio/mpeg';
                    break;

                case 'amr':
                    $this->mime = 'audio/amr';
                    break;

                case 'wav':
                    $this->mime = 'audio/x-wav';
                    break;

                case 'mp4':
                    $this->mime = 'video/mp4';
                    break;

                case 'wmv':
                    $this->mime = 'video/x-ms-wmv';
                    break;

                case '3gp':
                    $this->mime = 'video/3gpp';
                    break;

                case 'avi':
                    $this->mime = 'video/x-msvideo';
                    break;

                case 'mpg':
                case 'mpe':
                case 'mpeg':
                    $this->mime = 'video/mpeg';
                    break;

                case 'pdf':
                    $this->mime = 'application/pdf';
                    break;

                case 'doc':
                    $this->mime = 'application/msword';
                    break;

                case 'docx':
                    $this->mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    break;

                case 'xls':
                    $this->mime = 'application/vnd.ms-excel';
                    break;

                case 'xlsx':
                    $this->mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    break;

                default:
                    $this->mime = 'application/octet-stream';
                    break;
            }
        }

        return $this->mime;
    }


    // Содержимое файла, имя файла, MIME (опционально), кодировка (опционально), аттач (опционально)
    public function get($data, $file, $mime, $charset, $attach)
    {
        ob_implicit_flush(1);
        set_time_limit(2000);

        ini_set('zlib.output_compression', 'Off');
        ini_set('output_handler', '');
        ob_end_clean();

        $this->file = $file;
        $this->mime = $mime;
        $this->charset = $charset;
        $this->attach = $attach;

        if (!$this->file) {
            return 'File not found';
        }


        if ($data) {
            $this->data = & $data;
        } else {
            $this->data = file_get_contents($this->file);
        }

        if (!$this->mime) {
            $this->mime = $this->mime($this->file);
        }

        if (!$this->charset) {
            if (iconv('UTF-8', 'UTF-8//IGNORE', $this->data) == $this->data) {
                $this->charset = 'UTF-8';
            } else {
                $this->charset = mb_detect_encoding($this->data, 'Windows-1251, ISO-8859-1, KOI8-R');
            }
        }


        /*
        if ($_SERVER['HTTP_ACCEPT_ENCODING']) {
            $compress = strtolower($_SERVER['HTTP_ACCEPT_ENCODING']);
        } else {
            $compress = strtolower($_SERVER['HTTP_TE']);
        }

        if (substr_count($compress, 'deflate')) {
            header('Content-Encoding: deflate');
            $this->data = gzdeflate($this->data, 6);
        } elseif (substr_count($compress, 'gzip')) {
            header('Content-Encoding: gzip');
            $this->data = gzencode($this->data, 6);
        }
        */

        $sz = $range = strlen($this->data);

        // "От" и  "До" по умолчанию
        $file_range = array('from' => 0, 'to' => $sz);

        // Если докачка
        $isset = isset($_SERVER['HTTP_RANGE']);
        if ($isset) {
            if (preg_match('/bytes=(\d+)\-(\d*)/i', $_SERVER['HTTP_RANGE'], $matches)) {
                // "От", "До" если "До" нету, "До" равняется размеру файла
                $file_range = array('from' => $matches[1], 'to' => (!$matches[2]) ? $sz : $matches[2]);

                // Режем переменную в соответствии с данными
                if ($file_range) {
                    $this->data = substr($this->data, $file_range['from'], $file_range['to']);
                    $range = $file_range['to'] - $file_range['from'];
                }
            }
        }


        // Хэш
        $etag = md5($this->data);
        $etag = substr($etag, 0, 4) . '-' . substr($etag, 5, 5) . '-' . substr($etag, 10, 8);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if ($_SERVER['HTTP_IF_NONE_MATCH'] == '"' . $etag . '"') {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                //header('Date: ' . gmdate('r'));
                exit;
            }
        }


        // Заголовки...
        if ($file_range['from']) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 206 Partial Content');
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        }

        header('ETag: "' . $etag . '"');


        //header('Date: ' . gmdate('r'));
        //header('Content-Transfer-Encoding: binary');
        //header('Last-Modified: ' . gmdate('r'));

        // Кэш
        header('Cache-Control: public, max-age=86400');
        header('Pragma: public');
        //header('Expires: Tue, 10 Apr 2038 01:00:00 GMT');


        //header('Connection: Close');
        header('Keep-Alive: timeout=10, max=60');
        header('Connection: Keep-Alive');

        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $range);


        // Если докачка
        if ($file_range['from']) {
            header('Content-Range: bytes ' . $file_range['from'] . '-' . $file_range['to'] . '/' . $sz);
        }


        if ($this->mime == 'text/plain') {
            header('Content-Type: text/plain; charset=' . $this->charset);
        } else {
            header('Content-Type: ' . $this->mime);
            header('Content-Transfer-Encoding: binary');
        }

        // Если отдаем как аттач
        if ($this->attach) {
            header('Content-Disposition: attachment; filename="' . basename($this->file) . '"');
        }

        echo $this->data;
    }

}

?>
