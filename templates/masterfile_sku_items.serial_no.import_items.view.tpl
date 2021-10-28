<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sn_tbl"  width="99%" class=" table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr >
						<th width="10%">ARMS Code</th>
						<th width="60%">Description</th>
						<th width="10%">Serial No</th>
						<th width="20%">Remark</th>
					</tr>
				</thead>
				<tbody class="fs-08" {if count($items)>15} style="width:650;height:250;overflow-y:auto;overflow-x:hidden;"{/if}>
				{foreach name=i from=$items item=item key=iid}
					<!-- {$n++} -->
					<tr bgcolor="{cycle name=r1 values="#eeeeee,"}" class="no_border_bottom" height="25">
						{if $prev_sku_item_id eq $item.sku_item_id}
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						{else}
							<td align="center">{$item.sku_item_code|default:"&nbsp;"}</td>
							<td>{$item.sku_description|default:"&nbsp;"}</td>
						{/if}
						<td>{$item.sn}</td>
						<td>{$item.remark|default:"&nbsp;"}</td>
					</tr>
					{assign var=prev_sku_item_id value=$item.sku_item_id}
				{/foreach}
				{if count($items) eq 0}
					<tr>
						<td align="center" colspan="4" height="25">No data</td>
					</tr>
				{/if}
				</tbody>
				</table>
		</div>
	</div>
</div>