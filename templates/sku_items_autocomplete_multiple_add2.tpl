
{*
4/20/2010 10:16:11 AM Andy
- add skip department control on sku items search

10/22/2010 11:27:32 AM Justin
- Changed all the JS to accept inifinity call for this template within one module.

5/18/2011 3:50:22 PM Andy
- Add can pass document FORM name.

6/22/2011 11:18:15 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

7/5/2011 2:12:32 PM Andy
- Fix got javascript error for multiple add if got pass 'is_dbl_sku'

11/28/2013 2:41 PM Justin
- Enhanced to have add item by SKU group feature.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

05/07/2019 3:12 PM Liew
- Add by Group not working

12/2/2019 4:17 PM Andy
- Fixed syntax error.

06/24/2020 04:12 PM Sheila
- Updated button css
*}

<script>
var skip_dept_filter = '{$skip_dept_filter}';
var is_dbl_sku = '{$is_dbl_sku}';
var parent_form = eval("{$parent_form|default:'document.f_a'}"); 

add_sku_to_list{$is_dbl_sku}={literal}function(code,lbl){
	if(code!=''){
	    var new_option = document.createElement('option');
	    new_option.value = code;
	    new_option.text = lbl; //$('autocomplete_sku').value;

		var obj = $('sku_code_list'+is_dbl_sku);
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
			$('remove_sku'+is_dbl_sku).disabled=false;
			$('clear_sku'+is_dbl_sku).disabled=false;
		}
	}else{
		alert('Invalid input');
	}
}
{/literal}

remove_sku_from_list{$is_dbl_sku}={literal} function(){
    if($('sku_code_list'+is_dbl_sku).selectedIndex<0){
		alert('Please select a sku item from the list');
	}

	while($('sku_code_list'+is_dbl_sku).selectedIndex>=0){
        var selectedIndex = $('sku_code_list'+is_dbl_sku).selectedIndex;
		$('sku_code_list'+is_dbl_sku).remove(selectedIndex);
		if($('sku_code_list'+is_dbl_sku).length<=0){
		    $('remove_sku'+is_dbl_sku).disabled=true;
		    $('clear_sku'+is_dbl_sku).disabled=true;
		}
	}
}
{/literal}
clear_sku_from_list{$is_dbl_sku}={literal}function(){
    while($('sku_code_list'+is_dbl_sku).length>0){
		$('sku_code_list'+is_dbl_sku).remove(0);
	}
	$('remove_sku'+is_dbl_sku).disabled=true;
	$('clear_sku'+is_dbl_sku).disabled=true;
}
{/literal}
var _timeout_autocomplete_ = false;

reset_sku_autocomplete{$is_dbl_sku}={literal}function() {
	$('autocomplete_sku'+is_dbl_sku).onkeyup = function(k){
		if(k.keyCode==27) //escape
		{
			{/literal}clear_autocomplete{$is_dbl_sku}{literal}();
			return;
		}
		
		if (_timeout_autocomplete_!=false) clearTimeout(_timeout_autocomplete_);
		_timeout_autocomplete_ = false;
		
		val = this.value.trim();
		if (val<=0) return;
		_timeout_autocomplete_ = setTimeout('do_autocomplete'+is_dbl_sku+'()',500);
	};
	{/literal}clear_autocomplete{$is_dbl_sku}{literal}();
}
{/literal}

do_autocomplete{$is_dbl_sku}={literal}function(k) {
	var search_type = parent_form['search_type'+is_dbl_sku];

	var p = $H({
		a: 'ajax_search_sku',
		multiple: 1,
		type: getRadioValue(search_type),
		value: $('autocomplete_sku'+is_dbl_sku).value,
		skip_dept_filter: skip_dept_filter
	});
	
	$('autocomplete_sku_choices'+is_dbl_sku).innerHTML = _loading_;
	$('autocomplete_sku_choices'+is_dbl_sku).style.display='';
	ajax_request('ajax_autocomplete.php?'+p.toQueryString(), {
		onComplete: function(e){
			$('autocomplete_sku_choices'+is_dbl_sku).scrollTop = 0;
			$('autocomplete_sku_choices'+is_dbl_sku).innerHTML = e.responseText;
		}
	});
	_timeout_autocomplete_ = false;
}
{/literal}

add_autocomplete{$is_dbl_sku}={literal}function() {
	$('autocomplete_sku_choices'+is_dbl_sku).style.display='none';
	var opts = $('autocomplete_sku_choices'+is_dbl_sku).getElementsByTagName('input');
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked) {/literal}add_sku_to_list{$is_dbl_sku}{literal}(c[1],opts[i].title);
	}
	{/literal}clear_autocomplete{$is_dbl_sku}{literal}();
}
{/literal}

clear_autocomplete{$is_dbl_sku}={literal}function(){
	$('sku_item_id'+is_dbl_sku).value = '';
	$('sku_item_code'+is_dbl_sku).value = '';
	$('autocomplete_sku'+is_dbl_sku).value = '';
	$('autocomplete_sku_choices'+is_dbl_sku).innerHTML = 'Loading...';
	$('autocomplete_sku_choices'+is_dbl_sku).style.display='none';
	$('autocomplete_sku'+is_dbl_sku).focus();
}
{/literal}

load_SKU_Group{$is_dbl_sku}={literal}function(){
	if($('div_sku_group'+is_dbl_sku).style.display == 'none'){
	    $('div_sku_group'+is_dbl_sku).show();
	    
		if($('sku_group_load_count'+is_dbl_sku).value > 0){
            return;
		}else{
            $('div_sku_group'+is_dbl_sku).update('Loading...');
		}
		
		var p = $H({
			a: 'ajax_load_sku_group_list',
			is_dbl_sku: is_dbl_sku 
		});
		
	    ajax_request('ajax_autocomplete.php?'+p.toQueryString(),
		{
			onComplete: function(e) {
				if(e.responseText.indexOf('Error:') >= 0){
					alert(e.responseText);
					return;
				}
				$('div_sku_group'+is_dbl_sku).update(e.responseText);
				$('sku_group_load_count'+is_dbl_sku).value++;
				$('div_sku_group'+is_dbl_sku).show();
			}
		});
	}else{
        $('div_sku_group'+is_dbl_sku).hide();
	}
}
{/literal}

add_sku_item{$is_dbl_sku}={literal}function(sku_group_id,branch_id,user_id){
    $('div_sku_group'+is_dbl_sku).hide();
    
    var p = $H({
		a: 'ajax_add_sku_item_into_list',
		sku_group_id: sku_group_id,
		branch_id: branch_id,
		user_id: user_id,
		is_dbl_sku: is_dbl_sku
	});

    ajax_request('ajax_autocomplete.php?'+p.toQueryString(),
	{
		onComplete: function(e) {
			if(e.responseText.indexOf('Error:') >= 0){
				alert(e.responseText);
				return;
			}
			
			$('div_for_output'+is_dbl_sku).update(e.responseText);
		}
	});
}
{/literal}
</script>


<div id='history_popup{$is_dbl_sku}' style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup{$is_dbl_sku}')"></div>
<div id='history_popup_content{$is_dbl_sku}'></div>
</div>
<table>
<tr>
	<td width="100"><b>Search SKU</b></td>
	<td>
		<input id="sku_item_id{$is_dbl_sku}" name="sku_item_id{$is_dbl_sku}" size=3 type=hidden>
		<input id="sku_item_code{$is_dbl_sku}" name="sku_item_code{$is_dbl_sku}" size=13 type=hidden>
		<input id="autocomplete_sku{$is_dbl_sku}" name="sku{$is_dbl_sku}" size=50 onclick="this.select()" style="font-size:14px;width:500px;"> <input class="btn btn-primary" type=button value="Add" onclick='add_autocomplete{$is_dbl_sku}()'>
		<div id="autocomplete_sku_choices{$is_dbl_sku}" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><!--<input type=submit value="Find">--></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<input onchange="reset_sku_autocomplete{$is_dbl_sku}()" type=radio id="search_type{$is_dbl_sku}" name="search_type{$is_dbl_sku}" value="1" checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete{$is_dbl_sku}()" type=radio id="search_type{$is_dbl_sku}" name="search_type{$is_dbl_sku}" value="2" {if $config.consignment_modules}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete{$is_dbl_sku}()" type=radio id="search_type{$is_dbl_sku}" name="search_type{$is_dbl_sku}" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete{$is_dbl_sku}()" type=radio id="search_type{$is_dbl_sku}" name="search_type{$is_dbl_sku}" value="4"> Description
	</td>
</tr>
</table>

<br>
<table>
<tr>
<td rowspan=5 style="padding-left:75px">
<select multiple name="sku_code_list{$is_dbl_sku}[]" id="sku_code_list{$is_dbl_sku}" style="width:300px;height:100px;">
{if $group_item}
	{foreach from=$group_item item=r}
	    	<option value={$r.sku_item_code}>{$r.description}</option>-->
	{/foreach}
{/if}
</td>
<td></td></tr>
<tr>
	<td width="200"><!--<input type=button value="Add" onClick="add_sku_to_list()" style="width:80px;">-->
		<div style="position:absolute;width:300px;height:100px;margin-left:100px;display:none;overflow-x:hidden;overflow-y:auto;" class="autocomplete" id="div_sku_group{$is_dbl_sku}">
		</div>
		<input type="hidden" name="sku_group_load_count{$is_dbl_sku}" value="0" id="sku_group_load_count{$is_dbl_sku}">
		<input class="btn btn-primary" type="button" value="Add by Group" style="width:100px;" onClick="load_SKU_Group{$is_dbl_sku}()" />
	</td>
</tr>
<tr>
	<td><input class="btn btn-error" type=button value="Remove" id="remove_sku{$is_dbl_sku}" onClick="remove_sku_from_list{$is_dbl_sku}()" disabled style="width:80px;"></td>
</tr>
<tr>
	<td><input class="btn btn-primary" type=button value="Clear" id="clear_sku{$is_dbl_sku}" onClick="clear_sku_from_list{$is_dbl_sku}()" disabled style="width:80px;"></td>
</tr>
<tr><td>&nbsp;</td></tr>
</select>
</table>
<div id="div_for_output{$is_dbl_sku}" ></div>

{if $group_item}
<script>
	$('remove_sku'+is_dbl_sku).disabled=false;
	$('clear_sku'+is_dbl_sku).disabled=false;
</script>
{/if}
<script>reset_sku_autocomplete{$is_dbl_sku}(); </script>
