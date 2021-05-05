{*
4/26/2017 10:43 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}

<html>
{literal}
<style>
body {font:11px Arial; margin:0; padding:0; background:#000; color:#0f0; text-align:center; }
h1 { font: bold 14px Arial; margin:0.5em 0; }
h2 { font: bold 20px Arial; margin:0.5em 0; }
</style>
{/literal}
<h1>ARMS&copy; Price Checker</n1>

<h2 style="color:#fff">{$sku.description}</h2>
<h2 style="color:#fff">
{if $sku.member_price>0 && $sku.member_price != $sku.price}
	{if $sku.member_price == $sku.non_member_price}
	Selling Price: {$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({$sku.member_discount}%)
	{else}
	Selling Price: {$config.arms_currency.symbol}{$sku.non_member_price|number_format:2} ({$sku.non_member_discount}%)
	<br>Member Price: {$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({$sku.member_discount}%)
	{/if}
{else}
	Selling Price: {$config.arms_currency.symbol}{$sku.price|number_format:2}
	{if $sku.disc}
	    ({$sku.disc}%)
	{/if}
{/if}
</h2>
ARMS Code: {$sku.sku_item_code}<br>
Barcode: {$sku.mcode}<br>
{if $sku.artno}Article No: {$sku.artno}{/if}<br>
{if trim($sku.link_code) ne ''} Multics Code: {$sku.link_code}{/if}<br>

<meta http-equiv="refresh" content="30;URL=idle.php">


<br>Please scan your barcode.<br>
</html>
