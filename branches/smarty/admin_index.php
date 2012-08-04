<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT . 'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_MOD) {
    message($lang_common['No permission']);
}


$action = isset($_GET['action']) ? $_GET['action'] : null;

// Check for upgrade
if ($action == 'check_upgrade') {
    if (!ini_get('allow_url_fopen')) {
        message($lang_admin['index_allow_url_fopen']);
    }

    $fp = @fopen('http://punbb.informer.com/latest_version', 'r');
    $latest_version = trim(@fread($fp, 16));
    @fclose($fp);

    if ($latest_version == '') {
        message($lang_admin['index_fail_update']);
    }

    $latest_version = preg_replace('/(\.0)+(?!\.)|(\.0+$)/', '$2', $latest_version);
    $cur_version = preg_replace('/(\.0)+(?!\.)|(\.0+$)/', '$2', $cur_version);

    if (version_compare($cur_version, $latest_version, '>=')) {
        message($lang_admin['index_update_no']);
    } else {
        message($lang_admin['index_update_yes']);
    }
} else if ($action == 'phpinfo' && $pun_user['g_id'] == PUN_ADMIN) {
    // Is phpinfo() a disabled function?
    if (strpos(strtolower((string)@ini_get('disable_functions')), 'phpinfo') !== false) {
        message($lang_admin['phpinfo']);
    }

    phpinfo();
    exit;
} else if ($action == 'optimize') {
    $result = $db->query('SHOW TABLES FROM ' . $db_name);

    $tables = null;
    while ($row = $db->fetch_row($result)) {
        $tables .= '`' . $row[0] . '`, ';
    }
    $tables = rtrim($tables, ', ');

    if ($db->query('OPTIMIZE TABLE ' . $tables) && $db->query('ANALYZE TABLE ' . $tables)) {
        message('Tables Optimized');
    } else {
        message('Tables NOT Optimized');
    }
}


// Get the server load averages (if possible)
if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg')) {
    // We use @ just in case
    $fh = @fopen('/proc/loadavg', 'r');
    $load_averages = @fread($fh, 64);
    @fclose($fh);

    $load_averages = @explode(' ', $load_averages);
    $server_load = isset($load_averages[2]) ? $load_averages[0] . ' ' . $load_averages[1] . ' ' . $load_averages[2] : 'Not available';
} else if (!in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages)) {
    $server_load = $load_averages[1] . ' ' . $load_averages[2] . ' ' . $load_averages[3];
} else {
    $server_load = 'Not available';
}


// Get number of current visitors
$result = $db->query('SELECT COUNT(user_id) FROM ' . $db->prefix . 'online WHERE idle = 0') or error('Unable to fetch online count', __FILE__, __LINE__, $db->error());
$num_online = $db->result($result);


// Get the database system version
$result = $db->query('SELECT VERSION()') or error('Unable to fetch version info', __FILE__, __LINE__, $db->error());
$db_version = $db->result($result);


// Collect some additional info about MySQL
$db_version = 'MySQL ' . $db_version;

// Calculate total db size/row count
$result = $db->query('SHOW TABLE STATUS FROM `' . $db_name . '`') or error('Unable to fetch table status', __FILE__, __LINE__, $db->error());

$total_records = $total_size = 0;
while ($status = $db->fetch_assoc($result)) {
    $total_records += $status['Rows'];
    $total_size += $status['Data_length'] + $status['Index_length'];
}

$total_size = $total_size / 1024;

if ($total_size > 1024) {
    $total_size = round($total_size / 1024, 2) . ' mb';
} else {
    $total_size = round($total_size, 2) . ' kb';
}


// See if php accelerator is loaded
if (extension_loaded('memcache')) {
    $php_accelerator = '<a href="http://turck-mmcache.sourceforge.net/">Turck MMCache</a>';
} else if (isset($_PHPA)) {
    $php_accelerator = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
} else if (extension_loaded('apc')) {
    $php_accelerator = '<a href="http://pecl.php.net/package/apc">APC</a>';
} else if (extension_loaded('eaccelerator')) {
    $php_accelerator = '<a href="http://eaccelerator.net/">eAccelerator</a>';
} else if (extension_loaded('Zend Optimizer')) {
    $php_accelerator = '<a href="http://www.zend.com/en/products/guard/zend-optimizer">Zend Optimizer</a>';
} else if (extension_loaded('xcache')) {
    $php_accelerator = '<a href="http://xcache.lighttpd.net/">XCache</a>';
} else {
    $php_accelerator = 'N/A';
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin';
require_once PUN_ROOT . 'header.php';

generate_admin_menu('index');


echo '<div class="block">
<h2>' . $lang_admin['index'] . '</h2>
<div id="adintro" class="box">
<div class="inbox">
<p>' . $lang_admin['index_main'] . '</p>
<a href="admin_index.php?action=optimize">Optimize Tables</a>
</div>
</div>

<h2 class="block2"><span>' . $lang_admin['index_stats'] . '</span></h2>
<div id="adstats" class="box">
<div class="inbox">
<dl>
<dt>' . $lang_admin['index_punbb'] . '</dt>
<dd>
PunBB ' . $pun_config['o_cur_version'] . '<!-- - <a href="admin_index.php?action=check_upgrade">Update</a> --><br />
&copy; Copyright 2002, 2003, 2004, 2005 Rickard Andersson<br />
</dd>
<dt>' . $lang_admin['index_server'] . '</dt>
<dd>
' . $server_load . ' (' . $num_online . ' on-line)
</dd>
<dt>' . $lang_admin['index_int'] . '</dt>
<dd>
OS: ' . PHP_OS . '<br />
PHP: ' . phpversion() . ' - <a href="admin_index.php?action=phpinfo">Info</a><br />
Accelerator: ' . $php_accelerator . '
</dd>
<dt>' . $lang_admin['index_bd'] . '</dt>
<dd>
' . $db_version . '<br />
Lines: ' . $total_records . '<br />
Size: ' . $total_size . '
</dd>
</dl>
</div>
</div>
</div>
<div class="clearer"></div>
</div>';

require_once PUN_ROOT . 'footer.php';
?>