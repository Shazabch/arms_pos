{*
REVISION HISTORY
=================
1/11/2008 11:44:19 AM gary
- all color to account personel header.

10/12/2010 3:40:25 PM Justin
- Added FOC variance column.

8/15/2011 3:42:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.
*}

{if !$grn}
<p align=center>- no record -</p>
{else}
<div class=small style="float:right;padding:4px;">
<img src="/ui/print.png"> Print GRN Correction Sheet &nbsp;&nbsp;
<img src="/ui/ed.png"> Edit Invoice/DO Detail &nbsp;&nbsp;
<img src="/ui/table_add.png"> Enter GRN Correction detail
<img src="ui/view.png"> View GRN Detail
</div>
{$pagination}

<table cellspacing=1 cellpadding=4 border=0 width=100% style="clear:both;padding:2px;">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>Checked Date</th>
	<th>GRN No.</th>
	<th>GRR No.</th>
	<th>Department</th>
	<th>Vendor</th>
	<th>Account<br>Personel</th>
	<th>Doc Type</th>
	<th>Doc No</th>
	<th>GRN Amount</th>
	<th>Invoice/DO No</th>
	<th>Invoice/DO<br>Amount</th>
	<th>Qty<br>Variance</th>
	<th>FOC<br>Variance</th>
</tr>
<tbody>
{section name=i loop=$grn}
<tr bgcolor={cycle values="#ffffff,#eeeeee"}>
	<td>{$smarty.section.i.iteration+$smarty.request.s}.</td>
	<td nowrap>
		<a href="javascript:void(do_print({$grn[i].id},{$grn[i].branch_id}))"><img src="ui/print.png" border=0 title="Print GRN Correction Sheet"></a>
		{if $grn[i].by_account==$sessioninfo.id}
			<a href="?a=undo_acc&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="ui/ed.png" border=0 title="Edit Invoice/DO Amount and No"></a>
			<a href="?a=confirm_detail&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="/ui/table_add.png" border=0 title="Enter GRN Correction detail"></a>
			<a href="javascript:void(grn_chown({$grn[i].id},{$grn[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
		{else}
			<a href="?a=view_detail&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="/ui/view.png" border=0 title="View detail"></a>
		{/if}
	</td>
	<td nowrap>{$grn[i].account_update|date_format:$config.dat_format}</td>
	<td>{$grn[i].id|string_format:"GRN%05d"}</td>
	<td title="{$grn[i].grr_id|string_format:"GRR%05d"}/{$grn[i].grr_item_id} (Receive date: {$grn[i].rcv_date|date_format:$config.dat_format})">{$grn[i].grr_id|string_format:"GRR%05d"}</td>

	<td>{$grn[i].department}</td>
	<td>{$grn[i].vendor}</td>
	<td align=center style="color:blue;">{$grn[i].acc_u}</td>
	<td>{$grn[i].type}</td>
	<td>{$grn[i].doc_no}</td>
	<td align=right><font color=blue>{$grn[i].amount|number_format:2}</font></td>
	<td align=center>{$grn[i].account_doc_no}</td>
	<td align=right><font color=blue>{$grn[i].account_amount|number_format:2}</font></td>
	<th><font color=red>
	{if $grn[i].type eq 'PO'}
		<font color=red>{$grn[i].have_variance|qty_nf|ifzero:"nil"}</font>
	{/if}
	</th>
	<th><font color=red>
	{if $grn[i].type eq 'PO'}
		<font color=red>{$grn[i].foc_variance|qty_nf|ifzero:"nil"}</font>
	{/if}
	</th>
</tr>
{/section}
</tbody>
</table>
{/if}
