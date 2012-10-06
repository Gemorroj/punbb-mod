{include file='header.tpl'}


<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187;

{if $tid}
    <a href="viewforum.php?id={$cur_posting.id}">{$cur_posting.forum_name|escape}</a>
    {elseif $fid}
    {$cur_posting.forum_name|escape}
{/if}

{if isset($cur_posting.subject)}
    &#187; {$cur_posting.subject|escape}
{/if}
</div>

{if $errors}

    {assign var='Post_errors' value='Post errors'}
<div class="red">{$lang_post.$Post_errors}</div>

<div class="msg">
    {foreach from=$errors item=cur_error}
        &#187; {$cur_error}<br/>
    {/foreach}
</div>

    {elseif isset($smarty.post.preview)}

    {assign var='Post_preview' value='Post preview'}
<div class="info">{$lang_post.$Post_preview}</div>
<div class="msg">{$message_preview}</div>
{/if}

{assign var='Post_a_reply' value='Post a reply'}
{assign var='Post_new_topic' value='Post new topic'}


<div class="con">
{if $tid}
    {$lang_post.$Post_a_reply}
{elseif $fid}
    {$lang_post.$Post_new_topic}
{/if}
</div>

{if $tid}
<form method="post" action="post.php?action=post&amp;tid={$tid}" {if $file_limit}enctype="multipart/form-data"{/if}>
{elseif $fid}
<form method="post" action="post.php?action=post&amp;fid={$fid}" enctype="multipart/form-data">
{/if}

    <div class="input">

    {$poll_container}

        <input type="hidden" name="form_sent" value="1"/>
        <input type="hidden" name="form_user" value="{$pun_user.username|escape|default:'Guest'}"/>

    {if $pun_user.is_guest}

        {assign var='Guest_name' value='Guest name'}

        <strong>{$lang_post.$Guest_name}</strong><br/>
        <input type="text" name="req_username" value="{$username|escape}" /><br/>
            {if $pun_config.p_force_guest_email == 1}
            <strong>{$lang_common.E-mail}</strong>{else}{$lang_common.E-mail}{/if}<br/>
        <input type="text" name="{if $pun_config.p_force_guest_email == 1}req_email{else}email{/if}"
               value="{$email|escape}" /><br/>
    {/if}

    {if $fid}
        <strong>{$lang_common.Subject}</strong><br/>
        <input type="text" name="req_subject" value="{$subject|escape}" maxlength="70" /><br/>
    {/if}

        <textarea name="req_message" rows="4" cols="24">{if isset($smarty.post.req_message)}{$message|escape}{elseif $quote}{$quote|escape}{/if}</textarea><br/>

        <a href="help.php?id=3">{$lang_common.Smilies}</a>
        {if $pun_config.o_smilies == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if}
        <a href="help.php?id=1">{$lang_common.BBCode}</a>
        {if $pun_config.p_message_bbcode == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if}
    {assign var='img_tag' value='img tag'}
        <a href="help.php?id=4">{$lang_common.$img_tag}</a>
        {if $pun_config.p_message_img_tag == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if}
    </div>

    <div class="input2">

    {if $pun_user.g_post_replies == 2}
        <img src="{$pun_config.o_base_url}/include/captcha/captcha.php?{session_name()}={session_id()}" alt=""/><br/>
        {assign var='Image_text' value='Image text'}
            {$lang_post.$Image_text}<br/>
        <input type="text" name="req_image_" size="16" maxlength="16"/><br/>
    {/if}

    {* Attachmenst *}
    {if $can_upload && $num_to_upload > 0}
            {$lang_fu.Attachments}<br/>
    <input type="file" name="attach[]"/><br/>
    {/if}

    {assign var='Hide_smilies' value='Hide smilies'}
    {assign var='Merge_posts'  value='Merge posts'}

    {if ! $pun_user.is_guest}
        {if $pun_config.o_smilies == 1}
            <label for="hide_smilies"><input type="checkbox" id="hide_smilies" name="hide_smilies" value="1"
                                             {if isset($smarty.post.hide_smilies)}checked="checked"{/if} />{$lang_post.$Hide_smilies}
            <br/></label>
        {/if}

        {if $is_admmod}
            <label for="merge"><input type="checkbox" id="merge" name="merge" value="1"
                                      checked="checked"/>{$lang_post.$Merge_posts}<br/></label>
        {/if}

        {if $pun_config.o_subscriptions == 1}
            <label for="subscribe"><input type="checkbox" id="subscribe" name="subscribe" value="1"
                                          {if isset($smarty.post.subscribe)}checked="checked"{/if} />{$lang_post.Subscribe}
            <br/></label>
        {/if}
        {elseif $pun_config.o_smilies == 1}
        <label for="hide_smilies"><input type="checkbox" id="hide_smilies" name="hide_smilies" value="1"
                                         {if isset($smary.post.hide_smilies)}checked="checked"{/if} />{$lang_post.$Hide_smilies}
        <br/></label>
    {/if}

    </div>
    <div class="go_to">
        <input type="hidden" name="form_t" value="{$smarty.server.REQUEST_TIME}"/>
        <input type="submit" name="submit" value="{$lang_common.Submit}" accesskey="s"/>
        <input type="submit" name="preview" value="{$lang_post.Preview}" accesskey="p"/>
    </div>
</form>

{include file='footer.tpl'}
