{extends file='layout.scheme.tpl'}
{block name='center'}
{include file='profile.navigation.tpl'}

{assign var='Contact_details_legend' value='Contact details legend'}
{assign var='Section_messaging' value='Section messaging'}
{assign var='AOL_IM' value='AOL IM'}

<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_messaging}</strong>
</div>

<form method="post" action="profile.php?section=messaging&amp;id={$id}">
    <div class="input">
        <strong>{$lang_profile.$Contact_details_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        {$lang_profile.Jabber}<br/>
        <input type="text" name="form[jabber]" value="{$user.jabber|escape}" maxlength="75"/><br/>
        {$lang_profile.ICQ}<br/>
        <input type="text" name="form[icq]" value="{$user.icq}" maxlength="12"/><br/>
        {$lang_profile.MSN}<br/>
        <input type="text" name="form[msn]" value="{$user.msn|escape}" maxlength="50"/><br/>
        {$lang_profile.$AOL_IM}<br/>
        <input type="text" name="form[aim]" value="{$user.aim|escape}" maxlength="30"/><br/>
        {$lang_profile.Yahoo}<br/>
        <input type="text" name="form[yahoo]" value="{$user.yahoo|escape}" maxlength="30"/></div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{/block}