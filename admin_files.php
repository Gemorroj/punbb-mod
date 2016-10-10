<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';


if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

if (isset($_POST['show_errors']) || isset($_POST['delete_orphans']) || isset($_POST['delete_thumbnails']) || isset($_POST['fix_counters'])) {
    include PUN_ROOT . 'lang/' . $pun_user['language'] . '/fileup.php';
    include PUN_ROOT . 'include/file_upload.php';
}

// If the "Show text" button was clicked
if (isset($_POST['save'])) {
//confirm_referrer('admin_files.php');

    $form = array_map('trim', $_POST['form']);

// Error checking
    if (!$form['upload_path']) {
        message('You must enter an upload path.', true);
    }

    if (realpath($form['upload_path']) === false) {
        message('Upload path you entered is not a valid directory.', true);
    }

    if (!$form['thumb_path']) {
        message('You must enter a thumbnail path.', true);
    }

    if (!is_writable($form['upload_path'])) {
        message('Upload path is not writable.', true);
    }

    if (!is_dir($form['upload_path'])) {
        message('Upload path you entered is not a valid directory.', true);
    }

    if (realpath($form['thumb_path']) === false) {
        message('Thumbnail path you entered is not a valid directory.', true);
    }

    if (!is_writable($form['thumb_path'])) {
        message('Thumbnail path is not writable.', true);
    }

    if (!is_dir($form['thumb_path'])) {
        message('Thumbnail path you entered is not a valid directory.', true);
    }

    $form['max_width'] = intval($form['max_width']);
    if ($form['max_width'] < 1) {
        message('Invalid maximum image width.', true);
    }

    $form['max_height'] = intval($form['max_height']);
    if ($form['max_height'] < 1) {
        message('Invalid maximum image height.', true);
    }

    $form['max_size'] = intval($form['max_size']);
    if ($form['max_size'] < 1) {
        message('Invalid maximum image size.', true);
    }

    $form['thumb_width'] = intval($form['thumb_width']);
    if ($form['thumb_width'] < 1) {
        message('Invalid thumbnail width.', true);
    }

    $form['thumb_height'] = intval($form['thumb_height']);
    if ($form['thumb_height'] < 1) {
        message('Invalid thumbnail height.', true);
    }

    $form['preview_width'] = intval($form['preview_width']);
    if ($form['preview_width'] < 1) {
        message('Invalid preview width.', true);
    }

    $form['preview_height'] = intval($form['preview_height']);
    if ($form['preview_height'] < 1) {
        message('Invalid preview height.', true);
    }

    $form['first_only'] = (isset($form['first_only']) && $form['first_only'] == 1) ? 1 : 0;

    $form['max_post_files'] = intval($form['max_post_files']);
    if ($form['max_post_files'] < 1) {
        message('Invalid maximum files per post.', true);
    }

    $form['allowed_ext'] = strtolower($form['allowed_ext']);

    while (list($key, $input) = @each($form)) {
// Only update values that have changed
        if (array_key_exists('file_' . $key, $pun_config) && $pun_config['file_' . $key] != $input) {
            if ($input || is_int($input)) {
                $value = '\'' . $db->escape($input) . '\'';
            } else {
                $value = 'NULL';
            }

            $db->query('UPDATE ' . $db->prefix . 'config SET conf_value=' . $value . ' WHERE conf_name=\'file_' . $db->escape($key) . '\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
        }
    }

// Regenerate the config cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_config_cache();

    redirect('admin_files.php', 'Options updated. Redirecting &#x2026;');
} else // If not, we show the "Show text" form
{
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Files';
    $focus_element = array('files', 'form[upload_path]');
    require_once PUN_ROOT . 'header.php';

// Display the admin navigation menu
    generate_admin_menu('files');


    if (isset($_POST['show_errors'])) {
//confirm_referrer('admin_files.php');

        $log = show_problems();

        echo '<div id="imageupload" class="blockform">
<h2><span>Отчет об ошибках</span></h2>
<div class="box">
<div class="inform">
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<td>';
        echo implode('<br/>', $log);
        echo '</td>
</tr>
</table>
</div>
</div>
</div>
</div>
<br />';
    }

    if (isset($_POST['delete_orphans'])) {
//confirm_referrer('admin_files.php');

        $log = delete_orphans();

        echo '<div id="imageupload" class="blockform">
<h2><span>Отчет о "сиротах"</span></h2>
<div class="box">
<div class="inform">
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<td>';
        echo implode('<br/>', $log);
        echo '</td>
</tr>
</table>
</div>
</div>
</div>
</div>
<br />';
    }

    if (isset($_POST['delete_thumbnails'])) {
//confirm_referrer('admin_files.php');

        $log = delete_all_thumbnails();

        echo '<div id="imageupload" class="blockform">
<h2><span>Отчет об очистке кеша</span></h2>
<div class="box">
<div class="inform">
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<td>';
        echo implode('<br/>', $log);
        echo '</td>
</tr>
</table>
</div>
</div>
</div>
</div>
<br />';
    }

    if (isset($_POST['fix_counters'])) {
//confirm_referrer('admin_files.php');

        $log = fix_user_counters();

        echo '<div id="imageupload" class="blockform">
<h2><span>Отчет об исправлении счетчиков пользователей</span></h2>
<div class="box">
<div class="inform">
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<td>';
        echo implode('<br/>', $log);
        echo '</td>
</tr>
</table>
</div>
</div>
</div>
</div>
<br />';

    }
    ?>
<div id="imageupload" class="blockform">
    <h2><span>Файловые параметры</span></h2>

    <div class="box">
        <form id="files" method="post" action="admin_files.php">
            <p class="submittop"><input type="submit" name="save" value=" Сохранить изменения "/></p>

            <div class="inform">
                <input type="hidden" name="form_sent" value="1"/>
                <fieldset>
                    <legend>Общие параметры</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Папка загрузки</th>
                                <td>
                                    <input type="text" name="form[upload_path]" size="50" maxlength="255"
                                           value="<?php echo $pun_config['file_upload_path']; ?>"/>
                                    <span>Относительный путь до папки куда будут складываться файлы. На сервере необходимо установить права на запись, но желательно защитить от прямого считывания (из соображений безопасности).</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Папка превьюшек</th>
                                <td>
                                    <input type="text" name="form[thumb_path]" size="50" maxlength="255"
                                           value="<?php echo $pun_config['file_thumb_path']; ?>"/>
                                    <span>Относительный путь до папки где будут создаваться уменьшенные копии изображений. Необходимы права на запись, эта папка должна быть доступна для чтения по http.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Разрешенные расширения</th>
                                <td>
                                    <input type="text" name="form[allowed_ext]" size="50" maxlength="255"
                                           value="<?php echo $pun_config['file_allowed_ext']; ?>"/>
                                    <span>Список всех расширений файлов, разрешенных для загрузки.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Макс. размер</th>
                                <td>
                                    <input type="text" name="form[max_size]" size="6" maxlength="10"
                                           value="<?php echo $pun_config['file_max_size']; ?>"/>
                                    <span>Максимальный размер загружаемого файла в байтах.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend>Параметры картинок</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Расширения графики</th>
                                <td>
                                    <input type="text" name="form[image_ext]" size="50" maxlength="255"
                                           value="<?php echo $pun_config['file_image_ext']; ?>"/>
                                    <span>Список расширений графических файлов. Он должен быть подмножеством <strong>Разрешенных
                                        разрешений</strong>.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Макс. размер</th>
                                <td>
                                    <input type="text" name="form[max_width]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_max_width']; ?>"/> x
                                    <input type="text" name="form[max_height]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_max_height']; ?>"/>
                                    <span>Максимальная ширина и высота. Изображениям большего размера будет отказано в загрузке, но это не скажется на ранее загруженных картинках.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Размер ноготков</th>
                                <td>
                                    <input type="text" name="form[thumb_width]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_thumb_width']; ?>"/> x
                                    <input type="text" name="form[thumb_height]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_thumb_height']; ?>"/>
<span>Ширина и высота изображения-иконки. Изменение этого параметра не скажется на ранее созданных ноготках.<br/>
Картинки будут создаваться в <strong>Папке превьюшек</strong> непосредственно когда они понадобятся.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Размер превью</th>
                                <td>
                                    <input type="text" name="form[preview_width]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_preview_width']; ?>"/> x
                                    <input type="text" name="form[preview_height]" size="5" maxlength="5"
                                           value="<?php echo $pun_config['file_preview_height']; ?>"/>
<span>Ширина и высота изображения-превью. Изменение этого параметра не скажется на ранее созданных превьюшках.<br/>
Картинки будут создаваться в <strong>Папке превьюшек</strong> непосредственно когда они понадобятся.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend>Параметры сообщений</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Только в первом</th>
                                <td>
                                    <input type="checkbox" name="form[first_only]"
                                           value="1" <?php echo ($pun_config['file_first_only'] == 1) ? ' checked="checked"' : ''; ?> />
                                    Вложения разрешены только в начале темы.
                                    <span>Отметьте этот пункт чтобы запретить вложения в комментариях.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Доп. информация</th>
                                <td>
                                    <input type="radio" name="form[popup_info]"
                                           value="0"<?php if (!$pun_config['file_popup_info']) echo ' checked="checked"' ?> />
                                    Нет <input type="radio" name="form[popup_info]"
                                               value="1"<?php if ($pun_config['file_popup_info'] == 1) echo ' checked="checked"' ?> />
                                    Поп-ап <input type="radio" name="form[popup_info]"
                                                  value="2"<?php if ($pun_config['file_popup_info'] == '2') echo ' checked="checked"' ?> />
                                    На месте
                                    <span>Выберите метод отображения дополнительной информации. Вы можете выбрать варианты "не выводить совсем", "во всплывающем окне" или "статичная информация в сообщении".</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Кол-во вложений</th>
                                <td>
                                    <input type="text" name="form[max_post_files]" size="6" maxlength="6"
                                           value="<?php echo $pun_config['file_max_post_files']; ?>"/>
                                    <span>Максимальное количество вложений в одном сообщении.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="save" value=" Сохранить изменения "/></p>

            <div class="inform">
                <fieldset>
                    <legend>Инструменты</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Поиск ошибок</th>
                                <td>
                                    <input type="submit" name="show_errors" value="Искать ошибки"/>
                                    <span>Проверяет папки для загрузок и превьюшек на возможные ошибки. В общем случае ошибок быть не должно, но если папки создавались вручную, возможны некоторые проблемы.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Поиск "сирот"</th>
                                <td>
                                    <input type="submit" name="delete_orphans" value="Удалить лишнее"/>
<span>Удаление файлов не прикрепленных ни к одному сообщению.<br/>
Также удаляет "мертвые ссылки" на отсутствующие файлы.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Очистка превьюшек</th>
                                <td>
                                    <input type="submit" name="delete_thumbnails" value="Удалить превью"/>
<span>Удалить все имеющиеся превью и ноготки.<br/>
Уменьшенные картинки будут созданы заново по мере необходимости.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Накрутка счетчиков</th>
                                <td>
                                    <input type="submit" name="fix_counters" value="Сосчитать"/>
                                    <span>Сосчитать количество файлов и обновить счетчики в профилях пользователей. Это может занять много времени.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<div class="clearer"></div>
</div>
<?php

}

require_once PUN_ROOT . 'footer.php';
