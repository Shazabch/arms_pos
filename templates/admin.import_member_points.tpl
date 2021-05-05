{*
8/30/2013 3:14 PM Justin
- Enhanced to have new function that can set imported points as latest points.
*}

{include file='header.tpl'}
{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>

</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function check_file(obj){
	switch(obj.tagName.toLowerCase()){
		case 'form':
			var filename = obj.elements['import_csv'].value;
		break;
		case 'input':
			var filename = obj.value;
		break;
	}
	
	// only accept csv file
	if(filename.indexOf('.csv')<0){
		alert('Please select a valid csv file');
		return false;
	}
	return true;
}

function check_form(form){
	if(!check_file(form)) return false;   // check file extension
	
	if(!form.elements['date'].value){
		alert("Please select a date.");
		return false;
	}
	
	// ask final confirmation
	if(!confirm('Are you sure? This action cannot be undo!')) return false;
	
	return true; // no problem found
}

function condition_clicked(obj){
	if(obj.checked == true){
		if(obj.name == "clear_data"){
			document.f_a['clear_data'].disabled = false;
			document.f_a['is_curr_points'].disabled = false;
		}else{
			document.f_a['clear_data'].disabled = true;
			document.f_a['is_curr_points'].disabled = false;
		}
	}else{
		document.f_a['clear_data'].disabled = false;
		document.f_a['is_curr_points'].disabled = false;
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err.top}
The following error(s) has occured:
	<ul class="err" style="color:red;">
		{foreach from=$err.top item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" enctype="multipart/form-data" class="stdframe" method="post" onSubmit="return check_form(this);">
	<input type="hidden" name="a" value="import_member_points" />
	<table>
		<tr>
			<td colspan="2" style="color:#0000ff;">
				Note:<br/>
				Please make sure it is a valid CSV file.<br /><br />
			</td>
		</tr>
		<tr>
			<td><b>Date</b></td>
			<td>
				<input type="text" name="date" id="date" size="8" readonly><img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="checkbox" name="clear_data" value="1" onclick="condition_clicked(this);" /> <b>Clear all imported data on following Date</b><br />
				&nbsp;(Points will sum up with previous imported points if this option is untick)<br />
				<input type="checkbox" name="is_curr_points" value="1" onclick="condition_clicked(this);" /> <b>Import as current points</b>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=view_sample_member_points">View Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv" onChange="check_file(this);" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Import" /> <span style="color:red;">Warning: This action cannot be undo.</span></td>
		</tr>
	</table>
</form>
{if $ttl_imported_row}<p style="color:blue;">Import Success! Total {$ttl_imported_row} of {$ttl_row} item(s) imported</p>{/if}
{if $err.warning}
	<ul>
		{foreach from=$err.warning item=m}
			<li>{$m}</li>
		{/foreach}
	</ul>
{/if}

{literal}
<script>
	Calendar.setup({
		inputField     :    "date",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});
</script>
{/literal}
{include file='footer.tpl'}
