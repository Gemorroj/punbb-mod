<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}


//
// Validate an e-mail address
//
function is_valid_email($email)
{
    if (strlen($email) > 50) {
        return false;
    }

    return preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email);
}

/**
 * @param string $email
 * @return bool
 */
function is_email_not_spammer($email)
{
    $data = @file_get_contents('http://api.stopforumspam.org/api?f=json&email=' . rawurlencode($email));
    if (!$data) {
        return true;
    }
    $json = @json_decode($data);
    if (!$json) {
        return true;
    }

    if ($json->success !== 1) {
        return true;
    }
    if ($json->email->appears > 0) {
        return false;
    }

    return true;
}

//
// Check if $email is banned
//
function is_banned_email($email)
{
    global $db, $pun_bans;

    foreach ($pun_bans as $cur_ban) {
        if ($cur_ban['email'] && ($email == $cur_ban['email'] || (strpos($cur_ban['email'], '@') === false && stristr($email, '@' . $cur_ban['email'])))) {
            return true;
        }
    }

    return false;
}


/**
 * Send email
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $reply
 * @return bool
 */
function pun_mail($to, $subject, $message, $reply = '')
{
    global $pun_config, $lang_common;

    $sender = str_replace('"', '', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer']);
    $from = '"=?UTF-8?B?' . base64_encode($sender) . '?=" <' . $pun_config['o_webmaster_email'] . '>';

    // Default sender/return address
    if (!$reply) {
        $reply = $from;
    }

    // Do a little spring cleaning
    $to = trim(preg_replace('#[\n\r]+#s', '', $to));
    $subject = trim(preg_replace('#[\n\r]+#s', '', $subject));
    $from = trim(preg_replace('#[\n\r:]+#s', '', $from));
    $reply = trim(preg_replace('#[\n\r:]+#s', '', $reply));

    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $reply . "\r\n" .
        'Date: ' . date('r') . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-transfer-encoding: 8bit' . "\r\n" .
        'Content-type: text/plain; charset=UTF-8' . "\r\n" .
        'X-Mailer: PunBB Mod v' . $pun_config['o_show_version'];

    // Make sure all linebreaks are CRLF in message (and strip out any NULL bytes)
    $message = str_replace(array("\n", "\0"), array("\r\n", ''), pun_linebreaks($message));

    if ($pun_config['o_smtp_host']) {
        return smtp_mail($to, $subject, $message, $headers);
    } else {
        // Change the linebreaks used in the headers according to OS
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'MAC') {
            $headers = str_replace("\r\n", "\r", $headers);
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
            $headers = str_replace("\r\n", "\n", $headers);
        }

        return mail($to, $subject, $message, $headers);
    }
}


/**
 * This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com).
 * They deserve all the credit for writing it. I made small modifications for it to suit PunBB and it's coding standards.
 *
 * @param resource $socket
 * @param string $expected_response
 */
function server_parse($socket, $expected_response)
{
    $server_response = '';
    while (substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) {
            error('Could not get mail server response codes. Please contact the forum administrator.', __FILE__, __LINE__);
        }
    }

    if (!(substr($server_response, 0, 3) == $expected_response)) {
        error('Unable to send e-mail. Please contact the forum administrator with the following error message reported by the SMTP server: "' . $server_response . '"', __FILE__, __LINE__);
    }
}

/**
 * This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com).
 * They deserve all the credit for writing it. I made small modifications for it to suit PunBB and it's coding standards.
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $headers
 * @return bool
 */
function smtp_mail($to, $subject, $message, $headers = '')
{
    global $pun_config;

    $recipients = explode(',', $to);

    $smtp_host = '';
    $smtp_port = 25;
    // tests: https://3v4l.org/lp3EZ
    // ssl://mail.yandex.ru
    // ssl://mail.yandex.ru:25
    // mail.yandex.ru
    // mail.yandex.ru:25
    $smtp = parse_url($pun_config['o_smtp_host']);
    if ($smtp['scheme']) {
        $smtp_host .= $smtp['scheme'] . '://';
    }
    if ($smtp['host']) {
        $smtp_host .= $smtp['host'];
    }
    if (!$smtp_host && $smtp['path']) {
        $smtp_host = $smtp['path'];
    }

    if ($smtp['port']) {
        $smtp_port = $smtp['port'];
    }


    if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15))) {
        error('Could not connect to smtp host "' . $pun_config['o_smtp_host'] . '" (' . $errno . ') (' . $errstr . ')', __FILE__, __LINE__);
    }

    server_parse($socket, '220');

    if ($pun_config['o_smtp_user'] && $pun_config['o_smtp_pass']) {
        fwrite($socket, 'EHLO ' . $_SERVER['SERVER_NAME'] . "\r\n");
        server_parse($socket, '250');

        fwrite($socket, 'AUTH LOGIN' . "\r\n");
        server_parse($socket, '334');

        fwrite($socket, base64_encode($pun_config['o_smtp_user']) . "\r\n");
        server_parse($socket, '334');

        fwrite($socket, base64_encode($pun_config['o_smtp_pass']) . "\r\n");
        server_parse($socket, '235');
    } else {
        fwrite($socket, 'HELO ' . $smtp_host . "\r\n");
        server_parse($socket, '250');
    }

    fwrite($socket, 'MAIL FROM: <' . $pun_config['o_webmaster_email'] . '>' . "\r\n");
    server_parse($socket, '250');

    $to_header = 'To: ';

    @reset($recipients);
    foreach ($recipients as $email) {
        fwrite($socket, 'RCPT TO: <' . $email . '>' . "\r\n");
        server_parse($socket, '250');

        $to_header .= '<' . $email . '>, ';
    }

    fwrite($socket, 'DATA' . "\r\n");
    server_parse($socket, '354');

    fwrite($socket, 'Subject: ' . $subject . "\r\n" . $to_header . "\r\n" . $headers . "\r\n\r\n" . $message . "\r\n");

    fwrite($socket, '.' . "\r\n");
    server_parse($socket, '250');

    fwrite($socket, 'QUIT' . "\r\n");
    fclose($socket);

    return true;
}
