{*
2/11/2020 5:58 PM Andy
- Added new coupon feature "Referral Program".
*}

{foreach from=$member_list item=r name=f_mem}
	<tr id="tr_member-{$r.card_no}">
		<td align="center"><span class="span_row_no">{$smarty.foreach.f_mem.iteration}</span>.</td>
		{if $coupon_items.member_limit_type eq 'selected_member' or $coupon_items.member_limit_type eq 'referral_program'}
			<td align="center">			
				<img src="ui/clock.gif" id="img_active_loading-{$r.card_no}" style="display:none;" />
				<input type="checkbox" name="active[{$r.card_no}]" value="1" {if $r.active}checked {/if} id="cbx_active-{$r.card_no}" onChange="COUPON_MEMBER.active_changed('{$r.card_no}');" />
			</td>
			{if $coupon_items.member_limit_type eq 'selected_member'}
				<td>
					<img src="ui/del.png" align="absmiddle" onClick="COUPON_MEMBER.delete_clicked('{$r.card_no}');" />
				</td>
			{/if}
		{/if}
		<td>{$r.nric}</td>
		<td>{$r.card_no}</td>
		<td>{$r.name}</td>
		
		{if $coupon_items.member_limit_type eq 'referral_program'}
			{if $coupon_items.referrer_coupon_get > 0}
				<td align="right">{$r.referrer_count|number_format}</td>
				<td align="right">{$r.referrer_max_use|number_format}</td>
			{/if}
			{if $coupon_items.referee_coupon_get > 0}
				<td align="right">{$r.referee_max_use|number_format}</td>
			{/if}
		{/if}
		
		<td align="right">{$r.used_count}</td>
		<td align="center">{$r.added}</td>
		<td align="center">{$r.last_update}</td>
	</tr>
{foreachelse}
	<tr>
		{assign var=cols value=7}
		{if $coupon_items.member_limit_type eq 'selected_member' || $coupon_items.member_limit_type eq 'referral_program'}
			{assign var=cols value=$cols+1}
			{if $coupon_items.member_limit_type eq 'selected_member'}
				{assign var=cols value=$cols+1}
			{/if}
		{/if}
		{if $coupon_items.member_limit_type eq 'referral_program'}
			{if $coupon_items.referrer_coupon_get > 0}
				{assign var=cols value=$cols+2}
			{/if}
			{if $coupon_items.referee_coupon_get > 0}
				{assign var=cols value=$cols+1}
			{/if}
		{/if}
		<td colspan="{$cols}">No Data</td>
	</tr>
{/foreach}