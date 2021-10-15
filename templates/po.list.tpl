{*
6/24/2008 1:01:55 PM yinsee
- fix print icon appear on reject/terminated
2/18/2011 5:50:51 PM Alex
- change lorry link from 's=' to 'search='
10/4/2011 11:47:43 AM Justin
- Added new print parameter "approved".

11/24/2011 4:49:07 PM Andy
- Add show "Delivered GRN" for those delivered PO.

4/24/2012 6:09:12 PM Justin
- Added new function to send email to vendor.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

8/3/2012 3:51:23 PM Justin
- Enhanced the Email to Vendor function to base on config "po_disable_send_email_to_vendor", if found then become hidden.

8/15/2012 1:59:00 PM Fithri
- remove Print Barcode button

10/1/2013 2:32 PM Justin
- Enhanced to allow user can maintain and send email custom message to vendor.

12/31/2014 9:41 AM Justin
- Bug fixed on deliver branch do not show accordingly when view at saved tab.

11/11/2015 13:35 AM DingRen
- Fix print checklist error when PO is HQ Payment and only 1 branch

4/13/2018 4:22 PM Andy
- Added Foreign Currency feature.

5/22/2019 9:53 AM William
- Enhance "GRN" word to use report_prefix.

9/24/2019 10:28 AM William
- Enhanced po module row amount show warning when use pda add, edit or delete po item.

11/4/2020 9:19 AM William
- Enhanced to add "Export PO Items" feature.

01/07/2021 5:47 PM Rayleen
- Add parameter in export_po_item() to indicate if po is delivered to multiple branches
- Allow HQ Distribution to Export PO Items 
*}
{$pagination}
{assign var=nr_colspan value=10}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=sortable id=po_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
				<thead class="bg-gray-100">
					<tr>
						<th>&nbsp;</th>
						<th>PO No.</th>
						<th>Branch</th>
						<th>Vendor Code</th>
						{if $config.enable_vendor_account_id}
							<th>Account ID</th>
							{assign var=nr_colspan value=$nr_colspan+1}
						{/if}
						<th>Vendor</th>
						<th>Department</th>
						<th>Amount</th>
						<th width="10%">Delivered GRN</th>
						<th>Last Update</th>
						<th>Print</th>
					</tr>
				</thead>
				{section name=i loop=$po_list}
				<script>
					po_branch_id_list['{$po_list[i].branch_id}_{$po_list[i].id}'] = [];
					{if $po_list[i].po_branch_id}
						po_branch_id_list['{$po_list[i].branch_id}_{$po_list[i].id}'].push('{$po_list[i].po_branch_id}');
					{else}
						{foreach from=$po_list[i].deliver_to item=bid}
							{if $bid}
							po_branch_id_list['{$po_list[i].branch_id}_{$po_list[i].id}'].push('{$bid}');
							{/if}
						{/foreach}
					{/if}
				</script>
				<tbody class="fs-08">
					<tr bgcolor={cycle values=",#eeeeee"}>
						<td nowrap>
							{if !$po_list[i].status}
								{if $po_list[i].branch_id!=$sessioninfo.branch_id}
									<a href="po.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}"><img src="ui/approved.png" title="Open this PO" border=0></a>
									{if $sessioninfo.level>=9999 || $sessioninfo.id==$po_list[i].user_id}
									<a href="javascript:void(po_chown({$po_list[i].id},{$po_list[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
									{/if}
								{else}
									<a href="po.php?a=open&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}"><img src="ui/ed.png" title="Open this PO" border=0></a>
									{if $sessioninfo.level>=9999 || $sessioninfo.id==$po_list[i].user_id}
									<a href="javascript:void(po_chown({$po_list[i].id},{$po_list[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
									{/if}
								{/if}
								
							{elseif $po_list[i].status==2}
								<a href="po.php?a=open&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/rejected.png" title="Open this PO" border=0></a>
							{elseif $po_list[i].status==4 or $po_list[i].status==5}
								<a href="po.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/cancel.png" title="Open this PO" border=0></a>
							{else}
								<a href="po.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/approved.png" title="Open this PO" border=0></a>
							{/if}
					
							{if $po_list[i].status==1}
								{if $po_list[i].active}
									<a href="javascript:void(do_print({$po_list[i].id},{$po_list[i].branch_id}, {$po_list[i].approved|default:0}))"><img src="ui/print.png" title="Print this PO" border=0></a>
									{if $po_list[i].po_option == 3}
									<a href="javascript:void(do_print_checklist({$po_list[i].id},{$po_list[i].branch_id},'checklist'))"><img src="ui/foc.png" title="Print this PO Checklist" border=0></a>
									{/if}
									{if $po_list[i].approved and $po_list[i].po_option <> 3}
										<!--<a href="po.label_print.php?po_no={$po_list[i].po_no}&send_to=barcode/{$po_list[i].po_no}.txt" target=ifprint><img src="ui/barcode.png" title="Print Barcode" border=0></a>-->
									{/if}
									{if $po_list[i].approved && !$config.po_disable_send_email_to_vendor}
										<a href="javascript:void(email_to_vendor_dialog('{$po_list[i].id}', '{$po_list[i].branch_id}'))"><img src="ui/icons/email_go.png" title="Send Email to Vendor" border=0></a>
									{/if}
								{/if}
								{if ($po_list[i].active && $po_list[i].approved) || ($po_list[i].branch_id==1 && $po_list[i].approved) }
									<a href="javascript:void(export_po_item('{$po_list[i].id}', '{$po_list[i].branch_id}', '{$po_list[i].deliver_to_count}'))"><img src="ui/icons/page_excel.png" title="Export PO Item" border=0></a>
								{/if}
							{/if}
							{if !$po_list[i].active && $po_list[i].branch_id==1 && $po_list[i].approved && $po_list[i].status==1}
								<img src="ui/icons/printer_add.png" title="Print distribution HQ PO list" border=0 onclick="do_print_distribution({$po_list[i].id},{$po_list[i].branch_id});">
							{/if}
							
							{if $po_list[i].delivered}
								<a href="goods_receiving_note.php?t=0&search={$po_list[i].po_no}"><img src="/ui/lorry.png" border=0 title="PO delivered. Click to search GRN Record"></a>
							{/if}
							
							{if $offline_url && $po_list[i].offline_id}
								<a href="javascript:;" onclick="goto_offline('{$offline_url}/po.php?a=view&id={$po_list[i].offline_id}&branch_id={$po_list[i].branch_id}');">
								<img src="/ui/offline_doc.png" border=0 title="View Offline Doc" />
								</a>
							{/if}
						</td>
						
						<td>
						{if $po_list[i].status==0}
							{$po_list[i].report_prefix}{$po_list[i].id|string_format:"%05d"}(DP)
						{elseif $po_list[i].po_no eq ''}
							{$po_list[i].report_prefix}{$po_list[i].id|string_format:"%05d"}(PP)
						{else}
							{$po_list[i].po_no}
							<br><font class="small" color=#009900>
							{$po_list[i].report_prefix}{if $po_list[i].hq_po_id}{$po_list[i].hq_po_id|string_format:"%05d"}{else}{$po_list[i].id|string_format:"%05d"}{/if}(PP)
							</font>
						{/if}
						</td>
						<td>
						{*$po_list[i].po_branch|default:$po_list[i].branch*}
						{if $po_list[i].po_branch_id || $po_list[i].branch_id!='1'}
							{$po_list[i].po_branch|default:$po_list[i].branch}
						{else}
							{foreach from=$po_list[i].deliver_to item=pn name=db}
								{if $smarty.foreach.db.iteration>1}, {/if}
								{$branch_list.$pn.code}
							{/foreach}
						{/if}
						</td>
						<td>{$po_list[i].vendor_code}</td>
						{if $config.enable_vendor_account_id}
							<td>{$po_list[i].account_id}</td>
						{/if}
						<td>{$po_list[i].vendor}
							{if preg_match('/\d/',$po_list[i].approvals)}
								<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$po_list[i].approvals aorder_id=$po_list[i].approval_order_id}</font></div>
							{/if}
							{if $po_list[i].branch_id=='1' && $po_list[i].approved}
								<div class=small>
								<font color=#0000ff>
								{foreach from=$po_list[i].po_no_list item=pn name=pn}
								{if $smarty.foreach.pn.iteration>1} ,{/if}
								<a href="/po.php?a=view&id={$pn.po_id}&branch_id={$pn.branch_id}" target="_blank">
								{$pn.po_no} {if $pn.b_name}({$pn.b_name}){/if}
								</a>
								{/foreach}
								</font>
								</div>
							{/if}		
						</td>
						<td>{$po_list[i].dept}</td>
						{strip}
						<td align="right">
							{if $po_list[i].amt_need_update}		
								<img src="/ui/messages.gif" title="Please open and save again to correct the amount." align="absmiddle" />
							{/if}
							{if !$po_list[i].currency_code}
								{$po_list[i].po_amount|number_format:2}
							{else}
								{$po_list[i].currency_code} {$po_list[i].po_amount|number_format:2}
								<br />
								{assign var=base_po_amount value=$po_list[i].po_amount*$po_list[i].currency_rate}
								{assign var=base_po_amount value=$base_po_amount|round2}
								<span class="converted_base_amt">{$config.arms_currency.code} {$base_po_amount|number_format:2}*</span>
							{/if}
						</td>
						{/strip}
						
						<td>
							{if !$po_list[i].delivered_grn_list}-{else}
								{foreach from=$po_list[i].delivered_grn_list item=delivered_grn name=fdg}
									<a href="goods_receiving_note.php?a=view&id={$delivered_grn.grn_id}&branch_id={$delivered_grn.branch_id}" target="_blank">
										{$delivered_grn.report_prefix}{$delivered_grn.grn_id|string_format:"%05d"}
									</a>
									{if !$smarty.foreach.fdg.last}, {/if}
								{/foreach}
							{/if}
						</td>
						<td align=right>{$po_list[i].last_update}</td>
						<td align=center>{$po_list[i].print_counter}</td>
					</tr>
				</tbody>
				{sectionelse}
				<tr>
					<td colspan="{$nr_colspan}" align="center">- no record -</td>
				</tr>
				{/section}
				</table>
		</div>
	</div>
</div>
<script>
ts_makeSortable($('po_tbl'));
</script>
