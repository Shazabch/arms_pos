{*
Revision History
================
4 Apr 2007 - yinsee
- 	check for $config.sku_application_require_multics to enable/disable multics code columns

9/19/2007 1:34:30 PM -gary
- added only the last approval can appear the multics details for keyin.

9/28/2007 3:22:46 PM gary
- added display status for each approver.

1/7/2010 4:48:02 PM Andy
- Fix some javascript error

8/13/2010 10:14:09 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/19/2010 2:41:33 PM Andy
- Add config control to no inventory sku.

9/9/2010 5:04:56 PM Andy
- Fix sku trade discount code cannot show in cosignment mode some module.

6/20/2011 2:30:09 PM Andy
- Add show scale type in SKU approval page.

10/10/2011 10:52:45 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

2/2/2012 4:04:06 PM Andy
- Show missing Serial No. Information.
- Show missing Stock Reorder Information.

4/23/2012 4:44:12 PM Justin
- Added to show PO reorder qty by branch.

5/16/2012 11:12:32 AM Justin
- Fixed bugs that system show the form in fancy after add show PO reorder qty by branch.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

07/12/2013 04:21 PM Justin
- Bug fixed on having wrong indication of have_sn, it should be 2=Yes instead of 1=Yes (pre-list).

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

9/18/2014 11:09 AM Justin
- Enhanced to show Input Tax, Output Tax, Inclusive Tax.

3/12/2015 2:37 PM Andy
- Enhanced to able to change input tax, output tax, inclusive tax when under approval screen.

3/19/2015 5:58 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

4/10/2015 10:52 AM Andy
- Enhance to get trade discount percent when on sku approval screen.

7/16/2015 1:45 PM Andy
- Add assign $items[i] to $item_r.
- Remove the last approver can edit category/brand.

6/20/2016 2:47 PM Andy
- Remove SKU Approval KIV button.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

4/25/2017 1:34 PM Khausalya
- Enhanced changes from RM to use config setting. 

5/28/2019 5:23 PM William
- Added new PO Reorder Qty "Moq".
*}

<script type="javascript">
{if $gst_settings}
	// input tax
	cat_gst_settings['input_tax'] = [];
	cat_gst_settings['input_tax']['id'] = int('{$cat_gst_settings.input_tax.id}');
	cat_gst_settings['input_tax']['code'] = '{$cat_gst_settings.input_tax.code}';
	cat_gst_settings['input_tax']['rate'] = float('{$cat_gst_settings.input_tax.rate}');

	// output tax
	cat_gst_settings['output_tax'] = [];
	cat_gst_settings['output_tax']['id'] = int('{$cat_gst_settings.output_tax.id}');
	cat_gst_settings['output_tax']['code'] = '{$cat_gst_settings.output_tax.code}';
	cat_gst_settings['output_tax']['rate'] = float('{$cat_gst_settings.output_tax.rate}');

	// inclusive tax
	cat_gst_settings['inclusive_tax'] = '{$cat_gst_settings.inclusive_tax}';
{/if}
</script>

<form method=post action={$smarty.server.PHP_SELF} name="f_a">

<input type=hidden name=a value="save_approval">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=last_approval value="{$last_approval}">
<input type=hidden name=reason2 value="">
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">

{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}

<!--- show approval log -->
{if $form.approval_history_items}
	<div class="small" style="background-color:#eef; border:1px solid #99f; padding:5px;">
	<h5>Application Status</h5>
	{foreach from=$form.approval_history_items item=aitem}
	<div>
	<font color=##006600>{$aitem.timestamp} by {$aitem.u}  ({if $form.status == 1}Approved{elseif $form.status == 2}Rejected{elseif $form.status == 3}KIV{elseif $form.status == 4}Terminated{/if})</font><br>
	{if $aitem.status == 1}
	<img src=ui/checked.gif vspace=2 align=absmiddle> <b>{$aitem.log.general}</b>
	{elseif $aitem.status == 2}	
	{foreach from=$aitem.log.general item=log key=log_k}
	{if $log ne 'Others'}
	<img src=ui/deact.png vspace=2 align=absmiddle> {$log}<br>
	{/if}
	{/foreach}
	{else}
	<img src=ui/del.png vspace=2 align=absmiddle> <b>{$aitem.log.general}</b>
	{/if}
	</div>
	{/foreach}
	</div>
{/if}

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<h2>General Information (ID#{$form.id})</h2>

<table class="small" width=100% border=0 cellpadding=2 cellspacing=1>
<tr>
	<td><b>Apply By</b></td>
	<td colspan=5>{$form.username} ({$form.apply_branch_code})  {$form.added}</td>
</tr>
<tr bgcolor=#eeeeee>
	<td><b>Category</b></td>
	<td colspan=5>
	{* if $last_approval}
		<input name="category_id" class="normal" size=1 value="{$form.category_id}" readonly>
		<input id="autocomplete_category" class="normal" name="category" onclick="this.select()" value="{$form.category|escape}" size=50>
		<div id="autocomplete_category_choices" class="autocomplete" style="width:600px !important"></div>
		<div id="str_findcat">{$form.cat_tree|default:"Type the category name to search..."}</div>
	{else}
		{$form.cat_tree}
	{/if *}
	{$form.cat_tree}
	</td>
</tr>
<tr>
	<td><b>Vendor</b></td>
	<td colspan=5>{$form.vendor}</td>
</tr>
<tr bgcolor=#eeeeee>
	<td width=60 nowrap><b>SKU Type</b></td>
	<td width=100 nowrap>{$form.sku_type}
		<input type="hidden" name="sku_type" value="{$form.sku_type}" />
	</td>
	<td width=60 nowrap><b>Brand</b></td>
	<td width=50% nowrap>
	{* if $last_approval}
		<input name="brand_id" class="normal" type=hidden size=1 value="{$form.brand_id}" readonly>
		<input id="autocomplete_brand" class="normal" name="brand" onclick="this.select()" value="{$form.brand|default:"UN-BRANDED"}" size=10>
		<div id="autocomplete_brand_choices" class="autocomplete" style="width:200px !important"></div>
	{else}
		{$form.brand|default:"UN-BRANDED"}
	{/if *}
	{$form.brand|default:"UN-BRANDED"}
	</td>
	
</tr>
<tr>
	
	<td><b>Trade Discount</b></td>
	<td>
		{if $config.sku_always_show_trade_discount} <!-- consignment part -->
			{$form.default_trade_discount_code}
		{else}
			<!-- outright part -->
			{if $form.trade_discount_type == 0}
			no trade discount
			{elseif $form.trade_discount_type == 1}
			use Brand Table
			{else}
			use Vendor Table
			{/if}
		{/if}
	</td>
	
	{if ($form.trade_discount_type > 0) or ($config.sku_always_show_trade_discount)}
		<td nowrap><b>Price Type</b></td>
		<td nowrap>
			{$form.default_trade_discount_code} ({$form.trade_discount_info.rate}%)
			<input type="hidden" name="trade_discount_rate" value="{$form.trade_discount_info.rate}" />
		</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td>
	{/if}
</tr>
{if $config.enable_no_inventory_sku or $config.enable_fresh_market_sku}
<tr>
    {if $config.enable_no_inventory_sku}
	<td><b>SKU Without Inventory</b></td>
	<td>{$inherit_options[$form.no_inventory]}</td>
	{/if}
	{if $config.enable_fresh_market_sku}
		<td><b>Is Fresh Market SKU</b></td>
		<td>{$inherit_options[$form.is_fresh_market]}</td>
	{/if}
</tr>
{/if}

{if !$config.consignment_modules}
	<!-- Scale Type -->
	<tr>
		<td colspan="2"><b>Scale Type</b></td>
		<td>{$scale_type_list[$form.scale_type]|default:'--'}</td>
	</tr>
{/if}

<tr bgcolor=#eeeeee>
	<td valign=top><b>Listing Fee</b></td>
	<td colspan=5>{$form.listing_fee_type}

	<!-- fee -->
	{if $form.listing_fee_type eq 'Listing Fee'}
	<div><b>Listing Fee Amount</b>: {$config.arms_currency.symbol} {$form.listing_fee_remark.amount|number_format:2}
	({$form.listing_fee_remark.when} {if $form.listing_fee_remark.dn}#{$form.listing_fee_remark.dn}{/if})</div>
	{/if}

	<!-- package -->
	{if $form.listing_fee_type eq 'Package'}
	<div>
	<b>Package Amount</b>: {$config.arms_currency.symbol} {$form.listing_fee_remark.amount|number_format:2} ({$form.listing_fee_remark.when})<br>
	<b>No. of SKU</b>:  {$form.listing_fee_remark.count}
	{if $form.listing_fee_remark.first_sku_id>0}(SKU Package REF#: {$form.listing_fee_remark.first_sku_id} {if $form.listing_fee_remark.dn}#{$form.listing_fee_remark.dn}{/if}){/if}
	</div>
	{/if}

	<!-- package -->
	{if $form.listing_fee_type eq 'Package2'}
	<div>
	<b>Package Amount</b>: {$config.arms_currency.symbol} {$form.listing_fee_remark.amount|number_format:2} ({$form.listing_fee_remark.when} {if $form.listing_fee_remark.dn}#{$form.listing_fee_remark.dn}{/if})<br>
	<b>No. of Variety</b>:  {$form.listing_fee_remark.count}
	</div>
	{/if}

	<!-- inkind -->
	{if $form.listing_fee_type eq 'In Kind'}
	<table border=0 cellspacing=2 cellpadding=4 >
	<tr bgcolor=#ffffff><th>Item</th><th>Qty</th><th>Unit Cost</th><th>Total Cost</th></tr>
	{assign var=n value=0}
	{foreach name=f from=$form.listing_fee_remark.item item=ft}
	<tr>
		<td>{$form.listing_fee_remark.item[$smarty.foreach.f.index]}</td>
		<td>{$form.listing_fee_remark.qty[$smarty.foreach.f.index]|qty_nf}</td>
		<td>{$form.listing_fee_remark.cost[$smarty.foreach.f.index]|number_format:$config.global_cost_decimal_points}</td>
		<td>{$form.listing_fee_remark.total_cost[$smarty.foreach.f.index]}</td>
	</tr>
	<!-- {$n++} -->
	{/foreach}
	</table>
	{/if}
	</td>
</tr>
{if $config.sku_application_require_multics && $last_approval}
<tr>
	<td><b>{$config.link_code_name}</b></td>
	<td>
		<table class=small>
		<tr>
			<td>Department</td>
			<td>
			<input type="text" id="multics_dept" name="multics_dept" value="{$form.multics_dept}"/>
			<div id="multics_dept_choices" class="autocomplete"></div>
			</td>
			<td>Section</td>
			<td>
			<input type="text" id="multics_section" name="multics_section" value="{$form.multics_section}"/>
			<div id="multics_section_choices" class="autocomplete"></div>
			</td>
			<td>Category</td>
			<td>
			<input type="text" id="multics_category" name="multics_category" value="{$form.multics_category}"/>
			<div id="multics_category_choices" class="autocomplete"></div>
			</td>
		</tr>
		<tr>
			<td>Brand</td>
			<td>
			<input type="text" id="multics_brand" name="multics_brand" value="{$form.multics_brand}"/>
			<div id="multics_brand_choices" class="autocomplete"></div>
			</td>
			<td>Price Type</td>
			<td>
			<select name=multics_pricetype>
			<option value="">Please Select</option>
			<option value="N1" {if $form.multics_pricetype eq "N1"}selected{/if}>N1</option>
			<option value="N2" {if $form.multics_pricetype eq "N2"}selected{/if}>N2</option>
			<option value="N3" {if $form.multics_pricetype eq "N3"}selected{/if}>N3</option>
			<option value="B1" {if $form.multics_pricetype eq "B1"}selected{/if}>B1</option>
			<option value="B2" {if $form.multics_pricetype eq "B2"}selected{/if}>B2</option>
			<option value="B3" {if $form.multics_pricetype eq "B3"}selected{/if}>B3</option>
			<option value="B5" {if $form.multics_pricetype eq "B5"}selected{/if}>B5</option>
			<option value="B6" {if $form.multics_pricetype eq "B6"}selected{/if}>B6</option>
			<option value="B7" {if $form.multics_pricetype eq "B7"}selected{/if}>B7</option>
			<option value="B8" {if $form.multics_pricetype eq "B8"}selected{/if}>B8</option>
			<option value="B9" {if $form.multics_pricetype eq "B9"}selected{/if}>B9</option>
			</select>
			</td>
		</tr>
		</table>
	</td>
</tr>
<!--tr>
	<td><b>{$config.link_code_name}</b></td>
	<td colspan=5>
		<table>
		<tr>
			<td bgcolor=#eeeeee><b>Department</b></td>
			<td>{$form.multics_dept}</td>
			<td bgcolor=#eeeeee><b>Section</b></td>
			<td>{$form.multics_section}</td>
			<td bgcolor=#eeeeee><b>Category</b></td>
			<td>{$form.multics_category}</td>
		</tr>
		<tr>
			<td bgcolor=#eeeeee><b>Brand</b></td>
			<td>{$form.multics_brand}</td>
			<td bgcolor=#eeeeee><b>Price Type</b></td>
			<td>{$form.multics_pricetype}</td>
		</tr>
		</table>
	</td>
</tr-->
{/if}

{if $config.enable_sn_bn}
	<tr>
		<td><b>Use Serial No</b></td>
		<td>
			{if $form.have_sn eq '0'}No
			{elseif $form.have_sn eq 1}Yes (Pre-list)
			{elseif $form.have_sn eq 2}Yes
			{/if}
		</td>
	</tr>
{/if}

<tr valign="top">
	<td><b>PO Reorder Qty</b></td>
	<td nowrap>
	    Min: {$form.po_reorder_qty_min|default:'-'}
	    &nbsp;&nbsp;&nbsp;
	    Max: {$form.po_reorder_qty_max|default:'-'}
	    &nbsp;&nbsp;&nbsp;
	    MOQ: {$form.po_reorder_moq|default:'-'}
		{if !$config.consignment_modules}
			&nbsp;&nbsp;&nbsp;
			<span id="qty_setup">
				{if $form.po_reorder_qty_by_branch.min || $form.po_reorder_qty_by_branch.max}
					<img src="ui/checked.gif" align="absmiddle">
				{else}
					<img src="ui/unchecked.gif" align="absmiddle">
				{/if} Overwrite PO Reorder qty by Branch
			</span>
			
			{if $form.po_reorder_qty_by_branch.min || $form.po_reorder_qty_by_branch.max}
				<br />
				<br />
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
<tr>
	<td><b>Note</b></td>
	<td colspan=5>{$form.note|nl2br|default:'<i>- no comment was added -</i>'}</td>
</tr>

{if $gst_settings}
<tr>
	<td><b>Input Tax</b></td>
	<td colspan="5">
		{if $smarty.request.a eq 'approval'}
			<select name="mst_input_tax" onChange="SKU_APPROVAL_FORM.sku_input_tax_changed();">
				<option value="-1" {if $form.mst_input_tax eq -1}selected{/if} ori_text="Inherit (Follow Category)" id="opt_sku_inherit_cat_input_tax">Inherit (Follow Category)</option>
				{foreach from=$input_tax_list key=rid item=r}
					<option value="{$r.id}" {if $form.mst_input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
				{/foreach}
			</select>
		{else}
			{if $form.mst_input_tax eq -1}
				Inherit (Follow Category)
			{else}
				{foreach from=$input_tax_list key=rid item=r}
					{if $form.mst_input_tax eq $r.id}{$r.code} - {$r.description}{/if}
				{/foreach}
			{/if}			
		{/if}
      
	</td>
</tr>
<tr>
	<td><b>Output Tax</b></td>
	<td colspan="5">
		{if $smarty.request.a eq 'approval'}
			<select name="mst_output_tax" onchange="SKU_APPROVAL_FORM.sku_output_tax_changed();">
				<option value="-1" {if $form.mst_output_tax eq -1}selected{/if} ori_text="Inherit (Follow Category)" id="opt_sku_inherit_cat_output_tax">Inherit (Follow Category)</option>
				{foreach from=$output_tax_list key=rid item=r}
					<option data-rate="{$r.rate}" value="{$r.id}" {if $form.mst_output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
				{/foreach}
			</select>
		{else}
			{if $form.mst_output_tax eq -1}
				Inherit (Follow Category)
			{else}
				{foreach from=$output_tax_list key=rid item=r}
					{if $form.mst_output_tax eq $r.id}{$r.code} - {$r.description}{/if}
				{/foreach}
			{/if}
		{/if}
	</td>
</tr>
<tr>
	<td><b>Selling Inclusive Tax</b></td>
	<td>
		{if $smarty.request.a eq 'approval'}
			<span style="display:none;">
				<select name="mst_inclusive_tax" onchange="SKU_APPROVAL_FORM.sku_inclusive_tax_changed();">
					{foreach from=$inherit_options key=k item=val}
					<option value="{$k}" {if $form.mst_inclusive_tax eq $k}selected {/if} {if $k eq 'inherit'}ori_text="{$val}" id="opt_sku_inherit_cat_inclusive_tax"{/if}>{$val}</option>
					{/foreach}
				</select>
			</span>			
		{/if}
		{foreach from=$inherit_options key=k item=val}
			{if $form.mst_inclusive_tax eq $k}
				{$val} 
				{if $k eq 'inherit'}
					{$inherit_options[$cat_gst_settings.inclusive_tax]}
				{/if}
			{/if}
		{/foreach}
	</td>
</tr>
{/if}

</table>
<div style="border:1px solid #ff0; background:#ff9">
<img src=ui/arrow_up.png align=absmiddle>
<select class="small" id=approval[general] name=approval[general] onchange="sel_reason('general')">
<option value="">-- Select Approve or Reason to Reject --</option>
<option value="Approve">Approve</option>
<option value="Reject">Reject</option>
</select>
<div id=reject_box[general] class="small" style="text-align:left; display:none; width:300px; white-space: nowrap">
<h5>Reject Reason</h5>
<input type=checkbox name="reason[general][]" value="Spelling Mistake">Spelling Mistake<br>
<input type=checkbox name="reason[general][]" value="Wrong Category">Wrong Category<br>
<input type=checkbox name="reason[general][]" value="Wrong SKU Type">Wrong SKU Type<br>
<input type=checkbox name="reason[general][]" value="Wrong Vendor">Wrong Vendor<br>
<input type=checkbox name="reason[general][]" value="Wrong Brand">Wrong Brand<br>
<input type=checkbox name="reason[general][]" value="No Listing Fee">No Listing Fee<br>
<input type=checkbox name="reason[general][]" value="Listing fee too low">Listing Fee too low<br>
<input type=checkbox name="reason[general][]" value="Others" onchange="$('approval_other[general]').disabled=(!this.checked);$('approval_other[general]').focus();">Other reasons:<br>
<input id=approval_other[general] name=approval_other[general] size=30 disabled>
</div>
</div>

{section name=i loop=$items}
	{assign var=item_r value=$items[i]}
<br>

	<!--- show approval log --->
{if $form.approval_history_items}
    {assign var="item_n" value=`$smarty.section.i.iteration-1`}

	<div class="small" style="background-color:#eef; border:1px solid #99f; padding:5px;">
	<h5>Application Status</h5>
	{foreach from=$form.approval_history_items item=aitem}
	<div>
	<font color=##006600>{$aitem.timestamp} by {$aitem.u} ({if $form.status == 1}Approved{elseif $form.status == 2}Rejected{elseif $form.status == 3}KIV{elseif $form.status == 4}Terminated{/if})</font><br>
	{if $aitem.status == 1}
	<img src=ui/checked.gif vspace=2 align=absmiddle> <b>{$aitem.log[$item_n]}</b>
	{elseif $aitem.status == 2}
	{foreach from=$aitem.log[$item_n] item=log key=log_k}
	{if $log ne 'Others'}
	<img src=ui/deact.png vspace=2 align=absmiddle> {$log}<br>
	{/if}
	{/foreach}
	{else}
	<img src=ui/del.png vspace=2 align=absmiddle> <b>{$aitem.log[$item_n]}</b>
	{/if}
	</div>
	{/foreach}
	</div>
{/if}

{if $items[i].product_matrix}
{include file=masterfile_sku_approval.atom_matrix.tpl}
{else}
{include file=masterfile_sku_approval.atom_variety.tpl}
{/if}

<br>
<div style="border:1px solid #ff0; background:#ff9">
<img src=ui/arrow_up.png align=absmiddle>

<select name="approval[{$smarty.section.i.index}]" class="small" id="approval[{$smarty.section.i.index}]" onchange="sel_reason('{$smarty.section.i.index}')">
<option value="">-- Select Approve or Reason to Reject --</option>
<option value="Approve">Approve</option>
<option value="Reject">Reject</option>
</select>
<div id=reject_box[{$smarty.section.i.index}] class="small" style="text-align:left; display:none; width:300px; white-space: nowrap">
<h5>Reject Reason</h5>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Spelling Mistake">Spelling Mistake<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Wrong Article or Manufacturer Code">Wrong Article or Manufacturer Code<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Inproper Description">Inproper Description<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Selling Price too high">Selling Price too high<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Selling Price too low">Selling Price too low<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Profit Margin too low">Profit Margin too low<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Wrong Photo">Wrong Photo<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Photo not clear">Photo not clear<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Product not suitable">Product not suitable<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Not encouraged">Not encouraged<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Wrong Product Matrix table">Wrong Product Matrix table<br>
<input type=checkbox name="reason[{$smarty.section.i.index}][]" value="Others" onchange="$('approval_other[{$smarty.section.i.index}]').disabled=(!this.checked)">Other reasons: <input id=approval_other[{$smarty.section.i.index}] name=approval_other[{$smarty.section.i.index}] size=30 disabled><br>
</div>
</div>
{/section}

<script>
last_idx = '{$smarty.section.i.index}';
</script>

{if $smarty.request.a eq 'approval'}
<p align=center id=bsubmit>
<input type=button value="Submit" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Approve All" style="background-color:#f90; color:#fff;" onclick="do_approve_all()">
<input type=button value="Terminate" style="background-color:#900; color:#fff;" onclick="do_terminate()">
{*<input type=button value="KIV (Pending)" style="background-color:#09f; color:#fff;" onclick="do_kiv()">*}
</p>
{else}
<p align=center>
<input type=button onclick="close_window('/masterfile_sku.php')" value="Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
</p>
{/if}
</form>

{if $last_approval}
{literal}
<script>
/*category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category", {
	afterUpdateElement: function (obj,li)
	{
		this.defaultParams = '';
		var s = li.title.split(",");
		document.f_a.category_id.value = s[0];
		sel_category(obj,s[1]);
	}});

new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand", { afterUpdateElement: function (obj, li) { document.f_a.brand_id.value = li.title; }});*/
</script>
{/literal}

<script>
{if $config.sku_application_require_multics && $last_approval}
{literal}
new Ajax.Autocompleter("multics_dept", "multics_dept_choices", "multics_autocomplete.php", {paramName: "dept", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_section", "multics_section_choices", "multics_autocomplete.php", {paramName: "sect", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_category", "multics_category_choices", "multics_autocomplete.php", {paramName: "cat", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_brand", "multics_brand_choices", "multics_autocomplete.php", {paramName: "brand", afterUpdateElement: function (obj, li) { obj.value = li.title }});
{/literal}
{/if}
</script>
{/if}
<script>
{literal}
SKU_APPROVAL_FORM.initialize();
{/literal}
</script>
