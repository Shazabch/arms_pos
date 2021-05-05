{*
REVISON HISTORY
===============
9/28/2007 12:27:33 PM gary
- under sku approval, show items with same artno in same category (to prevent duplicate application)

11/16/2007 1:57:39 PM gary
- add packing UOM.

3/22/2010 12:33:16 PM Andy
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

8/13/2010 10:14:09 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

6/13/2011 3:03:13 PM Andy
- Add "Allow decimal qty in GRN" at SKU. (currently will be disabled until GRN is enhanced)

6/24/2011 5:15:24 PM Justin
- Enabled "Allow decimal qty in GRN" at SKU.

9/14/2011 11:11:49 AM Alex
- Add article size data

10/10/2011 10:52:45 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

10/25/2011 12:16:29 PM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/17/2011 4:43:41 PM Andy
- Add show/allow user to key in "link code" at SKU Application if got config.sku_application_show_linkcode

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

5/7/2012 11:43:14 AM Andy
- Add "Category Discount (%)" and "Category Reward Point" can override by SKU.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

7/2/2012 5:07:23 PM Justin
- Added to show scale type by item.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

5/16/2013 4:05 PM Justin
- Enhanced to show Additional Description by config set.

5/20/2013 12:00 PM Fithri
- bugfix : receipt desc should be 40 chars max

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

4/3/2014 2:28 PM Justin
- Enhanced to allow user to view "PO Reorder Qty Min & Max" by SKU items.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

8/21/2014 1:49 PM Justin 
- Enhanced to show Input, Output & Inclusive Taxes.
- Enhanced to show GST (%) and selling price after/before GST.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

10/20/2014 3:20 PM Justin
- Enhanced to move "Open Price" checkbox to the top of Selling Price.

12/26/2014 9:30 AM Andy
- Fix GP does not calculate when no gst settings.

1/23/2015 2:18 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.

3/12/2015 2:37 PM Andy
- Enhanced to able to change input tax, output tax, inclusive tax when under approval screen.

3/19/2015 5:58 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

4/10/2015 10:48 AM Andy
- Fix sku inclusive tax use selecting real inclusive tax when inclusive tax is inherit.
- Enhance to update cost when user submit sku approval.

4/11/2015 11:19AM yinse
- fix sku_items loop not using real_inclusive_tax!

5/5/2015 10:35 AM Andy
- Enhanced to show GP % description.

7/20/2015 4:08 PM Andy
- Enhanced to show real inclusive tax/input tax/output tax when is inherit.
- Enhanced to auto update sku & sku_items gst inherit info.

7/29/2016 10:56 AM Andy
- Enhanced to show notice for "allow decimal qty".

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

4/21/2017 1:39 PM Justin
- Enhanced to have "Not Allow Discount".

4/25/2017 1:14 PM Khausalya
- Enhanced changes from RM to use config setting. 

5/11/2017 10:07 AM Justin
- Added notes for "Not Allow Discount" checkbox.

5/11/2017 11:04 AM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/18/2017 3:34 PM Justin
- Enhanced to show counter version requirements for "Not Allow Discount" feature.

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

5/2/2018 5:00 PM KUAN YEH
- Receipt Description no need to check last approval, change to always show at default
- Last approval can edit the receipt description.

5/28/2019 5:35 PM William
- Added new PO Reorder Qty "Moq".

7/4/2019 11:29 AM Justin
- Amended the notes for "Weight in KG" to include Self Checkout info.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.
- Enhanced to have a container to contain extra info.

2/28/2020 3:34 PM William
- Enhanced to added new column "Marketplace Description".

7/13/2020 5:19 PM William
- Enhanced to added new checkbox "Prompt when scan at POS Counter".

11/9/2020 5:35 PM Andy
- Enhanced to show Packing UOM for Parent SKU.

11/12/2020 2:39 PM Andy
- Added "Recommended Selling Price" (RSP) feature.

*}

{literal}
<style>
.ntc {
	font-size:0.8em;
	color:#666;
}
</style>
{/literal}
{if $smarty.section.i.index > 0}
<h5>Variety {$smarty.section.i.index} (#{$form.id})</h5>
{else}
<h5>Default Item (#{$form.id})</h5>
{/if}

<input type="hidden" name="item_id_list[{$smarty.section.i.index}]" value="{$items[i].id}" class="inp_item_id_list" />
<input name="item_type[{$items[i].id}]" value="variety" type="hidden" />

<table class="small" border=0 cellpadding=1 cellspacing=1>
<tr>
{if $items[i].artno}
	<td><b>Article No.</b></td>
	<td class=hilite>{$items[i].artno}{if $config.masterfile_disable_auto_explode_artno} {$items[i].artsize}{/if}
	{if $items[i].art_list }
	<br>
	<div class=ntc>
	Same Artno (
	{foreach from=$items[i].art_list item=list_item name=fitem}
	{if $smarty.foreach.fitem.iteration>1} ,{/if}
		<a href="/masterfile_sku_application.php?a=view&id={$list_item.sku_id}" target=_blank>
		{$list_item.id}
		</a>
	{/foreach}
	)
	</div>
	{/if}
	</td>
{/if}
{if $items[i].artsize && !$config.masterfile_disable_auto_explode_artno}
	<td><b>Article Size</b></td>
	<td class=hilite>{$items[i].artsize}</td>
{/if}
</tr>
{if $items[i].mcode}
<tr>
	<td><b>MCode</b></td>
	<td class=hilite>{$items[i].mcode}</td>
</tr>
{/if}

{if $config.sku_application_show_linkcode || $items[i].link_code}
	<tr>
		<td><b>{$config.link_code_name}</b></td>
		<td class=hilite>{$items[i].link_code|default:'&nbsp;'}</td>
	</tr>
{/if}

<tr>
	<td><b>Packing UOM</b></td>
	<td class="hilite">{$items[i].uom|default:'EACH'}</td>
</tr>


<tr>
	<td><b>Weight in KG <a href="javascript:void(alert('Weight in KG is use for Work Order module and Self Checkout Counter from v199 and above.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></b></td>
	<td>{$items[i].weight_kg|weight_nf}</td>
</tr>

<tr bgcolor="#eeeeee">
	<td valign="top"><b>Product Desc</b></td>
	{if $last_approval && $smarty.request.a eq 'approval'}
	<td nowrap colspan="7"><input class="normal" onblur="uc(this)" onchange="check_description(this);add_to_sku_receipt_desc('description[{$smarty.section.i.index}]','receipt_description[{$smarty.section.i.index}]')" size="80" id="description[{$smarty.section.i.index}]" name="description[{$items[i].id}]" value="{$items[i].description|escape}">
	{else}
	<td nowrap colspan="7" class="hilite">{$items[i].description}</b>
	{/if}
	</td>
</tr>

{if $config.enable_replacement_items}
	<tr>
	    <td><b>Replacement Item Group</b></td>
	    <td>{$items[i].ri_group_name|default:'-'}</td>
	</tr>
{/if}

<tr>
	<td valign="top"><b>Receipt Desc [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>
	{if $last_approval && $smarty.request.a eq 'approval'}
	<td nowrap colspan="7">
		<input class="normal" onblur="uc(this)" onchange="check_description(this); update_sku_receipt_desc(this);" size="50" maxlength="40" id="receipt_description[{$smarty.section.i.index}]" name="receipt_description[{$items[i].id}]" value="{$items[i].receipt_description|default:$items[i].description|escape}"> <img src="ui/rq.gif" align="absbottom" title="Required Field">
	</td>
	{else}
	<td nowrap colspan="7" class="hilite">
		{$items[i].receipt_description}
	</td>
    {/if}
</tr>

{if $config.sku_enable_additional_description}
	<tr>
		<td valign="top"><b>Additional Description</b></td>
		{if $last_approval && $smarty.request.a eq 'approval'}
			<td colspan="7">
				<input type="checkbox" name="additional_description_print_at_counter[{$items[i].id}]" value="1" {if $items[i].additional_description_print_at_counter}checked{/if} /> Print at Counter&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="additional_description_prompt_at_counter[{$items[i].id}]" value="1" {if $items[i].additional_description_prompt_at_counter}checked{/if} /> Prompt when scan at POS Counter<br />
				<textarea cols="45" rows="6" onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this);"{/if} name="additional_description[{$items[i].id}]">{$items[i].additional_description|escape|nl2br}</textarea>
			</td>
		{else}
			<td nowrap colspan="7" class=hilite>
				<img src="/ui/{if $items[i].additional_description_print_at_counter}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Print at Counter&nbsp;&nbsp;&nbsp;
				<img src="/ui/{if $items[i].additional_description_prompt_at_counter}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Prompt when scan at POS Counter<br />
				<hr />
				{$items[i].additional_description|escape|nl2br}
				<br />
			</td>
		{/if}
	</tr>
{/if}


{if $gst_settings}
	<tr>
		<td><b>Input Tax</b></td>
		<td colspan="5">
			{if $smarty.request.a eq 'approval'}
				<select name="dtl_input_tax[{$items[i].id}]" class="dtl_input_tax">
					<option value="-1" {if $items[i].input_tax eq -1}selected{/if} id="opt_si_inherit_cat_input_tax-{$items[i].id}" ori_text="Inherit (Follow SKU)">Inherit (Follow SKU)</option>
					{foreach from=$input_tax_list key=rid item=r}
						<option value="{$r.id}" {if $items[i].input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
			{else}
				{if $items[i].input_tax eq -1}
					Inherit (Follow SKU) {$item_r.input_tax_code} {$item_r.input_tax_rate|number_format}%
				{else}
					{foreach from=$input_tax_list key=rid item=r}
						{if $items[i].input_tax eq $r.id}{$r.code} - {$r.description}{/if}
					{/foreach}
				{/if}
			{/if}			
		</td>
	</tr>

	<tr>
		<td><b>Output Tax</b></td>
		<td colspan="5">
			{if $smarty.request.a eq 'approval'}
				<select name="dtl_output_tax[{$items[i].id}]" onchange="calc_gst('{$items[i].id}');" class="dtl_output_tax">
					<option value="-1" {if $items[i].output_tax eq -1}selected{/if} id="opt_si_inherit_cat_output_tax-{$items[i].id}" ori_text="Inherit (Follow SKU)">Inherit (Follow SKU)</option>
					{foreach from=$output_tax_list key=rid item=r}
						<option data-rate="{$r.rate}" value="{$r.id}" {if $items[i].output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
			{else}
				{if $items[i].output_tax eq -1}
					Inherit (Follow SKU) {$item_r.output_tax_code} {$item_r.output_tax_rate|number_format}%
				{else}
					{foreach from=$output_tax_list key=rid item=r}
						{if $items[i].output_tax eq $r.id}{$r.code} - {$r.description}{/if}
					{/foreach}
				{/if}
			{/if}			
		</td>
	</tr>

	<tr>
		<td><b>Selling Price Inclusive Tax</b></td>
		<td colspan="5">
			{if $smarty.request.a eq 'approval'}
				<span style="display:none;">
					<select name="dtl_inclusive_tax[{$items[i].id}]" onchange="calc_gst('{$items[i].id}');" class="dtl_inclusive_tax">
						<option value="inherit" {if $items[i].inclusive_tax eq "inherit"}selected {/if} id="opt_si_inherit_cat_inclusive_tax-{$items[i].id}" ori_text="Inherit (Follow SKU)">Inherit (Follow SKU)</option>
						<option value="yes" {if $items[i].inclusive_tax eq "yes"}selected {/if}>Yes</option>
						<option value="no" {if $items[i].inclusive_tax eq "no"}selected {/if}>No</option>
					</select>
				</span>				
			{/if}
			{if $items[i].inclusive_tax eq "inherit"}
				Inherit (Follow SKU) {$inherit_options[$item_r.real_inclusive_tax]}
			{else}
				{$items[i].inclusive_tax|strtoupper}
			{/if}
		</td>
	</tr>
{/if}

<tr>
	<td valign="top"><b>Selling Price Settings</b></td>
	<td colspan="7">
		<ul style="list-style:none;" id="ul_selling_price_settings-{$item_n}">
			<li><img src="/ui/{if $items[i].open_price}checked.gif{else}unchecked.gif{/if}" align="absmiddle" />&nbsp;Open Price</li>
			<li>
				<img src="/ui/{if $items[i].allow_selling_foc}checked.gif{else}unchecked.gif{/if}" align="absmiddle" />&nbsp;
				Allow Selling FOC
			</li>
			<li>
				<img src="/ui/{if $items[i].not_allow_disc}checked.gif{else}unchecked.gif{/if}" align="absmiddle" />&nbsp;Not Allow Discount <a href="javascript:void(alert('Please take note that this feature only applies to the POS Counter.\nAvailable for ARMS POS V.191 / ARMS POS BETA V310 and above.'))">[?]</a>
			</li>
		</ul>
	</td>
</tr>

{* Use RSP *}
<tr>
	<td valign="top"><b>RSP</b></td>
	<td colspan="7">
		<img src="/ui/{if $items[i].use_rsp}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Use Recommended Selling Price (RSP) Control
		
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

<tr bgcolor=#eeeeee>
	<td width=80><b>Selling Price</b></td>
	<td>
		<input name="selling_price[{$items[i].id}]" type="hidden" value="{$items[i].selling_price|number_format:2:".":""}" />
		{$config.arms_currency.symbol} {$items[i].selling_price|number_format:2}
		{if $items[i].allow_selling_foc and $items[i].selling_foc}
			<br />
			<img src="/ui/checked.gif" align="absmiddle" />
			(FOC)
		{/if}
	</td>
	{assign var=selling_price value=$items[i].selling_price}
	{if $gst_settings}
		<td width="70"><b>GST
			(<span id="span_gst_rate_{$items[i].id}">{$items[i].output_tax_rate|default:'0'}</span>%)</b>
		</td>
		<td>
			{if $items[i].real_inclusive_tax eq "yes"}
				{assign var=tmp_gst_rate value=$items[i].output_tax_rate+100}
				{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
				{assign var=gst_selling_price value=$gst_selling_price|round2}
				{assign var=gst_amt value=$gst_selling_price*$items[i].output_tax_rate/100}
				{assign var=gst_amt value=$gst_amt|round2}
			{else}
				{assign var=gst_amt value=$selling_price*$items[i].output_tax_rate/100}
				{assign var=gst_amt value=$gst_amt|round2}
				{assign var=gst_selling_price value=$selling_price+$gst_amt}
			{/if}
			{$config.arms_currency.symbol} <span id="span_gst_amt-{$items[i].id}">{$gst_amt|number_format:2}</span>
		</td>
		<td nowrap><b>Selling Price <br />
			<span id="span_gst_indicator_{$items[i].id}">{if $items[i].real_inclusive_tax eq 'yes'}Before{else}After{/if}</span>
			GST</b>
		</td>
		<td>
			{$config.arms_currency.symbol} 
			<span id="span_selling_price_gst-{$items[i].id}">{$gst_selling_price|number_format:2}</span>
			<input name="gst_selling_price[{$items[i].id}]" type="hidden" value="{$gst_selling_price|number_format:2:".":""}" />
		</td>
	{/if}
</tr>

{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Selling Price</b></td>
	<td>{$config.arms_currency.symbol} {$items[i].hq_selling|number_format:2:".":""}</td>
</tr>
{/if}

<tr>	
	<td width=80><b>Cost Price</b></td>
	<td width=80>
		<input type="hidden" name="cost_price[{$items[i].id}]" value="{$items[i].cost_price}" /> 
		{$config.arms_currency.symbol} <span id="span_cost_price-{$items[i].id}">{$items[i].cost_price|number_format:$config.global_cost_decimal_points}</span>
	
	</td>

	{if $gst_settings && $items[i].real_inclusive_tax eq "yes"}
		{assign var=gp_selling_price value=$gst_selling_price}
	{else}
		{assign var=gp_selling_price value=$selling_price}
	{/if}
	{assign var=gross value=`$gp_selling_price-$items[i].cost_price`}
	<td width=80><b>Gross Profit</b></td>
	<td width=80>{$config.arms_currency.symbol} 
		<span id="span_gp_amt-{$items[i].id}">{$gross|number_format:4}</span>
		<input name="gross[{$items[i].id}]" type="hidden" value="{$gross|number_format:4:".":""}" />
	</td>
	<td width=80><b>GP(%) [<a href="javascript:void(alert('{$LANG.SKU_GP_PER_LEGEND}'))">?</a>]</b></td>
	<td width=80>
		<span id="span_gp_per-{$items[i].id}">
		{if $gp_selling_price}
			{$gross*100/$gp_selling_price|number_format:4}
		{else}
			-
		{/if}
		</span>
	</td>
</tr>

{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Cost</b></td>
	<td>{$config.arms_currency.symbol} {$items[i].hq_cost|number_format:$config.global_cost_decimal_points}</td>
</tr>
{/if}

<tr>
	<td><b>Allow Decimal Qty</b> [<a href="javascript:void(alert('{$LANG.SKU_ALLOW_DECIMAL_NOTIFICATION|escape:javascript}'));">?</a>]</td>
	<td colspan="7">
		
		<img src="/ui/{if $items[i].decimal_qty}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Counter
		&nbsp;&nbsp;&nbsp;
		<img src="/ui/{if $items[i].doc_allow_decimal}checked.gif{else}unchecked.gif{/if}" align="absmiddle" /> Adjustment, DO, GRN, Sales Order, GRA, PO
	</td>
</tr>

<!-- Category Discount -->
<tr bgcolor="#eeeeee">
	<td valign="top"><b>Category Discount (%)</b></td>
	<td colspan="7">
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
	<td colspan="7">
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
		<td colspan="7">
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
	<td colspan="7">{$items[i].model|default:'-'}</td>
</tr>

<tr>
	<td><b>Width</b></td>
	<td colspan="7">{$items[i].width} cm</td>
</tr>

<tr>
	<td><b>Height</b></td>
	<td colspan="7">{$items[i].height} cm</td>
</tr>

<tr>
	<td><b>Length</b></td>
	<td colspan="7">{$items[i].length} cm</td>
</tr>

<!-- extra info -->
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

{if $form.po_reorder_by_child}
	<tr>
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
{/if}

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

<br>
{include file="masterfile_sku_approval.atom_photos.tpl"}
