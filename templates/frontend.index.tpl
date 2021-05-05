{*
12/30/2010 4:34:50 PM Andy
- Check counter limit when adding new counter.

10/05/2011 11:31:09 AM Kee Kee
- Add Mprice Privilege in counter settings

10/13/2011 5:03:33 PM Andy
- Add setting to turn on/off "Print Receipt Reference Code".

11/24/2011 3:01:00 PM Kee Kee
- Hide "Print Receipt Reference Code" label and checkbox.

11/09/2011 10:52:00 AM Kee Kee
- Show "Print Receipt Reference Code" label and checkbox.

12/6/2011 3:13:43 PM Justin
- Added value=1 for all checkboxes.

01/16/2012 5:34:00 PM Kee Kee
- set "Deposit Settings" default is not allow

03/13/2012 9:10:00 AM Kee Kee
- add "Return Policy" settings
- set "Return Policy" default is not allow

03/14/2012 4:26:00 PM Kee Kee
- add "Trade In" settings
- set "Trade In" default is not allow

08/07/2012 3:44 PM Kee Kee
- Add "Allow Adjust Member Point" in member settings

11/12/2012 1:57 PM Kee Kee
- Added "Hold Bill Slot" in pos settings

7/9/2013 2:04 PM Justin
- Enhanced while activating counter also check counter limit.

4/15/2014 11:26 AM Kee Kee
- Enhanced to add Sync to weight scale setting.

9/14/2015 3.25 PM DingRen
- add logout checking on add counter

9/17/2015 5:43 PM Andy
- Rename from "Front End Setup" to "Counters Setup".
- Add new Weight Scale Format "BC11 800 v2".

11/18/2015 3:00 PM Andy
- Fix activate counter bug.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

01/21/2016 10:13 AM Edwin
- Change popup save/edit, reload table by using ajax
- Network name not allow to edit except user_id = 1
- Add temporary counter with date from/to.

10/30/2017 5:53 PM Justin
- Enhanced to allow user to unset counter_status (need privilege).

11/15/2019 10:48 AM Justin
- Bug fixed on counter's edit / add screen couldn't amend the checkbox from the end of the window.
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#div_counter_details{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}
#div_counter_details_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_counter_details_content{
    padding:2px;
}

.calendar, .calendar table {
	z-index:100000;
}
input[readonly]{
		background-color: #f0f0f0;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function add() {
	new Ajax.Request(phpself,{
		method:'post',
		parameters:{
			a: 'check_counter_limit',
			check: 1
		},
		onComplete: function(e){
			var str = e.responseText.trim();
			if (str == 'false') {
				alert("Reached maximum counter limit, unable to create new counter.");
				return;
			}
            else
				open(0);
		}
	});
}

function curtain_clicked(){
	$('div_counter_details').hide();
}

function open(id) {
    curtain(true);
    center_div('div_counter_details');
	$('div_counter_details').show();
	$('div_counter_details_content').update(_loading_);

	new Ajax.Updater('div_counter_details_content',phpself,{
	    parameters:{
			a: 'open',
			id: id
		},
		evalScripts: true
	})
}

function act(id, status){
    $('span_refreshing').update(_loading_);
	new Ajax.Request(phpself,{
		method:'post',
		parameters:{
			a: 'toggle_status',
			id: id,
			status: status
		},
		onComplete: function(e){
			var str = e.responseText.trim();
			if (str!='Ok')
                alert(str);
            reload_table(true);
		}
	});
}

function reload_table() {
    $('span_refreshing').update(_loading_);
    new Ajax.Updater('div_table',phpself,{
		parameters:{
            a: 'load_table'
        }
	});
}

var loc_auto = undefined;

function location_autocomplete() {
	var param_str = "a=ajax_location_list&";
	loc_auto = new Ajax.Autocompleter("location", "div_autocomplete_location", phpself, {parameters:param_str, paramName: "value", indicator: 'location_load'});
}

function activate_calendar() {
    Calendar.setup({
		inputField     :    "temp_start_date",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "b_temp_start_date",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
	});
	
	Calendar.setup({
		inputField     :    "temp_end_date",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "b_temp_end_date",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
	});
}

unset_counter_status = function(cid){
	if(cid == "" || cid == 0) return;

	if(!confirm("Are you sure want to delete this Counter Status? \n(NOTE: this action cannot be undo)")) return;
	
	var params = {
		a: 'ajax_unset_counter_status',
		counter_id: cid
	};
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){			
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					alert("Delete Counter Status successfully.");
					reload_table(true);
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
{/literal}
</script>

<!--pop out window-->
<div id="div_counter_details" style="position:absolute;z-index:10000;width:450px;height:575px;display:none;border:2px solid #CE0000;">
	<div id="div_counter_details_header"><span style="float:left;">Counter Information</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_counter_details_content" style="overflow-x:hidden;overflow-y:auto;max-height:87%;"></div>
</div>

<h1>Counters Setup</h1>

<div><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Counter</a>(Alt+A)</div><br>

<div id="div_table" class="stdframe">
{include file='frontend.table.tpl'}
</div>

{include file=footer.tpl}

{literal}
<script>
new Draggable('div_counter_details',{ handle: 'div_counter_details_header'});
</script>
{/literal}