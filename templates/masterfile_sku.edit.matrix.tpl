{*
Revision History
================
7/26/2010 9:25:44 AM Alex
- add 'softline','outright' keywords checking to allow add matrix link

8/13/2010 10:12:36 AM Andy
- Add can choose replacement item group when apply/edit sku.

9/6/2011 6:05:32 PM Justin
- Fixed the bugs where cannot display all valuables once errors returned from PHP.
- Added new validation to include/exclude hidden field.

10/17/2011 1:46:26 PM Alex
- Modified the round up for cost to base on config.

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

3/21/2012 4:42:43 PM Justin
- Added a new config "masterfile_enable_check_desc" to enable/disable whether need to do checking for product/receipt description for quote (").

3/23/2012 9:57:43 AM Justin
- Changed the config name "masterfile_enable_check_desc" into "masterfile_disallow_double_quote".

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

7/10/2014 9:57 AM Justin
- Enhanced to have max length for MCode and Artno.

8/20/2014 5:55 PM DingRen
- add Input Tax, Output Tax, Inclusive Tax

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

9/25/2014 11:59 AM Justin
- Enhanced to show GST description from drop down list in full.

1/2/2015 4:43 PM Justin
- Enhanced to show GST inherit information.

4/17/2015 10:40 AM Andy
- Increase the artno maxlength from 20 to 30.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

5/12/2017 17:04 PM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long
*}
{if $items}
{assign var=item_id value=`$items[i].id`}
{else}
{assign var=is_new value=1}
{assign var=item_id value=$smarty.now}
{/if}
{if $is_new || $items[i].is_new}
	<input type=hidden class="new" name=is_new[{$item_id}] value={$item_id}>
{/if}
<div id="item[{$item_id}]" class="stdframe" style="margin-bottom:10px;{if $items[i].is_parent}border:1px solid #fa6;background:#fe9;{/if}">

{if $is_new || !$items[i].sku_item_code}
	<div style="float:right"><a href="javascript:void(cancel_item('{$item_id}'))"><img src=/ui/del.png align=absmiddle border=0> Delete</a></div>
{/if}

<h3 id=desc_{$item_id}>SKU Item {$items[i].sku_item_code}</h3>

{if !$items[i].is_parent && $items[i].sku_item_code}
<button onclick="return set_as_parent({$item_id})">Set as Parent SKU</button>
{/if}

{if $errm.items[$item_id]}
<div class=errmsg><ul>
{foreach from=$errm.items[$item_id] item=e}
<li> {$e}
{/foreach}
</ul></div>
{/if}

<input name="item_type[{$item_id|default:0}]" value="matrix" type=hidden>
<input type=hidden name=packing_uom_id[{$item_id}] value=1>
<table  border=0 cellpadding=4 cellspacing=1>
<tr>
<td colspan=4><input type=checkbox name="own_article[{$item_id|default:0}]" onclick="matrix_article_toggle({$item_id|default:0}, this.checked)" {if $items[i].own_article}checked{/if} value="1"> <b>Enter Individual Article No. or Manufacturer's Code for Matrix</b></td>
</tr>
<tr id="item_article[{$item_id|default:0}]" {if $items[i].own_article|default:1}style="display:none"{/if}>
	<td><b>Article No.</b></td>
	<td><input data-item_id="{$item_id}" onchange="check_artmcode(this,'artno')" name="artno[{$item_id|default:0}]" value="{$items[i].artno}" maxlength="30">
	<img src=ui/rq.gif align=absbottom title="Required Field"></td>
	<td><b>MCode</b></td>
	<td><input onchange="check_artmcode(this,'mcode')" name="mcode[{$item_id|default:0}]" value="{$items[i].mcode}" maxlength="15">
	<img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr>
	<td><b>Product Description</b></td>
	<td colspan=3><input onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this);add_to_sku_receipt_desc('description-{$item_id|default:0}', 'receipt_description-{$item_id|default:0}');"{else}onchange="add_to_sku_receipt_desc('description-{$item_id|default:0}', 'receipt_description-{$item_id|default:0}');"{/if} size=80 id="description-{$item_id|default:0}" name="description[{$item_id|default:0}]" value="{$items[i].description}"></td>
</tr>

<tr {if !$last_approval}style="display:none;"{/if} class="tr_receipt_desc">
	<td valign=top><b>Receipt Description [<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b></td>
	<td colspan=3><input onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this); update_sku_receipt_desc(this);"{else}onchange="update_sku_receipt_desc(this)"{/if} size=40 maxlength=30 id="receipt_description-{$item_id|default:0}" name="receipt_description[{$item_id|default:0}]" value="{$items[i].receipt_description|escape}" {if !$last_approval}disabled {/if}  class="inp_receipt_desc"> <img src=ui/rq.gif align=absbottom title="Required Field">
	<input class="receipt_desc" type="hidden" value="receipt_description-{$item_id}" />
	</td>
</tr>

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
		<select name="dtl_output_tax[{$item_id}]" onchange="calc_matrix_gst({$item_id});" class="dtl_output_tax">
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
		<select name="dtl_inclusive_tax[{$item_id}]" style="width:300;" onchange="calc_matrix_gst({$item_id});" class="dtl_inclusive_tax">
          <option value="inherit" {if $items[i].inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow SKU)</option>
          <option value="yes" {if $items[i].inclusive_tax eq "yes"}selected {/if}>Yes</option>
          <option value="no" {if $items[i].inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>
</table>
<br>

{* include file="masterfile_sku_application.atom_photos.tpl" *}

<h4>Product Matrix</h4>
<ul>
<li>Enter the Varieties into Row (1,2,3.. for Sizes) and Column (A,B,C.. for Colours) headers - grey colour box
<li>Enter the Article No. or Manufacturer's Code into the content cells
</ul>
<div id="matrix[{$item_id|default:0}]">
{if $items[i].tb}
<table>
{foreach name=r from=$items[i].tb item=tb}
<tr>
{foreach name=c from=$tb item=tbc}
<td>
	<input class="nth" name="tb[{$item_id}][{$smarty.foreach.r.index}][{$smarty.foreach.c.index}]" value="{$tbc}"><br>
	<input class="nth" name="tbm[{$item_id}][{$smarty.foreach.r.index}][{$smarty.foreach.c.index}]" value="{$items[i].tbm[$smarty.foreach.r.index][$smarty.foreach.c.index]}">
</td>
{/foreach}

<td><input class="nth" name="tbprice[{$item_id}][{$smarty.foreach.r.index}]" value="{$items[i].tbprice[$smarty.foreach.r.index]}"></td>
{if $config.do_enable_hq_selling and $BRANCH_CODE eq 'HQ'}
	<td><input class="nth" name="tbhqprice[{$item_id}][{$smarty.foreach.r.index}]" value="{$items[i].tbhqprice[$smarty.foreach.r.index]|number_format:2}"></td>
{/if}
<td><input class="nth" name="tbcost[{$item_id}][{$smarty.foreach.r.index}]" value="{$items[i].tbcost[$smarty.foreach.r.index]|number_format:$config.global_cost_decimal_points}"></td>
{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
	<td><input class="nth" name="tbhqcost[{$item_id}][{$smarty.foreach.r.index}]" value="{$items[i].tbhqcost[$smarty.foreach.r.index]|number_format:$config.global_cost_decimal_points}"></td>
{/if}
</tr>
{/foreach}
</table>
{/if}
</div>

</div>
<script>
{if $items[i].tb}
{* call again to generate "nice" table *}
tb_expand({$item_id|default:0}, 0, 0);
{else}
tb_expand({$item_id|default:0}, 6, 6);
{/if}

//atom_update_gross({$item_id|default:0});
</script>
