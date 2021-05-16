<?php
// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
\define('PUN_PLUGIN_LOADED', 1);

require PUN_ROOT.'include/cache.php';

// If the "Regenerate all cache" button was clicked
if (isset($_POST['regen_all_cache'])) {
    // We re-generate it all
    \generate_config_cache();
    \generate_bans_cache();
    \generate_quickjump_cache();
    \generate_wap_quickjump_cache();

    // Display the admin navigation menu
    \generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Кэш пересоздан!</p>

            <p><a href="javascript:history.go(-1)">Назад</a></p>
        </div>
    </div>
</div>
<?php
} // If the "Regenerate ban cache" button was clicked
elseif (isset($_POST['regen_ban_cache'])) {
    // We re-generate it
    \generate_bans_cache();
    // Display the admin navigation menu
    \generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Бан-кэш пересоздан!</p>

            <p><a href="javascript:history.go(-1)">Назад</a></p>
        </div>
    </div>
</div>
<?php
} // If the "Regenerate ranks cache" button was clicked
elseif (isset($_POST['regen_ranks_cache'])) {
    // We re-generate it
    \generate_ranks_cache();
    // Display the admin navigation menu
    \generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Кэш рангов пересоздан!</p>

            <p><a href="javascript:history.go(-1)">Назад</a></p>
        </div>
    </div>
</div>
<?php
} // If the "Regenerate config cache" button was clicked
elseif (isset($_POST['regen_config_cache'])) {
    // We re-generate it
    \generate_config_cache();
    // Display the admin navigation menu
    \generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Кэш настроек пересоздан!</p>

            <p><a href="javascript:history.go(-1)">Назад</a></p>
        </div>
    </div>
</div>
<?php
} // If the "Regenerate quickjump cache" button was clicked
elseif (isset($_POST['regen_jump_cache'])) {
    // We re-generate it
    \generate_quickjump_cache();
    \generate_wap_quickjump_cache();
    // Display the admin navigation menu
    \generate_admin_menu($plugin); ?>
<div class="block">
    <h2><span>Очистка кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Кэш быстрого перехода перевоздан!</p>

            <p><a href="javascript:history.go(-1)">Назад</a></p>
        </div>
    </div>
</div>
<?php
} else {
        // If not, we show the form
        // Display the admin navigation menu
        \generate_admin_menu($plugin); ?>
<div id="exampleplugin" class="blockform">
    <h2><span>Переоздание кэша</span></h2>

    <div class="box">
        <div class="inbox">
            <p>Этот плагин позволяет вам легко и просто пересоздавать кэш файлы вашего PunBB</p>

            <form id="regenerate" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <p><input type="submit" name="regen_all_cache" value="Пересоздать все кэш файлы" /></p>

                <p><input type="submit" name="regen_ban_cache" value="Пересоздать бан-кэш" /></p>

                <p><input type="submit" name="regen_ranks_cache" value="Пересоздать кэш рангов" /></p>

                <p><input type="submit" name="regen_config_cache" value="Пересоздать кэш настроек" /></p>

                <p><input type="submit" name="regen_jump_cache" value="Пересоздать кэш быстрого перехода" />
                </p>
            </form>
        </div>
    </div>
</div>
<?php
    }
