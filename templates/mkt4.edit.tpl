<script>
{if $form.normal_target}
var real_normal_target={$form.normal_target};
{/if}
{if $form.sales_target}
var real_promo_target={$form.sales_target};
{/if}
</script>
{include file=header.tpl}
{literal}
<style>
.negative{
	color:red;
}
.zero{
	color:green;
	text-align:right;
	font: 12px
}
.keyin{
	background-color:yellow;
	text-align:right;
}
.keyin_w{
	background-color:yellow;
}
.st_block {
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
}
.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.st_block th { background:#efffff; padding:4px; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
.st_block input[disabled] { color:#000;border:none;background:transparent; }
.st_block input[readonly] { color:#00f;}
</style>
<script>

function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}
function do_confirm()
{
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
	//document.f_a.submit();
}

function recalc_row(id)
{
	// if id passed as non numeric, extract the index
	// from the <input>.name passed
	if (isNaN(id)) id = int(id.substring(id.lastIndexOf('[')+1));
	var temp_sales,temp_normal;
	var sstr = '['+id+']';
	var el = document.f_a.elements;
	el['offers[tcost]'+sstr].value = round2(el['offers[qty]'+sstr].value*el['offers[cost]'+sstr].value);
	el['offers[tsell]'+sstr].value = round2(el['offers[qty]'+sstr].value*el['offers[propose_selling]'+sstr].value);
	el['offers[mkup]'+sstr].value = round2(el['offers[tsell]'+sstr].value - el['offers[tcost]'+sstr].value);
	el['offers[mkup_pct]'+sstr].value = round2(el['offers[mkup]'+sstr].value/el['offers[tcost]'+sstr].value*100);
	
	//el['offers_promo_target_val'+sstr].value = el['offers[tsell]'+sstr].value;

	//el['offers_normal_target_val'+sstr].value = round2(el['offers[qty]'+sstr].value*el['offers[selling]'+sstr].value);
	calculate_total();

	//alert(total_cost);alert(total_sell);alert(total_mkup);
}
var total_target=0;
var total_normal_target=0;
var total_sales_target=0;
function calculate_total(){
	var e = $('offer_items_table').getElementsByTagName('input');
	var total_cost=0;
	var total_sell=0;	
	var total_mkup=0;
	var total_pct=0;
	for(var i=0;i<e.length;i++)	{
		if (/offers\[tcost\]/.test(e[i].name)){
			total_cost+=float(e[i].value);
		}
		if (/offers\[tsell\]/.test(e[i].name)){
			total_sell+=float(e[i].value);		
		}
		if (/offers\[mkup\]/.test(e[i].name)){
			total_mkup+=float(e[i].value);		
		}
	}
	$('t_cost').innerHTML=round(total_cost,2);
	$('t_sell').innerHTML=round(total_sell,2);
	$('t_mkup').innerHTML=round(total_mkup,2);
	total_pct=((total_sell-total_cost)/total_cost*100);
	$('t_pct').innerHTML=round(total_pct,2)+'%';
	{/literal}
	total_normal_target="{$form.normal_target}";
	total_sell_target="{$form.sales_target}";
	total_target="{$form.normal_target+$form.sales_target}";
	{literal}
	if(float($('t_sell').innerHTML)>total_target){
		$('Msg').innerHTML =  'Total selling is more than Total Target';
		alert('Total selling is more than Total Target');
	}
	else if (float($('t_sell').innerHTML)<total_target){
		$('Msg').innerHTML =  'Total selling is below Total Target';		
	}

}


function check_a()
{
	var e = $('offer_items_table').getElementsByTagName('input');
	var total_promo = 0;
	var total_normal = 0;

	for(var i=0;i<e.length;i++)	{
	    if (/offers_promo_target_val/.test(e[i].name)){
			total_promo += float(e[i].value);

		}
	    if (/offers_normal_target_val/.test(e[i].name)){
			total_normal += float(e[i].value);

		}
	}
	var f = $('brand_discount_table').getElementsByTagName('input');
	for(var i=0;i<f.length;i++)	{
	    if (/brands\[target_promo\]/.test(f[i].name)){
			total_promo += float(f[i].value);

		}
	    if (/brands\[target_normal\]/.test(f[i].name)){
			total_normal += float(f[i].value);

		}
	}
	total_normal=round(total_normal);
	total_promo=round(total_promo);
	
	/*if(real_normal_target>total_normal){
		if(!confirm('Your Total Normal Selling less than Normal Target\n Are you sure to continue?')){
        	return false;
		}
	}
 	if(real_promo_target>total_promo){
		if(!confirm('Your Total Sales Selling less than Sales Target\n Are you sure to continue?')){
        	return false;
        }
	}*/
	return true;
}

// insert new empty row and return the new row handler
function add_row(parent,template,unset_readonly)
{
	if  (parent == 'brand_rows')
		var sid = ++last_brand_row;
	else
	    var sid = ++last_offer_row;
	sid = '['+sid+']';
	
	var new_row = $(template).cloneNode(true);
	new_row.style.display='';
	new_row.id='';
	
	var el = new_row.getElementsByTagName('input');
	
	// make input fields's index 
	for(var i=0;i<el.length;i++)
	{
	    if (unset_readonly==true) el[i].readOnly = false;
		el[i].name = el[i].name.replace('[]',sid);
	}
	$(parent).appendChild(new_row);
	
	// replace SKU Item code with ID column for user input
	if (unset_readonly)
	{
		if  (parent == 'brand_rows'){
 			document.f_a.elements['brands[brand_id]'+sid].type = 'text';
			document.f_a.elements['brands[brand]'+sid].type = 'hidden';
		}
		else{
			document.f_a.elements['offers[sku_item_id]'+sid].type = 'text';
			document.f_a.elements['offers[sku_item_code]'+sid].type = 'hidden';
		}
	}
	return new_row;
}

function insert_sku()
{
	if (int(document.f_a.sku_item_id.value)==0) return;

	var newrow = add_row('offer_rows','offer_template');
	var el = newrow.getElementsByTagName('input');
	for (var i=0;i<el.length; i++)
	{
		if (/offers\[sku_item_code\]/.test(el[i].name))
		{
		    el[i].value = document.f_a.sku_item_code.value;
		}
		else if (/offers\[sku_item_id\]/.test(el[i].name))
		{
		    el[i].value = document.f_a.sku_item_id.value;
		}
		else if (/offers\[description\]/.test(el[i].name))
		{
		    el[i].value = $('autocomplete_sku').value;
		}
		else if (/offers\[cost\]/.test(el[i].name))
		{
		    el[i].value = document.f_a.sku_item_cost.value;
		}
		else if (/offers\[selling\]/.test(el[i].name))
		{
		    el[i].value = document.f_a.sku_item_selling.value;
		}
	}
}


function insert_brand()
{
	if (int(document.f_a.brand_id.value)==0) return;

	var newrow = add_row('brand_rows','brand_template');
	var el = newrow.getElementsByTagName('input');
	for (var i=0;i<el.length; i++)
	{
		
		if (/brands\[brand_id\]/.test(el[i].name))
		{
		    el[i].value = document.f_a.brand_id.value;
		}
		else if (/brands\[brand\]/.test(el[i].name))
		{
		    el[i].value = $('autocomplete_brand').value;
		}
	}
}


function brand_duplicate(obj,n,val){
	if(n=='new'){
		document.f_a.elements['brands[brand]['+last_brand_row+']'].value=obj.value;
	}
	else{
		document.f_a.elements['brands[brand]['+val+']'].value=obj.value;
	}
}


function offer_duplicate(obj,n,val){
	if(n=='new'){
        document.f_a.elements['offers[sku_item_code]['+last_offer_row+']'].value=obj.value;
	}
	else{
		document.f_a.elements['offers[sku_item_code]['+val+']'].value=obj.value;
	}
}

function add_prefix(el)
{
	uc(el);
	if (!/^~/.test(el.value)) el.value = '~'+el.value;
	
}
// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	var param_str = "a=ajax_search_sku&get_price=1&dept_id={/literal}{$form.dept_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value = s[0];
			document.f_a.sku_item_code.value = s[1];
			document.f_a.sku_item_cost.value = round(s[2],3);
			document.f_a.sku_item_selling.value = round(s[3],2);
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete()
{
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	document.f_a.sku_item_cost.value = '';
	document.f_a.sku_item_selling.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}
</script>
{/literal}
<h1>{$PAGE_TITLE} {if $form.id}({$form.current_branch}, MKT{$form.id|string_format:"%05d"}){else}(New){/if}</h1>


<form name=f_a method=post>
<input type=hidden name=a value=save>
<input type=hidden name=id value={$form.id}>
<input type=hidden name=branch_id value={$form.branch_id}>
{assign var=branch_id value=$sessioninfo.branch_id}
<input type=hidden name=dept_id value={$form.dept_id}>
<input type=hidden name=limit[min_offer] value={$form.limit.min_offer}>
<input type=hidden name=limit[min_brand] value={$form.limit.min_brand}>

<div class=stdframe style="background:#fff">
<table cellspacing=0 cellpadding=4 border=0 class=tl>
<tr>
	<td colspan=2 class=small>Created: {$form.added}, Last Update: {$form.last_update}</td>
</tr>
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.branches[$br]}<img src=/ui/checked.gif>
	{if $form.branch_id == $branch.id}<font color=red>{/if}
	 {$branch.code}
	{if $form.branch_id == $branch.id}</font>{/if} 
	{/if}	 
	{/foreach}
	</td>
</tr><tr>
	<th>Promotion Title</th>
	<td>{$form.title}</td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		{$form.offer_from|date_format:'%d/%m/%Y'} to {$form.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th nowrap>Submit Due Date</th>
	<td>
		{$form.submit_due_date_3|date_format:'%d/%m/%Y'}
	</td>
</tr>
<tr>
	<th>Publish Date </th>
	<td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $form.publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">{$form.publish_dates[$x]}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>
</tr>
<tr>
	<th nowrap>Attachments</th>
	<td>
		{foreach from=$form.attachments.name key=idx item=fn}
		{if $fn}<img src=/ui/icons/attach.png align=absmiddle> <a href="javascript:void(window.open('{$image_path}{$form.filepath[$idx]}'))">{$fn}</a> &nbsp;&nbsp; {/if}
		{/foreach}
	</td>
</tr><tr>
	<th nowrap>Promotion<br>Period Remark</th>
	<td>
		{$form.remark|nl2br}
	</td>
</tr><tr>
	<th nowrap>Department</th>
	<td>
		{$form.department}
	</td>
</tr>
<tr>
	<th nowrap>Normal Forecast</th>
	<td>
		{$form.normal_target|number_format:2}
	</td>
</tr>
<tr>
	<th nowrap>Sales Target</th>
	<td>
		{$form.sales_target|number_format:2}
	</td>
</tr>
<tr>
	<th nowrap>Variant</th>
	<td>
		{$form.sales_target-$form.normal_target|number_format:2}
	</td>
</tr>

</table>
</div>

<h3>Offer Items</h3>
- You must enter at least {$form.limit.min_offer|default:1} offer item{if $form.limit.min_offer>1}s{/if}.<br>
- Rows without 'Product Code' column will not be saved.
{if $form.unapproved_branch}
<br>
- The offer item cannot copy to the UNAPPROVED branches in MKT3.
	(
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.unapproved_branch[$br]}<font color=blue><i>{$branch.code} </i></font>{/if}
	{/foreach})<br>
{/if}
{if $form.confirmed_branch}
- The offer item cannot copy to the CONFIRMED branches in MKT4.
	(
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.confirmed_branch[$br]}<font color=blue><i>{$branch.code} </i></font>{/if}
	{/foreach})
{/if}
<br>

{if $errm.offers}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.offers item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table id=offer_items_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th rowspan=2>Copy To All Branch</th>
	<th rowspan=2>Product Code</th>
	<th rowspan=2>Description</th>
	<th rowspan=2>Cost</th>
	<th rowspan=2>Balance Stock</th>
	<th rowspan=2>Current Selling</th>
	<th rowspan=2>Propose Selling</th>
	<th colspan=3>Competitor Price</th>
	<th rowspan=2>Qty</th>
	<th rowspan=2>Total Cost</th>
	<th rowspan=2>Total Selling</th>
	<th rowspan=2>Mk Up/Dn</th>
	<th rowspan=2>%</th>
	<th rowspan=2>Limit</th>
	<th rowspan=2>A-Kad</th>
</tr>
<tr>
	<th>A</th>
	<th>B</th>
	<th>C</th>
</tr>

<tbody id=offer_rows>
{foreach from=$form.offers.sku_item_code key=i item=dummy}

<tr>
	<td align=center>&nbsp;</td>
	<td nowrap>
	    {if $smarty.request.a ne 'view'}
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		{/if}
		{if $form.offers.sku_item_id.$i[0] == '~'}
		{assign var=readonly value=''}
		<input size=12 onchange="add_prefix(this);offer_duplicate(this,'old',{$i});" name=offers[sku_item_id][{$i}] value="{$form.offers.sku_item_id.$i}">
		<input size=12 name=offers[sku_item_code][{$i}] value="{$form.offers.sku_item_code.$i}" type=hidden>
		{else}
		{assign var=readonly value='readonly'}
		<input size=12 name=offers[sku_item_id][{$i}] value="{$form.offers.sku_item_id.$i}" type=hidden>
		<input size=12 name=offers[sku_item_code][{$i}] value="{$form.offers.sku_item_code.$i}" {$readonly}>
		{/if}
	</td>
	<td><input size=60 name=offers[description][{$i}] value="{$form.offers.description.$i}" {$readonly}></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=8 onchange="mfz(this);recalc_row({$i});" name=offers[cost][{$i}] value="{$form.offers.cost.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange="miz(this);" size=8 name=offers[balance][{$i}] value="{$form.offers.balance.$i}"></td>
	<td><input class=r size=8 name=offers[selling][{$i}] value="{$form.offers.selling.$i}" {$readonly}></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange="mfz(this);recalc_row({$i});" size=8 name=offers[propose_selling][{$i}] value="{$form.offers.propose_selling.$i}"></td>
	<td><input class=r size=8 name=offers[competitor_1][{$i}] value="{$form.offers.competitor_1.$i}" {$readonly}></td>
	<td><input class=r size=8 name=offers[competitor_2][{$i}] value="{$form.offers.competitor_2.$i}" {$readonly}></td>
	<td><input class=r size=8 name=offers[competitor_3][{$i}] value="{$form.offers.competitor_3.$i}" {$readonly}></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=3 name=offers[qty][{$i}] value="{$form.offers.qty.$i}" onchange="miz(this);recalc_row({$i});"></td>
	<td><input class=r size=8 name=offers[tcost][{$i}] value="{$form.offers.tcost.$i}" readonly></td>
	<td><input class=r size=8 name=offers[tsell][{$i}] value="{$form.offers.tsell.$i}" readonly></td>
	<td><input class=r size=6 name=offers[mkup][{$i}] value="{$form.offers.mkup.$i}" readonly></td>
	<td><input class=r size=6 name=offers[mkup_pct][{$i}] value="{$form.offers.mkup_pct.$i}" readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=3 name=offers[limit][{$i}] value="{$form.offers.limit.$i}" onchange="miz(this)"></td>
	<td align=center><input type=checkbox name=offers[member][{$i}] {if $form.offers.member.$i}checked{/if}><input type=hidden name=offers_normal_target_val[{$i}] value="">
<input type=hidden name=offers_promo_target_val[{$i}] value=""></td>
</tr>
{/foreach}
</tbody>

<tr>
<td colspan=11 rowspan=2>&nbsp;</td>
<td id=t_cost class="zero">&nbsp;</td>
<td id=t_sell class="zero">&nbsp;</td>
<td id=t_mkup class="zero">&nbsp;</td>
<td id=t_pct class="zero">&nbsp;</td>
<td colspan=2 class="zero" rowspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=4 align=center class=negative>
<span id="Msg" title="Message" class="negative"></span>&nbsp;
</td>
</tr>

<script>
var last_offer_row = {$i|default:0};
</script>

{if $smarty.request.a ne 'view'}
<tr id=offer_template style="display:none">
	<td align=center><input type=checkbox name=offer_copy[]></td>
	<td nowrap>
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		<input size=12 onchange="add_prefix(this);offer_duplicate(this,'new',{$i});" name=offers[sku_item_id][] type=hidden class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}">
		<input size=12  name=offers[sku_item_code][] readonly>
	</td>
	<td><input size=60 name=offers[description][] readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=8 name=offers[cost][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange="miz(this);" size=8 name=offers[balance][]></td>
	<td><input class=r size=8 name=offers[selling][] readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange="mfz(this);recalc_row(this.name);" size=8 name=offers[propose_selling][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=8 name=offers[competitor_1][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=8 name=offers[competitor_2][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=8 name=offers[competitor_3][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=3 name=offers[qty][] onchange="miz(this);recalc_row(this.name);"></td>
	<td><input class=r size=8 name=offers[tcost][] readonly></td>
	<td><input class=r size=8 name=offers[tsell][] readonly></td>
	<td><input class=r size=6 name=offers[mkup][] readonly></td>
	<td><input class=r size=6 name=offers[mkup_pct][] readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=3 name=offers[limit][] onchange="miz(this)"></td>
	<td align=center><input type=checkbox name=offers[member][]></td>
</tr>
{/if}
</table>
<br>
{if $smarty.request.a ne 'view'}
<br>
<table>
	<tr><th>Search SKU</th>
	<td>
		<input name="sku_item_id" type=hidden>
		<input name="sku_item_code" type=hidden>
		<input name="sku_item_cost" type=hidden>
		<input name="sku_item_selling" type=hidden>
		<input id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;">
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
    <td><input type=button value="Add" style="background:#f90;color:#fff" onclick="insert_sku()">&nbsp;&nbsp;<input type=button value="Add New Product" style="background:#f90;color:#fff" onclick="add_row('offer_rows','offer_template',true)"></td>
	</tr>
	<tr><td>&nbsp;</td><td>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=1 checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=2> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=3> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value=4> Description
    </td>
	</tr>
</table>
{/if}

<h3>Brand Discount</h3>
- You must enter at least {$form.limit.min_brand|default:1} brand{if $form.limit.min_offer>1}s{/if}.<br>
- Rows without 'Brand' column will not be saved.
{if $form.unapproved_branch}
<br>
- The brand cannot copy to the UNAPPROVED branches in MKT3.
	(
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.unapproved_branch[$br]}<font color=blue><i>{$branch.code} </i></font>{/if}
	{/foreach})<br>
{/if}
{if $form.confirmed_branch}
- The brand cannot copy to the CONFIRMED branches in MKT4.
	(
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.confirmed_branch[$br]}<font color=blue><i>{$branch.code} </i></font>{/if}
	{/foreach})
{/if}
<br>

{if $errm.brands}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.brands item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table id=brand_discount_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th rowspan=2>Copy To All Branch</th>
	<th rowspan=2>Brand</th>
	<th rowspan=2>Propose Discount % or Promotion Mechanic</th>
	<th rowspan=2>Competitor Mechanic</th>
	<th colspan=2>Sales Target</th>
	<th rowspan=2>Limit</th>
	<th rowspan=2>A-Kad</th>
</tr>
<tr>
	<th>Normal</th>
	<th>Promotion</th>
</tr>

<tbody id=brand_rows>
{foreach from=$form.brands.brand key=i item=dummy}
<tr>
	<td align=center>&nbsp;</td>
	<td nowrap>
	    {if $smarty.request.a ne 'view'}
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		{/if}
 		{if $form.brands.brand_id.$i[0] == '~'}
		{assign var=readonly value=''}
		<input size=25 onchange="add_prefix(this);brand_duplicate(this,'old',{$i});" name=brands[brand_id][{$i}] value="{$form.brands.brand_id.$i}">
		<input size=25 name=brands[brand][{$i}] value="{$form.brands.brand.$i}" type=hidden>
		{else}
		{assign var=readonly value='readonly'}
		<input size=25 name=brands[brand_id][{$i}] type=hidden value="{$form.brands.brand_id.$i}">
		<input size=25 name=brands[brand][{$i}] value="{$form.brands.brand.$i}" {$readonly}>
		{/if}
	</td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}" size=50 name=brands[discount_or_mechanic][{$i}] value="{$form.brands.discount_or_mechanic.$i}"></td>
	<td><input size=50 name=brands[competitor_mechanic][{$i}] value="{$form.brands.competitor_mechanic.$i}" {$readonly}></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}"  size=10 onchange=mfz(this) name=brands[target_normal][{$i}] value="{$form.brands.target_normal.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=10 onchange=mfz(this) name=brands[target_promo][{$i}] value="{$form.brands.target_promo.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=3 name=brands[limit][{$i}] value="{$form.brands.limit.$i}" onchange="miz(this)"></td>
	<td align=center ><input type=checkbox name=brands[member][{$i}] {if $form.brands.member.$i}checked{/if}></td>
</tr>
{/foreach}
</tbody>

<script>
var last_brand_row = {$i|default:0};
</script>

{if $smarty.request.a ne 'view'}
<tr id=brand_template style="display:none">
	<td align=center><input id=copy type=checkbox name=brand_copy[]></td>
	<td nowrap>
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		<input size=25 onchange="add_prefix(this);brand_duplicate(this,'new',{$i});" name=brands[brand_id][] type=hidden class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}">
		<input size=25 name=brands[brand][] readonly>
	</td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}" size=50 name=brands[discount_or_mechanic][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}" size=50 name=brands[competitor_mechanic][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=10 onchange=mfz(this) name=brands[target_normal][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=10 onchange=mfz(this) name=brands[target_promo][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" class=r size=3 name=brands[limit][] onchange="miz(this)"></td>
	<td align=center><input type=checkbox name=brands[member][] {if $form.brands.member.$i}checked{/if}></td>
</tr>
{/if}
</table>
<br>
{if $smarty.request.a ne 'view'}
<table>
<tr>
	<th>Search Brand</th>
	<td>
	<input name="brand_id" type=hidden size=1 readonly>
	<input id="autocomplete_brand" name="brand" size=30>
	<div id="autocomplete_brand_choices" class="autocomplete"></div>
	</td>
    <td><input type=button value="Add" style="background:#f90;color:#fff" onclick="insert_brand()">&nbsp;&nbsp;<input type=button value="Add New Brand" style="background:#f90;color:#fff" onclick="add_row('brand_rows','brand_template',true)"></td>
</tr>
</table>
{/if}

</form>

<p id=submitbtn align=center>

{if $smarty.request.a ne 'view' and $mkt4_privilege.MKT4_EDIT.$branch_id}
<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >&nbsp;&nbsp;&nbsp;
{/if}

<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt4.php?branch_id={$form.branch_id}'">&nbsp;&nbsp;&nbsp;

{if $smarty.request.a ne 'view' and $mkt4_privilege.MKT4_EDIT.$branch_id}
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
{/if}
</p>



{include file=footer.tpl}
<script>
{if $smarty.request.a eq 'view' or $sessioninfo.branch_id!=1 and $mkt4_privilege.MKT4_EDIT.$branch_id}
Form.disable(document.f_a);
{else}
{literal}
new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand&no_unbranded=1", { afterUpdateElement: function (obj, li) { document.f_a.brand_id.value = li.title; }});
{/literal}
calculate_total();
reset_sku_autocomplete();
_init_enter_to_skip(document.f_a);
{/if}
</script>

