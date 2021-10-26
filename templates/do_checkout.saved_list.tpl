{*
REVISION HISTORY
++++++++++++++++++
10/5/2007 4:26:00 PM gary
- added "DO/" for displaying do no.

12/18/2007 12:13:38 PM gary
- show all the deliver branches or company.

7/31/2009 3:02:56 PM Andy
- Edit Do No. display layout

11/10/2009 11:16:04 AM Andy
- Add Invoice Amount column

1/14/2010 1:45:12 PM Andy
- Add debtor description under code

1/18/2010 3:54:01 PM Andy
- add paid checkbox

6/17/2011 9:48:11 AM Justin
- Added foreign amount field if found using multiple currencies.

11/18/2011 3:00:04 PM Andy
- Add save/show DO price type. (only if all items in DO having same price type and is consignment mode).

1/18/2013 5:30 PM Justin
- Enhanced not to show empty description with "()".

3/5/2013 4:57 PM Justin
- Enhanced to show "Paid" while config "do_generate_receipt_no" is turned on and DO is credit sales.
- Enhanced to show print icon beside of Paid for reprint receipt.

5/13/2014 1:57 PM Fithri
- show PO no column on the saved & completed list

4/19/2017 3.32 PM Khausalya 
- Enhanced changes from RM to use config setting.

6/8/2018 10:00 AM HockLee
- Added Batch Code column.

9/25/2018 1:52 PM Andy
- Remove DO print receipt feature.

7/16/2020 9:28 AM William
- Enhanced "Credit Sales DO" and "Cash Sales DO" checkout list can mark as paid and key in (Payment Date, payment type and Remark).

8/4/2020 9:29 AM William
- Remove sorting of column not able to use sorting function.
*}

{if $config.do_transfer_have_discount or $config.do_cash_sales_have_discount or $config.do_credit_sales_have_discount}
	{assign var=show_invoice_amt value=1}
{/if}

{$pagination}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=sortable id=do_tbl width=100% class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr >
						<th class="ignore_sorting">&nbsp;</th>
						<th>DO No.</th>
						<th>Batch Code</th>
						<th>Create By</th>
						<th>Deliver To</th>
						<th>Lorry No</th>
						<th class="ignore_sorting">Paid</th>
						{if $config.consignment_modules}
							<th>Price Type</th>
						{/if}
						<th>Total Amount<br>({$config.arms_currency.symbol})</th>
						{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
							<th>Foreign Amount</th>
						{/if}
						{if $show_invoice_amt}
							<th>Invoice Amount<br>({$config.arms_currency.symbol})</th>
							{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
								<th>Foreign Invoice<br />Amount</th>
							{/if}
						{/if}
						<th>DO Date</th>
						<th>PO No</th>
						<th>Last Update</th>
					</tr>
					
				</thead>
				{section name=i loop=$do_list}
				{assign var=n value=$smarty.section.i.iteration-2}
				<tbody class="fs-08">
					<tr bgcolor={cycle values=",#eeeeee"}>
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
							{if $do_list[i].checkout}
								 <a href="do_checkout.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}">
								<img src="ui/view.png" title="View Completed DO" border=0>
								 </a>
								 <a href="javascript:void(do_print('{$do_list[i].id}','{$do_list[i].branch_id}'))">
								<img src="ui/print.png" title="Print this DO" border=0>
								</a>
							{else}
								 <a href="do_checkout.php?a=open&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}">
								<img src="ui/lorry_go.png" title="Open this DO" border=0>
								</a>	
							 {/if}
						 {/if}
						</td>
					
						
						
						<td>{*{if $do_list[i].do_no ne $do_list.$n.do_no}DO/{$do_list[i].do_no}{/if}*}
							{if $do_list[i].do_no}
								{$do_list[i].do_no}
								<br>
								<font class="small" color=#009900>
									{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
								</font>
							{else}
								{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
							{/if}
						</td>
					
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
							{$do_list[i].batch_code|default:"-"}
						{/if}	
						</td>
						
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
							{$do_list[i].user_name}
						{/if}	
						</td>
					
						<td>
						{if $do_list[i].do_type eq 'credit_sales'}
							{assign var=debtor_id value=$do_list[i].debtor_id}
							Debtor: {$debtor.$debtor_id.code}
							{if $debtor.$debtor_id.description}
								<br />
								<span class="small" style="color:blue;">({$debtor.$debtor_id.description})</span>
							{/if}
						{else}
							{if $do_list[i].do_branch_id}
								{$do_list[i].branch_name_2}
							{elseif $do_list[i].open_info.name}
								{$do_list[i].open_info.name}
							{/if}
							{foreach from=$do_list[i].d_branch.name item=pn name=pn}
								{if $smarty.foreach.pn.iteration>1} ,{/if}
								{$pn}
							{/foreach}
						{/if}
						</td>	
						
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
						{$do_list[i].checkout_info.lorry_no|default:"-"}
						{/if}
						</td>
						<td align="center">
						{if $do_list[i].do_type eq 'open' || $do_list[i].do_type eq 'credit_sales'} 
							<button value="Paid" onclick="show_paid('{$do_list[i].id}','{$do_list[i].branch_id}')">Paid</button>
							<img id="img_paid_status_{$do_list[i].id}_{$do_list[i].branch_id}" src="{if $do_list[i].paid}ui/approved.png{else}ui/icons/cancel.png{/if}" title="Paid Status"/>
						{/if}
						</td>
						  {if $config.consignment_modules}
							  <td>{$do_list[i].sheet_price_type|default:'-'}</td>
						  {/if}
						  
						<td align=right>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
						{$do_list[i].total_amount|number_format:2}
						{/if}
						</td>
						{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
							<td align="right">{$do_list[i].total_foreign_amount|number_format:2}</td>
						{/if}
						{if $show_invoice_amt}
							<td align="right">
							{if ($do_list[i].do_type eq 'transfer' and $config.do_transfer_have_discount) or ($do_list[i].do_type eq 'open' and $config.do_cash_sales_have_discount) or ($do_list[i].do_type eq 'credit_sales' and $config.do_credit_sales_have_discount)}
								{$do_list[i].total_inv_amt|number_format:2}
							{else}-{/if}
							</td>
							{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
								<td align="right">{$do_list[i].total_foreign_inv_amt|number_format:2}</td>
							{/if}
						{/if}
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
						{$do_list[i].do_date|date_format:$config.dat_format}
						{/if}
						</td>
						<td align=center>{$do_list[i].po_no|default:"-"}</td>
						<td align=center>
						{if $do_list[i].do_no ne $do_list.$n.do_no}
						{$do_list[i].last_update}
						{/if}
						</td>
					</tr>
				</tbody>
				{sectionelse}
				<tr>
					{assign var=cols value=7}
					{if ($do_list[i].do_type eq 'transfer' and $config.do_transfer_have_discount) or ($do_list[i].do_type eq 'open' and $config.do_cash_sales_have_discount) or ($do_list[i].do_type eq 'credit_sales' and $config.do_credit_sales_have_discount)}
						{assign var=cols value=$cols+1}
						{if $show_invoice_amt}{assign var=cols value=$cols+1}{/if}
					{/if}
					{if $show_invoice_amt}{assign var=cols value=$cols+1}{/if}
					<td colspan=11 align=center>- no record -</td>
				</tr>
				{/section}
				</table>	
		</div>
	</div>
</div>
<script>
ts_makeSortable($('do_tbl'));
</script>
