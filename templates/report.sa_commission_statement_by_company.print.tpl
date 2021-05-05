{*
5/12/2014 5:13 PM Justin
- Enhanced to have total qty column.

6/29/2017 10:07 AM Justin
- Bug fixed on system will replace the previous receipt if having 2 same receipt number under 1 month.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

10/31/2019 2:19 PM Justin
- Bug fixed on subtotal showing twice in one Sales Agent section.

11/6/2019 4:22 PM Justin
- Enhanced to show "Commission by Sales / Qty Range".
*}
<!-- this is the print-out for approved but non-checkout DO -->
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}
.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}

{/literal}
</style>
<body onload="window.print()">
{/if}
<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url}" height="80" hspace="5" vspace="5">{else}&nbsp;{/if}</td>
	<td width=100%>
		<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
	</td>
	<td rowspan="2" align="right">
	    <table class="xlarge">
		<tr bgcolor="#cccccc" align="center"><td colspan="2">Statement of Commission</td></tr>
		<tr><td colspan="2">
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Date</td><td nowrap>{$month|str_month}-{$year}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>From Company</h4>
				<b>{$company_info.company_name|default:'Untitled Company'}</b>
			</td>
		</tr>
	</table>
	</td>
</tr>
</table>

{if $data}
	<br>

	<h2>Commission by Flat Rate</h2>
	<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

	<tr bgcolor=#cccccc>
		<th width="3%">#</th>
		<th width="7%">Date</th>
		<th width="10%">Document No</th>
		<th width="10%">Type</th>
		<th width="10%">Commission Sales <br />Amount</th>
		<th width="10%">Commission Sales <br />Qty</th>
		{if !$use_comm_ratio}
			{if $sessioninfo.show_cost}
				<th width="10%">Cost</th>
			{/if}
			{if $sessioninfo.show_report_gp}
				<th width="10%">GP</th>
				<th width="5%">GP(%)</th>
			{/if}
		{/if}
		<th width="10%">Total Commission<br />Amount</th>
		<th width="10%">Sales Amount <br /> After Commission</th>
		{if !$use_comm_ratio}
			<th width="10%">GP After <br />Comm. Amount</th>
			<th width="5%">GP(%) After <br />Commission</th>
		{/if}
	</tr>
	{assign var=counter value=0}
	{assign var=ttl_final_amount value=0}
	{assign var=ttl_cost value=0}
	{assign var=ttl_commission_amt value=0}
	{assign var=colspan value=8}
	{if !$use_comm_ratio}
		{assign var=colspan value=$colspan+5}
	{/if}
	{foreach from=$data key=sa_id item=day_list name=dd}
		{if !$smarty.foreach.dd.first}
			<tr class="header">
				<th class="r" colspan="4" align="right">Sub Total</th>
				<th align="right">{$sub_ttl_final_amount|number_format:2|ifzero:'-'}</th>
				<th align="right">{$sub_ttl_qty|qty_nf|ifzero:'-'}</th>
				{if !$use_comm_ratio}
					{if $sessioninfo.show_cost}
						<th align="right">{$sub_ttl_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
					{/if}
					{if $sessioninfo.show_report_gp}
						<th align="right">
							{assign var=sub_ttl_gp value=$sub_ttl_final_amount-$sub_ttl_cost}
							{$sub_ttl_gp|number_format:2|ifzero:'-'}
						</th>
						<th align="right">
							{if $sub_ttl_final_amount}
								{assign var=sub_ttl_gp_per value=$sub_ttl_gp/$sub_ttl_final_amount*100}
							{else}
								{assign var=sub_ttl_gp_per value=0}
							{/if}
							{$sub_ttl_gp_per|number_format:2}%
						</th>
					{/if}
				{/if}
				<th align="right">{$sub_ttl_commission_amt|number_format:2|ifzero:'-'}</th>
				<th align="right">{$sub_ttl_final_amount-$sub_ttl_commission_amt|number_format:2}</th>
				{if !$use_comm_ratio}
					<th align="right">
						{assign var=sub_ttl_gp_after_csm value=$sub_ttl_final_amount-$sub_ttl_commission_amt-$sub_ttl_cost}
						{$sub_ttl_gp_after_csm|number_format:2}
					</th>
					<th align="right">
						{if $sub_ttl_final_amount}
							{assign var=sub_ttl_gp_after_csm_per value=$sub_ttl_gp_after_csm/$sub_ttl_final_amount*100}
						{else}
							{assign var=sub_ttl_gp_after_csm_per value=0}
						{/if}
						{$sub_ttl_gp_after_csm_per|number_format:2}%
					</th>
				{/if}
			</tr>
			{assign var=sub_ttl_final_amount value=0}
			{assign var=sub_ttl_qty value=0}
			{assign var=sub_ttl_cost value=0}
			{assign var=sub_ttl_commission_amt value=0}
		{/if}
		<tr>
			<td colspan="{$colspan}">{$sa_info.$sa_id.code}{if $sa_info.$sa_id.name} - {$sa_info.$sa_id.name}{/if}</td>
		</tr>
		{assign var=row_count value=0}
		{foreach from=$day_list key=day item=doc_no_list name=mst}
			{foreach from=$doc_no_list key=doc_no item=sa name=dtl}
				<!-- {$row_count++} -->
				<tr bgcolor="#eeeeee">
					<td>{$row_count}.</td>
					<td>{$sa.date}</td>
					<td align="center">{$doc_no|default:'&nbsp;'}</td>
					<td align="center">{$sa.type}{if $sa.do_type} - {$sa.do_type}{/if}</td>
					<td align="right">{$sa.amt|number_format:2}</td>
					<td align="right">{$sa.qty|qty_nf}</td>
					{if !$use_comm_ratio}
						{if $sessioninfo.show_cost}
							<td class="r" align="right">{$sa.cost|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						{if $sessioninfo.show_report_gp}
							<td class="r" align="right">
								{assign var=sa_gp value=$sa.amt-$sa.cost}
								{$sa_gp|number_format:2}
							</td>
							<td class="r" align="right">
								{if $sa.amt}
									{assign var=sa_gp_per value=$sa_gp/$sa.amt*100}
								{else}
									{assign var=sa_gp_per value=0}
								{/if}
								{$sa_gp_per|number_format:2}
							</td>
						{/if}
					{/if}
					<td class="r" align="right">
						{if $comm_data.$doc_no.top_commission_amt}
							{assign var=commission_amt value=$sa.commission_amt*$comm_data.$doc_no.top_commission_amt/$comm_data.$doc_no.ttl_commission_amt}
						{else}
							{assign var=commission_amt value=0}
						{/if}
						{assign var=commission_amt value=$commission_amt|round:2}
						{$commission_amt|number_format:2}
					</td>
					<td class="r" align="right">{$sa.amt-$commission_amt|number_format:2}</td>
					{if !$use_comm_ratio}
						<td class="r" align="right">
							{assign var=sa_gp_after_csm value=$sa.amt-$commission_amt-$sa.cost}
							{$sa_gp_after_csm|number_format:2}
						</td>
						<td class="r" align="right">
							{if $sa.amt}
								{assign var=sa_gp_after_csm_per value=$sa_gp_after_csm/$sa.amt*100}
							{else}
								{assign var=sa_gp_after_csm_per value=0}
							{/if}
							{$sa_gp_after_csm_per|number_format:2}
						</td>
					{/if}
				</tr>
				{assign var=sub_ttl_final_amount value=$sub_ttl_final_amount+$sa.amt}
				{assign var=sub_ttl_qty value=$sub_ttl_qty+$sa.qty}
				{assign var=sub_ttl_cost value=$sub_ttl_cost+$sa.cost}
				{assign var=sub_ttl_commission_amt value=$sub_ttl_commission_amt+$commission_amt}
				{assign var=ttl_final_amount value=$ttl_final_amount+$sa.amt}
				{assign var=ttl_qty value=$ttl_qty+$sa.qty}
				{assign var=ttl_cost value=$ttl_cost+$sa.cost}
				{assign var=ttl_commission_amt value=$ttl_commission_amt+$commission_amt}
			{/foreach}
		{/foreach}
		{if $smarty.foreach.dd.last}
			<tr class="header">
				<th class="r" colspan="4" align="right">Sub Total</th>
				<th align="right">{$sub_ttl_final_amount|number_format:2|ifzero:'-'}</th>
				<th align="right">{$sub_ttl_qty|qty_nf|ifzero:'-'}</th>
				{if !$use_comm_ratio}
					{if $sessioninfo.show_cost}
						<th align="right">{$sub_ttl_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
					{/if}
					{if $sessioninfo.show_report_gp}
						<th align="right">
							{assign var=sub_ttl_gp value=$sub_ttl_final_amount-$sub_ttl_cost}
							{$sub_ttl_gp|number_format:2|ifzero:'-'}
						</th>
						<th align="right">
							{if $sub_ttl_final_amount}
								{assign var=sub_ttl_gp_per value=$sub_ttl_gp/$sub_ttl_final_amount*100}
							{else}
								{assign var=sub_ttl_gp_per value=0}
							{/if}
							{$sub_ttl_gp_per|number_format:2}%
						</th>
					{/if}
				{/if}
				<th align="right">{$sub_ttl_commission_amt|number_format:2|ifzero:'-'}</th>
				<th align="right">{$sub_ttl_final_amount-$sub_ttl_commission_amt|number_format:2}</th>
				{if !$use_comm_ratio}
					<th align="right">
						{assign var=sub_ttl_gp_after_csm value=$sub_ttl_final_amount-$sub_ttl_commission_amt-$sub_ttl_cost}
						{$sub_ttl_gp_after_csm|number_format:2}
					</th>
					<th align="right">
						{if $sub_ttl_final_amount}
							{assign var=sub_ttl_gp_after_csm_per value=$sub_ttl_gp_after_csm/$sub_ttl_final_amount*100}
						{else}
							{assign var=sub_ttl_gp_after_csm_per value=0}
						{/if}
						{$sub_ttl_gp_after_csm_per|number_format:2}%
					</th>
				{/if}
			</tr>
			{assign var=sub_ttl_final_amount value=0}
			{assign var=sub_ttl_qty value=0}
			{assign var=sub_ttl_cost value=0}
			{assign var=sub_ttl_commission_amt value=0}
		{/if}
	{foreachelse}
		<tr><td colspan="{$colspan}" align="center">- No Data -</td></tr>
	{/foreach}
	<tr class="header">
		<th class="r" colspan="4" align="right">Total</th>
		<th align="right">{$ttl_final_amount|number_format:2|ifzero:'-'}</th>
		<th align="right">{$ttl_qty|qty_nf|ifzero:'-'}</th>
		{if !$use_comm_ratio}
			{if $sessioninfo.show_cost}
				<th align="right">{$ttl_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
			{/if}
			{if $sessioninfo.show_report_gp}
				<th align="right">
					{assign var=ttl_gp value=$ttl_final_amount-$ttl_cost}
					{$ttl_gp|number_format:2|ifzero:'-'}
				</th>
				<th align="right">
					{if $ttl_final_amount}
						{assign var=ttl_gp_per value=$ttl_gp/$ttl_final_amount*100}
					{else}
						{assign var=ttl_gp_per value=0}
					{/if}
					{$ttl_gp_per|number_format:2}%
				</th>
			{/if}
		{/if}
		<th align="right">{$ttl_commission_amt|number_format:2|ifzero:'-'}</th>
		<th align="right">{$ttl_final_amount-$ttl_commission_amt|number_format:2|ifzero:'-'}</th>
		{if !$use_comm_ratio}
			<th align="right">
				{assign var=ttl_gp_after_csm value=$ttl_final_amount-$ttl_commission_amt-$ttl_cost}
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

{if $range_data}
	<br />
	<h2>Commission by Sales / Qty Range</h2>
	<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">
		<tr bgcolor="#cccccc">
			<th width="40" rowspan="2">#</th>
			<th width="15%" rowspan="2">Sales Agent Code</th>
			<th rowspan="2">Sales Agent Name</th>
			<th colspan="3">Commission</th>
		</tr>
		<tr bgcolor="#cccccc">
			<th>Sales Amount</th>
			<th>Qty</th>
			<th>Amount</th>
		</tr>
		{foreach from=$range_data item=r key=sa_id name=rl}
			<tr>
				<td>{$smarty.foreach.rl.iteration}.</td>
				<td>{$r.code}</td>
				<td>{$r.name}</td>
				<td align="right">{$r.amt|number_format:2|ifzero:'-'}</td>
				<td align="right">{$r.qty|qty_nf|ifzero:'-'}</td>
				<td align="right">{$r.commission_amt|number_format:2|ifzero:'-'}</td>
			</tr>
		{/foreach}
		<tr bgcolor="#cccccc">
			<th colspan="3" align="right">Total</th>
			<th align="right">{$range_total.amt|number_format:2|ifzero:'-'}</th>
			<th align="right">{$range_total.qty|qty_nf|ifzero:'-'}</th>
			<th align="right">{$range_total.commission_amt|number_format:2|ifzero:'-'}</th>
		</tr>
	</table>
{/if}

{if $aging_info}
<br />
<h4>Commission Aging</h4>
<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">

	<tr bgcolor="#cccccc" align="center">
		<td colspan="3">5 Months+</td>
		<td colspan="3">4 Months</td>
		<td colspan="3">3 Months</td>
		<td colspan="3">2 Months</td>
		<td colspan="3">1 Month</td>
		<td colspan="3">Current Month</td>
	</tr>
	<tr bgcolor="#cccccc" align="center">
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
		<td>Comm. Amt</td>
		<td>Sales Amt</td>
		<td>Sales Qty</td>
	</tr>
	<tr bgcolor="#eeeeee" align="right">
		<td>{$aging_info.5th_mth_above_comm_amt|number_format:2}</td>
		<td>{$aging_info.5th_mth_above_amt|number_format:2}</td>
		<td>{$aging_info.5th_mth_above_qty|qty_nf}</td>
		<td>{$aging_info.5th_mth_comm_amt|number_format:2}</td>
		<td>{$aging_info.5th_mth_amt|number_format:2}</td>
		<td>{$aging_info.5th_mth_qty|qty_nf}</td>
		<td>{$aging_info.4th_mth_comm_amt|number_format:2}</td>
		<td>{$aging_info.4th_mth_amt|number_format:2}</td>
		<td>{$aging_info.4th_mth_qty|qty_nf}</td>
		<td>{$aging_info.3rd_mth_comm_amt|number_format:2}</td>
		<td>{$aging_info.3rd_mth_amt|number_format:2}</td>
		<td>{$aging_info.3rd_mth_qty|qty_nf}</td>
		<td>{$aging_info.2nd_mth_comm_amt|number_format:2}</td>
		<td>{$aging_info.2nd_mth_amt|number_format:2}</td>
		<td>{$aging_info.2nd_mth_qty|qty_nf}</td>
		<td>{$aging_info.curr_mth_comm_amt|number_format:2}</td>
		<td>{$aging_info.curr_mth_amt|number_format:2}</td>
		<td>{$aging_info.curr_mth_qty|qty_nf}</td>
	</tr>
</table>
{/if}
</div>