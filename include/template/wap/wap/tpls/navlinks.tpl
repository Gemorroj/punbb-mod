{* Index and Userlist should always be displayed *}
<div class="navlinks">
{assign var='Link_separator_m' value='Link separator_m'}
    {assign var='User_list' value='User list'}
    <a href="userlist.php">{$lang_common.$User_list}</a>

    {if $pun_config.o_rules == 1}
        {$lang_common.$Link_separator_m}<a href="misc.php?action=rules">{$lang_common.Rules}</a>
    {/if}

    {if $pun_user.g_search == 1 or $pun_user.g_id > $smarty.const.PUN_MOD}
        {$lang_common.$Link_separator_m}<a href="search.php">{$lang_common.Search}</a>
    {/if}

    {if ! $pun_user.is_guest}
        {$lang_common.$Link_separator_m}<a href="uploads.php">{$lang_common.Uploader}</a>
        {$lang_common.$Link_separator_m}<a href="filemap.php">{$lang_common.Attachments}</a>
    {/if}
</div>