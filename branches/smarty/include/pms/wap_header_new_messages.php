<?php
if (! $pun_user['is_guest']
    && $pun_user['g_pm'] == 1
    && $pun_config['o_pms_enabled']
    && $pun_user['messages_enable'] == 1
) {
    if (empty($lang_pms)) {
        include(PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php');
    }
    // 2) В некоторых функциях можно просто global $lang_pms сделать и не придется еще раз include
    // 3) Вынести в шапку include гребаного $lang_pms и больше с ним нигде не париться.
    $smarty->assign('lang_pms', $lang_pms);
    
    // Check for new messages
    $result_messages = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE showed=0 AND owner=' . $pun_user['id']) or error('Unable to check for new messages', __FILE__, __LINE__, $db->error());
    $new_msgs = $db->result($result_messages, 0);

    if ($new_msgs > 0) {
        
        $smarty->assign('new_msgs', $new_msgs);
    }

    // Check if the inbox is full
    $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'messages WHERE owner=' . $pun_user['id']) or error('Unable to test if the message-box is full', __FILE__, __LINE__, $db->error());
    $count = $db->result($result, 0);

    if ($count >= $pun_user['g_pm_limit']) {
        
        $smarty->assign('full_inbox', true);
    }
}
