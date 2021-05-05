{*
6/27/2013 4:28 PM Andy
- Add can choose whether group by same price or not.

6/28/2013 10:51 AM Andy
- Add to show Counter Name and Receipt No.
- Change Gross Sales to Nett Sales.
- Enhance the printing format.

5/16/2014 11:31 AM Justin
- Enhanced to allow user to either select brand, vendor or department filter.
- Enhanced to have new filter "Location".

5/29/2014 2:44 PM Fithri
- change filter setting to allow select all Vendor / Brand / Department
- include option to select by Brand Group in Brand filter
- change report name to Category Sales Report by SKU

5/29/2014 5:33 PM Fithri
- add SKU Type filter

6/4/2014 2:16 PM Fithri
- bugfix: Trade Discount Type filter will only affect SKU type consignment
*}

{if $smarty.request.submit_type eq 'print'}
	{include file='header.print.tpl'}
	<body onload="window.print()">
	<table width="100%" border="0">
		<tr>
			<td width="10%"><img src="{get_logo_url}" height="80" hspace="5" vspace="5"></td>
			<td width="80%" nowrap>
				<h5>{$print_branch_info.code} - {$print_branch_info.description}</h5>
			</td>
			<td width="150" nowrap>
				Printed By: {$sessioninfo.fullname}<br />
				Printed Date: {$smarty.now|date_format:$config.dat_format}<br />
				Branch: {$print_branch_info.code}
			</td>
		</tr>
	</table>
{else}
	{include file="header.tpl"}
{/if}

{if !$no_header_footer && !$smarty.request.submit_type}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
.tr_cat_total{
	background-color: #cfc;
}
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
}
</style>
{/literal}

<script type="text/javascript">



{literal}
var LOADING = '<img src="/ui/clock.gif" />';

var REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
	        inputField     :    "inp_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_from",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	
	    Calendar.setup({
	        inputField     :    "inp_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_to",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	},
	// function when user toggle all discount type
	toggle_all_discount_type: function(){
		var checked = $('chx_toggle_all_discount_type').checked;
		
		$(this.f).getElementsBySelector("input.chx_trade_discount_type").each(function(ele){
			ele.checked = checked;
		});
	},
	toggle_all_locations: function(){
		var checked = $('chx_toggle_all_locations').checked;
		
		$(this.f).getElementsBySelector("input.chx_location").each(function(ele){
			ele.checked = checked;
		});
	},
	// function to check form before submit
	check_form: function(){
		// branch
		if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch.');
				return false;
			}
		}
		
		// brand
		/*if(!this.f['brand_id'].value){
			alert('Please select brand.');
			return false;
		}*/
		
		// vendor
		/*if(!this.f['vendor_id'].value){
			alert('Please select vendor.');
			return false;
		}
		
		// department
		if(!this.f['dept_id'].value){
			alert('Please select department.');
			return false;
		}*/
		
		/*
		if(!this.f['vendor_id'].value && !this.f['dept_id'].value){
			alert("Please select vendor or department.");
			return false;
		}
		*/
		
		if (this.f.sku_type.value != 'OUTRIGHT') {
			// trade discount type
			var chx_trade_discount_type_list = $(this.f).getElementsBySelector("input.chx_trade_discount_type");
			var got_checked = false;
			for(var i=0,len=chx_trade_discount_type_list.length; i<len; i++){
				if(chx_trade_discount_type_list[i].checked){
					got_checked = true;
					break;
				}
			}
			if(!got_checked){
				alert('Please select at least one Trade Discount Type.');
				return false;
			}
		}
		
		// location
		var chx_location_list = $(this.f).getElementsBySelector("input.chx_location");
		got_checked = false;
		for(var i=0,len=chx_location_list.length; i<len; i++){
			if(chx_location_list[i].checked){
				got_checked = true;
				break;
			}
		}
		if(!got_checked){
			alert('Please select at least one Location.');
			return false;
		}
			
		return true;
	},
	// function when user click to show report or export
	submit_form: function(t){
		if(!this.check_form())	return false;
		
		this.f['submit_type'].value = '';
		this.f.target = '';
		
		if(t == 'excel'){
			this.f['submit_type'].value = 'excel';
		}else if(t == 'print'){
			this.f['submit_type'].value = 'print';
			this.f.target = '_blank';
		}
		
		this.f.submit();
	},
	// function when user click receipt no
	items_details: function (branch_id,counter_id,id,date){
		
		curtain(true);
	    center_div($('div_item_details'));
	
	    $('div_item_details').show()
		$('div_item_content').update(LOADING+' Please wait...');
	
		new Ajax.Updater('div_item_content','counter_collection.php',
		{
		    method: 'post',
		    parameters:{
				a: 'item_details',
				counter_id: counter_id,
				branch_id: branch_id,
				pos_id: id,
				date: date
			}
		});
	},
	sku_type_changed: function() {
		if (this.f.sku_type.value == 'OUTRIGHT') $('cb_trade_disc').hide();
		else $('cb_trade_disc').show();
	},
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}
{/literal}
</script>
{/if}

{if !$no_header_footer}
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:700px;height:450px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>
{/if}

{if $smarty.request.submit_type eq 'print'}
	<h5>{$PAGE_TITLE}</h5>
{else}
	<h1>{$PAGE_TITLE}</h1>
{/if}

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer && !$smarty.request.submit_type}

<form name="f_a" onSubmit="return false;" method="post" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<span>
			<b>Branch: </b>	
			<select name="branch_id">
				<option value="">-- Please Select --</option>
				{foreach from=$branch_list key=bid item=b}
					<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
				{/foreach}
			</select>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<span>
		<b>Brand: </b>
		<select name="brand_id">
			<option value="">-- All --</option>
			<option value="0" {if $smarty.request.brand_id eq '0'}selected{/if}>UN-BRANDED</option>
			{if $brand_groups}
			<optgroup label="Brand Group">
			{foreach from=$brand_groups key=bgk item=bgv}
				<option value="{$bgk}" {if $bgk eq $smarty.request.brand_id}selected {/if}>{$bgv}</option>
			{/foreach}
			</optgroup>
			{/if}
			{if $brand_list}
			<optgroup label="Brand">
			{foreach from=$brand_list key=brand_id item=r}
				<option value="{$brand_id}" {if $brand_id eq $smarty.request.brand_id}selected {/if}>{$r.description}</option>
			{/foreach}
			</optgroup>
			{/if}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<span>
		<b>Vendor: </b>
		<select name="vendor_id">
			<option value="">-- All --</option>
			{foreach from=$vendor_list key=vendor_id item=r}
				<option value="{$vendor_id}" {if $vendor_id eq $smarty.request.vendor_id}selected {/if}>{$r.description}</option>
			{/foreach}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<p>
		<span>
			<b>Department: </b>
			<select name="dept_id">
				<option value="">-- All --</option>
				{foreach from=$dept_list key=dept_id item=r}
					<option value="{$dept_id}" {if $dept_id eq $smarty.request.dept_id}selected {/if}>{$r.description}</option>
				{/foreach}
			</select>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<span>
			<b>SKU Type: </b>
			<select name="sku_type" onchange="REPORT.sku_type_changed();">
				<option value="">-- All --</option>
				{foreach from=$sku_type_list key=sku_type_code item=r}
					<option value="{$sku_type_code}" {if $sku_type_code eq $smarty.request.sku_type}selected {/if}>{$r.description|upper}</option>
				{/foreach}
			</select>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
	</p>
	
	<span id="cb_trade_disc" {if $smarty.request.sku_type eq 'OUTRIGHT'}style="display:none;"{/if}><p>
		<b>Trade Discount Type: </b>
		<input type="checkbox" onChange="REPORT.toggle_all_discount_type();" id="chx_toggle_all_discount_type" /> All
		&nbsp;&nbsp;
		{foreach from=$discount_type_list item=r}
			<span>
				<input type="checkbox" class="chx_trade_discount_type" name="trade_discount_type[{$r.code}]" value="{$r.code}" {if is_array($smarty.request.trade_discount_type) and in_array($r.code, $smarty.request.trade_discount_type)}checked {/if} />
				{$r.code}
			</span>&nbsp;&nbsp;
		{/foreach}
	</p></span>
	
	<p>
		<b>Location: </b>
		<input type="checkbox" onChange="REPORT.toggle_all_locations();" id="chx_toggle_all_locations" /> All
		&nbsp;&nbsp;
		{foreach from=$location_list item=r name=loc}
				<span>
					<input type="checkbox" class="chx_location" name="location[{$r.location}]" value="{$r.location}" {if is_array($smarty.request.location) and in_array($r.location, $smarty.request.location)}checked {/if} />
					{$r.location}
				</span>&nbsp;&nbsp;
		{/foreach}
	</p>
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
	
	<input type="checkbox" name="group_same_price" value="1" {if $smarty.request.group_same_price}checked {/if}/> Group by same price &nbsp;&nbsp;&nbsp;&nbsp;
	
	<p>
		<input type="button" value='Show Report' onClick="REPORT.submit_form();" /> &nbsp;&nbsp;
		<button onClick="REPORT.submit_form('print');"><img src="/ui/icons/printer.png" align="absmiddle"> Print</button>
	
		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button  onClick="REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
		{/if}
	</p>
	
	
</form>
<script type="text/javascript">REPORT.initialize();</script>
{/if}

{if $smarty.request.load_report && !$err}
	
	{if !$data}
		<br />
		* No Data *
	{else}
		{if $smarty.request.submit_type eq 'print'}
			<h5>Brand: {$print_info.brand} &nbsp;&nbsp;&nbsp;&nbsp; Vendor: {$print_info.vendor}</h5>
			<h5>Department: {$print_info.dept} &nbsp;&nbsp;&nbsp;&nbsp; SKU Type: {$print_info.sku_type} &nbsp;&nbsp;&nbsp;&nbsp;  Trade Discount Type: {$print_info.trade_discount}</h5>
			<h5>{$print_info.date}</h5>
		{else}
			<br />
			<h2>{$report_title}</h2>
		{/if}
		
		<table width="100%" class="report_table tb" cellspacing="0" cellpadding="2">
			<thead>
				<tr class="header">
					<th>ARMS Code</th>
					<th>MCode</th>
					<th>{$config.link_code_name}</th>
					<th>Description</th>
					
					{if !$smarty.request.group_same_price}
						<th>Counter</th>
						<th>Receipt</th>
					{/if}
					
					<th>Qty</th>
					<th>Gross Sales</th>
					<th>Total Disc</th>
					<th>Nett Sales</th>
				</tr>
			</thead>
			
			{foreach from=$data.by_cat key=dt item=dt_dept_list}
				{foreach from=$dt_dept_list key=tmp_dept_id item=tmp_dept_info}
					{foreach from=$tmp_dept_info.cat_list key=tmp_cat_id item=tmp_discount_type_list}
						{foreach from=$tmp_discount_type_list key=tmp_discount_type item=sales_info}
							{foreach from=$sales_info.item_list item=r}
								{assign var=sid value=$r.sid}
								
								<tr>
									<td>{$data.si_info.$sid.sku_item_code}</td>
									<td>{$data.si_info.$sid.mcode|default:'-'}</td>
									<td>{$data.si_info.$sid.link_code|default:'-'}</td>
									<td>{$data.si_info.$sid.description|default:'-'}</td>
									
									{if !$smarty.request.group_same_price}
										<td align="center">{$r.counter_name|default:'-'}</td>
										<td align="center">
											{if !$smarty.request.submit_type}
												<a href="javascript:void(REPORT.items_details('{$r.branch_id}','{$r.counter_id}','{$r.pos_id}','{$dt}'));">
													{$r.receipt_no|default:'-'}
												</a>
											{else}
												{$r.receipt_no|default:'-'}
											{/if}
										</td>
									{/if}
									
									{* Qty *}
									<td align="right">{$r.qty|qty_nf}</td>
									
									{* Amt *}
									<td align="right">{$r.amt|number_format:2}</td>
									
									{* Cost / Disc *}
									<td align="right">{$r.total_cost|number_format:3}</td>
									
									{* Nett Amt *}
									<td align="right">{$r.nett_amt|number_format:2}</td>
								</tr>
							{/foreach}
							
							<tr class="tr_cat_total">
								<td colspan="3"><b>{if $data.cat_info.$tmp_cat_id.code}{$data.cat_info.$tmp_cat_id.code} - {/if}{$data.cat_info.$tmp_cat_id.description} ({$tmp_discount_type})</b></td>
								{assign var=cols value=1}
								{if !$smarty.request.group_same_price}
									{assign var=cols value=$cols+2}
								{/if}
								<td align="right" colspan="{$cols}"><b>Cat Total:</b> </td>
								
								{* Qty *}
								<td align="right">{$sales_info.qty|qty_nf}</td>
								
								{* Amt *}
								<td align="right">{$sales_info.amt|number_format:2}</td>
								
								{* Cost / Disc *}
								<td align="right">{$sales_info.total_cost|number_format:2}</td>
								
								{* Nett Amt *}
								<td align="right">{$sales_info.nett_amt|number_format:2}</td>
							</tr>
						{/foreach}
						
						<tr class="tr_dept_total header">
							<td colspan="3"><b>{if $dept_list.$tmp_dept_id.code}{$dept_list.$tmp_dept_id.code} - {/if}{$dept_list.$tmp_dept_id.description}</b></td>
							
							{assign var=cols value=1}
							{if !$smarty.request.group_same_price}
								{assign var=cols value=$cols+2}
							{/if}
							<td align="right" colspan="{$cols}"><b>{$dt} Dept Total: </b></td>
							
							{* Qty *}
							<td align="right"><b>{$tmp_dept_info.qty|qty_nf}</b></td>
							
							{* Amt *}
							<td align="right"><b>{$tmp_dept_info.amt|number_format:2}</b></td>
							
							{* Cost / Disc *}
							<td align="right"><b>{$tmp_dept_info.total_cost|number_format:2}</b></td>
							
							{* Nett Amt *}
							<td align="right"><b>{$tmp_dept_info.nett_amt|number_format:2}</b></td>
						</tr>
					{/foreach}
				{/foreach}
			{/foreach}
			
			<tr class="header">
				{assign var=cols value=4}
				{if !$smarty.request.group_same_price}
					{assign var=cols value=$cols+2}
				{/if}
				<th align="right" colspan="{$cols}">Total: </th>
				<th align="right">{$data.total.qty|qty_nf}</th>
				<th align="right">{$data.total.amt|number_format:2}</th>
				<th align="right">{$data.total.total_cost|number_format:2}</th>
				<th align="right">{$data.total.nett_amt|number_format:2}</th>
			</tr>
		</table>
	{/if}
{/if}

{if !$smarty.request.submit_type}
{include file="footer.tpl"}
{/if}
