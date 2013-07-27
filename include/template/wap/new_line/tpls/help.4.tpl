{include file='header.tpl'}

{assign var='img_tag' value='img tag'}
{assign var='Images_info' value='Images info'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a href="help.php">{$lang_help.Help}</a>
    &#187; {$lang_common.$img_tag}</div>
<div class="con">
    {$lang_help.$Images_info}
</div>
<div class="msg2">
    [img]{$pun_config.o_base_url}/img/punbb.gif[/img] - <img src="{$pun_config.o_base_url}/img/punbb.gif" alt="img"/>
    <input type="text" value="[img]{$pun_config.o_base_url}/img/punbb.gif[/img]" size="15"/>
</div>

{include file='footer.tpl'}