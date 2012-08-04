{include file='header.tpl'}

{assign var='Delete_messages_comply' value='Delete messages comply'}

<form method="post" action="message_list.php?">
    <div class="input">
        <strong>{$lang_pms.$Delete_messages_comply}</strong><br/>
        <input type="hidden" name="messages" value="{$idlist_str|escape}"/>
        <input type="hidden" name="box" value="{$smarty.post.box}"/></div>
    <div class="go_to">
        <input type="submit" name="delete_messages_comply" value="{$lang_pms.Delete}"/>
    </div>
</form>

{include file='footer.tpl'}