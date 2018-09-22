<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);


// Confirm Page

if (isset($_POST['confirm'])) {
    // Make sure message body was entered
    if (!trim($_POST['message_body'])) {
        message('Вы не ввели тело письма!');
    }

    // Make sure message subject was entered
    if (!trim($_POST['message_subject'])) {
        message('Вы не ввели тему письма!');
    }

    // Make sure group id was entered
    if (!trim($_POST['g_id']) == '') {
        message('Вы не выбрали группу!');
    }

    // Display the admin navigation menu
    generate_admin_menu($plugin);

    $preview_message_body = nl2br(pun_htmlspecialchars($_POST['message_body']));

    if ($_POST['g_id'] != 0) {
        $adv = 'and group_id = ' . $_POST['g_id'];
    } else {
        $adv = '';
    }

    $result = $db->query('SELECT COUNT(1) AS usercount FROM ' . $db->prefix . 'users WHERE username != "Guest" ' . $adv . ' ORDER BY username') or error('Could not get user count from database', __FILE__, __LINE__, $db->error());
    $row = $db->fetch_assoc($result); ?>
<div id="exampleplugin" class="blockform">
    <h2><span>Массовая рассылка - Подтверждение</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Пожалуйста подтвердите ваше вообщение ниже.<br/><br/>Если чтото на правильно, пожалуйста <a
                href="javascript:history.go(-1)">Вернитесь</a>.</p>
        </div>
    </div>
    <h2 class="block2"><span>Подтверждение сообщения</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inform">
                <input type="hidden" name="message_subject"
                       value="<?php echo pun_htmlspecialchars($_POST['message_subject']); ?>"/>
                <input type="hidden" name="message_body"
                       value="<?php echo pun_htmlspecialchars($_POST['message_body']); ?>"/>
                <input type="hidden" name="g_id" value="<?php echo pun_htmlspecialchars($_POST['g_id']); ?>"/>
                <fieldset>
                    <legend>Получатели сообщения</legend>
                    <div class="infldset">
                        [ <strong><?php echo $row['usercount']; ?></strong> ] Зарегистрированных пользователей получат
                        это сообщение (включая администратора).
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend>Содержание сообщения</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Тема</th>
                                <td>
                                    <?php echo pun_htmlspecialchars($_POST['message_subject']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Тело письма</th>
                                <td>
                                    <?php echo $preview_message_body; ?>
                                </td>
                            </tr>
                            <tr>
                            <th scope="row">Группа</th>
                            <td>
                                <?php echo pun_htmlspecialchars($_POST['g_id']); ?>
                            </td>
                        </table>
                        <div class="fsetsubmit"><input type="submit" name="send_message" value="Подтверждаю - Послать" /></div>
                        <p class="topspace">Пожалуйста нажмите кнопку только однажды. Ожидайте сообщения о
                            результате.</p>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<?php
} elseif (isset($_POST['send_message'])) {
        // Send the Message
        require_once PUN_ROOT . 'include/email.php';

        // Display the admin navigation menu
        generate_admin_menu($plugin);

        if ($_POST['g_id'] != 0) {
            $gid = 'and group_id = ' . $_POST['g_id'];
        } else {
            $gid = '';
        }

        $result = $db->query('SELECT username, email FROM ' . $db->prefix . 'users WHERE username != "Guest" ' . $gid . ' ORDER BY username') or error('Could not get users from the database', __FILE__, __LINE__, $db->error());
        while ($row = $db->fetch_assoc($result)) {
            $addresses[$row['username']] = $row['email'];
        }

        $usercount = count($addresses);

        foreach ($addresses as $recipientname => $recipientemail) {
            $mail_to = $recipientname . ' <' . $recipientemail . '>';
            $mail_subject = $_POST['message_subject'];
            $mail_message = $_POST['message_body'];

            pun_mail($mail_to, $mail_subject, $mail_message);
        } ?>
<div class="block">
    <h2><span>Массовая рассылка - Сообщение отослано</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Сообщение было отослано [ <strong><?php echo $usercount; ?></strong> ] Зарегистрированным пользователям.
            </p>

            <p>Вы так же получите администраторскую копию в через несколько минут.</p>

            <p>Пожалуйста просмотрите администраторскую копию для проверки.</p>
        </div>
    </div>
</div>
<?php
    } else {
        // Display the Main Page

        // Display the admin navigation menu
        generate_admin_menu($plugin); ?>
<div id="exampleplugin" class="blockform">
    <h2><span>Массовая рассылка e-mail</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин позволяет администратору посылать массовые сообщения (e-mail) всем зарегистрированным
                пользователям</p>

            <p>После заполнения формы на следующей странице будет запрошено подтверждение во избежание ошибок.</p>
        </div>
    </div>

    <h2 class="block2"><span>Составление письма</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inform">
                <fieldset>
                    <legend>Содержание письма</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Тема</th>
                                <td>
                                    <input type="text" name="message_subject" size="50" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Тело письма</th>
                                <td>
                                    <textarea name="message_body" rows="14" cols="92"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Группа</th>
                                <td>
                                    <select name="g_id">
                                        <option></option>
                                        <option value="0">Все группы</option>
                                        <?php
                                        $result = $db->query('SELECT g_id, g_title FROM `' . $db->prefix . 'groups` WHERE g_title != "Guest" ORDER BY g_id');
        while ($groups = $db->fetch_assoc($result)) {
            echo '<option value="' . $groups['g_id'] . '">' . pun_htmlspecialchars($groups['g_title']) . '</option>';
        } ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <div class="fsetsubmit"><input type="submit" name="confirm" value="перейти к подтверждению" /></div>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<?php
    }
