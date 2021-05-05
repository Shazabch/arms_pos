{*
REVISION HISTORY
=================
10/6/2010 3:34:47 PM Justin
- Added Document No to show PO No when found config['use_grn_future'].
*}

{include file=header.tpl}
<script>
{literal}
function do_close()
{
	if (!confirm('Discard changes and close?'))
	{
	    return;
	}
	document.location = '/goods_receiving_note_approval.account.php';
}
{/literal}
</script>

<form name=f_a method=post>

<h1>GRN (Account Confirmation) for GRN{$form.id|string_format:"%05d"}</h1>

{include file=approval_history.tpl}

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<input type=hidden name=a value="save_confirm">
<input type=hidden name=id value={$form.id}>
<input type=hidden name=doc_no value="{$grr.doc_no}">
<input type=hidden name=type value="{$grr.type}">

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRN Amount</b></td><td><font class="hilite" color=red>{$form.amount|number_format:2}</font></td>
<td><b>Account Amount</b></td><td><font class="hilite" color=red>{$form.account_amount|number_format:2}</font></td>
<td><b>Invoice/DO No</b></td><td>{$form.account_doc_no}</td>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:"%d/%m/%Y"}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:"%d/%m/%Y"}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td><td colspan=3>{$form.department|default:$grr.department}</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
</tr><tr>
<td width=100><b>Document Type.</b></td><td width=100><font color=blue>{$grr.type}</font></td>
<td width=100><b>Document No.</b></td><td width=100><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100><b>Partial Delivery</b></td><td width=100>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</td>
{/if}
</tr>
</table>
</div>

<br>
<div align=right><img src="ui/flag.png" align=absmiddle> <span class=hilite>Make correction to the Received Quantity</span></div>

<div id=tblist>
{include file=goods_receiving_note.acc_confirm_list.tpl}
</div>

<p align=center>
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#090; color:#fff;" onclick="if (!confirm('Click OK to continue.')) return false; this.disabled=true;form.submit()">

<input type=button value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;" onclick="do_close()">
</p>


</form>

<script>
_init_enter_to_skip(document.f_a);
</script>
{include file=footer.tpl}
