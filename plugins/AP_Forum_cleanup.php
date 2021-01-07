<?php
// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
\define('PUN_PLUGIN_LOADED', 1);
\define('PLUGIN_VERSION', 1.0);

if (isset($_POST['cleanup'])) {
    //delete all users and posts from specified ips, then perform all other cleanup tasks except resetting post counts since that might not be needed or wanted.
    @\set_time_limit(0);
    $ip = "'".\implode("','", \array_values(\explode(' ', $_POST['ip_addys'])))."'";
    $db->query('DELETE FROM '.$db->prefix.'posts WHERE poster_ip IN('.$ip.')') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
    $db->query('DELETE FROM '.$db->prefix.'users WHERE registration_ip IN('.$ip.')') or error('Could not delete users', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_posts SELECT t.forum_id, count(*) as posts FROM '.$db->prefix.'posts as p LEFT JOIN '.$db->prefix.'topics as t on p.topic_id=t.id GROUP BY t.forum_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_posts SET num_posts=posts WHERE id=forum_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_topics SELECT forum_id, count(*) as topics FROM '.$db->prefix.'topics GROUP BY forum_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_topics SET num_topics=topics WHERE id=forum_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_posts SELECT topic_id, count(*)-1 as replies FROM '.$db->prefix.'posts GROUP BY topic_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_posts SET num_replies=replies WHERE id=topic_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_last SELECT p.posted AS n_last_post, p.id AS n_last_post_id, p.poster AS n_last_poster, t.forum_id FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id ORDER BY p.posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_lastb SELECT * FROM '.$db->prefix.'forum_last WHERE forum_id > 0 GROUP BY forum_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=forum_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_last SELECT posted AS n_last_post, id AS n_last_post_id, poster AS n_last_poster, topic_id FROM '.$db->prefix.'posts ORDER BY posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_lastb SELECT * FROM '.$db->prefix.'topic_last WHERE topic_id > 0 GROUP BY topic_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=topic_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topic SELECT t.id as o_id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON p.topic_id = t.id WHERE p.id IS NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topic WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_posts SELECT p.id as o_id FROM '.$db->prefix.'posts p LEFT JOIN '.$db->prefix.'topics t ON p.topic_id=t.id WHERE t.id IS NULL') or error('Creating orphaned posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'posts FROM '.$db->prefix.'posts, '.$db->prefix.'orph_posts WHERE o_id=id') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topics SELECT t.id as o_id FROM '.$db->prefix.'topics as t LEFT JOIN '.$db->prefix.'forums as f ON t.forum_id=f.id WHERE f.id is NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topics WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Форумы очищены');
}

if (isset($_POST['forum_post_sync'])) {
    // synchronise forum posts
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_posts SELECT t.forum_id, count(*) as posts FROM '.$db->prefix.'posts as p LEFT JOIN '.$db->prefix.'topics as t on p.topic_id=t.id GROUP BY t.forum_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_posts SET num_posts=posts WHERE id=forum_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_topics SELECT forum_id, count(*) as topics FROM '.$db->prefix.'topics GROUP BY forum_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_topics SET num_topics=topics WHERE id=forum_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Форумы синхронизированы');
} elseif (isset($_POST['topic_post_sync'])) {
    // synchronise topic posts
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_posts SELECT topic_id, count(*)-1 as replies FROM '.$db->prefix.'posts GROUP BY topic_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_posts SET num_replies=replies WHERE id=topic_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Темы синхронизированы');
} elseif (isset($_POST['user_post_sync'])) {
    // synchronise user posts
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'user_posts SELECT poster_id, count(*)as posts FROM '.$db->prefix.'posts GROUP BY poster_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'users, '.$db->prefix.'user_posts SET num_posts=posts WHERE id=poster_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Количество сообщений пользователей синхронизированы');
} elseif (isset($_POST['forum_last_post'])) {
    // synchronise forum last posts
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_last SELECT p.posted AS n_last_post, p.id AS n_last_post_id, p.poster AS n_last_poster, t.forum_id FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id ORDER BY p.posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_lastb SELECT * FROM '.$db->prefix.'forum_last WHERE forum_id > 0 GROUP BY forum_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=forum_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Последние сообщения форума синхронизированы');
} elseif (isset($_POST['topic_last_post'])) {
    // synchronise topic last posts
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_last SELECT posted AS n_last_post, id AS n_last_post_id, poster AS n_last_poster, topic_id FROM '.$db->prefix.'posts ORDER BY posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_lastb SELECT * FROM '.$db->prefix.'topic_last WHERE topic_id > 0 GROUP BY topic_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=topic_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Последние сообщения тем синхронизированы');
} elseif (isset($_POST['delete_orphans'])) {
    // Clear orphans
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topic SELECT t.id as o_id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON p.topic_id = t.id WHERE p.id IS NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topic WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_posts SELECT p.id as o_id FROM '.$db->prefix.'posts p LEFT JOIN '.$db->prefix.'topics t ON p.topic_id=t.id WHERE t.id IS NULL') or error('Creating orphaned posts table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'posts FROM '.$db->prefix.'posts, '.$db->prefix.'orph_posts WHERE o_id=id') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
    $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topics SELECT t.id as o_id FROM '.$db->prefix.'topics as t LEFT JOIN '.$db->prefix.'forums as f ON t.forum_id=f.id WHERE f.id is NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
    $db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topics WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
    redirect('admin_loader.php?plugin=AP_Forum_cleanup.php', 'Предки удалены');
} else {
    // Display the admin navigation menu
    generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка форумов - v<?php echo PLUGIN_VERSION; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин используется для очистки от лишних сообщений спамеров и редактирует базу данных приводя в
                соответствие не совпадающие вещи.</p>
        </div>
    </div>
</div>
<div class="block">
    <h2 class="block2"><span>Очистка от спама</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Позволяет навести порядок после атаки спамеров. Работает так: вы вводите один или несколько IP
                    адресов (разделены пробелами) и все пользователи и сообщения с этими IP удаляются вместе с другими
                    операциями очистки.</p>
                <table class="aligntop" cellspacing="0">
                    <tr>
                        <th scope="row">IP Адреса</th>
                        <td>
                            <input type="text" name="ip_addys" size="50" maxlength="255"/><br/>
                            <span>Введите один или больше IP адресов разделенных пробелами для удаления из форума (причем рекомендуется также забанить эти IP адреса в разделе банов админ-панели).</span>
                        </td>
                    </tr>
                </table>
            </div>
            <p class="submitend">
                <input type="submit" name="cleanup" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Синхронизировать показатели сообщений/тем форума</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Исправляет несоответствующие действительности показатели количества сообщений/тем каждого форума
                    (обычно если вы редактировали базу данных в ручную) пересчитать верно.</p>
            </div>
            <p class="submitend">
                <input type="submit" name="forum_post_sync" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Синхронизировать количество ответов в темах</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Исправляет сбившееся количество ответов каждой темы (обычно если вы редактировали базу данных в
                    ручную) и пересчитывает верно</p>
            </div>
            <p class="submitend">
                <input type="submit" name="topic_post_sync" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Синхронизировать количество пользовательских сообщений</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Исправляет сбившееся количество сообщений каждого пользователя (обычно если вы удаляли какие то
                    сообщения в базе данных) и пересчитывает верно.</p>
            </div>
            <p class="submitend">
                <input type="submit" name="user_post_sync" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Синхронизировать последние сообщения форума</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Исправляет список последних постов каждого форума. Обычно эта проблема бывает после чистки от спама
                    или после редактирования базы данных.</p>
            </div>
            <p class="submitend">
                <input type="submit" name="forum_last_post" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Синхронизировать последние сообщения тем</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Исправляет список последних постов каждой темы. Обычно эта проблема бывает после чистки от спама или
                    после редактирования базы данных.</p>
            </div>
            <p class="submitend">
                <input type="submit" name="topic_last_post" value="Отправить" />
            </p>
        </form>
    </div>
    <h2 class="block2"><span>Удалить предков</span></h2>

    <div class="box">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inbox">
                <p>Удаляет все сообщения чьи родительские темы былы удалены, или наоборот темы которые не содержат
                    сообщений и все темы, чьи родительские форумы были удалены. Обычно эта проблема бывает после
                    редактирования базы данных.</p>
            </div>
            <p class="submitend">
                <input type="submit" name="delete_orphans" value="Отправить" />
            </p>
        </form>
    </div>
</div>
<?php
}
