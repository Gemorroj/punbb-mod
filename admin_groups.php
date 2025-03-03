<?php
// Tell header.php to use the admin template
\define('PUN_ADMIN_CONSOLE', 1);

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

require PUN_ROOT.'include/common_admin.php';

if ($pun_user['g_id'] > PUN_ADMIN) {
    \message($lang_common['No permission']);
}

// Add/edit a group (stage 1)
if (isset($_POST['add_group']) || isset($_GET['edit_group'])) {
    if (isset($_POST['add_group'])) {
        $base_group = (int) $_POST['base_group'];

        $result = $db->query('SELECT * FROM `'.$db->prefix.'groups` WHERE `g_id`='.$base_group) || \error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
        $group = $db->fetch_assoc($result);

        $mode = 'add';
    } else { // We are editing a group
        $group_id = (int) $_GET['edit_group'];
        if ($group_id < 1) {
            \message($lang_common['Bad request']);
        }

        $result = $db->query('SELECT * FROM `'.$db->prefix.'groups` WHERE `g_id`='.$group_id) || \error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
        if (!$db->num_rows($result)) {
            \message($lang_common['Bad request']);
        }

        $group = $db->fetch_assoc($result);

        $mode = 'edit';
    }

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / User groups';
    $required_fields = ['req_title' => 'Group title'];
    $focus_element = ['groups2', 'req_title'];

    require_once PUN_ROOT.'header.php';

    \generate_admin_menu('groups'); ?>
<div class="blockform">
    <h2><span>Настройки группы</span></h2>

    <div class="box">
        <form id="groups2" method="post" action="admin_groups.php" onsubmit="return process_form(this)">
            <p class="submittop"><input type="submit" name="add_edit_group" value=" Сохранить "/></p>

            <div class="inform">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
                <?php if ('edit' == $mode) { ?>
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>"/>
                <?php } ?><?php if ('add' == $mode) { ?>
                <input type="hidden" name="base_group" value="<?php echo $base_group; ?>"/>
                <?php } ?>
                <fieldset>
                    <legend>Настройка опций и разрешений группы</legend>
                    <div class="infldset">
                        <p>Следующие опции и разрешения - базовые для группы пользователей. Эти опции применяются, если
                            не действуют особенные разрешения форума.</p>
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Название группы</th>
                                <td>
                                    <input type="text" name="req_title" size="25" maxlength="50"
                                           value="<?php if ('edit' == $mode) {
                                               echo \pun_htmlspecialchars($group['g_title']);
                                           } ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Название пользователя</th>
                                <td>
                                    <input type="text" name="user_title" size="25" maxlength="50"
                                           value="<?php echo \pun_htmlspecialchars($group['g_user_title']); ?>"/>
                                    <span>Это название заменяет любой ранг пользователя. Оставьте пустым для использования названия по умолчанию или ранга.</span>
                                </td>
                            </tr>
                            <?php if (PUN_ADMIN != $group['g_id']) { ?>
                            <tr>
                                <th scope="row">Читать форумы</th>
                                <td>
                                    <input type="radio" name="read_board"
                                           value="1"<?php if (1 == $group['g_read_board']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                           name="read_board"
                                           value="0"<?php if (!$group['g_read_board']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Нет</strong>
                                    <span>Позволить пользователям этой группы смотреть форумы. Эта настройка применяется к любым аспектам форумов и следовательно не может быть отменена специальными настройками форума. Если выбрать "Нет", пользователи этой группы смогут только войти/выйти и зарегистрироваться.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Ответы в темах</th>
                                <td>
                                    <input type="radio" name="post_replies"
                                           value="1"<?php if (1 == $group['g_post_replies']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                           name="post_replies"
                                           value="2"<?php if (2 == $group['g_post_replies']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>С капчей</strong>&#160;&#160;&#160;
                                    <input type="radio" name="post_replies"
                                           value="0"<?php if (!$group['g_post_replies']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Нет</strong>
                                    <span>Разрешить пользователям группы отвечать в темах.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Новые темы</th>
                                <td>
                                    <input type="radio" name="post_topics"
                                           value="1"<?php if (1 == $group['g_post_topics']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                           name="post_topics"
                                           value="0"<?php if (!$group['g_post_topics']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Нет</strong>
                                    <span>Разрешить пользователям группы создавать новые темы.</span>
                                </td>
                            </tr>
                            <?php if (PUN_GUEST != $group['g_id']) { ?>
                                <tr>
                                    <th scope="row">Редактировать свои сообщения</th>
                                    <td>
                                        <input type="radio" name="edit_posts"
                                               value="1"<?php if (1 == $group['g_edit_posts']) {
                                                   echo ' checked="checked"';
                                               } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;<input
                                        type="radio" name="edit_posts"
                                        value="0"<?php if (!$group['g_edit_posts']) {
                                            echo ' checked="checked"';
                                        } ?>/>&#160;<strong>Нет</strong>
                                        <span>Разрешить пользователям группы редактировать свои сообщения.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Удалять свои сообщения</th>
                                    <td>
                                        <input type="radio" name="delete_posts"
                                               value="1"<?php if (1 == $group['g_delete_posts']) {
                                                   echo ' checked="checked"';
                                               } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;<input
                                        type="radio" name="delete_posts"
                                        value="0"<?php if (!$group['g_delete_posts']) {
                                            echo ' checked="checked"';
                                        } ?>/>&#160;<strong>Нет</strong>
                                        <span>Разрешить пользователям группы удалять свои сообщения.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Удалять свои темы</th>
                                    <td>
                                        <input type="radio" name="delete_topics"
                                               value="1"<?php if (1 == $group['g_delete_topics']) {
                                                   echo ' checked="checked"';
                                               } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;<input
                                        type="radio" name="delete_topics"
                                        value="0"<?php if (!$group['g_delete_topics']) {
                                            echo ' checked="checked"';
                                        } ?>/>&#160;<strong>Нет</strong>
                                        <span>Разрешить пользователям группы удалять свои темы (включая любые ответы).</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Задать название пользователя</th>
                                    <td>
                                        <input type="radio" name="set_title"
                                               value="1"<?php if (1 == $group['g_set_title']) {
                                                   echo ' checked="checked"';
                                               } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;<input
                                        type="radio" name="set_title"
                                        value="0"<?php if (!$group['g_set_title']) {
                                            echo ' checked="checked"';
                                        } ?>/>&#160;<strong>Нет</strong>
                                        <span>Разрешить пользователям группы задать свое название пользователя.</span>
                                    </td>
                                </tr>
                                <?php } ?>
                            <tr>
                                <th scope="row">Использовать поиск</th>
                                <td>
                                    <input type="radio" name="search"
                                           value="1"<?php if (1 == $group['g_search']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                            name="search"
                                            value="0"<?php if (!$group['g_search']) {
                                                echo ' checked="checked"';
                                            } ?>/>&#160;<strong>Нет</strong>
                                    <span>Разрешить пользователям группы использовать поиск.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Поиск по списку пользователей</th>
                                <td>
                                    <input type="radio" name="search_users"
                                           value="1"<?php if (1 == $group['g_search_users']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                            name="search_users"
                                            value="0"<?php if (!$group['g_search_users']) {
                                                echo ' checked="checked"';
                                            } ?>/>&#160;<strong>Нет</strong>
                                    <span>Разрешить пользователям группы полнотекстовый поиск пользователей в списке пользователей.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Скачивание файлов</th>
                                <td>
                                    <input type="radio" name="file_download"
                                           value="1"<?php if (1 == $group['g_file_download']) {
                                               echo ' checked="checked"';
                                           } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                    <input type="radio"
                                            name="file_download"
                                            value="0"<?php if (!$group['g_file_download']) {
                                                echo ' checked="checked"';
                                            } ?>/>&#160;<strong>Нет</strong>
                                    <span>Разрешить пользователям группы скачивать файлы, прикрепленные к сообщениям.</span>
                                </td>
                            </tr>
                            <?php if (PUN_GUEST != $group['g_id']) { ?>
                                <tr>
                                    <th scope="row">Выкладывание файлов</th>
                                    <td>
                                        <input type="radio" name="file_upload"
                                               value="1"<?php if (1 == $group['g_file_upload']) {
                                                   echo ' checked="checked"';
                                               } ?>/>&#160;<strong>Да</strong>&#160;&#160;&#160;
                                        <input type="radio" name="file_upload"
                                                value="0"<?php if (!$group['g_file_upload']) {
                                                    echo ' checked="checked"';
                                                } ?>/>&#160;<strong>Нет</strong>
                                        <span>Разрешить пользователям группы прикреплять свои файлы к сообщениям.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Файловый лимит</th>
                                    <td>
                                        <input type="text" name="file_limit" size="5" maxlength="4"
                                               value="<?php echo $group['g_file_limit']; ?>"/>
                                        <span>Количество файлов, которое пользователи из этой группы могут выложить.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Интервал редактирования имени темы</th>
                                    <td>
                                        <input type="text" name="edit_subjects_interval" size="5" maxlength="5"
                                               value="<?php echo $group['g_edit_subjects_interval']; ?>"/>
                                        <span>Количество секунд после поста в течение которого пользователь группы может редактировать имя своей темы. Поставьте 0 чтобы разрешить редактировать бесконечно.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Антифлуд-интервал</th>
                                    <td>
                                        <input type="text" name="post_flood" size="5" maxlength="4"
                                               value="<?php echo $group['g_post_flood']; ?>"/>
                                        <span>Количество секунд которое пользователь группы должен ждать между постами. Поставьте 0 чтобы отключить.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Антифлуд интервал поиска</th>
                                    <td>
                                        <input type="text" name="search_flood" size="5" maxlength="4"
                                               value="<?php echo $group['g_search_flood']; ?>"/>
                                        <span>Количество секунд которое пользователь группы должен ждать между поисковыми запросами. Поставьте 0 чтобы отключить.</span>
                                    </td>
                                </tr>
                                <?php }
                            } ?>
                        </table>
                        <?php if (PUN_MOD == $group['g_id']) { ?>
                        <p class="warntext">Пожалуйста учтите чтобы дать пользователю этой группы права модератора,
                            он/она должен быть назначен модератором одного или нескольких форумов. Это делается в
                            админ-панели профиля пользователя.</p>
                        <?php } ?>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="add_edit_group" value=" Сохранить "/></p>
        </form>
    </div>
</div>
<div class="clearer"></div>
</div>
<?php

                            require_once PUN_ROOT.'footer.php';
} // Add/edit a group (stage 2)
elseif (isset($_POST['add_edit_group'])) {
    // confirm_referrer('admin_groups.php');

    // Is this the admin group? (special rules apply)
    $is_admin_group = (isset($_POST['group_id']) && PUN_ADMIN == $_POST['group_id']) ? true : false;

    $title = \trim($_POST['req_title']);
    $user_title = \trim($_POST['user_title']);
    $read_board = isset($_POST['read_board']) ? (int) ($_POST['read_board']) : 1;
    $post_replies = isset($_POST['post_replies']) ? (int) ($_POST['post_replies']) : 1;
    $post_topics = isset($_POST['post_topics']) ? (int) ($_POST['post_topics']) : 1;
    $edit_posts = isset($_POST['edit_posts']) ? (int) ($_POST['edit_posts']) : ($is_admin_group ? 1 : '0');
    $delete_posts = isset($_POST['delete_posts']) ? (int) ($_POST['delete_posts']) : ($is_admin_group ? 1 : '0');
    $delete_topics = isset($_POST['delete_topics']) ? (int) ($_POST['delete_topics']) : ($is_admin_group ? 1 : '0');
    $set_title = isset($_POST['set_title']) ? (int) ($_POST['set_title']) : ($is_admin_group ? 1 : '0');
    $search = isset($_POST['search']) ? (int) ($_POST['search']) : 1;
    $search_users = isset($_POST['search_users']) ? (int) ($_POST['search_users']) : 1;
    $file_download = isset($_POST['file_download']) ? (int) ($_POST['file_download']) : '0';
    $file_upload = isset($_POST['file_upload']) ? (int) ($_POST['file_upload']) : '0';
    $file_limit = isset($_POST['file_limit']) ? (int) ($_POST['file_limit']) : '0';
    $edit_subjects_interval = isset($_POST['edit_subjects_interval']) ? (int) ($_POST['edit_subjects_interval']) : '0';
    $post_flood = isset($_POST['post_flood']) ? (int) ($_POST['post_flood']) : '0';
    $search_flood = isset($_POST['search_flood']) ? (int) ($_POST['search_flood']) : '0';

    if (!$title) {
        \message('You must enter a group title.');
    }

    $user_title = ($user_title) ? '\''.$db->escape($user_title).'\'' : 'NULL';

    if ('add' == $_POST['mode']) {
        $result = $db->query('SELECT 1 FROM `'.$db->prefix.'groups` WHERE `g_title`=\''.$db->escape($title).'\'') || \error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
        if ($db->num_rows($result)) {
            \message('There is already a group with the title "'.\pun_htmlspecialchars($title).'".');
        }

        $db->query('INSERT INTO `'.$db->prefix.'groups` (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_file_download, g_file_upload, g_file_limit, g_edit_subjects_interval, g_post_flood, g_search_flood) VALUES(\''.$db->escape($title).'\', '.$user_title.', '.$read_board.', '.$post_replies.', '.$post_topics.', '.$edit_posts.', '.$delete_posts.', '.$delete_topics.', '.$set_title.', '.$search.', '.$search_users.', '.$file_download.', '.$file_upload.', '.$file_limit.', '.$edit_subjects_interval.', '.$post_flood.', '.$search_flood.')') || \error('Unable to add group', __FILE__, __LINE__, $db->error());
        $new_group_id = $db->insert_id();

        // Now lets copy the forum specific permissions from the group which this group is based on
        $result = $db->query('SELECT forum_id, read_forum, post_replies, post_topics FROM '.$db->prefix.'forum_perms WHERE group_id='.(int) $_POST['base_group']) || \error('Unable to fetch group forum permission list', __FILE__, __LINE__, $db->error());
        while ($cur_forum_perm = $db->fetch_assoc($result)) {
            $db->query('INSERT INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics) VALUES('.$new_group_id.', '.$cur_forum_perm['forum_id'].', '.$cur_forum_perm['read_forum'].', '.$cur_forum_perm['post_replies'].', '.$cur_forum_perm['post_topics'].')') || \error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
        }
    } else {
        $result = $db->query('SELECT 1 FROM `'.$db->prefix.'groups` WHERE g_title=\''.$db->escape($title).'\' AND g_id!='.(int) $_POST['group_id']) || \error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
        if ($db->num_rows($result)) {
            \message('There is already a group with the title "'.\pun_htmlspecialchars($title).'".');
        }

        $db->query('UPDATE `'.$db->prefix.'groups` SET g_title=\''.$db->escape($title).'\', g_user_title='.$user_title.', g_read_board='.$read_board.', g_post_replies='.$post_replies.', g_post_topics='.$post_topics.', g_edit_posts='.$edit_posts.', g_delete_posts='.$delete_posts.', g_delete_topics='.$delete_topics.', g_set_title='.$set_title.', g_search='.$search.', g_search_users='.$search_users.', g_file_download='.$file_download.', g_file_upload='.$file_upload.', g_file_limit='.$file_limit.', g_edit_subjects_interval='.$edit_subjects_interval.', g_post_flood='.$post_flood.', g_search_flood='.$search_flood.' WHERE g_id='.(int) $_POST['group_id']) || \error('Unable to update group', __FILE__, __LINE__, $db->error());
    }

    // Regenerate the quickjump cache
    include_once PUN_ROOT.'include/cache.php';
    \generate_quickjump_cache();
    \generate_wap_quickjump_cache();

    \redirect('admin_groups.php', 'Группа '.(('edit' === $_POST['mode']) ? 'отредактирована' : 'добавлена').'. Перенаправление &#x2026;');
} // Set default group
elseif (isset($_POST['set_default_group'])) {
    // confirm_referrer('admin_groups.php');

    $group_id = (int) $_POST['default_group'];
    if ($group_id < 4) {
        \message($lang_common['Bad request']);
    }

    $db->query('UPDATE '.$db->prefix.'config SET conf_value='.$group_id.' WHERE conf_name=\'o_default_user_group\'') || \error('Unable to update board config', __FILE__, __LINE__, $db->error());

    // Regenerate the config cache
    include_once PUN_ROOT.'include/cache.php';
    \generate_config_cache();

    \redirect('admin_groups.php', 'Группа по умолчанию задана. Перенаправление &#x2026;');
} // Remove a group
elseif (isset($_GET['del_group'])) {
    // confirm_referrer('admin_groups.php');

    $group_id = (int) $_GET['del_group'];
    if ($group_id < 5) {
        \message($lang_common['Bad request']);
    }

    // Make sure we don't remove the default group
    if ($group_id == $pun_config['o_default_user_group']) {
        \message('The default group cannot be removed. In order to delete this group, you must first setup a different group as the default.');
    }

    // Check if this group has any members
    $result = $db->query('SELECT g.g_title, COUNT(u.id) FROM `'.$db->prefix.'groups` AS g INNER JOIN `'.$db->prefix.'users` AS u ON g.g_id=u.group_id WHERE g.g_id='.$group_id.' GROUP BY g.g_id, g_title') || \error('Unable to fetch group info', __FILE__, __LINE__, $db->error());

    // If the group doesn't have any members or if we've already selected a group to move the members to
    if (!$db->num_rows($result) || isset($_POST['del_group'])) {
        if (isset($_POST['del_group'])) {
            $move_to_group = (int) $_POST['move_to_group'];
            $db->query('UPDATE '.$db->prefix.'users SET group_id='.$move_to_group.' WHERE group_id='.$group_id) || \error('Unable to move users into group', __FILE__, __LINE__, $db->error());
        }

        // Delete the group and any forum specific permissions
        $db->query('DELETE FROM `'.$db->prefix.'groups` WHERE g_id='.$group_id) || \error('Unable to delete group', __FILE__, __LINE__, $db->error());
        $db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE group_id='.$group_id) || \error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());

        // Regenerate the quickjump cache
        include_once PUN_ROOT.'include/cache.php';
        \generate_quickjump_cache();
        \generate_wap_quickjump_cache();

        \redirect('admin_groups.php', 'Группа удалена. Перенаправление &#x2026;');
    }

    [$group_title, $group_members] = $db->fetch_row($result);

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / User groups';

    require_once PUN_ROOT.'header.php';

    \generate_admin_menu('groups');

    echo '<div class="blockform">
<h2><span>Удаление группы</span></h2>
<div class="box">
<form id="groups" method="post" action="admin_groups.php?del_group='.$group_id.'">
<div class="inform">
<fieldset>
<legend>Перемещение пользователей группы</legend>
<div class="infldset">
<p>Группа "'.\pun_htmlspecialchars($group_title).'" содержит '.$group_members.' членов. Пожалуйста выберите группу, в которую будут перемещены пользователи после ее удаления</p>
<label>Переместить пользователей в
<select name="move_to_group">';

    $result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` WHERE g_id!='.PUN_GUEST.' AND g_id!='.$group_id.' ORDER BY g_title') || \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

    while ($cur_group = $db->fetch_assoc($result)) {
        if (PUN_MEMBER == $cur_group['g_id']) { // Pre-select the pre-defined Members group
            echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
        } else {
            echo '<option value="'.$cur_group['g_id'].'">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
        }
    }

    echo '</select>
</br></label>
</div>
</fieldset>
</div>
<p><input type="submit" name="del_group" value="Удалить группу" /></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

    require_once PUN_ROOT.'footer.php';
}

$page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / User groups';

require_once PUN_ROOT.'header.php';

\generate_admin_menu('groups');

echo '<div class="blockform">
<h2><span>Добавление/установки групп</span></h2>
<div class="box">
<form id="groups" method="post" action="admin_groups.php?action=foo">
<div class="inform">
<fieldset>
<legend>Добавить новую группу</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">На основе группы<div><input type="submit" name="add_group" value=" Добавить " /></div></th>
<td>
<select id="base_group" name="base_group">';

$result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` WHERE g_id>'.PUN_GUEST.' ORDER BY g_title') || \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result)) {
    if ($cur_group['g_id'] == $pun_config['o_default_user_group']) {
        echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    } else {
        echo '<option value="'.$cur_group['g_id'].'">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    }
}

echo '</select>
<span>Выбрать группу пользователей разрешения которой унаследует новая группа. Следующая страница позволит вам откорректировать упомянутые настройки.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>Задать группу по умолчанию</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Группа по умолчанию<div><input type="submit" name="set_default_group" value=" Сохранить " /></div></th>
<td>
<select id="default_group" name="default_group">';

$result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` WHERE g_id>'.PUN_GUEST.' ORDER BY g_title') || \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result)) {
    if ($cur_group['g_id'] == $pun_config['o_default_user_group']) {
        echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    } else {
        echo '<option value="'.$cur_group['g_id'].'">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    }
}

echo '</select>
<span>Группа пользователей по умолчанию, т.е. группа в которую попадает вновь зарегистрировавшийся пользователь. Для безопасности пользователь не может сразу попасть в группу модераторов или администраторов.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
</form>
</div>
<h2 class="block2"><span>Существующие группы</span></h2>
<div class="box">
<div class="fakeform">
<div class="inform">
<fieldset>
<legend>Редактирование/удаление групп</legend>
<div class="infldset">
<p>Пред-установленные группы Гости, Администраторы, Модераторы и Пользователи не могут быть удалены. Хотя их можно редактировать, учтите что в некоторых группах иные настройки недоступны (напр.: разрешение <em>редактировать свои сообщения</em> для группы Гости). Администраторы всегда имеют все разрешения.</p>
<table cellspacing="0">';

$result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` ORDER BY g_id') || \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result)) {
    echo '<tr><th scope="row"><a href="admin_groups.php?edit_group='.$cur_group['g_id'].'">Edit</a>'.(($cur_group['g_id'] > PUN_MEMBER) ? ' - <a href="admin_groups.php?del_group='.$cur_group['g_id'].'">Remove</a>' : '').'</th><td>'.\pun_htmlspecialchars($cur_group['g_title']).'</td></tr>';
}

echo '</table></div></fieldset></div></div></div></div><div class="clearer"></div></div>';

require_once PUN_ROOT.'footer.php';
