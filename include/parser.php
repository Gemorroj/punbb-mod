<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

// Here you can add additional smilies if you like (please note that you must escape singlequote and backslash)
$smiley_text = array(
    ':))',
    ':)',
    '=)',
    ':|',
    '=|',
    ':(',
    '=(',
    ':D',
    '=D',
    ':o',
    ':O',
    ';)',
    ':/',
    ':P',
    ':lol:',
    ':mad:',
    ':rolleyes:',
    ':cool:',
    ':confused:'
);
$smiley_img = array(
    'roll.png',
    'smile.png',
    'smile.png',
    'neutral.png',
    'neutral.png',
    'sad.png',
    'sad.png',
    'big_smile.png',
    'big_smile.png',
    'yikes.png',
    'yikes.png',
    'wink.png',
    'hmm.png',
    'tongue.png',
    'lol.png',
    'mad.png',
    'roll.png',
    'cool.png',
    'confused.png'
);

// Uncomment the next row if you add smilies that contain any of the characters &"'<>
//$smiley_text = array_map('pun_htmlspecialchars', $smiley_text);


//
// Make sure all BBCodes are lower case and do a little cleanup
//
function preparse_bbcode($text, &$errors, $is_signature = false)
{
    global $lang_prof_reg;

    // Change all simple BBCodes to lower case
    // MODERN BBCODE BEGIN
    $text = str_replace(
        array('[B]', '[I]', '[U]', '[/B]', '[/I]', '[/U]', '[S]', '[/S]'),
        array('[b]', '[i]', '[u]', '[/b]', '[/i]', '[/u]', '[s]', '[/s]'),
        $text
    );
    // MODERN BBCODE END

    // Do the more complex BBCodes (also strip excessive whitespace and useless quotes)
    $a = array(
        '#\[url=("|\'|)(.*?)$1\]\s*#i', '#\[url\]\s*#i', '#\s*\[/url\]#i',
        '#\[search=("|\'|)(.*?)$1\]\s*#i', '#\[search\]\s*#i', '#\s*\[/search\]#i',
        '#\[email=("|\'|)(.*?)$1\]\s*#i', '#\[email\]\s*#i', '#\s*\[/email\]#i',
        '#\[img\]\s*(.*?)\s*\[/img\]#is',
        '#\[color=("|\'|)(.*?)$1\](.*?)\[/color\]#is',
        '#\[font=("|\'|)(.*?)$1\](.*?)\[/font\]#is'
    );

    $b = array(
        '[url=$2]', '[url]', '[/url]',
        '[search=$2]', '[search]', '[/search]',
        '[email=$2]', '[email]', '[/email]',
        '[img]$1[/img]',
        '[color=$2]$3[/color]',
        '[font=$2]$3[/font]'
    );

    if (!$is_signature) {
        // For non-signatures, we have to do the quote and code tags as well
        $a[] = '#\[quote=(&quot;|"|\'|)(.*?)$1\]\s*#i';
        $a[] = '#\[quote\]\s*#i';
        $a[] = '#\s*\[/quote\]\s*#i';
        $a[] = '#\[code\][\r\n]*(.*?)\s*\[/code\]\s*#is';
        $a[] = '#\[hide=(&quot;|"|\'|)(.*?)$1\]\s*#i';
        $a[] = '#\[hide\]\s*#i';
        $a[] = '#\s*\[/hide\]\s*#i';

        $b[] = '[quote=$1$2$1]';
        $b[] = '[quote]';
        $b[] = '[/quote]' . "\n";
        $b[] = '[code]$1[/code]' . "\n";
        $b[] = '[hide=$1$2$1]';
        $b[] = '[hide]';
        $b[] = '[/hide]' . "\n";
    }

    // Run this baby!
    $text = preg_replace($a, $b, $text);

    if (!$is_signature) {
        $error = '';
        $overflow = check_tag_order($text, $error);

        if ($error) {
            // A BBCode error was spotted in check_tag_order()
            $errors[] = $error;
        } else if ($overflow) {
            // The quote depth level was too high, so we strip out the inner most quote(s)
            $text = substr($text, 0, $overflow[0]) . substr($text, $overflow[1], (strlen($text) - $overflow[0]));
        }
    } else {
        if (preg_match('#\[quote=(&quot;|"|\'|)(.*)\\1\]|\[quote\]|\[/quote\]|\[code\]|\[/code\]|\[hide=(&quot;|"|\'|)(.*)\\1\]|\[hide\]|\[/hide\]#i', $text)) {
            if (basename(dirname($_SERVER['PHP_SELF'])) == 'wap') {
                wap_message($lang_prof_reg['Signature quote/code']);
            } else {
                message($lang_prof_reg['Signature quote/code']);
            }
        }
    }

    return trim($text);
}


//
// Parse text and make sure that [code] and [quote] syntax is correct
//
function check_tag_order($text, &$error)
{
    global $lang_common;

    // The maximum allowed quote depth
    $max_depth = 3;
    $cur_index = $q_depth = $h_depth = 0;

    while (true) {
        // Look for regular code and quote tags
        $c_start = strpos($text, '[code]');
        $c_end = strpos($text, '[/code]');
        $q_start = strpos($text, '[quote]');
        $q_end = strpos($text, '[/quote]');
        $h_start = strpos($text, '[hide]');
        $h_end = strpos($text, '[/hide]');

        // Look for [quote=username] style quote tags
        if (preg_match('#\[quote=(&quot;|"|\'|)(.*)\\1\]#sU', $text, $matches)) {
            $q2_start = strpos($text, $matches[0]);
        } else {
            $q2_start = 65536;
        }

        // Look for [hide=number] style hide tags
        if (preg_match('#\[hide=(&quot;|"|\'|)(.*)\\1\]#sU', $text, $matches2)) {
            $h2_start = strpos($text, $matches2[0]);
        } else {
            $h2_start = 65536;
        }

        // Deal with strpos() returning false when the string is not found
        // (65536 is one byte longer than the maximum post length)
        if ($c_start === false) {
            $c_start = 65536;
        }
        if ($c_end === false) {
            $c_end = 65536;
        }
        if ($q_start === false) {
            $q_start = 65536;
        }
        if ($q_end === false) {
            $q_end = 65536;
        }
        if ($h_start === false) {
            $h_start = 65536;
        }
        if ($h_end === false) {
            $h_end = 65536;
        }

        // If none of the strings were found
        if (min($c_start, $c_end, $q_start, $q_end, $q2_start, $h_start, $h_end, $h2_start) == 65536) {
            break;
        }

        // We are interested in the first quote (regardless of the type of quote)
        $q3_start = ($q_start < $q2_start) ? $q_start : $q2_start;
        // We are interested in the first hide (regardless of the type of hide)
        $h3_start = ($h_start < $h2_start) ? $h_start : $h2_start;

        // We found a [quote] or a [quote=username]
        if ($q3_start < min($q_end, $c_start, $c_end, $h_start, $h_end)) {
            $step = ($q_start < $q2_start) ? 7 : strlen($matches[0]);

            $cur_index += $q3_start + $step;

            // Did we reach $max_depth?
            if ($q_depth == $max_depth) {
                $overflow_begin = $cur_index - $step;
            }

            ++$q_depth;
            $text = substr($text, $q3_start + $step);
        } else if ($q_end < min($q_start, $c_start, $c_end, $h_start, $h_end)) {
            // We found a [/quote]
            if (!$q_depth) {
                $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 1'];
                return;
            }

            $q_depth--;
            $cur_index += $q_end + 8;

            // Did we reach $max_depth?
            if ($q_depth == $max_depth) {
                $overflow_end = $cur_index;
            }

            $text = substr($text, $q_end + 8);
        } else if ($c_start < min($c_end, $q_start, $q_end, $h_start, $h_end)) {
            // We found a [code]
            // Make sure there's a [/code] and that any new [code] doesn't occur before the end tag
            $tmp = strpos($text, '[/code]');
            $tmp2 = strpos(substr($text, $c_start + 6), '[code]');
            if ($tmp2 !== false) {
                $tmp2 += $c_start + 6;
            }

            if ($tmp === false || ($tmp2 !== false && $tmp2 < $tmp)) {
                $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 2'];
                return;
            } else {
                $text = substr($text, $tmp + 7);
            }

            $cur_index += $tmp + 7;
        } else if ($c_end < min($c_start, $q_start, $q_end, $h_start, $h_end)) {
            // We found a [/code] (this shouldn't happen since we handle both start and end tag in the if clause above)
            $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 3'];
            return;
        }

        // We found a [hide] or a [hide=number]
        if ($h3_start < min($h_end, $c_start, $c_end, $q_start, $q_end)) {
            $step = ($h_start < $h2_start) ? 7 : strlen($matches2[0]);

            $cur_index += $h3_start + $step;

            // Did we reach $max_depth?
            if ($h_depth == $max_depth) {
                $overflow_begin = $cur_index - $step;
            }

            ++$h_depth;
            $text = substr($text, $h3_start + $step);
        } else if ($h_end < min($h_start, $c_start, $c_end, $q_start, $q_end)) {
            // We found a [/hide]
            if (!$h_depth) {
                $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 7'];
                return;
            }

            $h_depth--;
            $cur_index += $h_end + 8;

            // Did we reach $max_depth?
            if ($h_depth == $max_depth) {
                $overflow_end = $cur_index;
            }

            $text = substr($text, $h_end + 8);
        }
    }

    // If $q_depth <> 0 something is wrong with the quote syntax
    if ($q_depth) {
        $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 4'];
        return;
    } else if ($q_depth < 0) {
        $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 5'];
        return;
    }

    // If $h_depth <> 0 something is wrong with the quote syntax
    if ($h_depth) {
        $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 7'];
        return;
    } else if ($h_depth < 0) {
        $error = $lang_common['BBCode error'] . ' ' . $lang_common['BBCode error 8'];
        return;
    }

    // If the quote depth level was higher than $max_depth we return the index for the
    // beginning and end of the part we should strip out
    if (@$overflow_begin) {
        return array($overflow_begin, $overflow_end);
    } else {
        return null;
    }
}


//
// Split text into chunks ($inside contains all text inside $start and $end, and $outside contains all text outside)
//
function split_text($text, $start, $end)
{
    global $pun_config;

    $tokens = explode($start, $text);

    $outside[] = $tokens[0];
    $inside = array();

    $num_tokens = sizeof($tokens);
    for ($i = 1; $i < $num_tokens; ++$i) {
        $temp = explode($end, $tokens[$i]);
        $inside[] = $temp[0];
        $outside[] = $temp[1];
    }

    if ($pun_config['o_indent_num_spaces'] != 8 && $start == '[code]') {
        $spaces = str_repeat(' ', $pun_config['o_indent_num_spaces']);
        $inside = str_replace("\t", $spaces, $inside);
    }

    return array($inside, $outside);
}


//
// Truncate URL if longer than 55 characters (add http:// or ftp:// if missing)
//
function handle_url_tag($url, $link = '')
{
    $full_url = str_replace(
        array(' ', "'", '`', '"'),
        array('%20', '%27', '%60', '%22'),
        $url
    );
    if (strpos($url, 'www.') === 0) { // If it starts with www, we add http://
        $full_url = 'http://' . $full_url;
    } else if (strpos($url, 'ftp.') === 0) { // Else if it starts with ftp, we add ftp://
        $full_url = 'ftp://' . $full_url;
    } else if (!preg_match('#^([a-z0-9]{3,6})://#', $url, $bah)) { // Else if it doesn't start with abcdef://, we add http://
        $full_url = 'http://' . $full_url;
    }

    // Ok, not very pretty :-)
    $link = ($link == '' || $link == $url) ? ((mb_strlen($url) > 55) ? mb_substr($url, 0, 39) . ' &#x2026; ' . mb_substr($url, -10) : $url) : stripslashes($link);

    return '<a href="' . $full_url . '">' . $link . '</a>';
}


//
// Turns [search] tag into <a href=...>
//
function handle_search_tag($where = 'forum', $what = '')
{
    $where = mb_strtolower($where);
    switch ($where) {
        case 'forum':
            $full_url = 'search.php?action=search&amp;keywords=' . urlencode($what);
            break;

        case 'yandex':
            $full_url = 'http://yandex.ru/yandsearch?text=' . urlencode($what);
            break;

        case 'google':
            $full_url = 'http://www.google.com/search?q=' . urlencode($what);
            break;

        case 'baidu':
            $full_url = 'http://www.baidu.com/s?wd=' . urlencode($what);
            break;

        default:
            return '[ERROR SEARCH]';
            break;
    }

    return '<a class="search_tag" title="search ' . $where . '" href="' . $full_url . '">' . $what . '</a>';
}


//
// Turns an URL from the [img], [imgr], [imgl] tags into an <img> tag or a <a href...> tag. ADDED BY MOD: MODERN BBCODE
//
function handle_img_tag_modern($align, $url, $is_signature = false)
{
    global $lang_common, $pun_user;

    $img_tag = '<a href="' . $url . '">&lt;' . $lang_common['Image link'] . '&gt;</a>';

    if ($is_signature && $pun_user['show_img_sig']) {
        $img_tag = '<img class="sigimage" src="' . $url . '" alt="' . htmlspecialchars($url) . '" />';
    } else if (!$is_signature && $pun_user['show_img']) {
        if ($align) {
            $img_tag = '<img class="postimg" style="float: ' . $align . '; clear: ' . $align . '" src="' . $url . '" alt="' . htmlspecialchars($url) . '" />';
        }
    }

    return $img_tag;
}


//
// Turns an URL from the [img] tag into an <img> tag or a <a href...> tag
//
function handle_img_tag($url, $is_signature = false)
{
    global $lang_common, $pun_user;

    $img_tag = '<a href="' . $url . '">&lt;' . $lang_common['Image link'] . '&gt;</a>';

    if ($is_signature && $pun_user['show_img_sig']) {
        $img_tag = '<img class="sigimage" src="' . $url . '" alt="' . htmlspecialchars($url) . '" />';
    } else if (!$is_signature && $pun_user['show_img']) {
        $img_tag = '<img class="postimg" src="' . $url . '" alt="' . htmlspecialchars($url) . '" />';
    }

    return $img_tag;
}

// AJAX POLL MOD BEGIN
function handle_poll_tag($pid)
{
    global $Poll, $lang_poll, $pun_user;

    if (!$Poll) {
        include_once PUN_ROOT . 'include/poll/poll.inc.php';
    }

    $poll_tag = '';
    if ($_SERVER['SCRIPT_NAME'] != '/post.php') {
        $poll_tag = $Poll->showPoll($pid);
    }

    return $poll_tag;
}


function do_bbcode($text)
{
    global $lang_common, $pun_user;
    $wap = pathinfo(dirname($_SERVER['PHP_SELF']), PATHINFO_FILENAME) == 'wap';

    if (strpos($text, 'quote') !== false) {
        if ($wap) {
            $text = str_replace('[quote]', '<div class="quote">', $text);
            $text = preg_replace('#\[quote=(&quot;|"|\'|)(.*)\\1\]#seU', '"<div class=\"quote\"><strong>".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].":</strong><br />"', $text);
            $text = preg_replace('#\[\/quote\]\s*#', '</div>', $text);
        } else {
            $text = str_replace('[quote]', '</p><blockquote><div class="incqbox"><p>', $text);
            $text = preg_replace('#\[quote=(&quot;|"|\'|)(.*)\\1\]#seU', '"</p><blockquote><div class=\"incqbox\"><h4>".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].":</h4><p>"', $text);
            $text = preg_replace('#\[\/quote\]\s*#', '</p></div></blockquote><p>', $text);
        }
    }

    if (strpos($text, 'list') !== false) {
        $text = str_replace('[listo]', '</p><ol>', $text);
        $text = str_replace('[list]', '</p><ul>', $text);
        $text = str_replace('[li]', '<li>', $text);
        $text = str_replace('[/li]', '</li>', $text);
        $text = preg_replace('#\[\/listo\]\s*#', '</ol><p>', $text);
        $text = preg_replace('#\[\/list\]\s*#', '</ul><p>', $text);
    }

    if ($wap) {
        $reArr = array(
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<span class="bbu">$1</span>',
            '<span class="bbs">$1</span>',
            '<code>$1</code>',
            'handle_url_tag(\'$1\')',
            'handle_url_tag(\'$1\', \'$2\')',
            'handle_search_tag(\'forum\', \'$1\')', 'handle_search_tag(\'$1\', \'$2\')',
            '<a href="mailto:$1">$1</a>', '<a href="mailto:$1">$2</a>',
            '<span style="color: $1">$2</span>',
            '<span style="text-align:center;">$1</span>',
            '<span style="text-align:right;">$1</span>',
            '<span style="font-size: $1px">$2</span>',
            '<span style="font-family: $1">$2</span>'
        );
    } else {
        $reArr = array(
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<span class="bbu">$1</span>',
            '<span class="bbs">$1</span>',
            '<code>$1</code>',
            'handle_url_tag(\'$1\')',
            'handle_url_tag(\'$1\', \'$2\')',
            'handle_search_tag(\'forum\', \'$1\')', 'handle_search_tag(\'$1\', \'$2\')',
            '<a href="mailto:$1">$1</a>', '<a href="mailto:$1">$2</a>',
            '<span style="color: $1">$2</span>',
            '</p><p class="center">$1</p><p>',
            '</p><p class="right">$1</p><p>',
            '<span style="font-size: $1px">$2</span>',
            '<span style="font-family: $1">$2</span>'
        );
    }

    // This thing takes a while! :)
    return preg_replace(
        array(
            '#\[b\](.*?)\[/b\]#s',
            '#\[i\](.*?)\[/i\]#s',
            '#\[u\](.*?)\[/u\]#s',
            '#\[s\](.*?)\[/s\]#s',
            '#\[mono\](.*?)\[/mono\]#s',
            '#\[url\]([^\[]*?)\[/url\]#e',
            '#\[url=([^\[]*?)\](.*?)\[/url\]#e',
            '#\[search\]([^\[]*?)\[/search\]#e', '#\[search=([^\[]*?)\](.*?)\[/search\]#e',
            '#\[email\]([^\[]*?)\[/email\]#', '#\[email=([^\[]*?)\](.*?)\[/email\]#',
            '#\[color=([a-zA-Z]*|\#?(?:[0-9a-fA-F]{3}){1,2})](.*?)\[/color\]#s',
            '#\[center\](.*?)\[/center\]#s',
            '#\[right\](.*?)\[/right\]#s',
            '#\[size=([0-9]*)](.*?)\[/size\]#s',
            '#\[font=([a-zA-Z ]*)](.*?)\[/font\]#s'
        ),
        $reArr,
        $text
    );
}

//
// Make hyperlinks clickable
//
function do_clickable($text)
{
    global $pun_user;

    $text = ' ' . $text;
    $text = preg_replace('#([\s\(\)])(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2://$3\')', $text);
    $text = preg_replace('#([\s\(\)])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2.$3\', \'$2.$3\')', $text);

    return substr($text, 1);
}


//
// Convert a series of smilies to images
//
function do_smilies($text)
{
    global $smiley_text, $smiley_img, $pun_config;

    $text = ' ' . $text . ' ';

    $num_smilies = sizeof($smiley_text);
    for ($i = 0; $i < $num_smilies; ++$i) {
        $text = preg_replace("#(?<=.\W|\W.|^\W)" . preg_quote($smiley_text[$i], '#') . "(?=.\W|\W.|\W$)#m", '$1<img src="' . PUN_ROOT . 'img/smilies/' . $smiley_img[$i] . '" alt="' . substr($smiley_img[$i], 0, strrpos($smiley_img[$i], '.')) . '" />$2', $text);
    }

    // ::thumb###:: tag
    return substr(preg_replace('#::thumb([0-9]+)::#', '<a href="' . PUN_ROOT . 'download.php?aid=$1"><img src="' . $pun_config['o_base_url'] . '/' . $pun_config['file_thumb_path'] . '$1-' . $pun_config['file_preview_width'] . 'x' . $pun_config['file_preview_height'] . '.jpg" alt=""/></a>', $text), 1, -1);
}

// hide
function do_hide($text, $post = 0, $matches)
{
    global $pun_user, $lang_topic, $lang_common;

    include_once PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';


    $matches[3] = intval($matches[3]);

    /**
     * Если автор указал [hide=(\d+)][/hide] с параметром,
     * то он ограничил читателя, в возмозможности просматривать скрытый текст,
     * по кол-ву написанных читателем сообщений в форум.
    **/
    if ($pun_user['num_posts'] < $matches[3]) {
        return str_replace($matches[0], str_replace(array('%num_posts%', '%posts%'), array($pun_user['num_posts'], $matches[3]), $lang_common['BBCode posts']), $text);
    }

    $basename = basename($_SERVER['PHP_SELF']);

    if (pathinfo(dirname($_SERVER['PHP_SELF']), PATHINFO_FILENAME) == 'wap') {
        if ($basename == 'hide.php') {
            return str_replace($matches[0], '<div class="attach_list">' . $matches[4] . '</div>', $text);
        }
        return str_replace($matches[0], '<div class="attach_list"><a onclick="window.open(\'hide.php?id=' . $post . '\', \'\', \'width=420,height=230,resizable=yes,scrollbars=yes,status=yes,location=no\'); return false;" target="_blank" href="hide.php?id=' . $post . '">' . $lang_topic['Show'] . '</a></div>', $text);
    }

    if ($basename == 'viewprintable.php') {
        return str_replace($matches[0], '<div class="spoiler" style="display: block;"><strong>' . $lang_topic['Hide'] . '</strong><br/>' . $matches[4] . '</div>', $text);
    }

    return str_replace($matches[0], '<div><input type="button" value="' . $lang_topic['Hide'] . '" onclick="$(this.nextSibling).slideToggle(200);"/><div class="spoiler"><br/>' . $matches[4] . '</div></div>', $text);
}

//
// Parse message text
//
function parse_message($text, $hide_smilies, $post = 0)
{
    global $pun_config, $lang_common, $pun_user;
    $wap = pathinfo(dirname($_SERVER['PHP_SELF']), PATHINFO_FILENAME) == 'wap';

    if ($pun_config['o_censoring'] == 1) {
        $text = censor_words($text);
    }

    // Convert applicable characters to HTML entities
    $text = pun_htmlspecialchars($text);

    // hide
    if (preg_match_all('#\[hide(=(&quot;|"|\'|)+(\d+)){0,1}\](.*)\[/hide\]#seU', $text, $matches)) {
        foreach ($matches[0] as $key => $value) {
            $match = array($value, $matches[1][$key], $matches[2][$key], $matches[3][$key], $matches[4][$key]);
            $text = do_hide($text, $post, $match);
        }
    }


    // If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
    $inside = array();
    if (strpos($text, '[code]') !== false && strpos($text, '[/code]') !== false) {
        list($inside, $outside) = split_text($text, '[code]', '[/code]');
        $outside = array_map('ltrim', $outside);
        $text = implode('<">', $outside);
    }

    if ($pun_config['o_make_links'] == 1) {
        $text = do_clickable($text);
    }

    if ($pun_config['o_smilies'] == 1 && $pun_user['show_smilies'] == 1 && !$hide_smilies) {
        $text = do_smilies($text);
    }

    if ($pun_config['p_message_bbcode'] == 1 && strpos($text, '[') !== false && strpos($text, ']') !== false) {
        $text = do_bbcode($text);

        if ($pun_config['p_message_img_tag'] == 1) {
            $text = preg_replace('#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e', 'handle_img_tag(\'$1$3\')', $text);
            $text = preg_replace('#\[imgr\]((ht|f)tps?://)([^\s<"]*?)\[/imgr\]#e', 'handle_img_tag_modern(\'right\', \'$1$3\')', $text);
            $text = preg_replace('#\[imgl\]((ht|f)tps?://)([^\s<"]*?)\[/imgl\]#e', 'handle_img_tag_modern(\'left\', \'$1$3\')', $text);
        }
    }

    // Deal with newlines, tabs and multiple spaces
    $text = str_replace(array("\n", "\t", '  ', '  ', "\r"), array('<br />', '&#160; &#160; ', '&#160; ', ' &#160;', ''), $text);

    // AJAX POLL MOD BEGIN
    $text = preg_replace('#\[poll\]([0-9]*?)\[/poll\]#e', 'handle_poll_tag(\'$1\')', $text);
    // AJAX POLL MOD END

    // If we split up the message before we have to concatenate it together again (code tags)
    $text = do_code($text, $inside);

    // Add paragraph tag around post, but make sure there are no empty paragraphs
    if ($wap) {
        $text = str_replace('<p></p>', '', $text);
    } else {
        $text = str_replace('<p></p>', '', '<p>' . $text . '</p>');
    }

    return $text;
}


function do_code ($text, $inside = array())
{
    global $pun_config, $lang_common, $pun_user;
    $wap = pathinfo(dirname($_SERVER['PHP_SELF']), PATHINFO_FILENAME) == 'wap';


    // If we split up the message before we have to concatenate it together again (code tags)
    if ($inside) {
        $outside = explode('<">', $text);
        $num_tokens = sizeof($outside);
        $text = '';

        for ($i = 0; $i <= $num_tokens; ++$i) {
            $text .= $outside[$i];

            if (isset($inside[$i])) {
                $num_lines = ((substr_count($inside[$i], "\n")) + 3) * 1.5;
                $height_str = ($num_lines > 35) ? '35em' : $num_lines . 'em';

                if ($inside[$i][0] . $inside[$i][1] . $inside[$i][2] . $inside[$i][3] . $inside[$i][4] === '&lt;?') {
                    $code = str_replace(
                        array(
                            '<code>',
                            '</code>',
                            "\r",
                            "\n",
                            "\t"
                        ),
                        array(
                            '',
                            '',
                            '',
                            '',
                            ''
                        ),
                        // delete the first <span style="color:#000000;"> and the corresponding </span>
                        $str = substr(highlight_string(htmlspecialchars_decode($inside[$i]), true), 35, -8)
                    );
                } else {
                    $code = str_replace(array("\r", "\n", "\t"), '', nl2br($inside[$i]));
                }

                $c = explode('<br />', $code);
                $s = sizeof($c);

                if ($s > 1) {
                    $code = $num_line = '';
                    $span = '<span>';
                    for ($i2 = 0; $i2 < $s; ++$i2) {

                        if ($c[$i2] === '') {
                            $code .= '<tr><td>&#160;</td></tr>';
                        } else {
                            if (substr($c[$i2], 0, 7) === '</span>') {
                                $c[$i2] = substr($c[$i2], 7);
                            }

                            $openSpan = substr_count($c[$i2], '<span');
                            $closeSpan = substr_count($c[$i2], '</span>');
                            if ($openSpan > $closeSpan) {
                                $c[$i2] .= str_repeat('</span>', $openSpan - $closeSpan);
                            } elseif ($closeSpan > $openSpan) {
                                $c[$i2] = str_repeat('<span>', $closeSpan - $openSpan) . $c[$i2];
                            }

                            $code .= '<tr><td>' . $span . $c[$i2] . '</span></td></tr>';

                            if ($preg_span = preg_match('/.*<span style="color: #([a-z0-9]+?)">.*/i', $span . $c[$i2], $array_span)) {
                                $span = '<span style="color: #' . $array_span[$preg_span] . '">';
                            } else {
                                $span = '<span>';
                            }
                        }
                        $num_line .= '<tr><td>' . ($i2 + 1) . '</td></tr>';

                    }
                } else {
                    $code = '<tr><td>' . $code . '</td></tr>';
                    $num_line = '<tr><td>1</td></tr>';
                }

                if ($wap) {
                    $text .= '<div class="code">' . $lang_common['Code'] . ':<br/><table class="p_cnt" style="white-space: pre;font-family:Courier New;font-size:8pt;">' . str_replace('&nbsp;', '&#160;', $code) . '</table></div>';
                } else {
                    $text .= '</p><div class="codebox"><div class="incqbox"><h4>' . $lang_common['Code'] . ':</h4><div class="scrollbox"' . (basename($_SERVER['PHP_SELF']) != 'viewprintable.php' ? ' style="height: ' . $height_str . '"' : '') . '><table class="p_cnt" style="font-family:Courier New;"><tr><td style="width:1pt;"><table>' . $num_line . '</table></td><td><table>' . str_replace('&nbsp;', '&#160;', $code) . '</table></td></tr></table></div></div></div><p>';
                }
            }
        }
    }

    return $text;
}



//
// Parse signature text
//
function parse_signature($text)
{
    global $pun_config, $lang_common, $pun_user;

    if ($pun_config['o_censoring'] == 1) {
        $text = censor_words($text);
    }

    $text = pun_htmlspecialchars($text);

    if ($pun_config['o_make_links'] == 1) {
        $text = do_clickable($text);
    }

    if ($pun_config['o_smilies_sig'] == 1 && $pun_user['show_smilies']) {
        $text = do_smilies($text);
    }

    if ($pun_config['p_sig_bbcode'] && strpos($text, '[') !== false && strpos($text, ']') !== false) {
        $text = do_bbcode($text);

        if ($pun_config['p_sig_img_tag']) {
            $text = preg_replace('#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e', 'handle_img_tag(\'$1$3\', true)', $text);
        }
    }

    // Deal with newlines, tabs and multiple spaces

    return str_replace(array("\n", "\t", '  ', '  '), array('<br />', '&#160; &#160; ', '&#160; ', ' &#160;'), $text);
}
