{assign var=id value=$form.table|default:'0'}
<table class="input_matrix" cellspacing="0" cellpadding="2" border="0">
	<tr>
		<td colspan="2"></td>
		{foreach from=$alp_list key=arr item=c}
			<td align="center"><font color="#999999">{$c}</font></td>
		{/foreach}
		<td rowspan="2" align="center"><font >Selling<br />Price</font></td>
		<td rowspan="2" align="center" class="gst_settings"><font >GST (<span id="span_gst_rate_{$id}">0</span>%)</font></td>
		<td rowspan="2" align="center" class="gst_settings"><font >Selling Price<br /><span id="span_gst_indicator_{$id}">Before</span> GST</font></td>
		{if $config.do_enable_hq_selling && $BRANCH_CODE eq "HQ"}
			<td rowspan="2" align=center><font >HQ Selling<br />Price</font></td>
		{/if}
		<td rowspan="2" align=center><font >Cost<br />Price<br />({$config.arms_currency.symbol} or %)</font></td>
		{if $config.sku_listing_show_hq_cost && $BRANCH_CODE eq "HQ"}
			<td rowspan="2" align="center"><font >HQ<br />Cost</font></td>
		{/if}
	</tr>
	<tr>
		<td></td>
		<td><input name="tb[{$id}][0][0]" type="hidden"><input name="tbm[{$id}][0][0]" type="hidden">Product<br />Varieties</td>
		{foreach from=$size_list key=s item=size}
			{assign var=s value=$s+1}
			<td>
				<input class="form-control form-control-sm" onchange="uc(this);" onkeydown="autocomplete_color_size('size',{$id},{$s},0,this);" alt="header" autocomplete="off" title="Sizes" name="tb[{$id}][{$s}][0]" id="autocomplete_size_{$id}_{$s}_0" value="{$size}" maxlength="30">
				<div id="div_autocomplete_size_choices_{$id}_{$s}_0" class="autocomplete" style="display:none;"></div>
				<br /><img src="ui/pixel.gif" height="2" width="10"><br />
			</td>
		{/foreach}
	</tr>
	{foreach from=$clr_list name=color key=c item=clr}
		{assign var=c value=$c+1}
		<tr>
			<td><font >{$smarty.foreach.color.iteration}</font></td>
			<td>
				<input class="form-control form-control-sm" onchange="uc(this);" onkeydown="autocomplete_color_size('color',{$id},0,{$c},this);" alt="header" autocomplete="off" title="Sizes" name="tb[{$id}][0][{$c}]" id="autocomplete_color_{$id}_0_{$c}" value="{$clr}" maxlength="30">
				<div id="div_autocomplete_color_choices_{$id}_0_{$c}" class="autocomplete" style="display:none;"></div>
				<img src="ui/pixel.gif" height="2" width="10">
			</td>
			{foreach from=$size_list key=s item=size}
				{assign var=s value=$s+1}
				<td>
					<input onchange="uc(this);" onchange="check_artmcode(this,'artno');" title='Art No' class="ntd" name="tb[{$id}][{$s}][{$c}]" id="autocomplete_size_{$id}_{$s}_{$c}" value="{$form.tb.$id.$s.$c|escape:'html'}" maxlength="30">
					<div class="form-control form-control-sm" id="div_autocomplete_size_choices_{$id}_{$s}_{$c}" class="autocomplete" style="display:none;"></div>
					<br /><img src="ui/pixel.gif" height="2" width="10"><br />
					<input onchange="check_artmcode(this,'mcode');" title='Mcode' class="ntd" name="tbm[{$id}][{$s}][{$c}]" value="{$form.tbm.$id.$s.$c|escape:'html'}" maxlength='15'>
				</td>
			{/foreach}
			<td><input class="ntp form-control form-control-sm" onblur="this.value=round2(this.value); calc_matrix_gst({$id},{$c});" name="tbprice[{$id}][{$c}]" value=""></td>
			<td  class="gst_settings"><input class="ntp form-control form-control-sm" type="text" name="tbgst[{$id}][{$c}]" value="" readonly /></td>
			<td class="gst_settings"><input class="ntp form-control form-control-sm" type="text" name="tbprice_gst[{$id}][{$c}]" onblur="this.value=round2(this.value); calc_matrix_gst('{$id}','{$c}','gst_price');" value="0.00"/></td>
			{if $config.do_enable_hq_selling && $BRANCH_CODE eq "HQ"}
				<td><input class="ntp" onblur="this.value=round(this.value, 2);" name="tbhqprice[{$id}][{$c}]" value="{$form.tbhqprice.$id.$c|number_format:2:'.':''}" /></td>
			{/if}
			<td><input class="ntp form-control form-control-sm" onblur="matrix_cost_changed(this, '{$id}', '{$c}');" name="tbcost[{$id}][{$c}]" value="{$form.tbcost.$id.$c|number_format:$config.global_cost_decimal_points}" {if $form.trade_discount_type}readonly{/if} ></td>
			{if $config.sku_listing_show_hq_cost && $BRANCH_CODE eq "HQ"}
				<td><input class="ntp form-control form-control-sm" onblur="this.value=round(this.value, {$config.global_cost_decimal_points});" name="tbhqcost[{$id}][{$c}]" value="{$form.tbhqcost.$id.$c|number_format:$config.global_cost_decimal_points}" /></td>
			{/if}
		</tr>
	{/foreach}
</table>