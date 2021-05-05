{*
9/15/2011 3:12:49 PM Andy
- Add checkbox for FOC item, user must checked checkbox instead of leaving zero for FOC item.

10/24/2011 6:08:35 PM Andy
- Add checking to only show FOC checkbox if the SKU got allow FOC in masterfile.

10/25/2011 6:16:29 PM Andy
- Move HQ to always at first column.
- Prompt user to confirm whether want to replace HQ selling price/price type to all branches.

7/24/2012 5:31:34 PM Justin
- Enhanced the function that capture item and branch ID for automate price copy.

5:35 PM 1/23/2013 Justin
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

7/16/2014 1:18 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

7/17/2014 5:41 PM Justin
- Enhanced to have GP value calculation.

9/25/2014 2:39 PM Justin
- Enhanced to have GST information.

10/29/2014 9:54 AM Justin
- Bug fixed on GST does not showing 0% while it is zero percent.

3/11/2015 5:37 PM Andy
- Enhanced to store the checkbox FOC value into database.
- Change to not auto zerolise selling price when user tick FOC checkbox.

10/28/2016 15:05 Qiu Ying
- Enhanced the backend Mprice module to show both 1) Member's type 2) Member1, Member2, Member 3(remain)

11/8/2016 9:35 AM Qiu Ying
- Bug fixed on MPrice name should show one times when membership_type same name as sku_multiple selling_price

6/5/2017 5:06 PM Justin
- Enhanced to have cost information (need view cost privilege).

4/28/2020 5:36 PM Andy
- Modified layout to compatible with new UI.

11/13/2020 4:15 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
*}

{if $trade_discount_type!=0 or $config.sku_always_show_trade_discount}
<div style="height:30;" class="showborder">
	<select name="trade_discount_code[{$items[i].id}][{$bid}]" onChange="{if $b.code eq 'HQ'}copy_price_type_to_branch(this);{/if}" class="sel_price_type">
		{foreach from=$discount_codes.$bid key=dc item=pct}
			<option value="{$dc}" {if ($items[i].trade_discount_code[$bid] eq $dc) || ($items[i].trade_discount_code[$bid] eq '' && $default_trade_discount_code eq $dc)}selected{/if}>{$dc} ({$pct|ifzero:'0'}%)</option>
		{/foreach}
	</select>
</div>
{/if}

<div style="position:relative;height:100;width:{if $gst_settings}200{else}80{/if};" class="showborder">
<div style="position:absolute;bottom:0;">

<!-- Normal Price -->
{*if $config.sku_multiple_selling_price*}
<div>
	<font color="green"><b>normal</b></font>
	{if $gst_settings}
		<font color="#da4" style="padding-left:19;font-weight:bold;">GST ({$items[i].gst_rate|default:'0'}%)</font>
		<font color="#f90" style="padding-left:9;font-weight:bold;">{if $items[i].inclusive_tax eq 'no'}After{else}Before{/if} GST</font>
	{/if}
	
	{* RSP Discount *}
	{if $items[i].use_rsp}
		<font color="red" style="padding-left:35px;font-weight:bold;">RSP Discount</font>
	{/if}
</div>
{*/if*}

{assign var=selling_price value=$items[i].price[$bid]|default:$items[i].selling_price}
{assign var=selling_price_foc value=$items[i].selling_price_foc[$bid]|default:$items[i].default_selling_price_foc}

<!-- Price -->
<input name="price[{$items[i].id}][{$bid}]" value="{$selling_price|number_format:2:".":""}" onchange="item_selling_price_changed('{$items[i].id}', '{$bid}');" onfocus="this.select()" size="5" mprice_type="normal" class="inp_price inp_mprice_{$items[i].id} inp_mprice_{$items[i].id}_{$bid} branch_selling" id="inp_price-normal-{$items[i].id}-{$bid}" {*if !$selling_price and $items[i].allow_selling_foc}readonly{/if*} readonly />

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

<img src="/ui/icons/zoom.png" onclick="get_price_history(this,{$items[i].id},{$bid},'{$b.code}')" title="View History" />

{* RSP Discount *}
{if $items[i].use_rsp}
	{assign var=branch_rsp_discount value=$items[i].rsp_discount}
	{if isset($items[i].branch_rsp_discount.$bid)}
		{assign var=branch_rsp_discount value=$items[i].branch_rsp_discount.$bid}
	{/if}
	<input type="text" name="rsp_discount[{$items[i].id}][{$bid}]" value="{$branch_rsp_discount}" size="6" onfocus="this.select()" onChange="rsp_discount_changed('{$items[i].id}', '{$bid}')" class="inp_rsp_discount" readonly />
{/if}

<br />

<!-- FOC -->
<span style="{if !$items[i].allow_selling_foc}display:none;{/if}">
	<input type="checkbox" name="item_foc[normal][{$items[i].id}][{$bid}]" value="1" title="FOC" onChange="item_foc_changed('normal','{$items[i].id}', '{$bid}')" id="item_foc-normal-{$items[i].id}-{$bid}" class="chx_item_foc" mprice_type="normal" {if $selling_price_foc and $items[i].allow_selling_foc}checked {/if} {if !$items[i].allow_selling_foc}disabled {/if} />FOC
	<br />
</span>

<!-- Update -->
<input type="checkbox" name="item_edit[normal][{$items[i].id}][{$bid}]" value="1" onChange="self_click_edit(this);" id="item_edit-normal-{$items[i].id}-{$bid}" class="chx_item_edit-normal-{$items[i].id} col_item-{$items[i].id}-{$bid} cb_mprice_{$items[i].id} cb_update_price" />Update

</div>
</div>
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
<br />
<!-- additional sellings: mprice -->
{if $config.sku_multiple_selling_price}
	{foreach from=$config.sku_multiple_selling_price item=s}
		{assign var=selling_price value=$items[i].mprice.$s.$bid|default:$items[i].price[$bid]|default:$items[i].selling_price}
		<div style="/*position:relative;*/height:80;width:100%;" class="showborder">
		<div style="/*position:absolute;bottom:0;*/">
		<input type="checkbox" name="item_edit[{$s}][{$items[i].id}][{$bid}]" value="1" id="item_edit-{$s}-{$items[i].id}-{$bid}" onChange="self_click_edit(this);" class="chx_item_edit-{$s}-{$items[i].id} col_item-{$items[i].id}-{$bid} cb_mprice_{$items[i].id} cb_update_price" />Update
		<div>{if $config.membership_type[$s] && $config.membership_type[$s] neq $s}{$config.membership_type[$s]} ({$s}){else}{$s}{/if}</div>
		
		<!-- Price -->
		<input name="mprice[{$items[i].id}][{$s}][{$bid}]" value="{$selling_price|number_format:2:".":""}" onchange="mf(this);calculate_gst({$items[i].id}, '{$s}', {$bid}, this);{if $b.code eq 'HQ'}copy_to_branch(this,'mprice[{$items[i].id}][{$s}]', '{$items[i].id}', '{$bid}');{/if} {if $sessioninfo.privilege.SHOW_COST}update_gp('{$items[i].id}', '{$s}', '{$bid}');{/if}" onfocus="this.select()" size="5"  mprice_type="{$s}" class="inp_price inp_mprice_{$items[i].id} inp_mprice_{$items[i].id}_{$bid} branch_selling" id="inp_price-{$s}-{$items[i].id}-{$bid}" readonly />
		
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
			<input type="text" name="gst_mprice[{$items[i].id}][{$s}][{$bid}]" onchange="mf(this);calculate_gst({$items[i].id}, '{$s}', {$bid}, this);{if $BRANCH_CODE eq 'HQ'}copy_to_branch($('inp_price-{$s}-{$items[i].id}-{$bid}'),'mprice[{$items[i].id}][{$s}]', '{$items[i].id}', '{$bid}');{/if}" onfocus="this.select()" value="{$gst_selling_price|number_format:2:'.':''}" style="background:#f90;" id="inp_gst_price-{$s}-{$items[i].id}-{$bid}" class="inp_gst_price inp_gst_mprice_{$items[i].id}" size="5" readonly />
		{/if}
		
		<!-- FOC -->
		{*<input type="checkbox" name="item_foc[{$s}][{$items[i].id}][{$bid}]" value="1" title="FOC" onChange="item_foc_changed('{$s}','{$items[i].id}', '{$bid}')" id="item_foc-{$s}-{$items[i].id}-{$bid}" class="chx_item_foc" mprice_type="{$s}" {if !$selling_price}checked {/if} />*}
		 
		<img src="/ui/icons/zoom.png" onclick="get_price_history(this,{$items[i].id},{$bid},'{$b.code}','{$s}')" title="View History" /><br>
		
		</div>
		</div>
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
		
		<br />
	{/foreach}
{/if}