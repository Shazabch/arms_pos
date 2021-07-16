<?php
/*
3/2/2011 2:48:27 PM Andy
- Add auto create cache folder if cannot find the folder

3/18/2015 4:33 PM Justin
- Enhanced to have directy name onto file include.

11/18/2020 4:34 PM Andy
- Enhanced to check config.enable_pda for pda modules.
*/
include(dirname(__file__)."/../include/common.php");
include(dirname(__file__)."/../languages/en.php");

// create cache folder is not found
if(!is_dir(dirname(__file__).'/templates_c'))   mkdir(dirname(__file__).'/templates_c', 0777);

$smarty->template_dir = dirname(__file__).'/templates';
$smarty->compile_dir = dirname(__file__).'/templates_c';
//$smarty->config_dir = 'pda/templates';

if(!is_dir($smarty->compile_dir)){
	mkdir($smarty->compile_dir, 0777);
}

// Not allow to use PDA if no turn on config
if(!$config['enable_pda']){
	$smarty->display("pda_not_allow.tpl");
	exit;
}
?>
