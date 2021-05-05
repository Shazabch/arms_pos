<?php
/*
2017-09-13 17:07 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/21/2018 4:42 PM Justin
- Enhanced to use 'like' to do matching for SKU description instead of using 'match'.
*/
include('include/common.php');

if (!$dp_session) die("<ul><li>Session Timeout. Please Login</li></ul>");

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
	global $con, $dp_session, $config, $smarty;
	
	$v = trim($_REQUEST['value']);
	$bid = mi($dp_session['branch_id']);
	$search_type = mi($_REQUEST['type']);
	
	$filter = array();
	
	// search type
	switch ($search_type)
    {
        case 1:     // search mcode and link_code
			$filter[] = "(si.mcode like ". ms(replace_special_char($v)."%")." or si.link_code like ".ms(replace_special_char.($v)."%") . ")";
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
	

	$limit_str = "limit ".($LIMIT+1);
		
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
	    if ($con->sql_numrows($result1) > $LIMIT)
	    {
			if(!$hide_print)	print "<li><span class=informal>Showing first $LIMIT items…</span></li>";
		}

		// generate list.
		while ($r = $con->sql_fetchassoc($result1))
		{
			$items_list[] = $r;
		}
    }
    $con->sql_freeresult($result1);
	
	if(count($items_list)>0){
		foreach($items_list as $r){
			$out .= "<li title=\"$r[id],$r[sku_item_code],$r[sku_id]\">". ($_REQUEST['multiple']?"<input id=cb_ajax_sku_$r[id] value=\"$r[id],$r[sku_item_code]\" title=\"".htmlspecialchars($r['description'])."\" artno=\"$r[artno]\" mcode=\"$r[mcode]\" type=checkbox> ":"")."<label class=clickable for=cb_ajax_sku_$r[id]>".htmlspecialchars ($r['description'])."</label>";
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
