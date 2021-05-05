{*
3/2/2010 3:26:47 PM Andy
- Add receipt date period checking
- Add option to allow user can one time toggle multiple redemption items to delete

3/12/2010 11:40:10 AM Andy
- Delete button change and multiple delete function.
- Toggle active button change and multiple active/deactive function.
- Fix item cannot approve due to have amount but no end date bugs.
- Add use current date feature for receipt control.

8/11/2010 12:25:52 PM Justin
- Added 2 new fields which is valid date start and end.

8/18/2010 10:50:00 AM Justin
- Highlighted the row if found the item going to expire.

8/27/2010 3:15:31 PM Justin
- Added cash column.
- Disabled all the fields whenever found the redemption items is confirmed.

9/22/2010 12:24:02 PM Justin
- Added Canceled By and Canceled Date fields.

9/27/2010 3:33:48 PM PM Justin
- Modified the alignment for all the fields which require user to key in by integer variables.
- Modified the image display for inactive and active items.
- Added a new hidden field to store item status.
- Added the cancel date to display on system update info.

10/28/2010 4:31:03 PM Justin
- Hidden the Canceled By and Canceled Date columns whenever no config set.
- Added different output display with/without set config.
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

11/8/2010 11:04:29 AM Justin
- Added the missing status icon whenever login to sub branch.
- Disabled user from delete the item when the "Created By" is not belong to the current logged in branch.

11/12/2010 3:23:55 PM Justin
- Added the message of "Inactive" on bottom of item's status icon.

1/20/2011 2:08:55 PM Justin
- Added both created and approved by columns.

2/15/2011 3:44:46 PM Justin
- Modified the calendar icon not to drop down into second line.

10/12/2011 3:45:54 PM Alex
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

1/14/2013 2:09 PM Justin
- Enhanced to show voucher value for user to maintain.
- Enhanced to enable/disable voucher value field.
*}

<tr class="tr_item {if $smarty.request.highlight_item_id eq $item.sku_item_id || $item.days_left <= $config.membership_redemption_expire_days && $item.days_left > 0 && $item.active eq '1'}highlight_row{/if}" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="tr_item,{$item.branch_id}_{$item.id}">
	<td align="center" width="40" nowrap>
		{if (!$item.confirm || !$config.membership_redemption_use_enhanced) && ($item.branch_id eq $sessioninfo.branch_id || $BRANCH_CODE eq 'HQ')}
			<img src="/ui/icons/delete.png" width="17" title="Delete Item" class="clickable" onclick="ajax_delete_item('{$item.branch_id}','{$item.id}',this);" align="absmiddle" id="img_del,{$item.branch_id},{$item.id}" />
			<input type="hidden" name="item_is_delete[{$item.branch_id}_{$item.id}]" value="0" class="dont_disabled" id="inp_item_is_delete,{$item.branch_id}_{$item.id}" />
		{else}
			<img width="17" src="/ui/pixel.gif" />
		{/if}
		<span style="vertical-align:bottom;" class="td_no" id="td_no,{$item.branch_id}_{$item.id}">
			{$smarty.foreach.fitem.iteration}.
		</span>
			<input type="hidden" name="item_sku_item_code[{$item.branch_id}_{$item.id}]" value="{$item.sku_item_code}" />
		    <input type="hidden" name="item_sku_item_id[{$item.branch_id}_{$item.id}]" title="{$item.sku_item_code}" value="{$item.sku_item_id}" />
		    <input type="hidden" name="item_array[{$item.branch_id}_{$item.id}]" value="{$item.branch_id},{$item.id}" class="dont_disabled" />
		    <input type="hidden" name="item_status[{$item.branch_id}_{$item.id}]" id="item_status,{$item.branch_id},{$item.id}" value="{$item.active}" />
		    <input type="hidden" name="item_confirm[{$item.branch_id}_{$item.id}]" id="item_confirm,{$item.branch_id},{$item.id}" value="{$item.confirm}" />
	</td>
	<td align="center" width="70" nowrap>
	{if ($item.confirm && $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ') || ($item.confirm && $config.membership_redemption_use_enhanced)}
		<img src="ui/approved.png" style="vertical-align:top;" title="This item has been confirmed">
	{else}
		<img src="{if $item.active}ui/deact.png{else}ui/act.png{/if}" title="{if !$item.active}Activate{else}Deactivate{/if}" align="absmiddle" id="img_act,{$item.branch_id},{$item.id}" class="clickable" onclick="toggle_active_status('{$item.branch_id}','{$item.id}', this);" /><div id="inac_area,{$item.branch_id},{$item.id}">{if !$item.active}(Inactive){/if}</div>
	{/if}
	</td>
	<td align="center">
	{if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
		&nbsp;
	{else}
		<input type="checkbox" class="chx_item" title="Select this item" name="sel_item[{$item.branch_id}_{$item.id}]" value="1" align="absmiddle" id="inp_item,{$item.branch_id},{$item.id}" />
	{/if}
	</td>
    <td align="center">{$item.bcode}</td>
    <td align="center" width="200">
        {if $BRANCH_CODE eq 'HQ' and !$item.confirm || !$config.membership_redemption_use_enhanced}
        <input type="hidden" name="available_branches2[{$item.branch_id}_{$item.id}]" id="available_branches2,{$item.branch_id}_{$item.id}" value="{$item.available_branches2}" />
        <div style="float:right;">
            <a href="javascript:void(edit_available_branches('{$item.id}','{$item.branch_id}'));">
			<img src="/ui/icons/pencil.png" border="0" title="Edit Available Branches">
			</a>
		</div>
		{/if}

		<div id="div_ab_list,{$item.branch_id}_{$item.id}" class="crop" >
		{if $item.available_branches}
		    {assign var=ab_count value=0}
			{foreach from=$item.available_branches key=bid item=dummy name=fab}
			    {assign var=ab_count value=$ab_count+1}
			    {if $ab_count<=5}{$branches.$bid.code}{if !$smarty.foreach.fab.last},&nbsp;{/if}{/if}

			{/foreach}
			{if $ab_count>5}...{/if}
		{/if}
		</div>
		<span id="span_ab_total_b,{$item.branch_id}_{$item.id}" style="font-weight:bold;">{if $ab_count>1}({$ab_count} branches){/if}</span>
	</td>
	<td>{$item.sku_item_code|default:"-"}</td>
	<td>{$item.description}</td>
	<td align="right">{$item.grn_cost|number_format:$config.global_cost_decimal_points}</td>
	<td align="right">{$item.selling_price|number_format:2|ifzero:"-"}</td>
	<td align="right">{$item.qty|qty_nf}</td>
	<td align="right">
	    {if $item.branch_id ne $sessioninfo.branch_id and $sessioninfo.branch_id ne 1 || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.point|default:0}
			<input type="hidden" name="point[{$item.branch_id}_{$item.id}]" id="point,{$item.branch_id}_{$item.id}" value="{$item.point}" />
	    {else}
		<input size="5" name="point[{$item.branch_id}_{$item.id}]" class="inp_point" id="point,{$item.branch_id}_{$item.id}" value="{$item.point}" onFocus="select_ele(this);" onchange="mi(this);" style="text-align:right;" />
		{/if}
	</td>
	<td align="right">
	    {if $item.branch_id ne $sessioninfo.branch_id and $sessioninfo.branch_id ne 1 || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.cash|default:0}
	    {else}
			<input size="5" name="cash[{$item.branch_id}_{$item.id}]" class="inp_cash" id="cash,{$item.branch_id}_{$item.id}" value="{$item.cash}" onFocus="select_ele(this);" onchange="this.value=round(this.value,2);" style="text-align:right;" />
		{/if}
	</td>
	<td align="center" nowrap>
		{if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.valid_date_from|ifzero:'-'}
		{else}
		    <input type="text" size="11" name="valid_date_from[{$item.branch_id}_{$item.id}]" value="{$item.valid_date_from|ifzero:''}" id="valid_date_from_{$item.branch_id}_{$item.id}" onFocus="this.select();" />
			<img src="ui/calendar.gif" align="top" title="Choose Valid Date From" class="clickable" id="img_valid_date_from_{$item.branch_id}_{$item.id}" />
			<script>init_calendar('valid_date_from_{$item.branch_id}_{$item.id}','img_valid_date_from_{$item.branch_id}_{$item.id}');</script>
		{/if}
	</td>
	<td align="center" nowrap>
		{if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.valid_date_to|ifzero:'-'}
		{else}
	   	<input type="text" size="11" name="valid_date_to[{$item.branch_id}_{$item.id}]" value="{$item.valid_date_to|ifzero:''}" id="valid_date_to_{$item.branch_id}_{$item.id}" onFocus="this.select();" />
		<img src="ui/calendar.gif" align="top" title="Choose Valid Date To" class="clickable" id="img_valid_date_to_{$item.branch_id}_{$item.id}" />
		<script>init_calendar('valid_date_to_{$item.branch_id}_{$item.id}','img_valid_date_to_{$item.branch_id}_{$item.id}');</script>
		{/if}
	</td>
	<td align="right">
	    {if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.receipt_amount|number_format:2|default:0}
			<input type="hidden" name="receipt_amount[{$item.branch_id}_{$item.id}]" class="inp_receipt_amount" id="receipt_amount,{$item.branch_id}_{$item.id}" value="{$item.receipt_amount}" />
	    {else}
  			<input size="5" name="receipt_amount[{$item.branch_id}_{$item.id}]" class="inp_receipt_amount" id="receipt_amount,{$item.branch_id}_{$item.id}" value="{$item.receipt_amount}" onFocus="select_ele(this);" onChange="receipt_amt_changed(this);" style="text-align:right;" />
  		{/if}
	</td>
	<td align="right" nowrap>
		{if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.receipt_date_from|ifzero:'-'}
		{else}
		 	<input type="text" size="11" name="receipt_date_from[{$item.branch_id}_{$item.id}]" value="{$item.receipt_date_from|ifzero:''}" id="inp_receipt_date_from_{$item.branch_id}_{$item.id}" {if !$item.receipt_amount || $item.use_curr_date}readonly {/if} onFocus="this.select();" />
			<img src="ui/calendar.gif" align="top" title="Choose Receipt Date From" class="clickable" id="img_receipt_date_from_{$item.branch_id}_{$item.id}" {if !$item.receipt_amount || $item.use_curr_date}style="display:none;"{/if} />
			<script>init_calendar('inp_receipt_date_from_{$item.branch_id}_{$item.id}','img_receipt_date_from_{$item.branch_id}_{$item.id}');</script>
		{/if}
	</td>
	<td align="center" nowrap>
		{if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || $item.confirm && $config.membership_redemption_use_enhanced}
			{$item.receipt_date_to|ifzero:'-'}
		{else}
			<input type="text" size="11" name="receipt_date_to[{$item.branch_id}_{$item.id}]" value="{$item.receipt_date_to|ifzero:''}" id="inp_receipt_date_to_{$item.branch_id}_{$item.id}" {if !$item.receipt_amount || $item.use_curr_date}readonly {/if} onFocus="this.select();" />
			<img src="ui/calendar.gif" align="top" title="Choose Receipt Date To" class="clickable" id="img_receipt_date_to_{$item.branch_id}_{$item.id}" {if !$item.receipt_amount || $item.use_curr_date}style="display:none;"{/if} />
			<script>init_calendar('inp_receipt_date_to_{$item.branch_id}_{$item.id}','img_receipt_date_to_{$item.branch_id}_{$item.id}');</script>
		{/if}
	</td>
	<td align="center">
	    <input type="checkbox" name="use_curr_date[{$item.branch_id}_{$item.id}]" id="use_curr_date{$item.branch_id}_{$item.id}" value="1" {if $item.use_curr_date}checked {/if} onclick="use_curr_date('{$item.branch_id}', '{$item.id}');" {if $item.branch_id ne $sessioninfo.branch_id and $BRANCH_CODE ne 'HQ' || !$item.receipt_amount || $item.confirm}disabled{/if} />
	</td>
	{if $config.membership_redemption_use_enhanced}
		{if $item.cancel_by}
			<td class="nl">{$item.cancel_user}</td>
			<td class="nl">{$item.cancel_date}</td>
		{elseif $item.active eq '0' && $item.confirm eq '1'}
			<td colspan="2" class="nl">Expired - System Updated on {$item.cancel_date}</td>
		{else}
			<td align="center">-</td>
			<td align="center">-</td>
		{/if}
	{/if}
	<td align="center">{$item.create_user|default:"-"}</td>
	<td align="center">{$item.approve_user|default:"-"}</td>
	{if $config.membership_use_voucher}
		<td align="center" nowrap>
			<input size="5" name="voucher_value[{$item.branch_id}_{$item.id}]" class="inp_voucher_value" id="voucher_value,{$item.branch_id}_{$item.id}" {if $item.voucher_value ne 0}value="{$item.voucher_value}"{else}disabled{/if} onFocus="select_ele(this);" onChange="mf(this);" style="text-align:right;" />
			<input type="checkbox" class="chx_item" title="Use/unused Voucher for this item" name="use_voucher[{$item.branch_id}_{$item.id}]" value="1" align="absmiddle" id="inp_use_voucher,{$item.branch_id},{$item.id}" {if $item.voucher_value}checked{/if} onclick="use_voucher('{$item.branch_id}','{$item.id}', this);" />
		</td>
	{/if}
</tr>
