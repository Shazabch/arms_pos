{*
8/8/2011 11:05:11 AM Justin
- Added new JS function float and round for the use of global qty decimal points.
- Removed common js functions since it is already assigned at forms.js.

7/25/2012 5:08:43 PM yinsee
- add row number for items

04/17/2020 04:38 PM Sheila
- Modified layout to compatible with new UI.

11/04/2020 3:37PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page
	-Remove class small in table and added cellspacing and cellpadding

2/1/2021 4:31 PM William
- Enhanced to add selling price, discount, ctn, uom, remark to search scan result and view sales order item screen.
*}

{include file='header.tpl'}

<script>
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
{literal}

function change_row_color(ele){
	// if($(ele).is(":checked")){
	// 	$(ele).parent().parent().parent().addClass('table-warning');
	// 	$(ele).parent().parent().parent().next().addClass('table-warning');
	// }else{
 //        $(ele).parent().parent().parent().removeClass('table-warning');
 //        $(ele).parent().parent().parent().next().removeClass('table-warning');
	// }

	var ele_parent = $(ele).parent().parent().parent();
	var tr_ele = ele_parent[0];
	if(tr_ele != undefined){
		if($(ele).is(":checked")){
			if(tr_ele.className)   $("."+tr_ele.className).css('background-color','#FFEEBA');
		}else{
			if(tr_ele.className)   $("."+tr_ele.className).css('background-color','#fff');
		}
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0){
			notify('error','{/literal}{$LNG.PLEASE_CHECK_AT_LEAST_ONE_ITEM}{literal}','center');
			return false;
		}
		if(!confirm('{/literal}{$LNG.DELETE_CONFIRMATION_MSG}{literal}')) return false;
		
        document.f_a['a'].value = 'delete_items';
	}else{
        document.f_a['a'].value = 'save_items';
	}
	document.f_a.submit();
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

function update_all_item_amt(){
	var all_tr_item_row = document.querySelectorAll('[id^="tr_so_item,"]');
	
	if(all_tr_item_row.length > 0){
		for(var i=0; i<all_tr_item_row.length; i++){
			var item_id = all_tr_item_row[i].id.split(',')[1];
			row_recalc(item_id);
		}
	}
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.scan_product.name}</h4>
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
	</ol>
</nav>
<!-- /BreadCrumbs -->

{include file='sales_order.top_include.tpl'}<br>

<div class="card ">
	<div class="card-body">
	{if $items}
		<div class="d-flex justify-content-between align-items-center py-2">
			<div class="badge badge-pill badge-light p-2 border">{count var=$items} {$LNG.ITEMS}</div>
			<div class="">
				<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			</div>
		</div>
		<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" />
			<div class="table-responsive">
				<table id="so_items" class="table text-md-nowrap mb-0">
			    <thead>
			    	<tr>
				        <th>
				        	<div class="custom-checkbox custom-control">
								<input type="checkbox" class="toggle_chx custom-control-input" id="checkbox-del0">
								<label for="checkbox-del0" class="custom-control-label mt-1">{$LNG.DEL}</label>
							</div>
				        </th>
				        <th>{$LNG.UOM}</th>
				        <th>{$LNG.CTN}</th>
						<th>{$LNG.PCS}</th>
						<th>{$LNG.SELLING_PRICE}</th>
						<th>{$LNG.DIS}</th>
						<th>{$LNG.AMT}</th>
				    </tr>
			    </thead>
				{*<pre>{$items|@print_r}</pre>*}
			    <tbody>
			    	{foreach from=$items item=r name=i}
						<tr id="tr_so_item,{$r.id}" class="tr_so_item_{$r.id}">
							<input type="hidden" name="so_item[{$r.id}]" />
							<td rowspan="5" colspan="1">
								<div class="custom-checkbox custom-control">
									<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
									<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
								</div>
							</td>

						</tr>
						<tr class="tr_so_item_{$r.id}">
							<td colspan="6">
								ARMS Code: {$r.sku_item_code}</br>
								{if $r.link_code}{$config.link_code_name}: {$r.link_code}</br>{/if}
								{$r.sku_description}
							</td>
						</tr>
						<tr class="tr_so_item_{$r.id}">
				            <td>
								<input type="hidden" name="uom_fraction[{$r.id}]" value="{$r.uom_fraction|default:1}" />
								<select name="sel_uom[{$r.id}]" class="form-control  select2 min-w-100" onchange="uom_change(this.value,'{$r.id}');" {if $config.doc_uom_control}disabled {/if}>
									{foreach from=$uom key=uom_id item=u}
										<option value="{$uom_id},{$u.fraction}" {if ($r.uom_id eq $uom_id) or (!$r.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
									{/foreach}
								</select>
							</td>
				            <td>
								<input type="text" class="r form-control  min-w-100" {if $r.uom_fraction == 1 or $r.uom_id==1 or !$r.uom_id}disabled value=""{else}value="{$r.ctn}"{/if} name="ctn[{$r.id}]" 
								{if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="row_recalc({$r.id});{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" 
								/>
							</td>
				            <td>
								<input type="number" class="r form-control  min-w-100" name="pcs[{$r.id}]" value="{$r.pcs}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} 
								onChange="row_recalc({$r.id});{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" 
								/>
							</td>
				            <td><input type="text" class="r form-control  min-w-100" onchange="row_recalc({$r.id})" name="selling_price[{$r.id}]" value="{$r.selling_price}" /></td>
							<td>
								<input type="hidden" name="item_discount_amount[{$r.id}]" value="{$r.item_discount_amount}" />
								<input type="text" class="r form-control  min-w-100" onchange="row_recalc({$r.id})" name="item_discount[{$r.id}]" value="{$r.item_discount}" />
							</td>
							<td>
								<span class="items r" style="float: right;" id="span-so_amt-{$r.id}"></span>
							</td>
				        </tr>
						<tr class="tr_so_item_{$r.id}">
							<td colspan="6">
								{$LNG.REMARK}: <textarea name="remark[{$r.id}]" class="form-control  min-w-100" style="resize: none;background-color: transparent;">{$r.remark|escape}</textarea>
							</td>
						</tr>
						<tr class="tr_so_item_{$r.id}">
							<td colspan="6">
								{$LNG.STOCK_BALANCE}: <span>{$r.stock_balance}</span></br>
								Cost: <input style="width: 50%" type="text" readonly name="cost_price[{$r.id}]" class="form-control  min-w-100" value="{$r.cost_price|number_format:$config.global_cost_decimal_points:".":""}" /></br>
								{$LNG.RESERVE_QTY}: <span>{$r.reserve_qty|default:'0'}<a data-toggle="modal" href="#reserve-qty-modal"><i class="fas fa-question-circle ml-2"></i></a></span>
							</td>
						</tr>
				    {/foreach}
			    </tbody>
			</table>
			</div>
			
		</form>
		
		<div class="d-flex justify-content-end align-items-center py-2">
				<button class="btn btn-danger mr-1" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
		</div>
	{else}
		<div class="row">
			<div class="col-lg-12" style="max-height: 80vh;">
				<div class=" mg-b-20 text-center">
					<div class=" h-100">
						<img src="../../assets/img/svgicons/note_taking.svg" alt="" class="wd-35p" style="max-height: 50vh;">
						<h5 class="mg-b-10 mg-t-15 tx-18">{$LNG.ITS_EMPTY_HERE}</h5>
						<a href="#" class="text-muted">{$LNG.NO_ITEM}</a>
					</div>
				</div>
			</div>
		</div>
	{/if}
	</div>
</div>
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
{include file='footer.tpl'}
<script>
{literal}
    $('input.item_chx').click(function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').click(function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
	update_all_item_amt();
{/literal}
</script>
