<?php

require_once PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

if ($pun_config['o_pms_enabled'] && !$pun_user['is_guest'] && 1 == $pun_user['g_pm']) {
    $pid = $cur_post['poster_id'] ?? $cur_post['id'];
    $user_contacts[] = '<a href="message_send.php?id='.$pid.'&amp;tid='.$id.'">'.$lang_pms['PM'].'</a>';
}
