{*
5/30/2018 4:13PM HockLee
- Create DO Checkout by Batch

8/27/2018 4:00PM HockLee
- Fixed form validation.

8/30/2018 4:00PM HockLee
- Fixed javascript error.

05/13/2020 6:16PM Sheila
- Updated button color
*}

{include file='header.tpl'}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var rearrng = false;
sessionStorage.setItem('rearrng', rearrng);

{/literal}

{literal}
var CHECKOUT_BY_BATCH = {
	initialize: function(){
		// initial calendar
		this.init_calendar();
	},

	init_calendar: function(){
	    // sales date from
        Calendar.setup({
			inputField     :    "inp_date_range_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// sales date to
		Calendar.setup({
			inputField     :    "inp_date_range_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_to",
			align          :    "Bl",
			singleClick    :    true
		});

	},

}

function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});   
}

function open_route(id, batch, date_from, date_to){
    curtain(true);
	center_div('div_transporter_vehicle_popup');
	$('div_transporter_vehicle_popup').show();
	$('div_transporter_vehicle_popup_content').update(_loading_);
	new Ajax.Updater('div_transporter_vehicle_popup_content',phpself,{
	    parameters:{
			a: 'open_route',
			id: id,
			batch_code: batch,
			date_from: date_from,
			date_to: date_to
		},
		evalScripts: true
	})
}

function show_do(){
	if(document.f_do_checkout_by_batch['transporter_id'].value == 0){
		alert('Please enter a Transporter');
		document.f_do_checkout_by_batch['transporter_id'].focus();
		return false;
	}

	if(document.f_do_checkout_by_batch['batch_code'].value.trim() == ''){
		alert('Please enter Batch Code');
		document.f_do_checkout_by_batch['batch_code'].focus();
		return false;
	}

	if(document.f_do_checkout_by_batch['date_from'].value.trim() == ''){
		alert('Please select DO Added Date from.');
		document.f_do_checkout_by_batch['date_from'].focus();
		return false;
	}
	if(document.f_do_checkout_by_batch['date_to'].value.trim() == ''){
		alert('Please select DO Added Date to.');
		document.f_do_checkout_by_batch['date_to'].focus();
		return false;
	}
	
	document.f_do_checkout_by_batch.submit();
}

function do_checkout_save(){
	var do_no = document.getElementsByName("do_no[]");
	var do_no_length = do_no.length;
	var count = 0;

	if(do_no_length == undefined){
		do_no_length = 1;
	}
	
	for(var i = 0; i < do_no_length; i++){
		if(do_no[i].checked == true){
			count += 1;
		}
	}

	if(count == 0){
		alert('You have not select DO.');
		return false;
	}

	if(document.f_do_checkout_transporter['do_date'].value.trim() == ''){
		alert('Please enter DO date');
		document.f_do_checkout_transporter['do_date'].focus();
		return false;
	}

	if(confirm('Are you sure?')){
		document.f_do_checkout_transporter.submit();
	}
}

var batch_code_autocomplete = undefined;

function reset_batch_code_autocomplete(){
	var param_str = "a=ajax_search_batch_code&";
	batch_code_autocomplete = new Ajax.Autocompleter("inp_batch_code", "div_autocomplete_batch_code_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_batch_code',
	afterUpdateElement: function (obj, li) {
	    s = li.title;
	    $('span_loading_batch_code').hide();
	}});
}

function go_back() {
    location.href = phpself;
}

function selectOnlyThis(id, count){
	for(var i = 1;i <= count; i++){
        document.getElementById(i).checked = false;
    }
    document.getElementById(id).checked = true;
}

function rearrange(){
	var x = document.getElementsByClassName("do_no_dis");
	var y = document.getElementsByClassName("do_no_color");
	var i;

	for(i = 0; i < x.length; i++){
		x[i].disabled = false;
		y[i].style.color = "black";
	}

	rearrng = true;
}

function checkOnlyOne(id){
	var x = document.getElementsByName("do_no[]");
	var y = document.getElementById(id);
	var do_id = id.split("_");
	var i;

	if(y.checked == true){
		if(rearrng == true){
			for(var i = 0; i < x.length; i++){
				var d_id = x[i].value.split("_");

				if(d_id[2] == do_id[1]){
					x[i].checked = false;
				}
			}

		y.checked = true;			
		}
	}
}

function resetForm(){	
	var x = document.getElementsByClassName("do_no_dis");
	var y = document.getElementsByClassName("do_no_color");
	var i;

	for(i = 0; i < x.length; i++){
		x[i].disabled = true;
		y[i].style.color = "grey";
	}

	rearrng = false;

	document.getElementById("f_do_checkout_transporter").reset();
}

</script>

<style>
.text_left { 
    text-align: left; 
}

.text_right { 
    text-align: right; 
}

.text_center { 
    text-align: center; 
}

.width {
	width: 150px;
}

 /* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 420px;
    background-color: #555;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;

    /* Position the tooltip text */
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;

    /* Fade in tooltip */
    opacity: 0;
    transition: opacity 0.3s;
}

/* Tooltip arrow */
.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
{/literal}

<h1>DO Checkout Information</h1>

<form name="f_do_checkout_by_batch" class="stdframe" method="post">
	<input type="hidden" name="a" value="show_do_for_checkout" />

	<table border="0" cellspacing="0" cellpadding="4" width="100%">
		<tr>
			<th width="15%" align="left">Transporter</th>
			<td>
				<select name="transporter_id" class="width">
					<option value="0">Please select</option>
					{foreach from=$transporter item=name key=transporter_id}
					<option value="{$transporter_id}" {if $transporter_id eq $transport_type_id}selected{/if}>{$name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th align="left">Batch Code</th>
			<td>
				<input type="text" name="batch_code" value="{$batch_code}" id="inp_batch_code" class="width" /><img src="ui/rq.gif" align="absbottom" title="Required Field">
	            <span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
			</td>
		</tr>
		<tr>
			<th align="left">DO Added Date</th>
			<td>
				<b>from </b> 
				<input name="date_from" id="inp_date_range_from" size="10" maxlength="10"  value="{$date_from|default:$smarty.now-604800|date_format:"%Y-%m-%d"}" />
	    		<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_from" style="cursor: pointer;" title="Select Date" />

				<b>to </b> 
				<input name="date_to" id="inp_date_range_to" size="10" maxlength="10"  value="{$date_to|default:$smarty.now|date_format:"%Y-%m-%d"}" />
	   			<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_to" style="cursor: pointer;" title="Select Date" />
			</td>
		</tr>
		<tr>
			<td colspan="3">				
				<p id="p_submit_btn" align="center">
				<input type="button" class="btn btn-success" value="Submit" onclick="show_do();" />
				<input type="button" value="Back" class="btn btn-primary" onclick="go_back();" />
				</p>
			</td>
		</tr>
	</table>
</form>

{if $no_data_msg}
<h2>{$no_data_msg}</h2>
{/if}

{if !$no_data_msg}
	<h2>Vehicle List</h2>
	<form name="f_do_checkout_transporter" method="post" id="f_do_checkout_transporter">
	<input type="hidden" name="a" value="do_checkout_save">
	<input type="hidden" name="transporter_id" value="{$transport_id}" />
	<input type="hidden" name="batch_code" value="{$batch_code}" />
	<input type="hidden" name="date_from" value="{$date_from}" />
	<input type="hidden" name="date_to" value="{$date_to}" />

		<table>
			<tr>
				<th width=80 align=left>DO Date</th>
				<td width=150>
					<input name="do_date" id="added1" size=12 maxlength=10 value="{$smarty.now|date_format:"%Y-%m-%d"}">
					<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan=3 nowrap style="background-color:yellow"><img align="absmiddle" src="ui/icons/information.png" /> System have automatically help you to change DO Date to today.</td>
			</tr>			
		</table>
		<br>

		<table class="report_table" width="100%">
			<tr class="header">
				<th>No.</th>
				<th>Plate No.</th>
				<th>Max Load (kg)</th>
				<th>Status
				<div class="tooltip"><img src="ui/icons/information.png" align="absmiddle">
  					<span class="tooltiptext">Go to Master Files > Transporter v2 > Vehicle to change the status</span>
				</div>
				</th>
				<th>Route Name</th>
				<th>DO No.</th>
			</tr>
			{assign var=item_no value=0}
			{foreach from=$transport_info item=transport_route}
			{assign var=arr_count value=$transport_route|@count}
			{foreach from=$transport_route item=vehicle}
			{assign var=item_no value=$item_no+1}
			<tr class="text_center">
				<td>{$item_no}</td>
				<td>{$vehicle.plate_no}</td>
				<td>{$vehicle.max_load}</td>
				<td><b>{$vehicle.status}</b></td>
				<td><a href="javascript:void(open_route({$vehicle.route_id}, '{$batch_code}', '{$date_from}', '{$date_to}'))">{$vehicle.route_name}</a></td>
				<td class="text_left">
					{foreach from=$destination item=debtor_area key=route_id}
					{if $route_id eq $vehicle.route_id}	
					<span class="small" style="color:blue;">System recommendation</span></br>
					{/if}
					{foreach from=$debtor_area item=value}
					{if $route_id eq $vehicle.route_id}				
					<input type="checkbox" class="do_no" id="{$item_no}_{$value.id}" name="do_no[]" value="{$vehicle.vehicle_id}_{$vehicle.route_id}_{$value.id}" onclick="checkOnlyOne(this.id);" checked>
					<span>{$value.do_no}</span>
					{else}
					<input type="checkbox" class="do_no_dis" id="{$item_no}_{$value.id}" name="do_no[]" value="{$vehicle.vehicle_id}_{$vehicle.route_id}_{$value.id}" onclick="checkOnlyOne(this.id);" disabled>
					<span class="do_no_color" style="color:grey;">{$value.do_no}</span>
					{/if}
					{/foreach}
					{/foreach}
				</td>
			</tr>		
			{/foreach}
			{/foreach}
		</table>
		<br>		

		<h2>Additional Remark</h2>
		<textarea style="border:1px solid #000;width:100%;height:100px;" name="checkout_remark">{$form.checkout_remark|escape}
		</textarea>
		<br>
			
		<center>
			<input type="button" value="Checkout" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_checkout_save();" />
			{if $item_no > 1}
				<input type="button" value="Rearrange" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="rearrange();" />
			{/if}
			<input type="button" value="Reset" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="resetForm();" />
		</center>
	</form>

	<script>
		init_calendar();
	</script>
{/if}

{if $save_succeed}
<script language="javascript">alert("Save successfully.");</script>
{/if}

<div id="div_transporter_vehicle_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:600px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_transporter_vehicle_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Route Information</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_transporter_vehicle_popup_content" style="padding:2px;"></div>
</div>

{include file='footer.tpl'}

<script>
	CHECKOUT_BY_BATCH.initialize();
	reset_batch_code_autocomplete();
	{literal}
	new Draggable('div_transporter_vehicle_popup',{ handle: 'div_transporter_vehicle_popup_header'});
	{/literal}
</script>