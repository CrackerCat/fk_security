<?php
/* Smarty version 3.1.31, created on 2019-11-08 12:44:57
  from "cms_stylesheet:Navigation Simple - Vertical" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc56349f3f9f6_84902280',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'bb20866e7b5077c3b70c0ed4b2be34621c2c49d0' => 
    array (
      0 => 'cms_stylesheet:Navigation Simple - Vertical',
      1 => '1573121657',
      2 => 'cms_stylesheet',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc56349f3f9f6_84902280 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_root_url')) require_once '/home/wwwroot/default/lib/plugins/function.root_url.php';
?>
/* cmsms stylesheet: Navigation Simple - Vertical modified: 11/07/19 10:14:17 */
/******************** MENU *********************/
#menu_vert {
	margin: 0;
	padding: 0;
}
#menu_vert ul {
/* remove any bullets */
	list-style: none;
/* margin/padding set in li */
	margin: 0px;
	padding: 0px;
}
#menu_vert ul ul {
	margin: 0;
/* padding right sets second level li in on right from first li */
	padding: 0px 5px 0px 0px;
/* replaces bottom of li.menuactive menuparent, looks like li below it, set in 5px more, is sitting on top of it */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/liup.gif) no-repeat right -4px;
}
#menu_vert li {
/* remove any bullets */
	list-style: none;
/* negative bottom margin pulls them together, images look like one border between */
	margin: 0px 0px -1px;
/* bottom padding pushes "a" up enough to show our image */
	padding: 0px 0px 4px 0px;
/* you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/liup.gif) no-repeat right bottom;
}
#menu_vert li.currentpage {
	padding: 0px 0px 3px 0px;
}
#menu_vert li.menuactive {
	margin: 0;
	padding: 0px;
/* replaced by image in ul ul */
	background: none;
}
#menu_vert li.menuactive ul {
	margin: 0;
}
#menu_vert li.activeparent {
	margin: 0;
	padding: 0px;
}
/* fix stupid IE6 bug with display:block; */
* html #menu_vert li {
	height: 1%;
}
* html #menu_vert li a {
	height: 1%;
}
* html #menu_vert li hr {
	height: 1%;
}
/** end fix **/
/* first level links */
div#menu_vert a {
/* IE6 has problems with this, fixed above */
	display: block;
/* some air for it */
	padding: 0.8em 0.3em 0.5em 1.5em;
/* this will be link color for all levels */
	color: #18507C;
/* Fixes IE7 whitespace bug */
	min-height: 1em;
/* no underline for links */
	text-decoration: none;
/* you can set your own image here this is tall enough to cover text heavy links */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/libk.gif) no-repeat right top;
}
/* next level links, more padding and smaller font */
div#menu_vert ul ul a {
	font-size: 90%;
	padding: 0.8em 0.3em 0.5em 2.8em;
}
/* third level links, more padding */
div#menu_vert ul ul ul a {
	padding: 0.5em 0.3em 0.3em 3em;
}
/* hover state for all links */
div#menu_vert a:hover {
	background-color: transparent;
	color: #595959;
	text-decoration: underline;
}
div#menu_vert a.activeparent:hover {
	color: #595959;
}
/* active parent, that is the first level parent of a child page that is the current page */
div#menu_vert li.activeparent {
/* you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/liup.gif) no-repeat right -65px;
/* white to contrast with background image */
	color: #fff;
}
div#menu_vert li.activeparent a.activeparent {
/* you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/libk.gif) no-repeat right top;
/* to contrast with background image */
	color: #000;
}
div#menu_vert li a.parent {
/* takes left padding out so span image has room on left */
	padding-left: 0em;
}
div#menu_vert ul ul li a.parent {
/* increased padding on left offsets it from one above */
	padding-left: 0.9em;
}
div#menu_vert li a.parent span {
	display: block;
	margin: 0;
/* adds left padding taken out of "a.parent" */
	padding-left: 1.5em;
/* arrow on left for pages with children, points down, you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/active.png) no-repeat 2px center;
}
div#menu_vert li a.parent:hover {
/* removes underline hover effect */
	text-decoration: none;
}
div#menu_vert li a.parent:hover span {
	display: block;
	margin: 0;
	padding-left: 1.5em;
/* arrow on left for pages with children, points right for hover, you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/parent.png) no-repeat 2px center;
}
div#menu_vert li a.menuactive.menuparent {
/* sets it in a little more than a.parent */
	padding-left: 0.35em;
}
div#menu_vert ul ul li a.menuactive.menuparent {
/* sets it in a little more on next level */
	padding-left: 0.99em;
}
div#menu_vert li a.menuactive.menuparent span {
	display: block;
	margin: 0;
/* to contrast with non active pages */
	font-weight: bold;
	padding-left: 1.5em;
/* arrow on left for active pages with children, points right, you can set your own image here */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/parent.png) no-repeat 2px center;
}
div#menu_vert li a.menuactive.menuparent:hover {
	text-decoration: none;
	color: #18507C;
}
div#menu_vert ul ul li a.activeparent {
	color: #fff;
}
/* current pages in the default Menu Manager template are unclickable. This is for current page on first level */
div#menu_vert ul h3 {
	display: block;
/* some air for it */
	padding: 0.8em 0.5em 0.5em 1.5em;
/* this will be link color for all levels */
	color: #000;
/* instead of the normal font size for <h3> */
	font-size: 1em;
/* as <h3> normally has some margin by default */
	margin: 0;
/* you can set your own image here, same as "a" */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/libk.gif) no-repeat right top;
}
/* next level current pages, more padding, smaller font and no background color or bottom border */
div#menu_vert ul ul h3 {
	font-size: 90%;
	padding: 0.8em 0.5em 0.5em 2.8em;
/* you can set your own image here, same as "a" */
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/libk.gif) no-repeat right top;
	color: #000;
}
/* current page on third level, more padding */
div#menu_vert ul ul ul h3 {
	padding: 0.6em 0.5em 0.2em 3em;
}
/* BIG NOTE: I didn''t do anything to these, never tested */
/* section header */
div#menu_vert li.sectionheader {
	border-right: none;
	padding: 0.8em 0.5em 0.5em 1.5em;
	background: transparent url(<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
/uploads/ngrey/libk.gif) no-repeat right top;
	line-height: 1em;
	margin: 0;
        color: #18507C;
        cursor:text;
}
/* separator */
div#menu_vert .separator {
	height: 1px !important;
	margin-top: -1px;
	margin-bottom: 0;
	-padding: 2px 0 2px 0;
	background-color: #000;
	overflow: hidden !important;
	line-height: 1px !important;
	font-size: 1px;
/* for ie */
}
div#menu_vert li.separator hr {
	display: none;
/* this is for accessibility */
}
<?php }
}
