<?php
if (!defined('PUN') or ! defined('PUN_ROOT')) exit();
define('PUN_HEADER', 1);


ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once(PUN_ROOT . 'include/PunTemplate.php');
$smarty = new PunTemplate($pun_user['style_wap']);
$smarty->assign('pun_start', $pun_start);

if ($pun_user['g_id'] < PUN_GUEST) {

    $result_header = $db->query('SELECT COUNT(1) FROM `' . $db->prefix . 'reports` WHERE `zapped` IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

    if ($db->result($result_header)) {
        $smarty->append('conditions', array('reports' => true));
    }
}

require PUN_ROOT . 'include/pms/wap_header_new_messages.php';