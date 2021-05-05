{*
REVISION HISTORY
=================
11/1/2007 12:22:18 PM gary
- add artno column. 

5/22/2017 4:44 PM Justin
- Enhanced Load Vendor SKU to check master vendor if got config po_vendor_listing_enable_check_master_vendor.

11/9/2018 2:48 PM Andy
- Enhanced to have "Print Additional Week Column".
*}

{include file=report_header.potrait.tpl}

{assign var=last_dept value=''}

<table cellspacing="0" cellpadding="4" border="0" class="tb" width="100%">
<tr bgcolor="#ffee99">
	<th>ARMS Code<br>MCode</th>
	<th>Artno<br>{if $config.link_code_name}{$config.link_code_name}{/if}</th>
	<th>Description</th>
	<th>Dept</th>
	<th>Brand</th>
	{if $smarty.request.show_cost}<th width=50>GRN Cost</th>{/if}
	<th width="30">Cost</th>
	<th width="30">Qty</th>
	<th width="150">Remark</th>
	{section start=0 loop=$week_col name=w}
		<th>W{$smarty.section.w.iteration}</th>
	{/section}
</tr>
{foreach from=$items name=i key=sid item=item}
	{if $last_dept ne $item.department}
		{assign var=last_dept value=$item.department}
		{assign var=cols value=10}
		{if $smarty.request.show_cost}{assign var=cols value=$cols+1}{/if}
		{if $week_col>0}{assign var=cols value=$cols+$week_col}{/if}
		<tr><th align="left" colspan="{$cols}">{$item.department}</th></tr>
	{/if}
	<tr>
		<td>{$item.sku_item_code}<br>{$item.mcode|default:"&nbsp;"}</td>
		<td>{$item.artno}<br>{if $config.link_code_name}{$item.link_code|default:"&nbsp;"}{/if}</td>
		<td class=small>{$item.description|default:"&nbsp;"}</td>
		<td class=small>{$item.department|default:"&nbsp;"}</td>
		<td class=small>{$item.brand|default:"&nbsp;"}</td>
		{if $smarty.request.show_cost}<td align=right>{$item.grn_cost|number_format:2|default:"&nbsp;"}</td>{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{section start=0 loop=$week_col name=w}
			<td>&nbsp;</td>
		{/section}
	</tr>
{/foreach}
</table>

{include file=report_footer.landscape.tpl}
