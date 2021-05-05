{*
7/31/2012 11:41 AM Andy
- Add sorting feature for report.

8/2/2012 2:40 PM Andy
- Add cost and gp.

8/14/2012 11:37 AM Andy
- Enhance Sales Report by Week/Month to have expand/collapse control.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

{include file="header.tpl"}

{if !$no_header_footer}
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
.tr_date_total td{
	background-color: #cfcfcf;
}
.negative{
	font-weight: bold;
	color: red;
}
</style>
{/literal}

<script type="text/javascript">

{literal}

var SALES_REPORT_BY_MONTH_WEEK = {
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
	// function to validate form before submit
	check_form: function(){
		var dt1 = strtotime(this.f['date_from'].value);
		var dt2 = strtotime(this.f['date_to'].value);
		
		if(dt2 < dt1){
			alert('Date to cannot early then date from.');
			return false;
		}
		
		var diff = day_diff(this.f['date_from'].value, this.f['date_to'].value);
		if(diff>90){
			alert('Report maximum can view sales last 90 days.');
			return false;
		}
		
		return true;
	},
	// function when user click show report
	submit_form: function(type){
		if(!this.check_form())	return false;
		
		this.f['submit_type'].value = '';
		if(type == 'excel')	this.f['submit_type'].value = type;
		
		this.f.submit();
	},
	// function when user click print
	print_form: function(){
		window.print();
	},
	// function when user expand/collapse details
	toggle_sub_view: function(k){
		var img = $('img_date_total-'+k);
		var tobdy_item_row = $('tobdy_item_row-'+k);
		
		if(!img || !tobdy_item_row)	return false;
		
		var new_src = 'ui/collapse.gif';
		var new_title = 'Collapse';
		var show_sub = true;
		
		if(img.src.indexOf('collapse.gif')>0){	// is collapse
			new_src = 'ui/expand.gif';
			new_title = 'Expand';
			show_sub = false;
		}
		
		img.src = new_src;
		img.title = new_title;
		if(show_sub){
			tobdy_item_row.show();
		}else{
			tobdy_item_row.hide();
		}
		
	}
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if !$no_header_footer}
<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="type" value="{$rpt_type}" />
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" value="" />
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Sort by</b>
	<select name="order_by">
		{foreach from=$sort_list key=k item=r}
			<option value="{$k}" {if $smarty.request.order_by eq $k}selected {/if}>{$r.label}</option>
		{/foreach}
	</select>
	<select name="order_seq">
		<option value="asc" {if $smarty.request.order_seq eq 'asc'}selected {/if}>Ascending</option>
		<option value="desc" {if $smarty.request.order_seq eq 'desc'}selected {/if}>Descending</option>
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	
	<input type="button" value="Show Report" onClick="SALES_REPORT_BY_MONTH_WEEK.submit_form();" />
	<input type="button" value="Print" onClick="SALES_REPORT_BY_MONTH_WEEK.print_form();" />
	<button onClick="SALES_REPORT_BY_MONTH_WEEK.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	
	<br />
	<ul>
		<li> Report maximum can view sales last 90 days.</li>
		<li> This report only show finalised sales.</li>
	</ul>
	
</form>

<script type="text/javascript">
	SALES_REPORT_BY_MONTH_WEEK.initialize();
</script>
{/if}

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
	 * No Data
	{else}
		<h3>{$report_title}</h3>
		
		<table width="100%" class="report_table" cellpadding="0" cellspacing="0">
			<tr class="header">
				<th>{if $rpt_type eq 'w'}Week{else}Month{/if}</th>
				<th>No.</th>
				<th>MCode</th>
				<th>{$config.link_code_name}</th>
				<th>Description</th>
				<th>Qty</th>
				<th>Amt</th>
				<th>AVG SP</th>
				<th>Cost</th>
				<th>GP</th>
			</tr>
			
			
			{foreach from=$data.data key=k item=data_info}
				<!-- row total -->
				<tr class="tr_date_total">
					<td>
						<img src="/ui/expand.gif" align="bottom" class="clickable" title="Expand" onClick="SALES_REPORT_BY_MONTH_WEEK.toggle_sub_view('{$k}');" id="img_date_total-{$k}" />&nbsp;
						{$data_info.label}
					</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="r {if $data_info.total.qty<0}negative{/if}">{$data_info.total.qty|qty_nf}</td>
					<td class="r {if $data_info.total.amt<0}negative{/if}">{$data_info.total.amt|number_format:2}</td>
					
					<!-- avg selling price -->
					{assign var=avg_sp value=$data_info.total.amt/$data_info.total.qty}
					<td class="r">{$avg_sp|number_format:2}</td>
					
					<!-- cost -->
					<td class="r {if $data_info.total.cost<0}negative{/if}">{$data_info.total.cost|number_format:$config.global_cost_decimal_points}</td>
					
					<!-- GP -->
					{assign var=gp value=$data_info.total.amt-$data_info.total.cost}
					<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
				</tr>
				
				<!-- weekly items -->
				<tbody id="tobdy_item_row-{$k}" style="display:none;">
					{assign var=row_no value=0}
					{foreach from=$data_info.item_list key=sid item=item}
						{assign var=row_no value=$row_no+1}
						{assign var=si_info value=$data.si_info.$sid}
						
						<tr>
							<td>{$data_info.label}</td>
							<td align="center">{$row_no}</td>
							<td>{$si_info.mcode|default:'-'}</td>
							<td>{$si_info.link_code|default:'-'}</td>
							<td>{$si_info.description|default:'-'}</td>
							
							<td class="r {if $item.qty<0}negative{/if}">{$item.qty|qty_nf}</td>
							<td class="r {if $item.amt<0}negative{/if}">{$item.amt|number_format:2}</td>
							
							<!-- avg selling price -->
							{assign var=avg_sp value=$item.amt/$item.qty}
							<td class="r">{$avg_sp|number_format:2}</td>
							
							<!-- cost -->
							<td class="r {if $item.cost<0}negative{/if}">{$item.cost|number_format:$config.global_cost_decimal_points}</td>
							
							<!-- GP -->
							{assign var=gp value=$item.amt-$item.cost}
							<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
						</tr>
					{/foreach}
				</tbody>
			{/foreach}
			
			<tr class="header">
				<td colspan="5" class="r"><b>Total</b></td>
				<td class="r">{$data.total.qty|qty_nf}</td>
				<td class="r">{$data.total.amt|number_format:2}</td>
				
				<!-- avg selling price -->
				{assign var=avg_sp value=$data.total.amt/$data.total.qty}
				<td class="r">{$avg_sp|number_format:2}</td>
				
				<!-- cost -->
				<td class="r">{$data.total.cost|number_format:$config.global_cost_decimal_points}</td>
				
				<!-- GP -->
				{assign var=gp value=$data.total.amt-$data.total.cost}
				<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}