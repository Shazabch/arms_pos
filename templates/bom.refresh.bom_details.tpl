{*
REVISION HISTORY
=================
3/3/2008 4:38:16 PM gary
- add the total cost and total selling column.
- move the misc cost column.

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

10/25/2010 3:49:12 PM Justin
- Fixed the number format problem from newer version of php.

10/10/2011 11:19:42 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

9/19/2012 10:50 AM Andy
- Add new BOM Type (Package). (Need Config)

9/20/2012 11:34 AM Justin
- Enhanced to include more info for user to edit.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

1/7/2015 4:40 PM Justin
- Enhanced to have GST calculation.

1/28/2015 3:10 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.
- Enhance Open Price/Allow Selling FOC checking.

3/24/2015 3:39 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

10/25/2016 10:18 AM Andy
- Enhanced to remove double quotes for product description and receipt description.

10/27/2016 3:15 PM Andy
- Fixed double quotes checking should check config.masterfile_disallow_double_quote

4/20/2017 9:53 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

7/13/2018 2:31 PM Andy
- Fixed bug where item in bom content will become missing if user delete it in other tab.

11/6/2019 6:00 PM William
- Fixed bug "reason" field value will show on create new item when last edit bom item has reason field value.
- Fixed bug artno, mcode field will disable on create new item when last edit item has implode.
- Enhanced to show Sku Type.

05/13/2020 6:27PM Sheila
- Updated button color

06/26/2020 02:34PM Sheila
- Updated button css

02/15/2021 05:28PM Rayleen
- Add marketplace required field - Weight, Lenght, Width, Height and Marketplace Description

02/16/2021 02:13PM Rayleen
- Add checking for "arms_marketplace_settings" before displaying marketplace description

*}
<div class="stdframe" style="background:#fff;">

	<input type="hidden" name="edit_time" value="{$form.edit_time}" />
	<input type="hidden" name="disabled_edit" value="{$form.disabled_edit}" />
	
{if $form.bom_id}
<h3>SKU Item {$form.sku_item_code}</h3>

<div id="div_qty_implode_explode" style="{if $form.bom_type eq 'package'}display:none;{/if}">
	<b>Qty :</b> <input name=qty_bom size=8 class="r" onchange="mi(this);"> &nbsp;&nbsp;
	<input class="btn btn-success" type=button value="Implode" onclick="do_implode();"> &nbsp;&nbsp;
	<input class="btn btn-error" type=button value="Explode" onclick="do_explode();">
	<br><br>
</div>
{/if}

<h4>General Information</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>Product Description</b></td>
<td>
<input id="description" name="description" size="80" value="{$form.description|escape:html}" onchange="uc(this);{if $config.masterfile_disallow_double_quote}check_description(this);{/if} add_to_sku_receipt_desc('description', 'receipt_description');"> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
</tr>

<tr>
<td><b>Receipt Description [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>
<td>
<input id="receipt_description" name="receipt_description" size="50" value="{$form.receipt_description|escape:html}" onchange="uc(this);{if $config.masterfile_disallow_double_quote}check_description(this);{/if} update_sku_receipt_desc(this);" maxlength="30"> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
</tr>

<tr>
<td><b>Article No.</b></td>
<td>
<input name=artno value="{$form.artno}" {if $form.disabled_edit}readonly{/if} maxlength="30" onchange="check_artmcode(this,'artno')">
</td>
</tr>

{if $config.sku_bom_show_mcode}
<tr>
<td><b>Manufacturer's Code</b></td>
<td>
<input name=mcode value="{$form.mcode}" {if $form.disabled_edit}readonly{/if} maxlength="30" onchange="check_artmcode(this,'mcode')">
</td>
</tr>
{/if}

<tr>
<td><b>Sku Type</b></td>
<td><label>{$form.sku_type_description}</label></td>
</tr>

{if $config.sku_bom_additional_type}
	<tr>
		<td><b>Type [<a href="javascript:void(alert('Package: sell as BOM content in POS'))">?</a>]</b></td>
		<td>
			{if $form.disabled_edit}
				{if $form.bom_type eq 'normal'}Normal{else}Package{/if}
			{/if}
			<select name="bom_type" {if $form.disabled_edit}style="display:none;"{/if} onChange="BOM_EDITOR.bom_type_changed();" >
				<option value="normal" {if $form.bom_type eq 'normal'}selected {/if}>Normal</option>
				<option value="package" {if $form.bom_type eq 'package'}selected {/if}>Package</option>
			</select>
		</td>
	</tr>
{else}
	<tr>
		<td><b>Type</b></td>
		<td>
			{$form.bom_type|capitalize}
			<input type="hidden" value="{$form.bom_type}" name="bom_type" />
		</td>
	</tr>
{/if}



{if $gst_status}
<tr>
	<td><b>Output Tax</b></td>
	<td>
		<select name="output_tax" onchange="BOM_EDITOR.calc_gst();">
			{foreach from=$output_tax_list key=rid item=r}
				<option value="{$r.id}" {if $form.output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}

		</select>
	</td>
</tr>

<tr>
	<td><b>Selling Price Inclusive Tax</b></td>
	<td>
		<select name="inclusive_tax" onchange="BOM_EDITOR.calc_gst();">
          <option value="inherit" {if !$form.inclusive_tax || $form.inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow GST Settings: {$mst_inclusive_tax|@strtoupper})</option>
          <option value="yes" {if $form.inclusive_tax eq "yes"}selected {/if}>Yes</option>
          <option value="no" {if $form.inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>
{/if}

<tr>
	<td valign="top"><b>Selling Price Settings</b></td>
	<td>
		<ul style="list-style:none;" id="ul_selling_price_settings">
			<li><input type="checkbox" class="chx_sp_settings" name="open_price" {if $form.open_price}checked{/if} value="1" onChange="toggle_open_price();">&nbsp;Open Price</li>
			<li>
				<input type="checkbox" class="chx_sp_settings" name="allow_selling_foc" value="1" {if $form.allow_selling_foc}checked {/if} onclick="{if $form.disabled_edit}return false;{/if}" onChange="toggle_allow_selling_foc();" />&nbsp;
				Allow Selling FOC
			</li>
		</ul>
	</td>
</tr>

<tr>
<td><b>Selling Price</b></td>
<td>
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			{$config.arms_currency.symbol}
			<input name="selling_price" size=8 onchange="mf(this); BOM_EDITOR.calc_gst();" value="{$form.selling_price|default:0|number_format:2}" {if $form.disabled_edit || $form.bom_type eq 'package'}readonly{/if} onclick="this.select();"> 
			<img src="ui/rq.gif" align="absbottom" title="Required Field"> 
			<span id="span_selling_foc" style="{if !$form.allow_selling_foc}display:none;{/if}">
				<input type="checkbox" name="selling_foc" value="1" {if $form.allow_selling_foc and $form.selling_foc}checked {/if} {if !$form.allow_selling_foc}disabled {/if} onclick="{if $form.disabled_edit}return false;{else}check_selling_foc();{/if}"/> <b>FOC</b>
			</span>
			<span style="color:blue;">(Item Total Selling: <span id="span_item_total_selling">0.00</span>)</span>
		</td>
		{if $gst_status}
			<td><b>GST (<span id="span_gst_rate">0</span>%)</b></td>
			<td>{$config.arms_currency.symbol} <input type="text" size="6" name="gst_rate" value="" readonly /></td>
		{/if}
	</tr>
	</table>
</td>
{if $gst_status}
<td class="gst_settings" nowrap><b>Selling Price <span id="span_gst_indicator">Before</span> GST</b></td>
<td class="gst_settings">{$config.arms_currency.symbol} <input type="text" size="6" name="selling_price_gst" value="" onchange="BOM_EDITOR.calc_gst('gst_price');" {if $form.allow_selling_foc}readOnly {/if}/></td>
{/if}
</tr>

<tr>
<td><b>Misc Cost</b></td>
<td>{$config.arms_currency.symbol}
<input size=8 id=misc_cost name=misc_cost value="{$form.misc_cost+0|number_format:2}" onchange="mf(this);calc_all();" onclick="this.select();" {if $form.disabled_edit}readonly{/if}>
</td>
</tr>

<tr>
<td><b>Cost Price</b></td>
<td>{$config.arms_currency.symbol}
<input id=cost_price name=cost_price size=8 onchange="float(round(this,global_cost_decimal_points));" value="{$form.cost_price+0|number_format:$config.global_cost_decimal_points}" readonly {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
{if $sessioninfo.privilege.SHOW_COST}<span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>{/if}
{if !$sessioninfo.privilege.SHOW_COST}-{/if}
</td>
</tr>

<tr>
	<td>
	<b>Item Location</b></td>
	<td>
	<input onchange="ucz(this)" name="location" size=6 value="{$form.location}">
	</td>
</tr>

<tr>
    <td><b>Block item in PO</b></td>
    <td id="branches_id" colspan=3>
    <input type="checkbox" onclick="check_all_branch(this)"> All
    {foreach item=br from=$branch_list}
    <span style="white-space:nowrap"><input type="checkbox" class="branches_id" name="block_list[{$br.id}]" {if $form.block_list[$br.id]}checked{/if}>&nbsp;{$br.code}</span>
    {/foreach}
	</td>
</tr>


<tr>
	<td><b>Active</b></td>
	<td>
	<table cellpadding=0 cellspacing=0>
	<tr>
	<td>
		<input type="hidden" id="oactive" name="oactive" value="{$form.active}">
		<input type="checkbox" id="active" name="active" {if $form.active}checked{/if} value="1" onchange="toggle_active(this);">
	</td>
	<td class="reason_box" {if $form.active}style="display:none"{/if}>&nbsp;&nbsp;&nbsp;&nbsp;<b>Reason</b></td>
	<td class="reason_box" {if $form.active}style="display:none"{/if}>
		<textarea class="reason" name="reason" rows="3" cols="40">{if !$form.active}{$form.reason.log}{/if}</textarea>
		<input type="hidden" name="log_reason" value="{$form.reason.log|escape:'html'}" />
	</td>
	</tr>
	</table>
	{if $form.reason.id}
	<font color="red" class="small">{if $form.reason.log}{$form.reason.log}{/if} by {$form.reason.u} on {$form.reason.timestamp}</font>
	{/if}
	</td>
</tr>

{assign var=item_id value=$form.bom_id|default:0}
<!-- Category Discount -->
<tr>
	<td valign="top"><b>Category Discount (%)</b>
		<a href="javascript:void(alert('This feature only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All) \n\nRequire privilege CATEGORY_DISCOUNT_EDIT to use this.'));">
			<img src="/ui/icons/information.png" align="absmiddle" />
		</a>
	</td>
	<td>
		{include file='masterfile_sku.edit.items.discount.tpl' is_edit=1 item_obj=$form}
	</td>
</tr>


<!-- Reward Point -->
<tr>
	<td valign="top"><b>Reward Point</b>
		<a href="javascript:void(alert('This feature only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All) \n\nRequire privilege MEMBER_POINT_REWARD_EDIT to use this.'));">
			<img src="/ui/icons/information.png" align="absmiddle" />
		</a>
	</td>
	<td>
		{include file='masterfile_sku.edit.items.point.tpl' is_edit=1 item_obj=$form}
	</td>
</tr>

{if $config.sku_non_returnable}
	<tr valign="top">
		<td nowrap><b>Non-returnable</b> <a href="javascript:void(alert('Turn on this will not allow this SKU to return at GRA'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></td>
		<td>
			<select name="non_returnable">
				<option value="-1" {if $form.non_returnable eq -1}selected {/if}>inherit (Follow SKU)</option>
				<option value="1" {if $form.non_returnable eq 1}selected {/if}>Yes</option>
				<option value="0" {if $form.non_returnable eq 0}selected {/if}>No</option>
			</select>
		</td>
	</tr>
{/if}

<tr>
	<td>
	<b>Weight in KG</b></td>
	<td>
	<input name="weight_kg" size=6 value="{$form.weight_kg|ifzero:''}">
	</td>
</tr>

<tr>
	<td>
	<b>Width</b></td>
	<td>
	<input name="width" size=6 value="{$form.width|ifzero:''}"> cm
	</td>
</tr>

<tr>
	<td>
	<b>Height</b></td>
	<td>
	<input name="height" size=6 value="{$form.height|ifzero:''}"> cm
	</td>
</tr>

<tr>
	<td>
	<b>Length</b></td>
	<td>
	<input name="length" size=6 value="{$form.length|ifzero:''}"> cm
	</td>
</tr>
{if $config.arms_marketplace_settings}
<tr>
	<td>
	<b>Marketplace<br>Description</b></td>
	<td>
	<textarea id="" cols="30" rows="5" name="marketplace_description">{$form.marketplace_description|escape}</textarea>
	</td>
</tr>
{/if}

</table>


<br><br>

<h4>BOM Content</h4>

{if $errm.item}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.item item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{if $BRANCH_CODE eq 'HQ'}
<ul>
<li>Latest GRN Cost is latest cost price from all branches.
<li>Avg Selling is Average of all branches's latest selling price.
<li>Click <img src="/ui/option_button.jpg" align=absmiddle> to view all branches's latest selling price.
</ul>
{/if}

<table width=100% id=tbl_item style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=1>
<thead>

<tr height=24 bgcolor=#ffffff>
	<th rowspan=2>#</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br>/MCode</th>
	<th nowrap>SKU Description</th>
	<th nowrap width=90>Last<br>Implode Cost</th>
	<th nowrap width=90>Last<br>Implode Selling</th>
	<th nowrap width=90>Latest<br>GRN Cost</th>
	<th nowrap width=90>Avg Selling</th>
	<th nowrap width=40>Qty</th>
	<th nowrap width=90 {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Total Cost</th>
	<th nowrap width=90>Total Value</th>
</tr>
</thead>

<tbody id="docs_items">
{foreach from=$bom_items item=item name=fitem}
<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="titem{$item.id}">
{include file=bom.new.row.tpl}
</tr>
{/foreach}
</tbody>

<tfoot>

{*
<tr class=r >
<th colspan=4>
Misc Cost
</th>
<td>
<input size=8 id=misc_cost name=misc_cost value="{$form.misc_cost|number_format:2}" onchange="calc_all();mf(this);" onclick="this.select();" class=r {if $form.disabled_edit}readonly{/if}>
</td>
<th colspan=4>&nbsp;</th>
</tr>
*}

<tr height=24 bgcolor=#ffffff>
	<th colspan=8 class="r">Total</th>
	<th class="r" id=total_qty>{$total_qty|qty_nf}</th>
	<th class="r" id=total_cost {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>{$total_cost}</th>
	<th class="r" id=total_sell>{$total_sell}</th>
</tr>
</tfoot>

</table>

{if !$form.disabled_edit}
{*
<table id=tbl_sku width=100% style="border:1px solid #999; padding:2px; background-color:#dddddd">
<tr class=normal>
<td nowrap>
<input name="sku_item_id" size=3 type=hidden>
<input name="sku_item_code" size=13 type=hidden>

<b>Search : </b><input id="autocomplete_sku" name="sku" size=35 onclick="this.select()">

<input id=btn_add_item type=button value="Add" onclick="add_item()" style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">

<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
<br>
<img src=ui/pixel.gif width=40 height=1>
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=1 checked> MCode &amp; {$config.link_code_name}
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=2> Article No
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=3> ARMS Code
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=4> Description
</td>
</tr>
</table>
*}
<div style="background:#ddd;border:1px solid #999;">
{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 block_is_bom=1}
</div>
{/if}

</div>

<p align=center>
<input type=button class="btn btn-primary" id=btn_confirm value="Save and Submit" onclick="do_confirm();">
{if $form.id}
<input type=button class="btn btn-primary" id=btn_print value="Print BOM List" onclick="do_print();">
<iframe name=_ifprint style="visibility:hidden" width=1 height=1></iframe>
{/if}
</p>

<script>
calc_all();
reset_row_no();
var bom_type = '{$form.bom_type}';
{if !$form.disabled_edit}
	{if !$form.description}
		document.f_a.description.value="{$default_description}";
	{/if}
	{if !$form.receipt_description}
		document.f_a.receipt_description.value="{$default_description}";
	{/if}
	reset_sku_autocomplete();
	document.f_a.description.focus();
{else}
	document.f_a.qty_bom.focus();
{/if}
</script>
