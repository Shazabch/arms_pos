{*
2/19/2013 3:12 PM Justin
- Enhanced to show qty with decimal points.
*}

<div class="r"><span id="span_generating_po"></span><input type="button" value="Create PO" onClick="generate_po();" id="inp_generate_po" /></div>

<form id="f_sku_to_po">
<input type="hidden" name="from_branch" value="{$smarty.request.from_branch}"  />
<input type="hidden" name="vendor_id" value="{$smarty.request.vendor_id}" />

<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
	<tr bgcolor="#ffee99">
		<th>&nbsp;</th>
		<th>ARMS Code</th>
		<th>Art No.</th>
		<th>MCode</th>
		<th>Description</th>
		<th>Location</th>
		<th>Current<br />Request<br />Qty</th>
	</tr>
	{foreach from=$do_request_items item=r name=f}
		<tr bgcolor={cycle values=",#eeeeee"}>
			<td width="60" nowrap>{$smarty.foreach.f.iteration}.
				<input type="checkbox" value="{$r.id}" name="do_request_item_id[]" checked class="chx_item_for_po" />
				<img onclick="get_price_history(this,'{$r.sku_item_id}')" title="History" src="/ui/table_multiple.png"/>
			</td>
			<td>{$r.sku_item_code}</td>
			<td>{$r.artno}</td>
			<td>{$r.mcode}</td>
			<td>{$r.description}</td>
			<td>{$r.location|default:'-'}</td>
			<td align="right">{$r.request_qty|qty_nf}</td>
		</tr>
	{/foreach}
</table>
</form>
