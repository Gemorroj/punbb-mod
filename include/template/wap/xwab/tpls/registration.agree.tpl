{include file='header.tpl'}

{assign var='Forum_rules' value='Forum rules'}
{assign var='Rules_legend' value='Rules legend'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <strong>{$lang_registration.$Forum_rules}</strong>
</div>
<div class="info">{$lang_registration.$Rules_legend}</div>
<form method="get" action="registration.php?">
    <div class="input">
    {$pun_config.o_rules_message}</div>
    <div class="go_to">
        <input type="submit" name="agree" value="{$lang_registration.Agree}"/>
        <input type="submit" name="cancel" value="{$lang_registration.Cancel}"/>
    </div>
</form>

{include file='footer.tpl'}