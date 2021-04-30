<?php

\define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';

require_once PUN_ROOT.'wap/header.php';

$getPageNumber = isset($_GET['p']) ? (int) $_GET['p'] : 1;

//require_once PUN_ROOT . 'wap/footer.php'; //cache quickjump

// This particular function doesn't require forum-based moderator access. It can be used
// by all moderators and admins.
if (isset($_GET['get_host'])) {
    if ($pun_user['g_id'] > PUN_MOD) {
        wap_message($lang_common['No permission']);
    }

    // Is get_host an IP address or a post ID?
    if (\preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $_GET['get_host'])) {
        $ip = $_GET['get_host'];
    } else {
        $get_host = \intval($_GET['get_host']);

        if ($get_host < 1) {
            wap_message($lang_common['Bad request']);
        }

        $result = $db->query('SELECT poster_ip FROM `'.$db->prefix.'posts` WHERE id='.$get_host) or error('Unable to fetch post IP address', __FILE__, __LINE__, $db->error());

        if (!$db->num_rows($result)) {
            wap_message($lang_common['Bad request']);
        }

        $ip = $db->result($result);
    }

    $whois = \gethostbyaddr($ip);
    if ($whois == $ip) {
        $whois = '';
    }

    $smarty->assign('page_title', $pun_config['o_board_title'].' / '.$lang_common['Info']);
    $smarty->assign('ip', $ip);
    $smarty->assign('whois', $whois);
    $smarty->display('moderate.get_host.tpl');

    exit();
}

// All other functions require moderator/admin access
$fid = isset($_GET['fid']) ? \intval($_GET['fid']) : 0;
if ($fid < 1) {
    wap_message($lang_common['Bad request']);
}

$result = $db->query('SELECT `moderators` FROM `'.$db->prefix.'forums` WHERE id='.$fid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());

$moderators = $db->result($result);
$mods_array = ($moderators) ? \unserialize($moderators, ['allowed_classes' => false]) : [];

if (PUN_ADMIN != $pun_user['g_id'] && (PUN_MOD != $pun_user['g_id'] || !\array_key_exists($pun_user['username'], $mods_array))) {
    wap_message($lang_common['No permission']);
}

// Load the misc.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/misc.php';

// All other topic moderation features require a topic id in GET
if (isset($_GET['tid'])) {
    $tid = \intval($_GET['tid']);
    if ($tid < 1) {
        wap_message($lang_common['Bad request']);
    }

    // Fetch some info about the topic
    $result = $db->query('SELECT t.subject, t.num_replies, f.id AS forum_id, forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$fid.' AND t.id='.$tid.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }

    $cur_topic = $db->fetch_assoc($result);

    // Delete one or more posts
    if (isset($_POST['delete_posts']) || isset($_POST['delete_posts_comply'])) {
        $posts = $_POST['posts'];
        if (!$posts) {
            wap_message($lang_misc['No posts selected']);
        }

        if (isset($_POST['delete_posts_comply'])) {
            //confirm_referrer('moderate.php');
            if (\preg_match('/[^0-9,]/', $posts)) {
                wap_message($lang_common['Bad request']);
            }

            // Verify that the post IDs are valid
            $result = $db->query('SELECT 1 FROM '.$db->prefix.'posts WHERE id IN('.$posts.') AND topic_id='.$tid) or error('Unable to check posts', __FILE__, __LINE__, $db->error());

            if ($db->num_rows($result) != \substr_count($posts, ',') + 1) {
                wap_message($lang_common['Bad request']);
            }

            // Delete the posts
            $db->query('DELETE FROM '.$db->prefix.'posts WHERE id IN('.$posts.')') or error('Unable to delete posts', __FILE__, __LINE__, $db->error());

            require_once PUN_ROOT.'include/search_idx.php';
            strip_search_index($posts);

            // Delete attachments
            include PUN_ROOT.'lang/'.$pun_user['language'].'/fileup.php';

            include_once PUN_ROOT.'include/file_upload.php';
            delete_post_attachments($posts);

            // Get last_post, last_post_id, and last_poster for the topic after deletion
            $result = $db->query('SELECT id, poster, posted FROM '.$db->prefix.'posts WHERE topic_id='.$tid.' ORDER BY id DESC LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
            $last_post = $db->fetch_assoc($result);

            // How many posts did we just delete?
            $num_posts_deleted = \substr_count($posts, ',') + 1;

            // Update the topic
            $db->query('UPDATE '.$db->prefix.'topics SET last_post='.$last_post['posted'].', last_post_id='.$last_post['id'].', last_poster=\''.$db->escape($last_post['poster']).'\', num_replies=num_replies-'.$num_posts_deleted.' WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

            update_forum($fid);

            wap_redirect('viewtopic.php?id='.$tid);
        }

        $page_title = $pun_config['o_board_title'].' / '.$lang_misc['Moderate'];

        $smarty->assign('page_title', $page_title);
        $smarty->assign('lang_misc', $lang_misc);
        $smarty->assign('fid', $fid);
        $smarty->assign('tid', $tid);
        $smarty->assign('posts', $posts);

        $smarty->display('moderate.delete_posts.tpl');

        exit();
    }

    // Show the delete multiple posts view
    // Load the viewtopic.php language file
    require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

    // Used to disable the Move and Delete buttons if there are no replies to this topic
    $button_status = (!$cur_topic['num_replies']) ? ' disabled="bisabled"' : '';

    // Determine the post offset (based on $getPageNumber)
    $num_pages = \ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

    $p = ($getPageNumber <= 1 || $getPageNumber > $num_pages) ? 1 : $getPageNumber;
    $start_from = $pun_user['disp_posts'] * ($p - 1);

    $posts = $db->fetch_assoc($result);

    // Generate paging links
    $paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'moderate.php?fid='.$fid.'&amp;tid='.$tid);

    if (1 == $pun_config['o_censoring']) {
        $cur_topic['subject'] = censor_words($cur_topic['subject']);
    }

    $page_title = $pun_config['o_board_title'].' / '.$cur_topic['subject'];

    //moderate delete topic

    include_once PUN_ROOT.'include/parser.php';

    //$bg_switch = true; // Used for switching background color in posts
    //$post_count = 0; // Keep track of post numbers
    //$j = false;

    if ('all' != @$_GET['action']) {
        $act_all = ' LIMIT '.$start_from.', '.$pun_user['disp_posts'];
    } else {
        $act_all = null;
    }

    // Retrieve the posts (and their respective poster)
    $result = $db->query('SELECT u.title, u.num_posts, g.g_id, g.g_user_title, p.id, p.poster, p.poster_id, p.poster_ip, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by FROM `'.$db->prefix.'posts` AS p INNER JOIN `'.$db->prefix.'users` AS u ON u.id=p.poster_id INNER JOIN `'.$db->prefix.'groups` AS g ON g.g_id=u.group_id WHERE p.topic_id='.$tid.' ORDER BY p.id'.$act_all) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

    while ($cur_post = $db->fetch_assoc($result)) {
        $cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies'], $cur_post['id']);
        $posts[] = $cur_post;
    }

    $smarty->assign('page_title', $page_title);
    $smarty->assign('fid', $fid);
    $smarty->assign('tid', $tid);
    $smarty->assign('posts', $posts);
    $smarty->assign('cur_topic', $cur_topic);
    $smarty->assign('start_from', $start_from);
    $smarty->assign('lang_misc', $lang_misc);
    $smarty->assign('lang_topic', $lang_topic);
    $smarty->assign('paging_links', $paging_links);
    $smarty->assign('button_status', $button_status);

    $smarty->display('moderate.show_delete_posts.tpl');

    exit();
}

// Move one or more topics
if (isset($_REQUEST['move_topics']) || isset($_POST['move_topics_to'])) {
    if (isset($_POST['move_topics_to'])) {
        //confirm_referrer('moderate.php');

        if (\preg_match('/[^0-9,]/', $_POST['topics'])) {
            wap_message($lang_common['Bad request']);
        }

        $topics = \explode(',', $_POST['topics']);
        $move_to_forum = \intval($_POST['move_to_forum']);

        if (!$topics || $move_to_forum < 1) {
            wap_message($lang_common['Bad request']);
        }

        // Verify that the topic IDs are valid
        $result = $db->query('SELECT 1 FROM '.$db->prefix.'topics WHERE id IN('.\implode(',', $topics).') AND forum_id='.$fid) or error('Unable to check topics', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result) != \count($topics)) {
            wap_message($lang_common['Bad request']);
        }

        // Delete any redirect topics if there are any (only if we moved/copied the topic back to where it where it was once moved from)
        $db->query('DELETE FROM '.$db->prefix.'topics WHERE forum_id='.$move_to_forum.' AND moved_to IN('.\implode(',', $topics).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());

        // Move the topic(s)
        $db->query('UPDATE '.$db->prefix.'topics SET forum_id='.$move_to_forum.' WHERE id IN('.\implode(',', $topics).')') or error('Unable to move topics', __FILE__, __LINE__, $db->error());

        // Should we create redirect topics?
        if (isset($_POST['with_redirect'])) {
            foreach ($topics as $cur_topic) {
                // Fetch info for the redirect topic
                $result = $db->query('SELECT poster, subject, posted, last_post FROM '.$db->prefix.'topics WHERE id='.$cur_topic) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
                $moved_to = $db->fetch_assoc($result);

                // Create the redirect topic
                $db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, moved_to, forum_id) VALUES(\''.$db->escape($moved_to['poster']).'\', \''.$db->escape($moved_to['subject']).'\', '.$moved_to['posted'].', '.$moved_to['last_post'].', '.$cur_topic.', '.$fid.')') or error('Unable to create redirect topic', __FILE__, __LINE__, $db->error());
            }
        }

        update_forum($fid); // Update the forum FROM which the topic was moved
        update_forum($move_to_forum); // Update the forum TO which the topic was moved

        $redirect_msg = (\count($topics) > 1) ? $lang_misc['Move topics redirect'] : $lang_misc['Move topic redirect'];
        wap_redirect('viewforum.php?id='.$move_to_forum);
    }

    if (isset($_POST['move_topics'])) {
        $topics = $_POST['topics'] ?? [];

        if (!$topics) {
            wap_message($lang_misc['No topics selected']);
        }

        $topics = \implode(',', \array_map('intval', \array_keys($topics)));
        $action = 'multi';
    } else {
        $topics = \intval($_GET['move_topics']);

        if ($topics < 1) {
            wap_message($lang_common['Bad request']);
        }

        $action = 'single';
    }

    $page_title = $pun_config['o_board_title'].' / '.$lang_misc['Moderate'];

    $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

    while ($forum = $db->fetch_assoc($result)) {
        $forums[] = $forum;
    }

    $smarty->assign('page_title', $page_title);
    $smarty->assign('pun_user', $pun_user);
    $smarty->assign('lang_misc', $lang_misc);
    $smarty->assign('action', $action);

    $smarty->assign('fid', $fid);
    $smarty->assign('topics', $topics);
    $smarty->assign('forums', $forums);

    $smarty->display('moderate.move_topic.tpl');

    exit();
}

// Delete one or more topics
if (isset($_REQUEST['delete_topics']) || isset($_POST['delete_topics_comply'])) {
    $topics = $_POST['topics'] ?? [];

    if (!$topics) {
        wap_message($lang_misc['No topics selected']);
    }

    if (isset($_POST['delete_topics_comply'])) {
        //confirm_referrer('moderate.php');

        if (\preg_match('/[^0-9,]/', $topics)) {
            wap_message($lang_common['Bad request']);
        }

        require_once PUN_ROOT.'include/search_idx.php';

        // Verify that the topic IDs are valid
        $result = $db->query('SELECT 1 FROM '.$db->prefix.'topics WHERE id IN('.$topics.') AND forum_id='.$fid) or error('Unable to check topics', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result) != \substr_count($topics, ',') + 1) {
            wap_message($lang_common['Bad request']);
        }

        // hcs AJAX POLL MOD BEGIN
        if (1 == $pun_config['poll_enabled']) {
            include PUN_ROOT.'include/poll/poll.inc.php';
            $Poll->deleteTopic($topics);
        }
        // hcs AJAX POLL MOD END

        // Delete the topics and any redirect topics
        $db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.$topics.') OR moved_to IN('.$topics.')') or error('Unable to delete topic', __FILE__, __LINE__, $db->error());

        // Delete any subscriptions
        $db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE topic_id IN('.$topics.')') or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());

        // Create a list of the post ID's in this topic and then strip the search index
        $result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id IN('.$topics.')') or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());

        $post_ids = null;
        while ($row = $db->fetch_row($result)) {
            $post_ids .= ($post_ids) ? ','.$row[0] : $row[0];
        }

        // We have to check that we actually have a list of post ID's since we could be deleting just a redirect topic
        if ($post_ids) {
            strip_search_index($post_ids);
        }

        // Delete attachments
        include PUN_ROOT.'lang/'.$pun_user['language'].'/fileup.php';

        include_once PUN_ROOT.'include/file_upload.php';
        delete_post_attachments($post_ids);

        // Delete posts
        $db->query('DELETE FROM '.$db->prefix.'posts WHERE topic_id IN('.$topics.')') or error('Unable to delete posts', __FILE__, __LINE__, $db->error());

        update_forum($fid);

        wap_redirect('viewforum.php?id='.$fid);
    }

    require PUN_ROOT.'lang/'.$pun_user['language'].'/forum.php';

    $page_title = $pun_config['o_board_title'].' / '.$lang_misc['Moderate'];

    $smarty->assign('page_title', $page_title);
    $smarty->assign('lang_forum', $lang_forum);
    $smarty->assign('lang_misc', $lang_misc);

    $smarty->assign('fid', $fid);
    $smarty->assign('topics', $topics);

    $smarty->display('moderate.delete_topics.tpl');

    exit();
}
if (isset($_REQUEST['open']) || isset($_REQUEST['close'])) {
    // Open or close one or more topics
    $action = (isset($_REQUEST['open'])) ? 0 : 1;

    // There could be an array of topic ID's in $_POST
    if (isset($_POST['open']) || isset($_POST['close'])) {
        //confirm_referrer('moderate.php');

        $topics = isset($_POST['topics']) ? @\array_map('intval', @\array_keys($_POST['topics'])) : [];

        if (!$topics) {
            wap_message($lang_misc['No topics selected']);
        }

        $db->query('UPDATE '.$db->prefix.'topics SET closed='.$action.' WHERE id IN('.\implode(',', $topics).') AND forum_id='.$fid) or error('Unable to close topics', __FILE__, __LINE__, $db->error());

        $redirect_msg = ($action) ? $lang_misc['Close topics redirect'] : $lang_misc['Open topics redirect'];

        wap_redirect('moderate.php?fid='.$fid);
    } else {
        // Or just one in $_GET

        //confirm_referrer('viewtopic.php');

        $topic_id = ($action) ? \intval($_GET['close']) : \intval($_GET['open']);

        if ($topic_id < 1) {
            wap_message($lang_common['Bad request']);
        }

        $db->query('UPDATE '.$db->prefix.'topics SET closed='.$action.' WHERE id='.$topic_id.' AND forum_id='.$fid) or error('Unable to close topic', __FILE__, __LINE__, $db->error());

        $redirect_msg = ($action) ? $lang_misc['Close topic redirect'] : $lang_misc['Open topic redirect'];

        wap_redirect('viewtopic.php?id='.$topic_id);
    }
} elseif (isset($_GET['stick'])) {
    // Stick a topic

    //confirm_referrer('viewtopic.php');

    $stick = \intval($_GET['stick']);

    if ($stick < 1) {
        wap_message($lang_common['Bad request']);
    }

    $db->query('UPDATE `'.$db->prefix.'topics` SET sticky=1 WHERE id='.$stick.' AND forum_id='.$fid) or error('Unable to stick topic', __FILE__, __LINE__, $db->error());

    wap_redirect('viewtopic.php?id='.$stick);
} elseif (isset($_GET['unstick'])) {
    // Unstick a topic

    //confirm_referrer('viewtopic.php');

    $unstick = \intval($_GET['unstick']);

    if ($unstick < 1) {
        wap_message($lang_common['Bad request']);
    }

    $db->query('UPDATE '.$db->prefix.'topics SET sticky=0 WHERE id='.$unstick.' AND forum_id='.$fid) or error('Unable to unstick topic', __FILE__, __LINE__, $db->error());

    wap_redirect('viewtopic.php?id='.$unstick);
}

// No specific forum moderation action was specified in the query string, so we'll display the moderator forum

// Load the viewforum.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/forum.php';

// Fetch some info about the forum
$result = $db->query('SELECT f.forum_name, f.redirect_url, f.num_topics FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$fid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);

// Is this a redirect forum? In that case, abort!
if ($cur_forum['redirect_url']) {
    wap_message($lang_common['Bad request']);
}

$page_title = $pun_config['o_board_title'].' / '.$cur_forum['forum_name'];

// Determine the topic offset (based on $getPageNumber)
$num_pages = \ceil($cur_forum['num_topics'] / $pun_user['disp_topics']);

$p = ($getPageNumber <= 1 || $getPageNumber > $num_pages) ? 1 : $getPageNumber;
$start_from = $pun_user['disp_topics'] * ($p - 1);

// Generate paging links
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'moderate.php?fid='.$fid);

if ('all' != @$_GET['action']) {
    $act_all = ' LIMIT '.$start_from.', '.$pun_user['disp_topics'];
} else {
    $act_all = null;
}

// AJAX POLL ADD has_poll COLUMN INTO SELECT
// Select topics
$result = $db->query('SELECT id, poster, has_poll, subject, posted, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, moved_to FROM '.$db->prefix.'topics WHERE forum_id='.$fid.' ORDER BY sticky DESC, last_post DESC'.$act_all) or error('Unable to fetch topic list for forum', __FILE__, __LINE__, $db->error());

$topics = [];
while ($topic = $db->fetch_assoc($result)) {
    if (1 == $pun_config['o_censoring']) {
        $topic['subject'] = censor_words($topic['subject']);
    }
    $topics[] = $topic;
}

// If there are topics in this forum.
$smarty->assign('page_title', $page_title);
$smarty->assign('lang_forum', $lang_forum);
$smarty->assign('cur_forum', $cur_forum);
$smarty->assign('lang_misc', $lang_misc);

$smarty->assign('fid', $fid);
$smarty->assign('topics', $topics);
$smarty->assign('paging_links', $paging_links);

$smarty->display('moderate.tpl');
