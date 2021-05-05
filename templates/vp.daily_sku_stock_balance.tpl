{*
*}

{include file="header.tpl"}

{if $is_email}
<style>
{literal}
.report_table th, .report_table td{
	border: 1px solid black;
	border-collapse: collapse;
}
.r{
	text-align: right;
}
{/literal}
</style>
{/if}

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


<style>
{literal}
.negative{
	font-weight: bold;
	color: red;
}

.not_up_to_date{
	color:green;
}
{/literal}
</style>

<script type="text/javascript">

{literal}

var DAILY_SKU_SB_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
	        inputField     :    "inp_date",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	},
	
	// function when user click show report
	submit_form: function(type){
		//if(!this.check_form())	return false;
		
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
<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" value="" />
	
	<b>Date</b>
	<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Show Report" onClick="DAILY_SKU_SB_REPORT.submit_form();" />
	<input type="button" value="Print" onClick="DAILY_SKU_SB_REPORT.print_form();" />
	<button onClick="DAILY_SKU_SB_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	
	<br />
	<ul>
		<li> Report maximum can view 1 day.</li>
		<li> This report only show finalised sales.</li>
		<li> <span class="not_up_to_date">*</span> = Indicate SKU item(s) is not Up-to-date.</li>
	</ul>	
</form>

<script type="text/javascript">
	DAILY_SKU_SB_REPORT.initialize();
</script>
{/if}

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
	 * No Data
	{else}
		<h3>{$report_title}</h3>
		
		<table class="report_table" cellpadding="0" cellspacing="0" {if $is_email}border="1"{/if} width="100%">
			<tr class="header">
				<th rowspan="2">No.</th>
				<th rowspan="2">SKU Item Code</th>
				<th rowspan="2">MCode</th>
				<th rowspan="2" width="35%">Description</th>
				<th rowspan="2">Selling<br />Price</th>
				{if $got_opening_sc}
					<th colspan="2">Stock Take</th>
				{/if}
				<th rowspan="2">Opening<br />Balance</th>
				<th colspan="3">Stock In</th>
				<th colspan="4">Stock Out</th>
				<th rowspan="2">Closing<br />Balance</th>
			</tr>
			
			<tr class="header">
				{if $got_opening_sc}
					<th>Stk Qty</th>
					<th>Stk Adj</th>
				{/if}
				<th>Vendor GRN</th>
				<th>IBT GRN</th>
				<th>Adj In</th>
				<th>POS</th>
				<th>GRA</th>
				<th>DO</th>
				<th>Adj Out</th>
			</tr>
			
			{foreach from=$data.details name=d key=sid item=r}
				<tr>
					<td>{$smarty.foreach.d.iteration}</td>
					<td align="center">{$r.sku_item_code} {if $r.changed}<sup class="not_up_to_date">*</sup>{/if}</td>
					<td>{$r.mcode}</td>
					<td>{$r.description}</td>
					<td class="r {if $r.selling_price<0}negative{/if}">{$r.selling_price|number_format:2}</td>
					{if $got_opening_sc}
						<td class="r {if $r.sc.qty<0}negative{/if}">{$r.sc.qty|qty_nf}</td>
						<td class="r {if $r.sc_adj.qty<0}negative{/if}">{$r.sc_adj.qty|qty_nf}</td>
					{/if}
					<td class="r {if $r.opening_sb.qty<0}negative{/if}">{$r.opening_sb.qty|qty_nf}</td>
					<td class="r {if $r.vendor_grn.qty<0}negative{/if}">{$r.vendor_grn.qty|qty_nf}</td>
					<td class="r {if $r.ibt_grn.qty<0}negative{/if}">{$r.ibt_grn.qty|qty_nf}</td>
					<td class="r {if $r.adj_in.qty<0}negative{/if}">{$r.adj_in.qty|qty_nf}</td>
					<td class="r {if $r.pos.qty<0}negative{/if}">{$r.pos.qty|qty_nf}</td>
					<td class="r {if $r.gra.qty<0}negative{/if}">{$r.gra.qty|qty_nf}</td>
					<td class="r {if $r.do.qty<0}negative{/if}">{$r.do.qty|qty_nf}</td>
					<td class="r {if $r.adj_out.qty<0}negative{/if}">{$r.adj_out.qty|qty_nf}</td>
					<td class="r {if $r.closing_sb.qty<0}negative{/if}">{$r.closing_sb.qty|qty_nf}</td>
				</tr>
			{/foreach}
			
			<tr>
				<td colspan="5" align="right"><b>Total</b></td>
				{if $got_opening_sc}
					<td class="r {if $data.total.sc.qty<0}negative{/if}">{$data.total.sc.qty|qty_nf}</td>
					<td class="r {if $data.total.sc_adj.qty<0}negative{/if}">{$data.total.sc_adj.qty|qty_nf}</td>
				{/if}
				<td class="r {if $data.total.opening_sb.qty<0}negative{/if}">{$data.total.opening_sb.qty|qty_nf}</td>
				<td class="r {if $data.total.vendor_grn.qty<0}negative{/if}">{$data.total.vendor_grn.qty|qty_nf}</td>
				<td class="r {if $data.total.ibt_grn.qty<0}negative{/if}">{$data.total.ibt_grn.qty|qty_nf}</td>
				<td class="r {if $data.total.adj_in.qty<0}negative{/if}">{$data.total.adj_in.qty|qty_nf}</td>
				<td class="r {if $data.total.pos.qty<0}negative{/if}">{$data.total.pos.qty|qty_nf}</td>
				<td class="r {if $data.total.gra.qty<0}negative{/if}">{$data.total.gra.qty|qty_nf}</td>
				<td class="r {if $data.total.do.qty<0}negative{/if}">{$data.total.do.qty|qty_nf}</td>
				<td class="r {if $data.total.adj_out.qty<0}negative{/if}">{$data.total.adj_out.qty|qty_nf}</td>
				<td class="r {if $data.total.closing_sb.qty<0}negative{/if}">{$data.total.closing_sb.qty|qty_nf}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
