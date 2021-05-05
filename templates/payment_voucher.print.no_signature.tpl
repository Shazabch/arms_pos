{*
REVISION HISTORY
================
2/22/2008 11:42:05 AM GARY
- set all date format as %d/%m/%Y.

7/15/2011 2:50:39 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/19/2017 11:50 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}


{if !$skip_header}
{include file='header.print.tpl'}

<script type="text/javascript">
var doc_no = '{$form.voucher_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
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
table.tb1  {
	border-left:2px solid #000;
	border-top:2px solid #000;
}
table.tb1 td {
	border-bottom: 2px solid #000;
	border-right:2px solid #000;
}

table.tb1 th {
	border-bottom: 2px solid #000;
	border-right:2px solid #000;
}
</style>
{/literal}

<div class=printarea>
<div style="float:right">{$form.voucher_branch_code}</div>
<br>
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr valign=top>
	<td><img src="{get_logo_url}" height=80 hspace=5 vspace=5></td>
	<td width=100% nowrap class="small">
	<h5>{$form.branch_name}</h5>
	{$form.branch_address|nl2br}<br>
	Tel: {$form.branch_phone_1}{if $form.branch_phone_2} / {$form.branch_phone_2}{/if}
	{if $form.branch_phone_3}<br>Fax: {$form.branch_phone_3}{/if}
	</td>
	<td align=right valign=top>
	    <table class="tb1 normal" border=0 cellspacing=0 cellpadding=1 width="100%">
	    
	    <tr>
	    <th nowrap colspan=6 width=480>Cheque Payment Voucher (Supplier Copy)</th>
	    </tr>
	    
	    <tr align=left>
	    <td nowrap width=85 style="border-left:0px;">Payment Date</td>
		<th align=left width=100>{$form.payment_date|date_format:$config.dat_format}</th>
		<td nowrap width=85>Reference No.</td>
		<th align=left width=100>{$form.voucher_no}{if $form.voucher_type eq '5'}(BA){/if}</th> 
		<td nowrap width=85>Voucher No.</td>
		<th align=left width=200>P{$form.banker_code}{$form.payment_date|date_format:"%y%m"}/</th>
	    </tr>
	    
	    <tr align=left>
	    <td nowrap width=85 style="border-left:0px;">Debit A/C No.</td>
		<th align=left width=100>{$form.acct_code|default:$vvc.acct_code|default:"&nbsp;"}</th> 
		<td nowrap width=85>Credit A/C No.</td>
		<th align=left width=100>{$vvc.selected_bank_code|default:"&nbsp;"}</th>
		<td nowrap width=85>Page No :</td>
		<th align=left width=200>{$page}</th>
	    </tr>
	    
	  	</table>
	</td>
</tr>

<tr>
<td colspan=3>
<table class="normal tb" border=0 cellspacing=0 cellpadding=1 width="100%">
<tr>
<td colspan=4 align=left><b>Pay To : </b> {$form.issue_name|default:$form.vendor} {if $form.voucher_type eq '3'} / {$form.vendor}{/if}</td>
</tr>

<tr>
<td colspan=4 align=left><b>Being Payment For : </b> {$form.voucher_remark|default:"&nbsp;"}</td>
</tr>
</table>
</td>

</tr>
</table>

<!---START item table-->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">

<tr class="hd topline">

<th width=18>No.</th>
<th>Doc Type</th>
<th>Doc No.</th>
<th>Doc Date</th>
<th>Amount ({$config.arms_currency.symbol}) </th>

</tr>


<tr>
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=count value=0}
{assign var=counter value=0}
{assign var=total_positive value=0}
{assign var=total_negative value=0}


{section name=i loop=$list}
{assign var=counter value=$counter+1}
{assign var=n value=$smarty.section.i.iteration-1}
{assign var=no value=$smarty.section.i.iteration}

{if $list.$n.type eq 'positive'}
<tr height=10 class="rw{cycle name=row values=",2"}">
{assign var=total_positive value=$list.$n.credit+$total_positive}
<td align=center>{$n+1}.</td>
<td>{$list.$n.doc_type_display}</td>
<td>{$list.$n.doc_no}</td>
<td align=center>{$list.$n.doc_date|date_format:$config.dat_format}</td>
<td align=right>{$list.$n.credit|number_format:2}</td>
</tr>

{elseif $list.$n eq 'total_positive'}

<tr>
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

<tr height=10 class="hd">
<td align=right colspan=4>Subtotal (+)</td>
<td align=right>{$form.total_credit|number_format:2}</td>


{elseif $list.$n.type eq 'negative'}
{assign var=count value=$count+1}
<tr height=10 class="rw{cycle name=row values=",2"}">

{assign var=total_negative value=$list.$n.debit+$total_negative}
<td align=center>{$count}.</td>
<td>{$list.$n.doc_type_display}</td>
<td>{$list.$n.doc_no}</td>
<td align=center>{$list.$n.doc_date|date_format:$config.dat_format}</td>
<td align=right>- {$list.$n.debit|number_format:2}</td>

</tr>

{elseif $list.$n eq 'total_negative' && $form.total_debit>0}

<tr>
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

<tr height=10 class="hd">
<td align=right colspan=4>Subtotal (-)</td>
<td align=right>- {$form.total_debit|number_format:2}</td>
</tr>



{elseif $list.$n eq 'total_amount'}

<tr>
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

<tr height=10 class="hd">
	<div style="float:left">
	<th colspan=4 align=right>TOTAL</th>
	<td align=right>{$form.total_credit-$form.total_debit|number_format:2}</td>
</tr>

<tr>
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

{/if}
{/section}

{repeat s=$counter+1 e=12}
<!-- filler -->
<div>
<tr height=10>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</div>
{/repeat}

{if $current_page==$total_page}
<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<tr><td colspan=5>{$form.total_in_words|upper}</tr>

<td colspan=5 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>


<tr>
<td colspan=3>
Bank : {$vvc.selected_bank_name}
</td>
<td colspan=2>
Cheque No : {if $form.voucher_type eq '5'}BA{else}{$form.cheque_no}{/if}
</td>
</tr>
{/if}

</table>
</div>
<!--- END item table-->

<br>

<table class="small">
<tr>
<td align=left>** This is computer generated , not signature is require.</td>
</tr>
</table>
</div>
