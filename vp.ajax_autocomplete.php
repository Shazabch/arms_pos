<?php
/*
12/26/2012 5:00 PM Andy
- add checking to active sku group item for SKU Change Selling Price.

1/2/2012 10:49 AM Andy
- Add get sku id in ajax sku autocomplete.

10/8/2013 10:22 AM Fithri
- show mcode in search SKU autocomplete

2017-09-13 17:07 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/21/2018 4:42 PM Justin
- Enhanced to use 'like' to do matching for SKU description instead of using 'match'.
*/
include('include/common.php');

if (!$vp_session) die("<ul><li>Session Timeout. Please Login</li></ul>");

if(isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_search_sku':
			ajax_search_sku();
			exit;
		default:
			die("<ul><li>Invalid Action</li></ul>");
			exit;
	}
}

function ajax_search_sku(){
	global $con, $vp_session, $config, $smarty;
	
	$v = trim($_REQUEST['value']);
	$bid = mi($vp_session['branch_id']);
	$search_type = mi($_REQUEST['type']);
	
	$filter = array();
	
	// search type
	switch ($search_type)
    {
        case 1:     // search mcode and link_code
			$filter[] = "(si.mcode like ". ms(replace_special_char($v)."%")." or si.link_code like ".ms(replace_special_char($v)."%") . ")";
            break;
        case 2:	// search artno
			$filter[] = "si.artno like " . ms(replace_special_char($v)."%");
            break;

        case 3:     // search arms code
			$filter[] = "(sku_id = ".mi($v). " or sku_item_code like " . ms(replace_special_char($v)."%") .")";
            break;

		default:    // search description
			$ll = preg_split("/\s+/", $v);

			$desc_matching = array();
			foreach ($ll as $l) {
				if ($l) $desc_matching[] = "si.description like " . ms('%'.replace_special_char($l).'%');
			}
			$desc_match = join(" and ", $desc_matching);
			
			if ($config['sku_autocomplete_hide_variety'])
				//$sql_where = "sku_item_code like " . ms('%0000') . " and $dept $desc_match";
				$filter[] = "si.is_parent=1 and $desc_match";
			else
				$filter[] = "$desc_match";
			break;
	}

	if($config['sku_autocomplete_limit']) $LIMIT = $config['sku_autocomplete_limit'];
	else $LIMIT = 50;
		
	$vsh_tbl = 'vendor_sku_history_b'.$bid;
	$today = date("Y-m-d");
	
	/*if($vp_session['vp']['use_last_grn']){
		$xtra_col = ",vsh.vendor_id as last_vendor_id";
		$xtra_join = "left join $vsh_tbl vsh on vsh.sku_item_id=si.id and vsh.from_date=0 and vsh.to_date=0";	// last vendor id
		
		$filter[] = $vp_session['id']." in (sku.vendor_id, vsh.vendor_id)";
	}else{
		$filter[] = "sku.vendor_id=".mi($vp_session['id']);*/
		$limit_str = "limit ".($LIMIT+1);
	//}
	
	$sid_list = array();
	$sku_group_id = $sku_group_bid = 0;
	
	$sku_group_ids = $vp_session['vp']['sku_group_info'][$bid];
	if($sku_group_ids){
		list($sku_group_bid, $sku_group_id) = explode("|", $sku_group_ids);
		if(!$sku_group_bid || !$sku_group_id){
			$sku_group_id = $sku_group_bid = 0;		
		}else{
			// select sku id list
			$con->sql_query("select distinct si.sku_id
	from sku_group_item sgi
	join sku_items si on si.sku_item_code=sgi.sku_item_code
	join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
	where sgi.branch_id=".mi($sku_group_bid)." and sgi.sku_group_id=".mi($sku_group_id));
			$sku_id_list = array();
			while($r = $con->sql_fetchassoc()){
				$sku_id_list[] = mi($r['sku_id']);
			}
			$con->sql_freeresult();
		}
	}
	if($sku_id_list)	$filter[] = "si.sku_id in (".join(',', $sku_id_list).")";
	else	$filter[] = "si.id=-1";
		
	$filter[] = "si.active=1";
	if($filter)	$filter = 'where '.join(' and ', $filter);
	else	$filter = '';
	
	$sql = "select si.id, si.sku_item_code, si.description, si.artno, si.mcode, si.sku_id, sku.vendor_id as master_vendor_id,
								if(sku_items_price.price>0,sku_items_price.price,si.selling_price) as selling_price, 
								if(sku_items_price.cost>0,sku_items_price.cost,si.cost_price) as cost_price, 
								sku.varieties , si.block_list, sku.sku_type, si.is_parent, si.doc_allow_decimal $xtra_col
								from sku_items si
								left join sku_items_price on si.id = sku_items_price.sku_item_id and sku_items_price.branch_id = $bid
								left join sku on si.sku_id = sku.id
								left join category on sku.category_id = category.id
								left join category_cache cc on cc.category_id=sku.category_id
								$xtra_join
								$filter
								order by si.description $limit_str";
				
	//print $sql;
	
	$result1 = $con->sql_query($sql);
								
	$out = '';
	$items_list = array();
	if(!$hide_print)	print "<ul>";
	if ($con->sql_numrows($result1) > 0)
	{
	    if (!$vp_session['vp']['use_last_grn'] && $con->sql_numrows($result1) > $LIMIT)
	    {
			if(!$hide_print)	print "<li><span class=informal>Showing first $LIMIT items…</span></li>";
		}

		// generate list.
		while ($r = $con->sql_fetchassoc($result1))
		{
			if($vp_session['vp']['use_last_grn']){
				if(count($items_list)>=$LIMIT){
					if(!$hide_print)	print "<li><span class=informal>Showing first $LIMIT items…</span></li>";
					break;
				}
			
				if($r['last_vendor_id'] && $r['last_vendor_id'] != $vp_session['id'])	continue;
				if($r['master_vendor_id'] != $vp_session['id'])	continue;
			}
			$items_list[] = $r;
		}
    }
    $con->sql_freeresult($result1);
	
	if(count($items_list)>0){
		foreach($items_list as $r){
			$out .= "<li title=\"$r[id],$r[sku_item_code],$r[sku_id]\">". ($_REQUEST['multiple']?"<input id=cb_ajax_sku_$r[id] value=\"$r[id],$r[sku_item_code]\" title=\"".htmlspecialchars($r['description'])."\" artno=\"$r[artno]\" mcode=\"$r[mcode]\" type=checkbox> ":"")."<label class=clickable for=cb_ajax_sku_$r[id]>".htmlspecialchars($r['description'])."<span class=informal> (MCode:".htmlspecialchars($r['mcode']).")</span></label>";
			$out .= "</span>";
			$out .= "</li>";
		}
	}else{
		if(!$hide_print)	print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
	}

	if(!$hide_print){
        print $out;
    	print "</ul>";
	}
}
?>
