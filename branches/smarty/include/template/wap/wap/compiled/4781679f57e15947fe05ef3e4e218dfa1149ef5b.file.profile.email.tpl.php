<?php /* Smarty version Smarty-3.1.7, created on 2012-04-14 22:25:59
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\profile.email.tpl" */ ?>
<?php /*%%SmartyHeaderCode:275604f89c0b9563041-25060041%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4781679f57e15947fe05ef3e4e218dfa1149ef5b' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\profile.email.tpl',
      1 => 1334427957,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '275604f89c0b9563041-25060041',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f89c0b965385',
  'variables' => 
  array (
    'Change_email' => 0,
    'lang_profile' => 0,
    'id' => 0,
    'Email_legend' => 0,
    'New_email' => 0,
    'lang_common' => 0,
    'Email_instructions' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f89c0b965385')) {function content_4f89c0b965385($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<?php $_smarty_tpl->tpl_vars['Change_email'] = new Smarty_variable('Change e-mail', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Email_legend'] = new Smarty_variable('E-mail legend', null, 0);?>
<?php $_smarty_tpl->tpl_vars['New_email'] = new Smarty_variable('New e-mail', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Email_instructions'] = new Smarty_variable('E-mail instructions', null, 0);?>

<div class="con">
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Change_email']->value];?>
</strong>
</div>
<form method="post" action="profile.php?action=change_email&amp;id=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">
<div class="input">
<strong><?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Email_legend']->value];?>
</strong><br/>
<input type="hidden" name="form_sent" value="1" />
<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['New_email']->value];?>
<br/>
<input type="text" name="req_new_email" maxlength="50" /><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Password'];?>
<br/>
<input type="password" name="req_password" maxlength="16" /><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_profile']->value[$_smarty_tpl->tpl_vars['Email_instructions']->value];?>

</div>
<div class="go_to">
<input type="submit" name="new_email" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" />
</div>
</form>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>