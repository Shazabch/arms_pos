{* 
##REVISION HISOTRY##
8/27/2007 2:28:50 PM gary
- add control the diplay icon for cancelled grn.

12/28/2007 12:34:04 PM gary
- add print preview for grn performance report.

1/7/2008 11:53:59 AM gary
- dept and vendor header.

1/11/2008 11:44:19 AM gary
- all color to account personel header.

4/1/2010 3:51:29 PM Andy
- Fix Account GRN Verification cannot show full completion date bugs

10/12/2010 3:40:25 PM Justin
- Added FOC variance column.

6/13/2011 5:53:32 PM Justin
- Fixed the bugs where user still can edit canceled GRN when it is being canceled from GRR reset.

8/15/2011 3:42:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.
*}

{if !$grn}
<p align=center>- no record -</p>
{else}
<div class=small style="float:right;padding:4px;">
<img src="/ui/print.png"> Print GRN Summary &nbsp;&nbsp;
<img src="/ui/ed.png"> Undo GRN Correction confirmation &nbsp;&nbsp;
<img src="ui/view.png"> View GRN Detail
</div>
{$pagination}
<table class=tab cellspacing=1 cellpadding=4 border=0 width=100%>
<tr bgcolor=#ffee99>
	<th colspan=2>&nbsp;</th>
	<th>GRN Date</th>
	<th>GRN No.</th>
	<th>GRR No.</th>
	<th>Department</th>
	<th>Vendor</th>
	<th>Account<br>Personel</th>
	<th>Doc Type</th>
	<th>Doc No</th>
	<th>Qty<br>Variance</th>
	<th>FOC<br>Variance</th>
	<!--th>GRN Amount</th>
	<th>Account Amount</th-->
	<th>Approved GRN Amount</th>
	<th>Completion</th>
	<th>Print</th>
</tr>
<tbody>
{section name=i loop=$grn}
<tr bgcolor={cycle values="#ffffff,#eeeeee"}>
	<td>{$smarty.section.i.iteration+$smarty.request.s}.</td>
	<td nowrap>
		{if $grn[i].active eq '0'}
		<a href="?a=view_detail&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="ui/cancel.png" border=0></a>
		{elseif $grn[i].by_account==$sessioninfo.id}
		<a href="?a=confirm_detail&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="ui/ed.png" border=0 title=""></a>
		{else}
		<a href="?a=view_detail&id={$grn[i].id}&branch_id={$grn[i].branch_id}"><img src="ui/view.png" border=0></a>		
		{/if}
		{if $grn[i].active}
			<a href="javascript:void(do_print2({$grn[i].id},{$grn[i].branch_id}))"><img src="ui/print.png" border=0></a>		
			<a href="javascript:void(do_print_preview({$grn[i].id},{$grn[i].branch_id}))"><img src="ui/icons/report.png" title="Preview GRN Performance Report" border=0>
			</a>		
		{/if}
	</td>
	<td nowrap>{$grn[i].added|date_format:$config.dat_format}</td>
	<td>{$grn[i].id|string_format:"GRN%05d"}</td>
	<td title="{$grn[i].grr_id|string_format:"GRR%05d"}/{$grn[i].grr_item_id} (Receive date: {$grn[i].rcv_date|date_format:$config.dat_format})">{$grn[i].grr_id|string_format:"GRR%05d"}</td>
	<td>{$grn[i].department}</td>
	<td>{$grn[i].vendor}</td>
	<td align=center style="color:blue;">{$grn[i].acc_u}</td>
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
	<!--td align=right>{$grn[i].amount|number_format:2}</td>
	<td align=right>{$grn[i].account_amount|number_format:2}</td-->
	<td align=right>{$grn[i].final_amount|number_format:2}</td>
	<td nowrap>{$grn[i].last_update|date_format:"`$config.dat_format` %I:%M%p"}</td>
	<td align=center>{$grn[i].print_counter}</td>
</tr>
{/section}
</tbody>
</table>
{/if}
