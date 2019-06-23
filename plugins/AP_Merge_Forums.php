<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '0.1 mod');

// If the "Run Merge" button was clicked
if (isset($_POST['run_merge'])) {
    // Get the variables
    $forum1 = intval($_POST['forum1']);
    $forum2 = intval($_POST['forum2']);

    // Make sure a forum was specified.
    if ('' == trim($forum1)) {
        message('Вы не уточнили из какого форума брать темы.');
    }

    if ('' == trim($forum2)) {
        message('Вы не уточнили в какой форум переносить темы.');
    }

    //Make sure the forum specified exists
    $result = $db->query('SELECT * FROM '.$db->prefix.'forums WHERE id='.$forum1);
    if (!$db->num_rows($result)) {
        message('Такого форума - источника нет.');
    }

    $result = $db->query('SELECT * FROM '.$db->prefix.'forums WHERE id='.$forum2);
    if (!$db->num_rows($result)) {
        message('Такого форума - назначения нет.');
    }

    //Make sure the forums being merged aren't the same
    if ($forum1 == $forum2) {
        message('Указанные форумы совпадают.');
    }

    //Run the update query.
    $db->query('UPDATE '.$db->prefix.'topics SET forum_id='.$forum2.' WHERE forum_id='.$forum1);

    //Delete the old forum
    $db->query('DELETE FROM '.$db->prefix.'forums WHERE id = '.$forum1);

    //Update the forum last post, etc.
    update_forum($forum2);

    // Display the admin navigation menu
    generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Объединение форумов - v<?php echo PLUGIN_VERSION; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <p>Форумы обьединены.</p>
        </div>
    </div>
</div>
<?php
} else {
        // If not, we show the "Show text" form

        // Display the admin navigation menu
        generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Объединение форумов - v<?php echo PLUGIN_VERSION; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин объединяет два форума в один. И затем удаляет старый форум.</p>
        </div>
    </div>
</div>

<div class="blockform">
    <h2 class="block2"><span>Опции</span></h2>

    <div class="box">
        <form id="merge" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="inform">
                <fieldset>
                    <legend>Выберите форум из которого хотите добавить и в который.</legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row">Объединить форумы
                                    <div><input type="submit" name="run_merge" value="Объединить" /></div>
                                </th>
                                <td>
                                    <select name="forum1">
                                        <?php
                                        $categories_result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories WHERE 1=1 ORDER BY id ASC');
        $forums_result = $db->query('SELECT id, forum_name, cat_id FROM '.$db->prefix.'forums WHERE 1=1 ORDER BY cat_id ASC');
        $cat_now = 0;

        while ($forums = $db->fetch_assoc($forums_result)) {
            //Check if it is a new cat
            if ($forums['cat_id'] != $cat_now) {
                $categories = $db->fetch_assoc($categories_result);
                echo '<option value="blargh" disabled="disabled">'.$categories['id'].'</option>';
                $cat_now = $categories['id'];
            }
            echo '<option value="'.$forums['id'].'">'.pun_htmlspecialchars($forums['forum_name']).'</option>';
        } ?>
                                    </select>
                                    <span>Выберите форум - источник.</span>
                                </td>
                                <td>
                                    <select name="forum2">
                                        <?php
                                        $categories_result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories WHERE 1=1 ORDER BY id ASC');
        $forums_result = $db->query('SELECT id, forum_name, cat_id FROM '.$db->prefix.'forums WHERE 1=1 ORDER BY cat_id ASC');
        $cat_now = 0;

        while ($forums = $db->fetch_assoc($forums_result)) {
            //Check if it is a new cat
            if ($forums['cat_id'] != $cat_now) {
                $categories = $db->fetch_assoc($categories_result);
                echo '<option value="blargh" disabled="disabled">'.$categories['id'].'</option>';
                $cat_now = $categories['id'];
            }
            echo '<option value="'.$forums['id'].'">'.pun_htmlspecialchars($forums['forum_name']).'</option>';
        } ?>
                                    </select>
                                    <span>Выберите форум, куда переносить темы.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<?php
    }
