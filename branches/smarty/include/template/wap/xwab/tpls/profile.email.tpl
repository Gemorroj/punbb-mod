{include file='header.tpl'}

{assign var='Change_email' value='Change e-mail'}
{assign var='Email_legend' value='E-mail legend'}
{assign var='New_email' value='New e-mail'}
{assign var='Email_instructions' value='E-mail instructions'}

<div class="con">
    <strong>{$lang_profile.$Change_email}</strong>

    <br/><s>{basename($smarty.const.__FILE__)}</s>

</div>
<form method="post" action="profile.php?action=change_email&amp;id={$id}">
    <div class="input">
        <strong>{$lang_profile.$Email_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        {$lang_profile.$New_email}<br/>
        <input type="text" name="req_new_email" maxlength="50"/><br/>
        {$lang_common.Password}<br/>
        <input type="password" name="req_password" maxlength="16"/><br/>
    {$lang_profile.$Email_instructions}
    </div>
    <div class="go_to">
        <input type="submit" name="new_email" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}