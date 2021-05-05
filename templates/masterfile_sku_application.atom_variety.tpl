{*
Revision History
================
12 Apr 2007 - yinsee
- add back the "Delete" option, but only show when Variety is enabled

6/7/2007 10:41:11 AM - yinsee
- ignore variety, Delete button always show

6/27/2007 1:42:14 PM - yinsee
- add "sku_application_allow_no_artno_mcode" check

9/14/2007 10:33:49 AM -gary
- selling_price and cost_price remove the "," from number_format.

11/16/2007 1:57:58 PM gary
- add packing UOM.

7/28/2009 1:43:12 PM Andy
- add ctn 1 and ctn 2

3/19/2010 5:25:25 PM Andy
- Automatically show receipt description if user is the last approval
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

6/2/2010 9:58:23 AM Alex
- Add maxlenth for description

12/9/2010 3:42:28 PM Justin
- Adjusted the Replacement Item Group row and place at the end of sku table.

5/18/2011 1:43:44 PM Alex
- split article no to art no and size
- change article no to read only and get data $config.ci_auto_gen_artno 

5/27/2011 10:08:59 AM Alex
- move checking artno function from article no to article size

6/6/2011 10:01:15 AM Alex
- change Article No. to uppercase

6/13/2011 3:15:24 PM Andy
- Add "Allow decimal qty in GRN" at SKU. (currently will be disabled until GRN is enhanced)

6/24/2011 5:15:24 PM Justin
- Enabled "Allow decimal qty in GRN" at SKU.

8/15/2011 9:29:11 AM Alex
- remove $config.ci_auto_gen_artno

9/12/2011 7:32:17 PM Alex
- add checking $config.masterfile_disable_auto_explode_artno for artno

10/25/2011 11:46:42 AM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/17/2011 4:35:40 PM Andy
- Add show/allow user to key in "link code" at SKU Application if got config.sku_application_show_linkcode

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

3/7/2012 4:34:32 PM Justin
- Added "Return Policy" for user to maintain by branch.

3/21/2012 4:42:43 PM Justin
- Added a new config "masterfile_enable_check_desc" to enable/disable whether need to do checking for product/receipt description for quote (").

3/23/2012 9:57:43 AM Justin
- Changed the config name "masterfile_enable_check_desc" into "masterfile_disallow_double_quote".

5/7/2012 11:46:25 AM Andy
- Add "Category Discount (%)" and "Category Reward Point" can override by SKU.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

7/2/2012 5:09:23 PM Justin
- Added new field "Scale Type" for user to maintain by item.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

5/16/2013 4:05 PM Justin
- Enhanced to show Additional Description by config set.

5/20/2013 12:00 PM Fithri
- bugfix : receipt desc should be 40 chars max

8/29/2013 5:17 PM Fithri
- automatically strips out character that is not 0-9,a-Z,-_/ and space in artno field

9/3/2013 2:50 PM Fithri
- add checking whether allow special chars in artno

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour" and "Flavor" to "Flavour".

4/3/2014 2:28 PM Justin
- Enhanced to allow user maintain "PO Reorder Qty Min & Max" by SKU items.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

7/10/2014 9:57 AM Justin
- Enhanced to have max length for MCode, Old Code and Artno.

8/21/2014 1:49 PM Justin 
- Enhanced to have Input, Output & Inclusive Taxes.
- Enhanced to have GST (%) and selling price after/before GST.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

9/22/2014 4:24 PM Fithri
- when edit or create new SKU, need to select scale Type as "Inherit" when add child.

9/25/2014 11:59 AM Justin
- Enhanced to show GST description from drop down list in full.

10/20/2014 3:20 PM Justin
- Enhanced to move "Open Price" checkbox to the top of Selling Price.
- Enhanced to skip zero selling price and selling below cost price errors while Open Price is checked.

1/2/2015 4:43 PM Justin
- Enhanced to show GST inherit information.

1/23/2015 2:18 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.
- Enhance Open Price/Allow Selling FOC checking.
- Change the selling price must always >0 except is open price.

3/19/2015 5:58 PM Andy
- Fix wrong gp calculation.
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

3/26/2015 4:54 PM Andy
- Fix sku item edit cost by percentage bug.

4/17/2015 10:40 AM Andy
- Increase the artno maxlength from 20 to 30.

5/5/2015 10:35 AM Andy
- Enhanced to show GP % description.

7/14/2016 1:21 PM Andy
- Fixed there is always a "-1" word at scale type label.

7/29/2016 10:56 AM Andy
- Enhanced to show notice for "allow decimal qty".

9/6/2016 9:18 AM Qiu Ying
- Increase the old code/link code maxlength from 12 to 20

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

4/21/2017 1:39 PM Justin
- Enhanced to have "Not Allow Discount".

4/21/2017 2:08 PM Khausalya
- Enhanced changes from RM to use config setting. 

5/11/2017 10:07 AM Justin
- Added notes for "Not Allow Discount" checkbox.

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/18/2017 3:34 PM Justin
- Enhanced to show counter version requirements for "Not Allow Discount" feature.

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".
- Enhanced to show notification icon for existing Weight settings.

5/3/2018 11:00 PM KUAN YEH
- Receipt Description no need to check last approval, change to always show at default

5/28/2019 5:23 PM William
- Added new PO Reorder Qty "Moq".

7/4/2019 11:29 AM Justin
- Amended the notes for "Weight in KG" to include Self Checkout info.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.
- Enhanced to have a container to contain extra info.

2/28/2020 3:35 PM William
- Enhanced to added new column "Marketplace Description".

7/13/2020 3:50 PM William
- Enhanced to added new checkbox "Prompt when scan at POS Counter".

11/9/2020 5:35 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.

11/11/2020 4:35 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
*}

{if $item_n}
<div style="float:right"><a class="remove_sku" href="javascript:void(cancel_item('item[{$item_n}]',{$items[$item_n].id|default:0}))"><img src=/ui/del.png align=absmiddle border=0> Delete</a></div>
<h3>Variety {$item_n}</h3>
{else}
<h3>Default Item</h3>
{/if}

{if $errm.items[$item_n]}
<div class=errmsg><ul>
{foreach from=$errm.items[$item_n] item=e}
<li> {$e}
{/foreach}
</ul></div>
{/if}

<input name="item_type[{$item_n|default:0}]" value="variety" type=hidden>
<table  border=0 cellpadding=2 cellspacing=1>
<tr>
	<td><b>Article No.</b></td>
	<td><input class="input_artno" item_id="{$item_n}" name="artno[{$item_n|default:0}]"
		onchange="this.value=this.value.trim().toUpperCase();{if !$config.sku_artno_allow_specialchars}correct_artno(this);{/if}{if $config.masterfile_disable_auto_explode_artno}check_artmcode(this,'artno','{$item_n}'){/if}"
		value="{$items[$item_n].artno}{if $config.masterfile_disable_auto_explode_artno && $items[$item_n].artsize} {$items[$item_n].artsize}{/if}" maxlength="30">
		{if !$config.sku_application_allow_no_artno_mcode}<img src=ui/rq.gif align=absbottom title="Required Field">{/if}
		{if !$config.masterfile_disable_auto_explode_artno}
			&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Article Size</b>&nbsp;&nbsp;
			<input onchange="this.value=this.value.trim().toUpperCase();check_artmcode(this,'artno','{$item_n}')" name="artsize[{$item_n|default:0}]" value="{$items[$item_n].artsize}">
		{/if}
	</td>
</tr>
<tr>
	<td><b>Manufacturer's Code</b></td>
	<td><input onchange="check_artmcode(this,'mcode','{$item_n}')" name="mcode[{$item_n|default:0}]" value="{$items[$item_n].mcode}" maxlength="15"> {if !$config.sku_application_allow_no_artno_mcode}<img src=ui/rq.gif align=absbottom title="Required Field">{/if}</td>
</tr>

{if $config.sku_application_show_linkcode}
	<tr>
		<td><b>{$config.link_code_name}</b></td>
		<td>
			<input name="link_code[{$item_n|default:0}]" value="{$items[$item_n].link_code}" maxlength="20" />
		</td>
	</tr>
{/if}


<tr>
	<td><b>Packing UOM</b></td>
	<td>
	<select name="packing_uom_id[{$item_n}]" onchange="calc_weight_kg({$item_n});">
	{section name=j loop=$uom}
		{if $item_n > 0 or ($item_n eq 0 and $uom[j].fraction eq 1)}
			<option value="{$uom[j].id}" {if $uom[j].id==$items[$item_n].packing_uom_id or ($items[$item_n].packing_uom_id==0 and $uom[j].code eq 'EACH')}selected{/if} uom_fraction="{$uom[j].fraction|default:1}">
				{$uom[j].code}
			</option>
		{/if}
	{/section}
	</select>
	</td>
</tr>


<tr>
	<td><b>Weight in KG <a href="javascript:void(alert('Weight in KG is use for Work Order module and Self Checkout Counter from v199 and above.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></b></td>
	<td>
		<input type="text" name="weight_kg[{$item_n|default:0}]" value="{$items[$item_n].weight_kg|weight_nf}" size="10" maxlength="15" {if $item_n ne 0}readonly{else}onchange="mf(this, {$config.global_weight_decimal_points}); calc_weight_kg();"{/if} />
	</td>
</tr>

<tr>
	<td valign=top><b>Remark</b></td>
	<td nowrap colspan=3>
	<input name="description0[{$item_n|default:0}]" value="{$items[$item_n].description0|escape}" type=hidden>
	(Weight Description) <a href="javascript:void(alert('This weight settings is used for display purpose only.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a>&nbsp;<input onchange="uc(this);atom_update_fulldesc({$item_n|default:0})" size=10 maxlength=15  name="description1[{$item_n|default:0}]" value="{$items[$item_n].description1|escape}">
	(Sz) <input id="autocomplete_size_{$item_n}" onblur="uc(this);atom_update_fulldesc({$item_n|default:0})" onkeydown="autocomplete_color_size_variety('size',{$item_n})" size=10 maxlength=15 name="description2[{$item_n|default:0}]" value="{$items[$item_n].description2|escape}" autocomplete="off">
		<div id="div_autocomplete_size_choices_{$item_n}" class="autocomplete" style="display:none;"></div>
	(Colour)<input id="autocomplete_color_{$item_n}" onblur="uc(this);atom_update_fulldesc({$item_n|default:0});" onkeydown="autocomplete_color_size_variety('color',{$item_n});" size=10 maxlength=15 name="description3[{$item_n|default:0}]" value="{$items[$item_n].description3|escape}" autocomplete="off">
		<div id="div_autocomplete_color_choices_{$item_n}" class="autocomplete" style="display:none;" ></div>
	(Flavour)<input onchange="uc(this);atom_update_fulldesc({$item_n|default:0})" size=10 maxlength=15 name="description4[{$item_n|default:0}]" value="{$items[$item_n].description4|escape}">
	(Misc) <input onchange="uc(this);atom_update_fulldesc({$item_n|default:0})" size=20 maxlength=30 name="description5[{$item_n|default:0}]" value="{$items[$item_n].description5|escape}">
	</td>
</tr>
<tr>
	<td valign=top><b>Product Description</b></td>
	<td colspan=3><input size=80 id="description-{$item_n|default:0}" name="description[{$item_n|default:0}]" onchange="uc(this); {if $config.masterfile_disallow_double_quote}check_description(this);{/if} add_to_sku_receipt_desc('description-{$item_n|default:0}','receipt_description-{$item_n|default:0}');" style="background-color:#fff" value="{$items[$item_n].description|escape}">
	 <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
	<div class=small style="color:#f00">Eg: [brand] [flavour] [product name] [weight/size/colour] [misc]</div>
	</td>
</tr>


<tr>
	<td valign=top><b>Receipt Description [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>
	<td colspan=3>
	<input onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this); update_sku_receipt_desc(this);"{else}onchange="update_sku_receipt_desc(this)"{/if} onchange="check_receipt(this,'receipt_description'，'$item_n'" size="50" maxlength="40" id="receipt_description-{$item_n|default:0}" name="receipt_description[{$item_n|default:0}]" value="{$items[$item_n].receipt_description|escape:"html"}"  class="inp_receipt_desc" /> <img src=ui/rq.gif align=absbottom title="Required Field">
	<input class="receipt_desc" type="hidden" value="receipt_description-{$item_n|default:0}" />
	</td>
</tr>


{if $config.sku_enable_additional_description}
	<tr>
		<td valign="top"><b>Additional Description</b></td>
		<td colspan="3">
			<input type="checkbox" name="additional_description_print_at_counter[{$item_n|default:0}]" value="1" {if $items[$item_n].additional_description_print_at_counter}checked{/if} /> Print at Counter
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="additional_description_prompt_at_counter[{$item_n|default:0}]" value="1" {if $items[i].additional_description_prompt_at_counter}checked{/if} /> Prompt when scan at POS Counter
			<br />
			<textarea cols="45" rows="6" onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this);"{/if} name="additional_description[{$item_n|default:0}]">{$items[$item_n].additional_description|escape}</textarea>
		</td>
	</tr>
{/if}



<tr class="gst_settings">
	<td><b>Input Tax</b></td>
	<td>
		<select name="dtl_input_tax[{$item_n}]" class="dtl_input_tax">
			<option value="-1" {if $items[$item_n].input_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$input_tax_list key=rid item=r}
				<option value="{$r.id}" {if $items[$item_n].input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>

<tr class="gst_settings">
	<td><b>Output Tax</b></td>
	<td>
		<select name="dtl_output_tax[{$item_n}]" onchange="calculate_gst({$item_n}, this);" class="dtl_output_tax">
			<option value="-1" {if $items[$item_n].output_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$output_tax_list key=rid item=r}
				<option data-rate="{$r.rate}" value="{$r.id}" {if $items[$item_n].output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>

<tr style="{if !$gst_settings or ($global_gst_settings.inclusive_tax eq 'yes' and (!isset($items.$item_n.inclusive_tax) or $items.$item_n.inclusive_tax eq 'inherit'))}display:none;{/if}">
	<td><b>Selling Price Inclusive Tax</b></td>
	<td>
		<select name="dtl_inclusive_tax[{$item_n}]" onchange="calculate_gst({$item_n}, this);" class="dtl_inclusive_tax">
			<option value="inherit" {if $items[$item_n].inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow SKU)</option>
			<option value="yes" {if $items[$item_n].inclusive_tax eq "yes"}selected {/if}>Yes</option>
			<option value="no" {if $items[$item_n].inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>

<tr>
	<td valign="top"><b>Selling Price Settings</b></td>
	<td>
		<ul style="list-style:none;" id="ul_selling_price_settings-{$item_n}">
			<li><input type=checkbox name="open_price[{$item_n|default:0}]" class="chx_sp_settings" {if $items[$item_n].open_price}checked{/if} value="1" onChange="toggle_open_price('{$item_n}');"/>&nbsp;Open Price</li>
			<li>
				<input type="checkbox" name="allow_selling_foc[{$item_n}]" class="chx_sp_settings" value="1" {if $items[$item_n].allow_selling_foc}checked {/if} onChange="toggle_allow_selling_foc('{$item_n}');" />&nbsp;Allow Selling FOC
			</li>
			<li>
				<input type="checkbox" name="not_allow_disc[{$item_n|default:0}]" {if $items[$item_n].not_allow_disc}checked{/if} value="1" />&nbsp;Not Allow Discount <a href="javascript:void(alert('Please take note that this feature only applies to the POS Counter.\nAvailable for ARMS POS V.191 / ARMS POS BETA V310 and above.'))">[?]</a>
			</li>
		</ul>
	</td>
</tr>

{* Use RSP *}
<tr>
	<td valign="top"><b>RSP</b> <a href="javascript:void(prompt_rsp_notification())">[?]</a></td>
	<td>
		<input type="checkbox" id="inp_use_rsp-{$item_n|default:0}" name="use_rsp[{$item_n|default:0}]" value="1" {if $items[$item_n].use_rsp}checked {/if} onChange="use_rsp_changed('{$item_n}');" /> 
		<label for="inp_use_rsp-{$item_n|default:0}">Use Recommended Selling Price (RSP) Control</label>
		
		<br />
		<table>
			<tr>
				<td><b>RSP</b></td>
				<td>{$config.arms_currency.symbol}</td>
				<td>
					<input type="text" name="rsp_price[{$item_n|default:0}]" value="{$items[$item_n].rsp_price|number_format:2:".":""}" size="6" {if !$items[$item_n].use_rsp}readonly {/if} onChange="rsp_price_changed('{$item_n}');" />
				</td>
			</tr>
			<tr>
				<td><b>RSP Discount <a href="javascript:void(show_discount_help())">[?]</a></b></td>
				<td>&nbsp;</td>
				<td>
					<input type="text" name="rsp_discount[{$item_n|default:0}]" value="{$items[$item_n].rsp_discount}" size="6" {if !$items[$item_n].use_rsp}readonly {/if} onChange="rsp_discount_changed('{$item_n}');" />
				</td>
			</tr>
		</table>
	</td>
</tr>

<tr>
	<td width=100><b>Selling Price</b></td>
	<td>
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					{$config.arms_currency.symbol} <input name="selling_price[{$item_n|default:0}]" size=6 value="{$items[$item_n].selling_price|number_format:2:".":""}" onchange=" item_selling_price_changed('{$item_n}');"> 
					<img src=ui/rq.gif align=absbottom title="Required Field">
					<span id="span_selling_foc-{$item_n}" style="{if !$items[$item_n].allow_selling_foc}display:none;{/if}">
						<input type="checkbox" name="selling_foc[{$item_n}]" value="1" {if $items[$item_n].allow_selling_foc and $items[$item_n].selling_foc}checked {/if} {if !$items[$item_n].allow_selling_foc}disabled {/if} onChange="check_selling_foc('{$item_n}');"/> <b>FOC</b>
					</span>
				</td>
				<td class="gst_settings"><b>GST (<span id="gst_perc_{$item_n|default:0}">0</span>%)</b></td>
				<td class="gst_settings">{$config.arms_currency.symbol} <input type="text" name="gst_amount[{$item_n|default:0}]" value="" size="6" readonly /></td>
			</tr>
		</table>
	</td>
	<td class="gst_settings" nowrap><b>Selling Price <span id="span_gst_indicator_{$item_n}"></span> GST</b></td>
	<td class="gst_settings">{$config.arms_currency.symbol} <input type="text" name="gst_selling_price[{$item_n|default:0}]" value="" size="6" onchange="this.value=round2(this.value); calculate_gst({$item_n}, this);" /></td>
</tr>

{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Selling</b></td>
	<td>{$config.arms_currency.symbol} <input name="hq_selling[{$item_id}]" size=6 value="{$items[$item_n].hq_selling|number_format:2:".":""}" onchange="this.value=round2(this.value);"></td>
</tr>
{/if}
<tr>
	<td width=100><b>Cost Price</b></td>
	<td>(Enter {$config.arms_currency.symbol} or %) <input name="cost_price[{$item_n|default:0}]" size=6 value="{$items[$item_n].cost_price|number_format:4:".":""}" onchange="cost_changed(this, '{$item_n}');"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Cost</b></td>
	<td>{$config.arms_currency.symbol} <input name="hq_cost[{$item_id}]" size=6 value="{$items[$item_n].hq_cost|number_format:2:".":""}" onchange="this.value=round2(this.value);"></td>
</tr>
{/if}
<tr>
	<td><b>Gross Profit</b></td>
	<td>{$config.arms_currency.symbol} <input name="gross[{$item_n|default:0}]" size=6 readonly></td>
	<td><b>GP(%) [<a href="javascript:void(alert('{$LANG.SKU_GP_PER_LEGEND}'))">?</a>]</b></td>
	<td><input name="grossp[{$item_n|default:0}]" size=6 readonly> %</td>
</tr>
{if $config.masterfile_sku_enable_ctn}
    <tr>
	    <td><b>Ctn #1</b></td>
	    <td>
			<select name="ctn_1_uom_id[{$item_n|default:0}]">
			    {foreach from=$uom item=u}
			        <option value="{$u.id}" {if $u.id==$items[$item_n].ctn_1_uom_id or (!$items[$item_n].ctn_1_uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
			    {/foreach}
			</select>
		</td>
	</tr>
	<tr>
	    <td><b>Ctn #2</b></td>
	    <td>
			<select name="ctn_2_uom_id[{$item_n|default:0}]">
			    {foreach from=$uom item=u}
			        <option value="{$u.id}" {if $u.id==$items[$item_n].ctn_2_uom_id or (!$items[$item_n].ctn_2_uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
			    {/foreach}
			</select>
		</td>
	</tr>
{/if}

<tr>
	<td><b>Allow Decimal Qty</b> [<a href="javascript:void(alert('{$LANG.SKU_ALLOW_DECIMAL_NOTIFICATION|escape:javascript}'));">?</a>]</td>
	<td>
		<input type="checkbox" name="decimal_qty[{$item_n|default:0}]" {if $items[$item_n].decimal_qty}checked {/if} value="1" />
		Counter
		&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="doc_allow_decimal[{$item_n|default:0}]" {if $items[$item_n].doc_allow_decimal}checked {/if} value="1" /> Adjustment, DO, GRN, Sales Order, GRA, PO
	</td>
</tr>

{if $config.enable_replacement_items}
	<tr>
	    <td><b>Replacement Item Group</b></td>
	    <td colspan="3">
	        <input type="checkbox" onClick="toggle_replacement_group(this, '{$item_n|default:0}');" {if $items.$item_n.ri_id}checked {/if} />
			<input type="text" readonly size="1" name="ri_id[{$item_n|default:0}]" id="inp_ri_id_{$item_n|default:0}" value="{$items.$item_n.ri_id}" {if !$items.$item_n.ri_id}disabled {/if} />
			<input type="text" name="ri_group_name[{$item_n|default:0}]" size="40" id="inp_ri_group_name_{$item_n|default:0}"  value="{$items.$item_n.ri_group_name}"  {if !$items.$item_n.ri_id}disabled {/if} />
			<span id="span_ri_loading_{$item_n|default:0}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			<div id="autocomplete_replacement_group_choices_{$item_n|default:0}" class="autocomplete"></div>
		</td>
	</tr>
{/if}

<!-- Category Discount -->
<tr>
	<td valign="top"><b>Category Discount (%)</b></td>
	<td>
		{include file='masterfile_sku.edit.items.discount.tpl' item_id=$item_n is_edit=1 item_obj=$items.$item_n}
	</td>
</tr>

<!-- Reward Point -->
<tr>
	<td valign="top"><b>Reward Point</b></td>
	<td>
		{include file='masterfile_sku.edit.items.point.tpl' item_id=$item_n is_edit=1 item_obj=$items.$item_n}
	</td>
</tr>

{if !$config.consignment_modules}
	<tr>
		<td><b>Scale Type</b></td>
		<td>
			<select name="dtl_scale_type[{$item_n|default:0}]">
				{if isset($items.$item_n.scale_type)}
				{assign var="cur_st" value=$items.$item_n.scale_type}
				{else}
				{assign var="cur_st" value="-1"}
				{/if}
				{foreach from=$scale_type_list key=st_value item=st_name}
					<option value="{$st_value}" {if $cur_st eq $st_value}selected {/if}>{$st_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
{/if}

{if $config.sku_non_returnable}
	<tr valign="top">
		<td nowrap><b>Non-returnable</b> <a href="javascript:void(alert('Turn on this will not allow this SKU to return at GRA'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></td>
		<td>
			<select name="non_returnable[{$item_n}]">
				<option value="-1" {if !isset($items.$item_n.non_returnable) || $items.$item_n.non_returnable eq -1}selected {/if}>inherit (Follow SKU)</option>
				<option value="1" {if $items.$item_n.non_returnable eq 1}selected {/if}>Yes</option>
				<option value="0" {if isset($items.$item_n.non_returnable) && $items.$item_n.non_returnable eq 0}selected {/if}>No</option>
			</select>
		</td>
	</tr>
{/if}

<tr>
	<td><b>Model</b></td>
	<td>
		<input type="text" name="model[{$item_n}]" value="{$items[$item_n].model}" />
	</td>
</tr>

<tr>
	<td><b>Width</b></td>
	<td>
		<input type="text" name="width[{$item_n}]" value="{$items[$item_n].width}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
	</td>
</tr>

<tr>
	<td><b>Height</b></td>
	<td>
		<input type="text" name="height[{$item_n}]" value="{$items[$item_n].height}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
	</td>
</tr>

<tr>
	<td><b>Length</b></td>
	<td>
		<input type="text" name="length[{$item_n}]" value="{$items[$item_n].length}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
	</td>
</tr>

{if $sku_extra_info}
	<tr>
		<td colspan="2">
			<fieldset>
				<legend><b>Extra Info</b></legend>
				<table>
					{foreach from=$sku_extra_info key=c item=extra_info}
						<tr>
							<td width="90"><b>{$extra_info.description}</b></td>
							<td>
								{if $extra_info.input_type eq 'text'}
									<input type="text" name="extra_info[{$item_n}][{$c}]" value="{$items.$item_n.extra_info.$c}" {if $extra_info.data_type eq 'date'}onchange="date_validate(this);"{/if} />
									{if $extra_info.data_type eq 'date'} (YYYY-MM-DD){/if}
								{/if}
							</td>
						</tr>
					{/foreach}
				</table>
			</fieldset>
		</td>
	</tr>
{/if}

<tr class="tr_si_po_reorder_qty">
	<td><b>PO Reorder Qty</b></td>
	<td>
	    Min: <input type="text" size="3" name="si_po_reorder_qty_min[{$item_n|default:0}]" value="{$items.$item_n.po_reorder_qty_min}" class="si_po_reorder_qty" />
	    &nbsp;&nbsp;&nbsp;
	    Max: <input type="text" size="3" name="si_po_reorder_qty_max[{$item_n|default:0}]" value="{$items.$item_n.po_reorder_qty_max}" class="si_po_reorder_qty" />
	    &nbsp;&nbsp;&nbsp;
		MOQ: <a href="javascript:void(alert('Minimum Order Quantity'))"><img src="/ui/icons/information.png" align="absmiddle" /></a> <input type="text" size="3" name="si_po_reorder_moq[{$item_n|default:0}]" value="{$items.$item_n.po_reorder_moq}" class="si_po_reorder_qty" />
		&nbsp;&nbsp;&nbsp;
		Notify Person 
		<select name="si_po_reorder_notify_user_id[{$item_n|default:0}]" class="si_po_reorder_qty">
			<option value="" {if !$items.$item_n.po_reorder_notify_user_id}selected{/if}>--</option>
			{foreach from=$po_reorder_users key=row item=r}
				<option value="{$r.id}" {if $items.$item_n.po_reorder_notify_user_id eq $r.id}selected{/if}>{$r.u}</option>
			{/foreach}
		</select>
	</td>
</tr>

{if $config.enable_sn_bn}
	<tr>
		<td width="100"><b>Warranty Period</b></td>
		<td>
			<input name="sn_we[{$item_n}]" size="6" value="{$items[$item_n].sn_we}" class="r" onchange="mi(this);">&nbsp;
			<select name="sn_we_type[{$item_n}]">
				<option value="day" {if $items.$item_n.sn_we_type eq 'day'}selected{/if}>Day(s)</option>
				<option value="week" {if $items.$item_n.sn_we_type eq 'week'}selected{/if}>Week(s)</option>
				<option value="month" {if $items.$item_n.sn_we_type eq 'month' || !$items.$item_n.sn_we_type}selected{/if}>Month(s)</option>
				<option value="year" {if $items.$item_n.sn_we_type eq 'year'}selected{/if}>Year(s)</option>
			</select>
		</td>
	</tr>
{/if}

{if $sessioninfo.privilege.MST_INTERNAL_DESCRIPTION}
	<tr>
		<td valign="top" width="100"><b>Internal Description</b></td>
		<td>
			<textarea cols="45" rows="6" onblur="uc(this)" name="internal_description[{$item_n}]">{$items.$item_n.internal_description|escape}</textarea>
		</td>
	</tr>
{/if}

{if $config.arms_marketplace_settings}
<tr>
	<td valign="top" width="100"><b>Marketplace Description</b></td>
	<td>
		<textarea cols="45" rows="6" onblur="uc(this)" name="marketplace_description[{$item_n}]">{$items.$item_n.marketplace_description|escape}</textarea>
	</td>
</tr>
{/if}

</table>

<br>
<script>
toggle_gst_settings();
atom_update_fulldesc({$item_n|default:0});
atom_update_gross({$item_n|default:0});
{if $config.enable_replacement_items}
	init_ri_autocomplete('{$item_n|default:0}');
{/if}

</script>
{include file="masterfile_sku_application.atom_photos.tpl"}
