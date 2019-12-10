<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "tpl_head:10" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee802c2720_39216834',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3816413b4868e71aadce5ded6a8df7aecdce56d5' => 
    array (
      0 => 'tpl_head:10',
      1 => '1573121657',
      2 => 'tpl_head',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee802c2720_39216834 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_metadata')) require_once '/home/wwwroot/default/lib/plugins/function.metadata.php';
if (!is_callable('smarty_function_sitename')) require_once '/home/wwwroot/default/lib/plugins/function.sitename.php';
if (!is_callable('smarty_cms_function_cms_stylesheet')) require_once '/home/wwwroot/default/lib/plugins/function.cms_stylesheet.php';
if (!is_callable('smarty_function_cms_selflink')) require_once '/home/wwwroot/default/lib/plugins/function.cms_selflink.php';
?>
<head>
        <meta charset='<?php echo $_smarty_tpl->tpl_vars['nls']->value->encoding();?>
' />
        <?php echo smarty_function_metadata(array(),$_smarty_tpl);?>
 
        <title><?php echo $_smarty_tpl->tpl_vars['main_title']->value;?>
 - <?php echo smarty_function_sitename(array(),$_smarty_tpl);?>
</title>
        <meta name='HandheldFriendly' content='True' />
        <meta name='MobileOptimized' content='320' />
        <meta name='viewport' content='width=device-width, initial-scale=1' />
        <meta http-equiv='cleartype' content='on' />
        <meta name='msapplication-TileImage' content='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-152x152.png' />
        <meta name='msapplication-TileColor' content='#5C5A59' />
        <?php if (isset($_smarty_tpl->tpl_vars['canonical']->value)) {?><link rel='canonical' href='<?php echo $_smarty_tpl->tpl_vars['canonical']->value;?>
' /><?php } elseif (isset($_smarty_tpl->tpl_vars['content_obj']->value)) {?><link rel='canonical' href='<?php echo $_smarty_tpl->tpl_vars['content_obj']->value->GetURL();?>
' /><?php }?> 
        <?php echo smarty_cms_function_cms_stylesheet(array(),$_smarty_tpl);?>
 
        <link href='//fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic|Oswald:700' rel='stylesheet' type='text/css' />
        <link rel='apple-touch-icon-precomposed' sizes='152x152' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-152x152.png' />
        <link rel='apple-touch-icon-precomposed' sizes='120x120' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-120x120.png' />
        <link rel='apple-touch-icon-precomposed' sizes='72x72' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-76x76.png' />
        <link rel='apple-touch-icon-precomposed' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-60x60.png' />
        <link rel='shortcut icon' sizes='196x196' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-196x196.png' />
        <link rel='shortcut icon' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/cmsms-60x60.png' />
        <link rel='icon' href='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/icons/favicon_cms.ico' type='image/x-icon' />
        <?php echo smarty_function_cms_selflink(array('dir'=>'start','rellink'=>'1'),$_smarty_tpl);?>
 
        <?php echo smarty_function_cms_selflink(array('dir'=>'prev','rellink'=>'1'),$_smarty_tpl);?>

        <?php echo smarty_function_cms_selflink(array('dir'=>'next','rellink'=>'1'),$_smarty_tpl);?>

        <!--[if lt IE 9]>
            <?php echo '<script'; ?>
 src="//html5shiv.googlecode.com/svn/trunk/html5.js"><?php echo '</script'; ?>
>
            <?php echo '<script'; ?>
 src="//css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"><?php echo '</script'; ?>
>
        <![endif]-->
    </head><?php }
}
