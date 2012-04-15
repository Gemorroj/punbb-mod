<?php /* Smarty version Smarty-3.1.7, created on 2012-04-14 22:27:59
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\profile.avatar.tpl" */ ?>
<?php /*%%SmartyHeaderCode:166454f89c197c3b332-89094061%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4babf9e896f50250a0bcebb80e856f80a6db9145' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\profile.avatar.tpl',
      1 => 1334428078,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '166454f89c197c3b332-89094061',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f89c197d24a1',
  'variables' => 
  array (
    'Upload_avatar' => 0,
    'lang_profile' => 0,
    'id' => 0,
    'Upload_avatar_legend' => 0,
    'Avatar_desc' => 0,
    'pun_config' => 0,
    'lang_common' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f89c197d24a1')) {function content_4f89c197d24a1($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<?php $_smarty_tpl->tpl_vars['Upload_avatar'] = new Smarty_variable('Upload avatar', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Upload_avatar_legend'] = new Smarty_variable('Upload avatar legend', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Avatar_desc'] = new Smarty_variable('Avatar desc', null, 0);?>

<div class="con">
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Upload_avatar']->value];?>
</strong>
</div>
<form method="post" enctype="multipart/form-data" action="profile.php?action=upload_avatar2&amp;id=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">
<div class="input">
<strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Upload_avatar_legend']->value];?>
</strong><br/>
<input type="hidden" name="form_sent" value="1" />
<input name="req_file" type="file" size="40" /><br/>
<span class="sub"><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Avatar_desc']->value];?>
&#160;<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_avatars_width'];?>
&#160;x&#160;<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_avatars_height'];?>
&#160;<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value['pixels'];?>
&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['and'];?>
&#160;<?php echo ceil($_smarty_tpl->tpl_vars['pun_config']->value['o_avatars_size']/1024);?>
&#160;kb</span>
</div>
<div class="go_to">
<input type="submit" name="upload" value="<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value['Upload'];?>
" />
</div>
</form>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>