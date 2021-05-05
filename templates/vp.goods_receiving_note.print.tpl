{*
2/25/2013 1:50 PM Justin
- Enhanced to show Link / Old Code column.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.grn_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}
</style>
<script type="text/javascript">
var doc_no = 'GRN{$form.id|string_format:"%05d"}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">

{/if}

<!-- print sheet -->
<div class=printarea>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
	<td align=center><b>Goods Receiving Note<br />{if $grn.status eq 0}(Draft){else}(Confirmed){/if}</b></td>
</tr>
<tr bgcolor=#cccccc>
	<td align=center><b>{$page}</b></td>
</tr>
</table>
<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
<td><b>GRN No</b></td><td>GRN{$form.id|string_format:"%05d"}</td>
<td><b>GRR No</b></td><td>GRR{$form.grr_id|string_format:"%05d"}</td>
<td><b>GRR Date</b></td><td>{$form.added|date_format:$config.dat_format}</td>
<td><b>Received Date</b></td><td>{$form.rcv_date|date_format:$config.dat_format}</td>
</tr><tr>
<td><b>Department</b></td><td colspan=3>{$form.department|default:$form.department}</td>
<td><b>Document Type.</b></td><td><font color=blue>{$form.type}</font></td>
<td><b>Document No.</b></td><td><font color=blue>{$form.doc_no}</font></td>
</tr>
</table>

<br>
<table class="box small tb" width=100% cellpadding=2 cellspacing=0 border=0>
<tr class="topline botline" bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>ARMS/<br>Mcode</th>
	<th>Artno</th>
	<th>Link Code/<br>Old Code</th>
	<!--<th rowspan=2>Mcode</th>-->
	<th>Description</th>
	<th width="45">Cost<br>Price</th>
	<th>Received<br />Qty (Pcs)</th>
	<th width="45">Amount</th>
</tr>
<tbody id=tbditems>
{* assign var=total value=0}
{assign var=tctn value=0}
{assign var=tpcs value=0}
{assign var=tpctn value=0}
{assign var=tppcs value=0 *}
{assign var=n value=0}

{foreach name=i from=$grn_items item=item key=iid}
{assign var=row_total value=`$item.cost_price*$item.qty`}
{assign var=total value=$total+$row_total|round:2}
{assign var=tpcs value=`$tpcs+$item.pcs`}
<!-- {$n++} -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	<td>{$start_counter+$n}.</td>
	<td>{$item.sku_item_code}{if $item.mcode<>''}<br>{$item.mcode|default:"-"}{/if}</td>
	<td align=center>{$item.artno|default:"-"}</td>
	<td align=center>{$item.link_code|default:"-"}</td>
	<!--<td align=center>{$item.mcode|default:"-"}</td>-->
	<td><div class="crop" style="height:2em">{$item.description}</div></td>
	<td align=right>{$item.cost_price|number_format:$config.global_cost_decimal_points}</td>
	<td align=right>{$item.qty|qty_nf}</td>
	<td align=right>{$row_total|number_format:2}</td>
</tr>
{/foreach}

{assign var=s2 value=$n}
{section name=s start=$n loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=30 bgcolor="{cycle name=r1 values=',#eeeeee'}" class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
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


</tbody>
<tr class="topline" height="24" bgcolor="#cccccc">
<td colspan="6" align="right"><b>{if !$is_last_page}Sub {/if}Total</b></td>
<td align="right">{$tpcs|qty_nf}</td>
<td align="right">{$total|number_format:2}</td>
</tr>
</table>
</div>
