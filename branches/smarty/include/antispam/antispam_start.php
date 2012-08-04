<?php
function generate_spam_regexp_cache()
{
    global $db;

    // Get the ban list from the DB
    $result = $db->query('SELECT `id`, `regexpr` FROM ' . $db->prefix . 'spam_regexp', true) or error('Unable to fetch spam_regexp', __FILE__, __LINE__, $db->error());

    $output = array();
    while ($cur_regexp = $db->fetch_assoc($result)) {
        $output[$cur_regexp['id']] = $cur_regexp['regexpr'];
    }

    // Output ban list as PHP code
    $fh = @fopen(PUN_ROOT . 'cache/cache_spam_regexp.php', 'wb');
    if (!$fh) {
        error('Unable to write bans cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
    }

    fwrite($fh, '<?php' . "\n\n" . 'define(\'PUN_SPAM_REGEXP_LOADED\', 1);' . "\n\n" . '$spam_regexp = ' . var_export($output, true) . ';' . "\n\n" . '?>');
    fclose($fh);
}


function check_spam($text)
{
    global $spam_regexp;
    $spam = array();

    $text = mb_strtolower($text);


    foreach ($spam_regexp as $key => $value) {
        if (preg_match($value, $text, $matches)) {
            $spam['regexpr'] = $matches[0];
            $spam['id'] = $key;

            break;
        }
    }

    return $spam;
}

$is_spam = false;
if ($pun_user['num_posts'] < 10) {
    // Load cached spam regexp
    @include PUN_ROOT . 'cache/cache_spam_regexp.php';
    if (!defined('PUN_SPAM_REGEXP_LOADED')) {
        include PUN_ROOT . 'include/cache.php';
        generate_spam_regexp_cache();
        include PUN_ROOT . 'cache/cache_spam_regexp.php';
    }


    $checkedspam = check_spam($message);
    if ($checkedspam) {
        $original_message = $message;
        //$pattern = $checkedspam['regexpr'];
        // Заменить сообщения на устанавливаемое из админ-центра

        $message = 'Данное сообщение было расценено как спам';
        if (!$pun_user['is_guest']) {
            $message .= ' Автор сообщения временно переведен в группу "спамеры", до выяснения обстоятельств.';
        } else {
            $message .= ' IP-адрес автора сообщения записан в логах и отправлен в КГБ. Автору сообщения предлагается оставаться на месте, сейчас за вами приедут.';
        }

        $message .= "\n" . '[mono][right]Это сообщение было сгенерировано автоматически[/right][/mono]';
        $is_spam = true;
    }
}
?>