{include file='header.tpl'}

{assign var='Send_email_to' value='Send e-mail to'}
{assign var='Write_email' value='Write e-mail'}
{assign var='Email_subject' value='E-mail subject'}
{assign var='Email_message' value='E-mail message'}
{assign var='Email_disclosure_note' value='E-mail disclosure note'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_misc.$Send_email_to}
</div>
<div class="con">{$lang_misc.$Send_email_to} <strong>{$recipient|escape}</strong></div>
<form method="post" action="misc.php?email={$recipient_id}">
    <div class="input">
        <strong>{$lang_misc.$Write_email}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        <input type="hidden" name="redirect_url" value="{$redirect_url}"/>
        {$lang_misc.$Email_subject}<br/>
        <input type="text" name="req_subject" maxlength="70"/><br/>
        {$lang_misc.$Email_message}<br/>
        <textarea name="req_message" rows="4" cols="24"></textarea><br/>
        {$lang_misc.$Email_disclosure_note}</div>
    <div class="go_to">
        <input type="submit" name="submit" value="{$lang_common.Submit}" accesskey="s"/>
    </div>
</form>

{include file='footer.tpl'}