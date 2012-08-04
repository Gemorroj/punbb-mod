<?php
if (!defined('PUN') or !defined('PUN_ROOT')) exit;
define('PUN_HEADER', 1);


$pun_xhtml = stripos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') ? 'application/xhtml+xml' : 'text/html';

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: ' . gmdate('r') . ' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-Type: ' . $pun_xhtml . '; charset=UTF-8');


require_once(PUN_ROOT . 'include/PunTemplate.php');
$smarty = new PunTemplate($pun_user['style_wap']);
$smarty->assign('pun_start', $pun_start);
$smarty->assign('pun_config', $pun_config);
$smarty->assign('date_format', '%Y.%m.%d %H:%I');
$smarty->assign('lang_common', $lang_common);
$smarty->assign('pun_xhtml', $pun_xhtml);

if ($pun_user['g_id'] < PUN_GUEST) {

    $result_header = $db->query('SELECT COUNT(1) FROM `' . $db->prefix . 'reports` WHERE `zapped` IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

    if ($db->result($result_header)) {
        $smarty->append('conditions', array('reports' => true));
    }
}

require PUN_ROOT . 'include/pms/wap_header_new_messages.php';