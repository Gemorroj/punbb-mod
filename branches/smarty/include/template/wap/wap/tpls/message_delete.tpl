{include file='header.tpl'}

{assign var='Delete_message' value='Delete message'}
{assign var='New_message' value='New message'}

<div class="red">{$lang_pms.$Delete_message}</div>
<form method="post" action="message_delete.php?id={$id}">
<div class="msg">
<input type="hidden" name="box" value="{intval($smarty.get.box)}"/>
<input type="hidden" name="p" value="{intval($smarty.get.p)}"/>
{$lang_pms.Sender}: <strong>{$cur_post.sender|escape}</strong><br/>
{$cur_post.message}
</div>
<div class="go_to">
<input type="submit" name="delete" value="{$lang_delete.Delete}" />
</div>
</form>
<div class="navlinks">
<a href="message_list.php?box=0">{$lang_pms.Inbox}</a> |
<a href="message_list.php?box=1">{$lang_pms.Outbox}</a> |
<a href="message_list.php?box=2">{$lang_pms.Options}</a> |
<a href="message_send.php">{$lang_pms.$New_message}</a></div>

{include file='footer.tpl'}