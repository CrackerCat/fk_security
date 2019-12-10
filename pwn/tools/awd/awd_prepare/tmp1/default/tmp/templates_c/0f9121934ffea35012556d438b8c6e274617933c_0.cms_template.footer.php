<?php
/* Smarty version 3.1.31, created on 2019-11-08 12:44:57
  from "cms_template:footer" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc56349e6d6a0_11306712',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0f9121934ffea35012556d438b8c6e274617933c' => 
    array (
      0 => 'cms_template:footer',
      1 => '1573121657',
      2 => 'cms_template',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc56349e6d6a0_11306712 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_cms_version')) require_once '/home/wwwroot/default/lib/plugins/function.cms_version.php';
?>
<p>&copy; Copyright <?php echo cms_user_tag_custom_copyright(array(),$_smarty_tpl);?>
 - CMS Made Simple<br />
This site is powered by <a class="external" href="http://www.cmsmadesimple.org">CMS Made Simple</a> version <?php echo smarty_function_cms_version(array(),$_smarty_tpl);?>
</p><?php }
}
