<?php

/**
 * @param string $ip
 *
 * @return bool
 */
function is_ip_not_spammer($ip)
{
    $data = @\file_get_contents('http://api.stopforumspam.org/api?f=json&ip='.\rawurlencode($ip));
    if (!$data) {
        return true;
    }
    $json = @\json_decode($data, false);
    if (!$json) {
        return true;
    }

    if (1 !== $json->success) {
        return true;
    }
    if ($json->ip->appears > 0) {
        return false;
    }

    return true;
}

// Cookie stuff!
function check_cookie(&$pun_user): void
{
    global $db, $pun_config, $cookie_name, $cookie_seed;

    $expire = \time() + 31536000; // The cookie expires after a year

    // We assume it's a guest
    $cookie = ['user_id' => 1, 'password_hash' => 'Guest'];

    // If a cookie is set, we get the user_id and password hash from it
    // @see http://php-security.org/2010/06/25/mops-2010-061-php-splobjectstorage-deserialization-use-after-free-vulnerability/index.html
    // уязвимость при использовании unserialize
    if (isset($_COOKIE[$cookie_name]) && \preg_match('/a:2:{i:0;s:\d+:"(\d+)";i:1;s:\d+:"([0-9a-f]+)";}/', $_COOKIE[$cookie_name], $matches)) {
        [, $cookie['user_id'], $cookie['password_hash']] = $matches;
    }

    if ($cookie['user_id'] > 1) {
        // Check if there's a user with the user ID and password hash from the cookie
        $result = $db->query(
            '
            SELECT u.*, g.*, o.logged, o.idle
            FROM `'.$db->prefix.'users` AS u
            INNER JOIN `'.$db->prefix.'groups` AS g ON u.group_id=g.g_id
            LEFT JOIN `'.$db->prefix.'online` AS o ON o.user_id=u.id
            WHERE u.id='.(int) $cookie['user_id']
        );
        if (!$result) {
            \error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
        }
        $pun_user = $db->fetch_assoc($result);

        // If user authorisation failed
        if (!isset($pun_user['id']) || \md5($cookie_seed.$pun_user['password']) !== $cookie['password_hash']) {
            \pun_setcookie(1, \md5(\uniqid(\mt_rand(), true)), $expire);
            \set_default_user();

            return;
        }

        // Set a default language if the user selected language no longer exists
        if (!@\file_exists(PUN_ROOT.'lang/'.$pun_user['language'])) {
            $pun_user['language'] = $pun_config['o_default_lang'];
        }

        // Set a default style if the user selected style no longer exists
        if (!@\file_exists(PUN_ROOT.'style/'.$pun_user['style'].'.css')) {
            $pun_user['style'] = $pun_config['o_default_style'];
        }

        // Set a default style if the user selected style no longer exists
        // if (!@file_exists(PUN_ROOT . 'style_wap/' . $pun_user['style_wap'] . '.css')) {
        // $pun_user['style_wap'] = $pun_config['o_default_style_wap'];
        // }
        if (!@\is_file(PUN_ROOT.'/style/wap/'.$pun_user['style_wap'].'/style.css')) {
            $pun_user['style_wap'] = $pun_config['o_default_style_wap'];
        }

        if (!$pun_user['disp_topics']) {
            $pun_user['disp_topics'] = $pun_config['o_disp_topics_default'];
        }

        if (!$pun_user['disp_posts']) {
            $pun_user['disp_posts'] = $pun_config['o_disp_posts_default'];
        }

        // Define this if you want this visit to affect the online list and the users last visit data
        if (!\defined('PUN_QUIET_VISIT')) {
            // Update the online list
            if (!$pun_user['logged']) {
                $pun_user['logged'] = $_SERVER['REQUEST_TIME'];

                $db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES('.$pun_user['id'].', \''.$db->escape($pun_user['username']).'\', '.$pun_user['logged'].')') || \error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
            } else {
                // Special case: We've timed out, but no other user has browsed the forums since we timed out
                if ($pun_user['logged'] < ($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_visit'])) {
                    $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$pun_user['logged'].' WHERE id='.$pun_user['id']) || \error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
                    $pun_user['last_visit'] = $pun_user['logged'];
                }

                $idle_sql = (1 == $pun_user['idle']) ? ', idle=0' : '';
                $db->query('UPDATE '.$db->prefix.'online SET logged='.$_SERVER['REQUEST_TIME'].$idle_sql.' WHERE user_id='.$pun_user['id']) || \error('Unable to update online list', __FILE__, __LINE__, $db->error());
            }
        }

        $pun_user['is_guest'] = false;
    } else {
        \set_default_user();
    }
}

//
// Fill $pun_user with default values (for guests)
//
function set_default_user(): void
{
    global $db, $pun_user, $pun_config;
    $remote_addr = \get_remote_address();

    // Fetch guest user
    $result = $db->query('
      SELECT u.*, g.*, o.logged
      FROM `'.$db->prefix.'users` AS u
      INNER JOIN `'.$db->prefix.'groups` AS g ON g.g_id = u.group_id
      LEFT JOIN `'.$db->prefix.'online` AS o ON o.ident="'.$remote_addr.'"
      WHERE u.id=1
    ');
    if (!$result) {
        \error('Unable to fetch guest information', __FILE__, __LINE__, $db->error());
    }
    if (!$db->num_rows($result)) {
        exit('Unable to fetch guest information. The table \''.$db->prefix.'users\' must contain an entry with id = 1 that represents anonymous users.');
    }

    $pun_user = $db->fetch_assoc($result);

    // Update online list
    if (!$pun_user['logged']) {
        $pun_user['logged'] = $_SERVER['REQUEST_TIME'];

        $db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES(1, \''.$db->escape($remote_addr).'\', '.$pun_user['logged'].')') || \error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
    } else {
        $db->query('UPDATE '.$db->prefix.'online SET logged='.$_SERVER['REQUEST_TIME'].' WHERE ident=\''.$db->escape($remote_addr).'\'') || \error('Unable to update online list', __FILE__, __LINE__, $db->error());
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

    return \setcookie($cookie_name, \serialize([$user_id, \md5($cookie_seed.$password_hash)]), $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
}

//
// Check whether the connecting user is banned (and delete any expired bans while we're at it)
//
function check_bans(): void
{
    global $db, $pun_config, $lang_common, $pun_user, $pun_bans;

    // Admins aren't affected
    if (PUN_ADMIN == $pun_user['g_id'] || !$pun_bans) {
        return;
    }

    // Add a dot at the end of the IP address to prevent banned address 192.168.0.5 from matching e.g. 192.168.0.50
    $user_ip = \get_remote_address().'.';
    $bans_altered = false;

    foreach ($pun_bans as $cur_ban) {
        // Has this ban expired?
        if ($cur_ban['expire'] && $cur_ban['expire'] <= $_SERVER['REQUEST_TIME']) {
            $db->query('DELETE FROM '.$db->prefix.'bans WHERE id='.$cur_ban['id']) || \error('Unable to delete expired ban', __FILE__, __LINE__, $db->error());
            $bans_altered = true;

            continue;
        }

        if ($cur_ban['username'] && !\strcasecmp($pun_user['username'], $cur_ban['username'])) {
            $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($pun_user['username']).'\'') || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
            \message($lang_common['Ban message'].' '.(($cur_ban['expire']) ? $lang_common['Ban message 2'].' '.\mb_strtolower(\format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message']) ? $lang_common['Ban message 3'].'<br /><br /><strong>'.\pun_htmlspecialchars($cur_ban['message']).'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.', true);
        }

        if ($cur_ban['ip']) {
            $cur_ban_ips = \explode(' ', $cur_ban['ip']);

            for ($i = 0, $all = \count($cur_ban_ips); $i < $all; ++$i) {
                $cur_ban_ips[$i] .= '.';

                if (\str_starts_with($user_ip, $cur_ban_ips[$i])) {
                    $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($pun_user['username']).'\'') || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
                    \message($lang_common['Ban message'].' '.(($cur_ban['expire']) ? $lang_common['Ban message 2'].' '.\mb_strtolower(\format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message']) ? $lang_common['Ban message 3'].'<br /><br /><strong>'.\pun_htmlspecialchars($cur_ban['message']).'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.', true);
                }
            }
        }
    }

    // If we removed any expired bans during our run-through, we need to regenerate the bans cache
    if ($bans_altered) {
        include_once PUN_ROOT.'include/cache.php';
        \generate_bans_cache();
    }
}

//
// Update "Users online"
//
function update_users_online(): void
{
    global $db, $pun_config, $pun_user;

    // Fetch all online list entries that are older than "o_timeout_online"
    $result = $db->query('SELECT * FROM '.$db->prefix.'online WHERE logged<'.($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_online']));
    if (!$result) {
        \error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
    }
    while ($cur_user = $db->fetch_assoc($result)) {
        // If the entry is a guest, delete it
        if (1 == $cur_user['user_id']) {
            $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($cur_user['ident']).'\'') || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
        } else {
            // If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
            if ($cur_user['logged'] < ($_SERVER['REQUEST_TIME'] - $pun_config['o_timeout_visit'])) {
                $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$cur_user['logged'].' WHERE id='.$cur_user['user_id']) || \error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
                $db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$cur_user['user_id']) || \error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
            } else {
                if (!$cur_user['idle']) {
                    $db->query('UPDATE '.$db->prefix.'online SET idle=1 WHERE user_id='.$cur_user['user_id']) || \error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
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
    $links[] = '<li id="navindex"><a href="'.$pun_config['o_base_url'].'">'.$lang_common['Index'].'</a>';
    $links[] = '<li id="navuserlist"><a href="userlist.php">'.$lang_common['User list'].'</a>';

    if (1 == $pun_config['o_rules']) {
        $links[] = '<li id="navrules"><a href="misc.php?action=rules">'.$lang_common['Rules'].'</a>';
    }
    // -для гостя
    if ($pun_user['is_guest']) {
        if (1 == $pun_user['g_search']) {
            $links[] = '<li id="navsearch"><a href="search.php">'.$lang_common['Search'].'</a>';
        }

        if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
            $links[] = '<li id="nauploads"><a href="uploads.php">'.$lang_common['Uploader'].'</a>';
        }

        $links[] = '<li id="navwap"><a href="wap/">'.$lang_common['WAP'].'</a>';
        $links[] = '<li id="navregister"><a href="registration.php">'.$lang_common['Register'].'</a>';
        $links[] = '<li id="navlogin"><a href="login.php">'.$lang_common['Login'].'</a>';

        $info = $lang_common['Not logged in'];
    } else {
        // PMS MOD BEGIN//для юзеров
        include PUN_ROOT.'include/pms/functions_navlinks.php';

        if ($pun_user['g_id'] > PUN_MOD) {
            if (1 == $pun_user['g_search']) {
                $links[] = '<li id="navsearch"><a href="search.php">'.$lang_common['Search'].'</a>';
            }
            $links[] = '<li id="navprofile"><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a>';

            if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
                $links[] = '<li id="navuploads"><a href="uploads.php">'.$lang_common['Uploader'].'</a>';
            }

            $links[] = '<li id="navfilemap"><a href="filemap.php">'.$lang_common['Attachments'].'</a>';
            $links[] = '<li id="navwap"><a href="wap/">'.$lang_common['WAP'].'</a>';
            $links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.\sha1($pun_user['id'].\sha1(\get_remote_address())).'">'.$lang_common['Logout'].'</a>';
        } else { // для админов
            $links[] = '<li id="navsearch"><a href="search.php">'.$lang_common['Search'].'</a>';
            $links[] = '<li id="navprofile"><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a>';
            $links[] = '<li id="navadmin"><a href="admin_index.php">'.$lang_common['Admin'].'</a>';
            $links[] = '<li id="navuploads"><a href="uploads.php">'.$lang_common['Uploader'].'</a>';
            $links[] = '<li id="navfilemap"><a href="filemap.php">'.$lang_common['Attachments'].'</a>';
            $links[] = '<li id="navwap"><a href="wap/">'.$lang_common['WAP'].'</a>';
            $links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.\sha1($pun_user['id'].\sha1(\get_remote_address())).'">'.$lang_common['Logout'].'</a>';
        }

        // PMS MOD END
    }

    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (\preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = \count($extra_links[1]); $i < $all; ++$i) {
                \array_splice($links, $extra_links[1][$i], 0, ['<li id="navextra'.($i + 1).'">'.$extra_links[2][$i]]);
            }
        }
    }

    return '<ul>'.\implode($lang_common['Link separator'].'</li>', $links).'</li></ul>';
}

function generate_wap_navlinks()
{
    global $pun_config, $lang_common, $pun_user, $lang_pms;

    // Index and Userlist should always be displayed
    $links['userlist.php'] = $lang_common['User list'];

    if (1 == $pun_config['o_rules']) {
        $links['misc.php?action=rules'] = $lang_common['Rules'];
    }

    if ($pun_user['is_guest']) {
        if (1 == $pun_user['g_search']) {
            $links['search.php'] = $lang_common['Search'];
        }

        if ($pun_config['uploads_conf'][$pun_user['group_id']]) {
            $links['uploads.php'] = $lang_common['Uploader'];
        }

        $info = $lang_common['Not logged in'];
    } else {
        if ($pun_user['g_id'] > PUN_MOD) {
            if (1 == $pun_user['g_search']) {
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

    $out = [];
    foreach ($links as $k => $link) {
        $out[] = '<option value="'.$k.'">'.$link.'</option>';
    }

    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (\preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = \count($extra_links[1]); $i < $all; ++$i) {
                if (\preg_match('!<a[^>]+href="?\'?([^ "\'>]+)"?\'?[^>]*>([^<>]*?)</a>!is', $extra_links[2][$i], $row)) {
                    \array_splice($out, $extra_links[1][$i], 0, ['<option value="'.$row[1].'">'.$row[2].'</option>']);
                }
            }
        }
    }

    return '<form id="qjump" action="redirect.php" method="get"><div><select name="r" onchange="window.location.assign(\''.$pun_config['o_base_url'].'/wap/redirect.php?r=\'+this.options[this.selectedIndex].value)">'.\implode('', $out).'</select> <input type="submit" value="'.$lang_common['Go'].'" accesskey="g" /></div></form>';
}

// верхняя Wap-навигация//редактировать в индексе
function generate_wap_1_navlinks()
{
    global $pun_config, $lang_common, $pun_user, $lang_pms;

    // Index and Userlist should always be displayed
    if ($pun_user['is_guest']) {
        // для гостя
        $links[] = '<a href="login.php">'.$lang_common['Login'].'</a> ';
        $links[] = ' <a href="registration.php">'.$lang_common['Register'].'</a>';

        $info = $lang_common['Not logged in'];
    } else {
        if ($pun_user['g_id'] > PUN_MOD) {
            // для юзеров

            $links[] = '<a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].' (<span style="font-weight: bold">'.\pun_htmlspecialchars($pun_user['username']).'</span>)</a>';
            // PMS MOD BEGIN
            include PUN_ROOT.'include/pms/functions_wap_navlinks.php';
            // PMS MOD END
            $links[] = '<a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.\sha1($pun_user['id'].\sha1(\get_remote_address())).'">'.$lang_common['Logout'].'</a>';
        } else {
            // для админов

            $links[] = '<a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].' (<span style="font-weight: bold">'.\pun_htmlspecialchars($pun_user['username']).'</span>)</a>';
            // PMS MOD BEGIN
            include PUN_ROOT.'include/pms/functions_wap_navlinks.php';
            // PMS MOD END
            $links[] = '<a href="../admin_index.php">'.$lang_common['Admin_m'].'</a>';
            $links[] = '<a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.\sha1($pun_user['id'].\sha1(\get_remote_address())).'">'.$lang_common['Logout'].'</a>';
        }
    }

    // Are there any additional navlinks we should insert into the array before imploding it?
    if ($pun_config['o_additional_navlinks']) {
        if (\preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
            // Insert any additional links into the $links array (at the correct index)
            for ($i = 0, $all = \count($extra_links[1]); $i < $all; ++$i) {
                \array_splice($links, $extra_links[1][$i], 0, [''.($i + 1).'">'.$extra_links[2][$i]]);
            }
        }
    }

    // сборка верхнего меню
    return \implode($lang_common['Link separator'].'|', $links);
}

//
// Display the profile navigation menu
//
function generate_profile_menu($page = ''): void
{
    global $lang_profile, $pun_config, $pun_user, $id;

    echo '<div id="profile" class="block2col">
<div class="blockmenu">
<h2><span>'.$lang_profile['Profile menu'].'</span></h2>
<div class="box">
<div class="inbox">
<ul>
<li';
    if ('essentials' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=essentials&amp;id='.$id.'">'.$lang_profile['Section essentials'].'</a></li><li';
    if ('personal' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=personal&amp;id='.$id.'">'.$lang_profile['Section personal'].'</a></li><li';
    if ('messaging' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=messaging&amp;id='.$id.'">'.$lang_profile['Section messaging'].'</a></li><li';
    if ('personality' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=personality&amp;id='.$id.'">'.$lang_profile['Section personality'].'</a></li><li';
    if ('display' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=display&amp;id='.$id.'">'.$lang_profile['Section display'].'</a></li><li';
    if ('privacy' === $page) {
        echo ' class="isactive"';
    }
    echo '><a href="profile.php?section=privacy&amp;id='.$id.'">'.$lang_profile['Section privacy'].'</a></li>';
    if (PUN_ADMIN == $pun_user['g_id'] || (PUN_MOD == $pun_user['g_id'] && 1 == $pun_config['p_mod_ban_users'])) {
        echo '<li';
        if ('admin' === $page) {
            echo ' class="isactive"';
        }
        echo '><a href="profile.php?section=admin&amp;id='.$id.'">'.$lang_profile['Section admin'].'</a></li>';
    }
    echo '<li><a href="profile.php?id='.$id.'&amp;preview=1">'.$lang_profile['Preview'].'</a></li></ul></div></div></div>';
}

/**
 * Update posts, topics, last_post, last_post_id and last_poster for a forum.
 *
 * @param int $forum_id
 */
function update_forum($forum_id): void
{
    global $db;

    $result = $db->query('SELECT COUNT(1), SUM(num_replies) FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id);
    if (!$result) {
        \error('Unable to fetch forum topic count', __FILE__, __LINE__, $db->error());
    }
    [$num_topics, $num_posts] = $db->fetch_row($result);

    $num_posts += $num_topics; // $num_posts is only the sum of all replies (we have to add the topic posts)

    $result = $db->query('SELECT last_post, last_post_id, last_poster FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id.' AND moved_to IS NULL ORDER BY last_post DESC LIMIT 1');
    if (!$result) {
        \error('Unable to fetch last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    }
    if ($db->num_rows($result)) {
        // There are topics in the forum
        [$last_post, $last_post_id, $last_poster] = $db->fetch_row($result);

        $db->query('UPDATE '.$db->prefix.'forums SET num_topics='.$num_topics.', num_posts='.$num_posts.', last_post='.$last_post.', last_post_id='.$last_post_id.', last_poster=\''.$db->escape($last_poster).'\' WHERE id='.$forum_id) || \error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    } else {
        // There are no topics
        $db->query('UPDATE '.$db->prefix.'forums SET num_topics='.$num_topics.', num_posts='.$num_posts.', last_post=NULL, last_post_id=NULL, last_poster=NULL WHERE id='.$forum_id) || \error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
    }
}

//
// Delete a topic and all of it's posts
//
function delete_topic($topic_id): void
{
    global $db, $pun_user; // for included files

    // Delete the topic and any redirect topics
    $db->query('DELETE FROM '.$db->prefix.'topics WHERE id='.$topic_id.' OR moved_to='.$topic_id) || \error('Unable to delete topic', __FILE__, __LINE__, $db->error());

    // Create a list of the post ID's in this topic
    $post_ids = null;
    $result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id);
    if (!$result) {
        \error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
    }
    while ($row = $db->fetch_row($result)) {
        $post_ids .= ($post_ids) ? ','.$row[0] : $row[0];
    }

    // Make sure we have a list of post ID's
    if ($post_ids) {
        \strip_search_index($post_ids);

        // Delete attachments
        include PUN_ROOT.'lang/'.$pun_user['language'].'/fileup.php';

        include_once PUN_ROOT.'include/file_upload.php';
        \delete_post_attachments($post_ids);

        // Delete posts in topic
        $db->query('DELETE FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) || \error('Unable to delete posts', __FILE__, __LINE__, $db->error());
    }

    // Delete any subscriptions for this topic
    $db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE topic_id='.$topic_id) || \error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());
}

//
// Delete a single post
//
function delete_post($post_id, $topic_id): void
{
    global $db, $pun_user;

    $result = $db->query('SELECT `id`, `poster`, `posted` FROM `'.$db->prefix.'posts` WHERE `topic_id` = '.$topic_id.' ORDER BY `id` DESC LIMIT 2');
    if (!$result) {
        \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    }
    [$last_id, $poster] = $db->fetch_row($result);
    [$second_last_id, $second_poster, $second_posted] = $db->fetch_row($result);

    // Delete the post
    $db->query('DELETE FROM `'.$db->prefix.'posts` WHERE `id` = '.$post_id) || \error('Unable to delete post', __FILE__, __LINE__, $db->error());

    \strip_search_index($post_id);

    include PUN_ROOT.'lang/'.$pun_user['language'].'/fileup.php';

    include_once PUN_ROOT.'include/file_upload.php';
    \delete_post_attachments($post_id);

    // Count number of replies in the topic
    $result = $db->query('SELECT COUNT(1) FROM `'.$db->prefix.'posts` WHERE `topic_id`='.$topic_id);
    if (!$result) {
        \error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
    }
    $num_replies = $db->result($result, 0) - 1;

    // уменьшаем кол-во постов
    $db->query('UPDATE `'.$db->prefix.'users` SET `num_posts` = `num_posts` - 1 WHERE `username` = "'.$db->escape($poster).'" LIMIT 1');

    // If the message we deleted is the most recent in the topic (at the end of the topic)
    if ($last_id == $post_id) {
        // If there is a $second_last_id there is more than 1 reply to the topic
        if ($second_last_id) {
            $db->query('UPDATE `'.$db->prefix.'topics` SET `last_post`='.$second_posted.', `last_post_id`='.$second_last_id.', `last_poster`=\''.$db->escape($second_poster).'\', `num_replies`='.$num_replies.' WHERE `id`='.$topic_id) || \error('Unable to update topic', __FILE__, __LINE__, $db->error());
        } else {
            // We deleted the only reply, so now last_post/last_post_id/last_poster is posted/id/poster from the topic itself
            $db->query('UPDATE `'.$db->prefix.'topics` SET `last_post`=posted, `last_post_id`=id, `last_poster`=poster, `num_replies`='.$num_replies.' WHERE `id`='.$topic_id) || \error('Unable to update topic', __FILE__, __LINE__, $db->error());
        }
    } else {
        // Otherwise we just decrement the reply counter
        $db->query('UPDATE `'.$db->prefix.'topics` SET `num_replies`='.$num_replies.' WHERE `id`='.$topic_id) || \error('Unable to update topic', __FILE__, __LINE__, $db->error());
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
        $result = $db->query('SELECT search_for, replace_with FROM '.$db->prefix.'censoring');
        if (!$result) {
            \error('Unable to fetch censor word list', __FILE__, __LINE__, $db->error());
        }
        $num_words = $db->num_rows($result);

        $search_for = [];
        for ($i = 0; $i < $num_words; ++$i) {
            [$search_for[$i], $replace_with[$i]] = $db->fetch_row($result);
            // FIX UTF REGULAR EXPRESSIONS BUG BEGIN
            // ORIGINAL:
            // $search_for[$i] = '/\b('.str_replace('\*', '\w*?', preg_quote($search_for[$i], '/')).')\b/i';
            $search_for[$i] = '/(?<=^|\s)('.\str_replace('\*', '['.ALPHANUM.']*?', \preg_quote($search_for[$i], '/')).')(?=$|\s)/iu';
            // FIX UTF REGULAR EXPRESSIONS BUG END
        }
    } else {
        $text = \substr(\preg_replace($search_for, $replace_with, ' '.$text.' '), 1, -1);
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
        $ban_list = [];

        foreach ($pun_bans as $cur_ban) {
            $ban_list[] = \mb_strtolower($cur_ban['username']);
        }
    }

    // If not already loaded in a previous call, load the cached ranks
    if (1 == $pun_config['o_ranks'] && !$pun_ranks) {
        @include PUN_ROOT.'cache/cache_ranks.php';
        if (!\defined('PUN_RANKS_LOADED')) {
            include_once PUN_ROOT.'include/cache.php';
            \generate_ranks_cache();

            include PUN_ROOT.'cache/cache_ranks.php';
        }
    }

    $user_title = '';

    if ($user['title']) {
        // If the user has a custom title
        $user_title = \pun_htmlspecialchars($user['title']);
    } elseif (\in_array(\mb_strtolower(@$user['username']), $ban_list, true)) {
        // If the user is banned
        $user_title = $lang_common['Banned'];
    } elseif ($user['g_user_title']) {
        // If the user group has a default user title
        $user_title = \pun_htmlspecialchars($user['g_user_title']);
    } elseif (PUN_GUEST == $user['g_id']) {
        // If the user is a guest
        $user_title = $lang_common['Guest'];
    } else {
        // Are there any ranks?
        if (1 == $pun_config['o_ranks'] && $pun_ranks) {
            @\reset($pun_ranks);
            foreach ($pun_ranks as $cur_rank) {
                if ((int) $user['num_posts'] >= $cur_rank['min_posts']) {
                    $user_title = \pun_htmlspecialchars($cur_rank['rank']);
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
    // / MOD VIEW ALL PAGES IN ONE BEGIN
    global $lang_common;

    $active_all = true;

    // If $cur_page > $num_pages, we show link to all pages
    if ($cur_page > $num_pages) {
        $active_all = false;
        $link_to_all = true;
        --$cur_page;
    }
    // / MOD VIEW ALL PAGES IN ONE END

    $pages = [];
    $link_to_all = false;

    // If $cur_page == -1, we link to all pages (used in viewforum.php)
    if (-1 == $cur_page) {
        $cur_page = 1;
        $link_to_all = true;
    }

    if ($num_pages <= 1) {
        $pages = ['<strong>1</strong>'];
    } else {
        if ($cur_page > 3) {
            $pages[] = '<a href="'.$link_to.'&amp;p=1">1</a>';
            if (4 != $cur_page) {
                $pages[] = '&#x2026;';
            }
        }

        // Don't ask me how the following works. It just does, OK? :-)
        for ($current = $cur_page - 2, $stop = $cur_page + 3; $current < $stop; ++$current) {
            if ($current < 1 || $current > $num_pages) {
                continue;
            }
            if ($current != $cur_page || $link_to_all) {
                $pages[] = '<a href="'.$link_to.'&amp;p='.$current.'">'.$current.'</a>';
            } else {
                $pages[] = '<strong>'.$current.'</strong>';
            }
        }

        if ($cur_page <= ($num_pages - 3)) {
            if ($cur_page != ($num_pages - 3)) {
                $pages[] = '&#x2026;';
            }

            $pages[] = '<a href="'.$link_to.'&amp;p='.$num_pages.'">'.$num_pages.'</a>';
        }

        // / MOD VIEW ALL PAGES IN ONE BEGIN
        if (!$active_all) {
            $pages[] = $lang_common['All'];
        } else {
            $pages[] = '<a href="'.$link_to.'&amp;action=all">'.$lang_common['All'].'</a>';
        }
        // / MOD VIEW ALL PAGES IN ONE END
    }

    return \implode(' ', $pages);
}

//
// Display a message
//
function message($message, $no_back_link = false): void
{
    global $db, $pun_user, $lang_common, $pun_config, $pun_start, $tpl_main, $id;

    if (!\defined('PUN_HEADER')) {
        $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_common['Info'];

        require_once PUN_ROOT.'header.php';
    }

    echo '<div id="msg" class="block">
<h2><span>'.$lang_common['Info'].'</span></h2>
<div class="box">
<div class="inbox">
<p>'.$message.'</p>';
    if (!$no_back_link) {
        echo '<p><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>';
    }
    echo '</div></div></div>';

    require_once PUN_ROOT.'footer.php';

    exit;
}

function wap_message($message, $no_back_link = false): void
{
    global $db, $pun_user, $lang_common, $pun_config, $pun_start, $tpl_main, $smarty;

    if (!\defined('PUN_HEADER')) {
        require_once PUN_ROOT.'wap/header.php';
    }

    if (!isset($page_title)) {
        $page_title = $pun_config['o_board_title'].' / '.$lang_common['Info'];
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

    $date = \date($pun_config['o_date_format'], $timestamp);
    $today = \date($pun_config['o_date_format'], $_SERVER['REQUEST_TIME'] + $diff);
    $yesterday = \date($pun_config['o_date_format'], $_SERVER['REQUEST_TIME'] + $diff - 86400);

    if ($date == $today) {
        $date = $lang_common['Today'];
    } elseif ($date == $yesterday) {
        $date = $lang_common['Yesterday'];
    }

    if (!$date_only) {
        return $date.' '.\date($pun_config['o_time_format'], $timestamp);
    }

    return $date;
}

//
// Make sure that HTTP_REFERER matches $pun_config['o_base_url']/$script
//
function confirm_referrer($script): void
{
    global $pun_config, $lang_common, $_SERVER;

    if (!\preg_match('#^'.\preg_quote(\str_ireplace('www.', '', $pun_config['o_base_url']).'/'.$script, '#').'#i', \str_ireplace('www.', '', $_SERVER['HTTP_REFERER'] ?? ''))) {
        \message($lang_common['Bad referrer']);
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
        $password .= $chars[\mt_rand() % \strlen($chars)];
    }

    return $password;
}

//
// Compute a hash of $str
// Uses sha1()
function pun_hash($str)
{
    if (\function_exists('sha1')) {
        return \sha1($str);
    }
    if (\function_exists('mhash')) {
        return \bin2hex(\mhash(\MHASH_SHA1, $str));
    }

    return \md5($str);
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
    if (null === $str) {
        return null;
    }

    return \str_replace(['<', '>', '"'], ['&lt;', '&gt;', '&quot;'], \preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str));
}

//
// Convert \r\n and \r to \n
function pun_linebreaks($str)
{
    return \str_replace("\r", "\n", \str_replace("\r\n", "\n", $str));
}

function pun_show_avatar()
{
    global $pun_config, $pun_user, $cur_post;

    $user_avatar = '';
    if (1 == $pun_config['o_avatars'] && 1 == $cur_post['use_avatar'] && $pun_user['show_avatars']) {
        if (@\getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif')) {
            $user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif" alt="" />';
        } elseif (@\getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg')) {
            $user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg" alt="" />';
        } elseif (@\getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png')) {
            $user_avatar = '<img src="'.PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png" alt="" />';
        }
    }

    return $user_avatar;
}

//
// Display a message when board is in maintenance mode
//
function maintenance_message(): void
{
    global $db, $pun_config, $lang_common, $pun_user;

    // Deal with newlines, tabs and multiple spaces
    $message = \str_replace(["\t", ' ', ' '], ['&#160; &#160; ', '&#160; ', ' &#160;'], $pun_config['o_maintenance_message']);

    // Load the maintenance template
    $tpl_maint = \trim(\file_get_contents(PUN_ROOT.'include/template/maintenance.tpl'));

    // START SUBST - <pun_include "*">
    while (\preg_match('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_maint, $cur_include)) {
        if (!\file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2])) {
            \error('Unable to process user include '.\htmlspecialchars($cur_include[0]).' from template maintenance.tpl. There is no such file in folder /include/user/', __FILE__, __LINE__);
        }

        \ob_start();

        include PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
        $tpl_temp = \ob_get_contents();
        $tpl_maint = \str_replace($cur_include[0], $tpl_temp, $tpl_maint);
        \ob_end_clean();
    }
    // END SUBST - <pun_include "*">

    // START SUBST - <pun_head>
    \ob_start();

    echo '<title>'.\pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_common['Maintenance'].'</title><link rel="stylesheet" type="text/css" href="'.PUN_ROOT.'style/'.$pun_user['style'].'.css" />';

    $tpl_temp = \trim(\ob_get_contents());
    $tpl_maint = \str_replace('<pun_head>', $tpl_temp, $tpl_maint);
    \ob_end_clean();
    // END SUBST - <pun_head>

    // START SUBST - <pun_maint_heading>
    $tpl_maint = \str_replace('<pun_maint_heading>', $lang_common['Maintenance'], $tpl_maint);
    // END SUBST - <pun_maint_heading>

    // START SUBST - <pun_maint_message>
    $tpl_maint = \str_replace('<pun_maint_message>', $message, $tpl_maint);
    // END SUBST - <pun_maint_message>

    // Close the db connection (and free up any result data)
    $db->close();

    exit($tpl_maint);
}

//
// Display $message and redirect user to $destination_url
//
function redirect($destination_url, $message = '', $redirect_code = 302): void
{
    global $db, $pun_config, $lang_common, $pun_user;

    // Prefix with o_base_url (unless there's already a valid URI)
    if (!\str_starts_with($destination_url, 'http://') && !\str_starts_with($destination_url, 'https://') && !\str_starts_with($destination_url, 'ftp://') && !\str_starts_with($destination_url, 'ftps://') && !\str_starts_with($destination_url, '/')) {
        $destination_url = $pun_config['o_base_url'].'/'.$destination_url;
    }

    // Do a little spring cleaning
    $destination_url = \preg_replace('/([\r\n])|(%0[ad])|(;[\s]*data[\s]*:)/i', '', $destination_url);

    // If the delay is 0 seconds, we might as well skip the redirect all together
    if (!$pun_config['o_redirect_delay'] || !$message) {
        \header('Location: '.\str_replace('&amp;', '&', $destination_url), true, $redirect_code);

        exit;
    }

    // Load the redirect template
    $tpl_redir = \trim(\file_get_contents(PUN_ROOT.'include/template/redirect.tpl'));

    // START SUBST - <pun_include "*">
    while (\preg_match('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_redir, $cur_include)) {
        if (!\file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2])) {
            \error('Unable to process user include '.\htmlspecialchars($cur_include[0]).' from template redirect.tpl. There is no such file in folder /include/user/', __FILE__, __LINE__);
        }

        \ob_start();

        include PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
        $tpl_temp = \ob_get_contents();
        $tpl_redir = \str_replace($cur_include[0], $tpl_temp, $tpl_redir);
        \ob_end_clean();
    }
    // END SUBST - <pun_include "*">

    // START SUBST - <pun_head>
    \ob_start();

    echo '<meta http-equiv="refresh" content="'.$pun_config['o_redirect_delay'].'; url='.\str_replace(['<', '>', '"'], ['&lt;', '&gt;', '&quot;'], $destination_url).'" />
<title>'.\pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_common['Redirecting'].'</title>
<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'.css" />';

    $tpl_temp = \trim(\ob_get_contents());
    $tpl_redir = \str_replace('<pun_head>', $tpl_temp, $tpl_redir);
    \ob_end_clean();
    // END SUBST - <pun_head>

    // START SUBST - <pun_redir_heading>
    $tpl_redir = \str_replace('<pun_redir_heading>', $lang_common['Redirecting'], $tpl_redir);
    // END SUBST - <pun_redir_heading>

    // START SUBST - <pun_redir_text>
    $tpl_temp = $message.'<br /><br /><a href="'.$destination_url.'">'.$lang_common['Click redirect'].'</a>';
    $tpl_redir = \str_replace('<pun_redir_text>', $tpl_temp, $tpl_redir);
    // END SUBST - <pun_redir_text>

    // START SUBST - <pun_footer>
    \ob_start();

    // Display executed queries (if enabled)
    if (\defined('PUN_SHOW_QUERIES')) {
        \display_saved_queries();
    }

    $tpl_temp = \trim(\ob_get_contents());
    $tpl_redir = \str_replace('<pun_footer>', $tpl_temp, $tpl_redir);
    \ob_end_clean();
    // END SUBST - <pun_footer>

    // Close the db connection (and free up any result data)
    $db->close();
    \header('Content-Type: text/html; charset=UTF-8');

    exit($tpl_redir);
}

function wap_redirect($destination_url, $redirect_code = 301): void
{
    // Prefix with o_base_url (unless there's already a valid URI)
    if (!\str_starts_with($destination_url, 'http://') && !\str_starts_with($destination_url, 'https://') && !\str_starts_with($destination_url, 'ftp://') && !\str_starts_with($destination_url, 'ftps://') && !\str_starts_with($destination_url, '/')) {
        // echo $destination_url . "\n";
        $destination_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].\dirname($_SERVER['PHP_SELF']).'/'.$destination_url;
        // echo $destination_url;
    }

    \header('Location: '.$destination_url, true, $redirect_code);

    exit;
}

/**
 * Display a simple error message.
 *
 * @param string $message
 * @param string $file
 * @param int    $line
 * @param array  $db_error
 */
function error($message, $file, $line, $db_error = []): never
{
    global $pun_config, $db;

    // Set a default title if the script failed before $pun_config could be populated
    if (!$pun_config) {
        $pun_config['o_board_title'] = 'PunBB mod';
    }

    // Empty output buffer and stop buffering
    @\ob_end_clean();

    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>'.\pun_htmlspecialchars($pun_config['o_board_title']).' / Error</title>
<style>
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

    if (\defined('PUN_DEBUG')) {
        echo '<strong>File:</strong> '.$file.'<br /><strong>Line:</strong> '.$line.'<br /><br /><strong>PunBB reported</strong>: '.$message;

        if ($db_error) {
            echo '<br /><br /><strong>Database reported:</strong> '.\pun_htmlspecialchars($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '');

            if ($db_error['error_sql']) {
                echo '<br /><br /><strong>Failed query:</strong> '.\pun_htmlspecialchars($db_error['error_sql']);
            }
        }
    } else {
        echo 'Error: <strong>'.$message.'.</strong>';
    }

    echo '</div>
</div>
</body>
</html>';

    // If a database connection was established (before this error) we close it
    if ($db_error) {
        $db->close();
    }

    exit;
}

// DEBUG FUNCTIONS BELOW

//
// Display executed queries (if enabled)
//
function display_saved_queries(): void
{
    global $db, $lang_common;

    // Get the queries so that we can print them out
    $saved_queries = $db->get_saved_queries();

    echo '<div id="debug" class="blocktable">
<h2><span>'.$lang_common['Debug table'].'</span></h2>
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
    foreach ($saved_queries as $cur_query) {
        $query_time_total += $cur_query[1];
        echo '<tr><td class="tcl">'.($cur_query[1] ?: ' ').'</td><td class="tcr">'.\pun_htmlspecialchars($cur_query[0]).'</td></tr>';
    }
    echo '<tr>
<td class="tcl" colspan="2">Total query time: '.$query_time_total.' s</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>';
}

// MOD CONVENIENT FORUM URL BEGIN

function convert_forum_url(&$text)
{
    global $db, $pun_config;

    function replace($query, $pattern, $text)
    {
        global $db;
        if (\preg_match_all($pattern, $text, $regs, \PREG_SET_ORDER)) {
            foreach ($regs as $pid) {
                $result = $db->query($query.$pid[1]);
                if ($result) {
                    $subject = $db->result($result);
                    $text = \preg_replace('/(?<=^|\s)'.\str_replace('/', '\/', \str_replace('?', '\?', \str_replace('.', '\.', $pid[0]))).'\b/', '[url='.$pid[0].']'.$subject.'[/url]', $text, 1);
                }
            }
        }

        return $text;
    }

    // Convert viewtopic
    $url = \str_replace('/', '\/', \str_replace('.', '\.', $pun_config['o_base_url'].'/viewtopic.php\?'));
    $text = \replace('SELECT t.subject FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id = p.topic_id WHERE p.id=', '/(?<=^|\s)'.$url.'pid=([0-9]+)#p[0-9]+\b/', $text);
    $text = \replace('SELECT subject FROM '.$db->prefix.'topics WHERE id=', '/(?<=^|\s)'.$url.'id=([0-9]+)\b/', $text);

    // Convert profile
    $url = \str_replace('/', '\/', \str_replace('.', '\.', $pun_config['o_base_url'].'/profile.php\?'));
    $text = \replace('SELECT username FROM '.$db->prefix.'users WHERE id=', '/(?<=^|\s)'.$url.'id=([0-9]+)\b/', $text);

    // Convert viewforum
    $url = \str_replace('/', '\/', \str_replace('.', '\.', $pun_config['o_base_url'].'/viewforum.php\?'));
    $text = \replace('SELECT forum_name FROM '.$db->prefix.'forums WHERE id=', '/(?<=^|\s)'.$url.'id=([0-9]+)\b/', $text);
}

// MOD CONVENIENT FORUM URL END

function clear_empty_multiline($text)
{
    return \preg_replace("/\n\n\n+/m", "\n\n", $text);
}

function vote($to = 0, $vote = 1): void
{
    global $db, $pun_user;

    $vote = ((1 == $vote) ? 1 : -1);
    $q = $db->query('SELECT 1 FROM `'.$db->prefix.'karma` WHERE `id`='.$pun_user['id'].' AND `to`='.(int) $to);
    if (!$q) {
        \error('Error', __FILE__, __LINE__, $db->error());
    }

    if ($db->num_rows($q)) {
        \message('Error');
    }

    $db->query('INSERT INTO `'.$db->prefix.'karma` SET `id`='.$pun_user['id'].', `to`='.(int) $to.', `vote`="'.$vote.'", `time`='.$_SERVER['REQUEST_TIME']) || \error('Error', __FILE__, __LINE__, $db->error());
}

/**
 * @param string $file
 * @param string $default
 *
 * @return string
 */
function mime($file, $default = 'application/octet-stream')
{
    $mime = null;

    // если есть Fileinfo
    if (\function_exists('finfo_open') && \is_file($file)) {
        $finfo = \finfo_open(\FILEINFO_MIME_TYPE);
        $mime = \finfo_file($finfo, $file);
        \finfo_close($finfo);
    }

    // если нет, ставим MIME в зависимости от расширения
    if (!$mime) {
        switch (\strtolower(\pathinfo($file, \PATHINFO_EXTENSION))) {
            case 'jar':
                $mime = 'application/java-archive';

                break;

            case 'jad':
                $mime = 'text/vnd.sun.j2me.app-descriptor';

                break;

            case 'apk':
                $mime = 'application/vnd.android.package-archive';

                break;

            case 'cab':
                $mime = 'application/vnd.ms-cab-compressed';

                break;

            case 'sis':
                $mime = 'application/vnd.symbian.install';

                break;

            case 'zip':
                $mime = 'application/x-zip';

                break;

            case 'rar':
                $mime = 'application/x-rar-compressed';

                break;

            case '7z':
                $mime = 'application/x-7z-compressed';

                break;

            case 'gz':
            case 'tgz':
                $mime = 'application/x-gzip';

                break;

            case 'bz':
            case 'bz2':
                $mime = 'application/x-bzip';

                break;

            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $mime = 'image/jpeg';

                break;

            case 'gif':
                $mime = 'image/gif';

                break;

            case 'png':
                $mime = 'image/png';

                break;

            case 'bmp':
                $mime = 'image/bmp';

                break;

            case 'ico':
                $mime = 'image/x-icon';

                break;

            case 'asp':
            case 'txt':
            case 'dat':
            case 'php':
            case 'php5':
            case 'py':
            case 'rb':
            case 'c':
            case 'h':
            case 'cpp':
            case 'cs':
            case 'pl':
            case 'wml':
            case 'sql':
            case 'ini':
            case 'log':
            case 'bat':
            case 'sh':
                $mime = 'text/plain';

                break;

            case 'css':
                $mime = 'text/css';

                break;

            case 'js':
                $mime = 'application/javascript';

                break;

            case 'json':
                $mime = 'application/json';

                break;

            case 'xml':
            case 'xsd':
                $mime = 'application/xml';

                break;

            case 'xsl':
            case 'xslt':
                $mime = 'application/xslt+xml';

                break;

            case 'wsdl':
                $mime = 'application/wsdl+xml';

                break;

            case 'mmf':
                $mime = 'application/x-smaf';

                break;

            case 'mid':
                $mime = 'audio/mid';

                break;

            case 'mp3':
                $mime = 'audio/mpeg';

                break;

            case 'amr':
                $mime = 'audio/amr';

                break;

            case 'wav':
                $mime = 'audio/x-wav';

                break;

            case 'mp4':
                $mime = 'video/mp4';

                break;

            case 'wmv':
                $mime = 'video/x-ms-wmv';

                break;

            case '3gp':
                $mime = 'video/3gpp';

                break;

            case 'avi':
                $mime = 'video/x-msvideo';

                break;

            case 'flv':
                $mime = 'video/x-flv';

                break;

            case 'mpg':
            case 'mpe':
            case 'mpeg':
                $mime = 'video/mpeg';

                break;

            case 'swf':
                $mime = 'application/x-shockwave-flash';

                break;

            case 'pdf':
                $mime = 'application/pdf';

                break;

            case 'rtf':
                $mime = 'application/rtf';

                break;

            case 'doc':
                $mime = 'application/msword';

                break;

            case 'docx':
                $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

                break;

            case 'xls':
                $mime = 'application/vnd.ms-excel';

                break;

            case 'xlsx':
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

                break;

            default:
                $mime = $default;

                break;
        }
    }

    return $mime;
}

function download($path, $name, $mime = null): void
{
    if (!$mime || 'application/octet-stream' === $mime) {
        $mime = \mime($path);
    }

    $disposition = 'attachment';
    if (\str_starts_with($mime, 'image/') || \str_starts_with($mime, 'video/') || \str_starts_with($mime, 'audio/')) {
        $disposition = 'inline';
    }

    \header('Content-Type: '.$mime);
    \header('Content-Disposition: '.$disposition.'; filename*=UTF-8\'\''.\rawurlencode($name));
    \header('Content-Length: '.\filesize($path));
    \header('Content-Transfer-Encoding: binary');
    \readfile($path);

    exit;
}
