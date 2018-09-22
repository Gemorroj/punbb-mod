<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.0 mod');

function RoundSigDigs($number, $sigdigs)
{
    $multiplier = 1;
    while ($number < 0.1) {
        $number *= 10;
        $multiplier /= 10;
    }
    while ($number >= 1) {
        $number /= 10;
        $multiplier *= 10;
    }
    return round($number, $sigdigs) * $multiplier;
}

if (isset($_POST['lang'])) {
    // Do Post
    $db->query('UPDATE ' . $db->prefix . 'users SET language=\'' . $_POST['form']['language'] . '\' WHERE id>1') or error('Unable to set lang settings', __FILE__, __LINE__, $db->error());
    message('Языки установлены');
} elseif (isset($_POST['style'])) {
    // Do Post
    $db->query('UPDATE ' . $db->prefix . 'users SET style=\'' . $_POST['form']['style'] . '\' WHERE id>1') or error('Unable to set style settings', __FILE__, __LINE__, $db->error());
    message('WEB стили установлены');
} elseif (isset($_POST['style_wap'])) {
    // Do Post
    $db->query('UPDATE ' . $db->prefix . 'users SET style_wap=\'' . $_POST['form']['style_wap'] . '\' WHERE id>1') or error('Unable to set style settings', __FILE__, __LINE__, $db->error());
    message('WAP стили установлены');
} else {
    // If not, we show the form
    // Display the admin navigation menu
    generate_admin_menu($plugin);


    echo '<div class="block">
<h2><span>Языковая и стилевая статистика/устнановка - v' . PLUGIN_VERSION . '</span></h2>
<div class="box">
<div class="inbox">
<p>Этот плагин позволяет смотреть какие стили и языки использую пользватели форума и назначать в ручную.</p>
</div>
</div>
</div>
<div class="blockform">
<h2 class="block2"><span>Языки</span></h2>
<div class="box">
<form id="lang" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
<div class="inform">
<fieldset>
<legend>Языки</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Используются языки</th>
<td>';

    $result = $db->query('SELECT language, COUNT(1) AS number FROM ' . $db->prefix . 'users WHERE id > 1 GROUP BY language ORDER BY number') or error('Unable to fetch lang settings', __FILE__, __LINE__, $db->error());
    $number = $db->num_rows($db->query('SELECT username FROM ' . $db->prefix . 'users WHERE id > 1'));
    while ($cur_lang = $db->fetch_assoc($result)) {
        echo $cur_lang['number'] . ' - ' . RoundSigDigs($cur_lang['number'] / $number * 100, 3) . '% <strong>' . str_replace('_', ' ', $cur_lang['language']) . '</strong><br />';
    }

    echo '</td></tr><tr><th scope="row">Язык</th><td>';

    $languages = array();
    $d = dir(PUN_ROOT . 'lang');
    while (($entry = $d->read()) !== false) {
        if ($entry != '.' && $entry != '..' && is_dir(PUN_ROOT . 'lang/' . $entry)) {
            $languages[] = $entry;
        }
    }
    $d->close();

    echo '<select name="form[language]">';

    foreach ($languages as $temp) {
        echo '<option value="' . $temp . '">' . $temp . '</option>';
    }

    echo '</select>
<span>Все языки будут приведены к указанному.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="lang" value="Отправить" /></p>
</form>
</div>
</div>
<div class="blockform">
<h2 class="block2"><span>WEB Стили</span></h2>
<div class="box">
<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
<div class="inform">
<fieldset>
<legend>WEB Стили</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Используемые WEB стили</th><td>';

    $result = $db->query('SELECT style, COUNT(1) AS number FROM ' . $db->prefix . 'users WHERE id > 1 GROUP BY style ORDER BY number') or error('Unable to fetch style settings', __FILE__, __LINE__, $db->error());
    $number = $db->num_rows($db->query('SELECT username FROM ' . $db->prefix . 'users WHERE id > 1'));

    while ($cur_lang = $db->fetch_assoc($result)) {
        echo $cur_lang['number'] . ' - ' . RoundSigDigs($cur_lang['number'] / $number * 100, 3) . '% <strong>' . str_replace('_', ' ', $cur_lang['style']) . '</strong><br />';
    }

    echo '</td></tr><tr><th scope="row">WEB Стиль</th><td>';

    $styles = array();
    $d = dir(PUN_ROOT . 'style');
    while (($entry = $d->read()) !== false) {
        if (pathinfo($entry, PATHINFO_EXTENSION) == 'css') {
            $styles[] = pathinfo($entry, PATHINFO_FILENAME);
        }
    }
    $d->close();


    echo '<select name="form[style]">';

    foreach ($styles as $temp) {
        echo '<option value="' . $temp . '">' . str_replace('_', ' ', $temp) . '</option>';
    }

    echo '</select>
<span>WEB стили всех пользователей будут приведены к указанному.</span>
</td>
</tr></table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="style" value="Отправить" /></p>
</form>
</div>
</div>
<div class="blockform">
<h2 class="block2"><span>WAP Стили</span></h2>
<div class="box">
<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
<div class="inform">
<fieldset>
<legend>WAP Стили</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Используемые WAP стили</th><td>';

    $result = $db->query('SELECT `style_wap`, COUNT(1) AS `number` FROM `' . $db->prefix . 'users` WHERE id > 1 GROUP BY `style_wap` ORDER BY `number`') or error('Unable to fetch style_wap settings', __FILE__, __LINE__, $db->error());
    $number = $db->num_rows($db->query('SELECT `username` FROM `' . $db->prefix . 'users` WHERE id > 1'));

    while ($cur_lang = $db->fetch_assoc($result)) {
        echo $cur_lang['number'] . ' - ' . RoundSigDigs($cur_lang['number'] / $number * 100, 3) . '% <strong>' . str_replace('_', ' ', $cur_lang['style_wap']) . '</strong><br />';
    }


    echo '</td></tr><tr><th scope="row">WAP Стиль</th><td>';

    $stylesWap = array();
    $d = dir(PUN_ROOT . 'include/template/wap');
    while (($entry = $d->read()) !== false) {
        if ($entry[0] != '.' && is_dir(PUN_ROOT . 'include/template/wap/' . $entry)) {
            $stylesWap[] = $entry;
        }
    }
    $d->close();


    echo '<select name="form[style_wap]">';

    foreach ($stylesWap as $temp) {
        echo '<option value="' . $temp . '">' . str_replace('_', ' ', $temp) . '</option>';
    }

    echo '</select>
<span>WAP стили всех пользователей будут приведены к указанному.</span>
</td>
</tr></table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="style_wap" value="Отправить" /></p>
</form>
</div>
</div>';
}
