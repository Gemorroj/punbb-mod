<?php
define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/parser.php';

if(!$pun_config['o_pms_enabled'] || !$pun_user['g_pm'])
{message($lang_common['No permission']);}

if($pun_user['is_guest'])
{message($lang_common['Login required']);}

if(isset($_GET['id']))
{
$id = intval($_GET['id']);
$result_messages = $db->query('SELECT owner, sender, posted, subject FROM '.$db->prefix.'messages WHERE status=0 AND id='.$id) or error('Unable check owner popup', __FILE__, __LINE__, $db->error());
$return = $db->fetch_assoc($result_messages);
if($return['owner'] != $pun_user['id'])
{message($lang_common['No permission']);}
}
else
{message($lang_common['No permission']);}

//require PUN_ROOT.'lang/'.$pun_user['language'].'/common.php';
//require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';


// Load the message.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$lang_common['lang_encoding'].'" />
<title>'.$pun_config['o_board_title'].': Private Message Services</title>
<link rel="stylesheet" type="text/css" href="'.PUN_ROOT.'style/'.$pun_user['style'].'.css" />
</head>
<body>
<script language=\'javascript\'>
<!--
function goto_inbox() {
opener.document.location.href = \'message_list.php?box=0\';
window.close();
}
function goto_this_inbox() {
window.resizeTo(\'700\',\'500\');
document.location.href = \'message_list.php\';
}
function go_read_msg() {
window.resizeTo(\'800\',\'800\');
document.location.href = \'message_list.php?id='.$id.'&p=1&box=0\';
}
//-->
</script>
<div id="punwrap">
<div id="punmessage_list" class="pun">
<div class="class="block">
<div class="blockform">
<h2><span>'.$lang_pms['New messages'].'</span></h2>
<div class="box" style="text-align:center">
<p>'.$lang_pms['Popup new'].pun_htmlspecialchars($return['sender']).$lang_pms['Popup subj'].'<strong>'.pun_htmlspecialchars($return['subject']).'</strong><br />'.$lang_pms['Popup send'].format_time($return['posted']).'</p>
<ul>
<li><a href="javascript:go_read_msg();">'.$lang_pms['Popup open msg'].'</a></li>
<li><a href="javascript:goto_inbox();">'.$lang_pms['Popup go in'].'</a> ( <a href="javascript:goto_this_inbox();">'.$lang_pms['Popup open this'].'</a>)</li>
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
?>