{*
Revision History
================
19.03.07  yinsee
- add "rounding error adjustment" field
- fix the Balance comparison

8/28/2007 3:06:11 PM gary
- make uom can be changing, grn * grn acct correction "uom" allow change.

9/20/2007 3:32:13 PM gary
- reset all selling uom to EACH, remove the drop down.

12/20/2007 1:54:37 PM yinsee
- let acc_ctn and acc_pcs allow zero or blank

1/16/2008 5:49:18 PM yinsee
- add highlight row by $smarty.request.highlight_item_id

3/18/2008 2:37:55 PM gary
- fix the alter pcs qty to ctn qty (bug).

6/19/2008 3:37:30 PM yinsee
- view detail will show the adjusted value 

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST

9/23/2010 5:34:46 PM Justin
- Added the JS auto strike on row feature for "Reconcile".

11/29/2010 1:46:07 PM Justin
- Added the UOM id to fix the bugs where user had saved the new UOM fraction but no changes after check again. 

4/5/2011 11:56:32 AM Justin
- Added highlight in red when found different selling prices between GRN and PO.
- Only apply when user in Account Correction.  

4/13/2011 5:45:48 PM Justin
- Added current selling price from SKU item in small font while different with selling price from GRN.

4/15/2011 12:41:43 PM Justin
- Added round up for PO amt and GRN amt by 2 decimal points during sum up for total amt.

6/24/2011 12:30:31 PM Justin
- Modified all the ctn and pcs fields for Account Verification to accept decimal points calculation.
- Make the field larger when found the SKU item is allow decimal points for account ctn and pcs.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/15/2011 5:45:54 PM Justin
- Modified po cost amount round to 2.

11/10/2011 11:21:43 AM Justin
- Fixed the po amount should multiply with  po cost instead of cost from grn items.

3:36 PM 2/10/2012 Justin
- Fixed the sum up bugs for Adjusted Total.

2/24/2012 5:21:54 PM Justin
- Added to show output like PO table when received by IBT DO.

4/20/2012 5:40:56 PM Alex
- add packing uom code after description

8/3/2012 3:04:34 PM Justin
- Enhanced PO amount to use GRN cost instead PO cost.

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

4/20/2017 1:53 PM Khausalya
- Enhanced changes from RM to use config setting. 
*}
{literal}

<style>
.pv {
	color:#fff;
	background:#0c0;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

.nv {
	color:#fff;
	background:#e00;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

textarea {
	font:11px Arial;
}

.red_area {
	color:#f00;
	cursor:pointer;
}
</style>
{/literal}

{literal}
<!-- to make sure item_id[] become an array (>1 items), we create a dummy -->
<input type=hidden name=item_id[] value="0">
{/literal}
<script>
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
function calc_grn_total(id){
    var the_id = '['+id+']';
	document.f_a.elements['amt'+the_id].value = round2(document.f_a.elements['cost'+the_id].value * (document.f_a.elements['ctn'+the_id].value+document.f_a.elements['pcs'+the_id].value/document.f_a.elements['uomf'+the_id].value));

	diff = (document.f_a.elements['ctn'+the_id].value*document.f_a.elements['uomf'+the_id].value) + document.f_a.elements['pcs'+the_id].value - document.f_a.elements['qty2'+the_id].value;
	diff = float(round(diff, global_qty_decimal_points));

	document.f_a.elements['var'+the_id].value = diff;
	if (diff > 0)
		$('td_var'+the_id).innerHTML = '<span class=pv>+'+diff+'</span>';
    else if(diff < 0)
		$('td_var'+the_id).innerHTML = '<span class=nv>'+diff+'</span>';
	else
	    $('td_var'+the_id).innerHTML = '&nbsp;';

	var total = 0;
	var items = document.f_a.elements["item_id[]"];
	var i, tctn = 0, tpcs = 0;

	for (i=0;i<items.length;i++)
	{	
	    if (items[i].value==0) continue;

	    the_id = '['+items[i].value+']';
	    tctn += float(document.f_a.elements['ctn'+the_id].value);
	    tctn = float(round(tctn, global_qty_decimal_points));
	    tpcs += float(document.f_a.elements['pcs'+the_id].value);
	    tpcs = float(round(tpcs, global_qty_decimal_points));
		total += float(document.f_a.elements['amt'+the_id].value);
	}

	$('grn_qty').innerHTML = 'Ctn:'+float(round(tctn, global_qty_decimal_points))+' Pcs:'+float(round(tpcs, global_qty_decimal_points));
	$('grn_amt').innerHTML = round2(total);

	calc_acc_total();
}

function calc_acc_total()
{
	var total = 0;
	var items = document.f_a.elements["item_id[]"];
	var i;
	var qty;

	for (i=0;i<items.length;i++)
	{
	    if (items[i].value==0) continue;

	    var the_id = '['+items[i].value+']';
	    // if no account adjustment,
		if (document.f_a.elements["acc_ctn"+the_id].value=='' && document.f_a.elements["acc_pcs"+the_id].value=='' && document.f_a.elements["acc_cost"+the_id].value==''){
			qty = float(document.f_a.elements["ctn"+the_id].value)+float(document.f_a.elements["pcs"+the_id].value/document.f_a.elements["uomf"+the_id].value);
			var curr_amt = float(document.f_a.elements["cost"+the_id].value*qty);
			total += float(round2(curr_amt));
		}
		else{
		    var qty = 0;
		    var cost = 0;
		    		    
		    if (document.f_a.elements["acc_ctn"+the_id].value=='' && document.f_a.elements["acc_pcs"+the_id].value=='')
		        qty = float(document.f_a.elements["ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["pcs"+the_id].value);
			else
			    qty = float(document.f_a.elements["acc_ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["acc_pcs"+the_id].value);

            if (document.f_a.elements["acc_cost"+the_id].value=='')
		        cost = document.f_a.elements["cost"+the_id].value;
			else
				cost = document.f_a.elements["acc_cost"+the_id].value;
				
		    //alert(qty);
		    var curr_amt = float(cost*qty/document.f_a.elements["uomf"+the_id].value);
			total += float(round2(curr_amt));
		}
	}
    document.f_a.acc_adjustment.value = round2(total);
	calc_balance();
}

function update_var2(id)
{
	var sstr = '['+id+']';
	
	if (document.f_a.elements['acc_pcs'+sstr].value=='' && document.f_a.elements['acc_ctn'+sstr].value==''){
		$('var2'+sstr).innerHTML = '';
		return;
	}
	
	var var2 = (float(document.f_a.elements['acc_pcs'+sstr].value)+float(document.f_a.elements['acc_ctn'+sstr].value*document.f_a.elements['uomf'+sstr].value))-(float(document.f_a.elements['pcs'+sstr].value)+float(document.f_a.elements['ctn'+sstr].value*document.f_a.elements['org_uom'+sstr].value));
	var2 = float(round(var2, global_qty_decimal_points));
	
	if (var2>0)
		$('var2'+sstr).innerHTML = '<span class=pv>+'+var2+'</span>';
	else if (var2<0)
		$('var2'+sstr).innerHTML = '<span class=nv>'+var2+'</span>';
	else
		$('var2'+sstr).innerHTML = '';
}

function uom_change(value,id){

	var a = value.split(",");
	
    old_cost = float($('cost_'+id).value/$('uom_f_'+id).value);
    new_cost = float(old_cost*a[1]);
    
	if(a[1]>1){
		old_pcs=float($('acc_pcs_'+id).value);
		new_pcs=float(old_pcs%a[1]);
		remain=float(old_pcs)-new_pcs;
		ctn=(remain/a[1])+float($('acc_ctn_'+id).value);
		
		if ($('acc_pcs_'+id).value!=''){
			$('acc_pcs_'+id).value=float(round(new_pcs, global_qty_decimal_points));		
		}
		$('acc_ctn_'+id).value=float(round(ctn, global_qty_decimal_points));
		/*
		alert(ctn);	
		if ($('acc_ctn_'+id).value==''){
			$('acc_ctn_'+id).value=round(ctn);		
		}
		*/
	}
	
    $('cost_'+id).value=round(new_cost, global_cost_decimal_points);
	$('uom_f_'+id).value=a[1];
	$('uom_id_'+id).value=a[0];

	if(($('uom_f_'+id).value)=='1'){
		$('acc_ctn_'+id).value='';
		$('acc_ctn_'+id).disabled=true;
	}
	else{
    	$('acc_ctn_'+id).disabled=false;
    	if($('acc_ctn_'+id).value==''){		    		
    		$('acc_ctn_'+id).value='';	
		}
	}

	update_var2(id);

}
</script>
{/literal}

<br>
<table width=100% cellpadding=2 cellspacing=1 border=0 style="border:1px solid #000">
<tr class=small bgcolor=#ffee99>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>ARMS</th>
	<th rowspan=2>Artno</th>
	<th rowspan=2>Mcode</th>
	<th rowspan=2>Description</th>
	<th rowspan=2>Selling</th>
	<th rowspan=2>Selling UOM</th>
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<th rowspan=2 width=45>Order<br>Price</th>
	<th colspan=3 bgcolor=#C2DDFE>{if $grr.type eq 'PO'}Purchased{else}Delivered{/if}</th>
	<th colspan=2 bgcolor=#C2DDFE>FOC</th>
	<th rowspan=2 width=45 bgcolor=#C2DDFE>{$grr.type}<br>Amount</th>
{else}
	<th rowspan=2 width=45 {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Cost</th>
{/if}
	<th colspan=3 bgcolor=#FFCCFF>Received</th>
	<th rowspan=2 bgcolor=#FFCCFF width=45>Amount</th>
{if $grr.grn_get_weight}<th rowspan=2 bgcolor=#FFCCFF width=45>Weight<br>(kg)</th>{/if}
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<th rowspan=2 bgcolor=#FFCCFF width=45>GRN/{$grr.type}<br>Variance<br>(Pcs)</th>
{/if}
{if $acc_col || $is_correction || $manager_col || $confirm_col}
	<th bgcolor=#99cc66 colspan=2>Account Correction</th>
{/if}
{if $is_correction || $manager_col}
    <th bgcolor=#99cc66 rowspan=2>ACC/GRN<br>Variance<br>(Pcs)</th>
<!--    <th bgcolor=#6699cc rowspan=2>Adjustment<br>CN/DN</th>
	<th bgcolor=#6699cc rowspan=2>Action</th>-->
{/if}
</tr>
<tr class=small bgcolor=#ffee99>
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<th width=45 bgcolor=#C2DDFE>UOM</th>
	<th width=45 bgcolor=#C2DDFE>Ctn</th>
	<th width=45 bgcolor=#C2DDFE>Pcs</th>
	<th width=45 bgcolor=#C2DDFE>Ctn</th>
	<th width=45 bgcolor=#C2DDFE>Pcs</th>
{/if}
	<th width=45 bgcolor=#FFCCFF>UOM</th>
	<th width=45 bgcolor=#FFCCFF>Ctn</th>
	<th width=45 bgcolor=#FFCCFF>Pcs</th>
{if $acc_col || $is_correction || $manager_col || $confirm_col}
	<th bgcolor=#99cc66>Qty</th>
	<th bgcolor=#99cc66>Price</th>
{/if}
</tr>
<tbody id=tbditems>
{assign var=total value=0}
{assign var=tctn value=0}
{assign var=tpcs value=0}
{assign var=tpctn value=0}
{assign var=tppcs value=0}

{foreach name=i from=$form.items item=item key=iid}

{assign var=qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
{assign var=row_amt value=`$item.cost*$qty/$item.uom_fraction`}
{assign var=row_amt value=$row_amt|round2}
{assign var=total value=`$total+$row_amt`}

{if $grr.type eq 'PO' || $grr.is_ibt_do}
    {assign var=qty_foc value=`$item.po_foc_ctn*$item.po_uomf+$item.po_foc_pcs`}
	{assign var=qty_order value=`$item.po_order_ctn*$item.po_uomf+$item.po_order_pcs`}
	{assign var=qty2 value=`$qty_foc+$qty_order`}
	{if $qty2 ne '0'}
		{assign var=cost1 value=`$item.cost*$qty2/$item.po_uomf`}
		{assign var=cost1 value=$cost1|round:2}
	{else}
		{assign var=cost1 value=0}
	{/if}
	{assign var=total2 value=`$total2+$cost1`}
{/if}
{assign var=tctn value=`$tctn+$item.ctn`}
{assign var=tpcs value=`$tpcs+$item.pcs`}
{assign var=tpctn value=`$tpctn+$item.po_ctn`}
{assign var=tppcs value=`$tppcs+$item.po_pcs`}
{cycle name=c0 values="#EEF6FF,#DEE6FF" assign=rowcolor}
{cycle name=c1 values="#FFF0FF,#FFDDFF" assign=rowcolor2}
<tr height=24 {if $item.ctn+$item.pcs<=0}style="background:#fdd;color:#f00"{else}{cycle name=r1 values=",bgcolor=#eeeeee"}{/if} {if $smarty.request.highlight_item_id eq $item.sku_item_id}class=highlight_row{/if}>
	<td>{$smarty.foreach.i.iteration}. </td>
	<td nowrap>{$item.sku_item_code}</td>
	<td align=center nowrap>{$item.artno|default:"-"}</td>
	<td align=center nowrap>{$item.mcode|default:"-"}</td>
	<td>{$item.description} {include file=details.uom.tpl uom=$item.packing_uom_code}
	{if ($grr.type eq 'PO' || $grr.is_ibt_do) && ($item.po_disc_remark || $item.po_tax)}
		<div class=small>(
		{if $item.po_disc_remark}Discount: {$item.po_disc_remark}{/if}
		{if $item.po_tax}{if $item.po_disc_remark} / {/if}Sales Tax: {$item.po_tax}%{/if}
		)</div>
	{/if}
	</td>
	<td align=right {if $is_correction eq '1' && $item.po_item_id && $item.selling_price ne $item.sug_selling_price}class="red_area" title="Different Selling Price between GRN and PO"{/if}>
		{$item.selling_price|number_format:2}
		{if $is_correction eq '1' && $item.po_item_id && $item.selling_price ne $item.sug_selling_price}
			<br />
			<span style="color: blue;" class="small r">S.P: {$item.sug_selling_price|number_format:2}</span>
		{/if}
	</td>
	<!--td align=center>{$item.sell_uom|default:'EACH'}</td-->
	<td align=center>EACH</td>	
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<td align=right>
	    {if $item.po_cost eq 'FOC'}FOC
		{else}{$item.po_cost|number_format:$config.global_cost_decimal_points}
		{/if}
		<br>
  		{if $item.cost eq 'FOC'}
			(FOC)
		{else}
			({$item.cost|number_format:$config.global_cost_decimal_points})
		{/if}
	</td>
	<td bgcolor={$rowcolor}>{$item.po_uom}</td>
	<td bgcolor={$rowcolor} align=right>{$item.po_order_ctn|qty_nf}</td>
	<td bgcolor={$rowcolor} align=right>{$item.po_order_pcs|qty_nf}</td>
	<td bgcolor={$rowcolor} align=right>{$item.po_foc_ctn|qty_nf}</td>
	<td bgcolor={$rowcolor} align=right>{$item.po_foc_pcs|qty_nf}</td>
	<td bgcolor={$rowcolor} align=right>{$cost1|number_format:2}</td>
{else}
	<td align=right  {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
	    {if $item.cost eq 'FOC'}
   FOC
		{else}
			{$item.cost|number_format:$config.global_cost_decimal_points}
		{/if}
	</td>
{/if}
	<!--CHANGE HERE-->
	<td bgcolor={$rowcolor2} align=center>
		{if $is_correction}
			<select name=sel_uom[{$item.id}] id="sel_uom{$item.id}" onchange="uom_change(this.value,'{$item.id}');calc_acc_total();">
			{section name=i loop=$uom}
			<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.order_uom==$uom[i].code}selected{/if}>{$uom[i].code}
			</option>
			{/section}
			</select>
		{else}
			{$item.order_uom}
		{/if}
	</td>
{* show reconfirm column *}

{if $confirm_col}
	<input type=hidden name=item_id[] value="{$item.id}">
	<input type=hidden id=cost_{$item.id} name=cost[{$item.id}] value="{$item.cost}">
	<input type=hidden name=org_uom[{$item.id}] value="{$item.uom_fraction}">
	<input type=hidden id=uom_f_{$item.id} name=uomf[{$item.id}] value="{$item.uom_fraction}">
	<input type=hidden id=uom_id_{$item.id} name=uom_id[{$item.id}] value="{$item.uom_id}">
	<!--input type=hidden name=selling_uomf[{$item.id}] value="{$item.selling_uom_fraction}"-->
	<input type=hidden name=selling_uomf[{$item.id}] value="1">
	<input type=hidden name=qty2[{$item.id}] value="{$qty2}">
	
	<td bgcolor={$rowcolor2} align=right>
	<input class="small" name=ctn[{$item.id}] size=1 value="{$item.ctn}" onclick="clear0(this)" onchange="mi(this);calc_grn_total({$item.id});" {if $item.uom_fraction<=1}readonly{/if}></td>
	<td bgcolor={$rowcolor2} align=right><input class="small" name=pcs[{$item.id}] size=1 value="{$item.pcs}" onclick="clear0(this)" onchange="mi(this);calc_grn_total({$item.id});"></td>
	<td bgcolor={$rowcolor2} align=right><input class="small r" name=amt[{$item.id}] size=5 value="{$row_amt|number_format:2}" readonly></td>
{else}
	<td bgcolor={$rowcolor2} align=right>{$item.ctn|qty_nf}</td>
	<td bgcolor={$rowcolor2} align=right>{$item.pcs|qty_nf}</td>
	<td bgcolor={$rowcolor2} align=right>{$row_amt|number_format:2}</td>
{/if}
{if $grr.grn_get_weight}<td bgcolor={$rowcolor2} align=right>{$item.weight|number_format}</td>{/if}
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<td id="td_var[{$item.id}]" bgcolor={$rowcolor2} align=right align=center>
	<input type=hidden name=var[{$item.id}] value="{$qty-$item.po_qty}">
	<!--added gary 6/21/2007 3:19:02 PM -->
	<!--fix variant problem-->
	{assign var=po_qty value=`$item.po_ctn*$item.po_uomf+$item.po_pcs`}
	{assign var=qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
	
	{if $qty>$po_qty}
	{assign var=tvar value=`$tvar+$qty-$item.po_qty`}
	<span class="pv">+{$qty-$po_qty|qty_nf}</span>
	{elseif $qty<$item.po_qty}
	{assign var=tvar value=`$tvar+$item.po_qty-$qty`}
	<span class="nv">{$qty-$po_qty|qty_nf}</span>
	{else}
	&nbsp;
	{/if}
	<!--added gary 6/21/2007 3:19:02 PM -->
	<!--fix variant problem-->
	</td>
{/if}
{* show account correction *}
{if $acc_col || $is_correction || $confirm_col || $manager_col}
	<td bgcolor=#ccff99 nowrap>
	<input type=hidden name=po_item_id[{$item.id}] value="{$item.po_item_id}">
	{if !$confirm_col}
	<input type=hidden name=item_id[] value="{$item.id}">
	<input type=hidden id=cost_{$item.id} name=cost[{$item.id}] value="{$item.cost}">
	<input type=hidden name=org_uom[{$item.id}] value="{$item.uom_fraction}">
	<input type=hidden id=uom_f_{$item.id} name=uomf[{$item.id}] value="{$item.uom_fraction}">
	<input type=hidden id=uom_id_{$item.id} name=uom_id[{$item.id}] value="{$item.uom_id}">
	<input type=hidden name=ctn[{$item.id}] value="{$item.ctn}">
    <input type=hidden name=pcs[{$item.id}] value="{$item.pcs}">
    <input type=hidden name=rcc_status[{$item.id}] value="{$item.rcc_status}">
    <input type=hidden name=amt[{$item.id}] value="{$row_amt}" title="{$item.id},{$item.sku_item_code}">
    {/if}
	
	Ctn <input onclick="this.select()" class=small id=acc_ctn_{$item.id} name=acc_ctn[{$item.id}] {if $item.doc_allow_decimal}size="10"{else}size="1"{/if} onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); calc_acc_total(); update_var2({$item.id});" value="{$item.acc_ctn}" {if $item.uom_fraction<=1}disabled{/if}>

	Pcs <input onclick="this.select()" class=small id=acc_pcs_{$item.id} name=acc_pcs[{$item.id}] {if $item.doc_allow_decimal}size="10"{else}size="1"{/if} onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); calc_acc_total(); uom_change($('sel_uom{$item.id}').value,{$item.id});" value="{$item.acc_pcs}">
	
	</td>
	<td bgcolor=#ccff99 nowrap>
	<input class=small id=acc_cost_{$item.id} name=acc_cost[{$item.id}] size=5 onchange="mf(this);positive_check(this);calc_acc_total();" value="{$item.acc_cost}">
	</td>
{/if}

{if $is_correction || $manager_col}
<td bgcolor=#ccff99 align=center id="var2[{$item.id}]">
{if $item.acc_ctn ne '' || $item.acc_pcs ne ''}
	{assign var=var2 value=`$item.acc_ctn*$item.uom_fraction`}
	{assign var=var2 value=`$var2+$item.acc_pcs-$qty`}
	{if $var2 > 0}
	<span class="pv">+{$var2|qty_nf}</span>
	{elseif $var2 < 0}
	<span class="nv">{$var2|qty_nf}</span>
	{else}
	&nbsp;
	{/if}
{else}
&nbsp;
{/if}
</td>
{/if}
<!-- {* $is_correction}
	<td bgcolor=#99ccff nowrap>
	<input onclick="clear0(this)" class=small name=buyer_adjust[{$item.id}] size=5 onchange="mfz(this);recalc_total();" value="{$item.buyer_adjust}">
	{assign var=badjust_total value=`$badjust_total+$item.buyer_adjust`}
	</td>
	<td bgcolor=#99ccff nowrap>
	<input class=small name=buyer_action[{$item.id}] size=50 maxlength=80 value="{$item.buyer_action|escape}">
	</td>
{elseif $manager_col}
	<td bgcolor=#99ccff nowrap align=right>
	{if $item.buyer_adjust}{$item.buyer_adjust|number_format:2}{else}&nbsp;{/if}
	{assign var=badjust_total value=`$badjust_total+$item.buyer_adjust`}
	</td>
	<td bgcolor=#99ccff nowrap>
	{$item.buyer_action|default:"&nbsp;"}
	</td>

{/if*}-->
</tr>
{/foreach}

</tbody>
<tr height=24 bgcolor=#ffee99>
<td colspan="{if !$sessioninfo.privilege.SHOW_COST && $grr.type ne 'PO' && !$grr.is_ibt_do}7{else}8{/if}" align=right><b>Total</b></td>
{if $grr.type eq 'PO' || $grr.is_ibt_do}
<td colspan=5 bgcolor=#C2DDFE align=right>Ctn:{$tpctn|qty_nf} Pcs:{$tppcs|qty_nf}</td>
<td bgcolor=#C2DDFE align=right>{$total2|number_format:2}</td>
{/if}
<td bgcolor=#ffccff colspan=3 align=right><span id=grn_qty>Ctn:{$tctn} Pcs:{$tpcs}</span></td>
<td bgcolor=#ffccff align=right><span id=grn_amt>{$total|number_format:2}</span></td>
{if $grr.grn_get_weight}<td bgcolor=#ffccff align=right>&nbsp;</td>{/if}
{if $grr.type eq 'PO' || $grr.is_ibt_do}
<td bgcolor=#ffccff align=right><!--{$tvar|number_format}-->&nbsp;</td>
{/if}
{if $acc_col || $is_correction || $manager_col || $confirm_col}
<td nowrap bgcolor=#ccff99 colspan=2 align=right>Adjusted Total: <input size=8 name=acc_adjustment class=r value="{$form.acc_adjustment|default:$total|string_format:"%.2f"}" readonly></td>
{/if}
{if $is_correction || $manager_col}
<td bgcolor=#ccff99>&nbsp;</td>
<!--<td bgcolor=#99ccff><input size=8 class=r name=action_adjustment value="{$badjust_total|number_format:2}" readonly></td>
<td bgcolor=#99ccff>&nbsp;</td>-->
{/if}
</tr>

{if $acc_col || $confirm_col}

<tr class="xlarge">
<td colspan=14 align=right><b>GRN Amount</b>
<td align=right><span class=hilite>{$form.amount|number_format:2}</span></td>
</tr>

<tr class="xlarge">
<td colspan=14 align=right><b>Amount Difference</b>
<input id=amt_diff size=8 class="r hilite" readonly value="{$total-$form.amount|number_format:2}"></td>
</tr>

<tr class="xlarge">
<td colspan=14 align=right><b>Account Action</b></td>
</tr>
<tr>
<td colspan=10>&nbsp;</td>
<td colspan=8><textarea rows=5 cols=40 name=acc_action>{$form.acc_action|escape}</textarea></td>
</tr>

<script>
calc_acc_total();
</script>
{/if}


{if $is_correction || $manager_col}
{literal}
<script>
function calc_balance()
{
{/literal}
    document.f_a.grn_amount2.value=round2(float(document.f_a.rounding_amt.value)+float(document.f_a.acc_adjustment.value));
	document.f_a.ga.value=round2(float(document.f_a.account_amount.value)+float(document.f_a.buyer_adjustment.value)+float(document.f_a.action_adjustment.value));
    if(document.f_a.ga.value==document.f_a.grn_amount2.value)
		$('as').innerHTML = '<img src=/ui/approved.png align=absmiddle>';
	else
		$('as').innerHTML = '<img src=/ui/cancel.png align=absmiddle>';
{literal}
}
/*function recalc_total()
{
	var el = document.f_a.elements;
	var total_adjust=0;
	for (i=0;i<el.length;i++)
	{
		if (el[i].name.indexOf("buyer_adjust[")==0)
	    {
	        total_adjust += float(el[i].value);
		}
	}

	document.f_a.action_adjustment.value = round2(total_adjust);
	calc_balance();
}*/
</script>
{/literal}

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>Rounding Error Adjustment</b>
<input size=8 class="r" style="color:blue" onclick="clear0(this)" name=rounding_amt value="{$form.rounding_amt|number_format:2}" onchange="mf(this);calc_balance();"></td>
</tr>

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>GRN Amount (After Adjust)</b>
<input size=8 class="r" style="color:blue" name=grn_amount2 readonly value="{$form.acc_adjustment+$form.rounding_amt|number_format:2}"></td>
</tr>

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>Invoice/DO Amount</b>
<input size=8 class=r style="color:blue" name=account_amount value="{$form.account_amount|number_format:2}" readonly></td>
</tr>

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>Account Adjustment</b> (CN/DN)
<input size=8 class=r name=buyer_adjustment onclick="clear0(this)" value="{$form.buyer_adjustment|number_format:2}" {if $manager_col}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
</tr>

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>Other Adjustment</b>
<input size=8 class=r name=action_adjustment onclick="clear0(this)" value="{$form.action_adjustment|number_format:2}" {if $manager_col}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
</tr>

<tr>
<td nowrap colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}18{else}11{/if} align=right><b>Balance</b> (Invoice/DO Amount + Account Adjustment)
<span id="as"></span> <input class=r size=8 name=ga value="{$form.acc_adjustment+$form.buyer_adjustment+$form.action_adjustment|number_format:2}" readonly>
</td>
</tr>


<tr>
<td colspan={if $grr.type eq 'PO' || $grr.is_ibt_do}13{else}6{/if} align=right><b>Account Action</b></td>
<td colspan=6><textarea rows=5 cols=40 name=acc_action>{$form.acc_action|escape}</textarea></td>
</tr>

<script>
calc_acc_total();
calc_balance();
</script>
{/if}
</table>

{if $grr.sdiscount[0] || $grr.rdiscount[0] || $grr.po_remark1[0] || $grr.po_remark2[0]}
<!-- show D/N table if there is discount from remark in PO  -->
<br>
<h2>Discount / Debit Note Record</h2>
<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
<td valign=top style="border:1px solid #000">
<b>PO Remark #1 (Discount Amt: {$grr.sdiscount[0]|default:"-"})</b><br>
<span class=small>{$grr.po_remark1[0]|nl2br}</span>
</td>
<td>&nbsp;</td>
<td valign=top style="border:1px solid #000">
<b>PO Remark #2 (Discount Amt: {$grr.rdiscount[0]|default:"-"})</b><br>
<span class=small>{$grr.po_remark2[0]|nl2br}</span>
</td>
<td>&nbsp;</td>
<td valign=top style="border:1px solid #000">
{if $is_correction}
	<input type=checkbox name=dn_issued value=1 {if $form.dn_issued}checked{/if}> <b>D/N Issued (please tick)</b><br>
	<b>D/N Number:</b> <input name=dn_number size=5 value="{$form.dn_number}"> &nbsp;
	<b>D/N Amount:</b> <input name=dn_amount size=5 value="{$form.dn_amount}"><br>
	If not issued, enter reason:<br>
	<textarea name=dn_reason rows=5 cols=40>{$form.dn_reason|escape}</textarea>
{else}
	{if $form.dn_issued}
	<b>D/N Number:</b> {$form.dn_number}<br>
	<b>D/N Amount:</b> {$config.arms_currency.symbol}{$form.dn_amount|number_format:2}
	{else}
	{$form.dn_reason|nl2br}
	{/if}
{/if}
</td>
</tr>
</table>
{/if}

<script>
{if $is_correction && $smarty.request.a eq 'confirm_detail'}
	{literal}
	var set_fields = $('tblist').getElementsByTagName("INPUT");
	var parent_tr = '';

	$A(set_fields).each(
		function (ele,idx){
			if (ele.name.indexOf("rcc_status[")==0){
				if(ele.value == 1){
					parent_tr = $(ele).parentNode.parentNode;
					$(parent_tr).setStyle({'text-decoration': 'line-through'});
				}
			}
		}
	);
	{/literal}
{/if}
</script>
