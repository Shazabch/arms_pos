
{if $config.consignment_modules}{assign var=hide_branches value=1}{/if}

<div id="div_category_staff_discount_container-member" style="padding:2px;">
	<table class="report_table">
		<tr class="header">
			<th rowspan="2">&nbsp;</th>
			<th {if !$hide_branches}colspan="{count var=$branch_list}"{/if}>Discount %
				{if !$hide_branches}
					(set on individual branch to override "All")
				{/if}
			</th>
		</tr>
		<tr class="header">
			<th>All
				<input type="checkbox" name="category_staff_disc_by_branch[0][set_override]" value="1" {if $form.category_staff_disc_by_branch.0.set_override}checked {/if} title="Override" onChange="category_staff_discount_branch_override_changed(0);" id="inp_category_staff_disc_override-0" {if !$is_edit || !$sessioninfo.privilege.CATEGORY_STAFF_DISCOUNT_EDIT}disabled {/if} />
			</th>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<th>{$b.code}
							<input type="checkbox" name="category_staff_disc_by_branch[{$bid}][set_override]" value="1" {if $form.category_staff_disc_by_branch.$bid.set_override}checked {/if} title="Override" onChange="category_staff_discount_branch_override_changed('{$bid}');" id="inp_category_staff_disc_override-{$bid}" {if !$is_edit || !$sessioninfo.privilege.CATEGORY_STAFF_DISCOUNT_EDIT}disabled {/if} />
						</th>
					{/if}
				{/foreach}
			{/if}
		</tr>

		<!-- All Staff -->
		<tr>
			<td><b>All Staff</b></td>
			<td nowrap>
				{include file='masterfile_category.open.staff_discount.input.tpl' bid=0 staff_col='staff' is_edit=$is_edit b_cat_disc=$form.category_staff_disc_by_branch.0 editable=$form.category_staff_disc_by_branch.0.set_override}
			</td>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<td nowrap>
							{include file='masterfile_category.open.staff_discount.input.tpl' bid=$bid staff_col='staff' is_edit=$is_edit b_cat_disc=$form.category_staff_disc_by_branch.$bid editable=$form.category_staff_disc_by_branch.$bid.set_override}
						</td>
					{/if}
				{/foreach}
			{/if}
		</tr>
		
		<!-- Staff Type -->
		{foreach from=$config.membership_staff_type key=staff_type item=staff_desc name=fmt}
			{if $smarty.foreach.fmt.first}
				<tr>
					<td colspan="{if $hide_branches}2{else}{count var=$branch_list offset=2}{/if}">Staff Type (Leave empty will follow "All Staff")</td>
				</tr>
			{/if}
			
			<tr>
				<td><b>{$staff_desc}</b></td>
				<td nowrap>
					{include file='masterfile_category.open.staff_discount.input.tpl' bid=0 is_edit=$is_edit staff_col='staff' type_col=$staff_type b_cat_disc=$form.category_staff_disc_by_branch.0 editable=$form.category_staff_disc_by_branch.0.set_override}
				</td>
				{if !$hide_branches}
					{foreach from=$branch_list key=bid item=b}
						{if $bid>1}
							<td nowrap>
								{include file='masterfile_category.open.staff_discount.input.tpl' bid=$bid is_edit=$is_edit staff_col='staff' type_col=$staff_type b_cat_disc=$form.category_staff_disc_by_branch.$bid editable=$form.category_staff_disc_by_branch.$bid.set_override}
							</td>
						{/if}
					{/foreach}
				{/if}
			</tr>
		{/foreach}
	</table>
</div>