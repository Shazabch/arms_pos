{*
7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

8/30/2012 5:56 PM Justin
- Enhanced to allow user edit Member Points Reward while has privilege "MEMBER_POINT_REWARD_EDIT".

4/20/2017 3:38 PM Khausalya
- Enhanced changes from RM to use config setting. 
*}
{if $config.consignment_modules}{assign var=hide_branches value=1}{/if}

{if $is_edit && $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
	<select name="category_point_inherit[{$item_id}]" onChange="category_point_inherit_changed('{$item_id}');" class="sel_category_point_inherit" id="sel-category_point_inherit-{$item_id}">
		{foreach from=$category_point_inherit_options key=k item=dr}
			<option value="{$k}" {if $item_obj.category_point_inherit eq $k}selected {/if}>{$dr}</option>
		{/foreach}
	</select>&nbsp;&nbsp;
{else}
	{foreach from=$category_point_inherit_options key=k item=dr}
		{if $item_obj.category_point_inherit eq $k}<b>{$dr}</b>{/if}
	{/foreach}
	<input type="hidden" name="category_point_inherit[{$item_id}]" id="sel-category_point_inherit-{$item_id}" value="{$item_obj.category_point_inherit}" />
{/if}

<div id="div_category_point_container-{$item_id}" style="{if $item_obj.category_point_inherit != 'set'}display:none;{/if}padding:2px;">
	<table class="report_table">
		<tr class="header">
			<th rowspan="2">&nbsp;</th>
			<th {if !$hide_branches}colspan="{count var=$branch_list}"{/if}>{$config.arms_currency.symbol} X = 1 Point 
				{if !$hide_branches}
					(set on individual branch to override "All")
				{/if}
			</th>
		</tr>
		<tr class="header">
			<th>All
				<input type="checkbox" name="category_point_by_branch_inherit[{$item_id}][0][set_override]" value="1" {if $item_obj.category_point_by_branch_inherit.0.set_override}checked {/if} title="Override" onChange="category_point_branch_override_changed('{$item_id}', 0);" id="inp_category_point_override-{$item_id}-0" {if !$is_edit || !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}disabled {/if} />
			</th>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<th>{$b.code}
							<input type="checkbox" name="category_point_by_branch_inherit[{$item_id}][{$bid}][set_override]" value="1" {if $item_obj.category_point_by_branch_inherit.$bid.set_override}checked {/if} title="Override" onChange="category_point_branch_override_changed('{$item_id}', '{$bid}');" id="inp_category_point_override-{$item_id}-{$bid}" {if !$is_edit || !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}disabled {/if} />
						</th>
					{/if}
				{/foreach}
			{/if}
		</tr>
		
		<!-- Member -->
		<tr>
			<td><b>Member</b></td>
			<td align="center">
				{include file='masterfile_sku.edit.items.point.input.tpl' bid=0 is_edit=$is_edit b_cat_point=$item_obj.category_point_by_branch_inherit.0 editable=$item_obj.category_point_by_branch_inherit.0.set_override}
			</td>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<td align="center">
							{include file='masterfile_sku.edit.items.point.input.tpl' bid=$bid is_edit=$is_edit b_cat_point=$item_obj.category_point_by_branch_inherit.$bid editable=$item_obj.category_point_by_branch_inherit.$bid.set_override}
						</td>
					{/if}
				{/foreach}
			{/if}
		</tr>
		
		<!-- member type -->
		{foreach from=$config.membership_type key=member_type item=mtype_desc name=fmt}
			{if is_numeric($member_type)}
				{assign var=mt value=$mtype_desc}
			{else}
				{assign var=mt value=$member_type}
			{/if}
			{if $smarty.foreach.fmt.first}
				<tr>
					<td colspan="{if $hide_branches}2{else}{count var=$branch_list offset=2}{/if}">Member Type (Leave empty will follow member)</td>
				</tr>
			{/if}
			
			<tr>
				<td><b>{$mtype_desc}</b></td>
				<td align="center">
					{include file='masterfile_sku.edit.items.point.input.tpl' bid=0 is_edit=$is_edit type_col=$mt b_cat_point=$item_obj.category_point_by_branch_inherit.0 editable=$item_obj.category_point_by_branch_inherit.0.set_override}
				</td>
				{if !$hide_branches}
					{foreach from=$branch_list key=bid item=b}
						{if $bid>1}
							<td align="center">
								{include file='masterfile_sku.edit.items.point.input.tpl' bid=$bid is_edit=$is_edit type_col=$mt b_cat_point=$item_obj.category_point_by_branch_inherit.$bid editable=$item_obj.category_point_by_branch_inherit.$bid.set_override}
							</td>
						{/if}
					{/foreach}
				{/if}
			</tr>
		{/foreach}
	</table>
</div>