<?php
define('PUN_ROOT', '../');

require PUN_ROOT . 'include/functions.php';
wap_redirect(substr(rawurldecode($_SERVER['QUERY_STRING']), 2));
?>