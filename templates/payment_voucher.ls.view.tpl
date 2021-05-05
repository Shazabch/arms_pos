{*
4/19/2017 10:44 AM Khausalya 
- Enhanced changes from RM to use config setting. 
*}

{include file=header.tpl}

{literal}
<script>
</script>
<style>
</style>
{/literal}

<h1>Payment Voucher Log Sheet</h1>

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<th width=90>Log Sheet No :</th>
<td><h3>{$form.ls_no} ({$form.voucher_branch})</h3></td>
</tr>
</table>

<br>

<table id=tbl_log_sheet class=tb border=0 cellspacing=0 cellpadding=2 width="100%">

{foreach from=$items item=e key=page}
<tr>
<td colspan=9>
{if $page}
<h4>{$form.ls_no}/{$page}</h4>
{else}
<h4>{$form.ls_no} Remain Items</h4>
{/if}
</td>

<tr style="border:1px solid #999; padding:5px; background-color:#fe9">
<th nowrap>No.</th>
<th>Status</th>
<th>Ref No.</th>
<th>Cheque Date</th>
<th width=550>Pay To</th>
<th nowrap>Banker</th>
<th>Cheque No.</th>
<th>Amount ({$config.arms_currency.symbol})</th>
<th>Cheque Collect At</th>
</tr>

{section name=i loop=$items.$page}
{assign var=n value=$smarty.section.i.iteration}
<tr>
<td align=center>{$n}.</td>
<td align=center>
{if $items.$page[i].log_sheet_page}
	{if $items.$page[i].status}
	<img src=/ui/approved.png border=0 title="View Voucher">
	{else}
	<img src=/ui/cancel.png border=0 title="View Voucher">
	{/if}
{else}
<img src=/ui/approved_grey.png border=0 title="View Voucher">
{/if}
</td>
<td>{$items.$page[i].voucher_no}</td>
<td>{$items.$page[i].payment_date|date_format:$config.dat_format}</td>
<td>{$items.$page[i].issue_name|default:$items.$page[i].vendor} {if $items.$page[i].voucher_type eq '3'} / {$items.$page[i].vendor}{/if}
</td>
<td align=center nowrap>{$items.$page[i].bank}</td>
<td align=center>{$items.$page[i].cheque_no|default:"&nbsp;"}</td>
<td align=right>{$items.$page[i].total_credit-$items.$page[i].total_debit|number_format:2}</td>
<td align=center>{$items.$page[i].c_branch_code}</td>
</tr>
	{/section}
{/foreach}
</table>

<br>

<p align=center>
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/payment_voucher.php'">
</p>

{include file=footer.tpl}
