{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
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

{/literal}
</style>
<body onload="window.print()">
{/if}


<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='membership'}" height="80" hspace="5" vspace="5">{else}&nbsp;{/if}</td>
	<td width=100%>
	<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
    <td rowspan=2 align=right>
	    <table class="xlarge">
		<tr>
          <td colspan=2>
      <div style="background:#000;padding:4px;color:#fff" align=center><b>
          DELIVERY
		</b>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Receipt No.</td><td nowrap>{receipt_no_prefix_format branch_id=$data.branch_id counter_id=$data.counter_id receipt_no=$data.receipt_no}</td></tr>
		<tr height=22><td nowrap>Date</td><td nowrap>{$data.date|date_format:$config.dat_format}</td></tr>		
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
      <table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
      <tr>
          <td valign=top width=50% style="border:1px solid #000; padding:5px">
          <h4>Deliver To</h4>
          <b>{$data.delivery_name}</b><br/>
          {$data.delivery_address|nl2br}<br/>
          Tel: {$data.delivery_phone}
      </tr>
      </table>
	</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
    <th nowrap>Artno / MCode</th>
	<th width="90%">SKU Description</th>	
	<th nowrap width=40>Qty</th>	
</tr>
{assign var=counter value=0}
{section name=i loop=$pos_items}
{assign var=counter value=$counter+1}
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  <td align="center" nowrap>{$start_counter+$counter}.</td>
  <td align="center" nowrap>{$pos_items[i].sku_item.sku_item_code|default:'-'}</td>
  <td align="center" nowrap>{$pos_items[i].sku_item.artno|default:$pos_items[i].sku_item.mcode|default:'-'}</td>
  <td width="90%"><div class="crop">{$pos_items[i].sku_item.description}</div></td>  
  <td align="right">{$pos_items[i].qty|qty_nf}</td>
</tr>
{assign var=total_qty value=$pos_items[i].qty+$total_qty}
{/section}
{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>  
  <td>&nbsp;</td>	
</tr>
{/section}
{if $is_lastpage}
<tr class="total_row">
  <th align=right colspan=4 >Total</th>  
  <th align=right>{$total_qty|qty_nf}</th>
</tr>
{/if}

</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$data.remark|default:"-"|nl2br}
</div>

<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}
<p align=center class=small>** This document is for reference purpose only **</p> 


</div><!--printarea-->