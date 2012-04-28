{include file='header.tpl'}

{assign var='Confirm_delete_user' value='Confirm delete user'}
{assign var='Confirm_delete_legend' value='Confirm delete legend'}
{assign var='Confirmation_info' value='Confirmation info'}
{assign var='Delete_warning' value='Delete warning'}
{assign var='Delete_posts' value='Delete posts'}

<div class="con">
    <strong>{$lang_profile.$Confirm_delete_user}</strong>

<br/><s>{basename($smarty.const.__FILE__)}</s>

</div>
<form method="post" action="profile.php?id={$id}">
<div class="input">
    <strong>{$lang_profile.$Confirm_delete_legend}</strong>
</div>
<div class="input2">
{$lang_profile.$Confirmation_info} <strong>{$username|escape}</strong>.<br/>
<input type="checkbox" name="delete_posts" value="1" checked="checked" />{$lang_profile.$Delete_posts}
</div>
<div class="input2">
<strong>{$lang_profile.$Delete_warning}</strong>
</div>
<div class="go_to">
<input type="submit" name="delete_user_comply" value="{$lang_profile.Delete}" />
</div>
</form>

{include file='footer.tpl'}