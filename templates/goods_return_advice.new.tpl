{*
REVISION HISTORY
================
4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

5/3/2011 5:03:12 PM Justin
- Modified the add item not in ARMS SKU to base on config['gra_cost_decimal_points'] while do rounding.

6/24/2011 10:18:32 AM Justin
- Added refresh session feature every 25 minutes to avoid timeout when user take long time (>30 mins) to key in item not in ARMS.

7/29/2011 4:25:32 PM Justin
- Removed the number format for qty.

4/23/2012 3:00:35 PM Alex
- add packing uom code

9/24/2012 3:37 PM Justin
- Added new field "remark".

10/19/2012 12:01 PM Justin
- Enhanced to disable general information while found user assigned items to the following GRA.

6/20/2013 10:24 AM Justin
- Enhanced to allow change header information while config "gra_allow_change_header_info".

7/4/2013 2:51 PM Justin
- Enhanced to have confirm and send to approval flow.
- Enhanced to show approval flow history.

07/19/2013 11:24 AM Justin
- Enhanced to show different info while config "gra_no_approval_flow" and hide confirm button while is turned on.

7/31/2013 11:37 AM Andy
- Enhance GRA to show approval history when under save.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

2/10/2014 5:24 PM Justin
- Bug fixed on loading wrong GRA items while access GRA that created from subbranch at HQ.

2/20/2014 4:58 PM Justin
- Enhanced to process GRA items under tmp table before it is update into the actual table.

2/26/2014 2:00 PM Justin
- Bug fixed on SKU type is unable to select while create new GRA.

2/16/2015 1:20 PM Justin
- Bug fixed on vendor ajax autocomplete will allow user to choose empty if it is disabled.

4/18/2015 4:19 PM Justin
- Enhanced the add row for item not in ARMS to use clone row instead of javscript assigned row.
- Enhanced to have GST Code, Document No and Return Type for item not in ARMS section.

5/8/2015 10:54 AM Justin
- Enhanced to allow user maintain Document Date.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

3/9/2017 11:02 AM Justin
- Enhanced to add checking for cancel button, new GRA not suppose to show it.

5/16/2017 10:01 AM Justin
- Bug fixed on description from item not in ARMS will cause items corrupted if having special character.

4/26/2018 4:22 PM Justin
- Enhanced to have foreign currency feature.

10/23/2018 1:33 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

11/16/2018 3:55 PM Justin
- Removed the readonly for Inv/DO Date when adding Items Not in ARMS SKU and added validation for the date format.

11/4/2019 10:50 AM William
- Fix bug when no config "gra_allow_change_header_info", the "sku type", "department" and "currency" selection on browser chrome can change after assign sku.

04/22/2020 11:20 AM Sheila
- Modified layout to compatible with new UI.

*}

{include file=header.tpl}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{literal}
<script>
{/literal}

var dp = '{$dp}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var id = '{$form.id|default:0}';
var gra_allow_change_header_info = int('{$config.gra_allow_change_header_info}');
var foreign_currency = '{$config.foreign_currency}';
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

{literal}

var num_row=0, curr_row=0;

function add_row(){
	if(num_row>0){
	    var result=chk_row();
	    if(result==true){
			curr_row++;
			add_new_row();
		}
	}
	else{
		add_new_row();
		num_row++;
		curr_row++;
	}
}
function remove_row(){
	num_row--;
}

function chk_row(){
	if(num_row>0){
    	var items_code = document.f_a.elements['new[code][]'];
    	var items_description = document.f_a.elements['new[description][]'];
    	var items_qty = document.f_a.elements['new[qty][]'];
    	var items_cost = document.f_a.elements['new[cost][]'];

		if(items_code != undefined){
			if(items_code.length){
				for(i=0;i<items_code.length;i++){
					if(trim(items_code[i].value)=="" || trim(items_description[i].value)=="" || trim(items_qty[i].value)=="" ){
					alert('You are not allowed to add row without complete this row details.');
					return false;
					}
				}
			}
			else{
				if(document.f_a.elements['new[code][]'].value=="" || document.f_a.elements['new[description][]'].value=="" || document.f_a.elements['new[qty][]'].value==""){
					alert('You are not allowed to add row without complete this row details.');
					return false;
				}
			}
		}
    	return true;
	}
	//else{
    //	return false;
	//}
}

function add_new_row(){
	//var new_row = $('tbl_new').insertRow(-1);
	/*new_row.height=25;
	new_row.innerHTML='<td nowrap><img src="/ui/remove16.png" title="Remove Item" height="12" class=clickable onclick="if(confirm(\'Are you sure?\')) Element.remove(this.parentNode.parentNode);remove_row();"></td><td><input name=new[code][]></td><td><input name=new[description][] onchange="uc(this);"></td><td><input name=new[qty][] onchange="this.value=float(round(this.value, '+global_qty_decimal_points+'));"></td><td><input name=new[cost][] onchange="mf(this, '+dp+');"></td><td>&nbsp;</td>';*/
	var new_tr = $('temp_gra_row').cloneNode(true).innerHTML;
	new_tr = new_tr.replace(/__id__/g, curr_row);
	new Insertion.Bottom($('tbody_item'), new_tr);
	init_calendar(curr_row);
}


function do_close()
{
	if (confirm('Discard the changes and close?'))
	{
		document.f_a.a.value='close';
		document.f_a.target = "";
		document.f_a.submit();
	}
}

function do_save(btn)
{
	if (is_new_id(document.f_a.vendor_id.value))
	{
		alert('You must select a vendor');
		document.f_a.vendor.focus();
		return false;
	} 
	
	/*
	//var result=chk_row();
	//if(result1==true){
		btn.disabled=true;
		document.f_a.submit();
	//}
	*/
	
	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	
	ajax_request('goods_return_advice.php',{
		method: 'post',
		parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
		onComplete: function(e){		
			if (e.responseText.trim() == 'OK') {								
				btn.disabled=true;				
				document.f_a.submit();				
				return;				
			}
			else {
				Element.hide('wait_popup');
				curtain(false,'curtain2');
				alert(e.responseText.trim());				
				return;
			}
		}
	});
}

function do_confirm(btn)
{
	if (is_new_id(document.f_a.vendor_id.value))
	{
		alert('You must select a vendor');
		document.f_a.vendor.focus();
		return false;
	}
	
	if(!confirm("Are you sure want to confirm?")) return false;
	
	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	
	ajax_request('goods_return_advice.php',{
		method: 'post',
		parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
		onComplete: function(e){
			if (e.responseText.trim() == 'OK') {
				btn.disabled=true;
				document.f_a['a'].value = "confirm";				
				document.f_a.submit();
				return;
			}
			else {
				Element.hide('wait_popup');
				curtain(false,'curtain2');
				alert(e.responseText.trim());
				return;
			}
		}
	});
	
}

function vendor_sel(obj,li)
{
	document.f_a.vendor_id.value = li.title;
	if (!is_new_id(document.f_a.vendor_id.value))
	{ 
		Element.show('sheet');
		refresh_tb(); 
		//refresh_items();
	}
	else
	{
		Element.hide('sheet');
	}
}

function add_item(id)
{
	 Element.remove('tbrow_'+id); 
	 new Ajax.Updater(
		'current', 'goods_return_advice.php', 
		{
			parameters: 'a=ajax_settemp_gra_item&rm_function=rm_item&gra_item_id='+id+'&gra_id='+document.f_a.id.value+'&vendor_id='+document.f_a.vendor_id.value+'&dept_id='+document.f_a.dept_id.value,
			evalScripts:true,
			onComplete: function(e){
				header_disabled();
			}
		}
	);
	
}

function rm_item(id)
{
	// construct params
	var params = {
		a: 'ajax_unsettemp_gra_item',
		add_function: 'add_item',
		gra_item_id: id,
		gra_id: document.f_a.id.value,
		vendor_id: document.f_a.vendor_id.value,
		sku_type: document.f_a.sku_type.value,
		dept_id: document.f_a.dept_id.value
	};
	
	if(foreign_currency) params['currency_code'] = document.f_a['currency_code'].value;

 	Element.remove('tbrow_'+id); 
 	new Ajax.Updater(
		'unused', 'goods_return_advice.php', 
		{
			parameters: params,
			evalScripts:true,
			onComplete: function(e){
				header_disabled();
			}
		}
	);
}

function assign_all()
{
	// construct params
	var params = {
		a: 'ajax_copyall_gra_items',
		gra_id: document.f_a.id.value,
		vendor_id: document.f_a.vendor_id.value,
		sku_type: document.f_a.sku_type.value,
		dept_id: document.f_a.dept_id.value
	};
	
	if(foreign_currency) params['currency_code'] = document.f_a['currency_code'].value;

	ajax_request(
		'goods_return_advice.php', 
		{
			parameters: params,
			onComplete: refresh_tb
		}
	);
}

function unassign_all()
{
	ajax_request(
		'goods_return_advice.php', 
		{
			parameters: 'a=ajax_removeall_gra_items&gra_id='+document.f_a.id.value,
			onComplete: refresh_tb
		}
	);
}

function refresh_items(get_valid_items)
{
	if (int(document.f_a.vendor_id.value)==0)
	{
		Element.hide('current');
		return;
	}
	
	if(get_valid_items == undefined) get_valid_items = 0;
	
	Element.show('current');
	$('current').innerHTML = "<img src=/ui/clock.gif align=absmiddle> Loading...";
	new Ajax.Updater(
		'current', 'goods_return_advice.php', 
		{
			parameters: 'a=ajax_showtemp_gra_items&rm_function=rm_item&gra_id='+document.f_a.id.value+'&branch_id='+document.f_a.branch_id.value+'&vendor_id='+document.f_a.vendor_id.value+'&sku_type='+document.f_a.sku_type.value+'&get_valid_items='+get_valid_items,
			evalScripts:true
		}
	);
}

function refresh_tb(){
	if (is_new_id(document.f_a.vendor_id.value))
	{
		Element.hide('unused');
		return;
	}
	
	// construct params
	var params = {
		a: 'ajax_unused_gra_items',
		add_function: 'add_item',
		gra_id: document.f_a.id.value,
		branch_id: document.f_a.branch_id.value,
		vendor_id: document.f_a.vendor_id.value,
		sku_type: document.f_a.sku_type.value,
		dept_id: document.f_a.dept_id.value
	};
	
	if(foreign_currency) params['currency_code'] = document.f_a['currency_code'].value;
	
	//alert(document.f_a.dept_id.value);
	Element.show('unused');
	$('unused').innerHTML = "<img src=/ui/clock.gif align=absmiddle> Loading...";
	new Ajax.Updater(
		'unused', 'goods_return_advice.php', 
		{
			parameters: params,
			evalScripts: true
		}
	);
	
	refresh_items();
}

function cancel_popup(){
    curtain(false);
	Element.hide('cancel_popup');
}

function do_cancel(){
	center_div('cancel_popup');
	curtain(true);
	Element.show('cancel_popup');
}

function do_submit_cancel(){
	Element.hide('cancel_popup');
	curtain(false);
	if($('cancel_comment').value){
		document.f_a.comment.value=$('cancel_comment').value;
		document.f_a.a.value='cancel';
		document.f_a.submit();
	}
	else{
		alert('Please provide any comment!!!');
		return;
	}
}

function header_disabled(){	
	var assigned_item_list = $$('.assigned_items');
	var sku_type = document.f_a['sku_type'];
	if(foreign_currency){
		var currency_code = document.f_a['currency_code'];
		if(assigned_item_list.length == 0){
			if($('span_currency_code') != null){
				if(document.f_a['sku_type'].value == "CONSIGN") {
					document.f_a['currency_code'].hide();
					$('span_currency_code').textContent= currency_code.options[currency_code.selectedIndex].text;
				}else{
					document.f_a['currency_code'].show();
					$('span_currency_code').textContent= "";
				}
				document.f_a['sku_type'].show()
				$('span_sku_type').textContent = "";
			}
		}else{
			if($('span_currency_code') != null){
				$('span_currency_code').textContent= currency_code.options[currency_code.selectedIndex].text;
				document.f_a['currency_code'].hide();
			
				if(document.f_a['sku_type'].value != "CONSIGN" || assigned_item_list.length > 0) {
					document.f_a['sku_type'].hide();
					$('span_sku_type').textContent = sku_type.options[sku_type.selectedIndex].text;
				}
			}
		}
	}
	
	if(!is_new_id(id)) return;
	
	if(assigned_item_list.length == 0 || gra_allow_change_header_info){
		document.f_a['autocomplete_vendor'].disabled = false;
		if(!foreign_currency){
			$('span_sku_type').textContent = "";
			document.f_a['sku_type'].show();
		}
		$('span_dept_id').textContent = "";
		document.f_a['dept_id'].show();
	}else{
		var dept_id = document.f_a['dept_id'];
		document.f_a['autocomplete_vendor'].disabled = true;
		document.f_a['sku_type'].hide();
		document.f_a['dept_id'].hide();
		$('span_sku_type').textContent = sku_type.options[sku_type.selectedIndex].text;
		$('span_dept_id').textContent  = dept_id.options[dept_id.selectedIndex].text;
	}
}

function update_selected_gst(obj){
	document.f_a["gst_id"].value = "";
	document.f_a["gst_code"].value = "";
	document.f_a["gst_rate"].value = "";
	document.f_a["gst_indicator"].value = "";

	if(obj.selectedIndex >= 0){
		// got select
		var opt = obj.options[obj.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		var gst_indicator = $(opt).readAttribute("gst_indicator");

		document.f_a["gst_id"].value = gst_id;
		document.f_a["gst_code"].value = gst_code;
		document.f_a["gst_rate"].value = gst_rate;
		document.f_a["gst_indicator"].value = gst_indicator;
	}
}

function gst_changed(vid){
	if(is_under_gst == 0) return;

	var gst_id = $('gst_id_'+vid).value;
	document.f_a['gst_id'].value = gst_id;
	update_selected_gst(document.f_a['gst_sel']);
}

function init_calendar(n){
	if(n != undefined){
		Calendar.setup({
			inputField     :    "isi_doc_date_"+n,
			ifFormat       :    "%Y-%m-%d",
			button         :    "dd_added_"+n,
			align          :    "Bl",
			singleClick    :    true
		});
	}
}

function toggle_foreign_currency(){
	var currency_code = document.f_a['currency_code'];
	if(document.f_a['sku_type'].value == "CONSIGN"){
		document.f_a['currency_code'].value = "";
		document.f_a['currency_code'].hide();
		$('span_currency_code').textContent= currency_code.options[currency_code.selectedIndex].text;
	}else{
		$('span_currency_code').textContent= "";
		document.f_a['currency_code'].show();
	}
}

function doc_date_changed(obj){
	if(obj.value.trim() != "") upper_lower_limit(obj);
}

</script>
<style>
#tbl_new input[size="1"]
{
	width:20px;
}
#tbl_new input
{
	padding:0; margin: 0; 
	font:normal 10px Arial;
}
#tbody_item tr:nth-child(odd)
{
	background-color:#eeeeee;
}
</style>

{/literal}
<h1>GRA {if !is_new_id($form.id)}(ID#{$form.id}){else}(New){/if}</h1>
<h3>Status: Saved GRA</h3>

{include file="approval_history.tpl"}

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>


<div id=cancel_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:5px;width:280;height:120">
<p align=center>
Please Provide Cancel Comment
<p align=left>
<table>
<tr>
<td>Comment</td> <td>:</td> <td><input id=cancel_comment size=30></td></tr>
<tr><td colspan=3></td></tr>
<tr align=center>
<td colspan=3><input type=button value="OK" onclick="do_submit_cancel();">&nbsp;&nbsp;&nbsp;<input type=button value="Cancel" onclick="cancel_popup();"></td>
</tr>
</table>
</div>

<div id=wait_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
<p align=center>
Please wait..
<br /><br />
<img src="ui/clock.gif" border="0" />
</p>
</div>

<table style="display:none;">
	<tbody id="temp_gra_row" class="temp_gra_row">
		<tr height="12">
			<td nowrap>
				<img src="/ui/remove16.png" title="Remove Item" height="12" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode);">
			</td>
			<td><input type="text" name="new[code][]" value=""></td>
			<td><input type="text" name="new[description][]" size="50" value=""></td>
			<td><input type="text" name="new[doc_no][]" value="" onchange="uc(this);"></td>
			<td align="center" nowrap>
				<input type="text" name="new[doc_date][]" id="isi_doc_date___id__" value="" size="10" onchange="doc_date_changed(this);">
				<img align="absmiddle" src="ui/calendar.gif" id="dd_added___id__" style="cursor: pointer;" title="Select Document Date">
			</td>
			<td><input type="text" name="new[qty][]" value=""></td>
			{if $is_under_gst}
				<td class="gst_field">
					<select name="new[gst_id][]">
						{foreach from=$gst_list item=gst}
							<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" gst_indicator="{$gst.indicator_receipt}" value="{$gst.id}">{$gst.code} -  ({$gst.rate|default:'0'}%)</option>
						{/foreach}
					</select>
				</td>
			{/if}
			<td {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}><input type="text" name="new[cost][]" value="" onchange="mf(this, '{$config.global_cost_decimal_points}');"></td>
			<td><input type="text" name="new[reason][]" value="" onchange="uc(this);"></td>
			<td width=16><img src="/ui/pixel.gif" width=16></td>
		</tr>
	</tbody>
</table>

<form name="f_a" method=post>
<input type=hidden name=comment value=''>
<input type=hidden name=a value="save">
<input type=hidden name=id value="{$form.id|default:0}">
<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
<input type=hidden name=approved value="{$form.approved}">
<input type=hidden name=is_under_gst value="{$is_under_gst}">
<input type=hidden name=return_timestamp value="{$form.return_timestamp}">

{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table border=0 cellspacing=0 cellpadding=4>
<tr>
	<td><b>Vendor</b></td>
	<td colspan=3>
    	{if !is_new_id($form.id)}
    	<input name=vendor_id type=hidden value="{$form.vendor_id}">
    	<input name=vendor type=hidden value="{$form.vendor}">	
    	{$form.vendor}
    	{else}
    	<input name="vendor_id" size=1 value="{$form.vendor_id}" readonly>
		<input id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
		<div id="autocomplete_vendor_choices" class="autocomplete"></div>
		<img src=ui/rq.gif align=absbottom title="Required Field">
		{/if}
	</td>
</tr>
<tr>
	<td><b>SKU Type</b></td>
	<td>
		{if !is_new_id($form.id) && !$config.gra_allow_change_header_info}
		<input type=hidden name=sku_type value="{$form.sku_type}">		
		{$form.sku_type}
		{else}
		<select name=sku_type onchange="{if $config.foreign_currency}toggle_foreign_currency();{/if} refresh_tb();">
			{foreach from=$sku_type_list key=st_code item=st}
				<option value="{$st_code}" {if $form.sku_type eq $st_code}selected{/if}>{$st.description}</option>
			{/foreach}
		</select>
		<span id="span_sku_type"></span>
		{/if}
	</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td>
		{if !is_new_id($form.id) && !$config.gra_allow_change_header_info}
		<input type=hidden name=dept_id value="{$form.dept_id}">
		{$form.dept_code}
		{else}
		<select name=dept_id onchange="refresh_tb()">
		{section name=i loop=$dept}
		<option value="{$dept[i].id}" {if $form.dept_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
		{/section}
		</select>
		<span id="span_dept_id"></span>
		{/if}
	</td>
</tr>

{if $config.foreign_currency}
	<tr>
		<td><b>Currency</b></td>
		<td>
			{if !is_new_id($form.id) && !$config.gra_allow_change_header_info}
				{$form.currency_code|default:'Base Currency'}
				<input type="hidden" name="currency_code" value="{$form.currency_code}" />
			{else}
				<select name="currency_code" onchange="refresh_tb();">
					<option value="" {if !$form.currency_code}selected{/if}>Base Currency</option>
					<optgroup label="Foreign Currency">
						{foreach from=$foreignCurrencyCodeList item=code}
							<option value="{$code}" {if $form.currency_code eq $code}selected{/if}>{$code}</option>
						{/foreach}
					</optgroup>
				</select>
				<span id="span_currency_code"></span>
			{/if}
		</td>
	</tr>
{/if}

<!--tr>
	<td><b>Returned Date</b></td>
	<td>
		<input name="returned" value="{$smarty.now|date_format:"%d/%m/%Y"}" size=10 readonly><br>
		dd/mm/yyyy
	</td>
</tr>
<tr>
	<td><b>Vehicle No.</b></td>
	<td><input name="transport" onchange="ucz(this)" value="{$form.transport}" size=10 maxlength=10></td>
	<td><b>Driver Name</b></td>
	<td><input name="misc_info[name]" onchange="ucz(this)" value="{$form.misc_info.name}" size=50></td>
	<td><b>IC No.</b></td>
	<td><input name="misc_info[nric]" onchange="ucz(this)" value="{$form.misc_info.nric}" size=20></td>
</tr-->
</table>
</div>

<div id=sheet {if is_new_id($form.id) && $form.vendor_id==0}style="display:none"{/if}>
<br>
{if $errm.sheet}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.sheet item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

<table border=0 cellspacing=1 cellpadding=2 width=78% class=small style="border:1px solid #000" id=tbl_new>
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Code</th>
	<th>Description</th>
	<th>Inv / DO No.</th>
	<th>Inv / DO Date</th>
	<th>Qty (pcs)</th>
	{if $is_under_gst}
		<th class="gst_field">GST Code</th>
	{/if}
	<th {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Cost</th>
	<th>Return Type</th>
	<th width=16><img src="/ui/pixel.gif" width=16></th>
</tr>

<h4>Items Not in ARMS SKU</h4>
- Rows without complete detail (code and description) will not be saved.<br><br>
<tbody id="tbody_item">
{if $new}
{foreach from=$new key=dept item="row" name=j}
{assign var=n value=$smarty.foreach.j.iteration-1}
	<tr>
		<td nowrap>
			<img src="/ui/remove16.png" title="Remove Item" height="12" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode);">
		</td>
		<td>{$new.$n.code}<input type=hidden name=new[code][] value="{$new.$n.code|escape:'html'}"></td>
		<td>{$new.$n.description}<input type=hidden name=new[description][] value="{$new.$n.description|escape:'html'}"></td>
		<td>{$new.$n.doc_no}<input type=hidden name=new[doc_no][] value="{$new.$n.doc_no}"></td>
		<td align="center">{$new.$n.doc_date}<input type=hidden name=new[doc_date][] value="{$new.$n.doc_date}"></td>
		<td align="center">{$new.$n.qty|qty_nf}<input type=hidden name=new[qty][] value="{$new.$n.qty}"></td>
		{if $is_under_gst}
			<td align="center" class="gst_field">
				{$new.$n.gst_code} ({$new.$n.gst_rate|default:'0'}%)
				<input type="hidden" name="new[gst_id][]" value="{$new.$n.gst_id}" />
				<input type="hidden" name="new[gst_code][]" value="{$new.$n.gst_code}" />
				<input type="hidden" name="new[gst_rate][]" value="{$new.$n.gst_rate}" />
			</td>
		{/if}
		<td align="center" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>{$new.$n.cost|default:0|number_format:$dp}<input type="hidden" name=new[cost][] value="{$new.$n.cost|default:0}"></td>
		<td>{$new.$n.reason}<input type=hidden name=new[reason][] value="{$new.$n.reason}"></td>
		<td width=16><img src="/ui/pixel.gif" width=16></td>
	</tr>
{/foreach}
</tbody>
{/if}
</table>
<br>
<input type=button class="btn btn-primary" value="Add New Product" onclick="add_row()">
<br><br>

<div id=ulist>
<table width=100%>
<tr>
	<td width=50%>
	<h4>Unassigned SKU</h4>
	</td>
	<td rowspan=2><div style="font-size:3em">&#8658;</div></td>
	<td width=50%>
	<h4>Selected SKU for the GRA</h4>
	</td>
<tr>
	<td width=50% valign=top>
	<a href="javascript:void(refresh_tb())"><img src="ui/refresh.png" align=absmiddle border=0> Refresh</a>
	<a href="javascript:void(assign_all())"><img src="ui/table_add.png" align=absmiddle border=0> Assign All</a>
	<br>
	<div id=unused></div>
	<td width=50% valign=top>
	<a href="javascript:void(refresh_items(1))"><img src="ui/refresh.png" align=absmiddle border=0> Refresh</a>
	{if $form.status == 0}
	<a href="javascript:void(unassign_all())" onclick="return confirm('Click OK to confirm remove all.');"><img src="ui/table_delete.png" align=absmiddle border=0> Remove All</a>
	{/if}
	<br>
	<div id=current></div>
</tr>
</table>
</div>

<br>
<h4>Remark</h4>
<textarea style="border:1px solid #000;width:100%;height:100px;" name=remark2>{$form.remark2|escape}
</textarea>

<br>
<p align=center>
<input class="btn btn-success" type=button value="Save" onclick="do_save(this)">
{if !$config.gra_no_approval_flow}
	<input class="btn btn-primary" type=button value="Confirm" onclick="do_confirm(this)">
{/if}
<input class="btn btn-error" type=button value="Close" onclick="do_close()">
{if !is_new_id($form.id)}
<input class="btn btn-error" type=button value="Cancel" onclick="do_cancel(this)">
{/if}
</p>
</div>
</form>

{include file=footer.tpl}

<script>
{if is_new_id($form.id)}
	{literal}
	new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: vendor_sel });
	{/literal}
{/if}
{if $form.vendor_id}
	refresh_tb(); 
{/if}
//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to key in item not in ARMS.
{literal}
new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
{/literal}
</script>
