<?php /* Smarty version Smarty-3.1.7, created on 2012-03-09 21:54:30
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\viewtopic.tpl" */ ?>
<?php /*%%SmartyHeaderCode:161054f587cce3d7b75-84448950%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0a68d5c74cc016502e34f01ddbe1790e2682a83a' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\viewtopic.tpl',
      1 => 1331199774,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '161054f587cce3d7b75-84448950',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f587cceb481d',
  'variables' => 
  array (
    'lang_common' => 0,
    'cur_topic' => 0,
    'posts' => 0,
    'post_count' => 0,
    'j' => 0,
    'cur_post' => 0,
    'pun_config' => 0,
    'pun_user' => 0,
    'start_from' => 0,
    'lang_topic' => 0,
    'date_format' => 0,
    'is_admmod' => 0,
    'Link_separator_m' => 0,
    'id' => 0,
    'Post_reply_m' => 0,
    'Antispam_pattern' => 0,
    'lang_misc' => 0,
    'Antispam_tread' => 0,
    'Antispam_del' => 0,
    'attachments' => 0,
    'lang_fu' => 0,
    'can_download' => 0,
    'basename' => 0,
    'attachment' => 0,
    'Mark_to_Delete' => 0,
    'Last_edit' => 0,
    'signature_cache' => 0,
    'paging_links' => 0,
    'Topic_closed' => 0,
    'Post_reply' => 0,
    'quickpost' => 0,
    'Quick_post' => 0,
    'Write_message_legend' => 0,
    'Merge_posts' => 0,
    'lang_post' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f587cceb481d')) {function content_4f587cceb481d($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\modifier.date_format.php';
?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>



<div class="inbox">
    <a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a>&#160;&#187;&#160;<a href="viewforum.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['forum_id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_topic']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</a>&#160;&#187;&#160;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_topic']->value['subject'], ENT_QUOTES, 'UTF-8', true);?>

</div>


<?php $_smarty_tpl->tpl_vars['Last_edit'] = new Smarty_variable('Last edit', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Link_separator_m'] = new Smarty_variable('Link separator_m', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Post_reply_m'] = new Smarty_variable('Post reply_m', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Post_reply'] = new Smarty_variable('Post reply', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Quick_post'] = new Smarty_variable('Quick post', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Write_message_legend'] = new Smarty_variable('Write message legend', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Merge_posts'] = new Smarty_variable('Merge posts', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Topic_closed'] = new Smarty_variable('Topic closed', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Mark_to_Delete'] = new Smarty_variable('Mark to Delete', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Antispam_pattern'] = new Smarty_variable('Antispam pattern', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Antispam_tread'] = new Smarty_variable('Antispam tread', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Antispam_del'] = new Smarty_variable('Antispam del', null, 0);?>


<?php $_smarty_tpl->tpl_vars['signature_cache'] = new Smarty_variable('', null, 0);?>


<?php $_smarty_tpl->tpl_vars['date_format'] = new Smarty_variable('%d/%m/%y %H:%I:%S', null, 0);?>


<?php $_smarty_tpl->tpl_vars['post_cont'] = new Smarty_variable('0', null, 0);?>


<?php $_smarty_tpl->tpl_vars['j'] = new Smarty_variable('false', null, 0);?>

<?php  $_smarty_tpl->tpl_vars['cur_post'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_post']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['posts']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_post']->key => $_smarty_tpl->tpl_vars['cur_post']->value){
$_smarty_tpl->tpl_vars['cur_post']->_loop = true;
?>

<?php $_smarty_tpl->tpl_vars['post_count'] = new Smarty_variable(($_smarty_tpl->tpl_vars['post_count']->value+1), null, 0);?>

<div class="<?php if (!isset($_smarty_tpl->tpl_vars['j'])) $_smarty_tpl->tpl_vars['j'] = new Smarty_Variable(null);if ($_smarty_tpl->tpl_vars['j']->value = !$_smarty_tpl->tpl_vars['j']->value){?>msg<?php }else{ ?>msg2<?php }?>">
<div class="zag_in" id="p<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
">

<?php if ($_smarty_tpl->tpl_vars['cur_post']->value['poster_id']>1&&$_smarty_tpl->tpl_vars['pun_config']->value['o_avatars']==1&&$_smarty_tpl->tpl_vars['cur_post']->value['use_avatar']==1&&$_smarty_tpl->tpl_vars['pun_user']->value['show_avatars']){?>

    <img src="<?php echo @PUN_ROOT;?>
<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_avatars_dir'];?>
/<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['poster_id'];?>
.<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['avatar_type'];?>
" alt="*" />&#160;
<?php }?>


<a href="viewtopic.php?pid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
#p<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
">#<?php echo ($_smarty_tpl->tpl_vars['start_from']->value+$_smarty_tpl->tpl_vars['post_count']->value);?>
.</a>&#160;<strong><?php if ($_smarty_tpl->tpl_vars['cur_post']->value['poster_id']>1){?><a href="profile.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['poster_id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['username'], ENT_QUOTES, 'UTF-8', true);?>
</a><?php }else{ ?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['username'], ENT_QUOTES, 'UTF-8', true);?>
<?php }?></strong>

<?php if ($_smarty_tpl->tpl_vars['cur_post']->value['poster_id']>1){?>
    &#160;
    <?php if ($_smarty_tpl->tpl_vars['cur_post']->value['is_online']==$_smarty_tpl->tpl_vars['cur_post']->value['poster_id']){?>
    
        <span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Online_m'];?>
</span>
    <?php }else{ ?>
        <span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Offline_m'];?>
</span>
    <?php }?>
    
    <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_show_post_karma']==1||$_smarty_tpl->tpl_vars['pun_user']->value['g_id']<@PUN_GUEST){?>
        
        &#160;(<?php echo (($tmp = @$_smarty_tpl->tpl_vars['cur_post']->value['karma']['val'])===null||$tmp==='' ? 'no value' : $tmp);?>
)
        <?php if (!$_smarty_tpl->tpl_vars['pun_user']->value['is_guest']&&!(($tmp = @$_smarty_tpl->tpl_vars['cur_post']->value['karma']['used'])===null||$tmp==='' ? '1' : $tmp)){?>
            &#160;<a href="karma.php?to=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['poster_id'];?>
&amp;vote=1&amp;pid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
">+</a>/<a href="karma.php?to=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['poster_id'];?>
&amp;vote=-1&amp;pid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
">-</a>
        <?php }?>
    <?php }?>
<?php }?>
<br/>

<?php echo get_title($_smarty_tpl->tpl_vars['cur_post']->value);?>
<br/>

<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['cur_post']->value['posted'],$_smarty_tpl->tpl_vars['date_format']->value);?>
<br/>

<?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>

<a href="delete.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Delete_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="edit.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Edit_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;qid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Quote_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;rid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Post_reply_m']->value];?>
</a>
<?php }else{ ?>
    <?php if (!$_smarty_tpl->tpl_vars['cur_topic']->value['closed']){?>
        <?php if ($_smarty_tpl->tpl_vars['cur_post']->value['poster_id']==$_smarty_tpl->tpl_vars['pun_user']->value['id']){?>
            
            <?php if ((($_smarty_tpl->tpl_vars['start_from']->value+$_smarty_tpl->tpl_vars['post_count']->value)==1&&$_smarty_tpl->tpl_vars['pun_user']->value['g_delete_topics']==1)||(($_smarty_tpl->tpl_vars['start_from']->value+$_smarty_tpl->tpl_vars['post_count']->value)>1&&$_smarty_tpl->tpl_vars['pun_user']->value['g_delete_posts']==1)){?>
                <a href="delete.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Delete_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>

            <?php }?>
            
            <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_edit_posts']==1){?>
                <a href="edit.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Edit_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>

            <?php }?>
            
        <?php }?>

        <?php if ((!$_smarty_tpl->tpl_vars['cur_topic']->value['post_replies']&&$_smarty_tpl->tpl_vars['pun_user']->value['g_post_replies']==1)||$_smarty_tpl->tpl_vars['cur_topic']->value['post_replies']==1){?>
            
            <a href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;qid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value['Quote_m'];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>

            <a href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;rid=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Post_reply_m']->value];?>
</a>
        <?php }?>
    <?php }?>
<?php }?>
</div>


<?php echo parse_message($_smarty_tpl->tpl_vars['cur_post']->value['message'],$_smarty_tpl->tpl_vars['cur_post']->value['hide_smilies'],$_smarty_tpl->tpl_vars['cur_post']->value['id']);?>


<?php if ($_smarty_tpl->tpl_vars['is_admmod']->value&&isset($_smarty_tpl->tpl_vars['cur_post']->value['spam_id'])){?>

<?php echo $_smarty_tpl->getSubTemplate ('`$smarty.const.PUN_ROOT`lang/`$pun_user.language`/misc.php', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<div class="antispam">
<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Antispam_pattern']->value];?>
 - <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['pattern'], ENT_QUOTES, 'UTF-8', true);?>
<br/>
<a href="./antispam_misc.php?action=allow&amp;id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['spam_id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Antispam_tread']->value];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>

<a href="./antispam_misc.php?action=deny&amp;id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['spam_id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Antispam_del']->value];?>
</a>
</div>
<?php }?>


<?php if ($_smarty_tpl->tpl_vars['attachments']->value[$_smarty_tpl->tpl_vars['cur_post']->value['id']]){?>

<div class="attach_list">
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_fu']->value['Attachments'];?>
</strong><br/>
    <?php  $_smarty_tpl->tpl_vars['attachment'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['attachment']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['attachments']->value[$_smarty_tpl->tpl_vars['cur_post']->value['id']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['attachment']->key => $_smarty_tpl->tpl_vars['attachment']->value){
$_smarty_tpl->tpl_vars['attachment']->_loop = true;
?>

        <?php if ($_smarty_tpl->tpl_vars['can_download']->value){?>
            
            <?php if ($_smarty_tpl->tpl_vars['basename']->value=='edit.php'){?>
                <input type="checkbox" name="delete_image[]" value="<?php echo $_smarty_tpl->tpl_vars['attachment']->value['id'];?>
" /> <?php echo $_smarty_tpl->tpl_vars['lang_fu']->value[$_smarty_tpl->tpl_vars['Mark_to_Delete']->value];?>

            <?php }?>
            
            <a href="<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_base_url'];?>
/download.php?aid=<?php echo $_smarty_tpl->tpl_vars['attachment']->value['id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['attachment']->value['filename'], ENT_QUOTES, 'UTF-8', true);?>
</a>
        <?php }else{ ?>
            <span class="red"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['attachment']->value['filename'], ENT_QUOTES, 'UTF-8', true);?>
</span>
        <?php }?>
        
        <?php if ($_smarty_tpl->tpl_vars['attachment']->value['size']>=1048576){?>
            <?php echo round($_smarty_tpl->tpl_vars['attachment']->value['size']/1048576,0);?>
 mb
        <?php }else{ ?>
            <?php echo round($_smarty_tpl->tpl_vars['attachment']->value['size']/1024,0);?>
 kb
        <?php }?>
        
        <?php if ('image'==strTok($_smarty_tpl->tpl_vars['attachment']->value['mime'],'/')){?>
            , <?php echo strtok('/');?>
 <?php echo $_smarty_tpl->tpl_vars['attachment']->value['image_dim'];?>

        <?php }?>
        [<strong><?php echo $_smarty_tpl->tpl_vars['lang_fu']->value['Downloads'];?>
: <?php echo $_smarty_tpl->tpl_vars['attachment']->value['downloads'];?>
</strong>]<br/>
    <?php } ?>
</div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['cur_post']->value['edited']){?>

    <div class= "small">
        <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Last_edit']->value];?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['edited_by'], ENT_QUOTES, 'UTF-8', true);?>
 (<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['cur_post']->value['edited'],$_smarty_tpl->tpl_vars['date_format']->value);?>
)
    </div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['cur_post']->value['signature']&&$_smarty_tpl->tpl_vars['pun_user']->value['show_sig']){?>

    <?php if (!isset($_smarty_tpl->tpl_vars['signature_cache']->value[$_smarty_tpl->tpl_vars['cur_post']->value['poster_id']])){?>
        <?php $_smarty_tpl->createLocalArrayVariable('signature_cache', null, 0);
$_smarty_tpl->tpl_vars['signature_cache']->value[$_smarty_tpl->tpl_vars['cur_post']->value['poster_id']] = parse_signature($_smarty_tpl->tpl_vars['cur_post']->value['signature']);?>
    <?php }?>
<div class="hr">
<?php echo $_smarty_tpl->tpl_vars['signature_cache']->value[$_smarty_tpl->tpl_vars['cur_post']->value['poster_id']];?>

</div>
<?php }?>

</div>
<?php } ?>

<div class="con"><?php echo $_smarty_tpl->tpl_vars['paging_links']->value;?>
</div>

<?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_post_replies']){?>
    
    <div class="go_to">
    <?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['closed']){?>
            <strong>#<?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Topic_closed']->value];?>
</strong>
            <?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>
                <a class="but" href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Post_reply']->value];?>
</a>
            <?php }?>
    <?php }else{ ?>
        <a class="but" href="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Post_reply']->value];?>
</a>
    <?php }?>
    </div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['quickpost']->value){?>

<form method="post" action="post.php?tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">
    
    <div class="input">
        <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Quick_post']->value];?>
:<br/>
        <span class="small"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Write_message_legend']->value];?>
</span><br/>

    <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_antiflood']){?>
        <input type="hidden" name="form_t" value="<?php echo $_SERVER['REQUEST_TIME'];?>
" />
    <?php }?>
    
    <input type="hidden" name="form_sent" value="1" />
    <input type="hidden" name="form_user" value="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['pun_user']->value['username'], ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 'Guest' : $tmp);?>
" />
    
    <!-- input name for guest -->
    <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
        <?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Username'];?>
<br/>
        <input type="text" name="req_username" tabindex="1" /><br/>
    <?php }?>
    
    <textarea name="req_message" rows="4" cols="24" tabindex="1"></textarea><br/>

    <?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>
        <input type="checkbox" name="merge" value="1" checked="checked" />&#160;<span class="small"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Merge_posts']->value];?>
</span><br/>
    <?php }?>
    
    <input type="submit" name="submit" tabindex="2" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" accesskey="s" />
    </div>
</form>
<?php }?>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>