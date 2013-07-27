{include file='header.tpl'}

{if $id == $pun_user.id or $pun_user.g_id == $smarty.const.PUN_MOD || $pun_user.g_id == $smarty.const.PUN_ADMIN}
{include file='profile.navigation.tpl'}
{else}
    {assign var='Profile_menu' value='Profile menu'}
    {* Навигация: Главная / Профиль *}
    <div class="inbox">
        <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_profile.$Profile_menu}
    </div>
{/if}

<div class="con">
{$lang_common.Profile} <strong>{$user.username|escape}</strong>
</div>

{* PHP.arrayKeyNames -> Smarty.Names *}

{assign var='No_avatar' value='No avatar'}
{assign var='No_sig' value='No sig'}
{assign var='Section_personal' value='Section personal'}
{assign var='Email' value='E-mail'}
{assign var='Send_email' value='Send e-mail'}
{assign var='Section_messaging' value='Section messaging'}
{assign var='AOL_IM' value='AOL IM'}
{assign var='User_activity' value='User activity'}
{assign var='Show_karma' value='Show karma'}

{assign var='Last_post' value='Last post'}

{assign var='Show_files' value='Show files'}
{assign var='Show_posts' value='Show posts'}

<div class="input">
    {if $pun_config.o_avatars}
        {if ! $user.use_avatar and $user_avatar}
            {$lang_profile.$No_avatar}
        {else}
            {$user_avatar}
        {/if}
        <br/>
    {/if}

    <strong>{$lang_profile.Signature}:</strong> {if isset($parsed_signature)}{$parsed_signature}{else}{$lang_profile.$No_sig}{/if}
</div>

{* Personal *}
<div class="input2">
    <strong>{$lang_profile.$Section_personal}</strong><br/>
    <strong>{$lang_common.Username}:</strong> {$user.username|escape}
    ({if $user.sex == 1}{$lang_profile.m}{else}{$lang_profile.w}{/if})<br/>
    {if $user.birthday}
        <strong>{$lang_profile.birthday}:</strong> {$user.birthday}<br/>
    {/if}

    <strong>{$lang_common.Title}:</strong>
    {$userTitle}<br/>
    <strong>{$lang_profile.Realname}:</strong>
{if $user.realname}
    {$user.realname|escape}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.Location}:</strong>
{if $user.location}
    {$user.location|escape}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.Website}:</strong>
{if $user.url}
    <a href="{$user.url}">{$user.url|escape}</a>
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_common.$Email}:</strong>
{if !$user.email_setting && !$pun_user.is_guest}
    <a href="mailto:{$user.email|rawurlencode}">{$user.email|escape}</a>
{else}
    {if $user.email_setting == 1 && ! $pun_user.is_guest}
        <a href="misc.php?email={$id}">{$lang_common.$Send_email}</a>
    {else}
        {$lang_profile.Private}
    {/if}
{/if}
    <br/>
</div>

{* Messaging *}
<div class="input">
    <strong>{$lang_profile.$Section_messaging}</strong><br/>
    <strong>{$lang_profile.Jabber}:</strong>
{if $user.jabber}
    {$user.jabber|escape}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.ICQ}:</strong>
{if $user.icq}
    {$user.icq}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.MSN}:</strong>
{if $user.msn}
    {$user.msn|escape}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.$AOL_IM}:</strong>
{if $user.aim}
    {$user.aim|escape}
{else}
    {$lang_profile.Unknown}
{/if}
    <br/>
    <strong>{$lang_profile.Yahoo}:</strong>
{if $user.yahoo}
    {$user.yahoo|escape}
{else}
    {$lang_profile.Unknown}
{/if}
</div>

{* User activity *}
<div class="input2">
    <strong>{$lang_profile.$User_activity}</strong><br/>
    <strong>{$lang_common.Posts}:</strong>
{if $pun_config.o_show_post_count == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
    {$user.num_posts}
{/if}

{if $pun_user.g_search == 1}
    {if $user.num_posts}- <a href="search.php?action=show_user&amp;user_id={$id}">{$lang_profile.$Show_posts}</a>{/if}
{/if}
    <br/>

    <strong>{$lang_common.Files}:</strong>
{if $pun_config.o_show_post_count == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
    {$user.num_files}
{/if}

{if $pun_user.g_search == 1}
    {if $user.num_files}- <a href="filemap.php?user_id={$id}">{$lang_profile.$Show_files}</a>{/if}
{/if}
    <br/>

{* Karma *}
{if $pun_config.o_show_post_karma == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
    {$lang_common.Karma}: {($karma.plus - $karma.minus)} (+{$karma.plus}/-{$karma.minus}) - <a href="karma.php?id={$id}">{$lang_common.$Show_karma}</a><br/>
{/if}

    <strong>{$lang_common.$Last_post}:</strong> {$user.last_post|date_format:$date_format|default:$lang_profile.Unknown}<br/>
    <strong>{$lang_common.Registered}:</strong> {$user.registered|date_format:$date_format}
</div>

{include file='footer.tpl'}