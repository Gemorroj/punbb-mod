{include file='header.tpl'}

{* Ключи массивов с пробелами *}
{assign var='Last_edit' value='Last edit'}
{assign var='Link_separator_m' value='Link separator_m'}
{assign var='Post_reply_m' value='Post reply_m'}
{assign var='Post_reply' value='Post reply'}
{assign var='Quick_post' value='Quick post'}
{assign var='Write_message_legend' value='Write message legend'}
{assign var='Merge_posts' value='Merge posts'}
{assign var='Topic_closed' value='Topic closed'}
{assign var='Mark_to_Delete' value='Mark to Delete'}
{assign var='Antispam_pattern' value='Antispam pattern'}
{assign var='Antispam_tread' value='Antispam tread'}
{assign var='Antispam_del' value='Antispam del'}

{* Кеш пользовательских подписей *}
{assign var='signature_cache' value=''}



{* Навигация: Главная / Форум / Тема *}
{*
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;<a
        href="viewforum.php?id={$cur_topic.forum_id}">{$cur_topic.forum_name|escape}</a>&#160;&#187;&#160;{$cur_topic.subject|escape}
</div>
*}

{$show_poll}


<div class="msg">
    <div class="zag_in" id="p{$cur_post.id}">

        {if $cur_post.poster_id > 1 and $pun_config.o_avatars == 1 && $cur_post.use_avatar == 1 && $pun_user.show_avatars}
        {* Аватарка *}
            <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$cur_post.poster_id}.{$cur_post.avatar_type}" alt="*"/>&#160;
        {/if}

    {* Имя пользователя *}
        <strong>{if $cur_post.poster_id > 1}<a href="profile.php?id={$cur_post.poster_id}">{$cur_post.username|escape}</a>{else}{$cur_post.username|escape}{/if}</strong>

        {if $cur_post.poster_id > 1}
            &#160;
            {if $cur_post.is_online == $cur_post.poster_id}
            {* Отображение присутствия/отсутствия *}
                <span class="green">{$lang_topic.Online_m}</span>
                {else}
                <span class="grey">{$lang_topic.Offline_m}</span>
            {/if}

            {if $pun_config.o_show_post_karma == 1 || $pun_user.g_id < $smarty.const.PUN_GUEST}
            {* Карма *}
                &#160;({$cur_post.karma.val|default:'no value'})
                {if ! $pun_user.is_guest and ! $cur_post.karma.used|default:'1'}
                    &#160;<a href="karma.php?to={$cur_post.poster_id}&amp;vote=1&amp;pid={$cur_post.id}">+</a>/<a href="karma.php?to={$cur_post.poster_id}&amp;vote=-1&amp;pid={$cur_post.id}">-</a>
                {/if}
            {/if}
        {/if}
        <br/>
    {* Должность *}
            {get_title($cur_post)}<br/>
    {* Когда было размещено сообщение *}
            {$cur_post.posted|date_format:$date_format}<br/>
    </div>

{* Сообщение *}
    {$cur_post.message}

    {if $is_admmod and isset($cur_post.spam_id)}
    {* Анти-спам *}
    {include file='`$smarty.const.PUN_ROOT`lang/`$pun_user.language`/misc.php'}

        <div class="antispam">
            {$lang_misc.$Antispam_pattern} - {$cur_post.pattern|escape}<br/>
            <a href="./antispam_misc.php?action=allow&amp;id={$cur_post.spam_id}">{$lang_misc.$Antispam_tread}</a>{$lang_topic.$Link_separator_m}
            <a href="./antispam_misc.php?action=deny&amp;id={$cur_post.spam_id}">{$lang_misc.$Antispam_del}</a>
        </div>
    {/if}


    {if $attachments[$cur_post.id]}
    {* Вложения *}
        <div class="attach_list">
            <strong>{$lang_fu.Attachments}</strong><br/>
            {foreach from=$attachments[$cur_post.id] item=attachment}

                {if $can_download}

                    {if $basename == 'edit.php'}
                        <label><input type="checkbox" name="delete_image[]" value="{$attachment.id}"/> {$lang_fu.$Mark_to_Delete}<label>
                    {/if}

                    <a href="{$pun_config.o_base_url}/download.php?aid={$attachment.id}">{$attachment.filename|escape}</a>
                    {else}
                    <span class="red">{$attachment.filename|escape}</span>
                {/if}

                {if $attachment.size >= 1048576}
                    {round($attachment.size / 1048576, 0)} mb
                {else}
                    {round($attachment.size / 1024, 0)} kb
                {/if}

                {if 'image' == strTok($attachment.mime, '/')}
                    , {strtok('/')} {$attachment.image_dim}
                {/if}
                [<strong>{$lang_fu.Downloads}: {$attachment.downloads}</strong>]<br/>
            {/foreach}
        </div>
    {/if}

    {if $cur_post.edited}
    {* Время последнего редактирования сообщения *}
        <div class="small">
            {$lang_topic.$Last_edit} {$cur_post.edited_by|escape} ({$cur_post.edited|date_format:$date_format})
        </div>
    {/if}

    {if $cur_post.signature && $pun_user.show_sig}
    {* Подпись пользователя *}
        {if ! isset($signature_cache[$cur_post.poster_id])}
            {$signature_cache[$cur_post.poster_id] = parse_signature($cur_post.signature)}
        {/if}
        <div class="hr">
            {$signature_cache[$cur_post.poster_id]}
        </div>
    {/if}

</div>

{include file='footer.tpl'}