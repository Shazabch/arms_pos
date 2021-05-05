{*
7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.
*}
<tr id="rpc_item_{$item.id}" class="rpc_items" {if $item.is_deleted}style="display:none;"{/if}>
	<td>
		<img src="/ui/del.png" width="15" align="absmiddle" onclick="RP_CONFIGURATION_MODULE.delete_rp_configuration({$item.id});" class="clickable"/>
		<img src="/ui/{if $item.active}deact.png{else}act.png{/if}" width="15" align="absmiddle" onclick="RP_CONFIGURATION_MODULE.toggle_rp_configuration_status({$item.id}, this);" class="clickable"/>
		<span class="row_no" id="row_no_{$item.id}"></span>
			<div id="inact_area_{$item.id}" {if $item.active}style="display:none;"{/if}><sup>(Inactive)</sup></div>
		<input type="hidden" name="active[{$item.id}]" value="{$item.active}">
		<input type="hidden" name="is_deleted[{$item.id}]" value="{$item.is_deleted}">
		<input type="hidden" name="type[{$item.id}]" value="{$item.type}">
		<input type="hidden" name="is_parent[{$item.id}]" value="{$item.is_parent}">
	</td>
	<td>
		{if $item.type eq 1}
			<b>Category:</b> {$item.cat_desc}
			<input type="hidden" name="category_id[{$item.id}]" value="{$item.category_id}">
		{elseif $item.type eq 2}
			<b>SKU: </b>{$item.sku_item_code} / {$item.artno} / {$item.description}
			<input type="hidden" name="sku_item_id[{$item.id}]" value="{$item.sku_item_id}">
		{else}
			<b>SKU Group:</b> {$item.sg_code} - {$item.sg_desc}
			<input type="hidden" name="sg_id[{$item.id}]" value="{$item.sg_id}">
			<input type="hidden" name="sg_branch_id[{$item.id}]" value="{$item.sg_branch_id}">
		{/if}
	</td>
	<td>
		<table width="100%" class="sub_tbl">
			<tr>
				<th width="20%">All</th>
				<td width="80%" nowrap>
					<input id="setup_{$item.id}_all" name="setup[{$item.id}][all]" size=50 onchange="uc(this);" value="{if $item.setup.all.title}{if $item.setup.all.branch_code}{$item.setup.all.branch_code} - {/if}{$item.setup.all.title}{else}-- None --{/if}" title="{$item.setup.all.title}" readonly><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="RP_CONFIGURATION_MODULE.load_rp_list({$item.id}, 'all');"><span id="span_loading_{$item.id}_all"></span>
					<input type="hidden" name="rp_id[{$item.id}][all]" value="{$item.setup.all.id}">
					<input type="hidden" name="rp_branch_id[{$item.id}][all]" value="{$item.setup.all.bid}">
					<div id="div_rp_{$item.id}_all"  class="div_rp" style="border:1px solid #000; padding: 2px; display:none; width:338px;"></div>
				</td>
			</tr>
			<tr>
				<th>Non-member</th>
				<td nowrap>
					<input id="setup_{$item.id}_nonmember" name="setup[{$item.id}][nonmember]" size=50 onchange="uc(this);" value="{if $item.setup.nonmember.title}{if $item.setup.nonmember.branch_code}{$item.setup.nonmember.branch_code} - {/if}{$item.setup.nonmember.title}{else}-- None --{/if}" title="{$item.setup.nonmember.title}" readonly><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="RP_CONFIGURATION_MODULE.load_rp_list({$item.id}, 'nonmember');"><span id="span_loading_{$item.id}_nonmember"></span>
					<input type="hidden" name="rp_id[{$item.id}][nonmember]" value="{$item.setup.nonmember.id}">
					<input type="hidden" name="rp_branch_id[{$item.id}][nonmember]" value="{$item.setup.nonmember.bid}">
					<div id="div_rp_{$item.id}_nonmember"  class="div_rp" style="border:1px solid #000; padding: 2px; display:none; width:338px;"></div>
				</td>
			</tr>
			<tr>
				<th>Member</th>
				<td nowrap>
					<input id="setup_{$item.id}_member" name="setup[{$item.id}][member]" size=50 onchange="uc(this);" value="{if $item.setup.member.title}{if $item.setup.member.branch_code}{$item.setup.member.branch_code} - {/if}{$item.setup.member.title}{else}-- None --{/if}" title="{$item.setup.member.title}" readonly><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="RP_CONFIGURATION_MODULE.load_rp_list({$item.id}, 'member');"><span id="span_loading_{$item.id}_member"></span>
					<input type="hidden" name="rp_id[{$item.id}][member]" value="{$item.setup.member.id}">
					<input type="hidden" name="rp_branch_id[{$item.id}][member]" value="{$item.setup.member.bid}">
					<div id="div_rp_{$item.id}_member"  class="div_rp" style="border:1px solid #000; padding: 2px; display:none; width:338px;"></div>
				</td>
			</tr>
			{foreach from=$config.membership_type key=mtype item=mtype_desc name=mt_list}
				{if is_numeric($mtype)}
					{assign var=mt value=$mtype_desc}
				{else}
					{assign var=mt value=$mtype}
				{/if}
				{if $smarty.foreach.mt_list.first}
					<tr>
						<th colspan="2">Member Type:</th>
					</tr>
				{/if}
				<tr>
					<th>{$mtype_desc}</th>
					<td nowrap>
						<input id="setup_{$item.id}_{$mt}" name="setup[{$item.id}][{$mt}]" size=50 onchange="uc(this);" value="{if $item.setup.$mt.title}{if $item.setup.$mt.branch_code}{$item.setup.$mt.branch_code} - {/if}{$item.setup.$mt.title}{else}-- None --{/if}" title="{$item.setup.$mt.title}" readonly><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="RP_CONFIGURATION_MODULE.load_rp_list({$item.id}, '{$mt}');"><span id="span_loading_{$item.id}_{$mt}"></span>
						<input type="hidden" name="rp_id[{$item.id}][{$mt}]" value="{$item.setup.$mt.id}">
						<input type="hidden" name="rp_branch_id[{$item.id}][{$mt}]" value="{$item.setup.$mt.bid}">
						<div id="div_rp_{$item.id}_{$mt}"  class="div_rp" style="border:1px solid #000; padding: 2px; display:none; width:338px;"></div>
					</td>
				</tr>
			{/foreach}
			</tr>
		</table>
	</td>
	{if $BRANCH_CODE ne "HQ"}
		<td valign="top">
			<table width="100%" class="sub_tbl">
				<tr>
					<th width="20%">All:</th>
					<td width="100%">{$item.hq_info.setup.all.title|default:'-- None --'}</td>
				</tr>
				<tr>
					<th>Non-member:</th>
					<td>{$item.hq_info.setup.nonmember.title|default:'-- None --'}</td>
				</tr>
				<tr>
					<th>Member:</th>
					<td>{$item.hq_info.setup.member.title|default:'-- None --'}</td>
				</tr>
				{foreach from=$config.membership_type key=mtype item=mtype_desc name=mt_list}
					{if is_numeric($mtype)}
						{assign var=mt value=$mtype_desc}
					{else}
						{assign var=mt value=$mtype}
					{/if}
					{if $smarty.foreach.mt_list.first}
						<tr>
							<th colspan="2">Member Type:</th>
						</tr>
					{/if}
					<tr>
						<th>{$mtype_desc}</th>
						<td>{$item.hq_info.setup.$mt.title|default:'-- None --'}</td>
					</tr>
				{/foreach}
			</table>
		</td>
	{/if}
</tr>