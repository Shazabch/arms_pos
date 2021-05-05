{include file=header.tpl}
<script>
{literal}
function check_amount(id)
{
	var sstr = "["+id+"]";
	document.f_a.elements['amount'+sstr].value = float(document.f_a.elements['amount'+sstr].value);
	if (document.f_a.elements['amount'+sstr].value != document.f_a.elements['grn_amount'+sstr].value)
	{
	    $('as'+id).innerHTML = "<img src=/ui/cancel.png align=absmiddle>";
	}
	else
	{
		$('as'+id).innerHTML = "<img src=/ui/approved.png align=absmiddle>";
	}
}
{/literal}
</script>

<h1>GRN (Buyer Verification)</h1>
{if $grn}
<p>- The following GRN has been verified by account department.<br>
- GRN with different Amount need to be verified by Buyer. You can verify them by clicking the Detail <img src="/ui/go.png" border=0 align=absmiddle> button</p>

<form method=post name=f_a>
<input type=hidden name=a value="approve">
<table cellspacing=1 cellpadding=4 border=0 style="border:1px solid #000">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>GRN Date</th>
	<th>GRN No.</th>
	<th>Vendor</th>
	<th>Department</th>
	<th>Doc Type</th>
	<th>Doc No</th>
	<th>Amount</th>
	<th>Entered<br>Amount</th>
	<th>Qty<br>Variance</th>
	<th>Verify</th>
</tr>
{section name=i loop=$grn}
<tr bgcolor={cycle values="#ffffff,#eeeeee"}>
	<td>{$smarty.section.i.iteration}.</td>
	<td>{$grn[i].added|date_format:"%d/%m/%Y %I:%M%p"}</td>
	<td>{$grn[i].id|string_format:"GRN%05d"}</td>
	<td>{$grn[i].department}</td>
	<td>{$grn[i].vendor}</td>
	<td>{$grn[i].type}</td>
	<td>{$grn[i].doc_no}</td>
	<td align=right>{$grn[i].amount|number_format:2}</td>
	<td align=right>{$grn[i].account_amount|number_format:2}
		{if $grn[i].amount != $grn[i].account_amount}
		<img src="/ui/cancel.png" border=0 align=absmiddle>
		{else}
		<img src="/ui/approved.png" border=0 align=absmiddle>
		{/if}
	</td>
	<td align=center><font color=red><b>
	{if $grn[i].type eq 'PO'}
	{$grn[i].have_variance|number_format}
	{/if}
	</b></font></td>
	<td align=center>
		<a href="?a=verify_detail&id={$grn[i].id}"><img src="/ui/go.png" border=0 align=absmiddle></a>
	</td>
</tr>
{/section}
</table>
{/if}
</form>

{include file=footer.tpl}
