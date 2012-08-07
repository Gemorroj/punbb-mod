<?php
define('PUN_ROOT', './');

require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/parser.php';

if (!$pun_config['o_pms_enabled'] || !$pun_user['g_pm']) {
    message($lang_common['No permission']);
}

if ($pun_user['is_guest']) {
    message($lang_common['Login required']);
}

// Load the message.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';

// Inbox or Sent?
$box = intval($_GET['box']);

if ($box == 1) {
    $name = $lang_pms['Outbox'];
} else if ($box == 2) {
    $name = $lang_pms['Options'];
} else {
    $box = 0;
    $name = $lang_pms['Inbox'];
}


$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);

//$name plus the link to the other box
$page_name = $name;

// Delete multiple posts
if (isset($_POST['delete_messages']) || isset($_POST['delete_messages_comply'])) {
    if (isset($_POST['delete_messages_comply'])) {
        //Check this is legit
        // confirm_referrer('message_list.php');

        if (preg_match('/[^0-9,]/', $_POST['messages']) || !trim($_POST['messages'])) {
            message($lang_common['Bad request']);
        }

        // Delete messages
        $db->query('DELETE FROM ' . $db->prefix . 'messages WHERE id IN(' . $_POST['messages'] . ') AND owner=' . $pun_user['id']) or error('Unable to delete messages.', __FILE__, __LINE__, $db->error());
        redirect('message_list.php?box=' . intval($_POST['box']), $lang_pms['Deleted redirect']);
    } else {
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_pms['Multidelete'];
        $idlist = is_array($_POST['delete_messages']) ? array_map('intval', $_POST['delete_messages']) : array();
        require_once PUN_ROOT . 'header.php';

        echo '<div class="blockform"><h2><span>' . $lang_pms['Multidelete'] . '</span></h2><div class="box"><form method="post" action="message_list.php?"><input type="hidden" name="messages" value="' . htmlspecialchars(implode(',', array_values($idlist))) . '"/><input type="hidden" name="box" value="' . intval($_POST['box']) . '"/><div class="inform"><fieldset><div class="infldset"><p class="warntext"><strong>' . $lang_pms['Delete messages comply'] . '</strong></p></div></fieldset></div><p><input type="submit" name="delete_messages_comply" value="' . $lang_pms['Delete'] . '" /><a href="javascript:history.go(-1);">' . $lang_common['Go back'] . '</a></p></form></div></div>';

        require_once PUN_ROOT . 'footer.php';
    }
} else if ($_GET['action'] == 'markall') {
    // Mark all messages as read
    $db->query('UPDATE ' . $db->prefix . 'messages SET showed=1 WHERE owner=' . $pun_user['id']) or error('Unable to update message status', __FILE__, __LINE__, $db->error());
    redirect('message_list.php?box=' . $box . '&p=' . $p, $lang_pms['Read redirect']);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_pms['Private Messages'] . ' - ' . $name;


if ($box < 2) {
    // Get message count
    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE status=' . $box . ' AND owner=' . $pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
    $num_messages = $db->result($result);

    //What page are we on?
    $num_pages = ceil($num_messages / $pun_config['o_pms_mess_per_page']);
    $p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
    $start_from = $pun_config['o_pms_mess_per_page'] * ($p - 1);
    if ($_GET['action'] != 'all') {
        $limit = 'LIMIT ' . $start_from . ',' . $pun_config['o_pms_mess_per_page'];
    }
}


require_once PUN_ROOT . 'header.php';
?>
<div id="profile" class="block2col">
<div class="blockmenu">
    <h2><span><?php echo $lang_pms['Private Messages']; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <ul>
                <li <?php if ($box == 0) echo 'class="isactive"'; ?>><a
                    href="message_list.php?box=0"><?php echo $lang_pms['Inbox']; ?></a></li>
                <li <?php if ($box == 1) echo 'class="isactive"'; ?>><a
                    href="message_list.php?box=1"><?php echo $lang_pms['Outbox']; ?></a></li>
                <li <?php if ($box == 2) echo 'class="isactive"'; ?>><a
                    href="message_list.php?box=2"><?php echo $lang_pms['Options']; ?></a></li>
                <li><a href="message_send.php"><?php echo $lang_pms['New message']; ?></a></li>
            </ul>
        </div>
    </div>
</div>
<?php
if ($box < 2) {


//Are we viewing a PM?
    if (isset($_GET['id'])) {
//Yes! Lets get the details
        $id = intval($_GET['id']);

// Set user
        $result = $db->query('SELECT status, owner FROM ' . $db->prefix . 'messages WHERE id=' . $id) or error('Unable to get message status', __FILE__, __LINE__, $db->error());
        list($status, $owner) = $db->fetch_row($result);
        $status == 0 ? $where = 'u.id=m.sender_id' : $where = 'u.id=m.owner';

        $result = $db->query('
    SELECT m.id AS mid,
    m.subject,
    m.sender_ip,
    m.message,
    m.smileys,
    m.posted,
    m.showed,
    u.id,
    u.group_id AS g_id,
    g.g_user_title,
    u.username,
    u.registered,
    u.email,
    u.title,
    u.url,
    u.icq,
    u.msn,
    u.aim,
    u.yahoo,
    u.location,
    u.use_avatar,
    u.email_setting,
    u.num_posts,
    u.admin_note,
    u.signature,
    o.user_id AS is_online
    FROM ' . $db->prefix . 'messages AS m, ' . $db->prefix . 'users AS u
    LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.idle=0)
    LEFT JOIN ' . $db->prefix . 'groups AS g ON u.group_id = g.g_id
    WHERE ' . $where . ' AND m.id=' . $id
        ) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
        $cur_post = $db->fetch_assoc($result);

        if ($owner != $pun_user['id']) {
            message($lang_common['No permission']);
        }

        if (!$cur_post['showed']) {
            $db->query('UPDATE ' . $db->prefix . 'messages SET showed=1 WHERE id=' . $id) or error('Unable to update message info', __FILE__, __LINE__, $db->error());
        }

        if ($cur_post['id'] > 0) {
            $username = '<a href="profile.php?id=' . $cur_post['id'] . '">' . pun_htmlspecialchars($cur_post['username']) . '</a>';
            $user_title = get_title($cur_post);

            if ($pun_config['o_censoring'] == 1) {
                $user_title = censor_words($user_title);
            }

// Format the online indicator
            $is_online = ($cur_post['is_online'] == $cur_post['id']) ? '<strong>' . $lang_topic['Online'] . '</strong>' : $lang_topic['Offline'];

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

// Now let's deal with the contact links (E-mail and URL)
                if ((!$cur_post['email_setting'] && !$pun_user['is_guest']) || $pun_user['g_id'] < PUN_GUEST) {
                    $user_contacts[] = '<a href="mailto:' . $cur_post['email'] . '">' . $lang_common['E-mail'] . '</a>';
                } else if ($cur_post['email_setting'] == 1 && !$pun_user['is_guest']) {
                    $user_contacts[] = '<a href="misc.php?email=' . $cur_post['id'] . '">' . $lang_common['E-mail'] . '</a>';
                }
                include PUN_ROOT . 'include/pms/viewtopic_PM-link.php';
                if ($cur_post['url']) {
                    $user_contacts[] = '<a href="' . pun_htmlspecialchars($cur_post['url']) . '">' . $lang_topic['Website'] . '</a>';
                }
            }

//Moderator and Admin stuff
            if ($pun_user['g_id'] < PUN_GUEST) {
                $user_info[] = '<dd>IP: <a href="moderate.php?get_host=' . $cur_post['id'] . '">' . $cur_post['sender_ip'] . '</a>';

                if ($cur_post['admin_note']) {
                    $user_info[] = '<dd>' . $lang_topic['Note'] . ': <strong>' . pun_htmlspecialchars($cur_post['admin_note']) . '</strong>';
                }
            }
// Generation post action array (reply, delete etc.)
            if (!$status) {
                $post_actions[] = '<li><a href="message_send.php?id=' . $cur_post['id'] . '&amp;reply=' . $cur_post['mid'] . '">' . $lang_pms['Reply'] . '</a>';
            }

            $post_actions[] = '<li><a href="message_delete.php?id=' . $cur_post['mid'] . '&amp;box=' . $box . '&amp;p=' . $p . '">' . $lang_pms['Delete'] . '</a>';

            if (!$status) {
                $post_actions[] = '<li><a href="message_send.php?id=' . $cur_post['id'] . '&amp;quote=' . $cur_post['mid'] . '">' . $lang_pms['Quote'] . '</a>';
            }

        } // If the sender has been deleted
        else {
            $result = $db->query('SELECT id,sender,message,posted FROM ' . $db->prefix . 'messages WHERE id=' . $id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
            $cur_post = $db->fetch_assoc($result);

            $username = pun_htmlspecialchars($cur_post['sender']);
            $user_title = 'Deleted User';

            $post_actions[] = '<li><a href="message_delete.php?id=' . $cur_post['id'] . '&amp;box=' . $box . '&amp;p=' . $p . '">' . $lang_pms['Delete'] . '</a>';

            $is_online = $lang_topic['Offline'];
        }

// Perform the main parsing of the message (BBCode, smilies, censor words etc)
        $cur_post['smileys'] = isset($cur_post['smileys']) ? $cur_post['smileys'] : $pun_user['show_smilies'];
        $cur_post['message'] = parse_message($cur_post['message'], intval(!$cur_post['smileys']));

// Do signature parsing/caching
        if (isset($cur_post['signature']) && $pun_user['show_sig']) {
            $signature = parse_signature($cur_post['signature']);
        }

        ?>
<div id="p<?php echo $cur_post['id']; ?>" class="blockpost row_odd firstpost" style="margin-left: 14em;">
    <h2><span><?php echo format_time($cur_post['posted']); ?></span></h2>

    <div class="box">
        <div class="inbox">
            <div class="postleft">
                <dl>
                    <dt><strong><?php echo $username; ?></strong></dt>
                    <dd class="usertitle"><strong><?php echo $user_title; ?></strong></dd>
                    <dd class="postavatar"><?php if (isset($user_avatar)) echo $user_avatar; ?></dd>
                    <?php if (isset($user_info)) if ($user_info) echo implode('</dd>', $user_info) . '</dd>'; ?>
                    <?php if (isset($user_contacts)) if ($user_contacts) echo '<dd class="usercontacts">' . implode(' ', $user_contacts) . '</dd>'; ?>
                </dl>
            </div>
            <div class="postright">
                <div class="postmsg">
                    <?php echo $cur_post['message']; ?>
                </div>
                <?php if (isset($signature)) echo '<div class="postsignature"><hr />' . $signature . '</div>'; ?>
            </div>
            <div class="clearer"></div>
            <div class="postfootleft"><?php if ($cur_post['id'] > 1) echo '<p>' . $is_online . '</p>'; ?></div>
            <div
                class="postfootright"><?php echo ($post_actions) ? '<ul>' . implode($lang_topic['Link separator'] . '</li>', $post_actions) . '</li></ul></div>' : '<div> </div></div>' ?>
            </div>
        </div>
    </div>
    <div class="clearer"></div>
<?php
}

    if ($_GET['action'] == 'all') {
        $p = $num_pages + 1;
    }

    echo '<form method="post" action="message_list.php?" id="delet">
<div class="postlinksb">
<div class="inbox">
<p class="pagelink conl" style="margin-left: 1em;">' . $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'message_list.php?box=' . $box) . '</p>
<ul><li><a href="index.php">' . pun_htmlspecialchars($pun_config['o_board_title']) . '</a> </li><li>&#187; ' . $lang_pms['Private Messages'] . ' </li><li>&#187; ' . $page_name . '</li></ul>
</div>
</div>
<div class="blockform">
<h2><span>' . $name . '</span></h2>
<div class="box">
<div class="inbox">
<table cellspacing="0">
<thead>
<tr>';

    if ($pun_user['g_pm_limit'] && $pun_user['g_id'] > PUN_GUEST) {
        // Get total message count
        $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
        list($tot_messages) = $db->fetch_row($result);
        $proc = ceil($tot_messages / $pun_user['g_pm_limit'] * 100);
        $status = ' - ' . $lang_pms['Status'] . ' ' . $proc . '%';
    } else {
        $status = null;
    }

    echo '<th class="tcl" scope="col">' . $lang_pms['Subject'] . $status . '</th>';
    if (!$box) {
        echo '<th class="tc2" scope="col">' . $lang_pms['Sender'] . '</th>';
    } else {
        echo '<th class="tc2" scope="col">' . $lang_pms['Receiver'] . '</th>';
    }
    echo '<th class="tc3" scope="col">' . $lang_pms['Date'] . '</th>
<th class="tcmod" scope="col">' . $lang_pms['Delete'] . ' <input type="checkbox" onclick="chekuncheck(this, document.getElementById(\'delet\'));"/></th>
</tr>
</thead>
<tbody>';


// Fetch messages
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id'] . ' AND status=' . $box . ' ORDER BY posted DESC ' . $limit) or error('Unable to fetch messages list for forum', __FILE__, __LINE__, $db->error());
    $new_messages = $messages_exist = false;

// If there are messages in this folder.
    if ($all = $db->num_rows($result)) {
        $messages_exist = true;
        while ($cur_mess = $db->fetch_assoc($result)) {
            $icon_text = $lang_common['Normal icon'];
            $icon_type = 'icon';
            if (!$cur_mess['showed']) {
                $icon_text .= ' ' . $lang_common['New icon'];
                $icon_type = 'icon inew';
            }

            ($new_messages == false && $cur_mess['showed'] == '0') ? $new_messages = true : null;

            $subject = '<a href="message_list.php?id=' . $cur_mess['id'] . '&amp;p=' . $p . '&amp;box=' . $box . '">' . pun_htmlspecialchars($cur_mess['subject']) . '</a>';
            if (isset($_GET['id'])) {
                if ($cur_mess['id'] == $_GET['id']) {
                    $subject = '<strong>' . $subject . '</strong>';
                }
            }

            echo '<tr>
<td class="tcl">
<div class="intd">
<div class="' . $icon_type . '"><div class="nosize">' . $icon_text . '</div></div>
<div class="tclcon">' . $subject . '</div>
</div>
</td>
<td class="tc2" style="white-space:nowrap; overflow:hidden;"><a href="profile.php?id=' . $cur_mess['sender_id'] . '">' . $cur_mess['sender'] . '</a></td>
<td style="white-space:nowrap;">' . format_time($cur_mess['posted']) . '</td>
<td style="text-align:center;"><input type="checkbox" name="delete_messages[]" value="' . $cur_mess['id'] . '"/></td>
</tr>';

        }
    } else {
        echo '<tr><td class="puncon1" colspan="' . (isset($_GET['action']) ? 4 : 3) . '">' . $lang_pms['No messages'] . '</td></tr>';
    }

    echo '</tbody>
</table>
</div>
</div>
</div>
<div class="postlinksb"><div class="inbox"><br/><p class="postlink conr"><input type="hidden" name="box" value="' . $box . '"/>' . ($all ? '<input type="submit" value="' . $lang_pms['Delete'] . '"/>' : '') . '</p><div class="clearer"></div></div></div>
</form>
<div class="clearer"></div>
</div>';

    if (isset($_GET['id'])) {
        $forum_id = $id;
    }

} else {

    if (isset($_POST['update'])) {
        isset($_POST['popup_enable']) ? $popup = 1 : $popup = 0;
        isset($_POST['messages_enable']) ? $msg_enable = 1 : $msg_enable = 0;
        $db->query('UPDATE ' . $db->prefix . 'users SET popup_enable=' . $popup . ', messages_enable=' . $msg_enable . ' WHERE id=' . $pun_user['id']) or error('Unable to update Private Messsage options', __FILE__, __LINE__, $db->error());
    }

    $result = $db->query('SELECT popup_enable, messages_enable FROM ' . $db->prefix . 'users WHERE id=' . $pun_user['id']) or error('Unable to fetch user info for Private Messsage options', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        message($lang_common['Bad request']);
    }
    $user = $db->fetch_assoc($result);

    echo '<div class="blockform">
<h2><span>' . $name . '</span></h2>
<div class="box">
<form id="messages" name="messages" method="post" action="message_list.php?box=2">
<div><input type="hidden" name="form_sent" value="1" /></div>
<div class="inform">
<fieldset id="profileavatar">
<legend>' . $lang_pms['Options PM'] . '</legend>
<div class="infldset">
<div class="rbox">
<label><input type="checkbox" name="popup_enable" value="1"';
    if ($user['popup_enable'] == 1) {
        echo ' checked="checked"';
    }
    echo ' />' . $lang_pms['Use popup'] . '<br /></label><label><input type="checkbox" name="messages_enable" value="1"';
    if ($user['messages_enable'] == 1) {
        echo ' checked="checked"';
    }
    echo ' />' . $lang_pms['Use messages'] . '<br /></label>
</div>
</div>
</fieldset>
</div>
<p><input type="submit" name="update" value="' . $lang_pms['Send'] . '" />' . $lang_pms['Instructions'] . '</p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

}
$footer_style = 'message_list';
require_once PUN_ROOT . 'footer.php';

?>