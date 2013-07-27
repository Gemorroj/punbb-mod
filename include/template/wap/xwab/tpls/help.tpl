{include file='header.tpl'}

{assign var='Links_and_images' value='Links and images'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; {$lang_help.Help}
</div>

<div class="msg">&#187; <a href="help.php?id=3">{$lang_common.Smilies}</a></div>
<div class="msg2">&#187; <a href="help.php?id=1">{$lang_common.BBCode}</a></div>
<div class="msg">&#187; <a href="help.php?id=2">{$lang_help.$Links_and_images}</a></div>

{include file='footer.tpl'}