<?php

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/file_upload.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title'] . ' / sample slideshow');

$aid = intval($_GET['aid']);
$fid = intval($_GET['fid']);
$user_id = intval($_GET['user_id']);
$last = intval($_GET['last']);
if (!$last) {
    $last = 100;
}

$view_as = 'slideshow';
if ($aid && !$fid && !$user_id) {
    $view_as = 'fullsize';
}

function make_img_ref($aid, $is_preview)
{
global $pun_config;

if($is_preview)
{
$width = $pun_config['file_preview_width'];
$height = $pun_config['file_preview_height'];
$do_cut = false;
}
else
{
$width = $pun_config['file_thumb_width'];
$height = $pun_config['file_thumb_height'];
$do_cut = true;
}

return require_thumb_name($aid, $width, $height, $do_cut);
}

function generate_gallery_quickjump($forum_id)
{
global $db, $lang_common, $pun_user, $pun_config;

$output = '<form id="qjump" method="get" action="slideshow.php"><div><label>' . $lang_common['Jump to'] . '<br /><select name="fid" onchange="window.location=(\'' . $pun_config['o_base_url'] . '/slideshow.php?\'+this.options[this.selectedIndex].value)"><optgroup label="All categories"><option value="last=100">last</option></optgroup>';

$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

$cur_category = 0;
while($cur_forum = $db->fetch_assoc($result))
{
if($cur_forum['cid'] != $cur_category) // A new category since last iteration?
{
if($cur_category){
$output .= '</optgroup>';
}

$output .= '<optgroup label="'.pun_htmlspecialchars($cur_forum['cat_name']).'">';
$cur_category = $cur_forum['cid'];
}

$redirect_tag = ($cur_forum['redirect_url']) ? ' &gt;&gt;&gt;' : '';
$output .= '<option value="fid='.$cur_forum['fid'].'"'.(($forum_id == $cur_forum['fid']) ? ' selected="selected">' : '>').pun_htmlspecialchars($cur_forum['forum_name']).$redirect_tag.'</option>';
}

$output .= '</optgroup></select></label></div></form>';

return $output;
}


$attachments = $fid_list = $categories = $forums = array();

// get category list for cache
$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while($cur_category = $db->fetch_assoc($result)){
$categories[$cur_category['id']] = $cur_category['cat_name'];
}

if($last)
{
$last = min($last, 100);
$subtitle = 'last '.$last;

// get available forum list
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, fp.file_download
FROM
'.$db->prefix.'forums AS f LEFT JOIN
'.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
ORDER BY f.id') or
error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

while($cur_forum = $db->fetch_assoc($result))
{
$fid_list[] = $cur_forum['fid'];

// we have to calculate download rights for every forum
$mods_array = ($cur_forum['moderators']) ? unserialize($cur_forum['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;
$can_download = $is_admmod || (!$cur_forum['file_download'] && $pun_user['g_file_download'] == 1) || $cur_forum['file_download'] == 1;

$forums[$cur_forum['fid']] = array(
'forum_name' => $cur_forum['forum_name'],
'can_download' => $can_download
);
}
$fid_list = implode(',', $fid_list);
unset($can_download);

$order_and_limit = 'ORDER BY t.posted DESC LIMIT '.$last;
}
else if($fid)
{
// get available forum list
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, fp.file_download
FROM
'.$db->prefix.'forums AS f LEFT JOIN
'.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
WHERE (f.id='.$fid.') AND (fp.read_forum IS NULL OR fp.read_forum=1)') or
error('Unable to fetch forum', __FILE__, __LINE__, $db->error());

if(!$db->num_rows($result)){
message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);
$subtitle = pun_htmlspecialchars($cur_forum['forum_name']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_posts'] * ($p - 1);

$order_and_limit = 'ORDER BY t.posted DESC LIMIT '.$start_from.','.$pun_user['disp_posts'];
}

$forum_cond = ($fid != 0)? ('f.id='.$fid.' AND ') : ('f.id in ('.$fid_list.') AND');
$user_cond = ($user_id!=0)? ('(a.poster_id='.$user_id.') AND '): '';

$result = $db->query('SELECT
f.id AS fid, f.forum_name, t.id AS tid, t.subject, t.last_post, t.poster, t.posted,
a.id AS id, a.mime, a.uploaded, a.image_dim, a.filename, a.downloads, a.location, a.size, a.poster_id, u.username
FROM
'.$db->prefix.'attachments AS a INNER JOIN
'.$db->prefix.'users AS u ON a.poster_id=u.id INNER JOIN
'.$db->prefix.'topics AS t ON a.topic_id=t.id INNER JOIN
'.$db->prefix.'forums AS f ON f.id = t.forum_id
WHERE ' . $forum_cond . $user_cond . '
(image_dim<>\'\')'.$order_and_limit) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

while($row = $db->fetch_assoc($result))
{
// can user download this attachment? it depends on per-forum permissions
$row['can_download'] = $forums[$row['fid']]['can_download'];
// prepare all previews
require_thumb($row['id'], $row['location'], $pun_config['file_preview_width'], $pun_config['file_preview_height'], false);
$attachments[] = $row;
}



// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('r').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability

switch ($view_as){
case 'slideshow':
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang_common['lang_encoding'] ?>" />
<title><?php echo $page_title ?></title>
<link rel="stylesheet" type="text/css" href="style/imports/slideshow.css" />
<script type="text/javascript">
<!--
var browser={isKHTML: false, isGecko: false, isIE: false, isMac : false, isIE5: false, isIE55: false, isWin: false, isOpera: false, isOpera75: false, isSafe:null, hasXMLHttp : false};
with (browser) {
isKHTML = navigator.userAgent.indexOf("KHTML")>=0;
isGecko = (!isKHTML) && navigator.product == "Gecko";
isIE = (!isGecko) && navigator.cpuClass != undefined && navigator.appName == "Microsoft Internet Explorer";
isIE5 = isIE && (!Function.apply);
isIE55 = isIE && (document.onmousewheel == undefined);
isOpera = (!(isIE || isGecko || isKHTML)) && document.attachEvent != undefined;
isMac = (navigator.appVersion.indexOf("Mac") >= 0);
if (isOpera){
isOpera75 = (!/Opera[^0-9]*(?:[1-6]|[7\.[1-4]])/.test(navigator.userAgent));
}
if (isOpera){
var r = new XMLHttpRequest;
hasXMLHttp = r.setRequestHeader ? true : false;
delete r;
}else{
hasXMLHttp = browser.isIE || window.XMLHttpRequest;
}
isWin = (navigator.appVersion.indexOf("Windows") != -1) ? true : false;
if (isWin) {
browser.isWin2k = (navigator.userAgent.indexOf("Windows NT 5.0") > 0) ? true : false;
}
isSafe = document.getElementById != undefined && (!isIE5) && (document.addEventListener != undefined || document.attachEvent != undefined);
}
var
<?php

if($attachments)
{
$tmp = array();
foreach ($attachments as $row)
{
$filesize = ($row['size']>=1048576)? (round($row['size']/1048576,0).'mb'): (round($row['size']/1024,0).'kb');
$format = (preg_match('/^image\/(.*)$/i', $row['mime'], $regs))? (' ('. $row['image_dim'].' '.$regs[1].')'): '';

$tmp[] = "'".$row['id']."': [".
"'".format_time($row['uploaded'])."',".
"'".$row['poster_id']."',".
"'".$row['username']."',".
"'".$row['fid']."',".
"'".pun_htmlspecialchars($row['forum_name'])."',".
"'".$row['tid']."',".
"'".pun_htmlspecialchars($row['subject'])."',".
"'".$filesize.$format."']";
}
echo 'ATTACH_DATA={ '.implode(",\n", $tmp).' };';
unset($tmp);
}

?>
function changeBg(o)
{
div = document.getElementById("content");
div.style.backgroundColor = o.style.backgroundColor;
return false;
}
function LoadImg(id)
{
if (null == id) {
var h = document.location.href;
if (h.indexOf('#')!=-1)

id = h.substring(h.indexOf('#')+2);
else
for (var i in ATTACH_DATA) {id = i; if(!browser.isOpera) break;}
}
var img = document.getElementById('photo');
var img_ref = document.getElementById('photo_ref');
img.src = "<? echo make_img_ref('" + id + "', true); ?>";
img_ref.href = "slideshow.php?aid=" + id;
var data = ATTACH_DATA[id];
var user = document.getElementById('user_info');
var forum = document.getElementById('forum_info');
var topic = document.getElementById('topic_info');
if (data[1] == 1)
user.innerHTML = data[0] + ': <strong>'+data[2]+'</strong>: '+data[7];
else
user.innerHTML = data[0] + ': <a href="profile.php?id=' +data[1]+'">'+data[2]+'</a>: '+data[7];
forum.innerHTML = ' &raquo; <a href="viewforum.php?id='+data[3]+'">'+data[4]+'</a>';
topic.innerHTML = ' &raquo; <a href="viewtopic.php?id='+data[5]+'">'+data[6]+'</a>';
return true;
}
//-->
</script>
</head>
<body <?php if ($attachments) { echo 'onload="javascript:LoadImg()"';} ?>>
<div id="punwrap">
<div id="punslideshow" class="pun">
<div id="left">
<ul class="topic">
<?php
foreach ($attachments as $row)
{
?>
<li><a class="hor" href="#a<?php echo $row['id'] ?>" onclick="return LoadImg('<?php echo $row['id'] ?>')"><img alt="" src="<?php echo make_img_ref($row['id'], false); ?>" /></a> </li>
<?php
}

echo '</ul>
<div class="clearer"></div>
</div>
<div id="content">
<h2>
<span id="color_palette" class="conr">
<a href=# style="background-color:#FFFFFF" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#E5E5E5" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#CCCCCC" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#B3B3B3" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#999999" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#808080" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#666666" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#4D4D4D" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#333333" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#1A1A1A" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#000000" onclick="return changeBg(this);">&#160; &#160;</a>
</span>
<span id="user_info">date: <a href="">user</a>: size</span>
</h2>
<table id="image_preview"><tr><td>
<p><a id="photo_ref" href="" target="_blank"><img id="photo" src=""/></a></p>
</td></tr></table>
</div>
<div id="brdheader" class="block">
<div class="box">
<div id="brdtitle" class="inbox">
<h1><span>'.pun_htmlspecialchars($pun_config['o_board_title']).'</span></h1>
<p><span>sample slideshow</span></p>
</div>
</div>
<div id="brdmenu" class="inbox">'.generate_navlinks().'</div>
<div id="top_link" class="postlink">
<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li id="forum_info">forum</li><li id="topic_info"> &raquo; topic</li></ul>
</div>
</div>
<div id="brdfooter" class="block">
<div class="box">
<div class="inbox conr">
<p>&copy; forum engine: PunBB</p>
<p>&copy; CSS tricks: Stuart A Nicholls<p>
<p>&copy; gallery mod: artoodetoo and Gemorroj</p>
</div>
<div id="quickjump" class="inbox">'.generate_gallery_quickjump($fid).'</div>
</div>
</div>
</div>
</div>
</body>
</html>';
break;

case 'picker':
break;

case 'fullsize':

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$lang_common['lang_encoding'].'" />
<title>fullsize</title>
<style type="text/css">
html{font-size:76%; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; line-height: normal;}
body {padding: 0; margin: 0; border: 0; background: #808080;}
img {border: none}
#image_preview {text-align: center; padding: 10px;}
#colorPalatte a {text-decoration: none; padding: 0 3px;}
</style>
<script language="JavaScript" type="text/javascript">
function changeBg(o)
<!--
{
div = document.getElementById("image_preview");
div.style.backgroundColor = o.style.backgroundColor;
return false;
}
//-->
</script>
</head>
<body>
<div id="image_preview">
<br />
<div id="colorPalatte">
<a href=# style="background-color:#FFFFFF" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#E5E5E5" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#CCCCCC" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#B3B3B3" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#999999" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#808080" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#666666" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#4D4D4D" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#333333" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#1A1A1A" onclick="return changeBg(this);">&#160; &#160;</a>
<a href=# style="background-color:#000000" onclick="return changeBg(this);">&#160; &#160;</a>
</div>
<p><a href="javascript:self.close()"><img id="photo" src="download.php?aid='.$aid.'"></a></p>
</div>
</body>
</html>';
break;
}

?>