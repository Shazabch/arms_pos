{*
11/20/2009 4:12:33 PM Andy
- Add after save or close go back to select previous selected branch and tab

11/23/2009 1:04:46 PM Andy
- Add Reject feature

4/12/2011 10:18:57 AM Andy
- Show Request Date.

10/13/2011 10:33:12 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/23/2012 2:06:29 PM Alex
- add packing uom code after description

2/19/2013 11:41 AM Justin
- Modified the qty round up to base on config set.

3/5/2013 4:20 PM Andy
- Enhance to show Stock Balance By & Group Stock Balance By in Picking List.

06/23/2020 05:15 Sheila
- Updated button css.
*}

{include file='header.tpl'}

<script>
{literal}

function do_save(){
	document.f_a['a'].value = 'save_picking_list';
	submit_form();
}

function generate_do(){
	var all_inp = $$('#f_a input.inp_qty');
	var total_qty = 0;
	for(var i=0; i<all_inp.length; i++){
		if(all_inp[i].value>0){
			total_qty = 1;
			break;
		}
	}
	if(total_qty<=0){
		alert('No item to generate, please enter some deliver Qty.');
		return;
	}
	if(!confirm('Click OK to confirm Generate DO.'))	return;
	document.f_a['a'].value = 'confirm_picking_list';
	submit_form();
}

function submit_form(){
	document.f_a.submit();
}

function reject_list(){
	if(!confirm("Click OK to continue."))   return;
	document.f_reject.submit();
}
{/literal}
</script>
<h1>DO Request Picking List</h1>

<table>
	<tr>
		<td><b>From</b></td>
		<td>{$all_branch[$form.branch_id].code} - {$all_branch[$form.branch_id].description}</td>
	</tr>
	<tr>
		<td><b>To</b></td>
		<td>{$all_branch[$form.do_branch_id].code} - {$all_branch[$form.do_branch_id].description}</td>
	</tr>
	<tr>
		<td><b>Processing by</b></td>
		<td>{$form.u}</td>
	</tr>
</table>
<br />

<form name="f_reject" type="post" style="display:none;">
	<input type="hidden" name="a" value="reject_picking_list" />
	<input type="hidden" name="pid" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{$form.do_branch_id}" />
</form>

<form name="f_a" method="post" onSubmit="return false;" id="f_a">
<input type="hidden" name="a" />
<input type="hidden" name="pid" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />
<input type="hidden" name="do_branch_id" value="{$form.do_branch_id}" />

<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border small body" cellspacing="1" cellpadding="1">
	<tr bgcolor="#ffffff">
		<th>&nbsp;</th>
		<th>ARMS Code</th>
		<th>Art No.</th>
		<th>MCode</th>
		<th>Description</th>
		<th>Location</th>
		<th>Stock<br />Balance</th>
		<th>Stock<br />Balance By</th>
		<th>Group<br />Stock<br />Balance By</th>
		<th>Delivered<br />Qty</th>
		<th>Request<br />Date</th>
		<th>Current<br />Request<br />Qty</th>
		<th>DO<br />Qty</th>
		<th>Selling<br />Price</th>
		<th>Remarks</th>
		<th>Last Update</th>
		<th>Generated<br />DO/PO</th>
	</tr>
	{foreach from=$items item=r name=f}
		<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" {if $smarty.request.highlight_item_id eq $r.sku_item_id}class=highlight_row{/if}>
			<td>{$smarty.foreach.f.iteration}</td>
			<td>{$r.sku_item_code|default:'&nbsp;'}</td>
			<td>{$r.artno|default:'&nbsp;'}</td>
			<td>{$r.mcode|default:'&nbsp;'}</td>
			<td>{$r.description|default:'&nbsp;'} {include file=details.uom.tpl uom=$r.packing_uom_code}</td>
			<td>{$r.location|default:'-'}</td>
			<td class="r">{$r.stock_balance|qty_nf}</td>
			<td class="r">{$r.stock_balance2|qty_nf}</td>
			<td class="r">{$r.group_stock_balance2|qty_nf}</td>
			
			<td class="r">{$r.total_do_qty|qty_nf}</td>
			<td align="center">{$r.added|date_format:'%Y-%m-%d'}</td>
			
			<td class="r">{$r.request_qty|qty_nf}</td>
			<td align="center"><input type="text" name="do_qty[{$r.id}]" value="{$r.do_qty}" size="8" class="r inp_qty" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" {if $form.user_id ne $sessioninfo.id}readOnly {/if}/></td>
			<td class="r">{$r.selling_price|number_format:2:".":""}</td>
			<td>{$r.comment|default:'&nbsp;'}</td>
			<td align="center">{$r.last_update}</td>
			<td align="center">
				{foreach from=$r.do_list item=do_id}
					{if $do_id>0}
						<a href="do.php?a=open&id={$do_id}&branch_id={$r.request_branch_id}&highlight_item_id={$r.sku_item_id}" target="_blank">DO#{$do_id}</a><br />
					{/if}
				{/foreach}
				{if $r.po_id}
					<a href="po.php?a=open&id={$r.po_id}&branch_id={$r.request_branch_id}&highlight_item_id={$r.sku_item_id}" target="_blank">PO#{$r.po_id}</a><br />
				{/if}
			</td>
		</tr>
	{/foreach}
</table>

<p align="center">
	{if $form.user_id eq $sessioninfo.id}
	<input class="btn btn-error" type=button value="Reject" onclick="reject_list();">
	<input class="btn btn-success" type="button" value="Save & Close" onclick="do_save();" >
	{/if}
	<input class="btn btn-error" type="button" value="Close" onclick="document.location='/do_request.process.php?branch_id={$form.do_branch_id}&t=2'">
	{if $form.user_id eq $sessioninfo.id}
	<input class="btn btn-primary" type="button" value="Generate DO" onclick="generate_do();">
	{/if}
</p>
</form>

{include file='footer.tpl'}
