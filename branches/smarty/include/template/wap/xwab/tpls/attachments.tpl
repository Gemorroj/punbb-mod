{* Attachments / Вложения *}
{assign var='Mark_to_Delete' value='Mark to Delete'}
<div class="attach_list">
    <strong>{$lang_fu.Attachments}</strong><br/>
    {foreach from=$attachments[$cur_post.id] item=attachment}

        {if $can_download}

            {if $basename == 'edit.php'}
                <label><input type="checkbox" name="delete_image[]" value="{$attachment.id}"/> {$lang_fu.$Mark_to_Delete}</label>
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