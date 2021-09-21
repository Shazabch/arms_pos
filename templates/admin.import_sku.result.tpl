{*
7/11/2014 10:50 AM Justin
- Enhanced to have 5 category levels.

10/9/2014 2:47 PM Justin
- Enhanced to have checking on UOM (if found got check by insert parent & child and first item UOM not "EACH", then the rest will capture as error).
- Enhanced to show UOM errors message.

12/16/2015 2:20 PM DingRen
- add new column sku type

04/26/2016 14:30 Edwin
- Enhanced on add or update PO max and min qty if filled
- Added parent_arms_code, parent_mcode and parent_artno to check and assign parent-child sku_items

07/27/2016 11:00 Edwin
- Change coding structure

9/17/2020 3:11 PM William
- Added nl2br for additional description column.
*}
<h3> Result Status:</h3>
<p>
	{if $result.import_row}
		Total {$result.import_row} of {$result.ttl_row} item(s) will be imported.<br />
	{/if}
	{if $result.error_row}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to import due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to import.<br />
	<br />
	<input type="button" class="btn btn-primary" id="import_btn" name="import_btn" value="Import" onclick="IMPORT_SKU.import_sku({$method});" {if !$result.import_row}disabled{/if} />
</p>
<div class="div_tbl">
	<div class="table-responsive">
		<table id="si_tbl" class="report_table table mb-0 text-md-nowrap  table-hover">
			<thead class="bg-gray-100">
				<tr>
					<th>#</th>
					{foreach from=$item_header item=i}
						<th>{$i}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
			{foreach from=$item_lists item=i name=sku}
				<tr class="{if $i.error}tr_error{/if}" title="{if $i.error}{$i.error}{/if}">
					<td>{$smarty.foreach.sku.iteration}.</td>
					{foreach from=$i key=k item=r}
						<td {if in_array($k, $align_right)}align="right"{elseif in_array($k, $align_center)}align="center"{elseif $k == 'error'}nowrap{/if}>{$r|nl2br}</td>
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>