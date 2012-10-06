{* This template is completed! *}
{include file='header.tpl'}

{assign var='Delete_post' value='Delete post'}

<div class="inbox">
<a href="index.php">{$lang_common.Index}</a>
&#187; <a href="viewforum.php?id={$cur_post.fid}">{$cur_post.forum_name|escape}</a> &#187; {$cur_post.subject|escape}
</div>
<div class="red">{$lang_delete.$Delete_post}</div>
<div class="msg2"><strong>{$lang_delete.Warning}</strong></div>
<form method="post" action="delete.php?id={$id}">
<div class="input">
{$lang_common.Author}: <strong>{$cur_post.poster|escape}</strong><br/>
{$cur_post.message}
</div>
<div class="go_to">
<input type="submit" name="delete" value="{$lang_delete.Delete}"/>
</div>
</form>

{include file='footer.tpl'}