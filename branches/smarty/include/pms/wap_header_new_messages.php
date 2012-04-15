<?php
if (! $pun_user['is_guest'] &&
    $pun_user['g_pm'] == 1 &&
    $pun_config['o_pms_enabled'] &&
    $pun_user['messages_enable'] == 1) {
    
    require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';
    
    // Check for new messages
    $result_messages = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'messages WHERE showed=0 AND owner='.$pun_user['id']) or error('Unable to check for new messages', __FILE__, __LINE__, $db->error());
    $new_msg = $db->fetch_row($result_messages);
    
    if ($new_msg[0] > 0) {
        $smarty->append('conditions', array('count_new_msgs' => $new_msg[0]));
    }
    
    // Check if the inbox is full
    $result = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']) or error('Unable to test if the message-box is full', __FILE__, __LINE__, $db->error());
    $count = $db->fetch_row($result);

    if ($count[0] >= $pun_user['g_pm_limit']) {
        $smarty->append('conditions', array('full_inbox' => true));
    }
}