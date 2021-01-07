<?php

// Record the start time (will be used to calculate the generation time for the page)
$pun_start = \microtime(true);

// Enable DEBUG mode by removing // from the following line
//define('PUN_DEBUG', 1);

// This displays all executed queries in the page footer.
// DO NOT enable this in a production environment!
//define('PUN_SHOW_QUERIES', 1);

if (!\defined('PUN_ROOT')) {
    exit('The constant PUN_ROOT must be defined and point to a valid PunBB installation root directory.');
}

// Load the functions script
require_once PUN_ROOT.'include/functions.php';

require_once PUN_ROOT.'config.php';

// If PUN isn't defined, config.php is missing or corrupt
if (!\defined('PUN')) {
    exit('DEBUG MODE');
}

// Make sure PHP reports all errors except E_NOTICE. PunBB supports E_ALL, but a lot of scripts it may interact with, do not.
//error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(0);

// If a cookie name is not specified in config.php, we use the default (punbb_cookie)
if (!$cookie_name) {
    $cookie_name = 'punbb_cookie';
}

// Define a few commonly used constants
\define('PUN_UNVERIFIED', 32000);
\define('PUN_ADMIN', 1);
\define('PUN_MOD', 2);
\define('PUN_GUEST', 3);
\define('PUN_MEMBER', 4);

// Load DB abstraction layer and connect
require PUN_ROOT.'include/common_db.php';

// Load cached config
@include PUN_ROOT.'cache/cache_config.php';
if (!\defined('PUN_CONFIG_LOADED')) {
    include PUN_ROOT.'include/cache.php';
    generate_config_cache();

    include PUN_ROOT.'cache/cache_config.php';
}

// Enable output buffering
if (!\defined('PUN_DISABLE_BUFFERING')) {
    @\ob_start();
}

// Check/update/set cookie and fetch user info
$pun_user = array();
check_cookie($pun_user);

// Attempt to load the common language file
@include PUN_ROOT.'lang/'.$pun_user['language'].'/common.php';
if (!isset($lang_common)) {
    exit('There is no valid language pack "'.pun_htmlspecialchars($pun_user['language']).'" installed. Please reinstall a language of that name.');
}

@\mb_internal_encoding('UTF-8');

// Check if we are to display a maintenance message
if ($pun_config['o_maintenance'] && $pun_user['g_id'] > PUN_ADMIN && !\defined('PUN_TURN_OFF_MAINT')) {
    maintenance_message();
}

// Load cached bans
@include PUN_ROOT.'cache/cache_bans.php';
if (!\defined('PUN_BANS_LOADED')) {
    include_once PUN_ROOT.'include/cache.php';
    generate_bans_cache();

    include PUN_ROOT.'cache/cache_bans.php';
}

// Check if current user is banned
check_bans();

// Update online list
update_users_online();

require PUN_ROOT.'include/JsHelper.php';
