{*
5/30/2011 10:01:02 AM Alex
- create by me

1/30/2012 10:13:07 AM Andy
- Fix SKU Autocomplete cannot search by first access.

3/20/2012 2:17:45 PM Andy
- Add show branch description.
- Fix export excel no data bugs.
*}
{include file=header.tpl}
{if !$no_header_footer}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
#branch_id option.bg{
	font-weight: bold;
	padding-left: 10px;
}

#branch_id option.bg_items{
	padding-left: 20px;
}

</style>

<script>

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

			sku_array_list[sku_array_list.length] = code;
			$('remove_sku').disabled=false;
			$('clear_sku').disabled=false;
		}
	}else{
		alert('Invalid input');
	}
}

function load_sku_group(){
	if($('div_sku_group').style.display == 'none'){
	    $('div_sku_group').show();
	    
		if($('sku_group_load_count').value>0){
            return;
		}else{
            $('div_sku_group').update('Loading...');
		}
		
		var p = $H({
			a: 'ajax_load_sku_group_list'
		});
		
	    new Ajax.Request('ajax_autocomplete.php?'+p.toQueryString(),
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


function add_sku_item(sku_group_id,branch_id,user_id){
    $('div_sku_group').hide();
    
    var p = $H({
		a: 'ajax_add_sku_item_into_list',
		sku_group_id: sku_group_id,
		branch_id: branch_id,
		user_id: user_id
	});

    new Ajax.Request('ajax_autocomplete.php?'+p.toQueryString(),
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

function change_view_type(){
	var val=getRadioValue(document.f_a['view_by']);
	if (val == "single"){
		$('group_id').hide();
		$$('#group_id select').each(function(ele){
			$(ele).disable();
		});
		$$('#group_id input').each(function(ele){
			$(ele).disable();
		});

		$$('#single_id input').each(function(ele){
			$(ele).enable();
		});
		$('single_id').show();
	}else{
		$('single_id').hide();
		$$('#single_id input').each(function(ele){
			$(ele).disable();
		});

		$$('#group_id select').each(function(ele){
			$(ele).enable();
		});

		$$('#group_id input').each(function(ele){
			$(ele).enable();
		});
		
		if($('sku_code_list').length<=0){
		    $('remove_sku').disable();
		    $('clear_sku').disable();
		}

		$('group_id').show();
	}
}

function passArrayToInput(){
    $('sku_code_list_2').value = sku_array_list;
}

</script>

{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li style="color:red;"> {$e}</li>
{/foreach}
</ul>
{/if}

<form name="f_a" method=post class="form" onSubmit="passArrayToInput()">
	<p>
		<b>Date From</b>
		<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
	
		<b>To</b>
		<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
	</p>
	<p>
		<b>View by</b> 
		<input type="radio" onclick="change_view_type()" name="view_by" id="view_single_id" value="single" {if !$smarty.request.view_by || $smarty.request.view_by eq 'single'} checked {/if} >
		<label for="view_single_id">Single Item</label> 
		<input type="radio" onclick="change_view_type()" name="view_by" id="view_group_id" value='group' {if $smarty.request.view_by eq 'group'} checked {/if} >
		<label for="view_group_id">Group Item</label>
		<div id="single_id">
			{include file="sku_items_autocomplete.tpl" no_add_button=1 show_parent=1}
		</div>
		<div id="group_id">
			<table>
				<tr>
					<td rowspan=5 style="padding-left:75px">
						<select multiple name="sku_code_list" id="sku_code_list" style="width:300px;height:100px;">
						{if $sku_codes}
							{foreach from=$sku_codes item=c}
							    {if $c.sku_item_code ne ''}
							    	<option value={$c.sku_item_code}>{$c.description}</option>
							    	<script>sku_array_list[sku_array_list.length] = '{$c.sku_item_code}';</script>
							    {/if}
							{/foreach}
						{/if}
						</select>
					</td>
				</tr>
				<tr>
					<td width="200"><!--<input type=button value="Add" onClick="add_sku_to_list()" style="width:80px;">-->
						<div style="position:absolute;width:300px;height:100px;margin-left:100px;display:none;overflow-x:hidden;overflow-y:auto;" class="autocomplete" id="div_sku_group">
						</div>
						<input type="hidden" name="sku_group_load_count" value="0" id="sku_group_load_count">
						<input type="button" value="Add by Group" style="width:100px;" onClick="load_sku_group()" />
					</td>
				</tr>
				<tr>
					<td><input type=button value="Remove" id="remove_sku" onClick="remove_sku_from_list()" disabled style="width:100px;"></td>
				</tr>
				<tr>
					<td><input type=button value="Clear" id="clear_sku" onClick="clear_sku_from_list()" disabled style="width:100px;"></td>
				</tr>
			</table>
		</div>
	<input type=hidden name=sku_code_list_2 id="sku_code_list_2">
	<div id="div_for_output" ></div>

	</p>
	<p>
		<button name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
</form>

{/if}

<h2>{$report_title}</h2>

{if $detail}

<table class="report_table" id="report_tbl">
	<tr class=header>
		<th colspan=2>Branch</th>
		{foreach from=$sku_items item=desc}
			<th title="{$desc.description}">{$desc.artno} {$desc.size}<br>{$desc.sku_item_code}</th>		
		{/foreach}
		<th>Total</th>
	</tr>
	{foreach from=$detail key=bid item=d}
		<tr>
		    <td>{$bid} - {$d.branch_desc}</td>
		    <th>Qty</th>
    		{foreach from=$sku_items key=sid item=desc}
			    <td class="r">{$d.$sid.total_pcs}</td>
			{/foreach}
			<td class="r">{$total.branch.$bid.total_pcs}</td>
		</tr>
	{/foreach}
	<tr class=header>
		<th colspan=2 class="r">Total</th>
  		{foreach from=$sku_items key=sid item=desc}
		    <td class="r">{$total.sku_items.$sid.total_pcs}</td>
		{/foreach}
		<td class="r">{$total.total.total_pcs}</td>
	</tr>
</table>
{else}
	{if $table}- No Data -{/if}
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
	change_view_type();
	reset_sku_autocomplete();
</script>
{/literal}

{if $smarty.request.sku_item_id}
<script>
	$('sku_item_id').value = '{$smarty.request.sku_item_id}';
	$('sku_item_code').value = '{$smarty.request.sku_item_code}';
	$('autocomplete_sku').value = '{$smarty.request.sku}';
</script>
{/if}
{/if}
{include file=footer.tpl}
