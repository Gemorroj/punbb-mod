<?php /* Smarty version Smarty-3.1.7, created on 2012-04-14 22:20:29
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\profile.password.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8004f89be6f1cec20-09520856%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6721ccb3027c332d36d738b875b475e05b9704f0' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\profile.password.tpl',
      1 => 1334427628,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8004f89be6f1cec20-09520856',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f89be6f2b9b3',
  'variables' => 
  array (
    'Change_pass' => 0,
    'lang_profile' => 0,
    'id' => 0,
    'Change_pass_legend' => 0,
    'pun_user' => 0,
    'Old_pass' => 0,
    'New_pass' => 0,
    'Confirm_new_pass' => 0,
    'lang_common' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f89be6f2b9b3')) {function content_4f89be6f2b9b3($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>



<?php $_smarty_tpl->tpl_vars['Change_pass'] = new Smarty_variable('Change pass', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Change_pass_legend'] = new Smarty_variable('Change pass legend', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Old_pass'] = new Smarty_variable('Old pass', null, 0);?>
<?php $_smarty_tpl->tpl_vars['New_pass'] = new Smarty_variable('New pass', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Confirm_new_pass'] = new Smarty_variable('Confirm new pass', null, 0);?>
<div class="con">
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Change_pass']->value];?>
</strong>
</div>
<form method="post" action="profile.php?action=change_pass&amp;id=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">
<div class="input">
<strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Change_pass_legend']->value];?>
</strong><br/>
<input type="hidden" name="form_sent" value="1" />
<?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_id']>@PUN_MOD){?>
<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Old_pass']->value];?>
<br/>
<input type="password" name="req_old_password" maxlength="16" /><br/>
<?php }?>
<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['New_pass']->value];?>
<br/>
<input type="password" name="req_new_password1" maxlength="16" /><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Confirm_new_pass']->value];?>
<br/>
<input type="password" name="req_new_password2" maxlength="16" />
</div>
<div class="go_to">
<input type="submit" name="update" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" />
</div>
</form>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>