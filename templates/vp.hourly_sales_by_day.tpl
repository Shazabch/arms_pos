{*
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
	
	{*
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
	*}
	
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
				{foreach from=$hour key=h item=hrs}
				<th nowrap>{$hrs}</th>
				{/foreach}
				<th>Total</th>
				<th>AVG Hour Amount</th>
			</tr>
			
			{foreach from=$days item=day}
			<tr>
				<td>{$day}</td>
				{foreach from=$hour key=h item=hrs}
				<td align=right>{$data.$day.$h|number_format:2|ifzero:"-"}</td>
				{/foreach}
				<td align=right>{$day_total.$day|number_format:2|ifzero:"-"}</td>
				<td align=right>{$day_total.$day/$hour_count|number_format:2|ifzero:"-"}</td>
			</tr>
			{/foreach}
			
			<tr>
				<td class="r"><b>Total</b></td>
				{foreach from=$hour key=h item=hrs}
				<td align=right>{$hour_total.$h|number_format:2|ifzero:"-"}</td>
				{/foreach}
				<td align=right>{$grand_total|number_format:2|ifzero:"-"}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
