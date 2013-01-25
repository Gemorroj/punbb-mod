<?php
define('PUN_ROOT', './');

require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/file_upload.php';


require PUN_ROOT . 'lang/' . $pun_user['language'] . '/post.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
}


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($id < 1 && $pid < 1) {
    message($lang_common['Bad request']);
}

// Load the viewtopic.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';


// If a post ID is specified we determine topic ID and page number so we can redirect to the correct message
if ($pid) {
    $result = $db->query('SELECT `topic_id` FROM `' . $db->prefix . 'posts` WHERE `id`=' . $pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        message($lang_common['Bad request']);
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
} // If action=new, we redirect to the first new post (if any)
else if ($_GET['action'] == 'new' && !$pun_user['is_guest']) {
    $result = $db->query('SELECT MIN(id) FROM ' . $db->prefix . 'posts WHERE topic_id=' . $id . ' AND posted>' . $pun_user['last_visit']) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $first_new_post_id = $db->result($result);

    if ($first_new_post_id) {
        redirect('viewtopic.php?pid=' . $first_new_post_id . '#p' . $first_new_post_id, '');
    } else {
        // If there is no new post, we go to the last post
        redirect('viewtopic.php?id=' . $id . '&action=last', '');
    }
} else if ($_GET['action'] == 'last') {
    // If action=last, we redirect to the last post

    $result = $db->query('SELECT MAX(id) FROM ' . $db->prefix . 'posts WHERE topic_id=' . $id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $last_post_id = $db->result($result);

    if ($last_post_id) {
        redirect('viewtopic.php?pid=' . $last_post_id . '#p' . $last_post_id, '');
    }
}


// Fetch some info about the topic
if (!$pun_user['is_guest']) {
    $result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, s.user_id AS is_subscribed, lt.log_time FROM ' . $db->prefix . 'topics AS t INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id LEFT JOIN ' . $db->prefix . 'subscriptions AS s ON (t.id=s.topic_id AND s.user_id=' . $pun_user['id'] . ') LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ') LEFT JOIN ' . $db->prefix . 'log_topics AS lt ON (lt.user_id=' . $pun_user['id'] . ' AND lt.topic_id=t.id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=' . $id . ' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query('SELECT t.subject,t.has_poll, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, fp.file_download, 0 FROM ' . $db->prefix . 'topics AS t INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id=' . $id . ' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
}

if (!$db->num_rows($result)) {
    message($lang_common['Bad request']);
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

// Can we or can we not post replies?
if (!$cur_topic['closed']) {
    if ((!$cur_topic['post_replies'] && ($pun_user['g_post_replies'] == 1 || $pun_user['g_post_replies'] == 2)) || $cur_topic['post_replies'] == 1 || $is_admmod) {
        $post_link = '<a href="post.php?tid=' . $id . '">' . $lang_topic['Post reply'] . '</a>';
    } else {
        $post_link = '&#160;';
    }
} else {
    $post_link = $lang_topic['Topic closed'];

    if ($is_admmod) {
        $post_link .= ' / <a href="post.php?tid=' . $id . '">' . $lang_topic['Post reply'] . '</a>';
    }
}

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


// !$pun_user['is_guest'] && - Это поебень
$quickpost = false;
if ($pun_config['o_quickpost'] == 1 &&
// !$pun_user['is_guest'] &&
    ($cur_topic['post_replies'] == 1 || (!$cur_topic['post_replies'] && $pun_user['g_post_replies'] == 1)) &&
    (!$cur_topic['closed'] || $is_admmod)
) {
    $required_fields = array('req_message' => $lang_common['Message']);
    $quickpost = true;
}

if (!$pun_user['is_guest'] && $pun_config['o_subscriptions'] == 1) {
    if ($cur_topic['is_subscribed']) {
        // I apologize for the variable naming here. It's a mix of subscription and action I guess :-)
        $subscraction = '<p class="subscribelink clearb">' . $lang_topic['Is subscribed'] . ' - <a href="misc.php?unsubscribe=' . $id . '">' . $lang_topic['Unsubscribe'] . '</a></p>';
    } else {
        $subscraction = '<p class="subscribelink clearb"><a href="misc.php?subscribe=' . $id . '">' . $lang_topic['Subscribe'] . '</a></p>';
    }
} else {
    $subscraction = '<div class="clearer"></div>';
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title'] . ' / ' . $cur_topic['subject']);

define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT . 'header.php';

if ($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
    $jsHelper->add(PUN_ROOT . 'js/karma.js');
}
echo '<div class="linkst"><div class="inbox">
<p class="pagelink conl">' . $paging_links . '</p>
<p class="postlink conr">' . $post_link . '</p>
<ul><li><a href="index.php">' . $lang_common['Index'] . '</a></li><li> &#187; <a href="viewforum.php?id=' . $cur_topic['forum_id'] . '">' . pun_htmlspecialchars($cur_topic['forum_name']) . '</a></li><li> &#187; ' . pun_htmlspecialchars($cur_topic['subject']) . '</li></ul>
<div class="clearer"></div></div></div>';


include_once PUN_ROOT . 'include/parser.php';
// hcs AJAX POLL MOD BEGIN
if ($pun_config['poll_enabled'] == 1) {
    include_once PUN_ROOT . 'include/poll/poll.inc.php';
    if ($cur_topic['has_poll']) {
        echo $Poll->showPoll($cur_topic['has_poll']);
    }
}
// hcs AJAX POLL MOD END

$bg_switch = true; // Used for switching background color in posts
$post_count = 0; // Keep track of post numbers

// Retrieve the posts (and their respective poster/online status)

// MOD ANTISPAM BEGIN
/*
// ORIGINAL:
$result = $db->query('
    SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online
    FROM '.$db->prefix.'posts AS p
    INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id
    INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id
    LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0)
    WHERE p.topic_id='.$id.'
    ORDER BY p.id
    LIMIT '.$start_from.','.$pun_user['disp_posts'], true
) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
*/
if ($pun_config['antispam_enabled'] == 1 && $is_admmod) {
    $result = $db->query('
        SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online, spam.pattern, spam.id AS spam_id
        FROM ' . $db->prefix . 'posts AS p
        INNER JOIN ' . $db->prefix . 'users AS u ON u.id=p.poster_id
        INNER JOIN ' . $db->prefix . 'groups AS g ON g.g_id=u.group_id
        LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0)
        LEFT JOIN ' . $db->prefix . 'spam_repository AS spam ON spam.post_id=p.id
        WHERE p.topic_id=' . $id . '
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
    $pids[] = $cur_post['id'];
}
$db->free_result($result);

// Retrieve the attachments
require PUN_ROOT . 'include/attach/fetch.php';

// insert popup info panel & its data (javascript)
if ($pun_config['file_popup_info'] == 1) {
    include PUN_ROOT . 'include/attach/popup_data.php';
}

foreach ($posts as $cur_post) {
    $post_count++;
    $user_avatar = $is_online = $signature = '';
    $user_info = $user_contacts = $post_actions = array();

    // If the poster is a registered user.
    if ($cur_post['poster_id'] > 1) {
        //
        // QUICK QUOTE MOD BEGIN
        //
        // ORIGINAL:
        // $username = '<a class="profile" href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';
        // MOD: QUICK QUOTE - 1 LINE FOLLOWING CODE MODIFIED, 3 NEW LINES ADDED
        if (!$pun_user['is_guest']) {
            $username = '<a href="javascript:pasteN(\'' . pun_htmlspecialchars($cur_post['username']) . '\');">' . pun_htmlspecialchars($cur_post['username']) . '</a>';
        } else {
            $username = '<a href="profile.php?id=' . $cur_post['poster_id'] . '">' . pun_htmlspecialchars($cur_post['username']) . '</a>';
        }
        // QUICK QUOTE MOD END


        $user_title = get_title($cur_post);

        if ($pun_config['o_censoring'] == 1) {
            $user_title = censor_words($user_title);
        }

        // Format the online indicator
        $is_online = ($cur_post['is_online'] == $cur_post['poster_id']) ? '<strong>' . $lang_topic['Online'] . '</strong>' : $lang_topic['Offline'];

        $user_avatar = pun_show_avatar();

        // We only show location, register date, post count and the contact links if "Show user info" is enabled
        if ($pun_config['o_show_user_info'] == 1) {
            if ($cur_post['location']) {
                if ($pun_config['o_censoring'] == 1) {
                    $cur_post['location'] = censor_words($cur_post['location']);
                }

                $user_info[] = '<dd>' . $lang_topic['From'] . ': ' . pun_htmlspecialchars($cur_post['location']);
            }

            $user_info[] = '<dd>' . $lang_common['Registered'] . ': ' . date($pun_config['o_date_format'], $cur_post['registered']);

            if ($pun_config['o_show_post_count'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
                $user_info[] = '<dd>' . $lang_common['Posts'] . ': ' . $cur_post['num_posts'];
            }


            if ($pun_config['o_show_post_karma'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
                $q = $db->fetch_row($db->query('
                    SELECT COUNT(1),
                    (SELECT COUNT(1) FROM `' . $db->prefix . 'karma` WHERE `vote` = "-1" AND `to` = ' . $cur_post['poster_id'] . ')
                    FROM `' . $db->prefix . 'karma` WHERE `vote` = "1" AND `to` = ' . $cur_post['poster_id']
                ));

                $karma['plus'] = intval($q[0]);
                $karma['minus'] = intval($q[1]);
                $karma['karma'] = $karma['plus'] - $karma['minus'];
                unset($q);

                if ($pun_user['is_guest']) {
                    $user_info[] = '<dd>' . $lang_common['Karma'] . ': ' . $karma['karma'];
                } else if ($db->num_rows($db->query('SELECT 1 FROM `' . $db->prefix . 'karma` WHERE `id`=' . $pun_user['id'] . ' AND `to`=' . $cur_post['poster_id'] . ' LIMIT 1'))) {
                    $user_info[] = '<dd>' . $lang_common['Karma'] . ': ' . $karma['karma'];
                } else {
                    $user_info[] = '<dd>' . $lang_common['Karma'] . ': <span class="num_' . $cur_post['poster_id'] . '">' . $karma['karma'] . '</span> <span class="karma_' . $cur_post['poster_id'] . '"><a href="javascript:karmaPlus(' . $cur_post['poster_id'] . ');">+</a>/<a href="javascript:karmaMinus(' . $cur_post['poster_id'] . ');">-</a></span>';
                }
            }

            // QUICK QUOTE MOD BEGIN
            // MOD: QUICK QUOTE - 1 LINE FOLLOWING CODE ADDED
            $user_contacts[] = '<a href="profile.php?id=' . $cur_post['poster_id'] . '">' . $lang_common['Profile'] . '</a>';
            // QUICK QUOTE MOD END

            // Now let's deal with the contact links (E-mail and URL)
            if ((!$cur_post['email_setting'] && !$pun_user['is_guest']) || $pun_user['g_id'] < PUN_GUEST) {
                $user_contacts[] = '<a href="mailto:' . $cur_post['email'] . '">' . $lang_common['E-mail'] . '</a>';
            } else if ($cur_post['email_setting'] == 1 && !$pun_user['is_guest']) {
                $user_contacts[] = '<a href="misc.php?email=' . $cur_post['poster_id'] . '">' . $lang_common['E-mail'] . '</a>';
            }

            // PMS MOD BEGIN
            require PUN_ROOT . 'include/pms/viewtopic_PM-link.php';
            // PMS MOD END

            if ($cur_post['url']) {
                $user_contacts[] = '<a href="' . pun_htmlspecialchars($cur_post['url']) . '">' . $lang_topic['Website'] . '</a>';
            }
        }

        if ($pun_user['g_id'] < PUN_GUEST) {
            $user_info[] = '<dd>IP: <a href="moderate.php?get_host=' . $cur_post['id'] . '">' . $cur_post['poster_ip'] . '</a>';

            if ($cur_post['admin_note']) {
                $user_info[] = '<dd>' . $lang_topic['Note'] . ': <strong>' . pun_htmlspecialchars($cur_post['admin_note']) . '</strong>';
            }
        }
    } else {
        // If the poster is a guest (or a user that has been deleted)
        $username = pun_htmlspecialchars($cur_post['username']);
        $user_title = get_title($cur_post);

        if ($pun_user['g_id'] < PUN_GUEST) {
            $user_info[] = '<dd>IP: <a href="moderate.php?get_host=' . $cur_post['id'] . '">' . $cur_post['poster_ip'] . '</a>';
        }

        if ($pun_config['o_show_user_info'] == 1 && $cur_post['poster_email'] && !$pun_user['is_guest']) {
            $user_contacts[] = '<a href="mailto:' . $cur_post['poster_email'] . '">' . $lang_common['E-mail'] . '</a>';
        }
    }

    // Generation post action array (quote, edit, delete etc.)
    if (!$is_admmod) {
        /*
        if(!$pun_user['is_guest']){
            $post_actions[] = '<li class="postreport"><a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>';
        }
        */

        if (!$cur_topic['closed']) {
            if ($cur_post['poster_id'] == $pun_user['id']) {
                if ((($start_from + $post_count) == 1 && $pun_user['g_delete_topics'] == 1) || (($start_from + $post_count) > 1 && $pun_user['g_delete_posts'] == 1)) {
                    $post_actions[] = '<li class="postdelete"><a href="delete.php?id=' . $cur_post['id'] . '">' . $lang_topic['Delete'] . '</a>';
                }
                if ($pun_user['g_edit_posts'] == 1) {
                    $post_actions[] = '<li class="postedit"><a href="edit.php?id=' . $cur_post['id'] . '">' . $lang_topic['Edit'] . '</a>';
                }
            }


            // QUICK QUOTE MOD BEGIN

            // MOD: QUICK REPLY - FOLLOWING "IF" CODE BLOCK MODIFIED
            if ((!$cur_topic['post_replies'] && $pun_user['g_post_replies'] == 1) || $cur_topic['post_replies'] == 1) {
                $post_actions[] = '<li class="postquote"><a href="post.php?tid=' . $id . '&amp;qid=' . $cur_post['id'] . '">' . $lang_topic['Post reply'] . '</a>';
                if (!$pun_user['is_guest']) {
                    //$post_actions[] = '<li class="postquote"><a onclick="copyPID(\''.$cur_post['id'].'\');" onmouseover="copyQ(\''.pun_htmlspecialchars($cur_post['username']).'\');" href="javascript:pasteQ();">'.$lang_topic['Quote'].'</a>';
                    $post_actions[] = '<li class="postquote"><a href="javascript:pasteQ(\'' . $cur_post['id'] . '\', \'' . pun_htmlspecialchars($cur_post['username']) . '\');">' . $lang_topic['Quote'] . '</a>';
                }
            }

            // QUICK QUOTE MOD END
        }
    } else {
        // QUICK QUOTE MOD BEGIN
        $post_actions[] = '<li class="postdelete"><a href="delete.php?id=' . $cur_post['id'] . '">' . $lang_topic['Delete'] . '</a>' . $lang_topic['Link separator'] . '</li><li class="postedit"><a href="edit.php?id=' . $cur_post['id'] . '">' . $lang_topic['Edit'] . '</a>' . $lang_topic['Link separator'] . '</li><li class="postquote"><a href="post.php?tid=' . $id . '&amp;qid=' . $cur_post['id'] . '">' . $lang_topic['Post reply'] . '</a>' . $lang_topic['Link separator'] . '</li><li class="postquote"><a id="pid' . $cur_post['id'] . '" href="javascript:pasteQ(\'' . $cur_post['id'] . '\', \'' . pun_htmlspecialchars($cur_post['username']) . '\');">' . $lang_topic['Quote'] . '</a>';
        // QUICK QUOTE MOD END
    }

    // Switch the background color for every message.
    $bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
    $vtbg = ($bg_switch) ? ' roweven' : ' rowodd';


    // Perform the main parsing of the message (BBCode, smilies, censor words etc)
    $cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);

    // Do signature parsing/caching
    if ($cur_post['signature'] && $pun_user['show_sig']) {
        if (isset($signature_cache[$cur_post['poster_id']])) {
            $signature = $signature_cache[$cur_post['poster_id']];
        } else {
            $signature = parse_signature($cur_post['signature']);
            $signature_cache[$cur_post['poster_id']] = $signature;
        }
    }


    echo '<div id="p' . $cur_post['id'] . '" class="blockpost' . $vtbg;
    if (($post_count + $start_from) == 1) {
        echo ' firstpost';
    }
    echo '"><h2><span><span class="conr">#' . ($start_from + $post_count) . ' </span><a href="viewtopic.php?pid=' . $cur_post['id'] . '#p' . $cur_post['id'] . '">' . format_time($cur_post['posted']) . '</a></span></h2><div class="box"><div class="inbox"><div class="postleft"><dl><dt><strong>' . $username . '</strong></dt><dd class="usertitle"><strong>' . $user_title . '</strong></dd><dd class="postavatar">' . $user_avatar . '</dd>';

    if ($user_info) {
        echo implode('</dd>', $user_info) . '</dd>';
    }

    if ($user_contacts) {
        echo '<dd class="usercontacts">' . implode(' ', $user_contacts) . '</dd>';
    }

    echo '</dl></div><div class="postright"><h3>';
    if (($post_count + $start_from) > 1) {
        echo ' Re: ';
    }
    echo pun_htmlspecialchars($cur_topic['subject']) . '</h3><div class="postmsg">' . $cur_post['message'];

    //$save_attachments = $attachments;
    //$attachments = array_filter($attachments, 'filter_attachments_of_post');
    if ($attachments[$cur_post['id']]) {
        echo '<br /><fieldset><legend>' . $lang_fu['Attachments'] . '</legend>';
        include PUN_ROOT . 'include/attach/view_attachments.php';
        echo '</fieldset>';
    }
    //$attachments = $save_attachments;

    /// MOD ANTISPAM BEGIN
    if ($pun_config['antispam_enabled'] == 1 && $is_admmod) {
        if (isset($cur_post['spam_id'])) {
            include_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';
            echo '<hr /><br />' . $lang_misc['Antispam pattern'] . ' - ' . pun_htmlspecialchars($cur_post['pattern']) . '<br /><br /><a href="./antispam_misc.php?action=show&amp;id=' . $cur_post['spam_id'] . '" onclick=\'window.open("' . $pun_config['o_base_url'] . '/antispam_misc.php?action=show&amp;id=' . $cur_post['spam_id'] . '", "Spam", "width=500,height=500,resizable=yes,scrollbars=yes"); return false;\' >' . $lang_misc['Antispam look mess'] . '</a> | <a href="./antispam_misc.php?action=allow&amp;id=' . $cur_post['spam_id'] . '">' . $lang_misc['Antispam tread'] . '</a> | <a href="./antispam_misc.php?action=deny&amp;id=' . $cur_post['spam_id'] . '">' . $lang_misc['Antispam del'] . '</a>';
        }
    }
    /// MOD ANTISPAM END

    if ($cur_post['edited']) {
        echo '<p class="postedit"><em>' . $lang_topic['Last edit'] . ' ' . pun_htmlspecialchars($cur_post['edited_by']) . ' (' . format_time($cur_post['edited']) . ')</em></p>';
    }

    echo '</div>';

    if ($signature) {
        echo '<div class="postsignature"><hr />' . $signature . '</div>';
    }

    echo '</div><div class="clearer"></div><div class="postfootleft">';
    if ($cur_post['poster_id'] > 1) {
        echo '<p>' . $is_online . '</p>';
    }
    echo '</div><div class="postfootright">';
    if ($post_actions) {
        echo '<ul>' . implode($lang_topic['Link separator'] . '</li>', $post_actions) . '</li></ul></div>';
    } else {
        echo '<div> </div></div>';
    }
    echo '</div></div></div>';
}

echo '<div class="postlinksb"><div class="inbox"><p class="postlink conr">' . $post_link . '</p><p class="pagelink conl">' . $paging_links . '</p><ul><li><a href="index.php">' . $lang_common['Index'] . '</a></li><li> &#187; <a href="viewforum.php?id=' . $cur_topic['forum_id'] . '">' . pun_htmlspecialchars($cur_topic['forum_name']) . '</a></li><li> &#187; ' . pun_htmlspecialchars($cur_topic['subject']) . '</li></ul>' . $subscraction . '</div></div>';

// Display quick post if enabled

// QUICK QUOTE MOD HTML ORIGINAL:
// <form method="post" action="post.php?tid=<?php echo $id
//>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">

if ($quickpost) {
    if (!$pun_user['is_guest']) {
        $form_user = pun_htmlspecialchars($pun_user['username']);
    } else {
        $form_user = 'Guest';
    }

    echo '<div class="blockform"><h2><span>' . $lang_topic['Quick post'] . '</span></h2><div class="box"><form onkeypress="ctrlSend(event);" id="post" method="post" action="post.php?tid=' . $id . '" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}"><div class="inform"><fieldset><legend>' . $lang_common['Write message legend'] . '</legend><div class="infldset txtarea">';

    if ($pun_config['o_antiflood']) {
        echo '<input type="hidden" name="form_t" value="' . $_SERVER['REQUEST_TIME'] . '" />';
    }

    echo '<input type="hidden" name="form_sent" value="1" /><input type="hidden" name="form_user" value="' . $form_user . '" />';

    // Ввод имени для гостей
    if ($pun_user['is_guest']) {
        echo '<label class="conl"><strong>Имя</strong><br /><input type="text" name="req_username" value="" size="25" maxlength="25" /><br /></label><label class="conl">E-mail<br /><input type="text" name="email" value="" size="50" maxlength="50" /><br /></label><div class="clearer"></div>';
    }

    echo '<label><textarea name="req_message" id="req_message" rows="6" cols="64"></textarea></label><ul id="buttonmenu"><li><a id="dectxt" href="javascript:resizeTextarea(-80)">-</a></li><li><a id="inctxt" href="javascript:resizeTextarea(80)">+</a></li></ul><ul class="bblinks"><li><a href="help.php#bbcode" onclick="window.open(this.href); return false;">' . $lang_common['BBCode'] . '</a>: ' . (($pun_config['p_message_bbcode'] == 1) ? $lang_common['on'] : $lang_common['off']) . '</li><li><a href="help.php#img" onclick="window.open(this.href); return false;">' . $lang_common['img tag'] . '</a>: ' . (($pun_config['p_message_img_tag'] == 1) ? $lang_common['on'] : $lang_common['off']) . '</li><li><a href="help.php#smilies" onclick="window.open(this.href); return false;">' . $lang_common['Smilies'] . '</a>: ' . (($pun_config['o_smilies'] == 1) ? $lang_common['on'] : $lang_common['off']) . '</li></ul>';

    if ($is_admmod) {
        echo '<label for="merge"><input type="checkbox" id="merge" name="merge" value="1" checked="checked" />' . $lang_post['Merge posts'] . '<br /></label>';
    }

    echo '</div></fieldset></div><p><input type="submit" name="submit" value="' . $lang_common['Submit'] . '" accesskey="s" /></p></form></div></div>';
}

// Increment "num_views" for topic
$db->query('UPDATE LOW_PRIORITY `' . $db->prefix . 'topics` SET `num_views`=`num_views`+1 WHERE id=' . $id, true) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

$forum_id = $cur_topic['forum_id'];
$footer_style = 'viewtopic';
require_once PUN_ROOT . 'footer.php';
