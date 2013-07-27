{assign var='New_message' value='New message'}
<div class="navlinks">
    <a href="message_list.php?box=0">{$lang_pms.Inbox}</a> |
    <a href="message_list.php?box=1">{$lang_pms.Outbox}</a> |
    <a href="message_list.php?box=2">{$lang_pms.Options}</a> |
    <a href="message_send.php">{$lang_pms.$New_message}</a>
</div>