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
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;<a href="viewforum.php?id={$cur_topic.forum_id}">{$cur_topic.forum_name|escape}</a>&#160;&#187;&#160;{$cur_topic.subject|escape}
</div>

{$show_poll}


{* Счетчик сообщений *}
{assign var='post_cont' value='0'}

{* Для чередующегося цвета фона сообщения *}
{assign var='j' value='false'}

{foreach from=$posts item=cur_post}

{assign var='post_count' value=($post_count + 1)}

<div class="{if $j = ! $j}msg{else}msg2{/if}">
<div class="zag_in" id="p{$cur_post.id}">

{if $cur_post.poster_id > 1 and $pun_config.o_avatars == 1 && $cur_post.use_avatar == 1 && $pun_user.show_avatars}
{* Аватарка *}
    <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$cur_post.poster_id}.{$cur_post.avatar_type}" alt="*" />&#160;
{/if}

{* Номер сообщения и имя пользователя *}
<a href="viewtopic.php?pid={$cur_post.id}#p{$cur_post.id}">#{($start_from + $post_count)}.</a>&#160;<strong>{if $cur_post.poster_id > 1}<a href="profile.php?id={$cur_post.poster_id}">{$cur_post.username|escape}</a>{else}{$cur_post.username|escape}{/if}</strong>

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

{if $is_admmod}
{* Административные ссылки для управления сообщением *}
<a href="delete.php?id={$cur_post.id}">{$lang_topic.Delete_m}</a>{$lang_topic.$Link_separator_m}<a href="edit.php?id={$cur_post.id}">{$lang_topic.Edit_m}</a>{$lang_topic.$Link_separator_m}<a href="post.php?tid={$id}&amp;qid={$cur_post.id}">{$lang_topic.Quote_m}</a>{$lang_topic.$Link_separator_m}<a href="post.php?tid={$id}&amp;rid={$cur_post.id}">{$lang_topic.$Post_reply_m}</a>
{else}
    {if ! $cur_topic.closed}
        {if $cur_post.poster_id == $pun_user.id}
            {* Пользовательские ссылки для управления сообщением *}
            {if (($start_from + $post_count) == 1 && $pun_user.g_delete_topics == 1) || (($start_from + $post_count) > 1 && $pun_user.g_delete_posts == 1)}
                <a href="delete.php?id={$cur_post.id}">{$lang_topic.Delete_m}</a>{$lang_topic.$Link_separator_m}
            {/if}
            
            {if $pun_user.g_edit_posts == 1}
                <a href="edit.php?id={$cur_post.id}">{$lang_topic.Edit_m}</a>{$lang_topic.$Link_separator_m}
            {/if}
            
        {/if}

        {if (! $cur_topic.post_replies && $pun_user.g_post_replies == 1) || $cur_topic.post_replies == 1}
            {* Ссылки для цитирования/ответа *}
            <a href="post.php?tid={$id}&amp;qid={$cur_post.id}">{$lang_topic.Quote_m}</a>{$lang_topic.$Link_separator_m}
            <a href="post.php?tid={$id}&amp;rid={$cur_post.id}">{$lang_topic.$Post_reply_m}</a>
        {/if}
    {/if}
{/if}
</div>

{* Сообщение *}
{parse_message($cur_post.message, $cur_post.hide_smilies, $cur_post.id)}

{if $is_admmod and isset($cur_post.spam_id)}
{* Анти-спам *}

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
                <input type="checkbox" name="delete_image[]" value="{$attachment.id}" /> {$lang_fu.$Mark_to_Delete}
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
    <div class= "small">
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
{/foreach}

<div class="con">{$paging_links}</div>

{if $pun_user.g_post_replies}
    {* Кнопка для ответа *}
    <div class="go_to">
    {if $cur_topic.closed}
            <strong>#{$lang_topic.$Topic_closed}</strong>
            {if $is_admmod}
                <a class="but" href="post.php?tid={$id}">{$lang_topic.$Post_reply}</a>
            {/if}
    {else}
        <a class="but" href="post.php?tid={$id}">{$lang_topic.$Post_reply}</a>
    {/if}
    </div>
{/if}

{if $quickpost}
{* Форма для быстрого ответа *}
<form method="post" action="post.php?tid={$id}">
    
    <div class="input">
        {$lang_topic.$Quick_post}:<br/>
        <span class="small">{$lang_common.$Write_message_legend}</span><br/>

    {if $pun_config.o_antiflood}
        <input type="hidden" name="form_t" value="{$smarty.server.REQUEST_TIME}" />
    {/if}
    
    <input type="hidden" name="form_sent" value="1" />
    <input type="hidden" name="form_user" value="{$pun_user.username|escape|default:'Guest'}" />
    
    <!-- input name for guest -->
    {if $pun_user.is_guest}
        {$lang_common.Username}<br/>
        <input type="text" name="req_username" tabindex="1" /><br/>
    {/if}
    
    <textarea name="req_message" rows="4" cols="24" tabindex="1"></textarea><br/>

    {if $is_admmod}
        <label for="merge"><input type="checkbox" id="merge" name="merge" value="1" checked="checked" />&#160;<span class="small">{$lang_post.$Merge_posts}</span><br/></label>
    {/if}
    
    <input type="submit" name="submit" tabindex="2" value="{$lang_common.Submit}" accesskey="s" />
    </div>
</form>
{/if}

{include file='footer.tpl'}