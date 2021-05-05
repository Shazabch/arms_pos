
{if !$staff_col}{assign var=staff_col value='staff'}{/if}
{if !$type_col}{assign var=type_col value='global'}{/if}

{if $is_edit && $sessioninfo.privilege.CATEGORY_STAFF_DISCOUNT_EDIT}
	<input type="text" name="category_staff_disc_by_branch[{$bid}][{$staff_col}][{$type_col}]" value="{$b_cat_disc.$staff_col.$type_col}" size="5" maxlength="6" id="inp-cat_staff_disc_value-{$staff_col}-{$bid}-{$type_col}" onChange="cat_staff_disc_value_changed(this);" {if !$editable}disabled {/if} class="inp_category_staff_disc-{$bid}" /> %
{else}
	{if isset($b_cat_disc.$staff_col.$type_col) and $b_cat_disc.$staff_col.$type_col !== ''}
		{$b_cat_disc.$staff_col.$type_col} %
		<input type="hidden" name="category_staff_disc_by_branch[{$bid}][{$staff_col}][{$type_col}]" value="{$b_cat_disc.$staff_col.$type_col}" id="inp-cat_staff_disc_value-{$staff_col}-{$bid}-{$type_col}" />
	{/if}
{/if}