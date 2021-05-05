{*
4/20/2017 10:21 AM Khausalya
-Enhanced change from RM to use config setting.

5/3/2018 1:13 PM Andy
- Added Foreign Currency feature.
*}


<div class="noscreen">
	<h4>{$title}</h4>
</div>

{if !$vendor_po_list}
	** no data **
{else}
	{if $config.foreign_currency}
		{$LANG.BASE_CURRENCY_CONVERT_NOTICE}
	{/if}
	
	<table id="tbl_po" class="report_table" width="100%">
		<tr class="header">
			<th>&nbsp;</th>
			<th>Vendor</th>
			
			{* Got Foreign Currency *}
			{if $currency_code_list}
				<th>Amount ({$config.arms_currency.code})</th>
				{foreach from=$currency_code_list item=code}
					<th>Amount ({$code})</th>
				{/foreach}
			{/if}
			<th>Total PO Amount ({$config.arms_currency.code})</th>
			<th>No of PO</th>
		</tr>

		{foreach from=$vendor_po_list key=vendor_id item=r name=i}
			<tr bgcolor='{cycle values=",#eeeeee"}'>
				<td>{$smarty.foreach.i.iteration}.</td>
				<td><a href="javascript:void(zoom_vendor('{$vendor_id}'))">{$r.vendor_desc}</a></td>
				
				{* Got Foreign Currency *}
				{if $currency_code_list}
					<td align="right">{$r.base_currency.amt|number_format:2|ifzero:''}</td>
					{foreach from=$currency_code_list item=code}
						<td align="right">{$r.currency.$code.amt|number_format:2|ifzero:''}</td>
					{/foreach}
				{/if}
				
				<td align="right">
					{if $r.got_currency}<span class="converted_base_amt">{/if}
					{$r.base_po_amount|number_format:2}
					{if $r.got_currency}</span>*{/if}
				</td>
				<td align="right">{$r.no_of_po}</td>
			</tr>
			{assign var=sum_po_amt value=$sum_po_amt+$item.po_amount}
			{assign var=sum_no_of_po value=$sum_no_of_po+$item.no_of_po}
		{/foreach}

		<tr class="header">
			<th colspan="2" align="right">Total</th>
			
			{* Got Foreign Currency *}
			{if $currency_code_list}
				<td align="right">{$total.base_currency.amt|number_format:2}</td>
				{foreach from=$currency_code_list item=code}
					<td align="right">{$total.currency.$code.amt|number_format:2|ifzero:''}</td>
				{/foreach}
			{/if}
				
			<th align="right">
				{if $total.got_currency}<span class="converted_base_amt">{/if}
				{$total.base_po_amount|number_format:2}
				{if $total.got_currency}</span>*{/if}
			</th>
			<th align="right">{$total.no_of_po}</th>
		</tr>
	</table>
{/if}
