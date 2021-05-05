{*
1/25/2011 9:58:27 AM Andy
- Change multiple qty price can enter 3 decimal digits.

9/22/2011 11:12:36 AM Andy
- Add checking to not allow negative selling price

1/06/2011 3:27:00 AM Kee Kee
- Change multiple qty price can enter 2 decimal digits.

4/3/2012 6:44:12 PM Alex
- change branch order same as mprice branch order

5/7/2012 3:23:03 PM Andy
- Fix qprice wrong branch column bugs.

7/24/2012 5:31:34 PM Justin
- Enhanced the function that capture item and branch ID for automate price copy.

6/6/2013 2:08 PM Justin
- Enhanced to allow user maintain multiple quantity by multiple price.

12/3/2019 9:38 AM William
- Fixed bug Change Selling Price multiple quantity price unable to delete.

06/26/2020 Sheila 11:46 AM
- Updated button css.
*}

<form name="f_q" method="post" onsubmit="return false;">
<input type="hidden" name="a" value="change_qprice">
<input type="hidden" name="sku_item_id" value="{$items[0].id}">
<input name="form_branch_id" value="{$sessioninfo.branch_id}" type="hidden">
<table border="0" cellspacing="1" cellpadding="2" width="100%" id="qprice_tbl">
<tr height=24 bgcolor=#ffee99><th>ARMS Code</th><th>Description</th><th>&nbsp;</th></tr>
{section loop=$items name=i}
<input type=hidden name=code[{$items[i].id}] value="{$items[i].sku_item_code}">
<tr><td valign=top>{$items[i].sku_item_code}<br>{$items[i].mcode} {$items[i].artno}</td><td valign=top>{$items[i].description}</td>
<td valign=top>
	<table border=0 cellspacing=1 cellpadding=2>
	<tr height=24 bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Min Qty<br>(>=)</th>
	{if $BRANCH_CODE eq 'HQ'}
		<!-- HQ -->
		<th title="HQ - {$branch.1.description}">HQ <img src="/ui/icons/zoom.png" onclick="get_price_history(this,{$items[i].id},1,'{$branch.1.code}','qprice')" title="View History"></th>
		
		<!-- Normal Change Price Method: by branch -->
		{foreach from=$branch item=b}
			{if $b.code ne 'HQ'}
				<th title="{$b.code} - {$b.description}">{$b.code} <img src="/ui/icons/zoom.png" onclick="get_price_history(this,{$items[i].id},{$b.id},'{$b.code}','qprice')" title="View History"></th>
			{/if}
		{/foreach}
	{else}
		<th>Selling</th>
	{/if}
	</tr>
	<tbody id="qprice_row" class="row_template" background="">
		<tr>
			<td><img src="/ui/icons/add.png" onclick="qprice_addrow(this)" title="Insert row below"><img class="deleteicon" src="/ui/icons/delete.png" onclick="qprice_delrow(this)"></td>
			<td><input name="min_qty[{$items[i].id}][]" class="minqty" size="3" onchange="minqty_check(this)"></td>
			{if $BRANCH_CODE eq 'HQ'}
				<!-- HQ -->
				<td>
					<input name="price[{$items[i].id}][1][]" size="3" onchange="mf(this,2);copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '1');" class="inp_qprice" />
				</td>
					
				{foreach from=$branch item=b}
					{if $b.code ne 'HQ'}
						<td>
							<input name="price[{$items[i].id}][{$b.id}][]" size="3" onchange="mf(this,2);{if $b.code eq 'HQ'}copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '{$b.id}');{/if}" class="inp_qprice" />
						</td>
					{/if}
				{/foreach}
			{else}
				<td><input class="inp_qprice" name="price[{$items[i].id}][]" size=3></td>
			{/if}
		</tr>
		{if $config.sku_multiple_quantity_mprice}
			{include file="masterfile_sku_items_price.mqprice.tpl"}
		{/if}
	</tbody>

	{foreach from=$items[i].min_qty key=min_qty item=qprice}
		<tbody id="qprice_row" class="row_template" background="">
			<tr>
				<td><img src="/ui/icons/add.png" onclick="qprice_addrow(this)" title="Insert row below"><img src="/ui/icons/delete.png" onclick="qprice_delrow(this)"></td>
				<td><input size=3 name="min_qty[{$items[i].id}][]" class="minqty" value="{$min_qty}" onchange="minqty_check(this)"></td>
				{if $BRANCH_CODE eq 'HQ'}
					{assign var=qprice value=$items[i].qprice[$min_qty]}
					
					{assign var=bid value=1}
					<td>
						<input size=3 name="price[{$items[i].id}][{$bid}][]" value="{$qprice.$bid.price}" onchange="mf(this,2);copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '1');" class="inp_qprice" />
					</td>
							 
					{foreach from=$branch item=b}
						{if $b.code ne 'HQ'}
							{assign var=bid value=$b.id}
							<td>
								<input size=3 name="price[{$items[i].id}][{$bid}][]" value="{$qprice.$bid.price}" onchange="mf(this,2);{if $b.code eq 'HQ'}copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '{$b.id}');{/if}" class="inp_qprice" />
							</td>
						{/if}
					{/foreach}
				{else}
					<td><input size=3 name="price[{$items[i].id}][]" value="{$qprice}" class="inp_qprice" /></td>
				{/if}
			</tr>
			{if $config.sku_multiple_quantity_mprice}
				{include file="masterfile_sku_items_price.mqprice.tpl"}
			{/if}
		</tbody>
	{/foreach}
	
	</table>
</td>
</tr>
{/section}
</table>
<br><input class="btn btn-primary" type=button name=upd value="Update Price" onclick="if (save_check()) form.submit()">
</form>

{literal}
<style>
input.error { border:1px solid red; background:#ff9; }
.row_template .deleteicon { display:none; }
#qprice_tbl tbody:nth-child(even){
	background-color:#eeeeee;
}
</style>
<script>
var that;
var err;
function save_check()
{
	err = false;
	$$('input.minqty').each(function(el){
		el.removeClassName('error');
		that = el;
		$$('input.minqty').each(function(el1){
			mf(el1); mf(that);			
			if(el1!=that && el1.value == that.value && el1.name==that.name)
			{
				el1.addClassName('error');
				that.addClassName('error');
				err = el1.value;
				return false;
			}
		});
	});
	
	if (err) {
		alert('Minimum quantity duplicate for '+err);	
		return false;
	}
	
	if(!check_price('qprice'))	return false;
	return true;
}
function minqty_check(el)
{
	mf(el);
	if (el.value<=0) 
	{
		alert('Min Qty must be > 0');
		el.value='';
		el.focus();
	}
	return false;	
}

function qprice_addrow(el)
{
	var tr = el.parentNode.parentNode.parentNode;
	var c = tr.cloneNode(true);
	c.className = "";
	c.id = '';
	tr.parentNode.insertAfter(c, tr);
	
	var obj = c.getElementsByTagName('input');
	for(i=0;i<obj.length;i++) { obj[i].value=''; }
	return c;
}

function qprice_delrow(el)
{
	if (!confirm('Are you sure?')) return;
	Element.remove(el.parentNode.parentNode.parentNode);
}
</script>
{/literal}
