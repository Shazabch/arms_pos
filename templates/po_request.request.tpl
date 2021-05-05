{*
Revision History
================
4 Apr 2007 - yinsee
- replace 'Multics Code' with $config.link_code_name

4/24/2007 5:23:30 PM - Gary
- added comment box for add item

4/30/2007 - yinsee
- add "autosave" message
- added Close button

7/3/2009 3:00 PM Andy
- get item details such as sales trend when item selected

6/22/2011 11:09:10 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- Added new function to change the qty and stock balance fields to enable/disable decimal points key in base on sku item's doc_allow_decimal.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

04/20/2020 06:16 PM Sheila
- Modified layout to compatible with new UI.


06/24/2020 02:42 PM Sheila
- Fixed Search SKU font-size

*}
{include file=header.tpl}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var po_get_item_details = '{$config.po_request_show_sales_trend}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
function list_sel(n,s)
{
	var i;
	for(i=1;i<=4;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('req_items').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('req_items', 'po_request.request.php', {
		parameters: 'a=ajax_load_items&t='+n+'&'+pg,
		evalScripts: true
		});
}

// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	var param_str = "a=ajax_search_sku&block_list=1&get_last_po=1&sku_type=outright&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
			var doc_allow_decimal = document.f_a.elements['inp_dad,'+s[0]].value;
			if(doc_allow_decimal == 1){
				document.f_a.qty.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
				document.f_a.balance.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
			}else{
				document.f_a.qty.onchange = function(){ mi(this); };
				document.f_a.balance.onchange = function(){ mi(this); };
			}
			
			if (s[0]>0)
			{
			    // if config['po_request_show_sales_trend'] turn on
			    if(po_get_item_details)	get_item_details(s[0]);
			    
				new Ajax.Request( 'po_request.request.php?id='+s[0]+'&a=get_last_po',
					{
						onComplete:function(m)
						{
						    //alert(m.responseText);
						    s = m.responseText.split(",");
							document.f_a.qty.value = s[0];
							changesel(document.f_a.uom_id, s[1]);
						}
					});
				document.f_a.qty.focus();
			}
			else{
				clear_autocomplete();
			}
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete()
{
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	document.f_a.qty.value = '';
	document.f_a.balance.value = '';
	document.f_a.comment.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}

function del_item(id)
{
	new Ajax.Updater('req_items', 'po_request.request.php',
	{
		parameters: 'a=ajax_del_item&id='+id,
		evalScripts: true
	});
}

function add_item(){

    //chk_sku(document.f_a.sku);
	if (empty_or_zero(document.f_a.sku_item_id, 'Select SKU to add'))
	{
	    $('autocomplete_sku').focus();
	    return false;
	}
	if (empty_or_zero(document.f_a.qty, 'Please enter Quantity'))
	{
	    return false;
	}
	if (empty(document.f_a.balance, 'Please enter Stock Balance'))
	{
	    return false;
	}

	new Ajax.Updater('req_items', 'po_request.request.php',
	{
		parameters: 'a=ajax_add_item&'+Form.serialize(document.f_a),
		evalScripts: true
	});
	clear_autocomplete();
}

function get_price_history(obj,id)
{
	Position.clone(obj, $('price_history'), {setHeight: false, setWidth:false});
	Element.show('price_history');
	$('price_history_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater(
	'price_history_list',
	'ajax_autocomplete.php',
		{
		    parameters: 'a=sku_cost_history&id='+id
		}
	);
}

function get_item_details(sku_item_id){
	$('div_item_details').update(_loading_);
	new Ajax.Updater('div_item_details',phpself,{
		parameters:{
		    a: 'get_item_details',
			sku_item_id: sku_item_id
		},
		evalScripts: true
	})
}
</script>
{/literal}
{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if}
<h1>{$PAGE_TITLE}</h1>
<ul>
<li> You can only search the products under your allowed departments.
<li> Items added to Request are auto-saved.
</ul>
<div id=tbl_1 class=stdframe style="margin:5px 0;background:#fff;">
<!-- sku search -->
<form name=f_a>
<input name="sku_item_id" size=3 type=hidden>
<input name="sku_item_code" size=13 type=hidden>
<table class="tl" cellpadding=2 cellspacing=0 border=0>
<tr><th>Search SKU</th>
<td colspan=6><input id="autocomplete_sku" name="sku" size=50 onclick="this.select();" style="font-size:13px;width:500px;"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div></td></tr>
<tr><td>&nbsp;</td><td colspan=6><input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
</td></tr>
</TABLE>
<div id="div_item_details">
</div>
<table>
<tr>
	<th>Request Qty</th><td><input size=3 name="qty" onchange="mi(this)"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
	<td>&nbsp;</td>
	<th>UOM</th>
	<td>
	<select name="uom_id">
	{foreach item="curr_uom" from=$uom}
	<option value={$curr_uom.id}>{$curr_uom.code}
	</option>
	{/foreach}
	</select> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
	</td>
	<td>&nbsp;</td>
	<th>Stock Balance</th><td><input size=3 name="balance" onchange="mi(this)"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
	<th>Remarks</th><td><input size=32 name="comment"></td>
	<td>&nbsp;</td><td><input class="btn btn-primary" type=button value="Add to PO Request" onclick="add_item()"></td>
</tr>
</table><br>
</form>
</div>
<br>
<div class=tab style="height:21px;white-space:nowrap;">
&nbsp;&nbsp;
<a id=lst1 href="javascript:list_sel(1);" class=active>New</a>
<a id=lst2 href="javascript:list_sel(2);">Approved</a>
<a id=lst3 href="javascript:list_sel(3);">Used In PO</a>
<a id=lst4 href="javascript:list_sel(4);">Rejected</a>
</div>

<div id=req_items style="border:1px solid #000;padding:1px;">
{include file=po_request.request.items.tpl}
</div>

<p align=center><input class="btn btn-error" type=button value="Close" onclick="document.location='/'"></p>
<script>
reset_sku_autocomplete();
</script>
{include file=footer.tpl}
