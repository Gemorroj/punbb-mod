{include file='header.tpl'}

{assign var='Options_PM' value='Options PM'}
{assign var='Use_popup' value='Use popup'}
{assign var='Use_messages' value='Use messages'}
{assign var='Private_Messages' value='Private Messages'}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_pms.$Private_Messages}
</div>

<div class="con">
    <strong>{$name}</strong>
</div>

<form method="post" action="message_list.php?box=2">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>
        <strong>{$lang_pms.$Options_PM}</strong><br/>
        <label><input type="checkbox" name="popup_enable" value="1"{if $user.popup_enable == 1} checked="checked"{/if} />{$lang_pms.$Use_popup}</label><br/>
        <label><input type="checkbox" name="messages_enable" value="1"{if $user.messages_enable == 1} checked="checked"{/if} />{$lang_pms.$Use_messages}</label>
    </div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_pms.Send}"/>
    </div>
</form>

{include file='message_list.navlinks.tpl'}

{include file='footer.tpl'}