<?php /* Smarty version Smarty-3.1.7, created on 2012-03-19 17:00:38
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:34614f671cbe1b4501-22652006%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '77792b02bab6e0b02b4dcd9345be588f2c5255b3' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\post.tpl',
      1 => 1332162037,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '34614f671cbe1b4501-22652006',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f671cbe22ca3',
  'variables' => 
  array (
    'lang_common' => 0,
    'tip' => 0,
    'cur_posting' => 0,
    'fid' => 0,
    'errors' => 0,
    'Post_errors' => 0,
    'lang_post' => 0,
    'cur_error' => 0,
    'Post_preview' => 0,
    'tid' => 0,
    'Post_a_reply' => 0,
    'Post_new_topic' => 0,
    'file_limit' => 0,
    'pun_user' => 0,
    'Guest_name' => 0,
    'username' => 0,
    'cur_index' => 0,
    'pun_config' => 0,
    'email' => 0,
    'subject' => 0,
    'message' => 0,
    'quote' => 0,
    'img_tag' => 0,
    'Image_text' => 0,
    'can_upload' => 0,
    'num_to_upload' => 0,
    'lang_fu' => 0,
    'Hide_smilies' => 0,
    'is_admmod' => 0,
    'Merge_posts' => 0,
    'smary' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f671cbe22ca3')) {function content_4f671cbe22ca3($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<div class="inbox">
<a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a> &#187;

<?php if ($_smarty_tpl->tpl_vars['tip']->value){?>
<a href="viewforum.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_posting']->value['id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_posting']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</a>
<?php }elseif($_smarty_tpl->tpl_vars['fid']->value){?>
<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_posting']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>

<?php }?>

<?php if (isset($_smarty_tpl->tpl_vars['cur_posting']->value['subject'])){?>
&#187; <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_posting']->value['subject'], ENT_QUOTES, 'UTF-8', true);?>

<?php }?>
</div>

<?php if ($_smarty_tpl->tpl_vars['errors']->value){?>

<?php $_smarty_tpl->tpl_vars['Post_errors'] = new Smarty_variable('Post errors', null, 0);?>
<div class="red"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_errors']->value];?>
</div>

<div class="msg">
<?php  $_smarty_tpl->tpl_vars['cur_error'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_error']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['errors']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_error']->key => $_smarty_tpl->tpl_vars['cur_error']->value){
$_smarty_tpl->tpl_vars['cur_error']->_loop = true;
?>
&#187; <?php echo $_smarty_tpl->tpl_vars['cur_error']->value;?>
<br/>
<?php } ?>
</div>

<?php }elseif(isset($_POST['preview'])){?>

<?php $_smarty_tpl->tpl_vars['Post_preview'] = new Smarty_variable('Post preview', null, 0);?>
<div class="info"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_preview']->value];?>
</div>
<div class="msg"></div>
<?php }?>

<?php $_smarty_tpl->tpl_vars['Post_a_reply'] = new Smarty_variable('Post a reply', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Post_new_topic'] = new Smarty_variable('Post new topic', null, 0);?>


<div class="con">
<?php if ($_smarty_tpl->tpl_vars['tid']->value){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_a_reply']->value];?>

<?php }elseif($_smarty_tpl->tpl_vars['fid']->value){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_new_topic']->value];?>

<?php }?>
</div>

<?php if ($_smarty_tpl->tpl_vars['tid']->value){?>
<form method="post" action="post.php?action=post&amp;tid=<?php echo $_smarty_tpl->tpl_vars['tid']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['file_limit']->value){?>enctype="multipart/form-data"<?php }?>>
<?php }elseif($_smarty_tpl->tpl_vars['fid']->value){?>
<form method="post" action="post.php?action=post&amp;fid=<?php echo $_smarty_tpl->tpl_vars['fid']->value;?>
" enctype="multipart/form-data">
<?php }?>

<div class="input">



<input type="hidden" name="form_sent" value="1" />
<input type="hidden" name="form_user" value="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['pun_user']->value['username'], ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 'Guest' : $tmp);?>
" />

<?php if ($_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
    
    <?php $_smarty_tpl->tpl_vars['Guest_name'] = new Smarty_variable('Guest name', null, 0);?>
    
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Guest_name']->value];?>
</strong><br />
    <input type="text" name="req_username" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['username']->value, ENT_QUOTES, 'UTF-8', true);?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" /><br />
    <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_force_guest_email']==1){?><strong><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['E']-'mail';?>
</strong><?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['E']-'mail';?>
<?php }?><br />
    <input type="text" name="<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_force_guest_email']==1){?>req_email<?php }else{ ?>email<?php }?>" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['email']->value, ENT_QUOTES, 'UTF-8', true);?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" /><br />
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['fid']->value){?>
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Subject'];?>
</strong><br />
    <input type="text" name="req_subject" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['subject']->value, ENT_QUOTES, 'UTF-8', true);?>
" maxlength="70" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" /><br />
<?php }?>

<textarea name="req_message" rows="4" cols="24" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>">
<?php if ($_POST['req_message']){?>
<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['message']->value, ENT_QUOTES, 'UTF-8', true);?>

<?php }elseif($_smarty_tpl->tpl_vars['quote']->value){?>
<?php echo $_smarty_tpl->tpl_vars['quote']->value;?>

<?php }?>
</textarea><br />

<a href="help.php?id=3"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Smilies'];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_smilies']==1){?><span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span><?php }else{ ?><span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span><?php }?>
<a href="help.php?id=1"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['BBCode'];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_message_bbcode']==1){?><span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span><?php }else{ ?><span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span><?php }?>
<?php $_smarty_tpl->tpl_vars['img_tag'] = new Smarty_variable('img tag', null, 0);?>
<a href="help.php?id=4"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['img_tag']->value];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_message_img_tag']==1){?><span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span><?php }else{ ?><span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span><?php }?>
</div>

<div class="input2">

<?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_post_replies']==2){?>
<img src="<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_base_url'];?>
/include/captcha/captcha.php?<?php echo session_name();?>
=<?php echo session_id();?>
" alt="" /><br />
<?php $_smarty_tpl->tpl_vars['Image_text'] = new Smarty_variable('Image text', null, 0);?>
<?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Image_text']->value];?>
<br />
<input type="text" name="req_image_" size="16" maxlength="16" /><br />
<?php }?>

<?php $_smarty_tpl->tpl_vars['num_to_upload'] = new Smarty_variable(min($_smarty_tpl->tpl_vars['file_limit']->value,20), null, 0);?>

<?php if ($_smarty_tpl->tpl_vars['can_upload']->value&&$_smarty_tpl->tpl_vars['num_to_upload']->value>0){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_fu']->value['Attachments'];?>
<br/>
    <?php echo $_smarty_tpl->getSubTemplate ('{$smarty.const.PUN_ROOT}include/attach/wap_post_input.php', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php }?>

<?php $_smarty_tpl->tpl_vars['Hide_smilies'] = new Smarty_variable('Hide smilies', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Merge_posts'] = new Smarty_variable('Merge posts', null, 0);?>

<?php if (!$_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
    <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_smilies']==1){?>
    <input type="checkbox" name="hide_smilies" value="1" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" <?php if (isset($_POST['hide_smilies'])){?>checked="checked"<?php }?> /><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Hide_smilies']->value];?>
<br/>
    <?php }?>
    
    <?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>
    <input type="checkbox" name="merge" value="1" checked="checked" /><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Merge_posts']->value];?>
<br/>
    <?php }?>
    
    <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_subscriptions']==1){?>
    <input type="checkbox" name="subscribe" value="1" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" <?php if (isset($_POST['subscribe'])){?>checked="checked"<?php }?> /><?php echo $_smarty_tpl->tpl_vars['lang_post']->value['Subscribe'];?>
<br/>
    <?php }?>
<?php }elseif($_smarty_tpl->tpl_vars['pun_config']->value['o_smilies']==1){?>
<input type="checkbox" name="hide_smilies" value="1" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" <?php if (isset($_smarty_tpl->tpl_vars['smary']->value['post']['hide_smilies'])){?>checked="checked"<?php }?> /><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Hide_smilies']->value];?>

<?php }?>

</div>
<div class="go_to">
<input type="hidden" name="form_t" value="<?php echo $_SERVER['REQUEST_TIME'];?>
" />
<input type="submit" name="submit" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" accesskey="s" />
<input type="submit" name="preview" value="<?php echo $_smarty_tpl->tpl_vars['lang_post']->value['Preview'];?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" accesskey="p" />
</div>
</form>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php }} ?>