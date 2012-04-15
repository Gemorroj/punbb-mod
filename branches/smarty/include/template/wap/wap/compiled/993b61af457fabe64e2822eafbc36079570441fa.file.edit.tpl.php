<?php /* Smarty version Smarty-3.1.7, created on 2012-03-23 23:07:04
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:195974f6cc9062ceb17-70109752%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '993b61af457fabe64e2822eafbc36079570441fa' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\edit.tpl',
      1 => 1332529623,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '195974f6cc9062ceb17-70109752',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f6cc90636413',
  'variables' => 
  array (
    'lang_common' => 0,
    'cur_post' => 0,
    'errors' => 0,
    'Post_errors' => 0,
    'lang_post' => 0,
    'Post_errors_info' => 0,
    'cur_error' => 0,
    'Post_preview' => 0,
    'Edit_post' => 0,
    'id' => 0,
    'can_edit_subject' => 0,
    'cur_index' => 0,
    'message' => 0,
    'pun_config' => 0,
    'img_tag' => 0,
    'Hide_smilies' => 0,
    'is_admmod' => 0,
    'Silent_edit' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f6cc90636413')) {function content_4f6cc90636413($_smarty_tpl) {?><div class="inbox">
<a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a> &#187; <a href="viewforum.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_post']->value['fid'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</a> &#187; <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_post']->value['subject'], ENT_QUOTES, 'UTF-8', true);?>
</div>

<?php if ($_smarty_tpl->tpl_vars['errors']->value){?>
<div class="red">
<?php $_smarty_tpl->tpl_vars['Post_errors'] = new Smarty_variable('Post errors', null, 0);?>
<?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_errors']->value];?>

</div>
<?php $_smarty_tpl->tpl_vars['Post_errors_info'] = new Smarty_variable('Post errors info', null, 0);?>
<div class="msg"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_errors_info']->value];?>

<?php  $_smarty_tpl->tpl_vars['cur_error'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_error']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['errors']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_error']->key => $_smarty_tpl->tpl_vars['cur_error']->value){
$_smarty_tpl->tpl_vars['cur_error']->_loop = true;
?>
&#187; <?php echo $_smarty_tpl->tpl_vars['cur_error']->value;?>
<br/>
<?php } ?>
</div>
<?php }elseif($_POST['preview']){?>
<?php echo $_smarty_tpl->getSubTemplate ('{$smarty.const.PUN_ROOT}include/parser.php', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php $_smarty_tpl->tpl_vars['Post_preview'] = new Smarty_variable('Post preview', null, 0);?>
<div class="info"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Post_preview']->value];?>
</div>
<div class="msg"></div>
<?php }?>

<?php $_smarty_tpl->tpl_vars['Edit_post'] = new Smarty_variable('Edit post', null, 0);?>
<div class="con"><?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Edit_post']->value];?>
</div>

<form method="post" action="edit.php?id=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;action=edit" enctype="multipart/form-data">
<div class="input">
<input type="hidden" name="form_sent" value="1" />
<?php if ($_smarty_tpl->tpl_vars['can_edit_subject']->value){?>
<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Subject'];?>
<br/>
<input type="text" name="req_subject" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" value="<?php ob_start();?><?php echo define($_POST['req_subject'],$_smarty_tpl->tpl_vars['cur_post']->value['subject']);?>
<?php $_tmp1=ob_get_clean();?><?php echo htmlspecialchars($_tmp1, ENT_QUOTES, 'UTF-8', true);?>
" /><br/>
<!-- /label -->
<?php }?>



<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Message'];?>
:<br/>
<textarea name="req_message" rows="4" cols="24" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>"><?php ob_start();?><?php echo define($_smarty_tpl->tpl_vars['message']->value,$_smarty_tpl->tpl_vars['cur_post']->value['message']);?>
<?php $_tmp2=ob_get_clean();?><?php echo htmlspecialchars($_tmp2, ENT_QUOTES, 'UTF-8', true);?>
</textarea><br/>
<a href="help.php?id=3"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Smilies'];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_smilies']==1){?>
<span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span>;
<?php }else{ ?>
<span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span>;
<?php }?>
<a href="help.php?id=1"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['BBCode'];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_message_bbcode']==1){?>
<span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span>;
<?php }else{ ?>
<span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span>;
<?php }?>
<?php $_smarty_tpl->tpl_vars['img_tag'] = new Smarty_variable('img tag', null, 0);?>
<a href="help.php?id=4"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['img_tag']->value];?>
</a>
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['p_message_img_tag']==1){?>
<span class="green"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['on_m'];?>
</span>
<?php }else{ ?>
<span class="grey"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['off_m'];?>
</span>;
<?php }?>
<br/>



<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_smilies']==1){?>
<?php $_smarty_tpl->tpl_vars['Hide_smilies'] = new Smarty_variable('Hide smilies', null, 0);?>
<input type="checkbox" name="hide_smilies" value="1" <?php if (isset($_POST['hide_smilies'])||$_smarty_tpl->tpl_vars['cur_post']->value['hide_smilies']==1){?>checked="checked"<?php }?> tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" /> <?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Hide_smilies']->value];?>
<br/>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>
<input type="checkbox" name="silent" value="1" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" <?php if ((isset($_POST['form_sent'])&&isset($_POST['silent']))||!isset($_POST['form_sent'])){?>checked="checked"<?php }?> /> <?php echo $_smarty_tpl->tpl_vars['lang_post']->value[$_smarty_tpl->tpl_vars['Silent_edit']->value];?>
<br/>
<?php }?>


</div>
<div class="go_to">
<input type="submit" name="submit" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" accesskey="s" />
<input type="submit" name="preview" value="<?php echo $_smarty_tpl->tpl_vars['lang_post']->value['Preview'];?>
" tabindex="<?php $_smarty_tpl->tpl_vars['cur_index'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_index']->value+1, null, 0);?>" accesskey="p" />
</div>
</form><?php }} ?>