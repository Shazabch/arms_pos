<?php /* Smarty version 2.6.18, created on 2021-05-07 18:20:50
         compiled from frontend.index.tpl */ ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<?php echo '
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
'; ?>


<script type="text/javascript">
var phpself = '<?php echo $_SERVER['PHP_SELF']; ?>
';
<?php echo '
function add() {
	new Ajax.Request(phpself,{
		method:\'post\',
		parameters:{
			a: \'check_counter_limit\',
			check: 1
		},
		onComplete: function(e){
			var str = e.responseText.trim();
			if (str == \'false\') {
				alert("Reached maximum counter limit, unable to create new counter.");
				return;
			}
            else
				open(0);
		}
	});
}

function curtain_clicked(){
	$(\'div_counter_details\').hide();
}

function open(id) {
    curtain(true);
    center_div(\'div_counter_details\');
	$(\'div_counter_details\').show();
	$(\'div_counter_details_content\').update(_loading_);

	new Ajax.Updater(\'div_counter_details_content\',phpself,{
	    parameters:{
			a: \'open\',
			id: id
		},
		evalScripts: true
	})
}

function act(id, status){
    $(\'span_refreshing\').update(_loading_);
	new Ajax.Request(phpself,{
		method:\'post\',
		parameters:{
			a: \'toggle_status\',
			id: id,
			status: status
		},
		onComplete: function(e){
			var str = e.responseText.trim();
			if (str!=\'Ok\')
                alert(str);
            reload_table(true);
		}
	});
}

function reload_table() {
    $(\'span_refreshing\').update(_loading_);
    new Ajax.Updater(\'div_table\',phpself,{
		parameters:{
            a: \'load_table\'
        }
	});
}

var loc_auto = undefined;

function location_autocomplete() {
	var param_str = "a=ajax_location_list&";
	loc_auto = new Ajax.Autocompleter("location", "div_autocomplete_location", phpself, {parameters:param_str, paramName: "value", indicator: \'location_load\'});
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

	if(!confirm("Are you sure want to delete this Counter Status? \\n(NOTE: this action cannot be undo)")) return;
	
	var params = {
		a: \'ajax_unset_counter_status\',
		counter_id: cid
	};
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){			
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = \'\';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret[\'ok\']){ // success
					alert("Delete Counter Status successfully.");
					reload_table(true);
					return;
				}else{  // save failed
					if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
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
'; ?>

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
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'frontend.table.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<script>
new Draggable(\'div_counter_details\',{ handle: \'div_counter_details_header\'});
</script>
'; ?>