{include file='header.tpl'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <strong>{$lang_misc.$Delete_topics}</strong></div>
<form method="post" action="moderate.php?fid={$fid}">
    <div class="input">
        <input type="hidden" name="topics" value="{implode(',', array_map('intval', array_keys($topics)))}"/>
        <strong>{$lang_misc.$Confirm_delete_legend}</strong><br/>
    {$lang_misc.$Delete_topics_comply}
    </div>
    <div class="go_to">
        <input type="submit" name="delete_topics_comply" value="{$lang_misc.Delete}"/>
    </div>
</form>

{include file='footer.tpl'}