{*
1/30/2012 4:23:39 PM Alex
- created

2/6/2012 6:41:16 PM Alex
- replace Receipt No with No. of Transaction and can show receipt no.
- remove extra matches column, total qty, total price
- separate automatch or manually match with diff color
- change invalid code to barcode
- add separator: Front-End, Back-End

4/10/2012 5:22:56 PM Alex
- add type column

5/9/2013 1:40 PM Fithri
- bugfix - if got multiple same code is invalid, only replace 1 will cause the module to show all verified, but it is actually still got other un-verify

8/28/2013 2:48 PM Justin
- Enhanced the invalid SKU to list by index key instead of barcode in order to prevent special characters that causes javascript errors.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

2/3/2015 3:24 PM Andy
- Show GST info column.

12/19/2016 5:38 PM Andy
- Fixed a bug where special character will cause item cannot be verify.

4/20/2017 4:22 PM Khausalya
- Enhanced changes from RM to use config setting. 

10/15/2020 8:46 PM William
- Enhanced to add new tax checking.
*}
{if $invalid_items}
{if $sessioninfo.privilege.POS_VERIFY_SKU}
	{assign var=set_target_class value="set_target"}
{/if}

<form name="f_c" method="post" onsubmit="return check_form();">
	<input type='hidden' name='branch_id' value="{$smarty.request.branch_id}">
	<input type='hidden' name='date_select' value="{$smarty.request.date_select}">
	<input type='hidden' name='a' value="update_pos">
	{assign var=left_col value=7}
	{assign var=right_col value=8}
	{if $view_only}
		{assign var=left_col value=$left_col-1}
	{/if}
	{if $config.enable_gst || $config.enable_tax}
		{assign var=left_col value=$left_col+1}
		{assign var=right_col value=$right_col+1}
	{/if}

	<table class="report_table" id="report_tbl">
		<tr class="header">
			<th colspan="{$left_col}">Frontend</th>
			<th colspan="{$right_col}">Backend</th>
			<th>Others</th>
		</tr>
		<tr class="header">
			<!-- Frontend -->
			{if !$view_only}
				<th>&nbsp;</th>
			{/if}
			<th>No. of Transaction</th>
			<th>Barcode</th>
			<th>Description</th>
			<th>Price ({$config.arms_currency.symbol}) per unit</th>
			{if $config.enable_gst || $config.enable_tax}
				<th>Tax</th>
			{/if}
			<th>Type</th>
			<th>Approve by</th>
			
			<!-- Backend -->
			<th>&nbsp;</th>
			<th>Receipt Description</th>
			<th>ARMS Code</th>
			<th>Manufacture Code</th>
			<th>{if $config.link_code_name}{$config.link_code_name}{else}Link Code{/if}</th>
			<th>Price ({$config.arms_currency.symbol}) per unit</th>
			{if $config.enable_gst || $config.enable_tax}
				<th>Tax</th>
			{/if}
			<th>Verify by</th>
			<th>Verify Timestamp</th>
			
			<!-- Other -->
			<th>Serial List</th>
		</tr>	
		{foreach name=invalid_items_loop from=$invalid_items key=barcode item=pos_data}
			{foreach from=$pos_data key=selling_price item=otherinfo}
				{assign var=r value=$otherinfo.info}
				<!--{$row_id++}-->
				<tr class="hover  {if $r.verify_user}highlight{elseif $r.sku_item_id}auto{/if}" id="tr_row-{$row_id}">
					<!-- Frontend -->
					{assign var=barc value=$r.barcode}
					{assign var=round_selling value=$selling_price|default:0|number_format:2|ifzero:'-'}
					
					<input type="hidden" id="row_id_{$barc}_{$round_selling}" value="{$row_id}" />
					<!---------Just a temporarily data--------------->
					<input class="tmp_code" type="hidden" value="{$r.sku_item_id}">
					<input class="tmp_receipt_description" type="hidden" value="{$r.receipt_description|default:'-'}">
					<input class="tmp_sku_item_code" type="hidden" value="{$r.sku_item_code|default:'-'}">
					<input class="tmp_mcode" type="hidden" value="{$r.mcode|default:'-'}">
					<input class="tmp_link_code" type="hidden" value="{$r.link_code|default:'-'}">
					<input class="tmp_org_selling_price" type="hidden" value="{$r.org_selling_price|default:0|number_format:2|ifzero:'-'}">
					<input class="tmp_verify_user" type="hidden" value="{$r.verify_user|default:'-'}">
					<input class="tmp_has_partially_verified" type="hidden" value="{$r.has_partially_verified}">
					<input class="tmp_verify_timestamp" type="hidden" value="{$r.verify_timestamp|ifzero:'-'}">
					<input class="tmp_barcode" type="hidden" value="{$r.barcode}">
					<input class="tmp_ori_barcode" type="hidden" value="{$r.ori_barcode}">
					<!----------------------------------------------->
					{if !$view_only}
						<td>
							<input class="selected org_code code_{$row_id}" id="code_{$row_id}_{$round_selling}" name="code[{$row_id}][{$selling_price}]" type="checkbox" value="{$r.sku_item_id}" 
							barcode="{$barc}" onclick="change_background(this);check_selected_remaining('selected');" {if $r.verify_user} disabled {elseif $r.sku_item_id} checked {/if}
							row_id="{$row_id}">
							<input id="pos_items_id_{$row_id}_{$round_selling}" name="pos_items_id[{$row_id}][{$selling_price}]" type="checkbox" style="display:none;" value="{$otherinfo.id}" {if $r.sku_item_id} checked {/if}>
							<input type="hidden" name="new_sku_gst_info[{$row_id}][gst_id]" />
						</td>
					{/if}
					<td>
						<a onclick="toggle_transaction('{$row_id}','{$round_selling}')"><img src="/ui/expand.gif" id="expand_{$row_id}_{$round_selling}"> {$otherinfo.transactions_total}</a>
						<div id="toggle_{$row_id}_{$round_selling}" style="display:none;">
							<ul>
								{foreach from=$otherinfo.transactions_info item=ti}
									<li><a onclick="trans_detail('{$ti.counter_id}','{$smarty.request.date_select}','{$ti.pos_id}','{$smarty.request.branch_id}');" href="javascript:void(0)">Counter Name:{$ti.network_name} | Receipt No:{receipt_no_prefix_format branch_id=$smarty.request.branch_id counter_id=$ti.counter_id receipt_no=$ti.receipt_no}</a></li>
								{/foreach}
							</ul>
						</div>			
					</td>
					<td class="{$set_target_class} change_sid org_barcode">{$r.ori_barcode}</td>
					<td class="{$set_target_class} change_sid description">{$r.sku_description|default:'-'}</td>
					<td class="{$set_target_class} change_sid price_unit r">{$selling_price|default:0|number_format:2|ifzero:'-'}</td>
					
					{if $config.enable_gst || $config.enable_tax}
						<td class="{$set_target_class} change_sid">
							{if $otherinfo.gst_info.old} 
								{foreach from=$otherinfo.gst_info.old key=gst_key item=gst_info name=f_gst}
									<input type="hidden" name="gst_info[{$row_id}][old][gst_key][]" value="{$gst_key}" class="inp_old_gst_key-{$row_id}" />
									<input type="hidden" name="gst_info[{$row_id}][old][{$gst_key}][tax_indicator]" value="{$gst_info.tax_indicator}" />
									<input type="hidden" name="gst_info[{$row_id}][old][{$gst_key}][tax_rate]" value="{$gst_info.tax_rate}" />
									<input type="hidden" name="gst_info[{$row_id}][old][{$gst_key}][gst_code]" value="{$gst_info.tax_code}" />
									{if !$smarty.foreach.f_gst.first}, {/if}
									{$gst_info.tax_indicator}@{$gst_info.tax_rate}%
								{/foreach}
							{else}
								-
							{/if}
						</td>
					{/if}
					<td class="{$set_target_class} change_sid type">{$r.type|default:'-'}</td>
					<td class="{$set_target_class} change_sid">{$r.open_code_user|default:'-'}</td>
					
					<!-- Backend -->
					<td>
						{if $r.has_partially_verified}
							<img class="img_{$barc}" id="img_{$row_id}_{$round_selling}" width='16'	src="ui/approved_grey.png" title="Partially Verified">
							<span class="title_{$barc}" id="title_{$row_id}_{$round_selling}">Partially Verified</span>
						{elseif $r.verify_user}
							<img class="img_{$barc}" id="img_{$row_id}_{$round_selling}" width='16'	src="ui/approved.png" title="Verified">
							<span class="title_{$barc}" id="title_{$row_id}_{$round_selling}">Verified</span>
						{elseif $r.sku_item_id}
							<img class="img_{$barc}" id="img_{$row_id}_{$round_selling}" width='16' src="ui/icons/cog.png" title="Auto-match">
							<span class="title_{$barc}" id="title_{$row_id}_{$round_selling}">Auto-match</span>
						{else}
							<img class="img_{$barc}" id="img_{$row_id}_{$round_selling}" width='16'	src="ui/cancel.png" title="No-matched">
							<span class="title_{$barc}" id="title_{$row_id}_{$round_selling}">No-matched</span>
						{/if}
						 
						<input class="remaining match_{$barc}" style="display:none;" id="match_{$row_id}_{$round_selling}" type="checkbox" value="1" {if $r.sku_item_id}checked {/if}>
					</td>
					<td class="{$set_target_class} change_sid receipt_{$barc}" id="receipt_{$row_id}_{$round_selling}">
						{$r.receipt_description|default:'-'}
					</td>
					<td class="{$set_target_class} change_sid sku_item_code_{$barc}" id="sku_item_code_{$row_id}_{$round_selling}">
						{$r.sku_item_code|default:'-'}
					</td>
					<td class="{$set_target_class} change_sid mcode_{$barc}" id="mcode_{$row_id}_{$round_selling}">
						{$r.mcode|default:'-'}
					</td>
					<td class="{$set_target_class} change_sid linkcode_{$barc}" id="linkcode_{$row_id}_{$round_selling}">
						{$r.link_code|default:'-'}
					</td>
					<td class="{$set_target_class} change_sid r selling_{$barc}" id="selling_{$row_id}_{$round_selling}">
						{$r.org_selling_price|default:0|number_format:2|ifzero:'-'}
					</td>
					
					{if $config.enable_gst || $config.enable_tax}
						<td>
							<span id="span_sku_new_gst_info-{$row_id}">
								{if $otherinfo.gst_info.new} 
									{foreach from=$otherinfo.gst_info.new item=gst_info name=f_gst}
										{if !$smarty.foreach.f_gst.first}, {/if}
										{$gst_info.tax_indicator}@{$gst_info.tax_rate}%
									{/foreach}
								{else}
									-
								{/if}
							</span>
							<span style="display:none;" id="span_sku_revert_gst_info-{$row_id}"></span>
						</td>
					{/if}
					
					<td class="{$set_target_class} change_sid org_verify_user verify_user_{$barc}" id="verify_user_{$row_id}_{$round_selling}">{$r.verify_user|default:'-'}</td>
					<td class="{$set_target_class} change_sid org_verify_timestamp verify_timestamp_{$barc}" id="verify_timestamp_{$row_id}_{$round_selling}">{$r.verify_timestamp|ifzero:'-'}</td>
					
					<!-- Other -->
					
					<!-- serial number -->
					<td>
						{if $otherinfo.serial_list}
							<ul style="padding:0;margin:0;">
								{foreach from=$otherinfo.serial_list item=serial_data}
									<li> 
										{if $r.verify_user and $r.sku_item_id and !$view_only and !$serial_data.pos_items_sn}
											
											<img src="/ui/icons/page_go.png" align="absmiddle" title="Generate Serial Number for this SKU" class="clickable" onClick="generate_serial('{$r.sku_item_id}','{$row_id}', '{$round_selling}', '{$serial_data.serial_no}');" id="img_generate_serial-{$row_id}-{$round_selling}-{$serial_data.serial_no}" />
										{/if}
										{$serial_data.serial_no}
										<span class="span_serial_added" id="span_serial_added-{$row_id}-{$round_selling}-{$serial_data.serial_no}" style="{if !$serial_data.pos_items_sn}display:none;{/if}">
											(Added)
										</span>
									</li>
								{/foreach}
							</ul>
						{else}
							-
						{/if}
					</td>
				</tr>
			{/foreach}
		{/foreach}
		<tr class="header">
			<th colspan="{$left_col}" class="r"><span id="no_selected_id">0</span> code(s) selected.</th>
			<th colspan="{$right_col}" class="r"><span id="no_remaining_id">0</span> code(s) remaining.</th>
		</tr>
	</table>

	{if !$view_only}
		<br /><p><input type="submit" style="font:bold 20px Arial;background-color:#f90;color:#fff;" value="Update POS"></p>
	{/if}
</form>
{else}
	{if $table}	<center><b>No invalid SKU to be verify.</b></center>	{/if}
{/if}
