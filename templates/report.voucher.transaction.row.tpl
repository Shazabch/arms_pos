{*
1/31/2013 1:56 PM Andy
- commit the changes done by alex.
*}

<tbody id="tbody_{if $is_fresh_market}fm_row{else}cat_row{/if}-{$cat_id}" class="{if $is_fresh_market}is_fresh_market_row{/if}">
	<tr>
		{if ($type!='total')}
		<th nowrap align="left">
				<a {if $type eq "unknown"}class="red" title="Invalid code" {else}class="blue"{/if} target='_blank' href="report.voucher.details.php?a=show_report&from_date={$smarty.request.from_date}&to_date={$smarty.request.to_date}&branch_id={$smarty.request.branch_id}&counter_name=all&search_code={$id}">{$vc_code}</a>
		</th>
		{else}
			<th class="r">Total</th>
		{/if}
		<th>{$code_amount.$vc_code.amount|number_format:2|ifzero}</th>
		<th style="font-size:8pt">Qty<br />Amt</th>
		{foreach from=$uq_cols key=dt item=d}
		    {assign var=fmt value="%0.2f"}
			{assign var=fmt value="%d"}
			{assign var=qty value=$row.$dt.used}
			{assign var=val value=$row.$dt.amt}

			{capture assign=tooltip}
				Qty:{$row.$dt.used|number_format}  /  Amt:{$row.$dt.amt|string_format:'%.2f'}
			{/capture}
			{if $qty}
				<td class="small" align="right" title='{$tooltip}'>
					<a class="{if $unknown_code.$id}red{else}blue{/if}" target='_blank' href="report.voucher.details.php?a=show_report&from_date={$date_ori.$dt}&to_date={$date_ori.$dt}&branch_id={$smarty.request.branch_id}&counter_name=all&search_code={$vc_code}">
                    {$qty}<br>
                    {$val|number_format:2}
					</a>
				</td>
			{else}
			    <td class="small" align="right">&nbsp;</td>
			{/if}
		{/foreach}
		
		<td class="small" align="right">{$row.total.used}</td>
		<td class="small" align="right">{$row.total.amt|number_format:2}</td>
	</tr>
</tbody>
