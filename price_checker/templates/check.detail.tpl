{*
12/27/2007 3:28:35 PM yinsee
- show local sku photo if found, otherwise show application photo

12/29/2008 11:35:15 AM yinsee
- remove photos (SLLEE)

6/25/2010 3:59:05 PM Andy
- Add show promotion period.

3/22/2011 5:21:25 PM Justin
- Added show first sku item photo when scan barcode.

5/16/2011 5:50:48 PM Andy
- Change display SKU photo script.

5/23/2011 10:46:20 AM Andy
- Add generate cache for thumb picture.

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache.

2/21/2012 3:45:22 PM Andy
- Show member type price in web-base price checker.

5/16/2013 5:11 PM Justin
- Enhanced to have new function that can check and print member info.

7/1/2013 1:26 PM Andy
- Re-commit to fix crash copy in sync server.

3/16/2015 12:24 PM Andy
- Enhanced to show price before gst and gst amt.

3/26/2015 3:30 PM Andy
- Enhance to show GST Indicator.

4/26/2017 10:26 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}
                   
{literal}
<style>
body {font:20px Arial; margin:0; padding:0; background:#000; color:#0f0; text-align:center; }
.small{ font-size: 18px; }

.gst_info{
	font-size: 14px;
	color: #ccc;
}
</style>
{/literal}
<br><br>
<table align="center">
	{if $sku && !$sku.error}
		<tr>
			<td colspan="2" align="center"><h1>ARMS&copy; Price Checker</h1></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><h2 style="color:#fff">{$sku.description}</h2></td>
		</tr>
		<tr>
			<td {if !$sku.photo_count && !$sku.photos}align="center" colspan="2"{/if}>
				<h2 style="color:#fff">
				<!-- got member price -->
				{if $sku.member_price>0 && $sku.member_price != $sku.price}
					{if $sku.member_price == $sku.non_member_price}<!-- member price same as non member price -->
						Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
						Today Price: {$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({$sku.member_discount}%)<br>
						{if $sku.is_under_gst}
							<span class="gst_info">Before GST : {$sku.member_price_before_gst|number_format:2}, GST : {$sku.member_gst_amt|number_format:2}</span><br />
						{/if}
						
						{if $sku.member_date_to}
							<span class="small">Valid till {$sku.member_date_to}</span><br>
						{/if}
					{else}
						{if $sku.non_member_price eq $sku.default_price}
							Selling Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: {$config.arms_currency.symbol}{$sku.default_price|number_format:2}<br>
							{if $sku.is_under_gst}
								<span class="gst_info">Before GST : {$sku.default_price_before_gst|number_format:2}, GST : {$sku.default_gst_amt|number_format:2}</span><br />
							{/if}
						{else}
							Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
							Today Price: {$config.arms_currency.symbol}{$sku.non_member_price|number_format:2} ({$sku.non_member_discount}%)<br>
							{if $sku.is_under_gst}
								<span class="gst_info">Before GST : {$sku.non_member_price_before_gst|number_format:2}, GST : {$sku.non_member_gst_amt|number_format:2}</span><br />
							{/if}
							{if $sku.non_member_date_to}
								<span class="small">Valid till {$sku.non_member_date_to}</span><br>
							{/if}
						{/if}
						Member Price: {$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({if $sku.member_limit}Limit {$sku.member_limit}, {/if}{$sku.member_discount}%)<br>
						{if $sku.is_under_gst}
							<span class="gst_info">Before GST : {$sku.member_price_before_gst|number_format:2}, GST : {$sku.member_gst_amt|number_format:2}</span><br />
						{/if}
						{if $sku.member_date_to}
							<span class="small">Valid till {$sku.member_date_to}</span><br>
						{/if}
					{/if}
					
				{else}
					{if $sku.disc}
						Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
						Today Price: {$config.arms_currency.symbol}{$sku.price|number_format:2} ({$sku.disc}%)<br>
						{if $sku.is_under_gst}
							<span class="gst_info">Before GST : {$sku.price_before_gst|number_format:2}, GST : {$sku.gst_amt|number_format:2}</span><br />
						{/if}
					{else}
						Selling Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: {$config.arms_currency.symbol}{$sku.default_price|number_format:2}<br>
						{if $sku.is_under_gst}
							<span class="gst_info">Before GST : {$sku.default_price_before_gst|number_format:2}, GST : {$sku.default_gst_amt|number_format:2}</span><br />
						{/if}
					{/if}
				{/if}
				
				{if $sku.member_type_cat_discount}
					<br>
					{foreach from=$sku.member_type_cat_discount key=mtype item=r}
						{$mtype}: {$config.arms_currency.symbol}{$r.price|number_format:2} {if $r.discount}({$r.discount}%){/if}<br>
						{if $r.date_to}
							<span class="small">Valid till {$r.date_to}</span><br>
						{/if}
					{/foreach}
				{/if}
				</h2>
			</td>
			{if $sku.photo_count > 0 || $sku.photos}
				<td rowspan="2" valign="top">
					<div class=photo_icon>
						{*
						{if $sku.photo_count > 0}
							{capture assign=p}{$sku.image_path}sku_photos/{$sku.sku_apply_items_id}/{$sku.photo_count}.jpg{/capture}
							<img width=150 height=150 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="/thumb.php?w=150&h=150&img={$p|urlencode}" border=1 style="cursor:pointer">
						{else}
							{foreach from=$sku.photos item=p name=i}
								<img width=150 height=150 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=150&h=150&img={$p|urlencode}" border=1 style="cursor:pointer">
							{/foreach}
						{/if}
						*}
						
						{foreach from=$sku.photos item=p name=i}
							<img width=150 height=150 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=150&h=150&img={$p|urlencode}" border=1 style="cursor:pointer">
						{/foreach}
					</div>
				</td>
			{/if}
		</tr>
		<tr>
			<td {if !$sku.photo_count && !$sku.photos}align="center" colspan="2"{/if}>
				ARMS Code: {$sku.sku_item_code}<br>
				Barcode: {$sku.mcode}<br>
				{if $sku.artno}Article No: {$sku.artno}{/if}<br>
				{if trim($sku.link_code) ne ''} Multics Code: {$sku.link_code}{/if}<br>
				{*
				{if $sku.photos}
				{foreach from=$sku.photos item=p name=i}
				<img width=80 height=80 vspace=4 hspace=4 src="/thumb.php?w=110&h=100&img={$p|urlencode}">
				{/foreach}
				{else}
				{if $sku.photo_count > 0}
				<!-- display previously uploaded images -->
				{section name=loop loop=$sku.photo_count}
				{assign var=p value="sku_photos/`$sku.sku_apply_items_id`/`$smarty.section.loop.iteration`.jpg"}
				<img width=80 height=80 vspace=4 hspace=4 src="{$sku.imgpath}thumb.php?w=100&h=100&img={$p|escape:'url'}">
				{/section}
				{/if}
				{/if}
				*}
			</td>
		</tr>
	{elseif $member}
		<tr>
			<td colspan="2" align="center"><h1>Membership Info</h1></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><h2 style="color:#fff">{$member.name}</h2></td>
		</tr>
		<tr style="color:#fff">
			<td>
				<h2>NRIC:</h2>
			</td>
			<td>
				<h2>{$member.nric}</h2>
			</td>
		</tr>
		<tr style="color:#fff">
			<td>
				<h2>Points:</h2>
			</td>
			<td>
				<h2>{$member.points}</h2>
			</td>
		</tr>
		<tr style="color:#fff">
			<td>
				<h2>Points Update:</h2>
			</td>
			<td>
				<h2>{$member.points_update|default:"-"}</h2>
			</td>
		</tr>
		<tr style="color:#fff">
			<td>
				<h2>Issue Date:</h2>
			</td>
			<td>
				<h2>{$member.issue_date|date_format:"%Y-%m-%d"}</h2>
			</td>
		</tr>
		<tr style="color:#fff">
			<td>
				<h2>Expiry Date:</h2>
			</td>
			<td>
				<h2>{$member.next_expiry_date|date_format:"%Y-%m-%d"}</h2>
			</td>
		</tr>
	{/if}
</table>

<meta http-equiv="refresh" content="30;URL=idle.php">

<br>Please scan your barcode.<br>
