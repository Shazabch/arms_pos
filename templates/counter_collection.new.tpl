{include file=header.tpl}

{literal}
<script>
function add_new_row(){

	var c = $('row_template').cloneNode(true);
	var row_count = $$('#cc_list tr').length;
	c.style.display = '';
	c.id = '';
	$('cc_list').appendChild(c);

}

function calc_total()
{
	var tb = $('cc_list');
	var input = tb.getElementsByTagName('input');
	var total_cols = new Array('cash','credit_card','cash_voucher','coupon');
	var cash = 0;
	var credit_card = 0;
	var cash_voucher = 0;
	var coupon = 0;
	for(i=0;i<input.length;i++)
	{	
		if (!/cashier_id/.test(input[i].name))
		{
			ttl = input[i].name.match(/(\w+\[)(\w+)/);
			mfz(input[i]);
			eval(ttl[2]+"+="+float(input[i].value));
		}
		
	}
	

	total_cols.each(function(e,idx){
		document.f.elements['total['+e+']'].value = eval(e).toFixed(2);
	});	
}

function get_user(obj)
{
	//var update = obj.id.replace(/id/,'name');

	new Ajax.Request(
		'counter_collection.php',
		{
			method:'post',
			evalScripts:true,
			parameters: 'a=get_user&id='+obj.value,
			onComplete:function(m)
			{
				var nobj = obj.nextSibling;
				while(nobj.className != 'c_name') { nobj = nobj.nextSibling; }
				if (/invalid/i.test(m.responseText))
				{
					alert(m.responseText);
					obj.focus();
					obj.value = '';
					nobj.innerHTML = '';
				}
				else
				{
					nobj.innerHTML = m.responseText;
				}
			
			}
		});

}
</script>
{/literal}
{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
<p align=center><font color=red>{$msg}</font></p>

<h1>{$PAGE_TITLE} ({if $form.id >0}ID#{$form.id}{else}New{/if})</h1>
<form name=f method=post>
<input name=a value="save" type=hidden>
<input name=id value="{$form.id}" type=hidden>
<table>
<tr>
<th>Counter ID</th>
<td>
<select name=counter_id {if $form.id>0}disabled{/if}>
	{foreach from=$counters item=counter}
	<option value='{$counter.id}' {if $form.counter_id == $counter.id}selected{/if}>{$counter.network_name}</option>
	{/foreach}
</select>
</td>
</tr>
<tr>
<th>Date</th>
<td><input name=date value='{$form.date|default:$smarty.request.date}'{if $form.id>0}readonly{/if}> (dd/mm/yyyy)</td> 
</tr>
</table>

<table id=counter_collection_items class=sortable cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
<th>Cashier ID</th>
<th>Cash Amt.</th>
<th>Credit Card</th>
<th>Cash Voucher</th>
<th>Coupon</th>
</tr>
<tr style="display:none;" id=row_template>
<td><input size=3 name=data[cashier_id][] value="" onchange="get_user(this);"> <span class=c_name>{$item.cashier_name}</span></td>
<td><input size=10 class=r name=data[cash][] value="" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[credit_card][] value="" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[cash_voucher][] value="" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[coupon][] value="" onchange="calc_total();"></td>
</tr>
<tbody id=cc_list>
{foreach name=i from=$form.data item=item}
{if $item}
<tr bgcolor={cycle values=",#eeeeee"}>
<td><input size=3 name=data[cashier_id][] value="{$item.cashier_id}" onchange="get_user(this);"> <span class=c_name>{$item.cashier_name}</span></td>
<td><input size=10 class=r name=data[cash][] value="{$item.cash}" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[credit_card][] value="{$item.credit_card}" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[cash_voucher][] value="{$item.cash_voucher}" onchange="calc_total();"></td>
<td><input size=10 class=r name=data[coupon][] value="{$item.coupon}" onchange="calc_total();"></td>
</tr>
{/if}
{/foreach}
</tbody>
<tfoot>
<tr bgcolor=#eeeeee>
<th>Total</th>
<td><input size=10 class=r name=total[cash] readonly></td>
<td><input size=10 class=r name=total[credit_card] readonly></td>
<td><input size=10 class=r name=total[cash_voucher] readonly></td>
<td><input size=10 class=r name=total[coupon] readonly></td></tr>
</tfoot>
</table>
<input type=button onclick="add_new_row()" value="Add">
<p align=center>
<input name=submitted type=submit value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/counter_collection.php'">
</p>
</form>
<script>calc_total();</script>
{include file=footer.tpl}
