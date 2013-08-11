{include file='header.tpl'}

{assign var='User_search' value='User search'}
{assign var='User_list' value='User list'}
{assign var='User_find_legend' value='User find legend'}
{assign var='User_group' value='User group'}
{assign var='All_users' value='All users'}
{assign var='Sort_by' value='Sort by'}
{assign var='No_of_posts' value='No of posts'}
{assign var='Sort_order' value='Sort order'}
{assign var='User_search_info' value='User search info'}
{assign var='No_hits' value='No hits'}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_common.$User_list}
</div>

<div class="con">
    <strong>{$lang_search.$User_search}</strong>
</div>

<form method="get" action="userlist.php?">
    <div class="input">
        <strong>{$lang_ul.$User_find_legend}</strong><br/>

    {if $pun_user.g_search_users == 1}
        {$lang_common.Username}<br/>
        <input type="text" name="username" value="{$username|escape}" maxlength="25"/><br/>
    {/if}

        {$lang_ul.$User_group}<br/>
        <select name="show_group">
            <option value="-1"{if $show_group == -1} selected="selected"{/if}>{$lang_ul.$All_users}</option>

            {foreach from=$groups item=cur_group}
                <option value="{$cur_group.g_id}"{if $cur_group.g_id == $show_group} selected="selected"{/if}>{$cur_group.g_title|escape}</option>
            {/foreach}

        </select><br/>
        {$lang_search.$Sort_by}<br/>
        <select name="sort_by">
            <option value="username"{if $sort_by == 'username'} selected="selected"{/if}>{$lang_common.Username}</option>
            <option value="registered"{if $sort_by == 'registered'} selected="selected"{/if}>{$lang_common.Registered}</option>
        {if $show_post_count}
            <option value="num_posts"{if $sort_by == 'num_posts'} selected="selected"{/if}>{$lang_ul.$No_of_posts}</option>
        {/if}

        </select><br/>
        {$lang_search.$Sort_order}<br/>
        <select name="sort_dir">
            <option value="ASC"{if $sort_dir == 'ASC'} selected="selected"{/if}>{$lang_search.Ascending}</option>
            <option value="DESC"{if $sort_dir == 'DESC'} selected="selected"{/if}>{$lang_search.Descending}</option>
        </select>
    </div>

    <div class="input2">{$lang_ul.$User_search_info}</div>
    <div class="go_to">
        <input type="submit" name="search" value="{$lang_common.Submit}" accesskey="s"/>
    </div>
</form>

<div class="con"><strong>{$lang_common.$User_list}</strong></div>
<div class="navlinks">{$lang_common.Username}

{if $show_post_count}
    &#160;|&#160;{$lang_common.Posts}
{/if}
    &#160;|&#160;{$lang_common.Title}&#160;|&#160;{$lang_common.Registered}</div>

{foreach from=$users item=user_data}
    <div class="{if $j = ! $j}in{else}in2{/if}">
        <strong><a href="profile.php?id={$user_data.id}">{$user_data.username|escape}</a></strong>&#160;
        {if $show_post_count}
            [{$user_data.num_posts}]
        {/if}

        {* Должность *}          {* Дата размещения сообщения *}
        {get_title($user_data)} ({$user_data.registered|date_format:$date_format})
    </div>
{foreachelse}
    <div class="msg">{$lang_search.$No_hits}</div>
{/foreach}

<div class="con">{$lang_common.Pages}: {$paging_links}</div>

{include file='footer.tpl'}