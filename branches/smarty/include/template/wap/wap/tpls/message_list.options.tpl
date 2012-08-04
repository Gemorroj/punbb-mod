{include file='header.tpl'}

{assign var='Options_PM' value='Options PM'}
{assign var='Use_popup' value='Use popup'}
{assign var='Use_messages' value='Use messages'}
{assign var='' value=''}

<form method="post" action="message_list.php?box=2">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>
        <strong>{$lang_pms.$Options_PM}</strong><br/>
        <input type="checkbox" name="popup_enable" value="1"{if $user.popup_enable == 1}
               checked="checked"{/if} />{$lang_pms.$Use_popup}<br/>
        <input type="checkbox" name="messages_enable" value="1"{if $user.messages_enable == 1}
               checked="checked"{/if} />{$lang_pms.$Use_messages}
    </div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_pms.Send}"/>
    </div>
</form>

{include file='message_list.navlinks.tpl'}

{include file='footer.tpl'}