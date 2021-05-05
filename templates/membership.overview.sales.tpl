{*
4/5/2017 4:01PM Zhi Kai 
-Changing the alignment of 'Loading. Please wait...' to center.
-altering default year and month when first entering to current year and month.

4/6/2017 9:23 AM Justin
- Modified to change the JQuery CSS and JS files.

4/6/2017 9:48AM Zhi Kai
- increase the width of age group chart.

9:58 AM 4/6/2017 Justin
- Bug fixed on Sales by Age Group too small.
- Added new note.

12:01PM 4/6/2017 Zhi Kai
-Changing chart name from 'By Race' to 'By Ethnicity'.

4/28/2017 8:05 AM Khausalya
- Enhanced changes from RM to use config settings. 

5/18/2017 11:11 AM Zhi Kai
- Enhance the chart to have popup dialog showing the third level category sales.

5/18/2017 4:58 PM Justin
- Bug fixed on piechart still showing RM when config currency symbol has been changed.

5/22/2017 11:27 AM Justin
- Bug fixed on piechart showing description too long and causing the piechart become smaller.

6/12/2017 4:47PM Zhi Kai
- Enhancement on showing sku sales table when clicking on dialog's pie chart.
*}
{include file="header.tpl"}
<h1>{$PAGE_TITLE}</h1>
<script type="text/javascript" src="js/canvasjs.min.js"></script>
<link type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>


{literal}
<style>
div.div_chart{
	height: 340px; 
	width: 43%;
	border:1px solid white;
	float:left;
	margin: 0 0% 20px 4%;
}
{*slightly bigger box covering the top 2 chart*}
div.div_chart1{
	height: 355px; 
	width: 45%;
	border:1px solid black;
	float:left;
	margin: -5 0% 20px -44%;
}
{*a small box cover the age group chart*}
div.div_chart2{                              
	height: 330px;
	width: 80%;
	border: 1px solid white;
	float: left;
	margin: 10 10% 20px 10%;	
}
{*a big box cover the age group chart*}
div.div_chart3{                              
	height: 345px;
	width: 93.3%;
	border: 1px solid black;
	float: left;
	margin: -359 4% 10px 3.2%;
}

{*.cTable td{
text-align:center;
}              *}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var currency_symbol = '{$config.arms_currency.symbol}';

{literal}

var JQ= {};
JQ = jQuery.noConflict(true);

var HOME = {
	race_chart: undefined,
	gender_chart:undefined,
	age_chart: undefined,
	pie_chart: undefined,
	
	
	initialize: function(){
		this.reload_race_sales();
		this.reload_gender_sales();
		this.reload_age_sales();
		
		JQ('#chartDialog').dialog({
			width:720,
			height:610,	
			autoOpen:false,
		});
		JQ('#tableDialog').dialog({
			width:720,
			height:610,	
			autoOpen:false,
		});
		JQ("#cat_date_from").datepicker({ dateFormat: 'yy-mm-dd' });
		JQ("#cat_date_to").datepicker({ dateFormat: 'yy-mm-dd' });
		JQ("#table_date_from").datepicker({ dateFormat: 'yy-mm-dd' });
		JQ("#table_date_to").datepicker({ dateFormat: 'yy-mm-dd' });
	},
	
	hide:function(){               //to hide the dialog box
		JQ("#chartDialog").dialog("close");
		JQ("#tableDialog").dialog("close");
	},
	
	reload_race_sales: function(){
		var THIS = this;
		
		JQ('#div_chart_sales_by_race').html('<br><div align="center">Loading.Please wait...</div>');
		
		var params = {
			a: 'ajax_get_sales_data',
			chart_type: 'race_sales',
			year: JQ("#year").val(),
			month: JQ("#month").val(),
			branch: JQ("#branch").val(),
		};
		CanvasJS.addColorSet("colorShades2",              
                [//colorSet Array
					"#e21212",
					"#1e8716",
					"#e5a40b",
					"#731fc1"
                ]);
		
		JQ.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';
			var can_generate_chart = false;
			JQ('#div_chart_sales_by_race').html('');
			
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

			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_sales_by_race').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{	
				if(ret['ok']){
	            	THIS.race_chart = new CanvasJS.Chart("div_chart_sales_by_race",
				    {
				    	colorSet : "colorShades2",
						title:{
							text: "By Ethnicity ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "Gross Sales (" + currency_symbol + ")",
							minimum: 0
						},
						axisX: {
							title: "Day",
							intervalType:"day",
						},
			      	});  
			      	
			      	// start populate
					THIS.race_chart.options.data = [];
			
					////////
					if(ret['member_sales']){
						for(var race in ret['race_list']){	
							var entry = {        
								type: "line",
							    showInLegend: true,
								legendText: ret['race_list'][race]['race_name'],
							}
							
							entry.dataPoints = [];
							for(var date in ret['member_sales'][race]){
								entry.dataPoints.push({
									x:new Date(ret['member_sales'][race][date]['year'],
												ret['member_sales'][race][date]['month']-1, 
												ret['member_sales'][race][date]['day']),
									y: (ret['member_sales'][race][date]['gross_amount']),
									toolTipContent:  date+" : "+ret['member_sales'][race][date]['gross_amount'].toLocaleString(undefined,{minimumFractionDigits: 2}),
									label: date,
									click : function(e){
										var race = e.dataSeries.legendText;
										var c_date = e.dataPoint.label;
										var att_id =1;
										
										JQ("#chartDialog").dialog('option', 'title', 'Ethnicity Sales by Category => 3rd Level (click X to close)').dialog('open');
										
										THIS.reload_pie_chart(c_date,race, "race" , att_id);  //pass parameter here   (date , male , gender) (date, chinese , race)
									},
								});
						    }
							THIS.race_chart.options.data.push(entry);
						}
					}else{
						JQ('#div_chart_sales_by_race').html('<br><div align="center">No Sales Data</div>');	
					}
					
			      	THIS.race_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				}
		    }
		});
	},
	
	reload_gender_sales: function(){
		var THIS = this;
		
		JQ('#div_chart_sales_by_gender').html('<br><div align=\center>Loading.Please wait...</div>');
		var params = {
			a: 'ajax_get_sales_data',
			chart_type: 'gender_sales',
			year: JQ("#year").val(),
			month: JQ("#month").val(),
			branch: JQ("#branch").val(),
		};
		CanvasJS.addColorSet("colorShades",              
                [//colorSet Array
					"#866ead",
					"#1de20f",
					"#e5a40b"                
                ]);
		
		JQ.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';
			var can_generate_chart = false;
			JQ('#div_chart_sales_by_gender').html('');

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

			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_sales_by_gender').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{	//if receive data
				if(ret['ok']){
	            	THIS.gender_chart = new CanvasJS.Chart("div_chart_sales_by_gender",
				    {
				    	colorSet: "colorShades",
						title:{
							text: "By Gender ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "Gross Sales (" + currency_symbol + ")",
							//minimum: 0
						},
						axisX: {
							title: "Day",
							intervalType:"day",
						},
			      	});  
			      	
					THIS.gender_chart.options.data = [];

					if(ret['member_sales']){
						for(var gender in ret['gender_list']){	
							var entry = {
								type: "stackedColumn",
							    showInLegend: "true",
								legendText: ret['gender_list'][gender]['gender_name'],
							}
							
							entry.dataPoints = [];
								
							for(var date in ret['member_sales'][gender]){
								entry.dataPoints.push({
									x:new Date(ret['member_sales'][gender][date]['year'],
												ret['member_sales'][gender][date]['month']-1, 
												ret['member_sales'][gender][date]['day']),
											
									y: ret['member_sales'][gender][date]['gross_amount'],
									toolTipContent:  date+" : "+ret['member_sales'][gender][date]['gross_amount'].toLocaleString(undefined,{minimumFractionDigits: 2}),
									label: date,
									
									click : function(e){
										var gender = e.dataSeries.legendText;
										var c_date = e.dataPoint.label;
										var att_id = 1;
								
										JQ("#chartDialog").dialog('option', 'title', 'Gender Sales by Category => 3rd Level (click X to close)').dialog('open');
										
										THIS.reload_pie_chart(c_date,gender, "gender" , att_id);  //pass parameter here   (date , male , gender) (date, chinese , race)
									},
									
								});
								
						    }
							THIS.gender_chart.options.data.push(entry);
						}
					}else{
						JQ('#div_chart_sales_by_gender').html('<br><div align="center">No Sales Data</div>');				  
					}
					
			      	THIS.gender_chart.render();
					
					JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				}
		    }
			
		});
	},

	
	reload_age_sales: function(){
		var THIS = this;
		
		JQ('#div_chart_sales_by_age').html('<br><div align="center">Loading.Please wait...</div>');
		var params = {
			a: 'ajax_get_sales_data',
			chart_type: 'age_sales',
			year: JQ("#year").val(),
			month: JQ("#month").val(),
			branch: JQ("#branch").val(),
		};
		CanvasJS.addColorSet("colorShades3",              
                [//colorSet Array
					"#1e8716",
					"#e5740b",
					"#0bb6e5",
					"#b22121",
					"#d6d324"                
                ]);
		
		JQ.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';
			var can_generate_chart = false;
			JQ('#div_chart_sales_by_age').html('');
			
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

			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_sales_by_age').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{	
				if(ret['ok']){
	            	THIS.age_chart = new CanvasJS.Chart("div_chart_sales_by_age",
				    {
						colorSet:  "colorShades3",
						title:{
							text: "By Age Group ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "Gross Sales (" + currency_symbol + ")",
							//minimum: 0
						},
						axisX: {
							title: "Day",
							intervalType:"day",
						},
			      	});  
			      	
			      	// start populate
					THIS.age_chart.options.data = [];
			
					////////
					if(ret['member_sales']){
						
						for(var age in ret['age_list']){	
							var entry = {        
								type: "column",
							    showInLegend: true,
								legendText: ret['age_list'][age]['age_name'],
							}
							
							entry.dataPoints = [];
							for(var date in ret['member_sales'][age]){
								entry.dataPoints.push({
									x:new Date(ret['member_sales'][age][date]['year'],
												ret['member_sales'][age][date]['month']-1, 
												ret['member_sales'][age][date]['day']),
											
									y: ret['member_sales'][age][date]['gross_amount'],
									toolTipContent:  date+" : "+ret['member_sales'][age][date]['gross_amount'].toLocaleString(undefined,{minimumFractionDigits: 2}),
									label: date,
									click : function(e){
										var age = e.dataSeries.legendText;
										var c_date = e.dataPoint.label;
										var att_id = 1;
										
										JQ("#chartDialog").dialog('option', 'title', 'Age Group Sales by Category => 3rd Level (click X to close)').dialog('open');
										
										THIS.reload_pie_chart(c_date,age, "age" , att_id);  //pass parameter here   (date , male , gender) (date, chinese , race)
									},
								});
						    }
							THIS.age_chart.options.data.push(entry);
						}
					}else{
						JQ('#div_chart_sales_by_age').html('<br><div align="center">No Sales Data</div>');
					}
					
			      	THIS.age_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				}	
		    }
		});
	},
	
	reload_pie_chart:function(c_date, c_value, c_type, att_id){     
		var THIS = this;
		JQ("#tableDialog").dialog("close");
		JQ('#chartcontainer2').html('<br><div align="center">Loading.Please wait...</div>');
		var bid = 0, datefrom = 0, dateto = 0 , dateChecking =/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{2})$/; 
		
		if(att_id == 1){          //att_id is passed from the stackcolumn/line/column chart
			bid = JQ("#branch").val();
			datefrom = c_date;
			dateto = c_date;
		}else{
			c_type = JQ("#cat_ctype").val();
			if(c_type == 'gender') c_value = JQ("#cat_gender").val();
			else if(c_type == 'race') c_value = JQ("#cat_race").val();
			else c_value = JQ("#cat_age").val();
			
			bid = JQ("#branchpie").val();
			datefrom = JQ("#cat_date_from").val();
			dateto = JQ("#cat_date_to").val();
		}
		
		if(datefrom == '' || dateto == '' ){
			JQ('#chartcontainer2').html('<br><div align="center"><b>Error:Please enter Date.</b></div>');
			return;
		}
		else if (dateChecking.test(datefrom) == false || dateChecking.test(dateto) == false){
			JQ('#chartcontainer2').html('<br><div align="center"><b>Error:Invalid date format.</b></div>');
			return;
		}
		else if (datefrom>dateto){
			JQ('#chartcontainer2').html('<br><div align="center"><b>Error:Date from cannot be newer than Date to.</b></div>');
			return;
		}
		
		var params ={
			a: 'get_pie_data',
			c_type : c_type,
			c_value : c_value,
			bid : bid,
			datefrom : datefrom,
			dateto : dateto
		};
		
		CanvasJS.addColorSet("colorShades4",              
                [//colorSet Array
                "#4286f4",
                "#f49542",
                "#42f495",
                "#f90e26",
                "#7a42f4",
                "#722113",
                "#6bd7db",
                "#67676d",
                "#f442aa",
                "#e5f442",	
                ]);
		JQ.post(phpself, params, function(data){
			var ret = {};
			var err_msg = '';
			var can_generate_chart = false;
			JQ('#chartcontainer2').html('');
			
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
			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#chartcontainer2').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{	
				if(att_id == 1){
					if($('branch') != undefined) $('branchpie').value = $('branch').value; //means in HQ
					$('cat_date_from').value = c_date;
					$('cat_date_to').value = c_date;
					$('cat_ctype').value = c_type;
					
					if(c_type == "gender"){ 
						$('cat_gender').value = c_value;
					}else if(c_type == "race"){
						$('cat_race').value = c_value;
					}else $('cat_age').value = c_value;
				}
				if(c_type == "gender"){ 
					$('span_gender').show();
					$('span_race').hide();
					$('span_age').hide(); 
				}
				else if(c_type == "race"){
					$('span_gender').hide();
					$('span_race').show();                    
					$('span_age').hide();
				}
				else if(c_type == "age"){
					$('span_gender').hide();
					$('span_race').hide();                    
					$('span_age').show();
				}
				
				THIS.pie_chart = new CanvasJS.Chart("chartcontainer2", 
				{   
					colorSet:  "colorShades4",
					title:{
						text: "",
						fontFamily: "arial",
						fontColor: "#CE0000",
						fontSize:17
					},
				});
				
				THIS.pie_chart.options.data = [];
				
				var entry={
					type: "pie",       
					indexLabelFontFamily: "Arial",       
					indexLabelFontSize: 15,
					indexLabelFontWeight: "",
					indexLabelFontColor: "black",       
					indexLabelLineColor: "darkgrey", 
					startAngle:0,      
					showInLegend: true,
					toolTipContent:"{label}",                     
					indexLabelPlacement: "outside",
					dataPoints: []
				};
				if (ret['cat_sales']){     
					/*for (var lv3_cat_id in ret['cat_sales']){    
						entry.dataPoints.push({
							y: ret['cat_sales'][lv3_cat_id]['total'],
							legendText: ret['cat_sales'][lv3_cat_id]['cat_name'],
							label: ret['cat_sales'][lv3_cat_id]['cat_name']+": "+ret['cat_sales'][lv3_cat_id]['total'].toLocaleString(undefined,{minimumFractionDigits: 2}),
							indexLabel: ret['cat_sales'][lv3_cat_id]['cat_name'],
						});      
					}*/
					for(var i=0; i<10; i++){
						if(ret['cat_sales'][i] != undefined){
							entry.dataPoints.push({
								y: ret['cat_sales'][i]['total'],
								name: ret['cat_sales'][i]['category_id'],             //the legend will be overridden below
								legendText: ret['cat_sales'][i]['cat_full_name'],
								label: ret['cat_sales'][i]['cat_full_name']+": "+ret['cat_sales'][i]['total'].toLocaleString(undefined,{minimumFractionDigits: 2}),
								indexLabel: ret['cat_sales'][i]['cat_name'],		

								click : function(e){
									var cid = e.dataPoint.name;
									var att_id = 2;
									
									JQ("#tableDialog").dialog('option', 'title', '3rd level Category Sales Report (click X to close)').dialog('open');
									
									THIS.reload_table_report(c_type,c_value,datefrom,dateto,cid,att_id);  
								},
							});
						}
					}
				}else{
					JQ('#chartcontainer2').html('<br><div align="center">No Sales Data</div>');
				}
				
				THIS.pie_chart.options.data.push(entry);
				THIS.pie_chart.render();
				JQ('a[href="http://canvasjs.com/"]').remove();	
			}
		});
			 
	},
	
	reload_table_report:function(c_type,c_value,datefrom,dateto,cid,att_id){
		var THIS = this;
		var tableDatefrom=0 , tableDateto=0, dateChecking =/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{2})$/ ; 
		
		if(att_id == 2){   //if it is passing via clicking on pie chart
			bid = JQ("#branchpie").val();
			tableDatefrom= datefrom;   
			tableDateto= dateto;
		}else{      //passing the value via select and drop down list
			c_type = JQ("#cat_ctype").val();  
			if(c_type == 'gender') c_value = JQ("#table_gender").val();  
			else if(c_type == 'race') c_value = JQ("#table_race").val();
			else c_value = JQ("#table_age").val();
		
			bid = JQ("#branchpie").val();  
			tableDatefrom = JQ("#table_date_from").val();
			tableDateto = JQ("#table_date_to").val();
			cid = JQ("#cid").val();
		}
	
		JQ('#myTablebig').html('<br><div align="center">Loading.Please wait...</div>');
		
		if(tableDatefrom == '' || tableDateto == '' ){
			JQ('#myTablebig').html('<br><div align="center" border="0"><b>Error:Please enter Date.</b></div>');
			return;
		}
		else if (dateChecking.test(tableDatefrom) == false || dateChecking.test(tableDateto) == false){
			JQ('#myTablebig').html('<br><div align="center"><b>Error:Invalid date format.</b></div>');
			return;
		}
		else if (tableDatefrom>tableDateto){
			JQ('#myTablebig').html('<br><div align="center"><b>Error:Date from cannot be newer than Date to.</b></div>');
			return;
		}
		
		var params = {
			a: 'get_table_data',
			c_type : c_type,
			c_value : c_value,
			bid : bid,
			datefrom : tableDatefrom,
			dateto : tableDateto,
			cid : cid,
		};
		
		JQ.post(phpself, params, function(data){
			var ret = {};
			var err_msg = '';
			var can_generate_table = false;
			JQ('#tablecontainer').html('');
			
			try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                	JQ('#tablecontainer').html(ret['html']);
					can_generate_table = true;
				}
				else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_table){
		    	JQ('#tablecontainer').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }else{ 
				//retain value in html select option even after clicking on refresh button
				$('table_date_from').value = tableDatefrom;
				$('table_date_to').value = tableDateto;
				$('cat_ctype').value = c_type;
				$('cid').value = cid;   
				
				if(c_type == "gender"){ 
					$('table_gender').value = c_value;
				}else if(c_type == "race"){
					$('table_race').value = c_value;
				}else $('table_age').value = c_value;
				
				$('myTable').show(); 
			}
		});
	},
}

{/literal}
</script>

<p>
<input type="hidden" name="a" value="get_axis_data" />
{if BRANCH_CODE eq 'HQ'}
	<b>Branch : </b>
	<select id="branch" >
		<option value="">--All--</option>
		{foreach from=$brn key=bid item=bn}
			<option value="{$bid}">{$bn}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;
{/if}
<b>Year : </b>
<select id="year">
	{foreach from=$yea key=yea item=yr}
		<option value="{$yea}" {if $year eq $yea}selected{/if}>{$yr}</option>
	{/foreach}
</select>
&nbsp;&nbsp;&nbsp;
<b>Month: </b>
<select id="month">
	{foreach from=$mon key=mon item=mn}
		<option value="{$mon}" {if $month eq $mon}selected{/if}>{$mn}</option>
	{/foreach}
</select>
&nbsp;&nbsp;&nbsp;

<input type="button" value="Refresh" id="refresh_btn" onclick="HOME.reload_race_sales();HOME.reload_gender_sales();HOME.reload_age_sales();HOME.hide();"> 

<ul>
<li><b>This module shows finalised sales only.</b></li>
</ul>
</p>

<div id="div_chart_sales_by_gender" class="ui-corner-all div_chart"></div>
<div class="ui-corner-all div_chart1"></div>

<div id="div_chart_sales_by_race" class="ui-corner-all div_chart"></div>
<div class="ui-corner-all div_chart1"></div>

<div id="div_chart_sales_by_age" class="ui-corner-all div_chart2"></div>
<div class="ui-corner-all div_chart3"></div>

<div id="chartDialog" style="display:none;" >
		<h2 align="center">Sales by Category => Third Level ({$config.arms_currency.symbol})</h2>
		<p>
			<input type="hidden" name="a" value="get_axis_data" />
			{if BRANCH_CODE eq 'HQ'}
				<b>Branch :</b>
				<select id="branchpie" >
					<option value="">--All--</option>
					{foreach from=$brn key=bid item=bn}
						<option value="{$bid}">{$bn}</option>
					{/foreach}
				</select>		
			{/if}
			&nbsp;&nbsp;&nbsp;
			<span id="span_gender" style="display:none;">
				<b>Gender :</b>
				<select id="cat_gender">
					<option value="">--All--</option>
					{foreach from=$gen key=gid item=gn}
						<option value="{$gn}">{$gn}</option>
					{/foreach}
				</select>
			</span>
			<span id="span_race" style="display:none;">
				<b>Ethnicity :</b>
				<select id="cat_race">
					<option value="">--All--</option>
					{foreach from=$rc key=rid item=r}
						<option value="{$r}">{$r}</option>
					{/foreach}
				</select>
			</span>
			<span id="span_age" style="display:none;">
				<b>Age :</b>
				<select id="cat_age">
					<option value="">--All--</option>
					{foreach from=$age key=aid item=a}
						<option value="{$a}">{$a}</option>
					{/foreach}
				</select>
			</span>
			 
			<input type='text' style='position:relative;top:-500px;width:1px' />
			<b>Date From :</b>
			<input size="10" type="text" value="{$smarty.request.cat_date_from}" name="cat_date_from" id="cat_date_from" placeholder="yyyy-mm-dd" >
			&nbsp;
			<b>To</b> 
			<input size="10" type="text" value="{$smarty.request.cat_date_to}" name="cat_date_to"  id="cat_date_to" placeholder="yyyy-mm-dd">
			&nbsp;&nbsp;
			<input type="button" value="Refresh" id="refresh_btn2" onclick="HOME.reload_pie_chart();"> 
			<input type="hidden" id="cat_ctype" value="" />
		</p>
		
		<div id="chartcontainer2"></div>		
</div>

<div id="tableDialog" style="display:none;" >
<div id="tablecontainer" ></div>

{*the table th start*}
</div>			


<script type="text/javascript">
{literal}
JQ(function(){
	HOME.initialize();
});
{/literal}
</script>
{include file="footer.tpl"}