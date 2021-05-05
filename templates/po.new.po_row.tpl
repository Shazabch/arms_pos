{* 
1/4/2008 5:50:57 PM - yinsee
- move icons into popup menu

5/14/2008 4:56:08 PM gary
-PO – (HQ /Branch ) – add extra gp % towards individual beside SP(for HQ PO).

6/9/2008 11:23:37 PM yinsee
- fix the division by zero bug (and no unit price during view mode)

8/8/2008 3:48:35 PM yinsee
- add GP(%) display for single branch PO

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST
- Hide GP if no $sessioninfo.privilege.SHOW_REPORT_GP

7/31/2009 4:19:27 PM Andy
- Edit colspan control

8/7/2009 2:47:56 PM Andy
- if $config.doc_uom_control true, if master uom id not equal 1, lock purchase uom

11/4/2010 5:58:26 PM Alex
- remove input tag on remark in view mode

2/7/2011 11:56:36 AM Justin
- Rename the S.P become S.S.P.

6/2/2011 5:08:59 PM Andy
- Add show photo at PO.

6/7/2011 2:05:11 PM Andy
- Add checking for config "po_use_simple_mode" and only show some simple column.

8/25/2011 4:31:38 PM Andy
- Change PO to ignore "SHOW COST" privilege and will show Cost & Cost indicator all the time.

9/8/2011 4:37:08 PM Alex
- add the missing T.selling/T.cost and total qty and foc when single branch

9/14/2011 12:42:14 PM Alex
- fix total selling and total cost calculation bugs

9/20/2011 11:39:54 AM Justin
- Modified all selling price to round to 2 instead of 3.
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/28/2012 5:40:30 PM Alex
- add checking $config.po_hide_parent_salestrend_stockbalance parent stock balance and parent sales trend

3/30/2012 11:10:43 AM Justin
- Added to set require to show confirmation when user about to leave the page.
- Added table's "tr".

4/3/2012 5:19:09 PM Andy
- Add can show highlight row color.
- Add show relationship between PO and SO.

4/20/2012 11:18:57 AM Alex
- add show packing uom code

4/30/2012 11:26:28 AM Andy
- Change sales trend qty not to put in ","

7/12/2012 9:50:23 AM Justin
- Enhanced to have UOM control by config and packing uom fraction.

7/19/2012 11:16:34 AM Justin
- Bug fixed for using uom fraction to swap from selling uom fraction into masterfile uom fraction.

8/10/2012 11:12 AM Andy
- Add purchase agreement control.

9/5/2012 11:20 AM Justin
- Enhanced to disable UOM selection while found config "doc_disable_edit_uom".
- Bug fixed on system will allow user to edit UOM after added new item.

10/17/2012 4:47 PM Andy
- Enhance PO to checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.
- Enhance when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Add a legend [BOM PACKAGE] after sku description.

1/28/2013 1:58 PM Justin
- Enhanced to always show "EACH" for selling UOM and default it as 1.

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

1/2/2014 4:37 PM Fithri
- increase the po discount input size to 10

4/30/2014 10:36 AM Justin
- Bug fixed on highlight row is no longer working.

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.

4/7/2015 4:11 PM Andy
- Fix PO Amount Include GST wrong profit calculation.

6/17/2015 11:16 AM Justin
- Bug fixed on GST selling price will show as zero when created from PO request.

01/27/2016 13:41 Edwin
- Bug fixed on uom and quantity assign wrongly when uom is 'Ctn24' in Purchase Order detail.

02/02/2016 11:17 Edwin
- Bug fixed on PO cost price error when refresh

3/14/2016 4:53 PM Andy
- Fix PO view screen showing wrong purchase uom in certain config.

06/22/2016 15:50 Edwin
- Show price before tax in S.S.P when vendor is not under GST

1/11/2017 11:33 AM Andy
- Enhanced to show Nett Selling Price all the time.
- Enhanced to check gst selling price when branch is under gst.

2/7/2017 5:08 PM Andy
- Fixed SSP input box size problem.

2/8/2017 4:10 PM Andy
- breakdown one line for SSP when sku inclusive tax is No.
- Highlight the font of SSP when sku inclusive tax is No.

4/18/2017 11:37 AM Andy
- Change to use data stored in database instead of recalculate everytime.

7/19/2017 11:53 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items

4/12/2018 5:29 PM Andy
- Added Foreign Currency feature.

3:06 PM 11/21/2018 Justin
- Enhanced to have new function to auto focus on ctn and pcs.

11/27/2018 4:38 PM Justin
- Enhanced to load Quotation cost when getting item detail.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

06/22/2020 11:50 AM Sheila
- Fixed table boxes alignment and width.

8/4/2020 6:08 PM Andy
- Added fixed header and column.

11/4/2020 5:22 PM Andy
- Fixed bug on press enter auto focus next qty field not working after gui changed.

3/23/2021 3:25 PM Ian
-Enhanced to check Block GRN, if got block GRN/PO, then not allow to add item & foc item/unable to enter qty.
*}

{*if BRANCH_CODE=='HQ' && $form.deliver_to*}
{if $form.branch_id==1 && !$form.po_branch_id && is_array($form.deliver_to)}
	{assign var=view_hq value=1}
{else}
	{assign var=view_hq value=0}
{/if}
{assign var=item_id value=$item.id}
<tr id="titem{$item.id}" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="{if ($smarty.request.highlight_item_id eq $item.sku_item_id) or (isset($smarty.request.highlight_sku_id) and $smarty.request.highlight_sku_id eq $item.sku_id) or (isset($smarty.request.highlight_po_item_id) and $smarty.request.highlight_po_item_id eq $item_id)}highlight_row{/if} tr_po_item_row {if $item.bom_ref_num}tr_bom_ref_num-{$item.bom_ref_num}{/if}" po_item_id="{$item_id}" >

<td nowrap>

	<input type="hidden" name="is_foc[{$item.id}]" id="is_foc{$item.id}" value="{$item.is_foc}" />
	<input type="hidden" name="master_uom_id[{$item.id}]" value="{$item.master_uom_id}" />
	<input type="hidden" name="doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}" />
	<input type="hidden" name="so_branch_id[{$item.id}]" value="{$item.so_branch_id}" />
	<input type="hidden" name="so_item_id[{$item.id}]" value="{$item.so_item_id}" />
	<input type="hidden" name="inclusive_tax[{$item.id}]" value="{$item.inclusive_tax}" />
	
	{if $config.enable_po_agreement}
		<input type="hidden" name="pa_branch_id[{$item.id}]" value="{$item.pa_branch_id}" class="pa_branch_id" item_id="{$item.id}" />
		<input type="hidden" name="pa_item_id[{$item.id}]" value="{$item.pa_item_id}" />
		<input type="hidden" name="pa_foc_item_id[{$item.id}]" value="{$item.pa_foc_item_id}" />
	{/if}
	
	{if $config.sku_bom_additional_type}
		<input type="hidden" name="bom_ref_num[{$item.id}]" value="{$item.bom_ref_num}" />
		<input type="hidden" name="bom_qty_ratio[{$item.id}]" value="{$item.bom_qty_ratio}" />
	{/if}
	
	<span id="count{$item.id}">
		{if $item_n ne ''}
			{$item_n}.
		{else}
			{$smarty.foreach.fitem.iteration}.
		{/if}
	</span>
</td>

<td nowrap>
	<a href="javascript:void(0)" onclick="show_context_menu(this, {$item.id}, {$item.sku_item_id}, {$item.is_foc})"><img src="/ui/icons/bullet_arrow_down.png" border=0></a>
	{if $config.po_show_photo}
		{if $item.photo}
			{assign var=p value=$item.photo}
			<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" src="/thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border="1" style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View" />
		{else}
			- No Photo -
		{/if}
	{/if}
</td>

<td {if $item.is_foc}style="color:#090"{/if} nowrap>
	<input type=hidden name="artno_mcode[{$item.id}]" id=artno_mcode{$item.id} value="{$item.artno_mcode}">
	{$item.artno}
</td>

<td {if $item.is_foc}style="color:#090"{/if} nowrap>
	{$item.mcode}
</td>

{if $config.link_code_name && $config.docs_show_link_code}
	<td nowrap>{$item.link_code}</th>
{/if}

<td {if $item.is_foc}style="color:#090"{/if} nowrap scope="row">

	{if $item.is_foc}
	<sup id="foc_id{$item.id}" style="color:#f00">{$item.foc_id}</sup>
	{/if}
	<span title="{$item.sku_item_code}">{$item.description}</span>
	
	{if $item.bom_ref_num}
		<span class="bom_legend">
			[BOM PACKAGE]
		</span>
	{/if}
	
	{include file=details.uom.tpl uom=$item.packing_uom}
		
	<sup style="color:#f00" id="foc_annotation{$item.id}">
	{$foc_annotations.$item_id}</sup>
	
	
	<!-- supplier remark -->
	<div id=note{$item.id} style="white-space:nowrap;{if !$item.remark}display:none{/if}">
		<img src=ui/note16.png align=absmiddle>&nbsp;
		{if $allow_edit}
			<input style="font:10px Arial;width:150px;" id=rem{$item.id} name="item_remark[{$item.id}]" value="{$item.remark|escape}" onblur="if(this.value==''){literal}{{/literal}hidediv('note{$item.id}');{literal}}{/literal}" onclick="this.select()" maxlength=100>
		{else}
		    {$item.remark|escape}
		{/if}

	</div>
	
	<!-- internal remark -->
	<div id=note2{$item.id} style="white-space:nowrap;{if !$item.remark2}display:none{/if}">
		<img src=ui/inote16.png align=absmiddle>&nbsp;
		{if $allow_edit}
			<input style="font:10px Arial;width:150px;" id=rem2{$item.id} name="item_remark2[{$item.id}]" value="{$item.remark2|escape}" onblur="if(this.value==''){literal}{{/literal}hidediv('note2{$item.id}');{literal}}{/literal}" onclick="this.select()" maxlength=100>
		{else}
		    {$item.remark2|escape}
		{/if}
	</div>
</td>

{if $item.is_foc}
	{assign var=total_profit value=$item.item_total_selling}
{else}
	{if $form.currency_code}
		{assign var=base_item_nett_amt value=$item.item_nett_amt*$form.currency_rate}
		{assign var=base_item_nett_amt value=$base_item_nett_amt|round2}
		
		{assign var=total_profit value=$item.item_total_selling-$base_item_nett_amt}
	{else}
		{assign var=total_profit value=$item.item_total_selling-$item.item_nett_amt}
	{/if}
{/if}

<!--============================================================================== -->
<!--============================================================================== -->

<!--START MULTIPLE DELIVER BRANCHES-->
{if $view_hq}
	<td>
		{*if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom*}
			{assign var=selling_uom_fraction value=1}
			{assign var=selling_uom_id value=1}
		{*else}
			{assign var=selling_uom_fraction value=$item.master_uom_fraction}
			{assign var=selling_uom_id value=$item.selling_uom_id}
		{/if*}
		<input type=hidden name=selling_uom_id[{$item.id}] value="{$selling_uom_id}">
		<input type=hidden id=suomf{$item.id} name=selling_uom_fraction[{$item.id}] value="{$selling_uom_fraction|default:1}">
		<!--select name=selling_uom[{$item.id}] id="suom{$item.id}" onchange="uom_change(this.value,'selling','{$item.id}');row_recalc({$item.id})" {if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled class="uom_disabled"{/if}>
		{section name=i loop=$uom}
		<option value="{$uom[i].id},{$uom[i].fraction}" {if $selling_uom_id == $uom[i].id or ($selling_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
		{/section}
		</select-->
		
		{*if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom*}
			<input type="hidden" name=selling_uom[{$item.id}] value="{$selling_uom_id|default:1}">
		{*/if*}
		&nbsp;EACH
	</td>
	
	<td align=center>
	{if $allow_edit and ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom)}
		{assign var=order_uom_fraction value=1}
		{assign var=order_uom_id value=1}
	{else}
		{assign var=order_uom_fraction value=$item.order_uom_fraction}
		{assign var=order_uom_id value=$item.order_uom_id}
	{/if}
	<input type=hidden name=order_uom_id[{$item.id}] value="{$order_uom_id}">
	<input type=hidden id=ouomf{$item.id} name=order_uom_fraction[{$item.id}] value="{$order_uom_fraction|default:1}">
	
	<select style="font-size: 10px !important;padding-left: 0px !important;font-family: Roboto, "Sans Serif" !important;" name=order_uom[{$item.id}] id="ouom{$item.id}" onchange="uom_change(this.value,'order','{$item.id}');positive_check(this);row_recalc({$item.id})" {if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled class="uom_disabled"{/if}>
	{section name=i loop=$uom}
	<option value="{$uom[i].id},{$uom[i].fraction}" {if $order_uom_id == $uom[i].id or ($order_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
	{/section}
	</select>

	{if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}
		<input type="hidden" name=order_uom[{$item.id}] value="{$item.order_uom_id|default:1}">
	{/if}
	</td>
	
	<td align=left nowrap>
		<input id="op{$item.id}" name="order_price[{$item.id}]" size=6 value="{$item.order_price|number_format:$config.global_cost_decimal_points:".":""}" onchange="item_cost_changed('{$item.id}');" style="{if $item.is_foc};background:#ff0;color:#f00;{/if}" onclick="clear0(this)">
		{if $item.use_qc && $item.qc_is_higher}
			<span><img src="ui/messages.gif" onclick="alert('Quotation Cost is higher than last GRN cost');" title="Quotation Cost is higher than last GRN cost" border="0" style="cursor:pointer;"></img></span>
		{/if}
	</td>
	
	<td align=center>
	{$item.cost_indicate|default:"-"}
	<input type=hidden name=cost_indicate[{$item.id}] value="{$item.cost_indicate}">
	</td>
{section name=i loop=$branch}
	{if in_array($branch[i].id,$form.deliver_to)}
		{assign var=bid value=`$branch[i].id`}
		
		<td align=right nowrap style="width:70px;">	
		<div align=center>
		{if !$item.is_foc}

			<table width="100%">
				<tr>
					<td align="center">
						<input style="width: 40px;" class="col_width-1 qty_fields" id="q{$item.id}[{$bid}]" name="qty_allocation[{$item.id}][{$bid}]" size=1 {if $order_uom_fraction == 1 || $item.blocked_branch[$bid]}disabled value="--"{else}value="{$item.qty_allocation[$bid]}"{/if} onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}qty_changed(this);positive_check(this);row_recalc({$item.id})" item_for_bid="{$bid}" onkeypress="return qty_keypressed(this, event);" />	
					</td>
					<td align="center">
						<input style="width: 40px;" class="col_width-1 qty_fields" id="ql{$item.id}[{$bid}]" name="qty_loose_allocation[{$item.id}][{$bid}]" {if $item.blocked_branch[$bid]}disabled value="--"{/if}  background:#fc9; size=1 onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}qty_changed(this);positive_check(this);row_recalc({$item.id})" value="{$item.qty_loose_allocation[$bid]}" item_for_bid="{$bid}" onkeypress="return qty_keypressed(this, event);" />
					</td>
				</tr>
			</table>	
		{else}	
			<table width="100%">
				<tr>
					<td align="center">
					<input id="q{$item.id}[{$bid}]"  name="qty_allocation[{$item.id}][{$bid}]" value=0 type=hidden>
					</td>
					<td align="center">
					<input id="ql{$item.id}[{$bid}]" name="qty_loose_allocation[{$item.id}][{$bid}]" value=0 type=hidden>
					</td>
				</tr>
			</table>
			<br>
		{/if}
		</div>	
		
		<div align=left>
			{*
			S.S.P
			{if $form.is_under_gst && $item.inclusive_tax eq "yes"}
				<input id="gst_sp{$item.id}[{$bid}]" name="gst_selling_price_allocation[{$item.id}][{$bid}]" value="{$item.gst_selling_price_allocation[$bid]|default:$item.gst_selling_price|number_format:2:'.':''}" size=3 onchange="this.value=round(this.value,2);positive_check(this);calculate_selling_gst({$item.id}, {$bid});" onclick="this.select();" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if} style="width:40px;">
			{else}
				<input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=3 onchange="this.value=round(this.value,2);positive_check(this);{if $form.is_under_gst}calculate_selling_gst({$item.id}, {$bid});{/if}row_recalc({$item.id});" onclick="this.select();" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if} style="width:40px;">
				{if $form.is_under_gst}
					<input type="hidden" id="gst_sp{$item.id}[{$bid}]" name="gst_selling_price_allocation[{$item.id}][{$bid}]" value="{$item.gst_selling_price_allocation[$bid]|round:2}" />
				{/if}
			{/if}
			{if $form.is_under_gst && $item.inclusive_tax eq "yes"}
				<br />
				<font color="blue">
					<span id="ori_sp{$item.id}">
						N.S.P
						<input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=3 readonly style="background-color:#ccc; width:40px;">
					</span>
				</font>
			{/if}
			*}
			{if $form.branch_is_under_gst}
				{if $item.inclusive_tax eq "yes"}
					S.S.P
					<input id="gst_sp{$item.id}[{$bid}]" name="gst_selling_price_allocation[{$item.id}][{$bid}]" value="{$item.gst_selling_price_allocation[$bid]|default:$item.gst_selling_price|number_format:2:'.':''}" size=3 onchange="this.value=round(this.value,2);positive_check(this);calculate_selling_gst({$item.id}, {$bid});" onclick="this.select();" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if} style="width:40px;">
					<br />
					<font color="blue">
						<span id="ori_sp{$item.id}">
							N.S.P
							<input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=3 readonly style="background-color:#ccc; width:40px;">
						</span>
					</font>
				{else}
					N.S.P
					<input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=3 onchange="this.value=round(this.value,2);positive_check(this);{if $form.is_under_gst}calculate_selling_gst({$item.id}, {$bid});{/if}row_recalc({$item.id});" onclick="this.select();" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if} style="width:40px;">
					<br />
					<font color="blue">S.S.P</font>
					<input type="text" id="gst_sp{$item.id}[{$bid}]" name="gst_selling_price_allocation[{$item.id}][{$bid}]" size="3" style="width:40px;" value="{$item.gst_selling_price_allocation[$bid]|round:2}" readonly />
				{/if}
			{else}
				<table width="100%">
					<tr>
						<td align="center">
						<span style="padding-left: 15px;text-align: right;" class="col-left-title">N.S.P</span>
						</td>
						<td align="center">
						<input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=3 onchange="this.value=round(this.value,2);positive_check(this);row_recalc({$item.id});" onclick="this.select();" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;width:40px;"{/if} class="col_width-1">
						</td>
					</tr>
				</table>
			{/if}
			<span {if !$sessioninfo.privilege.SHOW_REPORT_GP}style="display:none;"{/if}>
				<table width="100%">
					<tr>
						<td align="center">
						<span style="padding-left: 15px;text-align: right;" class="col-left-title">GP(%)</span>
						</td>
						<td align="center">
						<input id="branch_gp{$item.id}[{$bid}]" name="branch_gp{$item.id}[{$bid}]" class="col_width-1" style="background:#ccc;width:40px;" disabled value="{$item.branch_gp[$bid]}">
						</td>
					</tr>
				</table>
			</span>
		</div>
		<span id="br_sp[{$bid}][{$item.id}]" style="display:none">{$item.br_sp[$bid]}</span>
		<span id="br_cp[{$bid}][{$item.id}]" style="display:none">{$item.br_cp[$bid]}</span>
		</td>
		
		<!-- FOC -->
		<td align="right" nowrap style="width:60px;{if $config.po_use_simple_mode}display:none;{/if}">
			<div align=center>
				<table width="100%">
					<tr>
						<td align="center">
							<input class="col_width-1" id="f{$item.id}[{$bid}]" name="foc_allocation[{$item.id}][{$bid}]" style="background:#ff0; width:40px;" size=1 {if $order_uom_fraction == 1 || $item.blocked_branch[$bid]}disabled value="--"{else}value="{$item.foc_allocation[$bid]}"{/if} onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); row_recalc({$item.id})">
						</td>
						<td align="center">
							<input class="col_width-1" id="fl{$item.id}[{$bid}]" name="foc_loose_allocation[{$item.id}][{$bid}]" {if $item.blocked_branch[$bid]}disabled value="--" {else}value="{$item.foc_loose_allocation[$bid]}"{/if}  style="background:#f90;width:40px" size=1 onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); row_recalc({$item.id})" >		
						</td>
					</tr>
				</table>	
				{if $form.is_request or $form.is_vendor_po}
				<div style="padding-top:3px;" align=center>Bal: <font color=blue>{$item.balance[$bid]}</font></div>
				{/if}
			</div>
			<div align=center>
				<table width="100%">
					<tr>
						<td align="center">
							<span style="padding-left: 15px;text-align: right;" title="Stock Balance" class="col-left-title">A.Bal:</span>
						</td>
						<td align="center">
							<input name="stock_balance[{$item.id}][{$bid}]" style="background:#ddd;width:40px" readonly size=5 value="{$item.stock_balance[$bid]|ifzero:0|number_format:2:".":""|ifzero}" class="col_width-1">		
						</td>
					</tr>
				</table>	
				{if ($item.parent_stock_balance) and !$config.po_hide_parent_salestrend_stockbalance}
					<br /><span title="Parent Stock Balance">Parent:</span> <input name="parent_stock_balance[{$item.id}]" style="width:30px; background:#ddd;" size=5 readonly value="{$item.parent_stock_balance.qty.$bid|ifzero:0|number_format:2:".":""|ifzero}">
				{/if}	
			</div>
		</td>
	{/if}
{/section}

<!--=================================================================================-->

<!--START SINGLE DELIVER BRANCHES-->
{else}
	{* if $form.is_under_gst && $item.inclusive_tax eq "yes"}
		<td align=center>
			<input id="gst_sp{$item.id}" name="gst_selling_price[{$item.id}]" size=6 value="{$item.gst_selling_price|number_format:2:'.':''}" onchange="this.value=round2(this.value);positive_check(this);calculate_selling_gst({$item.id});" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
		</td>
		<td align=center>
			<input id="sp{$item.id}" name="selling_price[{$item.id}]" size=6 value="{$item.selling_price|number_format:2:'.':''}" readonly style="background-color:#ccc;">
		</td>
	{else}
		<td align=center>
			<input id="sp{$item.id}" name="selling_price[{$item.id}]" size=6 value="{$item.selling_price|number_format:2:'.':''}" onchange="this.value=round2(this.value);positive_check(this);{if $form.is_under_gst}calculate_selling_gst({$item.id});{/if}row_recalc({$item.id});" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
		</td>
		
		<td align=center>
			{if $form.is_under_gst}
				<input type="text" id="tmp_gst_sp{$item.id}" name="tmp_gst_selling_price[{$item.id}]" size="6" value="{$item.selling_price|number_format:2:'.':''}" readonly style="background-color:#ccc;" />
				<input type="hidden" id="gst_sp{$item.id}" name="gst_selling_price[{$item.id}]" value="{$item.gst_selling_price}" />
			{/if}			
		</td>
	{/if *}
	{if $form.branch_is_under_gst}
		{if $item.inclusive_tax eq "yes"}
			<td align=center>
				<input id="gst_sp{$item.id}" name="gst_selling_price[{$item.id}]" size=6 value="{$item.gst_selling_price|number_format:2:'.':''}" onchange="this.value=round2(this.value);positive_check(this);calculate_selling_gst({$item.id});" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
			</td>
			<td align=center>
				<input id="sp{$item.id}" name="selling_price[{$item.id}]" size=6 value="{$item.selling_price|number_format:2:'.':''}" readonly style="background-color:#ccc;">
			</td>
		{else}
			<td align=center>
				<input id="sp{$item.id}" name="selling_price[{$item.id}]" size=6 value="{$item.selling_price|number_format:2:'.':''}" onchange="this.value=round2(this.value);positive_check(this);{if $form.is_under_gst}calculate_selling_gst({$item.id});{/if}row_recalc({$item.id});" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
			</td>
			
			<td align=center>
				<input type="text" id="tmp_gst_sp{$item.id}" name="tmp_gst_selling_price[{$item.id}]" size="6" value="{$item.selling_price|number_format:2:'.':''}" readonly style="background-color:#ccc;" />
				<input type="text" id="gst_sp{$item.id}" name="gst_selling_price[{$item.id}]" size="6" value="{$item.gst_selling_price}" readonly />
			</td>
		{/if}
	{else}
		<td align=center>
			<input id="sp{$item.id}" name="selling_price[{$item.id}]" size=6 value="{$item.selling_price|number_format:2:'.':''}" onchange="this.value=round2(this.value);positive_check(this);row_recalc({$item.id});" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
		</td>
	{/if}
	
	<td align=center>
		{*if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom*}
			{assign var=selling_uom_fraction value=1}
			{assign var=selling_uom_id value=1}
		{*else}
			{assign var=selling_uom_fraction value=$item.master_uom_fraction}
			{assign var=selling_uom_id value=$item.selling_uom_id}
		{/if*}
		<input type=hidden name=selling_uom_id[{$item.id}] value="{$selling_uom_id}">
		<input type=hidden id=suomf{$item.id} name=selling_uom_fraction[{$item.id}] value="{$selling_uom_fraction|default:1}">
		<!--select name=selling_uom[{$item.id}] id="suom{$item.id}" onchange="uom_change(this.value,'selling','{$item.id}');row_recalc({$item.id})" {if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled class="uom_disabled"{/if}>
		{section name=i loop=$uom}
		<option value="{$uom[i].id},{$uom[i].fraction}" {if $selling_uom_id == $uom[i].id or ($selling_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
		{/section}
		</select-->
		
		{*if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom*}
			<input type="hidden" name=selling_uom[{$item.id}] value="{$selling_uom_id|default:1}">
		{*/if*}
		&nbsp;EACH
	</td>
	
	<td align=center>
	{if $allow_edit and ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom)}
		{assign var=order_uom_fraction value=1}
		{assign var=order_uom_id value=1}
	{else}
		{assign var=order_uom_fraction value=$item.order_uom_fraction}
		{assign var=order_uom_id value=$item.order_uom_id}
	{/if}
	<input type=hidden name=order_uom_id[{$item.id}] value="{$order_uom_id}">
	<input type=hidden id=ouomf{$item.id} name=order_uom_fraction[{$item.id}] value="{$order_uom_fraction|default:1}">
	
	<select style="font-size: 10px !important;padding-left: 0px !important;font-family: Roboto, "Sans Serif" !important;" name="order_uom[{$item.id}]" id="ouom{$item.id}" onchange="uom_change(this.value,'order','{$item.id}');row_recalc({$item.id});" {if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled class="uom_disabled"{/if}>
	{section name=i loop=$uom}
	<option value="{$uom[i].id},{$uom[i].fraction}" {if $order_uom_id == $uom[i].id or ($order_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
	{/section}
	</select>
	{if (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}
		<input type="hidden" name=order_uom[{$item.id}] value="{$order_uom_id|default:1}">
	{/if}
	</td>
	
	<td align=left nowrap>
		<input id="op{$item.id}" name="order_price[{$item.id}]" size=7 value="{$item.order_price|number_format:$config.global_cost_decimal_points:".":""}" onchange="item_cost_changed('{$item.id}');" style="{if $item.is_foc};background:#ff0;color:#f00;{/if}" class="r" onclick="clear0(this)">
		{if $item.use_qc && $item.qc_is_higher}
			<span><img src="ui/messages.gif" onclick="alert('Quotation Cost is higher than last GRN cost');" title="Quotation Cost is higher than last GRN cost" border="0" style="cursor:pointer;"></span>
		{/if}
	</td>
	
	<td align=center>
	{$item.cost_indicate|default:"-"}
	<input type=hidden name=cost_indicate[{$item.id}] value="{$item.cost_indicate}">
	</td>
	
	<td align=center nowrap>
		{if !$item.is_foc}
			<div align=center>
			<table width="100%">
				<tr>
					<td align="center">
					<input id="q{$item.id}" name="qty[{$item.id}]" {if $order_uom_fraction == 1}disabled value="--"{else}value="{$item.qty}"{/if} size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);qty_changed(this);row_recalc({$item.id})" style="width:40px;" onclick="clear0(this)" class="qty_fields" onkeypress="return qty_keypressed(this, event);">
					</td>
					<td align="center">
					<input id="ql{$item.id}" name="qty_loose[{$item.id}]" style="width:40px; background:#fc9;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);qty_changed(this);row_recalc({$item.id});" onclick="clear0(this)" value="{$item.qty_loose}" class="qty_fields" onkeypress="return qty_keypressed(this, event);">
					</td>
				</tr>
			</table>
			</div>
		{else}
			<div>
		    <input id="q{$item.id}" name=qty[{$item.id}] value="0" type=hidden>
		    <input id="ql{$item.id}" name=qty_loose[{$item.id}] value="0" type=hidden>
		    </div>
		{/if}
		<span {if !$sessioninfo.privilege.SHOW_REPORT_GP}style="display:none;"{/if}>
		<table width="100%">
			<tr>
				<td align="center">
				<span style="padding-left: 15px;text-align: right;" class="col-left-title">GP(%)</span>
				</td>
				<td align="center">
				<input id="total_marginc{$item.id}" value="{if $item.item_total_selling<=0}-{else}{$total_profit/$item.item_total_selling*100|number_format:2}{/if}" style="width:40px;background:#ccc;" disabled value="{$item.branch_gp[$bid]}">
				</td>
			</tr>
		</table>
		</span>
		<span id="br_sp[{$item.id}]" style="display:none">{$item.br_sp}</span>
		<span id="br_cp[{$item.id}]" style="display:none">{$item.br_cp}</span>
	</td>

	<!-- FOC -->
	<td align="center" nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">
		<table width="100%">
			<tr>
				<td align="center">
				<input id="f{$item.id}" name=foc[{$item.id}] {if $order_uom_fraction == 1}disabled value="--"{else}value="{$item.foc}"{/if} size=3 onchange="positive_check(this); positive_check(this); row_recalc({$item.id})" style="width:40px;background:#ff0;" onclick="clear0(this)">
				</td>
				<td align="center">
				<input id="fl{$item.id}" name="foc_loose[{$item.id}]" style="width:40px; background:#f90;" size=5 onchange="positive_check(this); positive_check(this); row_recalc({$item.id})" onclick="clear0(this)" value="{$item.foc_loose}">
				</td>
			</tr>
		</table>
		<span id=ctn{$item.id} style="display:none">{$item.qty+$item.foc}</span>
		{if $form.is_request or $form.is_vendor_po}
		<div style="padding-top:3px;" align=center>Bal: <font color=blue>{$item.balance}</font></div>
		{/if}
		<div align=center>
			<span title="Stock Balance">
			<table width="100%">
				<tr>
					<td align="center">
						<span style="padding-left: 15px;text-align: right;" title="Stock Balance" class="col-left-title">A.Bal:</span>
					</td>
					<td align="center">
						<input name="stock_balance[{$item.id}]" style="width:40px; background:#ddd;" size=5 readonly value="{$item.stock_balance|ifzero:0|number_format:2:".":""|ifzero}">
					</td>
				</tr>
			</table>
			</span> 	

			{if ($item.parent_stock_balance) and !$config.po_hide_parent_salestrend_stockbalance}
				<span title="Parent Stock Balance">Parent:</span> <input name="parent_stock_balance[{$item.id}]" style="width:30px; background:#ddd;" size=5 readonly value="{$item.parent_stock_balance.qty|ifzero:0|number_format:2:".":""|ifzero}">
			{/if}
		</div>
	</td>
{/if}
<!--END SINGLE DELIVER BRANCHES-->

<!--============================================================================== -->

	<!-- Sales Trend -->
	<td align="center" nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">
		<div align=center style="margin-bottom: 5px">
			<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][qty][1]" size=3 style=background:#ccc; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.qty.1|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][qty][3]" style=background:#ddd; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=3 value="{$item.sales_trend.qty.3|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][qty][6]" size=3 style=background:#ccc; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.qty.6|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][qty][12]" style=background:#ddd; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=3 value="{$item.sales_trend.qty.12|ifzero:0|ifzero}" readonly>
		</div>
		<div align=center>
			<input class="tbl_col_salestrend" size=3 style="background:#ccc; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.qty.1|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" style="background:#ddd; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=3 value="{$item.sales_trend.qty.3/3|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" size=3 style="background:#ccc; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.qty.6/6|ifzero:0|ifzero}" readonly>
			<input class="tbl_col_salestrend" style="background:#ddd; font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=3 value="{$item.sales_trend.qty.12/12|ifzero:0|ifzero}" readonly>
		</div>
		
		{if $item.sales_trend.parent and !$config.po_hide_parent_salestrend_stockbalance}
		<fieldset>
			<legend>Parent Sales Trend</legend>
			<div align=center style="margin-bottom: 5px">
				<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][parent][qty][1]" size=4 style="background:#ccc;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.parent.qty.1|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][parent][qty][3]" style=" background:#ddd;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=4 value="{$item.sales_trend.parent.qty.3|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][parent][qty][6]" size=4 style="background:#ccc;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.parent.qty.6|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" name="sales_trend[{$item.id}][parent][qty][12]" style=" background:#ddd;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=4 value="{$item.sales_trend.parent.qty.12|ifzero:0|qty_nf:".":""|ifzero}" readonly>
			</div>
			<div align=center>
				<input class="tbl_col_salestrend" size=4 style="background:#ccc;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.parent.qty.1|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" style="background:#ddd;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=4 value="{$item.sales_trend.parent.qty.3/3|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" size=4 style="background:#ccc;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="{$item.sales_trend.parent.qty.6/6|ifzero:0|qty_nf:".":""|ifzero}" readonly>
				<input class="tbl_col_salestrend" style="background:#ddd;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" size=4 value="{$item.sales_trend.parent.qty.12/12|ifzero:0|qty_nf:".":""|ifzero}" readonly>
			</div>
		</fieldset>
		{/if}
	</td>

	<!-- Total Pcs -->
	<td align=right>
		<span id=qty{$item.id}>{$item.row_qty|qty_nf|default:0}</span>
		<span style="display:none" id=ctn{$item.id}>{$item.ctn|qty_nf|default:0}</span>
	</td>
	
	<!-- Total FOC -->
	<td align="right" style="{if $config.po_use_simple_mode}display:none;{/if}">
		<span id=foc{$item.id}>{$item.row_foc|qty_nf|default:0}</span>
	</td>


<!-- Gross Amount -->
<td align="right">
	<span id="gamount{$item.id}">
		{if $item.is_foc}FOC{else}{$item.item_gross_amt|number_format:2}{/if}
	</span>
</td>

<!-- Tax -->
<td style="{if $config.po_use_simple_mode}display:none;{/if}">
	{if !$item.is_foc}
		<input id="tax{$item.id}" name="tax[{$item.id}]" value="{$item.tax}" size="3" onchange="row_recalc({$item.id})" onclick="clear0(this)" />
	{else}
		<input type="hidden" id="tax{$item.id}" name="tax[{$item.id}]" value="0" />
	{/if}
</td>

<!-- Discount -->
<td style="{if $config.po_use_simple_mode}display:none;{/if}">
	{if !$item.is_foc}
		<input id="disc{$item.id}" name="discount[{$item.id}]" value="{$item.discount}" size="10" onchange="row_recalc({$item.id})" onclick="clear0(this)">
		<div class="small" style="color:#00f" id="disc_amount{$item.id}">
		{if $item.discount_amt>0 && strstr($item.discount,'%')}
		{$item.discount_amt|number_format:2}
		{/if}
		</div>
	{else}
		<input type="hidden" id="disc{$item.id}" name="discount[{$item.id}]" value="">
		<!-- Added by gary to calculate the FOC and get correct total selling-->
		<div class="small" style="color:#00f" id="disc_amount{$item.id}">
		{if $item.discount_amt>0 && strstr($item.discount,'%')}
			{$item.discount_amt|number_format:2}
		{/if}
		</div>
	{/if}
</td>

<!-- Nett Amount -->
{if $view_hq}
	{assign var=c2 value=$item.foc+$item.qty}
	{else}
	{assign var=c2 value=$item.row_foc+$item.row_qty}
{/if}
<td align="right" style="{if $config.po_use_simple_mode}display:none;{/if}">
	<span id="amount{$item.id}">
		{if $item.is_foc}
		  FOC
		{else}
		  {$item.item_nett_amt|number_format:2}<br>
		  {if $c2>0}<font color=blue>{$item.item_nett_amt/$c2|number_format:$config.global_cost_decimal_points}</font>{else}-{/if}
		{/if}
	</span>
	
	{* Base Nett Amount *}
	{if $form.currency_code}
		<br />
		<span class="converted_base_amt">
			<span id="span_base_item_nett_amt-{$item.id}" >
				{if !$item.is_foc}
					{$base_item_nett_amt|number_format:2}
				{/if}
			</span>{if !$item.is_foc}*{/if}
		</span>
	{/if}
</td>

<!-- GST info -->
{if $form.is_under_gst}
	<td align="">
		{if $item.is_foc}
			&nbsp;
		{else}
			<select name="item_gst[{$item.id}]" id="item_gst{$item.id}" item_id="{$item.id}" onchange="on_item_gst_changed(this, {$item.id}, 'cost');">
				{foreach from=$input_gst_list key=rid item=gst}
					<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $item.cost_gst_id eq $gst.id and $item.cost_gst_code eq $gst.code and $item.cost_gst_rate eq $gst.rate}selected {/if}>{$gst.code} ({$gst.rate}%)</option>
				{/foreach}
			</select>
			<br />
			<font color="blue"><span id="cost_gst_rate_amt{$item.id}" class="small">{$item.unit_gst_incl_foc|number_format:$config.global_cost_decimal_points}</span></font>
			<input type="hidden" name="cost_gst_id[{$item.id}]" value="{$item.cost_gst_id}" />
			<input type="hidden" name="cost_gst_code[{$item.id}]" value="{$item.cost_gst_code}" />
			<input type="hidden" name="cost_gst_rate[{$item.id}]" value="{$item.cost_gst_rate}" />
		{/if}
	</td>
	
	<!-- GST ttl amount / unit gst order price -->
	<td align="right">
		<span id="ttl_cost_gst_amt{$item.id}">
		{if $item.is_foc}
			FOC
		{else}
			{$item.item_amt_incl_gst|number_format:2}
		{/if}
		</span>
		<input type="hidden" name="item_amt_incl_gst[{$item.id}]" class="item_amt_incl_gst" value="{$item.item_amt_incl_gst}" />
	</td>
{/if}

<!-- Total Selling -->
<td align=right>
<span id="total_sell{$item.id}">{$item.item_total_selling|number_format:2}</span>
{if $form.branch_is_under_gst}
	<input type="hidden" name="selling_gst_id[{$item.id}]" value="{$item.selling_gst_id}" />
	<input type="hidden" name="selling_gst_code[{$item.id}]" value="{$item.selling_gst_code}" />
	<input type="hidden" name="selling_gst_rate[{$item.id}]" value="{$item.selling_gst_rate}" />
	<input type="hidden" id="total_gst_sell-{$item.id}" class="inp_total_gst_sell" value="{$item.item_total_gst_selling|number_format:2}" />
{/if}
</td>

<!-- Gross Profit -->
<td align="right" {if !$sessioninfo.privilege.SHOW_REPORT_GP}style="display:none;"{/if} class="{if $form.currency_code}converted_base_amt{/if}">
	<span id="total_profit{$item.id}" style=";{if $item.is_foc}color:#090;{/if}" class="{if $total_profit<=0}negative_value{/if}">{$total_profit|number_format:2}</span>
	{if $form.currency_code}*{/if}
</td>

<!-- Profit % -->
<td align="right">
	<span id="total_margin{$item.id}" style=";{if $item.is_foc}color:#090;{/if}" class="{if $total_profit<=0}negative_value{/if}">
		{if $item.item_total_selling<=0}-{else}{$total_profit/$item.item_total_selling*100|number_format:2}%{/if}
	</span>
</td>

<!-- create by sales order -->
{if $form.po_create_type eq 1}
	<td align="right">
		{if $item.sales_order_items}
			<a href="sales_order.php?a=view&id={$item.sales_order_items.sales_order_id}&branch_id={$item.sales_order_items.branch_id}&highlight_soi_item_id={$item.sales_order_items.id}" target="_blank">
			{$item.sales_order_items.order_no}
			</a>
		{else}
			-
		{/if}
	</td>
{/if}
</tr>

<script type="text/javascript">
{if $allow_edit}
	needCheckExit=true;
	
	{if $config.enable_po_agreement}
		{if $item.pa_branch_id and $item.pa_item_id}
			disable_sub_ele($('titem{$item.id}'));	// disable row if this item is create from purchase agreement
		{/if}
	{/if}
{/if}
</script>