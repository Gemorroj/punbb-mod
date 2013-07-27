{include file='header.tpl'}
{* This template is completed! *}
<div class="inbox">
<a href="index.php">{$lang_common.Index}</a> &#187; <strong>{$lang_common.Login}</strong>
</div>
<form method="post" action="login.php?action=in">
<div class="input">
{assign var='Login_legend'   value='Login legend'}
<strong>{$lang_login.$Login_legend}</strong><br/>
<input type="hidden" name="form_sent" value="1"/>
<input type="hidden" name="redirect_url" value="{$redirect_url}"/>
<strong>{$lang_common.Username}</strong><br/>
<input type="text" name="req_username" maxlength="25" /><br/>
<strong>{$lang_common.Password}</strong><br/>
<input type="password" name="req_password" maxlength="16" />
</div>
<div class="go_to">
<input type="submit" name="login" value="{$lang_common.Login}" />
</div>
</form>
{assign var='Not_registered' value='Not registered'}
<div class="in2"> &#187; <a href="register.php">{$lang_login.$Not_registered}</a></div>
{assign var='Forgotten_pass' value='Forgotten pass'}
<div class="in"> &#187; <a href="login.php?action=forget">{$lang_login.$Forgotten_pass}</a></div>

{include file='footer.tpl'}