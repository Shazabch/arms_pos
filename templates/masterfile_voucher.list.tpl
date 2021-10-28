{*
4/22/2011 2:31:13 PM Alex
- add no. of active qty and cancel qty information 
- remove cancel batch button if found no $config['voucher_edit_after_print'] and printed voucher

4/27/2011 5:37:48 PM Alex
- remove printer icon link by checking $config['voucher_hq_print'], $config['voucher_hq_reprint'], $config['voucher_subbranch_print'], $config['voucher_subbranch_reprint']

6/15/2011 3:28:53 PM Alex
- add no of voucher used
- add voucher cancel privilege control

8/22/2011 2:16:48 PM Alex
- add show numbering and multiple branch under allow interbranch

3/13/2012 12:01:07 PM Alex
- only HQ can cancel whole batch voucher

6/21/2012 4:08:00 PM Andy
- Add feature to voucher listing to able to print member redeem format.

8/14/2012 5:19 PM Justin
- Enhanced to have export voucher feature.
- Added new config "voucher_enable_export" to hide/show export feature.

1/31/2013 4:24 PM Justin
- Enhanced to feature voucher listing to able to print member redeem format.

4/21/2017 9:46 PM Khausalya 
- Enhanced changes from RM to use config setting. 
*}

{if $voucher_batch}
<div>
	<div style="margin:5px;float:right;" >{$pagination}
	<br />Total {$total_row|number_format} record(s) found.
	</div>
	<div style="float:left;">
		<ul>
			<li><a href="./ui/3of9/mrvcode39extma.ttf">Click here to download and install the font for printing barcode</a></li>
		</ul>
	</div>
	<br style="clear:both;">
</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table width=100% >
					<thead class="bg-gray-100">
						<tr>
							<th rowspan=2>#</th>
							<th rowspan=2>&nbsp;</th>
							<th rowspan=2>Branch</th>
							   <th rowspan=2>Batch no</th>
							<th colspan=3>Code</th>
							<th rowspan=2>Voucher Value({$config.arms_currency.symbol})</th>
							<th rowspan=2>Allow Interbranch</th>
							<th colspan=2>Activated</th>
							<th colspan=2>Cancelled</th>
							<th colspan=2>Used(Pcs)</th>
							<th rowspan=2>Last Update</th>
							<th rowspan=2>Added</th>
							<th rowspan=2>Created By</th>
						</tr>
						<tr >
							<th>From</th>
							<th>To</th>
							<th>Quantity (pcs)</th>
							<th>Last Timestamp</th>
							<th>Quantity (pcs)</th>
							<th>Last Timestamp</th>
							<th>Quantity (pcs)</th>
							<th>Last Timestamp</th>
							<th>Quantity (pcs)</th>
						</tr>
					</thead>
					<tbody id="voucher_listing" class="fs-08">
					{assign var=start_no value=$num_start}
					
					{foreach from=$voucher_batch key=bat_n item=vou_bat}
					<tr>
						{assign var=start_no value=$start_no+1}
						<td nowrap>{$start_no}</td>
						<td align=center nowrap>
						{if $sessioninfo.privilege.MST_VOUCHER_EDIT && $vou_bat.cancel_status eq '0' && ($config.voucher_edit_after_print || $vou_bat.voucher_qty ne $vou_bat.print_qty) && $vou_bat.voucher_qty ne $vou_bat.active_qty}
							<a target="_blank" href="{$smarty.server.PHP_SELF}?a=voucher_list_data&branch_id={$vou_bat.branch_id}&batch_no={$vou_bat.batch_no}&batch_type={$vou_bat.batch_type}"><img border=0 width='15px' src=/ui/ed.png title="Edit" align=absmiddle border=0></a>
						{else}
							<a target="_blank" href="{$smarty.server.PHP_SELF}?a=voucher_list_data&branch_id={$vou_bat.branch_id}&batch_no={$vou_bat.batch_no}&batch_type={$vou_bat.batch_type}"><img border=0 width='15px' src=/ui/view.png title="View" align=absmiddle border=0></a>
						{/if}
						{if $BRANCH_CODE eq 'HQ' && $sessioninfo.privilege.MST_VOUCHER_CANCEL && $vou_bat.voucher_qty ne $vou_bat.cancel_qty && $vou_bat.num_used<$vou_bat.voucher_qty}
							<img width='15px' src=/ui/rejected.png onclick="if (confirm('Are you sure to cancel this batch?'))ajax_cancel_batch('{$vou_bat.batch_no}');" title="Cancel" align=absmiddle border=0>
						{/if}
			
						{if $sessioninfo.privilege.MST_VOUCHER_PRINT && $vou_bat.cancel_status eq '0' && 
							(((!$vou_bat.printed && (($config.voucher_hq_print && $BRANCH_CODE eq 'HQ') || ($config.voucher_subbranch_print && $BRANCH_CODE ne 'HQ'))) || 
							($vou_bat.printed && (($config.voucher_hq_reprint && $BRANCH_CODE eq 'HQ') || ($config.voucher_subbranch_reprint && $BRANCH_CODE ne 'HQ')))))}
							<img width='15px' src=/ui/print.png onclick="show_print('{$vou_bat.branch_id}','{$vou_bat.batch_no}','{$vou_bat.batch_type}');" title="Print" align=absmiddle border=0>
							{if $config.voucher_enable_export}
								<img width='15px' src="/ui/icons/page_white_put.png" onclick="export_voucher('{$vou_bat.batch_no}','{$vou_bat.branch_id}');" title="Download Voucher List (CSV)" align=absmiddle border=0>
							{/if}
						{/if}
						</td>
						
			
						<td align=center>{$vou_bat.branch_desc}</td>
						<td align=center>{$vou_bat.batch_no}</td>
						<td align=center>{$vou_bat.min_code|str_pad:7:0:$smarty.const.STR_PAD_LEFT}</td>
						<td align=center>{$vou_bat.max_code|str_pad:7:0:$smarty.const.STR_PAD_LEFT}</td>
						<td align=center>{$vou_bat.voucher_qty}</td>
						<td align=center>{$vou_bat.voucher_value}</td>
						<td class="voucher_b">
							{if $vou_bat.allow_interbranch_short}
								<span class="vbranch_short">{$vou_bat.allow_interbranch_short}</span>
								<span class="vbranch_hide">{$vou_bat.allow_interbranch_full}</span>
							{/if}
						</td>
						   <td align=center>{$vou_bat.activated|ifzero}</td>
						   <td align=center>{$vou_bat.active_qty|ifzero}</td>
						   <td align=center>{$vou_bat.cancelled|ifzero}</td>
						   <td align=center>{$vou_bat.cancel_qty|ifzero}</td>
						   <td align=center>{$vou_bat.max_pos_time|ifzero}</td>
						   <td {if $vou_bat.num_used>$vou_bat.voucher_qty}class="red"{/if} align=center>{$vou_bat.num_used|ifzero}</td>
						<td align=center>{$vou_bat.last_update}</td>
						<td align=center>{$vou_bat.added}</td>
						<td align=center>{$vou_bat.create_user}</td>
					</tr>
					{/foreach}	
					</tbody>
				</table>
			</div>
		</div>
	</div>
{else}
	<p align=center>- No Data -</p>
{/if}
