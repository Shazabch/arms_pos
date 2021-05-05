{*
REVISION HISTORY
=================
6/19/2015 11:00 AM Eric
- Enhanced to show by items and sales agent

1/12/2017 10:37 AM Andy
- Fixed expand details bug.

2/3/2017 3:29 PM Andy
- Fixed sometime link to empty DO.
- Fixed zero daily amount.

4/20/2017 4:28 PM Justin
- Bug fixed on total qty and amount sum up wrongly.

3/26/2019 5:27 PM Justin
- Enhanced to have Commission Sales Qty, Sales Amount and Commission Amount.

11/6/2019 4:22 PM Justin
- Removed date from and to selection, and replaced with Year and Month selection.
- Enhanced to show "Commission by Sales / Qty Range".
- Removed the "All" select for sales agent filter.

11/19/2019 10:24 AM Justin
- Removed the 7 days notes.
- Enhanced to show ratio information.

11/22/2019 5:37 PM Justin
- Set 2 decimal points for total ratio.

12/18/2019 2:44 PM Justin
- Bug fixed on report couldn't show out data while doesn't have range sales but has flat rate table.

12/20/2019 2:44 PM Justin
- Enhanced to bring back the "All" selection for Sales Agent filter.

06/29/2020 02:11 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}

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

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function toggle_sac_details(bid, doc_no, obj){
	var ele = $("sac_detail_"+bid+"_"+doc_no);
	if(ele.style.display == "none"){
		obj.src = "/ui/collapse.gif";
		ele.style.display = "";
	}else{
		obj.src = "/ui/expand.gif";
		ele.style.display = "none";
	}
}

function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	
	$('span_counters').update(_loading_);
	new Ajax.Request(phpself+'?a=load_counters&branch_id='+bid, {
		onComplete: function(msg){
			var data = msg.responseText;

			try{
                ret = JSON.parse(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                $('span_counters').update(ret['html']);
	                return;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

      		// prompt the error
		    alert(err_msg);
		}
	});
}

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a">
<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id" onchange="branch_changed();">
		    <option value="">-- All --</option>
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
		    {/foreach}
		    {*if $branch_group.header}
		        <optgroup label="Branch Group">
					{foreach from=$branch_group.header item=r}
					    {capture assign=bgid}bg,{$r.id}{/capture}
						<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					{/foreach}
				</optgroup>
			{/if*}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<b>Counter</b>
	<span id="span_counters">
		{include file='report.sa_daily_details.counters.tpl'}
	</span>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Year</b> 
	<select name="year">
	{foreach from=$years key=k item=r}
		<option value="{$k}" {if $smarty.request.year eq $k}selected{/if}>{$r.year}</option>
	{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	<b>Month</b>
	<select name="month">
		{foreach from=$months key=k item=r}
			<option value="{$k}" {if $smarty.request.month eq $k}selected{/if}>{$r}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Sales From</b>
	<select name="sales_type">
		<option value="">-- All --</option>
		<option value="open" {if $smarty.request.sales_type eq 'open'}selected{/if}>DO - Cash Sales</option>
		<option value="credit_sales" {if $smarty.request.sales_type eq 'credit_sales'}selected{/if}>DO - Credit Sales</option>
		<option value="pos"{if $smarty.request.sales_type eq 'pos'}selected{/if}>POS</option>
	</select>
</p>
<p>
	<b>Department</b>
	<select name="department_id">
		<option value=0>-- All --</option>
		{foreach from=$departments item=dept}
			<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>SKU Type</b>
	<select name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=t}
			<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Sales Agent</b>
	<select name="sa_id">
		<option value="">-- All --</option>
		{foreach from=$sa item=sa}
			<option value="{$sa.id}" {if $smarty.request.sa_id eq $sa.id}selected {/if}>{$sa.code} - {$sa.name}</option>
		{/foreach}
	</select>
</p>
<p>
	<b>Transaction Status</b>
	<select name="tran_status">
		<option value="all" {if !$smarty.request.tran_status || $smarty.request.tran_status eq "all"}selected{/if}>-- All --</option>
		{foreach from=$transaction_status key=status item=t}
			<option value="{$status}" {if $smarty.request.tran_status eq $status}selected {/if}>{$t}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>DO Status</b>
		<select name="do_status">
		<option value="0" {if !$smarty.request.status}selected{/if}>All</option>
		{foreach from=$do_status key=status item=t}
			<option value="{$status}" {if $smarty.request.do_status eq $status}selected {/if}>{$t}</option>
		{/foreach}
	</select>
</p>
<p>
* This report does not based on finalised sales.
{if !$config.sa_calc_average_sales}
<br />* Sales amount will not be divided if the receipt contains more than one Sales Agent.
{/if}
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table && !$range_table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	{if $BRANCH_CODE ne 'HQ' || !$smarty.request.branch_id}
		<table class="rpt_table" width="30%" cellspacing="0" cellpadding="0">
			<tr class="header">
				<th colspan="2">Report Summary</th>
			</tr>
			<tr>
				<td><b>Total Qty</b></td>
				<td class="r">{$total_sales.qty|qty_nf}</td>
			</tr>
			<tr>
				<td><b>Total Amount</b></td>
				<td class="r">{$total_sales.amt|number_format:2}</td>
			</tr>
			{if $total_sales.flat_rate.commission_sales_amt > 0}
				<tr>
					<td><b>Total Commission Sales Qty (Flat Rate)</b></td>
					<td class="r">{$total_sales.flat_rate.commission_sales_qty|qty_nf}</td>
				</tr>
				<tr>
					<td><b>Total Commission Sales Amount (Flat Rate)</b></td>
					<td class="r">{$total_sales.flat_rate.commission_sales_amt|number_format:2}</td>
				</tr>
			{/if}
			{if $total_sales.range.commission_sales_amt > 0}
				<tr>
					<td><b>Total Commission Sales Qty (Sales / Qty Range)</b></td>
					<td class="r">{$total_sales.range.commission_sales_qty|qty_nf}</td>
				</tr>
				<tr>
					<td><b>Total Commission Sales Amount (Sales / Qty Range)</b></td>
					<td class="r">{$total_sales.range.commission_sales_amt|number_format:2}</td>
				</tr>
			{/if}
			{if $smarty.request.sa_id}
				<tr>
					<td><b>Total Commission Amount</b></td>
					<td class="r">{$total_sales.sa_commission_amt|number_format:2}</td>
				</tr>
			{/if}
		</table>
	{/if}
	{if $table}
		{foreach from=$table item=dc_list key=bid}
			{assign var=ttl_amt value=0}
			{assign var=ttl_qty value=0}
			{assign var=ttl_cost value=0}
			{assign var=ttl_commission_sales_amt value=0}
			{assign var=ttl_commission_sales_qty value=0}
			{assign var=ttl_sa_commission_amt value=0}
			{assign var=header_colspan value=2}
			{if $smarty.request.sa_id}
				{assign var=header_colspan value=$header_colspan+1}
			{/if}
			
			{if $BRANCH_CODE ne 'HQ' || !$smarty.request.branch_id}
				<h3>{$branches.$bid.code}</h3>
			{/if}
			<h2>Commission by Flat Rate</h2>
			<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0">
				<tr class="header">
					<th width="40" rowspan="2">#</th>
					<th rowspan="2">Document No</th>
					<th rowspan="2">Document Type</th>
					<th rowspan="2">Counter Name</th>
					<th rowspan="2">Cashier Name</th>
					<th rowspan="2">Sales Agent</th>
					<th rowspan="2">ARMS Code</th>
					<th rowspan="2">Mcode</th>
					<th rowspan="2">Sku Description</th>
					<th colspan="2">Transaction</th>
					<th colspan="2">Sales</th>
					<th colspan="{$header_colspan}">Commission</th>
					{*if $sessioninfo.show_report_gp}
						<th rowspan="2">GP</th>
						<th rowspan="2">GP(%)</th>
					{/if*}
				</tr>
				<tr class="header">
					<th>Times</th>
					<th>Status</th>
					<th>Amount</th>
					<th>Qty</th>
					<th>Sales Amount</th>
					<th>Sales Qty</th>
					{if $smarty.request.sa_id}
						<th>Amount</th>
					{/if}
					{*if $sessioninfo.show_cost}
						<th rowspan="2">Cost</th>
					{/if*}
				</tr>
				<tbody>
					{foreach from=$dc_list item=doc_list key=date_counter name=mst}
						<tr>
							<td>{$smarty.foreach.mst.iteration}. <img src="/ui/expand.gif" width="10" onclick="toggle_sac_details('{$bid}','{$smarty.foreach.mst.iteration}', this);" title="Show Detail" class="clickable"></td>
							<td colspan="10">{$date_counter}&nbsp;</td>
							<td class="r">{$date_sales.$bid.$date_counter.amt|number_format:2}</td>
							<td class="r">{$date_sales.$bid.$date_counter.qty|qty_nf}</td>
							<td class="r">{$date_sales.$bid.$date_counter.commission_sales_amt|number_format:2}</td>
							<td class="r">{$date_sales.$bid.$date_counter.commission_sales_qty|qty_nf}</td>
							{if $smarty.request.sa_id}
								<td class="r">{$date_sales.$bid.$date_counter.sa_commission_amt|number_format:2}</td>
							{/if}
						</tr>
						
						<tbody id="sac_detail_{$bid}_{$smarty.foreach.mst.iteration}" style="display:none;">
							{foreach from=$doc_list item=r key=ref_no name=dtl}
								<tr bgcolor="#eeeeee">
									<td>&nbsp;</td>
									<td align="center">
										{if $r.type eq 'POS'}
											{if $no_header_footer}
												{$r.doc_no}
											{else} 
												<a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$ref_no}'));">{$r.doc_no}</a>
											{/if}
										{else}
											{if $no_header_footer}
												{$r.doc_no}
											{else} 
												<a href="do.php?a=view&id={$r.id}&branch_id={$bid}" target="_blank">{$r.doc_no}</a>
											{/if}
										{/if}
									</td>
									<td>{$r.type}</td>
									<td>{if $r.counter_name != ""}{$r.counter_name}{else}&nbsp{/if}</td>
									<td>{$r.cashier_name}</td>
									<td colspan="4">{$r.sa_name}</td>
									<td>{$r.trans_time}</td>
									<td align="center">
										{if $r.type eq 'POS'}
											{if !$r.cancel_status}
												Valid 
											{else}
												{if $r.prune_status && $r.cancel_status}
													Pruned
												{else}
													Cancelled{if $r.cancel_at_backend} <sup class="sup_cancel_at_backend" title="Cancel at backend">@backend</sup>{/if}
												{/if}
												{if $r.cancelled_by_u}
													<br /><span class="small" style="color:blue;">(by {$r.cancelled_by_u})</span>
												{/if}
											{/if}
										{else}
											{$r.prune_status}
										{/if}
									</td>
									<td class="r">{$r.amt|number_format:2}</td>
									<td class="r">{$r.qty|qty_nf}</td>
									<td class="r">{$r.commission_sales_amt|number_format:2}</td>
									<td class="r">{$r.commission_sales_qty|qty_nf}</td>
									{if $smarty.request.sa_id}
										<td class="r">{$r.sa_commission_amt|number_format:2}</td>
										{assign var=ttl_sa_commission_amt value=$ttl_sa_commission_amt+$r.sa_commission_amt}
									{/if}
									
									{*if $sessioninfo.show_cost}
										<td class="r">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
									{/if}
									{if $sessioninfo.show_report_gp}
										<td class="r">
											{assign var=sa_gp value=$r.amt-$r.cost}
											{$sa_gp|number_format:2}
										</td>
										<td class="r">
											{if $r.amt}
												{assign var=sa_gp_per value=$sa_gp/$r.amt*100}
											{else}
												{assign var=sa_gp_per value=0}
											{/if}
											{$sa_gp_per|number_format:2}
										</td>
									{/if*}
								</tr>
								{foreach from=$r.items item=item}
									<tr>
										<td colspan="5">&nbsp</td>
										<td>{$item.item_sa}</td>
										<td>{$item.arms_code}</td>
										<td>{if $item.mcode != ""}{$item.mcode}{else}&nbsp{/if}</td>
										<td>{$item.desc}</td>
										<td colspan="2">
											{if $item.use_ratio}
												Total Ratio: {$item.ttl_ratio|number_format:2}, S/A Ratio: {$item.ratio|number_format:2}
											{else}
												&nbsp;
											{/if}
										</td>
										<td align="right">{$item.amount|number_format:2}</td>
										<td align="right">{$item.qty}</td>
										<td align="right">{$item.commission_sales_amt|number_format:2|ifzero:'-'}</td>
										<td align="right">{$item.commission_sales_qty|qty_nf|ifzero:'-'}</td>
										{if $smarty.request.sa_id}
											<td class="r">{$item.sa_commission_amt|number_format:2|ifzero:'-'}</td>
										{/if}
									</tr>
								{/foreach}
								{assign var=ttl_amt value=$ttl_amt+$r.amt}
								{assign var=ttl_qty value=$ttl_qty+$r.qty}
								{assign var=ttl_cost value=$ttl_cost+$r.cost}
								{assign var=ttl_commission_sales_amt value=$ttl_commission_sales_amt+$r.commission_sales_amt}
								{assign var=ttl_commission_sales_qty value=$ttl_commission_sales_qty+$r.commission_sales_qty}
							{/foreach}
						</tbody>
					{foreachelse}
						<tr><td colspan="16" align="center">- No data -</td></tr>
					{/foreach}
					<tr class="header">
						<th class="r" colspan="11">Total</th>
						<th align="right">{$ttl_amt|number_format:2|ifzero:'-'}</th>
						<th align="right">{$ttl_qty|qty_nf|ifzero:'-'}</th>
						<th align="right">{$ttl_commission_sales_amt|number_format:2|ifzero:'-'}</th>
						<th align="right">{$ttl_commission_sales_qty|qty_nf|ifzero:'-'}</th>
						{if $smarty.request.sa_id}
							<th align="right">{$ttl_sa_commission_amt|number_format:2|ifzero:'-'}</th>
						{/if}
						{*if $sessioninfo.show_cost}
							<th align="right">{$ttl_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
						{/if}
						{if $sessioninfo.show_report_gp}
							<th align="right">
								{assign var=ttl_gp value=$ttl_amt-$ttl_cost}
								{$ttl_gp|number_format:2|ifzero:'-'}
							</th>
							<th align="right">
								{if $ttl_amt}
									{assign var=ttl_gp_per value=$ttl_gp/$ttl_amt*100}
								{else}
									{assign var=ttl_gp_per value=0}
								{/if}
								{$ttl_gp_per|number_format:2}%
							</th>
						{/if*}
					</tr>
				</tbody>
			</table>
		
			{if $range_table.$bid}
				{if !$table}<h2>{$report_title}</h2>{/if}
				<h2>Commission by Sales / Qty Range</h2>
				<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0">
					<tr class="header">
						<th width="40" rowspan="2">#</th>
						<th width="15%" rowspan="2">Sales Agent Code</th>
						<th rowspan="2">Sales Agent Name</th>
						<th colspan="3">Commission</th>
					</tr>
					<tr class="header">
						<th>Sales Amount</th>
						<th>Qty</th>
						<th>Amount</th>
					</tr>
					{foreach from=$range_table.$bid item=r key=sa_id name=rl}
						<tr>
							<td>{$smarty.foreach.rl.iteration}.</td>
							<td>{$r.code}</td>
							<td>{$r.name}</td>
							<td align="right">{$r.amt|number_format:2|ifzero:'-'}</td>
							<td align="right">{$r.qty|qty_nf|ifzero:'-'}</td>
							<td align="right">{$r.commission_amt|number_format:2|ifzero:'-'}</td>
						</tr>
					{/foreach}
					<tr class="header">
						<th colspan="3" align="right">Total</th>
						<th class="r">{$range_total.$bid.amt|number_format:2|ifzero:'-'}</th>
						<th class="r">{$range_total.$bid.qty|qty_nf|ifzero:'-'}</th>
						<th class="r">{$range_total.$bid.commission_amt|number_format:2|ifzero:'-'}</th>
					</tr>
				</table>
			{/if}
		{/foreach}
	{/if}
{/if}

{include file=footer.tpl}
