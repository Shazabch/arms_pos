<tbody>
	<tr>
		<th nowrap align="left">
			<a {if $unknown_code.$cp_code}class="red" title="Invalid code" {else}class="blue"{/if} target='_blank' href="report.coupon.details.php?a=show_report&branch_id={$smarty.request.branch_id}&counter_name=all&dept_id={$smarty.request.dept_id}&r_type={$smarty.request.r_type}&brand_id={$smarty.request.brand_id}&vendor_id={$smarty.request.vendor_id}&from_date={$smarty.request.from_date}&to_date={$smarty.request.to_date}&search_code={if $id ne 'Unknown'}{$cp_code}{/if}">{$cp_code}</a>
		</th>
		<th>
			{if $code_amount.$cp_code.percentage}
				{$code_amount.$cp_code.percentage|ifzero}
			{else}
			    {$code_amount.$cp_code.amount|number_format:2|ifzero}
			{/if}
		</th>
		<th style="font-size:8pt">Qty<br>Amt</th>
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
					<a class="{if $unknown_code.$id}red{else}blue{/if}" target='_blank' href="report.coupon.details.php?a=show_report&&branch_id={$smarty.request.branch_id}&counter_name=all&dept_id={$smarty.request.dept_id}&r_type={$smarty.request.r_type}&brand_id={$smarty.request.brand_id}&vendor_id={$smarty.request.vendor_id}&from_date={$date_ori.$dt}&to_date={$date_ori.$dt}&search_code={$cp_code}">
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
