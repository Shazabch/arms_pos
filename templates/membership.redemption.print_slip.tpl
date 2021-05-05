{*

8/25/2010 6:56:31 PM Justin
- Added Cash column and Total Cash Needed.

9/29/2010 3:37:10 PM Justin
- Added Total Cash column, Cash Paid and Change.

7/15/2011 2:05:03 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/12/2011 2:09:54 PM Alex
- add qty_nf to control quantity decimal

1/14/2013 1:56 PM Justin
- Enhanced to show voucher value and code from item if found.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/19/2017 3.27 PM Khausalya 
- Enhanced changes from RM to use config setting. 

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

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

.normal_tbl{
	border-collapse: collapse;
	
}

.normal_tbl tr{
    border-top:1px solid black;
    border-right:1px solid black;
}

.normal_tbl td,.normal_tbl th{
    border-bottom:1px solid black;
    border-left:1px solid black;
	padding:2px;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.redemption_no}';
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

<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
    <td><img src="{get_logo_url mod='membership'}" height=80 hspace=5 vspace=5></td>
	<td width=100%>
	<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
	<td rowspan="2" valign="bottom">
	    <table class="normal_tbl">
			<tr>
				<td colspan=2><div style="background:#000;padding:4px;color:#fff" align=center><b>REDEMPTION Info</b></div></td>
			</tr>
			<tr bgcolor="#cccccc" height=22>
				<td nowrap>Redemption No.</td><td nowrap>{$form.redemption_no}</td>
			</tr>
		    <tr height=22>
				<td nowrap>Date</td><td nowrap>{$form.added|date_format:$config.dat_format}</td>
			</tr>
			<tr bgcolor="#cccccc" height=22>
				<td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td>
			</tr>
			<tr>
			    <td colspan="2" align="center">{$page}</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td style="padding:5px;" colspan="2">
        <table class="normal_tbl" width="100%">
			<tr>
				<td colspan="6"><div style="background:#000;padding:4px;color:#fff" align=center><b>Membership Info</b></div></td>
			</tr>
			<tr height=22>
				<td><b>Name</b></td><td>{$membership_info.designation} {$membership_info.name}</td>
				<td><b>NRIC</b></td><td>{$membership_info.nric}</td>
				<td><b>Card No.</b></b></td><td>{$membership_info.card_no}</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<br />
{*<tr>
	<td>
	<table width="100%" cellspacing=5 cellpadding=0 border=0 height="120px" class="tb">
		<tr>
	        <td >

				<table>
				<tr><td><b>Name</b></td><td>{$membership_info.designation} {$membership_info.name}</td></tr>
				<tr><td><b>NRIC</b></td><td>{$membership_info.nric}</td></tr>
				<tr><td><b>Current {$config.membership_cardname} Number</b></td><td>{$membership_info.card_no}</td></tr>
				</table>
	        </td>
		</tr>
	</table>
	</td>
</tr>
</table>*}


<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br />/MCode</th>
	<th>SKU Description</th>
	<th width=40>Pts</th>
	<th nowrap width=60>Cash</th>
	<th nowrap width=60>Qty</th>
	<th nowrap>Total Cash<br />Need</th>
	<th nowrap>Total Pts<br />Need</th>
</tr>

{assign var=counter value=0}
{foreach from=$items item=r}
<!-- {$counter++} -->
<tr class="no_border_bottom">
	<td align="center" nowrap>{$start_counter+$counter}.</td>
	<td align="center" nowrap>{$r.sku_item_code|default:"&nbsp;"}</td>
	<td align="center" nowrap>{if $r.artno <> ''}{$r.artno|default:"&nbsp;"}{else}{$r.mcode|default:"&nbsp;"}{/if}</td>
	<td width="90%">
		<div class="crop">{$r.description|default:"&nbsp;"}</div>
		{if $r.is_voucher && $r.voucher_code}
			Voucher Value: {$r.voucher_value|number_format:2}<br />
			Voucher Codes: {$r.voucher_code}
		{/if}
	</td>
	<td align="right">{$r.pt_need|number_format|ifzero:"-"}</td>
	<td align="right">{$r.cash_need|number_format:2|ifzero:"-"}</td>
	<td align="right">{$r.qty|qty_nf|ifzero:"-"}</td>
	<td align="right">{$r.cash_need*$r.qty|number_format:2|ifzero:"-"}</td>
	<td align="right">{$r.pt_need*$r.qty|number_format|ifzero:"-"}</td>
</tr>
{/foreach}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20 class="no_border_bottom">
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
{/repeat}

{if $is_lastpage}
<tr class="total_row">
    <th align=right colspan="6" >Total</th>
	<th align=right>{$form.total_qty|qty_nf}</th>
    <th align=right>{if $form.total_cash_need}{$config.arms_currency.symbol}{$form.total_cash_need|number_format:2|default:'0'}{else}-{/if}</th>
	<th align="right">{$form.total_pt_need|number_format|default:"&nbsp;"}</th>
</tr>
<tr class="total_row">
    <th align=right colspan="8" >Current Points</th>
    <th align="right">{$form.points_left|number_format|default:"&nbsp;"}</th>
</tr>
<tr class="total_row">
    <th align=right colspan="8" >Points Left</th>
    <th align="right">{$form.points_left-$form.total_pt_need|number_format|default:"&nbsp;"}</th>
</tr>
<tr class="total_row">
    <th align=right colspan="8" >Total Cash Paid</th>
    <th align="right">{if $form.total_cash_paid}{$config.arms_currency.symbol}{$form.total_cash_paid|number_format:2|default:"&nbsp;"}{else}-{/if}</th>
</tr>
<tr class="total_row">
    <th align=right colspan="8" >Total Cash Needed</th>
    <th align="right">{if $form.total_cash_need}{$config.arms_currency.symbol}{$form.total_cash_need|number_format:2|ifzero:"&nbsp;"}{else}-{/if}</th>
</tr>
<tr class="total_row">
    <th align=right colspan="8" >Change</th>
    <th align="right">{if $form.total_cash_paid-$form.total_cash_need>0}{$config.arms_currency.symbol}{$form.total_cash_paid-$form.total_cash_need|number_format:2|default:"&nbsp;"}{else}-{/if}</th>
</tr>
{/if}

</table>

{if $is_lastpage}
<br>

<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$sessioninfo.fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name: <br>
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

</div>
