<?php

ini_set("include_path",
	ini_get("include_path") . ":/home/ssb/src/Smarty-1.4.3");
include_once "Smarty.class.php";
$s = new Smarty;
$s->compile_check = true;
$s->debugging     = false;
$s->template_dir  = "../templates";
$s->compile_dir   = "../templates_c";
$s->cache_dir     = "../cache";
$s->config_dir    = "../configs";
$s->register_function("page", "smarty_page_start");
$s->register_function("endpage", "smarty_page_end");
$s->register_function("box", "smarty_border_box_start");
$s->register_function("endbox", "smarty_border_box_end");
$s->display('test.tpl');

?>
