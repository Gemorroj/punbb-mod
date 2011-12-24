<?php
define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';

// REAL MARK TOPIC AS READ MOD BEGIN
if (!$pun_user['is_guest']) {
    $result = $db->query('DELETE FROM `' . $db->prefix . 'log_topics` WHERE log_time < ' . ($_SERVER['REQUEST_TIME'] - $pun_user['mark_after']) . ' AND user_id=' . $pun_user['id']) or error('Unable to delete marked as read topic info', __FILE__, __LINE__, $db->error());
}

function is_reading($log_time, $last_post)
{
    if ($log_time > $last_post) {
        return true;
    }
    return false;
}
// REAL MARK TOPIC AS READ MOD END

if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

$id = intval($_GET['id']);
if ($id < 1) {
    wap_message($lang_common['Bad request']);
}

// Load the viewforum.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/forum.php';

// Fetch some info about the forum
$result = $db->query('SELECT f.forum_name, f.redirect_url, f.moderators, f.num_topics, f.sort_by, fp.post_topics, lf.log_time, f.id as forum_id FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') LEFT JOIN '.$db->prefix.'log_forums AS lf ON (lf.user_id='.$pun_user['id'].' AND lf.forum_id=f.id) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);

// REAL MARK TOPIC AS READ MOD BEGIN
if (!$pun_user['is_guest'] && !$cur_forum['log_time']) {
    $result = $db->query('INSERT INTO '.$db->prefix.'log_forums (user_id, forum_id, log_time) VALUES ('.$pun_user['id'].', '.$cur_forum['forum_id'].', '.$_SERVER['REQUEST_TIME'].')') or error('Unable to insert reading_mark info', __FILE__, __LINE__, $db->error());
} else {
    $result = $db->query('UPDATE '.$db->prefix.'log_forums SET log_time='.$_SERVER['REQUEST_TIME'].' WHERE forum_id='.$cur_forum['forum_id'].' AND user_id='.$pun_user['id']) or error('Unable to update reading_mark info', __FILE__, __LINE__, $db->error());
}
// REAL MARK TOPIC AS READ MOD END


// Is this a redirect forum? In that case, redirect!
if ($cur_forum['redirect_url']) {
    header('Location: ' . $cur_forum['redirect_url'], true, 301);
    exit;
}

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = array();
if ($cur_forum['moderators']) {
    $mods_array = unserialize($cur_forum['moderators']);
}

$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Can we or can we not post new topics?
if ((!$cur_forum['post_topics'] && $pun_user['g_post_topics'] == 1) || $cur_forum['post_topics'] == 1 || $is_admmod) {
    $post_link = '
    <div class="go_to"><a class="but" href="post.php?fid='.$id.'">'.$lang_forum['Post topic'].'</a></div>';
} else {
    $post_link = null;
}

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($cur_forum['num_topics'] / $pun_user['disp_topics']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_topics'] * ($p - 1);

// Generate paging links
if (isset($_GET['action']) && $_GET['action'] == 'all') {
    $p = $num_pages + 1;
    $pun_user['disp_topics'] = $cur_forum['num_topics'];
}
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewforum.php?id='.$id);

$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' &#187; '.$cur_forum['forum_name']);
define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT . 'wap/header.php';


echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; '.pun_htmlspecialchars($cur_forum['forum_name']).'</div>';


// Fetch list of topics to display on this page
if ($pun_user['is_guest'] || !$pun_config['o_show_dot']) {
    // Without "the dot"
    // REAL MARK TOPIC AS READ MOD BEGIN
    $sql = 'SELECT t.id, t.poster, t.has_poll, t.subject, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, lt.log_time, lf.mark_read FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'log_topics AS lt ON lt.user_id='.$pun_user['id'].' AND lt.topic_id=t.id LEFT JOIN '.$db->prefix.'log_forums AS lf ON lf.forum_id=t.forum_id AND lf.user_id='.$pun_user['id'].' WHERE t.forum_id='.$id.' ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == 1) ? 'posted' : 'last_post').' DESC LIMIT '.$start_from.', '.$pun_user['disp_topics'];
    // REAL MARK TOPIC AS READ MOD END
} else {
    // With "the dot"
    // REAL MARK TOPIC AS READ MOD BEGIN
    $sql = 'SELECT p.poster_id AS has_posted, t.has_poll, t.id, t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, lt.log_time, lf.mark_read FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id='.$pun_user['id'].' LEFT JOIN '.$db->prefix.'log_topics AS lt ON lt.user_id='.$pun_user['id'].' AND lt.topic_id=t.id LEFT JOIN '.$db->prefix.'log_forums AS lf ON lf.forum_id=t.forum_id AND lf.user_id='.$pun_user['id'].' WHERE t.forum_id='.$id.' GROUP BY t.id ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == 1) ? 'posted' : 'last_post').' DESC LIMIT '.$start_from.', '.$pun_user['disp_topics'];
    // REAL MARK TOPIC AS READ MOD END
}

$result = $db->query($sql) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());


// If there are topics in this forum.
if ($db->num_rows($result)) {
    $out = null;
    $j = false;
    $icon_new_text = false;
    
    while ($cur_topic = $db->fetch_assoc($result)) {
        $icon_text = $lang_common['Normal icon'];
        $item_status = '';
        $icon_type = 'icon';

        if ($cur_topic['moved_to']) {
            $last_post = '&#160;';
        } else {
            $last_post = '<a href="viewtopic.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['last_poster']);
        }

        if ($pun_config['o_censoring'] == 1) {
            $cur_topic['subject'] = censor_words($cur_topic['subject']);
        }
//topic moved icon
        if ($cur_topic['moved_to']) {
//icon Moved - "•»"
            $subject = '<strong>' . $lang_forum['Moved_m'] . '</strong> <a href="viewtopic.php?id='.$cur_topic['moved_to'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
        } else if (!$cur_topic['closed']) {
            $subject = '<a href="viewtopic.php?id='.$cur_topic['id'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
        } else {
            $subject = '<a href="viewtopic.php?id='.$cur_topic['id'].'">'.pun_htmlspecialchars($cur_topic['subject']).'</a> '.$lang_common['by'].' '.pun_htmlspecialchars($cur_topic['poster']);
//icon Closed - "#"
            $icon_text = $lang_common['Closed icon_m'];
            //$item_status = 'iclosed';
        }
        
        
        // REAL MARK TOPIC AS READ MOD BEGIN
        if (!$pun_user['is_guest'] && !$cur_topic['moved_to'] && !is_reading( $cur_topic['log_time'], $cur_topic['last_post']) && $cur_topic['last_post'] > $cur_topic['mark_read'] && ($cur_topic['last_post'] > $pun_user['last_visit'] || ($_SERVER['REQUEST_TIME'] - $cur_topic['last_post'] < $pun_user['mark_after']))) {
            // REAL MARK TOPIC AS READ MOD END
//icon new - "new"
            $icon_new_text = ' <span class="red">'.$lang_common['New icon_m'].'</span>';
            $item_status .= ' inew';
            $icon_type = 'icon inew';
            $subject = $subject;
            // $subject_new_posts = '<span class="newtext">[ <a href="viewtopic.php?id='.$cur_topic['id'].'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a> ]</span>';
        } else {
            $subject_new_posts = null;
        }
        
        // Should we display the dot or not? :)
        if (!$pun_user['is_guest'] && $pun_config['o_show_dot'] == 1) {
            if ($cur_topic['has_posted'] == $pun_user['id']) {
                $subject = '<strong>&#183;</strong> ' . $subject;
            } else {
                $subject = ' ' . $subject;
            }
        }
        
        
        // hcs AJAX POLL MOD BEGIN
//icon poll - "?"
        if ($pun_config['poll_enabled'] == 1 && $cur_topic['has_poll']) {
            $icon_type .= ' ipoll';
            //$subject = '<span class="stickytext">['.$lang_forum['poll'].'] </span>'.$subject;
            $icon_text .= ' ' . $lang_forum['poll_m'];
        }
        // hcs AJAX POLL MOD END
        
//icon Sticky - "!"
        if ($cur_topic['sticky'] == 1) {
            //$subject = $lang_forum['Sticky'].': '.$subject;
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
        
        // - ASSEMBLY OF TOPICS
        if ($cur_topic['moved_to']) {//topic moved
        $in_class = ($j = !$j) ? 'in' : 'in2';
            $out .= '
            <div class="' . $in_class . '">'.$subject . '</div>';
             } else {
            
            $in_class = ($j = !$j) ? 'in' : 'in2';       
            $out .= '
            <div class="' . $in_class . '">';
            //The topic icons
            if ($icon_text) {
            $out.= '<strong>'.$icon_text.'</strong> ';
        }

            $out .= $subject.' ('.$cur_topic['num_replies'].'/'.$cur_topic['num_views'].')'.$icon_new_text.'<br/>
            <span class="sub">&#187; ' . $last_post . '</span></div>
            ';
            
        }
    }

    echo rtrim($out, '');

} else {
    echo '
    <div class="in">' . $lang_forum['Empty forum'] . '</div>';
}

echo '
<div class="con">' . $paging_links . '</div>' . $post_link;

$forum_id = $id;
$footer_style = 'viewforum';
require_once PUN_ROOT . 'wap/footer.php';

?>
