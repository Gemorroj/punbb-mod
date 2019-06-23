<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT.'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

if (@$_POST['form_sent']) {
    // Custom referrer check (so we can output a custom error message)
    /*
    if(!preg_match('#^'.preg_quote(str_replace('www.', '', $pun_config['o_base_url']).'/admin_options.php', '#').'#i', str_replace('www.', '', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''))))
    {message($lang_common['Bad referrer']);}
    */

    $form = array_map('trim', $_POST['form']);

    if (!$form['board_title']) {
        message($lang_admin['options_fail_board_title']);
    }

    // Clean default_lang
    $form['default_lang'] = preg_replace('#[\.\\\/]#', '', $form['default_lang']);

    require PUN_ROOT.'include/email.php';

    $form['admin_email'] = strtolower($form['admin_email']);
    if (!is_valid_email($form['admin_email'])) {
        message($lang_admin['options_fail_email']);
    }

    $form['webmaster_email'] = strtolower($form['webmaster_email']);
    if (!is_valid_email($form['webmaster_email'])) {
        message($lang_admin['options_fail_webm_email']);
    }

    if ($form['mailing_list']) {
        $form['mailing_list'] = strtolower(preg_replace('/[\s]/', '', $form['mailing_list']));
    }

    // Make sure base_url doesn't end with a slash
    if ('/' === substr($form['base_url'], -1)) {
        $form['base_url'] = substr($form['base_url'], 0, -1);
    }

    // Clean avatars_dir
    $form['avatars_dir'] = str_replace(chr(0), '', $form['avatars_dir']);

    // Make sure avatars_dir doesn't end with a slash
    if ('/' == substr($form['avatars_dir'], -1)) {
        $form['avatars_dir'] = substr($form['avatars_dir'], 0, -1);
    }

    if ($form['additional_navlinks']) {
        $form['additional_navlinks'] = trim(pun_linebreaks($form['additional_navlinks']));
        if ($form['additional_navlinks']) {
            $form['additional_navlinks'] .= "\n";
        }
    }

    if ($form['announcement_message']) {
        $form['announcement_message'] = pun_linebreaks($form['announcement_message']);
    } else {
        $form['announcement_message'] = $lang_admin['options_announcement_message'];

        if (1 == $form['announcement']) {
            $form['announcement'] = 0;
        }
    }

    if ($form['rules_message']) {
        $form['rules_message'] = pun_linebreaks($form['rules_message']);
    } else {
        $form['rules_message'] = $lang_admin['options_rules'];

        if (1 == $form['rules']) {
            $form['rules'] = 0;
        }
    }

    if ($form['maintenance_message']) {
        $form['maintenance_message'] = pun_linebreaks($form['maintenance_message']);
    } else {
        $form['maintenance_message'] = $lang_admin['options_maintenance'];

        if (1 == $form['maintenance']) {
            $form['maintenance'] = 0;
        }
    }

    $form['timeout_visit'] = intval($form['timeout_visit']);
    $form['timeout_online'] = intval($form['timeout_online']);
    $form['redirect_delay'] = intval($form['redirect_delay']);
    $form['topic_review'] = intval($form['topic_review']);
    $form['disp_topics_default'] = intval($form['disp_topics_default']);
    $form['disp_posts_default'] = intval($form['disp_posts_default']);
    $form['indent_num_spaces'] = intval($form['indent_num_spaces']);
    $form['avatars_width'] = intval($form['avatars_width']);
    $form['avatars_height'] = intval($form['avatars_height']);
    $form['avatars_size'] = intval($form['avatars_size']);
    $form['timeout_reg'] = intval($form['timeout_reg']);
    $form['timeout_merge'] = intval($form['timeout_merge']);
    $form['show_moderators'] = intval($form['show_moderators']);

    // голосования
    $db->query('UPDATE `'.$db->prefix.'config` SET `conf_value`="'.intval($form['poll']).'" WHERE conf_name="poll_enabled"') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
    unset($form['poll']);

    if ($form['timeout_online'] >= $form['timeout_visit']) {
        message($lang_admin['options_timeout_online']);
    }

    foreach ($form as $key => $input) {
        // Only update values that have changed
        if (array_key_exists('o_'.$key, $pun_config) && $pun_config['o_'.$key] != $input) {
            if ($input || is_int($input)) {
                $value = "'".$db->escape($input)."'";
            } else {
                $value = 'NULL';
            }

            $db->query('UPDATE `'.$db->prefix.'config` SET `conf_value`='.$value.' WHERE `conf_name`="o_'.$db->escape($key).'"') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
        }
    }

    // Regenerate the config cache
    include_once PUN_ROOT.'include/cache.php';
    generate_config_cache();
    generate_quickjump_cache();
    generate_wap_quickjump_cache();

    redirect('admin_options.php', $lang_admin['Updated'].' '.$lang_admin['Redirect']);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Options';
$form_name = 'update_options';
require_once PUN_ROOT.'header.php';

generate_admin_menu('options');

echo '<div class="blockform">
<h2><span>'.$lang_admin['options'].'</span></h2>
<div class="box">
<form method="post" action="admin_options.php?">
<p class="submittop"><input type="submit" name="save" value="'.$lang_admin['Upd'].'" /></p>
<div class="inform">
<input type="hidden" name="form_sent" value="1" />
<fieldset>
<legend>'.$lang_admin['options_osn'].'</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">'.$lang_admin['options_title'].'</th>
<td>
<input type="text" name="form[board_title]" size="50" maxlength="255" value="'.pun_htmlspecialchars($pun_config['o_board_title']).'" />
<span>'.$lang_admin['options_title_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_about'].'</th>
<td>
<input type="text" name="form[board_desc]" size="50" maxlength="255" value="'.pun_htmlspecialchars($pun_config['o_board_desc']).'" />
<span>'.$lang_admin['options_full_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_url'].'</th>
<td>
<input type="text" name="form[base_url]" size="50" maxlength="100" value="'.$pun_config['o_base_url'].'" />
<span>'.$lang_admin['options_url_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_timezone'].'</th>
<td>';
?>
<select name="form[server_timezone]">
    <option value="-12"<?php if (-12 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-12</option>
    <option value="-11"<?php if (-11 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-11</option>
    <option value="-10"<?php if (-10 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-10</option>
    <option value="-9.5"<?php if (-9.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-09.5
    </option>
    <option value="-9"<?php if (-9 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-09</option>
    <option value="-8.5"<?php if (-8.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-08.5
    </option>
    <option value="-8"<?php if (-8 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-08 PST</option>
    <option value="-7"<?php if (-7 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-07 MST</option>
    <option value="-6"<?php if (-6 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-06 CST</option>
    <option value="-5"<?php if (-5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-05 EST</option>
    <option value="-4"<?php if (-4 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-04 AST</option>
    <option value="-3.5"<?php if (-3.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-03.5
    </option>
    <option value="-3"<?php if (-3 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-03 ADT</option>
    <option value="-2"<?php if (-2 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-02</option>
    <option value="-1"<?php if (-1 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>-01</option>
    <option value="0"<?php if (0 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>00 GMT</option>
    <option value="1"<?php if (1 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+01 CET</option>
    <option value="2"<?php if (2 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+02</option>
    <option value="3"<?php if (3 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+03</option>
    <option value="3.5"<?php if (3.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+03.5</option>
    <option value="4"<?php if (4 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+04</option>
    <option value="4.5"<?php if (4.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+04.5</option>
    <option value="5"<?php if (5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+05</option>
    <option value="5.5"<?php if (5.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+05.5</option>
    <option value="6"<?php if (6 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+06</option>
    <option value="6.5"<?php if (6.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+06.5</option>
    <option value="7"<?php if (7 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+07</option>
    <option value="8"<?php if (8 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+08</option>
    <option value="9"<?php if (9 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+09</option>
    <option value="9.5"<?php if (9.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+09.5</option>
    <option value="10"<?php if (10 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+10</option>
    <option value="10.5"<?php if (10.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+10.5
    </option>
    <option value="11"<?php if (11 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+11</option>
    <option value="11.5"<?php if (11.5 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+11.5
    </option>
    <option value="12"<?php if (12 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+12</option>
    <option value="13"<?php if (13 == $pun_config['o_server_timezone']) {
    echo ' selected="selected"';
} ?>>+13</option>
</select>
<?php

echo '<span>'.$lang_admin['options_timezone_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_lang'].'</th>
<td>
<select name="form[default_lang]">';

$languages = array();
$d = dir(PUN_ROOT.'lang');
while (false !== ($entry = $d->read())) {
    if ('.' != $entry[0] && is_dir(PUN_ROOT.'lang/'.$entry) && file_exists(PUN_ROOT.'lang/'.$entry.'/common.php')) {
        $languages[] = $entry;
    }
}
$d->close();

@natsort($languages);

foreach ($languages as $temp) {
    if ($pun_config['o_default_lang'] == $temp) {
        echo '<option value="'.$temp.'" selected="selected">'.$temp.'</option>';
    } else {
        echo '<option value="'.$temp.'">'.$temp.'</option>';
    }
}

echo '</select>
<span>'.$lang_admin['options_lang_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_style'].'</th>
<td>
<select name="form[default_style]">';

$styles = array();
$d = dir(PUN_ROOT.'style');
while (false !== ($entry = $d->read())) {
    if ('.css' == substr($entry, strlen($entry) - 4)) {
        $styles[] = substr($entry, 0, strlen($entry) - 4);
    }
}
$d->close();

@natsort($styles);

foreach ($styles as $temp) {
    if ($pun_config['o_default_style'] == $temp) {
        echo '<option value="'.$temp.'" selected="selected">'.str_replace('_', ' ', $temp).'</option>';
    } else {
        echo '<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>';
    }
}

echo '</select>
<span>'.$lang_admin['options_style_about'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['options_style_wap'].'</th>
<td>
<select name="form[default_style_wap]">';

$stylesWap = array();
$d = dir(PUN_ROOT.'include/template/wap');
while (false !== ($entry = $d->read())) {
    if ('.' != $entry[0] && is_dir(PUN_ROOT.'include/template/wap/'.$entry)) {
        $stylesWap[] = $entry;
    }
}
$d->close();

@natsort($stylesWap);

foreach ($stylesWap as $temp) {
    if ($pun_config['o_default_style_wap'] == $temp) {
        echo '<option value="'.$temp.'" selected="selected">'.str_replace('_', ' ', $temp).'</option>';
    } else {
        echo '<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>';
    }
}

echo '</select>
<span>'.$lang_admin['options_style_about_wap'].'</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>Время и промежутки</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Формат времени</th>
<td>
<input type="text" name="form[time_format]" size="25" maxlength="25" value="'.pun_htmlspecialchars($pun_config['o_time_format']).'" />
<span>[Нынешний формат: '.date($pun_config['o_time_format']).'] Смотрите <a href="http://php.net/date">здесь</a> более подробно.</span>
</td>
</tr>
<tr>
<th scope="row">Формат даты</th>
<td>
<input type="text" name="form[date_format]" size="25" maxlength="25" value="'.pun_htmlspecialchars($pun_config['o_date_format']).'" />
<span>[Нынешний формат: '.date($pun_config['o_date_format']).'] Смотрите <a href="http://php.net/date">здесь</a> более подробно.</span>
</td>
</tr>
<tr>
<th scope="row">Промежуток визита</th>
<td>
<input type="text" name="form[timeout_visit]" size="5" maxlength="5" value="'.$pun_config['o_timeout_visit'].'" />
<span>Количество секунд, которое пользователь должен ждать пока данные о его/ее последнем визите обновятся (главным образом касается отображения новых сообщений).</span>
</td>
</tr>
<tr>
<th scope="row">Промежуток онлайн</th>
<td>
<input type="text" name="form[timeout_online]" size="5" maxlength="5" value="'.$pun_config['o_timeout_online'].'" />
<span>Количество секунд, которое пользователь должен ждать пока он не будет удален из списка онлайн пользователей.</span>
</td>
</tr>
<tr>
<th scope="row">Время переадресации</th>
<td>
<input type="text" name="form[redirect_delay]" size="3" maxlength="3" value="'.$pun_config['o_redirect_delay'].'" />
<span>Количество секунд ожидания переадресации. Если задать 0, страница переадресации не показывается (не рекомендуется).</span>
</td>
</tr>
<tr>
<th scope="row">Время склейки</th>
<td>
<input type="text" name="form[timeout_merge]" size="5" maxlength="5" value="'.$pun_config['o_timeout_merge'].'" />
<span>Количество секунд, в течение которого будут склеиваться идущие подряд несколько сообщений одного пользователем.</span>
</td>
</tr>
<tr>
<th scope="row">Промежуток между регистраций с одного IP</th>
<td>
<input type="text" name="form[timeout_reg]" size="5" maxlength="5" value="'.$pun_config['o_timeout_reg'].'" />
<span>Количество секунд, в течении которых запрещена регистрация с одного IP.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>

<div class="inform">
<fieldset>
<legend>Антифлуд</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Антифлуд</th>
<td>
<input type="radio" name="form[antiflood]" value="1"';
if ($pun_config['o_antiflood']) {
    echo ' checked="checked"';
}
echo '/> <strong>Да</strong>&#160; &#160;<input type="radio" name="form[antiflood]" value="0"';
if (!$pun_config['o_antiflood']) {
    echo ' checked="checked"';
}

echo '/> <strong>Нет</strong>
<span>Включить / Отключить антифлуд</span>
</td>
</tr>
<tr>
<th scope="row">Первое ограничение</th>
<td>
<input type="text" name="form[antiflood_a]" size="5" maxlength="5" value="'.$pun_config['o_antiflood_a'].'" />
<span>Минимальное количество секунд, которое должно пройти от захода на страницу и до отправки сообщения.</span>
</td>
</tr>
<tr>
<th scope="row">Второе ограничение</th>
<td>
<input type="text" name="form[antiflood_b]" size="5" maxlength="5" value="'.$pun_config['o_antiflood_b'].'" />
<span>Максимальное количество секунд, которое должно пройти от захода на страницу и до отправки сообщения.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>

<div class="inform">
<fieldset>
<legend>Отображение</legend>
<div class="infldset">';
?>
<table class="aligntop" cellspacing="0">
    <tr>
        <th scope="row">Отображение модераторов</th>
        <td>
            <input type="radio" name="form[show_moderators]" value="1"<?php if (1 == $pun_config['o_show_moderators']) {
    echo ' checked="checked"';
} ?> /> <strong>Да</strong>&#160; &#160;<input type="radio" name="form[show_moderators]"
                                                           value="0"<?php if (!$pun_config['o_show_moderators']) {
    echo ' checked="checked"';
} ?> /> <strong>Нет</strong>
            <span>Показать модераторов форумов. Только WEB версия.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Информация о пользователе в сообщениях</th>
        <td>
            <input type="radio" name="form[show_user_info]"
                   value="1"<?php if (1 == $pun_config['o_show_user_info']) {
    echo ' checked="checked"';
} ?> />
            <strong>Да</strong>&#160; &#160;<input type="radio" name="form[show_user_info]"
                                                   value="0"<?php if (!$pun_config['o_show_user_info']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Показать информацию под именем пользователя в теме. Информация включает расположение, дату регистрации,количество постов и контактные ссылки (e-mail и URL).</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Карма пользователя</th>
        <td>
            <input type="radio" name="form[show_post_karma]"
                   value="1"<?php if (1 == $pun_config['o_show_post_karma']) {
    echo ' checked="checked"';
} ?> />
            <strong>Да</strong>&#160; &#160;<input type="radio" name="form[show_post_karma]"
                                                   value="0"<?php if (!$pun_config['o_show_post_karma']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Показать карму пользователя в теме, профиле и списке пользователей.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Количество сообщений пользователя</th>
        <td>
            <input type="radio" name="form[show_post_count]"
                   value="1"<?php if (1 == $pun_config['o_show_post_count']) {
    echo ' checked="checked"';
} ?> />
            <strong>Да</strong>&#160; &#160;<input type="radio" name="form[show_post_count]"
                                                   value="0"<?php if (!$pun_config['o_show_post_count']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Показать количество сообщений пользователя в теме, профиле и списке пользователей.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Смайлы</th>
        <td>
            <input type="radio" name="form[smilies]"
                   value="1"<?php if (1 == $pun_config['o_smilies']) {
    echo ' checked="checked"';
} ?> /> <strong>Да</strong>&#160;
            &#160;<input type="radio" name="form[smilies]"
                         value="0"<?php if (!$pun_config['o_smilies']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Заменять смайлы маленькими иконками.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Смайлы в подписях</th>
        <td>
            <input type="radio" name="form[smilies_sig]"
                   value="1"<?php if (1 == $pun_config['o_smilies_sig']) {
    echo ' checked="checked"';
} ?> />
            <strong>Да</strong>&#160; &#160;<input type="radio" name="form[smilies_sig]"
                                                   value="0"<?php if (!$pun_config['o_smilies_sig']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Заменять смайлы в подписях маленькими иконками.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Активные ссылки</th>
        <td>
            <input type="radio" name="form[make_links]"
                   value="1"<?php if (1 == $pun_config['o_make_links']) {
    echo ' checked="checked"';
} ?> />
            <strong>Да</strong>&#160; &#160;<input type="radio" name="form[make_links]"
                                                   value="0"<?php if (!$pun_config['o_make_links']) {
    echo ' checked="checked"';
} ?> />
            <strong>Нет</strong>
            <span>Если включено, PunBB автоматически определяет любые URL в сообщениях и делает из нее активную гиперссылку</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Обзор темы</th>
        <td>
            <input type="text" name="form[topic_review]" size="3" maxlength="3"
                   value="<?php echo $pun_config['o_topic_review']; ?>"/>
            <span>Максимальное число сообщений показываемое при ответе (новейшее - первое). 0 для отключения.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Тем на странице по умолчанию</th>
        <td>
            <input type="text" name="form[disp_topics_default]" size="3" maxlength="3"
                   value="<?php echo $pun_config['o_disp_topics_default']; ?>"/>
            <span>Количество тем по умолчанию на страницу форума. Пользователи могут настраивать по своему.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Сообщений на страницу по умолчанию</th>
        <td>
            <input type="text" name="form[disp_posts_default]" size="3" maxlength="3"
                   value="<?php echo $pun_config['o_disp_posts_default']; ?>"/>
            <span>Количество сообщений по умолчанию на страницу темы. Пользователи могут настраивать по своему.</span>
        </td>
    </tr>
    <tr>
        <th scope="row">Размер отступа</th>
        <td>
            <input type="text" name="form[indent_num_spaces]" size="3" maxlength="3"
                   value="<?php echo $pun_config['o_indent_num_spaces']; ?>"/>
            <span>Если задать 8, обычный отступ будет использоваться при отображении текста окруженного тэгами [ code][ /code]. Иначе эти много пробелов будут использоваться для отступа текста.</span>
        </td>
    </tr>
</table>
</div>
</fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Свойства</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Голосования</th>
                    <td>
                        <input type="radio" name="form[poll]" value="1"<?php if (1 == $pun_config['poll_enabled']) {
    echo ' checked="checked"';
} ?> /> <strong>Да</strong>&#160; &#160;<input type="radio" name="form[poll]"
                                                                       value="0"<?php if (!$pun_config['poll_enabled']) {
    echo ' checked="checked"';
} ?> /> <strong>Нет</strong>
                        <span>Включить / Отключить голосования</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Быстрый ответ</th>
                    <td>
                        <input type="radio" name="form[quickpost]"
                               value="1"<?php if (1 == $pun_config['o_quickpost']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[quickpost]"
                                                               value="0"<?php if (!$pun_config['o_quickpost']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Если включено, PunBB добавит форму быстрого ответа внизу тем. Это позволит пользователям отвечать прямо в теме.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Пользователи онлайн</th>
                    <td>
                        <input type="radio" name="form[users_online]"
                               value="1"<?php if (1 == $pun_config['o_users_online']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[users_online]"
                                                               value="0"<?php if (!$pun_config['o_users_online']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Показать информацию на главной странице о присутствующих в данное время на форуме гостях и зарегистрированных пользователях.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><a name="censoring">Цензура слов</a></th>
                    <td>
                        <input type="radio" name="form[censoring]"
                               value="1"<?php if (1 == $pun_config['o_censoring']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[censoring]"
                                                               value="0"<?php if (!$pun_config['o_censoring']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Включить цензуру определенных слов на форуме. Смотрите <a href="admin_censoring.php">Цензура</a> для более подробной информации.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><a name="ranks">Ранги пользователей</a></th>
                    <td>
                        <input type="radio" name="form[ranks]"
                               value="1"<?php if (1 == $pun_config['o_ranks']) {
    echo ' checked="checked"';
} ?> /> <strong>Да</strong>&#160;
                        &#160;<input type="radio" name="form[ranks]"
                                     value="0"<?php if (!$pun_config['o_ranks']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Включить использование ранга пользователей. Смотрите <a href="admin_ranks.php">Ранги</a> для более подробной информации.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Пользователь отвечал ранее</th>
                    <td>
                        <input type="radio" name="form[show_dot]"
                               value="1"<?php if (1 == $pun_config['o_show_dot']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[show_dot]"
                                                               value="0"<?php if (!$pun_config['o_show_dot']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Это свойство ставит точку в начале тем в viewforum.php в случае если вошедший пользователь отвечал в этой теме ранее. Отключите если сервер испытывает большую нагрузку</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Быстрый переход</th>
                    <td>
                        <input type="radio" name="form[quickjump]"
                               value="1"<?php if (1 == $pun_config['o_quickjump']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[quickjump]"
                                                               value="0"<?php if (!$pun_config['o_quickjump']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Включить быстрый переход (переход к форуму) выпадающий список.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Поиск по всем форумам</th>
                    <td>
                        <input type="radio" name="form[search_all_forums]"
                               value="1"<?php if (1 == $pun_config['o_search_all_forums']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[search_all_forums]"
                                                               value="0"<?php if (!$pun_config['o_search_all_forums']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Если отключено, поиск возможен только в одном форуме одновременно. Отключите, если загрузка сервера черезмерно перегружена поиском.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Дополнительные пункты меню</th>
                    <td>
                        <textarea name="form[additional_navlinks]" rows="3"
                                  cols="55"><?php echo pun_htmlspecialchars($pun_config['o_additional_navlinks']); ?></textarea>
                        <span>Вводом HTML гиперссылок в эту форму можно добавить к навигационному меню вначале всех страницы любое количество пунктов. Формат добавления ссылок X = &lt;a href="URL"&gt;ССЫЛКА&lt;/a&gt; где X - позиция куда ссылка будет вставлена (т.е. 0 - вставить в начало и 2 - вставить после "Пользователи"). Разделитель - перенос строки.</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Отчеты</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Тип отчета</th>
                    <td>
                        <input type="radio" name="form[report_method]"
                               value="0"<?php if (!$pun_config['o_report_method']) {
    echo ' checked="checked"';
} ?> />&#160;Внутренний&#160;
                        &#160;<input type="radio" name="form[report_method]"
                                     value="1"<?php if (1 == $pun_config['o_report_method']) {
    echo ' checked="checked"';
} ?> />
                        E-mail&#160; &#160;<input type="radio" name="form[report_method]"
                                                  value="2"<?php if ('2' == $pun_config['o_report_method']) {
    echo ' checked="checked"';
} ?> />
                        Оба
                        <span>Выберите метод получения отчетов. Вы можете выбрать сообщать ли вам о теме/сообщении используя внутреннюю систему отчетов, посылать ли e-mail адресам в списке рассылки (смотрите далее) или оба варианта.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Сообщать о новых регистрациях</th>
                    <td>
                        <input type="radio" name="form[regs_report]"
                               value="1"<?php if (1 == $pun_config['o_regs_report']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[regs_report]"
                                                               value="0"<?php if (!$pun_config['o_regs_report']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Если включено, PunBB будет уведомлять пользователей из списка рассылки (смотрите ниже) когда новый пользователь регистрируется на форуме.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Список рассылки</th>
                    <td>
                        <textarea name="form[mailing_list]" rows="5"
                                  cols="55"><?php echo pun_htmlspecialchars($pun_config['o_mailing_list']); ?></textarea>
                        <span>Запятые разделяют список подписчиков. Люди из этого списка - получатели отчетов.</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Аватары</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Использовать аватары</th>
                    <td>
                        <input type="radio" name="form[avatars]"
                               value="1"<?php if (1 == $pun_config['o_avatars']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[avatars]"
                                                               value="0"<?php if (!$pun_config['o_avatars']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Когда включено, пользователи могут загрузить аватар который будет показываться под их названием/рангом.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Директория загрузок</th>
                    <td>
                        <input type="text" name="form[avatars_dir]" size="35" maxlength="50"
                               value="<?php echo pun_htmlspecialchars($pun_config['o_avatars_dir']); ?>"/>
                        <span>Директория загрузок для аватар (относительно корневой директории PunBB). PHP должен иметь разрешения на запись в эту директорию.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Максимальная ширина</th>
                    <td>
                        <input type="text" name="form[avatars_width]" size="5" maxlength="5"
                               value="<?php echo $pun_config['o_avatars_width']; ?>"/>
                        <span>Максимально допустимая ширина аватар в пикселях (60 рекомендуется).</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Максимальная высота</th>
                    <td>
                        <input type="text" name="form[avatars_height]" size="5" maxlength="5"
                               value="<?php echo $pun_config['o_avatars_height']; ?>"/>
                        <span>Максимально допустимая высота аватар в пикселях (60 рекомендуется).</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Максимальный размер</th>
                    <td>
                        <input type="text" name="form[avatars_size]" size="6" maxlength="6"
                               value="<?php echo $pun_config['o_avatars_size']; ?>"/>
                        <span>Максимально допустимый размер аватар в байтах (10240 рекомендуется).</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>E-mail</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Админский e-mail</th>
                    <td>
                        <input type="text" name="form[admin_email]" size="50" maxlength="50"
                               value="<?php echo $pun_config['o_admin_email']; ?>"/>
                        <span>Адрес e-mail администратора форума.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Вебмастерский e-mail</th>
                    <td>
                        <input type="text" name="form[webmaster_email]" size="50" maxlength="50"
                               value="<?php echo $pun_config['o_webmaster_email']; ?>"/>
                        <span>Этот адрес с которого приходят все e-mails посланные от лица форума.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Подписки на темы</th>
                    <td>
                        <input type="radio" name="form[subscriptions]"
                               value="1"<?php if (1 == $pun_config['o_subscriptions']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[subscriptions]"
                                                               value="0"<?php if (!$pun_config['o_subscriptions']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Позволить пользователям подписываться на темы (получать e-mail когда кто-то ответил).</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Адрес SMTP сервера</th>
                    <td>
                        <input type="text" name="form[smtp_host]" size="30" maxlength="100"
                               value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_host']); ?>"/>
                        <span>Адрес внешнего SMTP сервера для отправки e-mail писем через него. Вы можете указать любой порт если SMTP сервер не использует по умолчанию порт 25 (прим.: mail.myhost.com:3580). Оставьте пустым чтобы использовать локальную почтовую программу.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Имя пользователя SMTP</th>
                    <td>
                        <input type="text" name="form[smtp_user]" size="25" maxlength="50"
                               value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_user']); ?>"/>
                        <span>Имя пользователя для SMTP сервера. Введите имя пользователя только если SMTP сервер требует его (большинство серверов <strong>не
                            требуют</strong> аутентификации).</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Пароль SMTP</th>
                    <td>
                        <input type="text" name="form[smtp_pass]" size="25" maxlength="50"
                               value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_pass']); ?>"/>
                        <span>Пароль для SMTP сервера. Введите пароль только если SMTP сервер требует его (большинство серверов <strong>не
                            требуют</strong> аутентификации).</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Регистрация</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Позволить новые регистрации</th>
                    <td>
                        <input type="radio" name="form[regs_allow]"
                               value="1"<?php if (1 == $pun_config['o_regs_allow']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[regs_allow]"
                                                               value="0"<?php if (!$pun_config['o_regs_allow']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Может ли форум принимать новые регистрации. Отключать только в экстренных ситуациях.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">E-mail проверка регистраций</th>
                    <td>
                        <input type="radio" name="form[regs_verify]"
                               value="1"<?php if (1 == $pun_config['o_regs_verify']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[regs_verify]"
                                                               value="0"<?php if (!$pun_config['o_regs_verify']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Когда включено, пользователям посылается случайный пароль при регистрации. Они могут войти и изменить его в своем профиле на более удобный. Кроме того пользователь вынужден проверять новый e-mail адрес введенный при регистрации. Это эффективный способ избежать авто-регистраций и проверить корректность e-mail адреса всех пользователей в их профилях.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Проверка картинкой регистрации и гостей</th>
                    <td>
                        <input type="radio" name="form[regs_verify_image]"
                               value="1"<?php if (1 == $pun_config['o_regs_verify_image']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[regs_verify_image]"
                                                               value="0"<?php if (!$pun_config['o_regs_verify_image']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Когда включено, пользователи должны ввести текст с картинки для подтверждения регистрации и в форме ответа. Это лучший способ избавиться от авто-регистраций ботов и не заставлять каждого пользователя подтверждать подлинность через e-mail, экономя его время.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Использовать правила форума</th>
                    <td>
                        <input type="radio" name="form[rules]"
                               value="1"<?php if (1 == $pun_config['o_rules']) {
    echo ' checked="checked"';
} ?> />&#160;<strong>Да</strong>&#160;
                        &#160;<input type="radio" name="form[rules]"
                                     value="0"<?php if (!$pun_config['o_rules']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Когда включено, пользователи должны согласиться выполнять правила при регистрации (введите текст ниже). Правила всегда доступны по ссылке в навигационной таблице вначале каждой страницы.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Правила</th>
                    <td>
                        <textarea name="form[rules_message]" rows="10"
                                  cols="55"><?php echo pun_htmlspecialchars($pun_config['o_rules_message']); ?></textarea>
                        <span>Здесь вы можете ввести любые правила или другую информацию с которой пользователи должны ознакомиться и согласиться при регистрации. Если вы включили правила выше, введите что либо здесь, иначе они будут отключены. Этот текст не пре-обрабатывается как обычные сообщения и может содержать HTML.</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Объявление</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row">Показывать объявление</th>
                    <td>
                        <input type="radio" name="form[announcement]"
                               value="1"<?php if (1 == $pun_config['o_announcement']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Да</strong>&#160; &#160;<input type="radio" name="form[announcement]"
                                                               value="0"<?php if (!$pun_config['o_announcement']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Включите для отображения перед сообщениями в форумах.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Текст объявления</th>
                    <td>
                        <textarea name="form[announcement_message]" rows="5"
                                  cols="55"><?php echo pun_htmlspecialchars($pun_config['o_announcement_message']); ?></textarea>
                        <span>Этот текст не пре-обрабатывается как обычные сообщения и может содержать HTML.</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<div class="inform">
    <fieldset>
        <legend>Ремонт</legend>
        <div class="infldset">
            <table class="aligntop" cellspacing="0">
                <tr>
                    <th scope="row"><a name="maintenance">Режим ремонта</a></th>
                    <td>
                        <input type="radio" name="form[maintenance]"
                               value="1"<?php if (1 == $pun_config['o_maintenance']) {
    echo ' checked="checked"';
} ?> />&#160;<strong>Да</strong>&#160;
                        &#160;<input type="radio" name="form[maintenance]"
                                     value="0"<?php if (!$pun_config['o_maintenance']) {
    echo ' checked="checked"';
} ?> />
                        <strong>Нет</strong>
                        <span>Когда включено, форумы доступны только администраторам. Используется когда форумы требуется временно отключить для ремонта. ВНИМАНИЕ! Не выходите когда форумы в режиме ремонта. Вы не сможете войти снова.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Ремонтное сообщение</th>
                    <td>
                        <textarea name="form[maintenance_message]" rows="5"
                                  cols="55"><?php echo pun_htmlspecialchars($pun_config['o_maintenance_message']); ?></textarea>
                        <span>Сообщение показывается пользователям форумов в режиме ремонта. Если оставить пустым - используется сообщение по умолчанию. Этот текст не пре-обрабатывается как обычные сообщения и может содержать XHTML.</span>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
</div>
<p class="submitend"><input type="submit" name="save" value="Сохранить изменения"/></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>
<?php
require_once PUN_ROOT.'footer.php';
