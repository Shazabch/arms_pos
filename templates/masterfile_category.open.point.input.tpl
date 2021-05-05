{*
8/30/2012 5:56 PM Justin
- Enhanced to allow user edit Member Points Reward while has privilege "MEMBER_POINT_REWARD_EDIT".
*}

{if !$type_col}{assign var=type_col value='global'}{/if}

{if $is_edit && $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
	<input type="text" name="category_point_by_branch[{$bid}][{$type_col}]" value="{$b_cat_point.$type_col}" size="3" id="inp-category_point_value-{$bid}-{$type_col}" onChange="category_point_value_changed(this);"  class="inp_category_point-{$bid}" {if !$editable}disabled {/if} />
{else}
	{$b_cat_point.$type_col}
	<input type="hidden" name="category_point_by_branch[{$bid}][{$type_col}]" value="{$b_cat_point.$type_col}" id="inp-category_point_value-{$bid}-{$type_col}" />
{/if}