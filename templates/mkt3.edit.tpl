{if !$form.approval_screen}
{include file=header.tpl}
{/if}
{literal}
<style>
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
function do_approve()
{
	document.f_approve.a.value='approve';
	document.f_approve.reason.value='Approved';
	document.f_approve.submit();
}
function do_reject()
{
	var s = prompt('Enter Reason:');
	if (s==null)
	{
	    return false;
	}
	document.f_approve.reason.value=s;
	document.f_approve.a.value='reject';
	document.f_approve.submit();
}

function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}
function do_confirm()
{
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
}

function check_a()
{
	return true;
}

// insert new empty row and return the new row handler
function add_row(parent,template)
{
	var new_row = $(template).cloneNode(true);
	new_row.style.display='';
	new_row.id='';
	$(parent).appendChild(new_row);
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

// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.dept_id}{literal}&get_price=1&type="+getRadioValue(document.f_a.search_type);
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

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{include file=approval_history.tpl}

<form name=f_a method=post>
<input type=hidden name=a value=save>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type=hidden name=id value={$form.id}>
<input type=hidden name=branch_id value={$branch_id}>
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
	{if $branch_id == $branch.id}<font color=red>{/if}
	{$branch.code}
	{if $branch_id == $branch.id}</font>{/if}
	{/if}
	{/foreach}
	</td>
</tr>
<tr>
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
		{$form.submit_due_date_2|date_format:'%d/%m/%Y'}
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
- Rows without 'Product Code' column will not be saved.<br><br>

{if $errm.offers}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.offers item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table id=offer_items_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th rowspan=2>ARMS Code</th>
	<th rowspan=2>Description</th>
	<th rowspan=2>Cost</th>
	<th rowspan=2>Balance Stock</th>
	<th rowspan=2>Current Selling</th>
	<th rowspan=2>Propose Selling</th>
	<th colspan=3>Competitor Price</th>
</tr>
<tr>
	<th>A</th>
	<th>B</th>
	<th>C</th>
</tr>

<tbody id=offer_rows>

{foreach from=$form.offers.sku_item_code key=i item=dummy}
<tr>
	<td nowrap>
	    {if $smarty.request.a ne 'view'}
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		{/if}
		<input name=offers[sku_item_id][] value="{$form.offers.sku_item_id.$i}" type=hidden><input size=12 name=offers[sku_item_code][] value="{$form.offers.sku_item_code.$i}" readonly></td>
	<td><input size=60 name=offers[description][] value="{$form.offers.description.$i}" readonly></td>
	<td><input class=r onchange=mfz(this) size=8 name=offers[cost][] value="{$form.offers.cost.$i}" readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=miz(this) size=8 name=offers[balance][] value="{$form.offers.balance.$i}"></td>
	<td><input class=r onchange=mfz(this) size=8 name=offers[selling][] value="{$form.offers.selling.$i}" readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[propose_selling][] value="{$form.offers.propose_selling.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_1][] value="{$form.offers.competitor_1.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_2][] value="{$form.offers.competitor_2.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_3][] value="{$form.offers.competitor_3.$i}"></td>
</tr>
{/foreach}

</tbody>

{if $smarty.request.a ne 'view'}
<tr id=offer_template style="display:none">
	<td nowrap>
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		<input name=offers[sku_item_id][] type=hidden>
		<input size=12 name=offers[sku_item_code][]>
	</td>
	<td><input size=60 name=offers[description][] readonly></td>
	<td><input class=r onchange=mfz(this) size=8 name=offers[cost][] readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=miz(this) size=8 name=offers[balance][]></td>
	<td><input class=r onchange=mfz(this) size=8 name=offers[selling][] readonly></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[propose_selling][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_1][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_2][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=8 name=offers[competitor_3][]></td>
</tr>
{/if}

</table>
<br>
{if $smarty.request.a ne 'view'}
<!--img src="/ui/table_row_insert.png" align=absmiddle> <a href="">Add More Rows</a><br-->
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
    <td><input type=button value="Add" style="background:#f90;color:#fff" onclick="insert_sku()"></td>
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
- Rows without 'Brand' column will not be saved.<br><br>

{if $errm.brands}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.brands item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table id=brand_discount_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th rowspan=2>Brand</th>
	<th rowspan=2>Propose Discount % or Promotion Mechanic</th>
	<th rowspan=2>Competitor Mechanic</th>
	<th colspan=2>Sales Target</th>
</tr>
<tr>
	<th>Normal</th>
	<th>Promotion</th>
</tr>

<tbody id=brand_rows>

{foreach from=$form.brands.brand key=i item=dummy}
<tr>
	<td nowrap>
	    {if $smarty.request.a ne 'view'}
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		{/if}
		<input size=3 name=brands[brand_id][] type=hidden value="{$form.brands.brand_id.$i}">
		<input size=25 name=brands[brand][] value="{$form.brands.brand.$i}" readonly>
	</td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}" size=50 name=brands[discount_or_mechanic][] value="{$form.brands.discount_or_mechanic.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin_w{/if}" size=50 name=brands[competitor_mechanic][] value="{$form.brands.competitor_mechanic.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=10 onchange=mfz(this) name=brands[target_normal][] value="{$form.brands.target_normal.$i}"></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" size=10 onchange=mfz(this) name=brands[target_promo][] value="{$form.brands.target_promo.$i}"></td>
</tr>
{/foreach}

</tbody>

{if $smarty.request.a ne 'view'}
<tr id=brand_template style="display:none">
	<td nowrap>
		<img src="/ui/remove16.png" class=clickable onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode)">
		<input size=3 name=brands[brand_id][] type=hidden>
		<input size=25 name=brands[brand][] readonly>
	</td>
	<td><input size=50 name=brands[discount_or_mechanic][]></td>
	<td><input size=50 name=brands[competitor_mechanic][]></td>
	<td><input size=10 onchange=mfz(this) name=brands[target_normal][]></td>
	<td><input size=10 onchange=mfz(this) name=brands[target_promo][]></td>
</tr>
{/if}

</table>
<br>
{if $smarty.request.a ne 'view' }
<table>
<tr>
	<th>Search Brand</th>
	<td>
	<input name="brand_id" type=hidden size=1 readonly>
	<input id="autocomplete_brand" name="brand" size=30>
	<div id="autocomplete_brand_choices" class="autocomplete"></div>
	</td>
    <td><input type=button value="Add" style="background:#f90;color:#fff" onclick="insert_brand()"></td>
</tr>
</table>
{/if}
</form>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $mkt3_privilege.MKT3_EDIT.$branch_id}
<form name=f_approve>
<input type=hidden name=a>
<input type=hidden name=reason>
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
<input type=hidden name=id value={$form.id}>
<input type=hidden name=approvals value="{$form.approvals}">
<input type=hidden name=branch_id value={$branch_id|default:$smarty.request.branch_id}>
<input type=hidden name=dept_id value={$form.dept_id}>
<input type=hidden name=limit[min_offer] value={$form.limit.min_offer}>
<input type=hidden name=limit[min_brand] value={$form.limit.min_brand}>
</form>
{/if}

<p id=submitbtn align=center>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen and $mkt3_privilege.MKT3_APPROVAL.$branch_id}
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
{/if}

{if $smarty.request.a ne 'view' and $mkt3_privilege.MKT3_EDIT.$branch_id and !$form.approval_screen}
<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >

<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
{/if}

{if !$form.approval_screen}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt3.php?branch_id={$branch_id}'">
{/if}
</p>

{include file=footer.tpl}
<script>
{if $smarty.request.a eq 'view' or $form.approval_screen}
Form.disable(document.f_a);
{else}
{literal}
new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand&no_unbranded=1", { afterUpdateElement: function (obj, li) { document.f_a.brand_id.value = li.title; }});
{/literal}
reset_sku_autocomplete();
_init_enter_to_skip(document.f_a);
{/if}
</script>

