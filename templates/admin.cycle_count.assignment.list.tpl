{*
6/15/2020 2:29 PM Andy
- Added sortable to column "Propose Stock Take Date".
*}

{if !$cc_list}
	<p align="center"> &nbsp; * No Data Found *</p>
{else}
    {if $total_page >1}
		<div style="padding:2px;float:left;">
			Page
			<select id="sel_page" onChange="CC_ASSGN_LIST.list_sel();">
				{section loop=$total_page name=s}
					<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
				{/section}
			</select>
		</div>
	{/if}

	<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="100">&nbsp;</th>
			<th width="60">Created Branch</th>
			<th width="100">Document No</th>
			<th width="60">Stock Take Branch</th>
			<th>Stock Take Covered / Content</th>
			<th width="80">Estimate SKU Count</th>
			<th width="80" class="sortable_col" onClick="CC_ASSGN_LIST.sort_list('propose_st_date', 'cycle_count');">Propose Stock Take Date {darrow col='propose_st_date' grp='cycle_count'}</th>
			<th width="80">Assigned Stock Take Person</th>
			<th width="80">Created By</th>
			<th width="120">Last Update</th>
		</tr>
		
		{foreach from=$cc_list item=r}
		    <tr bgcolor='{cycle values=",#eeeeee"}'>
				<td>
					{assign var=can_edit value=0}
					{assign var=can_change_pic value=0}
					{assign var=can_print value=0}
					{assign var=can_view_sheet value=0}
					{assign var=can_clone value=0}
					{if $r.active}
						{if ($r.status eq 0 or $r.status eq 2) and $r.branch_id eq $sessioninfo.branch_id and $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT}
							{assign var=can_edit value=1}
						{/if}
						
						{if $r.status eq 1 and $r.approved eq 1 and !$r.completed and $r.branch_id eq $sessioninfo.branch_id and $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT}
							{assign var=can_change_pic value=1}
						{/if}
						
						{if $r.status eq 1 and $r.approved eq 1 and $r.st_branch_id eq $sessioninfo.branch_id and $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT}
							{if  $sessioninfo.id eq $r.pic_user_id}
								{assign var=can_print value=1}
							{else}
								{if $r.printed eq 1}
									{assign var=can_print value=1}
								{/if}
							{/if}
							
							{if $r.printed eq 1}
								{assign var=can_view_sheet value=1}
							{/if}
						{/if}
					{else}
					
					{/if}
					
					{if $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT and ($BRANCH_CODE eq 'HQ' || $r.branch_id eq $sessioninfo.branch_id)}
						{assign var=can_clone value=1}
					{/if}
					
					{if $can_edit}
						<a href="admin.cycle_count.assignment.php?a=open&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/ed.png" title="Edit" border="0" /></a>
					{else}
						<a href="admin.cycle_count.assignment.php?a=view&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/view.png" title="View" border="0" /></a>
					{/if}
					{if $can_change_pic}
						<a href="javascript:void(CC_ASSGN_LIST.change_owner_clicked('{$r.branch_id}', '{$r.id}'));"><img src="ui/chown.png" title="Change Assigned Stock Take Person" border="0" /></a>
					{/if}
					{if $can_print}
						<a href="javascript:void(CC_ASSGN_LIST.print_sku_list('{$r.branch_id}', '{$r.id}', '{$r.printed}'));"><img src="ui/print.png" title="Print SKU Listing" border="0" /></a>
					{/if}
					
					{if $can_clone}
						<a href="javascript:void(CC_CLONE_DIALOG.open('{$r.branch_id}', '{$r.id}'));"><img src="ui/icons/page_copy.png" title="Clone" border="0" /></a>
					{/if}
					
					{if $can_view_sheet}
						<a href="admin.cycle_count.assignment.php?a=download_sku_csv&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/icons/page_excel.png" title="Download SKU List" border="0" /></a>
						<a href="admin.cycle_count.assignment.php?a=show_cycle_count_sheet&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/icons/application_view_icons.png" title="Show Sheet" border="0" /></a>
					{/if}
					
					
				</td>
				<td>{$r.bcode}</td>
				<td>{$r.doc_no}
					{if preg_match('/\d/',$r.approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$r.approvals aorder_id=$r.approval_order_id}</font></div>
					{/if}
				</td>
				<td>{$r.st_bcode}</td>
				<td>
					{if $r.st_content_type eq 'cat_vendor_brand'}
						{if $r.category_id}
							<b>Category: </b>{$r.cat_desc}<br />
						{/if}
						{if $r.vendor_id}
							<b>Vendor: </b>{$r.vendor_desc}<br />
						{/if}
						{if $r.brand_id>=0}
							<b>Brand: </b>{if $r.brand_id eq 0}UN-BRANDED{else}{$r.brand_desc}{/if}<br />
						{/if}
					{elseif $r.st_content_type eq 'sku_group'}
						<b>SKU Group: </b>{$r.sg_code} - {$r.sg_desc}
					{/if}
				</td>
				<td align="right">{$r.estimate_sku_count|number_format}</td>
				<td align="center">{$r.propose_st_date}</td>
				<td align="center" class="td_pic_username {if $r.pic_user_id eq $sessioninfo.id}curr_pic_username{/if}">{$r.pic_username}</td>
				<td align="center">{$r.owner_u}</td>
				<td align="center">{$r.last_update}</td>
			</tr>
		{/foreach}
	</table>
{/if}