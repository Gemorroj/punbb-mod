{include file='header.tpl'}

{* Change_pass *}
{assign var='Change_pass' value='Change pass'}
{assign var='Change_pass_legend' value='Change pass legend'}
{assign var='Old_pass' value='Old pass'}
{assign var='New_pass' value='New pass'}
{assign var='Confirm_new_pass' value='Confirm new pass'}

{if $id == $pun_user.id or $pun_user.g_id == $smarty.const.PUN_MOD || $pun_user.g_id == $smarty.const.PUN_ADMIN}
{include file='profile.navigation.tpl'}
{/if}

<div class="con">
    <strong>{$lang_profile.$Change_pass}</strong>
</div>

<form method="post" action="profile.php?action=change_pass&amp;id={$id}">
    <div class="input">
        <strong>{$lang_profile.$Change_pass_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
    {if $pun_user.g_id > $smarty.const.PUN_MOD}
        {$lang_profile.$Old_pass}<br/>
        <input type="password" name="req_old_password" maxlength="16"/><br/>
    {/if}
        {$lang_profile.$New_pass}<br/>
        <input type="password" name="req_new_password1" maxlength="16"/><br/>
        {$lang_profile.$Confirm_new_pass}<br/>
        <input type="password" name="req_new_password2" maxlength="16"/>
    </div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}