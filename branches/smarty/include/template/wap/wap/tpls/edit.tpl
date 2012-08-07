{include file='header.tpl'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a
        href="viewforum.php?id={$cur_post.fid}">{$cur_post.forum_name|escape}</a> &#187; {$cur_post.subject|escape}
</div>

{if $errors}
<div class="red">
    {assign var='Post_errors' value='Post errors'}
{$lang_post.$Post_errors}
</div>
    {assign var='Post_errors_info' value='Post errors info'}
<div class="msg">{$lang_post.$Post_errors_info}
    {foreach from=$errors item=cur_error}
        &#187; {$cur_error}<br/>
    {/foreach}
</div>
    {elseif $smarty.post.preview}
{*include file="`$smarty.const.PUN_ROOT`include/parser.php"*}
    {assign var='Post_preview' value='Post preview'}
<div class="info">{$lang_post.$Post_preview}</div>
<div class="msg">{$preview_message}</div>
{/if}

{assign var='Edit_post' value='Edit post'}
<div class="con">{$lang_post.$Edit_post}</div>

<form method="post" action="edit.php?id={$id}&amp;action=edit" enctype="multipart/form-data">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>
    {if $can_edit_subject}
            {$lang_common.Subject}<br/>
        <input type="text" name="req_subject" tabindex="{assign var='cur_index' value=$cur_index+1}"
               value="{{$smarty.post.req_subject|default:$cur_post.subject}|escape}"/><br/>
        <!-- /label -->
    {/if}

    {* include file='{$smarty.const.PUN_ROOT}include/attach/fetch.php' *}

    {$lang_common.Message}:<br/>
        <textarea name="req_message" rows="4" cols="24"
                  tabindex="{assign var='cur_index' value=$cur_index+1}">{{$message|default:$cur_post.message}|escape}</textarea><br/>
        <a href="help.php?id=3">{$lang_common.Smilies}</a>
    {if $pun_config.o_smilies == 1}
        <span class="green">{$lang_common.on_m}</span>;
        {else}
        <span class="grey">{$lang_common.off_m}</span>;
    {/if}
        <a href="help.php?id=1">{$lang_common.BBCode}</a>
    {if $pun_config.p_message_bbcode == 1}
        <span class="green">{$lang_common.on_m}</span>;
        {else}
        <span class="grey">{$lang_common.off_m}</span>;
    {/if}
    {assign var='img_tag' value='img tag'}
        <a href="help.php?id=4">{$lang_common.$img_tag}</a>
    {if $pun_config.p_message_img_tag == 1}
        <span class="green">{$lang_common.on_m}</span>
        {else}
        <span class="grey">{$lang_common.off_m}</span>;
    {/if}
        <br/>

    {if $uploaded_to_post || ($can_upload && $num_to_upload > 0)}
        {* Attachments *}
        {include file='attachments.tpl'}
        {if $can_upload && $num_to_upload > 0}
            </div>
            <div class="input2">{$lang_fu.$Choose_a_file}<br/>
        {/if}
        <input type="file" name="attach[]"/><br/>
    {/if}

    {if $pun_config.o_smilies == 1}
        {assign var='Hide_smilies' value='Hide smilies'}
        <label for="hide_smilies"><input type="checkbox" id="hide_smilies" name="hide_smilies" value="1"
               {if isset($smarty.post.hide_smilies) || $cur_post.hide_smilies == 1}checked="checked"{/if}
               tabindex="{assign var='cur_index' value=$cur_index+1}"/> {$lang_post.$Hide_smilies}<br/></label>
    {/if}

    {if $is_admmod}
        <label for="silent"><input type="checkbox" id="silent" name="silent" value="1" tabindex="{assign var='cur_index' value=$cur_index+1}"
               {if (isset($smarty.post.form_sent) && isset($smarty.post.silent)) || ! isset($smarty.post.form_sent)}checked="checked"{/if} /> {$lang_post.$Silent_edit}
        <br/></label>
    {/if}


    </div>
    <div class="go_to">
        <input type="submit" name="submit" value="{$lang_common.Submit}"
               tabindex="{assign var='cur_index' value=$cur_index+1}" accesskey="s"/>
        <input type="submit" name="preview" value="{$lang_post.Preview}"
               tabindex="{assign var='cur_index' value=$cur_index+1}" accesskey="p"/>
    </div>
</form>

{include file='footer.tpl'}