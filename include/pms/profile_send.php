<?php
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/pms.php';

if ($pun_config['o_pms_enabled'] && !$pun_user['is_guest'] && $pun_user['g_pm'] == 1) {
    echo '<dt>' . $lang_pms['PM'] . ': </dt><dd><a href="message_send.php?id=' . $id . '">' . $lang_pms['Quick message'] . '</a></dd>';
}
