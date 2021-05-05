{*
10/28/2009 10:22:52 AM edward
- add GRN Owner

10/6/2010 3:34:47 PM Justin
- Added Document No to show PO No when found config['use_grn_future'].

6/1/2015 11:30 AM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.
*}

<form name=f_a method=post>
<hr noshade size=1>

<h1>GRN (Manager Verification) for GRN{$form.id|string_format:"%05d"}</h1>

{include file=approval_history.tpl}

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<input type=hidden name=a value="save_detail">
<input type=hidden name=id value={$form.id}>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRN Amount</b></td><td><font class="hilite" color=red>{$form.amount|number_format:2}</font></td>
<td><b>Account Amount</b></td><td><font class="hilite" color=red>{$form.account_amount|number_format:2}</font></td>
<td><b>Invoice/DO No</b></td><td>{$form.account_doc_no}</td>
</tr>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:"%d/%m/%Y"}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr>
<tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:"%d/%m/%Y"}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr>
<tr>
<td><b>GRN Owner</b></td>
<td style="color:blue">{$form.u}</td>
{if $grr.invoice_no}
	<td><b>Invoice No.</b></td>
	<td style="color:blue" colspan="3">{$grr.invoice_no}</td>
{/if}
</tr>
<tr>
<td><b>Department</b></td><td colspan=3>{$form.department|default:$grr.department}</td>
</tr>
<tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
</tr>
<tr>
<td width=100 valign="top"><b>Document Type.</b></td><td width=100 valign="top"><font color=blue>{$grr.type}</font></td>
<td width=100 valign="top"><b>Document No.</b></td><td width=150 valign="top"><font color=blue>{$grr.doc_no}</font></td>
{if $grr.doc_type eq 'PO'}
<td width=100 valign="top"><b>Partial Delivery</b></td><td width=100 valign="top"><font color=blue>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</font></td>
{/if}
</tr>
{if $config.grn_have_tax || ($config.use_grn_future && $config.use_grn_future_allow_generate_gra)}
	<tr>
		{if $config.grn_have_tax}
			<td><b>Tax</b></td>
			<td>{$form.grn_tax|number_format:2} %</td>
		{/if}
		{if $config.use_grn_future && $config.use_grn_future_allow_generate_gra}
			<td><b>Generate GRA</b></td>
			<td>
				<img src="{if $form.generate_gra}ui/checked.gif{else}ui/unchecked.gif{/if}" style="vertical-align:top;" title="{if $form.generate_gra}This GRN allow to generate all returned items become GRA.{else}This GRN will not generate GRA for those returned items.{/if}">
			</td>
		{/if}
	</tr>
{/if}
</table>
</div>


<div id=tblist>
{assign var=manager_col value=1}
{include file=goods_receiving_note.view.list.tpl}
</div>


<hr noshade size=1>

<p align=center id=bsubmit>
Enter Comment:<br><textarea name="approve_comment" rows=10 cols=60></textarea><br>
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
</p>

</form>
