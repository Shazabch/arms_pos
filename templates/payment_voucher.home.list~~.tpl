{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0 id=tbl_list>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>PV Ref. No.</th>
	<th>Supplier/Issue Name</th>
	<th>Voucher Type</th>
	<th>Banker</th>
	{if $status.completed}
	<th>Cheque No</th>
	{/if}
	{if BRANCH_CODE eq 'HQ'}
	<th>Branch</th>
	{/if}
	<th>Total (RM)</th>
	<th>Payment Date</th>
	<th>Last Update</th>
	{if $status.cancel}
	<th>Reason</th>	
	{/if}
</tr>

{section name=i loop=$list}
<tr>
	<td align=left width=100>
	{if $list[i].status ne '0'}
		{if $list[i].status eq '1'}
			{if BRANCH_CODE eq 'HQ'}
				{if ($list[i].user_id eq $sessioninfo.id || $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT)}
					<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/ed.png border=0 title="Edit Voucher">
					</a>
					<img src=/ui/del.png border=0 title="Cancel Payment Voucher" onclick="do_cancel('{$list[i].id}','{$list[i].branch_id}')">
				{else}
					<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/view.png border=0 title="View Voucher">
					</a>
				{/if}			
			{else}
				{if ($list[i].voucher_branch_id == $sessioninfo.branch_id) && $list[i].user_id eq $sessioninfo.id}
					<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/ed.png border=0 title="Edit Voucher">
					</a>
					<img src=/ui/del.png border=0 title="Cancel Payment Voucher" onclick="do_cancel('{$list[i].id}','{$list[i].branch_id}')">
				{else}
					<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/view.png border=0 title="View Voucher">
					</a>
				{/if}			
			{/if}
		{else}
			<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
			<img src=/ui/view.png border=0 title="View Voucher"></a>
			{if (($list[i].user_id eq $sessioninfo.id || $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT) && BRANCH_CODE eq 'HQ') || (($list[i].user_id eq $sessioninfo.id || $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT) && $list[i].status eq '2' && $list[i].log_sheet_status<2)}
				<img src=/ui/del.png border=0 title="Cancel Payment Voucher" onclick="do_cancel('{$list[i].id}','{$list[i].branch_id}')">			
			{/if}	
		{/if}
		
		{if BRANCH_CODE eq 'HQ'}
		{assign var=vb value=$list[i].voucher_branch_id}	
		{else}
		{assign var=vb value=$sessioninfo.branch_id}	
		{/if}
		
		{if $list[i].status}
		<img src=/ui/print.png border=0 title="Print Payment Voucher" onclick="do_print('{$list[i].id}','{$list[i].branch_id}','{$vb}');list_sel(1);">
		{/if}
		
		{if BRANCH_CODE eq 'HQ' && $list[i].status>1}
		<img src=/ui/icons/script.png border=0 title="Print Cheque" onclick="do_print_cheque('{$list[i].id}','{$list[i].branch_id}', '{$vb}','{$list[i].vvc_code}','print','{$list[i].voucher_no}','{$list[i].status}')">
		{/if}
		
		<!--
		{if BRANCH_CODE eq 'HQ' && $list[i].status eq '3'}
		<img src=/ui/icons/report_edit.png border=0 title="Edit Cheque No" onclick="do_print_cheque('{$list[i].id}','{$list[i].branch_id}', '{$vb}','{$list[i].vvc_code}','edit','{$list[i].voucher_no}','{$list[i].status}')">
		{/if}
		-->
			
	{else}
		<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
		<img src=/ui/rejected.png border=0 title="Open Cancelled Voucher"></a>	
	{/if}	
	</td>
	
	
	<td align=center>{$list[i].voucher_no}</td>
	<td>{$list[i].vendor|default:$list[i].issue_name}</td>
	<td>
	{if $list[i].voucher_type eq '1'}
	Normal {if $list[i].urgent}(Urgent){/if}
	{elseif $list[i].voucher_type eq '2'}
	Fast Payment
	{elseif $list[i].voucher_type eq '3'}
	Different Cheque Issue Name
	{elseif $list[i].voucher_type eq '4'}
	Blank Sheet
	{/if}
	</td>
	<td>{$list[i].banker}</td>
	{if $list[i].status eq '3'}
		<td>{$list[i].cheque_no|default:"-"}</td>	
	{/if}
	{if BRANCH_CODE eq 'HQ'}
	<td align=center>{$list[i].branch}</td>
	{/if}
	<td align=right>{$list[i].total_credit-$list[i].total_debit|number_format:2}</td>
	<td nowrap align=center>{$list[i].payment_date|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
	{if $list[i].status eq '0'}
	<td>{$list[i].cancelled_reason}</td>	
	{/if}
</tr>
{/section}
</table>
<script>
ts_makeSortable($('tbl_list'));
</script>
{/if}
