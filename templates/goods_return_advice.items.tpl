{*
Revision History
================
19 Apr 2007  - yinsee
- remove owner id checking for delete operation
- add return_type column

11/6/2007 11:27:39 AM gary
- add artno, mcode, selling price and price_type in the sku for return list.

7/6/2010 4:41:44 PM Justin
- Modified the Selling Price column to take directly from GRA items.

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

8/17/2011 11:40:21 AM Justin
- Added pagination for GRA items.

8/18/2011 11:11:42 AM Justin
- Fixed the delete function not working.

4/23/2012 3:00:35 PM Alex
- add packing uom code

7/13/2018 3:54 PM Andy
- Enhanced to show Department.

10/23/2018 3:32 PM Justin
- Enhanced to construct the tabs for SKU type using SKU type list instead of hardcoded it.

05/02/2019 11:36 PM Liew
- Enhanced to display Old Code
*}

{literal}
<script>
function do_sort_type(n, s){
	var pg = '';
	if (s!=undefined) pg = 's='+s;
	if (n==0) pg +='&search='+ $('search').value ;
	//new Ajax.Request( 'goods_return_advice.php?a=ajax_sort_list&sku_type='+n+'&'+Form.serialize(document.f_a) );
	new Ajax.Updater('gra_items', 'goods_return_advice.php',{
		parameters: 'curr_sku_type='+escape(n)+'&'+pg+'&'+Form.serialize(document.f_a)+'&a=ajax_sort_list',
		evalScripts: true
	});
	
}
</script>
{/literal}

<br>
<div class=tab style="white-space:nowrap;">
<a href="javascript:void(do_sort_type('*', 0));" class="btn btn-outline-primary btn-rounded" id="items_lst1" {if $curr_sku_type eq '*' or $curr_sku_type eq ''}class=active{/if}>ALL</a>
{assign var=tab_count value=2}
{foreach from=$sku_type_list key=st_code item=st}
	<a href="javascript:void(do_sort_type('{$st_code}', 0))" class="btn btn-outline-primary btn-rounded" id="items_lst{$tab_count}" {if $curr_sku_type eq $st_code}class=active{/if}>{$st.description|strtoupper}</a>
	{capture}{$tab_count++}{/capture}
{/foreach}
</div>
<div >
{$items_pagination}
<div style="height:400px; overflow:auto;">
<div class="table-responsive mt-3">
	<table border=0 cellspacing=1 cellpadding=2 width=100%>
		<thead class="bg-gray-100">
			<tr>
				<th>&nbsp;</th>
				<th>Artno<br>Mcode</th>
				<th>Old Code</th>
				<th>SKU</th>
				<th>Department</th>
				<th>Vendor</th>
				<th>Return Type</th>
				<th>Qty (pcs)</th>
				<th>Price Type</th>
				<th nowrap>Last Cost<br>Selling Price</th>
				<th>Added</th>
				<th>Reason</th>
			</tr>
		</thead>
		<tbody class="fs-08">
		{section name=i loop=$gra_items}
		<tr id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}">
			<td width=20>
			{if $smarty.request.add_function}<a href="javascript:void({$smarty.request.add_function}({$gra_items[i].id}))"><img src="ui/table_add.png" align=absmiddle border=0 title="Add item"></a>
			{else}
				<a href="javascript:void(del_item({$gra_items[i].id}))"><img src="ui/remove16.png" align=absmiddle border=0 title="Remove item"></a>
			{/if}
			<td>{$gra_items[i].artno}<br>{$gra_items[i].mcode}</td>
			<td>
				{$gra_items[i].link_code|default:'-'}
			</td>
			<td>{$gra_items[i].sku} {include file=details.uom.tpl uom=$gra_items[i].packing_uom_code}</td>
			<td>{$gra_items[i].dept_name}</td>
			<td>{$gra_items[i].vendor}</td>
			<td align=center>{$gra_items[i].return_type}</td>
			<td align=center>{$gra_items[i].qty}</td>
			<td align=center>
			{$gra_items[i].price_type|default:$gra_items[i].default_price_type}
			</td>
			<td align=right>
			{if $gra_items[i].sku_type ne 'CONSIGN'}{$gra_items[i].cost|number_format:$dp}{else}-{/if}<br>
			{$gra_items[i].selling_price|number_format:2}	
			</td>
			<td>{$gra_items[i].added}</td>
			<td align=center>{if $gra_items[i].reason}<img src="/ui/rejected.png" align=absmiddle width=16>{else}<img src="/ui/approved_grey.png" align=absmiddle width=16>{/if} {$gra_items[i].reason}</td>
		</tr>
		{/section}
		</table>
</div>
</div>
</div>
