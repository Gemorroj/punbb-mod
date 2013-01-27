{include file='header.tpl'}

{assign var='Links_and_images' value='Links and images'}
{assign var='Links_info' value='Links info'}
{assign var='Images_info' value='Images info'}
{assign var='My_email_address' value='My e-mail address'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a href="help.php">{$lang_help.Help}</a>
    &#187; {$lang_help.$Links_and_images}
</div>
<div class="con">
    {$lang_help.$Links_info}
</div>
<div class="msg">
    [url={$pun_config.o_base_url}/]{$pun_config.o_board_title|escape}[/url] - <a href="{$pun_config.o_base_url}">{$pun_config.o_board_title|escape}</a>
    <input type="text" value="[url={$pun_config.o_base_url}/][/url]" size="12"/>
</div>
<div class="msg2">
    [url]{$pun_config.o_base_url}/[/url] - <a href="{$pun_config.o_base_url}">{$pun_config.o_base_url}/</a>
    <input type=" text" value="[url]{$pun_config.o_base_url}/[/url]" size="11" />
</div>
<div class="msg">
    [email]myname@mydomain.com[/email] - <a href="mailto:myname@mydomain.com">myname@mydomain.com</a>
    <input type="text" value="[email]myname@mydomain.com[/email]" size="14"/>
</div>
<div class="msg2">
    [email=myname@mydomain.com]{$lang_help.$My_email_address}[/email] - <a href="mailto:myname@mydomain.com">{$lang_help.$My_email_address}</a>
    <input type="text" value="[email=myname@mydomain.com]{$lang_help.$My_email_address}[/email]" size="19"/>
</div>
<div class="con">
    {$lang_help.$Images_info}
</div>
<div class="msg2">
    [img]{$pun_config.o_base_url}/img/punbb.gif[/img] - <img src="{$pun_config.o_base_url}/img/punbb.gif" alt="img"/>
    <input type="text" value="[img]{$pun_config.o_base_url}/img/punbb.gif[/img]" size="15"/>
</div>

{include file='footer.tpl'}