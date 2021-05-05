<tr class="tr_price" >
	{if !$read_only && $sessioninfo.privilege.MST_CONTABLE_EDIT}
		<td><img src=/ui/del.png onclick="if (confirm('Are you sure?')) del_row(this);" title="Delete" align=absmiddle border=0> </td>
	{/if}
	<td>
		<select id="p_type_id" name="p_type[]"  onchange="p_type_changed(this);" {if $is_new}disabled {/if}>
		{foreach from=$price_type item=pt}
			<option value="{$pt.code}" rate="{$pt.rate}" {if $pp.trade_discount_type_code eq $pt.code }selected {/if} >{$pt.code}</option>
		{/foreach}
		</select>
	</td>
	<td><input name='profit[]' maxlength=6 size=5 onkeyup="recalc_sp(); " value="{$pp.profit|default:$price_type.0.rate}" readonly {if $is_new}disabled {/if} ></td>
	<td><input name="discount[]" maxlength=6 size=5 onkeyup="recalc_sp();" value="{$pp.discount|ifzero:0}" {if $is_new}disabled {/if}></td>
	<td>
	<select class=use_net name="use_net[]" onchange="change_location(this);recalc_sp();" {if $is_new}disabled {/if} >
		<option value="no" {if $pp.use_net eq 'no' || !$pp.use_net} selected {/if} >Bearing</option>
		<option value="yes" {if $pp.use_net eq 'yes'} selected {/if} >Net Sales</option>
	</select>
	</td>
	<td>
	    <span type=bearing>
	    {if $pp.use_net eq 'no' || !$pp.use_net}
		<input name="net_bearing[]" maxlength=6 size=5 onkeyup="recalc_sp() ;" value="{$pp.net_bearing|default:"0"}" {if $is_new}disabled {/if}>
		{/if}
		</span>
	</td>
	<td>
	    <span type=nett_sales>
	    {if $pp.use_net eq 'yes'}
		<input name="net_bearing[]" maxlength=6 size=5 onkeyup="recalc_sp() ;" value="{$pp.net_bearing|default:"0"}" {if $is_new}disabled {/if}>
		{/if}
		</span>

	</td>
	<td class="class_test"><input name='cal[]' size=8 readonly {if $is_new}disabled {/if}></td>
</tr>
