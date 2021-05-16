<?php

if (!(\defined('PUN') && \defined('PUN_ROOT'))) {
    exit;
}
\define('PUN_HEADER', 1);

\header('Content-Type: text/html; charset=UTF-8');

require_once PUN_ROOT.'include/PunTemplate.php';
$smarty = new PunTemplate($pun_user['style_wap']);
$smarty->assign('pun_start', $pun_start);
$smarty->assign('pun_config', $pun_config);
$smarty->assign('pun_user', $pun_user);
$smarty->assign('date_format', '%Y.%m.%d %H:%I');
$smarty->assign('lang_common', $lang_common);
$smarty->assign('basename', \basename($_SERVER['PHP_SELF']));

if ($pun_user['g_id'] < PUN_GUEST) {
    $result = $db->query('SELECT COUNT(1) FROM `'.$db->prefix.'reports` WHERE `zapped` IS NULL') or \error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

    if ($count = $db->result($result, 0)) {
        $smarty->assign('reports', $count);
    }
}

require_once PUN_ROOT.'include/pms/wap_header_new_messages.php';
