{*
REVISION HISTORY
=================
12/28/2007 12:50:29 PM gary
- add print preview for grn performance report.

11/2/2009 4:07 PM Andy
- add print icon for GRN in "Saved"     

10/6/2010 3:34:47 PM Justin
- Added GRN future to show different result shows and report printing icon base on config set.

7/13/2011 11:52:54 AM Justin
- Fixed the bugs while searching the GRN, it straight allow user to go straight Account Verification even PO Variance/SKU Apply is not being confirmed.

7/14/2011 12:14:21 PM Justin
- Fixed the bugs that system unable to show user Account Verification before confirming price change state.

8/1/2011 3:16:23 PM Justin
- Enhanced the GRN listing for GRN Future to show different info between created by GRN or GRN Future.

9/8/2011 3:36:32 PM Justin
- Modified function name of printing of GRN report.
- Simplified the JS scripts for GRN Future.

9/30/2011 12:43:11 PM Justin
- Fixed the bugs the edit function still showed out even owner does not have the privilege to confirm Account Verification.

6/28/2012 4:26:23 PM Justin
- Enhanced the print GRN report that trigger printing option choice window instead of print out report.

7/3/2012 3:34:44 PM Justin
- Added new feature that allows user to change GRN owner when GRN document is save as draft.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

7/31/2012 4:28:14 PM Justin
- Enhanced to show branch code column when search result from HQ.

8/23/2012 6:11 PM Justin
- Added to show related invoice if found config.

9/5/2012 10:52 AM Justin
- Allow user to change owner from GRN created by other user while found privilge "GRN_CHANGE_OWNER".

4/10/2013 3:48 PM Justin
- Enhanced search engine to search those GRR without GRN and show user the result.

11/25/2014 1:22 PM Justin
- Bug fixed on system will not show the printing option as every tab differently when using search engine.

3/14/2015 9:42 AM Justin
- Enhanced to have generate and print DN feature.

2/7/2017 5:46 PM Andy
- Fixed to only allow HQ user to view other branch GRN when using search function.

6/5/2017 15:15 Qiu Ying
- Enhanced to add GRR Date

7/11/2017 2:46 PM Justin
- Enhanced to show different lorry icon once user has confirmed from SKU Manage.

4/19/2018 5:03 PM Justin
- Enhanced to show both base and foreign currency.

5/22/2019 9:53 AM William
- Enhance "GRA","GRR","GRN" word to use report_prefix.

11/9/2020 11:00 AM William
- Enhanced to add export grn item icon.
*}

{if !$t && $search && $grr}
<h3>GRR without GRN</h3>
{include file="goods_receiving_note.grr_list.tpl" is_search=1}
<h3>General GRN</h3>
{/if}

{$pagination}
{assign var=nr_colspan value=11}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px"
			class="report_table table mb-0 text-md-nowrap  table-hover"
>
				<thead class="bg-gray-100">
					<tr >
						<th>&nbsp;</th>
						<th>GRN No</th>
						{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
							<th>Branch</th>
						{/if}
						<th>GRR No</th>
						<th>GRR Doc</th>
						<th>GRR Date</th>
						<th>Type</th>
						{if $config.grn_summary_show_related_invoice}
							<th>Related Invoice</th>
							{assign var=nr_colspan value=$nr_colspan+1}
						{/if}
						<th>Vendor Code</th>
						{if $config.enable_vendor_account_id}
							<th>Account ID</th>
							{assign var=nr_colspan value=$nr_colspan+1}
						{/if}
						<th>Vendor</th>
						<th>Selling</th>
						<th>Amount</th>
						<th>Last Update</th>
						<th>Print</th>
						{if $config.use_grn_future_allow_generate_gra && $t eq 6}
						<th>GRA</th>
						{assign var=nr_colspan value=$nr_colspan+1}
						{/if}
					</tr>
				</thead>
				
				{section name=i loop=$grn_list}
				<tbody class="fs-08">
					<tr bgcolor={cycle values=",#eeeeee"}>
						{if $config.use_grn_future}
							<td nowrap>
								<input type="hidden" id="active_{$grn_list[i].id}_{$grn_list[i].branch_id}" value="{$grn_list[i].active}" />
								<input type="hidden" id="status_{$grn_list[i].id}_{$grn_list[i].branch_id}" value="{$grn_list[i].status}" />
								<input type="hidden" id="authorized_{$grn_list[i].id}_{$grn_list[i].branch_id}" value="{$grn_list[i].authorized}" />
								<input type="hidden" id="approved_{$grn_list[i].id}_{$grn_list[i].branch_id}" value="{$grn_list[i].approved}" />
								{if $grn_list[i].active && $grn_list[i].status!=2}
								{if $grn_list[i].status==0}
								{if $grn_list[i].user_id==$sessioninfo.id && !$grn_list[i].authorized and $grn_list[i].branch_id==$sessioninfo.branch_id}
									<a href="goods_receiving_note.php?a=open&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}&action=edit"><img src="ui/ed.png" title="Edit this GRN" border=0></a>
								{else}
									<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/view.png" title="Open this GRN" border=0></a>
								{/if}
								
								{if !$grn_list[i].authorized && ($sessioninfo.level>=9999 || $sessioninfo.id==$grn_list[i].user_id || $sessioninfo.privilege.GRN_CHANGE_OWNER)}
									<a href="javascript:void(grn_change_owner({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
								{/if}
								
								{if $grn_list[i].authorized eq '1' && $grn_list[i].branch_id==$sessioninfo.branch_id && (($sessioninfo.privilege.GRN_VAR_DIV && !$grn_list[i].div1_approved_by) || ($sessioninfo.privilege.GRN_SIV_DIV && !$grn_list[i].div2_approved_by) || ($sessioninfo.privilege.GRN_PC_DIV && !$grn_list[i].div3_approved_by) || $sessioninfo.privilege.GRN_ACCV_DIV)}
									{if !$action && $smarty.request.search && (!$grn_list[i].div1_approved_by || !$grn_list[i].div2_approved_by || !$grn_list[i].div3_approved_by)}
										{assign var=action value=verify}
									{/if}
									
									{if $grn_list[i].div1_approved_by && $grn_list[i].div2_approved_by && $grn_list[i].div3_approved_by && !$grn_list[i].div4_approved_by}
										{assign var=is_acc_verify value=1}
									{else}
										{assign var=is_acc_verify value=0}
									{/if}
									<a href="goods_receiving_note.php?a=open&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}&action={$action}"><img src="{if $is_acc_verify}ui/icons/lorry_go.png{elseif $action eq 'verify'}ui/icons/lorry.png{elseif $action eq 'grr_edit'}ui/ed.png{else}ui/icons/table_add.png{/if}" title="{if $is_acc_verify}Account Verification{elseif $action eq 'verify'}SKU Manage{elseif $action eq 'grr_edit'}Edit GRR{else}Enter GRN Correction Detail{/if}" border=0></a>{/if}
								{else}
									<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/approved.png" title="Open this GRN" border=0></a>
								{/if}
								{else}
									<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/cancel.png" title="Open this GRN" border=0></a>
								{/if}
						
								{if $grn_list[i].active==1 && $grn_list[i].status==1 && $grn_list[i].authorized==1 && $grn_list[i].approved==1}
									<a href="javascript:void(do_print({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/print.png" title="Print this GRN Report" border=0></a>
									
									<a href="javascript:void(do_print_preview({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/icons/report.png" title="Preview GRN Performance Report" border=0></a>
									
									<a href="javascript:void(export_grn_item({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/icons/page_excel.png" title="Export GRN Item" border=0></a>
									{if $grn_list[i].generate_arms_dn}
										<img src="ui/icons/page_add.png" border="0" title="Generate ARMS DN" onclick="toggle_dn_printing_menu('{$grn_list[i].id}', '{$grn_list[i].branch_id}');">
									{elseif $grn_list[i].print_arms_dn}
										<a href="?a=print_arms_dn&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target="_blank">  		
											<img src="ui/icons/page_copy.png" border="0" title="Print ARMS DN">
										</a>
									{/if}
								{elseif ($grn_list[i].active==1 && $grn_list[i].status==0 && $grn_list[i].authorized==0) || ($grn_list[i].authorized && $grn_list[i].active)}
									<!--a href="javascript:void(print_grn({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/print.png" title="Print Summary Report" border=0></a-->
									<a href="javascript:void(do_print({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/print.png" title="Print Summary Report" border=0></a>
								{/if}
								
								{if $offline_url && $grn_list[i].offline_id}
									<a href="javascript:;" onclick="goto_offline('{$offline_url}/goods_receiving_note.php?a=view&id={$grn_list[i].offline_id}&branch_id={$grn_list[i].branch_id}');">
									<img src="/ui/offline_doc.png" border=0 title="View Offline Doc" />
									</a>
								{/if}
							</td>
						{else}
							<td nowrap>
								{if $grn_list[i].active}
								{if $grn_list[i].status==0}
								{if $grn_list[i].user_id==$sessioninfo.id}
								<a href="goods_receiving_note.php?a=open&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}"><img src="ui/ed.png" title="Open this GRN" border=0></a>
								{else}
								<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/view.png" title="Open this GRN" border=0></a>
								{/if}
								{else}
								<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/approved.png" title="Open this GRN" border=0></a>
								{/if}
								{else}
								<a href="goods_receiving_note.php?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target=_blank><img src="ui/cancel.png" title="Open this GRN" border=0></a>
								{/if}
						
								{if $grn_list[i].active && (($grn_list[i].status==1 && $grn_list[i].approved) or $grn_list[i].status==0)}
								<a href="javascript:void(do_print({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/print.png" title="Print this GRN Reports" border=0></a>
								
								<a href="javascript:void(do_print_preview({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/icons/report.png" title="Preview GRN Performance Report" border=0></a>
								{/if}
							</td>		
						{/if}
						<td>
							{$grn_list[i].report_prefix}{$grn_list[i].id|string_format:"%05d"}
							{if $grn_list[i].dn_number}
								<br /><font color="#0000ff">D/N No.: {$grn_list[i].dn_number}</font>
							{/if}
						</td>
						{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
							<td>{$grn_list[i].branch_code}</td>
						{/if}
						<td nowrap>{$grn_list[i].report_prefix}{$grn_list[i].grr_id|string_format:"%05d"}{if !$grn_list[i].is_future}/{$grn_list[i].grr_item_id}{/if}</td>
						<td>{$grn_list[i].doc_no}</td>
						<td>{$grn_list[i].rcv_date}</td>
						<td>{$grn_list[i].type}</td>
						{if $config.grn_summary_show_related_invoice}
							<td>
								{if $grn_list[i].type eq 'PO'}
									{$grn_list[i].related_invoice}
								{else}
									&nbsp;
								{/if}
							</td>
						{/if}
						<td>{$grn_list[i].vendor_code}</td>
						{if $config.enable_vendor_account_id}
							<td>{$grn_list[i].account_id}</td>
						{/if}
						<td>{$grn_list[i].vendor}
							{if preg_match('/\d/',$grn_list[i].approvals)}
								<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$grn_list[i].approvals aorder_id=$grn_list[i].approval_order_id}</font></div>
							{/if}
						</td>
						<td align=right>{$grn_list[i].total_selling|number_format:2}</td>
						<td align=right>
							{if !$grn_list[i].currency_code}
								{$grn_list[i].amount|number_format:2}
							{else}
								{$grn_list[i].currency_code} {$grn_list[i].amount|number_format:2}
								<br />
								{assign var=base_grr_amount value=$grn_list[i].amount*$grn_list[i].currency_rate}
								{assign var=base_grr_amount value=$base_grr_amount|round2}
								<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
							{/if}
						</td>
						<td align=right>{$grn_list[i].last_update}</td>
						<td align=center>{$grn_list[i].print_counter}</td>
						{if $config.use_grn_future_allow_generate_gra && $t eq 6}
						<td>
							{if $grn_list[i].unfinish_gra_list}
								<div class="stdframe" style="background-color:#FFE4E1;">
									<b>Unfinish GRA:</b><br/>
										{foreach from=$grn_list[i].unfinish_gra_list item=gra name=fgra}
											{if !$smarty.foreach.fgra.first}, {/if}
											<a href="goods_return_advice.php?a=open&id={$gra.id}&branch_id={$gra.branch_id}" target="_blank">
												{$grn_list[i].report_prefix}{$gra.id|string_format:"%05d"}
											</a>
										{/foreach}
								</div>
							{/if}
							{if $grn_list[i].finished_gra_list}
								<div class="stdframe" style="background-color:#F0FFF0;">
									<b>Finished GRA:</b><br/>
										{foreach from=$grn_list[i].finished_gra_list item=gra name=fgra}
											{if !$smarty.foreach.fgra.first}, {/if}
											<a href="goods_return_advice.php?a=view&id={$gra.id}&branch_id={$gra.branch_id}" target="_blank">
												{$grn_list[i].report_prefix}{$gra.id|string_format:"%05d"}
											</a>
										{/foreach}
								</div>
							{/if}
					
					
						</td>
						{/if}
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

{if $config.use_grn_future}
<script>
	var use_grn_future = '{$config.use_grn_future}';
	var grn_rpt_print = '{$smarty.request.grn_rpt_print}';
	var id = '{$smarty.request.id}';
	var branch_id = '{$smarty.request.branch_id}';

	{literal}
	if(grn_rpt_print){
		print_grn(id, branch_id);
	}
	{/literal}
</script>
{/if}
