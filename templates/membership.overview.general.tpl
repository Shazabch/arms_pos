{*
4/5/2017 3:58PM Zhi Kai 
-adding notification 'Loading. Please wait...' while charts is rendering.

4/6/2017 9:23 AM Justin
- Modified to change the JQuery CSS and JS files.

4/6/2017 11:21AM Zhi Kai
- Changing output when no chart value to text output of 'No data'.
- Adding thousand separator to all membership amount value.

6/28/2017 4:45PM Zhi Kai
- Adding showing brand sales feature in open dialog.

7/10/2017 9:46AM Zhi Kai
- adding labelAngle for axis-x for brand sales.
- y-axis minimum and maximum are determined by the calculation in php for brand sales.

7/11/2017 2:23PM Zhi Kai
- Alter brand sales 'date from' to 1 week before the current date (It was 1 month before this change was made). 
- Only allow 'Loading.Please wait...' line to be processed when first enter/reload the page instead of every 60 seconds.
*}
{include file="header.tpl"}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<script type="text/javascript" src="js/canvasjs.min.js"></script>
<link type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

{literal}
<style>
div.div_chart{
	height: 310px; 
	width: 43%;
	border:1px solid white;
	float:left;
	margin: 0 0% 20px 4%;
}
{*slightly bigger box covering the top 2 chart*}
div.div_chart1{
	height: 325px; 
	width: 46%;
	
	float:left;
	margin: -5 0% 20px -45%;
}
{*a small box cover the age group chart*}
div.div_chart2{                              
	height: 310px;
	width: 45%;
	border: 1px solid white;
	float: left;
	margin: 10 10% 20px 26%;	
}
{*a big box cover the age group chart*}
div.div_chart3{                              
	height: 325px;
	width: 94.4%;
	
	float: left;
	margin: -335 4% 10px 2%;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var currency_symbol = '{$config.arms_currency.symbol}';
var last_week_date = '{"-6 day"|date_format:"%Y-%m-%d"}';
var curr_month_date = '{$smarty.now|date_format:"%Y-%m-%d"}';

{literal}

var JQ = {};
JQ = jQuery.noConflict(true);

var Overview={
	gender_chart:undefined,
	race_chart:undefined,
	age_chart:undefined,
	initialize:function(){
		this.reload_general_by_gender();	
		this.reload_general_by_race();
		this.reload_general_by_age();
		
		JQ('#columnDialog').dialog({
			width:1100,
			height:630,	
			autoOpen:false,
		});
		JQ("#date_from").datepicker({ dateFormat: 'yy-mm-dd' });
		JQ("#date_to").datepicker({ dateFormat: 'yy-mm-dd' });
	},
	
	reload_general_by_gender: function(){
		var THIS = this;
		if(this.gender_chart==undefined) JQ('#div_chart_general_by_gender').html('<br><div align="center">Loading.Please wait...</div>');

		var params = {
			a: 'get_gender_data'
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
			JQ('#div_chart_general_by_gender').html('');
			
		    try{
                ret = JQ.parseJSON(data); 
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_general_by_gender').html('Chart cannot be load, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{
		    	THIS.gender_chart = new CanvasJS.Chart("div_chart_general_by_gender",
			    {
			    	colorSet: "colorShades",
					title:{
						text: "Gender ",
						fontFamily: "arial",
						fontColor: "#CE0000",
						fontSize:20
					},
					legend:{
						verticalAlign: "center",
						horizontalAlign: "right",
						fontSize: 15,
						fontFamily: "arial",
					},
					animationEnabled: false,
			    });
			     
			    THIS.gender_chart.options.data = [];
			     
			    var entry = {
					type: "pie",       
					indexLabelFontFamily: "Arial",       
					indexLabelFontSize: 17,
					indexLabelFontWeight: "",
					indexLabelFontColor: "black",       
					indexLabelLineColor: "darkgrey", 
					//indexLabelMaxWidth: 5,
					//indexLabelAngle: 45,
					startAngle:0,      
					showInLegend: true,
					toolTipContent:"{label}",
					indexLabelPlacement: "outside",	

					dataPoints: []
			    };
				 
			    //dataPoints:[]
		    	 if(ret['gender_info']){
					for(var gender in ret['gender_info']){
						//entry.dataPoints = [];
						entry.dataPoints.push({
							y: ret['gender_info'][gender]['count'], 
							legendText: ret['gender_info'][gender]['gender_name']+": "+ret['gender_info'][gender]['count'].toLocaleString(), 
							label: ret['gender_info'][gender]['gender_name']+": "+ret['gender_info'][gender]['count'].toLocaleString(),        //include amount in the label
							indexLabel: ret['gender_info'][gender]['gender_name'],
							
							click : function(e){
								var gender = e.dataPoint.indexLabel;
								var att_id = 1;
								
								JQ("#columnDialog").dialog('option', 'title', 'Sales by Brand (click X to close)').dialog('open');
								
								THIS.reload_column_chart(gender,"gender",att_id);  
							},
							
						});
					//THIS.gender_chart.options.data.push(entry);
					}
				}else{
					JQ('#div_chart_general_by_gender').html('<br><div align="center">No Data</div>');
				}
			    THIS.gender_chart.options.data.push(entry);
			     
			    THIS.gender_chart.render();
			    JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
		    }
			setTimeout(function(){THIS.reload_general_by_gender()}, 60000);	// reload every X second
		});
	},
	
	reload_general_by_race: function(){
		var THIS = this;
		if(this.race_chart==undefined) JQ('#div_chart_general_by_race').html('<br><div align="center">Loading.Please wait...</div>');
		
		var params = {
			a: 'get_race_data'
		};
		CanvasJS.addColorSet("colorShades2",              
                [//colorSet Array
                "#e21212",
                "#1e8716",
                "#e5a40b",
                "#731fc1",
                //"#0bb6e5"                
                ]);
		
		JQ.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';
			var can_generate_chart = false;
			JQ('#div_chart_general_by_race').html('');
			
		    try{
                ret = JQ.parseJSON(data); 
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_general_by_race').html('Chart cannot be load, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{
		    	THIS.race_chart = new CanvasJS.Chart("div_chart_general_by_race",
			    {
			    	colorSet : "colorShades2",
					title:{
						text: "Ethnicity ",
						fontFamily: "arial",
						fontColor: "#CE0000",
						fontSize:20
					},
					legend:{
						verticalAlign: "center",
						horizontalAlign: "right",
						fontSize: 15,
						fontFamily: "arial"        
					},
					animationEnabled: false,
			    });
			     
			    THIS.race_chart.options.data = [];
			     
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
			    };
			    
		    	 if(ret['race_info']){
			     	for(var race in ret['race_info']) {	
			     		entry.dataPoints.push({
			     			y: ret['race_info'][race]['count'], 
			     			legendText: ret['race_info'][race]['race']+": "+ret['race_info'][race]['count'].toLocaleString(), 	//include amount in the legend
							label: ret['race_info'][race]['race']+": "+ret['race_info'][race]['count'].toLocaleString(),        //include amount in the label
			     			indexLabel: ret['race_info'][race]['race'],
							
							click : function(e){
								var race = e.dataPoint.indexLabel;
								var att_id = 1;
								
								JQ("#columnDialog").dialog('option', 'title', 'Sales by Brand (click X to close)').dialog('open');
								
								THIS.reload_column_chart(race,"race",att_id);  
							},
			     		});
					}
			    }else{
			     	JQ('#div_chart_general_by_race').html('<br><div align="center">No Data</div>');
			    }
			    THIS.race_chart.options.data.push(entry);
			    
			    THIS.race_chart.render();
			    JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
		    }
				setTimeout(function(){THIS.reload_general_by_race()}, 60000);	// reload every X second
		});
	},
	
	reload_general_by_age: function(){
		var THIS = this;
		if(this.age_chart==undefined) JQ('#div_chart_general_by_age').html('<br><div align="center">Loading.Please wait...</div>');
		
		var params = {
			a: 'get_age_data'
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
			JQ('#div_chart_general_by_age').html('');
			
		    try{
                ret = JQ.parseJSON(data); 
                if(ret['ok']){ // got 'ok' return mean save success
                	can_generate_chart = true;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}
			catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
			if(!err_msg)	err_msg = 'No Respond from Server.';
		    if(!can_generate_chart){
		    	JQ('#div_chart_general_by_age').html('Chart cannot be load, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{
		    	THIS.age_chart = new CanvasJS.Chart("div_chart_general_by_age",
			    {
					colorSet:  "colorShades3",        //sets of color for the data
					title:{
						text: "Age Group",
						fontFamily: "arial",
						fontColor: "#CE0000",
						fontSize:20
					},
					legend:{
						verticalAlign: "center",
						horizontalAlign: "right",
						fontSize: 15,
						fontFamily: "arial"        
					},
					animationEnabled: false,
			    });
			     
			     THIS.age_chart.options.data = [];
			     
			     var entry = {
			     	type: "doughnut",       
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
			     };
				 
		    	 if(ret['age_info']){
			     	for(var age in ret['age_info']) {	
			     		entry.dataPoints.push({
			     			y: ret['age_info'][age]['count'], 
			     			legendText: ret['age_info'][age]['age_name']+": "+ret['age_info'][age]['count'].toLocaleString(),
							
							label: ret['age_info'][age]['age_name']+": "+ret['age_info'][age]['count'].toLocaleString(),        //include amount in the label
							
			     			indexLabel: ret['age_info'][age]['age_name'],
							
							click : function(e){
								var age = e.dataPoint.indexLabel;
								var att_id = 1;
							
								JQ("#columnDialog").dialog('option', 'title', 'Sales by Brand (click X to close)').dialog('open');
							
								THIS.reload_column_chart(age,"age",att_id);  
							},
							
			     		});
					}
				}else{
					JQ('#div_chart_general_by_age').html('<br><div align="center">No Data</div>');
				}
			    THIS.age_chart.options.data.push(entry);
			     
			    THIS.age_chart.render();
			    JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
		    }
			setTimeout(function(){THIS.reload_general_by_age()}, 60000);	// reload every X second
		});
	},
	
	reload_column_chart: function(b_value,b_type,att_id){
		var THIS = this;
		var bid=0, datefrom=0, dateto=0, dateChecking =/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{2})$/;
		JQ('#columncontainer').html('<br><div align="center">Loading.Please wait...</div>');
		
		if(att_id == 1){					
			datefrom = last_week_date;
			dateto = curr_month_date;
			b_value = b_value.trim();
		}
		else{
			b_type = JQ("#cat_ctype").val();
			if(b_type =='gender')
				b_value= JQ("#column_gender").val();
			else if(b_type =='race')
				b_value= JQ("#column_race").val();
			else if(b_type =='age')
				b_value= JQ("#column_age").val();
			
			bid=JQ("#branchcolumn").val();
			datefrom= JQ("#date_from").val();
			dateto= JQ("#date_to").val();
		}
		
		if(datefrom == '' || dateto == '' ){
			JQ('#columncontainer').html('<br><div align="center"><b>Error:Please enter Date.</b></div>');
			return;
		}
		else if (dateChecking.test(datefrom) == false || dateChecking.test(dateto) == false){
			JQ('#columncontainer').html('<br><div align="center"><b>Error:Invalid date format.</b></div>');
			return;
		}
		else if (datefrom>dateto){
			JQ('#columncontainer').html('<br><div align="center"><b>Error:Date from cannot be newer than Date to.</b></div>');
			return;
		}
	
		var params = {
			a: 'load_column_chart',
			b_type : b_type,
			b_value : b_value,
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
			JQ('#columncontainer').html('');
			
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
		    	JQ('#columncontainer').html('Sales cannot be loaded, please contact System Admin.<br><br> Error Trace:<br>'+err_msg);
		    	return;
		    }
			else{
				if(ret['ok']){
					if(att_id==1){
						//branch.value
						if(b_type == "gender")
							$('column_gender').value = b_value;
						else if(b_type == "race")
							$('column_race').value = b_value;
						else if(b_type == "age"){
							$('column_age').value = b_value;
						}
							
						$('date_from').value = datefrom;
						$('date_to').value = dateto;
						$('cat_ctype').value = b_type;
					}
					
					if(b_type=="gender"){
						$('span_gender').show();
						$('span_race').hide();
						$('span_age').hide();
					}
					else if(b_type=="race"){
						$('span_gender').hide();
						$('span_race').show();
						$('span_age').hide();
					}
					else if(b_type=="age"){
						$('span_gender').hide();		
						$('span_race').hide();		
						$('span_age').show();		
					}
					
					THIS.column_chart = new CanvasJS.Chart("columncontainer",
					{
						colorSet:  "colorShades4",
						title:{
							text: "",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							verticalAlign: "bottom",
							horizontalAlign: "center",
							fontSize: 15,
							fontFamily: "arial"   ,
							
						},  
						axisY:{
							title: "Gross Sales (" + currency_symbol + ")",
							maximum:ret['max_value'],
							minimum:ret['min_value'],
						},
						axisX: {
							title: "Brands",
							labelFontSize: 14,
							labelAngle: -45
						},
					}); 
		
					// start populate
					THIS.column_chart.options.data = [];

					////////
					if(ret['brand_sales']){
						var entry = {
							type: "column"
						}
						
						entry.dataPoints = [];

						for (var i=0; i<10; i++){
							if(ret['brand_sales'][i] != undefined){
								entry.dataPoints.push({
									y: ret['brand_sales'][i]['total'],
									label: ret['brand_sales'][i]['brand_desc'],
									//legendText: ret['brand_sales'][i]['brand_desc'],
									toolTipContent: ret['brand_sales'][i]['brand_desc']+" : "+ret['brand_sales'][i]['total'].toLocaleString(undefined,{minimumFractionDigits: 2}),
								});
							}
						}
						THIS.column_chart.options.data.push(entry);
					}
					else{
						JQ('#columncontainer').html('<br><div align="center">No Sales Data</div>');			
					}
					THIS.column_chart.render();
					JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				}
			}
		});	
	},
}
	
{/literal}
</script>

{* display chart by Gender *}
<div id="div_chart_general_by_gender" class="ui-corner-all div_chart"></div>
<div class="ui-corner-all div_chart1"></div>

{* display chart by race*}
<div id="div_chart_general_by_race" class="ui-corner-all div_chart"></div>
<div class="ui-corner-all div_chart1"></div>

{* display chart by age*}
<div id="div_chart_general_by_age" class="ui-corner-all div_chart2"></div>
<div class="ui-corner-all div_chart3"></div>

{*display column chart showing brand sales*}
<div id="columnDialog" style="display:none;" >
	<h2 align="center">Sales by Brand ({$config.arms_currency.symbol})</h2>
		<p>
		<input type="hidden" name="a" value="" />
			{if BRANCH_CODE eq 'HQ'}
				<b>Branch :</b>
				<select id="branchcolumn" >
					<option value="">--All--</option>
					{foreach from=$brn key=bid item=bn}
						<option value="{$bid}">{$bn}</option>
					{/foreach}
				</select>		
			{/if}
			&nbsp;&nbsp;&nbsp;
			<span id="span_gender" style="display:none;">
				<b>Gender :</b>
				<select id="column_gender">
					<option value="">--All--</option>
					{foreach from=$gen key=gid item=gn}
						<option value="{$gn}">{$gn}</option>
					{/foreach}
				</select>
			</span>
			<span id="span_race" style="display:none;">
				<b>Ethnicity :</b>
				<select id="column_race">
					<option value="">--All--</option>
					{foreach from=$rc key=rid item=r}
						<option value="{$r}">{$r}</option>
					{/foreach}
				</select>
			</span>
			<span id="span_age" style="display:none;">
				<b>Age :</b>
				<select id="column_age">
					<option value="">--All--</option>
					{foreach from=$age key=aid item=a}
						<option value="{$a}">{$a}</option>
					{/foreach}
				</select>
			</span>
			
			<input type='text' style='position:relative;top:-500px;width:1px' />
			<b>Date From :</b>
			<input size="10" type="text" value="{$smarty.request.cat_date_from}" name="date_from" id="date_from" placeholder="yyyy-mm-dd" >
			&nbsp;
			<b>To</b> 
			<input size="10" type="text" value="{$smarty.request.date_to}" name="cat_date_to"  id="date_to" placeholder="yyyy-mm-dd">
			&nbsp;&nbsp;
			<input type="button" value="Refresh" id="refresh_btn" onclick="Overview.reload_column_chart();"> 
			<input type="hidden" id="cat_ctype" value="" />
			</br></br>
		</p>
		</br>
	<div id="columncontainer" ></div>
</div>


	
<script type="text/javascript">
{literal}
JQ(function(){
	Overview.initialize();              //load the above var Overview
});
{/literal}
</script>
{include file="footer.tpl"}