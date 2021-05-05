{*
3/19/2012 2:36:32 PM Alex
- add list stock before stock check 
- add checking no data no show 
*}

{if $sku_type == 'FRESH'}
	<b>Department: </b> {$department} (FRESH MARKET WEIGHT)
{else}
	<b>Department: </b> {$department} &nbsp;&nbsp;&nbsp;&nbsp; <b>Vendor:</b> {if !$vendor}Other{else}{$vendor}{/if} &nbsp;&nbsp;&nbsp;&nbsp; <b>SKU Type:</b> {$sku_type}
{/if} 

{if !$item_vos && !$item_vstv && !$item_vsr && !$item_vadj && !$item_vrs && !$item_vpa && !$item_vpca && !$item_vacs}
	<h5>No item record for this month.</h5> 
{else}
	<table class="report_table">
		<tr class="header">
			<th>ARMS Code</th>
			<th>Receipt Description</th>
			<th>&nbsp;</th>
			<th>Good Received(GRN)</th>
			{assign var=col3 value="Good Received(GRN)"}
			<th>GP (%)</th>
			{assign var=colgp value="GP (%)"}
			<th>Adjustment(ARMS)</th>
			{assign var=col4 value="Adjustment(ARMS)"}
			<th>GP (%)</th>
			<th>Return Stock</th>
			{assign var=col5 value="Return Stock"}
			<th>GP (%)</th>
			<th>Promotion Amount</th>
			{assign var=col6 value="Promotion Amount"}
			<th>(%)</th>
			{assign var=colper value="(%)"}
			<th>Price Change Amount</th>
			{assign var=col7 value="Price Change Amount"}
			<th>(%)</th>
			<th>Actual Sales</th>
			{assign var=col8 value="Actual Sales"}
			<th>GP (%)</th>
		</tr>
		{assign var=percent value="%"}
		{assign var=cost value="Cost Price"}
		{assign var=selling value="Selling Price"}
		{foreach from=$sku_items key=sid item=other}
			{if $item_vsr.$sid.cost_price || $item_vsr.$sid.selling_price || $item_vadj.$sid.cost_price || $item_vadj.$sid.selling_price || $item_vrs.$sid.cost_price || $item_vrs.$sid.selling_price || $item_vpa.$sid.cost_price || $item_vpa.$sid.selling_price || $item_vpca.$sid.cost_price || $item_vpca.$sid.selling_price || $item_vacs.$sid.cost_price || $item_vacs.$sid.selling_price}
				{assign var=item_descrip value=$other.receipt_description}
				{assign var=item_descrip value="Item: $item_descrip"}
				{if $sku_type == 'FRESH'}
					<tr>
						<td>{$other.sku_item_code}</td>
						<td>{$other.receipt_description}</td>
						<td>CP</td>
						<td title="{$col3}|{$item_descrip}" class="r">{$item_vsr.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colgp}|{$item_descrip}">&nbsp;</td>
						<td title="{$col4}|{$item_descrip}" class="r">{$item_vadj.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colgp}|{$item_descrip}">&nbsp;</td>
						<td title="{$col5}|{$item_descrip}" class="r">{$item_vrs.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colgp}|{$item_descrip}">&nbsp;</td>
						<td title="{$col6}|{$item_descrip}" class="r">{$item_vpa.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colper}|{$item_descrip}">&nbsp;</td>
						<td title="{$col7}|{$item_descrip}" class="r">{$item_vpca.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colper}|{$item_descrip}">&nbsp;</td>
						<td title="{$col8}|{$item_descrip}" class="r">{$item_vacs.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						<td title="{$colgp}|{$item_descrip}">&nbsp;</td>
					</tr>
				{else}
					<tr>
						<td rowspan=2>{$other.sku_item_code}</td>
						<td rowspan=2>{$other.receipt_description}</td>
						<th>CP</th>
						{if $sku_type == 'OUTRIGHT'}
							<td title="{$col3}|{$item_descrip}" class="r">{$item_vsr.$sid.cost_price|number_format:2|ifzero:'-'}</td>
							{assign var=costprice value=$item_vsr.$sid.cost_price}
							{assign var=sellingprice value=$item_vsr.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=gpvalue value=$sellingprice-$costprice}
								{assign var=gpvalue value=$gpvalue/$sellingprice}
							{else}
								{assign var=gpvalue value=0}
							{/if}
							<td title="{$colgp}|{$item_descrip}" rowspan=2 class="r">{$gpvalue|number_format:2|ifzero:'-':$percent}</td>
							<td title="{$col4}|{$item_descrip}" class="r">{$item_vadj.$sid.cost_price|number_format:2|ifzero:'-'}</td>
							{assign var=costprice value=$item_vadj.$sid.cost_price}
							{assign var=sellingprice value=$item_vadj.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=gpvalue value=$sellingprice-$costprice}
								{assign var=gpvalue value=$gpvalue/$sellingprice}
							{else}
								{assign var=gpvalue value=0}
							{/if}
							<td title="{$colgp}|{$item_descrip}" rowspan=2 class="r">{$gpvalue|number_format:2|ifzero:'-':$percent}</td>
							<td title="{$col5}|{$item_descrip}" class="r">{$item_vrs.$sid.cost_price|number_format:2|ifzero:'-'}</td>
							{assign var=costprice value=$item_vrs.$sid.cost_price}
							{assign var=sellingprice value=$item_vrs.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=gpvalue value=$sellingprice-$costprice}
								{assign var=gpvalue value=$gpvalue/$sellingprice}
							{else}
								{assign var=gpvalue value=0}
							{/if}
							<td title="{$colgp}|{$item_descrip}" rowspan=2 class="r">{$gpvalue|number_format:2|ifzero:'-':$percent}</td>
							<td title="{$col6}|{$item_descrip}" rowspan=2 class="r">{$item_vpa.$sid.selling_price|number_format:2|ifzero:'-'}</td>
							{assign var=sprice value=$item_vpa.$sid.selling_price}
							{assign var=sellingprice value=$item_vacs.$sid.cost_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=pervalue value=$sprice/$sellingprice*100}
							{else}
								{assign var=pervalue value=0}
							{/if}
							<td title="{$colper}|{$item_descrip}" rowspan=2 class="r">{$pervalue|number_format:2|ifzero:'-':$percent}</td>
							<td title="{$col7}|{$item_descrip}" rowspan=2 class="r">{$item_vpca.$sid.selling_price|number_format:2|ifzero:'-'}</td>
							{assign var=sprice value=$item_vpca.$sid.selling_price}
							{assign var=sellingprice value=$item_vacs.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=pervalue value=$sprice/$sellingprice*100}
							{else}
								{assign var=pervalue value=0}
							{/if}
							<td title="{$colper}|{$item_descrip}" rowspan=2 class="r">{$pervalue|number_format:2|ifzero:'-':$percent}</td>
							<td title="{$col8}|{$item_descrip}" class="r">{$item_vacs.$sid.cost_price|number_format:2|ifzero:'-'}</td>
							{assign var=costprice value=$item_vacs.$sid.cost_price}
							{assign var=sellingprice value=$item_vacs.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=gpvalue value=$sellingprice-$costprice}
								{assign var=gpvalue value=$gpvalue/$sellingprice}
							{else}
								{assign var=gpvalue value=0}
							{/if}
							<td title="{$colgp}|{$item_descrip}" rowspan=2 class="r">{$gpvalue|number_format:2|ifzero:'-':$percent}</td>
						{elseif $sku_type == 'CONSIGN'}
							<td title="{$col3}|{$item_descrip}">&nbsp;</td>
							<td title="{$colgp}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$col4}|{$item_descrip}">&nbsp;</td>
							<td title="{$colgp}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$col5}|{$item_descrip}">&nbsp;</td>
							<td title="{$colgp}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$col6}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$colper}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$col7}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$colper}|{$item_descrip}" rowspan=2>&nbsp;</td>
							<td title="{$col8}|{$item_descrip}" class="r">{$item_vacs.$sid.cost_price|number_format:2|ifzero:'-'}</td>	
							{assign var=costprice value=$item_vacs.$sid.cost_price}
							{assign var=sellingprice value=$item_vacs.$sid.selling_price}
							{if $sellingprice ne 0 or $sellingprice ne ''}
								{assign var=gpvalue value=$sellingprice-$costprice}
								{assign var=gpvalue value=$gpvalue/$sellingprice}
							{else}
								{assign var=gpvalue value=0}
							{/if}			
							<td title="{$colgp}|{$item_descrip}" rowspan=2>&nbsp;</td>
						{/if}
					</tr>
					<tr>
						<th>SP</th>
						{if $sku_type == 'OUTRIGHT'}
							<td title="{$col3}|{$item_descrip}" class="r">{$item_vsr.$sid.selling_price|number_format:2|ifzero:'-'}</td>
							<td title="{$col4}|{$item_descrip}" class="r">{$item_vadj.$sid.selling_price|number_format:2|ifzero:'-'}</td>
							<td title="{$col5}|{$item_descrip}" class="r">{$item_vrs.$sid.selling_price|number_format:2|ifzero:'-'}</td>
							<td title="{$col8}|{$item_descrip}" class="r">{$item_vacs.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						{elseif $sku_type == 'CONSIGN'}
							<td title="{$col3}|{$item_descrip}">&nbsp;</td>
							<td title="{$col4}|{$item_descrip}">&nbsp;</td>
							<td title="{$col5}|{$item_descrip}">&nbsp;</td>
							<td title="{$col8}|{$item_descrip}" class="r">{$item_vacs.$sid.selling_price|number_format:2|ifzero:'-'}</td>
						{/if}	
					</tr>
				{/if}
			{/if}
		{/foreach}
	</table>
{/if}