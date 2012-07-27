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
<a href="profile.php?section=essentials&amp;id={$id}">{$lang_profile.$Section_essentials}</a> |
<a href="profile.php?section=personal&amp;id={$id}">{$lang_profile.$Section_personal}</a> |
<a href="profile.php?section=messaging&amp;id={$id}">{$lang_profile.$Section_messaging}</a> |
<a href="profile.php?section=personality&amp;id={$id}">{$lang_profile.$Section_personality}</a> |
<a href="profile.php?section=display&amp;id={$id}">{$lang_profile.$Section_display}</a> |
<a href="profile.php?section=privacy&amp;id={$id}">{$lang_profile.$Section_privacy}</a> |
{if $pun_user.g_id == $smarty.const.PUN_ADMIN || ($pun_user.g_id == $smarty.const.PUN_MOD && $pun_config.p_mod_ban_users == 1)}
<strong>
<a href="profile.php?section=admin&amp;id={$id}">{$lang_profile.$Section_admin}</a>
</strong> |
{/if}
<strong>
<a href="profile.php?preview=1&amp;id={$id}">{$lang_profile.Preview}</a>
</strong>
</div>