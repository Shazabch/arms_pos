<h2>Click an item for price change</h2>
{count var=$items} record(s) found :
<div style="height:310px ;max-height:400px; border:1px solid #ccc; overflow:auto;" id="div_sku">
	<ul style="list-style-type:none;margin:0;padding:0;">
	{foreach from=$items key=dummy item=r}
		<li style="cursor:pointer;display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id="li{$r.id}" onclick="si_list_item_clicked('{$r.sku_item_id}', '{$r.sku_item_code}');">
			{$r.description}
			<font color="#009911">(Article: {$r.artno|default:'-'})</font>
			<!--font class="small">[<a href="javascript:void(window.open('masterfile_sku.php?a=view&id={$r.sku_id}'))">detail</a>]</font-->
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<span class="small">
			<font color="blue">ARMS Code:</font> {$r.sku_item_code}
			<font color="blue">Link Code:</font> {$r.link_code|default:"-"}
			<font color="blue">MCode:</font> {$r.mcode|default:"-"}
			<input type="hidden" id="list_sku_code{$r.id}" value="{$r.sku_item_code}" />
		</span>
	{/foreach}
	</ul>
</div>
<div align="center">
	<br><input type="button" value="Close" onclick="curtain_clicked();">
</div>
