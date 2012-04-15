<?php
define('PUN_ROOT', '../');

require PUN_ROOT . 'include/common.php';


if ($pun_user['g_id'] != PUN_MOD && $pun_user['g_id'] != PUN_ADMIN) {
    
    wap_message($lang_common['No permission']);
}

$id = intval($_GET['id']);

if ($id > 0) {
    
    $result_messages = $db->query('SELECT message FROM '.$db->prefix.'spam_repository WHERE id='.$id) or error('Unable check spam message', __FILE__, __LINE__, $db->error());
    $return = $db->fetch_assoc($result_messages);
}
else {
    
    wap_message($lang_common['Bad request']);
}

require_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/common.php';
require_once PUN_ROOT . 'include/parser.php';

$return['message'] = parse_message($return['message'], 1);

//require PUN_ROOT . 'include/PunTemplate.php';

//$smarty->assign('return', $return);