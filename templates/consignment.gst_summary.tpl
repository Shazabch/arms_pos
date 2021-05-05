{*
9/01/2015 2:05 PM DingRen
- Enhance to show gst amount in foreign amount

4/21/2017 3:56 PM Khausalya
- Enhanced changes from RM to use config setting. 
*}

{if $gst_summary_list}
	<table width="100%" border="0" cellspacing="2" cellpadding="2">
		<tr>
			<th width="40%" style="border:none !important;">GST Summary</th>
			<th style="border:none !important;">Amount ({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
			<th style="border:none !important;">Tax ({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
		</tr>
		{foreach from=$gst_summary_list key=gst_indicator item=r}
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
			<tr>
				<td style="border:none !important;">{$gst_indicator} @ {$r.gst_rate}%</td>
				<td align="right" style="border:none !important;">{$r.total_foreign_amount|number_format:2}</td>
				<td align="right" style="border:none !important;">{$r.total_foreign_gst_amount|number_format:2}</td>
			</tr>
			{else}
			<tr>
				<td style="border:none !important;">{$gst_indicator} @ {$r.gst_rate}%</td>
				<td align="right" style="border:none !important;">{$r.total_amount|number_format:2}</td>
				<td align="right" style="border:none !important;">{$r.total_gst_amount|number_format:2}</td>
			</tr>
			{/if}
		{/foreach}
	</table>
{/if}
