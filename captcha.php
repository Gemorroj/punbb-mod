<?php

//error_reporting (0);

\session_start();

require \dirname(__FILE__).'/include/captcha/kcaptcha.php';

$captcha = new KCAPTCHA();

if ($_REQUEST[\session_name()]) {
    $_SESSION['captcha_keystring'] = $captcha->getKeyString();
}
