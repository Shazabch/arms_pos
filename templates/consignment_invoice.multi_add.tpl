{*
7/14/2010 2:54:36 PM Andy
- Add settings for consignment invoice.
- Able to control whether use item discount or not.
- Able to control whether split invoice by price type or not when confirm.

6/17/2011 5:01:11 PM Justin
- Added currency code to be used in php.

6/10/2015 5:13 PM Andy
- Fix multiple add layout.
*}

<script>

var currency_code = "{$currency_code}";

{literal}
toggle_multi_add = function(ele){
	var status = ele.checked;
	
	$$('#tbl_multi_add input.mul').each(function(chx){
		chx.checked = status;
	});
}

{/literal}
</script>

<div style="float:right"><img src="ui/closewin.png" onClick="default_curtain_clicked();" /></div>
<h2>Multi Add</h2>
<form name="f_mul" id="f_mul">
<input type=hidden name="id" value="{$smarty.request.ci_id}">
<input type=hidden name=ci_branch_id value="{$smarty.request.ci_branch_id}" />
<input type="hidden" name="type" value="{$smarty.request.type}" />
<input type="hidden" name="show_per" value="{$smarty.request.show_per}" />

<table width="100%" id="tbl_multi_add">
<tr style="background:#fe9;">
	<th><input type="checkbox" id="inp_chx_all" onClick="toggle_multi_add(this);"></th>
	<th>Description</th>
	<th>Art. No</th>
	<th>Mcode</th>
	<th>Stock<br>Balance</th>
	<th>Selling Price</th>
	<th>Discount Code</th>
</tr>

<tbody style="overflow-x:hidden;overflow-y:auto;height:370px;">

{foreach from=$items key=sid item=r}
	<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
		<td><input type="checkbox" name="sid[]" value="{$sid}" class="mul" /></td>
		<td>{$r.description}</td>
		<td class="small">{$r.artno|default:'-'}</td>
		<td class="small">{$r.mcode|default:'-'}</td>
		<td class="small" align="right">{$r.qty|default:'-'}</td>
		<td class="small" align="right">{$r.price|default:'-'}</td>
		<td class="small" align="center">{$r.discount_code|default:'-'}</td>
	</tr>
{/foreach}
</tbody>
</table>
</form>

{*<p align="center"><input type="button" value="Add" onClick="submit_multi(this);" /></p>*}
