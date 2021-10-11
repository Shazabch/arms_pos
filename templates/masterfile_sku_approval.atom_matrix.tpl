{*
REVISON HISTORY
===============
9/28/2007 12:27:33 PM gary
- under sku approval, show items with same artno in same category (to prevent duplicate application)

1/7/2010 4:18:25 PM Andy
- Fix sku application approval & status scrren din't show mcode problem

3/22/2010 12:33:22 PM Andy
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

10/10/2011 10:52:45 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

5/20/2013 12:00 PM Fithri
- bugfix : receipt desc should be 40 chars max

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

8/21/2014 1:49 PM Justin 
- Enhanced to show Input, Output & Inclusive Taxes.
- Enhanced to show GST (%) and selling price after/before GST.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

12/24/2014 9:47 AM Justin
- Bug fixed on GP does not calculate correctly.

3/12/2015 2:37 PM Andy
- Enhanced to able to change input tax, output tax, inclusive tax when under approval screen.
- Fix matric table show wrong gst amt.

3/19/2015 5:58 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

4/10/2015 10:48 AM Andy
- Fix sku inclusive tax use selecting real inclusive tax when inclusive tax is inherit.
- Fix sku approval screen wrong matrix gp and gp percent.
- Enhance to update cost when user submit sku approval.

7/20/2015 4:08 PM Andy
- Enhanced to show real inclusive tax/input tax/output tax when is inherit.
- Enhanced to auto update sku & sku_items gst inherit info.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

5/11/2017 11:04 AM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/3/2018 10:00 AM KUAN YEH
- Receipt Description no need to check last approval, change to always show at default
- Last approval can edit the receipt description.
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
<input name="item_type[{$items[i].id}]" value="matrix" type="hidden" />

<table class="small" border=0 cellpadding=1 cellspacing=1>
<tr>
{if $items[i].artno}
	<td><b>Article No.</b></td>
	<td class=hilite>{$items[i].artno}
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
{if $items[i].mcode}
	<td><b>MCode</b></td>
	<td class=hilite>{$items[i].mcode}</td>
{/if}
</tr>
<tr bgcolor=#eeeeee>
	<td valign=top><b>Product Desccription</b></td>
	{if $last_approval && $smarty.request.a eq 'approval'}
	<td nowrap><input class="normal" onblur="uc(this)" onchange="check_description(this);add_to_sku_receipt_desc('description[{$smarty.section.i.index}]','receipt_description[{$smarty.section.i.index}]')" size="80" id="description[{$smarty.section.i.index}]" name="description[{$items[i].id}]" value="{$items[i].description|escape}">
	{else}
	<td nowrap class=hilite>{$items[i].description}</b>
	{/if}
	</td>
</tr>

<tr>
	<td valign=top><b>Receipt Description [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>

	{if $last_approval}
				  
	<td colspan=3><input class="normal" onblur="uc(this)" onchange="check_description(this); update_sku_receipt_desc(this);" size="50" maxlength="40" id="receipt_description[{$smarty.section.i.index}]" name="receipt_description[{$items[i].id}]" value="{$items[i].receipt_description|default:$items[i].description|escape}"> <img src=ui/rq.gif align="absbottom" title="Required Field">
	{else}
	<td nowrap class=hilite>{$items[i].receipt_description}</td>
	{/if}
</tr>

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
				<select name="dtl_output_tax[{$items[i].id}]" onchange="calc_matrix_gst('{$items[i].id}');" class="dtl_output_tax">
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
					<select name="dtl_inclusive_tax[{$items[i].id}]" style="width:300;" onchange="calc_matrix_gst('{$items[i].id}');" class="dtl_inclusive_tax">
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
</table>
<br>

{assign var=gst_rate value=$items[i].output_tax_rate}
{assign var=inclusive_tax value=$items[i].real_inclusive_tax}

{include file="masterfile_sku_approval.atom_photos.tpl"}

<h5>Product Matrix</h5>
<div>
{if $items[i].tb}
<table class="small grid" cellspacing=1 cellpadding=1 border=0>
{foreach name=r from=$items[i].tb item=tb}
{assign var=r_index value=$smarty.foreach.r.index}
  <tr>
    {foreach name=c from=$tb item=tbc}
      {assign var=c_index value=$smarty.foreach.c.index}
      {if $smarty.foreach.r.index==0 or $smarty.foreach.c.index==0}
      <th bgcolor="#ffffff" {if $r_index>0}rowspan="2"{/if}>{$tbc}</th>
      {else}
      <td>{$tbc|default:"-"}</td>
      {/if}
    {/foreach}
	{if $r_index==0}
		<th bgcolor="#ffffff">Selling Price</th>
		{if $gst_settings}
			<th bgcolor="#ffffff">GST
				(<span id="span_gst_rate_{$items[i].id}">{$gst_rate|default:'0'}</span>%)</b>
			</th>
			<th bgcolor="#ffffff">Selling Price 
				<span id="span_gst_indicator_{$items[i].id}">{if $inclusive_tax eq 'yes'}Before{else}After{/if}</span>
				GST
			</th>
		{/if}
		{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
			<th bgcolor="#ffffff">HQ Selling Price</th>
		{/if}
		<th bgcolor="#ffffff">Cost Price</th>
		{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
			<th bgcolor="#ffffff">HQ Cost</th>
		{/if}
		<th bgcolor="#ffffff">Gross Profit</th>
		<th bgcolor="#ffffff">GP(%)</th>
    {else}
		{* Selling Price *}
		<td rowspan="2">
			<input type="hidden" name="tbprice[{$items[i].id}][{$r_index}]" value="{$items[i].tbprice[$r_index]}" />
			{$items[i].tbprice[$smarty.foreach.r.index]|number_format:2}
		</td>
		
		{assign var=tbprice value=$items[i].tbprice[$smarty.foreach.r.index]}
		{assign var=nett_price value=$items[i].tbprice[$smarty.foreach.r.index]}
		{if $gst_settings}
			{if $inclusive_tax eq "yes"}
				{assign var=item_total_gst_rate value=$gst_rate+100}
				{assign var=gst_amt value=$tbprice/$item_total_gst_rate*$gst_rate}
				{assign var=gst_amt value=$gst_amt|round2}
				{assign var=gst_selling_price value=$tbprice-$gst_amt}
				{assign var=nett_price value=$gst_selling_price}
			{else}
				{assign var=gst_amt value=$tbprice*$gst_rate/100}
				{assign var=gst_amt value=$gst_amt|round2}
				{assign var=gst_selling_price value=$tbprice+$gst_amt}
			{/if}
			
				
			<td rowspan="2">
				<span id="span_gst_amt-{$items[i].id}-{$r_index}">{$gst_amt|number_format:2}</span>
			</td>
			<td rowspan="2">
				<span id="span_gst_selling_price-{$items[i].id}-{$r_index}">{$gst_selling_price|number_format:2}</span>
				<input type="hidden" name="tbprice_gst[{$items[i].id}][{$r_index}]" value="{$gst_selling_price}" />
			</td>
		{/if}
		
		{* HQ Selling *}
		{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
			<td rowspan="2">{$items[i].tbhqprice[$smarty.foreach.r.index]|number_format:2}</td>
		{/if}
		
		{* Cost *}
		{assign var=p value=`$nett_price-$items[i].tbcost[$smarty.foreach.r.index]`}
		<td rowspan="2">
			<input type="hidden" name="tbcost[{$items[i].id}][{$r_index}]" value="{$items[i].tbcost[$r_index]}" />
			<span id="span_tbcost-{$items[i].id}-{$r_index}">{$items[i].tbcost[$smarty.foreach.r.index]|number_format:$config.global_cost_decimal_points}</span>
		</td>
		
		{* HQ Cost *}
		{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
			<td rowspan="2">{$items[i].tbhqcost[$smarty.foreach.r.index]|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td rowspan="2"><span id="span_gp-{$items[i].id}-{$r_index}">{$p|number_format:4}</span></td>
		<td rowspan="2"><span id="span_gp_per-{$items[i].id}-{$r_index}">{$p*100/$nett_price|number_format:4}</span>%</td>
      </tr>
      <tr>
      {foreach name=n from=$items[i].tbm.$r_index item=tbm}
            {if $smarty.foreach.n.index>0}
              <td>{$tbm|default:"-"}</td>
           {/if}
      {/foreach}
    {/if}
  </tr>
{/foreach}
</table>
{/if}
</div>
