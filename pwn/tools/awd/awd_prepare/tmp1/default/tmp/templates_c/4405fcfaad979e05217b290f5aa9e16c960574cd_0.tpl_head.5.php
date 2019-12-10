<?php
/* Smarty version 3.1.31, created on 2019-11-08 12:44:57
  from "tpl_head:5" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc56349e7ab54_86821649',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4405fcfaad979e05217b290f5aa9e16c960574cd' => 
    array (
      0 => 'tpl_head:5',
      1 => '1573121657',
      2 => 'tpl_head',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc56349e7ab54_86821649 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_sitename')) require_once '/home/wwwroot/default/lib/plugins/function.sitename.php';
if (!is_callable('smarty_function_title')) require_once '/home/wwwroot/default/lib/plugins/function.title.php';
if (!is_callable('smarty_function_metadata')) require_once '/home/wwwroot/default/lib/plugins/function.metadata.php';
if (!is_callable('smarty_cms_function_cms_stylesheet')) require_once '/home/wwwroot/default/lib/plugins/function.cms_stylesheet.php';
if (!is_callable('smarty_function_cms_selflink')) require_once '/home/wwwroot/default/lib/plugins/function.cms_selflink.php';
?>
<head>
    <title><?php echo smarty_function_sitename(array(),$_smarty_tpl);?>
 - <?php echo smarty_function_title(array(),$_smarty_tpl);?>
</title>


 <?php echo smarty_function_metadata(array(),$_smarty_tpl);?>



 <?php echo smarty_cms_function_cms_stylesheet(array(),$_smarty_tpl);?>



 <?php echo smarty_function_cms_selflink(array('dir'=>"start",'rellink'=>1),$_smarty_tpl);?>

 <?php echo smarty_function_cms_selflink(array('dir'=>"prev",'rellink'=>1),$_smarty_tpl);?>

 <?php echo smarty_function_cms_selflink(array('dir'=>"next",'rellink'=>1),$_smarty_tpl);?>




 
<?php echo '<script'; ?>
 type="text/JavaScript">
<!--
//pass min and max - measured against window width
function P7_MinMaxW(a,b){
var nw="auto",w=document.documentElement.clientWidth;
if(w>=b){nw=b+"px";}if(w<=a){nw=a+"px";}return nw;
}
//-->
<?php echo '</script'; ?>
>
    <!--[if lte IE 6]>
    <style type="text/css">
    #pagewrapper {width:expression(P7_MinMaxW(720,1200));}
    #container {height: 1%;}
    </style>
    <![endif]-->
    


  </head><?php }
}
