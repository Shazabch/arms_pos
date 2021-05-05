{*
4/5/2010 4:09:17 PM Andy
- Fix GRN after direct verified in amount check still show aprroval name bugs

10/12/2010 3:40:25 PM Justin
- Added FOC variance column.

8/15/2011 3:42:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only
*}

{if !$grn}
<p align=center>- no record -</p>
{else}
<div style="padding:4px;">
&#187; Enter the Invoice/DO Amount and No. for GRN below (only those applicable)
</div>

{$pagination}
<form method=post name=f_a onsubmit="return fcheck()">
<input type=hidden name=a value="verify">
<table class=tab cellspacing=1 cellpadding=4 border=0 width=100%>
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>GRN Date</th>
	<th>GRN No.</th>
	<th>GRN By</th>
	<th>GRR No.</th>
	<th>Department</th>
	<th>Vendor</th>
	<th>Doc Type</th>
	<th>Doc No</th>
	<th>Qty<br>Variance</th>
	<th>FOC<br>Variance</th>
	<th>Amount</th>
	<th>Invoice/DO No</th>
</tr>
<input type=hidden name=id[] value="0">
{section name=i loop=$grn}
<input type=hidden name=id[] value="{$grn[i].id}">
<tr bgcolor={cycle values="#ffffff,#eeeeee"}>
	<td>{$smarty.section.i.iteration}.</td>
	<td nowrap>{$grn[i].added|date_format:$config.dat_format}</td>
	<td>{$grn[i].id|string_format:"GRN%05d"}</td>
	<td align=center>{$grn[i].grn_u}</td>
	</td>
	<td title="{$grn[i].grr_id|string_format:"GRR%05d"}/{$grn[i].grr_item_id} (Receive date: {$grn[i].rcv_date|date_format:$config.dat_format})">{$grn[i].grr_id|string_format:"GRR%05d"}</td>
	<td>{$grn[i].department}</td>
	<td>{$grn[i].vendor}</td>
	<td>{$grn[i].type}</td>
	<td>{$grn[i].doc_no}</td>
	<th>
	{if $grn[i].type eq 'PO'}
		<font color=red>{$grn[i].have_variance|qty_nf|ifzero:"nil"}</font>
	{/if}
	</th>
	<th>
	{if $grn[i].type eq 'PO'}
		<font color=red>{$grn[i].foc_variance|qty_nf|ifzero:"nil"}</font>
	{/if}
	</th>
	{if $grn[i].is_approval}
	<td nowrap>
	<input class=r size=8 name="amount[{$grn[i].id}]" onchange="check_amount({$grn[i].id})" value="{$grn[i].account_amount}">
	<span id=as{$grn[i].id}></span>
	{if $grn[i].type eq 'PO'}<input type=hidden name="po_no[{$grn[i].id}]" value="{$grn[i].doc_no}">{/if}
	<input type=hidden name="grn_variance[{$grn[i].id}]" value="{$grn[i].have_variance}">
	<input type=hidden name="foc_variance[{$grn[i].id}]" value="{$grn[i].foc_variance}">
	<input type=hidden name="grn_amount[{$grn[i].id}]" value="{$grn[i].amount}">
	<input type=hidden name="approval_history_id[{$grn[i].id}]" value="{$grn[i].approval_history_id}" />
	<input type=hidden name="curr_date[{$grn[i].id}]" value="{$grn[i].added|date_format:$config.dat_format}" />
	</td>
	<td>
	<input size=10 name="account_doc_no[{$grn[i].id}]" value="{$grn[i].account_doc_no}" onchange="uc(this)">
	</td>
	{else}
	<td>&nbsp;</td><td>&nbsp;</td>
	{/if}
</tr>
{/section}
</table>
<p align=center><input type=submit value="Verify GRN" class="cbtn"></p>
</form>
{/if}
