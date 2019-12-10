<?php
/* Smarty version 3.1.31, created on 2019-11-08 12:44:57
  from "tpl_body:5" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc56349bb60e7_70632892',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd537bf3c237ffac6cf7b373b7dd17497ea43ae1c' => 
    array (
      0 => 'tpl_body:5',
      1 => '1573121657',
      2 => 'tpl_body',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc56349bb60e7_70632892 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_anchor')) require_once '/home/wwwroot/default/lib/plugins/function.anchor.php';
if (!is_callable('smarty_function_sitename')) require_once '/home/wwwroot/default/lib/plugins/function.sitename.php';
if (!is_callable('smarty_function_cms_selflink')) require_once '/home/wwwroot/default/lib/plugins/function.cms_selflink.php';
if (!is_callable('smarty_function_title')) require_once '/home/wwwroot/default/lib/plugins/function.title.php';
if (!is_callable('smarty_function_global_content')) require_once '/home/wwwroot/default/lib/plugins/function.global_content.php';
?>
<body>
    <div id="pagewrapper">


      <ul class="accessibility">
        <li><?php echo smarty_function_anchor(array('anchor'=>'menu_vert','title'=>'Skip to navigation','accesskey'=>'n','text'=>'Skip to navigation'),$_smarty_tpl);?>
</li>
        <li><?php echo smarty_function_anchor(array('anchor'=>'main','title'=>'Skip to content','accesskey'=>'s','text'=>'Skip to content'),$_smarty_tpl);?>
</li>
      </ul>


      <hr class="accessibility" />



      <div id="header">


        <h2 class="headright"><?php echo smarty_function_sitename(array(),$_smarty_tpl);?>
</h2>


        <h1><?php echo smarty_function_cms_selflink(array('dir'=>"start",'text'=>((string)$_smarty_tpl->tpl_vars['sitename']->value)),$_smarty_tpl);?>
</h1> 
       
        <hr class="accessibility" />
      </div>



      <div id="search">
      <?php echo Search::function_plugin(array(),$_smarty_tpl);?>

      </div>



      <div class="crbk">


        <div class="breadcrumbs">
        <?php echo Navigator::nav_breadcrumbs(array('root'=>'Home'),$_smarty_tpl);?>

          <hr class="accessibility" />
        </div>
      </div>



      <div id="content">


        <div id="sidebar">
          <div id="sidebara">


            <div id="menu_vert">
              <h2 class="accessibility">Navigation</h2>
              <?php echo Navigator::function_plugin(array('loadprops'=>0,'template'=>'Simple Navigation','collapse'=>'1'),$_smarty_tpl);?>

            </div>



            <div id="news">
              <h2>News</h2>
              <?php echo News::function_plugin(array('number'=>'3','detailpage'=>'news'),$_smarty_tpl);?>

            </div>


          </div>
        </div>




        <div class="back">        
          <div id="main">
            <h2><?php echo smarty_function_title(array(),$_smarty_tpl);?>
</h2>
            <?php CMS_Content_Block::smarty_internal_fetch_contentblock(array(),$_smarty_tpl); ?>
            <br />




            <div class="right49">
              <p><?php echo smarty_function_anchor(array('anchor'=>'main','text'=>'^ Top'),$_smarty_tpl);?>
</p>
            </div>

            <div class="left49">
              <p><?php echo smarty_function_cms_selflink(array('dir'=>"previous"),$_smarty_tpl);?>



              <br />
              <?php echo smarty_function_cms_selflink(array('dir'=>"next"),$_smarty_tpl);?>

              </p>
            </div>


            <hr class="accessibility" />
          </div>
        </div>


        <div class="clear"></div>


      </div>



      <div class="footback">
        <div id="footer">

          <div id="fooleft">
          <?php echo Navigator::function_plugin(array('loadprops'=>0),$_smarty_tpl);?>

          </div>
          <div id="footrt">
          <?php echo smarty_function_global_content(array('name'=>'footer'),$_smarty_tpl);?>

          </div>
          <div class="clear"></div>
        </div>
      </div>


    </div>

  </body>
</html><?php }
}
