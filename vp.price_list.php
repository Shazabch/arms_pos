<?php
/*
7/20/2012 2:15 PM Andy
- Fix get wrong branch selling price.

7/24/2012 1:49 PM Andy
- Add to show Old Code.

7/31/2012 10:38 AM Andy
- Add sorting/print/export feature for vendor price list.

12/17/2012 5:14 PM Justin
- Enhanced to filter off those expired items.

1:03 PM 3/28/2015 Andy
- Enhanced to show GST information.
*/
include('include/common.php');
/* $maintenance->check(137); */

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class PRICE_LIST extends Module{
	var $bid = 0;
	var $sort_list = array(
		'desc' => array(
			'label' => 'Description',
			'col' => 'si.description'
		),
		'mcode' => array(
			'label' => 'Mcode',
			'col' => 'si.mcode'
		),
		'link_code' => array(
			'label' => "",
			'col' => 'si.link_code'
		),
		'sku_item_code' => array(
			'label' => 'ARMS Code',
			'col' => 'si.sku_item_code'
		),
		'sp' => array(
			'label' => 'Selling Price',
			'col' => 'price'
		)
	);
	
	function __construct($title){
		global $vp_session, $config, $smarty;
		
		$this->sort_list['link_code']['label'] = $config['link_code_name'];	// fix cant put config name when define
		
		$this->bid = $vp_session['branch_id'];
		
		$smarty->assign('sort_list', $this->sort_list);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		if($_REQUEST['submit_type']=='excel'){	// export excel
			include_once("include/excelwriter.php");
			log_vp($vp_session['id'], "MASTERFILE_SKU", 0, "Export Price List");

			Header('Content-Type: application/msexcel');
			Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
			print ExcelWriter::GetHeader();
			$smarty->assign('no_header_footer', 1);
		}
		$this->load_report();
		$this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $vp_session, $config, $con_multi;
		
		$bid = $this->bid;
				
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		
		//$y = date("Y", strtotime($date));
		//if(!$err && $y<2000)	$err[] = "Invalid Year.";

		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
				
		if($err){
			$smarty->assign('err', $err);
			return;
		}

/*

			select pi.branch_id,pi.date,pi.counter_id,pi.pos_id,pi.sku_item_id,pi.price,pi.discount,pi.qty, si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, sku.vendor_id as master_vendor_id, pi.open_price_by,
			if(si.scale_type=-1, sku.scale_type, si.scale_type) as scale_type
		 $xtra_col
		from pos_items pi
		join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
		join sku_items si on si.id=pi.sku_item_id
*/
		if($_REQUEST['order_by']){
			$order_by = $this->sort_list[$_REQUEST['order_by']]['col'];
			$order_seq = $_REQUEST['order_seq'] == 'desc' ? 'desc' : 'asc';
		}
		
		if(!$order_by){
			$order_by = 'si.description';
			if(!$order_seq)	$order_seq = 'asc';
		}
		
		$sql = "select si.mcode, si.sku_item_code, si.artno, si.description, if(si.scale_type=-1, sku.scale_type, si.scale_type) as scale_type, if(sip.price is null, si.selling_price, sip.price) as price, sip.trade_discount_code,
			si.link_code, sic.qty as stock_bal, sic.changed, 
			output_gst.code as output_gst_code,output_gst.rate as output_gst_rate,
			if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)) as inclusive_tax
			from sku_group_item sgi
			left join sku_items si on si.sku_item_code=sgi.sku_item_code
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id=".mi($bid)."
			left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($bid)."
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
			where sgi.branch_id=".mi($sku_group_bid)." and sgi.sku_group_id=".mi($sku_group_id)." order by $order_by $order_seq";


		if($_REQUEST['show_q'])
			print $sql;
		
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$this->data = array();
		$q1 = $con_multi->sql_query_false($sql, true);
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$r['scale_type_label'] = 'No';
            if($r['scale_type']==1)      $r['scale_type_label'] = 'Fix Price';
			elseif($r['scale_type']==2)  $r['scale_type_label'] = 'Weighted';
			
			if($vp_session['is_under_gst']){
				if($r['inclusive_tax'] == 'yes'){
					// inclusive tax
					$gst_amt = round($r['price'] / ($r['output_gst_rate']+100) * $r['output_gst_rate'], 2);
					$price_included_gst = $r['price'];
					$before_tax_price = $price_included_gst - $gst_amt;
				}else{
					// exclusive tax
					$before_tax_price = $r['price'];
					$gst_amt = round($before_tax_price * $r['output_gst_rate'] / 100, 2);
					$price_included_gst = $before_tax_price + $gst_amt;
				}
				$r['before_tax_price'] = $before_tax_price;
				$r['gst_amt'] = $gst_amt;
				$r['price_included_gst'] = $price_included_gst;
			}
			$this->data[] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();
		
/* 		print_r($this->data); */
		
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$PRICE_LIST = new PRICE_LIST('Vendor Price List');
?>
