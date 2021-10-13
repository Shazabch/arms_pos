{*

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

12/17/2009 6:38:28 PM Andy
- Fix SKU Autocomplte Multiple add undefined variable

4/2/2010 5:42:23 PM Andy
- Add able to insert responseText into class with "multiple_add_container"

8/11/2010 12:34:24 PM Justin
- Add different calling ajax function when found it is request membership redemption.

9/17/2010 6:02:56 PM Justin
- Added a new feature onto SKU item Ajax call to check for existed SKU items from the list.
- Added decision window to allow user add/cancel the existed SKU item.
  -> The multiple added SKU items will still proceed even user refused to add a existed SKU item into the list.
  -> Only those not existed or existed SKU items that agreed to add will be insert into the list.

12/16/2010 11:28:04 AM Justin
- Added the list of sku codes to pass into php.

12/16/2010 3:07:04 PM Alex
- add sku_type filter for promotion 
--->ONLY consignment items can be added into promotion if use consignment bearing table
--->ONLY outright items can be added if no use consignment bearing table

6/22/2011 11:13:54 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

8/3/2012 11:48 AM Andy
- Add can check autocomplete object before submit to add.

8/14/2012 12:04 PM Justin
- Enhanced to use "do_ajax_add" as if found it.

9/20/2012 1:21 PM Andy
- Add can accpet parameter "block_is_bom" for sku autocomplete multiple add.

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

2/4/2015 2:13 PM Andy
- Enhance not to fire the add item event if no item is selected.

9/17/2015 5:24 PM Andy
- Enhance to have variable $default_mcode to auto check on search mcode.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

12/27/2017 5:42 PM Andy
- Enhanced ajax search sku can filter sku with weight_kg.
*}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var replace_table = {$replace_table|default:0};
var check_item_list = '{$check_item_list}';
var block_is_bom = '{$block_is_bom}';
var need_weight_kg = int('{$need_weight_kg}');
{literal}
if(document.f_a){
	if(document.f_a.r_type){
		var r_type=document.f_a.r_type.value;
	}
	if(document.f_a.nric){
		var nric = document.f_a.nric.value;
	}
}
var sku_array_list = new Array();

function add_sku_to_list(code,lbl){
	//var id = $('sku_item_id').value;
	//var code = $('sku_item_code').value;
//	alert(id);
	//alert(code);
	if(code!=''){
	    var new_option = document.createElement('option');
	    new_option.value = code;
	    new_option.text = lbl; //$('autocomplete_sku').value;

		var obj = $('sku_code_list');
		var found =false;

		for(var i=0; i<obj.length; i++){
			if(obj.options[i].value!=null){
				if(obj.options[i].value==code){
					alert(lbl+' already in the list.');
					found=true;
					break;
				}
			}
		}

		if(!found){
		    try {
			    obj.add(new_option, null); // standards compliant; doesn't work in IE
			}
			catch(ex) {
			    obj.add(new_option); // IE only
			}
			//$('sku_code_list_2').value = $('sku_code_list_2').value+'|'+code;
			sku_array_list[sku_array_list.length] = code;
			$('remove_sku').disabled=false;
			$('clear_sku').disabled=false;
		}
	}else{
		alert('Invalid input');
	}
	/*
	var input = $('autocomplete_sku_choices').getElementsByTagName('input');
	
	for(i=0;i<input.length;i++)
	{
		if(input[i].checked)
		alert(input[i].value);
	}*/
}

function remove_sku_from_list(){
    if($('sku_code_list').selectedIndex<0){
		alert('Please select a sku item from the list');
	}

	while($('sku_code_list').selectedIndex>=0){
        var selectedIndex = $('sku_code_list').selectedIndex;
		$('sku_code_list').remove(selectedIndex);
		sku_array_list.splice(selectedIndex, 1);
		if($('sku_code_list').length<=0){
		    $('remove_sku').disabled=true;
		    $('clear_sku').disabled=true;
		}
	}
}

function clear_sku_from_list(){
    while($('sku_code_list').length>0){
		$('sku_code_list').remove(0);
	}
	$('remove_sku').disabled=true;
	$('clear_sku').disabled=true;
	sku_array_list = new Array();
}

function passArrayToInput(){
    $('sku_code_list_2').value = sku_array_list;
}
function init_sku_array_list(code){
    sku_array_list[sku_array_list.length] = code;
    $('remove_sku').disabled=false;
	$('clear_sku').disabled=false;
}


var _timeout_autocomplete_ = false;

function reset_sku_autocomplete()
{
	$('autocomplete_sku').onkeyup = function(k){
		if(k.keyCode==27) //escape
		{
			clear_autocomplete();
			return;
		}

		if (_timeout_autocomplete_!=false) clearTimeout(_timeout_autocomplete_);
		_timeout_autocomplete_ = false;
		
		val = this.value.trim();
		if (val<=0) return;
		_timeout_autocomplete_ = setTimeout('do_autocomplete()',500);
	};
	clear_autocomplete();
}

function do_autocomplete(k) {
	var cb = document.f_a;
	var others_param = '';
	var item_list = '';
	var ajax_search = 'ajax_search_sku';
	if(document.f_a){

		if(document.f_a.s_consignment_bearing){
			//consignment bearing module
			if (document.f_a.s_consignment_bearing.checked){
				var filter_bearing=''
				if  (r_type == 'vendor')  	filter_bearing="&vendor_id="+document.f_a.vendor_id.value;
				else if (r_type == 'brand') filter_bearing="&brand_id="+document.f_a.brd_id.value;
		
			    others_param= "bearing=1&sku_type=CONSIGN&dept_id="+document.f_a.dept_id.value+filter_bearing;
			}else{
                //others_param= "sku_type=OUTRIGHT"; due to unable to find CONSIGN item under promotion module
			}
		}
		
		if(nric){
			//membership redemption modules
			ajax_search = 'ajax_search_redemption';
			others_param="&nric="+nric;
			var all_item_id = $$('#tbody_item_list input.item_id');

			for(var i=0; i<all_item_id.length; i++){
				if (all_item_id[i].value){
					item_list += all_item_id[i].value+",";
				}
			}

			item_list =  item_list.slice(0,-1);
			others_param+= "&item_list="+item_list;
		}
	}
	
	if(block_is_bom){
		others_param += '&block_is_bom=1';
	}
	if(need_weight_kg){
		others_param += '&need_weight_kg=1';
	}

	var p = $H({
		a: ajax_search,
		multiple: 1,
		type: getRadioValue(document.f_a.search_type),
		value: $('autocomplete_sku').value
	});

	$('autocomplete_sku_choices').innerHTML = _loading_;
	$('autocomplete_sku_choices').style.display='';
	ajax_request('ajax_autocomplete.php?'+p.toQueryString(), {
		method: 'post',
	    parameters: others_param,
		onComplete: function(e){
			$('autocomplete_sku_choices').scrollTop = 0;
			$('autocomplete_sku_choices').innerHTML = e.responseText;
		}
	});
	_timeout_autocomplete_ = false;
}

function add_autocomplete()
{
	var disc = new Array();
	var d = '';
	var is_existed = false;
	var sku_code_title = '';
	var grp_sku_code = '';
	
	$('autocomplete_sku_choices').style.display='none';
	
	var opts = $('autocomplete_sku_choices').getElementsByTagName('input');
	if(window.autocomplete_multiadd_validation){
		if(!autocomplete_multiadd_validation(opts))	return;
	}
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked){
			add_sku_to_list(c[0],opts[i].title);
			if(document.f_a){
				if(nric) grp_sku_code += c[1]+",";
			}
		}
	}

	sku_list = document.getElementById("sku_code_list");
	
	for (var i=0;i<sku_list.options.length;i++)
	{
		// in order to use this feature, please add a hidden field with follow:
		// a) make sure send a variable $check_item_list when u include this file and tbody id=tbody_item_list
		// b) name of "item_sku_item_id[" and a field title 
		// c) title of "sku item code" the following hidden field
		if(check_item_list){
			var all_sku_item_id = $('tbody_item_list').getElementsByTagName('input');

			$A(all_sku_item_id).each(
				function (r,idx){
					if (r.name.indexOf("item_sku_item_id[")==0){
						if(r.value == sku_list.options[i].value){
							sku_code_title = r.title;
							is_existed = true;
						}
					}
				}
			);
			
			if(is_existed){
				if(!confirm("SKU Item Code "+sku_code_title+" is existed, add to list?")){
					sku_list.options[i].selected=false;
				}else{
					sku_list.options[i].selected=true;
				}
				is_existed = false;
			}else{
				sku_list.options[i].selected=true;
			}
		}else{
			sku_list.options[i].selected=true;
		}
	}
	
//	alert(Form.serialize(document.f_a).replace(/\&/g,"\n"));

	clear_autocomplete();
	
	if (window.add_autocomplete_callback) disc = add_autocomplete_callback();
	if (disc) d = '&d='+escape(disc.join());
	if(grp_sku_code){
		grp_sku_code = grp_sku_code.slice(0, -1);
		d += '&grp_sku_code='+grp_sku_code;
	}
	
	if($('sku_code_list').selectedIndex < 0)	return;	// nothing is selected
	
   	parms = Form.serialize(document.f_a) + '&a=ajax_add_item_row'+d;	

	if(window.do_ajax_add){
		do_ajax_add(parms);
		return;
	}

 	// insert new row
	ajax_request(phpself,{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
            var tb = $('docs_items');
            var lbody;
			var xml = m.responseXML;
				
			if (!xml) { 
				if (replace_table) {
					if (/<table/.test(m.responseText))
						$('tb_div').innerHTML = m.responseText;
					else
						alert(m.responseText);
					return;
				}
				if (!/^(\s+)*<t/.test(m.responseText) && m.responseText != '') alert(m.responseText);
				else if (/<html>/.test(m.responseText)) alert("Item is currently available, please try again later");
				else{
				    if($('tbl_footer')){
                        new Insertion.Before($('tbl_footer'),m.responseText);
					}else{
                        
                        new Insertion.Bottom($$('.multiple_add_container').first(),m.responseText);
					}
				}
			}
			else
			{
				var records = xml.getElementsByTagName("record");
				$A(records).each(
					function(r,idx){
						var rowitem = tb.insertRow(-1);
						rowitem.id = "titem"+xml_getData(r, "id").strip();
						rowitem.innerHTML = xml_getData(r,'rowdata');
					}
				);				
			}
			if (window.add_autocomplete_extra) disc = add_autocomplete_extra();
		},		
		onComplete: function(){
			sku_list.length = 0;
		}
	});	
}

function clear_autocomplete(){
	$('sku_item_id').value = '';
	$('sku_item_code').value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku_choices').innerHTML = 'Loading...';
	$('autocomplete_sku_choices').style.display='none';
	$('autocomplete_sku').focus();
}

{/literal}
</script>

<div id="history_popup" style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>

<table class="mx-3">
<tr>
	<label><b>Search SKU</b></label>
	<td>
		<input class="form-control" id="sku_item_id" name="sku_item_id" size=3 type=hidden>
		<input class="form-control" id="sku_item_code" name="sku_item_code" size=13 type=hidden>
		<div class="row">
			<div class="col">
				<div class="form-inline">
					<input class="form-control" id="autocomplete_sku"  name="sku" onclick="this.select()"> 
					&nbsp;<input type="button" class="btn btn-primary fs-08" value="Add" onclick='add_autocomplete()'>
				</div>
			</div>
		</div>
		{if $include_all_sku_item}&nbsp;&nbsp;
		<label><input type="checkbox" name="include_all_sku_item" />Include all parent / child SKU</label>{/if}
		{if $add_parent_child}&nbsp;&nbsp;<label>
			<input class="btn btn-light mt-3" type="button" value="Add Parent & Child" onclick='add_parent_child()'></label>{/if}
		<div id="autocomplete_sku_choices" class="autocomplete bd-x bd-y " style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><!--<input type=submit value="Find">--></td>
</tr>
<br><br />
<tr class="mt-2 fs-09">
	
	<td>
		<br />
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" {if $is_promo || $default_mcode}checked{/if}> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
	</td>
</tr>
</table>

<br>
<table style="display:none">
<tr>
<td rowspan=5 style="padding-left:75px">
<select multiple name="sku_code_list[]" id="sku_code_list" style="width:300px;height:100px;">
{if $category}
	{foreach from=$category item=c}
	    {if $c.sku_item_code ne ''}
	    	<option value={$c.sku_item_code}>{$c.description}</option>
	    	{*<script>init_sku_array_list('{$c.sku_item_code}')</script>*}
	    {/if}
	{/foreach}
{/if}
</td>
<td></td></tr>
<tr>
	<td><!--<input type=button value="Add" onClick="add_sku_to_list()" style="width:80px;">-->&nbsp;</td>
</tr>
<tr>
	<td><input type=button value="Remove" id="remove_sku" onClick="remove_sku_from_list()" disabled style="width:80px;"></td>
</tr>
<tr>
	<td><input type=button value="Clear" id="clear_sku" onClick="clear_sku_from_list()" disabled style="width:80px;"></td>
</tr>
{foreach from=$category item=c}
	{if $c.sku_item_code ne ''}
	    <script>init_sku_array_list('{$c.sku_item_code}')</script>
	{/if}
{/foreach}

<tr><td>&nbsp;</td></tr>
</select>
</table>
<input type=hidden name=sku_code_list_2 id="sku_code_list_2">
{if $allow_edit}
<script>reset_sku_autocomplete();</script>
{/if}
