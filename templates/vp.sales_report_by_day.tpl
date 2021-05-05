{*
7/12/2012 11:09 AM Andy
- Add to show total Amount.
- Hide column "Stock Balance" and "Qty".
- Remove Branch dropdown from report. Always show the login branch sales only.

7/19/2012 9:44 AM Andy
- Enhance report to show open price, scale type and sales in date range.

7/24/2012 1:49 PM Andy
- Add to show Old Code.

7/31/2012 11:41 AM Andy
- Add sorting feature for report.

8/1/2012 3:50 PM Andy
- Add print and export excel.

8/2/2012 2:40 PM Andy
- Add cost and gp.

12/14/2012 4:54 PM Andy
- Move "Open Price" and "Scale Type" to become the last 2 column.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

{include file="header.tpl"}

{if !$no_header_footer}
{literal}
<style>
.negative{
	font-weight: bold;
	color: red;
}

.tr_date_total td{
	background-color: #cfcfcf;
}
</style>
{/literal}

<script type="text/javascript">
{literal}

var SALES_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
	},
	// function to validate form
	check_form: function(){
		/*if(!this.f['branch_id'].value){
			alert('Please select branch.');
			return false;
		}*/
		
		if(this.f['date_from'].value > this.f['date_to'].value){
			alert('Date to cannot early then date from.');
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
<form name="f_a" method="post" onSubmit="return false;" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" value="" />
	
	{*
	<span>
		<b>Branch:</b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$allowed_branches_list key=bid item=b}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
			{/foreach}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	*}
	
	<span>
		<b>Date: </b>
		<select name="date_from">
			{foreach from=$allowed_date item=date}
				<option value="{$date}" {if $smarty.request.date_from eq $date}selected {/if}>{$date}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;&nbsp;
		<select name="date_to">
			{foreach from=$allowed_date item=date}
				<option value="{$date}" {if $smarty.request.date_to eq $date}selected {/if}>{$date}</option>
			{/foreach}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
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
	
	<input type="button" value="Show Report" onClick="SALES_REPORT.submit_form();" />
	<input type="button" value="Print" onClick="SALES_REPORT.print_form();" />
	<button onClick="SALES_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	
	<br />
	<ul>
		<li> Report maximum can view sales last 1 week.</li>
		<li> This report included unfinalise sales.</li>
	</ul>
</form>

<script type="text/javascript">
	SALES_REPORT.initialize();
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
				<th>Date</th>
				<th>No.</th>
				<th>MCode</th>
				<th>{$config.link_code_name}</th>
				<th>Description</th>
				<th>Qty</th>
				<th>Amt</th>
				<th>AVG SP</th>
				<th>Cost</th>
				<th>GP</th>
				<th>No# of Receipt</th>
				<th>Open Price</th>
				<th>Scale Type</th>
			</tr>
			
			{assign var=row_no value=0}
			{foreach from=$data.date_item_sales key=date item=date_si_list}
				{assign var=date_total_data value=$data.date_sales.$date}
				
				{foreach from=$date_si_list key=sid item=si_sales_info}
					{assign var=si_info value=$data.si_info.$sid}
					
					{foreach from=$si_sales_info.details key=price_scale_key item=price_scale_data}
						{assign var=row_no value=$row_no+1}
						
						<tr>
							<td>{$date}</td>
							<td align="center">{$row_no}</td>
							<td>{$si_info.mcode|default:'-'}</td>
							<td>{$si_info.link_code|default:'-'}</td>
							<td>{$si_info.description|default:'-'}</td>
							<td class="r {if $price_scale_data.qty<0}negative{/if}">{$price_scale_data.qty|qty_nf}</td>
							<td class="r {if $price_scale_data.amt<0}negative{/if}">{$price_scale_data.amt|number_format:2}</td>
							
							<!-- avg selling price -->
							{assign var=avg_sp value=$price_scale_data.amt/$price_scale_data.qty}
							<td class="r">{$avg_sp|number_format:2}</td>
							
							<!-- cost -->
							<td class="r {if $price_scale_data.cost<0}negative{/if}">{$price_scale_data.cost|number_format:$config.global_cost_decimal_points}</td>
							
							<!-- GP -->
							{assign var=gp value=$price_scale_data.amt-$price_scale_data.cost}
							<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
							
							<!-- no. of receipt -->
							<td class="r">{$price_scale_data.receipt_count|number_format}</td>
							
							<td>{$price_scale_data.open_price_label}</td>
							<td>{$price_scale_data.scale_type_label}</td>
						</tr>
					{/foreach}
				{/foreach}
				
				<tr class="tr_date_total">
					<td>Total</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					
					<td class="r {if $date_total_data.qty<0}negative{/if}">{$date_total_data.qty|qty_nf}</td>
					<td class="r {if $date_total_data.amt<0}negative{/if}">{$date_total_data.amt|number_format:2}</td>
					
					<!-- avg selling price -->
					{assign var=avg_sp value=$date_total_data.amt/$date_total_data.qty}
					<td class="r">{$avg_sp|number_format:2}</td>
					
					<!-- cost -->
					<td class="r {if $date_total_data.cost<0}negative{/if}">{$date_total_data.cost|number_format:$config.global_cost_decimal_points}</td>
					
					<!-- GP -->
					{assign var=gp value=$date_total_data.amt-$date_total_data.cost}
					<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
							
					<!-- no. of receipt -->
					<td class="r">{$date_total_data.receipt_count|number_format}</td>
					
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			{/foreach}
			
			<tr class="header">
				<td colspan="5" class="r"><b>Total</b></td>
				<td class="r {if $data.total.qty<0}negative{/if}">{$data.total.qty|qty_nf}</td>
				<td class="r {if $data.total.amt<0}negative{/if}">{$data.total.amt|number_format:2}</td>
				
				<!-- avg selling price -->
				{assign var=avg_sp value=$data.total.amt/$data.total.qty}
				<td class="r">{$avg_sp|number_format:2}</td>
				
				<!-- cost -->
				<td class="r {if $data.total.cost<0}negative{/if}">{$data.total.cost|number_format:$config.global_cost_decimal_points}</td>
				
				<!-- GP -->
				{assign var=gp value=$data.total.amt-$data.total.cost}
				<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
							
				<!-- no. of receipt -->
				<td class="r">{$data.total.receipt_count|number_format}</td>
				
				<td>-</td>
				<td>-</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}