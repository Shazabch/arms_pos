
{if $linked_sku_info}
	<table class="report_table">
		<tr class="header">
			<th>ARMS Code</th>
			<th>MCode</th>
			<th>Art No</th>
			<th>{$config.link_code_name}</th>
			<th>Description</th>
			<th>Selling Price</th>
		</tr>
		<tr>
			<td>{$linked_sku_info.sku_item_code}</td>
			<td>{$linked_sku_info.mcode|default:'-'}</td>
			<td>{$linked_sku_info.artno|default:'-'}</td>
			<td>{$linked_sku_info.link_code|default:'-'}</td>
			<td>{$linked_sku_info.description|default:'-'}</td>
			
			<td>
				<table class="tb" cellspacing="0" cellpadding="4" id="tbl_selling_by_branch">
					{foreach from=$branches_list key=bid item=b}
						<tr id="tr_selling_by_branch-{$bid}" class="tr_selling_by_branch" style="{if !$form.allowed_branches.$bid}display:none;{/if}" >
							<td>
								{$b.code} 
							</td>
							<td>
								{$linked_sku_info.selling_by_branch.$bid|number_format:2}<br />
							</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
	</table>
{/if}