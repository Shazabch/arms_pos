{*
1/11/2013 10:33 AM Justin
- Enhanced to show user voucher activation link when found customer redeem vouchers.
- Enhanced to auto show new page for voucher activation page.

4/3/2013 4:04 PM Justin
- Bug fixed on print slip not working properly.
*}

{include file='header.tpl'}

<script>
{literal}
function print_slip(){
	document.f_print.target = 'if_print';
	//document.f_print.target = '_blank';
	document.f_print.submit();
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

<iframe name="if_print" style="visibility:hidden;width:1px;height:1px;"></iframe>

<form name="f_print" style="display:none;" action="membership.redemption_history.php">
	<input type="hidden" name="a" value="print_slip" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
</form>

<div style="text-align:center;border:1px solid black;background:#f0f0f0;">
	<h2>Thank You! Your Redemption Success.</h2>
	<p>
		<a href="javascript:void(print_slip());"><img src="/ui/icons/printer.png" border="0" align="absmiddle"> Click here to print slip</a>
		&nbsp; | &nbsp;
		<a href="membership.redemption.php" onClick="return confirm('Click OK to start new redemption.');"><img src="/ui/icons/page.png" align="absmiddle" border="0 /"> Make New Redemption</a>
		{if $smarty.request.have_vouchers}
			&nbsp; | &nbsp;
			<a href="masterfile_voucher.activate.php?mr_id={$smarty.request.id}&mr_branch_id={$smarty.request.branch_id}" target="_blank"><img src="/ui/icons/application_cascade.png" align="absmiddle" border="0" /> Voucher Activation</a>
		{/if}
	</p>
</div>

{include file='footer.tpl'}
<script>
{literal}
	if(confirm('Click OK to print slip')) print_slip();
{/literal}
{if $smarty.request.have_vouchers}
	window.open('masterfile_voucher.activate.php?mr_id={$smarty.request.id}&mr_branch_id={$smarty.request.branch_id}');
{/if}
</script>
