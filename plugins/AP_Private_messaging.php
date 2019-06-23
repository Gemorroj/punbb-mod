<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.2.3 mod');

if (isset($_POST['form_sent'])) {
    $form = array_map('trim', $_POST['form']);
    $allow = array_map('trim', $_POST['allow']);
    $limit = array_map('trim', $_POST['limit']);

    foreach ($form as $key => $input) {
        // Only update values that have changed
        if ((isset($pun_config['o_'.$key])) || (null == $pun_config['o_'.$key])) {
            if ($pun_config['o_'.$key] != $input) {
                if ('' != $input || is_int($input)) {
                    $value = '\''.$db->escape($input).'\'';
                } else {
                    $value = 'NULL';
                }

                $db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
            }
        }
    }

    foreach ($allow as $id => $set) {
        $db->query('UPDATE `'.$db->prefix.'groups` SET g_pm='.$set.' WHERE g_id='.$id) or error('Unable to change permissions.', __FILE__, __LINE__, $db->error());
    }
    foreach ($limit as $id => $set) {
        $db->query('UPDATE `'.$db->prefix.'groups` SET g_pm_limit='.intval($set).' WHERE g_id='.$id) or error('Unable to change permissions.', __FILE__, __LINE__, $db->error());
    }
    // Regenerate the config cache
    require_once PUN_ROOT.'include/cache.php';
    generate_config_cache();

    redirect('admin_loader.php?plugin=AP_Private_messaging.php', 'Опции обновлены. Перенаправление &#x2026;');
} else {
    // Display the admin navigation menu
    generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Личные сообщения - v<?php echo PLUGIN_VERSION; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин используется для контроля настроек и разрешений мода личных сообщений (private messaging
                mod).</p>
        </div>
    </div>
</div>

<div class="blockform">
    <h2 class="block2"><span>Опции</span></h2>

    <div class="box">
        <form method="post" action="admin_loader.php?plugin=AP_Private_messaging.php">
            <div class="inform">
                <input type="hidden" name="form_sent" value="1"/>
                <fieldset>
                    <legend>Настройки</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Включить личные сообщения</th>
                                <td>
                                    <input type="radio" name="form[pms_enabled]"
                                           value="1"<?php if (1 == $pun_config['o_pms_enabled']) {
        echo ' checked="checked"';
    } ?> />
                                    <strong>Да</strong>&#160; &#160;<input type="radio" name="form[pms_enabled]"
                                                                           value="0"<?php if (0 == $pun_config['o_pms_enabled']) {
        echo ' checked="checked"';
    } ?> />
                                    <strong>Нет</strong>
                                    <span>Если "нет" - все функции личных сообщений будут отключены.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Сообщений на странице</th>
                                <td>
                                    <input type="text" name="form[pms_mess_per_page]" size="50" maxlength="255"
                                           value="<?php echo $pun_config['o_pms_mess_per_page']; ?>"/>
                                    <span>Число сообщений которые будут отображаться на странице в разделе личных сообщений</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend>Разрешения</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <?php
// g_id>'.PUN_ADMIN.' AND
                            $result = $db->query('SELECT g_id, g_title, g_pm, g_pm_limit FROM `'.$db->prefix.'groups` WHERE g_id != 3 ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
    while ($cur_group = $db->fetch_assoc($result)) {
        ?>
                                <tr>
                                    <th scope="row"><?php echo pun_htmlspecialchars($cur_group['g_title']); ?></th>
                                    <td>
                                        <input type="radio" name="allow[<?php echo $cur_group['g_id']; ?>]"
                                               value="1"<?php if (1 == $cur_group['g_pm']) {
            echo ' checked="checked"';
        } ?> />
                                        <strong>Да</strong>&#160; &#160;<input type="radio"
                                                                               name="allow[<?php echo $cur_group['g_id']; ?>]"
                                                                               value="0"<?php if (0 == $cur_group['g_pm']) {
            echo ' checked="checked"';
        } ?> />
                                        <strong>Нет</strong>
                                        <span>Разрешить этой группе использовать личные сообщения.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"></th>
                                    <td>
                                        Лимит сообщений: <input type="text"
                                                                name="limit[<?php echo $cur_group['g_id']; ?>]"
                                                                size="20" maxlength="10"
                                                                value="<?php echo $cur_group['g_pm_limit']; ?>"/>
                                        <span>Число сообщений в ящике каждого пользователя.</span>
                                    </td>
                                </tr>
                                <?php
    } ?>

                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="save" value="Сохранить изменения"/></p>
        </form>
    </div>
</div>

<?php
}
