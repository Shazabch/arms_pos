{*
7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

8/30/2012 5:56 PM Justin
- Enhanced to allow user edit Category Discount while has privilege "CATEGORY_DISCOUNT_EDIT".
*}
{if $config.consignment_modules}{assign var=hide_branches value=1}{/if}

<div id="div_category_discount_container-member" style="padding:2px;">
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
			<th >All
				<input  type="checkbox" name="category_disc_by_branch[0][set_override]" value="1" {if $form.category_disc_by_branch.0.set_override}checked {/if} title="Override" onChange="category_discount_branch_override_changed(0);" id="inp_category_disc_override-0" {if !$is_edit || !$sessioninfo.privilege.CATEGORY_DISCOUNT_EDIT}disabled {/if} />
			</th>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<th >{$b.code}
							<input type="checkbox" name="category_disc_by_branch[{$bid}][set_override]" value="1" {if $form.category_disc_by_branch.$bid.set_override}checked {/if} title="Override" onChange="category_discount_branch_override_changed('{$bid}');" id="inp_category_disc_override-{$bid}" {if !$is_edit || !$sessioninfo.privilege.CATEGORY_DISCOUNT_EDIT}disabled {/if} />
						</th>
					{/if}
				{/foreach}
			{/if}
		</tr>

		<!-- Member -->
		<tr>
			<td><b class="form-label">Member</b></td>
			<td nowrap>
				{include file='masterfile_category.open.discount.input.tpl' bid=0 member_col='member' is_edit=$is_edit b_cat_disc=$form.category_disc_by_branch.0 editable=$form.category_disc_by_branch.0.set_override}
			</td>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<td nowrap>
							{include file='masterfile_category.open.discount.input.tpl' bid=$bid member_col='member' is_edit=$is_edit b_cat_disc=$form.category_disc_by_branch.$bid editable=$form.category_disc_by_branch.$bid.set_override}
						</td>
					{/if}
				{/foreach}
			{/if}
		</tr>
		
		<!-- Non-member -->
		<tr>
			<td><b class="form-label">Non-Member</b></td>
			<td nowrap>
				{include file='masterfile_category.open.discount.input.tpl' bid=0 is_edit=$is_edit b_cat_disc=$form.category_disc_by_branch.0 editable=$form.category_disc_by_branch.0.set_override}
			</td>
			{if !$hide_branches}
				{foreach from=$branch_list key=bid item=b}
					{if $bid>1}
						<td nowrap>
							{include file='masterfile_category.open.discount.input.tpl' bid=$bid is_edit=$is_edit b_cat_disc=$form.category_disc_by_branch.$bid editable=$form.category_disc_by_branch.$bid.set_override}
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
				<td><b class="form-label">{$mtype_desc}</b></td>
				<td nowrap>
					{include file='masterfile_category.open.discount.input.tpl' bid=0 is_edit=$is_edit member_col='member' type_col=$mt b_cat_disc=$form.category_disc_by_branch.0 editable=$form.category_disc_by_branch.0.set_override}
				</td>
				{if !$hide_branches}
					{foreach from=$branch_list key=bid item=b}
						{if $bid>1}
							<td nowrap>
								{include file='masterfile_category.open.discount.input.tpl' bid=$bid is_edit=$is_edit member_col='member' type_col=$mt b_cat_disc=$form.category_disc_by_branch.$bid editable=$form.category_disc_by_branch.$bid.set_override}
							</td>
						{/if}
					{/foreach}
				{/if}
			</tr>
		{/foreach}
	</table>
</div>