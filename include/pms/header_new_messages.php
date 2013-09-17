<?php
if (!$pun_user['is_guest'] && $pun_user['g_pm'] == 1 && $pun_config['o_pms_enabled'] && $pun_user['messages_enable'] == 1) {
    include PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';

    // Check for new messages
    $result_messages = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE showed=0 AND owner=' . $pun_user['id']) or error('Unable to check for new messages', __FILE__, __LINE__, $db->error());
    $new_msg = $db->fetch_row($result_messages);
    if ($new_msg[0] > 0) {
        $tpl_temp .= '<li class="pmlink"><strong><a href="message_list.php"> ' . $lang_pms['New messages'] . ' (' . $new_msg[0] . ')</a></strong></li>';

        if ($pun_user['popup_enable'] == 1) {
            $result = $db->query('SELECT id FROM ' . $db->prefix . 'messages WHERE popup=0 AND owner=' . $pun_user['id'] . ' ORDER BY id DESC') or error('Unable update popup status', __FILE__, __LINE__, $db->error());
            $return = $db->fetch_row($result);

            if ($return[0]) {
                JsHelper::getInstance()->addInternal('window.open("' . PUN_ROOT . 'message_popup.php?id=' . $return[0] . '","NewPM","width=760,height=200,resizable=yes,scrollbars=yes");');
                $db->query('UPDATE ' . $db->prefix . 'messages SET popup=1 WHERE popup=0 AND owner=' . $pun_user['id']) or error('Unable to update popup status', __FILE__, __LINE__, $db->error());
            }
        }
    }
    // Check if the inbox is full
    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id']) or error('Unable to test if the message-box is full', __FILE__, __LINE__, $db->error());
    $count = $db->fetch_row($result);

    // Display error message
    if ($count[0] >= $pun_user['g_pm_limit']) {
        $tpl_temp .= '<li class="pmlink"><strong><a href="message_list.php">' . $lang_pms['Full inbox'] . '</a></strong></li>';
    }
}
