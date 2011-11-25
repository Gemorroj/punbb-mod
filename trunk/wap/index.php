<?php
define('PUN_ROOT', '../');
require PUN_ROOT.'include/common.php';


if (!$pun_user['g_read_board']) {
	wap_message($lang_common['No view']);
}


// Load the index.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/index.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']);
define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT . 'wap/header.php';

// REAL MARK TOPIC AS READ MOD BEGIN
// под вопросом!
if (!$pun_user['is_guest']) {
    $db->query('DELETE FROM `' . $db->prefix . 'log_forums` WHERE log_time < ' . ($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']) . ' AND user_id=' . $pun_user['id']) or error('Unable to delete marked as read forum info', __FILE__, __LINE__, $db->error());
}
// REAL MARK TOPIC AS READ MOD END

// Print the categories and forums


// Add Topic Title Info to Last Post column MOD BEGIN
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

    ORDER BY c.disp_position, c.id, f.disp_position
', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
// REAL MARK TOPIC AS READ MOD END

$cur_category = $cat_count = 0;
$out = '';
while ($cur_forum = $db->fetch_assoc($result)) {
    $moderators = null;

    // A new category since last iteration?
    if ($cur_forum['cid'] != $cur_category) {
        if ($cur_category) {
        	$out .= '</div></div>';
        }

        ++$cat_count;

        $out .= '<div><strong>' . pun_htmlspecialchars($cur_forum['cat_name']) . '</strong><br/></div><div class="in">';
        $cur_category = $cur_forum['cid'];
    }

    // Is this a redirect forum?
    if ($cur_forum['redirect_url']) {
        $forum_field = '<strong><a href="' . pun_htmlspecialchars($cur_forum['redirect_url']) . '">' . pun_htmlspecialchars($cur_forum['forum_name']) . '</a></strong>';
        $num_topics = $num_posts = '&#160;';
        $item_status = 'iredirect';
        $icon_text = $lang_common['Redirect icon'];
        $icon_type = 'icon';
    } else {
        $forum_field = '<strong><a href="viewforum.php?id=' . $cur_forum['fid'] . '">' . pun_htmlspecialchars($cur_forum['forum_name']) . '</a></strong>';
        $num_topics = $cur_forum['num_topics'];
        $num_posts = $cur_forum['num_posts'];
    }

    // If there is a last_post/last_poster.
    if ($cur_forum['last_post']) {
        // MOD:
        $last_post = '&#160;&#187; <a href="viewtopic.php?pid=' . $cur_forum['last_post_id'] . '#p' . $cur_forum['last_post_id'] . '">' . pun_htmlspecialchars($cur_forum['subject']) . '</a> (' . format_time($cur_forum['last_post']) . $lang_common['by'] . ' ' . pun_htmlspecialchars($cur_forum['last_poster']) . ')';
        // END MOD
    } else {
        $last_post = '&#160;';
    }
    $out .= $forum_field . ' (' . $num_topics . '/' . $num_posts . ')<br/>' . $last_post . '<hr/>';
}
echo rtrim($out, '<hr/>');

// Did we output any categories and forums?
if ($cur_category > 0) {
	echo '</div>';
} else {
	echo '<div class="box"><p>' . $lang_index['Empty board'] . '</p></div>';
}

if (!$pun_user['is_guest']) {
	echo '<div><a class="con" href="search.php?action=show_new">' . $lang_common['Show new posts'] . '</a><br/><a class="con" href="misc.php?action=markread">' . $lang_common['Mark all as read'] . '</a><br/></div>';
}

// Collect some statistics from the database
$result = $db->query('SELECT COUNT(1) - 1 FROM `' . $db->prefix . 'users` LIMIT 1') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['total_users'] = $db->result($result);

$result = $db->query('SELECT `id`, `username` FROM `' . $db->prefix . 'users` ORDER BY `registered` DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
$stats['last_user'] = $db->fetch_assoc($result);

$result = $db->query('SELECT SUM(`num_topics`), SUM(`num_posts`) FROM `' . $db->prefix . 'forums` LIMIT 1') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);

echo '<div class="navlinks">' . generate_wap_navlinks() . '</div><div class="incqbox" style="color:#fff;margin:1%;padding:2pt;">' . $lang_index['No of users'] . ': ' . $stats['total_users'] . '<br/>' . $lang_index['No of topics'] . ': ' . $stats['total_topics'] . '<br/>' . $lang_index['No of posts'] . ': ' . $stats['total_posts'] . '<br/>';

$num_users = 0;
if ($pun_config['o_users_online'] == 1) {
    // Fetch users online info and generate strings for output
    $num_guests = 0;
    $users = array();
    $result = $db->query('SELECT user_id, ident FROM ' . $db->prefix . 'online WHERE idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    while($pun_user_online = $db->fetch_assoc($result)) {
        if ($pun_user_online['user_id'] > 1) {
            $users[] = '<a href="profile.php?id=' . $pun_user_online['user_id'] . '">' . pun_htmlspecialchars($pun_user_online['ident']) . '</a> ';
        } else {
        	++$num_guests;
        }
    }

    $num_users = sizeof($users);
    echo $lang_index['Users online'] . ': ' . $num_users . '<br/>' . $lang_index['Guests online'] . ': ' . $num_guests . '<br/>';
}

echo '</div><div class="red">';

if ($pun_config['o_users_online'] && $num_users > 0) {
	echo $lang_index['Online'] . ': ' . implode(', ', $users);
}

echo '</div>';

$footer_style = 'index';
require_once PUN_ROOT . 'wap/footer.php';

?>
