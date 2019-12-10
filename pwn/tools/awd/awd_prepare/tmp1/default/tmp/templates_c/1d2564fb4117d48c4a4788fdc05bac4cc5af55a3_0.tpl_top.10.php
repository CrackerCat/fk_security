<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "tpl_top:10" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee800393d4_05160804',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1d2564fb4117d48c4a4788fdc05bac4cc5af55a3' => 
    array (
      0 => 'tpl_top:10',
      1 => '1573121657',
      2 => 'tpl_top',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee800393d4_05160804 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_cms_lang_info')) require_once '/home/wwwroot/default/lib/plugins/function.cms_lang_info.php';
if (!is_callable('smarty_function_uploads_url')) require_once '/home/wwwroot/default/lib/plugins/function.uploads_url.php';
if (!is_callable('smarty_function_title')) require_once '/home/wwwroot/default/lib/plugins/function.title.php';
if (!is_callable('smarty_function_cms_selflink')) require_once '/home/wwwroot/default/lib/plugins/function.cms_selflink.php';
if (!is_callable('smarty_cms_function_share_data')) require_once '/home/wwwroot/default/lib/plugins/function.share_data.php';
echo CMS_Content_Block::smarty_fetch_pagedata(array(),$_smarty_tpl);
echo smarty_function_cms_lang_info(array('assign'=>'nls'),$_smarty_tpl);
ob_start();
echo smarty_function_uploads_url(array(),$_smarty_tpl);
$_prefixVariable1=ob_get_clean();
$_smarty_tpl->_assignInScope('theme_path', $_prefixVariable1."/simplex");
echo smarty_function_title(array('assign'=>'main_title'),$_smarty_tpl);
CMS_Content_Block::smarty_internal_fetch_contentblock(array('assign'=>'main_content'),$_smarty_tpl);
echo smarty_function_cms_selflink(array('dir'=>'previous','assign'=>'prev_page'),$_smarty_tpl);
echo smarty_function_cms_selflink(array('dir'=>'next','assign'=>'next_page'),$_smarty_tpl);
echo smarty_cms_function_share_data(array('scope'=>'global','vars'=>'nls,theme_path,main_title,main_content,prev_page,next_page'),$_smarty_tpl);?>
<!doctype html>
<!--[if IE 8]>         <html lang='<?php echo $_smarty_tpl->tpl_vars['nls']->value->htmlarea();?>
' dir='<?php echo $_smarty_tpl->tpl_vars['nls']->value->direction();?>
' class='lt-ie9'> <![endif]-->
<!--[if gt IE 8]><!--> <html lang='<?php echo $_smarty_tpl->tpl_vars['nls']->value->htmlarea();?>
' dir='<?php echo $_smarty_tpl->tpl_vars['nls']->value->direction();?>
'> <!--<![endif]--><?php }
}
