<?php
/*
1/24/2011 10:13:09 AM Alex
- change use report_server

5/25/2011 9:59:03 AM Alex
- change title of report

6/24/2011 6:30:35 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:39:27 PM Andy
- Change split() to use explode()

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

12/2/2014 10:47 AM Andy
- Change the selling price & GP calculation to check inclusive tax and output tax rate.
- Enhance to get gst and selling price after gst.

3/12/2015 2:04 PM Andy
- Fix report filter inactive error.

2/9/2017 10:43 AM Andy
- Enhanced to show MCode, Art No and Old Code.

4/7/2017 9:06 AM Justin
- Enhanced to change the GP% to use range filter instead of using one filter only.-
- Enhanced to change the GP% filter able to select "Below" and "Between" selections.

2/21/2020 4:37 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

set_time_limit(0);
class BelowCost extends Report
{
	function run($bid)
	{
		global $con_multi, $config;

		/*
		select ifnull(sip.price,si.selling_price) as selling_price, 
		if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax)) as input_tax_id, 
		input_gst.rate as input_gst_rate,
		if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) as output_tax_id,
		output_gst.rate as output_gst_rate,
		if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)) as inclusive_tax,
		round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',si.selling_price/(100+output_gst.rate)*100,si.selling_price),2) as selling_price_before_tax,
		round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(sip.price,si.selling_price),ifnull(sip.price,si.selling_price)*(100+output_gst.rate)/100),2) as price_after_gst
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
		left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
		left join sku_items_price sip on sip.branch_id=1 and sip.sku_item_id=si.id
		where si.sku_item_code="284024070000"
		*/
		$q2 = $con_multi->sql_query($sql="select si.id, $bid as bid, si.sku_item_code,si.mcode,si.artno,si.link_code, si.description, if (c.grn_cost is null,si.cost_price,c.grn_cost) as cost, 
			c.qty,c.changed,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),2)
			as price,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2)
			as price_after_gst
			from sku_items si
			left join sku_items_cost c on c.sku_item_id = si.id and c.branch_id = $bid 
			left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = $bid
			left join sku on si.sku_id = sku.id
			left join category_cache cc on cc.category_id=sku.category_id
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			".$this->filter." ".$this->having." order by sku_item_code
		") or die(mysql_error());//print "$sql<br /><br />";//xx
		
		while($r1 = $con_multi->sql_fetchassoc($q2)){
			if($config['enable_gst']){
				$r1['gst_amt'] = round($r1['price_after_gst'] - $r1['price'], 2);
			}
			$this->table[$bid][] = $r1;
		}
		$con_multi->sql_freeresult($q2);
		//return $con_multi->sql_fetchrowset();
	}
		
	function generate_report()
	{
		global $con, $smarty, $con_multi;

		$branch_group = $this->branch_group;
		$this->table = array();
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);  // selected single branch group
			foreach($branch_group['items'][$bg_id] as $bid=>$b){
                $this->run($bid);
			}
			$q1 = $con_multi->sql_query("select code from branch_group where id=".$bg_id);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$branch_group_code = $r['code'];
			}
			$con_multi->sql_freeresult($q1);
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $this->run($bid);    // only can retrieve own branch
			}else{
				if($bid==0){
                 	$q1 = $con_multi->sql_query("select id,code from branch where active=1 order by sequence,code");
					while($r = $con_multi->sql_fetchassoc($q1)){
						$this->run($r['id']);
					}
					$con_multi->sql_freeresult($q1);
				}else{
	                $this->run($bid);    // selected single branch
				}
			}
		}
		
		$q1 = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);

		//get title
		if ($_REQUEST['all_category']){
			$cat_title="All";
		}else{
			$cat_rid=$con_multi->sql_query("select description from category where id=$_REQUEST[category_id]");
			$cat=$con_multi->sql_fetchassoc($cat_rid);
			$con_multi->sql_freeresult($cat_rid);
			$cat_title=$cat['description'];
		}

		$report_title[]="Category: ".$cat_title;

		$skutype=$_REQUEST['sku_type'] ? $_REQUEST['sku_type']:"All";
		
		$report_title[]="Sku Type: ".$skutype;
		
		if($_REQUEST['gp_type'] == 1) $report_title[]="GP Below: ".$_REQUEST['percentage']." %";
		else $report_title[]="GP: From ".intval($_REQUEST['percentage_from'])."% to ".intval($_REQUEST['percentage_to'])."%";
		
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_title));
		
		//clear empty data
		/*foreach ($this->table as $b => $data){
			if (!$data)	unset($this->table[$b]);
		}*/
	
		$smarty->assign('table',$this->table);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process

		// call parent
		parent::process_form();
		
		$this->gp_type = $_REQUEST['gp_type'];
		$this->percent = intval($_REQUEST['percentage'])/100;
		$this->percent_from = intval($_REQUEST['percentage_from'])/100;
		$this->percent_to = intval($_REQUEST['percentage_to'])/100;
		$this->hidezero = isset($_REQUEST['hidezero']);
		$this->category_id = intval($_REQUEST['category_id']);
		$this->sku_type = $_REQUEST['sku_type'];
		
		if ($this->hidezero) $zeroqty = " and qty <> 0";
		if ($this->sku_type != '') $where[] = 'sku.sku_type = '.ms($this->sku_type);
		if ($this->category_id) {
			$con_multi->sql_query("select level from category where id = $this->category_id");
			$lvl = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			$where[] = "cc.p$lvl = $this->category_id";
		}
		
		$where[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		if (isset($where))
		{
			$where = join(" and ", $where);
			if ($where) $where = "where $where";
		}
		
		if($this->gp_type == 1){ // filter with below gp
			$gp_having = "having (price-cost)/price <= ".$this->percent;
		}else{ // filter with gp in between
			$gp_having = "having (price-cost)/price between ".$this->percent_from." and ".$this->percent_to;
		}
		
		$this->having = "$gp_having $zeroqty";
		$this->filter = $where;
		
	}
}
//$con_multi = new mysql_multi();
$report = new BelowCost('SKU Items Gross Profit Report');
//$con_multi->close_connection();
?>
