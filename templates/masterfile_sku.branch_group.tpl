{*
4/25/2016 3:24 PM Andy
- Add Mcode & art no.

11/22/2017 9:29 AM Justin
- Optimised to take out on screen call for smarty function.
*}
<div class="small" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(default_curtain_clicked())" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle"></a><br><u>C</u>lose (Alt+C)</div>
<h2>
	Branch: {$branches_group.header.0.code} &nbsp;&nbsp;&nbsp; ARMS Code: {$sku_info.sku_item_code} 
	<br />
	MCode: {$sku_info.mcode|default:'-'} &nbsp;&nbsp;&nbsp; Art No: {$sku_info.artno|default:'-'}
</h2>

<table width="100%" style="padding:1px;">
	<tr bgcolor="#ffee99">
	    <th>Branch</th>
	    <th>Stock Balance</th>
	    <th>Selling Price</th>
	</tr>
	{foreach from=$branches_group.items[$smarty.request.group_id] item=r}
		{assign var=bid value=$r.branch_id}
	    <tr class="thover">
	        <td>{$r.code}</td>
	        <td align="right">{$branch_sku_info.$bid.stock_balance|ifempty:"-"}{if $branch_sku_info.$bid.changed}<font color="red">*</font>{/if}</td>
	        <td align="right">{$branch_sku_info.$bid.selling_price|number_format:2}<br>{$branch_sku_info.$bid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
	    </tr>
	{/foreach}
</table>
