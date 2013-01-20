{include file='header.tpl'}
{include file='profile.navigation.tpl'}



{assign var='Section_essentials' value='Section essentials'}
{assign var='Change_pass' value='Change pass'}
{assign var='Email_legend' value='E-mail legend'}
{assign var='Email' value='E-mail'}
{assign var='Send_email' value='Send e-mail'}
{assign var='Quick_message' value='Quick message'}
{assign var='Timezone_info' value='Timezone info'}
{assign var='Localisation_legend' value='Localisation legend'}
{assign var='Language_info' value='Language info'}
{assign var='User_activity' value='User activity'}
{assign var='Last_post' value='Last post'}
{assign var='Show_posts' value='Show posts'}
{assign var='Show_files' value='Show files'}
{assign var='Show_karma' value='Show karma'}
{assign var='Admin_note' value='Admin note'}

{* + $username_field *}
<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_essentials}</strong>
</div>

<form method="post" action="profile.php?section=essentials&amp;id={$id}">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>
    {if $pun_user.g_id < $smarty.const.PUN_GUEST}
        {if $pun_user.g_id == $smarty.const.PUN_ADMIN || $pun_config.p_mod_rename_users == 1}
            <input type="hidden" name="old_username" value="{$user.username|escape}"/>
            <strong>{$lang_common.Username}</strong><br/>
            <input type="text" name="req_username" value="{$user.username|escape}" maxlength="25"/><br/>
        {else}
            {$lang_common.Username}: {$user.username|escape}
        {/if}
        {else}
        {$lang_common.Username}: {$user.username|escape}
    {/if}
    {* - $username_field *}

    {if $pun_user.id == $id || $pun_user.g_id == $smarty.const.PUN_ADMIN || ($user.g_id > $smarty.const.PUN_MOD && $pun_config.p_mod_change_passwords == 1)}
        <a href="profile.php?action=change_pass&amp;id={$id}">{$lang_profile.$Change_pass}</a><br/>
    {/if}

    </div>
    <div class="input2">
        <strong>{$lang_prof_reg.$Email_legend}</strong><br/>
    {* + $email_field *}
    {if $pun_user.g_id < $smarty.const.PUN_GUEST}
        <strong>{$lang_common.$Email}</strong><br/>
        <input type="text" name="req_email" value="{$user.email}" maxlength="50"/><br/>
        <a href="misc.php?email={$id}">{$lang_common.$Send_email}</a><br/>
        <a href="message_send.php?id={$id}">{$lang_pms.$Quick_message}</a>
        {else}
        {if $pun_config.o_regs_verify == 1}
            {$lang_common.$Email}: {$user.email} - <a href="profile.php?action=change_email&amp;id={$id}">{$lang_profile.$Change_email}</a>
        {else}
            <strong>{$lang_common.$Email}</strong><br/>
            <input type="text" name="req_email" value="{$user.email}" maxlength="50"/>
        {/if}
    {/if}
    {* - $email_field *}
    </div>
    <div class="input">
        <strong>{$lang_prof_reg.$Localisation_legend}</strong><br/>
    {$lang_prof_reg.Timezone}: {$lang_prof_reg.$Timezone_info}<br/>
        <select name="form[timezone]">
            <option value="-12" {if $user.timezone == -12}selected="selected"{/if}>-12</option>
            <option value="-11" {if $user.timezone == -11}selected="selected"{/if}>-11</option>
            <option value="-10" {if $user.timezone == -10}selected="selected"{/if}>-10</option>
            <option value="-9.5" {if $user.timezone == -9.5}selected="selected"{/if}>-09.5</option>
            <option value="-9" {if $user.timezone == -9}selected="selected"{/if}>-09</option>
            <option value="-8.5" {if $user.timezone == -8.5}selected="selected"{/if}>-08.5</option>
            <option value="-8" {if $user.timezone == -8}selected="selected"{/if}>-08 PST</option>
            <option value="-7" {if $user.timezone == -7}selected="selected"{/if}>-07 MST</option>
            <option value="-6" {if $user.timezone == -6}selected="selected"{/if}>-06 CST</option>
            <option value="-5" {if $user.timezone == -5}selected="selected"{/if}>-05 EST</option>
            <option value="-4" {if $user.timezone == -4}selected="selected"{/if}>-04 AST</option>
            <option value="-3.5" {if $user.timezone == -3.5}selected="selected"{/if}>-03.5</option>
            <option value="-3" {if $user.timezone == -3}selected="selected"{/if}>-03 ADT</option>
            <option value="-2" {if $user.timezone == -2}selected="selected"{/if}>-02</option>
            <option value="-1" {if $user.timezone == -1}selected="selected"{/if}>-01</option>
            <option value="0" {if $user.timezone == 0}selected="selected"{/if}>00 GMT</option>
            <option value="1" {if $user.timezone == 1}selected="selected"{/if}>+01 CET</option>
            <option value="2" {if $user.timezone == 2}selected="selected"{/if}>+02</option>
            <option value="3" {if $user.timezone == 3}selected="selected"{/if}>+03</option>
            <option value="3.5" {if $user.timezone == 3.5}selected="selected"{/if}>+03.5</option>
            <option value="4" {if $user.timezone == 4}selected="selected"{/if}>+04</option>
            <option value="4.5" {if $user.timezone == 4.5}selected="selected"{/if}>+04.5</option>
            <option value="5" {if $user.timezone == 5}selected="selected"{/if}>+05</option>
            <option value="5.5" {if $user.timezone == 5.5}selected="selected"{/if}>+05.5</option>
            <option value="6" {if $user.timezone == 6}selected="selected"{/if}>+06</option>
            <option value="6.5" {if $user.timezone == 6.5}selected="selected"{/if}>+06.5</option>
            <option value="7" {if $user.timezone == 7}selected="selected"{/if}>+07</option>
            <option value="8" {if $user.timezone == 8}selected="selected"{/if}>+08</option>
            <option value="9" {if $user.timezone == 9}selected="selected"{/if}>+09</option>
            <option value="9.5" {if $user.timezone == 9.5}selected="selected"{/if}>+09.5</option>
            <option value="10" {if $user.timezone == 10}selected="selected"{/if}>+10</option>
            <option value="10.5" {if $user.timezone == 10.5}selected="selected"{/if}>+10.5</option>
            <option value="11" {if $user.timezone == 11}selected="selected"{/if}>+11</option>
            <option value="11.5" {if $user.timezone == 11.5}selected="selected"{/if}>+11.5</option>
            <option value="12" {if $user.timezone == 12}selected="selected"{/if}>+12</option>
            <option value="13" {if $user.timezone == 13}selected="selected"{/if}>+13</option>
            <option value="14" {if $user.timezone == 14}selected="selected"{/if}>+14</option>
        </select>
    </div>

    <div class="input2">
        <strong>{$lang_prof_reg.Language}</strong>: {$lang_prof_reg.$Language_info}<br/>
        <select name="form[language]">
        {foreach from=$languages item=temp}
            <option value="{$temp}" {if $user.language == $temp}selected="selected"{/if}>{$temp}</option>
        {/foreach}
        </select>
    </div>
<div class="input">
    <strong>{$lang_profile.$User_activity}</strong><br/>
{$lang_common.Registered}: {$user.registered|date_format:$date_format}

{if $pun_user.g_id < $smarty.const.PUN_GUEST}
    (<a href="moderate.php?get_host={$user.registration_ip}">{$user.registration_ip}</a>)
{/if}

    <br/>{$lang_common.$Last_post}: {$user.last_post|date_format:$date_format|default:$lang_profile.Unknown}<br/>
{if $pun_config.o_show_post_karma == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
    {$lang_common.Karma}: {$karma.karma} (+{$karma.plus}/-{$karma.minus}) - <a href="karma.php?id={$id}">{$lang_common.$Show_karma}</a><br/>
{/if}

{* + posts and files *}
{if $pun_user.g_id == $smarty.const.PUN_ADMIN}
    {$lang_common.Posts}: <input type="text" name="num_posts" value="{$user.num_posts}" size="3" maxlength="8"/> - <a href="search.php?action=show_user&amp;user_id={$id}">{$lang_profile.$Show_posts}</a><br/>
    {$lang_common.Files}: <input type="text" name="num_files" value="{$user.num_files}" size="3" maxlength="8"/> - <a href="filemap.php?user_id={$id}">{$lang_profile.$Show_files}</a><br/>
    {$lang_common.Bonus}: <input type="text" name="file_bonus" value="{$user.file_bonus}" size="3" maxlength="8"/><br/>
{else}
    {if $pun_config.o_show_post_count == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
        {$lang_common.Posts}: {$user.num_posts} - <a href="search.php?action=show_user&amp;user_id={$id}">{$lang_profile.$Show_posts}</a><br/>
        {$lang_common.Files}: {$user.num_files} - <a href="filemap.php?user_id={$id}">{$lang_profile.$Show_files}</a><br/>
    {else}
        <a href="search.php?action=show_user&amp;user_id={$id}">{$lang_profile.$Show_posts}</a><br/>
        <a href="filemap.php?user_id={$id}">{$lang_profile.$Show_files}</a><br/>
    {/if}
{/if}
{* - posts and files*}

{if $pun_user.g_id < $smarty.const.PUN_GUEST}
</div>
<div class="in">
    {$lang_profile.$Admin_note}
    <input type="text" name="admin_note" value="{$user.admin_note|escape}" maxlength="30"/>
{/if}
</div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}