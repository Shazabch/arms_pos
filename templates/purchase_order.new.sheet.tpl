{*
8/21/2007 4:02:06 PM - yinsee
- Remove password checking for 'all categories'

10/23/2007 4:35:18 PM gary
- add cost indicate column.

11/30/2007 3:06:11 PM gary
- list out related sku.

2/20/2008 5:59:46 PM  yinsee
- zero qty not allow confirm

*}
{config_load file="site.conf"}
{if $sheet_n eq '' and $sheet_n ne '0'}<h1>Error - sheet_n not defined</h1>{/if}
<div id="sheet[{$sheet_n}]" style="margin-bottom:10px; z-index:0;">

<!--div style="float:right"><img src=ui/del.png align=absmiddle> <a href="javascript:void(cancel_sheet('sheet[{$sheet_n}]'))">remove sheet</a></div-->

<!--h3>PO Sheet #{$sheet_n+1}</h3-->

{if $errm[$sheet_n]}
<div id=err><div class=errmsg><ul>
{foreach from=$errm[$sheet_n] item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<input type=hidden name="sheet[{$sheet_n}]" value="{$sheet_n}">

<input type=hidden id="total_check[{$sheet_n}]" name="total_check[{$sheet_n}]" value="{$total[$sheet_n].ctn+$total[$sheet_n].qty+$total[$sheet_n].foc|number_format}">

<!-- po item table -->
<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border small body" border=0 cellspacing=1 cellpadding=1 id=tbl_items>
<thead>
<!-- for HQ -->
{if $form.branch_id == 1}
<tr bgcolor=#ffffff>
	<th colspan=2 rowspan=2>#</th>
	<th nowrap rowspan=2>Article /<br>MCode</th>
	<th nowrap rowspan=2>SKU Description</th>
	<th rowspan=2>Selling<br>UOM</th>
	<th rowspan=2>Purchase<br>UOM</th>
	{if $form.po_option eq '1'}
	<th colspan=2>Cost Price</th>
	{else}
	<th rowspan=2>Cost<br>Price</th>
	<th rowspan=2>Cost<br>Indicate</th>
	{/if}
	<!--th rowspan=2>PM<br>(%)</th-->
	<th nowrap colspan={count multi=2 var=$form.deliver_to}>Qty</th>
	<th rowspan=2>Total<br>Qty</th>
	<th rowspan=2>Total<br>FOC</th>
	<th rowspan=2>Gross<br>Amount</th>
	<th rowspan=2>Tax<br>(%)</th>
	<th rowspan=2>Discount<br>[<a href="javascript:void(discount_help())">?</a>]</th>
	<th rowspan=2>Nett<br>Amount</th>
	<th rowspan=2>Total<br>Selling</th>
	<th rowspan=2>Gross<br>Profit</th>
	<th rowspan=2>Profit(%)</th>
</tr>
<tr bgcolor=#ffffff>
{if $form.po_option eq '1'}
<th>HQ</th>
<th>Branch</th>
{/if}

{if $form.deliver_to}
{section name=i loop=$branch}
{if in_array($branch[i].id,$form.deliver_to)}
	<th nowrap>{$branch[i].code}<br>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
	<th nowrap>FOC<br>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
{/if}
{/section}
{/if}
</tr>
{else}
<tr bgcolor=#ffffff>
	<th colspan=2>#</th>
	<th nowrap>Article /<br>MCode</th>
	<th nowrap>SKU Description</th>
	<th>Sugg.<br>Selling<br>Price</th>
	<th>Selling<br>UOM</th>
	<th>Purchase<br>UOM</th>
	<th>Cost<br>Price</th>
	<th>Cost<br>Indicate</th>
	<!--th>PM<br>(%)</th-->
	<th>Qty<br>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
	<th>FOC<br>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
	<th>Gross<br>Amount</th>
	<th>Tax<br>(%)</th>
	<th>Discount</th>
	<th>Nett<br>Amount</th>
	<th>Total<br>Selling</th>
	<th>Gross<br>Profit</th>
	<th>Profit(%)</th>
</tr>
{/if}
</thead>

<tbody id="po_items[{$sheet_n}]">
{foreach from=$po_items[$sheet_n] item=item name=fitem}

<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="titem{$item.id}">
{include file=purchase_order.new.po_row.tpl}
</tr>
{/foreach}
</tbody>

<tfoot>
<!-- sku search -->
<tr class=normal bgcolor={#TB_ROWHEADER#}>
{if $form.deliver_to}
	<td colspan={count var=$form.deliver_to multi=2 offset=17} nowrap>
{else}
	<td colspan=18 nowrap>
{/if}
		<input name="sku_item_id[{$sheet_n}]" size=3 type=hidden>
		<input name="sku_item_code[{$sheet_n}]" size=13 type=hidden>
		Search: <input id="autocomplete_sku[{$sheet_n}]" name="sku[{$sheet_n}]" size=35 onclick="this.select()">

	<input type=button value="Add" onclick="add_item({$sheet_n})" style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
	
	<input type=button value="Add FOC" onclick="sel_foc_cost({$sheet_n})" style="background:#cee;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
	
	<div id="price_history[{$sheet_n}]" style="display:none;position:absolute;width:350px;height:275px;background:#fff;border:1px solid #000;padding:5px;">
	<div id="price_history_list[{$sheet_n}]" style="width:350px;height:250px;overflow:auto;"></div>
	<div align=center style="padding-top:5px">
	<input type=button onclick="Element.hide(this.parentNode.parentNode)" value="Close">
	</div>
	</div>
	
	<input type=button value="Cost History" onclick="get_price_history(this,{$sheet_n})" style="background:#ef9;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
	<input type=button value="SKU Detail" onclick="show_sku_detail({$sheet_n})" style="background:#ef9;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
	<!--added gary to add all category items-->
	<input type=button value="Related SKU" onclick="show_related_sku({$sheet_n});" style="background:#ef9;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
	
	<div id="autocomplete_sku_choices[{$sheet_n}]" class="autocomplete" style="height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	<br>
	<img src=ui/pixel.gif width=40 height=1>
	<input onchange="reset_sku_autocomplete({$sheet_n})" type=radio name="search_type[{$sheet_n}]" value=1 checked> MCode &amp; {$config.link_code_name}
	<input onchange="reset_sku_autocomplete({$sheet_n})" type=radio name="search_type[{$sheet_n}]" value=2> Article No
	<input onchange="reset_sku_autocomplete({$sheet_n})" type=radio name="search_type[{$sheet_n}]" value=3> ARMS Code
	<input onchange="reset_sku_autocomplete({$sheet_n})" type=radio name="search_type[{$sheet_n}]" value=4> Description
    <br>
	<img src=ui/pixel.gif width=40 height=1><input type=checkbox id=all_dept[{$sheet_n}] onclick="reset_sku_autocomplete({$sheet_n});"> <label for=all_dept[{$sheet_n}]>Search from other department</label>
	<!--span id=pw[{$sheet_n}] style="display:none">-
	<font color=red>Enter your password </font><input type=password id=dept_pass[{$sheet_n}] size=8 onchange="if($('allow_dept[{$sheet_n}]').checked) allow_dept_autocomplete({$sheet_n})">
	<input type=button onclick="$('autocomplete_sku[{$sheet_n}]').value='';$('autocomplete_sku[{$sheet_n}]').focus()" value="OK">
	</span-->
	</td>
</tr>

<!-- total -->
<tr bgcolor=#ffffff class=normal height=24>
{if $form.deliver_to}
	{if $form.po_option==1}
	<th colspan=9 align=right>Total</th>
	{else}
	<th colspan=8 align=right>Total</th>
	{/if}
	{section name=i loop=$branch}
		{if in_array($branch[i].id,$form.deliver_to)}
		{assign var=bid value=$branch[i].id}
		<td colspan=2>
			<b>
			T.Sell: <span id="br_sp[{$branch[i].id}]">{$total[$sheet_n].br_sp[$bid]|number_format:2}</span>
			<br>
			T.Cost: <span id="br_cp[{$branch[i].id}]">{$total[$sheet_n].br_cp[$bid]|number_format:2}</span>
			</b>
		</td>
		{/if}
	{/section}
{else}
	<th colspan=9 align=right>Total</th>
{/if}
	<th align=right id=total_ctn[{$sheet_n}]>Ctn: {$total[$sheet_n].ctn|number_format}</th>
	<th align=right id=total_pcs[{$sheet_n}]>Pcs: {$total[$sheet_n].qty+$total[$sheet_n].foc|number_format}</th>
	<th align=right id=total_gross_amount[{$sheet_n}]>{$total[$sheet_n].gamount|number_format:2}</th>
	<th align=right colspan=3 id=total_amount[{$sheet_n}]>{$total[$sheet_n].amount|number_format:2}</th>
	<th align=right id=total_sell[{$sheet_n}]>{$total[$sheet_n].sell|number_format:2}</th>
	{assign var=total_profit value=$total[$sheet_n].sell-$total[$sheet_n].amount}
	<th align=right id=total_profit[{$sheet_n}] {if $total_profit<=0}style="color:#f00"{/if}>{$total_profit|number_format:2}</th>
	<th align=right id=total_margin[{$sheet_n}] {if $total_profit<=0}style="color:#f00"{/if}>{if $total[$sheet_n].sell<=0}-{else}{$total_profit/$total[$sheet_n].sell*100|number_format:2}%{/if}</th>
</tr>

<!-- misc cost -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
<td colspan=5 rowspan=7 valign=top>
<!--- remark box -->
<b>Remark:</b><br>
<textarea style="width:200px;height:80px;font:10px arial;" name="remark[{$sheet_n}]">{$form.remark[$sheet_n]|escape}</textarea>
</td>
<td colspan=4 rowspan=7 valign=top>
<b>Remark#2 (For Internal Use):</b><br>
<textarea style="width:200px;height:80px;font:10px arial;" name="remark2[{$sheet_n}]">{$form.remark2[$sheet_n]|escape}</textarea>
</td>
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
    	Misc Cost [<a href="javascript:void(cost_help())">?</a>]
    </th>
    <th align=right colspan=2>
		+ <input size=8 id=misc_cost[{$sheet_n}] name=misc_cost[{$sheet_n}] value="{$form.misc_cost[$sheet_n]}" onchange="calculate_total()" onclick="clear0(this)">
	</th>
</tr>

<!-- final discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
		Discount [<a href="javascript:void(discount_help())">?</a>]
	</th>
	<th align=right colspan=2>
		- <input size=8 id=sdiscount[{$sheet_n}] name=sdiscount[{$sheet_n}] value="{$form.sdiscount[$sheet_n]}" onchange="calculate_total()" onclick="clear0(this)">
	</th>
</tr>


<!-- "special" discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
		Discount from Remark#2 [<a href="javascript:void(discount_help())">?</a>]
	</th>
	<th align=right colspan=2>
		- <input size=8 id=rdiscount[{$sheet_n}] name=rdiscount[{$sheet_n}] value="{$form.rdiscount[$sheet_n]}" onchange="calculate_total()" onclick="clear0(this)">
	</th>
</tr>

<!-- "special" discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
		Deduct Cost from Remark#2 [<a href="javascript:void(discount_help())">?</a>]
	</th>
	<th align=right colspan=2>
		- <input size=8 id=ddiscount[{$sheet_n}] name=ddiscount[{$sheet_n}] value="{$form.ddiscount[$sheet_n]}" onchange="calculate_total()" onclick="clear0(this)">
	</th>
</tr>


<!-- transportation cost -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
    	Transportation Charges
    </th>
    <th align=right colspan=2>
		+ <input size=8 id=transport_cost[{$sheet_n}] name=transport_cost[{$sheet_n}] value="{$form.transport_cost[$sheet_n]}" onchange="calculate_total()" onclick="clear0(this)">
	</th>
</tr>

<!-- total amount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
		PO Amount
	</th>
	<th align=right colspan=2 id=final_amount[{$sheet_n}] class=large>
	{$total[$sheet_n].final_amount|number_format:2}
	</th>
	{assign var=final_profit value=$total[$sheet_n].sell-$total[$sheet_n].final_amount}
	<th align=right id=final_profit[{$sheet_n}] class=large style="{if $final_profit<=0}color:#f00{/if}">
	{$final_profit|number_format:2}
	</th>
	<th align=right id=final_margin[{$sheet_n}] class=large style="{if $final_profit<=0}color:#f00{/if}">
	{if $total[$sheet_n].sell}
	{$final_profit/$total[$sheet_n].sell*100|number_format:2}%
	{/if}
	</th>
</tr>

<!-- supplier amount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if $form.branch_id == 1}
	<th align=right colspan={count var=$form.deliver_to multi=2 offset=4}>
{else}
	<th align=right colspan=4>
{/if}
		Supplier PO Amount
	</th>
	<th align=right colspan=2 id=final_amount2[{$sheet_n}] class=large>
	{$total[$sheet_n].final_amount2|number_format:2}
	</th>
</tr>

</tfoot>
</table>

</div>
