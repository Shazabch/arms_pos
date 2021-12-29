{*
Revision History
================
4 Apr 2007 - yinsee
- 	replace 'Multics Code' with $config.link_code_name

4/22/2009 12:05:47 PM yinsee
- fix numbering bug

12/28/2009 9:32:03 AM edward
- add location and qty balance.

8/13/2010 10:58:15 AM Andy
- Add Replacement Item Group popup in front end check code.

12/2/2010 10:33:54 AM Andy
- Fix sku photo cannot show due to wrong image path.

5/4/2011 3:15:11 PM Justin
- Added Batch No and Expired Date.

5/17/2011 5:13:47 PM Andy
- Add checking for sku photo path and change path to show/add the image.

5/23/2011 10:46:20 AM Andy
- Add generate cache for thumb picture.

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache when view in popup.

2/29/2012 2:24:43 PM Justin
- Added to show Price Type when it SKU item is consignment.

9/7/2012 4:37:PM Fithri
- add offer price after the selling price

2/27/2013 3:20 PM Fithri
- show stock balance - deduct unfinalized qty (stock with un-finalize sales)

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

3/27/2015 3:02 PM Andy
- Enhanced to show GST Indicator.

4/26/2017 8:42 AM Khausalya
- Enhanced changes from RM to use config setting. 

1/29/2019 4:48 PM Andy
- Fixed mprice show as 'Array' bug.
*}
{assign var=n value=1}
{foreach from=$items item=item max=50}

<div class="card mx-3">
	<div class="card-body">
		<div class=item>
			<div class=bignumber>{$n++}.</div>
			
			{if $item.photos}
			{foreach from=$item.photos item=p}
			<img width=100 height=100 align=absmiddle vspace=4 hspace=4 src="/thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=0 style="float:left;margin-right:5px;cursor:pointer" onclick="window.open('{$p|replace:$smarty.server.DOCUMENT_ROOT:''}','','width=800,height=600')" title="View">
			{/foreach}
			{/if}
			
			{*
			{if $item.photo_count > 0}
			<!-- display previously uploaded images -->
			{section name=n start=1 loop=`$item.photo_count+1`}
			{capture assign=p}{$item.image_path}sku_photos/{$item.sku_apply_items_id}/{$smarty.section.n.iteration}.jpg{/capture}
			<img width=100 height=100 align=absmiddle vspace=4 hspace=4 src="thumb.php?w=100&h=100&img={$p|urlencode}" border=0 style="float:left;margin-right:5px;cursor:pointer" onclick="window.open('{$p}','','width=800,height=600')" title="View">
			{/section}
			{/if}
			*}
			
			{get_sku_apply_photos sku_apply_items_id=$item.sku_apply_items_id assign=images_list}
			{if $images_list}
				<!-- display previously uploaded images -->
				{foreach from=$images_list item=p name=loop}
					<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=220&h=200&cache=1&img={$p|urlencode}" border=0 style="cursor:pointer" onclick="window.open('{$p}','','width=800,height=600')" title="View" />
				{/foreach}
			
			{/if}
			
			<div class=block>
			<b><font color=#ff0000>{$item.description}</font></b><br>
			ARMS Code: <em>{$item.sku_item_code}</em><br>
			{$config.link_code_name}: <em>{$item.link_code}</em><br>
			Artno/MCode: <em>{$item.artno|default:"-"}/{$item.mcode|default:"-"}</em><br>
			Vendor: <em>{$item.vendor}</em><br>
			Brand: <em>{$item.brand}</em><br>
			SKU Type: <em>{$item.sku_type}</em><br>
			Selling Price: <em>{$config.arms_currency.symbol}{$item.selling_price|number_format:2} {if $item.sku_type eq 'CONSIGN'}({$item.trade_discount_code}){/if}</em><br />
			{if $is_under_gst}
				GST Indicator: <em>{$item.output_gst.indicator_receipt}</em><br />
				
			{/if}
			
			
			{if $item.price}
			{foreach from=$item.price key=branch item=price}
			&nbsp; <em><span class=br>{$branch}</span> - {$config.arms_currency.symbol}{$price.price|number_format:2}{if $item.sku_type eq 'CONSIGN'} ({$price.trade_discount_code}){/if}</em>
			{/foreach}
			<br />
			{/if}
			
			{if $config.sku_mprice_in_check_code}
				{if $item.show_all_branches}
					{foreach from=$item.mprice key=type item=t}
					{$type}:<br />
					{foreach from=$t key=bcode item=p}
					&nbsp; <em><span class=br>{$bcode}</span> - {$config.arms_currency.symbol}{$p|number_format:2}</em>
					{/foreach}
					<br />
					{/foreach}
				{else}
					{foreach from=$item.mprice item=s key=k}
					{$k}: <em>{$config.arms_currency.symbol}{$s|number_format:2}</em>
					<br />
					{/foreach}
				{/if}
			{/if}
			
			{if $config.check_code_show_balance}
			Location:<em>{$item.location}</em><br>
			Stock Balance:<em>{$item.qty}</em><br>
			Unfinalised Stock Balance:<em>{$item.unfinalize_qty}</em><br>
			{/if}
			
			{if $config.enable_replacement_items and $item.ri_id}
			Replacement Item Group: <a href="javascript:void(show_replacement_items('{$item.id}'));">{$item.ri_group_name|default:'-'}</a>
			{/if}
			
			{if $item.batch_items}
				Batch No: <em>{$item.batch_no}</em> &nbsp;&nbsp;&nbsp;&nbsp; Expired Date: <em>{$item.expired_date}</em>
				{foreach from=$item.batch_items key=branch item=batch_item name=bn_list}
					{if $branch ne $BRANCH_CODE}&nbsp; <em><span class=br>{$branch}</span></em>{/if}
				{/foreach}
			{/if}
			
			</div>
			
			<br style="clear:both">
			</div>
	</div>
</div>
{/foreach}
