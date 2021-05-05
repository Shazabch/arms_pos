{*
8/30/2012 5:56 PM Justin
- Enhanced to allow user edit Category Discount while has privilege "CATEGORY_DISCOUNT_EDIT".
*}

{if !$member_col}{assign var=member_col value='nonmember'}{/if}
{if !$type_col}{assign var=type_col value='global'}{/if}

{if $is_edit && $sessioninfo.privilege.CATEGORY_DISCOUNT_EDIT}
	<input type="text" name="category_disc_by_branch[{$bid}][{$member_col}][{$type_col}]" value="{$b_cat_disc.$member_col.$type_col}" size="5" maxlength="6" id="inp-cat_disc_value-{$member_col}-{$bid}-{$type_col}" onChange="cat_disc_value_changed(this);" {if !$editable}disabled {/if} class="inp_category_disc-{$bid}" /> %
{else}
	{if isset($b_cat_disc.$member_col.$type_col) and $b_cat_disc.$member_col.$type_col !== ''}
		{$b_cat_disc.$member_col.$type_col} %
		<input type="hidden" name="category_disc_by_branch[{$bid}][{$member_col}][{$type_col}]" value="{$b_cat_disc.$member_col.$type_col}" id="inp-cat_disc_value-{$member_col}-{$bid}-{$type_col}" />
	{/if}
{/if}