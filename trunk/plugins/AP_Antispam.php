<?php
// Gemorroj


// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}


define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', 0.1);



if($_POST){

if(isset($_POST['antispam'])){
$db->query('UPDATE `'.$db->prefix.'config` SET `conf_value`="'.intval($_POST['antispam']).'" WHERE `conf_name`="antispam_enabled" LIMIT 1') or error('Unable to update board config', __FILE__, __LINE__, $db->error());

include PUN_ROOT.'include/cache.php';
generate_config_cache();
message('Данные для антиспама обновлены');
}


if(isset($_POST['regexp'])){
$db->query('TRUNCATE TABLE `'.$db->prefix.'spam_regexp`');

$regexp = explode("\n",trim($_POST['regexp']));
$all = sizeof($regexp);
for ($i=0; $i<$all; ++$i) {
    $db->query('INSERT INTO `'.$db->prefix.'spam_regexp` (`id`,`matches`,`regexpr`) VALUES ("0","0","'.trim($regexp[$i]).'")') or error('Unable to update board spam_regexp', __FILE__, __LINE__, $db->error());
}
unlink(PUN_ROOT.'cache/cache_spam_regexp.php');
message('Данные для антиспама обновлены');
}

}
else{

generate_admin_menu($plugin);

$regexp = null;

$q = $db->query('SELECT `regexpr` FROM `spam_regexp`');
while ($arr = $db->fetch_row($q)) {
    $regexp .= $arr[0] . "\n";
}

echo '<div class="block">
<h2><span>Антиспам v'.PLUGIN_VERSION.'</span></h2>
<div class="box">
<div class="inbox">
<p>Этот плагин позволяет включать / отключать антиспам и создавать регулярные выражения, для поиска спамерских сообщений.</p>
</div>
</div>
</div>
<div class="blockform">
<h2 class="block2"><span>Антиспам</span></h2>
<div class="box">
<form id="lang" method="post" action="'.$_SERVER['REQUEST_URI'].'">
<div class="inform">
<fieldset>
<legend>Включение / Отключение антиспама</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Антиспам</th>
<td>
<input type="radio" name="antispam" value="1"';
if($pun_config['antispam_enabled'] == 1){
echo ' checked="checked"';
}
print ' /> <strong>Да</strong>&#160; &#160;<input type="radio" name="antispam" value="0"';
if(!$pun_config['antispam_enabled']){
echo ' checked="checked"';
}
print '/> <strong>Нет</strong>
<span>Включить / Отключить антиспам</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="style_wap" value="Отправить" tabindex="2" /></p>
</form>
</div>
<h2 class="block2"><span>Правила для антиспама</span></h2>
<div class="box">
<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
<div class="inbox">
<p>Редактирование правил для антиспама.</p>
<table cellspacing="0">
<tr>
<th scope="row">Регулярные выражения</th>
<td>
<textarea name="regexp" rows="8" cols="68"/>'.htmlspecialchars($regexp,ENT_NOQUOTES,'UTF-8').'</textarea><br />
<span>Введите регулярные выражения для поиска спама. Каждое новое правило должно начинаться с новой строки.</span>
</td>
</tr>
</table>
</div>
<p class="submitend">
<input type="submit" name="forum_last_post" value="Отправить" tabindex="4" />
</p>
</form>
</div>
</div>';
}
?>