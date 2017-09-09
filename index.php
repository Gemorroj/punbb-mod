<?php
define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
}


// Load the index.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/index.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']);
define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT . 'header.php';

// REAL MARK TOPIC AS READ MOD BEGIN
// под вопросом!
if (!$pun_user['is_guest']) {
    $db->query('DELETE FROM `' . $db->prefix . 'log_forums` WHERE log_time < ' . ($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']) . ' AND user_id=' . $pun_user['id']) or error('Unable to delete marked as read forum info', __FILE__, __LINE__, $db->error());
}
// REAL MARK TOPIC AS READ MOD END

// Print the categories and forums
$result = $db->query('
SELECT c.id AS cid,
c.cat_name,
f.id AS fid,
f.forum_name,
f.forum_desc,
f.redirect_url,
f.moderators,
f.num_topics,
f.num_posts,
f.last_post,
f.last_post_id,
lf.log_time,
lf.mark_read,
f.last_poster,
t.subject,
p.poster_id

FROM ' . $db->prefix . 'categories AS c
INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id
LEFT JOIN ' . $db->prefix . 'topics AS t ON f.last_post_id=t.last_post_id
LEFT JOIN ' . $db->prefix . 'log_forums AS lf ON lf.user_id=' . $pun_user['id'] . ' AND lf.forum_id=f.id
LEFT JOIN ' . $db->prefix . 'posts AS p ON f.last_post_id=p.id
LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')

WHERE fp.read_forum IS NULL OR fp.read_forum=1

ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
// REAL MARK TOPIC AS READ MOD END

$cur_category = $cat_count = 0;
while ($cur_forum = $db->fetch_assoc($result)) {
    $moderators = null;

    // A new category since last iteration?
    if ($cur_forum['cid'] != $cur_category) {
        if ($cur_category) {
            echo '</tbody></table></div></div></div>';
        }

        ++$cat_count;

        echo '<div id="idx' . $cat_count . '" class="blocktable"><h2><span>' . pun_htmlspecialchars($cur_forum['cat_name']) . '</span></h2><div class="box"><div class="inbox"><table cellspacing="0"><thead><tr><th class="tcl" scope="col">' . $lang_common['Forum'] . '</th><th class="tc2" scope="col">' . $lang_index['Topics'] . '</th><th class="tc3" scope="col">' . $lang_common['Posts'] . '</th><th class="tcr" scope="col">' . $lang_common['Last post'] . '</th></tr></thead><tbody>';
        $cur_category = $cur_forum['cid'];
    }

    $item_status = null;
    $icon_text = $lang_common['Normal icon'];
    $icon_type = 'icon';

    // Are there new posts?
    // REAL MARK TOPIC AS READ MOD BEGIN


    if (!$pun_user['is_guest'] && ($cur_forum['log_time'] < $cur_forum['last_post']) && $cur_forum['last_post'] > $cur_forum['mark_read'] && $cur_forum['poster_id'] != $pun_user['id'] && ($cur_forum['last_post'] > $pun_user['last_visit'] || ($_SERVER['REQUEST_TIME'] - $cur_forum['last_post'] < $pun_user['mark_after']))) {
        // REAL MARK TOPIC AS READ MOD END
        $item_status = 'inew';
        $icon_text = $lang_common['New icon'];
        $icon_type = 'icon inew';
    }

    // Is this a redirect forum?
    if ($cur_forum['redirect_url']) {
        $forum_field = '<h3><a href="' . pun_htmlspecialchars($cur_forum['redirect_url']) . '" title="' . $lang_index['Link to'] . ' ' . pun_htmlspecialchars($cur_forum['redirect_url']) . '">' . pun_htmlspecialchars($cur_forum['forum_name']) . '</a></h3>';
        $num_topics = $num_posts = '&#160;';
        $item_status = 'iredirect';
        $icon_text = $lang_common['Redirect icon'];
        $icon_type = 'icon';
    } else {
        $forum_field = '<h3><a href="viewforum.php?id=' . $cur_forum['fid'] . '">' . pun_htmlspecialchars($cur_forum['forum_name']) . '</a></h3>';
        $num_topics = $cur_forum['num_topics'];
        $num_posts = $cur_forum['num_posts'];
    }


    if ($cur_forum['forum_desc']) {
        $forum_field .= $cur_forum['forum_desc'];
    }


    // If there is a last_post/last_poster.
    if ($cur_forum['last_post']) {
        // MOD:
        $last_post = '<a href="viewtopic.php?pid=' . $cur_forum['last_post_id'] . '#p' . $cur_forum['last_post_id'] . '">' . pun_htmlspecialchars($cur_forum['subject']) . '</a> <span class="byuser">' . format_time($cur_forum['last_post']) . ' ' . $lang_common['by'] . ' ' . pun_htmlspecialchars($cur_forum['last_poster']) . '</span>';
        // END MOD
    } else {
        $last_post = '&#160;';
    }


    if ($cur_forum['moderators'] && $pun_config['o_show_moderators']) {
        $mods_array = unserialize($cur_forum['moderators']);
        $moderators = array();

        foreach ($mods_array as $mod_username => $mod_id) {
            $moderators[] = '<a href="profile.php?id=' . $mod_id . '">' . pun_htmlspecialchars($mod_username) . '</a>';
        }

        $moderators = '<p><em>(' . $lang_common['Moderated by'] . '</em> ' . implode(', ', $moderators) . ')</p>';
    }

    echo '<tr' . ($item_status ? ' class="' . $item_status . '"' : '') . '><td class="tcl"><div class="intd"><div class="' . $icon_type . '"><div class="nosize">' . $icon_text . '</div></div><div class="tclcon">' . $forum_field . $moderators . '</div></div></td><td class="tc2">' . $num_topics . '</td><td class="tc3">' . $num_posts . '</td><td class="tcr">' . $last_post . '</td></tr>';
}

// Did we output any categories and forums?
if ($cur_category > 0) {
    echo '</tbody></table></div></div></div>';
} else {
    echo '<div id="idx0" class="block"><div class="box"><div class="inbox"><p>' . $lang_index['Empty board'] . '</p></div></div></div>';
}


// Collect some statistics from the database
$result = $db->query('SELECT COUNT(1) - 1 FROM ' . $db->prefix . 'users') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['total_users'] = $db->result($result);

$result = $db->query('SELECT id, username FROM ' . $db->prefix . 'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
$stats['last_user'] = $db->fetch_assoc($result);

$result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM ' . $db->prefix . 'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);


echo '<div id="brdstats" class="block"><h2><span>' . $lang_index['Board info'] . '</span></h2><div class="box"><div class="inbox"><dl class="conr"><dt><strong>' . $lang_index['Board stats'] . '</strong></dt><dd>' . $lang_index['No of users'] . ': <strong>' . $stats['total_users'] . '</strong></dd><dd>' . $lang_index['No of topics'] . ': <strong>' . $stats['total_topics'] . '</strong></dd><dd>' . $lang_index['No of posts'] . ': <strong>' . $stats['total_posts'] . '</strong></dd></dl><dl class="conl"><dt><strong>' . $lang_index['User info'] . '</strong></dt><dd>' . $lang_index['Newest user'] . ': <a href="profile.php?id=' . $stats['last_user']['id'] . '">' . pun_htmlspecialchars($stats['last_user']['username']) . '</a></dd>';

if ($pun_config['o_users_online'] == 1) {
    // Fetch users online info and generate strings for output
    $num_guests = 0;
    $users = array();
    $result = $db->query('SELECT user_id, ident FROM ' . $db->prefix . 'online WHERE idle=0 ORDER BY ident') or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    while ($pun_user_online = $db->fetch_assoc($result)) {
        if ($pun_user_online['user_id'] > 1) {
            $users[] = '<dd><a href="profile.php?id=' . $pun_user_online['user_id'] . '">' . pun_htmlspecialchars($pun_user_online['ident']) . '</a>';
        } else {
            ++$num_guests;
        }
    }

    $num_users = sizeof($users);
    echo '<dd>' . $lang_index['Users online'] . ': <strong>' . $num_users . '</strong></dd> <dd>' . $lang_index['Guests online'] . ': <strong>' . $num_guests . '</strong></dd> </dl>';


    if ($num_users > 0) {
        echo '<dl id="onlinelist" class= "clearb"> <dt><strong>' . $lang_index['Online'] . ': </strong></dt>' . implode(',</dd> ', $users) . '</dd> </dl>';
    } else {
        echo '<div class="clearer"></div>';
    }

} else {
    echo '</dl><div class="clearer"></div>';
}

echo '</div></div></div>';

$footer_style = 'index';
require_once PUN_ROOT . 'footer.php';
