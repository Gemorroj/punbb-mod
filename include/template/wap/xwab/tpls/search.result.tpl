{include file='header.tpl'}

{assign var='Go_to_post' value='Go to post'}
{assign var='New_posts_info' value='New posts info'}
{assign var='New_posts' value='New posts'}
{assign var='Last_post' value='Last post'}
{assign var='Search_results' value='Search results'}
{assign var='j' value=false}
{assign var='forum' value=''}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_search.$Search_results}
</div>

{foreach from=$search_set item=searchResult}
    {foreach from=$forum_list item=i}
        {if $i[0] == $searchResult.forum_id}
            {assign var='temp' value=$i}
        {/if}
    {/foreach}

    {if $show_as == 'posts'}
        <div class="in">
            {* Forum *}
            <a href="viewforum.php?id={$temp[0]}">{$temp[1]|escape}</a>
            &#187; <a href="viewtopic.php?id={$searchResult.tid}">{$searchResult.subject|escape}</a>
            &#187; <a class="small" href="viewtopic.php?pid={$searchResult.pid}#p{$searchResult.pid}">{$searchResult.pposted|date_format:$date_format}</a>
        </div>
        <div class="msg">
            {* Harry Poster :) *}
            {if $searchResult.poster_id > 1}
                <strong><a href="profile.php?id={$searchResult.poster_id}">{$searchResult.pposter|escape}</a></strong>
            {else}
                {$searchResult.pposter|escape}
            {/if}
            <br/>
            <span class="sub">{$lang_search.Replies}: {$searchResult.num_replies} | <a href="viewtopic.php?pid={$searchResult.pid}#p{$searchResult.pid}">{$lang_search.$Go_to_post}</a></span><br/>
            {* Message*}
            {$searchResult.message} &#x2026;
        </div>
    {else}
        <div class="{if $j = ! $j}msg{else}msg2{/if}">
            {* $forum *}
            <a href="viewforum.php?id={$temp[0]}">{$temp[1]|escape}</a>
            &#187; {* $subject *}
            {if ! $pun_user.is_guest && $searchResult.last_post > $pun_user.last_visit}
                <strong><a href="viewtopic.php?id={$searchResult.tid}">{$searchResult.subject|escape}</a> ({$searchResult.poster|escape})</strong>
            {else}
                <a href="viewtopic.php?id={$searchResult.tid}">{$searchResult.subject|escape}</a> ({$searchResult.poster|escape})
            {/if}

            {if ! $pun_user.is_guest && $searchResult.last_post > $pun_user.last_visit}
                <a class="red" href="viewtopic.php?id={$searchResult.tid}&amp;action=new" title="{$lang_common.$New_posts_info}">{$lang_common.$New_posts}</a>
            {/if}

            {assign var='num_pages_topic' value=ceil(($searchResult.num_replies + 1) / $pun_user.disp_posts)}

            {if $num_pages_topic > 1}
                [ {paginate($num_pages_topic, -1, "viewtopic.php?id={$searchResult.tid}", 0)} ]
            {/if}
            <br/>
        <span class="sub">{$lang_common.Replies}: {$searchResult.num_replies}<br/>
        <a href="viewtopic.php?pid={$searchResult.last_post_id}#p{$searchResult.last_post_id}">{$lang_common.$Last_post}</a>:
            {$searchResult.last_poster|escape} ({$searchResult.last_post|date_format:$date_format})</span>
        </div>
    {/if}
{/foreach}

<div class="con">{$lang_common.Pages}: {$paging_links}</div>

{include file='footer.tpl'}