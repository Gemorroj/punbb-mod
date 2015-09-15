<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', 1.3);

if (isset($_POST['prune'])) {
    // Make sure something something was entered
    if ((trim($_POST['days']) == '') || trim($_POST['posts']) == '') {
        message('Вы должны задать все настройки!');
    }
    if ($_POST['admods_delete']) {
        $admod_delete = 'group_id > 0';
    } else {
        $admod_delete = 'group_id > 3';
    }

    if ($_POST['verified'] == 1) {
        $verified = '';
    } else if ($_POST['verified'] == 0) {
        $verified = 'AND (group_id < 32000)';
    } else {
        $verified = 'AND (group_id = 32000)';
    }

    $prune = ($_POST['prune_by'] == 1) ? 'registered' : 'last_visit';

    $user_time = $_SERVER['REQUEST_TIME'] - ($_POST['days'] * 86400);
    $result = $db->query('DELETE FROM ' . $db->prefix . 'users WHERE (num_posts < ' . intval($_POST['posts']) . ') AND (' . $prune . ' < ' . intval($user_time) . ') AND (id > 2) AND (' . $admod_delete . ')' . $verified) or error('Unable to delete users', __FILE__, __LINE__, $db->error());
    $users_pruned = $db->affected_rows();
    message('Сокращение завершено. Удалены пользователи ' . $users_pruned . '.');
} else if (isset($_POST['add_user'])) {
    require PUN_ROOT . 'lang/' . $pun_user['language'] . '/prof_reg.php';
    require PUN_ROOT . 'lang/' . $pun_user['language'] . '/register.php';
    $username = pun_trim($_POST['username']);
    $email1 = mb_strtolower(trim($_POST['email']));
    $email2 = mb_strtolower(trim($_POST['email']));

    if ($_POST['random_pass'] == 1) {
        $password1 = random_pass(8);
        $password2 = $password1;
    } else {
        $password1 = trim($_POST['password']);
        $password2 = trim($_POST['password']);
    }

    // Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
    $username = preg_replace('#\s+#s', ' ', $username);

    // Validate username and passwords
    if (mb_strlen($username) < 2) {
        message($lang_prof_reg['Username too short']);
    } else if (mb_strlen($username) > 25) { // This usually doesn't happen since the form element only accepts 25 characters
        message($lang_common['Bad request']);
    } else if (mb_strlen($password1) < 4) {
        message($lang_prof_reg['Pass too short']);
    } else if ($password1 != $password2) {
        message($lang_prof_reg['Pass not match']);
    } else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) {
        message($lang_prof_reg['Username guest']);
    } else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) {
        message($lang_prof_reg['Username IP']);
    } else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, "'") !== false && strpos($username, '"') !== false) {
        message($lang_prof_reg['Username reserved chars']);
    } else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]|\[hide|\[/hide\]#i', $username)) {
        message($lang_prof_reg['Username BBCode']);
    }

    // Check username for any censored words
    if ($pun_config['o_censoring'] == 1) {
        // If the censored username differs from the username
        if (censor_words($username) != $username) {
            message($lang_register['Username censor']);
        }
    }

    // Check that the username (or a too similar username) is not already registered
    $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE username=\'' . $db->escape($username) . '\' OR username=\'' . $db->escape(preg_replace('/[^\w]/', '', $username)) . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result)) {
        $busy = $db->result($result);
        message($lang_register['Username dupe 1'] . ' ' . pun_htmlspecialchars($busy) . '. ' . $lang_register['Username dupe 2']);
    }


    // Validate e-mail
    require PUN_ROOT . 'include/email.php';

    if (!is_valid_email($email1)) {
        message($lang_common['Invalid e-mail']);
    }

    // Check if someone else already has registered with that e-mail address
    $dupe_list = array();
    $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE email=\'' . $email1 . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        while ($cur_dupe = $db->fetch_assoc($result)) {
            $dupe_list[] = $cur_dupe['username'];
        }
    }

    $timezone = 0;
    $language = isset($_POST['language']) ? $_POST['language'] : $pun_config['o_default_lang'];
    $save_pass = (!isset($_POST['save_pass']) || $_POST['save_pass'] != 1) ? 0 : 1;


    // Insert the new user into the database. We do this now to get the last inserted id for later use.
    $intial_group_id = ($_POST['random_pass'] == '0') ? $pun_config['o_default_user_group'] : PUN_UNVERIFIED;
    $password_hash = pun_hash($password1);

    // Add the user
    $db->query('INSERT INTO ' . $db->prefix . 'users (username, group_id, password, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit) VALUES(\'' . $db->escape($username) . '\', ' . $intial_group_id . ', \'' . $password_hash . '\', \'' . $email1 . '\', 1, ' . $save_pass . ', ' . $timezone . ' , \'' . $language . '\', \'' . $pun_config['o_default_style'] . '\', ' . $_SERVER['REQUEST_TIME'] . ', \'' . get_remote_address() . '\', ' . $_SERVER['REQUEST_TIME'] . ')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
    $new_uid = $db->insert_id();

    // Should we alert people on the admin mailing list that a new user has registered?
    if ($pun_config['o_regs_report'] == 1) {
        $mail_subject = 'Alert - New registration';
        $mail_message = 'User \'' . $username . '\' registered in the forums at ' . $pun_config['o_base_url'] . "\n\n" . 'User profile: ' . $pun_config['o_base_url'] . '/profile.php?id=' . $new_uid . "\n\n" . '-- ' . "\n" . 'Forum Mailer' . "\n" . '(Do not reply to this message)';

        pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
    }

    // Must the user verify the registration or do we log him/her in right now?
    if ($_POST['random_pass'] == 1) {
        // Load the "welcome" template
        $mail_tpl = trim(file_get_contents(PUN_ROOT . 'lang/' . $pun_user['language'] . '/mail_templates/welcome.tpl'));

        // The first row contains the subject
        $first_crlf = strpos($mail_tpl, "\n");
        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
        $mail_message = trim(substr($mail_tpl, $first_crlf));
        $mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
        $mail_message = str_replace('<base_url>', $pun_config['o_base_url'] . '/', $mail_message);
        $mail_message = str_replace('<username>', $username, $mail_message);
        $mail_message = str_replace('<password>', $password1, $mail_message);
        $mail_message = str_replace('<login_url>', $pun_config['o_base_url'] . '/login.php', $mail_message);
        $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer'], $mail_message);
        pun_mail($email1, $mail_subject, $mail_message);
    }

    message('Пользователь создан');
} else {
    // Display the admin navigation menu
    generate_admin_menu($plugin);

    ?>
<div class="block">
    <h2><span>Управление пользователями - v<?php echo PLUGIN_VERSION; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин позволяет сокращать пользователей которые отвечали меньше определенного числа раз в течение
                заданного количество дней.</p>

            <p><strong>Внимание: Это на совсем и использовать нужно очень осторожно (рекомендуется сделать бэкап перед
                сокращением).</strong></p>

            <p>Так же вы можете вручную добавлять пользователей, это удобно для закрытых форумов (если вы отключили
                возможность регистрации в опциях.)</p>
        </div>
    </div>
</div>
<div class="blockform">
    <h2 class="block2"><span>Сокращение пользователей</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inform">
                <fieldset>
                    <legend>Настройки</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Сокращать по</th>
                                <td>
                                    <input type="radio" name="prune_by" value="1" checked="checked"/>&#160;<strong>Дате
                                    регистрации</strong>&#160;&#160;&#160;<input type="radio" name="prune_by"
                                                                                 value="0"/>&#160;<strong>Последнему
                                    посещению</strong>
                                    <span>Решите с момента даты регистрации или последнего посещения отсчитывать минимальное количество дней.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Минимум дней с регистрации/последего посещения</th>
                                <td>
                                    <input type="text" name="days" value="28" size="25" />
                                    <span>Минимум дней от настроек выше, с которого сократятся пользователи.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Максимум сообщений</th>
                                <td>
                                    <input type="text" name="posts" value="1" size="25" />
                                    <span>Пользователи с большим количеством сообщений не сократятся. т.е. значение 1 удалит пользователей без сообщений.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Удалять администраторов и модераторов?</th>
                                <td>
                                    <input type="radio" name="admods_delete" value="1"/> <strong>Да</strong>&#160;
                                    &#160;<input type="radio" name="admods_delete" value="0" checked="checked"/>
                                    <strong>Нет</strong>
                                    <span>Если да, любой отвечающий условиям модератор или администратор тоже сократится.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Статус пользователя</th>
                                <td>
                                    <input type="radio" name="verified" value="1"/> <strong>Удалить любых</strong>&#160;
                                    &#160;<input type="radio" name="verified" value="0" checked="checked"/> <strong>Удалить
                                    только проверенных</strong>&#160; &#160;<input type="radio" name="verified"
                                                                                   value="2"/> <strong>Удалить только не
                                    проверенных</strong>
                                    <span>Решите проверенные или не проверенные пользователи должны быть удалены.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="prune" value="Отправить" /></p>
        </form>
    </div>

    <h2 class="block2"><span>Добавление пользователя</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inform">
                <fieldset>
                    <legend>Параметры</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Имя пользователя</th>
                                <td>
                                    <input type="text" name="username" size="25" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td>
                                    <input type="text" name="email" size="50" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Сгенерировать случайный пароль?</th>
                                <td>
                                    <input type="radio" name="random_pass" value="1"/> <strong>Да</strong>&#160;
                                    &#160;<input type="radio" name="random_pass" value="0" checked="checked"/> <strong>Нет</strong>
                                    <span>Если да, случайный пароль будет сгенерирован и отослан по адресу выше.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Пароль</th>
                                <td>
                                    <input type="text" name="password" size="25" />
                                    <span>Если не хотите случайный пароль.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="add_user" value="Отправить" /></p>
        </form>
    </div>
</div>

<?php
}
