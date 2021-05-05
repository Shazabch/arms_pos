{*
3/16/2012 12:09:46 PM Andy
- Add Export to excel format.

6/15/2012 12:14:00 PM Andy
- 'admin.sku_export.export.tpl' is no longer use.
*}
{if $export_type eq 'txt'}
{foreach from=$items item=r}
{$r.id|str_pad:$s.0:' '}{$r.sku_item_code|str_pad:$s.1:' '}{$r.artno|str_pad:$s.2:' '}{$r.mcode|str_pad:$s.3:' '}{$r.link_code|str_pad:$s.4:' '}{$r.receipt_description|str_pad:$s.5:' '}{$r.selling|str_pad:$s.6:' '}{$r.cost|str_pad:$s.7:' '}{$r.disc_code|str_pad:$s.8:' '}{$r.sku_type|str_pad:$s.9:' '}{if $show_cat}{$r.p2|str_pad:$s.10:' '}{$r.p3|str_pad:$s.11:' '}{/if}{$r.type1}
{/foreach}
{elseif $export_type eq 'excel'}
	<table>
		<tr style="background-color:#dddddd;">
			<th>SKU ITEM ID</th> 
			<th>ARMS CODE</th> 
			<th>ARTNO</th>
			<th>MCODE</th>
			<th>BARCODE</th>
			<th>RECEIPT DESCRIPTION</th> 
			<th>SELLING PRICE</th>
			<th>COST PRICE</th>
			<th>PRICE TYPE</th>
			<th>SKU TYPE</th>
			{if $show_cat}
				<th>DEPARTMENT</th>
				<th>THIRD LEVEL OF CATEGORY</th>
			{/if} 
			<th>SKU / PRC</th>
		</tr>
	</table>
{/if}