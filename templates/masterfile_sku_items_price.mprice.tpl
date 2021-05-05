{*
4/4/2011 2:47:45 PM Justin
- modified the status message to show depend on ajax.
- added progress loading area for user to know the progress of updating price.

5/19/2011 11:29:39 AM Andy
- Show change price by HQ and region when found config "sku_use_region_price".
- Add region price type and selling price, included mprice.

9/15/2011 11:42:38 AM Andy
- Show mouse over branch description.
- Add checkbox for FOC item, user must checked checkbox instead of leaving zero for FOC item.

10/25/2011 2:29:47 PM Andy
- Add checking to only show FOC checkbox if the SKU got allow FOC in masterfile.
- Move HQ to always at first column.
- Prompt user to confirm whether want to replace HQ selling price/price type to all branches.

3/27/2012 4:11:39 PM Andy
- Add can click on region title to change price by branch in region.

7/24/2012 5:54:12 PM Justin
- Added new variable for JS to process change price by row by column.

8/15/2012 11:29 AM Andy
- Enhance to can copy selling price cross sku based on same UOM, need config sku_change_price_always_apply_to_same_uom.

8/15/2012 2:48 PM Andy
- Change to copy selling price based on uom fraction instrad of uom id.

8/16/2012 3:22 PM Andy
- Add need same artno only can copy price.

10/8/2012 1:58:00 PM Fithri
- SKU change price block bom type "package"

1/23/2013 5:25 PM Justin
- Enhanced to auto add additional selling price if found any.

2/1/2013 11:55 AM Fithri
- add checkbox to enable update price
- tick to enable change price
- have a checkbox to tick whole row or column
- click into region change price, show a region price and its related control
- if change region price, all branches in region price will change

3/15/2013 10:15 AM Fithri
- fix the column messed up & all merge together if too many brnach
- fix "check all" checkbox if dont have member type

3/19/2013 1:55 PM Fithri
- add one checkbox to toggle update all price

12/12/2013 11:12 AM Fithri
- when view at branch, if mprice is not set, will show selling price instead of zero (0)

12/16/2013 1:43 PM Fithri
- fix bug mprice showing wrong value

7/16/2014 1:18 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

7/17/2014 5:41 PM Justin
- Enhanced to have GP value calculation.

9/25/2014 2:39 PM Justin
- Enhanced to have GST information.

3/11/2015 5:37 PM Andy
- Enhanced to store the checkbox FOC value into database.
- Change to not auto zerolise selling price when user tick FOC checkbox.

08-Mar-2016 16:25 Edwin
- Enhanced to show selling price label(normal, GST, before/after GST) when login at branch

10/28/2016 16:25 Qiu Ying
- Enhanced the backend Mprice module to show both 1) Member's type 2) Member1, Member2, Member 3(remain)

11/8/2016 9:35 AM Qiu Ying
- Bug fixed on MPrice name should show one times when membership_type same name as sku_multiple selling_price

6/5/2017 5:06 PM Justin
- Enhanced to have cost information (need view cost privilege).

12/4/2019 2:24 PM William
- Added checking for Change Selling Price "Selling Price" when change branch and update, system will show alert warning. 

4/28/2020 5:36 PM Andy
- Modified layout to compatible with new UI.

06/26/2020 Sheila 11:34 AM
- Updated button css.

11/13/2020 4:15 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
*}

<form name=f_p method=post onsubmit="return false;">
<input type=hidden name=a value="change_price">
<input type=hidden name=sku_item_id value="{$items[0].id}">
<input name="form_branch_id" value="{$sessioninfo.branch_id}" type="hidden">
<table border="0" cellspacing="1" cellpadding="2" width="100%">

{assign var=col_select value=1}
{assign var=first_header value=1}
{section loop=$items name=i}
	<!-- Header -->
	<tr height="24" bgcolor="#ffee99">
		<th>ARMS Code</th>
		
		{if $BRANCH_CODE eq 'HQ'}
		<th>Description</th>
		<th align="right"><input type="checkbox" onclick="toggle_check_all_col_row_item('{$items[i].id}',this.checked);" class="cb_check_all" /></th>
		{else}
		<th colspan="2">Description</th>
		{/if}
		
		{if $BRANCH_CODE eq 'HQ'}
			{if !$show_by_region_code}
				<!-- HQ -->
				<th title="HQ - {$branch.1.description}"><input type="checkbox" onchange="toggle_check_all_column(this.checked,{$items[i].id},'1');" class="cb_all_col cb_all_col_{$items[i].id}" />HQ&nbsp;&nbsp;&nbsp;&nbsp;</th>
			{/if}
			
			{if $config.sku_use_region_price and $config.masterfile_branch_region and !$show_by_region_code}
				<!-- Change Price by Region -->
				
				<!-- Region -->
				{foreach from=$config.masterfile_branch_region key=region_code item=rg}
					<th class="region_header">
						<input type="checkbox" onchange="toggle_check_all_column(this.checked,{$items[i].id},'{$region_code}');" class="cb_all_col cb_all_col_{$items[i].id}" />
						<a href="javascript:void(change_price_by_region('{$region_code}'))">
							{$rg.name}
						</a>
					</th>
				{/foreach}
				
				<!-- Those branch have no region -->
				{foreach from=$region_branch.no_region item=b}
					{if $b.code ne 'HQ'}
						<th title="{$b.code} - {$b.description}"><input type="checkbox" onchange="toggle_check_all_column(this.checked,{$items[i].id},'{$b.id}');" class="cb_all_col cb_all_col_{$items[i].id}" />{$b.code}</th>
					{/if}
				{/foreach}
			{else}
				<!-- Normal Change Price Method: by branch -->
				{if $smarty.request.show_by_region_code}
				<th class="region_header">
					<input type="checkbox" onchange="toggle_check_all_column(this.checked,{$items[i].id},'{$smarty.request.show_by_region_code}');" class="cb_all_col cb_all_col_{$items[i].id}" />{$smarty.request.show_by_region_code}
				</th>
				{/if}
				{foreach from=$branch item=b}
					{if $b.code ne 'HQ'}
						<th title="{$b.code} - {$b.description}"><input type="checkbox" onchange="toggle_check_all_column(this.checked,{$items[i].id},'{$b.id}');" class="cb_all_col cb_all_col_{$items[i].id}" />{$b.code}</th>
					{/if}
				{/foreach}
			{/if}
		{else}
		
			{if $first_header}
			<th>Price Type</th>
			<th align="right"><input type="checkbox" onclick="toggle_check_all_col_row(this.checked);" class="cb_check_all" /></th>
			{else}
			<th colspan="2">Price Type</th>
			{/if}
			{assign var=first_header value=0}
			
			<th>
			{if $col_select}<input type="checkbox" onchange="toggle_check_all_column2(this.checked,'normal');" class="cb_all_col2 cb_all_col2_{$items[i].id}" />{/if}
			Selling
			</th>
			{if $config.sku_multiple_selling_price}
				{foreach from=$config.sku_multiple_selling_price item=s}
					<th>
					{if $col_select}<input type="checkbox" onchange="toggle_check_all_column2(this.checked,'{$s}');" class="cb_all_col2 cb_all_col2_{$items[i].id}" />{/if}
					{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}
					</th>
				{/foreach}
			{/if}
		{/if}
	</tr>
	
	<!-- Items -->
	<tr {if !$items[i].active}class="inactive"{/if} class="tr_mprice_row tr_mprice_row_by_uom_id-{$items[i].packing_uom_id} tr_mprice_row_by_uom_fraction-{$items[i].packing_uom_fraction}" id="tr_mprice_row-{$items[i].id}">
		<td valign="top" nowrap>{$items[i].sku_item_code}<br>
			{if $items[i].mcode}<span><b>MCode:</b> {$items[i].mcode}</span><br>{/if}
			{if $items[i].artno}<span><b>Art No:</b> {$items[i].artno}</span>{/if}
			
			{* RSP *}
			{if $items[i].use_rsp}
				<br /><br />
				<fieldset>
					<legend>Using RSP Control</legend>
					<table>
						<tr>
							<td><b>Master RSP: </b></td>
							<td>
								{$items[i].rsp_price|number_format:2}
							</td>
						</tr>
						
						<tr>
							<td><b>Master RSP Discount: </b></td>
							<td>{$items[i].rsp_discount}</td>
						</tr>
						
					</table>
					
					
				</fieldset>
			{/if}
			<input type="hidden" name="code[{$items[i].id}]" value="{$items[i].sku_item_code}" />
			<input type="hidden" id="inp_packing_uom_fraction-{$items[i].id}" value="{$items[i].packing_uom_fraction}" />
			<input type="hidden" id="inp_packing_uom_id-{$items[i].id}" value="{$items[i].packing_uom_id}" />
			<input type="hidden" id="inp_artno-{$items[i].id}" value="{$items[i].artno}" />
			<input type="hidden" name="inclusive_tax[{$items[i].id}]" value="{$items[i].inclusive_tax}" />
			<input type="hidden" name="gst_rate[{$items[i].id}]" value="{$items[i].gst_rate}" />
			<input type="hidden" name="use_rsp[{$items[i].id}]" value="{$items[i].use_rsp}" />
			<input type="hidden" name="rsp_price[{$items[i].id}]" value="{$items[i].rsp_price}" />
		</td>
		<td valign="top">{$items[i].description}</td>
		<td valign="top">
			{if $trade_discount_type!=0 or $config.sku_always_show_trade_discount}
			<div style="height:30;width:20;" class="showborder">
				&nbsp;
			</div>
			{/if}
			{if $BRANCH_CODE eq 'HQ'}
				<div style="position:relative;height:100;" class="showborder">
					<div style="position:absolute;bottom:0;right:0;">
					<input type="checkbox" onchange="toggle_check_all_row(this.checked,'normal',{$items[i].id});" class="cb_all_row cb_all_row_{$items[i].id}" />
					</div>
				</div>
				{if $sessioninfo.privilege.SHOW_COST}
					<div style="height:160;">&nbsp;</div>
				{/if}
				<br />
				{if $config.sku_multiple_selling_price}
					{foreach from=$config.sku_multiple_selling_price item=s}
						<div style="position:relative;height:80;" class="showborder">
							<div style="position:absolute;/*bottom:0;*/right:0;">
								<input type="checkbox" onchange="toggle_check_all_row(this.checked,'{$s}',{$items[i].id});" class="cb_all_row cb_all_row_{$items[i].id}" />
							</div>
						</div>
						{if $sessioninfo.privilege.SHOW_COST}
							<div style="height:160;">&nbsp;</div>
						{/if}
						<br />
					{/foreach}
				{/if}
			{/if}
		</td>
		{if $items[i].is_bom_package}
		<td align="left" colspan="{count var=$branch}"><span style="color:red">** EDIT PRICE IS NOT ALLOWED FOR SKU BOM TYPE 'PACKAGE'</span></td>
		{else}
			{if $items[i].active}
				{if $BRANCH_CODE eq 'HQ'}
					{if !$show_by_region_code}
						<!-- HQ -->
						<td valign="top" nowrap>
							{include file='masterfile_sku_items_price.mprice.branch.tpl' bid=1 b=$branch.1}
						</td>
					{/if}
						
					{if $config.sku_use_region_price and $config.masterfile_branch_region and !$show_by_region_code}
						<!-- Change Price by Region -->

						<!-- Region -->
						{foreach from=$config.masterfile_branch_region key=region_code item=rg}
							<td valign="top">
								<!-- Price Type -->
								{if $trade_discount_type!=0 or $config.sku_always_show_trade_discount}
									<nobr>
									<div style="height:30;" class="showborder">
									<select name="region_trade_discount_code[{$items[i].id}][{$region_code}]" class="sel_price_type">
										{foreach from=$price_type_list item=pt}
											<option value="{$pt.code}" {if ($items[i].region_price.$region_code.normal.trade_discount_code eq $pt.code) or (!isset($items[i].region_price.$region_code.normal) and $pt.code eq $default_trade_discount_code)}selected {/if}>
												{$pt.code}
											</option>
										{/foreach}
									</select>
									<!-- View branch price type -->
									<img src="/ui/icons/application_view_list.png" title="View Branch Price Type" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', 'normal')" />
									
									<!-- Region price type not set -->
									{if !isset($items[i].region_price.$region_code.normal)}
										<img src="/ui/messages.gif" title="Region Price Type Not Set, default will choose master price type" />
									{/if}
									
									<!-- Branch have different price type -->
									{if $items[i].region_data.$region_code.normal.price_type_count>1}
										<img src="/ui/icons/exclamation.png" title="There are different price type in region branch" />
									{/if}
									
									<!-- Branch have different discount rate -->		
									{if $items[i].region_data.$region_code.normal.rate_count_got_diff}
										<img src="/ui/icons/chart_bar_error.png" title="There are different discount rate in region branch" />
									{/if}
									</nobr>
									</div>
								{/if}
								
								<!-- Selling Price -->			
								<div style="position:relative;height:100;width:{if $gst_settings}200{else}80{/if};" class="showborder">
								<div style="position:absolute;bottom:0;">
								{*if $config.sku_multiple_selling_price*}
								<div>
									<font color="green"><b>normal</b></font>
									{if $gst_settings}
										<font color="#da4" style="padding-left:19;font-weight:bold;">GST ({$items[i].gst_rate}%)</font>
										<font color="#f90" style="padding-left:9;font-weight:bold;">{if $items[i].inclusive_tax eq 'no'}After{else}Before{/if} GST</font>
									{/if}
								</div>
								{*/if*}
								<nobr>
									{assign var=selling_price value=$items[i].region_price.$region_code.normal.price|default:$items[i].selling_price}
									{assign var=selling_price_foc value=$items[i].region_price.$region_code.normal.selling_price_foc|default:$items[i].default_selling_price_foc}
									
									<!-- Price -->
									<input name="region_price[{$items[i].id}][{$region_code}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);check_foc_cb(this);" onfocus="this.select()" size="5" mprice_type="normal" is_region_price="1" class="inp_region_price region_selling inp_region_price_{$items[i].id}" id="inp_region_price-normal-{$items[i].id}-{$region_code}" {*if $selling_price_foc and $items[i].allow_selling_foc}readonly {/if*} readonly />
									
									{if $gst_settings}
										<!-- GST Amount -->
										{if $items[i].inclusive_tax eq "yes"}
											{assign var=tmp_gst_rate value=$items[i].gst_rate+100}
											{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
											{assign var=gst_amt value=$gst_selling_price*$items[i].gst_rate/100}
										{else}
											{assign var=gst_amt value=$selling_price*$items[i].gst_rate/100}
											{assign var=gst_selling_price value=$selling_price+$gst_amt}
										{/if}
										{assign var=gst_selling_price value=$gst_selling_price|round:2}
										<input type="text" name="region_gst_amount[{$items[i].id}][{$region_code}]" value="{$gst_amt|number_format:2:'.':''}" size="5" style="background:#da4;" readonly />

										<!-- sp before/after gst -->
										<input type="text" name="region_gst_price[{$items[i].id}][{$region_code}]" onchange="mf(this);calculate_region_gst({$items[i].id}, 'normal', '{$region_code}', this);{if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}','normal', '{$region_code}');{/if}" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" class="region_gst_selling inp_region_gst_price_{$items[i].id}" id="inp_region_gst_price-normal-{$items[i].id}-{$region_code}" style="background:#f90;" size="5" readonly />
									{/if}
									
									<!-- View Branch Selling Price -->
									<img src="/ui/icons/application_view_list.png" title="View Branch Selling Price" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', 'normal')" />
									
									<!-- View Price History -->
									<img src="/ui/icons/zoom.png" onclick="get_region_price_history(this,'{$items[i].id}','{$region_code}','{$rg.name}','normal')" title="View History" />
									
									<!-- Region selling price not set -->
									{if !isset($items[i].region_price.$region_code.normal)}
										<img src="/ui/messages.gif" title="Region selling price not set, the price will show master selling price" />
									{/if}
									<!-- Branch have different price type -->
									{if $items[i].region_data.$region_code.normal.selling_price_count>1}
										<img src="/ui/icons/exclamation.png" title="There are different selling price in region branch" />
									{/if}
									 
									 <br />
									 
									<!-- FOC -->
									<span style="{if !$items[i].allow_selling_foc}display:none;{/if}">
										<input type="checkbox" name="region_item_foc[normal][{$items[i].id}][{$region_code}]" value="1" title="FOC" onChange="region_item_foc_changed('normal', '{$items[i].id}', '{$region_code}')" id="region_item_foc-normal-{$items[i].id}-{$region_code}" class="chx_region_item_foc" mprice_type="normal" {if $selling_price_foc and $items[i].allow_selling_foc}checked {/if} {if !$items[i].allow_selling_foc}disabled {/if} />FOC
										<br />
									</span>
									
									<!-- Update -->
									<input type="checkbox" name="reg_item_edit[normal][{$items[i].id}][{$region_code}]" value="1" onChange="self_click_edit(this);" id="reg_item_edit-normal-{$items[i].id}-{$region_code}" class="chx_item_edit-normal-{$items[i].id} col_item-{$items[i].id}-{$region_code} cb_item_{$items[i].id} cb_update_price" is_region_price="1" />Update
									
								</nobr>
								</div>
								</div>
								{if $sessioninfo.privilege.SHOW_COST}
									{assign var=cost_price value=$items[i].cost_price}
									<div style="padding-top:10;height:150px;" class="small">
										<span><b>Latest Cost Price: {$cost_price|number_format:$config.global_cost_decimal_points}</b></span><br />
										{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
											{assign var=gp_selling_price value=$gst_selling_price}
										{else}
											{assign var=gp_selling_price value=$selling_price}
										{/if}
										{assign var=gp value=$gp_selling_price-$cost_price}
										{if $gp_selling_price ne 0}
											{assign var=gpp value=$gp/$gp_selling_price*100}
										{/if}
										<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
											<tr class="header">
												<th style="background:white;" align="left"><!--S.B: <font style="color:{if $items[i].stock_bal.$region_code >= 0}blue{else}red{/if};">{$items[i].stock_bal.$region_code}|default:'-'}-->&nbsp;</th>
												<th style="background:white;">GP</th>
												<th style="background:white;">GP(%)</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Current</th>
												<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
												<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">New</th>
												<th align="right" id="new_gp_{$items[i].id}_normal_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
												<th align="right" id="new_gpp_{$items[i].id}_normal_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Var.</th>
												<th align="right" id="gp_var_{$items[i].id}_normal_{$region_code}" style="background:white;">&nbsp;</th>
												<th align="right" id="gpp_var_{$items[i].id}_normal_{$region_code}" style="background:white;">&nbsp;</th>
											</tr>
											<input type="hidden" name="curr_price[{$items[i].id}][normal][{$region_code}]" value="{$gp_selling_price}" />
											<input type="hidden" name="cost_price[{$items[i].id}][normal][{$region_code}]" value="{$cost_price}" />
										</table>
									</div>
								{/if}
								<br />
								<!-- additional sellings: mprice -->
								{if $config.sku_multiple_selling_price}
									{foreach from=$config.sku_multiple_selling_price item=s}
										{assign var=selling_price value=$items[i].region_price.$region_code.$s.price|default:$items[i].selling_price}
										<div style="position:relative;height:80;width:80;" class="showborder">
										<div style="position:absolute;bottom:0;">
										<div>{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}</div>
										<nobr>								
											<!-- Price -->
											<input name="region_mprice[{$items[i].id}][{$s}][{$region_code}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);calculate_region_gst({$items[i].id}, '{$s}', '{$region_code}', this);{if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}','{$s}', '{$region_code}');{/if}" onfocus="this.select()" size="5" mprice_type="{$s}" class="inp_region_price region_selling inp_region_price_{$items[i].id}" id="inp_region_price-{$s}-{$items[i].id}-{$region_code}" readonly /> 
											
											{if $gst_settings}
												<!-- GST Amount -->
												{if $items[i].inclusive_tax eq "yes"}
													{assign var=tmp_gst_rate value=$items[i].gst_rate+100}
													{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
													{assign var=gst_amt value=$gst_selling_price*$items[i].gst_rate/100}
												{else}
													{assign var=gst_amt value=$selling_price*$items[i].gst_rate/100}
													{assign var=gst_selling_price value=$selling_price+$gst_amt}
												{/if}
												{assign var=gst_selling_price value=$gst_selling_price|round:2}
												<input type="text" name="region_gst_amount[{$items[i].id}][{$s}][{$region_code}]" value="{$gst_amt|number_format:2:'.':''}" size="5" style="background:#da4;" readonly />

												<!-- sp before/after gst -->
												<input type="text" name="region_gst_mprice[{$items[i].id}][{$s}][{$region_code}]" onchange="mf(this);calculate_region_gst({$items[i].id}, '{$s}', '{$region_code}', this);{if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}','{$s}', '{$region_code}');{/if}" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" class="region_gst_selling inp_region_gst_price_{$items[i].id}" id="inp_region_gst_price-{$s}-{$items[i].id}-{$region_code}" style="background:#f90;" size="5" readonly />
											{/if}
											
											<!-- FOC -->
											{*<input type="checkbox" name="region_item_foc[{$s}][{$items[i].id}][{$region_code}]" value="1" title="FOC" onChange="region_item_foc_changed('{$s}', '{$items[i].id}', '{$region_code}')" id="region_item_foc-{$s}-{$items[i].id}-{$region_code}" class="chx_region_item_foc" mprice_type="{$s}" {if !$selling_price}checked {/if} />*}
											
											<!-- View Branch Selling Price -->
											<img src="/ui/icons/application_view_list.png" title="View Branch MPrice Selling" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', '{$s}')" />
									
											<!-- View Price History -->
											<img src="/ui/icons/zoom.png" onclick="get_region_price_history(this,'{$items[i].id}','{$region_code}','{$rg.name}','{$s}')" title="View History" />
											
											<!-- Region selling price not set -->
											{if !isset($items[i].region_price.$region_code.$s)}
												<img src="/ui/messages.gif" title="Region mprice selling not set, the price will show master selling price" />
											{/if}
										
											<!-- Branch have different price type -->
											{if $items[i].region_data.$region_code.$s.selling_price_count>1}
												<img src="/ui/icons/exclamation.png" title="There are different mprice selling in region branch" />
											{/if}
										</nobr>
										
										<br />
										<input type="checkbox" name="reg_item_edit[{$s}][{$items[i].id}][{$region_code}]" value="1" onChange="self_click_edit(this);" id="reg_item_edit-{$s}-{$items[i].id}-{$region_code}" class="chx_item_edit-{$s}-{$items[i].id} col_item-{$items[i].id}-{$region_code} cb_item_{$items[i].id} cb_update_price" is_region_price="1" />Update
										
										</div>
										</div>
										{if $sessioninfo.privilege.SHOW_COST}
											<div style="padding-top:10;height:150px;" class="small">
												{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
													{assign var=gp_selling_price value=$gst_selling_price}
												{else}
													{assign var=gp_selling_price value=$selling_price}
												{/if}
												{assign var=cost_price value=$items[i].cost_price}
												{assign var=gp value=$gp_selling_price-$cost_price}
												{if $gp_selling_price ne 0}
													{assign var=gpp value=$gp/$gp_selling_price*100}
												{/if}
												<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
													<tr class="header">
														<th style="background:white;" align="left"><!--S.B: <font style="color:{if $items[i].stock_bal.$region_code >= 0}blue{else}red{/if};">{$items[i].stock_bal.$region_code}|default:'-'}-->&nbsp;</th>
														<th style="background:white;">GP</th>
														<th style="background:white;">GP(%)</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">Current</th>
														<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
														<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">New</th>
														<th align="right" id="new_gp_{$items[i].id}_{$s}_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
														<th align="right" id="new_gpp_{$items[i].id}_{$s}_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">Var.</th>
														<th align="right" id="gp_var_{$items[i].id}_{$s}_{$region_code}" style="background:white;">&nbsp;</th>
														<th align="right" id="gpp_var_{$items[i].id}_{$s}_{$region_code}" style="background:white;">&nbsp;</th>
													</tr>
													<input type="hidden" name="curr_price[{$items[i].id}][{$s}][{$region_code}]" value="{$gp_selling_price}" />
													<input type="hidden" name="cost_price[{$items[i].id}][{$s}][{$region_code}]" value="{$cost_price}" />
												</table>
											</div>
										{/if}
										<br />
									{/foreach}
								{/if}
							</td>
						{/foreach}
						
						<!-- Those branch have no region -->
						{foreach from=$region_branch.no_region item=b}
							<td valign="top" nowrap>
								{include file='masterfile_sku_items_price.mprice.branch.tpl' bid=$b.id}
							</td>
						{/foreach}
					{else}
						<!-- Normal Change Price Method: by branch -->
						
						{foreach from=$config.masterfile_branch_region key=region_code item=rg}
						
						{if $region_code eq $smarty.request.show_by_region_code}
						
							<td valign="top">
							
								<input type="hidden" name="region_only_update" value="1" />
							
								<!-- Price Type -->
								{if $trade_discount_type!=0 or $config.sku_always_show_trade_discount}
									<nobr>
									<div style="height:30;" class="showborder">
									<select name="ro_region_trade_discount_code[{$items[i].id}][{$region_code}]" class="sel_price_type">
										{foreach from=$price_type_list item=pt}
											<option value="{$pt.code}" {if ($items[i].region_price.$region_code.normal.trade_discount_code eq $pt.code) or (!isset($items[i].region_price.$region_code.normal) and $pt.code eq $default_trade_discount_code)}selected {/if}>
												{$pt.code}
											</option>
										{/foreach}
									</select>
									<!-- View branch price type -->
									<img src="/ui/icons/application_view_list.png" title="View Branch Price Type" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', 'normal')" />
									
									<!-- Region price type not set -->
									{if !isset($items[i].region_price.$region_code.normal)}
										<img src="/ui/messages.gif" title="Region Price Type Not Set, default will choose master price type" />
									{/if}
									
									<!-- Branch have different price type -->
									{if $items[i].region_data.$region_code.normal.price_type_count>1}
										<img src="/ui/icons/exclamation.png" title="There are different price type in region branch" />
									{/if}
									
									<!-- Branch have different discount rate -->		
									{if $items[i].region_data.$region_code.normal.rate_count_got_diff}
										<img src="/ui/icons/chart_bar_error.png" title="There are different discount rate in region branch" />
									{/if}
									</nobr>
									</div>
								{/if}
								
								<!-- Selling Price -->			
								<div style="position:relative;height:100;width:100;" class="showborder">
								<div style="position:absolute;bottom:0;">
								{if $config.sku_multiple_selling_price}<div><font color="green">normal</font></div>{/if}
								<nobr>
									{assign var=selling_price value=$items[i].region_price.$region_code.normal.price|default:$items[i].selling_price}
									{assign var=selling_price_foc value=$items[i].region_price.$region_code.normal.selling_price_foc|default:$items[i].default_selling_price_foc}
									
									<!-- Price -->
									<input name="ro_region_price[{$items[i].id}][{$region_code}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);copy_to_branch(this,'price[{$items[i].id}]', '{$items[i].id}', '{$region_code}');" onfocus="this.select()" size="5" mprice_type="normal" copy_from_region="1" class="inp_region_price region_selling inp_region_price_{$items[i].id}" id="inp_region_price-normal-{$items[i].id}-{$region_code}" {*if !$selling_price and $items[i].allow_selling_foc}readonly {/if*} readonly />
									
									<!-- View Branch Selling Price -->
									<img src="/ui/icons/application_view_list.png" title="View Branch Selling Price" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', 'normal')" />
									
									<!-- View Price History -->
									<img src="/ui/icons/zoom.png" onclick="get_region_price_history(this,'{$items[i].id}','{$region_code}','{$rg.name}','normal')" title="View History" />
									
									<!-- Region selling price not set -->
									{if !isset($items[i].region_price.$region_code.normal)}
										<img src="/ui/messages.gif" title="Region selling price not set, the price will show master selling price" />
									{/if}
									<!-- Branch have different price type -->
									{if $items[i].region_data.$region_code.normal.selling_price_count>1}
										<img src="/ui/icons/exclamation.png" title="There are different selling price in region branch" />
									{/if}
									 
									 <br />
									 
									<!-- FOC -->
									<span style="{if !$items[i].allow_selling_foc}display:none;{/if}">
										<input type="checkbox" name="ro_region_item_foc[normal][{$items[i].id}][{$region_code}]" value="1" title="FOC" onChange="region_item_foc_changed('normal', '{$items[i].id}', '{$region_code}')" id="region_item_foc-normal-{$items[i].id}-{$region_code}" class="chx_region_item_foc" mprice_type="normal" {if $selling_price_foc and $items[i].allow_selling_foc}checked {/if} {if !$items[i].allow_selling_foc}disabled {/if} />FOC
										<br />
									</span>
									<input type="checkbox" name="ro_reg_item_edit[normal][{$items[i].id}][{$region_code}]" value="1" onChange="self_click_edit(this);" id="reg_item_edit-normal-{$items[i].id}-{$region_code}" class="chx_item_edit-normal-{$items[i].id} col_item-{$items[i].id}-{$region_code} cb_item_{$items[i].id} cb_update_price" is_region_price="1" />Update
									
								</nobr>
								</div>
								</div>
								{if $sessioninfo.privilege.SHOW_COST}
									{assign var=cost_price value=$items[i].cost_price}
									<div style="padding-top:10;height:150px;" class="small">
										<span><b>Latest Cost Price: {$cost_price|number_format:$config.global_cost_decimal_points}</b></span><br />
										{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
											{assign var=gp_selling_price value=$gst_selling_price}
										{else}
											{assign var=gp_selling_price value=$selling_price}
										{/if}
										{assign var=gp value=$gp_selling_price-$cost_price}
										{if $gp_selling_price ne 0}
											{assign var=gpp value=$gp/$gp_selling_price*100}
										{/if}
										<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
											<tr class="header">
												<th style="background:white;" align="left"><!--S.B: <font style="color:{if $items[i].stock_bal.$region_code >= 0}blue{else}red{/if};">{$items[i].stock_bal.$region_code}|default:'-'}-->&nbsp;</th>
												<th style="background:white;">GP</th>
												<th style="background:white;">GP(%)</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Current</th>
												<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
												<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">New</th>
												<th align="right" id="new_gp_{$items[i].id}_normal_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
												<th align="right" id="new_gpp_{$items[i].id}_normal_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Var.</th>
												<th align="right" id="gp_var_{$items[i].id}_normal_{$region_code}" style="background:white;">&nbsp;</th>
												<th align="right" id="gpp_var_{$items[i].id}_normal_{$region_code}" style="background:white;">&nbsp;</th>
											</tr>
											<input type="hidden" name="curr_price[{$items[i].id}][normal][{$region_code}]" value="{$gp_selling_price}" />
											<input type="hidden" name="cost_price[{$items[i].id}][normal][{$region_code}]" value="{$cost_price}" />
										</table>
									</div>
								{/if}
								<br />
								<!-- additional sellings: mprice -->
								{if $config.sku_multiple_selling_price}
									{foreach from=$config.sku_multiple_selling_price item=s}
										{assign var=selling_price value=$items[i].region_price.$region_code.$s.price|default:$items[i].selling_price}
										<div style="position:relative;height:80;width:80;" class="showborder">
										<div style="position:absolute;bottom:0;">
										<div>{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}</div>
										<nobr>								
											<!-- Price -->
											<input name="ro_region_mprice[{$items[i].id}][{$s}][{$region_code}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);copy_to_branch(this,'mprice[{$items[i].id}][{$s}]', '{$items[i].id}', '{$region_code}');" onfocus="this.select()" size="5" mprice_type="{$s}" copy_from_region="1" class="inp_region_price region_selling inp_region_price_{$items[i].id}" id="inp_region_price-{$s}-{$items[i].id}-{$region_code}" readonly /> 
											
											{if $gst_settings}
												<!-- GST Amount -->
												{if $items[i].inclusive_tax eq "yes"}
													{assign var=tmp_gst_rate value=$items[i].gst_rate+100}
													{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
													{assign var=gst_amt value=$gst_selling_price*$items[i].gst_rate/100}
												{else}
													{assign var=gst_amt value=$selling_price*$items[i].gst_rate/100}
													{assign var=gst_selling_price value=$selling_price+$gst_amt}
												{/if}
												{assign var=gst_selling_price value=$gst_selling_price|round:2}
												<input type="text" name="region_gst_amount[{$items[i].id}][{$s}][{$region_code}]" value="{$gst_amt|number_format:2:'.':''}" size="5" style="background:#da4;" readonly />

												<!-- sp before/after gst -->
												<input type="text" name="ro_region_gst_mprice[{$items[i].id}][{$s}][{$region_code}]" onchange="mf(this);calculate_region_gst({$items[i].id}, '{$s}', '{$region_code}', this);{if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}','{$s}', '{$region_code}');{/if}" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" class="region_gst_selling inp_region_gst_price_{$items[i].id}" id="inp_region_gst_price-{$s}-{$items[i].id}-{$region_code}" style="background:#f90;" size="5" readonly />
											{/if}
											
											<!-- FOC -->
											{*<input type="checkbox" name="ro_region_item_foc[{$s}][{$items[i].id}][{$region_code}]" value="1" title="FOC" onChange="region_item_foc_changed('{$s}', '{$items[i].id}', '{$region_code}')" id="region_item_foc-{$s}-{$items[i].id}-{$region_code}" class="chx_region_item_foc" mprice_type="{$s}" {if !$selling_price}checked {/if} />*}
											
											<!-- View Branch Selling Price -->
											<img src="/ui/icons/application_view_list.png" title="View Branch MPrice Selling" class="clickable" onClick="show_region_branch_price_type_selling('{$items[i].id}', '{$region_code}', '{$s}')" />
									
											<!-- View Price History -->
											<img src="/ui/icons/zoom.png" onclick="get_region_price_history(this,'{$items[i].id}','{$region_code}','{$rg.name}','{$s}')" title="View History" />
											
											<!-- Region selling price not set -->
											{if !isset($items[i].region_price.$region_code.$s)}
												<img src="/ui/messages.gif" title="Region mprice selling not set, the price will show master selling price" />
											{/if}
										
											<!-- Branch have different price type -->
											{if $items[i].region_data.$region_code.$s.selling_price_count>1}
												<img src="/ui/icons/exclamation.png" title="There are different mprice selling in region branch" />
											{/if}
										</nobr>
										
										<br />
										<input type="checkbox" name="ro_reg_item_edit[{$s}][{$items[i].id}][{$region_code}]" value="1" onChange="self_click_edit(this);" id="reg_item_edit-{$s}-{$items[i].id}-{$region_code}" class="chx_item_edit-{$s}-{$items[i].id} col_item-{$items[i].id}-{$region_code} cb_item_{$items[i].id} cb_update_price" is_region_price="1" />Update
										
										</div>
										</div>
										{if $sessioninfo.privilege.SHOW_COST}
											<div style="padding-top:10;height:150px;" class="small">
												{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
													{assign var=gp_selling_price value=$gst_selling_price}
												{else}
													{assign var=gp_selling_price value=$selling_price}
												{/if}
												{assign var=cost_price value=$items[i].cost_price}
												{assign var=gp value=$gp_selling_price-$cost_price}
												{if $gp_selling_price ne 0}
													{assign var=gpp value=$gp/$gp_selling_price*100}
												{/if}
												<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
													<tr class="header">
														<th style="background:white;" align="left"><!--S.B: <font style="color:{if $items[i].stock_bal.$region_code >= 0}blue{else}red{/if};">{$items[i].stock_bal.$region_code}|default:'-'}-->&nbsp;</th>
														<th style="background:white;">GP</th>
														<th style="background:white;">GP(%)</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">Current</th>
														<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
														<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">New</th>
														<th align="right" id="new_gp_{$items[i].id}_{$s}_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
														<th align="right" id="new_gpp_{$items[i].id}_{$s}_{$region_code}" style="color:blue; background:white;">&nbsp;</th>
													</tr>
													<tr>
														<th align="left" style="background:white;">Var.</th>
														<th align="right" id="gp_var_{$items[i].id}_{$s}_{$region_code}" style="background:white;">&nbsp;</th>
														<th align="right" id="gpp_var_{$items[i].id}_{$s}_{$region_code}" style="background:white;">&nbsp;</th>
													</tr>
													<input type="hidden" name="curr_price[{$items[i].id}][{$s}][{$region_code}]" value="{$gp_selling_price}" />
													<input type="hidden" name="cost_price[{$items[i].id}][{$s}][{$region_code}]" value="{$cost_price}" />
												</table>
											</div>
										{/if}
										<br />
									{/foreach}
								{/if}
							</td>
							
						{/if}
						{/foreach}
						
						{foreach from=$branch item=b}
							{if $b.code ne 'HQ'}
								<td valign="top" nowrap>
									{include file='masterfile_sku_items_price.mprice.branch.tpl' bid=$b.id }
								</td>
							{/if}
						{/foreach}
					{/if}
				{else}
					<td valign=top>
			
					{assign var=bid value=$sessioninfo.branch_id}
					<!-- single branch -->
					{if $trade_discount_type!=0}
						<select name="trade_discount_code[{$items[i].id}][{$bid}]">
						{foreach from=$discount_codes key=dc item=pct}
						<option value="{$dc}" {if ($items[i].trade_discount_code eq $dc) || ($items[i].trade_discount_code eq '' && $default_trade_discount_code eq $dc)}selected{/if}>{$dc} ({$pct}%)</option>
						{/foreach}
						</select><br>
					{/if}
					</td>
					
					<td style="position:relative;padding-top:24;vertical-align:top;" class="showborder" align="right">
						<input type="checkbox" onchange="toggle_check_all_row2(this.checked,'{$items[i].id}');" class="cb_all_row2 cb_all_row2_{$items[i].id}" />
					</td>
					
					<td valign=top nowrap>
						{assign var=selling_price value=$items[i].price}
						{assign var=selling_price_foc value=$items[i].selling_price_foc}
						
						<div>
							<font color="green"><b>normal</b></font>
							{if $gst_settings}
								<font color="#da4" style="padding-left:19;font-weight:bold;">GST ({$items[i].gst_rate}%)</font>
								<font color="#f90" style="padding-left:9;font-weight:bold;">{if $items[i].inclusive_tax eq 'no'}After{else}Before{/if} GST</font>
							{/if}
							
							{* RSP Discount *}
							{if $items[i].use_rsp}
								<font color="red" style="padding-left:35px;font-weight:bold;">RSP Discount</font>
							{/if}
						</div>
						<!-- Price -->
						<input name="price[{$items[i].id}][{$bid}]" value="{$selling_price|number_format:2:".":""}" onchange="item_selling_price_changed('{$items[i].id}', '{$bid}');" onfocus="this.select()" size="5" class="inp_price inp_price_{$items[i].id}-{$bid}" id="inp_price-normal-{$items[i].id}-{$bid}" {if !$selling_price and $items[i].allow_selling_foc}readonly {/if} mprice_type="normal" readonly /> 
						
						{if $gst_settings}
							<!-- gst amount -->
							{if $items[i].inclusive_tax eq "yes"}
								{assign var=tmp_gst_rate value=$items[i].gst_rate+100}
								{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
								{assign var=gst_amt value=$gst_selling_price*$items[i].gst_rate/100}
							{else}
								{assign var=gst_amt value=$selling_price*$items[i].gst_rate/100}
								{assign var=gst_selling_price value=$selling_price+$gst_amt}
							{/if}
							{assign var=gst_selling_price value=$gst_selling_price|round:2}
							<input type="text" name="gst_amount[{$items[i].id}][{$bid}]" value="{$gst_amt|number_format:2:'.':''}" size="5" style="background:#da4;" readonly />

							<!-- sp before/after gst -->
							<input type="text" name="gst_price[{$items[i].id}][{$bid}]" onchange="item_gst_price_changed('{$items[i].id}', '{$bid}');" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" class="inp_gst_price inp_gst_price_{$items[i].id}" id="inp_gst_price-normal-{$items[i].id}-{$bid}" style="background:#f90;" size="5" readonly />
						{/if}
						
						<img src="/ui/icons/zoom.png" onclick="price_history(this,{$items[i].id},{$bid})" title="View History">
						
						{* RSP Discount *}
						{if $items[i].use_rsp}
							<input type="text" name="rsp_discount[{$items[i].id}][{$bid}]" value="{$items[i].rsp_discount}" size="6"  onfocus="this.select()" onChange="rsp_discount_changed('{$items[i].id}', '{$bid}')" class="inp_rsp_discount" readonly />
						{/if}

						<!-- FOC -->
						<span style="{if !$items[i].allow_selling_foc}display:none;{/if}">
							<br />
							<input type="checkbox" name="item_foc[normal][{$items[i].id}][{$bid}]" value="1" title="FOC" onChange="item_foc_changed('normal','{$items[i].id}', '{$bid}')" id="item_foc-normal-{$items[i].id}-{$bid}" class="chx_item_foc" mprice_type="normal" {if $selling_price_foc and $items[i].allow_selling_foc}checked {/if} {if !$items[i].allow_selling_foc}disabled {/if} /> FOC
						</span>
						
						<br />
						<label><input type="checkbox" name="cb_inp_price[normal][{$items[i].id}][{$bid}]" id="cb_inp_price-normal-{$items[i].id}-{$bid}" onChange="self_click_edit(this);" class="cb-{$items[i].id} cb_col-normal cb_update_price" />Update</label>
						{if $sessioninfo.privilege.SHOW_COST}
							{assign var=cost_price value=$items[i].cost.$bid|default:$items[i].cost_price}
							<div style="padding-top:10;height:150px;" class="small">
								<span><b>Latest Cost Price: {$cost_price|number_format:$config.global_cost_decimal_points}</b></span><br />
								{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
									{assign var=gp_selling_price value=$gst_selling_price}
								{else}
									{assign var=gp_selling_price value=$selling_price}
								{/if}
								{assign var=gp value=$gp_selling_price-$cost_price}
								{if $gp_selling_price ne 0}
									{assign var=gpp value=$gp/$gp_selling_price*100}
								{/if}
								{assign var=gp_val value=$gp*$items[i].stock_bal.$bid}
								<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
									<tr class="header">
										<th style="background:white;" align="left">S.B: <font style="color:{if $items[i].stock_bal.$bid >= 0}blue{else}red{/if};">{$items[i].stock_bal.$bid|default:'-'}</th>
										<th style="background:white;">GP</th>
										<th style="background:white;">GP(%)</th>
										<th style="background:white;">Val</th>
									</tr>
									<tr>
										<th align="left" style="background:white;">Current</th>
										<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
										<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
										<th align="right" style="color:blue; background:white;">{$gp_val|number_format:2}</th>
									</tr>
									<tr>
										<th align="left" style="background:white;">New</th>
										<th align="right" id="new_gp_{$items[i].id}_normal_{$bid}" style="color:blue; background:white;">&nbsp;</th>
										<th align="right" id="new_gpp_{$items[i].id}_normal_{$bid}" style="color:blue; background:white;">&nbsp;</th>
										<th align="right" id="new_gpv_{$items[i].id}_normal_{$bid}" style="color:blue; background:white;">&nbsp;</th>
									</tr>
									<tr>
										<th align="left" style="background:white;">Var.</th>
										<th align="right" id="gp_var_{$items[i].id}_normal_{$bid}" style="background:white;">&nbsp;</th>
										<th align="right" id="gpp_var_{$items[i].id}_normal_{$bid}" style="background:white;">&nbsp;</th>
										<th align="right" id="gpv_var_{$items[i].id}_normal_{$bid}" style="background:white;">&nbsp;</th>
									</tr>
									<input type="hidden" name="curr_price[{$items[i].id}][normal][{$bid}]" value="{$gp_selling_price}" />
									<input type="hidden" name="cost_price[{$items[i].id}][normal][{$bid}]" value="{$cost_price}" />
									<input type="hidden" name="stock_bal[{$items[i].id}][{$bid}]" value="{$items[i].stock_bal.$bid}" />
								</table>
							</div>
						{/if}
					</td>
					<!-- additional sellings: mprice -->
					{if $config.sku_multiple_selling_price}
						{foreach from=$config.sku_multiple_selling_price item=s}
							{assign var=selling_price value=$items[i].mprice.$s}
							{if $selling_price eq 0}
							{assign var=selling_price value=$items[i].price}
							{/if}
							<td valign=top nowrap>
								<div>
									<font color="green"><b>normal</b></font>
									{if $gst_settings}
										<font color="#da4" style="padding-left:19;font-weight:bold;">GST ({$items[i].gst_rate}%)</font>
										<font color="#f90" style="padding-left:9;font-weight:bold;">{if $items[i].inclusive_tax eq 'no'}After{else}Before{/if} GST</font>
									{/if}
								</div>
								<!-- Price -->
								<input name="mprice[{$items[i].id}][{$s}][{$bid}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);calculate_gst({$items[i].id}, '{$s}', {$bid}, this);{if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}', '{$s}', '{$bid}');{/if}" onfocus="this.select()" size="5" class="inp_price inp_mprice_{$items[i].id}_{$bid} inp_price_{$items[i].id}" id="inp_price-{$s}-{$items[i].id}-{$bid}" mprice_type="{$s}" readonly />
								
								{if $gst_settings}
									<!-- gst amount -->
									{if $items[i].inclusive_tax eq "yes"}
										{assign var=tmp_gst_rate value=$items[i].gst_rate+100}
										{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
										{assign var=gst_amt value=$gst_selling_price*$items[i].gst_rate/100}
									{else}
										{assign var=gst_amt value=$selling_price*$items[i].gst_rate/100}
										{assign var=gst_selling_price value=$selling_price+$gst_amt}
									{/if}
									{assign var=gst_selling_price value=$gst_selling_price|round:2}
									<input type="text" name="gst_amount[{$items[i].id}][{$s}][{$bid}]" value="{$gst_amt|number_format:2:'.':''}" size="5" style="background:#da4;" readonly />

									<!-- sp before/after gst -->
									<input type="text" name="gst_mprice[{$items[i].id}][{$s}][{$bid}]" onchange="mf(this);calculate_gst({$items[i].id}, '{$s}', {$bid}, this);" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" style="background:#f90;" id="inp_gst_price-{$s}-{$items[i].id}-{$bid}" class="inp_gst_price inp_gst_mprice_{$items[i].id}" size="5" readonly />
								{/if}
								
								<!-- FOC -->
								{*<input type="checkbox" name="item_foc[{$s}][{$items[i].id}]" value="1" title="FOC" onChange="item_foc_changed('{$s}','{$items[i].id}')" id="item_foc-{$s}-{$items[i].id}" class="chx_item_foc" mprice_type="{$s}" {if !$selling_price}checked {/if} />*}
								 
								<img src="/ui/icons/zoom.png" onclick="price_history(this,{$items[i].id},{$bid},'{$s}')" title="View History" />
								
								<br />
								<label><input type="checkbox" name="cb_inp_price[{$s}][{$items[i].id}][{$bid}]" id="cb_inp_price-{$s}-{$items[i].id}-{$bid}" onChange="self_click_edit(this);" class="cb-{$items[i].id} cb_col-{$s} cb_update_price" />Update</label>
								
								{if $sessioninfo.privilege.SHOW_COST}
									<div style="padding-top:10;height:150px;" class="small">
										{if $gst_settings && $items[i].inclusive_tax eq 'yes' && $items[i].gst_rate > 0}
											{assign var=gp_selling_price value=$gst_selling_price}
										{else}
											{assign var=gp_selling_price value=$selling_price}
										{/if}
										{assign var=gp value=$gp_selling_price-$cost_price}
										{if $gp_selling_price ne 0}
											{assign var=gpp value=$gp/$gp_selling_price*100}
										{/if}
										{assign var=gp_val value=$gp*$items[i].stock_bal.$bid}
										<table cellpadding="2" cellspacing="1" style="border:1px solid #999; padding:5px; background-color:#fe9">
											<tr class="header">
												<th style="background:white;" align="left">S.B: <font style="color:{if $items[i].stock_bal.$bid >= 0}blue{else}red{/if};">{$items[i].stock_bal.$bid|default:'-'}</th>
												<th style="background:white;">GP</th>
												<th style="background:white;">GP(%)</th>
												<th style="background:white;">Val</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Current</th>
												<th align="right" style="color:blue; background:white;">{$gp|number_format:4}</th>
												<th align="right" style="color:blue; background:white;">{$gpp|number_format:2}</th>
												<th align="right" style="color:blue; background:white;">{$gp_val|number_format:2}</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">New</th>
												<th align="right" id="new_gp_{$items[i].id}_{$s}_{$bid}" style="color:blue; background:white;">&nbsp;</th>
												<th align="right" id="new_gpp_{$items[i].id}_{$s}_{$bid}" style="color:blue; background:white;">&nbsp;</th>
												<th align="right" id="new_gpv_{$items[i].id}_{$s}_{$bid}" style="color:blue; background:white;">&nbsp;</th>
											</tr>
											<tr>
												<th align="left" style="background:white;">Var.</th>
												<th align="right" id="gp_var_{$items[i].id}_{$s}_{$bid}" style="background:white;">&nbsp;</th>
												<th align="right" id="gpp_var_{$items[i].id}_{$s}_{$bid}" style="background:white;">&nbsp;</th>
												<th align="right" id="gpv_var_{$items[i].id}_{$s}_{$bid}" style="background:white;">&nbsp;</th>
											</tr>
											<input type="hidden" name="curr_price[{$items[i].id}][{$s}][{$bid}]" value="{$gp_selling_price}" />
											<input type="hidden" name="cost_price[{$items[i].id}][{$s}][{$bid}]" value="{$cost_price}" />
										</table>
									</div>
								{/if}
							</td>
						{/foreach}
					{/if}
				{/if}
			{else}
				<td colspan="{count var=$branch}">In-active</td>
			{/if}
		{/if}
	</tr>
{assign var=col_select value=0}
{/section}
</table>
<div id="price_btn"><input class="btn btn-primary" type="button" name="upd" value="Update Price" onclick="update_price();"><span id="loading_area"></span></div>
</form>
