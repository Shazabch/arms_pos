{*
6/8/2011 5:03:37 PM Alex
- disable input if no profit 
7/4/2011 11:13:00 AM Alex
- add identity for each row 
7/6/2011 6:12:15 PM Alex
- default get profit 
8/3/2011 11:20:53 AM Alex
- fix unable to save multiple data
*}

{if $is_new}
	{assign var=auto_id value="__num__"}
{else}
	{assign var=auto_id value=$smarty.foreach.prices_data.iteration}
{/if}

<tbody class="tcontainer" index_no="{$auto_id}">
{foreach name=br_loop from=$detail.branch_arr key=br_id item=br_code}
<tr class="tr_price" >
	{if $smarty.foreach.br_loop.first}
		{if !$read_only && $sessioninfo.privilege.MST_CONTABLE_EDIT}
			<td rowspan={$branch_col} ><img src=/ui/del.png onclick="if (confirm('Are you sure?')) del_row(this);" title="Delete" align=absmiddle border=0> </td>
		{/if}
	{/if}

	<td rowspan="{$branch_col}" {if !$smarty.foreach.br_loop.first} style="display:none;" {/if} >
		<select name="p_type[{$br_id}][{$auto_id}]" calc="p_type" {if $detail.r_type ne 'none'} onchange="p_type_changed(this);" {/if} {if $is_new}disabled {/if}>
		{assign var=req_trade_code value=$pp.$br_id.trade_discount_type_code}
		
		{foreach name=select_p from=$price_type key=p_code item=pt}

		    {if $smarty.foreach.select_p.first}
		        {if $price_type.$p_code.$br_id.rate}
		        	{assign var=first_rate value=$price_type.$p_code.$br_id.rate}
		        {else}
			        {assign var=first_rate value=0}
		        {/if}
			{/if}
		
			<option value="{$p_code}" rate="{$pt.$br_id.rate|default:'0'}" {if $req_trade_code eq $p_code} selected {/if} >
			{$p_code}</option>
		{/foreach}
		
		</select>
	</td>
	<td>
		{$br_code}
	</td>
	{if $detail.r_type ne 'none' && !$price_type.$req_trade_code.$br_id.rate}
		{assign var=disable_input value=1}
	{else}
		{assign var=disable_input value=0}
	{/if}
	<td>
	<select class=use_net name="use_net[{$br_id}][{$auto_id}]" calc="use_net" onchange="change_location(this);recalc_sp();" {if $is_new || $disable_input}disabled {/if} >
		<option value="no" {if $pp.$br_id.use_net eq 'no' || !$pp.$br_id.use_net} selected {/if} >Bearing</option>
		<option value="yes" {if $pp.$br_id.use_net eq 'yes'} selected {/if} >Net Sales</option>
		<option value="amount" {if $pp.$br_id.use_net eq 'amount'} selected {/if} >Amount</option>
	</select>
	</td>
	<td>
		<input name='profit[{$br_id}][{$auto_id}]' calc="profit" maxlength=6 size=5 onchange="this.value=float(this.value);" onkeyup="recalc_sp(); " value="{if $detail.r_type eq 'none'}{$pp.$br_id.profit|default:'0'}{elseif !$req_trade_code}{$first_rate|default:'0'}{else}{$price_type.$req_trade_code.$br_id.rate|default:'0'}{/if}" readonly {if $is_new || $disable_input }disabled {/if} >
	</td>
	<td>
		<input name="discount[{$br_id}][{$auto_id}]" calc="discount" maxlength=6 size=5 onchange="this.value=float(this.value);" onkeyup="recalc_sp();" value="{$pp.$br_id.discount|ifzero:0}" {if $is_new || $disable_input || $pp.$br_id.use_net eq 'amount'}disabled {/if}>
	</td>
	<td>
	    <span type=bearing>
	    {if $pp.$br_id.use_net eq 'no' || !$pp.$br_id.use_net}
		<input name="net_bearing[{$br_id}][{$auto_id}]" calc="net_bearing" maxlength=6 size=5 onchange="this.value=float(this.value);" onkeyup="recalc_sp() ;" value="{$pp.$br_id.net_bearing|default:"0"}" {if $is_new || $disable_input}disabled {/if}>
		{/if}
		</span>
	</td>
	<td>
	    <span type=nett_sales>
	    {if $pp.$br_id.use_net eq 'yes' || $pp.$br_id.use_net eq 'amount'}
		<input name="net_bearing[{$br_id}][{$auto_id}]" calc="net_bearing" maxlength=6 size=5 onchange="this.value=float(this.value);" onkeyup="recalc_sp() ;" value="{$pp.$br_id.net_bearing|default:"0"}" {if $is_new || $disable_input}disabled {/if}>
		{/if}
		</span>

	</td>
	<td class="class_test"><input name='cal[{$br_id}]' calc="cal"  size=8 readonly {if $is_new || $disable_input}disabled {/if}></td>
</tr>
{/foreach}
</tbody>
