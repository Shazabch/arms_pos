{*
8/5/2010 6:06:32 PM Andy
- SKU Group Drop "GRN cutoff date", "report usage", "department" and "allowed user list".

6/22/2011 11:19:51 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

7/19/2011 2:11:42 PM Justin
- Added department checking for SKU autocompleter and import feature.
- Added to show SKU Item Code at SKU List.
- Modified the add SKU item function to include SKU Item Code, Art No and MCode info while Adding new SKU item(s).

7/21/2011 4:52:32 PM Justin
- Fixed the import function that cannot work properly once successfully added item.
- Added the missing branch code while it is not from HQ.

11/27/2012 1:43 PM Andy
- Change sku group item list from mutliple select to table list.

1/11/2012 3:58 PM Andy
- Add a checkbox to auto select all sku item checkbox.

1/24/2013 5:58 PM Justin
- Enhanced to skip checking for vendor autocomplete.
- Enhanced to do checking for department before load SKU.

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

06/26/2020 1:10 PM Sheila
- Updated button css.

7/23/2020 11:29 AM William
- Bug fixed syntax error.
*}

{literal}
<script type="text/javascript">
function add_sku_to_list(code,lbl,id){
	if(code!=''){
	    var new_option = document.createElement('option');
	    new_option.value = code;

		if(artno != undefined && mcode != undefined){
			var artno = $('cb_ajax_sku_'+id).readAttribute('artno');
			var mcode = $('cb_ajax_sku_'+id).readAttribute('mcode');
		}

		if(!artno) artno = "<None>";
		if(!mcode) mcode = "<None>";
	    new_option.text = code+" - "+artno+" - "+mcode+" - "+lbl; //$('autocomplete_sku').value;

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
		if($('sku_code_list').length<=0){
		    $('remove_sku').disabled=true;
		    $('clear_sku').disabled=true;
		}
	}
}

function clear_sku_from_list(){
	return;
	
    while($('sku_code_list').length>0){
		$('sku_code_list').remove(0);
	}
	$('remove_sku').disabled=true;
	$('clear_sku').disabled=true;
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
		dept_id: $('sel_department_id').value
	});
	
	$('autocomplete_sku_choices').innerHTML = _loading_;
	$('autocomplete_sku_choices').style.display='';
	new Ajax.Request('ajax_autocomplete.php?'+p.toQueryString(), {
		onComplete: function(e){
			$('autocomplete_sku_choices').scrollTop = 0;
			$('autocomplete_sku_choices').innerHTML = e.responseText;
		}
	});
	_timeout_autocomplete_ = false;
}

function add_autocomplete()
{
	var code_to_add_list = [];
	
	$('autocomplete_sku_choices').style.display='none';
	var opts = $('autocomplete_sku_choices').getElementsByTagName('input');
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		
		if (opts[i].checked){
			//add_sku_to_list(c[1],opts[i].title,c[0]);
			code_to_add_list.push(c[1]);	// add sku item code into array
		}
	}
	clear_autocomplete();
	
	if(code_to_add_list){
		add_sku_item_by_code(code_to_add_list);
	}
}

function clear_autocomplete(){
	$('sku_item_id').value = '';
	$('sku_item_code').value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku_choices').innerHTML = 'Loading...';
	$('autocomplete_sku_choices').style.display='none';
	$('autocomplete_sku').focus();
}

function load_vendor_sku(){
	/*if($('autocomplete_vendor').value.trim()==''){
		alert('Please search vendor first');
		return false;
	}*/
	
	if($('sel_department_id').value == ''){
		alert('Please select Department first');
		return false;
	}
	
    $('sku_table').style.display = '';
	$('sku_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater("sku_table","masterfile_sku_group.php",{
			method: 'post',
			parameters: {
            	a: 'ajax_load_sku',
            	vendor_id: $('vendor_id').value,
            	department_id: $('sel_department_id').value,
            	branch_id: $('vbranch_id').value
			},
			evalScripts: true
	})
}

function add_vendor_sku(){
	var code_to_add_list = [];
	var inputs = $('item_list').getElementsByTagName('input');
	for(var i=0; i<inputs.length; i++){
		if(inputs[i].className=='sku_item'){
			if(inputs[i].checked){
			    var sku_item_code = inputs[i].id;
			    var description = inputs[i].name;
				//add_sku_to_list(sku_item_code,description,inputs[i].id);
                code_to_add_list.push(sku_item_code);	// add sku item code into array
			}
		}
	}
	Element.hide('sku_table');
	
	if(code_to_add_list){
		add_sku_item_by_code(code_to_add_list);
	}
}

function import_by_text(){
	var dept_id = $('sel_department_id').value;

	if($('text_import').value.trim()==''){
		alert('Nothing to import.');
		return false;
	}
	
    new Ajax.Request("masterfile_sku_group.php",{
			method: 'post',
			parameters: {
            	a: 'ajax_import_by_text',
				dept_id: dept_id,
            	text_value: $('text_import').value
			},
			evalScripts: true,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_sku_item_group_list_loading').hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['code_list']){ // success
	                	add_sku_item_by_code(ret['code_list']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
	
				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
	});
}

function dept_changed(){
	var dept_id = $('sel_department_id').value;
    $('span_dept_changed_loading').update(_loading_).show();
    new Ajax.Updater("div_allowed_user", phpself+'?a=ajax_load_dept_user_list',{
		parameters:{
			branch_id: document.f_a['branch_id'].value,
			sku_group_id: document.f_a['sku_group_id'].value,
			dept_id: dept_id
		},
  		onComplete: function(e){
            $('span_dept_changed_loading').update('').hide();
		}
	});
}

function toggle_all_allowed_user(ele){
	var c = ele.checked;
	$$('#div_allowed_user input.allowed_user').each(function(inp){
		$(inp).checked = c;
	});
}
</script>


{/literal}
<table border="0">
<tr>
	<td width="100"><b class="form-label">Code<span class="text-danger" title="Required Field"> *</span></b></td>
	<td><input class="form-control" onBlur="uc(this)" name=code size=10 maxlength=6 value="{$table.code}"> </td>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td><b class="form-label">Description<span class="text-danger" title="Required Field"> *</span></b></td>
	<td><input class="form-control" name=description size=50 value="{$table.description}"> 
		<td colspan="2">&nbsp;</td>
</tr>
</table>

<div id=history_popup style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>
<table>
<tr>
	<td width="100"><b class="form-label">Search SKU</b></td>
	<td>
		<input id="sku_item_id" name="sku_item_id" size=3 type=hidden>
		<input id="sku_item_code" name="sku_item_code" size=13 type=hidden>
	<div class="form-inline">
		<input class="form-control" id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;" autocomplete="off"> 
		&nbsp;&nbsp;<input type=button class="btn btn-primary" value="Add" onclick='add_autocomplete()'>
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
<tr>
	<td><b class="form-label">Department</b></td>
	<td colspan="3">
	    <select class="form-control" name="dept_id" id="sel_department_id" onChange="dept_changed();">
            <option value="">-- Please Select --</option>
            {foreach from=$departments item=r}
                <option value="{$r.id}" {if $table.dept_id eq $r.id}selected {/if}>{$r.description}</option>
            {/foreach}
        </select>
        <span id="span_dept_changed_loading" style="padding:2px;background:yellow;display:none;"></span>
	</td>
</tr>
<tr>
	<td><b class="form-label">Search Vendor</b></td>
	<td>
		<div class="form-inline">
			<input class="form-control" name="vendor_id" id="vendor_id" size=1 value="{$form.vendor_id}" readonly>
		&nbsp;&nbsp;<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
		&nbsp;&nbsp;<input class="btn btn-primary" type="button" value="Load SKU" onclick="load_vendor_sku()" />
		</div>
		<div id="autocomplete_vendor_choices" class="autocomplete"></div>
	</td>
</tr>
<tr>
    <td><b class="form-label">Branch</b></td>
    <td>
        {if $BRANCH_CODE eq 'HQ'}
            <select class="form-control" name="vbranch_id" id="vbranch_id">
	            {foreach from=$branches item=r}
	                <option value="{$r.id}">{$r.code}</option>
	            {/foreach}
	        </select>
		{else}
			{$BRANCH_CODE}
			<input type="hidden" name="vbranch_id" id="vbranch_id" value="{$sessioninfo.branch_id}" />
        {/if}
    </td>
</tr>
<tr>
	<td colspan="2"><b class="form-label mt-2">Text format Import (ARMS Code & MCode) Separate with "," (e.g: 9415007022510 &nbsp; ,&nbsp; 9555335609998)</b></td>
</tr>
<tr>
    <td colspan="2">
		<div class="form-inline">
			<textarea class="form-control" name="text_import" id="text_import" cols="60"></textarea>
	&nbsp;&nbsp;<input class="btn btn-primary mt-5" type="button" onClick="import_by_text();" value="Import" style="margin-bottom: 40px;" />
		</div>
	</td>
</tr>
</table>

{*
<br>
<table>
	<tr valign="top">
		<td>
		    <b>SKU List:<br />
				Header: SKU Item Code - Art No - MCode - Description
			</b>
			<table>
				<tr>
					<td>
						<select multiple name="sku_code_list[]" id="sku_code_list" style="width:500px;height:110px;">
							{if $group_item}
								{foreach from=$group_item item=r}
									<option value="{$r.sku_item_code}">
										{$r.sku_item_code} - 
										{if $r.artno}{$r.artno}{else}&#60;None&#62;{/if} -
										{if $r.mcode}{$r.mcode}{else}&#60;None&#62;{/if} -
										{if $r.description}{$r.description}{else}&#60;None&#62;{/if}
									</option>
								{/foreach}
							{/if}
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<input type=button value="Remove" class="btn btn-danger" id="remove_sku" onClick="remove_sku_from_list()" disabled style="width:80px;">
                        <input type=button value="Clear " class="btn btn-info" id="clear_sku" onClick="clear_sku_from_list()" disabled style="width:80px;">
					</td>
				</tr>
			</table>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>

{if $group_item}
<script>
	$('remove_sku').disabled=false;
	$('clear_sku').disabled=false;
</script>
{/if}

*}

<div class="table-responsive">
	<div style="height:200px;overflow:auto;border:1px inset grey;">
		<table class="report_table" width="100%">
			<div class="thead bg-gray-100">
				<tr class="header">
					<td><input type="checkbox" id="inp_toggle_all_items" onChange="toggle_all_items();" /></td>
					<th>ARMS Code</th>
					<th>Artno</th>
					<th>MCode</th>
					<th>Description</th>
					<th>Added by</th>
					<th>Timestamp</th>
				</tr>
			</div>
			
			<tbody class="fs-08" id="tbody_sku_item_code_row_list">
				{foreach from=$group_item item=item}
					{include file="masterfile_sku_group_popup.item_row.tpl"}
				{/foreach}
			</tbody>	
		</table>
	</div>
</div>
<p>
	<input type="button" class="btn btn-primary mt-2" value="Select / Un-select All" onClick="toggle_sku_item_group_select_all();" />
	<input type="button" class="btn btn-danger mt-2" value="Delete" onClick="delete_sku_item_group_clicked();" />
	<span id="span_sku_item_group_list_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Processing...</span>
</p>

<script type="text/javascript">
reset_sku_autocomplete();

{literal}
	new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; }});
{/literal}

</script>
