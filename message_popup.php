<?php

\define('PUN_ROOT', './');

require_once PUN_ROOT.'include/common.php';

require_once PUN_ROOT.'include/parser.php';

if (!$pun_config['o_pms_enabled'] || !$pun_user['g_pm']) {
    message($lang_common['No permission']);
}

if ($pun_user['is_guest']) {
    message($lang_common['Login required']);
}

if (isset($_GET['id'])) {
    $id = \intval($_GET['id']);
    $result_messages = $db->query('SELECT owner, sender, posted, subject FROM '.$db->prefix.'messages WHERE status=0 AND id='.$id) or error('Unable check owner popup', __FILE__, __LINE__, $db->error());
    $return = $db->fetch_assoc($result_messages);
    if ($return['owner'] != $pun_user['id']) {
        message($lang_common['No permission']);
    }
} else {
    message($lang_common['No permission']);
}

//require PUN_ROOT.'lang/'.$pun_user['language'].'/common.php';
//require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

// Load the message.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>'.$pun_config['o_board_title'].': Private Message Services</title>
<link rel="stylesheet" type="text/css" href="'.PUN_ROOT.'style/'.$pun_user['style'].'.css" />
</head>
<body>
<script src="js/message_popup.js"></script>
<div id="punwrap">
<div id="punmessage_list" class="pun">
<div class="block">
<div class="blockform">
<h2><span>'.$lang_pms['New messages'].'</span></h2>
<div class="box" style="text-align:center">
<p>'.$lang_pms['Popup new'].pun_htmlspecialchars($return['sender']).$lang_pms['Popup subj'].'<strong>'.pun_htmlspecialchars($return['subject']).'</strong><br />'.$lang_pms['Popup send'].format_time($return['posted']).'</p>
<ul>
<li><a href="javascript:go_read_msg('.$id.');">'.$lang_pms['Popup open msg'].'</a></li>
<li><a href="javascript:goto_inbox();">'.$lang_pms['Popup go in'].'</a></li>
<li><a href="javascript:goto_this_inbox();">'.$lang_pms['Popup open this'].'</a></li>
<li><a href="javascript:window.close();">'.$lang_pms['Popup close'].'</a></li>
</ul>
</div>
</div>
<div class="clearer"></div>
</div>
</div>
</div>
</body>
</html>';
