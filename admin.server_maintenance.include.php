<?php
/*
4/21/2011 1:04:49 PM Andy
- Change script to only can run in terminal and must stop apache first.
*/
//$maintenance->check(14);
$min_cutoff_date = date("Y-m-d", strtotime("-3 month", time()));
//$smarty->assign('min_cutoff_date', $min_cutoff_date);
?>
