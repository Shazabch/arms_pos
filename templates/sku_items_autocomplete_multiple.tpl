{*
6/22/2011 11:12:39 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

4/19/2017 2:54 PM Justin
- Enhanced to have Serial Number filter for SKU item or SKU group.

3/20/2019 2:34 PM Andy
- Disable the default input autocomplete for sku search.
*}

<script>
var check_sn = '{$check_sn|default:0}';
{literal}
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
	
	var p = $H({
		a: 'ajax_search_sku',
		multiple: 1,
		type: getRadioValue(document.f_a.search_type),
		value: $('autocomplete_sku').value,
		check_sn: check_sn
	});
	
	$('autocomplete_sku_choices').innerHTML = _loading_;
	$('autocomplete_sku_choices').style.display='';
	ajax_request('ajax_autocomplete.php?'+p.toQueryString(), {
		onComplete: function(e){
			$('autocomplete_sku_choices').scrollTop = 0;
			$('autocomplete_sku_choices').innerHTML = e.responseText;
		}
	});
	_timeout_autocomplete_ = false;
}

function add_autocomplete()
{
	$('autocomplete_sku_choices').style.display='none';
	var opts = $('autocomplete_sku_choices').getElementsByTagName('input');
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked) add_sku_to_list(c[1],opts[i].title);
	}
	clear_autocomplete();
}

function clear_autocomplete(){
	$('sku_item_id').value = '';
	$('sku_item_code').value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku_choices').innerHTML = 'Loading...';
	$('autocomplete_sku_choices').style.display='none';
	$('autocomplete_sku').focus();
}

function load_SKU_Group(){
	if($('div_sku_group').style.display == 'none'){
	    $('div_sku_group').show();
	    
		if($('sku_group_load_count').value>0){
            return;
		}else{
            $('div_sku_group').update('Loading...');
		}
		
		var p = $H({
			a: 'ajax_load_sku_group_list',
			check_sn: check_sn
		});
		
	    ajax_request('ajax_autocomplete.php?'+p.toQueryString(),
		{
			onComplete: function(e) {
				if(e.responseText.indexOf('Error:') >= 0){
					alert(e.responseText);
					return;
				}
				$('div_sku_group').update(e.responseText);
				$('sku_group_load_count').value++;
				$('div_sku_group').show();
			}
		});
	}else{
        $('div_sku_group').hide();
	}
}

function add_sku_item(sku_group_id,branch_id,user_id){
    $('div_sku_group').hide();
    
    var p = $H({
		a: 'ajax_add_sku_item_into_list',
		sku_group_id: sku_group_id,
		branch_id: branch_id,
		user_id: user_id,
		check_sn: check_sn
	});

    ajax_request('ajax_autocomplete.php?'+p.toQueryString(),
	{
		onComplete: function(e) {
			if(e.responseText.indexOf('Error:') >= 0){
				alert(e.responseText);
				return;
			}
			
			$('div_for_output').update(e.responseText);
		}
	});
}
{/literal}
</script>

<div id=history_popup style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>
<table>
<tr>
	<th class="form-label">Search SKU&nbsp;</th>
	<td>
		<input id="sku_item_id" name="sku_item_id" size=3 type=hidden>
		<input id="sku_item_code" name="sku_item_code" size=13 type=hidden>
		<div class="form-inline">
			<input class="form-control" id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;" autocomplete="off" /> 
		&nbsp;<input class="btn btn-primary" type=button value="Add" onclick='add_autocomplete()'>
		</div>
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><!--<input type=submit value="Find">--></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
	</td>
</tr>
</table>

<br>
<table>
<tr>
<td rowspan=5 style="padding-left:75px">
<select class="form-control" multiple name="sku_code_list" id="sku_code_list" style="width:300px;height:127px;">
{if $category}
	{foreach from=$category item=c}
	    {if $c.sku_item_code ne ''}
	    	<option value={$c.sku_item_code}>{$c.description}</option>
	    	{*<script>init_sku_array_list('{$c.sku_item_code}')</script>*}
	    {/if}
	{/foreach}
{/if}
</select>
</td>
<td></td></tr>
<tr>
	<td width="200"><!--<input type=button value="Add" onClick="add_sku_to_list()" style="width:80px;">-->
		<div style="position:absolute;width:300px;height:130px;margin-left:100px;display:none;overflow-x:hidden;overflow-y:auto;" class="autocomplete" id="div_sku_group">
		</div>
		<input type="hidden" name="sku_group_load_count" value="0" id="sku_group_load_count">
		<input type="button" class="btn btn-primary mt-3 ml-2" value="Add by Group" style="width:120px;"  onClick="load_SKU_Group()" />
	</td>
</tr>
<tr>
	<td><input type=button class="btn btn-danger ml-2 " value="Remove" id="remove_sku" onClick="remove_sku_from_list()" disabled style="width:120px;"></td>
</tr>
<tr>
	<td><input type=button class="btn btn-info ml-2" value="Clear" id="clear_sku" onClick="clear_sku_from_list()" disabled style="width:120px;"></td>
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
<div id="div_for_output" ></div>
