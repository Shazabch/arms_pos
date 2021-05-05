{include file='header.tpl'}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{*canvas js and files*}
<link type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="js/canvasjs.min.js"></script>
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

<style>
{literal}
.div_card_list{
	width: auto;
	height: auto;
	display: flex;
}
.card{
	display: flex;
	-ms-flex-direction: column;
	flex-direction: column;
	min-width: 0;
	word-wrap: break-word;
	background-color:#fff;
	background-clip: border-box;
	border: 1px solid rgba(0, 0, 0, 0.125);
	width: 165px;
	margin: 15px;
	float:left;
	min-width:90px;
}
.card span{
	font-weight: bold;
	display: flex;
	align-items: center; 
	justify-content: center; 
}
.card_header{
	display: block;
	color: #fff !important;
	font-weight: bold;
	text-align:center;
}
.card_header span{
	font-size: 4em;
	height: 110px;
}
.card_footer{
	height:40px;
}
.card_footer span{
	padding:15px;
	text-align: center;
}
.div_chart{
	height: 325px;
	width: 94.4%;
	border: 1px solid black;
	float: left;
	margin-left: 2%;
}
.div_chart2{
	height: 310px;
	width: 46%;
	border: 1px solid black;
	float: left;
	margin: 20px 0% 20px 2%;
}
{/literal}
</style>


<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

var JQ= {};
JQ = jQuery.noConflict(true);
var ATTENDANCE_OVERVIEW = {
	initialize: function(){
		this.init_calendar();
		this.employee_daily_working_ratio();
		this.dept_attendance_ratio();
		this.daily_employee_attendance_strength();
	},
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
		});
	},
	
	dept_attendance_ratio : function(){
		var THIS = this;
		
		JQ('#div_chart_department_attendance_ratio').html('<br><div align="center">Loading.Please wait...</div>');
		
		CanvasJS.addColorSet("colorShades1",["#ff1616","#4e9f37"]);
		
		var params= {
			a: 'ajax_dept_attendance_ratio',
			branch_id: JQ('#branch_id').val(),
			date: JQ('#inp_date').val(),
		};
		
		JQ.post(phpself, params, function(data){
			var ret = {};
			var err_msg = '';
			var can_generate_chart = false;
			JQ("#div_chart_department_attendance_ratio").html('');
		
		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}
				else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg) err_msg = 'No Respond from Server.';
			if(!can_generate_chart){
		    	JQ('#div_chart_department_attendance_ratio').html('Department Attendance Ratio cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
			}else{
				if(ret['ok']){
	            	THIS.department_attendance_ratio_chart = new CanvasJS.Chart("div_chart_department_attendance_ratio",
				    {
				    	colorSet : "colorShades1",
						title:{
							text: "Department Attendance Ratio ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							gridColor: "#B6B1A8",
							tickColor: "#B6B1A8",
							minimum: 0,
						    interval: 1
						},
			      	});  
			      	
			      	// start populate
					THIS.department_attendance_ratio_chart.options.data = [];
			
					////////
					if(ret['department_attendance_ratio']['dept'] && ret['department_attendance_ratio']['status']){
						for(var i=0; i < ret['department_attendance_ratio']['status'].length; i++){
						
							var status = ret['department_attendance_ratio']['status'][i];
							var entry = {        
								type: "stackedColumn",
								showInLegend: true,
								legendText: status,
							}
							
							entry.dataPoints = [];
							for(var department in ret['department_attendance_ratio']['dept']){
								if(!ret['department_attendance_ratio']['dept'][department][status])  ret['department_attendance_ratio']['dept'][department][status] = 0;
								entry.dataPoints.push({
									y: ret['department_attendance_ratio']['dept'][department][status],
									toolTipContent:  department+" : "+ret['department_attendance_ratio']['dept'][department][status].toLocaleString(),
									label: department,
									legendText: department+": "+ret['department_attendance_ratio']['dept'][department][status].toLocaleString(), 	//include amount in the legend
								});
							}
							THIS.department_attendance_ratio_chart.options.data.push(entry);
						}
					}else{
						JQ('#div_chart_department_attendance_ratio').html('<br><div align="center">No Data</div>');	
					}
					
			      	THIS.department_attendance_ratio_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();
				}
			}
		});
	},
	
	employee_daily_working_ratio : function(){
		var THIS = this;
		
		JQ('#div_employee_daily_working_ratio').html('<br><div align="center">Loading.Please wait...</div>');
		var params= {
			a: 'ajax_employee_daily_working_ratio',
			branch_id: JQ('#branch_id').val(),
			date: JQ('#inp_date').val(),
		};
		
		CanvasJS.addColorSet("colorShades2",
			[
				"#4e9f37",
				"#ffce3c",
				"#ff1616",
				"#e1a516",
				"#169ee1"
			]);
		
		JQ.post(phpself, params, function(data){
			var ret = {};
			var err_msg = '';
			var can_generate_chart = false;
			JQ("#div_employee_daily_working_ratio").html('');
			
		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}
				else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg) err_msg = 'No Respond from Server.';
			if(!can_generate_chart){
		    	JQ('#div_employee_daily_working_ratio').html('Employee Daily Working Ratio cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
			}else{
				if(ret['ok']){
	            	THIS.pie_chart = new CanvasJS.Chart("div_employee_daily_working_ratio",
				    {
				    	colorSet : "colorShades2",
						title:{
							text: "Employee Daily Working Ratio ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "",
							minimum: 0
						},
						axisX: {
							title: "Department",
							intervalType:"",
						},
			      	});  
			      	
			      	// start populate
					THIS.pie_chart.options.data = [];
					
					if(ret['employee_daily_ratio']){
						var entry = {        
							type: "pie",
							indexLabelFontFamily: "Arial",       
							indexLabelFontSize: 17,
							indexLabelFontWeight: "",
							indexLabelFontColor: "black",       
							indexLabelLineColor: "darkgrey", 
							startAngle:0,      
							showInLegend: true,
							toolTipContent:"{label}",
							indexLabelPlacement: "outside",
							dataPoints: []
						}
							
						entry.dataPoints = [];
						for(var status in ret['employee_daily_ratio']) {
							if(ret['employee_daily_ratio'][status]['count'] > 0){
								entry.dataPoints.push({
									y: ret['employee_daily_ratio'][status]['count'], 
									legendText: ret['employee_daily_ratio'][status]['status']+": "+ret['employee_daily_ratio'][status]['count'].toLocaleString(), 	//include amount in the legend
									label: ret['employee_daily_ratio'][status]['status']+": "+ret['employee_daily_ratio'][status]['count'].toLocaleString(),        //include amount in the label
									indexLabel: ret['employee_daily_ratio'][status]['status'],
								});
							}else{
								entry.dataPoints.push({
									y: ret['employee_daily_ratio'][status]['count'], 
									legendText: ret['employee_daily_ratio'][status]['status']+": "+ret['employee_daily_ratio'][status]['count'].toLocaleString(), 	//include amount in the legend
								});
							}
						}
						THIS.pie_chart.options.data.push(entry);
					}else{
						JQ('#div_employee_daily_working_ratio').html('<br><div align="center">No Data</div>');	
					}
					
			      	THIS.pie_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();
				}
			}
		});
	},
	
	daily_employee_attendance_strength: function (){
		var THIS = this;
		
		JQ('#div_recent_employee_daily_working_ratio').html('<br><div align="center">Loading.Please wait...</div>');
		var params= {
			a: 'ajax_recent_employee_daily_working_ratio',
			branch_id: JQ('#branch_id').val(),
			date: JQ('#inp_date').val(),
		};
		
		CanvasJS.addColorSet("colorShades3", ["#5335c1", "#4e9f37", "#ffce3c", "#ff1616", "#e1a516", "#169ee1"]);
		
		JQ.post(phpself, params, function(data){
			var ret = {};
			var err_msg = '';
			var can_generate_chart = false;
			JQ("#div_recent_employee_daily_working_ratio").html('');
			
		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}
				else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg) err_msg = 'No Respond from Server.';
			if(!can_generate_chart){
		    	JQ('#div_recent_employee_daily_working_ratio').html('Last 7 Days Employee Daily Working Ratio cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
			}else{
				if(ret['ok']){
	            	THIS.area_chart = new CanvasJS.Chart("div_recent_employee_daily_working_ratio",
				    {
				    	colorSet : "colorShades3",
						title:{
							text: "Last 7 Days Employee Daily Working Ratio",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" ,
						},
						axisY:{
							title:"",
							minimum: 0,
							interval: 1
						},
						axisX: {
							title: "Day",
							intervalType:"date",
							valueFormatString: "YYYY-MM-DD",
						},
			      	});  
			      	
			      	// start populate
					THIS.area_chart.options.data = [];
					
					if(ret['recent_employee_daily_ratio']){
						for(var status in ret['recent_employee_daily_ratio']){
							var entry = {
								name: ret['recent_employee_daily_ratio'][status]['status'],
								type: "line",
								showInLegend: true,
								legendMarkerType: "none",
							}
							
							entry.dataPoints = [];
							for(var date in ret['recent_employee_daily_ratio'][status]['date']){
								if(ret['recent_employee_daily_ratio'][status]['date'] != undefined){
								entry.dataPoints.push({
									x:new Date(ret['recent_employee_daily_ratio'][status]['date'][date]['year'],
											   ret['recent_employee_daily_ratio'][status]['date'][date]['month']-1, 
											   ret['recent_employee_daily_ratio'][status]['date'][date]['day']),
									y: ret['recent_employee_daily_ratio'][status]['date'][date]['count'],
									toolTipContent: ret['recent_employee_daily_ratio'][status]['status']+": "+ret['recent_employee_daily_ratio'][status]['date'][date]['count'],
								});
								
								}
								
							}
							THIS.area_chart.options.data.push(entry);
						}
						
					}else{
						JQ('#div_recent_employee_daily_working_ratio').html('<br><div align="center">No Data</div>');	
					}
			      	THIS.area_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();
				}
			}
		});
	},
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>
<form name="f_a"  method="post">
	<span>
		{if $BRANCH_CODE eq 'HQ'}
			<span>
				<b>Branch: </b>
				<select name="branch_id" id="branch_id">
					{foreach from=$branch_list key=bid item=b}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
					{/foreach}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
			</span>
		{else}
			<input type="hidden" id="branch_id" name="branch_id" value="{$sessioninfo.branch_id}" />
		{/if}
		
		<b>Date: </b>
		<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size="12" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" value="Refresh" />
	</span>
</form>


<div class="div_card_list">
	<div class="card">
		<span class="card_header" style="background-color:#5335c1;">
			<span>{$attendance_status.total_employee|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>Total Working Employee</span>
		</span>
	</div>
	
	<div class="card">
		<span class="card_header" style="background-color:#4e9f37;">
			<span>{$attendance_status.clock_in|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>Clock In</span>
		</span>
	</div>
	
	<div class="card">
		<span class="card_header" style="background-color:#ffce3c;">
			<span>{$attendance_status.late_entry|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>Late Entry</span>
		</span>
	</div>
	
	<div class="card">
		<span class="card_header" style="background-color:#ff1616;">
			<span>{$attendance_status.absent|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>Absent</span>
		</span>
	</div>
	
	<div class="card">
		<span class="card_header" style="background-color:#e1a516;">
			<span>{$attendance_status.on_leave|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>On Leave</span>
		</span>
	</div>
	
	<div class="card">
		<span class="card_header" style="background-color:#169ee1;">
			<span>{$attendance_status.no_shift|default:'0'}</span>
		</span>
		<span class="card_footer">
			<span>No Shift</span>
		</span>
	</div>
</div>

<p style="margin-left: 2%;"><b>Department Attendance Analysis</b></p>
<div id="div_chart_department_attendance_ratio" class="div_chart"></div>
<div id="div_employee_daily_working_ratio" class="div_chart2"></div>
<div id="div_recent_employee_daily_working_ratio" class="div_chart2"></div>

<script>
{literal}
ATTENDANCE_OVERVIEW.initialize();
{/literal}
</script>
{include file='footer.tpl'}