{*
7/15/2011 2:49:57 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/19/2017 11:47 AM Khausalya 
- Enhanced changes from RM to use config setting. 
*}
{if !$skip_header}
{include file='header.print.tpl'}

<script type="text/javascript">
var doc_no = '{$form.ls_no}';
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
</style>
{/literal}

<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="tb">
<tr colspan=6>
<b>{$form.branch_name} ({$smarty.now|date_format:$config.dat_format})</b>
</tr>

<tr valign=top>
<th nowrap colspan=6 width=325>
{if $form.is_hq}
	Payment Voucher Log Sheet (HQ Issue For {$form.branch_code})			
{else}
	Payment Voucher Log Sheet ({$form.branch_code}->HQ)		
{/if}
</th>
</tr>

<tr align=left>
<td nowrap width=50>Log No</td>
<td align=left width=80>{$form.ls_no}</td>

<td nowrap width=50>Printed By</td>
<td align=left width=50>{$sessioninfo.u}</td>

<td nowrap width=50 style="border-left:0px;">Page No</td>
<td align=left>{$page}</td>
</tr>
</table>
<br>

<!---START item table-->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">

<tr class="hd topline">
<th width=20>No.</th>
<th>Ref No</th>
<th>Payment Date</th>
<th width=60%>Pay To</th>
<th nowrap>Amount<br>({$config.arms_currency.symbol})</th>
<th>Payment<br>Type</th>
</tr>


<tr>
<td colspan=6 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=count value=0}
{assign var=counter value=0}
{assign var=total_positive value=0}
{assign var=total_negative value=0}

{if $list && !$form.is_hq}

{section name=i loop=$list}
{assign var=counter value=$counter+1}
{assign var=n value=$smarty.section.i.iteration-1}
{assign var=no value=$smarty.section.i.iteration}

<tr height=18 class="rw{cycle name=row values=",2"}">
<td align=center>{$n+1}.</td>
<td align=center width=50>
{if $list.$n.status eq '0'}<strike>{/if}
{$list.$n.voucher_no}
</td>
<td align=center>{$list.$n.payment_date|date_format:$config.dat_format}</td>
<td>
{if $list.$n.status eq '0'}
<span class="">Cancelled</span>
<strike>
{/if}
{if $list.$n.voucher_type eq '3'} 
{$list.$n.issue_name} / {$list.$n.vendor} 
{else}
{$list.$n.vendor|default:$list.$n.issue_name} 
{/if}
</td>

<td align=right>
{if $list.$n.status eq '0'}<strike>{/if}
{$list.$n.total_credit-$list.$n.total_debit|number_format:2}
</td>

<td align=center  nowrap>
{if $list.$n.status eq '0'}<strike>{/if}

{if $list[i].voucher_type eq '1'}
{if $list[i].urgent}Urgent{else}Normal{/if}
{elseif $list[i].voucher_type eq '2'}
Fast Payment
{elseif $list[i].voucher_type eq '3'}
DC Issue Name
{elseif $list[i].voucher_type eq '4'}
Blank Sheet
{/if}
</td>
</tr>
{/section}
{/if}

{if $hq_list && $form.is_hq}
{section name=i loop=$hq_list}
{assign var=counter value=$counter+1}
{assign var=n value=$smarty.section.i.iteration-1}
{assign var=no value=$smarty.section.i.iteration}

<tr height=18 class="rw{cycle name=row values=",2"}">
<td align=center>{$n+1}.</td>
<td align=center>{$hq_list.$n.voucher_no}</td>
<td align=center>{$hq_list.$n.payment_date|date_format:$config.dat_format}</td>
<td>
{if $hq_list.$n.voucher_type eq '3'} 
{$hq_list.$n.issue_name} / {$hq_list.$n.vendor} 
{else}
{$hq_list.$n.vendor|default:$hq_list.$n.issue_name} 
{/if}
</td>

<td align=right>{$hq_list.$n.total_credit-$hq_list.$n.total_debit|number_format:2}</td>

<td align=center nowrap>
{if $hq_list[i].voucher_type eq '1'}
{if $hq_list[i].urgent}Urgent{else}Normal{/if}
{elseif $hq_list[i].voucher_type eq '2'}
Fast Payment
{elseif $hq_list[i].voucher_type eq '3'}
DC Issue Name
{elseif $hq_list[i].voucher_type eq '4'}
Blank Sheet
{/if}
</td>
</tr>
{/section}
{/if}

{repeat s=$counter+1 e=30}
<!-- filler -->
<div>
<tr height=18>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</div>
{/repeat}

</table>
</div>
<!--- END item table-->
<br>

<table>
<tr>
<td align=left>** This is computer generate , not signature require.</td>
</tr>
</table>
</div>
