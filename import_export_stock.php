<?php
/*
5/19/2011 4:47:45 PM Andy
- Fix different total amount when export by SKU or Branch. Due to rounding problem.

5/19/2011 5:54:18 PM Andy
- Fix wrong description when export by SKU.
*/
include("include/common.php");
$maintenance->check(1);

ini_set('memory_limit', '512M');
set_time_limit(0);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class IMPORT_EXPORT_STOCK extends Module{
    function _default(){
        global $con, $smarty;
        $con->sql_query("select count(*) from import_export_stock");
        $data_count = $con->sql_fetchfield(0);
        $smarty->assign('data_count', $data_count);
		$this->display();
	}
	
	function import_stock(){
		global $con, $smarty;
		
		if (!preg_match('/\.csv$/i',$_FILES['stocks']['name']))	$err[] = "Please upload a CSV file. {$_FILES['csv']['type']}";
		if(!$err){
            $fp = fopen($_FILES['stocks']['tmp_name'], "r");
            if(!$fp)    $err[] = "Cannot read the file";
		}	
		
		if($err){
            $smarty->assign('err',$err);
			$this->_default();
			exit;
		}

		$con->sql_query("truncate import_export_stock");
        while (($r = fgetcsv($fp)) !== FALSE) {
            $upd = array();
            if(!$r[0])  continue;
            $upd['branch_code'] = $r[0];
            $upd['sku_item_code'] = $r[1];
            $upd['cost'] = str_replace(",","",$r[2]);
            $upd['qty'] = str_replace(",","",$r[3]);
            $con->sql_query("insert into import_export_stock ".mysql_insert_by_field($upd));
        }
        fclose($fp);
        header("Location: $_SERVER[PHP_SELF]");
        exit;
	}
	
	function export_excel(){
        global $con, $smarty;
        
        $export_type = $_REQUEST['export_type'];
        $sort_by = $_REQUEST['sort_by'] ? $_REQUEST['sort_by'] : 'ies.sku_item_code';
        
        /*if($export_type=='sku'){
			$sql = "select ies.sku_item_code,si.artno,si.description,avg(ies.cost) as cost,sum(ies.qty) as qty,sum(ies.cost*ies.qty) as bal
from import_export_stock ies
left join sku_items si on si.sku_item_code=ies.sku_item_code
group by ies.sku_item_code order by $sort_by";
		}else{
			$sql = "select ies.branch_code,branch.description,sum(ies.qty) as qty,sum(ies.cost*ies.qty) as bal
from import_export_stock ies
left join branch on branch.code=ies.branch_code
group by ies.branch_code
order by ies.branch_code";
		}
		$con->sql_query($sql);
		$smarty->assign('data', $con->sql_fetchrowset());*/
		
		if($export_type!='sku'){
			$sort_by = "ies.branch_code";
			$extra_col = ",ies.branch_code,branch.description";
		}
		
		$sql = "select ies.sku_item_code,si.artno,si.description,ies.cost,ies.qty $extra_col
from import_export_stock ies
left join sku_items si on si.sku_item_code=ies.sku_item_code
left join branch on branch.code=ies.branch_code
order by $sort_by";
		$con->sql_query($sql);
		while($r = $con->sql_fetchrow()){
			$total_cost = round($r['qty']*$r['cost'], 2);
			
			if($export_type=='sku'){
				$data[$r['sku_item_code']]['sku_item_code'] = $r['sku_item_code'];
			    $data[$r['sku_item_code']]['artno'] = $r['artno'];
			    $data[$r['sku_item_code']]['description'] = $r['description'];
				$data[$r['sku_item_code']]['cost'] = $r['cost'];
				$data[$r['sku_item_code']]['qty'] += $r['qty'];
				$data[$r['sku_item_code']]['bal'] += $total_cost;
			}else{
				$data[$r['branch_code']]['branch_code'] = $r['branch_code'];
			    $data[$r['branch_code']]['description'] = $r['description'];
				$data[$r['branch_code']]['qty'] += $r['qty'];
				$data[$r['branch_code']]['bal'] += $total_cost;
			}
		}
		
		$smarty->assign('data', $data);
		include("include/excelwriter.php");
	    $smarty->assign('no_header_footer', true);

		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=stock_by_'.$export_type.'.xls');

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}
}

$IMPORT_EXPORT_STOCK = new IMPORT_EXPORT_STOCK('Import & Export Stock');
?>
