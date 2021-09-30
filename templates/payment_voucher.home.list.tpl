{*
4/19/2017 11:32 AM Khausalya 
- Enhanced changes from RM to use config setting. 
*}


{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<div class="table-responsive">
	<table class="report_table table mb-0 text-md-nowrap  table-hover" id="tbl_list">
	<thead class="bg-gray-100">
		<tr class="" height=24>
			<th>&nbsp;</th>
			<th>PV Ref No. /<br>Log Sheet No.</th>
			<th>Supplier/Issue Name</th>
			<th>Voucher Type</th>
			<th>Banker</th>
			{*if $status.completed*}
			<th>Cheque No</th>
			{*/if*}
			{if BRANCH_CODE eq 'HQ'}
			<th>Branch</th>
			{/if}
			<th>Total ({$config.arms_currency.symbol})</th>
			<th>Payment Date</th>
			<th>Last Update</th>
			<!--
			{if $status.cancel}
			<th>Reason</th>	
			{/if}
			-->
		</tr>
	</thead>
		
		{section name=i loop=$list}
		<tbody class="fs-08">
			<tr>
				<td align=left width=80>
				{if $list[i].status ne '0'}
					{if $list[i].log_sheet_status<3}
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
							{if ($list[i].voucher_branch_id == $sessioninfo.branch_id) && $list[i].user_id eq $sessioninfo.id }
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
						{if (($list[i].user_id eq $sessioninfo.id || $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT) && BRANCH_CODE eq 'HQ') || ($list[i].user_id eq $sessioninfo.id || $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT)}
							<img src=/ui/del.png border=0 title="Cancel Payment Voucher" onclick="do_cancel('{$list[i].id}','{$list[i].branch_id}')">			
						{/if}	
					{/if}
					
					{if BRANCH_CODE eq 'HQ'}
					{assign var=vb value=$list[i].voucher_branch_id}	
					{else}
					{assign var=vb value=$sessioninfo.branch_id}
					{/if}
					
					{if ($list[i].status && BRANCH_CODE eq 'HQ') || ($list[i].status && $list[i].voucher_type ne '2' && BRANCH_CODE != 'HQ')}
					<img src=/ui/print.png border=0 title="Print Payment Voucher" onclick="do_print('{$list[i].id}','{$list[i].branch_id}','{$vb}');list_sel(1);">
					{/if}
					
					{if BRANCH_CODE eq 'HQ' && $list[i].status>1 && $list[i].voucher_type ne '5'}
					<img src=/ui/icons/script.png border=0 title="Print Cheque" onclick="do_print_cheque('{$list[i].id}','{$list[i].branch_id}', '{$vb}','{$list[i].vvc_code}','print','{$list[i].voucher_no}','{$list[i].status}')">
					{/if}			
				{else}
					<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/cancel.png border=0 title="Open Cancelled Voucher"></a>
				{/if}	
				</td>
				
				
				<td>{$list[i].voucher_no}{if $list[i].voucher_type eq '5'}(BA){/if} /
					<br>{$list[i].log_sheet_no|default:"-"}
				</td>
				
				<td>
				{if $list[i].voucher_type eq '3'}
				{$list[i].issue_name} /<br>{$list[i].vendor}
				{else}
				{$list[i].vendor|default:$list[i].issue_name}
				{/if}
				</td>
				
				<td>
				{if $list[i].voucher_type eq '1'}
				Normal {if $list[i].urgent}(Urgent){/if}
				{elseif $list[i].voucher_type eq '2'}
				Fast Payment
				{elseif $list[i].voucher_type eq '3'}
				Different Cheque Issue Name
				{elseif $list[i].voucher_type eq '4'}
				Blank Sheet
				{elseif $list[i].voucher_type eq '5'}
				BA
				{/if}
				</td>
				<td>{$list[i].banker}</td>
				{*if $list[i].status eq '3'*}
					<td align=right>{$list[i].cheque_no|default:"-"}</td>	
				{*/if*}
				{if BRANCH_CODE eq 'HQ'}
				<td align=center>{$list[i].branch}</td>
				{/if}
				<td align=right>{$list[i].total_credit-$list[i].total_debit|number_format:2}</td>
				<td nowrap align=center>{$list[i].payment_date|date_format:$config.dat_format}</td>
				<td nowrap align=center>{$list[i].last_update}</td>
				<!--
				{if $list[i].status eq '0'}
				<td>{$list[i].cancelled_reason}</td>	
				{/if}
				-->
			</tr>
		</tbody>
		{/section}
		</table>
</div>
<script>
ts_makeSortable($('tbl_list'));
</script>
{/if}
