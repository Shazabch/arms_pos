{*
8/13/2012 5:40 PM Andy
- Add link to PO and PO document when PO is success generated.

8/14/2012 10:45 AM Justin
- Bug fixed on still showing branch select option even login into subbranch.
- Bug fixed to hide the PO option during at subbranch.
- Enhanced to have multiplier feature while qty type is multiply.

8/15/2012 5:48 PM Justin
- Added new ability to have multiplier for FOC qty follow by the lowest multiplier of required rule from item.

8/16/2012 9:50 AM Justin
- Enhanced to disable user to check/uncheck FOC item.

8/16/2012 5:46 PM Justin
- Bug fixed on unable to show item list when having normal item but without any foc item.

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.

9/27/2012 10:52 AM Justin
- Enhanced to include new info "Allow items from all departments".

10/23/2012 11:21 AM Andy
- Fix after generate PO, and enter new settings then click "Clike here to continue", on top of screen still showing generated PO # but with #00000.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

01/06/2016 11:45 PM DingRen
- remove PO option

7/5/2016 4:17 PM Andy
- Enhanced to able to create from tmp_purchase_agreement_info.

9/8/2016 4:23 PM Andy
- Enhanced to have remark for purchase agreement.
- Enhanced to bring purchase agreement remark to po when the item was selected.

2/27/2017 9:58 AM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 

06/24/2020 02:49 PM Sheila
- Updated button css
*}

{if !$form.approval_screen}{include file='header.tpl'}{/if}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
input[disabled] {
  color:black;
  background:rgb(255,238,153);
}
input[readonly] {
  color:black;
  background:rgb(255,238,153);
}
select[disabled] {
  color:black;
  background:rgb(255,238,153);
}
span.span_latest_cost{
	color:blue;
	font-size: 80%;
}
.rule_remark_inactive{
	color: grey;
}
.rule_remark_active{
	color: blue;
	font-weight: bold;
	background-color: yellow;
}
{/literal}
</style>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var allow_edit = '{$allow_edit}';
var form_label = '{$form.label}';
var is_approval_screen = '{$form.approval_screen}';
var global_cost_decimal_points = int('{$config.global_cost_decimal_points}');
var currency_symbol = '{$config.arms_currency.symbol}';
{literal}

var PURCHASE_AGREEMENT_MODULE = {
	f: undefined,
	bid: 0,
	pa_id: 0,
	initialize: function(){
		this.f = document.f_a;
		this.bid = this.f['branch_id'].value;
		this.pa_id = this.f['id'].value;
		
		var THIS = this;
		
		// autocomplete for vendor
		new Ajax.Autocompleter("inp_autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&block=po", {
			paramName: 'vendor',
			afterUpdateElement: function (obj, li){
				var  s = li.title.split(",");
			    if(s[0]==0){
			        $('inp_autocomplete_vendor').value = '';
			        return;
				}
				THIS.f['vendor_id'].value = li.title;
				THIS.header_changed();
			}
		});
		
		// event when user click to continue
		$('btn_refresh').observe('click', function(){
			THIS.refresh_page();
		});
		
		// initial calendar
		Calendar.setup({
			    inputField     :    "inp_date",     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_date",  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true,
				onUpdate	   :	THIS.header_changed
		});
		
		// event when user change to vendor
		$('vendor_id').observe('onchange', function(){
			THIS.header_changed();
		});
		
		// check which item checked
		this.check_all_items_checked();
	},
	// function when user "click to continue"
	refresh_page: function(){
		// validate header
		if(!this.check_header())    return false;
		
		this.f['a'].value = 'refresh';
		this.f.submit();
	},
	// function to check form header
	check_header: function(){
		if(!this.f)	return false;
		
		if(!check_required_field(this.f))	return false;
		
		return true;
	},
	// function when user click save or confirm
	submit_form: function(act, skip_checking){
		if(!act)	return false;
		
		this.f['a'].value = act;
		
		if(!skip_checking){
			if(!this.check_header())    return false;
		}

		// check if user got tick a item
		var required_rule_list = $$('#pa_content_list input.item_checkbox');
		var item_checked = false;
		for(j=0; j<required_rule_list.length; j++){
			if(required_rule_list[j].checked == true) item_checked = true;
		}
		if(!item_checked){
			alert("Please choose a item before save this as PO!");
			return;
		}
		
		// ask last confirmation
		if(!confirm('Are you sure?'))   return false;
		
		// enable the form before submit
		Form.enable(this.f);
		this.f.submit();
	},
	// function when user change qty
	qty_changed: function(id, item_id, bid, ele){
		var type = this.f['qty_type[item]['+item_id+']['+bid+']'].value;
		var qty1 = this.f['qty1[item]['+item_id+']['+bid+']'].value;
		var qty2 = this.f['qty2[item]['+item_id+']['+bid+']'].value;

		if(type == "range"){
			if(float(qty1) > ele.value || float(qty2) < ele.value){
				alert("Qty keyed in is out of range from "+qty1+" to "+qty2);
				ele.value = qty1;
			}
		}else if (type == "multiply"){
			var ele_val;
			if(ele.value != 0) ele_val = ele.value;
			else ele_val = 1;
			ele.value = float(qty1) * Math.ceil(float(ele_val/qty1));
			
			var rule_num = this.f["rule_num[item]["+item_id+"]["+bid+"]"].value;
			
			// get all input which got this rule
			var inp_ref_rule_list_list = $$('#pa_foc_item_list input.inp_ref_rule_list-'+id+'-'+bid+'-'+rule_num);
			
			for(var i=0; i<inp_ref_rule_list_list.length; i++){	// loop for each input
				var all_rule_checked = true;

				// get <tr> ids
				var parent_id = this.get_parent_id(inp_ref_rule_list_list[i]);
				var ids = parent_id.split("-");
				var item_id = ids[1];
				var item_bid = ids[2];
				
				var required_rule_list = $$('#'+parent_id+' input.inp_ref_rule_item_list-'+item_id+'-'+item_bid);
				
				var new_multiplier = 0;
				for(j=0; j<required_rule_list.length; j++){
					var tmp_rule = required_rule_list[j].value;
					
					// check whether this rule got checked
					var tmp_chx = $$("input.chx_rule-"+id+"-"+bid+"-"+tmp_rule);
					if(tmp_chx[0].checked){
						var tmp_ids = tmp_chx[0].id.split("-");
						var tmp_item_id = tmp_ids[1];
						var tmp_item_bid = tmp_ids[2];
						
						var curr_qty = this.f["qty[item]["+tmp_item_id+"]["+tmp_item_bid+"]"].value;
						var curr_multiplier = this.f["qty1[item]["+tmp_item_id+"]["+tmp_item_bid+"]"].value;
						var curr_multiplier = Math.ceil(float(curr_qty/curr_multiplier));
						if(new_multiplier == 0) new_multiplier = curr_multiplier;
						if(curr_multiplier < new_multiplier) new_multiplier = curr_multiplier;
					}else{
						all_rule_checked = false;
					}
				}

				if(all_rule_checked){
					var old_foc_qty = this.f['old_qty[foc_item]['+item_id+']['+item_bid+']'].value;
					this.f['qty[foc_item]['+item_id+']['+item_bid+']'].value = old_foc_qty * new_multiplier;
				}
			}
		}
	},
	// function when user change selling price or purchase price
	item_price_changed: function(ele){
		var ids = ele.id.split("-");
		var type = ids[0];
		var item_type = ids[1];
		var pai_id = ids[2];
		var pai_bid = ids[3];
		
		if(type=='purchase_price'){
			mf(ele, global_cost_decimal_points, 1);
		}else{
			mfz(ele);
		}		
		
		if(item_type=='foc_item'){
		
		}else{
			// recalculate gp
			this.recalculate_item_gp(pai_id, pai_bid);
		}	
	},
	// function to recalculate gp
	recalculate_item_gp: function(pai_id, pai_bid){
		if(!pai_id)	return false;
		
		var sp = float($('suggest_selling_price-item-'+pai_id+"-"+pai_bid).value);
		var cost = float($('purchase_price-item-'+pai_id+"-"+pai_bid).value);
		
		var gp_per = 0;
		if(sp){
			// get discount element
			var inp = $('inp_discount-item-'+pai_id+"-"+pai_bid);
					
			// get discount format
			var discount_format = inp.value.trim();
			
			// get discount amt
			discount_amt = float(round(get_discount_amt(cost, discount_format),2));
			if(discount_amt){
				cost -= discount_amt
			}
	
			var gp = sp-cost;
			gp_per = gp/sp*100;
		}
		$('span_gp_per-item-'+pai_id+"-"+pai_bid).update(round(gp_per,2));
	},
	// function when user change discount
	item_discount_changed: function(pai_id, pai_bid){
		if(!pai_id)	return false;

		// get input element
		var inp = $('inp_discount-item-'+pai_id);
				
		// get discount format
		var discount_format = inp.value.trim();
	
		// check discount pattern
		discount_format = validate_discount_format(discount_format);
		
		// update back the value
		inp.value = discount_format;
		
		// recalculate row
		this.recalculate_item_gp(pai_id, pai_bid);
	},
	// function to construct and return row related info
	get_row_info_by_pai_id: function(pai_id){
		var ret = {};
		
		// rule num
		ret['rule_num'] = {};
		ret['rule_num']['ele'] = $('inp_rule_num-'+pai_id);
		ret['rule_num']['val'] = ret['rule_num']['ele'].value;
		
		// arms code
		ret['sku_item_code'] = {};
		ret['sku_item_code']['ele'] = $('td_sku_item_code-item-'+pai_id);
		ret['sku_item_code']['val'] = ret['sku_item_code']['ele'].innerHTML;
		
		// mcode
		ret['mcode'] = {};
		ret['mcode']['ele'] = $('td_mcode-item-'+pai_id);
		ret['mcode']['val'] = ret['mcode']['ele'].innerHTML;
		
		// description
		ret['desc'] = {};
		ret['desc']['ele'] = $('td_desc-item-'+pai_id);
		ret['desc']['val'] = ret['desc']['ele'].innerHTML;
		
		return ret;
	},
	
	header_changed: function(){
		$('div_refresh').style.display='';
		if ($('pa_content_list') != undefined){
			$('pa_content_list').style.display='none';
			$('btn_list').style.display='none';
		}
	},
	get_parent_id: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_foc_item_row')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;
		
		return parent_ele.id;
	},
	// function to get pa item id by element
	get_row_pa_item_row_info: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_item_row')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;
		
		var ret = {};
		ret['branch_id'] = parent_ele.id.split('-')[1];
		ret['pa_item_id'] = parent_ele.id.split('-')[2];
		return ret;
	},
	check_foc_items: function(id, item_id, bid){
		var items = $('pa_content_list').getElementsByClassName("inp_rule_num-"+item_id+"-"+bid);
		var rule_num = this.f["rule_num[item]["+item_id+"]["+bid+"]"].value;
		
		
		// get all input which got this rule
		var inp_ref_rule_list_list = $$('#pa_foc_item_list input.inp_ref_rule_list-'+id+'-'+bid+'-'+rule_num);
		
		for(var i=0; i<inp_ref_rule_list_list.length; i++){	// loop for each input
			var all_rule_checked = true;
			
			// get <tr> ids
			var parent_id = this.get_parent_id(inp_ref_rule_list_list[i]);
			var ids = parent_id.split("-");
			var row_item_id = ids[1];
			var item_bid = ids[2];
			
			//alert('#'+parent_id+' input.inp_ref_rule_item_list-'+item_id+'-'+item_bid)
			// get all required rule input for this foc item
			var required_rule_list = $$('#'+parent_id+' input.inp_ref_rule_item_list-'+row_item_id+'-'+item_bid);
			
			var new_multiplier = 0;
			for(j=0; j<required_rule_list.length; j++){
				var tmp_rule = required_rule_list[j].value;
				
				// check whether this rule got checked
				var tmp_chx = $$("input.chx_rule-"+id+"-"+bid+"-"+tmp_rule);
				if(tmp_chx[0].checked){
					var tmp_ids = tmp_chx[0].id.split("-");
					var tmp_item_id = tmp_ids[1];
					var tmp_item_bid = tmp_ids[2];
					
					var curr_qty = this.f["qty[item]["+tmp_item_id+"]["+tmp_item_bid+"]"].value;
					var curr_multiplier = this.f["qty1[item]["+tmp_item_id+"]["+tmp_item_bid+"]"].value;
					var curr_multiplier = Math.ceil(float(curr_qty/curr_multiplier));
					if(new_multiplier == 0) new_multiplier = curr_multiplier;
					if(curr_multiplier < new_multiplier) new_multiplier = curr_multiplier;
				}else{
					all_rule_checked = false;
				}
				
			}
			
			if(all_rule_checked){
				$('img_foc_item_check-'+row_item_id+'-'+item_bid).style.display = "";
				this.f['item_check[foc_item]['+row_item_id+']['+item_bid+']'].checked = true;
				var old_foc_qty = this.f['old_qty[foc_item]['+row_item_id+']['+item_bid+']'].value;
				this.f['qty[foc_item]['+row_item_id+']['+item_bid+']'].value = old_foc_qty * new_multiplier;
			}else{
				$('img_foc_item_check-'+row_item_id+'-'+item_bid).style.display = "none";
				this.f['item_check[foc_item]['+row_item_id+']['+item_bid+']'].checked = false;
			}
		}
		
		// turn on/off remark
		this.check_remark(item_id, bid);
	},
	// check all items which is checked
	check_all_items_checked: function(){
		var tr_item_row_list = $$('#tbody_pa_item_list tr.tr_item_row');
		for(var i=0,len=tr_item_row_list.length; i<len; i++){
			var pa_item_info = this.get_row_pa_item_row_info(tr_item_row_list[i]);
			// skip if not ticked
			if(!this.f['item_check[item]['+pa_item_info['pa_item_id']+']['+pa_item_info['branch_id']+']'].checked)	continue;
			
			//alert(pa_item_info['branch_id']+' , '+pa_item_info['pa_item_id']);
			var purchase_agreement_id = this.f['pa_id[item]['+pa_item_info['pa_item_id']+']['+pa_item_info['branch_id']+']'].value;
			//alert(purchase_agreement_id);
			
			// check foc item
			this.check_foc_items(purchase_agreement_id, pa_item_info['pa_item_id'], pa_item_info['branch_id']);
		}
	},
	// function to show which remark is turn on
	check_remark: function(item_id, bid){
		var tr_item_row = $('tr_item_row-'+bid+'-'+item_id);
		var pa_id = this.f['pa_id[item]['+item_id+']['+bid+']'].value;
		var rule_group_alp = $(tr_item_row).readAttribute('rule_group_alp');
		var item_checked = this.f['item_check[item]['+item_id+']['+bid+']'].checked;
		var tr_rule_remark = $('tr_rule_remark-'+rule_group_alp);
		if(!tr_rule_remark)	return;	// tr not found
		var show_remark = false;
		if(item_checked){	// checked, must show remark
			show_remark = true;
		}else{	// un-checked, check others
			$$('#tbody_pa_item_list input.item_checkbox_rule_group-'+rule_group_alp).each(function(inp){
				if(inp.checked){
					show_remark = true;
				}
			});
		}
		
		if(show_remark){	// show remark
			if(!$(tr_rule_remark).hasClassName('rule_remark_active')){
				$(tr_rule_remark).removeClassName('rule_remark_inactive');
				$(tr_rule_remark).addClassName('rule_remark_active');
			}
			this.f['remarks_on['+bid+']['+pa_id+']'].checked = true;
		}else{	// hide remark
			if(!$(tr_rule_remark).hasClassName('rule_remark_inactive')){
				$(tr_rule_remark).removeClassName('rule_remark_active');
				$(tr_rule_remark).addClassName('rule_remark_inactive');
			}
			this.f['remarks_on['+bid+']['+pa_id+']'].checked = false;;
		}
	}
}

function discount_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => discount of 10 percent\n";
	msg += "10  => discount of "+currency_symbol+"10\n";
	msg += "10%+10 => discount 10%, follow by "+currency_symbol+"10\n";
	msg += "10+10% => discount "+currency_symbol+"10, then discount 10%\n";

	alert(msg);
}

function do_save(){
	PURCHASE_AGREEMENT_MODULE.submit_form('save');
}

{/literal}
</script>

<h1>{$PAGE_TITLE} - New</h1>

{if $smarty.request.type eq 'save'}
	<img src="/ui/approved.png" align="absmiddle"> PO Saved as <a href="po.php?a=open&branch_id={$sessioninfo.branch_id}&id={$smarty.request.id}">ID#{$smarty.request.id|string_format:"%05d"|default:'-'}</a>, <a href="po.php">Go to Purchase Order</a>
	<br />
	<br />
{/if}

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
	<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if $form.approval_screen}
	<form name="f_c" method="post">
		<input type="hidden" name="a" value="save_approval" />
		<input type="hidden" name="approve_comment" value="" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="approvals" value="{$form.approvals}" />
		<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	</form>
{/if}

<form name="f_a" method="post" onSubmit="return false;" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="save" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	<input type="hidden" name="reason" />
	<input type="hidden" name="tmp_purchase_agreement_info_id" value="{$tmp_pa_data.id}" />

	<div class="stdframe" style="background:#fff">
		<h4>General Information</h4>
		<table border="0" cellspacing="0" cellpadding="4">
			<!-- Dept -->
			<tr>
				<td><b>Department</b></td>
				<td>
					<select name="dept_id" onchange="PURCHASE_AGREEMENT_MODULE.header_changed();" class="required" title="Department">
						{foreach from=$dept key=r item=d}
							<option value="{$d.id}" {if $form.dept_id eq $d.id}selected{/if}>{$d.description}</option>
						{/foreach}
					</select>
					<img src="ui/rq.gif" align="absbottom" title="Required Field" />
				</td>
			</tr>
			
			<!-- Vendor -->
			<tr>
				<td><b>Vendor</b></td>
				<td>
					<input type="text" name="vendor_id" id="vendor_id" size="1" value="{$form.vendor_id}" readonly class="required" title="Vendor" />
					<input id="inp_autocomplete_vendor" name="vendor_desc" value="{$form.vendor_desc}" size="50" />
					<img src="ui/rq.gif" align="absbottom" title="Required Field" />
					
					{if !$form.approval_screen}
						<div id="autocomplete_vendor_choices" class="autocomplete" style="display:none;"></div>	
					{/if}
				</td>
			</tr>
			
			<!-- IBT -->
			{if $config.po_enable_ibt}
				<tr>
					<td><b>IBT</b></td>
					<td><input type="checkbox" name="is_ibt" value="1" {if $form.is_ibt}checked {/if} /></td>
				</tr>
			{/if}
			
			<!-- Date -->
			<tr>
			    <td><b>Date</b></td>
			    <td>
			        <input type="text" name="date" id="inp_date" size="12" value="{$form.date|default:$smarty.now|date_format:'%Y-%m-%d'}" class="required" title="Date" onchange="PURCHASE_AGREEMENT_MODULE.header_changed();" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" />
					<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			    </td>
			</tr>
			
			<!-- partial devivery -->
			<tr>
				<td><b>Partial Delivery</b></td>
				<td>
					<input name="partial_delivery" type="checkbox" {if $form.partial_delivery}checked{/if} id="pd"> <label for="pd">Allowed</label>
				</td>
			</tr>
			
			<!-- PO Option -->
			{*if $BRANCH_CODE eq 'HQ'}
				<tr>
					<td valign="top" {if $config.po_allow_hq_purchase}rowspan="2"{/if}><b>PO Option</b></td>
					<td><input type="radio" name="po_option" onchange="PURCHASE_AGREEMENT_MODULE.header_changed();" value="2" {if $form.po_option eq 2 or !$form.po_option}checked{/if}>
					HQ purchase on behalf of Branches 
					<font color=#990000><b>(Branch Payment)</b></font>
					</td>
				</tr>
				{if $config.po_allow_hq_purchase}
					<tr>
						<td><input type="radio" name="po_option" onchange="PURCHASE_AGREEMENT_MODULE.header_changed();" value="3" {if $form.po_option eq 3}checked{/if}>
						HQ purchase <font color="#990000"><b>(HQ Payment)</b></font>
						</td>
					</tr>
				{/if}
			{else}
				<input type="hidden" name="po_option" value="2" />
			{/if*}
			
			<!-- Deliver to Branch -->
			<tr>
				<td valign="top"><b>Delivery Branch</b></td>
				<td>
					{if $BRANCH_CODE eq 'HQ'}
						<select name="po_branch_id" onchange="PURCHASE_AGREEMENT_MODULE.header_changed();">
						{foreach from=$branch key=r item=b}
							<option value="{$b.id}" {if $form.po_branch_id eq $b.id}selected{/if}>{$b.code}</option>
						{/foreach}
						</select> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
					{else}
						{$BRANCH_CODE}
						<input type="hidden" name="po_branch_id" value="{$sessioninfo.branch_id}">
					{/if}
				</td>
			</tr>

			<!-- All departments -->
			<tr>
				<td valign="top"><b>Allow items from all departments</b></td>
				<td>
					<input type="checkbox" name="all_depts" value="1" {if $form.all_depts}checked{/if} onchange="PURCHASE_AGREEMENT_MODULE.header_changed();" />
				</td>
			</tr>
			
			{* Remarks *}			
			<tr>
				<td valign="top"><b>Remarks</b></td>
				<td>
					{if $items.item}
						<table>
							<tr>
								<th>Rule</th>
							</tr>
							{assign var=last_rule_num value=""}
							{foreach from=$items.item item=pa_item}
								{if $last_rule_num ne $pa_item.rule_group_alp}
									<tr valign="top" class="rule_remark_inactive" id="tr_rule_remark-{$pa_item.rule_group_alp}">
										<td>{$pa_item.rule_group_alp}
											<span style="display:none;">
												<input type="checkbox" value="1" name="remarks_on[{$pa_item.branch_id}][{$pa_item.purchase_agreement_id}]" />
												<textarea name="remarks[{$pa_item.branch_id}][{$pa_item.purchase_agreement_id}]">{$pa_item.remark}</textarea>
											</span>
										</td>
										<td>{$pa_item.remark|nl2br}</td>
									</tr>
								{/if}
								{assign var=last_rule_num value=$pa_item.rule_group_alp}
							{/foreach}
						</table>
					{else}
						-
					{/if}
				</td>
			</tr>
			
		</table>
		
		<div id="div_refresh" style="{if ($smarty.request.a eq 'refresh' || $tmp_pa_data) && ($items.item || $items.foc_item)} display:none; {/if} padding-top:10px">
			<input id="btn_refresh" type="button" class="btn btn-primary" value="click here to continue" />
		</div>
	</div>
	
	<br />
	
	<ul>
		<li>Seasonal items will replace all normal item.</li>
	</ul>
	
	<div id="pa_content_list" {if !$items.item && !$items.foc_item}style="display:none;"{/if}>
		<h3>Item List</h3>
		<div id="pa_item_list">
			{include file="po.po_agreement.item_list.tpl"}
		</div>
		
		<h3>FOC Item List</h3>
		<div id="pa_foc_item_list">
			{include file="po.po_agreement.foc_item_list.tpl"}
		</div>
	</div>
	
	{if !$items.item && !$items.foc_item && $smarty.request.a eq 'refresh'}
	<div align="center">- No data -</div>
	{/if}
</form>

<script type="text/javascript">
	PURCHASE_AGREEMENT_MODULE.initialize();
</script>
	
<p align="center" id="btn_list" {if ($smarty.request.a ne 'refresh' && !$tmp_pa_data) || (!$items.item && !$items.foc_item)}style="display:none;"{/if}>
	<input type="button" value="Generate PO" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_save();" />
	<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/po.php'" />
</p>

{include file="footer.tpl"}
