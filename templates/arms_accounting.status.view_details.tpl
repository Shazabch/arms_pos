{*
4/17/2019 3:19 PM Andy
- Added column "Tran ID" for Cash Sales Details.

5/20/2019 1:40 PM Andy
- Added Account Receivable Integration.
*}
{include file='header.tpl'}

<style>

{literal}
.status_color-2{
	color:blue;
}
.status_color-3{
	color:red;
}
{/literal}
</style>

<h1>ARMS Accounting Integration - Batch #{$batch_info.batch_id}</h1>

<table width="100%" class="report_table">
	<tr>
		<td class="col_header">Batch No</td>
		<td>{$batch_info.batch_id}</td>
		<td class="col_header">Status</td>
		<td>
			<span class="status_color-{$batch_info.status}">
				{$status_desc[$batch_info.status]}
			</span>
		</td>
	</tr>
	
	<tr>
		<td class="col_header">Created at</td>
		<td>{$batch_info.added}</td>
		<td class="col_header">Last Updated at</td>
		<td>{$batch_info.last_update}</td>
	</tr>
	
	<tr>
		<td class="col_header">Tax Amount</td>
		<td>{$batch_info.tax|number_format:2}</td>
		<td class="col_header">Total Amount Incl. Tax</td>
		<td>{$batch_info.amount|number_format:2}</td>
	</tr>
</table>

<br />
<h3>Data Contains:</h3>

<table width="100%" class="report_table">
	<tr class="header">
		<th width="30">&nbsp;</th>
		{if $acc_type eq 'cs'}
			<th>Doc Type</th>
		{/if}
		
		<th>
			{if $acc_type eq 'ap'}
				GRR ID
			{elseif $acc_type eq 'cs'}
				Invoice No.
			{elseif $acc_type eq 'ar'}
				DO ID
			{/if}
		</th>
		
		{if $acc_type eq 'cs'}
			<th>Tran ID</th>
		{/if}
		<th>Status</th>
		<th>Tax</th>
		<th>Amount Incl Tax</th>
		<th>Last Update</th>
		<th>Accounting Doc. No</th>
	</tr>
	
	{foreach from=$inv_list item=inv_data name=finv}
		<tr>
			<td>{$smarty.foreach.finv.iteration}</td>
			{if $acc_type eq 'cs'}
				<td nowrap>
					{if $inv_data.type eq 'pos'}
						POS
					{elseif $inv_data.type eq 'do'}
						Cash Sales DO
					{/if}
				</td>
			{/if}
			
			<td>
				{if $acc_type eq 'ap'}
					<a href="goods_receiving_record.php?a=view&id={$inv_data.grr_id}&branch_id={$inv_data.branch_id}" target="_blank">
						{$inv_data.grr_id}
					</a>
				{elseif $acc_type eq 'cs'}
					{if $inv_data.type eq 'pos'}
						<a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$inv_data.inv_no}'));">
							{$inv_data.inv_no}
						</a>
					{elseif $inv_data.type eq 'do'}
						<a href="do.php?a=view&branch_id={$inv_data.branch_id}&id={$inv_data.do_id}" target="_blank">
							{$inv_data.inv_no}
						</a>
					{/if}
				{elseif $acc_type eq 'ar'}
					<a href="do.php?a=view&id={$inv_data.do_id}&branch_id={$inv_data.branch_id}" target="_blank">
						{$inv_data.do_id}
					</a>
				{/if}
			</td>
			
			{if $acc_type eq 'cs'}
				<td>{$inv_data.acc_tran_id}</td>
			{/if}
			
			<td>
				<span class="status_color-{$inv_data.status}">
					{$status_desc[$inv_data.status]}
					{if $inv_data.status eq 3}
						:
						({$inv_data.failed_reason})
					{/if}
				</span>
			</td>
			
			<td align="right">{$inv_data.tax_amount|number_format:2}</td>
			<td align="right">{$inv_data.amount|number_format:2}</td>
			<td align="center">{$inv_data.last_update}</td>
			<td>{$inv_data.acc_doc_no|default:'-'}</td>
		</tr>
	{/foreach}
</table>

{include file='footer.tpl'}