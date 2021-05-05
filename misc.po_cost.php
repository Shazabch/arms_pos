<?
include("include/common.php");

if (isset($_REQUEST['a']))
{
switch($_REQUEST['a'])
{
	case 'find':
		// if this is armscode, use the linkcode as $_request[find] 
		if (preg_match("/^28\d+$/",$_REQUEST['find']))
		{
			$con->sql_query("select link_code from sku_items where sku_item_code = ".ms($_REQUEST['find']));
			$t = $con->sql_fetchrow();
			if ($t)
			{
				$_REQUEST['find'] = $t['link_code'];
			}
		}
		
		foreach (file("PO_COST.DAT") as $line)
		{
			$s = preg_split("/\s*;\s*/", $line);
			if (strpos($s[0],$_REQUEST['find'])!==false)
			{
				$con->sql_query("select sku_item_code,description from sku_items where link_code = ".ms($s[0]));
				$r = $con->sql_fetchrow();
				$s[4] = $r;
				$ss[] = $s;
			}
		}
		$smarty->assign("search", $ss);
		break;
		
 	default:
		print "Unhandled Request";
		print_r($_REQUEST);
		exit;
}
}
$smarty->assign("PAGE_TITLE", "Multics PO Cost");
$smarty->display("misc.po_cost.tpl");
?>
