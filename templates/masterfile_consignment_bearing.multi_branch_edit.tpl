{*
12/30/2010 4:34:23 PM Alex
- add able to list out multiple branch if HQ

3/15/2011 5:33:07 PM Alex
- add remarks on creating new data

6/8/2011 5:01:51 PM Alex
- replace loading icon for update button after click
7/6/2011 10:47:50 AM Alex
- fix get wrong 'all' as branch_id
7/6/2011 6:12:50 PM Alex
- recalculate while adding new row and changing price type
8/3/2011 11:20:53 AM Alex
- fix unable to save multiple data
11/30/2011 2:37:37 PM Alex
- fix auto disable input box while add new row => add_row()

11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field
*}

{include file=header.tpl}
{literal}
<style>
input[readonly] {
	border:none;
}

.tcontainer:nth-child(even){
	background-color:#eeeeee;
}

.tcontainer:nth-child(odd){
	background-color:#f9f9f9;
}

.tcontainer tr.tr_price td{
	text-align: center;
}

.red{
	color:red;
}

.blue{
	color:blue;
}

.grey{
	color:grey;
}

.pink{
	color: #ff00ff;
}

.green{
	color:green;
}

.calc{
	border:1px #aaa solid;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

//----------------------------------top form-------------------------->

var ven_autocomplete = undefined;
function reset_vendor_autocomplete(type){
	var branch_ids=[];
	$$(".branch_ids").each(function (ele){
	    if (ele.checked) branch_ids.push(ele.value);
	});

	var vendor_params="branch_id="+branch_ids+"&dept_id="+$('sel_did').value;

	if(ven_autocomplete!= undefined){
		ven_autocomplete.options.defaultParams=vendor_params;
	}
	else{
		ven_autocomplete = new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&bearing=1", {
			parameters: vendor_params,
			afterUpdateElement: function (obj, li)
			{
				var  s = li.title.split(",");
			    if(s[0]==0){
			        $('autocomplete_vendor').value = '';
			        return;
				}
				document.f_a.vendor_id.value = li.title;
			}
		});
	}
}

var bran_autocomplete = undefined;
function reset_brand_autocomplete(){
	var branch_ids=[];
	$$(".branch_ids").each(function (ele){
	    if (ele.checked) branch_ids.push(ele.value);
	});

	var brand_params="no_unbranded=1&branch_id="+branch_ids+"&dept_id="+$('sel_did').value;

	if(bran_autocomplete!= undefined){
		bran_autocomplete.options.defaultParams=brand_params;
	}
	else{
		bran_autocomplete=new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand&bearing=1", {
			parameters: brand_params,

			afterUpdateElement: function (obj, li)
			{
				var  s = li.title.split(",");
			    if(s[0]==0){
			        $('autocomplete_brand').value = '';
			        return;
				}
				document.f_a.brand_id.value = li.title;
			}
		});

	}

}

function lock_form(){
	document.f_a.disabled=true;
}

//----------------------------------bottom form-------------------------->

function recalc_sp(){

	var sp = float($('inp_sp').value);
	var all_tr = $$('#cb_data .tcontainer tr.tr_price');
	for(var i=0; i<all_tr.length; i++){
		var prof_ele = $(all_tr[i]).getElementsBySelector('input[calc="profit"]')[0];
		var prof_val = float(prof_ele.value);

	 	var disc_ele = $(all_tr[i]).getElementsBySelector('input[calc="discount"]')[0];
		var disc_val = float(disc_ele.value);

	 	var net_ele = $(all_tr[i]).getElementsBySelector('select[calc="use_net"]')[0];
		var net_chk = net_ele.value;

		var bear_ele = $(all_tr[i]).getElementsBySelector('input[calc="net_bearing"]')[0];
		
		var bear_val = float(bear_ele.value);
		var sup_bear_val = float((100-bear_val)/100);

		var cost = float(sp-sp*(prof_val/100));
		var after_discount = float(sp-sp*(disc_val/100));

		if (net_chk == 'yes'){
			var net_sp=float(after_discount);
			var net_cost=float(after_discount * (100 - bear_val)/100);
			var total= round(float(net_sp-net_cost) ,2);
		}else{
			var profit_loss = float(after_discount-cost);
			var total = round(float(profit_loss+((sp-after_discount)*sup_bear_val)),2);
		}
		
		$(all_tr[i]).getElementsBySelector('input[calc="cal"]')[0].value = total;
	}
}

function p_type_changed(obj){

	var select_value = obj.value;
	
	var body_tr = obj.parentNode.parentNode.parentNode;

	$(body_tr).getElementsBySelector('select[calc="p_type"]').each(function (ele){
		ele.value=select_value;
	
		for(var i=0; i<ele.options.length; i++){
			if(ele.options[i].selected){
				var rate= $(ele.options[i]).readAttribute('rate');
				break;
			}
		}

		var parent_tr=ele.parentNode.parentNode;

		if (rate=='') rate=0;

		var input_prof=$(parent_tr).getElementsBySelector('input[calc="profit"]')[0];
		var input_discount=$(parent_tr).getElementsBySelector('input[calc="discount"]')[0];
		var select_use_net=$(parent_tr).getElementsBySelector('select[calc="use_net"]')[0];
		var input_net_bear=$(parent_tr).getElementsBySelector('input[calc="net_bearing"]')[0];

		input_prof.value=rate;

		if (rate == 0){
			$(input_prof).disable();
			$(input_discount).disable();
			$(select_use_net).disable();
			$(input_net_bear).disable();
		}else{
			$(input_prof).enable();
			$(select_use_net).enable();
			$(input_net_bear).enable();
			change_location(select_use_net);
		}
		recalc_sp();
	});
	
}

function change_profit(){

	var all_dis = $$('tr.tr_price td input[calc="profit"]').each(function(ele, index){
		ele.readOnly = false;

	});
}

function add_row(){
	var max_index=0;

	if ($$('#cb_data .tcontainer')){
		$$('#cb_data .tcontainer').each(function(ele,index){
			if ($(ele).getAttribute('index_no') > max_index)	max_index=int($(ele).getAttribute('index_no'));
		});
	}
	
	var new_tr = $('temp_new_row').cloneNode(true);

	$(new_tr).getElementsBySelector('input','select').each(function(ele, index){
		ele.disabled = false;
	});

	$(new_tr).getElementsBySelector('input','select').each(function(ele, index){
		ele.disabled = false;
	});

	p_type_changed($(new_tr).getElementsByClassName('p_type')[0]);

	new_tr = new_tr.innerHTML.replace(/__num__/g, (max_index+1));

	new Insertion.Before($('tbody_container'), new_tr);
	recalc_sp();
}

function del_row(ele){

	var parent_row=ele.parentNode.parentNode.parentNode;

//	var is_new=$(parent_row).getElementsBySelector('input[name="is_new[]"]')[0].value;
//	var p_id= $(parent_row).getElementsBySelector('input[name="price_id[]"]')[0].value;

	if($('delete_msg'))	$('delete_msg').remove();
	$(parent_row).remove();
//	new Insertion.Bottom($('ul_errm'), "<font id='delete_msg' color='green'>Delete Success</font>");
}

function show_calculation(){

	$$('.class_test').each(function (ele,index){
		ele.show();
	});
	$('id_show').hide();

}

function load_data(v){

	if (v=='vendor'){
		$('vendor').show();
		$('brand').hide();
	}
	else if (v=='brand'){
		$('vendor').hide();
		$('brand').show();
	}
	else if (v=='none'){
		$('vendor').hide();
		$('brand').hide();
	}
}

function check_data(){

	var li_exist=$('ul_errm').getElementsBySelector('li');

	if (li_exist){
		li_exist.each(function(ele,index){
			ele.remove();
		});
	}
	
	var update_btn=$('button_id').innerHTML;

	$('button_id').update(_loading_);

	new Ajax.Request(
		phpself+'?'+Form.serialize(document.f_row),{
			parameters: {
				a: 'ajax_validate_data',
				ajax: 1
			},
			onComplete: function(e){

			    if (e.responseText != "ok"){
					new Insertion.Bottom($('ul_errm'), e.responseText);
				}else{
					document.f_row.submit();
				}
				
				$('button_id').update(update_btn);

			}
		}
	);
}

function delete_data(){
	document.f_row.a.value='delete';
	document.f_row.submit();
}

function close_data(){
	window.location = "/masterfile_consignment_bearing.php";
}

function change_location(ele){

	var row_ele = ele.parentNode.parentNode;
	var input_discount = $(row_ele).getElementsBySelector('input[calc="discount"]')[0];
	var span_bearing = $(row_ele).getElementsBySelector('span[type="bearing"]')[0];
	var span_nett_sales = $(row_ele).getElementsBySelector('span[type="nett_sales"]')[0];
	var input_bearing = $(row_ele).getElementsBySelector('span[type="bearing"] input')[0];
	var input_nett_sales = $(row_ele).getElementsBySelector('span[type="nett_sales"] input')[0];

	if (ele.value == 'yes'){
		//change to nett sales
		$(input_discount).enable();
        if (input_bearing)	span_nett_sales.appendChild(input_bearing);
	}else if (ele.value == 'amount'){
		//change to Amount
		$(input_discount).disable();
		if (input_bearing)	span_nett_sales.appendChild(input_bearing);
	}else{
		//change to bearing
		$(input_discount).enable();
		if (input_nett_sales) span_bearing.appendChild(input_nett_sales);
	}
}

function show_hide_calc(display,type){

	if (display == "show"){
		//show calc
        if (type == "bearing"){
			$("bearing_calc").style.display="";
			
		}else{
            $("nett_sales_calc").style.display="";
		}
	}else{
		//hide calc
        if (type == "bearing"){
			$("bearing_calc").style.display="none";
		}else{
            $("nett_sales_calc").style.display="none";
		}
	}
}

function add_remove_branch(){
	$('table_items_id').hide();

	$$('.show_only').each(function(obj) {
		obj.hide();
	});
	$$('.class_test','.hide_later').each(function(obj) {
		obj.show();
	});

	$$(".branch_ids").each(function (ele){
    	ele.enable();
	});

	if ($('edit_consignment_id'))
		$('edit_consignment_id').hide();
	
	$('update_consignment_id').enable();
	$('update_consignment_id').show();
}

function update_consignment(){

	document.f_a['a'].value="update_consignment";

	$(document.f_a['a']).enable();
	$(document.f_a['group_id']).enable();

	var param_str=Form.serialize(document.f_a)+"&a=ajax_check_cons_branch&group_id="+document.f_a['group_id'].value

	$('loading_id').update(_loading_);
	$('loading_id').show();
	$('update_consignment_id').hide();

	new Ajax.Request(phpself,{
		method:'post',
		parameters: param_str,
	    evalScripts: true,
		onComplete: function(m){

			$('loading_id').hide();
			$('update_consignment_id').show();

			var msg=m.responseText.trim();

			if (msg == "OK")
               	document.f_a['a'].value="edit_items";
			else if (msg == "NO"){
			    alert("Must tick at least 1 branch");
			    return;
			}else{
				if (confirm(msg+"\nAre you confirm about this?")){
				    $('f_a_id').enable();
                   	document.f_a['a'].value="update_consignment";
				}else{
                   	document.f_a['a'].value="edit_items";
				}
			}

			document.f_a.submit();
		}
	});
}

function check_all_branch(obj){
	$$("#branches_id .branch_ids").each(function (ele,index){
		ele.checked=obj.checked;
	});
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $errm.top}
	<div id=err><div class=errmsg><ul>
	{foreach from=$errm.top item=e}
	<li> {$e}
	{/foreach}
	</ul></div></div>
{/if}
<form id='f_a_id' action="{$smarty.server.PHP_SELF}" onsubmit="lock_form();" name="f_a" method=post >
<input name='a' type=hidden value='create_new' >
<input name='group_id' type=hidden value='{$detail.group_id}' >
<table>
	<tr>
		<td height="100 pixel" valign="top">
			<table border=0 cellspacing=0 cellpadding=4 style="border:1px solid #000">
			<tr>
			{if $BRANCH_CODE eq 'HQ'}
			    <td>
					<b>Branch</b>&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td id="branches_id" class="hide_later">
					<input id="all_id" type="checkbox" onclick="check_all_branch(this)"> <label for="all_id">All</label>&nbsp;&nbsp;
				    {foreach from=$branches item=branch}
						<input type="checkbox" name="branch_id[]" onclick="reset_vendor_autocomplete();reset_brand_autocomplete();" id="{$branch.code}_id" value="{$branch.id}" class="branch_ids"

						{foreach from=$detail.branch_arr key=b_id  item=req_bid }
						{if $b_id eq $branch.id} checked {/if}
						{/foreach}
						>
						<label for="{$branch.code}_id">{$branch.code}</label>&nbsp;&nbsp;
					{/foreach}
					<img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
				<td class="show_only"  style="display:none;">
				    {$detail.branch_code}
				</td>
			{/if}
			</tr>
			<tr>
				<td>
				<b>Department</b>&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td class="hide_later">
				<select name="dept_id" onchange="reset_vendor_autocomplete();reset_brand_autocomplete();" id="sel_did">
					<option value="">-- Please Select --</option>
					{foreach from=$departments item=dept}
						<option value={$dept.id} {if $smarty.request.dept_id eq $dept.id || $detail.dept_id eq $dept.id}selected {/if}>{$dept.description}</option>
					{/foreach}
				</select> <img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
				<td class="show_only"  style="display:none;">
				    {$detail.dept}
				</td>
			</tr>
			<tr id="r_type_id" class='hide_later'>
				<td>
				<b>Type</b>&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td colspan=3>
					<input type=radio name="r_type" value="vendor" onchange="load_data(this.value)" {if $smarty.request.r_type eq 'vendor' || $detail.r_type eq 'vendor'}checked {/if}>Vendor &nbsp;&nbsp;&nbsp;&nbsp;
					<input type=radio name="r_type" value="brand" onchange="load_data(this.value)" {if $smarty.request.r_type eq 'brand' || $detail.r_type eq 'brand'}checked {/if}>Brand &nbsp;&nbsp;&nbsp;&nbsp;
					<input type=radio name="r_type" value="none" onchange="load_data(this.value)" {if $smarty.request.r_type eq 'none' || $detail.r_type eq 'none'}checked {/if}>None &nbsp;&nbsp;&nbsp;&nbsp;
					<img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
			</tr>

			<tr id=vendor style="display:none;">
				<td ><b>Vendor</b></td>
				<td class="hide_later" colspan=3>
			    	<input type="hidden" name="vendor_id" size=1 value="{if $detail.vendor_id}{$detail.vendor_id}{else}{$smarty.request.vendor_id}{/if}" readonly>
					<input id="autocomplete_vendor"  name="vendor" value="{if $detail.vendor}{$detail.vendor}{else}{$smarty.request.vendor}{/if}" size=50> <img src="ui/rq.gif" align="absbottom" title="Required Field">&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="#" title="Please make sure the vendor is exist within current Branches and Department."><b>?</b></a>
					<b>Note:</b> Please make sure current vendor's trade discount table got data.
					<div id="autocomplete_vendor_choices" style="display:none;" class="autocomplete"></div>
				</td>
				<td class="show_only"  style="display:none;" colspan=3>
				    {$detail.vendor}
				</td>
			</tr>
			<tr id=brand style="display:none;">
				<td><b>Brand</b></td>
				<td class="hide_later" colspan=3>
				<input type="hidden" name="brand_id" size=1 value="{if $detail.brand_id}{$detail.brand_id}{else}{$smarty.request.brand_id}{/if}" readonly>
				<input id="autocomplete_brand" name="brand" value="{if $detail.brand}{$detail.brand}{else}{$smarty.request.brand}{/if}" size=50> <img src="ui/rq.gif" align="absbottom" title="Required Field">&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="#" title="Please make sure the brand is exist within current Branches and Department."><b>?</b></a>
					<b>Note:</b> Please make sure current brand's trade discount table got data.
				<div id="autocomplete_brand_choices" style="display:none;" class="autocomplete"></div>
				</td>
				<td class="show_only"  style="display:none;" colspan=3>
				    {$detail.brand}
				</td>
			</tr>
			</table>
		</td>
		<td valign="top">
		<div id="bearing_calc" class="calc" style="display:none;">
		    <b><u>Bearing Calculation</u></b><br>
			<b class="grey">Selling after discount</b> = Selling Price - ( Selling price x Discount(%) )<br>
			<b class="red">Cost</b> = Selling Price - ( Selling price x Profit(%) )<br>
			<b class="pink">Profit or Loss</b> = <b class="grey">Selling after discount</b> - <b class="red">Cost</b> <br>
			<b class="green">Supplier Bearing</b> = Selling price x Discount(%) x ( 100% - Our Bearing(%) )<br>
			<b class="blue">Total Nett Profit</b> = <b class="pink">Profit or Loss</b> + <b class="green">Supplier Bearing</b>
		</div>
		<div id="nett_sales_calc" class="calc" style="display:none;">
		    <b><u>Nett Sales Calculation</u></b><br>
			<b class="grey">Selling after discount</b> = Selling Price - (Selling price x Discount(%) )<br>
			<b class="red">Cost</b> = <b class="grey">Selling after discount</b> x ( 100% - Nett Sales(%) )<br>
			<b class="blue">Total Nett Profit</b> = <b class="grey">Selling after discount</b> - <b class="red">Cost</b>
		</div>

		</td>
	</tr>
</table>
<p>
    {if !$consignment_exist}
		<input id='create_new_id' type="submit" value="Create New"> &nbsp;&nbsp;&nbsp;&nbsp;
		<input onclick="if (confirm('Are you sure you want to close this?')) document.location='{$smarty.server.PHP_SELF}'" type="button" value="Close">
	{else}
		{if !$read_only}
			<input id='edit_consignment_id' type="button" onclick="add_remove_branch()" value="Edit Consignment">
			<span id="loading_id" style="display:none;"></span>
			<input id='update_consignment_id' type="button" onclick="update_consignment()" value="Update Consignment" style="display:none;">
		{/if}
	{/if}
</p>
</form>



<div id=err_mid><div class=errmsg><ul id='ul_errm'>
</ul></div></div>

<div id="table_items_id" style="display:none;">
<form id="f_row_id" name="f_row" action="{$smarty.server.PHP_SELF}" onsubmit="return false;" method=post >
<input type='hidden' name='a' value='update_items'>
<input type='hidden' name='group_id' value='{$detail.group_id}'>

<div style="visibility:none;">

</div>

<table id="cb_data" border=0 cellspacing=1 cellpadding=4 style="border:1px solid #000;">
<tr class="header" bgcolor=#ffee99>
	{if !$read_only && $sessioninfo.privilege.MST_CONTABLE_EDIT}<td width="20">&nbsp;</td>{/if}
	<th>Price Type</th>
	<th>Branch</th>
	<th>Use</th>
	<th>Profit(%)</th>
	<th>Discount(%)</th>
	<th><a onmouseover="show_hide_calc('show','bearing')" onmouseout="show_hide_calc('hide','bearing')">Our Bearing(%)</a></th>
	<th><a onmouseover="show_hide_calc('show','net_sales')" onmouseout="show_hide_calc('hide','net_sales')">Net Sales(%)</a></th>
	<th class="class_test">Calculated Nett Profit</th>
</tr>

{if $BRANCH_CODE ne 'HQ'}
<tr>
	<td colspan=8 align="center">-- No Data --</td>
</tr>
{/if}

{foreach name=prices_data from=$prices key=id item=pp}
	{include file="masterfile_consignment_bearing.multi_branch_row.tpl"}
{/foreach}

<tfoot id="tbody_container">
<tr id="add_row_id">
	<td colspan=6><input type=button onclick="add_row()" value="Add New Row"></td>
</tr>
<tr>
	<td class="class_test" colspan=6>
		<b>Test Selling Price:&nbsp;&nbsp;&nbsp;&nbsp;</b>
		<input name='input_test' onkeyup="recalc_sp();" id="inp_sp" value="{$smarty.request.input_test}" >
	</td>
</tr>
</tfoot>
</table>

<p>
<input id='id_show' name=bshow type=button onclick="show_calculation();" value="Test Calculation" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
</p>
<p>
<span id="button_id">
	<input id='id_update' name=bsubmit type=button onclick="if (confirm('Are you sure?')) check_data();" value="Update" style="font:bold 20px Arial; background-color:#1d0; color:#fff;">

{*
	<input id='id_cancel' name=bdelete type=button onclick="if (confirm('This will delete whole data, Are you sure?')) delete_data();" value="Delete" style="font:bold 20px Arial; background-color:#a00; color:#fff;"  >
*}

</span>
    <input id='id_close' name=bclose type=button onclick="close_data();" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;"  >
</p>

</form>
</div>

<table id="temp_new_row" style="display:none;">
	{include file='masterfile_consignment_bearing.multi_branch_row.tpl' is_new=1}
</table>

{include file=footer.tpl}


<div style="display:none;"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<script>

load_data("{$detail.r_type}");

{if $consignment_exist}
	$('f_a_id').disable();
	showdiv('table_items_id');
	if ($('edit_consignment_id'))
		$('edit_consignment_id').enable();

	{if $detail.r_type eq 'none'}
		$('r_type_id').hide();
	{/if}

	var checkedgif = '<img src="ui/checked.gif" align=absmiddle> ';
	var uncheckedgif = '<img src="ui/unchecked.gif" align=absmiddle> ';

	{literal}
	$$('input[type=radio]').each(function(obj) {
		if (obj.checked)
			new Insertion.After(obj,checkedgif)
		else
			new Insertion.After(obj,uncheckedgif);
		Element.hide(obj);
	});


	$$('.class_test','.hide_later').each(function(obj) {
		obj.hide();
	});
	$$('.show_only').each(function(obj) {
		obj.show();
	});

	{/literal}

{/if}

{if $read_only || !$sessioninfo.privilege.MST_CONTABLE_EDIT}
	$('add_row_id').remove();
	$('button_id').remove();
	$('f_row_id').disable();
	$('id_show').enable();
	$('id_close').enable();
	{literal}
	$$('.class_test input').each(function (ele,index){
		ele.enable();
	});
	{/literal}
{else}
	reset_vendor_autocomplete();
	reset_brand_autocomplete();
{/if}

{if $smarty.request.r_type eq 'none' || $detail.r_type eq 'none'}
    change_profit();
{/if}


</script>
