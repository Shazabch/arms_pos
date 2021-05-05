{*
9/18/2007 3:45:05 PM yinsee
- delivered PO not allow cancel

12/10/2007 9:46:03 AM gary
- add hq po distribution list.
*}
{include file=header.tpl}
{literal}
<script>

function do_print(){
	curtain(true);
	show_print_dialog();
}

function show_print_dialog(){
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	$('print_dialog').style.display = 'none';
{/literal}
		$('ifprint').src = '{$smarty.server.PHP_SELF}?a=print&branch_id={$form.branch_id}&id={$form.id}&load=1&'+Form.serialize(document.fprn);
{literal}
	curtain(false);
}

function print_cancel(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}


function print_all(){
	document.fprn.print_vendor_copy.checked = true;
	document.fprn.print_branch_copy.checked = true;
	print_ok();
}

function do_revoke()
{
	if (confirm('Copy details from this PO to a new PO?'))
	{
{/literal}
		document.location='{$smarty.server.PHP_SELF}?a=revoke&id={$form.id}&branch_id={$form.branch_id}';
{literal}
	}
}

function do_cancel(){
	if (confirm('Cancel this PO?'))
	{
{/literal}
		document.location='{$smarty.server.PHP_SELF}?a=cancel&id={$form.id}&branch_id={$form.branch_id}';
{literal}
	}
}
</script>
{/literal}


<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=fprn>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=checkbox name="print_vendor_copy" checked> Vendor's Copy<Br>
<input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)<br>
<p align=center><input type=button value="Print" onclick="print_ok()"> 
<input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>

<h1>Purchase Order (ID#{$form.id})</h1>
<h3>Status:
{if $form.status == 1}
	{if $form.approved}
		Fully Approved  
		{if $form.active} 
		(PO No: {$form.po_no})
		{else}
		(Branches PO: 
		{foreach from=$hq_po_list item=pn name=pn}
		{if $smarty.foreach.pn.iteration>1} ,{/if}
		<a href="/purchase_order.php?a=view&id={$pn.po_id}&branch_id={$pn.branch_id}" target="_blank">
		{$pn.po_no} {if $pn.b_name}({$pn.b_name}){/if}
		</a>
		{/foreach}
		)
		{/if}
	{else}
	In Approval Cycle
	{/if}
{elseif $form.status == 5}
	Cancelled
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft Purchase Order
{/if}

{if $form.revoke_id}(This PO has been revoked to PO ID#{$form.revoke_id} <a href="?a=open&id={$form.revoke_id}&branch_id={$form.branch_id}"><img src=ui/view.png border=0 title="Click here to open the new PO" align=absmiddle></a>){/if}
</h3>

{if $approval_history}
<br>
<div class="stdframe" style="background:#fff">
<h4>Approval History</h4>
{section name=i loop=$approval_history}
<p>
{if $approval_history[i].status==1}
<img src=ui/approved.png width=16 height=16>
{elseif $approval_history[i].status==2}
<img src=ui/rejected.png width=16 height=16>
{else}
<img src=ui/terminated.png width=16 height=16>
{/if}
{$approval_history[i].timestamp} by {$approval_history[i].u}<br>
{$approval_history[i].log}
</p>
{/section}
</div>
{/if}

<br>
<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<table  border=0 cellspacing=0 cellpadding=4>
<tr>
	<td width=100><b>PO No.</b></td>
	<td width=100>{$form.po_no|default:"-"}</td>

	<td width=100><b>Proforma PO No.</b></td>
	<td width=100>
	{if $form.hq_po_id}
	HQ{$form.hq_po_id|string_format:"%06d"}(PP)
	{else}
	{$form.report_prefix}{$form.id|string_format:"%06d"}(PP)
	{/if}
	</td>

	<td width=100><b>PO Date</b></td>
	<td width=100>{$form.po_date|date_format:"%d/%m/%Y"}</td>
</tr>
<tr>
	<td width=100><b>Owner</b></td>
	<td width=100>{$form.user}</td>
{if $form.cancel_by}
	<td width=100><b>Cancelled</b></td>
	<td width=100 colspan=3>{$form.cancelled} by {$form.cancel_user}</td>
{/if}
</tr>
<tr>
	<td><b>Department</b></td>
	<td>{$department}</td>
	<td><b>Vendor</b></td>
	<td colspan=3>{$form.vendor}</td>
</tr>
{if $config.po_show_terms}
<tr>
	<td><b>Payment Terms<b></td>
	<td>{$form.term|default:"-"} Days</td>
	<td><b>Prompt Payment Terms<b></td>
	<td>{$form.prompt_payment_term|default:"-"} Days</td>
	<td><b>Prompt Payment Discount<b></td>
	<td>{$form.prompt_payment_discount|default:"-"}%</td>
</tr>
{/if}

{if is_array($form.deliver_to)}
<tr>
	<td><b>PO Option</b></td>
	<td colspan=5>
		{if $form.po_option eq '1'}
			HQ Purchase and sell to Branches <font color=#990000><b>(HQ Payment)</b></font><br>
		{elseif $form.po_option eq '2'}
			HQ purchase on behalf of Branches <font color=#990000><b>(Branch Payment)</b></font>
		{/if}
	</td>
</tr>
<tr>
	<td valign=top><b>Delivery Branches</b></td>
	<td colspan=5>
		<table  cellpadding=2 cellspacing=2 border=0>
		<tr class="small">
			<th>&nbsp;</th>
			<th>Vendor</th>
			<th>Delivery<br>Date</th>
			<th>Cancellation<br>Date</th>
			<th>Partial<br>Delivery</th>
		</tr>
		{section name=i loop=$branch}
		{assign var=bid value=$branch[i].id}
		{if in_array($branch[i].id,$form.deliver_to)}
		<tr>
			<td><b>{$branch[i].code}</b></td>
			<td>{$form.delivery_vendor_name[$bid]|default:"-same as above-"}</td>
			<td>{$form.delivery_date[$bid]}</td>
			<td>{$form.cancel_date[$bid]}</td>
			<td>{if $form.partial_delivery[$bid]}<img src=ui/checked.gif>{/if}</td>
		</tr>
		{/if}
		{/section}
		</table>
	</td>
</tr>
{else}
<tr>
	<td><b>Delivery Branch</b></td>
	<td>
	{if $form.po_branch_id>0}
	{$form.po_branch}
	{else}
	{$form.branch}
	{/if}
	</td>
</tr>
<tr>
	<td><b>Delivery Date</b></td>
	<td>{$form.delivery_date}</td>
	<td><b>Cancellation Date</b></td>
	<td>{$form.cancel_date}</td>
</tr>
<tr>
	<td><b>Partial Delivery</b></td>
	<td>{if $form.partial_delivery}Allowed{else}Not Allowed{/if}</td>
</tr>
{/if}
</table>
</div>

<br>

<div id="new_sheets">
{assign var=sheet_n value=0}
{include file=purchase_order.view.sheet.tpl}
</div>

<p id=submitbtn align=center>
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="close_window('/purchase_order.php')">
{if $form.active}
	<input type=button value="Print{if $form.status==0} Draft{elseif !$form.approved} Proforma{/if} PO" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_print()">
	{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.user==$sessioninfo.u && !$form.delivered}
	<input type=button value="Cancel PO" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_cancel()">
	{/if}
{else}
	{if $form.revoke_id == 0}
	<input type=button value="Revoke" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_revoke()">
	{/if}
{/if}
</p>

<iframe width=1 height=1 style="visibility:hidden" id=ifprint></iframe>
{include file=footer.tpl}
