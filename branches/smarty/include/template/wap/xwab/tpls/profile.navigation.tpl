{assign var='Section_essentials' value='Section essentials'}
{assign var='Section_personal' value='Section personal'}
{assign var='Section_messaging' value='Section messaging'}
{assign var='Section_personality' value='Section personality'}
{assign var='Section_display' value='Section display'}
{assign var='Section_privacy' value='Section privacy'}
{assign var='Section_admin' value='Section admin'}
{assign var='Profile_menu' value='Profile menu'}

{* Навигация: Главная / Профиль *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_profile.$Profile_menu}
</div>

<div class="navlinks">
    {if $smarty.get.section == 'essentials' || !$smarty.get.section}
        <strong>{$lang_profile.$Section_essentials}</strong>
    {else}
        <a href="profile.php?section=essentials&amp;id={$id}">{$lang_profile.$Section_essentials}</a>
    {/if}
    |
    {if $smarty.get.section == 'personal'}
        <strong>{$lang_profile.$Section_personal}</strong>
    {else}
        <a href="profile.php?section=personal&amp;id={$id}">{$lang_profile.$Section_personal}</a>
    {/if}
    |
    {if $smarty.get.section == 'messaging'}
        <strong>{$lang_profile.$Section_messaging}</strong>
    {else}
        <a href="profile.php?section=messaging&amp;id={$id}">{$lang_profile.$Section_messaging}</a>
    {/if}
    |
    {if $smarty.get.section == 'personality'}
        <strong>{$lang_profile.$Section_personality}</strong>
    {else}
        <a href="profile.php?section=personality&amp;id={$id}">{$lang_profile.$Section_personality}</a>
    {/if}
    |
    {if $smarty.get.section == 'display'}
        <strong>{$lang_profile.$Section_display}</strong>
    {else}
        <a href="profile.php?section=display&amp;id={$id}">{$lang_profile.$Section_display}</a>
    {/if}
    |
    {if $smarty.get.section == 'privacy'}
        <strong>{$lang_profile.$Section_privacy}</strong>
    {else}
        <a href="profile.php?section=privacy&amp;id={$id}">{$lang_profile.$Section_privacy}</a>
    {/if}
    |
    {if $pun_user.g_id == $smarty.const.PUN_ADMIN || ($pun_user.g_id == $smarty.const.PUN_MOD && $pun_config.p_mod_ban_users == 1)}
        {if $smarty.get.section == 'admin'}
            <strong>{$lang_profile.$Section_admin}</strong>
        {else}
            <a href="profile.php?section=admin&amp;id={$id}">{$lang_profile.$Section_admin}</a>
        {/if}
        |
    {/if}
    {if $smarty.get.preview}
        <strong>{$lang_profile.Preview}</strong>
    {else}
        <a href="profile.php?preview=1&amp;id={$id}">{$lang_profile.Preview}</a>
    {/if}
</div>