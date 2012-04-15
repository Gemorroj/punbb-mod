<?php
define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';


if($pun_user['g_id']!=PUN_MOD && $pun_user['g_id']!=PUN_ADMIN){
wap_message($lang_common['No permission']);
}


$id = intval($_GET['id']);

if($id>0){
$result_messages = $db->query('SELECT message FROM '.$db->prefix.'spam_repository WHERE id='.$id) or error('Unable check spam message', __FILE__, __LINE__, $db->error());
$return = $db->fetch_assoc($result_messages);
}
else{
wap_message($lang_common['Bad request']);
}

require PUN_ROOT.'lang/'.$pun_user['language'].'/common.php';
//require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';
include_once PUN_ROOT.'include/parser.php';
$return['message'] = parse_message($return['message'], 1);


print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$lang_common['lang_encoding'].'" />
<title>'.$mod_title.' AntiSPAM</title>
<link rel="stylesheet" type="text/css" href="'.PUN_ROOT.'style/'.$pun_user['style'].'.css" />
</head>
<body>
<div id="punwrap">
<div id="punmessage_list" class="pun">
<div class="class="block">
<div class="blockform">
<h2><span>'.$lang_misc['Antispam despository'].'</span></h2>
<div class="box" style="text-align:justify">
<p>'.$return['message'].'<br /></p>
<ul>
<li><a href="javascript:window.close();">'.$lang_misc['Antispam close window'].'</a></li>
</ul>
</div>
</div>
<div class="clearer"></div>
</div>
</div>
</div>
</body>
</html>';
?>