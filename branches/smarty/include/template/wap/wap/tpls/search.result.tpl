{include file='header.tpl'}



{assign var='j' value='false'}
{assign var='forum' value=''}

{assign var='Go_to_post' value='Go to post'}
{assign var='New_posts_info' value='New posts info'}
{assign var='New_posts' value='New posts'}
{assign var='Last_post' value='Last post'}

{foreach from=$search_set item=searchResult}
    {foreach from=$forum_list item=i}
        {if $i[0] == $searchResult.forum_id}
            {assign var='temp' value=$i}
        {/if}
    {/foreach}
    
    {if $pun_config.o_censoring == 1}
        {assign var='sbj' value=censor_words($searchResult.subject)}
    {else}
        {assign var='sbj' value=$searchResult.subject}
    {/if}
    
    {if $show_as == 'posts'}
        <div class="in">
        {* Forum *}
        <a href="viewforum.php?id={$temp[0]}">{$temp[1]|escape}</a>
        &#187; <a href="viewtopic.php?id={$searchResult.tid}">{$sbj|escape}</a>
        &#187; <a class="small" href="viewtopic.php?pid={$searchResult.pid}#p{$searchResult.pid}">{$searchResult.pposted|date_format:$date_format}</a></div>
        <div class="msg">
        {* Harry Poster :) *}
        {if $searchResult.poster_id > 1}
            <strong><a href="profile.php?id={$searchResult.poster_id}">{$searchResult.pposter|escape}</a></strong>
        {else}
            {$searchResult.pposter|escape}
        {/if}<br/>
        <span class="sub">{$lang_search.Replies}: {$searchResult.num_replies} | <a href="viewtopic.php?pid={$searchResult.pid}#p{$searchResult.pid}">{$lang_search.$Go_to_post}</a></span><br/>
        {* Message*}
        {if $pun_config.o_censoring == 1}
            {assign var='msg' value=censor_words($searchResult.message)}
        {else}
            {assign var='msg' value=$searchResult.message}
        {/if}
        
        {assign var='message' value=parse_message($msg, 0)}
        
        {if mb_strlen($message) > 999}
            {assign var='message' value="$message &hellip;"}
        {/if}
        </div>
    {else}
        
        <div class="{if $j = ! $j}msg{else}msg2{/if}">
        {* $forum *}
        <a href="viewforum.php?id={$temp[0]}">{$temp[1]|escape}</a>
        &#187; {* $subject *}
        {if ! $pun_user.is_guest && $searchResult.last_post > $pun_user.last_visit}
            <strong><a href="viewtopic.php?id={$searchResult.tid}">{$sbj|escape}</a> ({$searchResult.poster|escape})</strong>
        {else}
            <a href="viewtopic.php?id={$searchResult.tid}">{$sbj|escape}</a> ({$searchResult.poster|escape})
        {/if}
        
        {if ! $pun_user.is_guest && $searchResult.last_post > $pun_user.last_visit}
            <a class="red" href="viewtopic.php?id={$searchResult.tid}&amp;action=new" title="{$lang_common.$New_posts_info}">{$lang_common.$New_posts}</a>
        {/if}
        
        {assign var='num_pages_topic' value=ceil(($searchResult.num_replies + 1) / $pun_user.disp_posts)}
        
        {if $num_pages_topic > 1}
            [ {paginate($num_pages_topic, -1, "viewtopic.php?id={$searchResult.tid}", 0)} ]
        {/if}
        <br />
        <span class="sub">{$lang_common.Replies}: {$searchResult.num_replies}<br />
        <a href="viewtopic.php?pid={$searchResult.last_post_id}#p{$searchResult.last_post_id}">{$lang_common.$Last_post}</a>:
        {$searchResult.last_poster|escape} ({$searchResult.last_post|date_format:$date_format})</span>
        </div>
    {/if}
{/foreach}

<div class="con">{$paging_links}</div>

{include file='footer.tpl'}