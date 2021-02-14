<?php

//error_reporting (0);

\session_start();

require __DIR__.'/include/captcha/kcaptcha.php';

$captcha = new KCAPTCHA();

if ($_REQUEST[\session_name()]) {
    $_SESSION['captcha_keystring'] = $captcha->getKeyString();
}
