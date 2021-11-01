<form name="f_sku" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_update_quotation_cost" />
	<input type="hidden" name="vendor_id" value="{$data.vendor_id}" />
	<input type="hidden" name="sid" value="{$data.sid}" />
	
	<div style="background-color: #ffff; padding: 10px;" >
		<div class="table-responsive">
			<table width="100%" class="report_table">
				<tr>
					<td class="col_header form-label"><b>ARMS Code</b></td>
					<td>{$data.info.sku_item_code}</td>
					<td class="col_header form-label"><b>MCode</b></td>
					<td>{$data.info.mcode|default:'-'}</td>
				</tr>
				
				<tr>
					<td class="col_header form-label"><b>Art No</b></td>
					<td>{$data.info.artno|default:'-'}</td>
					<td class="col_header form-label"><b>{$config.link_code_name}</b></td>
					<td>{$data.info.link_code|default:'-'}</td>
				</tr>
				
				<tr>
					<td class="col_header form-label"><b>Description</b></td>
					<td colspan="3">{$data.info.description|default:'-'}</td>
				</tr>
			</table>
		</div>
	</div>
	
	<div style="margin:10px;padding:10px;height:180px;overflow-y:auto;border: 3px solid gainsboro;margin-top:5px;background-color: #fff;">
		<table width="100%" class="report_table">
			<thead class="bg-gray-100">
				<tr class="header">
					<th>Branch</th>
					<th width="100">
						Normal Cost
						<img src="ui/icons/information.png" onClick="alert('Leave Vendor Quotation Cost empty will use back Normal Cost.');" align="absmiddle" />
					</th>
					<th width="100" nowrap>
						Quotation Cost 
						<img src="ui/icons/textfield_rename.png" align="absmiddle" onClick="SKU_QUOTATION_COST_DIALOG.edit_all_branch_quotation_cost();" title="Edit All Branch Quotation Cost" />
					</th>
				</tr>
			</thead>
			
			{foreach from=$data.bid_list item=bid}

				<tbody class="fs-08">
					<tr>
						<td>{$branch_list.$bid.code} - {$branch_list.$bid.description}</td>
						<td align="right">{$data.b_info.$bid.normal_cost|number_format:2}</td>
						<td align="right">
							<input  type="text" name="quotation_cost[{$bid}]" value="{$data.b_info.$bid.quotation_cost|number_format:$config.global_cost_decimal_points|ifzero:''}" style="text-align:right;width:80px;" class="quotation_cost form-control" onChange="SKU_QUOTATION_COST_DIALOG.quotation_cost_changed(this);" />
						</td>
					</tr>
				</tbody>

			{/foreach}
		</table>
	</div>
	
	<div style="text-align:center;" id="div_all_action_btn">
		<input type="button" class="btn btn-primary mt-2" value="Update" onClick="SKU_QUOTATION_COST_DIALOG.update_clicked();" id="btn_update" />
		<input type="button" class="btn btn-danger mt-2" value="Close" onClick="SKU_QUOTATION_COST_DIALOG.close();" />
	</div>
</form>