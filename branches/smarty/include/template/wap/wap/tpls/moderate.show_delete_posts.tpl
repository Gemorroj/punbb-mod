{include file='header.tpl'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a
        href="viewforum.php?id={$fid}">{$cur_topic.forum_name|escape}</a> &#187; {$cur_topic.subject|escape}</div>
<form method="post" action="moderate.php?fid={$fid}&amp;tid={$tid}">

{assign var='j' value='false'}
{assign var='Last_edit' value='Last edit'}

{foreach from=$posts item=cur_post}

    {assign var='post_count' value=$post_count+1}

    <div class="{if $j = !$j}msg{else}msg2{/if}">
        <div class="zag_in">
            <a href="viewtopic.php?pid={$cur_post.id}#p{$cur_post.id}">#{($start_from + $post_count)}</a>
            <strong>{if $cur_post.poster_id > 1}<a
                    href="profile.php?id={$cur_post.poster_id}">{$cur_post.poster|escape}</a>{else}{$cur_post.poster|escape}{/if}
            </strong> ({get_title($cur_post)})<br/>
                {$cur_post.posted|date_format:$date_format}<br/>
            IP: {$cur_post.poster_ip}

            {if $start_from + $post_count > 1}
                <br/><span class="grey">{$lang_misc.Select} <input type="checkbox" name="posts[{$cur_post.id}]"
                                                                   value="1"/></span>
            {/if}

        </div>
            {$cur_post.message}<br/>

        {if $cur_post.edited}
            <div class="small">
                {$lang_topic.$Last_edit} {$cur_post.edited_by|escape} ({$cur_post.edited|date_format:$date_format})
            </div>
        {/if}
    </div>
{/foreach}

    <div class="con">{$paging_links}</div>
    <div class="go_to">
        <input type="submit" name="delete_posts" value="{$lang_misc.Delete}"{$button_status} />
    </div>
</form>

{include file='footer.tpl'}