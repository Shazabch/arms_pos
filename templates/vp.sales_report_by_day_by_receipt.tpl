{*
12/12/2017 11:35 AM Andy
- Rename column "Sales" to "Merchant Sales", "Vendor Price" to "Commission Charge".

12/15/2017 2:54 PM Andy
- Fixed note should be "last 30 days".
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
		<li> Report maximum can view sales last 30 days.</li>
		<li> This report only show finalised sales.</li>
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
				<th rowspan="2">No.</th>
				<th rowspan="2">Date</th>
				<th rowspan="2">Receipt<br />Reference No</th>
				<th rowspan="2">ARMS Code</th>
				<th rowspan="2">MCode</th>
				<th width="40%" rowspan="2">Description</th>
				<th colspan="2">Merchant Sales</th>
				<th colspan="3">Commission Charge</th>
				<th rowspan="2">Net Rec.<br />Amount</th>
			</tr>
			
			<tr class="header">
				<th>Qty</th>
				<th>Total</th>
				<th>Type</th>
				<th>Comm (%)</th>
				<th>Comm Amt</th>
			</tr>
			
			{assign var=row_no value=0}
			{foreach from=$data.sales_data key=date item=sid_list}
				{assign var=date_total_data value=$data.date_sales.$date}
				
				{foreach from=$sid_list key=sid item=si_sales_info}
					{assign var=si_info value=$data.si_info.$sid}
					{foreach from=$si_sales_info.details key=receipt_ref_no item=r}
						
						{assign var=row_no value=$row_no+1}
						
						<tr>
							<td align="center">{$row_no}</td>
							<td>{$date}</td>
							<td>{$receipt_ref_no}</td>
							<td class="center">{$si_info.sku_item_code|default:'-'}</td>
							<td>{$si_info.mcode|default:'-'}</td>
							<td>{$si_info.description|default:'-'}</td>
							<td class="r {if $r.qty<0}negative{/if}">{$r.qty|qty_nf}</td>
							<td class="r {if $r.amt<0}negative{/if}">{$r.amt|number_format:2}</td>
							<td align="center">{$r.discount_code|default:'-'}</td>
							<td class="r">{$r.discount_rate|default:'0'}%</td>
							<td class="r {if $r.commission_amt<0}negative{/if}">{$r.commission_amt|number_format:2}</td>
							<td class="r {if $r.nett_amt<0}negative{/if}">{$r.nett_amt|number_format:2}</td>
						</tr>
					{/foreach}
				{/foreach}
				
				<tr class="tr_date_total">
					<td colspan="5">&nbsp;</td>
					<td><b>Total of the Day</b></td>
					<td class="r {if $date_total_data.qty<0}negative{/if}">{$date_total_data.qty|qty_nf}</td>
					<td class="r {if $date_total_data.amt<0}negative{/if}">{$date_total_data.amt|number_format:2}</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="r {if $date_total_data.commission_amt<0}negative{/if}">{$date_total_data.commission_amt|number_format:2}</td>
					<td class="r {if $date_total_data.nett_amt<0}negative{/if}">{$date_total_data.nett_amt|number_format:2}</td>
				</tr>
			{/foreach}
			
			<tr class="header">
				<td colspan="5">&nbsp;</td>
				<td><b>Grand Total</b></td>
				<td class="r {if $data.total.qty<0}negative{/if}">{$data.total.qty|qty_nf}</td>
				<td class="r {if $data.total.amt<0}negative{/if}">{$data.total.amt|number_format:2}</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="r {if $data.total.commission_amt<0}negative{/if}">{$data.total.commission_amt|number_format:2}</td>
				<td class="r {if $data.total.nett_amt<0}negative{/if}">{$data.total.nett_amt|number_format:2}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}