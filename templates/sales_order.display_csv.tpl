{*
3/30/2018 4:13PM HockLee
- New template for Sales Order by upload csv
*}
<script type="text/javascript">
{literal}
// if art no. duplicated, only allow to check 1 sku
function checkOnlyOne(value, b){
	var sku = document.getElementsByClassName('sku_check_' + b);
	var sku_length = document.getElementsByClassName('sku_check_' + b).length;
	var i;

	if(sku_length == undefined){
		sku_length = 1;
	}

	for (i = 0; i < sku_length; i++){
	 	if(sku[i].value != value){
	  		sku[i].checked = false;
	  	}
	}
}
{/literal}
</script>

<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="2" id="docs_items">
	<thead>
		<tr bgcolor=#ffffff height=40>
			<th>#</th>
			<th>ARMS Code</th>
			<th>Article No</th>
			<th>Mcode</th>
			<th>{$config.link_code_name|default:'Link Code'}</th>
			<th>SKU Description</th>
			<th>UOM</th>
			<th>Ctn</th>
			<th>Remark</th>
		</tr>
	</thead>

	{foreach from=$debtor.sku_item_info_list item=item name=f}		
			<tr 
			{if $item.disabled || $item.duplicate}bgcolor="#f8c471"{else}bgcolor="#ffee99"{/if} onmouseover="this.bgColor='#ffffcc';" {if $item.disabled || $item.duplicate}onmouseout="this.bgColor='#f8c471';"{else}onmouseout="this.bgColor='';"{/if}  id="tr_item,{$item.id}" class="tr_item">
				<td align=center nowrap width="5%">
					<span class="no">{$smarty.foreach.f.iteration}.</span>
					<input type="checkbox" name="select_sku[]" id="{$item.integration_code}" {if $item.duplicate} class="sku_check_{$item.suggest_po_ctn_by_branch}" onclick="checkOnlyOne(this.value, '{$item.suggest_po_ctn_by_branch}'), check_item('{$debtor.integration_code}')" {/if} value="{$item.integration_code},{$item.mcode},{$item.artno},{$item.uom_id},{$item.suggest_po_ctn_by_branch},{$item.sku_item_code}" 
					{if $debtor.debtor_disabled eq 1}disabled{elseif $item.disabled}disabled{/if}
					{if $item.uncheck ne 1}checked{/if} onclick="check_item('{$debtor.integration_code}')" />
				</td>
				<td nowrap width="5%">{$item.sku_item_code|default:'&nbsp;'}</td>
				<td nowrap width="10%">{$item.artno|default:'&nbsp;'}</td>
				<td nowrap width="9%">{$item.mcode|default:'&nbsp;'}</td>
				<td nowrap width="9%">{$item.link_code|default:'&nbsp;'}</td>
				<td nowrap width="35%">{$item.description|default:'&nbsp;'}<br>
					{if $item.vendor}<span style="color:#00f">Vendor: {$item.vendor|default:'&nbsp;'}</span>{/if}</td>
				<td nowrap width="5%">{$item.uom|default:'&nbsp;'}</td>
				<td nowrap width="5%" align="right">{$item.suggest_po_ctn_by_branch|default:'&nbsp;'}</td>
				<td nowrap width="17%">{$item.remark|default:'&nbsp;'}</td>
			</tr>		
	{/foreach}
</table>