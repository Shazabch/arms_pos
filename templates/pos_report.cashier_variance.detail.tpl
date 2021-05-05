{*
6/2/2015 4:31 PM Justin
- Enhanced to have total cash advance column.

4/20/2017 3:52 PM Khausalya 
- Enhanced chances from RM to use config setting. 

2017-09-14 15:04 PM Qiu Ying
- Enhanced to split by branch when in HQ

01/04/2019 04:47 PM Justin
- Revamped the report.
- Enhanced to include the missing foreign currency info.
*}
{foreach from=$data key=child_cid item=d}
	{foreach from=$d key=date item=r}
		{assign var=total_row value=0}
		<tr class="tr_cashier_child_{$parent_cashier_id}_{$parent_cid}_{$parent_bid} date_details">
			<th align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$date}</th>
	  
			<!-- Normal Payment Method -->
			{foreach from=$normal_payment_type item=payment_type}
				<td class="r {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
					{$r.cash_domination.$payment_type.amt|number_format:2}
					{if $r.cash_domination.$payment_type.amt<>$r.cash_domination.$payment_type.o_amt}
						<br />
						<span class="small" style="color:black;">{$r.cash_domination.$payment_type.o_amt|number_format:2}</span>
					{/if}
					<br />
					{if $payment_type eq 'Cash'}
						<span class="small" style="color:grey;">
						C:{$r.cash_domination.Float.amt+$r.cash_domination.Cash.amt|number_format:2}
						/ F:{$r.cash_domination.Float.amt|number_format:2}
						</span>
					{/if}
				</td>
			{/foreach}
			   
			<!-- Foreign Currency -->
			{if $foreign_currency_list}
				<td class="r {if $r.sub_total.amt<0}negative{/if}">{$r.cash_domination.sub_total.amt|number_format:2}</td>
				{foreach from=$foreign_currency_list key=curr_type item=curr_rate}
					{assign var=payment_type value=$curr_type}
					<td class="r {if $r.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} ">
						{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
		   
						<!-- Currency Float -->
						<br />
						<span class="small" style="color:grey;">
						C:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
						/ F:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
						</span>
					</td>
				{/foreach}
			{/if}
		   
			<td class="r">{$r.cash_advance.amt|number_format:2}</td>
			{assign var=total_row value=$r.cash_domination.sub_total.amt+$r.cash_advance.amt}
			<td class="r" nowrap>
				{if $foreign_currency_list}{$config.arms_currency.symbol}&nbsp;&nbsp;{/if}<span {if $total_row<0}class="negative"{/if}>{$total_row|number_format:2}
				{if $foreign_currency_list}
					<br />
					{foreach from=$foreign_currency_list key=curr_type item=curr_rate name=fc}
						{assign var=payment_type value=$curr_type}
						{assign var=curr_fc_amt value=$r.cash_domination.foreign_currency.$payment_type.foreign_amt}
						{$payment_type}&nbsp;<span {if $curr_fc_amt<0}class="negative"{/if}> {$curr_fc_amt|number_format:2}
						{if !$smarty.foreach.fc.last}<br />{/if}
					{/foreach}
				{/if}
			</td>
			<td class="r date_details_variance {if $r.variance.amt<0}negative{/if}">{$r.variance.amt|number_format:2}</td>
		</tr>
	{/foreach}
{foreachelse}
	<tr><td colspan=""></td></tr>
{/foreach}