{include file='header.tpl'}
{include file='profile.navi.tpl'}



{assign var='Personal_details_legend' value='Personal details legend'}
{assign var='Section_personal' value='Section personal'}

<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_personal}</strong>
</div>
<form method="post" action="profile.php?section=personal&amp;id={$id}">
    <div class="input">
        <strong>{$lang_profile.$Personal_details_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>{$lang_profile.sex}<br/>
        <select name="form[sex]">

        {if $user.sex} == 1}
            <option value="1">{$lang_profile.m}</option>
            <option value="0">{$lang_profile.w}</option>
            {else}
            <option value="0">{$lang_profile.w}</option>
            <option value="1">{$lang_profile.m}</option>
        {/if}

        </select><br/>
        {$lang_profile.birthday}<br/>
        <input type="text" value="{$birthday[0]|default}" name="day" title="{$lang_profile.day}" size="2" maxlength="2"/>.<input
            type="text" value="{$birthday[1]|default}" name="month" title="{$lang_profile.month}" size="2"
            maxlength="2"/>.<input type="text" value="{$birthday[2]|default}" name="year" title="{$lang_profile.year}" size="4"
                                   maxlength="4"/><br/>
        {$lang_profile.Realname}<br/>
        <input type="text" name="form[realname]" value="{$user.realname|escape}"
               maxlength="40"/><br/>{$title_field|default:''}{$lang_profile.Location}<br/>
        <input type="text" name="form[location]" value="{$user.location|escape}" maxlength="30"/><br/>
        {$lang_profile.Website}<br/>
        <input type="text" name="form[url]" value="{$user.url|escape}" maxlength="80"/></div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}