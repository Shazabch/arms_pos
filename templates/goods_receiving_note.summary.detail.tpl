{*
Revision History
================
4/20/07 5:29:47 PM yinsee
- add GRN total_selling

9/27/2007 12:42:23 PM gary
- add mark-on column.

10/1/2007 5:00:53 PM gary
-add show details when printing.

3/5/2008 11:29:26 AM gary
- change old po link to new po link.

3/21/2011 11:10:37 AM Alex
- add link to print preview GRN performance

6/24/2011 6:11:14 PM Justin
- Modified to show multiple documents with different link while found GRN is created from GRN Future.

6/29/2011 9:47:21 AM Justin
- Added no record row when found empty record from database.

12/15/2011 10:07:43 AM Justin
- Removed the "authorized=1" checking since it is not usable for 1st verson of GRN.

7/2/2012 3:03 PM Andy
- Add to show "Vendor Code".

7/17/2012 2:59 PM Justin
- Added "Account ID" column and available when config is found.

7/24/2012 10:35 AM Andy
- Add print and export excel function.

7/24/2012 11:21 AM Justin
- Moved the "Account ID" to put after Vendor Code.

8/23/2012 5:35 PM Justin
- Added to pickup branch code.
- Enhanced to ignore show doc no while itself is INVOICE and show the doc no on related invoice column.

9/7/2012 9:59 AM Justin
- Enhanced to show Completed Date column.

11/18/2014 4:42 PM Justin
- Enhanced to have GST Info.

4/25/2018 10:50 AM Justin
- Enhanced to show foreign currency.

9/7/2018 4:42 PM Justin
- Enhanced to load GST information base on cost instead of selling price.

4/19/2019 3:37 PM Justin
- Bug fixed on system shows empty info for Branch and Department while it is not being filtered.

5/16/2019 10:27 AM William
- Enhance "GRR" and "GRN" to use "report_prefix" value.

8/26/2019 3:05 PM Andy
- Fixed ordering number wrong if filter by all branch.
*}

<div class="noscreen">
<h3>
Branch :
{if $BRANCH_CODE eq 'HQ'}
	{if $smarty.request.branch_id}
		{section name=i loop=$branch}
			{if $smarty.request.branch_id eq $branch[i].id}{$branch[i].code}{/if}
		{/section}
	{else}
		All
	{/if}
{else}
	{$BRANCH_CODE}
{/if}&nbsp;&nbsp;&nbsp;

Department : 
{if $smarty.request.department_id}
	{section name=i loop=$dept}
		{if $smarty.request.department_id eq $dept[i].id}{$dept[i].description}{/if}
	{/section}
{else}
	All
{/if}&nbsp;&nbsp;&nbsp;

GRN Date : {$smarty.request.from} - {$smarty.request.to}
</h3>
</div>
<br>
<table id=tbl_grn border=0 cellspacing=1 cellpadding=4 style="padding:1px;border:1px solid #000" width=100% class=sortable>
{assign var=nr_colspan value=12}
<tr bgcolor="#ffee99">
	<th>&nbsp;</th>
	<th>GRN</th>
	<th>Branch</th>
	<th>GRN Date</th>
	<th>GRR</th>
	<th>GRR Received</th>
	<th nowrap>Doc<br>Type</th>
	<th nowrap>Doc No.</th>
	{if $config.grn_summary_show_related_invoice}
	<th width="100">Related<br>Invoice</th>
		{assign var=nr_colspan value=$nr_colspan+1}
	{/if}
	<th>Vendor Code</th>
	{if $config.enable_vendor_account_id}
	<th width="100">Account<br>ID</th>
		{assign var=nr_colspan value=$nr_colspan+1}
	{/if}
	<th width="40%">Vendor</th>
	<th>Total<br>Selling ({$config.arms_currency.symbol})</th>
	{if $sessioninfo.show_cost}
		{if $have_fc}
			<th>Foreign Amount</th>
			<th>Exchange Rate</th>
		{/if}
		<th>GRN<br>Amount ({$config.arms_currency.symbol})</th>
		{if $is_under_gst}
			<th>GST ({$config.arms_currency.symbol})</th>
			<th nowrap>GRN Amount<br />Incl. GST ({$config.arms_currency.symbol})</th>
		{/if}
		{assign var=nr_colspan value=$nr_colspan+1}
	{/if}
	{if $sessioninfo.show_report_gp}
		<th>GP (%)</th>
		{assign var=nr_colspan value=$nr_colspan+1}
	{/if}
	<th>Status</th>
	<th width=20%>Account Action</th>
	<th>Completed<br />Date</th>
</tr>
{if count($grn) eq 0}
	<tr><td colspan="{$nr_colspan}" align="center">No Record Found</td></tr>
{/if}

{assign var=row_num value=0}
{foreach from=$grn name=tmp key=grn_id item=bid_list}
	{foreach from=$bid_list name=i key=bid item=r}
		{assign var=row_num value=$row_num+1}
		<tbody id="tbl_grn">
		<tr bgcolor="{cycle values='#ffffff,#eeeeee'}">
		<td>{$row_num}.</a></td>
		<td>{if $r.active==1 && $r.status==1 && $r.approved==1 && !$no_header_footer}
				<a href="javascript:void(do_print_preview({$r.id},{$r.branch_id}))">{$r.report_prefix}{$r.id|string_format:"%05d"}</a>
			{else}
				{$r.report_prefix}{$r.id|string_format:"%05d"}
			{/if}
		</td>
		<td align="center">{$r.branch_code}</td>
		<td>{$r.added|date_format:$config.dat_format}</td>
		<td>{$r.report_prefix}{$r.grr_id|string_format:"%05d"}{if !$r.is_future}/{$r.grr_item_id}{/if}</td>
		<td>{$r.rcv_date|date_format:$config.dat_format}</td>
		<td>{$r.doc_type}</td>

		<td nowrap>
		{if $r.doc_type eq 'PO'}
		{*
		<a href="/purchase_order.php?a=view&id={$r.po_id}&branch_id={$r.po_bid}" target="_blank">
		*}
			{if !$r.is_future}
				<a href="/po.php?a=view&id={$r.po_id}&branch_id={$r.po_bid}" target="_blank">{$r.doc_no}</a>
			{else}
				{foreach from=$r.doc_no key=i item=doc_no name=doc_item}
					<a href="/po.php?a=view&id={$r.set_po_id.$i}&branch_id={$r.set_po_bid.$i}" target="_blank">{$doc_no}</a>
					{if !$smarty.foreach.doc_item.last},{/if}
				{/foreach}
			{/if}
		{elseif $r.doc_type eq 'DO' && $r.do_id}
			{if !$r.is_future}
				<a href="/do.php?a=view&do_no={$r.doc_no}" target="_blank">{$r.doc_no}</a>
			{else}
				{foreach from=$r.doc_no key=i item=doc_no name=doc_item}
					<a href="/do.php?a=view&do_no={$doc_no}" target="_blank">{$doc_no}</a>
					{if !$smarty.foreach.doc_item.last},{/if}
				{/foreach}
			{/if}
		{else}
			{if (!$config.grn_summary_show_related_invoice && $r.doc_type eq "INVOICE") || $r.doc_type ne "INVOICE"}
				{$r.doc_no}
			{else}
				&nbsp;
			{/if}
		{/if}
		</td>

		{if $config.grn_summary_show_related_invoice}
			<td width="100">{$r.related_invoice}</td>
		{/if}

		<td>{$r.vendor_code}</td>
		{if $config.enable_vendor_account_id}
			<td>{$r.account_id}</td>
		{/if}
		<td>{$r.vendor}</td>
		<td align="right">{$r.total_selling|number_format:2}</td>
		{if $sessioninfo.show_cost}
			{if $r.currency_code}
				<td align="right" nowrap>{$r.currency_code} {$total.itemise.$grn_id.$bid.foreign_amt|number_format:2}</td>
				<td align="right">{$r.currency_rate}</td>
			{else}
				{if $have_fc}
					<td align="right">-</td>
					<td align="right">-</td>
				{/if}
			{/if}
			<td align="right" {if $r.currency_code}class="converted_base_amt"{/if}>{$total.itemise.$grn_id.$bid.final_amount|number_format:2}{if $r.currency_code}*{/if}</td>
			{if $is_under_gst}
				<td align="right">{$total.itemise.$grn_id.$bid.total_gst|number_format:2}</td>
				<td align="right" {if $r.currency_code}class="converted_base_amt"{/if}>{$total.itemise.$grn_id.$bid.total_gst_amount|number_format:2}{if $r.currency_code}*{/if}</td>
			{/if}
		{/if}
		{if $sessioninfo.show_report_gp}
			{if $total.itemise.$grn_id.$bid.total_selling>0}
				{assign var=m value=$total.itemise.$grn_id.$bid.total_selling-$total.itemise.$grn_id.$bid.final_amount}
				{assign var=m value=$m/$total.itemise.$grn_id.$bid.total_selling*100}
			{/if}
			<td align="right">{$m|number_format:2}</td>
		{/if}
		<td align="center" nowrap>
			{if $r.approved}
				<img src="/ui/approved.png" align="absmiddle"> Approved
			{else}
				<font color="blue">Verifying</font>
			{/if}
		</td>
		<td>{$r.acc_action}</td>
		<td align="center">{$r.last_update}</td>
		</tr>
	{/foreach}
{/foreach}

<tr bgcolor="#ffee99" class="sortbottom">
{if $config.grn_summary_show_related_invoice}
{assign var=cols value=11}
{else}
{assign var=cols value=10}
{/if}

{if $config.enable_vendor_account_id}
	{assign var=cols value=$cols+1}
{/if}

<td colspan="{$cols}" align="right"><b>Total</b></th>
<td align="right">{$total.total_selling|number_format:2}</td>
{if $sessioninfo.show_cost}
	{if $have_fc}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	<td align="right" {if $have_fc}class="converted_base_amt"{/if}>{$total.final_amount|number_format:2}{if $have_fc}*{/if}</td>
	{if $is_under_gst}
		<td align="right">{$total.total_gst|number_format:2}</td>
		<td align="right" {if $have_fc}class="converted_base_amt"{/if}>{$total.total_gst_amount|number_format:2}{if $have_fc}*{/if}</td>
	{/if}
{/if}
{if $sessioninfo.show_report_gp}
	{if $total.total_selling>0}
		{assign var=t value=$total.total_selling-$total.final_amount}
		{assign var=t value=$t/$total.total_selling*100}
	{/if}
	<td align="right">{$t|number_format:2}</td>
{/if}
<td colspan="2">&nbsp;</td>
<td colspan="2">&nbsp;</td>
</tr>

</table>
<script>
ts_makeSortable($('tbl_grn'));
</script>
