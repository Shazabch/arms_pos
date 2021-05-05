{*
10/28/2016 15:05 Qiu Ying
- Enhanced the backend Mprice module to show both 1) Member's type 2) Member1, Member2, Member 3(remain)

11/8/2016 9:35 AM Qiu Ying
- Bug fixed on MPrice name should show one times when membership_type same name as sku_multiple selling_price
*}
<!-- additional sellings: mqprice -->
{if $sku_multiple_selling_price}
	{if $BRANCH_CODE eq 'HQ'}
		{foreach from=$sku_multiple_selling_price item=s}
			{assign var=mqprice value=$items[i].mqprice.$min_qty.$s.1.price}
			<tr>
				<td><b>{if $s ne "member" && preg_match("/member/i", $s)}&nbsp;&nbsp;&nbsp;&nbsp;{/if}{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}</b></td>
				<td>&nbsp;</td>
				<td>
					<input size=3 name="mqprice[{$items[i].id}][{$s}][1][]" value="{$items[i].mqprice.$min_qty.$s.1.price}" onchange="mf(this,2);copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '1');" class="inp_qprice" />
				</td>
		
				{foreach from=$branch item=b}
					{if $b.code ne 'HQ'}
						{assign var=bid value=$b.id}
						{assign var=mqprice value=$items[i].mqprice.$min_qty.$s.$bid.price}
						<!-- Price -->
						<td>
							<input name="mqprice[{$items[i].id}][{$s}][{$bid}][]" value="{$mqprice}" onchange="mf(this);{if $b.code eq 'HQ'}copy_to_branch(this,'mprice[{$items[i].id}][{$s}]', '{$items[i].id}', '{$bid}');{/if}" onfocus="this.select()" size="3"  mqprice_type="{$s}" class="inp_qprice inp_mqprice_{$items[i].id} inp_mqprice_{$items[i].id}_{$bid} branch_selling" id="inp_mqprice-{$s}-{$items[i].id}-{$bid}" readonly />
						</td>
					{/if}
				{/foreach}
			</tr>
		{/foreach}
	{else}
		{foreach from=$sku_multiple_selling_price item=s}
			{assign var=bid value=$sessioninfo.branch_id}
			{assign var=mqprice value=$items[i].mqprice.$min_qty.$s.$bid.price}
			<tr>
				<td><b>{if $s ne "member" && preg_match("/member/i", $s)}&nbsp;&nbsp;&nbsp;&nbsp;{/if}{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}</b></td>
				<td>&nbsp;</td>

				<!-- Price -->
				<td>
					<input name="mqprice[{$items[i].id}][{$s}][]" value="{$mqprice}" onchange="mf(this);" onfocus="this.select()" size="3"  mqprice_type="{$s}" class="inp_qprice inp_mqprice_{$items[i].id} inp_mqprice_{$items[i].id} branch_selling" id="inp_mqprice-{$s}-{$items[i].id}" readonly />
				</td>
			</tr>
		{/foreach}
	{/if}
{/if}