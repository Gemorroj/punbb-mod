{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Delete_posts' value='Delete posts'}
{assign var='Confirm_delete_legend' value='Confirm delete legend'}
{assign var='Delete_posts_comply' value='Delete posts comply'}

<div class="con">
    <strong>{$lang_misc.$Delete_posts}</strong>
</div>
<form method="post" action="moderate.php?fid={$fid}&amp;tid={$tid}">
    <div class="input">
        <strong>{$lang_misc.$Confirm_delete_legend}</strong><br/>
        <input type="hidden" name="posts" value="{implode(',', array_keys($posts))}"/>
    {$lang_misc.$Delete_posts_comply}</div>
    <div class="go_to">
        <input type="submit" name="delete_posts_comply" value="{$lang_misc.Delete}"/>
    </div>
</form>

{/block}