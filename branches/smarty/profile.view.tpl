{* NOT COMPILE *}

<div class="con">
    {$lang_common.Profile} <strong>{$user.username|escape}</strong>
</div>

<div class="input">
{if $pun_config.o_avatars}
    {if $user.use_avatar == 1}
        {if is_file("{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.gif")}
            <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.gif" alt="" />
        {else}
            {if is_file("{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.jpg")}
                <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.jpg" alt="" />
            {else}
                {if is_file("{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.png")}
                    <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$id}.png" alt="" />
                {else}
                    {$lang_profile.$No_avatar}
                {/if}
            {/if}
        {/if}
    {else}
        {$lang_profile.$No_avatar}
    {/if}
<br/>
{/if}

<strong>{$lang_profile.Signature}:</strong> {if isset($parsed_signature)}{$parsed_signature}{else}{$lang_profile.$No_sig}{/if}
</div>

{* Personal *}
<div class="input2">
<strong>{$lang_profile.$Section_personal}</strong><br/>
<strong>{$lang_common.Username}:</strong> {$user.username|escape} ({$use.sex})<br/>
{if $user.birthday}
<strong>{$lang_profile.birthday}:</strong> {$user.birthday}<br/>
{/if}

<strong>{$lang_common.Title}:</strong> 
{if $pun_config.o_censoring == 1}
    {censor_words($user_title_field)}
{else}
    {$user_title_field}
{/if}
<br/>
<strong>{$lang_profile.Realname}:</strong> 
{if $user.realname}
    {if $pun_config.o_censoring == 1}
        {censor_words($user.realname)|escape}
    {else}
        {$user.realname|escape}
    {/if}
{else}
    {$lang_profile.Unknown}
{/if}
<br/>
<strong>{$lang_profile.Location}:</strong> 
{if $user.location}
    {if $pun_config.o_censoring == 1}
        {censor_words($user.location)|escape}
    {else}
        {$user.location|escape}
    {/if}
{else}
    {$lang_profile.Unknown}
{/if}
<br/>
<strong>{$lang_profile.Website}:</strong>
{if $user.url}
    <a href="{$user.url}">{if $pun_config.o_censoring == 1}{censor_words($user.url)|escape}{/if}</a>
{else}
    {$lang_profile.Unknown}
{/if}
<br/>
<strong>{$lang_common.E-mail}:</strong>
{if ! $user.email_setting && ! $pun_user.is_guest}
    <a href="mailto:{$user.email}">{$user.email}</a>
{else}
    {if $user.email_setting == 1 && ! $pun_user.is_guest}
        <a href="misc.php?email={$id}">{$lang_common.$Send_e-mail}</a>
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
    {if $pun_config.o_censoring == 1}
        {censor_words($user.msn)|escape}
    {else}
        {$user.msn|escape}
    {/if}
{else}
    {$lang_profile.Unknown}
{/if}
<br/>
<strong>{$lang_profile.$AOL_IM}:</strong> 
{if $user.aim}
    {if $pun_config.o_censoring == 1}
        {censor_words($user.aim)|escape}
    {else}
        {$user.aim|escape}
    {/if}
{esle}
    {$lang_profile.Unknown}
{/if}
<br/>
<strong>{$lang_profile.Yahoo}:</strong> 
{if $user.yahoo}
    {if $pun_config.o_censoring == 1}
        {censor_words($user.yahoo)|escape}
    {else}
        {$user.yahoo|escape}
    {/if}
{else}
    {$lang_profile.Unknown}
{/if}
</div>

{* User activity *}
<div class="input2">
<strong>{$lang_profile.$User_activity}</strong><br/>
<strong>{$lang_common.Posts}:</strong> {$posts_field}<br/>
<strong>{$lang_common.Files}:</strong> {$files_field}<br/>

{* Karma *}
{if $pun_config.o_show_post_karma == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
    {$lang_common.Karma}: {($karma.plus - $karma.minus)} (+{$karma.plus}/-{$karma.minus}) - <a href="karma.php?id={$id}">{$lang_common.$Show_karma}</a><br/>
{/if}

<strong>{$lang_common.$Last_post}:</strong> {$last_post}<br/>
<strong>{$lang_common.Registered}:</strong> {$user.registered|date_format:$date_format}
</div>

{**}