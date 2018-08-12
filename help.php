<?php
define('PUN_HELP', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
}


// Load the help.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/help.php';


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_help['Help'];
require_once PUN_ROOT . 'header.php';

?>
<h2><?php echo $lang_common['BBCode']; ?></h2>
<div class="box">
    <div class="inbox">
        <p><a name="bbcode"></a><?php echo $lang_help['BBCode info 1']; ?></p><br/>
        <p><?php echo $lang_help['BBCode info 2']; ?></p>
    </div>
</div>
<h2><?php echo $lang_help['Text style']; ?></h2>
<div class="box">
    <p><?php echo $lang_help['Text style info']; ?></p><br/>

    <div style="padding-left: 4px">
        [b]<?php echo $lang_help['Bold text']; ?>[/b] <?php echo $lang_help['produces']; ?> <strong><?php echo $lang_help['Bold text']; ?></strong><br/>
        [u]<?php echo $lang_help['Underlined text']; ?>[/u] <?php echo $lang_help['produces']; ?> <span class="bbu"><?php echo $lang_help['Underlined text']; ?></span><br/>
        [i]<?php echo $lang_help['Italic text']; ?>[/i] <?php echo $lang_help['produces']; ?> <em><?php echo $lang_help['Italic text']; ?></em><br/>
        [color=#FF0000]<?php echo $lang_help['Red text']; ?>[/color] <?php echo $lang_help['produces']; ?> <span style="color: #ff0000"><?php echo $lang_help['Red text']; ?></span><br/>
        [color=blue]<?php echo $lang_help['Blue text']; ?>[/color] <?php echo $lang_help['produces']; ?> <span style="color: blue"><?php echo $lang_help['Blue text']; ?></span><br/>
        [hide]<?php echo $lang_help['Hide text']; ?>[/hide] <?php echo $lang_help['hidden']; ?><br/>
        [hide=1]<?php echo $lang_help['Hide text']; ?>[/hide] <?php echo $lang_help['hidden']; ?>
    </div>
</div>
<h2><?php echo $lang_help['Links and images']; ?></h2>
<div class="box">
    <p><?php echo $lang_help['Links info']; ?></p><br/>

    <div style="padding-left: 4px">
        [url=<?php echo $pun_config['o_base_url'] . '/'; ?>]<?php echo pun_htmlspecialchars($pun_config['o_board_title']); ?>[/url] <?php echo $lang_help['produces']; ?> <a href="<?php echo $pun_config['o_base_url'] . '/'; ?>"><?php echo pun_htmlspecialchars($pun_config['o_board_title']); ?></a><br/>
        [url]<?php echo $pun_config['o_base_url'] . '/'; ?>[/url] <?php echo $lang_help['produces']; ?> <a href="<?php echo $pun_config['o_base_url']; ?>"><?php echo $pun_config['o_base_url'] . '/'; ?></a><br/>
        [email]myname@mydomain.com[/email] <?php echo $lang_help['produces']; ?> <a href="mailto:myname@mydomain.com">myname@mydomain.com</a><br/>
        [email=myname@mydomain.com]<?php echo $lang_help['My e-mail address']; ?>[/email] <?php echo $lang_help['produces']; ?> <a href="mailto:myname@mydomain.com"><?php echo $lang_help['My e-mail address']; ?></a><br/><br/>
    </div>
    <p><a name="img"></a><?php echo $lang_help['Images info']; ?></p>

    <div>[img]<?php echo $pun_config['o_base_url']; ?>/style/img/punbb.gif[/img] <?php echo $lang_help['produces']; ?> <img src="<?php echo $pun_config['o_base_url']; ?>/style/img/punbb.gif" alt="<?php echo $pun_config['o_base_url']; ?>/img/punbb.gif"/></div>
</div>
<h2><?php echo $lang_help['Quotes']; ?></h2>
<div class="box">
    <div style="padding-left: 4px">
        <?php echo $lang_help['Quotes info']; ?><br/><br/>
        &#160; &#160; [quote=James]<?php echo $lang_help['Quote text']; ?>[/quote]<br/><br/>
        <?php echo $lang_help['produces quote box']; ?><br/><br/>

        <div class="postmsg">
            <blockquote>
                <div class="incqbox"><h4>James <?php echo $lang_common['wrote']; ?>:</h4>
                    <p><?php echo $lang_help['Quote text']; ?></p></div>
            </blockquote>
        </div>
        <br/>
        <?php echo $lang_help['Quotes info 2']; ?><br/><br/>
        &#160; &#160; [quote]<?php echo $lang_help['Quote text']; ?>[/quote]<br/><br/>
        <?php echo $lang_help['produces quote box']; ?><br/><br/>

        <div class="postmsg">
            <blockquote>
                <div class="incqbox"><p><?php echo $lang_help['Quote text']; ?></p></div>
            </blockquote>
        </div>
    </div>
</div>
<h2><?php echo $lang_help['Code']; ?></h2>
<div class="box">
    <div style="padding-left: 4px">
        <?php echo $lang_help['Code info']; ?><br/><br/>
        &#160; &#160; [code]<?php echo $lang_help['Code text']; ?>[/code]<br/><br/>
        <?php echo $lang_help['produces code box']; ?><br/><br/>

        <div class="postmsg"><div class="codebox"><div class="incqbox"><h4><?php echo $lang_common['Code']; ?>:</h4><div class="scrollbox" style="height: 4.5em"><table class="p_cnt" style="font-family:'Courier New',monospace;"><tr><td style="width:1pt;"><table><tr><td>1</td></tr></table></td><td><table><tr><td><?php echo $lang_help['Code text']; ?></td></tr></table></td></tr></table></div></div></div></div>
    </div>
</div>
<h2><?php echo $lang_help['Nested tags']; ?></h2>
<div class="box">
    <div style="padding-left: 4px">
        <?php echo $lang_help['Nested tags info']; ?><br/><br/>
        &#160; &#160; [b][u]<?php echo $lang_help['Bold, underlined text']; ?>
        [/u][/b] <?php echo $lang_help['produces']; ?> <span
        class="bbu"><strong><?php echo $lang_help['Bold, underlined text']; ?></strong></span><br/><br/>
    </div>
</div>
<h2><?php echo $lang_common['Smilies']; ?></h2>
<div class="box">
    <div style="padding-left: 4px">
        <a name="smilies"></a><?php echo $lang_help['Smilies info']; ?><br/><br/>
<?php

// Display the smiley set
include_once PUN_ROOT . 'include/parser.php';

$num_smilies = count($smiley_text);
for ($i = 0; $i < $num_smilies; ++$i) {
    // Is there a smiley at the current index?
    if (!isset($smiley_text[$i])) {
        continue;
    }

    echo '&#160; &#160; ' . $smiley_text[$i];

    // Save the current text and image
    $cur_img = $smiley_img[$i];
    $cur_text = $smiley_text[$i];

    // Loop through the rest of the array and see if there are any duplicate images
    // (more than one text representation for one image)
    for ($next = $i + 1; $next < $num_smilies; ++$next) {
        // Did we find a dupe?
        if (isset($smiley_img[$next]) && $smiley_img[$i] == $smiley_img[$next]) {
            echo ' ' . $lang_common['and'] . ' ' . $smiley_text[$next];

            // Remove the dupe so we won't display it twice
            unset($smiley_text[$next], $smiley_img[$next]);
        }
    }

    echo ' ' . $lang_help['produces'] . ' <img src="' . PUN_ROOT . 'img/smilies/' . $cur_img . '" style="width:15px; height:15px;" alt="' . $cur_text . '"/><br/>';
}


echo '<br /></div></div>';


require_once PUN_ROOT . 'footer.php';
