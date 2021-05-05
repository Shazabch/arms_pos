{*
7/15/2011 2:47:49 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

4/19/2017 11:24 AM Khausalya 
- Enhanced changes from RM to use config setting. 
*}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print()">
{/if}

{literal}
<style>
#tbl_box td{
	border:1px solid #000;
}
#tbl_box td, #tbl_box th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.hd {
	background-color:#ddd;
	text-align:center;
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
.cancel{
	color:red;
}
</style>
{/literal}

<div class=printarea>
<div style="float:right">{$page}</div>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="xsmall">
<tr>
	<td><!--img src="{get_logo_url}" height=80 hspace=5 vspace=5--></td>
	<td width=30%>
	<h3>
	{$branch.description}<br>
	{$bank} Cheque Butt (Cheque No : {$form.from_c_no} - {$form.to_c_no})<br>	
	</h3>
	</td>
</tr>
</table>

<br>
<!---START item table-->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box xsmall">

<tr class="hd topline">
<th width=30>No.</th>
<th width=12%>Cheque No.</th>
<th width=15%>Cheque Date</th>
<th>Pay To</th>
<th width=12%>Amount ({$config.arms_currency.symbol})</th>
</tr>

{assign var=counter value=0}

{section name=i loop=$items}
{assign var=counter value=$counter+1}
{assign var=n value=$smarty.section.i.iteration-1}
<tr {if $items.$n.status eq '0' || $items.$n.status eq '-1'}class=cancel{/if}>
<td width=20 align=center>{$n+1}</td>
<td align=center>{$items.$n.cheque_no}</td>
<td>{$items.$n.payment_date|date_format:$config.dat_format}</td>
<td>
{$items.$n.issue_name|default:$items.$n.vendor} 
{if $items.$n.voucher_type eq '3'} / {$form.vendor}{/if}
{if $items.$n.status eq '0'}({$items.$n.cancelled_reason}){/if}
</td>
<td align=right>{$items.$n.total_credit-$items.$n.total_debit|number_format:2}</td>
</tr>
{/section}

</table>
</div>
<!--- END item table-->

<br>

<table>
<tr>
<td align=left width=300>Printed By : {$sessioninfo.fullname}</td>
<td align=left>Printed Date : {$smarty.now|date_format:$config.dat_format}</td>
</tr>
</table>
</div>
