{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var PH_ASSIGN = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		this.init_calendar();
	},
	// initialise calendar
	init_calendar: function(){
		// Loop holiday list
		$$('#div_ph_list tr.tr_ph').each(function(tr){
			var ph_id = tr.id.split('-')[1];
			
			// Date From
			Calendar.setup({
				inputField     :    "inp_date_from-"+ph_id,     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "img_date_from-"+ph_id,  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true,
				onUpdate       :    function(e){
					if(!$("inp_date_to-"+ph_id).value){
						$("inp_date_to-"+ph_id).value = $("inp_date_from-"+ph_id).value;
					}
				}
			});
			
			// Date To
			Calendar.setup({
				inputField     :    "inp_date_to-"+ph_id,     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "img_date_to-"+ph_id,  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true,
				onUpdate       :    function(e){
					if(!$("inp_date_from-"+ph_id).value){
						$("inp_date_from-"+ph_id).value = $("inp_date_to-"+ph_id).value;
					}
				}
			});
		});
	},
	// function to validate form
	validate_form: function(){
		// Year
		var y = int(this.f['y'].value);
		if(y < 2000 || y > 2999){
			alert('Invalid Year');
			this.f['y'].focus();
			return false;
		}
		
		// Loop holiday list
		var tr_ph_list = $$('#div_ph_list tr.tr_ph');
		for(var i=0,len=tr_ph_list.length; i<len; i++){
			var tr_ph = tr_ph_list[i];
			var ph_id = tr_ph.id.split('-')[1];
			var date_from = $("inp_date_from-"+ph_id).value;
			var date_to = $("inp_date_to-"+ph_id).value;
			var ph_code = this.f['ph_list['+ph_id+'][code]'].value;
			
			if(!date_from){
				alert('Please select Date From for ['+ph_code+']');
				return false;
			}
			
			if(!date_to){
				alert('Please select Date To for ['+ph_code+']');
				return false;
			}
			
			if(strtotime(date_from) > strtotime(date_to)){
				alert('Date To is ealier than Date From for ['+ph_code+']');
				return false;
			}
		}
		
		return true;
	},
	// function when user click on button save
	save_clicked: function(){
		// Check form
		if(!this.validate_form())	return false;
		
		// Show processing
		GLOBAL_MODULE.show_wait_popup();
		
		var THIS = this;
		var params = (this.f).serialize();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				// Hide processing
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update HTML
						alert('Save Successfully');
						document.location = phpself;
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	}
};
{/literal}
</script>

<h1>{$PAGE_TITLE} {if $form}({$form.y}){else}(NEW){/if}</h1>


<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save" />
	<input type="hidden" name="id" value="{$form.id}" />
	
	<div class="stdframe">
		<table>
			<tr>
				<td width="100"><b>Year</b></td>
				<td>
					<input type="text" name="y" value="{$form.y}" {if $form.id gt 0}readonly {/if} style="width:50px;" onChange="miz(this);" maxlength="4" />
				</td>
			</tr>
		</table>
	</div>
	
	<br />
	<h3>Holiday List</h3>
	<div class="stdframe" id="div_ph_list">
		<table class="report_table" style="background-color: #fff;">
			<tr class="header">
				<th>Code - Description</th>
				<th>Date From</th>
				<th>Date To</th>
				<th>Show in Report 
					[<a href="javascript:void(alert('Always show the Holiday in Report even no user on shift'))">
					?
					</a>]
				</th>
			</tr>
			
			{foreach from=$ph_list key=ph_id item=ph}
				<tr id="tr_ph-{$ph_id}" class="tr_ph">
					<td>
						<input type="hidden" name="ph_list[{$ph_id}][code]" value="{$ph.code}" />
						{$ph.code} - {$ph.description}
					</td>
					
					{* Date From *}
					<td>
						<input type="text" name="ph_list[{$ph_id}][date_from]" value="{$form.ph_list.$ph_id.date_from}" id="inp_date_from-{$ph_id}" size="12" class="inp_date_from" />
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_from-{$ph_id}" style="cursor: pointer;" title="Select Date"/> &nbsp;
					</td>
					
					{* Date To *}
					<td>
						<input type="text" name="ph_list[{$ph_id}][date_to]" value="{$form.ph_list.$ph_id.date_to}" id="inp_date_to-{$ph_id}" size="12" class="inp_date_to" />
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_to-{$ph_id}" style="cursor: pointer;" title="Select Date"/> &nbsp;
					</td>
					
					{* Show in Report *}
					<td align="center">
						<input type="checkbox" name="ph_list[{$ph_id}][show_in_report]" value="1" {if $form.ph_list.$ph_id.show_in_report}checked {/if} />
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
</form>

<p align="center" id="p_action">
	<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="PH_ASSIGN.save_clicked();" />
	<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='{$smarty.server.PHP_SELF}'" />
</p>

<script>PH_ASSIGN.initialize();</script>
{include file='footer.tpl'}