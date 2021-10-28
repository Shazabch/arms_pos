{*
06/30/2020 10:28 AM Sheila
- Updated button css.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}

#btn_change_batch{
	margin-top: 10px;
	margin-left: 5px;
}

#btn_change_batch:disabled{
	background-color:grey;
}

.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	$('div_date').update(_loading_);
	new Ajax.Updater('div_date', phpself, {
		parameters:{
			'a': 'ajax_load_date',
			'branch_id': bid
		}
	});
	document.f_a['loc'].length = 0;
	document.f_a['shelf'].length = 0;
	toggle_change_branch_button(false);
}

function load_location(date)
{
	var branch_id = document.f_a['branch_id'].value;
	
	$('div_location').update(_loading_);
	new Ajax.Updater('div_location',phpself,{
		parameters:{
			a: 'load_location',
			d: date,
			'branch_id': branch_id
		},onComplete: function(msg){
				
		  }
	});
	document.f_a['shelf'].length = 0;
	toggle_change_branch_button(true);
}

function load_shelf(location)
{
  var branch_id = document.f_a['branch_id'].value;
  var dats =  document.f_a.dat.value;
  $('div_shelf').update(_loading_);
	new Ajax.Updater('div_shelf',phpself,{
		parameters:{
			a: 'load_shelf',
			dat: dats ,
			loc: location,
			'branch_id': branch_id
		}
	});
	//toggle_change_branch_button(false);
}

function toggle_change_branch_button(on){
	if(on){
		$('btn_change_batch').disabled = false;
	}else{
		$('btn_change_batch').disabled = true;
	}
}

function show_record(){
	toggle_change_branch_button(true);
}

function popup_change_batch(){
	if(!document.f_a['dat'].value.trim())
  	{
		alert('Please Select Date.');
		return false;
	}
	
	/*if(!document.f_a['loc'].value.trim())
		{
      alert('Please Select Location.');
			return false;
    }
    else if(!document.f_a['shelf'].value.trim())
		{
      alert('Please Select Shelf.');
			return false;
    }*/
    
	curtain(true);
	center_div($('div_change_batch').show());
	
	var params = {
		a: 'ajax_load_change_batch_popup',
		branch_id: document.f_a['branch_id'].value,
		date: document.f_a['dat'].value,
		loc: document.f_a['loc'].value,
		shelf: document.f_a['shelf'].value
	};
	
	$('div_change_batch_content').update(_loading_);
	new Ajax.Updater("div_change_batch_content", phpself, {
		parameters: params,
		evalScripts: true,
		onComplete: function(str){
		
		}
	});
}

function init_change_batch_calendar(){
	Calendar.setup({
		inputField     :    "inp_n_date",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "img_n_date",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
		//,
		//onUpdate       :    load_data
	}); 
}

function toggle_new_stock_info_chx(inp_name, ele){
	var editable = ele.checked;
	
	document.f_change_batch[inp_name].readOnly = editable;
}

function start_change_batch(){
	if(!document.f_change_batch){
		alert('Unhandle Error!');
		return false;
	}
	
	var f = document.f_change_batch;
	
	// check date
	if(!f['n_date'].value.trim()){
		alert('Invalid Date');
		f['n_date'].focus();
		return false;
	}
	// check location
	if(!f['keep_o_loc'].checked){
		if(!f['n_loc'].value.trim()){
			alert('Invalid Location');
			f['n_loc'].focus();
			return false;
		}
	}
	// check shelf
	if(!f['keep_o_shelf'].checked){
		if(!f['n_shelf'].value.trim()){
			alert('Invalid Shelf');
			f['n_shelf'].focus();
			return false;
		}
	}
	
	// check whether all are same, then no need update
	if(f['n_date'].value.trim() == f['o_date'].value.trim() && (f['keep_o_loc'].checked || f['n_loc'].value.trim()==f['o_loc'].value.trim()) && (f['keep_o_shelf'].checked || f['n_shelf'].value.trim()==f['o_shelf'].value.trim())){
		alert('All info are same, nothing to update.');
		return false;
	}
	
	if(!confirm('Are you sure?'))	return false;
	f.submit();
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

<!-- popup change batch -->
<div id="div_change_batch" class="curtain_popup" style="position:absolute;z-index:10005;width:450px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_change_batch_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Change Batch</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_change_batch_content" style="padding:2px;height:420px;overflow:auto;">

	</div>
</div>

{if $smarty.request.err_msg}<font color="red">{$smarty.request.err_msg|htmlentities}</font>{/if}
<form name="f_a" action="admin.stock_take.php">

	<input type=hidden name=a value=load_table_data>
	<input type=hidden name=rpt_type>
	{if !$can_select_branch}<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />{/if}

	<table>
		<tr>
		    {if $can_select_branch}<td><b>Branch</b></td>{/if}
			<td valign=top><b>Date</b></td>
			<td><b>Location</b></td>
			<td><b>Shelf</b></td>
			<td></td>
		</tr>
		<tr>
		    {if $can_select_branch}
		        <td>
		            <select class="form-control" name="branch_id" onchange="branch_changed(this.value)" size="10">
						{foreach from=$branches item=r}
							<option value="{$r.id}" {if !$smarty.request.branch_id and $BRANCH_CODE eq $r.code}selected {else}{if $smarty.request.branch_id eq $r.id}selected {/if}{/if}>{$r.code}</option>
						{/foreach}
					</select>
		        </td>
		    {/if}
			<td>
			    <div id="div_date" style="min-width:100px;">
					<select class="form-control" name="dat" onchange="load_location(this.value)" size=10 style="width:100%;">
						{foreach from=$dat item=val}
							<option value="{$val.date}" {if $smarty.request.date eq $val.date}selected {/if}>{$val.date}</option>
						{/foreach}
					</select>
				</div>
			</td>
			<td>
				<div id="div_location" style="min-width:100px;">
					<select class="form-control" name="loc" onchange="load_shelf(this.value)" size=10 style="width:100%;">
						{foreach from=$loc item=val}
							<option value="{$val.location}" {if $smarty.request.location eq $val.location}selected {/if}>{$val.location}</option>
						{/foreach}
					</select>
				</div>
			</td>
			<td>
				<div id="div_shelf" style="min-width:100px;">
					<select class="form-control" name="shelf" onchange="show_record()" size=10 style="width:100%;">
						{foreach from=$shelf item=val}
							<option value="{$val.shelf}" {if $smarty.request.shelf eq $val.shelf}selected {/if}>{$val.shelf}</option>
						{/foreach}
					</select>
				</div>
			</td>
		</tr>
	</table>
</form>

<input class="btn btn-primary" type="button" value="Change Batch" onclick="popup_change_batch();"  id="btn_change_batch" disabled />

{include file='footer.tpl'}

{if $smarty.request.err_msg}</font>
	<script>alert('{$smarty.request.err_msg|escape:javascript}');</script>
{/if}

{literal}
<script>
new Draggable('div_change_batch',{ handle: 'div_change_batch_header'});
</script>
{/literal}