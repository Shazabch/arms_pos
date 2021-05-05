<?
include("include/common.php");

switch (BRANCH_CODE)
{
	case 'HQ': $bid = 1; break;
	case 'GURUN':$bid = 4; break;
	case 'BALING': $bid = 2; break;
	case 'TMERAH': $bid = 7; break;
	case 'JITRA': $bid = 6; break;
	case 'DUNGUN': $bid = 3; break;
	case 'KANGAR': $bid = 5; break;
}

$con->sql_query("alter table log add branch_id int, add index(user_id), drop primary key, add primary key (branch_id, id)",false,false);
$con->sql_query("update log set branch_id = $bid, timestamp=timestamp");
print "<li> OK";

$con->sql_query("alter table counter_inventory add branch_id int, drop primary key, add primary key (branch_id, counter_id)",false,false);
$con->sql_query("update counter_inventory set branch_id = $bid, timestamp=timestamp",false,false);
print "<li> OK";

$con->sql_query("alter table counter_settings add branch_id int, drop primary key, add primary key (branch_id, id)",false,false);
$con->sql_query("update counter_settings set branch_id = $bid",false,false);
print "<li> OK";

$con->sql_query("drop table if exists membership_inventory",false,false);
$con->sql_query("alter table membership_inventory_history add branch_id int, add id int auto_increment, add primary key (branch_id, counter_id, id)",false,false);
$con->sql_query("update membership_inventory_history set branch_id = $bid, timestamp=timestamp",false,false);
print "<li> OK";

$con->sql_query("alter table membership_receipt modify id int auto_increment, add branch_id int, drop primary key, add primary key (branch_id, counter_id, id), drop grab_GURUN, drop grab_BALING, drop grab_KANGAR, drop grab_DUNGUN, drop grab_TMERAH, drop grab_JITRA",false,false);
$con->sql_query("update membership_receipt set branch_id = $bid, timestamp=timestamp",false,false);
print "<li> OK";

$con->sql_query("alter table membership_receipt_items add branch_id int, add id int auto_increment, add primary key (branch_id, counter_id, receipt_id, id)",false,false);
$con->sql_query("update membership_receipt_items set branch_id = $bid",false,false);
print "<li> OK";

$con->sql_query("alter table membership_drawer_history add branch_id int, add id int auto_increment, add primary key (branch_id, counter_id, id), modify reason char(20)",false,false);
$con->sql_query("update membership_drawer_history set branch_id = $bid",false,false);
print "<li> OK";



?>
