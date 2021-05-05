<form name=f_a method=post>

<hr noshade size=2>
<h1>Purchase Order (ID#{$form.id})</h1>

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type=hidden name=a value="save_approval">
<input type=hidden name=id value={$form.id}>
<input type=hidden name=approve_comment value="">

{$approval_history}

<table  border=0 cellspacing=0 cellpadding=4>
<tr>
	<td><b>Apply By</b></td>
	<td>{$form.u}</td>
</tr>
<tr>
	<td><b>Vendor</b></td>
	<td>{$form.vendor}</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td>{$form.department}</td>
</tr>
<tr>
	<td><b>PO Date</b></td>
	<td>{$form.po_date|date_format:"%d/%m/%Y"}</td>
</tr>
{if $form.branch_id == 1}
<tr>
	<td><b>PO Option</b></td>
	<td>
		{if $form.po_option eq '1'}
			HQ Purchase and sell to Branches <font color=#990000><b>(HQ Payment)</b></font><br>
		{elseif $form.po_option eq '2'}
			HQ purchase on behalf of Branches <font color=#990000><b>(Branch Payment)</b></font>
		{/if}
	</td>
</tr>
<tr>
	<td valign=top><b>Delivery Branches</b></td>
	<td>
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
		{if is_array($form.deliver_to) and in_array($branch[i].id,$form.deliver_to)}
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
	<td>{$form.branch}</td>
</tr>
<tr>
	<td><b>Delivery Date</b></td>
	<td>{$form.delivery_date}</td>
	<td width=20>&nbsp;</td>
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

<hr noshade size=2>

<p align=center id=bsubmit>
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
<input type=button value="Terminate" style="background-color:#900; color:#fff;" onclick="do_terminate()">
</p>

</form>
