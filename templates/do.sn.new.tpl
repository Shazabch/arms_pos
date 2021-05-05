{*
6/23/2014 5:21 PM Justin
- Enhanced to have new feature that can add Serial No by range.

7/9/2014 11:33 AM Justin
- Enhanced to have new feature that can change customer info from S/N Details at once from new menu.
- Enhanced to have new ability that can skip S/N data for DO Transfer by branch(need config).

2/16/2017 9:46 AM Zhi Kai
-Change wording of 'NRIC' to 'NRIC/BRN'

05/07/2020 5:19 PM Sheila
-Fixed serial no container and modal alignment

*}
{if $show_mst_sn_menu}
	<fieldset class="stdframe" style="width:99%;">
		<legend><b><font size="2">Customer Information</font></b></legend>
		<p style="color:blue; font-weight:bold;">
			* Change below info will replace all customer info from Serial No.
		</p>
		<table id="sn_info_table">
			<tr>
				<td><b>NRIC/BRN</b></td>
				<td><input type="text" name="mst_sn_nric" value="{$item.serial_no.nric.0}" onchange="sn_mst_info_changed(this);"></td>
			</tr>
			<tr>
				<td><b>Name</b></td>
				<td><input type="text" name="mst_sn_name" value="{$item.serial_no.name.0}" size="30" onchange="sn_mst_info_changed(this);"></td>
			</tr>
			<tr>
				<td><b>Address</b></td>
				<td><input type="text" name="mst_sn_address" value="{$item.serial_no.address.0}" size="50" onchange="sn_mst_info_changed(this);"></td>
			</tr>
			<tr>
				<td><b>Contact No</b></td>
				<td><input type="text" name="mst_sn_cn" value="{$item.serial_no.cn.0}" onchange="sn_mst_info_changed(this);"></td>
			</tr>
			<tr>
				<td><b>Email</b></td>
				<td><input type="text" name="mst_sn_email" value="{$item.serial_no.email.0}" onchange="sn_mst_info_changed(this);"></td>
			</tr>
		</table>
	</fieldset>
{elseif $is_add_sn_by_range}
	<table>
		<tr>
			<td colspan="2" style="color:blue; font-weight:bold;">{$si_info.sku_item_code} - {$si_info.description}</td>
		</tr>
		<tr>
			<td><b>Branch</b></td>
			<td>
				{if !$deliver_branch}
					{$branch_code}
					<input type="hidden" id="sn_range_bid" value="{$branch_id}" />
				{else}
					{assign var=curr_bcode value=$branch_id|get_branch_code}
					<select id="sn_range_bid">
					{foreach from=$deliver_branch key=bid item=r}
						{assign var=to_branch_code value=$r.code}
						{if !$config.do_transfer_skip_sn.branch.$curr_bcode.$to_branch_code}
							<option value="{$bid}">{$to_branch_code}</option>
						{/if}
					{/foreach}
					</select>
				{/if}
			</td>
		</tr>
		<tr>
			<td><b>From</b></td>
			<td><input type="text" id="sn_range_from" size="15" onchange="mi(this);" /></td>
		</tr>
		<tr>
			<td><b>To</b></td>
			<td><input type="text" id="sn_range_to" size="15" onchange="mi(this);" /></td>
		</tr>
		<tr align="center btn_padding">
			<td colspan="2">
				<input type="button" value="Add" onclick="add_sn_by_range(this);" />
				<input type="button" value="Back" onclick="default_curtain_clicked();" />
				<input type="hidden" id="sn_range_item_id" value="{$item_id}" />
			</td>
		</tr>
	</table>
{elseif $is_setup_only}
<table style="display:none;">
	<tbody id="temp_sn_row" class="temp_sn_row">
		<tr id="sn_item_row___sn__id___sn__row">
			<td><input type="text" name="sn[__sn__id][__sn__row]"></td>
				<td><input type="text" name="sn_nric[__sn__id][__sn__row]" class="sn_nric" onchange="cron_val(this);"></td>
				<td><input type="text" name="sn_name[__sn__id][__sn__row]" class="sn_name" onchange="cron_val(this);"></td>
				<td><input type="text" name="sn_address[__sn__id][__sn__row]" class="sn_address" onchange="cron_val(this);"></td>
				<td><input type="text" name="sn_cn[__sn__id][__sn__row]" class="sn_cn" onchange="cron_val(this);"></td>
				<td><input type="text" name="sn_email[__sn__id][__sn__row]" class="sn_email" onchange="cron_val(this);"></td>
			<td nowrap>
				<input type="text" name="sn_we[__sn__id][__sn__row]" class="r" value="__sn__we" size="10" onchange="this.value=round(this.value,0);">
				<select name="sn_we_type[__sn__id][__sn__row]">
					<option value="day" {if $item.sn_we_type eq 'day'}selected{/if}>Day(s)</option>
					<option value="week" {if $item.sn_we_type eq 'week'}selected{/if}>Week(s)</option>
					<option value="month" {if $item.sn_we_type eq 'month' || !$item.sn_we_type}selected{/if}>Month(s)</option>
					<option value="year" {if $item.sn_we_type eq 'year'}selected{/if}>Year(s)</option>
				</select>
			</td>
		</tr>
	</tbody>
</table>

<div id="div_sn_by_range_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:300px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_sn_by_range_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Add Serial No by Range</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sn_by_range_popup_content" style="padding:2px;"></div>
</div>
{else}
<div id="sn_item{$item.id}">
<br />
<fieldset class="stdframe" style="width:99%;">
	<span class="serial_no"><b><font size="2">{$item.sku_item_code}</font></b></span>
	<div class="div_sel">
	<table width="100%">
		{if count($errm.sn) > 0}
			{assign var=iid value=$item.id}
			<div id="err"><div class="errmsg"><ul>
			{foreach from=$errm.sn.$iid item=bid_list key=id}
				{if $smarty.request.do_type eq 'transfer'}
					{foreach from=$bid_list item=row_list key=bid}
						{foreach from=$row_list item=errm key=row}
							<li valign="bottom"> {$errm}
						{/foreach}
					{/foreach}
				{else}
					<li valign="bottom"> {$bid_list}
				{/if}
			{/foreach}
			</ul></div></div>
		{/if}
		{if !$readonly && !$form.approval_screen}
			<tr>
				<td>
					<input type="button" name="sn_by_range_btn" value="Add S/N by Range" onclick="add_sn_by_range_clicked('{$item.id}', '{$item.sku_item_id}');" />
				</td>
			</tr>
		{/if}
		<tr valign="top">
			<td align="left" nowrap>
				<b>Description:</b><br />{$item.description}<br /><br />
				<b>Rcv Qty (Pcs):</b> 
				<span id="bal_qty_{$item.id}">
				{if $form.deliver_branch}
					{section name=i loop=$branch}
						{if in_array($branch[i].id,$form.deliver_branch)}
							{assign var=bid value=$branch[i].id}
							{assign var=qty value=$qty+$item.ctn_allocation.$bid*$item.uom_fraction+$item.pcs_allocation.$bid}
						{/if}
					{/section}
				{else}
					{assign var=qty value=$qty+$item.ctn*$item.uom_fraction+$item.pcs}
				{/if}

				{$qty|default:0}
				{if $item.ttl_sn != 0 && $smarty.request.do_type eq 'transfer'}
					{assign var=bal_qty value=$qty-$item.ttl_sn}
					{if $bal_qty < 0}
						<b><font color="#ff0000">(Over {$bal_qty|abs} S/N)</font></b>
					{elseif $bal_qty > 0}
						({$bal_qty} qty remaining)
					{/if}
				{/if}
				</span>
				<input type="hidden" name="sn_item_id[{$item.id}]" value="{$item.id}">
				<input type="hidden" name="sn_sku_item_id[{$item.id}]" value="{$item.sku_item_id}">
				<input type="hidden" name="sn_rcv_qty[{$item.id}]" value="{$qty|default:0}">
				<input type="hidden" name="ttl_sn[{$item.id}]" value="{$item.ttl_sn}">
				<input type="hidden" name="si_sn_we[{$item.id}]" value="{$item.sn_we}">
				<input type="hidden" name="si_sn_we_type[{$item.id}]" value="{$item.sn_we_type}">
			</td>
			{assign var=curr_bcode value=$form.branch_id|get_branch_code}
			{if $do_type eq 'transfer'}
				{if $form.deliver_branch}
					{section name=i loop=$branch}
						{assign var=to_branch_code value=$branch[i].code}
						{if in_array($branch[i].id,$form.deliver_branch) && !$config.do_transfer_skip_sn.branch.$curr_bcode.$to_branch_code}
						<td width="30" class="sn_data">
							{assign var=branch_id value=$branch[i].id}
							<b>{$branch[i].code}</b> 
							<span id="sn_branch_label_{$item.id}_{$branch_id}">
								{assign var=qty value=$item.ctn_allocation.$branch_id*$item.uom_fraction+$item.pcs_allocation.$branch_id}
								{if $qty > $item.serial_no_count.$branch_id}
									({$qty-$item.serial_no_count.$branch_id} qty remaining)
								{elseif $qty < $item.serial_no_count.$branch_id}
									<b><font color="#ff0000">(Over {$item.serial_no_count.$branch_id-$qty} S/N)</font></b>
								{/if}
							</span><br />
							<textarea name="sn[{$item.id}][{$branch_id}]" cols="20" rows="15" onchange="recalc_sn_used('{$item.id}', '{$branch_id}');">{$item.serial_no.$branch_id}</textarea>
							<input type="hidden" name="b_sn_rcv_qty[{$item.id}][{$branch_id}]" value="{$qty}">
						</td>
						{/if}
					{/section}
				{else}
					{assign var=branch_id value=$form.do_branch_id|default:0}
					{assign var=to_branch_code value=$branch_id|get_branch_code}
					{if !$config.do_transfer_skip_sn.branch.$curr_bcode.$to_branch_code}
						<td width="30" class="sn_data">
							<textarea name="sn[{$item.id}][{$branch_id}]" cols="20" rows="15" onchange="recalc_sn_used('{$item.id}', '{$branch_id}');">{$item.serial_no.$branch_id}</textarea>
							<input type="hidden" name="b_sn_rcv_qty[{$item.id}][{$branch_id}]" value="{$qty}">
						</td>
					{/if}
				{/if}
			{else}
				<td>
					<table id="sn_item_table_{$item.id}" class="sn_data">
						<tr style="background-color:#ffeeaa">
							<th>Serial No</th>
							<th>NRIC/BRN</th>
							<th>Name</th>
							<th>Address</th>
							<th>Contact No</th>
							<th>Email</th>
							<th>Warranty Expired</th>
						</tr>
						<tbody id="sn_list_{$item.id}">
							{section name=q loop=$qty}
								{assign var=row value=$smarty.section.q.iteration-1}
								<tr id="sn_item_row_{$item.id}_{$row}">
									<td><input type="text" name="sn[{$item.id}][{$row}]" value="{$item.serial_no.sn.$row}"></td>
									<td><input type="text" name="sn_nric[{$item.id}][{$row}]" class="sn_nric" value="{$item.serial_no.nric.$row}" onchange="cron_val(this);"></td>
									<td><input type="text" name="sn_name[{$item.id}][{$row}]" class="sn_name" value="{$item.serial_no.name.$row}" onchange="cron_val(this);"></td>
									<td><input type="text" name="sn_address[{$item.id}][{$row}]" class="sn_address" value="{$item.serial_no.address.$row}" onchange="cron_val(this);"></td>
									<td><input type="text" name="sn_cn[{$item.id}][{$row}]" class="sn_cn" value="{$item.serial_no.cn.$row}" onchange="cron_val(this);"></td>
									<td><input type="text" name="sn_email[{$item.id}][{$row}]" class="sn_email" value="{$item.serial_no.email.$row}" onchange="cron_val(this);"></td>
									<td nowrap>
										<input type="text" name="sn_we[{$item.id}][{$row}]" size="10" class="r" onchange="this.value=round(this.value,0);" value="{$item.serial_no.we.$row}">
										<select name="sn_we_type[{$item.id}][{$row}]">
											<option value="day" {if $item.serial_no.we_type.$row eq 'day'}selected{/if}>Day(s)</option>
											<option value="week" {if $item.serial_no.we_type.$row eq 'week'}selected{/if}>Week(s)</option>
											<option value="month" {if $item.serial_no.we_type.$row eq 'month' || !$item.serial_no.we_type.$row}selected{/if}>Month(s)</option>
											<option value="year" {if $item.serial_no.we_type.$row eq 'year'}selected{/if}>Year(s)</option>
										</select>
									</td>
								</tr>
							{/section}
						</tbody>
					</table>
				</td>
			{/if}
		</tr>
	</table>
	</div>
</fieldset>
</div>
{/if}
