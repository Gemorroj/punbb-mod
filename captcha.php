<?php

// Composer
require __DIR__.'/vendor/autoload.php';

\session_name('bunbb_captcha');
\session_start();

$symbols = \str_split('23456789abcdegikpqsvxyz', 1);
\array_push($symbols, ...$symbols);
\shuffle($symbols);
$_SESSION['captcha_keystring'] = $symbols[0].$symbols[1].$symbols[2].$symbols[3];

$builder = new Gregwar\Captcha\CaptchaBuilder($_SESSION['captcha_keystring']);
$builder->build(90, 45);

\header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
\header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
\header('Cache-Control: post-check=0, pre-check=0', false);

\header('Content-Type: image/jpeg');
$builder->output();
