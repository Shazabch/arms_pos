{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

22/10/2019 9:35 AM William
- Enhanced to add Delivery date and Cancellation date.
- Fixed bug PO Date will always show date today.
- Added new date format checking for date input.

25/10/2019 1:12 PM William
 - Add date checking to delivery date when config "po_agreement_cancellation_days " is not active.

 11/04/2020 10:22 AM Sheila
- Fixed title, table and form css

11/05/2020 11:56 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}
<script>
var po_type = '{$po_type}';
var po_branch_id = "";
var po_agreement_cancellation_days = int('{$config.po_agreement_cancellation_days}');
{if $form.id}
	var branch_id = '{$form.branch_id}';
	var po_branch_id = '{$form.po_branch_id}';
{else}
	var branch_id = '{$sessioninfo.branch_id}';
{/if}
{literal}
function search_vendor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_vendor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['vendor_id'].length;
		desc_length = desc.length;

		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			var vd_desc = $(document.f_a['vendor_id'].options[i]).attr('vd_desc').toLowerCase();
			if(desc_length == 1){
				if(vd_desc.indexOf(desc)==0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}else{
				if(vd_desc.indexOf(desc)>=0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}
		}
		
		if(opt == undefined){
			for(var i=1; i<opt_length; i++){    // loop options, skip the first
				var vd_desc = $(document.f_a['vendor_id'].options[i]).text().toLowerCase();
				var vd_desc_split = vd_desc.split(" - ", 2);
				if(desc_length == 1){
					if(vd_desc_split[0].indexOf(desc)==0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}else{
					if(vd_desc_split[0].indexOf(desc)>=0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}
			}
		}
		
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not foundnot found in Vendor list');
		}
	}
}

function search_dept(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_dept_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['dept_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['dept_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['dept_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in Department list');
		}
	}
}

function submit_form(){
	if(document.f_a['po_date'].value==''){
		alert('Please enter PO Date.');
		return false;
	}

	if(document.f_a['vendor_id'].value==''){
		alert('Please select Vendor Id.');
		return false;
	}
	
	if(document.f_a['dept_id'].value==''){
		alert('Please select Department Id.');
		return false;
	}
	
	if(branch_id == 1){
		var bid_checked = 0;
		var delivery_date_err = 0;
		var cancel_date_err = 0;
		var bid = document.f_a['deliver_to[]'];
		bid.forEach(function(element) {
		  if(element.checked == true){
			var element_branch_id = element.value;
			bid_checked +=1;
			//checking multi branch deliver date
			if(document.f_a['delivery_date['+element_branch_id+']'] != undefined && document.f_a['delivery_date['+element_branch_id+']'].value==''){  
				delivery_date_err +=1;
			}
			//checking multi branch cancellation date
			if(document.f_a['cancel_date['+element_branch_id+']'] != undefined && document.f_a['cancel_date['+element_branch_id+']'].value==''){  //checking cancellation date
				cancel_date_err +=1;
			}
		  }
		});
		if(bid_checked < 1){
			alert('Please select Branch.');
			return false;
		}
		//checking branch deliver date
		if(delivery_date_err > 0){
			alert('Please enter the delivery date.');
			return false;
		}
		//checking cancellation date
		if(cancel_date_err > 0){
			alert('Please enter the cancellation date');
			return false;
		}
	}else{
		if(document.f_a['delivery_date'].value == ''){
			alert('Please enter the delivery date.');
			return false;
		}
		if(document.f_a['cancel_date'].value == ''){
			alert('Please enter the cancellation date');
			return false;
		}
	}
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

// to display delivery date and cancellation date.
function active_btn(bid, obj){
	if ($("db_"+bid) != undefined && $("date_list_"+bid) != undefined){
		var inputs = document.getElementById("date_list_"+bid).getElementsByTagName('input');
		if(obj.checked == true){
			for(var i=0;i < inputs.length;i++){
				inputs[i].disabled = false;
			}
			$('#date_list_'+bid).get(0).style.display = "table-row";
		}else{
			for(var i=0;i < inputs.length;i++){
				inputs[i].disabled = true;
			}
			$('#date_list_'+bid).get(0).style.display = "none";
		}
	}
}

// auto update cancellation date when fill delivery date
function date_updated(bid,obj){
	if(po_agreement_cancellation_days){
		var deliver_date = obj.value;
		if(deliver_date != ''){
			var date_format_checking= date_format_check(obj);
			if(date_format_checking != null){
				var exp_delivery_date = deliver_date.split("-");
				var tmp_cancel_date = new Date(exp_delivery_date[0], exp_delivery_date[1]-1, exp_delivery_date[2]);
				var addon_times = int(po_agreement_cancellation_days) * 3600000 * 24;
				tmp_cancel_date.setTime(tmp_cancel_date.getTime()+addon_times);
				var new_cancel_date = tmp_cancel_date.getFullYear()+'-'+('0'+(tmp_cancel_date.getMonth()+1)).slice(-2)+'-'+('0'+tmp_cancel_date.getDate()).slice(-2);
				if(document.f_a['cancel_date['+bid+']'] != undefined) document.f_a['cancel_date['+bid+']'].value = new_cancel_date;
				else document.f_a['cancel_date'].value = new_cancel_date;
			}
		}	
	}
}

// checking date format YYYY-MM-DD 
function date_format_check(obj){
	var date_format = /^(19[5-9][0-9]|20[0-4][0-9]|2999)[-](0?[1-9]|1[0-2])[-](0?[1-9]|[12][0-9]|3[01])$/;
	if(obj != undefined) var date_val = obj.value;
	if(date_val){
		var result = date_val.match(date_format);
		if(result == null){
			obj.value = "";
			alert("The date format incorrect.");
		}
		return result;
	}
}
{/literal}
</script>
<h1>
New Purchase Order
&nbsp;
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=po">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>

{if $form.id&&$form.branch_id}{include file='po.top_include.tpl'}<br /><br />{/if}
<div class="stdframe" style="background:#fff">
<h2>Setting - 
{if $form.po_no}
		(PO/{$form.po_no})
{else}
	{if $form.id}
		(PO#{$form.id})
	{else}
		New PO
	{/if}
{/if}
</h2>
{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}


{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}

<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<table cellspacing="0" cellpadding="4" border="0" width="100%">
    <tr>
	    <th align="left">Vendor</th>
	    <td>
	        <select name="vendor_id" {if $disable_sett} disabled{/if}>
	            <option value="">-- Please Select --</option>
	            {foreach from=$vendor key=did item=r}
	                <option value="{$did}" {if $form.vendor_id eq $did}selected {/if} vd_desc="{$r.description|escape:'html'}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
	{if !$disable_sett}
	<tr>
	    <th align="left">Search Vendor</th>
	    <td><input type="text" class="txt-width" name="search_vendor_desc" id="search_vendor_desc" onKeyUp="search_vendor(event);" />
	</tr>
	{/if}
	<tr>
	    <th align="left">Department</th>
	    <td>
	        <select name="dept_id" {if $disable_sett} disabled{/if}>
	            <option value="">-- Please Select --</option>
	            {foreach from=$dept item=d}
	                <option value="{$d.id}" {if $form.dept_id eq $d.id}selected {/if} >{$d.description}</option>
	            {/foreach}
	        </select>
		</td>
	</tr>
	{if !$disable_sett}
	<tr>
	    <th align="left">Search Dept</th>
	    <td><input type="text" name="search_dept_desc" onKeyUp="search_dept(event);" /></td>
	</tr>
	{/if}
	<tr>
	    <th align="left">PO Date</th>
	    <td>
			<input type="text" name="po_date" onchange="date_format_check(this)" value="{$form.po_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="10"  {if $disable_sett} disabled{/if} /> <span class="small" > (YYYY-MM-DD) </span>
		</td>
	</tr>

	<tr>
		<th valign="top" align="left">Deliver Branch</th>
		<td>
			{if ($sessioninfo.branch_id eq 1 && (!$form.id && !$form.deliver_to)) || ($sessioninfo.branch_id eq 1 && ($form.id && $form.deliver_to)) || ($sessioninfo.branch_id eq 1 && $err) }
				<table border="0">
				{foreach from=$branches item=branch}
					<tr>
						<td valign="top"><input type="checkbox" onClick="active_btn({$branch.id},this)" id="db_{$branch.id}" name="deliver_to[]" value="{$branch.id}" class="branch" {if is_array($form.deliver_to) and in_array($branch.id,$form.deliver_to)}checked{/if} {if $disable_sett} disabled{/if}></td>
						<td valign="center">{$branch.code}</td>
					</tr>
					<tr id="date_list_{$branch.id}" {if !is_array($form.deliver_to) or !in_array($branch.id,$form.deliver_to)}style="display:none"{/if}>
						<td colspan="2">
							<table>
								<tr>
									<th align="left">Delivery Date</th>
									<td><input type="text" name="delivery_date[{$branch.id}]" {if in_array($branch.id,$form.deliver_to) && !$err}disabled{/if} onchange="date_updated({$branch.id},this);{if !$config.po_agreement_cancellation_days}date_format_check(this){/if}" value="{$form.delivery_date[$branch.id]}" size="10" {if !$disable_sett} placeholder="YYYY-MM-DD"{/if} /></td>
								</tr>
								<tr>
									<th align="left">Cancellation Date</th>
									<td><input type="text" name="cancel_date[{$branch.id}]" {if in_array($branch.id,$form.deliver_to) && !$err}disabled{/if} onchange="date_format_check(this)" value="{$form.cancel_date[$branch.id]}" size="10" {if !$disable_sett} placeholder="YYYY-MM-DD"{/if} /></td>
								</tr>
							</table>
						</td>
					</tr>
				{/foreach}
				</table>
			{else}
				{if $disable_sett}
					{$form.po_branch_code}
				{else}
					{$BRANCH_CODE}
				{/if}
			{/if}
		</td>
	</tr>
	{if ($sessioninfo.branch_id eq 1 && $form.id && !$form.deliver_to) || ($sessioninfo.branch_id neq 1 && !$form.id && !$form.deliver_to) || ($sessioninfo.branch_id neq 1 && $form.id && !$form.deliver_to) }
	<tr>
		<th align="left">Delivery Date</th>
		<td>
			<input type="text" name="delivery_date" value="{$form.delivery_date}" onchange="date_updated('',this);{if !$config.po_agreement_cancellation_days}date_format_check(this){/if}" size="10" {if $disable_sett} disabled{/if} /> (YYYY-MM-DD)
		</td>
	</tr>
	<tr>
		<th align="left">Cancellation Date</th>
		<td>
			<input type="text" name="cancel_date" value="{$form.cancel_date}" onchange="date_format_check(this)" size="10" {if $disable_sett} disabled{/if} /> (YYYY-MM-DD)
		</td>
	</tr>
	{/if}
</table>
{if !$disable_sett}
<p align="center">
	<input name="submit_btn" type="button" value="Save" onClick="submit_form();" />
</p>
{/if}
</form>
</div>
{include file='footer.tpl'}
