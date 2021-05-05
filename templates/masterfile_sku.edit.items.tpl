{*
6/13/2007 5:51:59 PM - gary
- added  block list function for each SKU.

6/21/2007 10:29:29 AM - yinsee
- replace multic's code with link_code_name

8/21/2007 6:32:08 PM - yinsee
- add image updating

9/14/2007 10:33:49 AM -gary
- selling_price and cost_price remove the "," from number_format.

11/16/2007 12:10:18 PM gary
- add multiple packing UOM.

12/27/2007 2:34:52 PM yinsee
- show application photos

1/11/2008 3:30:10 PM yinsee
- remove block_list from sku 

11/18/2008 2:40:22 PM yinsee
- orange color indicates sku parent

3/26/2009 3:52:07 PM yinsee
- change default item active status to 1

6/22/2009 3:40 PM Andy
- show HQ Cost if got $config.sku_listing_show_hq_cost and is HQ

7/28/2009 10:47:04 AM Andy
- Show ctn if $config.masterfile_sku_enable_ctn = 1

5/10/2009 4:54:51 PM yinsee
- add location box

11/16/2009 3:13:08 PM yinsee
- set item as parent

12/10/2009 12:35:21 PM edward
- add decimal qty control box

6/2/2010 10:18:43 AM Alex
- add weight,color,size,flavor,misc

7/26/2010 9:25:44 AM Alex
- add 'softline','outright' keywords checking to allow add matrix link

8/13/2010 10:12:15 AM Andy
- Add can choose replacement item group when apply/edit sku.

12/9/2010 12:00:43 PM Andy
- Add allow delete sku apply photo at sku edit.
- Change get sku apply photo method.

12/9/2010 3:42:28 PM Justin
- Adjusted the Replacement Item Group row and place at the end of sku table.

12/14/2010 10:58:53 AM Andy
- Temporary remove the "Delete sku apply photo feature".
- Change the way to get sku apply photo to use back the old method.

4/15/2011 11:22:39 AM Andy
- Add checking for sku photo path and change path to show the image.

5/11/2011 4:34:19 PM Alex
- add a checkbox to check all other checkboxes

5/18/2011 1:42:51 PM Alex
- split article no to art no and size
- check $config.ci_auto_gen_artno to change artno to readonly

5/23/2011 10:46:20 AM Andy
- Add generate cache for thumb picture.

5/27/2011 10:08:53 AM Alex
- move checking artno function from article no to article size

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache when view in popup.

6/3/2011 10:59:11 AM Justin
- Fixed the bugs for using lowercase entry for Product Description that cannot clone to Receipt Description.

6/6/2011 10:01:15 AM Alex
- change Article No. to uppercase

6/13/2011 2:41:31 PM Andy
- Add "Allow decimal qty in GRN" at SKU. (currently will be disabled until GRN is enhanced)

6/24/2011 5:15:24 PM Justin
- Enabled "Allow decimal qty in GRN" at SKU.

6/30/2011 12:14:24 PM Justin
- Added to show error messages div.

8/15/2011 9:29:11 AM Alex
- remove $config.ci_auto_gen_artno

9/6/2011 4:04:50 PM Justin
- Added new validation to include/exclude hidden field.
- Added new validation to control delete for those new SKU items/Matrix.
- Added new checking feature to show/hide "Set as Parent" button between existing/new SKU items.

9/7/2011 3:08:32 PM Justin
- Fixed the inactive reason that does not capture and capture properly whenever return from errors.
- Modified the reject reason text area not to always hidden whenever return from errors.
- Fixed the bugs where show out reject reason even the SKU item is active.

9/12/2011 7:36:50 PM Alex
- add checking $config.masterfile_disable_auto_explode_artno for artno

10/24/2011 3:16:35 PM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

3/7/2012 4:34:32 PM Justin
- Added "Return Policy" for user to maintain by branch.

3/21/2012 4:42:43 PM Justin
- Added a new config "masterfile_enable_check_desc" to enable/disable whether need to do checking for product/receipt description for quote (").

3/23/2012 9:57:43 AM Justin
- Changed the config name "masterfile_enable_check_desc" into "masterfile_disallow_double_quote".

4/10/2012 5:31:27 PM Alex
- add class for sku apply photo for identifying

5/7/2012 10:28:33 AM Andy
- Add "Category Discount (%)" and "Category Reward Point" can override by SKU.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

6/29/2012 3:14:45 PM Justin
- Fixed bug of system hidden reason while sku item is inactive.

7/2/2012 5:09:23 PM Justin
- Added new field "Scale Type" for user to maintain by item.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

8/23/2012 3:02 PM Justin
- Changed the wording "V149" into "V168".

9/3/2012 4:34 PM Justin
- Enhanced to show more info on privilege requirement in order to edit category discount & member rewards.

11/23/2012 12:13 PM Justin
- Enhanced to place existing reason on reason textarea to prevent system asking for reason while edit again.

5/16/2013 4:05 PM Justin
- Enhanced to show Additional Description by config set.

5/20/2013 12:00 PM Fithri
- bugfix : receipt desc should be 40 chars max

8/29/2013 5:17 PM Fithri
- automatically strips out character that is not 0-9,a-Z,-_/ and space in artno field

9/3/2013 2:50 PM Fithri
- add checking whether allow special chars in artno

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

12/19/2013 11:10 AM Andy
- Fix sku photo path if got special character will not able to show in popup.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour" and "Flavor" to "Flavour".

4/3/2014 2:28 PM Justin
- Enhanced to allow user maintain "PO Reorder Qty Min & Max" by SKU items.

4/18/2014 5:53 PM Justin
- Enhanced to have Block item in GRN.

4/28/2014 11:03 AM Justin
- Enhanced to show "Block item in GRN" while config "check_block_grn_as_po" is turned on.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

7/10/2014 9:57 AM Justin
- Enhanced to have max length for MCode, Old Code and Artno.

8/20/2014 5:55 PM DingRen
- add Input Tax, Output Tax, Inclusive Tax

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

1/23/2015 10:44 AM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.
- Enhance Open Price/Allow Selling FOC checking.
- Change the selling price must always >0 except is open price.

3/19/2015 5:58 PM Andy
- Fix wrong gp calculation.
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

3/26/2015 4:54 PM Andy
- Fix sku item edit cost by percentage bug.

4/10/2015 11:34 AM Andy
- Enhance to immediately calculate item gst_amt and gst_selling_price on load sku items.

4/17/2015 10:40 AM Andy
- Increase the artno maxlength from 20 to 30.

5/5/2015 10:35 AM Andy
- Enhanced to show GP % description.

7/29/2016 10:56 AM Andy
- Enhanced to show notice for "allow decimal qty".

9/6/2016 10:17 AM Qiu Ying
- Increase the old code/link code maxlength from 12 to 20

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

4/21/2017 1:39 PM Justin
- Enhanced to have "Not Allow Discount".

4/25/2017 9:41 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/10/2017 11:04 AM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/11/2017 10:07 AM Justin
- Added notes for "Not Allow Discount" checkbox.

5/18/2017 3:34 PM Justin
- Enhanced to show counter version requirements for "Not Allow Discount" feature.

6/19/2017 9:33 AM Qiu Ying
- Enhanced to show the latest cost

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

1/8/2019 3:40 PM Andy
- Enhanced to disable users to change packing uom if sku got grn.

5/29/2019 1:22 PM William
- Added new PO Reorder Qty "Moq".

6/28/2019 9:04 AM William
- Added new "add promotion image".

7/4/2019 11:29 AM Justin
- Amended the notes for "Weight in KG" to include Self Checkout info.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.
- Enhanced to have a container to contain extra info.

9/27/2019 1:31 PM Andy
- Enhanced to capture log when remove any sku image.
- Enhanced to update sku_items.lastupdate when upload POS Image or remove any sku image.
- Rename "Promotion Image" to "Promotion / POS Image"

12/16/2019 11:57 AM William
- Added new "Set PO Reorder Qty by Branch" to sku items when PO Reorder Qty by child.

2/28/2020 11:55 AM William
- Enhanced to added new column "Marketplace Description".

7/13/2020 3:26 PM William
- Enhanced to added new checkbox "Prompt when scan at POS Counter".

8/24/2020 1:58 PM William
- Enhanced to clear browser cache when diplay new upload image.

11/10/2020 5:47 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.

11/13/2020 2:42 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
*}

{if $items}
{assign var=item_id value=`$items[i].id`}
{else}
{assign var=item_id value=$smarty.now}
{assign var=is_new value=1}

{/if}
{if $is_new || $items[i].is_new}
	<input type=hidden class="new" name=is_new[{$item_id}] value={$item_id}>
{/if}
<div id="item[{$item_id}]" class="stdframe div_item" sku_item_id="{$item_id}" style="margin-bottom:10px;{if $items[i].is_parent}border:1px solid #fa6;background:#fe9;{/if}">
{if $is_new || !$items[i].sku_item_code}
	<div style="float:right"><a href="javascript:void(cancel_item('{$item_id}'))"><img src=/ui/del.png align=absmiddle border=0> Delete</a></div>
{/if}

<h3 id=desc_{$item_id}>SKU Item {$items[i].sku_item_code}</h3>
<input name=si_code[{$item_id}] value="{$items[i].sku_item_code}" type='hidden'>
<input name=is_parent[{$item_id}] value="{$items[i].is_parent}" type='hidden'>

{if !$items[i].is_parent && !$items[i].is_new && !$is_new}
<button onclick="return set_as_parent({$item_id})">Set as Parent SKU</button>
{/if}

{if $errm.items}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.items.$item_id item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}
<input name="item_type[{$item_id|default:0}]" value="variety" type=hidden>
<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td><b>Article No.</b></td>
	<td><input data-item_id="{$item_id}" name="artno[{$item_id}]"
		{if substr($items[i].sku_item_code,-4) eq '0000'}alt="header"{/if}
		onchange="this.value=this.value.trim().toUpperCase();{if !$config.sku_artno_allow_specialchars}correct_artno(this);{/if}{if $config.masterfile_disable_auto_explode_artno}check_artmcode(this,'artno','{$item_id}'){/if}"
		value="{$items[i].artno}{if $config.masterfile_disable_auto_explode_artno && $items[i].artsize} {$items[i].artsize}{/if}" maxlength="30"> 
		{if !$config.sku_application_allow_no_artno_mcode}<img src=ui/rq.gif align=absbottom title="Required Field">{/if}
		{if !$config.masterfile_disable_auto_explode_artno}
			&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Article Size</b>&nbsp;&nbsp;
			<input name="artsize[{$item_id}]" onchange="this.value=this.value.trim().toUpperCase();check_artmcode(this,'artno','{$item_id}');" value="{$items[i].artsize}">
		{/if}
	</td>
</tr>
<tr>
	<td><b>Manufacturer's Code</b></td>
	<td><input  onchange="check_artmcode(this,'mcode','{$item_id}')"  name="mcode[{$item_id}]" value="{$items[i].mcode}" {if substr($items[i].sku_item_code,-4) eq '0000'}alt="header"{/if} maxlength="15"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>

<tr>
	<td><b>{$config.link_code_name}</b></td>
	<td><input name="link_code[{$item_id}]" value="{$items[i].link_code}" maxlength="20"></td>
</tr>

<tr>
	<td><b>Packing UOM</b></td>
	<td>
		{if $items[i].is_parent}
			<input name="parent_sid" value="{$item_id}" type="hidden" />
			<input name="old_weight_kg" value="{$items[i].weight_kg|weight_nf}" type="hidden" />
		{/if}
		
		<div style="{if $items[i].disable_edit_packing_uom}display:none;{/if}">
			<input type="hidden" name="disable_edit_packing_uom[{$item_id}]" value="{$items[i].disable_edit_packing_uom}" />
			<select name="packing_uom_id[{$item_id}]" onchange="calc_weight_kg({$item_id});">
			{section name=j loop=$uom}
				{if !$items[i].is_parent or ($items[i].is_parent and $uom[j].fraction eq 1)}
					<option value="{$uom[j].id}" {if $uom[j].id==$items[i].packing_uom_id or ($items[i].packing_uom_id==0 and $uom[j].code eq 'EACH')}selected{/if} uom_fraction="{$uom[j].fraction|default:1}">
						{$uom[j].code}
					</option>
				{/if}
				
				{if $uom[j].id==$items[i].packing_uom_id or ($items[i].packing_uom_id==0 and $uom[j].code eq 'EACH')}
					{assign var=selected_uom_code value=$uom[j].code}
				{/if}
			{/section}
			</select>
		</div>
		
		{if $items[i].disable_edit_packing_uom}
			{$selected_uom_code}
			<img src="ui/icons/information.png" class="clickable" onClick="alert('Packing UOM Cannot be change due to it has been used in GRN');" />
		{/if}
	</td>
</tr>

<tr>
	<td><b>Weight in KG <a href="javascript:void(alert('Weight in KG is use for Work Order module and Self Checkout Counter from v199 and above.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></b></td>
	<td>
		<input type="text" name="weight_kg[{$item_id}]" value="{$items[i].weight_kg|weight_nf}" sid="{$item_id}" size="10" maxlength="15" {if !$items[i].is_parent}class="inp_weight_kg" readonly{else}onchange="mf(this, {$config.global_weight_decimal_points}); calc_weight_kg();"{/if} />
	</td>
</tr>

<tr>
	<td valign=top><b>Remark</b></td>
	<td nowrap colspan=3>
	<input name="description0[{$item_id|default:0}]" value="{$items[$item_id].description0|escape}" type=hidden>
	(Weight Description) <a href="javascript:void(alert('This weight settings is used for display purpose only.'))"><img src="/ui/icons/information.png" align="absmiddle" /></a>&nbsp;<input onchange="uc(this);" size=10 maxlength=15  name="description1[{$item_id|default:0}]" value="{$items[i].weight|escape}">
	(Sz) <input id="autocomplete_size_{$item_id}" onchange="uc(this);" size=10 maxlength=15 name="description2[{$item_id|default:0}]" onkeydown="autocomplete_color_size_variety('size',{$item_id})"  value="{$items[i].size|escape}">
		<div id="div_autocomplete_size_choices_{$item_id}" class="autocomplete" style="display:none;"></div>
	(Colour)<input id="autocomplete_color_{$item_id}" onchange="uc(this);" size=10 maxlength=15 name="description3[{$item_id|default:0}]" onkeydown="autocomplete_color_size_variety('color',{$item_id})"  value="{$items[i].color|escape}">
		<div id="div_autocomplete_color_choices_{$item_id}" class="autocomplete" style="display:none;" ></div>
	(Flavour)<input onchange="uc(this);" size=10 maxlength=15 name="description4[{$item_id|default:0}]" value="{$items[i].flavor|escape}">
	(Misc) <input onchange="uc(this);" size=20 maxlength=30 name="description5[{$item_id|default:0}]" value="{$items[i].misc|escape}">
	</td>
</tr>

<tr>
	<td valign=top><b>Product Description</b></td>
	<td colspan=3><input size=80 id="description-{$item_id}" name="description[{$item_id}]" onchange="uc(this); {if $config.masterfile_disallow_double_quote}check_description(this);{/if} add_to_sku_receipt_desc('description-{$item_id}', 'receipt_description-{$item_id}');" style="background-color:#fff" value="{$items[i].description|escape}">
	<div class=small style="color:#f00">Eg: [brand] [flavour] [product name] [weight/size/colour] [misc]</div>
	</td>
</tr>

<tr>
	<td valign=top><b>Receipt Description [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>
	<td colspan=3 ><input onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this); update_sku_receipt_desc(this)"{else}onchange="update_sku_receipt_desc(this);"{/if} id="receipt_description-{$item_id}"  name="receipt_description[{$item_id}]" value="{$items[i].receipt_description|escape}" size="50" maxlength="40"> <img src="ui/rq.gif" align="absbottom" title="Required Field">
	<input class="receipt_desc" type="hidden" value="receipt_description-{$item_id}" />
	</td>
</tr>

{if $config.sku_enable_additional_description}
	<tr>
		<td valign="top"><b>Additional Description</b></td>
		<td colspan="3">
			<input type="checkbox" name="additional_description_print_at_counter[{$item_id}]" value="1" {if $items[i].additional_description_print_at_counter}checked{/if} /> Print at Counter
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="additional_description_prompt_at_counter[{$item_id}]" value="1" {if $items[i].additional_description_prompt_at_counter}checked{/if} /> Prompt when scan at POS Counter
			<br />
			<textarea cols="45" rows="6" onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this);"{/if} name="additional_description[{$item_id}]">{$items[i].additional_description|escape}</textarea>
		</td>
	</tr>
{/if}

<tr class="gst_settings">
	<td><b>Input Tax</b></td>
	<td>
		<select name="dtl_input_tax[{$item_id}]" class="dtl_input_tax">
			<option value="-1" {if $items[i].input_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$input_tax_list key=rid item=r}
				<option value="{$r.id}" {if $items[i].input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>
<tr class="gst_settings">
	<td><b>Output Tax</b></td>
	<td>
		<select name="dtl_output_tax[{$item_id}]" onchange="calc_gst({$item_id});" class="dtl_output_tax">
			<option value="-1" {if $items[i].output_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$output_tax_list key=rid item=r}
				<option data-rate="{$r.rate}" value="{$r.id}" {if $items[i].output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}

		</select>
	</td>
</tr>

<tr style="{if !$gst_settings or ($global_gst_settings.inclusive_tax eq 'yes' and (!isset($items[i].inclusive_tax) or $items[i].inclusive_tax eq 'inherit'))}display:none;{/if}">
	<td><b>Selling Price Inclusive Tax</b></td>
	<td>
		<select name="dtl_inclusive_tax[{$item_id}]" onchange="calc_gst({$item_id});" class="dtl_inclusive_tax">
          <option value="inherit" {if $items[i].inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow SKU)</option>
          <option value="yes" {if $items[i].inclusive_tax eq "yes"}selected {/if}>Yes</option>
          <option value="no" {if $items[i].inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>

<tr>
	<td valign="top"><b>Selling Price Settings</b></td>
	<td>
		<ul style="list-style:none;" id="ul_selling_price_settings-{$item_id}">
			<li><input type="checkbox" name="open_price[{$item_id}]" class="chx_sp_settings" {if $items[i].open_price}checked {/if} value="1" onChange="toggle_open_price('{$item_id}');">&nbsp;Open Price</li>
			<li>
				<input type="checkbox" name="allow_selling_foc[{$item_id}]" class="chx_sp_settings" value="1" {if $items[i].allow_selling_foc}checked {/if} onChange="toggle_allow_selling_foc('{$item_id}');" />&nbsp;Allow Selling FOC
			</li>
			<li>
				<input type="checkbox" name="not_allow_disc[{$item_id}]" {if $items[i].not_allow_disc}checked{/if} value="1" />&nbsp;Not Allow Discount <a href="javascript:void(alert('Please take note that this feature only applies to the POS Counter.\nAvailable for ARMS POS V.191 / ARMS POS BETA V310 and above.'))">[?]</a>
			</li>
		</ul>
	</td>
</tr>

{* Use RSP *}
<tr>
	<td valign="top"><b>RSP</b> <a href="javascript:void(prompt_rsp_notification())">[?]</a></td>
	<td>
		{assign var=ori_use_rsp value=$items[i].use_rsp}
		{if isset($items[i].ori_use_rsp)}
			{assign var=ori_use_rsp value=$items[i].ori_use_rsp}
		{/if}
		<input type="hidden" name="ori_use_rsp[{$item_id}]" value="{$ori_use_rsp}" />
		
		{if $items[i].use_rsp and $items[i].got_change_price}
			<img src="/ui/checked.gif" align="absmiddle" />
			<input type="checkbox" name="use_rsp[{$item_id}]" value="1" checked style="display:none;" />
		{else}
			<input type="checkbox" id="inp_use_rsp-{$item_id}" name="use_rsp[{$item_id}]" value="1" {if $items[i].use_rsp}checked {/if} onChange="use_rsp_changed('{$item_id}');" />
		{/if}
		 
		<label for="inp_use_rsp-{$item_id}">Use Recommended Selling Price (RSP) Control
			{if $items[i].use_rsp and $items[i].got_change_price}
				<a href="javascript:void(alert('You cannot change this because there are price change history in branches'))"><img src="/ui/icons/information.png" align="absmiddle" /></a>
			{/if}
		</label>
		
		
		<br />
		<table>
			<tr>
				<td><b>RSP</b></td>
				<td>{$config.arms_currency.symbol}</td>
				<td>
					<input type="text" name="rsp_price[{$item_id}]" value="{$items[i].rsp_price|number_format:2:".":""}" size="6" {if !$items[i].use_rsp}readonly {/if} onChange="rsp_price_changed('{$item_id}');" {if $items[i].got_change_price}readonly title="You cannot edit RSP, because this SKU got change Selling Price in branches."{/if} />
				</td>
			</tr>
			<tr>
				<td><b>RSP Discount <a href="javascript:void(show_discount_help())">[?]</a></b></td>
				<td>&nbsp;</td>
				<td>
					<input type="text" name="rsp_discount[{$item_id}]" value="{$items[i].rsp_discount}" size="6" {if !$items[i].use_rsp}readonly {/if} onChange="rsp_discount_changed('{$item_id}');" />
				</td>
			</tr>
		</table>
	</td>
</tr>

<tr>
	<td><b>Selling Price</b></td>
	<td>
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td>
				{$config.arms_currency.symbol} <input name="selling_price[{$item_id}]" size=6 value="{$items[i].selling_price|number_format:2:".":""}" onchange="item_selling_price_changed('{$item_id}');" />
				<img src=ui/rq.gif align=absbottom title="Required Field">
				<span id="span_selling_foc-{$item_id}" style="{if !$items[i].allow_selling_foc}display:none;{/if}">
					<input type="checkbox" name="selling_foc[{$item_id}]" value="1" {if $items[i].allow_selling_foc and $items[i].selling_foc}checked {/if} {if !$items[i].allow_selling_foc}disabled {/if} onChange="check_selling_foc('{$item_id}');"/> <b>FOC</b>
					<a href="javascript:void(alert('If FOC is tick, the item will sell at price 0.00 at POS Counter.{if $config.enable_gst}\nBut the GST will still calculate based on the normal selling price.{/if}'))">[?]</a>
				</span>
				
				{* RSP Notice *}
				<span style="background-color: yellow; color:red; display:none;" id="span_use_rsp_first_time-{$item_id}"><img src="ui/icons/information.png" align="absmiddle" /> System will automatically do a price change to all branches if the branch already have sku selling price change.</span>
			</td>
			<td class="gst_settings"><b>GST (<span id="span_gst_rate_{$item_id}">{$items[i].output_gst_rate}</span>%)</b></td>
			<td class="gst_settings">{$config.arms_currency.symbol} <input type="text" size="6" name="gst_rate[{$item_id}]" value="{$items[i].gst_amt}" readonly/></td>
		</tr>
		</table>
	</td>
	<td class="gst_settings" nowrap><b>Selling Price <span id="span_gst_indicator_{$item_id}">{if $items[i].real_inclusive_tax eq 'yes'}Before{else}After{/if}</span> GST</b></td>
	<td class="gst_settings">
		{$config.arms_currency.symbol} <input type="text" size="6" name="selling_price_gst[{$item_id}]" value="{$items[i].gst_selling_price}" onchange="calc_gst({$item_id},'gst_price');" {if $items[i].allow_selling_foc and $items[i].selling_foc}readOnly {/if}/>
	</td>
</tr>

{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Selling Price</b></td>
	<td>{$config.arms_currency.symbol} <input name="hq_selling[{$item_id}]" size=6 value="{$items[i].hq_selling|number_format:2:".":""}" onchange="this.value=round2(this.value);"></td>
</tr>
{/if}
<tr>
	<td width=100><b>Cost Price</b></td>
	<td>(Enter {$config.arms_currency.symbol} or %) <input class="input_cost_price" {if $config.masterfile_not_allow_edit_cost && !$is_new}readonly{/if} name="cost_price[{$item_id}]" size=6 value="{$items[i].cost_price|number_format:$config.global_cost_decimal_points:".":""}" onchange="cost_changed(this, '{$item_id}');"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
{if !$is_new}
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
{/if}

{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
<tr>
	<td width="100"><b>HQ Cost</b></td>
	<td>{$config.arms_currency.symbol} <input name="hq_cost[{$item_id}]" size=6 value="{$items[i].hq_cost|number_format:$config.global_cost_decimal_points:".":""}" onchange="this.value=round(this.value, '{$config.global_cost_decimal_points}');"></td>
</tr>
{/if}
<tr>
	<td><b>Gross Profit</b></td>
	<td>{$config.arms_currency.symbol} <input name="gross[{$item_id}]" size=6 readonly></td>
	<td><b>GP(%) [<a href="javascript:void(alert('{$LANG.SKU_GP_PER_LEGEND}'))">?</a>]</b></td>
	<td><input name="grossp[{$item_id}]" size=6 readonly> %</td>
</tr>
{if $config.masterfile_sku_enable_ctn}
	<tr>
	    <td><b>Ctn #1</b></td>
	    <td>
			<select name="ctn_1_uom_id[{$item_id}]">
			    {foreach from=$uom item=u}
			        <option value="{$u.id}" {if $u.id==$items[i].ctn_1_uom_id or (!$items[i].ctn_1_uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
			    {/foreach}
			</select>
		</td>
	</tr>
	<tr>
	    <td><b>Ctn #2</b></td>
	    <td>
			<select name="ctn_2_uom_id[{$item_id}]">
			    {foreach from=$uom item=u}
			        <option value="{$u.id}" {if $u.id==$items[i].ctn_2_uom_id or (!$items[i].ctn_2_uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
			    {/foreach}
			</select>
		</td>
	</tr>
{/if}
<tr>
<td><b>Item Location</b></td><td><input onchange="ucz(this)" name="location[{$item_id}]" size=6 value="{$items[i].location}"></td>
</tr>
<tr>
    <td><b>Block item in PO</b></td>
	<!--6/13/2007 5:51:28 PM added by gary load block list -->
    <td id="bpo_branches_{$item_id}_id" colspan=3>
    <input type=checkbox onclick="check_all_branch(this,'{$item_id}','bpo')"> All
    {foreach item=br from=$branch}
    <span style="white-space:nowrap"><input type=checkbox class="bpo_branches_{$item_id}_id" name="block_list[{$item_id}][{$br.id}]" {if $items[i].block_list[$br.id]}checked{/if}>&nbsp;{$br.code}</span>
    {/foreach}
	</td>
</tr>
{if !$config.check_block_grn_as_po}
	<tr>
		<td><b>Block item in GRN</b></td>
		<td id="bgrn_branches_{$item_id}_id" colspan=3>
		<input type=checkbox onclick="check_all_branch(this,'{$item_id}','bgrn')"> All
		{foreach item=br from=$branch}
		<span style="white-space:nowrap"><input type=checkbox class="bgrn_branches_{$item_id}_id" name="doc_block_list[{$item_id}][grn][{$br.id}]" {if $items[i].doc_block_list.grn[$br.id]}checked{/if}>&nbsp;{$br.code}</span>
		{/foreach}
		</td>
	</tr>
{/if}
<tr>
	<td><b>Active</b></td>
	<td>
	<table class=reason_box cellpadding=0 cellspacing=0>
	<tr>
	<td><input type=hidden id=oactive_{$item_id} name=oactive value="{if !$items}1{else}{$items[i].active}{/if}"><input type=checkbox id=active_{$item_id} name="active[{$item_id}]" {if $items[i].active or !$items}checked{/if} value="1" onchange="toggle_active({$item_id},this);"></td>
	<td class=reason_box_{$item_id} {if $items[i].active || $item_id > 1000000000}style="display:none"{/if}>&nbsp;&nbsp;&nbsp;&nbsp;<b>Reason</b></td>
	<td class=reason_box_{$item_id} {if $items[i].active || $item_id > 1000000000}style="display:none"{/if}>
		<textarea class=reason name=reason[{$item_id}] rows=3 cols=40>{if !$items[i].active}{$items[i].reject_reason|default:$items[i].reason.log}{/if}</textarea>
	</td>
	</tr>
	</table>
	{if $items[i].reason.id}
	<font color=red class=small>{if $items[i].reason.log}{$items[i].reason.log}{/if} by {$items[i].reason.u} on {$items[i].reason.timestamp}</font>
	{/if}
	</td>
</tr>
<tr>
	<td><b>Allow Decimal Qty</b> [<a href="javascript:void(alert('{$LANG.SKU_ALLOW_DECIMAL_NOTIFICATION|escape:javascript}'));">?</a>]</td>
	<td>
		<input type="checkbox" name="decimal_qty[{$item_id}]" {if $items[i].decimal_qty}checked{/if} value="1"> Counter
		&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="doc_allow_decimal[{$item_id}]" {if $items[i].doc_allow_decimal}checked {/if} value="1" /> Adjustment, DO, GRN, Sales Order, GRA, PO
	</td>
</tr>

{if $config.enable_replacement_items}
	<tr>
	    <td><b>Replacement Item Group</b></td>
	    <td colspan="3">
	        <input type="checkbox" onClick="toggle_replacement_group(this, '{$item_id}');" {if $items[i].ri_id}checked {/if} />
			<input type="text" readonly size="1" name="ri_id[{$item_id}]" id="inp_ri_id_{$item_id}" value="{$items[i].ri_id}" {if !$items[i].ri_id}disabled {/if} />
			<input type="text" name="ri_group_name[{$item_id}]" size="40" id="inp_ri_group_name_{$item_id}"  value="{$items[i].ri_group_name}"  {if !$items[i].ri_id}disabled {/if} />
			<span id="span_ri_loading_{$item_id}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			<div id="autocomplete_replacement_group_choices_{$item_id}" class="autocomplete"></div>
	    </td>
	</tr>
{/if}

<!-- Category Discount -->
<tr>
	<td valign="top"><b>Category Discount (%)</b>
		<a href="javascript:void(alert('This feature only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All) \n\nRequire privilege CATEGORY_DISCOUNT_EDIT to use this.'));">
			<img src="/ui/icons/information.png" align="absmiddle" />
		</a>
	</td>
	<td>
		{include file='masterfile_sku.edit.items.discount.tpl' is_edit=1 item_obj=$items[i]}
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
		{include file='masterfile_sku.edit.items.point.tpl' is_edit=1 item_obj=$items[i]}
	</td>
</tr>

{if !$config.consignment_modules}
	<tr>
		<td><b>Scale Type</b></td>
		<td>
			<select name="dtl_scale_type[{$item_id}]">
				{if isset($items[i].scale_type)}
				{assign var="cur_st" value=$items[i].scale_type}
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
			<select name="non_returnable[{$item_id}]">
				<option value="-1" {if $items[i].non_returnable eq -1}selected {/if}>inherit (Follow SKU)</option>
				<option value="1" {if $items[i].non_returnable eq 1}selected {/if}>Yes</option>
				<option value="0" {if $items[i].non_returnable eq 0}selected {/if}>No</option>
			</select>
		</td>
	</tr>
{/if}

<tr>
	<td><b>Model</b></td>
	<td>
		<input type="text" name="model[{$item_id|default:0}]" value="{$items[i].model}" />
	</td>
</tr>

<tr>
	<td><b>Width</b></td>
	<td>
		<input type="text" name="width[{$item_id}]" value="{$items[i].width|ifzero:''}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
	</td>
</tr>

<tr>
	<td><b>Height</b></td>
	<td>
		<input type="text" name="height[{$item_id}]" value="{$items[i].height|ifzero:''}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
	</td>
</tr>

<tr>
	<td><b>Length</b></td>
	<td>
		<input type="text" name="length[{$item_id}]" value="{$items[i].length|ifzero:''}" onChange="mfz(this, 2)" style="width:80px;text-align:right;" /> cm
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
									<input type="text" name="extra_info[{$item_id}][{$c}]" value="{$items[i].extra_info.$c}" {if $extra_info.data_type eq 'date'}onchange="date_validate(this);"{/if} />
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
	    Min: <input type="text" size="3" name="si_po_reorder_qty_min[{$item_id}]" value="{$items[i].po_reorder_qty_min}" class="si_po_reorder_qty" />
	    &nbsp;&nbsp;&nbsp;
	    Max: <input type="text" size="3" name="si_po_reorder_qty_max[{$item_id}]" value="{$items[i].po_reorder_qty_max}" class="si_po_reorder_qty" />
		&nbsp;&nbsp;&nbsp;
	    MOQ: <input type="text" size="3" name="si_po_reorder_moq[{$item_id}]" value="{$items[i].po_reorder_moq}" class="si_po_reorder_qty" />
		&nbsp;&nbsp;&nbsp;
		Notify Person 
		<select name="si_po_reorder_notify_user_id[{$item_id}]" class="si_po_reorder_qty">
			<option value="" {if !$items[i].po_reorder_notify_user_id}selected{/if}>--</option>
			{foreach from=$po_reorder_users key=row item=r}
				<option value="{$r.id}" {if $items[i].po_reorder_notify_user_id eq $r.id}selected{/if}>{$r.u}</option>
			{/foreach}
		</select>
		<br>
		<a href="masterfile_sku_items.po_reorder_qty_by_branch.php?a=search&show_type=1&sku_item_id={$item_id}" target="_blank">Set PO Reorder Qty by Branch</a>
	</td>
</tr>

{if $config.enable_sn_bn}
	<tr>
		<td width="100"><b>Warranty Period</b></td>
		<td>
			<input name="sn_we[{$item_id}]" size="6" value="{$items[i].sn_we}" class="r" onchange="mi(this);">&nbsp;
			<select name="sn_we_type[{$item_id}]">
				<option value="day" {if $items[i].sn_we_type eq 'day'}selected{/if}>Day(s)</option>
				<option value="week" {if $items[i].sn_we_type eq 'week'}selected{/if}>Week(s)</option>
				<option value="month" {if $items[i].sn_we_type eq 'month' || !$items[i].sn_we_type}selected{/if}>Month(s)</option>
				<option value="year" {if $items[i].sn_we_type eq 'year'}selected{/if}>Year(s)</option>
			</select>
		</td>
	</tr>
{/if}

{if $sessioninfo.privilege.MST_INTERNAL_DESCRIPTION}
	<tr>
		<td valign="top" width="100"><b>Internal Description</b></td>
		<td>
			<textarea cols="45" rows="6" onblur="uc(this)" name="internal_description[{$item_id}]">{$items[i].internal_description|escape}</textarea>
		</td>
	</tr>
{/if}

{if $config.arms_marketplace_settings}
<tr>
	<td valign="top" width="100"><b>Marketplace Description</b></td>
	<td>
		<textarea cols="45" rows="6" onblur="uc(this)" name="marketplace_description[{$item_id}]">{$items[i].marketplace_description|escape}</textarea>
	</td>
</tr>
{/if}

</table>

{*
{if $items[i].photo_count}
	<h5>SKU Application Photos</h5>
	{section name=n loop=$items[i].photo_count}
	{capture assign=p}{$image_path}sku_photos/{$items[i].sku_apply_items_id}/{$smarty.section.n.iteration}.jpg{/capture}
	<img width=110 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&img={$p|urlencode}" border=0 style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View">
	{/section}
{/if}
*}

{if $items[i].sku_apply_photos > 0}
	<h5>SKU Application Photos</h5>
	{foreach from=$items[i].sku_apply_photos item=p name=i}
	<div class="imgrollover div_photo_{$items[i].sku_apply_items_id}_{$smarty.foreach.i.iteration}">
	<img class="popup_img_{$items[i].sku_apply_items_id}_{$smarty.foreach.i.iteration}" width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p|urlencode}" border=0 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View">
	<br />
	<img class="delete_img_{$items[i].sku_apply_items_id}_{$smarty.foreach.i.iteration}" src="/ui/del.png" align="absmiddle" onclick="if (confirm('Are you sure?'))del_sku_apply_image(this.parentNode,'{$p|urlencode}', '{$items[i].sku_apply_items_id}','{$smarty.foreach.i.iteration}')"> Delete
	</div>
	{/foreach}
	<br style="clear:both;" />
{/if}

{if empty($is_new) && !$items[i].is_new}
	<h5>Photo Attachment <img src="/ui/add.png" align=absmiddle onclick="add_image({$items[i].id})"></h5>
	<div id=item_photos[{$items[i].id}]>
	{foreach from=$items[i].photos item=p name=i}
	<div class=imgrollover>
	<img width=110 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p|urlencode}" border=0 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View"><br>
	<img src="/ui/del.png" align=absmiddle onclick="if (confirm('Are you sure?'))del_image(this.parentNode,'{$p|urlencode}', 'actual_photo')"> Delete
	</div>
	{/foreach}
	</div>
{/if}
{assign var=is_new value=0}
<br style="clear:both">
{if $items[i].photos_promotion > 0}
	<h5>Promotion / POS Image <img src="/ui/add.png" align=absmiddle onclick="add_promotion_image({$items[i].id})"></h5>
	<div id="promotion_photo[{$items[i].id}]">
	{foreach from=$items[i].photos_promotion item=p name=i}
	<div id="current_promotion_img[{$items[i].id}]" class=imgrollover>
	<img width=110 height=100 align=absmiddle id="promotion_img" vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache={if $items[i].photos_promotion_time}{$items[i].photos_promotion_time}{else}1{/if}&img={$p|urlencode}" border=0 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}', {$items[i].photos_promotion_time});" title="View"><br>
	<img src="/ui/del.png" align=absmiddle onclick="if (confirm('Are you sure?'))del_image(this.parentNode,'{$p|urlencode}', 'promo_photo')"> Delete
	</div>
	{/foreach}
	</div>
{/if}
	<br style="clear:both">
</div>
<script>
toggle_gst_settings();
atom_update_gross({$item_id});
//atom_update_fulldesc({$item_id|default:0});
{if $smarty.request.a eq 'ajax_add_item'}
copy_block_po_list({$item_id});
{/if}

{if $config.enable_replacement_items}
	init_ri_autocomplete('{$item_id}');
{/if}
</script>
