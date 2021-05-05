{*
Revision History
================
4 Apr 2007 - yinsee
- 	check for $config.sku_application_require_multics to enable/disable multics code columns
- 	replace 'Multics Code' with $config.link_code_name

11/16/2007 5:06:43 PM gary
- add packing UOM.

1/11/2008 3:29:45 PM yinsee
- remove block_list from sku

4/16/2008 4:21:51 PM yinsee 
- fix bug where packing UOM always show EACH

5/22/2008 5:39:22 PM  yinsee
change the way pictures are linked

6/22/2009 3:40 PM Andy
- show HQ Cost if got $config.sku_listing_show_hq_cost and is HQ

9/9/2010 5:04:56 PM Andy
- Fix sku trade discount code cannot show in cosignment mode some module.

5/23/2011 10:48:53 AM Andy
- Add generate cache for thumb picture.

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache when view in popup.

6/13/2011 2:50:17 PM Andy
- Add "Allow decimal qty in GRN" at SKU. (currently will be disabled until GRN is enhanced)

6/20/2011 2:17:43 PM Andy
- Add no inventory, fresh market and scale type info at view sku page.

6/24/2011 5:15:24 PM Justin
- Enabled "Allow decimal qty in GRN" at SKU.

10/25/2011 11:57:17 AM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/21/2011 11:12:40 AM Andy
- Change to no use cache for photo preview.

5/11/2017 10:07 AM Justin
- Added notes for "Not Allow Discount" checkbox.

2/2/2012 4:04:18 PM Andy
- Show missing Serial No. Information.
- Show missing Stock Reorder Information.
- Show missing Replacement Group Information.

3/7/2012 4:34:32 PM Justin
- Added "Return Policy" for user to view by branch.

4/23/2012 4:44:12 PM Justin
- Added to show PO reorder qty by branch.

5/7/2012 10:33:18 AM Andy
- Add "Category Discount (%)" and "Category Reward Point" can override by SKU.

5/16/2012 11:12:32 AM Justin
- Fixed bugs that system show the form in fancy after add show PO reorder qty by branch.

6/12/2012 2:27:34 PM Justin
- Added to hide master cost if found current logged on branch was franchise.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

7/2/2012 5:07:23 PM Justin
- Added to show scale type by item.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

5/16/2013 4:05 PM Justin
- Enhanced to show Additional Description by config set.

07/12/2013 04:19 PM Justin
- Bug fixed on having wrong indication of have_sn, it should be 2=Yes instead of 1=Yes (pre-list).

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

12/19/2013 11:10 AM Andy
- Fix sku photo path if got special character will not able to show in popup.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour" and "Flavor" to "Flavour".

4/3/2014 2:28 PM Justin
- Enhanced to have "Notify" drown-down list for PO Reorder Qty.

4/21/2014 10:52 AM Justin
- Enhanced to show "Block item in GRN" info.

4/28/2014 11:03 AM Justin
- Enhanced to show "Block item in GRN" while config "check_block_grn_as_po" is turned on.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

8/20/2014 5:55 PM DingRen
- add Input Tax, Output Tax, Inclusive Tax
- add GST calculation function

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

10/20/2014 3:20 PM Justin
- Enhanced to move "Open Price" checkbox to the top of Selling Price.

1/23/2015 1:41 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.

5/5/2015 10:35 AM Andy
- Enhanced to show GP % description.

7/16/2015 10:31 AM Andy
- Enhanced to show real inclusive tax/input tax/output tax when is inherit.

7/29/2016 10:56 AM Andy
- Enhanced to show notice for "allow decimal qty".

4/20/2017 2:21 PM Khausalya 
- Enhanced changes from RM to use config setting. 

4/21/2017 1:39 PM Justin
- Enhanced to have "Not Allow Discount".

5/11/2017 10:07 AM Justin
- Added notes for "Not Allow Discount" checkbox.

5/18/2017 3:34 PM Justin
- Enhanced to show counter version requirements for "Not Allow Discount" feature.

6/19/2017 9:33 AM Qiu Ying
- Enhanced to show the latest cost

7/21/2017 15:31 Qiu Ying
- Bug fixed on showing Gross Profit without currency symbol
- Bug fixed on Gross Profit Shown in Different Decimal Places

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

10/23/2018 3:08 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

5/29/2019 11:00 AM William
- Added new PO Reorder Qty "Moq".

7/4/2019 11:29 AM Justin
- Amended the notes for "Weight in KG" to include Self Checkout info.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.
- Enhanced to have a container to contain extra info.

2/28/2020 11:54 AM William
- Enhanced to added new column "Marketplace Description".

05/15/2020 10:26 PM Sheila
- Updated button color

7/13/2020 5:19 PM William
- Enhanced to added new checkbox "Prompt when scan at POS Counter".

11/10/2020 5:36 PM Andy
- Enhanced to show Packing UOM for Parent SKU.

11/11/2020 4:35 PM Andy
- Added "Recommended Selling Price" (RSP) feature.

*}
{include file=header.tpl}
{literal}
<style>
.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var cat_id={$form.category_id};
var category_gst=null;

{if $gst_settings}
var is_gst_active = true;
{else}
var is_gst_active = false;
{/if}

var gst_rate_list = [];
{foreach from=$input_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
{/foreach}

{foreach from=$output_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
{/foreach}

{literal}
function load_category_GST() {

  new Ajax.Request(phpself+'?a=ajax_load_category_GST&id='+cat_id, {
    method:'get',
    onSuccess: function(transport){
      category_gst = JSON.parse(transport.responseText);

      calc_all_gst();
    }
  });
}

function calc_all_gst(){
  $$('input[data-item_id]').each(function(i) {
    var id=$(i).readAttribute('data-item_id');
    if (id!=undefined) {
      calc_gst(id);
    }
  });
}

function calc_gst(id){

  //master sku
  var mst_output_tax = document.f_a['mst_output_tax'].value;
  var mst_inclusive_tax = document.f_a['mst_inclusive_tax'].value;

  if (mst_output_tax==-1) mst_output_tax=gst_rate_list[category_gst['output_tax']]; // found it is inherit to category
  else mst_output_tax=gst_rate_list[mst_output_tax];

  if (mst_inclusive_tax=='inherit') mst_inclusive_tax=category_gst['inclusive_tax']; // found it is inherit to category

  var output_tax = document.f_a['dtl_output_tax['+id+']'].value;
  var inclusive_tax = document.f_a['dtl_inclusive_tax['+id+']'].value;

  if(output_tax == -1) gst_rate= float(mst_output_tax); // found it is inherit to master sku
  else gst_rate = float(gst_rate_list[output_tax]);

  if (inclusive_tax=='inherit') inclusive_tax=mst_inclusive_tax; // found it is inherit to master sku

  $('span_gst_rate_'+id).update(gst_rate);

  $('span_gst_indicator_'+id).update((inclusive_tax=='no')?"After":"Before");


	var selling_price = float(document.f_a['selling_price['+id+']'].value);
  
	if (inclusive_tax=='yes') {
		var selling_price_gst=(selling_price*100)/(100+gst_rate);
		var gst=float(selling_price_gst) * gst_rate / 100;
	}
	else{
		var gst=float(selling_price) * gst_rate / 100;
		var selling_price_gst=float(selling_price+gst);
	}
	
	var gp_selling_price = 0;
	if(inclusive_tax == 'yes'){
		gp_selling_price = float(round(selling_price_gst,2));
	}else{
		gp_selling_price = float(round(selling_price,2));
	}
	
	// gross profit amt
	var gross_amt = round(gp_selling_price - document.f_a["cost_price["+id+"]"].value, 4);
	$("gross_"+id).update(gross_amt);

	// gross profit percent
	var grossp = 0
	if(gp_selling_price != 0){
		grossp = float(gross_amt/gp_selling_price)*100;
	}

	$("grossp_"+id).update(round(grossp,4));
  
	$('gst_rate_'+id).update(round(gst,2));
	$('selling_price_gst_'+id).update(round(selling_price_gst,2));
}

if(is_gst_active) load_category_GST();
</script>
{/literal}
<form name="f_a" onsubmit="return false;">
<h1>SKU Master File</h1>
<div class="stdframe" style="background:#fff">

<h4>General Information</h4>

<table  border=0 cellpadding=4 cellspacing=0>
<tr>
	<td width=80><b>SKU</b></td>
	<td class="hilite">{$form.sku_code}0000</td>
</tr>
<tr>
	<td><b>Applying Branch</b></td>
	<td>{$form.branch_code}</td>
</tr>

<tr>
	<td width=80><b>Category</b></td>
	<td>{$form.cat_tree}</td>
</tr>
<tr>
	<td><b>SKU Type</b></td>
	<td>
	{assign var=sku_type value=$form.sku_type}
	{if $sku_type_list.$sku_type.code}
		{$sku_type_list.$sku_type.description}
	{else}
		{$sku_type}
	{/if}
	</td>
</tr>

{if $config.enable_no_inventory_sku}
	<!-- No Inventory-->
	<tr>
		<td><b>SKU Without Inventory</b></td>
		<td>{$inherit_options[$form.no_inventory]}</td>
	</tr>
{/if}

{if $config.enable_fresh_market_sku}
	<!-- Is Fresh Market SKU-->
	<tr>
		<td><b>Is Fresh Market SKU</b></td>
		<td>{$inherit_options[$form.is_fresh_market]}</td>
	</tr>
{/if}

{if !$config.consignment_modules}
	<!-- Scale Type -->
	<tr>
		<td><b>Scale Type</b></td>
		<td>{$scale_type_list[$form.scale_type]|default:'--'}</td>
	</tr>
{/if}

<tr>
	<td><b>Vendor</b></td>
	<td>{$form.vendor}</td>
</tr>
<tr>
	<td><b>Brand</b></td>
	<td>{$form.brand|default:'UNBRANDED'}</td>
</tr>
<tr>
	<td><b>Trade Discount</b></td>
	<td>
	    {if $config.sku_always_show_trade_discount} <!-- consignment part -->
	        {$form.default_trade_discount_code}
		{else}
			{if $form.trade_discount_type == 0}no trade discount
			{elseif $form.trade_discount_type == 1}use Brand Table
			{elseif $form.trade_discount_type == 2}use Vendor Table
			{/if}
	    {/if}
    </td>
</tr>
{if $form.trade_discount_type > 0}
<tr>
	<td><b>Trade Discount Table</b></td>
	<td>
	    <table  border=0 cellpadding=4 cellspacing=1>
		<tr>
		{section name=c loop=$trade_discount_table}
		<th>{if $form.default_trade_discount_code eq $trade_discount_table[c].code}<img src="/ui/checked.gif" align=absmiddle>{/if} {$trade_discount_table[c].code}</th>
		{/section}
		</tr>
		<tr>
		{section name=c loop=$trade_discount_table}
		{assign var=ccode value=`$trade_discount_table[c].code`}
		<td align=center>{$form.trade_discount_table.$ccode}</td>
		{/section}
		</tr>
	    </table>
	</td>
</tr>
{/if}
<tr>
	<td valign=top><b>Note</b></td>
	<td>{$form.note|escape|nl2br}</td>
	
</tr>
{if $config.sku_application_require_multics}
<tr>
	<td><b>{$config.link_code_name}</b></td>
	<td>
		<table class=body>
		<tr>
			<td><b>Department</b></td>
			<td>{$form.multics_dept}</td>
			<td><b>Section</b></td>
			<td>{$form.multics_section}</td>
			<td><b>Category</b></td>
			<td>{$form.multics_category}</td>
		</tr>
		<tr>
			<td><b>Brand</b></td>
			<td>{$form.multics_brand}</td>
			<td><b>Price Type</b></td>
			<td>{$form.multics_pricetype}</td>
		</tr>
		</table>
		{*
		<tr>
		    <td><b>Block item in PO</b></td>
		    <table>
		    <tr>
		    <!--6/13/2007 5:51:28 PM added by gary load block list -->
		    {foreach item=b from=$branch}
		    <td><input type=checkbox disabled {if $form.block_list[$b.id]}checked{/if}>
			{$b.code}
			</td>
		    {/foreach}
		    </tr>
		    </table>

		</tr>
		*}
	</td>
</tr>
{/if}
{if $config.enable_sn_bn}
<tr>
	<td><b>Use Serial No</b></td>
	<td>
		{if $form.have_sn eq '0'}
			No
		{elseif $form.have_sn eq '1'}
			Yes (Pre-list)
		{elseif $form.have_sn eq '2'}
			Yes
		{/if}
	</td>
</tr>
{/if}

<tr valign="top">
	<td><b>PO Reorder Qty</b></td>
	<td nowrap>
		{if $form.po_reorder_by_child}
			<img src="/ui/checked.gif" align="absmiddle" /> By Child
		{else}
			Min: {$form.po_reorder_qty_min|default:'-'}
			&nbsp;&nbsp;&nbsp;
			Max: {$form.po_reorder_qty_max|default:'-'}
			&nbsp;&nbsp;&nbsp;
			MOQ: {$form.po_reorder_moq|default:'-'}
			&nbsp;&nbsp;&nbsp;
			Notify Person: 
			{if $form.po_reorder_notify_user_id}
				{assign var=po_reorder_user_id value=$form.po_reorder_notify_user_id}
				{$po_reorder_users.$po_reorder_user_id.u}
			{/if}
			{if !$config.consignment_modules}
				&nbsp;&nbsp;&nbsp;
				<span id="qty_setup">
					{if $form.po_reorder_qty_by_branch.min || $form.po_reorder_qty_by_branch.max}
						<img src="ui/checked.gif" align="absmiddle">
					{else}
						<img src="ui/unchecked.gif" align="absmiddle">
					{/if} Overwrite PO Reorder qty by Branch
				</span>
				<br />
				<br />
				{if $form.po_reorder_qty_by_branch.min || $form.po_reorder_qty_by_branch.max}
					<div style="border:1px solid grey; overflow:auto; padding: 2px; width:80%;">
						<table width="100%">
							{foreach from=$branch_list key=bid item=b}
								<tr>
									<td width="1%"><b>{$b.code}</b></td>
									<td width="1%">Min: {$form.po_reorder_qty_by_branch.min.$bid|default:'0'}</td>
									<td width="1%">Max: {$form.po_reorder_qty_by_branch.max.$bid|default:'0'}</td>
									<td width="1%">MOQ: {$form.po_reorder_qty_by_branch.moq.$bid|default:'0'}</td>
								</tr>
							{/foreach}
						</table>
					</div>
				{/if}
			{/if}
		{/if}
	</td>
</tr>

{if $config.sku_non_returnable}
	<tr valign="top">
		<td nowrap><b>Non-returnable</b></td>
		<td>
			{if $form.group_non_returnable eq 0}No{else}Yes{/if}
		</td>
	</tr>
{/if}

{if $gst_settings}
<tr>
	<td><b>Input Tax</b></td>
	<td>
      <input type="hidden" name="mst_input_tax" value="{$form.mst_input_tax}"/>
		{if $form.mst_input_tax eq -1}Inherit (Follow Category) {$category_gst.input_tax_code} {$category_gst.input_tax_rate|number_format}%{/if}
        {foreach from=$input_tax_list key=rid item=r}
            {if $form.mst_input_tax eq $r.id}{$r.code} - {$r.description}{/if}
        {/foreach}
	</td>
</tr>
<tr>
	<td><b>Output Tax</b></td>
	<td>
      <input type="hidden" name="mst_output_tax" value="{$form.mst_output_tax}"/>
      {if $form.mst_output_tax eq -1}Inherit (Follow Category) {$category_gst.output_tax_code} {$category_gst.output_tax_rate|number_format}%{/if}
      {foreach from=$output_tax_list key=rid item=r}
          {if $form.mst_output_tax eq $r.id}{$r.code} - {$r.description}{/if}
      {/foreach}
	</td>
</tr>
<tr>
	<td><b>Inclusive Tax</b></td>
	<td>
      <input type="hidden" name="mst_inclusive_tax" value="{$form.mst_inclusive_tax}"/>
      {foreach from=$inherit_options key=k item=val}
      {if $form.mst_inclusive_tax eq $k}{$val} {if $k eq 'inherit'}{$inherit_options[$category_gst.inclusive_tax]}{/if}{/if}
      {/foreach}
	</td>
</tr>
{/if}

</table></div>
<br>

{section name=i loop=$items}
{assign var=item_id value=`$items[i].id`}
{assign var=item_r value=$items[i]}

<input type="hidden" data-item_id="{$item_id}" name="artno[{$item_id}]" value="{$items[i].artno}{if $config.masterfile_disable_auto_explode_artno && $items[i].artsize} {$items[i].artsize}{/if}"/>
<input type="hidden" name="dtl_input_tax[{$item_id}]" value="{$items[i].input_tax}"/>
<input type="hidden" name="dtl_output_tax[{$item_id}]" value="{$items[i].output_tax}"/>
<input type="hidden" name="dtl_inclusive_tax[{$item_id}]" value="{$items[i].inclusive_tax}"/>
<input type="hidden" name="selling_price[{$item_id}]" value="{$items[i].selling_price|number_format:2:".":""}"/>
<input type="hidden" name="cost_price[{$item_id}]" value="{$items[i].cost_price}" />


<div class="stdframe" style="margin-bottom:10px">

<h3>SKU Item {$items[i].sku_item_code}</h3>

<table  border=0 cellpadding=4 cellspacing=0>
<tr>
	<td><b>Article No.</b></td>
	<td>{$items[i].artno}</td>
</tr>
<tr>
	<td><b>Manufacturer's Code</b></td>
	<td>{$items[i].mcode}</td>
</tr>

<tr>
	<td><b>Packing UOM</b></td>
	<td>{$items[i].uom|default:'EACH'}</td>
</tr>


<tr>
	<td><b>Weight in KG <a href="javascript:void(alert('Weight in KG is use for Work Order module and Self Checkout Counter from v199 and above.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></b></td>
	<td>{$items[i].weight_kg|weight_nf}</td>
</tr>

<tr>
	<td><b>{$config.link_code_name}</b></td>
	<td>{$items[i].link_code}</td>
</tr>

<tr>
	<td><b>Remark</b></td>
	<td nowrap colspan=3>
	(Weight Description):&nbsp;&nbsp;{$items[i].weight|default:"-"}&nbsp;&nbsp;&nbsp;&nbsp;
	(Sz):&nbsp;&nbsp;{$items[i].size|default:"-"}&nbsp;&nbsp;&nbsp;&nbsp;
	(Colour):&nbsp;&nbsp;{$items[i].color|default:"-"}&nbsp;&nbsp;&nbsp;&nbsp;
	(Flavour):&nbsp;&nbsp;{$items[i].flavor|default:"-"}&nbsp;&nbsp;&nbsp;&nbsp;
	(Misc):&nbsp;&nbsp;{$items[i].misc|default:"-"}&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
</tr>
<tr>
	<td valign=top><b>Product Description</b></td>
	<td colspan=3>{$items[i].description}</td>
</tr>
<tr>
	<td valign=top><b>Receipt Description</b></td>
	<td colspan=3>{$items[i].receipt_description|escape}</td>
</tr>
{if $config.sku_enable_additional_description}
	<tr>
		<td valign="top"><b>Additional Description</b></td>
		<td nowrap colspan="3">
			<img src="/ui/{if $items[i].additional_description_print_at_counter}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Print at Counter
			&nbsp;&nbsp;&nbsp;
			<img src="/ui/{if $items[i].additional_description_prompt_at_counter}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Prompt when scan at POS Counter<br />
			{$items[i].additional_description|escape|nl2br}
			<br />
		</td>
	</tr>
{/if}

{if $gst_settings}
<tr>
	<td><b>Input Tax</b></td>
	<td>
      {if $items[i].input_tax eq -1}Inherit (Follow SKU) {$item_r.input_gst_code} {$item_r.input_gst_rate|number_format}%{/if}
      {foreach from=$input_tax_list key=rid item=r}
        {if $items[i].input_tax eq $r.id}{$r.code} - {$r.description}{/if}
      {/foreach}
	</td>
</tr>
<tr>
	<td><b>Output Tax</b></td>
	<td>
		{if $items[i].output_tax eq -1}Inherit (Follow SKU) {$item_r.output_gst_code} {$item_r.output_gst_rate|number_format}%{/if}
        {foreach from=$output_tax_list key=rid item=r}
          {if $items[i].output_tax eq $r.id}{$r.code} - {$r.description}{/if}
        {/foreach}
	</td>
</tr>

<tr>
	<td><b>Inclusive Tax</b></td>
	<td>
		{if $items[i].inclusive_tax eq "inherit"}Inherit (Follow SKU) {$inherit_options[$item_r.real_inclusive_tax]}
        {elseif $items[i].inclusive_tax eq "yes"}Yes
        {elseif $items[i].inclusive_tax eq "no"}No{/if}
		</select>
	</td>
</tr>
{/if}

<tr>
	<td valign="top"><b>Selling Price Settings</b></td>
	<td>
		<ul style="list-style:none;" id="ul_selling_price_settings-{$item_id}">
			<li><input type=checkbox name="open_price[{$item_id}]" {if $items[i].open_price}checked{/if} disabled />&nbsp;Open Price</li>
			<li>
				<input type="checkbox" disabled name="allow_selling_foc[{$item_id}]" value="1" {if $items[i].allow_selling_foc}checked {/if} />&nbsp;
				Allow Selling FOC
			</li>
			<li>
				<input type="checkbox" disabled name="not_allow_disc[{$item_id}]" {if $items[i].not_allow_disc}checked{/if} value="1" />&nbsp;Not Allow Discount <a href="javascript:void(alert('Please take note that this feature only applies to the POS Counter.\nAvailable for ARMS POS V.191 / ARMS POS BETA V310 and above.'))">[?]</a>
			</li>
		</ul>
	</td>
</tr>

{* Use RSP *}
<tr>
	<td valign="top"><b>RSP</b></td>
	<td colspan="7">
		<input type="checkbox" {if $items[i].use_rsp}checked{/if} disabled /> Use Recommended Selling Price (RSP) Control
		
		{if $items[i].use_rsp}
			<br />
			<table>
				<tr>
					<td><b>RSP</b></td>
					<td>{$config.arms_currency.symbol}</td>
					<td>
						{$items[i].rsp_price|number_format:2}
					</td>
				</tr>
				<tr>
					<td><b>RSP Discount</b></td>
					<td>&nbsp;</td>
					<td>
						{$items[i].rsp_discount}
					</td>
				</tr>
			</table>
		{/if}
	</td>
</tr>

<tr>
	<td width=100><b>Selling Price</b></td>
	<td>
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td width="150">
				{$config.arms_currency.symbol}{$items[i].selling_price|number_format:2}
				{if $items[i].allow_selling_foc}
					<input type="checkbox" name="selling_foc[{$item_id}]" value="1" {if $items[i].allow_selling_foc and $items[i].selling_foc}checked {/if} disabled /> <b>FOC</b>
				{/if}
			</td>
			{if $gst_settings}
				<td width="70"><b>GST (<span id="span_gst_rate_{$item_id}">0</span>%)</b></td>
				<td>{$config.arms_currency.symbol}<span id="gst_rate_{$item_id}">0.00</span></td>
			{/if}
		</tr>
		</table>
	</td>
	{if $gst_settings}
		<td nowrap><b>Selling Price <span id="span_gst_indicator_{$item_id}">Before</span> GST</b></td>
		<td>{$config.arms_currency.symbol}<span id="selling_price_gst_{$item_id}">0.00</span></td>
	{/if}
</tr>

{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Selling Price</b></td>
	<td>{$config.arms_currency.symbol}{$items[i].hq_selling|number_format:2:".":""}</td>
</tr>
{/if}
{if $sessioninfo.show_cost && $sessioninfo.branch_type ne "franchise"}
<tr>
	<td width=100><b>Cost Price</b></td>
	<td>
		{$config.arms_currency.symbol}{$items[i].cost_price|number_format:4}
	</td>
</tr>
<tr>

<tr>
	<td width="100" valign="top"><b>Latest Cost</b></td>
	<td colspan="3">
		{assign var=login_bid value=$sessioninfo.branch_id}	
		{$config.arms_currency.symbol}{$items[i].all_branch_cost.$BRANCH_CODE.latest_cost|number_format:4}
		{if $BRANCH_CODE eq 'HQ'}
			&nbsp;
			<span>
				<img onclick="togglediv('more_branch_latest_cost_{$item_id}',this);" src="/ui/expand.gif"> Other Branch's Latest Cost
			</span>
		{/if}
		<table id="more_branch_latest_cost_{$item_id}" width="100%" style="border-collapse:collapse;border:1px solid black;display:none;background-color:#e4d8fa">
			{assign var=total_count value=$items[i].all_branch_cost|@count}
			{assign var=loop_count value=0}
			{assign var=num value=0}
			{assign var=no_now value=3}
			{if $total_count-1 < 3}
				{assign var=loop_count value=$total_count-1}
			{else}
				{assign var=loop_count value=3}
			{/if}
			<tr>
				{section name=header_latest_cost start=0 loop=$loop_count step=1}
					<th align="left" style="padding-left:10px">Branch</th>
					<th align="right" style="border-right:1px solid black;padding-right:10px">Latest Cost ({$config.arms_currency.symbol})</th>
				{/section}
			</tr>
			{foreach from=$items[i].all_branch_cost key=k item=data name=branch_latest_cost}
				{if $data.code neq 'HQ'}
					{assign var=num value=$num+1}
					{if $num eq 1 or $num eq $no_now+1}<tr>{/if}
					
					<td style="padding-left:10px">{$data.code}</td>
					<td align="right" style="border-right:1px solid black;padding-right:10px">{$data.latest_cost|number_format:4}</td>
					
					{if $num eq $no_now or $num eq $total_count-1}</tr>{/if}
					
					{if $num eq $no_now}
						{assign var=no_now value=$num+3}
					{/if}
				{/if}
			{/foreach}
		</table>
	</td>
</tr>

{assign var=gross value=`$items[i].selling_price-$items[i].cost_price`}
	<td><b>Gross Profit</b></td>
	<td>{$config.arms_currency.symbol}<span id="gross_{$item_id}">{$gross|number_format:3}</span></td>
	<td><b>GP(%) [<a href="javascript:void(alert('{$LANG.SKU_GP_PER_LEGEND}'))">?</a>]</b></td>
	<td id="grossp_{$item_id}">
		{if $items[i].selling_price}
			{$gross/$items[i].selling_price*100|number_format:2}%
		{else}
			-
		{/if}
	</td>
</tr>
{/if}

{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SHOW_COST}
<tr>
	<td width="100"><b>HQ Cost</b></td>
	<td>{$config.arms_currency.symbol}{$items[i].hq_cost|number_format:2:".":""}</td>
</tr>
{/if}
<tr>
    <td><b>Block item in PO</b></td>
    <td colspan="3">
    {*<table>
    <tr>
    <!--6/13/2007 5:51:28 PM added by gary load block list -->
    {foreach item=b from=$branch}
    <td><input type=checkbox disabled {if $items[i].block_list[$b.id]}checked{/if}>
	{$b.code}
	</td>
    {/foreach}
    </tr>
    </table>*}
    {foreach item=b from=$branch}
    <span style="white-space:nowrap"><input type=checkbox  disabled {if $items[i].block_list[$b.id]}checked{/if}>&nbsp;{$b.code}</span>
    {/foreach}
	</td>
</tr>

{if !$config.check_block_grn_as_po}
	<tr>
		<td><b>Block item in GRN</b></td>
		<td colspan="3">
		{*<table>
		<tr>
		<!--6/13/2007 5:51:28 PM added by gary load block list -->
		{foreach item=b from=$branch}
		<td><input type=checkbox disabled {if $items[i].doc_block_list.grn[$b.id]}checked{/if}>
		{$b.code}
		</td>
		{/foreach}
		</tr>
		</table>*}
		{foreach item=b from=$branch}
		<span style="white-space:nowrap"><input type=checkbox disabled {if $items[i].doc_block_list.grn[$b.id]}checked{/if}>&nbsp;{$b.code}</span>
		{/foreach}
		</td>
	</tr>
{/if}

<tr>
	<td><b>Active</b></td>
	<td><input type=checkbox id=active_{$item_id} name="active[{$item_id}]" {if $items[i].active}checked{/if} disabled>
	{if $items[i].reason.log <> ''}
	<font color=red class=small>{$items[i].reason.log} by {$items[i].reason.u} on {$items[i].reason.timestamp}</font>
	{/if}
	</td>
</tr>

<tr>
	<td><b>Allow Decimal Qty</b> [<a href="javascript:void(alert('{$LANG.SKU_ALLOW_DECIMAL_NOTIFICATION|escape:javascript}'));">?</a>]</td>
	<td>
		<input type="checkbox" name="decimal_qty[{$item_id}]" {if $items[i].decimal_qty}checked {/if} value="1" disabled /> Counter
		&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="doc_allow_decimal[{$item_id}]" {if $items[i].doc_allow_decimal}checked {/if} value="1" disabled /> Adjustment, DO, GRN, Sales Order, GRA, PO
	</td>
</tr>

{if $config.enable_replacement_items}
	<tr>
	    <td><b>Replacement Item Group</b></td>
	    <td>{$items[i].ri_group_name|default:'-'}</td>
	</tr>
{/if}


<!-- Category Discount -->
<tr>
	<td valign="top"><b>Category Discount (%)</b></td>
	<td>
		{assign var=inh value=$items[i].cat_disc_inherit}
		{$discount_inherit_options.$inh}
		{if $inh eq 'set'}
			{include file='masterfile_sku.edit.items.discount.tpl' item_obj=$items[i]}
		{/if}
	</td>
</tr>

<!-- Reward Point -->
<tr>
	<td valign="top"><b>Reward Point</b></td>
	<td>
		{assign var=inh value=$items[i].category_point_inherit}
		{$category_point_inherit_options.$inh}
		{if $inh eq 'set'}
			{include file='masterfile_sku.edit.items.point.tpl' item_obj=$items[i]}
		{/if}
	</td>
</tr>

{if !$config.consignment_modules}
	<!-- Scale Type -->
	<tr>
		<td><b>Scale Type</b></td>
		<td>
			{assign var=curr_scale_type value=$items[i].scale_type}
			{$scale_type_list[$curr_scale_type]|default:'--'}
		</td>
	</tr>
{/if}

{if $config.sku_non_returnable}
	<tr valign="top">
		<td nowrap><b>Non-returnable</b></td>
		<td>
			{if $items[i].non_returnable eq -1}
				inherit (Follow SKU)
			{elseif $items[i].non_returnable eq 0}
				No
			{else}
				Yes
			{/if}
		</td>
	</tr>
{/if}

<tr>
	<td><b>Model</b></td>
	<td colspan="7">{$items[i].model}</td>
</tr>

<tr>
	<td><b>Width</b></td>
	<td colspan="7">{$items[i].width}</td>
</tr>

<tr>
	<td><b>Height</b></td>
	<td colspan="7">{$items[i].height}</td>
</tr>

<tr>
	<td><b>Length</b></td>
	<td colspan="7">{$items[i].length}</td>
</tr>

{if $sku_extra_info}
	<tr>
		<td colspan="2">
			<fieldset>
				<legend><b>Extra Info</b></legend>
				<table>
					{foreach from=$sku_extra_info key=c item=extra_info}
						<tr>
							<td width="85"><b>{$extra_info.description}</b></td>
							<td>
								{if $extra_info.input_type eq 'text'}
									{$items[i].extra_info.$c}
								{/if}
							</td>
						</tr>
					{/foreach}
				</table>
			</fieldset>
		</td>
	</tr>
{/if}

<tr valign="top">
	<td><b>PO Reorder Qty</b></td>
	<td nowrap>
	    Min: {$items[i].po_reorder_qty_min|default:'-'}
	    &nbsp;&nbsp;&nbsp;
	    Max: {$items[i].po_reorder_qty_max|default:'-'}
		&nbsp;&nbsp;&nbsp;
	    MOQ: {$items[i].po_reorder_moq|default:'-'}
		&nbsp;&nbsp;&nbsp;
		Notify Person: 
		{if $items[i].po_reorder_notify_user_id}
			{assign var=po_reorder_user_id value=$items[i].po_reorder_notify_user_id}
			{$po_reorder_users.$po_reorder_user_id.u}
		{else}
			-
		{/if}
	</td>
</tr>

{if $config.enable_sn_bn}
	<tr>
		<td><b>Warranty Period</b></td>
		<td nowrap>
			{if $items[i].sn_we}
				{$items[i].sn_we} {$items[i].sn_we_type|ucwords}(s)
			{else}
				-
			{/if}
		</td>
	</tr>
{/if}

{if $sessioninfo.privilege.MST_INTERNAL_DESCRIPTION}
	<tr>
		<td valign="top"><b>Internal Description</b></td>
		<td nowrap>{$items[i].internal_description|escape|nl2br}</td>
	</tr>
{/if}
{if $config.arms_marketplace_settings}
	<tr>
		<td valign="top"><b>Marketplace Description</b></td>
		<td nowrap>{$items[i].marketplace_description|escape|nl2br}</td>
	</tr>
{/if}
</table>

{if $items[i].photo_count > 0 or $items[i].photos}
<!-- display previously uploaded images -->
<h5>Photos</h5>
{*
{section name=loop start=1 loop=`$items[i].photo_count+1`}
{capture assign=p}{$image_path}sku_photos/{$items[i].sku_apply_items_id}/{$smarty.section.loop.iteration}.jpg{/capture}
<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&img={$p|urlencode}" border=1 style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View">
<!--/div-->
{/section}
*}

{get_sku_apply_photos sku_apply_items_id=$items[i].sku_apply_items_id assign=images_list}
{if $images_list}
    <!-- display previously uploaded images -->
	{foreach from=$images_list item=p name=loop}
	    <img width="100" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=220&h=200&cache=0&img={$p|urlencode}" border=0 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View" />
	{/foreach}
	<div style="clear:both"></div>
	<br>
{/if}

{foreach from=$items[i].photos item=p name=i}
<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View">
{/foreach}

<div style="clear:both"></div>
<br>
{/if}

</div>
<br>
{/section}

<br>

<p align=center>
<input class="btn btn-warning" type=button onclick="close_window('/masterfile_sku.php')" value="Close">
</p>

</form>
{include file=footer.tpl}
