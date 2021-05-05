{*
8/6/2013 3:38 PM Justin
- Enhanced to show card no list before confirm to insert new member.

07/21/2016 16:00 Edwin
- Rename file from 'admin.import_members' to 'admin.preactivate_member_cards'.
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
var import_type = '{$form.import_type|default:"by_range"}';

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
	if(!form.elements['issue_date'].value || !form.elements['expiry_date'].value){
		alert("Please select a issue and expiry date.");
		return false;
	}
	
	if(import_type == "by_range" && $('div_card_list').style.display == "none" && form.elements['card_prefix'].value  && form.elements['card_range_from'].value >= 0 && form.elements['card_range_to'].value > 0){
		ajax_load_card_list();
		return false;
	}
	
	// ask final confirmation
	if(!confirm('Are you sure? This action cannot be undo!')) return false;
	
	return true; // no problem found
}

function check_import_type(obj) {
	if(obj.value == "by_range"){
		$('range_import').style.display = "";
		$('cn_list_import').style.display = "none";
		import_type = "by_range";
	}else{
		$('range_import').style.display = "none";
		$('cn_list_import').style.display = "";
		import_type = "by_cn_list";
	}

	hide_card_list();
}

function ajax_load_card_list(){
	// ajax_add_item_row
	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+'&a=ajax_load_card_list_by_range',
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			if(m.responseText){
				$('div_card_list').style.display = "";
				$('div_card_list').update(m.responseText);
			}
		},
	});
	
}

function hide_card_list(){
	$('div_card_list').style.display = "none";
}
{/literal}
</script>

{if !$config.consignment_modules}
	<div style="border:2px solid red;padding:5px;background-color:yellow;color:red;font-weight:bold;font-size:120%;">
		Warning: 
		<ul>
			<li> Please prevent to import at business hour. It will slow down the performance of all counters across branches.</li>
			<li> It is recommended to import maximum 500 members in a batch, and wait for 10 minutes for counter to sync.</li>
			<li> This action CANNOT be undo.</li>
		</ul>
		
	</div>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err.top}
The following error(s) has occured:
	<ul class="err" style="color:red;">
		{foreach from=$err.top item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" class="stdframe" method="post" onSubmit="return check_form(this);">
	<input type="hidden" name="a" value="import_members" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* You can either pre-activate members by card range or specified card no.<br />
				* The name of new members will be insert as "NEW MEMBER"<br /><br />
			</td>
		</tr>
		<!--tr>
			<td>&nbsp;</td>
			<td>
				<input type="checkbox" name="clear_data" value="1" /> <b>Clear all imported data on following Date</b>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=view_sample_member_points">View Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv" onChange="check_file(this);" />
			</td>
		</tr-->
		<tr>
			<td><b>Activate Method</b></td>
			<td colspan="3">
				<input type="radio" name="import_type" value="by_range" onclick="check_import_type(this);" {if !$form.import_type || $form.import_type eq 'by_range'}checked{/if} /> By Range
				<input type="radio" name="import_type" value="by_cn_list" onclick="check_import_type(this);" {if $form.import_type eq 'by_cn_list'}checked{/if}/> By Card No List
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="3">
				<input type="checkbox" name="skip_duplicate" value="1" {if $form.skip_duplicate}checked{/if} /> Skip duplicated Card No
			</td>
		</tr>
		<tr><td colspan="4">&nbsp;</td></tr>
		<tbody id="range_import" {if $form.import_type eq 'by_cn_list'}style="display:none;"{/if}>
			<tr>
				<td><b>Card Prefix</b></td>
				<td colspan="3">
					<input type="text" name="card_prefix" id="card_prefix" size="8" value="{$form.card_prefix}">		
				</td>
			</tr>
			<tr>
				<td><b>Cards Range</b></td>
				<td colspan="3">
					<input type="text" name="card_range_from" id="card_range_from" size="{$config.membership_length|default:12}" value="{$form.card_range_from}" onchange="hide_card_list();"> To
					<input type="text" name="card_range_to" id="card_range_to" size="{$config.membership_length|default:12}" value="{$form.card_range_to}" onchange="hide_card_list();">				
				</td>
			</tr>	
		</tbody>
		<tr id="cn_list_import" {if !$form.import_type || $form.import_type eq 'by_range'}style="display:none;"{/if}>
			<td valign="top" style="padding-top: 4px;"><b>Card No</b></td>
			<td colspan="3">
				<textarea name="card_no_list" cols="{$config.membership_length|default:12}" rows="15">{$form.card_no_list}</textarea>
				(Support multiple Card No)
			</td>
		</tr>
		<tr>
			<td><b>Issue Date</b></td>
			<td>
				<input type="text" name="issue_date" id="issue_date" size="8" value="{$form.issue_date}" readonly><img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</td>
			<td><b>Expiry Date</b></td>
			<td>
				<input type="text" name="expiry_date" id="expiry_date" size="8" value="{$form.expiry_date}" readonly><img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="3"><input type="submit" value="Import" /></td>
		</tr>
	</table>
</form>
{if $ttl_row}<p style="color:blue;">Import Success! Total {$ttl_imported_row} of {$ttl_row} item(s) imported</p>{/if}
{if $err.warning}
	<ul>
		{foreach from=$err.warning item=m}
			<li>{$m}</li>
		{/foreach}
	</ul>
{/if}
<br />
<div id="div_card_list" class="stdframe" style="height:200px; width:400px; overflow:auto; display:none;"></div>

{literal}
<script>
	Calendar.setup({
		inputField     :    "issue_date",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});

	Calendar.setup({
		inputField     :    "expiry_date",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added2",
		align          :    "Bl",
		singleClick    :    true
	});
</script>
{/literal}
{include file='footer.tpl'}
