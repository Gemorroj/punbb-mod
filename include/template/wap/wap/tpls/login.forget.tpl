{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Request_pass' value='Request pass'}
{assign var='Request_pass_legend' value='Request pass legend'}
{assign var='Request_pass_info' value='Request pass info'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <strong>{$lang_login.$Request_pass}</strong>
</div>
<form method="post" action="login.php?action=forget_2">
    <div class="input">
        <strong>{$lang_login.$Request_pass_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        <input type="text" name="req_email" maxlength="50"/><br/>
    {$lang_login.$Request_pass_info}
    </div>
    <div class="go_to">
        <input type="submit" name="request_pass" value="{$lang_common.Submit}"/>
    </div>
</form>

{/block}