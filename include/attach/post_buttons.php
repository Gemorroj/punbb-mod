<?php
/***********************************************************************

Show buttons above edit and post form.
This file is part of Elektra File Upload mod for PunBB.

Copyright (C) 2002-2005 Rickard Andersson (rickard@punbb.org)
Copyright (C) 2007 artoodetoo (master@1wd.ru)

Included from: edit.php, post.php

Incoming variables;

Outgoing variables:
$attachments: array - cache of attachments records
 ************************************************************************/

$jsHelper->add(PUN_ROOT . 'js/reply.js');
$jsHelper->addInternal('
var post_form = \'' . $focus_element[0] . '\';
var txt	 = \'\';
var text_enter_title = \'' . $lang_fu['JS enter title'] . '\';
var text_enter_url = \'' . $lang_fu['JS enter url'] . '\';
var text_enter_url_name = \'' . $lang_fu['JS enter url name'] . '\';
var text_enter_image = \'' . $lang_fu['JS enter image'] . '\';
var text_enter_email = \'' . $lang_fu['JS enter email'] . '\';
var error_no_url = \'' . $lang_fu['JS no url'] . '\';
var error_no_title = \'' . $lang_fu['JS no title'] . '\';
var error_no_email = \'' . $lang_fu['JS no email'] . '\';
');
?>
<div id="buttonmenu">
    <ul>
        <li><a href="#" onclick="return insert_text('[b]', '[/b]');"><strong>B</strong></a></li>
        <li><a href="#" onclick="return insert_text('[i]', '[/i]');"><em>I</em></a></li>
        <li><a href="#" onclick="return insert_text('[u]', '[/u]');"><span class="bbu">U</span></a></li>
        <li><a href="#" onclick="return insert_text('[s]', '[/s]');"><span class="bbs">S</span></a></li>
        <li><a href="#" onclick="return insert_text('[left]', '[/left]' );">Left</a></li>
        <li><a href="#" onclick="return insert_text('[center]', '[/center]');">Center</a></li>
        <li><a href="#" onclick="return insert_text('[right]', '[/right]');">Right</a></li>
        <li><a href="#" onclick="return false;">URL</a>
            <ul id="url">
                <li><a href="#" onclick="return tag_url();">URL</a></li>
                <li><a href="#" onclick="return insert_text('[url]', '[/url]');">[url][/url]</a></li>
            </ul>
        </li>
        <li><a href="#" onclick="return false;">Email</a>
            <ul id="email">
                <li><a href="#" onclick="return tag_email();">Email</a></li>
                <li><a href="#" onclick="return insert_text('[email]', '[/email]');">[email][/email]</a></li>
            </ul>
        </li>
        <li><a href="#" onclick="return false;">Image</a>
            <ul id="image">
                <li><a href="#" onclick="return tag_image();">Image</a></li>
                <li><a href="#" onclick="return insert_text('[img]', '[/img]');">[img][/img]</a></li>
            </ul>
        </li>
        <li><a href="#" onclick="return insert_text('[quote]', '[/quote]');">Quote</a></li>
        <li><a href="#" onclick="return insert_text('[hide]', '[/hide]');">Hide</a></li>
        <li><a href="#" onclick="return insert_text('[code]', '[/code]');">Code</a></li>
        <li><a href="#" onclick="return false;">Color</a>
            <ul id="colorcontent">
                <li>
                    <a href="#" style="background:#000" title="Black"
                       onclick="return insert_text('[color=#000]','[/color]');"></a>
                    <a href="#" style="background:#930" title="Brown"
                       onclick="return insert_text('[color=#930]','[/color]');"></a>
                    <a href="#" style="background:#330" title="Olive Green"
                       onclick="return insert_text('[color=#330]','[/color]');"></a>
                    <a href="#" style="background:#030" title="Dark Green"
                       onclick="return insert_text('[color=#030]','[/color]');"></a>
                    <a href="#" style="background:#036" title="Dark Teal"
                       onclick="return insert_text('[color=#036]','[/color]');"></a>
                    <a href="#" style="background:#008" title="Dark Blue"
                       onclick="return insert_text('[color=#008]','[/color]');"></a>
                    <a href="#" style="background:#339" title="Indigo"
                       onclick="return insert_text('[color=#339]','[/color]');"></a>
                    <a href="#" style="background:#333" title="Gray-80%"
                       onclick="return insert_text('[color=#333]','[/color]');"></a>
                    <a href="#" style="background:#800" title="Dark Red"
                       onclick="return insert_text('[color=#800]','[/color]');"></a>
                    <a href="#" style="background:#f60" title="Orange"
                       onclick="return insert_text('[color=#F60]','[/color]');"></a>
                    <a href="#" style="background:#880" title="Dark Yellow"
                       onclick="return insert_text('[color=#880]','[/color]');"></a>
                    <a href="#" style="background:#080" title="Green"
                       onclick="return insert_text('[color=#080]','[/color]');"></a>
                    <a href="#" style="background:#088" title="Teal"
                       onclick="return insert_text('[color=#088]','[/color]');"></a>
                    <a href="#" style="background:#00f" title="Blue"
                       onclick="return insert_text('[color=#00f]','[/color]');"></a>
                    <a href="#" style="background:#669" title="Blue-Gray"
                       onclick="return insert_text('[color=#669]','[/color]');"></a>
                    <a href="#" style="background:#888" title="Gray-50%"
                       onclick="return insert_text('[color=#888]','[/color]');"></a>
                    <a href="#" style="background:#f00" title="Red"
                       onclick="return insert_text('[color=#f00]','[/color]');"></a>
                    <a href="#" style="background:#f90" title="Light Orange"
                       onclick="return insert_text('[color=#F90]','[/color]');"></a>
                    <a href="#" style="background:#9c0" title="Lime"
                       onclick="return insert_text('[color=#9C0]','[/color]');"></a>
                    <a href="#" style="background:#396" title="Sea Green"
                       onclick="return insert_text('[color=#396]','[/color]');"></a>
                    <a href="#" style="background:#3cc" title="Aqua"
                       onclick="return insert_text('[color=#3CC]','[/color]');"></a>
                    <a href="#" style="background:#36f" title="Light Blue"
                       onclick="return insert_text('[color=#36F]','[/color]');"></a>
                    <a href="#" style="background:#808" title="Violet"
                       onclick="return insert_text('[color=#808]','[/color]');"></a>
                    <a href="#" style="background:#aaa" title="Gray-40%"
                       onclick="return insert_text('[color=#aaa]','[/color]');"></a>
                    <a href="#" style="background:#f0f" title="Pink"
                       onclick="return insert_text('[color=#F0F]','[/color]');"></a>
                    <a href="#" style="background:#fc0" title="Gold"
                       onclick="return insert_text('[color=#FC0]','[/color]');"></a>
                    <a href="#" style="background:#ff0" title="Yellow"
                       onclick="return insert_text('[color=#FF0]','[/color]');"></a>
                    <a href="#" style="background:#0f0" title="Bright Green"
                       onclick="return insert_text('[color=#0F0]','[/color]');"></a>
                    <a href="#" style="background:#0ff" title="Turquoise"
                       onclick="return insert_text('[color=#0FF]','[/color]');"></a>
                    <a href="#" style="background:#0cf" title="Sky Blue"
                       onclick="return insert_text('[color=#0CF]','[/color]');"></a>
                    <a href="#" style="background:#936" title="Plum"
                       onclick="return insert_text('[color=#936]','[/color]');"></a>
                    <a href="#" style="background:#ccc" title="Gray-25%"
                       onclick="return insert_text('[color=#CCC]','[/color]');"></a>
                    <a href="#" style="background:#f9c" title="Rose"
                       onclick="return insert_text('[color=#F9C]','[/color]');"></a>
                    <a href="#" style="background:#fc9" title="Tan"
                       onclick="return insert_text('[color=#FC9]','[/color]');"></a>
                    <a href="#" style="background:#ff9" title="Light Yellow"
                       onclick="return insert_text('[color=#FF9]','[/color]');"></a>
                    <a href="#" style="background:#cfc" title="Light Green"
                       onclick="return insert_text('[color=#CFC]','[/color]');"></a>
                    <a href="#" style="background:#cff" title="Light Turquoise"
                       onclick="return insert_text('[color=#CFF]','[/color]');"></a>
                    <a href="#" style="background:#9cf" title="Pale Blue"
                       onclick="return insert_text('[color=#9CF]','[/color]');"></a>
                    <a href="#" style="background:#c9f" title="Lavender"
                       onclick="return insert_text('[color=#C9F]','[/color]');"></a>
                    <a href="#" style="background:#fff" title="White"
                       onclick="return insert_text('[color=#fff]','[/color]');"></a>
                </li>
            </ul>
        </li>
        <li><a href="#" onclick="return false;">Font</a>
            <ul>
                <li><a href="#" style="font-family:Arial"
                       onclick="return insert_text('[font=Arial]', '[/font]');">Arial</a></li>
                <li><a href="#" style="font-family:Arial Black"
                       onclick="return insert_text('[font=Arial Black]', '[/font]');">Arial Black</a></li>
                <li><a href="#" style="font-family:Arial Narrow"
                       onclick="return insert_text('[font=Arial Narrow]', '[/font]');">Arial Narrow</a></li>
                <li><a href="#" style="font-family:Century Gothic"
                       onclick="return insert_text('[font=Century Gothic]', '[/font]');">Century Gothic</a></li>
                <li><a href="#" style="font-family:Courier New"
                       onclick="return insert_text('[font=Courier New]', '[/font]');">Courier New</a></li>
                <li><a href="#" style="font-family:Garamond"
                       onclick="return insert_text('[font=Garamond]', '[/font]');">Garamond</a></li>
                <li><a href="#" style="font-family:Georgia" onclick="return insert_text('[font=Georgia]', '[/font]');">Georgia</a>
                </li>
                <li><a href="#" style="font-family:Impact" onclick="return insert_text('[font=Impact]', '[/font]');">Impact</a>
                </li>
                <li><a href="#" style="font-family:Microsoft Sans Serif"
                       onclick="return insert_text('[font=Microsoft Sans Serif]', '[/font]');">Microsoft Sans Serif</a>
                </li>
                <li><a href="#" style="font-family:Palatino Linotype"
                       onclick="return insert_text('[font=Palatino Linotype]', '[/font]');">Palatino Linotype</a></li>
                <li><a href="#" style="font-family:Tahoma" onclick="return insert_text('[font=Tahoma]', '[/font]');">Tahoma</a>
                </li>
                <li><a href="#" style="font-family:Times New Roman"
                       onclick="return insert_text('[font=Times New Roman]', '[/font]');">Times New Roman</a></li>
                <li><a href="#" style="font-family:Verdana" onclick="return insert_text('[font=Verdana]', '[/font]');">Verdana</a>
                </li>
            </ul>
        </li>
        <li><a href="#" onclick="return false;">Size</a>
            <ul>
                <li><a style="font-size:8px" href="#" onclick="return insert_text('[size=8]', '[/size]');">8px</a></li>
                <li><a style="font-size:10px" href="#" onclick="return insert_text('[size=10]', '[/size]');">10px</a>
                </li>
                <li><a style="font-size:12px" href="#" onclick="return insert_text('[size=12]', '[/size]');">12px</a>
                </li>
                <li><a style="font-size:14px" href="#" onclick="return insert_text('[size=14]', '[/size]');">14px</a>
                </li>
                <li><a style="font-size:16px" href="#" onclick="return insert_text('[size=16]', '[/size]');">16px</a>
                </li>
                <li><a style="font-size:18px" href="#" onclick="return insert_text('[size=18]', '[/size]');">18px</a>
                </li>
                <li><a style="font-size:20px" href="#" onclick="return insert_text('[size=20]', '[/size]');">20px</a>
                </li>
            </ul>
        </li>
        <li><a href="#" onclick="return false;">/</a>
            <ul>
                <li><a id="dectxt" href="javascript:resizeTextarea(-100)">-</a></li>
                <li><a id="inctxt" href="javascript:resizeTextarea(100)">+</a></li>
            </ul>
        </li>
    </ul>
</div>
<div class="clearer"></div>
<div id="smilies-area">
    <div style="float: left;">
        <?php
        include_once PUN_ROOT . 'include/parser.php';

        foreach (array_combine($smiley_img, $smiley_text) as $k => $v) {
            echo '<img src="' . PUN_ROOT . 'img/smilies/' . $k . '" alt="' . $v . '" style="cursor: pointer" onclick="return insert_text(\' ' . $v . ' \', \'\');"/> ';
        }
        ?>
    </div>
</div><br class="clearb"/>