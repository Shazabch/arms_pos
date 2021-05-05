{*
4/26/2013 4:20 PM Justin
- Added a notes to indicate that the commission amt does not rely on sales target.

5/13/2014 10:28 AM Justin
- Enhanced to have total qty column.

8/3/2015 1:29 PM Justin
- Bug fixed on system has grouped all same receipt into one date.

11/6/2019 4:22 PM Justin
- Removed date from and to selection, and replaced with Year and Month selection.
- Enhanced to show "Commission by Sales / Qty Range".

1/2/2020 2:25 PM Justin
- Bug fixed on smarty error shown while the commission amount is zero.
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
/*.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}*/

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
{literal}
function toggle_sac_details(counter, obj){
	if($("sac_detail_"+counter).style.display == "none"){
		obj.src = "/ui/collapse.gif";
		$("sac_detail_"+counter).style.display = "";
	}else{
		obj.src = "/ui/expand.gif";
		$("sac_detail_"+counter).style.display = "none";
	}
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
		<select name="branch_id">
		    <option value="">-- All --</option>
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
		    {/foreach}
		    {if $branch_group.header}
		        <optgroup label="Branch Group">
					{foreach from=$branch_group.header item=r}
					    {capture assign=bgid}bg,{$r.id}{/capture}
						<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
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
</p>
<p>
* View in maximum 1 month<br />
* Total Commission Amount does not depends on Sales Target.
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button name="a" value="show_report">{#SHOW_REPORT#}</button>
<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
</p>
</form>
{/if}

{if !$table && !$range_table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	{if $table}
		<h2>{$report_title}</h2>
		<h2>Commission by Flat Rate</h2>
		<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
			<tr class="header">
				<th>#</th>
				<th>Date</th>
				<th>Document No</th>
				<th>Type</th>
				<th>Sales <br />Amount</th>
				<th>Sales <br />Qty</th>
				{if !$use_comm_ratio}
					<th>GP</th>
					<th>GP(%)</th>
				{/if}
				<th>Total Commission<br />Amount</th>
				<th>Sales Amount <br /> After Commission</th>
				{if !$use_comm_ratio}
					<th>GP After <br />Comm. Amount</th>
					<th>GP(%) After <br />Commission</th>
				{/if}
			</tr>
			<tbody>
			{foreach from=$table item=doc_list key=date name=mst}
				{foreach from=$doc_list item=mst_field key=doc_no name=mst1}
					<!--{$counter++}-->
					<tr>
						<td>{$counter}. <img src="/ui/expand.gif" width="10" onclick="toggle_sac_details({$counter}, this);" title="Show Detail" class="clickable"></td>
						<td align="center">{$mst_field.date}</td>
						<td>{$doc_no}&nbsp;</td>
						<td align="center">{$mst_field.type}{if $mst_field.do_type} - {$mst_field.do_type}{/if}</td>
						<td class="r">{$mst_field.final_amount|number_format:2}</td>
						<td class="r">{$mst_field.qty|qty_nf}</td>
						{if !$use_comm_ratio}
							<td class="r">
								{assign var=gp value=$mst_field.final_amount-$mst_field.cost}
								{$gp|number_format:2}
							</td>
							<td class="r">
								{if $mst_field.final_amount}
									{assign var=gp_per value=$gp/$mst_field.final_amount*100}
								{else}
									{assign var=gp_per value=0}
								{/if}
								{$gp_per|number_format:2}
							</td>
						{/if}
						<td class="r">
							{if $mst_field.ttl_sa_commission_amt}
								{assign var=ttl_sa_commission_amt value=$mst_field.ttl_sa_commission_amt*$mst_field.top_commission_amt/$mst_field.ttl_commission_amt}
								{assign var=ttl_sa_commission_amt value=$ttl_sa_commission_amt|round:2}
								{$ttl_sa_commission_amt|number_format:2}
							{else}
								0.00
							{/if}
						</td>
						<td class="r">{$mst_field.final_amount-$ttl_sa_commission_amt|number_format:2}</td>
						{if !$use_comm_ratio}
							<td class="r">
								{assign var=gp_after_csm value=$mst_field.final_amount-$ttl_sa_commission_amt-$mst_field.cost}
								{$gp_after_csm|number_format:2}
							</td>
							<td class="r">
								{if $mst_field.final_amount}
									{assign var=gp_after_csm_per value=$gp_after_csm/$mst_field.final_amount*100}
								{else}
									{assign var=gp_after_csm_per value=0}
								{/if}
								{$gp_after_csm_per|number_format:2}
							</td>
						{/if}
					</tr>
					
					<tbody id="sac_detail_{$counter}" style="display:none;">
					{foreach from=$sac_table.$date.$doc_no item=dtl_field key=sa_id name=dtl}
						<tr bgcolor="#eeeeee">
							<td>&nbsp;</td>
							<td colspan="3">{$dtl_field.code} - {$dtl_field.name}</td>
							<td class="r">{$dtl_field.amt|number_format:2}</td>
							<td class="r">{$dtl_field.qty|qty_nf}</td>
							{if !$use_comm_ratio}
								<td class="r">
									{assign var=sa_gp value=$dtl_field.amt-$dtl_field.cost}
									{$sa_gp|number_format:2}
								</td>
								<td class="r">
									{if $dtl_field.amt}
										{assign var=sa_gp_per value=$sa_gp/$dtl_field.amt*100}
									{else}
										{assign var=sa_gp_per value=0}
									{/if}
									{$sa_gp_per|number_format:2}
								</td>
							{/if}
							<td class="r">
								{if $dtl_field.commission_amt}
									{assign var=commission_amt value=$dtl_field.commission_amt*$mst_field.top_commission_amt/$mst_field.ttl_commission_amt}
									{assign var=commission_amt value=$commission_amt|round:2}
									{$commission_amt|number_format:2}
								{else}
									0.00
								{/if}
							</td>
							<td class="r">{$dtl_field.amt-$commission_amt|number_format:2}</td>
							{if !$use_comm_ratio}
								<td class="r">
									{assign var=sa_gp_after_csm value=$dtl_field.amt-$commission_amt-$dtl_field.cost}
									{$sa_gp_after_csm|number_format:2}
								</td>
								<td class="r">
									{if $dtl_field.amt}
										{assign var=sa_gp_after_csm_per value=$sa_gp_after_csm/$dtl_field.amt*100}
									{else}
										{assign var=sa_gp_after_csm_per value=0}
									{/if}
									{$sa_gp_after_csm_per|number_format:2}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
					{assign var=ttl_final_amount value=$ttl_final_amount+$mst_field.final_amount}
					{assign var=ttl_qty value=$ttl_qty+$mst_field.qty}
					{assign var=ttl_cost value=$ttl_cost+$mst_field.cost}
					{assign var=ttl_top_commission_amt value=$ttl_top_commission_amt+$ttl_sa_commission_amt}
					{assign var=ttl_commission_amt value=$ttl_commission_amt+$mst_field.ttl_commission_amt}
				{/foreach}
			{/foreach}
			<tr class="header">
				<th class="r" colspan="4">Total</th>
				<th align="right">{$ttl_final_amount|number_format:2|ifzero:'-'}</th>
				<th align="right">{$ttl_qty|qty_nf|ifzero:'-'}</th>
				{if !$use_comm_ratio}
					<th align="right">
						{assign var=ttl_gp value=$ttl_final_amount-$ttl_cost}
						{$ttl_gp|number_format:2|ifzero:'-'}
					</th>
					<th align="right">
						{if $mst_field.final_amount}
							{assign var=ttl_gp_per value=$ttl_gp/$ttl_final_amount*100}
						{else}
							{assign var=ttl_gp_per value=0}
						{/if}
						{$ttl_gp_per|number_format:2}%
					</th>
				{/if}
				<th align="right">{$ttl_top_commission_amt|number_format:2|ifzero:'-'}</th>
				<th align="right">{$ttl_final_amount-$ttl_top_commission_amt|number_format:2}</th>
				{if !$use_comm_ratio}
					<th align="right">
						{assign var=ttl_gp_after_csm value=$ttl_final_amount-$ttl_top_commission_amt-$ttl_cost}
						{$ttl_gp_after_csm|number_format:2}
					</th>
					<th align="right">
						{if $ttl_final_amount}
							{assign var=ttl_gp_after_csm_per value=$ttl_gp_after_csm/$ttl_final_amount*100}
						{else}
							{assign var=ttl_gp_after_csm_per value=0}
						{/if}
						{$ttl_gp_after_csm_per|number_format:2}%
					</th>
				{/if}
			</tr>
		</table>
	{/if}
	
	{if $range_table}
		{if !$table}<h2>{$report_title}</h2>{/if}
		<h2>Commission by Sales / Qty Range</h2>
		<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0">
			<tr class="header">
				<th width="40" rowspan="2">#</th>
				<th colspan="3">Commission</th>
			</tr>
			<tr class="header">
				<th>Sales Amount</th>
				<th>Qty</th>
				<th>Amount</th>
			</tr>
			{foreach from=$range_table item=r key=sa_id name=rl}
				<tr>
					<td>{$smarty.foreach.rl.iteration}.</td>
					<td align="right">{$r.amt|number_format:2|ifzero:'-'}</td>
					<td align="right">{$r.qty|qty_nf|ifzero:'-'}</td>
					<td align="right">{$r.commission_amt|number_format:2|ifzero:'-'}</td>
				</tr>
			{/foreach}
			<tr class="header">
				<th align="right">Total</th>
				<th align="right">{$range_total.amt|number_format:2|ifzero:'-'}</th>
				<th align="right">{$range_total.qty|qty_nf|ifzero:'-'}</th>
				<th align="right">{$range_total.commission_amt|number_format:2|ifzero:'-'}</th>
			</tr>
		</table>
	{/if}
{/if}

{include file=footer.tpl}
