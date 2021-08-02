{*
3/1/2011 5:25:32 PM Andy
- Fix back button redirect to wrong php.
- Fix scan product class bugs

7/26/2011 1:06:26 PM Justin
- Modified to syncronize with GRN future while adding items.

8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 11:05:11 AM Justin
- Modified the form layout to fill under PDA screen.
- Removed common js functions since it is already assigned at forms.js.

7/25/2012 6:04:34 PM Justin
- Enhanced to have UOM control while module is GRN.

11/1/2012 5:53 PM Justin
- Enhance when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhance when user delete one of the bom package sku, all related sku will be delete at the same time.
- Add a legend [BOM] after sku description.

11/30/2012 2:52:PM Fithri
- PDA - GRA Module

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

2/7/2013 3:58 PM Justin
- Bug fixed on system shows qty error even had keyed in the qty for item not in ARMS.

3/7/2013 3:42 PM Justin
- Enhanced to hide qty column while have checkbox (designed for batch barcode).
- Enhanced to ask user to tick at least one item to add (designed for batch barcode).

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

4/11/2014 10:53 AM Fithri
- add new Promotion module for PDA

4/24/2015 10:39 AM Justin
- Enhanced can insert document no and choose GST code when add item from GRA.

7/6/2017 2:41 PM Justin
- Enhanced to show message while adding items that is matched with PO/DO.

9/23/2019 9:19 AM William
- Enhanced to add display multi branch for po module.

12/10/2019 2:00 PM William
- Fixed po module not checking block item in PO block list.

12/17/2019 1:32 PM William
- Fixed grn module not checking block item in grn block list.

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

10/22/2020 5:50 PM William
- Enhanced to add new Qty column to batch barcode.

04/11/2020 3:55AM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu>Search)  and link to module menu page
	-Add table border and cellspacing

1/27/2021 1:31 PM William
- Enhanced to add selling price, discount, ctn, uom, remark to search scan result screen.

*}

{include file='header.tpl'}
{literal}
<style>
.tr_even{
  background-color: #eeeeee;
}
.inp_so{
	background-color: transparent;
}
</style>
{/literal}
<script>

var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var module = '{$module_name}';
var is_item_check = int('{$is_item_check}');
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
//var adj_type = '{$form.adj_type}';

{literal}
function add_items(){
	var total_qty = 0;
	
	if(module != "Adjustment" && !is_item_check){
		$('input.item_qty').each(function(i){
			total_qty += $(this).val();
		});
		
		if(document.f_a['isi_pcs'] != undefined){
			total_qty += document.f_a['isi_pcs'].value;
		}
		
		if(total_qty<=0){
			if (module != "Promotion") {
				notify('error','{/literal}{$LNG.ENTER_QTY_ERR}{literal}','center')
				return false;
			}
		}
	}else if(is_item_check){
		var check_count = $('input.item_checkbox:checked').length;
		
		if(check_count == 0){
			notify('error','{/literal}{$LNG.TICK_ONE_ITEM_TO_ADD_ERR}{literal}','center')
			return false;
		}
	}
	/*else{
		var error_type = "";
		$('input.item_qty').each(function(i){
			var qty = $(this).val();
			if(adj_type == "-" && qty > 0){
				error_type = "negative";
			}else if(adj_type == "+" && qty < 0){
				error_type = "positive";
			}
		});
		if(error_type){
			alert('Item must key in as '+error_type+' quantity');
			return false;
		}
	}*/
	document.f_a.add_btn.disabled = true;
	document.f_a.submit();
	return false;
}

function bom_ratio_calculation(id){
	if(!id) return;
	
	if(document.f_a['bom_ref_num['+id+']'] && document.f_a['bom_ref_num['+id+']'].value.trim() > 0){
		// is bom package
		var bom_ref_num = document.f_a['bom_ref_num['+id+']'].value.trim();
		var bom_qty_ratio = float(document.f_a['bom_qty_ratio['+id+']'].value);
		var multiply_ratio = 0;
		var doc_allow_decimal = int(document.f_a['doc_allow_decimal['+id+']'].value);
		
		var ctn = 0;
		if(document.f_a['ctn['+id+']'] && document.f_a['ctn['+id+']'].value.trim() > 0){
			ctn = float(document.f_a['ctn['+id+']'].value);
		}
		var pcs = float(document.f_a['pcs['+id+']'].value);
		var uom_fraction = float(document.f_a['uom_fraction['+id+']'].value);
		var total_pcs = (ctn * uom_fraction) + pcs;
		
		multiply_ratio = float(round(total_pcs / bom_qty_ratio,4));
		
		var bom_ref_num_list = $('.bom_ref_num_grp_'+bom_ref_num);
		
		if(int(multiply_ratio) != multiply_ratio){	// not allow decimal
			var group_allow_decimal_qty = true;
		
			// loop to check item can decimal qty or not
			for(var i=0; i<bom_ref_num_list.length; i++){
				// get the row grn item id + doc type
				var item_id = $(bom_ref_num_list[i]).attr('item_id');
				var tmp_doc_allow_decimal = int(document.f_a['doc_allow_decimal['+item_id+']'].value);

				if(!tmp_doc_allow_decimal){
					group_allow_decimal_qty = false;
					break;
				}
			}
			
			if(!group_allow_decimal_qty) multiply_ratio = int(multiply_ratio);	// group cannot hv decimal, make int
		}
		
		// loop to update qty
		for(var i=0; i<bom_ref_num_list.length; i++){
			// get the row grn item id + doc type
			var item_id = $(bom_ref_num_list[i]).attr('item_id');
			
			var item_bom_qty_ratio = float(document.f_a['bom_qty_ratio['+item_id+']'].value);
			
			var item_uom_fraction = float(document.f_a['uom_fraction['+item_id+']'].value);
			
			var item_ctn = 0;
			var item_pcs = 0;
			var item_total_pcs = item_bom_qty_ratio * multiply_ratio;
			 
			if(item_uom_fraction > 1){
				item_ctn = Math.floor(item_total_pcs / item_uom_fraction);
				item_pcs = item_total_pcs - (item_ctn * item_uom_fraction);
			}else{
				item_pcs = item_total_pcs;
			}
			
			if(document.f_a['ctn['+item_id+']'] && document.f_a['ctn['+item_id+']'].value.trim() > 0){
				if(item_uom_fraction > 1){
					document.f_a['ctn['+item_id+']'].value = item_ctn;
				}else{
					document.f_a['ctn['+item_id+']'].value = "";
				}
			}
			
			document.f_a['pcs['+item_id+']'].value = item_pcs;
			
			// recal row
			//if(tmp_po_item_id != po_item_id) row_recalc(tmp_po_item_id);
		}
	}
}

function uom_change(value, id){
	var uom_info = value.split(",");
	var old_fraction = document.f_a['uom_fraction['+id+']'];
	var selling_price = document.f_a['selling_price['+id+']'];
	
	// cost
	var inp_cost_price = document.f_a['cost_price['+id+']'];
	var old_cost = float(inp_cost_price.value)/old_fraction.value;
	var new_cost = old_cost*float(uom_info[1]);
	inp_cost_price.value = round(new_cost,global_cost_decimal_points);
	
	// selling
	var old_selling = float(selling_price.value)/old_fraction.value;
	var new_selling= old_selling*float(uom_info[1]);	
    selling_price.value=round(new_selling,2);
    
	//$('uom_id,'+id).value=uom_info[0];
	old_fraction.value = uom_info[1];
	
	var inp_ctn = document.f_a['ctn['+id+']'];
	if(uom_info[1] == 1){
		inp_ctn.disabled = true;
		inp_ctn.value='';
	}else{
		inp_ctn.disabled = false;
	}
	
	row_recalc(id);
}

function validate_discount_format(discount_pattern){
	if(!discount_pattern)	return '';
	
	discount_pattern = discount_pattern.regex(/[^0-9\.%+]/g,'');
    discount_pattern = discount_pattern.regex(/\+$/,'');
    return discount_pattern;
}

function get_discount_amt(amt, discount_pattern, params){
	var total_discount_amt = 0;
	var discount_amt = 0;
	if(discount_pattern == '')	return 0;
	if(!params)	params = {};
	var currency_multiply = 0;
	var discount_by_value_multiply = 0;
	if(params['currency_multiply'])	currency_multiply = float(params['currency_multiply']);
	if(params['discount_by_value_multiply'])	discount_by_value_multiply = float(params['discount_by_value_multiply']);
	
	// check discount pattern
	discount_pattern = validate_discount_format(discount_pattern);
    var original_amt = amt;
    
	if (discount_pattern != ''){
		var discount_list = discount_pattern.split("+");
		if(discount_list.length > 0){
			for(let i=0;i<discount_list.length; i++){
				if (discount_list[i].indexOf("%")>0){	// discount by percentage
					discount_amt = float(amt * float(discount_list[i])/100);
				}
				else{	// discount by value
					discount_amt = float(discount_list[i]);
					if(currency_multiply>0){	// multiply currency rate
						discount_amt = float(discount_amt*currency_multiply);
					}
					
					if(discount_by_value_multiply>0){	// maybe more than 1 branch
						discount_amt = float(discount_amt*discount_by_value_multiply);
					}
				}
				
				total_discount_amt += discount_amt;
				amt -= discount_amt;
			}
		}
	}
	
	if(total_discount_amt > original_amt)	total_discount_amt = original_amt;	// cannot discount more than amt
	return total_discount_amt;
}

function row_recalc(id){
	var row_amt = 0;
	var row_qty = 0;
	var ctn = float(document.f_a['ctn['+id+']'].value);
	var pcs = float(document.f_a['pcs['+id+']'].value);
	var uom = document.f_a['sel_uom['+id+']'].value;
	var uom_info = uom.split(",");
	var fraction = float(uom_info[1]);
	var selling_price = float(document.f_a['selling_price['+id+']'].value);
	var selling = float(selling_price/fraction);
	var discount_format = document.f_a['item_discount['+id+']'].value.trim();
	var discount_amt = 0;
	
	row_qty = (ctn*fraction) + pcs;
	row_qty = float(round(row_qty, global_qty_decimal_points));
	row_amt = float(row_qty*selling);
	discount_amt = float(get_discount_amt(row_amt, discount_format));
	
	if(discount_amt){
		row_amt -= discount_amt;
	}
	
	document.getElementById('span-so_amt-'+id).innerHTML =  row_amt.toFixed(2);
	document.f_a['item_discount_amount['+id+']'].value = discount_amt;
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.SCAN_RESULT}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="{$smarty.server.PHP_SELF}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 " role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<div class="alert alert-success border">{count var=$items} {$LNG.ITEMS_FOUND}</div>
<form name="f_a" method="post" onSubmit="return add_items();">
	<input type="hidden" name="scan_product" value="1" />
	<input type="hidden" name="scan_product_result" value="1" />
	<input type="hidden" name="product_code" value="{$smarty.request.product_code}" />
	<div class="container-fluid mb-2">
		{if $is_item_check}
					<div class="custom-checkbox custom-control">
							<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-1"  name="allow_duplicate">
							<label for="checkbox-1" class="custom-control-label mt-1">{$LNG.AUTO_ADD_QTY_WHEN_ITEM_DUPLICATE}</label>
					</div>
		{/if}
	</div>
	<!--Table-->
	<div class="col-xl-12 ">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table mb-0 text-md-nowrap">
						<thead>
							<tr>
								{if $module_name eq 'GRN' && !$is_isi}
									<th rowspan="2">{$LNG.DESCRIPTION}</th>
									<th colspan="2" class="text-center">{$LNG.QTY}</th>
								{else}
									{if $is_item_check}
										<th>#</th>
										<th>{$LNG.DESCRIPTION}</th>
										<th>{$LNG.QTY}</th>
									{else}
										{if $module_name neq 'Sales Order'}
											<th>{$LNG.DESCRIPTION}</th>
										{/if}
										{if $module_name eq 'Purchase Order' && $deliver_to}<th>{$LNG.BRANCH}</th>{/if}
										{if $module_name neq 'Promotion' && $module_name neq  'Sales Order'}<th>{$LNG.QTY}<br />({$LNG.PCS})</th>{/if}
										{if $module_name eq 'Purchase Order'}<th>{$LNG.FOC}<br />({$LNG.PCS})</th>{/if}
										{if $module_name eq 'GRA'}<th>{$LNG.COST_PRICE}</th>{/if}
									{/if}
								{/if}
								{if $module_name eq 'Sales Order'}
									<th>{$LNG.UOM}</th>
									<th class="text-center">{$LNG.CTN}</th>
									<th class="text-center">{$LNG.PCS}</th>
									<th class="text-center">{$LNG.SELLING_PRICE}</th>
									<th class="text-center">{$LNG.DIS}</th>
									<th>{$LNG.AMT}</th>
								{/if}
							</tr>
							{if $module_name eq 'GRN' && !$is_isi}
								<tr>
									<td class="text-center">{$LNG.CTN}</td>
									<td class="text-center">{$LNG.PCS}</td>
								</tr>
							{/if}
						</thead>
						<tbody>
							{foreach from=$items item=r name=i}
								{if $module_name eq 'Sales Order'}
									{assign var=i value=1}
								{/if}
							    <tr class="{if $i%2 neq 1}tr_even{/if}">
									{if $is_item_check}
										<td align="center"><input type="checkbox" name="item_check[{$r.id}]" class="item_checkbox" {if $r.item_check}checked{/if} /></td>
									{/if}
									
							        <td colspan="{if $module_name eq 'Sales Order'}6{else}1{/if}" {if $deliver_to}valign="top"{/if}>
										{if $module_name eq 'GRN' && $config.use_grn_future && $is_isi}
											<input type="hidden" name="is_isi" value="{$is_isi}" />
											<input type="text" class="form-control min-w-100" name="isi_desc" size="40" value="{$smarty.request.isi_desc}" onchange="this.value = this.value.toUpperCase().trim();" />
										{else}
											<span style="white-space: normal; color:blue;{if ($module_name eq 'Purchase Order' && $blocked_po[$r.id]) || ($module_name eq 'GRN' && $blocked_doc[$r.id]) }text-decoration:line-through;{/if}" >{$r.item_code_remark} {if $r.bom_ref_num > 0}<font color="grey">({$LNG.BOM})</font>{/if}</span><br />
											<span style="white-space: normal;" {if ($module_name eq 'Purchase Order' && $blocked_po[$r.id]) || ($module_name eq 'GRN' && $blocked_doc[$r.id])}style="text-decoration:line-through;"{/if}>{$r.description}</span>
											{if $module_name eq 'Purchase Order' && $blocked_po[$r.id]}<br /><span style="color:red;">({$LANG.PO_ITEM_IS_BLOCKED})</span>{/if}
											{if $module_name eq 'GRN' && $blocked_doc[$r.id]}<br /><span style="color:red;">({$LANG.DOC_ITEM_IS_BLOCKED|sprintf:"GRN"})</span>{/if}
											{if $smarty.session.grn.type eq 'DO' || $smarty.session.grn.type eq 'PO'}
												{if $r.matched_with_po}
													<br />
													<font color="blue">(Matched with {$smarty.session.grn.type})</font>
												{elseif $r.matched_with_po_pc}
													<br />
													<font color="blue">(Matched with {$smarty.session.grn.type} [Parent & Child])</font>
												{elseif $r.unmatched_with_po}
													<br />
													<font color="red">(Item not in {$smarty.session.grn.type})</font>
												{/if}
											{/if}
											{if $module_name eq 'Sales Order'}
												{if $r.link_code}<br />{$config.link_code_name}: {$r.link_code}{/if}
											{/if}
										{/if}
									</td>
									{if  $module_name eq 'Purchase Order' && $deliver_to}
										<td >
											<table>
												{foreach from=$deliver_to.branch_code item=bcode}
													<tr>
														<td>{$bcode}</td>
													</tr>
												{/foreach}
											</table>
										</td>
									{/if}
									{if $module_name eq 'GRN'}
										{if $is_isi}
											<td align="center"><input type="text" name="isi_pcs" class="items r form-control min-w-100" size="3" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points})); positive_check(this);" value="{$smarty.request.isi_pcs}" /></td>
										{else}
											<td align="center">
												{if ($module_name eq 'GRN' && $config.doc_allow_edit_uom && $r.uom_fraction ne 1) || ($module_name ne 'GRN' && $r.uom_fraction ne 1)}
													<input type="text" name="ctn[{$r.id}]" class="items r form-control min-w-100" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onchange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); {if $r.bom_ref_num > 0}bom_ratio_calculation({$r.id});{/if}" value="{$smarty.request.ctn[$r.id]}" {if $blocked_doc[$r.id]} disabled{/if} />
												{else}
													&nbsp;
												{/if}
											</td>
											<td align="center"><input type="text" name="pcs[{$r.id}]" class="items r item_qty form-control min-w-100 min-w-100 {if $r.bom_ref_num > 0}bom_ref_num_grp_{$r.bom_ref_num}{/if}" item_id="{$r.id}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onchange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); {if $r.bom_ref_num > 0}bom_ratio_calculation({$r.id});{/if}" value="{$smarty.request.pcs[$r.id]}" {if $blocked_doc[$r.id]} disabled{/if} /></td>
											<input type="hidden" name="uom_fraction[{$r.id}]" value="{$r.uom_fraction|default:1}" />
											<input type="hidden" name="bom_ref_num[{$r.id}]" value="{$r.bom_ref_num}" />
											<input type="hidden" name="bom_qty_ratio[{$r.id}]" value="{$r.bom_qty_ratio}" />
											<input type="hidden" name="doc_allow_decimal[{$r.id}]" value="{$r.doc_allow_decimal}" />
										{/if}
									{elseif !$is_item_check}
										{if $module_name neq 'Promotion'}
											{if  $module_name eq 'Purchase Order' && $deliver_to}
												<td align="center">
													<table class="table">
														{foreach from=$deliver_to.branch_id item=bid}
															<tr>
																<td align="center"><input type="text" name="item_qty[{$r.id}][{$bid}]" class="items r item_qty form-control min-w-100" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this);" value="{$smarty.request.item_qty[$r.id]}" {if $blocked_po[$r.id]}disabled{/if} /></td>
															</tr>
														{/foreach}
													</table>
												</td>
												<td>
													<table>
														{foreach from=$deliver_to.branch_id item=bid}
															<tr>
																<td>
																	<input class="items r item_qty form-control min-w-100" {if $blocked_po[$r.id]}disabled{/if} type="text" name="foc_qty[{$r.id}][{$bid}]" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} />
																</td>
															</tr>
														{/foreach}
													</table>
												</td>
											{else}
												{if $module_name neq 'Sales Order'}
												<td align="center"><input type="text" name="item_qty[{$r.id}]" {if $blocked_po[$r.id]}disabled{/if} class="items r item_qty form-control min-w-100" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this);" value="{$smarty.request.item_qty[$r.id]}" /></td>
												{/if}
												{if $module_name eq 'Purchase Order'}
												<td align="center">
													<input class="items r item_qty form-control min-w-100" {if $blocked_po[$r.id]}disabled{/if} type="text" name="foc_qty[{$r.id}]" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} />
												</td>
												{/if}
											{/if}
										{else}
										<input type="hidden" name="item_qty[{$r.id}]" value="1" />
										{/if}
										{if $module_name eq 'GRA'}
										<td align="center"><input type="text" name="item_price[{$r.id}]" class="items r item_price form-control min-w-100" size="6" value="{$r.return_cost}" /></td>
										{/if}
									{elseif $is_item_check}
										<td align="center"><input type="text" class="items form-control min-w-100" size="3" name="qty[{$r.id}]" value="1" /></td>
									{/if}
							    </tr>
								{if $module_name eq 'Sales Order'}
								<tr class="{if $i%2 neq 1}tr_even{/if}">
									<input type="hidden" name="so_item[{$r.id}]" />
									<td>
										<input type="hidden" name="uom_fraction[{$r.id}]" value="{$r.uom_fraction}" />
										<select class="inp_so form-select form-control min-w-100" name="sel_uom[{$r.id}]" onchange="uom_change(this.value,'{$r.id}');" {if $config.doc_uom_control}disabled {/if}>
											{foreach from=$uom key=uom_id item=u}
												<option value="{$uom_id},{$u.fraction}" {if ($item.uom_id eq $uom_id) or (!$item.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
											{/foreach}
										</select>
									</td>
									<td>
										<input type="number" class="items r item_qty inp_so form-control min-w-100" disabled name="ctn[{$r.id}]"  {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} 
											onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);row_recalc({$r.id});" 
											value="{$smarty.request.ctn[$r.id]}" 
										/>
									</td>
									<td>
										<input type="text" class="items r item_qty inp_so form-control min-w-100" name="pcs[{$r.id}]" {if $blocked_po[$r.id]}disabled{/if} {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} 
											onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this);row_recalc({$r.id});" 
											value="{$smarty.request.pcs[$r.id]}" 
										/>
									</td>
									<td>
										<input class="items r inp_so form-control min-w-100" onchange="row_recalc({$r.id});" type="text" name="selling_price[{$r.id}]" value="{$r.selling_price|default:0|number_format:2}" />
									</td>
									<td>
										<input type="hidden" name="item_discount_amount[{$r.id}]" value="0" />
										<input class="items r inp_so form-control min-w-100" onchange="row_recalc({$r.id});" type="text" name="item_discount[{$r.id}]" />
									</td>
									<td>
										<input type="hidden" name="line_amt[{$item.id}]"/>
										<span class="items r" style="float: right;" id="span-so_amt-{$r.id}"></span>
									</td>
								</tr>
								<tr class="{if $i%2 neq 1}tr_even{/if}">
									<td colspan="6">
										{$LNG.REMARK}: <textarea class="inp_so min-w-100 form-control" style="resize: none;" name="remark[{$r.id}]"></textarea>
									</td>
								</tr>
								<tr class="{if $i%2 neq 1}tr_even{/if}">
									<td colspan="6">
										{$LNG.STOCK_BALANCE}: <span>{$r.stock_balance}</span></br>
										{$LNG.COST}: <input style="width: 50%" type="text" class="form-control min-w-100" readonly name="cost_price[{$r.id}]" value="{$r.so_cost_price|number_format:$config.global_cost_decimal_points:".":""}" /></br>
										{$LNG.RESERVE_QTY}: <span>{$r.reserve_qty|default:'0'}<a data-toggle="modal" href="#reserve-qty-modal"><i class="fas fa-question-circle ml-2"></i></a></span>
									</td>
								</tr>
								{/if}
								
								{if $module_name eq 'GRA'}
									<tr>
										<td  nowrap colspan="3">
											{$LNG.INV}/{$LNG.DO_NO}. <input type="text" name="doc_no[{$r.id}]" class="form-control min-w-100" size="15" value="" /> 
											{if $form.is_under_gst}
												&nbsp;&nbsp;
												GST Code
												<select name="gst_id[{$r.id}]">
													{foreach from=$gst_list key=dummy item=gst}
														<option value="{$gst.id}" {if $r.gst_id eq $gst.id}selected{/if}>{$gst.code} ({$gst.rate|default:'0'}%)</option>
													{/foreach}
												</select>
											{/if}
										</td>
									</tr>
								{/if}
							{/foreach}
							{if $module_name eq 'GRA'}
								<input type="hidden" name="return_type" value="{$return_type}" />
							{/if}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- /Table -->
	<div class="mb-2 mx-3">
		<input name="add_btn" class="btn btn-primary btn-block" type="submit" value="Add">
	</div>
</form>
<!-- Reserve QTY Modal -->
<div class="modal fade" id="reserve-qty-modal">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo">
			<div class="modal-header card-category">
				<h6 class="modal-title">Reserve Quantity <i class="fas fa-question-circle"></i></h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<p>{$LNG.RESERVE_QTY_MODAL_MSG}</p>
			</div>
			<div class="modal-footer ">
				<button class="btn ripple btn-info mx-auto" data-dismiss="modal" type="button">OK</button>
			</div>
		</div>
	</div>
</div>
<!-- End QTY Modal-->
{*<div style="float:right;"><input type="button" value="Add" onClick="add_items();" /></div>*}
</div>
<script>
{literal}
if(!is_item_check)  $('input.items').get(0).focus();
{/literal}
</script>
{include file='footer.tpl'}
