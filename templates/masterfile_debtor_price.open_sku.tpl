<form name="f_sku" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_update_debtor_price" />
	<input type="hidden" name="debtor_id" value="{$data.debtor_id}" />
	<input type="hidden" name="sid" value="{$data.sid}" />
	
	<div style="background-color: #fff;">
		<table width="100%" class="report_table">
			<tr>
				<td class="col_header"><b>ARMS Code</b></td>
				<td>{$data.info.sku_item_code}</td>
				<td class="col_header"><b>MCode</b></td>
				<td>{$data.info.mcode|default:'-'}</td>
			</tr>
			
			<tr>
				<td class="col_header"><b>Art No</b></td>
				<td>{$data.info.artno|default:'-'}</td>
				<td class="col_header"><b>{$config.link_code_name}</b></td>
				<td>{$data.info.link_code|default:'-'}</td>
			</tr>
			
			<tr>
				<td class="col_header"><b>Description</b></td>
				<td colspan="3">{$data.info.description|default:'-'}</td>
			</tr>
		</table>
	</div>
	
	<div style="height:250px;overflow-y:auto;border: 1px solid grey;margin-top:5px;background-color: #fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th>Branch</th>
				<th width="100">
					Normal Price
					<img src="ui/icons/information.png" onClick="alert('Leave Debtor Price empty will use back Normal Price.');" align="absmiddle" />
				</th>
				<th width="100" nowrap>
					Debtor Price 
					<img src="ui/icons/textfield_rename.png" align="absmiddle" onClick="SKU_PRICE_DIALOG.edit_all_branch_debtor_price();" title="Edit All Branch Debtor Price" />
				</th>
			</tr>
			
			{foreach from=$data.bid_list item=bid}

				<tr>
					<td>{$branch_list.$bid.code} - {$branch_list.$bid.description}</td>
					<td align="right">{$data.b_info.$bid.normal_price|number_format:2}</td>
					<td align="right">
						<input type="text" name="debtor_price[{$bid}]" value="{$data.b_info.$bid.debtor_price|number_format:2|ifzero:''}" style="text-align:right;width:80px;" class="debtor_price" onChange="SKU_PRICE_DIALOG.debtor_price_changed(this);" />
					</td>
				</tr>

			{/foreach}
		</table>
	</div>
	
	<div style="text-align:center;" id="div_all_action_btn">
		<input type="button" value="Update" onClick="SKU_PRICE_DIALOG.update_clicked();" id="btn_update" />
		<input type="button" value="Close" onClick="SKU_PRICE_DIALOG.close();" />
	</div>
</form>