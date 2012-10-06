{include file='header.tpl'}

{assign var='Private_Messages' value='Private Messages'}
{assign var='Delete_message' value='Delete message'}
{assign var='New_message' value='New message'}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_pms.$Private_Messages}
</div>

<div class="red">
    {$lang_pms.$Delete_message}
</div>

<form method="post" action="message_delete.php?id={$id}">
    <div class="msg">
        <input type="hidden" name="box" value="{intval($smarty.get.box)}"/>
        <input type="hidden" name="p" value="{intval($smarty.get.p)}"/>
    {$lang_pms.Sender}: <strong>{$cur_post.sender|escape}</strong><br/>
    {$cur_post.message}
    </div>
    <div class="go_to">
        <input type="submit" name="delete" value="{$lang_delete.Delete}"/>
    </div>
</form>

{include file='message_list.navlinks.tpl'}

{include file='footer.tpl'}