<?php
/*
11/22/2008 8:57:13 AM yinsee
- remove HQ only

1/25/2011 9:38:51 AM Alex
- change use report_server

6/24/2011 6:25:40 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:34:18 PM Andy
- Change split() to use explode()

11/11/2011 3:04:48 PM Andy
- Add a notice for user to know "Promotion Summary" is not included "Mix and Match Promotion".

10/17/2014 9:45 AM Fithri
- add option to select "Items in Promotion"
- improve formatting

2/26/2020 10:12 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
/*
if (BRANCH_CODE == 'GURUN')
{
$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");
print_r($con);
}
*/

class PromotionSummary extends Report
{
	var $where;
	var $groupby;
	var $select;
	var $branch_id;
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $con_multi;
		$where = $this->where;
		
 	    $con_multi->sql_query("select * from branch where $where[bid] order by sequence,code");
		$smarty->assign("branch", $con_multi->sql_fetchrow());
		$con_multi->sql_freeresult();

		$con_multi->sql_query($abc="select c.description as categoryname, b.code, si.sku_item_code, si.artno, si.mcode, si.link_code, si.description, p.id as promotion_id, p.title, p.date_from, p.date_to, p.time_from, p.time_to, pi.*
		from promotion p
		left join promotion_items pi on pi.branch_id = p.branch_id and pi.promo_id = p.id
		left join sku_items si on pi.sku_item_id = si.id
		left join sku on si.sku_id = sku.id
		left join branch b on p.branch_id = b.id
		left join category_cache cc on sku.category_id = cc.category_id
		left join category c on cc.p1 = c.id
		where $where[branch_id] and $where[category_id] and $where[brand_id] and $where[sku_item_id] and $where[date] and $where[approved] and p.status between 0 and 3 and p.promo_type='discount' order by p.id, pi.sku_item_id");
		
		//print "<pre>$abc</pre>";
		while ($r = $con_multi->sql_fetchrow())
		{
			$header[$r['promotion_id']] = $r;
			$data[$r['promotion_id']][] = $r;
			$promo_count[$r['promotion_id']] = 1;
			$count++;
		}
		$con_multi->sql_freeresult();

		if ($data)
		{
			foreach ($data as $promo_id => $d2)		
			{
				foreach ($d2 as $d)
				{
					$d['price'] = $this->get_last_selling_price($d['sku_item_id'],$d['branch_id']);
					$data2[$promo_id][] = $d;
				}
			}
		}
		$data = $data2;
		
		$page_size = 25;
		$smarty->assign('page_size',$page_size);
		$total_item = $count+(count($promo_count)*2);
		$smarty->assign('page_total',ceil($total_item/$page_size));
/*		print $total_item;
		print "<pre>";
		print_r($data);
		print "</pre>";
*/		

		if ($_REQUEST['brand_id']) $con_multi->sql_query("select * from brand where id = ".mi($_REQUEST['brand_id']));
		$smarty->assign("selected_brand", $con_multi->sql_fetchrow());
		$con_multi->sql_freeresult();

		$smarty->assign("category", $this->category);
		$smarty->assign("data", $data);
		$smarty->assign('header',$header);
		$smarty->assign("count", $count);
	}
	
	function process_form()
	{
		global $config,$con,$con_multi;
		
		$where = array();
		
		// call parent
		if ($_REQUEST['type'] == 'category')
			unset($_REQUEST['sku_code_list_2']);
		else if ($_REQUEST['type'] == 'sku') 
		{
			unset($_REQUEST['category_id']);
			unset($_REQUEST['category']);
		}
		else if ($_REQUEST['type'] == 'items_in_promotion') 
		{
			unset($_REQUEST['sku_code_list_2']);
			unset($_REQUEST['category_id']);
			unset($_REQUEST['category']);
		}
		parent::process_form();
		
		$where['date'] = ms($_REQUEST['date_from'])." between date_from and date_to ";
		
		$this->branch_id = get_request_branch();
		if ((BRANCH_CODE != 'HQ' && $this->branch_id > 0) || (BRANCH_CODE == 'HQ' && $_REQUEST['branch_id'] > 0))
		{
			$_REQUEST['branch_id'] = $this->branch_id;
			$where['branch_id'] = "p.branch_id = ".mi($this->branch_id);
			$where['bid'] = "id = ".mi($this->branch_id);
			//$where['branch_id'] = "p.promo_branch_id regexp 'i:".$_REQUEST['branch_id']."'";
		}
		else
		{
			$where['bid'] = $where['bid'] = "id = 1";
			$where['branch_id'] = 1;
		}
		$where['category_id'] = 1;
		$where['brand_id'] = 1;
		$where['sku_item_id'] = 1;

		if ($_REQUEST['type'] == 'category')
		{
			
			$con_multi->sql_query("select level from category where id=".mi($_REQUEST['category_id']));
			$tmp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $tmp['level'];
			$where['category_id'] = "sku.category_id in (select category_id from category_cache where p$level = ".mi($_REQUEST['category_id']).")";
			if ($_REQUEST['brand_id'] == 0 && $_REQUEST['brand_id'] != 'All')
			$where['brand_id'] = "sku.brand_id = ''";
			elseif ($_REQUEST['brand_id'] > 0) 
			$where['brand_id'] = 'sku.brand_id = '.mi($_REQUEST['brand_id']);			
		}
		else if ($_REQUEST['type'] == 'sku') 
		{
			$code_list = $_REQUEST['sku_code_list_2'];
		    $list = explode(",",$code_list);
		    for($i=0; $i<count($list); $i++){
		        $con_multi->sql_query("select id, description from sku_items where sku_item_code=".ms($list[$i])) or die(sql_error());
		        $temp = $con_multi->sql_fetchrow();
				$con_multi->sql_freeresult();
		        $category[$list[$i]]['sku_item_code']=$list[$i];
		        $category[$list[$i]]['description']=$temp['description'];
				$list[$i]="'".$temp['id']."'";
			}
		    $list = join(",",$list);
		    $where['sku_item_id'] = "sku_item_id in($list)";
		}
		else if ($_REQUEST['type'] == 'items_in_promotion')
		{
		    $where['sku_item_id'] = "sku_item_id in (".join(',',$this->get_items_in_promotion()).")";
		}
		

		$where['approved'] = "approved = ".mi($_REQUEST['approved']);
		$this->where = $where;
	    $this->category = $category;
	    //print_r($where['sku_item_code']);
	}
	
	function get_last_selling_price($sku_item_id,$branch_id)
	{
		global $con,$con_multi;
		$con_multi->sql_query("select if (sip.price is null, si.selling_price,sip.price) as price from sku_items si left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = ".mi($branch_id)." where si.id = ".mi($sku_item_id)." limit 1");
		
		//$con->sql_query("select si.price from sku_items si left join sku_item si where sku_item_id = ".ms($sku_item_id)." and branch_id = ".mi($branch_id)." limit 1");
		$r = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		return $r['price'];
	
	}
		
	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d");
	}
	
	function get_items_in_promotion()
	{
		global $con,$con_multi;
		
		$filter = array();
		$filter[] = 'p.active = 1';
		$filter[] = ms($_REQUEST['date_from']).' between p.date_from and p.date_to';
		if ($_REQUEST['branch_id']) $filter[] = 'p.branch_id = '.mi($_REQUEST['branch_id']);
		$filter[] = 'p.approved = '.mi($_REQUEST['approved']);
		$filter = join(' and ',$filter);
		
		$sql = "select sku_item_id from promotion_items pi left join promotion p on pi.promo_id = p.id where $filter";
		$q = $con_multi->sql_query($sql);
		$list = array(0);
		while ($r = $con_multi->sql_fetchassoc($q)) $list[] = $r['sku_item_id'];
		$con_multi->sql_freeresult($q);
		//print join("\n",$list);
		return $list;
	}
}

//$con_multi = new mysql_multi();
$report = new PromotionSummary('Promotion Summary');
//$con_multi->close_connection();
?>
