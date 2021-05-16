<?php

// Tell header.php to use the admin template
\define('PUN_ADMIN_CONSOLE', 1);

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

require PUN_ROOT.'include/common_admin.php';

if ($pun_user['g_id'] > PUN_MOD) {
    \message($lang_common['No permission']);
}

// The plugin to load should be supplied via GET
$plugin = $_GET['plugin'] ?? '';
if (!@\preg_match('/^AM?P_(\w*?)\.php$/i', $plugin)) {
    \message($lang_common['Bad request']);
}

// AP_ == Admins only, AMP_ == admins and moderators
$prefix = \substr($plugin, 0, \strpos($plugin, '_'));
if (PUN_MOD == $pun_user['g_id'] && 'AP' == $prefix) {
    \message($lang_common['No permission']);
}

// Make sure the file actually exists
if (!\file_exists(PUN_ROOT.'plugins/'.$plugin)) {
    \message('Plugin "'.$plugin.'" not found');
}

// Construct REQUEST_URI if it isn't set
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = ($_SERVER['PHP_SELF'] ?? '').'?'.($_SERVER['QUERY_STRING'] ?? '');
}

$page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / '.$plugin;

require_once PUN_ROOT.'header.php';

// Attempt to load the plugin. We don't use @ here to supress error messages,
// because if we did and a parse error occurred in the plugin, we would only
// get the "blank page of death".
include PUN_ROOT.'plugins/'.$plugin;
if (!\defined('PUN_PLUGIN_LOADED')) {
    \message('Plugin "'.$plugin.'" not loaded');
}

// Output the clearer div

echo '<div class="clearer"></div></div>';

require_once PUN_ROOT.'footer.php';
