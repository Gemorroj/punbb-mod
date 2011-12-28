<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';


// This particular function doesn't require forum-based moderator access. It can be used
// by all moderators and admins.
if (isset($_GET['get_host'])) {
    if ($pun_user['g_id'] > PUN_MOD) {
        wap_message($lang_common['No permission']);
    }

    // Is get_host an IP address or a post ID?
    if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $_GET['get_host'])) {
        $ip = $_GET['get_host'];
    } else {
        $get_host = intval($_GET['get_host']);
        if ($get_host < 1) {
            wap_message($lang_common['Bad request']);
        }

        $result = $db->query('SELECT poster_ip FROM `'.$db->prefix.'posts` WHERE id='.$get_host) or error('Unable to fetch post IP address', __FILE__, __LINE__, $db->error());
        if (!$db->num_rows($result)) {
            wap_message($lang_common['Bad request']);
        }

        $ip = $db->result($result);
    }

    if ($whois = gethostbyaddr($ip) != $ip) {
        $whois = ' ('.$whois.')';
    } else {
        $whois = null;
    }

    wap_message('IP: '.$ip.$whois.'<br />
    &#187; <a href="http://www.robtex.com/ip/' . $ip . '.html">WHOIS</a><br/>
    &#187; <a href="'.PUN_ROOT.'admin_users.php?show_users='.$ip.'">'.$lang_common['Show IP'].'</a>');
}


// All other functions require moderator/admin access
$fid = intval($_GET['fid']);
if ($fid < 1) {
    wap_message($lang_common['Bad request']);
}

$result = $db->query('SELECT `moderators` FROM `'.$db->prefix.'forums` WHERE id='.$fid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());

$moderators = $db->result($result);
$mods_array = ($moderators) ? unserialize($moderators) : array();

if ($pun_user['g_id'] != PUN_ADMIN && ($pun_user['g_id'] != PUN_MOD || !array_key_exists($pun_user['username'], $mods_array))) {
    wap_message($lang_common['No permission']);
}

// Load the misc.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/misc.php';


// All other topic moderation features require a topic id in GET
if (isset($_GET['tid'])) {
    $tid = intval($_GET['tid']);
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

            if (preg_match('/[^0-9,]/', $posts)) {
                wap_message($lang_common['Bad request']);
            }

            // Verify that the post IDs are valid
            $result = $db->query('SELECT 1 FROM '.$db->prefix.'posts WHERE id IN('.$posts.') AND topic_id='.$tid) or error('Unable to check posts', __FILE__, __LINE__, $db->error());

            if ($db->num_rows($result) != substr_count($posts, ',') + 1) {
                wap_message($lang_common['Bad request']);
            }

            // Delete the posts
            $db->query('DELETE FROM '.$db->prefix.'posts WHERE id IN('.$posts.')') or error('Unable to delete posts', __FILE__, __LINE__, $db->error());

            require_once PUN_ROOT.'include/search_idx.php';
            strip_search_index($posts);

            // Delete attachments
            require_once PUN_ROOT.'include/file_upload.php';
            delete_post_attachments($posts);

            // Get last_post, last_post_id, and last_poster for the topic after deletion
            $result = $db->query('SELECT id, poster, posted FROM '.$db->prefix.'posts WHERE topic_id='.$tid.' ORDER BY id DESC LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
            $last_post = $db->fetch_assoc($result);

            // How many posts did we just delete?
            $num_posts_deleted = substr_count($posts, ',') + 1;

            // Update the topic
            $db->query('UPDATE '.$db->prefix.'topics SET last_post='.$last_post['posted'].', last_post_id='.$last_post['id'].', last_poster=\''.$db->escape($last_post['poster']).'\', num_replies=num_replies-'.$num_posts_deleted.' WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

            update_forum($fid);

            wap_redirect('viewtopic.php?id='.$tid);
        }


        $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_misc['Moderate'];
        require_once PUN_ROOT.'wap/header.php';
//moderate delete topic - final

echo '
<div class="con"><strong>'.$lang_misc['Delete posts'].'</strong></div>
<form method="post" action="moderate.php?fid='.$fid.'&amp;tid='.$tid.'">
<div class="input">
<strong>'.$lang_misc['Confirm delete legend'].'</strong><br/>
<input type="hidden" name="posts" value="'.implode(',', array_keys($posts)).'" />
'.$lang_misc['Delete posts comply'].'</div>
<div class="go_to">
<input type="submit" name="delete_posts_comply" value="'.$lang_misc['Delete'].'" />
</div></form>';

        require_once PUN_ROOT.'wap/footer.php';
    }


    // Show the delete multiple posts view

    // Load the viewtopic.php language file
    require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

    // Used to disable the Move and Delete buttons if there are no replies to this topic
    $button_status = (!$cur_topic['num_replies']) ? ' disabled="bisabled"' : '';


    // Determine the post offset (based on $_GET['p'])
    $num_pages = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

    $_GET['p'] = intval($_GET['p']);
    $p = ($_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
    $start_from = $pun_user['disp_posts'] * ($p - 1);

    // Generate paging links
    $paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'moderate.php?fid='.$fid.'&amp;tid='.$tid);


    if ($pun_config['o_censoring'] == 1) {
        $cur_topic['subject'] = censor_words($cur_topic['subject']);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$cur_topic['subject'];
    require_once PUN_ROOT.'wap/header.php';

//moderate delete topic
echo '
<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <a href="viewforum.php?id='.$fid.'">'.pun_htmlspecialchars($cur_topic['forum_name']).'</a> &#187; '.pun_htmlspecialchars($cur_topic['subject']).'</div>
<form method="post" action="moderate.php?fid='.$fid.'&amp;tid='.$tid.'">
';


    include_once PUN_ROOT.'include/parser.php';

    $bg_switch = true; // Used for switching background color in posts
    $post_count = 0; // Keep track of post numbers
    $j = false;

    if ($_GET['action'] != 'all') {
        $act_all = ' LIMIT '.$start_from.', '.$pun_user['disp_posts'];
    } else {
        $act_all = null;
    }


    // Retrieve the posts (and their respective poster)
    $result = $db->query('SELECT u.title, u.num_posts, g.g_id, g.g_user_title, p.id, p.poster, p.poster_id, p.poster_ip, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE p.topic_id='.$tid.' ORDER BY p.id'.$act_all, true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

    while ($cur_post = $db->fetch_assoc($result)) {
        $post_count++;
        
         // If the poster is a registered user.
        if ($cur_post['poster_id'] > 1) {
            $poster = '<a href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['poster']).'</a>';

            // get_title() requires that an element 'username' be present in the array
            $cur_post['username'] = $cur_post['poster'];
            $user_title = get_title($cur_post);

            if ($pun_config['o_censoring'] == 1) {
                $user_title = censor_words($user_title);
            }
        } else {
            // If the poster is a guest (or a user that has been deleted)
            $poster = pun_htmlspecialchars($cur_post['poster']);
            $user_title = $lang_topic['Guest'];
        }

        // Switch the background color for every message.
        $bg_switch = !$bg_switch;
        $vtbg = ($bg_switch) ? ' roweven' : ' rowodd';

        // Perform the main parsing of the message (BBCode, smilies, censor words etc)
        $cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);

        $cur_post['message'] = str_replace('<h4>'.$lang_common['Code'].':</h4>','<div class="code">'.$lang_common['Code'].'<br/>',$cur_post['message']);
    $cur_post['message'] = str_replace('<div class="codebox"><div class="incqbox">',null,$cur_post['message']);
    $cur_post['message'] = str_replace('</table></div></div></div>','</table></div></div>',$cur_post['message']);    
    $cur_post['message'] = str_replace('<div style="font-size:x-small;background-color:#999999;">','<div class="attach_list">',$cur_post['message']);
    $cur_post['message'] = str_replace('</div><br />','</div>',$cur_post['message']);
    $cur_post['message'] = str_replace('<div class="incqbox">','<div class="quote">',$cur_post['message']);
    $cur_post['message'] = str_replace('<h4>',null,$cur_post['message']);
    $cur_post['message'] = str_replace('</h4>','<br />',$cur_post['message']);
    $cur_post['message'] = str_replace('<blockquote>',null,$cur_post['message']);
    $cur_post['message'] = str_replace('</blockquote>',null,$cur_post['message']);
    $cur_post['message'] = str_replace('<p>',null,$cur_post['message']);
    $cur_post['message'] = str_replace('<p class="right">',null,$cur_post['message']);
    $cur_post['message'] = str_replace('</p>',null,$cur_post['message']);
    $cur_post['message'] = str_replace('<span style="color: #bbb">','<span class="small">',$cur_post['message']);
    $cur_post['message'] = str_replace(' style="width:15px; height:15px;"',null,$cur_post['message']);
    $signature = str_replace(' style="width:15px; height:15px;"',null,$signature);


        // $class = fmod($start_from + $post_count, 2) ? 'msg' : 'msg2';


$msg_class = ($j = !$j) ? 'msg' : 'msg2';

echo '
<div class="' . $msg_class . '">
<div class="zag_in">
<a href="viewtopic.php?pid='.$cur_post['id'].'#p'.$cur_post['id'].'">#'.($start_from + $post_count).'</a> <strong>'.$poster.' </strong> ('.$user_title.')<br/>
'.format_time($cur_post['posted']).'<br/>
IP: '.$cur_post['poster_ip'];

        if ($start_from + $post_count > 1) {
            echo '<br/>
            <span class="grey">'.$lang_misc['Select'].' <input type="checkbox" name="posts['.$cur_post['id'].']" value="1" /></span>';
        }

echo '</div>
'.$cur_post['message'].'<br />';

        if ($cur_post['edited']) {
            echo '
            <div class="small">'.$lang_topic['Last edit'].' '.pun_htmlspecialchars($cur_post['edited_by']).' ('.format_time($cur_post['edited']).')</div>';
        }




        echo '</div>';
    }

    echo '<div class="con">'.$paging_links.'</div>
<div class="go_to"><input type="submit" name="delete_posts" value="'.$lang_misc['Delete'].'"'.$button_status.' /></div></form>';


    require_once PUN_ROOT.'wap/footer.php';
}


// Move one or more topics
if (isset($_REQUEST['move_topics']) || isset($_POST['move_topics_to'])) {
    if (isset($_POST['move_topics_to'])) {
        //confirm_referrer('moderate.php');

        if (preg_match('/[^0-9,]/', $_POST['topics'])) {
            wap_message($lang_common['Bad request']);
        }

        $topics = explode(',', $_POST['topics']);
        $move_to_forum = intval($_POST['move_to_forum']);
        if (!$topics || $move_to_forum < 1) {
            wap_message($lang_common['Bad request']);
        }

        // Verify that the topic IDs are valid
        $result = $db->query('SELECT 1 FROM '.$db->prefix.'topics WHERE id IN('.implode(',',$topics).') AND forum_id='.$fid) or error('Unable to check topics', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result) != sizeof($topics)) {
            wap_message($lang_common['Bad request']);
        }

        // Delete any redirect topics if there are any (only if we moved/copied the topic back to where it where it was once moved from)
        $db->query('DELETE FROM '.$db->prefix.'topics WHERE forum_id='.$move_to_forum.' AND moved_to IN('.implode(',',$topics).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());

        // Move the topic(s)
        $db->query('UPDATE '.$db->prefix.'topics SET forum_id='.$move_to_forum.' WHERE id IN('.implode(',',$topics).')') or error('Unable to move topics', __FILE__, __LINE__, $db->error());

        // Should we create redirect topics?
        if (isset($_POST['with_redirect'])) {
            while (list(, $cur_topic) = each($topics)) {
                // Fetch info for the redirect topic
                $result = $db->query('SELECT poster, subject, posted, last_post FROM '.$db->prefix.'topics WHERE id='.$cur_topic) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
                $moved_to = $db->fetch_assoc($result);

                // Create the redirect topic
                $db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, moved_to, forum_id) VALUES(\''.$db->escape($moved_to['poster']).'\', \''.$db->escape($moved_to['subject']).'\', '.$moved_to['posted'].', '.$moved_to['last_post'].', '.$cur_topic.', '.$fid.')') or error('Unable to create redirect topic', __FILE__, __LINE__, $db->error());
            }
        }

        update_forum($fid); // Update the forum FROM which the topic was moved
        update_forum($move_to_forum); // Update the forum TO which the topic was moved

        $redirect_msg = (sizeof($topics) > 1) ? $lang_misc['Move topics redirect'] : $lang_misc['Move topic redirect'];
        wap_redirect('viewforum.php?id='.$move_to_forum);
    }

    if (isset($_POST['move_topics'])) {
        $topics = isset($_POST['topics']) ? $_POST['topics'] : array();
        if (!$topics) {
            wap_message($lang_misc['No topics selected']);
        }

        $topics = implode(',', array_map('intval', array_keys($topics)));
        $action = 'multi';
    } else {
        $topics = intval($_GET['move_topics']);
        if ($topics < 1) {
            wap_message($lang_common['Bad request']);
        }

        $action = 'single';
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_misc['Moderate'];
    require_once PUN_ROOT.'wap/header.php';


    echo '<div class="inbox">
<a href="index.php">'.$lang_common['Index'].'</a> &#187; ';
    echo $action == 'single' ? $lang_misc['Move topic'] : $lang_misc['Move topics'];
    echo '</div>
<form method="post" action="moderate.php?fid='.$fid.'">
<div class="input">
<input type="hidden" name="topics" value="'.$topics.'" />
<strong>'.$lang_misc['Move legend'].'</strong><br/>
'.$lang_misc['Move to'].'<br/>
<select name="move_to_forum">';


    $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

    $cur_category = 0;
    while ($cur_forum = $db->fetch_assoc($result)) {
        if ($cur_forum['cid'] != $cur_category) {
            // A new category since last iteration?
            if ($cur_category) {
                echo '</optgroup>';
            }

            echo '<optgroup label="'.pun_htmlspecialchars($cur_forum['cat_name']).'">';
            $cur_category = $cur_forum['cid'];
        }

        if ($cur_forum['fid'] != $fid) {
            echo '<option value="'.$cur_forum['fid'].'">'.pun_htmlspecialchars($cur_forum['forum_name']).'</option>';
        }
    }


    echo '</optgroup></select><br /><input type="checkbox" name="with_redirect" value="1"';
    if ($action == 'single') {
        echo ' checked="checked"';
    }
    echo ' />'.$lang_misc['Leave redirect'].'</div>
<div class="go_to">
<input type="submit" name="move_topics_to" value="'.$lang_misc['Move'].'" /></div></form>';


    require_once PUN_ROOT.'wap/footer.php';
}


// Delete one or more topics
if (isset($_REQUEST['delete_topics']) || isset($_POST['delete_topics_comply'])) {
    $topics = isset($_POST['topics']) ? $_POST['topics'] : array();
    if (!$topics) {
        wap_message($lang_misc['No topics selected']);
    }

    if (isset($_POST['delete_topics_comply'])) {
        //confirm_referrer('moderate.php');

        if (preg_match('/[^0-9,]/', $topics)) {
            wap_message($lang_common['Bad request']);
        }

        require_once PUN_ROOT.'include/search_idx.php';

        // Verify that the topic IDs are valid
        $result = $db->query('SELECT 1 FROM '.$db->prefix.'topics WHERE id IN('.$topics.') AND forum_id='.$fid) or error('Unable to check topics', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result) != substr_count($topics, ',') + 1) {
            wap_message($lang_common['Bad request']);
        }

        // hcs AJAX POLL MOD BEGIN
        if ($pun_config['poll_enabled'] == 1) {
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
        require_once PUN_ROOT.'include/file_upload.php';
        delete_post_attachments($post_ids);


        // Delete posts
        $db->query('DELETE FROM '.$db->prefix.'posts WHERE topic_id IN('.$topics.')') or error('Unable to delete posts', __FILE__, __LINE__, $db->error());

        update_forum($fid);

        wap_redirect('viewforum.php?id='.$fid);
    }


    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_misc['Moderate'];
    require_once PUN_ROOT.'wap/header.php';


echo '<div class="inbox">
<a href="index.php">'.$lang_common['Index'].'</a> &#187; <strong>'.$lang_misc['Delete topics'].'</strong></div>
<form method="post" action="moderate.php?fid='.$fid.'">
<div class="input">
<input type="hidden" name="topics" value="'.implode(',', array_map('intval', array_keys($topics))).'" />
<strong>'.$lang_misc['Confirm delete legend'].'</strong><br/>
'.$lang_misc['Delete topics comply'].'</div>
<div class="go_to"><input type="submit" name="delete_topics_comply" value="'.$lang_misc['Delete'].'" /></div></form>';

    require_once PUN_ROOT.'wap/footer.php';
} else if (isset($_REQUEST['open']) || isset($_REQUEST['close'])) {
    // Open or close one or more topics
    $action = (isset($_REQUEST['open'])) ? 0 : 1;

    // There could be an array of topic ID's in $_POST
    if (isset($_POST['open']) || isset($_POST['close'])) {
        //confirm_referrer('moderate.php');

        $topics = isset($_POST['topics']) ? @array_map('intval', @array_keys($_POST['topics'])) : array();
        if (!$topics) {
            wap_message($lang_misc['No topics selected']);
        }

        $db->query('UPDATE '.$db->prefix.'topics SET closed='.$action.' WHERE id IN('.implode(',', $topics).') AND forum_id='.$fid) or error('Unable to close topics', __FILE__, __LINE__, $db->error());

        $redirect_msg = ($action) ? $lang_misc['Close topics redirect'] : $lang_misc['Open topics redirect'];
        wap_redirect('moderate.php?fid='.$fid);
    } else {
        // Or just one in $_GET
        //confirm_referrer('viewtopic.php');

        $topic_id = ($action) ? intval($_GET['close']) : intval($_GET['open']);
        if ($topic_id < 1) {
            wap_message($lang_common['Bad request']);
        }

        $db->query('UPDATE '.$db->prefix.'topics SET closed='.$action.' WHERE id='.$topic_id.' AND forum_id='.$fid) or error('Unable to close topic', __FILE__, __LINE__, $db->error());

        $redirect_msg = ($action) ? $lang_misc['Close topic redirect'] : $lang_misc['Open topic redirect'];
        wap_redirect('viewtopic.php?id='.$topic_id);
    }
} else if (isset($_GET['stick'])) {
    // Stick a topic
    //confirm_referrer('viewtopic.php');

    $stick = intval($_GET['stick']);
    if ($stick < 1) {
        wap_message($lang_common['Bad request']);
    }

    $db->query('UPDATE `'.$db->prefix.'topics` SET sticky=1 WHERE id='.$stick.' AND forum_id='.$fid) or error('Unable to stick topic', __FILE__, __LINE__, $db->error());

    wap_redirect('viewtopic.php?id='.$stick);
} else if (isset($_GET['unstick'])) {
    // Unstick a topic
    //confirm_referrer('viewtopic.php');

    $unstick = intval($_GET['unstick']);
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

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.pun_htmlspecialchars($cur_forum['forum_name']);
require_once PUN_ROOT.'wap/header.php';

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($cur_forum['num_topics'] / $pun_user['disp_topics']);

$_GET['p'] = intval($_GET['p']);
$p = ($_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_topics'] * ($p - 1);

// Generate paging links
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'moderate.php?fid='.$fid, 0);


//Moderation forum header
echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; '.pun_htmlspecialchars($cur_forum['forum_name']).'</div>
<form method="post" action="moderate.php?fid='.$fid.'">';



if ($_GET['action'] != 'all') {
    $act_all = ' LIMIT '.$start_from.', '.$pun_user['disp_topics'];
} else {
    $act_all = null;
}


// AJAX POLL ADD has_poll COLUMN INTO SELECT
// Select topics
$result = $db->query('SELECT id, poster, has_poll, subject, posted, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, moved_to FROM '.$db->prefix.'topics WHERE forum_id='.$fid.' ORDER BY sticky DESC, last_post DESC'.$act_all) or error('Unable to fetch topic list for forum', __FILE__, __LINE__, $db->error());


// If there are topics in this forum.
if ($db->num_rows($result)) {
    $button_status = null;
    $out = null;

    while ($cur_topic = $db->fetch_assoc($result)) {
        $icon_text = $lang_common['Normal icon'];
        $item_status = '';
        $icon_type = 'icon';

        if ($cur_topic['moved_to'] == null) {
            $last_post = '<a href="viewtopic.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['last_poster']);
            $ghost_topic = false;
        } else {
            $last_post = ' ';
            $ghost_topic = true;
        }

        if ($pun_config['o_censoring'] == 1) {
            $cur_topic['subject'] = censor_words($cur_topic['subject']);
        }
//icon Moved - "•»"
        if ($cur_topic['moved_to']) {
            $subject = $lang_forum['Moved_m'].' <a href="viewtopic.php?id='.$cur_topic['moved_to'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
        } else if (!$cur_topic['closed']) {
            $subject = '<a href="viewtopic.php?id='.$cur_topic['id'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
        } else {
//icon Closed - "#"
            $subject = '<a href="viewtopic.php?id='.$cur_topic['id'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
            $icon_text = $lang_common['Closed icon_m'];
            $item_status = 'iclosed';
        }
//icon new - "new"
        if ($cur_topic['last_post'] > $pun_user['last_visit'] && !$ghost_topic) {
            $icon_new_text = ' <span class="red">'.$lang_common['New icon_m'].'</span>';
            //$icon_text .= ' '.$lang_common['New icon_m'];
            $item_status .= ' inew';
            $icon_type = 'icon inew';
            $subject = '<strong>'.$subject.'</strong>';
            $subject_new_posts = '[ <a href="viewtopic.php?id='.$cur_topic['id'].'&amp;action=new">'.$lang_common['New posts'].'</a> ]';
        } else {
            $subject_new_posts = null;
        }

        // We won't display "the dot", but we add the spaces anyway
        if ($pun_config['o_show_dot'] == 1) {
            $subject = ' '.$subject;
        }

        // hcs AJAX POLL MOD BEGIN
//icon poll - "?"
        if ($pun_config['poll_enabled'] == 1 && $cur_topic['has_poll']) {
            $icon_type .= ' ipoll';
            //$subject = '<strong>'.$lang_forum['poll_m'].' </strong>'.$subject;
            $icon_text .= ' '.$lang_forum['poll_m'];
        }
        // hcs AJAX POLL MOD END

//icon Sticky - "!"
        if ($cur_topic['sticky'] == 1) {
            //$subject = $lang_forum['Sticky_m'].' '.$subject;
            $item_status .= ' isticky';
            $icon_text .= ' '.$lang_forum['Sticky_m'];
        }

        $num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

        if ($num_pages_topic > 1) {
            $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'viewtopic.php?id='.$cur_topic['id']).' ]';
        } else {
            $subject_multipage = null;
        }

        // Should we show the "New posts" and/or the multipage links?
        if (!empty($subject_new_posts) || !empty($subject_multipage)) {
            $subject .= ' '.(!empty($subject_new_posts) ? $subject_new_posts : '');
            $subject .= !empty($subject_multipage) ? ' '.$subject_multipage : '';
        }

        $icon_text = trim($icon_text);
//page assembly        
        $out.= '
        <div class="in">
        <input type="checkbox" name="topics['.$cur_topic['id'].']" value="1" />';
        
        if ($icon_text) {
            $out.= ' <strong>' . $icon_text . '</strong>';
        }
        
        $out.= ' '.$subject.' ('.$cur_topic['num_replies'].'/'.$cur_topic['num_views'].') '.$icon_new_text.'<br/>'.$last_post.'</div>
';
    }

    echo rtrim($out, '');
} else {
    $button_status = ' disabled="disabled"';
    echo '<div class="in">'.$lang_forum['Empty forum'].'</div>';
}


echo '<div class="con">'.$paging_links.'</div>
<div class="go_to">
<input type="submit" name="move_topics" value="'.$lang_misc['Move'].'"'.$button_status.' /> <input type="submit" name="delete_topics" value="'.$lang_misc['Delete'].'"'.$button_status.' /> <input type="submit" name="open" value="'.$lang_misc['Open'].'"'.$button_status.' /> <input type="submit" name="close" value="'.$lang_misc['Close'].'"'.$button_status.' /></div>
</form>';


require_once PUN_ROOT.'wap/footer.php';
?>