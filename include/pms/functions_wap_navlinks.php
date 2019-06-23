<?php

require_once PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

if ($pun_config['o_pms_enabled'] && 1 == $pun_user['g_pm']) {
    $links[] = '<a href="message_list.php">'.$lang_pms['Private'].'</a>';
}
