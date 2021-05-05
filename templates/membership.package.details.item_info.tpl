{*
10/23/2019 3:22 PM Andy
- Change the word "Used Entry" to "Used Count".

10/25/2019 10:26 AM Andy
- Rename to word from "Entry" to "Credit".
*}

<h2>Please select an item to redeem</h2>

<input type="hidden" name="mpp_guid" value="{$mpp.guid}" />

<div id="div_f_mpp_items_info_list">
	<table class="report_table" width="100%" style="background-color: #fff;">
		<tr class="header">
			<th width="50">&nbsp;</th>
			<th>Title</th>
			<th>Description</th>
			<th>Remark</th>
			<th>Credit Needed</th>
			<th>Redeem Count</th>
			<th>Max Redeem</th>
		</tr>
		{foreach from=$item_list key=item_guid item=r}
			<tr id="tr_f_mpp_items-{$item_guid}">
				<td align="center">
					{if ($mpp.remaining_entry gte $r.entry_need) and (($r.used_count lt $r.max_redeem) or !$r.max_redeem)}
						<input type="button" value="Redeem" onClick="PACKAGE_ITEMS_DIALOG.redeem_clicked('{$item_guid}');" />
					{/if}
				</td>
				<td class="td_title">{$r.title|escape:'html'}</td>
				<td class="td_description">{$r.description|escape:'html'}</td>
				<td class="td_remark">{$r.remark|escape:'html'|nl2br}</td>
				<td class="td_entry_need" align="right">{$r.entry_need}</td>
				<td align="right">{$r.used_count|ifzero:'-'}</td>
				<td align="right">{$r.max_redeem|ifzero:'-'}</td>
			</tr>
		{/foreach}
	</table>
</div>

<div id="div_f_mpp_items_confirmation" style="display:none;background-color: #fff;border:3px outset black;padding: 5px;">
	<input type="button" value="Back" onClick="PACKAGE_ITEMS_DIALOG.redeem_cancel_clicked();" />
	<h3 align="center">Redeem Confirmation</h3>
	<input type="hidden" name="redeem_item_guid" />
	
	<table class="report_table" width="100%">
		{* Title *}
		<tr>
			<td class="col_header">Title</td>
			<td id="td_confirmation_title"></td>
		</tr>
		
		{* Description *}
		<tr>
			<td class="col_header">Description</td>
			<td id="td_confirmation_description"></td>
		</tr>
		
		{* Remark *}
		<tr>
			<td class="col_header">Remark</td>
			<td id="td_confirmation_remark"></td>
		</tr>
		
		{* Entry Needed *}
		<tr>
			<td class="col_header">Credit Needed</td>
			<td id="td_confirmation_entry_need"></td>
		</tr>
		
		{if $config.masterfile_enable_sa}
			{* Sales Agent *}
			<tr>
			<td class="col_header">Sales Agent Code</td>
			<td>
				<textarea name="sa_code_list"></textarea><br />
				Key in Sales Agent Code, separate by "," or new line (e.g: SA001, SA002)
			</td>
		</tr>
		{/if}
	</table>
	
	<p align="center">
		<input type="button" style="background-color:#091;color:#fff;font:bold 20px Arial;" value="Confirm Redeem" onClick="PACKAGE_ITEMS_DIALOG.redeem_confirm_clicked();" />
	</p>
</div>