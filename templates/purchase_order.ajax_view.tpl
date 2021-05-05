<h1>Purchase Order (ID#{$form.id})</h1>
<h3>Status:
{if $form.approved}
	Fully Approved (PO No: {$form.po_no})
{elseif $form.status == 1}
	In Approval Cycle
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
	<td><b>Vendor</b></td>
	<td colspan=5>{$form.vendor}</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td>{$department}</td>
</tr>

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
{include file=po.view.sheet.tpl}
</div>

