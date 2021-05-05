{*
1/21/2020 2:48 PM William
- Enhanced to show column sku photo and weight.

3/2/2020 11:47 AM William
- Enhanced to change column "Additional Description" to "Marketplace Description".
- Enhanced to view all sku items image.

3/5/2020 11:07 AM Andy
- Removed "Internal Description" and "Model".

3/10/2020 3:56 PM Andy
- Added remark to tell users they need to edit sku information in sku masterfile.
*}
{capture assign=html_mandatory_missing}
<div class="div_mandatory_missing">
	<img src="ui/messages.gif" title="Missing Mandatory Value" />
</div>
{/capture}

<div>
	<ul>
		<li>Please Edit SKU information in SKU Masterfile.</li>
	</ul>
</div>

<div style="float:right;">
Found total of {$form.si_count|number_format} SKU Item(s)
</div>
<b>Active</b>&nbsp;
<select id="status" onchange="MKTPLACE_MANAGE_SKU.ajax_reload_sku_items();">
	{foreach from=$status_list key=status item=status_desc}
		<option value="{$status}" {if $form.status eq $status}selected{/if}>{$status_desc}</option>
	{/foreach}
</select>&nbsp;&nbsp;
<input type="button" id="refresh_btn" value="Refresh" onclick="MKTPLACE_MANAGE_SKU.ajax_reload_sku_items();" />&nbsp;&nbsp;
<input type="button" id="activate_si_btn" value="Activate Selected SKU Item(s)" onclick="MKTPLACE_MANAGE_SKU.active_clicked(-1, 1);" />&nbsp;&nbsp;
<input type="button" id="deactivate_si_btn" value="Deactivate Selected SKU Item(s)" onclick="MKTPLACE_MANAGE_SKU.active_clicked(-1, 0);" />
<br /><br />
{if $err_msg}
<font color="red">
	{foreach from=$err_msg item=e}
		<p style="margin:0px;">* {$e}</p>
	{/foreach}
</font>
{/if}
{if $result}
	{if $result.ttl_mpsi_added > 0}
		<font color="blue">* Total of {$result.ttl_mpsi_added} SKU item(s) has been added into Marketplace item list.</font><br />
		<a href="marketplace.home.php?a=goto_marketplace" target="_blank">Please continue to configure in marketplace.</a><br />
	{/if}
	{if $result.existed_si_list}
		<font color="red">
			* Total of {$result.existed_si_list|@count} SKU item(s) is existed from Marketplace item list:<br />
			&nbsp;&nbsp;
			{foreach from=$result.existed_si_list name=si key=sid item=si}
				{$si.sku_item_code}
				{if !$smarty.foreach.si.last}, {/if}
			{/foreach}
		</font>
		<br />
	{/if}
	<br />
{/if}

<table width="100%" class="report_table" id="tbl_si_list">
	<tr class="header">
		<th><input type="checkbox" name="chk_all_si" onclick="MKTPLACE_MANAGE_SKU.check_all_si(this);" value="1" /> </th>
		<th class="sortable_col">No.</th>
		<th class="sortable_col">Photo</th>
		<th class="sortable_col">SKU Item Code</th>
		<th class="sortable_col">Art No.</th>
		<th class="sortable_col">MCode</th>
		<th class="sortable_col">Link Code</th>
		<th class="sortable_col" width="20%">Description</th>
		<th class="sortable_col" width="20%">Marketplace<br />Description</th>
		<th class="sortable_col">Color</th>
		<th class="sortable_col">Size</th>
		<th class="sortable_col">Length</th>
		<th class="sortable_col">Height</th>
		<th class="sortable_col">Width</th>
		<th class="sortable_col">Weight (KG)</th>
		<th class="sortable_col">Last Update</th>
		<th class="sortable_col">Added</th>
	</tr>
	{foreach from=$form.si_list key=sid name=si item=si}
		<tr id="tr_item-{$si.sku_item_id}" class="tr_item">
			<td nowrap>
				<input type="checkbox" name="chk_si_list[{$si.sku_item_id}]" class="si_checkbox" onclick="MKTPLACE_MANAGE_SKU.si_checkbox_clicked({$si.sku_item_id});" value="1" />
				{if $si.is_mpsi_active}
					<a href="javascript:void(MKTPLACE_MANAGE_SKU.active_clicked({$si.sku_item_id}, 0));"><img src="ui/deact.png" title="Deactivate this SKU Item" border="0"></a>
				{else}
					<a href="javascript:void(MKTPLACE_MANAGE_SKU.active_clicked({$si.sku_item_id}, 1));"><img src="ui/act.png" title="Activate this SKU Item" border="0"></a>
					<br /><span class="small">(inactive)</span>
				{/if}
			</td>
			<td nowrap>
				{$smarty.foreach.si.iteration}.
			</td>
			<td>
				{show_sku_photo sku_item_id=$sid container_id="sku_photo_`$sid`" show_as_first_image=1}
			</td>
			<td>{$si.sku_item_code}</td>
			<td>{$si.artno}</td>
			<td>{$si.mcode}</td>
			<td>{$si.link_code}</td>
			<td>{$si.description|escape|nl2br}</td>
			<td><div class="div_marketplace_desc">{$si.marketplace_description|escape|nl2br|default:$html_mandatory_missing}</div></td>
			<td align="center">{$si.color|default:$html_mandatory_missing}</td>
			<td align="center">{$si.size|default:$html_mandatory_missing}</td>
			<td align="center">{$si.length|default:0|ifzero:$html_mandatory_missing}</td>
			<td align="center">{$si.height|default:0|ifzero:$html_mandatory_missing}</td>
			<td align="center">{$si.width|default:0|ifzero:$html_mandatory_missing}</td>
			<td align="center">{$si.weight_kg|default:0|ifzero:$html_mandatory_missing}</td>
			<td align="center">{$si.mpsi_last_update}</td>
			<td align="center">{$si.mpsi_added}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="17" align="center">-- No SKU Item were added --</td>
		</tr>
	{/foreach}
</table>