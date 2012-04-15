<?php /* Smarty version Smarty-3.1.7, created on 2012-04-14 22:13:53
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\header.tpl" */ ?>
<?php /*%%SmartyHeaderCode:69964f587b12c96085-15584879%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3c8e2734eb372d7d059c822557464f56b5e239a7' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\header.tpl',
      1 => 1334426681,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '69964f587b12c96085-15584879',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f587b12ee851',
  'variables' => 
  array (
    'lang_common' => 0,
    'page_title' => 0,
    'punDesignDir' => 0,
    'pun_config' => 0,
    'basename' => 0,
    'pun_user' => 0,
    'Not_logged_in' => 0,
    'result_header' => 0,
    'New_reports' => 0,
    'lang_admin' => 0,
    'conditions' => 0,
    'New_messages' => 0,
    'lang_pms' => 0,
    'Full_inbox' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f587b12ee851')) {function content_4f587b12ee851($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\modifier.date_format.php';
?><?php $_smarty_tpl->tpl_vars['Not_logged_in'] = new Smarty_variable('Not logged in', null, 0);?>
<?php $_smarty_tpl->tpl_vars['New_reports'] = new Smarty_variable('New reports', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Full_inbox'] = new Smarty_variable('Full inbox', null, 0);?>
<?php $_smarty_tpl->tpl_vars['New_messages'] = new Smarty_variable('New messages', null, 0);?>

<?php echo '<?xml';?> version="1.0" encoding="UTF-8"<?php echo '?>';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
    
    <head>
        <meta http-equiv="Expires" content="Thu, 21 Jul 1977 07:30:00 GMT" />
        <meta http-equiv="Last-Modified" content="<?php echo smarty_modifier_date_format(time(),'r');?>
 GMT" />
        <meta http-equiv="Cache-Control" content="post-check=0, pre-check=0" />
        <meta http-equiv="Pragma" content="no-cache" />
                                                                    
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['lang_encoding'];?>
" />
        
        <title><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['page_title']->value, ENT_QUOTES, 'UTF-8', true);?>
</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->tpl_vars['punDesignDir']->value;?>
style.css" />
                                                               
        <link rel="alternate" type="application/rss+xml" title="<?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_board_title'];?>
" href="<?php echo @PUN_ROOT;?>
rss.xml" />
    </head>
    
    <body>
        
        <?php if ($_smarty_tpl->tpl_vars['basename']->value=='index.php'){?>
            
            <div class="hd">
                <img src="<?php echo $_smarty_tpl->tpl_vars['punDesignDir']->value;?>
imgs/logo.gif" title="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Forum'];?>
 <?php echo $_SERVER['HTTP_HOST'];?>
" alt="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Forum'];?>
 <?php echo $_SERVER['HTTP_HOST'];?>
" />
            </div>
            
            
            <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_board_desc']){?>
            
            <div class="hd_bott">
                <?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_board_desc'];?>

            </div>
            <?php }?>
            
            
            <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
                <div class="con">
                    <?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Not_logged_in']->value];?>

                </div>
            <?php }?>
            
            <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_id']<@PUN_GUEST){?>
                
                <?php if ($_smarty_tpl->tpl_vars['result_header']->value){?> 
                    <div class="con">
                        <a href="<?php echo @PUN_ROOT;?>
admin_reports.php"><?php echo $_smarty_tpl->tpl_vars['lang_admin']->value[$_smarty_tpl->tpl_vars['New_reports']->value];?>
</a>
                    </div>
                <?php }?>
                
                <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_maintenance']==1){?>
                    <div class="con">
                        <a href="<?php echo @PUN_ROOT;?>
admin_options.php#maintenance"><?php echo $_smarty_tpl->tpl_vars['lang_admin']->value['maintenance'];?>
</a>
                    </div>
                <?php }?>
            <?php }?>
            
            
            <?php if ($_smarty_tpl->tpl_vars['conditions']->value['count_new_msgs']){?>
                <div class="info">
                    <a href="message_list.php"> <?php echo $_smarty_tpl->tpl_vars['lang_pms']->value[$_smarty_tpl->tpl_vars['New_messages']->value];?>
 (<?php echo $_smarty_tpl->tpl_vars['conditions']->value['count_new_msgs'];?>
) </a>
                </div>
            <?php }?>
            
            <?php if ($_smarty_tpl->tpl_vars['conditions']->value['full_inbox']){?>
                <div class="red">
                    <a href="message_list.php"><?php echo $_smarty_tpl->tpl_vars['lang_pms']->value[$_smarty_tpl->tpl_vars['Full_inbox']->value];?>
</a>
                </div>
            <?php }?>
            
        
            <div class="in">
                <div>
                    <a href="<?php echo @PUN_ROOT;?>
rss.xml">RSS</a>
                </div>
            </div>
        
            <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_announcement']==1){?>
                
                <div class="incqbox">
                    <?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Announcement'];?>

                </div>
                <div class="msg">
                    <?php echo $_smarty_tpl->tpl_vars['pun_config']->value['o_announcement_message'];?>

                </div>
            <?php }?>
        <?php }?>
            
        <?php if ($_smarty_tpl->tpl_vars['basename']->value=='profile.php'||$_smarty_tpl->tpl_vars['basename']->value=='search.php'||$_smarty_tpl->tpl_vars['basename']->value=='userlist.php'||$_smarty_tpl->tpl_vars['basename']->value=='message_list.php'||$_smarty_tpl->tpl_vars['basename']->value=='message_send.php'||$_smarty_tpl->tpl_vars['basename']->value=='message_delete.php'||$_smarty_tpl->tpl_vars['basename']->value=='misc.php'||$_smarty_tpl->tpl_vars['basename']->value=='filemap.php'||$_smarty_tpl->tpl_vars['basename']->value=='karma.php'){?>
            
            <div class="inbox">
                <a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a>
            </div>
        <?php }?><?php }} ?>