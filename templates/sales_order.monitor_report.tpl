{*
9/2/2020 9:21 AM William
- Bug fixed report unable to export.
*}

{include file='header.tpl'}
{if !$no_header_footer}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function init_calendar(){
	Calendar.setup({
		inputField     :    "inp_date_from",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_from",
		align          :    "Bl",
		singleClick    :    true
	});
	
	Calendar.setup({
		inputField     :    "inp_date_to",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_to",
		align          :    "Bl",
		singleClick    :    true
	});		
}

function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	
	$('span_area').update(_loading_);
	$('span_batch_code').update(_loading_);
	
	new Ajax.Request(phpself, {
		parameters:{
			a: 'get_area_batch_code',
			branch_id: bid
		},
		onComplete:function(e){
			var str = e.responseText.trim();
			var ret = {};
		    var err_msg = '';
    
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['area_html'] && ret['batch_code_html']){ // success
                	$('span_area').update(ret['area_html']);
                	$('span_batch_code').update(ret['batch_code_html']);
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

		    // prompt the error
		    alert(err_msg);
		}
	});
}

function area_changed(){
	var bid = document.f_a['branch_id'].value;
	var area = document.f_a['area'].value;
	
	$('span_batch_code').update(_loading_);
	
	new Ajax.Request(phpself, {
		parameters:{
			a: 'get_batch_code',
			branch_id: bid,
			area: area
		},
		onComplete:function(e){
			var str = e.responseText.trim();
			var ret = {};
		    var err_msg = '';
    
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['batch_code_html']){ // success
                	$('span_batch_code').update(ret['batch_code_html']);
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

		    // prompt the error
		    alert(err_msg);
		}
	});
}

function submit_form(type){
	if(!document.f_a['branch_id'].value){
		alert('Please select branch.');
		return;
	}
	
	if(!document.f_a['area'] || document.f_a['area'].value=='NO_DATA'){
		alert('Please select area.');
		return;
	}
	
	if(!document.f_a['batch_code'] || document.f_a['batch_code'].value=='NO_DATA'){
		alert('Please select batch code.');
		return;
	}
	
	document.f_a['export_excel'].value = 0;
	if(type == 'excel'){
		document.f_a['export_excel'].value = 1;
	}
	
	document.f_a.submit();
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
		<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<form name="f_a" onSubmit="return false;" class="stdframe" method="post">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="export_excel" value="0" />
	
	<b>From</b>
	<input name="date_from" id="inp_date_from" size="10" maxlength="10"  value="{$smarty.request.date_from|date_format:"%Y-%m-%d"}" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>To</b>
	<input name="date_to" id="inp_date_to" size="10" maxlength="10"  value="{$smarty.request.date_to|date_format:"%Y-%m-%d"}" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	{if $BRANCH_CODE eq 'HQ'}
	<span>
		<b>Branch</b>
		<select name="branch_id" onChange="branch_changed();">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=b}
				{if $b.active}
					<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/if}
			{/foreach}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<span>
		<b>Area</b>
		<span id="span_area">
			{include file='sales_order.monitor_report.area_list.tpl'}
		</span>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<span>
		<b>Batch Code</b>
		<span id="span_batch_code">
			{include file='sales_order.monitor_report.batch_code_list.tpl'}
		</span>
	</span>
	
	<p>
		<button onClick="submit_form(0);">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
</form>

<script>init_calendar();</script>
{/if}

{if $smarty.request.show_report && !$err}
	{if !$data}
		<br />
		-- No Data --
	{else}
		<h2>{$report_title}</h2>
		
		<table width="100%" class="report_table">
			<tr	class="header">
				<th>&nbsp;</th>
				<th>ARMS Code</th>
				<th>MCode</th>
				<th>Art No.</th>
				<th>Description</th>
				<th>SO Qty</th>
				<th>PO Qty</th>
				<th>GRN Qty</th>
				<th>DO Qty</th>
			</tr>
			{assign var=n value=0}
			{foreach from=$data.by_so key=so_key item=so_items_list}
				{assign var=so_info value=$data.so_info.$so_key}
				<tr bgcolor="#99FFFF">
					<td colspan="9">{$so_info.order_no}, <b>Debtor:</b> {$so_info.debtor_desc}</td>
				</tr>
				{foreach from=$so_items_list key=soi_id item=r}
					{assign var=n value=$n+1}
					{assign var=si_info value=$data.si_info[$r.sid]}
					<tr>
						<td>{$n}.</td>
						<td>{$si_info.sku_item_code|default:'&nbsp;'}</td>
						<td>{$si_info.mcode|default:'&nbsp;'}</td>
						<td>{$si_info.artno|default:'&nbsp;'}</td>
						<td>{$si_info.description|default:'&nbsp;'}</td>
						
						<td class="r">{$r.so_total_qty|qty_nf}</td>
						<td class="r">{$r.po_total_qty|qty_nf}</td>
						<td class="r">{$r.grn_total_qty|qty_nf}</td>
						<td class="r">{$r.do_total_qty|qty_nf}</td>
					</tr>
				{/foreach}
			{/foreach}
		</table>
	{/if}
{/if}

{include file='footer.tpl'}