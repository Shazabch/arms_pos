{*
7/15/2011 11:25:33 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
	
<style>
{literal}
.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}
td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
{/literal}
</style>

<script type="text/javascript">
var doc_no = '{$form.po_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}
<!-- loop for each sheets -->
<!---------------------------------------------  BRANCH COPY -------------------------------------------------->
{if $print.branch_copy}
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td valign=top>
		<h2>{$billto.description}</h2><br>
	{$billto.address|nl2br}<br>
	Tel: {$billto.phone_1}{if $billto.phone_2} / {$billto.phone_2}{/if}
	{if $billto.phone_3} &nbsp;&nbsp; Fax: {$billto.phone_3}{/if}

	</td>
	<td rowspan=2 align=right valign=top>

	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>PO Physical Picking List</b></div>
		<br>
		</td></tr>
{*		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>*}
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>
		
		{if !$config.po_set_max_items}
		<tr bgcolor="#cccccc"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	{if $config.link_code_name}
	<th rowspan=2>{$config.link_code_name}</th>
	{/if}
{*	<th style="background:#aaa">Selling</th>*}
	<th>UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2 style="background:#aaa">FOC</th>
{*	<th rowspan=2>Gross<br>Amount</th>
	<th rowspan=2>Tax<br>(%)</th>
	<th rowspan=2>Discount</th>
	<th nowrap>T.Selling</th>
	<th nowrap style="background:#aaa">GP</th>
*}
</tr>
<tr class="hd topline">
{*	<th style="background:#aaa">Cost</th>*}
	<th>UOM</th>
	<th class="xsmall">Ctn</th>
	<th class="xsmall">Pcs</th>
	<th class="xsmall" style="background:#aaa">Ctn</th>
	<th class="xsmall" style="background:#aaa">Pcs</th>
{*	<th nowrap>Nett Amt</th>
	<th nowrap style="background:#aaa">Profit(%)</th>
*}
</tr>
<tr>
<td {if $config.link_code_name}colspan=9{else}colspan=8{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=counter value=0}
{foreach name=t from=$po_items item=item key=item_id}
{cycle name=rowbg values="#ddd,#ccc" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height=15 class="rw{cycle name=crow values=",2"}">
	<td rowspan=2>{$start_counter+$counter}</td>
	<td rowspan=2 nowrap>{$item.artno_mcode|default:"&nbsp;"}
	<br>{$item.sku_item_code}</td>
	<td rowspan=2>
		{if $item.is_foc}({$item.foc_id}){/if}
		{$item.description}
		{assign var=foc_id value=$item.id}
		{if $foc_annotations.$foc_id}({$foc_annotations.$foc_id}){/if}
		{if $item.remark}<br><i>{$item.remark}</i>{/if}
		{if $item.remark2}<br><i>{$item.remark2}</i>{/if}
	</td>
	{if $config.link_code_name}
	<td rowspan=2>{$item.link_code|default:"&nbsp;"}</td>
	{/if}
	
	{assign var=cost value=$item.order_price/$item.order_uom_fraction}
	{assign var=sell value=$item.selling_price/$item.selling_uom_fraction}
{*	<td nowrap align=right style="background:{$rowbg};{if $sell<$cost}font-weight:bold;{/if}">
	{$item.selling_price|number_format:3}
	</td>
*}
	<td nowrap>{$item.selling_uom|default:'EACH'}</td>
	<td rowspan=2 nowrap>{$item.qty|qty_nf|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap>{$item.qty_loose|qty_nf|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc|qty_nf|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc_loose|qty_nf|ifzero:"&nbsp;"}</td>
{*
	{if $item.is_foc}
	<th rowspan=2>FOC</th>
	<td rowspan=2>&nbsp;</td>
	<td rowspan=2>&nbsp;</td>
	{else}
	<td rowspan=2 nowrap align=right>{$item.gamount|number_format:2}</td>
	<td rowspan=2 nowrap>{$item.tax|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap>
		{if $item.disc_remark}
			{$item.disc_remark}<br>
			({$item.discount})
		{elseif $item.discount}
			{$item.discount}
			{if strstr($item.discount,"%")}
				<br>({$item.disc_amount|number_format:2})
			{/if}
		{else}
		&nbsp;
		{/if}
	</td>
	{/if}
	<td nowrap align=right>{$item.total_selling|number_format:2}</td>
	{if $item.is_foc}
		{assign var=total_profit value=$item.total_selling}
	{else}
		{assign var=total_profit value=$item.total_selling-$item.amount}
	{/if}
	<td nowrap align=right style="background:{$rowbg}">{$total_profit|number_format:2}</td>
	*}
</tr>
<tr height=15 class="rw{cycle name=crow2 values=",2"}">
{*
	{if $item.is_foc}
	<th style="background:{$rowbg};border-top:1px solid #000">FOC</th>
	{else}
	<td style="background:{$rowbg};border-top:1px solid #000" nowrap align=right>
	{$item.order_price|number_format:3}
	</td>
	{/if}
*}
	<td style="border-top:1px solid #000" nowrap>{$item.order_uom|default:'EACH'}</td>
{*
	{if $item.is_foc}
	<th style="border-top:1px solid #000">FOC</th>
	{else}
	<td style="border-top:1px solid #000" nowrap align=right>{$item.amount|number_format:2}</td>
	{/if}
	
	{assign var=gp value=$total_profit/$item.total_selling*100|number_format:2}
	<td style="background:{$rowbg};border-top:1px solid #000;{if $gp<0}font-weight:bold;{/if}" nowrap align=right>{if $item.total_selling<=0}-{else}{$gp}%{/if}</td>
*}
</tr>
{/if}
{/foreach}

{repeat s=$counter+1 e=$page_items}
<!-- filler -->
<tr height=30>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
{*	<td style="background:{$rowbg}">&nbsp;</td>*}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
{*	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
*}
</tr>
{/repeat}
{*
<td {if $config.link_code_name}colspan=9{else}colspan=8{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <td {if $config.link_code_name}colspan=4{else}colspan=3{/if} rowspan=10 style="background:none" valign=top>
    <h4>Remark</h4>
	{$form.remark|nl2br}<br>
	<br>
    <h4>Remark#2 (For Internal Use)</h4>
	{$form.remark2|nl2br}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Ctn</b><br>
		{$total.ctn|qty_nf}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total.foc+$total.qty|qty_nf}
	</td>
	<td rowspan=2 class="ft" colspan=4 align=right><b>Sub Total</b></td>
	<td rowspan=2 class="ft" align=right>{$total.gamount|number_format:2}</td>
	<td colspan=2 class="ft" align=right><b>T.Amount</b></td>
	{assign var=total_profit value=$total.sell-$total.amount}
	<td class="ft" nowrap align=right>{$total.amount|number_format:2}</td>
	<td class="ft" nowrap align=right>{$total_profit|number_format:2}</td>
</tr>

<tr class="ft topline">
	<td align=right colspan=2><b>T.Selling</b></td>
	<td align=right>{$total.sell|number_format:2}</td>
	<td align=right>{if $total.sell<=0}-{else}{$total_profit/$total.sell*100|number_format:2}%{/if}</td>
</tr>

<!-- misc cost -->
{if $form.misc_cost ne '' && $form.misc_cost > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>{$form.misc_cost}{$form.misc_cost_amount}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount ne '' && $form.sdiscount > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount}
	{if strstr($form.sdiscount,"%") or $form.sdiscount != $form.sdiscount_amount}
		({$total.sdiscount_amount|number_format:2})
	{/if}
	</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- "special" discount -->
{if $form.rdiscount ne '' && $form.rdiscount > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount from Remark#2</b></td>
	<td nowrap align=right>{$form.rdiscount}{$form.rdiscount_amount}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- "special" deduct cost discount -->
{if $form.ddiscount ne '' && $form.ddiscount > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Deduct Cost from Remark#2</b></td>
	<td nowrap align=right>{$form.ddiscount}{$form.ddiscount_amount}</td>
	<td>&nbsp;</td>
</tr>
{/if}



<!-- transportation cost -->
{if $form.transport_cost ne '' && $form.transport_cost > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Transport Charges</b></td>
	<td nowrap align=right>{$form.transport_cost|number_format:2}{$form.transport_cost_amount}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Actual PO Amount</b></td>
	<td nowrap align=right>
		{$total.final_amount|number_format:2}
	</td>
	{assign var=final_profit value=$total.sell-$total.final_amount}
	<td align=right>{$final_profit|number_format:2}</td>
</tr>
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Total Selling</b></td>
	<td align=right>{$total.sell|number_format:2}</td>
	<td align=right>
	{if $total.sell}
	{$final_profit/$total.sell*100|number_format:2}%
	{/if}
	</td>
</tr>

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Supplier PO Amount</b></td>
	<td nowrap align=right>
		{$total.final_amount2|number_format:2}
	</td>
	<td>&nbsp;</td>
</tr>
*}
</table>
</div>

<br><br><br>
<table width=100%>
<tr>
{if $config.po_internal_copy_3signatures}
<td valign=bottom class=small>
_________________<br>
Order By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Accepted By<br>
Name:
</td>

{else}
<td width=50% valign=bottom class=small>
______________________________<br>
Accepted By<br>
Name:
</td>
{/if}

<td valign=bottom align=right nowrap>
<h1>Internal Copy</h1>
</td>
</tr>

</table>
</div>
{/if}
<!-- end loop -->
