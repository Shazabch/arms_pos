<?php

$con->sql_query("select id,title,rpt_group from pivot_table order by title");
$smarty->assign("pivots", $con->sql_fetchrowset());

?>
