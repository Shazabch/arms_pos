{*
Revision History
================
12 Apr 2007 - yinsee
- add back the "Delete" option, but only show when Variety is enabled

5/16/2007 1:54:03 PM - yinsee
- fix a bug when no mcode/artno in table, table row/column got "eaten" during validate fail

6/7/2007 10:41:11 AM - yinsee
- ignore variety, Delete button always show

3/19/2010 5:25:25 PM Andy
- Automatically show receipt description if user is the last approval
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

7/14/2011 12:11:44 PM Andy
- Fix 'Product Matrix' instruction word. 

3/2/2012 4:56:42 PM Justin
- Added new function to take off quote (") when found it is keyed in by user for Product/Receipt Description.

3/21/2012 4:42:43 PM Justin
- Added a new config "masterfile_enable_check_desc" to enable/disable whether need to do checking for product/receipt description for quote (").

3/23/2012 9:57:43 AM Justin
- Changed the config name "masterfile_enable_check_desc" into "masterfile_disallow_double_quote".

5/20/2013 12:00 PM Fithri
- bugfix : receipt desc should be 40 chars max

8/29/2013 5:17 PM Fithri
- automatically strips out character that is not 0-9,a-Z,-_/ and space in artno field

9/3/2013 2:50 PM Fithri
- add checking whether allow special chars in artno

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

7/10/2014 9:57 AM Justin
- Enhanced to have max length for MCode and Artno.

8/21/2014 1:49 PM Justin 
- Enhanced to have Input, Output & Inclusive Taxes.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

9/25/2014 11:59 AM Justin
- Enhanced to show GST description from drop down list in full.

1/2/2015 4:43 PM Justin
- Enhanced to show GST inherit information.

3/19/2015 5:58 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

4/17/2015 10:40 AM Andy
- Increase the artno maxlength from 20 to 30.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/3/2018 11:00 PM KUAN YEH
- Receipt Description no need to check last approval, change to always show at default

*}
<div class="card mx-3">
	<div class="card-body">
<div style="float:right"><a href="javascript:void(cancel_item('item[{$item_n}]',{$items[$item_n].id|default:0}))"><img src=/ui/del.png align=absmiddle border=0> Delete</a></div>

		
{if $item_n}
<h3>Variety {$item_n}</h3>
{else}
<h3>Default Item</h3>
{/if}

{if $errm.items[$item_n]}
<div class=errmsg><ul>
{foreach from=$errm.items[$item_n] item=e}
<div class="alert alert-danger"><li> {$e}</li></div>
{/foreach}
</ul></div>
{/if}

<input name="item_type[{$item_n|default:0}]" value="matrix" type=hidden>
<input type=hidden name=packing_uom_id[{$item_n}] value=1>
<table  border=0 cellpadding=4 cellspacing=1>
<tr>
<div class="form-inline form-label">
	<input  type=checkbox name="own_article[{$item_n|default:0}]" onclick="matrix_article_toggle({$item_n|default:0}, this.checked)" {if $items[$item_n].own_article}checked{/if} value="1"> &nbsp;&nbsp;<b >Enter Individual Article No. or Manufacturer's Code for Matrix</b>
</div>
</tr>
<tr id="item_article[{$item_n|default:0}]" {if $items[$item_n].own_article|default:1}style="display:none"{/if}>
	<b class="form-label mt-2">Article No<span class="text-danger"> *</span></b>
	<input class="form-control" onchange="{if !$config.sku_artno_allow_specialchars}correct_artno(this);{/if}check_artmcode(this,'artno')" name="artno[{$item_n|default:0}]" value="{$items[$item_n].artno}" class="input_artno" item_id="{$item_n}" maxlength="30"></td>
	<b class="form-label">MCode <span class="text-danger"> *</span></b>
	<input class="form-control" onchange="check_artmcode(this,'mcode')" name="mcode[{$item_n|default:0}]" value="{$items[$item_n].mcode}" maxlength="15"></td>
</tr>
<tr>
	<b class="form-label mt-2">Product Description<span id="rq_img1" class="text-danger"> *</span></b>

	<input class="form-control" onchange="uc(this); {if $config.masterfile_disallow_double_quote}check_description(this);{/if} add_to_sku_receipt_desc('description-{$item_n|default:0}','receipt_description-{$item_n|default:0}');" size="80" id="description-{$item_n|default:0}" name="description[{$item_n|default:0}]" value="{$items[$item_n].description}">
	
</tr>

<tr> 
	<b class="form-label mt-2">Receipt Description  <span class="text-danger"> *</span>[<a href="javascript:void(alert('Max 40 characters for alphabetical character (Example: English) \nMax 13 characters for non alphabetical character (Example: Chinese)'))">?</a>]</b>
	<input class="form-control" onblur="uc(this)" {if $config.masterfile_disallow_double_quote}onchange="check_description(this); update_sku_receipt_desc(this);"{else}onchange="update_sku_receipt_desc(this)"{/if} size="50" maxlength="40" id="receipt_description-{$item_n|default:0}" name="receipt_description[{$item_n|default:0}]" value="{$items[$item_n].receipt_description|escape}" class="inp_receipt_desc"> 
	<input class="form-control receipt_desc" type="hidden" value="receipt_description-{$item_n|default:0}" /> 

</tr>

<tr class="gst_settings">
	<b class="form-label mt-2">Input Tax</b>
		<select  name="dtl_input_tax[{$item_n}]" class="form-control dtl_input_tax">
			<option value="-1" {if $items[$item_n].input_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$input_tax_list key=rid item=r}
				<option value="{$r.id}" {if $items[$item_n].input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>

<tr class="gst_settings">
	<b class="form-label">Output Tax</b>
		<select class="form-control" name="dtl_output_tax[{$item_n}]" onchange="calc_matrix_gst({$item_n});" class="dtl_output_tax">
			<option value="-1" {if $items[$item_n].output_tax eq -1}selected{/if}>Inherit (Follow SKU)</option>
			{foreach from=$output_tax_list key=rid item=r}
				<option data-rate="{$r.rate}" value="{$r.id}" {if $items[$item_n].output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}

		</select>
	</td>
</tr>

<tr style="{if !$gst_settings or ($global_gst_settings.inclusive_tax eq 'yes' and (!isset($items.$item_n.inclusive_tax) or $items.$item_n.inclusive_tax eq 'inherit'))}display:none;{/if}">
	<b class="form-label">Selling Price Inclusive Tax</b>
		<select class="form-control" name="dtl_inclusive_tax[{$item_n}]" onchange="calc_matrix_gst({$item_n});" class="dtl_inclusive_tax">
			<option value="inherit" {if $items[$item_n].inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow SKU)</option>
			<option value="yes" {if $items[$item_n].inclusive_tax eq "yes"}selected {/if}>Yes</option>
			<option value="no" {if $items[$item_n].inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>

</table>
<br>

{include file="masterfile_sku_application.atom_photos.tpl"}

<h4>Product Matrix</h4>
<div class="alert alert-primary rounded">
	<ul style="list-style-type: none;">
		<li>Enter the Varieties into Row (1,2,3.. for Sizes) and Column (A,B,C.. for Colours) headers - green colour box</li>
		<li>Enter the Article No. or Manufacturer's Code into the content cells</li>
		</ul>
</div>

<div id="matrix[{$item_n|default:0}]">
{if $items[$item_n].tb}
<table>
{foreach name=r from=$items[$item_n].tb item=tb}
<tr>
{foreach name=c from=$tb item=tbc}
<td>
	<input class="nth form-control" name="tb[{$item_n}][{$smarty.foreach.r.index}][{$smarty.foreach.c.index}]" value="{$tbc}"><br>
	<input class="nth form-control" name="tbm[{$item_n}][{$smarty.foreach.r.index}][{$smarty.foreach.c.index}]" value="{$items[$item_n].tbm[$smarty.foreach.r.index][$smarty.foreach.c.index]}">
</td>
{/foreach}

<td><input class="nth form-control" name="tbprice[{$item_n}][{$smarty.foreach.r.index}]" value="{$items[$item_n].tbprice[$smarty.foreach.r.index]}"></td>
{if $config.do_enable_hq_selling && $BRANCH_CODE eq 'HQ'}
	<td><input class="nth form-control" name="tbhqprice[{$item_n}][{$smarty.foreach.r.index}]" value="{$items[$item_n].tbhqprice[$smarty.foreach.r.index]}"></td>
{/if}
<td><input class="nth" name="tbcost[{$item_n}][{$smarty.foreach.r.index}]" value="{$items[$item_n].tbcost[$smarty.foreach.r.index]}"></td>
{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ'}
	<td><input class="nth form-control" name="tbhqcost[{$item_n}][{$smarty.foreach.r.index}]" value="{$items[$item_n].tbhqcost[$smarty.foreach.r.index]}"></td>
{/if}
</tr>
{/foreach}
</table>
{/if}
</div>


<script>
{if $items[$item_n].tb}
{* call again to generate "nice" table *}
tb_expand({$item_n|default:0}, 0, 0);
{else}
tb_expand({$item_n|default:0}, 6, 6);
{/if}
//atom_update_gross({$item_n|default:0});
</script>

	</div>
</div>