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
	
	<div class="card mx-3 mt-3">
		<div class="card-body">
			<b class="text-danger">Warning: </b>
			<ul class="text-muted">
				<li> Please prevent to import at business hour. It will slow down the performance of all counters across branches.</li>
				<li> It is recommended to import maximum 500 members in a batch, and wait for 10 minutes for counter to sync.</li>
				<li> This action CANNOT be undo.</li>
			</ul>
		</div>
	</div>
		

{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err.top}
		<div class="alert alert-danger rounded mx-3">
			<b>The following error(s) has occured:</b>
	<ul class="err">
		{foreach from=$err.top item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
		</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" method="post" onSubmit="return check_form(this);">
			<input type="hidden" name="a" value="import_members" />
		
			<div class="alert alert-primary rounded ">
				Note:<br />
				* You can either pre-activate members by card range or specified card no.<br />
				* The name of new members will be insert as "NEW MEMBER"
			</div>
				
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
				
						<b class="fs-09">Activate Method</b>
						<br>
						<div class="mt-2">
							<input type="radio" name="import_type" value="by_range" onclick="check_import_type(this);" {if !$form.import_type || $form.import_type eq 'by_range'}checked{/if} /> <span class="fs-09">By Range</span>
						<input type="radio" name="import_type" value="by_cn_list" onclick="check_import_type(this);" {if $form.import_type eq 'by_cn_list'}checked{/if}/><span class="fs-09"> By Card No List</span>
				
				
						<input type="checkbox" name="skip_duplicate" value="1" {if $form.skip_duplicate}checked{/if} /> <span class="fs-09">Skip duplicated Card No</span>
						</div>
				
				
				<div id="range_import" class="" {if $form.import_type eq 'by_cn_list'}style="display:none;"{/if}>
					
						<label class="mt-2"><b class="fs-09 ">Card Prefix</b></label>
						<input type="text" class="form-control" name="card_prefix" id="card_prefix" size="8" value="{$form.card_prefix}">
						
						<label class="mt-2"><b class="fs-09 mt-2">Cards Range</b></label>
						<input type="text" class="form-control" name="card_range_from" id="card_range_from" size="{$config.membership_length|default:12}" value="{$form.card_range_from}" onchange="hide_card_list();"> 
						<label class="mt-2"><b class="fs-09 mt-2">To</b></label>
						<input type="text" class="form-control" name="card_range_to" id="card_range_to" size="{$config.membership_length|default:12}" value="{$form.card_range_to}" onchange="hide_card_list();">				
						
				</div>

				<div id="cn_list_import" {if !$form.import_type || $form.import_type eq 'by_range'}style="display:none;"{/if}>
						<label class="mt-2"><b>Card No</b></label>
						<textarea class="form-control" name="card_no_list" cols="{$config.membership_length|default:12}" rows="3" >{$form.card_no_list}</textarea>
						(Support multiple Card No)
				
				</div>
				<div class="row">
					<div class="col-md-6"><label class="mt-2"><b class="fs-09">Issue Date</b></label>
						<div class="form-inline">
							<input type="text" class="form-control" name="issue_date" id="issue_date"  value="{$form.issue_date}" readonly>
							
							<img align="absmiddle" width="25px" height="25px" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
						</div>
					</div>
					<div class="col-md-6"><label class="mt-2"><b class="fs-09">Expiry Date</b></label>
						<div class="form-inline">
							<input type="text" class="form-control" name="expiry_date" id="expiry_date"  value="{$form.expiry_date}" readonly>
							<img align="absmiddle" width="25px" height="25px" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
						</div>
					</div>
				</div>
				<button class="btn btn-primary mt-2">Import</button>
			
		</form>
	</div>
</div>
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
