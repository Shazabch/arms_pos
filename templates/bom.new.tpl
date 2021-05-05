{*
REVISION HISTORY
=================
3/3/2008 4:59:52 PM gary
- calculate row total cost and selling in calc_all() function.

10/7/2009 11:34:06 AM yinsee
- fix javascript error preventing load bom details
> mcode could be non-exists if $config.sku_bom_show_mcode is not set

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

10/10/2011 11:19:42 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

9/19/2012 10:50 AM Andy
- Add new BOM Type (Package). (Need Config)

9/26/2012 12:22 PM Justin
- Enhanced to have new function which used for Selling FOC.

12/18/2012 3:30 PM Justin
- Bug fixed on showing JS error while changing BOM Type.

5/9/2013 3:58 PM Fithri
- bugfix - disable auto focus to next row qty input

1/7/2015 4:40 PM Justin
- Enhanced to have GST calculation.

1/28/2015 3:10 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.
- Enhance Open Price/Allow Selling FOC checking.

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

7/13/2018 2:31 PM Andy
- Fixed bug where item in bom content will become missing if user delete it in other tab.

11/6/2019 11:57 AM William
- Fixed bug reason value will show on create new item when last edit bom item has reason field value.
- Fixed bug after save bom editor items, the success message will always show on top after change sku item.

11/9/2020 4:33 PM Andy
- Fixed AVG Selling popup position.

02/16/2021 9:24 AM Rayleen
- update load_bom_details() function to refresh marketplace mandatory fields

03/02/2021 5:37 PM Rayleen
- Fix bug undefined "marketplace_description" if no config for "arms_marketplace_settings"
*}
{include file=header.tpl}
{literal}
<style>
#div_price_list ul li {
	cursor:cursor;
	display:block;
	margin:0;padding:0px;
	font-size:8pt;
	font-weight:bold;
}
#div_price_list ul li:hover {
	background:#9ff;
}

#div_price_list:hover ul {
	visibility:visible;
}
</style>

<script type="text/javascript">
{/literal}
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var bom_id = '{$form.bom_id}';
var global_inclusive_tax = '{$mst_inclusive_tax}';
var arms_marketplace_settings = '{$config.arms_marketplace_settings}';
{if $gst_status}
var is_gst_active = true;
{else}
var is_gst_active = false;
{/if}

var gst_rate_list = [];
var gst_code_list = [];

{foreach from=$output_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
    gst_code_list['{$r.id}'] = '{$r.code}'
{/foreach}
var sku_non_returnable = int('{$config.sku_non_returnable}');
{literal}

function load_sku_bom(obj){
	document.f_a.bom_id.value=0;
	load_bom_details();
}

function load_bom_details(){
	document.f_a.description.value='';
	document.f_a.receipt_description.value='';
	document.f_a.artno.value='';
	document.f_a['edit_time'].value='';
	if (document.f_a.mcode!=undefined) document.f_a.mcode.value='';
	document.f_a.selling_price.value='';
	document.f_a.cost_price.value='';
	document.f_a.misc_cost.value='';
	document.f_a.reason.value='';
	document.f_a.bom_type.value='';
	document.f_a.open_price.value='';
	document.f_a.allow_selling_foc.value='';
	document.f_a.location.value='';
    var branches_id = document.querySelectorAll('.branches_id');
    for (var i = 0; i < branches_id.length; i++) {
		branches_id[i].checked = false;
    }
	if(sku_non_returnable) document.f_a.non_returnable.value='-1';
	document.f_a.weight_kg.value='';
	document.f_a.width.value='';
	document.f_a.height.value='';
	document.f_a.length.value='';
	if(arms_marketplace_settings) document.f_a.marketplace_description.value='';
	
	document.f_a.a.value='load_bom_details';
	history.pushState(null,null,'bom.php');
	document.f_a.submit();
}

/*
var sku_autocomplete = undefined;
function reset_sku_autocomplete(){

	var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(document.f_a.search_type);
	
	if (sku_autocomplete != undefined){
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
			$('btn_add_item').focus();
		}});
	}
}

function add_item(){
	if (int(document.f_a.sku_item_id.value)==0){
	    alert('No item selected');
	    return false;
	}
	var parms;
    parms = Form.serialize(document.f_a)+'&a=ajax_add_item';

	new Ajax.Request("bom.php",{
		method:'post',
		parameters: parms,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            var tb = $('bom_items');
            var lbody;
			var xml = m.responseXML;
				
			if (!xml) { alert(m.responseText); return; }
			var records = xml.getElementsByTagName("record");
			$A(records).each(
				function(r,idx){
					var rowitem = tb.insertRow(-1);
					rowitem.id = "titem"+xml_getData(r, "id").strip();
					rowitem.innerHTML = xml_getData(r,'rowdata');
				}
			);
			calc_all();
			reset_row_no();
		},
	});
}
*/

function add_autocomplete_extra()
{
	calc_all();
	reset_row_no();
}

function reset_row_no(){
	var e = $('docs_items').getElementsByClassName('no');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^no_');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no_'+(i+1);
			$('item_qty_'+e[i].title).alt=(i+1);
			//alert($('item_qty_'+e[i].title).alt);
		}
	}
}

function do_confirm(){
	if (empty(document.f_a.description, "You must enter Product Description")){
	    return false;
	}
	if (empty(document.f_a.receipt_description, "You must enter Receipt Description")){
	    return false;
	}
	if (empty(document.f_a.selling_price, "You must enter Selling Price")){
	    return false;
	}
	if (empty(document.f_a.cost_price, "You must enter Cost Price")){
	    return false;
	}
	/*
	if (empty(document.f_a.misc_cost, "You must enter Misc Cost")){
	    return false;
	}
	*/
	
	if(!check_receipt_desc_length("receipt_description"))	return false;
	
	document.f_a.a.value='confirm';
	document.f_a.submit();			
}

function calc_all(){
	BOM_EDITOR.recal_total();
}

function delete_item(id){
 	if (!confirm('Remove this SKU from BOM?')) return;
	new Ajax.Request("bom.php",{
		method:'post',
		parameters: Form.serialize(document.f_a)+'&a=ajax_delete_row&delete_id='+id,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            Element.remove('titem'+id);
            reset_row_no();
            calc_all();
    	}
	});
}

function focus_next_row(obj){
	
	// no need this function for now
	return;
	
	var last_alt=float(obj.alt)+1;
	
	var e = $('docs_items').getElementsByClassName('total');
	for(var i=0;i<e.length;i++)	{
		var temp_2 =new RegExp('^item_qty');
		if (temp_2.test(e[i].id)) {
			if(e[i].alt==last_alt)
				e[i].focus();
		}	
	}
}

function check_a(){
	if (empty(document.f_a.qty_bom, "You must enter BOM Qty")){
	    return false;
	}
	if (document.f_a.qty_bom.value>0){
		return true;
	}
	else{
		alert('Your must enter valid BOM Qty.');
		return false;
	}	

}

function do_print(id)
{
	document.f_a.a.value='print_bom';
	document.f_a.target = '_xifprint';
	document.f_a.submit();	
	document.f_a.target = '';
}

function do_implode(){
	document.f_a.a.value='implode';
	if(check_a())
		document.f_a.submit();		
}

function do_explode(){
	document.f_a.a.value='explode';
	if(check_a())
		document.f_a.submit();	
}

function get_all_price(sid,id){
	if($('div_price_list').style.display=='none'){
		$('div_price_list').innerHTML='<img src=ui/clock.gif align=absmiddle><br /><img src=ui/pixel.gif height=500 width=1>';
		Position.clone($('price_list_'+sid), $('div_price_list'), {setHeight: false, setWidth:false});
		Element.show('div_price_list');
		new Ajax.Updater('div_price_list', 'bom.php', {
			parameters: 'a=load_price_list&sku_item_id='+sid,
			evalScripts: true
		});
	}
	else{
		Element.hide('div_price_list');		
	}	
}

function check_artmcode(obj, type){
{/literal}
	{if $config.sku_artno_allow_specialchars}
	obj.value = new String(obj.value).trim().toUpperCase();
	{else}
	obj.value = new String(obj.value).uczap();
	{/if}
{literal}
	/*
	if (obj.value == '') return;
	// check database
	s = 'a=ajax_check_artmcode&vendor_id=0&from_bom=1&id='+ document.f_a.sku_bom.value + '&'+type+'=' + obj.value+'&brand_id='+ document.f_a.brand_id.value+'&category_id='+document.f_a.category_id.value;
	new Ajax.Request(
 		"masterfile_sku_application.php",
 		{
			method:'post',
			onComplete: function (m) {
				if (m.responseText != 'OK')
				{
					alert("Error:"+m.responseText);
					obj.value = '';
					obj.focus();
				}
			},
		    parameters: s
		}
	);
	*/
}

var BOM_EDITOR = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		this.calc_gst();
	},
	// function when bom type changed
	bom_type_changed: function(){
		this.update_bom_type_related_elelemt();
	},
	// function to get bom type
	get_bom_type: function(){
		var t = 'normal';
		
		if(this.f['bom_type']){
			t = this.f['bom_type'].value;
		}
		
		return t;
	},
	// control to show/hide element base on bom type
	update_bom_type_related_elelemt: function(){
		if(!this.f['bom_type'])	return false;
		
		var t = this.get_bom_type();
		
		if(t == 'package'){
			if(bom_id > 0) $('div_qty_implode_explode').hide();
			this.f['selling_price'].readOnly = true;
			
			this.recal_total();	// must recalculate item selling
		}else{
			if(bom_id > 0) $('div_qty_implode_explode').show();
			this.f['selling_price'].readOnly = false;
		}
		bom_type = t;
	},
	// function to recalculate total
	recal_total: function(){
		var total_row=0;
		var total_cost=0, total_sell=0;
		var total=0;
		var total_qty=0;
		var misc_cost=0;
			
		var qty=new Array();
		var cost=new Array();
		var sell=new Array();
		//alert(($('bom_items').getElementsByClassName('no')).length);
		var e = $('docs_items').getElementsByClassName('total');
		for(var i=0;i<e.length;i++)	{
	 		var temp_1 =new RegExp('^item_cost');
			if (temp_1.test(e[i].id)) {
				cost[e[i].title]=float(e[i].value);
			}
	 		var temp_2 =new RegExp('^item_selling');
			if (temp_2.test(e[i].id)){
				sell[e[i].title]=float(e[i].value);
			}
	 		var temp_3 =new RegExp('^item_qty');
			if (temp_3.test(e[i].id)){
				temp_id=e[i].title;
				row_cost=cost[temp_id]*float(e[i].value);
				row_selling=sell[temp_id]*float(e[i].value);
				$('row_cost_'+temp_id).innerHTML=round(row_cost,2);
				$('row_selling_'+temp_id).innerHTML=round(row_selling,2);
				total_qty+=float(e[i].value);
				total_cost+=row_cost;
				total_sell+=row_selling;
			}
	
		}
		misc_cost=float($('misc_cost').value);
		total=total_cost+misc_cost;
		$('total_qty').innerHTML=float(round(total_qty,global_qty_decimal_points));
		$('total_cost').innerHTML=round(total_cost,2);
		$('total_sell').innerHTML=round(total_sell,2);
		document.f_a.cost_price.value=round(total,global_cost_decimal_points);
		
		$('span_item_total_selling').update(round(total_sell,2));
		//$('autocomplete_sku').select();
		
		// bom type
		if(this.f){
			var t = this.get_bom_type();
		
			if(t == 'package'){
				this.f['selling_price'].value = round(total_sell,2);
			}
		}		
	},
	
	calc_gst: function(field_type){
		if(is_gst_active == false) return;

		//master sku
		var output_tax = this.f['output_tax'].value;
		var inclusive_tax = this.f['inclusive_tax'].value;

		gst_rate = gst_rate_list[output_tax];
		if (inclusive_tax=='inherit') inclusive_tax = global_inclusive_tax; // found it is inherit to GST settings

		if(output_tax == -1) gst_rate= float(mst_output_tax); // found it is inherit to master sku
		else gst_rate = float(gst_rate_list[output_tax]);

		if (inclusive_tax=='inherit') inclusive_tax=mst_inclusive_tax; // found it is inherit to master sku

		$('span_gst_rate').update(gst_rate);

		$('span_gst_indicator').update((inclusive_tax=='no')?"After":"Before");

		if (field_type=='gst_price') {
			var selling_price_gst = float(this.f['selling_price_gst'].value);

			if (inclusive_tax=='no') {
				var selling_price=(selling_price_gst*100)/(100+gst_rate);
				var gst=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst=float(selling_price_gst) * gst_rate / 100;
				var selling_price=float(selling_price_gst+gst);
			}

			this.f['selling_price'].value=round(selling_price,2);
		}
		else{
			var selling_price = float(this.f['selling_price'].value);

			if (inclusive_tax=='yes') {
				var selling_price_gst=(selling_price*100)/(100+gst_rate);
				var gst=float(selling_price_gst) * gst_rate / 100;
			}
			else{
				var gst=float(selling_price) * gst_rate / 100;
				var selling_price_gst=float(selling_price+gst);
			}

			this.f['selling_price_gst'].value=round(selling_price_gst,2);
		}
		
		this.f['gst_rate'].value = round(gst, 2);
	  
		/*var gp_selling_price = 0;
		if(inclusive_tax == 'yes'){
			gp_selling_price = this.f["selling_price_gst"].value;
		}else{
			gp_selling_price = this.f["selling_price"].value;
		}

		// gross profit amt
		this.f["gross"].value = round(gp_selling_price - this.f["cost_price"].value, 4);

		// gross profit percent
		var grossp = 0
		if(gp_selling_price != 0){
			grossp = float(this.f["gross["+id+"]"].value/gp_selling_price)*100;
		}

		this.f["grossp["+id+"]"].value = round(grossp,4);

		*/
	}
};

function check_all_branch(obj){
	$$('#branches_id .branches_id').each(function (ele, index){
		ele.checked=obj.checked;
	});
}

function toggle_active(obj){
	var el = document.getElementsByClassName('reason_box');
	var display = '';

	if (obj.checked) display = 'none';
	for(i=0;i<el.length;i++) el[i].style.display = display;
}

function cat_disc_inherit_changed(item_id){
	var sel;
	var div_disc_container;
	
	sel = document.f_a['cat_disc_inherit['+item_id+']'];
	div_disc_container = $('div_category_discount_container-member-'+item_id);
	
	
	if(sel.value == 'set'){
		$(div_disc_container).show();
	}else{
		$(div_disc_container).hide();
	}
}

function cat_disc_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v>100)	inp.value = '100.00';
		else if(v<=0){
			inp.value = 0;
		}
	}
}

function category_discount_branch_override_changed(item_id, bid){
	var c = $('inp_category_disc_override-'+item_id+"-"+bid).checked;
	$('bom_details').getElementsBySelector("input.inp_category_disc-"+item_id+"-"+bid).each(function(inp){
		inp.disabled = !c
	});
}

////////// point ////////////

function category_point_inherit_changed(item_id){
	var sel = document.f_a['category_point_inherit['+item_id+']'];
	var div_container = $('div_category_point_container-'+item_id);
	
	if(sel.value == 'set'){
		$(div_container).show();	
	}else{
		$(div_container).hide();
	}
}

function category_point_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v<=0){
			inp.value = 0;
		}
	}
}

function category_point_branch_override_changed(item_id, bid){
	var c = $('inp_category_point_override-'+item_id+"-"+bid).checked;
	$('bom_details').getElementsBySelector("input.inp_category_point-"+item_id+"-"+bid).each(function(inp){
		inp.disabled = !c
	});
}

function toggle_prq_by_branch(obj){
	if(obj.checked == true){
		$('prq_by_branch').show();
	}else{
		$('prq_by_branch').hide();
	}
}

function toggle_allow_selling_foc(){
	var inp = document.f_a['allow_selling_foc'];	// get the element
	
	if(!inp)	return false;	// element not found
	
	if(inp.checked){	// allow foc
		$('span_selling_foc').show();
		document.f_a['selling_foc'].disabled = false;
		//check_selling_foc();
		check_selling_price_settings(inp);
	}else{	// not allow foc
		$('span_selling_foc').hide();
		//document.f_a['selling_foc'].disabled = true;
		//if(bom_type == "normal") document.f_a['selling_price'].readOnly = false;
	}
}

function check_selling_foc(){
	var inp_selling_foc = document.f_a['selling_foc']; // FOC checkbox
	var inp_selling_price = document.f_a['selling_price'];	// selling price box
	
	var is_foc = inp_selling_foc.checked && (!inp_selling_foc.disabled);	// tick FOC and checkbox not disable
	
	/*if(bom_type == "package") inp_selling_price.readOnly = true;
	else inp_selling_price.readOnly = is_foc;

	if(is_foc){	// set selling price as FOC
		inp_selling_price.old_price = inp_selling_price.value;
		inp_selling_price.value = '0.00';
	}else{	// set no use FOC
		if(inp_selling_price.old_price){	// set back to use selling price before tick use FOC (if got)
			inp_selling_price.value = inp_selling_price.old_price;
		}
	}
	inp_selling_price.onchange();	// call onchange function*/
}


function toggle_open_price(){
	var inp = document.f_a["open_price"];
	if(inp.checked){
		// check all other sp settings
		check_selling_price_settings(inp);
	}
}

function check_selling_price_settings(chx_selected){
	$$('#ul_selling_price_settings input.chx_sp_settings').each(function(inp){
		if(inp != chx_selected){
			inp.checked = false;
			if(inp.onchange)	inp.onchange();
		}
	});
}
</script>
{/literal}

<div id=div_price_list style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid #000;margin: 0 0 0 14;height:100px;width:74px;overflow:auto;">
{include file=bom.refresh.price_list.tpl}
</div>

<h1>{$PAGE_TITLE}</h1>

{if !$errm}
<div id=show_last style="font-weight:bold;">
{if $smarty.request.t eq 'completed'}
<img src=/ui/approved.png align=absmiddle> SKU ID#{$smarty.request.sku_id} has been generated.<br>
{elseif $smarty.request.t eq 'bom_completed'}
<img src=/ui/approved.png align=absmiddle> SKU Items ID#{$smarty.request.bom_id} has been saved.<br>
{elseif $smarty.request.t eq 'explode'}
<img src=/ui/approved.png align=absmiddle> SKU Items ID#{$smarty.request.bom_id} explode to {$smarty.request.q} unit(s). <br>
{elseif $smarty.request.t eq 'implode'}
<img src=/ui/approved.png align=absmiddle> SKU Items ID#{$smarty.request.bom_id} implode to {$smarty.request.q} unit(s). <br>
{/if} 
<br>
</div>
{/if}

<form name=f_a method=post>
<input type=hidden name=a>
<input type=hidden name=brand_id value="{$form.brand_id}">
<input type=hidden name=category_id value="{$form.category_id}">

<div class="stdframe" style="background:#fff">
<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>SKU</b></td>
<td>
	<select name="sku_bom" onchange="load_sku_bom(this);">
	{section name=i loop=$sku_bom}
	<option value={$sku_bom[i].id} {if $form.sku_bom eq $sku_bom[i].id}selected{/if}>{$sku_bom[i].id} ({$sku_bom[i].category},{$sku_bom[i].brand})</option>
	{/section}
	</select>
</td>
</tr>

<tr>
<td><b>ARMS Code</b></td>
<td id=sku_bom_item>
{include file=bom.refresh.sku_bom_items.tpl}
</td>
</tr>
</table>
</div>

<br>

<div id="bom_details" {if !$smarty.request.a}style="display:none;"{/if}>
{include file=bom.refresh.bom_details.tpl}
</div>

</form>

<script type="text/javascript">
	BOM_EDITOR.initialize();
</script>
{include file=footer.tpl}
