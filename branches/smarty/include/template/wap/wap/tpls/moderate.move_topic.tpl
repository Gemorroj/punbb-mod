{include file='header.tpl'}

{assign var='Move_topic' value='Move topic'}
{assign var='Move_topics' value='Move topics'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; {if $action == 'single'}{$lang_misc.$Move_topic}{else}{$lang_misc.$Move_topics}{/if}
</div>

{assign var='Leave_redirect' value='Leave redirect'}
{assign var='Move_to' value='Move to'}
{assign var='Move_legend' value='Move legend'}

<form method="post" action="moderate.php?fid={$fid}">
<div class="input">
<input type="hidden" name="topics" value="{$topics}" />
<strong>{$lang_misc.$Move_legend}</strong><br/>
{$lang_misc.$Move_to}<br/>
<select name="move_to_forum">

{assign var='cur_category' value='0'}
{foreach from=$forums item=cur_forum}
{if $cur_forum.cid != $cur_category}
{if $cur_category}
</optgroup>
{/if}
<optgroup label="{$cur_forum.cat_name|escape}">
{assign var='cur_category' value=$cur_forum.cid}
{/if}

{if $cur_forum.fid != $fid}
<option value="{$cur_forum.fid}">{$cur_forum.forum_name|escape}</option>
{/if}
{/foreach}
</optgroup>
</select><br/>
<label for="with_redirect"><input type="checkbox" id="with_redirect" name="with_redirect" value="1" {if $action == 'single'}checked="checked"{/if}/>{$lang_misc.$Leave_redirect}</label>
</div>
<div class="go_to">
<input type="submit" name="move_topics_to" value="{$lang_misc.Move}" />
</div>
</form>

{include file='footer.tpl'}