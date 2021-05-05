{*
10/15/2012 9:26 AM Andy
- Add import pos items by csv.
- Add a button to clear added pos items.
- Add can auto numbering item id at pos items list.
- Add can tick/un-tick all pos items to delete.
*}

<table style="display:none;">
	{include file="pos_edit.content.pi.tpl" pi_id="__TMP_PI_ID__"}
</table>
		
<table style="display:none;">
	{include file="pos_edit.content.pp.tpl" pp_id="__TMP_PP_ID__"}
</table>
		
<table style="display:none;">
	{include file="pos_edit.content.mm.tpl" mm_id="__TMP_MM_ID__"}
</table>
		
<form class="stdframe" name="f_b" onSubmit="return false;" method="post" enctype="multipart/form-data">
	{assign var=bid value=$search_form.bid}
	{assign var=cid value=$search_form.cid}
	
	<input type="hidden" name="a" value="ajax_save_pos" />
	<input type="hidden" name="branch_id" value="{$bid}" />
	<input type="hidden" name="counter_id" value="{$cid}" />
	<input type="hidden" name="date" value="{$search_form.date}" />
	<input type="hidden" name="pos_id" value="{$data.form.id}" />
	
	<h2>Branch: {$branches_list.$bid.code} &nbsp;&nbsp;&nbsp;&nbsp; Counter: {$counter_list.$cid.network_name} &nbsp;&nbsp;&nbsp;&nbsp; Date: {$search_form.date} &nbsp;&nbsp;&nbsp;&nbsp;
		{if $search_form.receipt_no}
			
		{/if}
	</h2>
	
	<div style="background-color:#fff;">
		<table>
			<tr>
				<!-- Receipt No -->
				<td><b>Receipt No</b></td>
				<td>
					<input type="text" size="5" name="receipt_no" value="{$data.form.receipt_no}" class="required" title="Receipt No" />	
				</td>
				
				<!-- Cashier -->
				<td><b>Cashier</b></td>
				<td>
					<select name="cashier_id" class="required" title="Cashier">
						<option value="">-- Please Select --</option>
						{foreach from=$user_list key=user_id item=r}
							<option value="{$user_id}" {if $data.form.cashier_id eq $user_id}selected {/if}>{$r.u}</option>
						{/foreach}
					</select>	
				</td>
				
				<!-- Cancel Status -->
				<td><b>Active</b></td>
				<td>
					
					<input type="radio" size="2" name="cancel_status" value="0" {if $data.form.cancel_status eq 0}checked {/if} /> Yes
					<input type="radio" size="2" name="cancel_status" value="1" {if $data.form.cancel_status eq 1}checked {/if} /> No	
				</td>
			</tr>
			
			<tr>
				<!-- Start Time -->
				<td><b>Start Time</b></td>
				<td>
					<input type="text" size="20" name="start_time" value="{$data.form.start_time}" class="required" title="Start Time" />	
				</td>
				
				<!-- End Time -->
				<td><b>End Time</b></td>
				<td>
					<input type="text" size="20" name="end_time" value="{$data.form.end_time}" class="required" title="End Time" />	
				</td>
				
				<!-- POS Time -->
				<td><b>POS Time</b></td>
				<td>
					<input type="text" size="20" name="pos_time" value="{$data.form.pos_time}" class="required" title="POS Time" />	
				</td>
			</tr>
			
			<tr>
				<!-- Amount -->
				<td><b>Receipt Amount</b></td>
				<td>
					<input type="text" size="5" name="amount" value="{$data.form.amount}" />	
				</td>
				
				<!-- Amount Tender -->
				<td><b>Amount Tender</b></td>
				<td>
					<input type="text" size="5" name="amount_tender" value="{$data.form.amount_tender}" />	
				</td>
				
				<!-- Amount Change -->
				<td><b>Amount Change</b></td>
				<td>
					<input type="text" size="5" name="amount_change" value="{$data.form.amount_change}" />	
				</td>
			</tr>
			
			<tr>
				<!-- Race -->
				<td><b>Race</b></td>
				<td>
					<input type="text" size="5" name="race" value="{$data.form.race}" />	
				</td>
				
				<!-- Member Card No -->
				<td><b>Member Card No</b></td>
				<td>
					<input type="text" size="5" name="member_no" value="{$data.form.member_no}" />	
				</td>
				
				<!-- Member Point Earned -->
				<td><b>Member Point Earned</b></td>
				<td>
					<input type="text" size="5" name="point" value="{$data.form.point}" />	
				</td>
			</tr>
			
			<tr>
				<!-- Prune Status -->
				<td><b>Prune Status</b></td>
				<td>
					<input type="text" size="5" name="prune_status" value="{$data.form.prune_status}" />	
				</td>
				
				<!-- Receipt Ref No -->
				<td><b>Receipt Ref No</b></td>
				<td>
					<input type="text" size="20" name="receipt_ref_no" value="{$data.form.receipt_ref_no}" />	
				</td>
			</tr>
		</table>
	</div>
	
	<h2>POS Items</h2>
	
	<b>Import by CSV: </b>
	<input type="file" name="pi_csv" />
	<input type="button" value="Import" onClick="POS_FORM.import_pi_by_csv();" />
	(ARMS_CODE, BARCODE, QTY, PRICE, DISCOUNT)
	
	<br />
	[<a href="javascript:void(POS_FORM.clear_pi_list())">Clear</a>]
	<div style="background-color:#fff;">
		<table class="report_table">
			<tr class="header">
				<th width="20">Delete<br />
					<input type="checkbox" id="chx_toggle_pi_all_delete" onChange="POS_FORM.pi_all_delete_changed();" />
				</th>
				<th width="40"></th>
				<th>ARMS Code</th>
				<th>Barcode</th>
				<th>
					Item ID
					<img src="/ui/icons/arrow_refresh_small.png" align="top" class="clickable" onClick="POS_FORM.renum_item_id_clicked();" title="Auto Numbering" />
				</th>
				<th>Qty</th>
				<th>Price</th>
				<th>Discount</th>
			</tr>
			
			<tbody id="tbody_pi_list">
				{foreach from=$data.pos_items item=pi}
					{include file="pos_edit.content.pi.tpl"}
				{/foreach}
			</tbody>
			
			<tfoot>
				<tr class="header">
					<td colspan="5" class="r">Total</td>
					<td align="center"><span id="span_total_pi_qty"></span></td>
					<td colspan="2" align="center"><span id="span_total_amt"></span></td>
				</tr>
			</tfoot>		
		</table>
		
		<br />
		<input type="button" value="Add New POS Items" onClick="POS_FORM.add_pos_items_clicked();" />
	</div>
	
	<h2>POS Payment</h2>
	<div style="background-color:#fff;">
		<table class="report_table">
			<tr class="header">
				<th width="20">Delete</th>
				<th>Type</th>
				<th>Remark</th>
				<th>Amount</th>
			</tr>
			
			<tbody id="tbody_pp_list">
				{foreach from=$data.pos_payment item=pp}
					{include file="pos_edit.content.pp.tpl"}
				{/foreach}
			</tbody>	
		</table>
		
		<br />
		<input type="button" value="Add New POS Payment" onClick="POS_FORM.add_pos_paymnent_clicked();" />
	</div>
	
	<h2>Mix & Match Discount</h2>
	<div style="background-color:#fff;">
		<table class="report_table">
			<tr class="header">
				<th width="20">Delete</th>
				<th>Receipt Description</th>
				<th>More Info</th>
				<th>Amount</th>
			</tr>
			
			<tbody id="tbody_mm_list">
				{foreach from=$data.mix_n_match item=mm}
					{include file="pos_edit.content.mm.tpl"}
				{/foreach}
			</tbody>	
		</table>
		
		<br />
		<input type="button" value="Add Mix & Match Discount" onClick="POS_FORM.add_mix_n_match_clicked();" />
	</div>
	
	<p align="center">
		<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" id="btn_save_pos" onClick="POS_FORM.save_pos_clicked();" />
	</p>
</form>