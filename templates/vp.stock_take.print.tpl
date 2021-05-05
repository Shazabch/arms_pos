{*
1/17/2013 9:55:00 AM Fithri
- add column avg cost and amount for report

1/23/2013 10:00:00 AM Fithri
- add stock balance, variance column

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{literal}
td div.crop{
	height:auto;
	max-height:2em;
	overflow:hidden;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$st_date}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">

{/if}

<div class=printarea>
<h3>{$branch.description|upper} - {$branch.code} {if $branch.company_no}({$branch.company_no}){/if}</h3>

<table>
<tr>
	<td nowrap><b>Stock Take Date</b>:</td><td width=100 style="border-bottom:1px solid #000;">&nbsp;&nbsp;{$st_date}</td>
	<td nowrap><b>Print Date</b>:</td><td width=100 style="border-bottom:1px solid #000;">&nbsp;&nbsp;{$smarty.now|date_format:"%Y-%m-%d"}</td>
	<td nowrap><b>Printed By</b>:</td><td width=130 style="border-bottom:1px solid #000;">&nbsp;&nbsp;{$user_name|upper}</td>
</tr>
</table>
{$page}
<br>
<table border=0 cellpadding=2 cellspacing=0 align=center width=100% class="tb">
<tr>
<th align=center bgcolor="{#TB_COLHEADER#}">Item</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Arms Code</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Mcode</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Art No</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Category</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Description</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Qty</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Stock Balance</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Variance</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Avg Cost</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Amount</th>
</tr>

{assign var=counter value=0}

{foreach from=$items item=r}
	<!-- {$counter++} -->
	<tr height="35">
		<td align=center style="width:5%">{$start_counter+$counter}.</td>
		<td>{$r.sku_item_code}</td>
		<td>{$r.mcode|default:'&nbsp;'}</td>
		<td>{$r.artno|default:'&nbsp;'}</td>
		<td>{$r.category|default:'&nbsp;'}</td>
		<td style="width:35%"><div class="crop">{$r.description|default:'&nbsp;'}</div></td>
		<td align=right style="width:5%">{$r.qty}</td>
		<td align=right style="width:5%">{$r.sb_qty}</td>
		<td align=right style="width:5%">{$r.qty-$r.sb_qty}</td>
		<td align=right>{$r.avg_cost|default:'&nbsp;'}</td>
		<td align=right>{$r.amount|default:'&nbsp;'}</td>
	</tr>
{/foreach}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height="35">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}
</table>
</div>
