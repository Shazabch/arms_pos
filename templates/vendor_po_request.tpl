{*
07.05.2008 12:56:23
- Add 'History' button and 'show_history' function to view all ticket created by the login user.

3/14/2013 3:04 PM Justin
- Enhanced to have new feature of deactivate access code.

4/18/2013 3:55 PM Fithri
- add to show info of how long is the valid period (from config) of vendor access code
- show code expiry date after generating vendor access code.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

6/22/2017 15:00 Qiu Ying
- Enhanced to select multiple department in vendor PO access

06/24/2020 02:49 PM Sheila
- Updated button css
*}
{include file=header.tpl}
{literal}

<style>
#tbl_history th,td{
	vertical-align:top;
}
</style>

<script>
function load_vendor_sku()
{
	if(int(document.f_a.vendor_id.value)<=0){
		alert('Please select a vendor.');
		return;
	}
	
	var is_checked = false;
	$$("#td_department .department").each(function (ele,index){
		if (ele.checked){
			is_checked = true;
		}
	});
	
	if(!is_checked){
		alert('Please select at least one department.');
		return;
	}
	
	$('sku_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	document.getElementById('sku_table').innerHTML
	new Ajax.Updater(
		"sku_table",
		"vendor_po_request.php",
		{
			method: 'post',
			parameters: Form.serialize(document.f_a) + '&a=ajax_load_sku',
			evalScripts: true
		}
	)
}

function show_history()
{
	if(document.f_a.vendor_id.value){
		$('sku_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
		document.getElementById('sku_table').innerHTML
		new Ajax.Updater(
			"sku_table",
			"vendor_po_request.php",
			{
				method: 'post',
				parameters: Form.serialize(document.f_a) + '&a=ajax_show_history',
				evalScripts: true
			}
		)
	}
	else{
		alert('Please select a vendor.');
		return;
	}
}

function do_generate_ac()
{
	if(int(document.f_a.vendor_id.value)<=0){
		alert('Please select a vendor.');
		return;
	}
	
	var is_checked = false;
	$$("#td_department .department").each(function (ele,index){
		if (ele.checked){
			is_checked = true;
		}
	});
	
	if(!is_checked){
		alert('Please select at least one department.');
		return;
	}
	
	document.f_a.a.value='generate_ac';
	document.f_a.submit();
}

function get_po_owners()
{
	$('po_owner').innerHTML = _loading_;
	new Ajax.Updater(
		'po_owner',
		'vendor_po_request.php?'+ Form.serialize(document.f_a) + '&a=get_po_owners',
		{ evalScripts: true }
	);
}

function deactivate_ac(ac, bid, obj){
	if(obj.src == "ui/clock.gif") return false;
	
	obj.src = "ui/clock.gif";
	
	if(!confirm("Are you sure want to deactivate "+ac+"?")){
		obj.src = "ui/cancel.png";
		return false;
	}
	
	new Ajax.Request('vendor_po_request.php', {
		method:'post',
		parameters: '?a=deactivate_ac&ac='+ac+'&bid='+bid,
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onComplete: function(m){
			alert(m.responseText);
			show_history();
			obj.src = "ui/cancel.png";
		}
	});
	
}

function toggle_all_check(obj, class_name){
	$$("#td_department ." + class_name).each(function (ele,index){
		ele.checked=obj.checked;
	});
}

function toggle_view_more(ac){
	if ($("div_view_more_"+ac).style.display == "none"){
		$("view_type_"+ac).innerHTML = "show less";
		$("div_view_more_"+ac).style.display = "";
	}
	else{
		$("view_type_"+ac).innerHTML = "show more";
		$("div_view_more_"+ac).style.display = "none";
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.t eq 'generated'}
<div id=dgen>
- Successful generate access code for {$smarty.request.vendor} <br>
<h2>
Access code : <font color=blue>{$smarty.request.code}</font>
&nbsp;&nbsp;&nbsp;
Expiry date : <font color=blue>{$smarty.request.expire}</font>
</h2>
<button onclick="$('fgen').style.display='';$('dgen').remove();">Generate another code</button>
</div>
{/if}

<div id=fgen {if $smarty.request.t eq 'generated'}style="display:none"{/if}>
<form name=f_a method=post>
<input type=hidden name=a>
<table border=0 cellspacing=0 cellpadding=4>
{if $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100px"><b>To Branch</b></td>
	<td>
		<select name="branch_id" onchange="get_po_owners();">
		{foreach item=curr_Branch from=$branches}
			<option value={$curr_Branch.id} {if $curr_branch.id==$branch_id}selected{/if}>{$curr_Branch.code}</option>
		{/foreach}
		</select> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
	</td>
</tr>
{else}
<input type=hidden name="branch_id" value={$sessioninfo.branch_id}>
{/if}

<tr>
	<td><b>To Vendor</b></td>
	<td>
    	<input name="vendor_id" size=1 value="{$form.vendor_id}" readonly>
		<input id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
		<div id="autocomplete_vendor_choices" class="autocomplete"></div>
		<img src=ui/rq.gif align=absbottom title="Required Field">
		<input class="btn btn-primary" type=button value="History" onclick="show_history()">
	</td>
</tr>
<tr>
	<td valign="top"><b>Department</b></td>
	<td id="td_department">
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
			<input type="checkbox" id="dept_all_id" onclick="toggle_all_check(this,'department')" onchange="get_po_owners();" value="1" />
			<label for="dept_all_id"><b>ALL DEPARTMENTS</b></label><br /><br />
			{assign var=tmp_dept value=""}
			{foreach item=items from=$dept}
				{if $tmp_dept neq $items.root_id}
					<b>{$items.root}</b><br />
					<input type="checkbox" id="dept_{$items.root_id}" value="1" class="department" onclick="toggle_all_check(this, 'root_{$items.root_id}')" onchange="get_po_owners();" /> 
					<label for="dept_{$items.root_id}">All</label><br />
					{assign var=tmp_dept value=$items.root_id}
				{/if}
				<input class="department root_{$items.root_id}" name="department_ids[{$items.id}]" type="checkbox" onchange="get_po_owners();" value="1"/>{$items.description}<br />
			{/foreach}
		</div>&nbsp;<span><img src="ui/rq.gif" align="absbottom" title="Required Field" /></span>
	</td>
</tr>
<tr id=po_owner>
</tr>

</table>
</form>

<p>* Generated Vendor Access Code will expire after <b>{$config.po_vendor_ticket_expiry}</b> days</p>

<p align=center>
<input class="btn btn-primary" type=button value="Load SKU" onclick="load_vendor_sku()"> 
<input class="btn btn-primary" type=button value="Generate Vendor Access Code" onclick="do_generate_ac()">
</p>
</div>

<div id="sku_table">
</div>

{include file=footer.tpl}
{literal}
<script>
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; get_po_owners(); }});
</script>
{/literal}
