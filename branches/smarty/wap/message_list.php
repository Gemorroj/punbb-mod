<?php
define('PUN_ROOT', '../');

require PUN_ROOT . 'include/common.php';
include_once PUN_ROOT . 'include/parser.php';
require PUN_ROOT . 'wap/header.php';

if (!$pun_config['o_pms_enabled'] || !$pun_user['g_pm']) {
    wap_message($lang_common['No permission']);
}

if ($pun_user['is_guest']) {
    wap_message($lang_common['Login required']);
}

// Load the message.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/misc.php';

$box = intval($_GET['box']);
if ($box != 1 && $box != 2) {
    $box = 0;
}

switch ($box) {
    case 0:
        $name = $lang_pms['Inbox'];
        break;


    case 1:
        $name = $lang_pms['Outbox'];
        break;


    case 2:
        $name = $lang_pms['Options'];
        break;
}

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);

// Delete multiple posts
if (isset($_POST['delete_messages']) || isset($_POST['delete_messages_comply'])) {
    if (isset($_POST['delete_messages_comply'])) {
        //Check this is legit
        //confirm_referrer('message_list.php');

        if (preg_match('/[^0-9,]/', $_POST['messages']) || !trim($_POST['messages'])) {
            wap_message($lang_common['Bad request']);
        }

        // Delete messages
        $db->query('DELETE FROM ' . $db->prefix . 'messages WHERE id IN(' . $_POST['messages'] . ') AND owner=\'' . $pun_user['id'] . '\'') or error('Unable to delete messages.', __FILE__, __LINE__, $db->error());
        wap_redirect('message_list.php?box=' . intval($_POST['box']));
    } else {
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_pms['Multidelete'];
        $idlist = is_array($_POST['delete_messages']) ? array_map('intval', $_POST['delete_messages']) : array();

        $smarty->assign('page_title', $page_title);
        $smarty->assign('lang_pms', $lang_pms);
        $smarty->assign('idlist_str', implode(',', array_values($idlist)));
        //$smarty->assign('', $);

        $smarty->display('message_list.delete_messages.tpl');

        exit();
    }
} // Mark all messages as read
else if (isset($_GET['action']) && $_GET['action'] == 'markall') {
    $db->query('UPDATE ' . $db->prefix . 'messages SET showed=1 WHERE owner=' . $pun_user['id']) or error('Unable to update message status', __FILE__, __LINE__, $db->error());
    //$p = (!isset($_GET['p']) || $_GET['p'] <= 1) ? 1 : $_GET['p'];
    wap_redirect('message_list.php?box=' . $box . '&p=' . $p);
}

$page_title = $pun_config['o_board_title'] . ' / ' . $lang_pms['Private Messages'] . ' - ' . $name;
$smarty->assign('page_title', $page_title);

if ($box < 2) {
    // Get message count
    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE status=' . $box . ' AND owner=' . $pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
    list($num_messages) = $db->fetch_row($result);

    //What page are we on?
    $num_pages = ceil($num_messages / $pun_config['o_pms_mess_per_page']);
    $p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
    $start_from = $pun_config['o_pms_mess_per_page'] * ($p - 1);
    if ($_GET['action'] != 'all') {
        $limit = 'LIMIT ' . $start_from . ',' . $pun_config['o_pms_mess_per_page'];
    }

    //Are we viewing a PM?
    if (isset($_GET['id'])) {

        //Yes! Lets get the details
        $id = intval($_GET['id']);

        // Set user
        $result = $db->query('SELECT status,owner FROM ' . $db->prefix . 'messages WHERE id=' . $id) or error('Unable to get message status', __FILE__, __LINE__, $db->error());
        list($status, $owner) = $db->fetch_row($result);
        $status == 0 ? $where = 'u.id=m.sender_id' : $where = 'u.id=m.owner';

        $result = $db->query('SELECT m.id AS mid,m.subject,m.sender_ip,m.message,m.smileys,m.posted,m.showed,u.id,u.group_id as g_id,g.g_user_title,u.username,u.registered,u.email,u.title,u.url,u.icq,u.msn,u.aim,u.yahoo,u.location,u.use_avatar,u.email_setting,u.num_posts,u.admin_note,u.signature,o.user_id AS is_online FROM ' . $db->prefix . 'messages AS m,' . $db->prefix . 'users AS u LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.idle=0) LEFT JOIN ' . $db->prefix . 'groups AS g ON u.group_id = g.g_id WHERE ' . $where . ' AND m.id=' . $id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
        $cur_post = $db->fetch_assoc($result);

        if ($owner != $pun_user['id']) {
            wap_message($lang_common['No permission']);
        }

        if (!$cur_post['showed']) {
            $db->query('UPDATE ' . $db->prefix . 'messages SET showed=1 WHERE id=' . $id) or error('Unable to update message info', __FILE__, __LINE__, $db->error());
        }

        if ($cur_post['id'] > 0) {

            // We only show location, register date, post count and the contact links if "Show user info" is enabled
            if ($pun_config['o_show_user_info'] == 1) {
                if ($cur_post['location']) {
                    if ($pun_config['o_censoring'] == 1) {
                        $cur_post['location'] = censor_words($cur_post['location']);
                    }
                }
            }
        } // If the sender has been deleted
        else {
            $result = $db->query('SELECT id,sender,message,posted FROM ' . $db->prefix . 'messages WHERE id=' . $id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
            $cur_post = $db->fetch_assoc($result);
        }

        // Perform the main parsing of the message (BBCode, smilies, censor words etc)
        $cur_post['smileys'] = isset($cur_post['smileys']) ? $cur_post['smileys'] : $pun_user['show_smilies'];
        $cur_post['message'] = parse_message($cur_post['message'], intval(!$cur_post['smileys']));
    }

    if ($pun_user['g_pm_limit'] && $pun_user['g_id'] > PUN_GUEST) {
        // Get total message count
        $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
        list($tot_messages) = $db->fetch_row($result);
        $proc = ceil($tot_messages / $pun_user['g_pm_limit'] * 100);
    }

    // Fetch messages
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id'] . ' AND status=' . $box . ' ORDER BY posted DESC ' . $limit) or error('Unable to fetch messages list for forum', __FILE__, __LINE__, $db->error());

    // If there are messages in this folder.
    if ($all = $db->num_rows($result)) {
        while ($cur_mess = $db->fetch_assoc($result)) {

            $messages[] = $cur_mess;
        }
    }

    if ($_GET['action'] == 'all') {

        $p = $num_pages + 1;
    }

    $page_links = paginate($num_pages, $p, 'message_list.php?box=' . $box);

    if (isset($_GET['id'])) {
        $forum_id = $id;
    }

    ////////
    //$smarty->debugging = true;
    $smarty->assign('name', $name);
    $smarty->assign('cur_post', $cur_post);
    $smarty->assign('pun_user', $pun_user);
    $smarty->assign('lang_topic', $lang_topic);
    $smarty->assign('status', $status);
    $smarty->assign('lang_pms', $lang_pms);
    $smarty->assign('messages', $messages);
    $smarty->assign('page_links', $page_links);
    $smarty->assign('all', $all);

    $smarty->display('message_list.tpl');

    exit();

} else {
    if (isset($_POST['update'])) {
        $popup = isset($_POST['popup_enable']) ? 1 : 0;
        $msg_enable = isset($_POST['messages_enable']) ? 1 : 0;
        $db->query('UPDATE ' . $db->prefix . 'users SET popup_enable=' . $popup . ', messages_enable=' . $msg_enable . ' WHERE id=' . $pun_user['id']) or error('Unable to update Private Messsage options', __FILE__, __LINE__, $db->error());
    }

    $result = $db->query('SELECT popup_enable, messages_enable FROM ' . $db->prefix . 'users WHERE id=' . $pun_user['id']) or error('Unable to fetch user info for Private Messsage options', __FILE__, __LINE__, $db->error());
    if (!$db->num_rows($result)) {
        wap_message($lang_common['Bad request']);
    }

    $user = $db->fetch_assoc($result);

    //Messsage options

    $smarty->assign('lang_pms', $lang_pms);
    $smarty->assign('user', $user);
    //$smarty->assign('', $);

    $smarty->display('message_list.options.tpl');

    exit();
}

