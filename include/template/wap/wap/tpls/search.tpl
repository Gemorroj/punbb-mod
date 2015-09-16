{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Search_criteria_legend' value='Search criteria legend'}
{assign var='Keyword_search' value='Keyword search'}
{assign var='Author_search' value='Author search'}
{assign var='Search_info' value='Search info'}
{assign var='Search_in_legend' value='Search in legend'}
{assign var='Forum_search' value='Forum search'}
{assign var='All_forums' value='All forums'}
{assign var='Search_in' value='Search in'}
{assign var='Message_and_subject' value='Message and subject'}
{assign var='Message_only' value='Message only'}
{assign var='Topic_only' value='Topic only'}
{assign var='Search_in_info' value='Search in info'}
{assign var='Search_results_legend' value='Search results legend'}
{assign var='Sort_by' value='Sort by'}
{assign var='Sort_by_post_time' value='Sort by post time'}
{assign var='Sort_by_author' value='Sort by author'}
{assign var='Sort_by_subject' value='Sort by subject'}
{assign var='Sort_by_forum' value='Sort by forum'}
{assign var='Sort_order' value='Sort order'}
{assign var='Show_as' value='Show as'}
{assign var='Show_as_posts' value='Show as posts'}
{assign var='Show_as_topics' value='Show as topics'}
{assign var='Search_results_info' value='Search results info'}
{assign var='cur_category' value=0}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_search.Search}
</div>

<div class="con">
    <strong>{$lang_search.Search}</strong>
</div>
<form method="get" action="search.php?">
    <div class="input"><strong>{$lang_search.$Search_criteria_legend}</strong><br/>
        <input type="hidden" name="action" value="search"/>{$lang_search.$Keyword_search}<br/>
        <input type="text" name="keywords" maxlength="100"/><br/>
        {$lang_search.$Author_search}<br/>
        <input type="text" name="author" maxlength="25"/></div>
    <div class="input2">
        {$lang_search.$Search_info}<strong>{$lang_search.$Search_in_legend}</strong><br/>
        {$lang_search.$Forum_search}<br/>
        <select name="forum">

        {if $pun_config.o_search_all_forums == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
            <option value="-1">{$lang_search.$All_forums}</option>
        {/if}

        {foreach from=$forums item=cur_forum}
            {if $cur_forum.cid != $cur_category}
                {if $cur_category}</optgroup>{/if}
                <optgroup label="{$cur_forum.cat_name|escape}">
                {assign var='cur_category' value=$cur_forum.cid}
            {/if}
            <option value="{$cur_forum.fid}">{$cur_forum.forum_name|escape}</option>
        {/foreach}

        {if $forums}</optgroup>{/if}

        </select><br/>
        {$lang_search.$Search_in}<br/>
        <select name="search_in">
            <option value="all">{$lang_search.$Message_and_subject}</option>
            <option value="message">{$lang_search.$Message_only}</option>
            <option value="topic">{$lang_search.$Topic_only}</option>
        </select><br/>
    {$lang_search.$Search_in_info}</div>
    <div class="input">
        <strong>{$lang_search.$Search_results_legend}</strong><br/>
        {$lang_search.$Sort_by}<br/>
        <select name="sort_by">
            <option value="0">{$lang_search.$Sort_by_post_time}</option>
            <option value="1">{$lang_search.$Sort_by_author}</option>
            <option value="2">{$lang_search.$Sort_by_subject}</option>
            <option value="3">{$lang_search.$Sort_by_forum}</option>
        </select><br/>
        {$lang_search.$Sort_order}<br/>
        <select name="sort_dir">
            <option value="DESC">{$lang_search.Descending}</option>
            <option value="ASC">{$lang_search.Ascending}</option>
        </select><br/>
        {$lang_search.$Show_as}<br/>
        <select name="show_as">
            <option value="posts">{$lang_search.$Show_as_posts}</option>
            <option value="topics">{$lang_search.$Show_as_topics}</option>
        </select><br/>
    {$lang_search.$Search_results_info}</div>
    <div class="go_to">
        <input type="submit" name="search" value="{$lang_common.Submit}" accesskey="s"/>
    </div>
</form>

{/block}
