{include file='header.tpl'}

{assign var='Section_admin' value='Section admin'}
{assign var='Delete_ban_legend' value='Delete ban legend'}
{assign var='Ban_user' value='Ban user'}
{assign var='Group_membership_legend' value='Group membership legend'}
{assign var='Delete_ban_legend' value='Delete ban legend'}
{assign var='Delete_user' value='Delete user'}
{assign var='Set_mods_legend' value='Set mods legend'}
{assign var='Update_forums' value='Update forums'}

<div class="con">
<strong>{$user.username|escape} - {$lang_profile.$Section_admin}</strong>
</div>

<form method="post" action="profile.php?section=admin&amp;id={$id}&amp;action=foo">
<div class="input">
<input type="hidden" name="form_sent" value="1" />

{if $pun_user.g_id == $smarty.const.PUN_MOD}
<strong>{$lang_profile.$Delete_ban_legend}</strong><br/>
<input type="submit" name="ban" value="{$lang_profile.$Ban_user}" /><br/>
{else}

{if $pun_user.id != $id}
<strong>{$lang_profile.$Group_membership_legend}</strong><br/>
<select name="group_id">

{foreach from=$groups item=cur_group}
    {if $cur_group.g_id == $user.g_id ||
       ($cur_group.g_id == $pun_config.o_default_user_group &&
        ! $user.g_id)}
        
        <option value="{$cur_group.g_id}" selected="selected">{$cur_group.g_title|escape}</option>
    {else}
        <option value="{$cur_group.g_id}">{$cur_group.g_title|escape}</option>
    {/if}
{/foreach}

</select>
<input type="submit" name="update_group_membership" value="{$lang_profile.Save}" />
{/if}

</div>
<div class="input2">
<strong>{$lang_profile.$Delete_ban_legend}</strong><br/>
<input type="submit" name="delete_user" value="{$lang_profile.$Delete_user}" />
<input type="submit" name="ban" value="{$lang_profile.$Ban_user}" />

{if $user.g_id == $smarty.const.PUN_MOD || $user.g_id == $smarty.const.PUN_ADMIN}
</div>
<div class="input">
<strong>{$lang_profile.$Set_mods_legend}</strong><br/>
{$lang_profile.Moderator_in_info}<br/>

{assign var='cur_category' value='0'}
{foreach from=$forums item=cur_forum}
    {if $cur_forum.cid != $cur_category}
    {*// A new category since last iteration?*}
    <strong>{$cur_forum.cat_name}</strong><br/>
    {assign var='cur_category' value=$cur_forum.cid}
    {/if}
    
    {if $cur_forum.moderators}
    {assign var='moderators' value=unserialize($cur_forum.moderators)}
    {/if}
    
    <input type="checkbox" name="moderator_in[{$cur_forum.fid}]" value="1"{if in_array($id, $moderators)} checked="checked"{/if} /> {$cur_forum.forum_name|escape}<br/>
{/foreach}
</div>
<div class="go_to">
<input type="submit" name="update_forums" value="{$lang_profile.$Update_forums}" />
{/if}

{/if}
</div>
</form>

{include file='footer.tpl'}