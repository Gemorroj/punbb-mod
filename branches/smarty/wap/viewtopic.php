<?php define('PUN_ROOT', '../');

require_once(PUN_ROOT . 'include/common.php');
require_once(PUN_ROOT . 'include/file_upload.php');
require_once(PUN_ROOT . 'lang/' . $pun_user['language'] . '/post.php');

require_once(PUN_ROOT . 'wap/header.php');

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($id < 1 && $pid < 1) {
    wap_message($lang_common['Bad request']);
}

// Load the viewtopic.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';

// If a post ID is specified we determine topic ID and page number so we can redirect to the correct message
if ($pid) {

    $result = $db->query('SELECT `topic_id` FROM `' . $db->prefix . 'posts` WHERE `id`=' . $pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }

    $id = $db->result($result);

    // Determine on what page the post is located (depending on $pun_user['disp_posts'])
    $result = $db->query('SELECT `id` FROM `' . $db->prefix . 'posts` WHERE `topic_id`=' . $id . ' ORDER BY `posted`') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $num_posts = $db->num_rows($result);

    for ($i = 0; $i < $num_posts; ++$i) {
        $cur_id = $db->result($result, $i);
        if ($cur_id == $pid) {
            break;
        }
    }

    ++$i; // we started at 0

    $_GET['p'] = ceil($i / $pun_user['disp_posts']);

} else if ($_GET['action'] == 'new' && !$pun_user['is_guest']) {
    // If action=new, we redirect to the first new post (if any)

    $result = $db->query('SELECT MIN(id) FROM ' . $db->prefix . 'posts WHERE topic_id=' . $id . ' AND posted>' . $pun_user['last_visit']) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $first_new_post_id = $db->result($result);

    if ($first_new_post_id) {
        wap_redirect('viewtopic.php?pid=' . $first_new_post_id . '#p' . $first_new_post_id);
    } else {
        // If there is no new post, we go to the last post
        wap_redirect('viewtopic.php?id=' . $id . '&action=last');
    }
} else if ($_GET['action'] == 'last') {
    // If action=last, we redirect to the last post

    $result = $db->query('SELECT MAX(id) FROM ' . $db->prefix . 'posts WHERE topic_id=' . $id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $last_post_id = $db->result($result);

    if ($last_post_id) {
        wap_redirect('viewtopic.php?pid=' . $last_post_id . '#p' . $last_post_id);
    }
}

// Fetch some info about the topic
if (!$pun_user['is_guest']) {
    $result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, s.user_id AS is_subscribed, lt.log_time FROM ' . $db->prefix . 'topics AS t INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id LEFT JOIN ' . $db->prefix . 'subscriptions AS s ON (t.id=s.topic_id AND s.user_id=' . $pun_user['id'] . ') LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ') LEFT JOIN ' . $db->prefix . 'log_topics AS lt ON (lt.user_id=' . $pun_user['id'] . ' AND lt.topic_id=t.id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=' . $id . ' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, 0 FROM ' . $db->prefix . 'topics AS t INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=' . $id . ' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
}

if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_topic = $db->fetch_assoc($result);

// REAL MARK TOPIC AS READ MOD BEGIN
if (!$pun_user['is_guest']) {

    $message_stack = array();

    if ($cur_topic['log_time'] == null) {
        $db->query('INSERT INTO ' . $db->prefix . 'log_topics (user_id, forum_id, topic_id, log_time) VALUES (' . $pun_user['id'] . ', ' . $cur_topic['forum_id'] . ', ' . $id . ', ' . $_SERVER['REQUEST_TIME'] . ')') or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
    } else {
        $db->query('UPDATE ' . $db->prefix . 'log_topics SET forum_id=' . $cur_topic['forum_id'] . ', log_time=' . $_SERVER['REQUEST_TIME'] . ' WHERE topic_id=' . $id . ' AND user_id=' . $pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
    }

    $result = $db->query('SELECT t.id, t.last_post, lt.log_time FROM ' . $db->prefix . 'topics AS t LEFT JOIN ' . $db->prefix . 'log_topics AS lt ON lt.topic_id=t.id AND lt.user_id=' . $pun_user['id'] . ' WHERE t.forum_id = ' . $cur_topic['forum_id'] . ' AND t.last_post > ' . $_SERVER['REQUEST_TIME'] . '-' . $pun_user['mark_after'] . ' ') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

    $find_new = false;
    while ($topic = $db->fetch_assoc($result)) {

        if ((!$topic['log_time'] && $topic['last_post'] > $pun_user['last_visit']) || ($topic['log_time'] < $topic['last_post'] && $topic['last_post'] > $pun_user['last_visit'])) {
            $find_new = true;
            break;
        }
    }

    if (!$find_new) {

        $requestTime = $_SERVER['REQUEST_TIME'] + 10;
        $result = $db->query('UPDATE ' . $db->prefix . 'log_forums SET log_time=' . $requestTime . ' WHERE forum_id=' . $cur_topic['forum_id'] . ' AND user_id=' . $pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());

        if ($db->affected_rows() < 1) {

            $result = $db->query('INSERT INTO ' . $db->prefix . 'log_forums (user_id, forum_id, log_time) VALUES (' . $pun_user['id'] . ', ' . $cur_topic['forum_id'] . ', ' . $requestTime . ')');
            $dberror = $db->error();

            if ($dberror['error_no'] && $dberror['error_no'] != 1062) {
                error('Unable to insert reading_mark info.', __FILE__, __LINE__, $db->error());
            }
        }
    }
}
// REAL MARK TOPIC AS READ MOD END


// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_topic['moderators']) ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

/*
// Can we or can we not post replies?
if (!$cur_topic['closed']) {
    if ((!$cur_topic['post_replies'] && ($pun_user['g_post_replies'] == 1 || $pun_user['g_post_replies'] == 2)) || $cur_topic['post_replies'] == 1 || $is_admmod) {
        $post_link = '<a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
    } else {
        $post_link = '&#160;';
    }
} else {
    $post_link = $lang_topic['Topic closed'];

    if ($is_admmod) {
        $post_link .= ' / <a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
    }
}
*/

// Can we or can we not download attachments?
$can_download = (!$cur_topic['file_download'] && $pun_user['g_file_download'] == 1) || $cur_topic['file_download'] == 1 || $is_admmod;

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_posts'] * ($p - 1);

// Generate paging links
/// MOD VIEW ALL PAGES IN ONE BEGIN
// ORIGINAL
//$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewtopic.php?id='.$id);
if ($_GET['action'] == 'all') {
    $p = ($num_pages + 1);
}

$paging_links = $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'viewtopic.php?id=' . $id);

if ($_GET['action'] == 'all' && !$pid) {
    $pun_user['disp_posts'] = $cur_topic['num_replies'] + 1;
}
/// MOD VIEW ALL PAGES IN ONE END

if ($pun_config['o_censoring'] == 1) {
    $cur_topic['subject'] = censor_words($cur_topic['subject']);
}


// !$pun_user['is_guest'] && - wft?
$quickpost = false;
if ($pun_config['o_quickpost'] == 1 &&
// !$pun_user['is_guest'] &&
    ($cur_topic['post_replies'] == 1 || (!$cur_topic['post_replies'] && $pun_user['g_post_replies'] == 1)) && (!$cur_topic['closed'] || $is_admmod)
) {
    $required_fields = array('req_message' => $lang_common['Message']);
    $quickpost = true;
}

/*
if (!$pun_user['is_guest'] && $pun_config['o_subscriptions'] == 1) {
    if ($cur_topic['is_subscribed']) {
        // I apologize for the variable naming here. It's a mix of subscription and action I guess :-)
        $subscraction = '<div class="con">'.$lang_topic['Is subscribed'].' - <a href="misc.php?unsubscribe='.$id.'">'.$lang_topic['Unsubscribe'].'</a></div>';
    } else {
        $subscraction = '<div class="con"><a href="misc.php?subscribe='.$id.'">'.$lang_topic['Subscribe'].'</a></div>';
    }
} else {
    $subscraction = null;
}
*/

define('PUN_ALLOW_INDEX', 1);

include_once PUN_ROOT . 'include/parser.php';

// !!!
// hcs AJAX POLL MOD BEGIN
$show_poll = '';
if ($pun_config['poll_enabled'] == 1) {
    include PUN_ROOT . 'include/poll/poll.inc.php';

    if ($cur_topic['has_poll']) {
        if ($_POST['pollid']) {
            if (is_array($_POST['poll_vote'])) {
                foreach ($_POST['poll_vote'] as $var) {
                    $q .= $var . '=' . $var . '&';
                }
                $q = rtrim($q, '&');
            } else {
                $q = 'poll_vote=' . $_POST['poll_vote'];
            }
            $warning = $Poll->vote($_POST['pollid'], $q);
        }

        $show_poll = $Poll->wap_showPoll($cur_topic['has_poll'], $warning);
    }
}
// hcs AJAX POLL MOD END


/// MOD ANTISPAM BEGIN
if ($pun_config['antispam_enabled'] == 1 && $is_admmod) {
    $result = $db->query('
        SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online, spam.pattern, spam.id AS spam_id
        FROM ' . $db->prefix . 'posts AS p
        INNER JOIN ' . $db->prefix . 'users AS u ON u.id=p.poster_id
        INNER JOIN ' . $db->prefix . 'groups AS g ON g.g_id=u.group_id
        LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0)
        LEFT JOIN ' . $db->prefix . 'spam_repository AS spam ON spam.post_id=p.id WHERE p.topic_id=' . $id . '
        ORDER BY p.id
        LIMIT ' . $start_from . ',' . $pun_user['disp_posts'], true
    ) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query('
        SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online
        FROM ' . $db->prefix . 'posts AS p
        INNER JOIN ' . $db->prefix . 'users AS u ON u.id=p.poster_id
        INNER JOIN ' . $db->prefix . 'groups AS g ON g.g_id=u.group_id
        LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0)
        WHERE p.topic_id=' . $id . '
        ORDER BY p.id
        LIMIT ' . $start_from . ',' . $pun_user['disp_posts'], true
    ) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
}
/// MOD ANTISPAM END

$posts = $pids = array();
while ($cur_post = $db->fetch_assoc($result)) {

    $posts[] = $cur_post;
    $pids[] = $cur_post['id']; // Need to fetch attachments from db.
}

$db->free_result($result);

// Retrieve the attachments
require_once PUN_ROOT . 'include/attach/fetch.php';

if ($pun_config['o_quickjump']) {
    include_once PUN_ROOT . 'include/quickjump.php';
}

// Increment "num_views" for topic
$db->query('UPDATE LOW_PRIORITY ' . $db->prefix . 'topics SET num_views=num_views+1 WHERE id=' . $id, true) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

$page_title = $pun_config['o_board_title'] . ' / ' . $cur_topic['forum_name'] . ' / ' . $cur_topic['subject'];

$smarty->assign('show_poll', $show_poll);
$smarty->assign('page_title', $page_title);

$smarty->assign('pun_start', $pun_start);
$smarty->assign('pun_user', $pun_user);

$smarty->assign('conditions', $conditions);
$smarty->assign('is_admmod', $is_admmod);
$smarty->assign('can_download', $can_download);
$smarty->assign('quickpost', $quickpost);

$smarty->assign('lang_topic', $lang_topic);
$smarty->assign('lang_fu', $lang_fu);
$smarty->assign('lang_post', $lang_post);
$smarty->assign('lang_pms', $lang_pms);

require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';
$smarty->assign('lang_misc', $lang_misc);

$smarty->assign('forum_id', $cur_topic['forum_id']);
$smarty->assign('id', $id);
$smarty->assign('p', $p);

$smarty->assign('cur_topic', $cur_topic);
$smarty->assign('posts', $posts);
$smarty->assign('start_from', $start_forum);

$smarty->assign('attachments', $attachments);
$smarty->assign('paging_links', $paging_links);

$smarty->assign('basename', basename($_SERVER['PHP_SELF']));


$smarty->display('viewtopic.tpl');
