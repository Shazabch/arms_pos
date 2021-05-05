{*
6/19/2008 3:37:30 PM yinsee
- view detail will show the adjusted value if approved=true 
10/28/2009 10:22:52 AM edward
- add GRN Owner
11/11/2009 4:05:03 PM edward
- add show status
10/6/2010 3:34:47 PM Justin
- Added Document No to show PO No when found config['use_grn_future'].

6/1/2015 11:30 AM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.
*}
{include file=header.tpl}
{literal}
<style>
#tblist textarea, #tblist input { background:#fff; border:1px solid #ccc; color:#000;}
</style>
{/literal}

<h1>GRN (Account Verification) for GRN{$form.id|string_format:"%05d"}</h1>
<h3>Status:
{if $form.approved}
	GRN Verified
{elseif $form.status == 1}
	Pending GRN Correction
{elseif $form.status == 2}
	Cancelled
{/if}
</h3>

{include file=approval_history.tpl}

<form name=f_a>
<div class="stdframe" style="background:#fff">
<h4>General Information</h4>
<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRN Amount</b></td><td><font class="hilite" color=red>{$form.amount|number_format:2}</font></td>
<td><b>Account Amount</b></td><td><font class="hilite" color=red>{$form.account_amount|number_format:2}</font></td>
<td><b>Invoice/DO No</b></td><td>{$form.account_doc_no}</td>
</tr>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr>
<tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
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
{if $grr.type eq 'PO'}
<td width=100 valign="top"><b>PO Amount</b></td><td width=100 valign="top"><font color=blue>{$grr.po_amount|number_format:2}</font></td>
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


<br>
<div id=tblist>
{if $form.approved}{assign var=manager_col value=1}{/if}
{include file=goods_receiving_note.view.list.tpl}
</div>
</form>

{if $form.non_sku_items && $config.use_grn_future}
	<br><h2>Returned Item(s)</h2>
	<div style="overflow:auto;">
	<table width=100% cellpadding=2 cellspacing=1 border=0 style="border:1px solid #000">
		<thead>
		<tr height=32 bgcolor="#ffee99">
			<th>#</th>
			<th width="20%">Code</th>
			<th width="60%">Description</th>
			<th>Cost Price</th>
			<th>Rcv<br />Qty (Pcs)</th>
			<th>Amount</th>
		</tr>
		</thead>
	
		<tbody id="tbditems">
			{foreach from=$form.non_sku_items key=sku_code item=item name=fitem}
				{assign var=n value=$smarty.foreach.fitem.iteration-1}
				{if $form.non_sku_items.code.$n}
					{assign var=ttl_pcs value=$ttl_pcs+$form.non_sku_items.qty.$n}
					{assign var=curr_amt value=$form.non_sku_items.qty.$n*$form.non_sku_items.cost.$n}
					{assign var=ttl_amt value=$ttl_amt+$curr_amt|round2}
					<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
						<td nowrap width="2%" align="right">{$smarty.foreach.fitem.iteration}.</td>
						<td>{$form.non_sku_items.code.$n}</td>
						<td>{$form.non_sku_items.description.$n}</td>
						<td align="right">{$form.non_sku_items.cost.$n|number_format:4:".":""}</td>
						<td class=r width="5%">{$form.non_sku_items.qty.$n|default:0}</td>
						<td class=r width="5%">{$curr_amt|round2}</td>
					</tr>
				{/if}
			{/foreach}
		</tbody>
	
		<tfoot>
			<tr height="24" bgcolor="#ffee99">
				<td colspan="4" align=right><b>Total</b></td>
				<td align="right" id="total_qty">{$ttl_pcs|default:0}</td>
				<td align="right" id="total_amt">{$ttl_amt|default:0}</td>
			</tr>
		</tfoot>
	</table>
	</div>
{/if}

<p align=center>
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;" onclick="close_window('/goods_receiving_note_approval.account.php')">
</p>

{include file=footer.tpl}
<script>
Form.disable(document.f_a);
</script>
