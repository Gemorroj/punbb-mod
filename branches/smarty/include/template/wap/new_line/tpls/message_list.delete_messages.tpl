{include file='header.tpl'}

{assign var='Delete_messages_comply' value='Delete messages comply'}
{assign var='Private_Messages' value='Private Messages'}
{assign var='Delete_message' value='Delete message'}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_pms.$Private_Messages}
</div>

<div class="red">{$lang_pms.Multidelete}</div>

<form method="post" action="message_list.php?">
    <div class="input">
        <strong>{$lang_pms.$Delete_messages_comply}</strong><br/>
        <input type="hidden" name="messages" value="{$idlist_str|escape}"/>
        <input type="hidden" name="box" value="{$smarty.post.box}"/></div>
    <div class="go_to">
        <input type="submit" name="delete_messages_comply" value="{$lang_pms.Delete}"/>
    </div>
</form>

{include file='message_list.navlinks.tpl'}

{include file='footer.tpl'}