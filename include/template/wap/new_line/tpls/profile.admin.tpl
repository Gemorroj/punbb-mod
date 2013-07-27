{include file='header.tpl'}
{* This template is completed! *}
{include file='profile.navigation.tpl'}

<div class="con">
    {assign var='Section_admin' value='Section admin'}
    <strong>{$user.username|escape} - {$lang_profile.$Section_admin}</strong>
</div>

<form method="post" action="profile.php?section=admin&amp;id={$id}&amp;action=foo">
    <div>
        <input type="hidden" name="form_sent" value="1"/>
    </div>
    {assign var='Delete_ban_legend' value='Delete ban legend'}
    {assign var='Ban_user' value='Ban user'}

    {if $pun_user.g_id == $smarty.const.PUN_MOD}
        <div class="input">
            <strong>{$lang_profile.$Delete_ban_legend}</strong><br/>
            <input type="submit" name="ban" value="{$lang_profile.$Ban_user}"/><br/>
        </div>
    {else}

        {if $pun_user.id != $id}
            <div class="input">
                {assign var='Group_membership_legend' value='Group membership legend'}
                <strong>{$lang_profile.$Group_membership_legend}</strong><br/>
                <select name="group_id">

                    {foreach from=$groups item=cur_group}

                        {if $cur_group.g_id == $user.g_id || ($cur_group.g_id == $pun_config.o_default_user_group && ! $user.g_id)}
                            <option value="{$cur_group.g_id}" selected="selected">{$cur_group.g_title|escape}</option>
                        {else}
                            <option value="{$cur_group.g_id}">{$cur_group.g_title|escape}</option>
                        {/if}

                    {/foreach}
                </select>
                <input type="submit" name="update_group_membership" value="{$lang_profile.Save}"/>
            </div>
        {/if}
        <div class="input2">
            <strong>{$lang_profile.$Delete_ban_legend}</strong><br/>
            {assign var='Delete_user' value='Delete user'}
            <input type="submit" name="delete_user" value="{$lang_profile.$Delete_user}"/>
            <input type="submit" name="ban" value="{$lang_profile.$Ban_user}"/>
        </div>
        {if $user.g_id == $smarty.const.PUN_MOD || $user.g_id == $smarty.const.PUN_ADMIN}
            <div class="input">
                {assign var='Set_mods_legend' value='Set mods legend'}
                <strong>{$lang_profile.$Set_mods_legend}</strong><br/>
                {assign var='Moderator_in_info' value='Moderator in info'}
                {$lang_profile.$Moderator_in_info}<br/>

                {assign var='cur_category' value='0'}
                {foreach from=$forums item=cur_forum}
                    {if $cur_forum.cid != $cur_category}
                    {*// A new category since last iteration?*}
                        <strong>{$cur_forum.cat_name}</strong><br/>
                        {assign var='cur_category' value=$cur_forum.cid}
                    {/if}
                    <label><input type="checkbox" name="moderator_in[{$cur_forum.fid}]" value="1" {if $cur_forum.is_moderator}checked="checked"{/if} /> {$cur_forum.forum_name|escape}
                    </label>
                    <br/>
                {/foreach}

            </div>
            <div class="go_to">
                {assign var='Update_forums' value='Update forums'}
                <input type="submit" name="update_forums" value="{$lang_profile.$Update_forums}"/>
            </div>
        {/if}

    {/if}
</form>

{include file='footer.tpl'}