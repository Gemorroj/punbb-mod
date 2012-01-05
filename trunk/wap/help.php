<?php
//define('PUN_HELP', 1);

define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';


if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

// Load the help.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/help.php';

switch(@$_GET['id']) {
    //BBCode    
    case 1:
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187;  '.$lang_help['Help'].' &#187; '.$lang_common['BBCode'];
        require_once PUN_ROOT . 'wap/header.php';
    
        echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <a href="help.php">'.$lang_help['Help'].'</a> &#187; '.$lang_common['BBCode'].'</div>
<div class="con">'.$lang_help['Text style'].'</div>
<div class="msg">
<strong>'.$lang_help['Text style info'].'</strong><br />
[b]'.$lang_help['Bold text'].'[/b] - <strong>'.$lang_help['Bold text'].'</strong>;
<input type="text" value="[b][/b]" size="5" /><br/>
[u]'.$lang_help['Underlined text'].'[/u] - <span class="bbu">'.$lang_help['Underlined text'].'</span>;
<input type="text" value="[u][/u]" size="5" /><br/>
[i]'.$lang_help['Italic text'].'[/i] - <em>'.$lang_help['Italic text'].'</em>;
<input type="text" value="[i][/i]" size="5" /><br/>
[color=#F00]'.$lang_help['Red text'].'[/color] - <span style="color: #f00">'.$lang_help['Red text'].'</span>;
<input type="text" value="[color=#F00][/color]" size="15" /><br/>
[color=blue]'.$lang_help['Blue text'].'[/color] - <span style="color: blue">'.$lang_help['Blue text'].'</span>;
<input type="text" value="[color=blue][/color]" size="15" /><br/>
[hide=0]'.$lang_help['Hide text'].'[/hide] - '.$lang_help['hidden'].'.
<input type="text" value="[hide=0][/hide]" size="11" /></div>
<div class="con">'.$lang_help['Quotes'].'</div>
<div class="msg">
<strong>'.$lang_help['Quotes info'].'</strong><br />
[quote=James]'.$lang_help['Quote text'].'[/quote]
<input type="text" value="[quote=James][/quote]" size="18" /><br />
'.$lang_help['produces quote box'].'<br />
<div class="quote">James '.$lang_common['wrote'].':<br/>
'.$lang_help['Quote text'].'</div>
<strong>'.$lang_help['Quotes info 2'].'</strong><br />
[quote]'.$lang_help['Quote text'].'[/quote]
<input type="text" value="[quote][/quote]" size="11" /><br />
'.$lang_help['produces quote box'].'<br />
<div class="quote">'.$lang_help['Quote text'].'</div></div>
<div class="con">'.$lang_help['Code'].'</div>
<div class="msg">
<strong>'.$lang_help['Code info'].'</strong><br />
[code]'.$lang_help['Code text'].'[/code]
<input type="text" value="[code][/code]" size="10" /><br />
'.$lang_help['produces code box'].'<br />
<div class="code">'.$lang_common['Code'].':<div class="scrollbox" style="height:4.5em"><table class="p_cnt" style="font-family:Courier New;"><tr><td style="width:1pt;"><table><tr><td>1</td></tr></table></td><td><table><tr><td><span>'.$lang_help['Code text'].'</span></td></tr></table></td></tr></table></div></div>
</div>
<div class="con">'.$lang_help['Nested tags'].'</div>
<div class="msg">
<strong>'.$lang_help['Nested tags info'].'</strong><br />
[b][u]'.$lang_help['Bold, underlined text'].'[/u][/b] - <strong><span class="bbu">'.$lang_help['Bold, underlined text'].'</span></strong>
<input type="text" value="[b][u][/u][/b]" size="9" /></div>';
        break;


    //url/images
    case 2:
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187;  '.$lang_help['Help'].' &#187; '.$lang_help['Links and images'];
        require_once PUN_ROOT . 'wap/header.php';

        echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <a href="help.php">'.$lang_help['Help'].'</a> &#187; '.$lang_help['Links and images'].'</div>
<div class="con">
'.$lang_help['Links info'].'</div>
<div class="msg">
[url='.$pun_config['o_base_url'].'/]'.pun_htmlspecialchars($pun_config['o_board_title']).'[/url] - <a href='.$pun_config['o_base_url'].'/>'.pun_htmlspecialchars($pun_config['o_board_title']).'</a>;
<input type="text" value="[url=http://][/url]" size="12" /></div>
<div class="msg2>
[url]'.$pun_config['o_base_url'].'/[/url] - <a href='.$pun_config['o_base_url'].'/>'.$pun_config['o_base_url'].'/</a>;
<input type="text" value="[url]http://[/url]" size="11" /></div>
<div class="msg">
[email]myname@mydomain.com[/email] - <a href="mailto:myname@mydomain.com">myname@mydomain.com</a>;
<input type="text" value="[email]@[/email]" size="14" /></div>
<div class="msg2">
[email=myname@mydomain.com]'.$lang_help['My e-mail address'].'[/email] - <a href="mailto:myname@mydomain.com">'.$lang_help['My e-mail address'].'</a>;
<input type="text" value="[email=@]адрес[/email]" size="19" /></div>
<div class="con">
<a name="img"></a>'.$lang_help['Images info'].'</div>
<div class="msg2">
[img]http://'.$_SERVER['HTTP_HOST'].'/img/punbb.gif[/img] - <img src="'.PUN_ROOT.'img/punbb.gif" alt="img" />
<input type="text" value="[img]http://[/img]" size="15" /></div>';
        break;


    //smilies
    case 3:
        $j = false;

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187;  '.$lang_help['Help'].' &#187; '.$lang_common['Smilies'];
        require_once PUN_ROOT . 'wap/header.php';

        echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <a href="help.php">'.$lang_help['Help'].'</a> &#187; '.$lang_common['Smilies'].'</div>';

        // Display the smiley set
        include_once PUN_ROOT . 'include/parser_wap.php';

        $num_smilies = sizeof($smiley_text);
        for ($i = 0; $i < $num_smilies; ++$i) {
            // Is there a smiley at the current index?
            if (!isset($smiley_text[$i])) {
                continue;
            }

            // Save the current text and image
            $cur_img = $smiley_img[$i];
            $cur_text = $smiley_text[$i];
            //Вывод строк со смайлами
            $msg_class = ($j = !$j) ? 'msg' : 'msg2';    
            echo '<div class="' . $msg_class . '"><img src="' . PUN_ROOT . 'img/smilies/' . $cur_img . '" alt="' . $cur_text . '"/><input type="text" value="' . $smiley_text[$i] . '" size="5" />';

            // Loop through the rest of the array and see if there are any duplicate images
            // (more than one text representation for one image)
            for ($next = $i + 1; $next < $num_smilies; ++$next) {
                // Did we find a dupe?
                if (isset($smiley_img[$next]) && $smiley_img[$i] == $smiley_img[$next]) {
                    echo  ' '.$lang_common['and'] . ' <input type="text" value="' . $smiley_text[$next] . '" size="5" />';

                    // Remove the dupe so we won't display it twice
                    unset($smiley_img[$next]);
                    unset($smiley_text[$next]);
                }
            }

            echo '</div>';
        }
        break;


    //url tag
    case 4:
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187;  '.$lang_help['Help'].' &#187; '.$lang_common['img tag'];
        require_once PUN_ROOT . 'wap/header.php';
        echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; <a href="help.php">'.$lang_help['Help'].'</a> &#187; '.$lang_common['img tag'].'</div>
<div class="con">
<a name="img"></a>'.$lang_help['Images info'].'</div>
<div class="msg2">
[img]http://'.$_SERVER['HTTP_HOST'].'/img/punbb.gif[/img] - <img src="'.PUN_ROOT.'img/punbb.gif" alt="img" />
<input type="text" value="[img]http://[/img]" size="15" /></div>';
        break;
    

    default:
        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_help['Help'];
        require_once PUN_ROOT . 'wap/header.php';

        echo '<div class="inbox"><a href="index.php">'.$lang_common['Index'].'</a> &#187; '.$lang_help['Help'].'</div>
<div class="msg">&#187; <a href="help.php?id=3">'.$lang_common['Smilies'].'</a></div>
<div class="msg2">&#187; <a href="help.php?id=1">'.$lang_common['BBCode'].'</a></div>
<div class="msg">&#187; <a href="help.php?id=2">'.$lang_help['Links and images'].'</a></div>';

        break;
}

require_once PUN_ROOT . 'wap/footer.php';

?>