{*
5/14/2010 6:07:40 PM Alex
- Add reset_area_autocomplete()

12/7/2012 3:27:00 PM Fithri
- add description and alphabet filter

4/1/2013 4:03 PM Andy
- Allow to generate login ticket for debtor if got config "enable_debtor_portal".

5/27/2013 2:14 PM Andy
- Change the debtor key to be editable from masterfile and at least 10 characters.

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

1/24/2015 4:12 PM Justin
- Enhanced search engine to search debtor code as well.

2/3/2015 3:57 PM Andy
- Enlarge the debtor details popup width to 700px.
- set maximum height for debtor details container div to 92%, and show scrollbar if overflow.

11/1/2016 11:41 AM Andy
- Enhanced to have debtor gst start date and gst registration number.

4/25/2017 1:40 PM Justin
- Bug fixed on alignment function is no longer working after change page or do filtering.

2017-08-25 15:08 PM Qiu Ying
- Enhanced to add Debtor blocklist for Credit Sales DO at debtor masterfile

12/1/2017 11:33 AM Justin
- Adjusted the popup window for Add/Edit Debtor to have larger height.

3/30/2018 4:13 PM HockLee
- Add new input Integration Code.
- Add integration_code_changed() function.
*}


{include file=header.tpl}
{literal}
<style>
.debtor_details{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}
.debtor_details_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

.debtor_details_content{
    padding:2px;
}
.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}

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
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function show_blocklist(id,n)
{
	curtain(true);
	center_div('div_debtor_blocklist');
	$('div_debtor_blocklist').show();
	$('div_debtor_blocklist_content').update(_loading_);
	new Ajax.Updater("div_debtor_blocklist_content", "masterfile_debtor.php",{
	    parameters:  'a=load_blocklist&debtor_id='+id,
	    evalScripts: true
	});
}

function curtain_clicked(){
	$('div_debtor_details').hide();
	$('div_debtor_blocklist').hide();
}

function ucwords(ele){
	ele.value = ele.value.capitalize(true);
}

function add(){
	open(0);
}

function open(id){
    curtain(true);
	center_div('div_debtor_details');
	$('div_debtor_details').show();
	$('div_debtor_details_content').update(_loading_);
	new Ajax.Updater('div_debtor_details_content',phpself,{
	    parameters:{
			a: 'open',
			id: id
		},
		evalScripts: true
	})
}

function reload_table(load_page){
	$('span_refreshing').update(_loading_);
	
	if (load_page) lp = '&pg='+$('pg').value;
	else lp = '';
	params = 'a=reload_table&'+Form.serialize($('search_form'))+lp;
	//alert(params);return;
	
	new Ajax.Updater('div_table',phpself,{
		parameters:params,
		onComplete: function(){
			ts_makeSortable($('debtor_tbl'));
		}
	});
	return false;
}


function do_find() {
	$('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table',phpself,{
		parameters:{
			a: 'reload_table',
			search : $('search').value
		}
	});
}

function act(id,status){
    $('span_refreshing').update(_loading_);
	new Ajax.Request(phpself,{
		method:'post',
		parameters:{
			a: 'toggle_status',
			id: id,
			status: status
		},
		onComplete: function(){
			//alert($('span_refreshing'));
			reload_table(true);
		}
	});
}

var area_autocomplete = undefined;

function reset_area_autocomplete()
{
	var param_str = "a=ajax_search_area&";
	area_autocomplete = new Ajax.Autocompleter("inp_area", "div_autocomplete_area_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_area'});
}

function generate_debtor_ticket(){
	var alpha_list = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var ticket_length = 10;
		var ticket = '';
		
		// generate new ticket
		for(var i=0; i<ticket_length; i++){
			var j = Math.floor(Math.random()*(rand_char.length));
			ticket += rand_char[j];
		}
		
		
		// assign
		document.f_a['login_ticket'].value = ticket;
}

function clear_debtor_ticket(){
	document.f_a['login_ticket'].value = '';
}

function login_ticket_changed(){
	document.f_a['login_ticket'].value = document.f_a['login_ticket'].value.regex(/\s/g, '');	// replace all whitespace
}

function integration_code_changed(){
	var int_code = document.f_a['integration_code'];
	var str = document.f_a['integration_code'].value.trim();
	int_code.value = int_code.value.regex(/\s/g, '');	// replace all whitespace
	int_code.value = int_code.value.regex(/[&\]/\\#,+()_$`@^~%.'[":*?<>|?!;:=~{}-]/g, '');	// replace special character

	if(str){	// got integration code
		if(str.length > 10){	// min 10 char
			alert('Integration code cannot more than 10 characters');
			document.f_a['integration_code'].focus();
			return false;
		}
		
		if(!str.match(/^[a-z0-9]+$/i)){
			alert('Integration code only allow alphabet and number.');
			document.f_a['integration_code'].focus();
			return false;
		}
	}
}

function gst_reg_no_changed(){
	check_gst_allow_edit();
}

function gst_reg_date_changed(){
	check_gst_allow_edit();
}

function check_gst_allow_edit(){
	if(document.f_a['gst_register_no'].value.trim()==''){
		document.f_a['gst_start_date'].value = '';
	}
}
{/literal}
</script>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body"><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd New Debtor</a> (Alt+A)</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="search_form" id="search_form" onsubmit="return reload_table()">
			<p>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Code / Description :</b> 
				<input class="form-control" type="text" name="desc" size="15" />
			
				</div>
			
				<div class="col-md-4">
					<b class="form-label">Status :</b>
				<select class="form-control" name="status">
					<option value="">All</option>
					<option value="1">Active</option>
					<option value="0">Inactive</option>
				</select>
			
				</div>
		
				<div class="col-md-4">
					<b class="form-label">Starts With :</b>
				<select class="form-control" name="starts_with">
					<option value="">All</option>
					<option value="A">A</option>
					<option value="B">B</option>
					<option value="C">C</option>
					<option value="D">D</option>
					<option value="E">E</option>
					<option value="F">F</option>
					<option value="G">G</option>
					<option value="H">H</option>
					<option value="I">I</option>
					<option value="J">J</option>
					<option value="K">K</option>
					<option value="L">L</option>
					<option value="M">M</option>
					<option value="N">N</option>
					<option value="O">O</option>
					<option value="P">P</option>
					<option value="Q">Q</option>
					<option value="R">R</option>
					<option value="S">S</option>
					<option value="T">T</option>
					<option value="U">U</option>
					<option value="V">V</option>
					<option value="W">W</option>
					<option value="X">X</option>
					<option value="Y">Y</option>
					<option value="Z">Z</option>
					<option value="others">Others</option>
				</select>
				</div>
			</div>

			<input type="button" class="btn btn-primary mt-2" value="Search" onclick="reload_table()" />
			</p>
			</form>
	</div>
</div>

<br>
<div class="card mx-3">
	<div class="card-body">
		<div id="div_table" class="stdframe">
			{include file='masterfile_debtor.table.tpl'}
		</div>
	</div>
</div>

<div id="div_debtor_details" class="debtor_details" style="position:absolute;z-index:10000;width:700px;height:600px;display:none;border:2px solid #CE0000;">
	<div id="div_debtor_details_header" class="debtor_details_header"><span style="float:left;">Debtor Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_debtor_details_content" class="debtor_details_content" style="overflow-x:hidden;overflow-y:auto;max-height:92%;"></div>
</div>

<div id="div_debtor_blocklist" class="debtor_details" style="position:absolute;z-index:10000;width:800px;height:250px;display:none;border:2px solid #CE0000;">
	<div id="div_debtor_blocklist_header" class="debtor_details_header"><span style="float:left;">Branch Block List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	
	<div id="div_debtor_blocklist_content" class="debtor_details_content" style="height:240px;overflow-x:auto;overflow-y:auto;max-height:92%;"></div>
</div>

{include file=footer.tpl}

{literal}
<script>
new Draggable('div_debtor_details',{ handle: 'div_debtor_details_header'});
new Draggable('div_debtor_blocklist',{ handle: 'div_debtor_blocklist_header'});
</script>
{/literal}
