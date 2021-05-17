{extends file='layout.scheme.tpl'}
{block name='center'}
{include file='profile.navigation.tpl'}

{assign var='Section_display'     value='Section display'}
{assign var='Style_legend'        value='Style legend'}
{assign var='Style_info'          value='Style info'}
{assign var='Post_display_legend' value='Post display legend'}
{assign var='Post_display_info'   value='Post display info'}
{assign var='Show_smilies'        value='Show smilies'}
{assign var='Show_sigs'           value='Show sigs'}
{assign var='Show_avatars'        value='Show avatars'}
{assign var='Show_images'         value='Show images'}
{assign var='Show_images_sigs'    value='Show images sigs'}
{assign var='Pagination_legend'   value='Pagination legend'}
{assign var='Topics_per_page'     value='Topics per page'}
{assign var='Posts_per_page'      value='Posts per page'}
{assign var='Paginate_info'       value='Paginate info'}
{assign var='Leave_blank'         value='Leave blank'}
{assign var='Mark_as_read_legend' value='Mark as read legend'}
{assign var='Mark_as_read_after'  value='Mark as read after'}

<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_display}</strong>
</div>
<form method="post" action="profile.php?section=display&amp;id={$id}">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>

        <strong>{$lang_profile.$Style_legend}</strong><br/>
        {$lang_profile.$Style_info}<br/>
        <select name="form[style_wap]">
        {foreach from=$styles item=temp}
            {if $user.style_wap == $temp}
                <option value="{$temp}" selected="selected">{$temp|replace:'_':' '}</option>
                {else}
                <option value="{$temp}">{$temp|replace:'_':' '}</option>
            {/if}
        {/foreach}
        </select>
    </div>
    <div class="input2">
        <strong>{$lang_profile.$Post_display_legend}</strong><br/>
        {$lang_profile.$Post_display_info}<br/>
        <label><input type="checkbox" name="form[show_smilies]" value="1"{if $user.show_smilies == 1} checked="checked"{/if}/>{$lang_profile.$Show_smilies}</label><br/>
        <label><input type="checkbox" name="form[show_sig]" value="1"{if $user.show_sig == 1} checked="checked"{/if}/>{$lang_profile.$Show_sigs}</label><br/>
    {if $pun_config.o_avatars == 1}
    <label><input type="checkbox" name="form[show_avatars]" value="1"{if $user.show_avatars == 1} checked="checked"{/if}/>{$lang_profile.$Show_avatars}</label><br/>
    {/if}
        <label><input type="checkbox" name="form[show_img]" value="1"{if $user.show_img == 1} checked="checked"{/if}/>{$lang_profile.$Show_images}</label><br/>
        <label><input type="checkbox" name="form[show_img_sig]" value="1"{if $user.show_img_sig == 1} checked="checked"{/if}/>{$lang_profile.$Show_images_sigs}</label>
    </div>
    <div class="input">
        <strong>{$lang_profile.$Pagination_legend}</strong><br/>
        {$lang_profile.$Topics_per_page}<br/>
        <input type="number" name="form[disp_topics]" value="{$user.disp_topics}" maxlength="3"/><br/>
        {$lang_profile.$Posts_per_page}<br/>
        <input type="number" name="form[disp_posts]" value="{$user.disp_posts}" maxlength="3"/><br/>
    {$lang_profile.$Paginate_info} {$lang_profile.$Leave_blank}
    </div>
    <div class="input2">
        <strong>{$lang_profile.$Mark_as_read_legend}</strong><br/>
        {$lang_profile.$Mark_as_read_after}<br/>
        <input type="number" name="form[mark_after]" value="{($user.mark_after / 86400)}" maxlength="3"/>
    </div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{/block}
