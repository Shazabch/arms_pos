{*
7/3/2017 11:14 AM Justin
- Modified to change the wording from "Vendor" to "Master Vendor".

7/5/2017 1:44 PM Justin
- Enhanced to make the font size larger.

4/12/2018 9:45 AM Kuan Yeh
- Enhanced to added total purchase by YTD and MTD 
- Enhanced to added total sales by YTD and MTD 

5/2/2018 1:00 PM Kuan Yeh
- Enhanced KPI table style and alignment
- Fixed table bug

11/26/2018 3:05 PM Justin
- Enhanced to have Quotation Cost information.

12/20/2018 4:06 PM Justin
- Enhanced to have Selling Price history dialog.

05/13/2020 6:45PM Sheila
- Updated css for links in the table

06/26/2020 03:35 Sheila
- Updated button css
*}

{include file='header.tpl'}

<script type="text/javascript" src="js/canvasjs.min.js"></script>
<link type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

{literal}
<style>
div.div_container{
	border:1px solid black;
	height: 100%;
	width: 100%;
}

div.div_chart{
	height: 400px; 
	width: 90%;
	border:1px solid black;
	padding: 10px;
}

div.div_chart_content{
	height: 330px;
}

div.div_sku_image{
	border: 1px solid black;
	width: 200px; 
	height: 200px;
	background-color: white;
}

div.div_no_image{
	text-align:center;
	line-height:200px;
	font-weight:bold;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var curr_bid = '{$sessioninfo.branch_id}';
{literal}
var JQ= {};
JQ = jQuery.noConflict(true);

var SKU_SUMMARY = {
	f: undefined,
	f_chart_sales: undefined,
	chart_sales: undefined,
	chart_gp: undefined,
	chart_purchase: undefined,	
	initialize: function(){
		this.f = document.f_a;
		this.f_chart_sales = document.f_chart_sales;
		
		// load sales chart
		this.reload_sales_chart();
	},
	// function when user changed branch
	branch_changed: function(){
		this.f.submit();
	},
	// function when user click on compare
	show_sales_compare: function(){
		this.reload_sales_chart(1);
	},
	// function to reload sales chart
	reload_sales_chart: function(compare_sales){
		var params = JQ(this.f).serialize()+'&'+JQ(this.f_chart_sales).serialize()+'&compare_sales='+compare_sales;
		JQ('#div_chart_sales_content').html('<img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...');
		JQ('#div_chart_gp_content').html('<img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...');
		JQ('#div_chart_purchase_content').html('<img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...');
		var THIS = this;
		
		JQ.post(phpself+'?a=reload_sales_chart', params, function(data){
		    var ret = {};
		    var err_msg = '';
			var can_generate_chart = false;
			
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

			if(!can_generate_chart){
				if(!err_msg)	err_msg = "Unknown Error";
				JQ('#div_chart_sales_content').html(err_msg);
				JQ('#div_chart_gp_content').html(err_msg);
				JQ('#div_chart_purchase_content').html(err_msg);
			}else{
				JQ('#div_chart_sales_content').html('');
				JQ('#div_chart_gp_content').html('');
				JQ('#div_chart_purchase_content').html('');
				
				// sales chart
				THIS.chart_sales = new CanvasJS.Chart("div_chart_sales_content",
				    {
				    	colorSet: "colorShades",
						title:{
							text: "Sales ",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "Gross Sales (" + ARMS_CURRENCY.symbol + ")",
							//minimum: 0
						},
						axisX: {
							title: "Month",
							//intervalType:"month",
							//interval: 3,
						},
			      	});  
			      	
				THIS.chart_sales.options.data = [];

				if(ret['item_sales']){
					for(var sid in ret['sid_list']){
						var entry = {
							type: "stackedColumn",
							showInLegend: "true",
							legendText: ret['sid_list'][sid]['sku_desc'],
							color: ret['sid_list'][sid]['color']
						}
						
						entry.dataPoints = [];
							
						for(var key in ret['item_sales'][sid]){
							//var y = date.split('-')[0];
							//var m = date.split('-')[1];
							//var d = date.split('-')[2];
							entry.dataPoints.push({
//									x: m,
								y: ret['item_sales'][sid][key]['gross_amount'],
								toolTipContent:  ret['item_sales'][sid][key]['label']+" : "+ret['item_sales'][sid][key]['gross_amount'].toLocaleString(undefined,{minimumFractionDigits: 2}),
								label: ret['item_sales'][sid][key]['label'],
							});
						}
						
						THIS.chart_sales.options.data.push(entry);
					}
				}else{
					JQ('#div_chart_sales_content').html('<br><div align="center">No Data</div>');				  
				}
				
				THIS.chart_sales.render();
				
				//JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				
				// Gross Profit
				THIS.chart_gp = new CanvasJS.Chart("div_chart_gp_content",
				    {
				    	colorSet : "colorShades2",
						title:{
							text: "Profit",
							fontFamily: "arial",
							fontColor: "#CE0000",
							fontSize:16
						},
						legend:{
							fontSize: 15,
							fontFamily: "arial" 
						},
						axisY:{
							title: "Amount (" + ARMS_CURRENCY.symbol + ")",
							minimum: 0
						},
						axisX: {
							title: "Month",
						},
			      	});  
			      	
				// start populate
				THIS.chart_gp.options.data = [];
		
				////////
				if(ret['item_sales']){
					for(var sid in ret['sid_list']){	
						var entry = {        
							type: "line",
							showInLegend: true,
							legendText: ret['sid_list'][sid]['sku_desc'],
							color: ret['sid_list'][sid]['color']
						}
						
						entry.dataPoints = [];
						for(var key in ret['item_sales'][sid]){
							entry.dataPoints.push({
								//x: key
								y: (ret['item_sales'][sid][key]['gp']),
								toolTipContent:  ret['item_sales'][sid][key]['label']+" : "+ret['item_sales'][sid][key]['gp'].toLocaleString(undefined,{minimumFractionDigits: 2}),
								label: ret['item_sales'][sid][key]['label']
							});
						}
						THIS.chart_gp.options.data.push(entry);
					}
				}else{
					JQ('#div_chart_sales_by_race').html('<br><div align="center">No Sales Data</div>');	
				}
				
				THIS.chart_gp.render();
				//JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
				
				// Purchase
				THIS.chart_purchase = new CanvasJS.Chart("div_chart_purchase_content",
			    {
			    	colorSet: "colorShades",
					title:{
						text: "Purchase",
						fontFamily: "arial",
						fontColor: "#CE0000",
						fontSize:20
					},
					legend:{
						fontSize: 15,
						fontFamily: "arial",
					},
					animationEnabled: false,
			    });
			     
			    THIS.chart_purchase.options.data = [];
			     
			    var entry = {
					type: "pie",       
					startAngle:0,      
					showInLegend: true,
					dataPoints: []
			    };
				 
		    	if(ret['grn'] && ret['grn']['vendor_id_list']){
					for(var vid in ret['grn']['vendor_id_list']){
						entry.dataPoints.push({
							y: ret['grn']['vendor_id_list'][vid]['amt'], 
							toolTipContent: ret['grn']['vendor_id_list'][vid]['vendor_desc']+": "+ret['grn']['vendor_id_list'][vid]['amt'].toLocaleString(undefined,{minimumFractionDigits: 2}),
							legendText: ret['grn']['vendor_id_list'][vid]['vendor_desc']
						});
					}
				}else{
					JQ('#div_chart_purchase_content').html('<br><div align="center">No Data</div>');
				}
			    THIS.chart_purchase.options.data.push(entry);
			     
			    THIS.chart_purchase.render();
			    JQ('a[href="http://canvasjs.com/"]').remove();              //remove the canvas.com sign on bottom right
			}
		});
	},
	
	toggle_sku_photo: function(photo_path){
		JQ('#sku_image').hide();
		JQ('#sku_photo_display').attr("src", "/thumb.php?w=110&h=100&img="+photo_path);
		//JQ('#sku_photo_display').setAttribute( "onClick", "popup_div('div_sku_image', '<img width=640 src=\""+photo_path+"\">')" );
		JQ('#sku_photo_display').attr("onClick", "popup_div('sku_image', '<img width=640 src=\""+photo_path+"\">'); center_div('sku_image');");
	},
	
	toggle_quotation_cost_dialog: function(){
		var sid = document.f_a['sid'].value;
		var bid = 0;
		if(document.f_a['branch_id'] != undefined) bid = document.f_a['branch_id'].value;
		if(sid == 0) return false;
		$('div_sku_quotation_cost_dialog_content').update(_loading_);
		
		curtain(true);
		center_div($('div_sku_quotation_cost_dialog').show());
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_load_quotation_cost',
				sid: sid,
				bid: bid
			},
			method: 'post',
			onComplete: function(msg){
				
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_sku_quotation_cost_dialog_content').update(ret['html']);
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
	},
	
	dialog_close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	
	toggle_sku_sp_history_dialog: function(){
		curtain(true);
		center_div($('div_sku_sp_history_dialog').show());
		// loading
		$('div_sku_sp_history_content').update(_loading_);
		
		if(document.f_a['branch_id'] != undefined) bid = document.f_a['branch_id'].value;
		else bid = curr_bid;
		
		new Ajax.Updater('div_sku_sp_history_content','masterfile_sku_items_price.php',{
			parameters:{
				a: 'history',
				id: document.f_a['sid'].value,
				branch_id: bid,
				type: ''
			},
			evalScripts:true
		});
	},
}


{/literal}
</script>

<h1>{$PAGE_TITLE}{if $data.sku_info}: {$data.sku_info.sku_item_code}{/if}</h1>

{* Quotation Cost Dialog *}
<div id="div_sku_quotation_cost_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:550px;height:300px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;overflow-y: auto;">
	<div id="div_sku_quotation_cost_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Quotation Cost</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_SUMMARY.dialog_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_quotation_cost_dialog_content" style="padding:2px;">
		
	</div>
</div>

<div id="div_sku_sp_history_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:480px;height:320px;display:none;border:2px solid #CE0000;background:#fff;background-repeat:repeat-x;padding:0;">
	<div id="div_sku_sp_history_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Selling Price History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_SUMMARY.dialog_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_sp_history_content" id="price_history_content" style="height:290px;overflow:auto;">
	</div>
</div>

{if !$data}
	* No Data *
{else}
	
	<form name="f_a" onSubmit="return false;">
		<input type="hidden" name="sid" value="{$data.sku_info.id}" />
		<p align="center">
			<b>Branch: </b>
			{if $BRANCH_CODE eq 'HQ'}
				<select name="branch_id" onChange="SKU_SUMMARY.branch_changed();">
					{foreach from=$branches key=bid item=b}
						<option value="{$bid}" {if (isset($smarty.request.branch_id) and $bid eq $smarty.request.branch_id) or (!$smarty.request.branch_id and $bid eq $sessioninfo.branch_id)}selected {/if}>{$b.code} - {$b.description}</option>
					{/foreach}
				</select>
			{else}
				{$BRANCH_CODE}
			{/if}
		</p>
	</form>
	
	<table width="100%" border="0">
		<tr>
			<td width="50%" height="100%">
				<div class="ui-corner-all div_container stdframe">
					<h3 align="center">General Information</h3>
					<table width="100%" style="font-size:14px;">
						<tr>
							<td width="30%" valign="top">
								<div class="div_sku_image">
									{if $data.sku_info.photo_list}
										<img width="190" id="sku_photo_display" align="absmiddle" vspace="4" hspace="4" src="/thumb.php?w=110&h=100&img={$data.sku_info.photo_list.0.abs_path}" border="0" style="cursor:pointer" onclick="popup_div('sku_image', '<img width=640 src=\'{$data.sku_info.photo_list.0.abs_path}\'>')" title="View">
									{else}
										<div class="div_no_image">
											No image
										</div>
									{/if}
								</div>
								{if $data.sku_info.photo_list}
									<div style="width: 200px; overflow-x: auto; white-space: nowrap;">
										{foreach from=$data.sku_info.photo_list name=i key=arr item=r}
											<img width="60" height="50" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&img={$r.abs_path}" border="0" style="cursor:pointer" onclick="SKU_SUMMARY.toggle_sku_photo('{$r.abs_path}');" title="View">
										{/foreach}
									</div>
								{/if}
							</td>
							<td valign="top">
								<table width="100%">
									<tr style="background-color:white;">
										<td width="30%"><b>ARMS Code:</b></td>
										<td>{$data.sku_info.sku_item_code}</td>
									</tr>
									<tr>
										<td><b>MCode:</b></td>
										<td>{$data.sku_info.mcode|default:'-'}</td>
									</tr>
									<tr style="background-color:white;">
										<td><b>{$config.link_code_name}:</b></td>
										<td>{$data.sku_info.link_code|default:'-'}</td>
									</tr>
									<tr>
										<td><b>Art No:</b></td>
										<td>{$data.sku_info.artno|default:'-'}</td>
									</tr>
									<tr style="background-color:white;">
										<td><b>Product Description:</b></td>
										<td>{$data.sku_info.sku_desc|default:'-'}</td>
									</tr>
									<tr>
										<td><b>Category:</b></td>
										<td>{$data.sku_info.cat_desc|default:'-'}</td>
									</tr>
									<tr style="background-color:white;">
										<td><b>Brand:</b></td>
										<td>{$data.sku_info.brand_desc|default:'UN-BRANDED'}</td>
									</tr>
									<tr>
										<td><b>Master Vendor:</b></td>
										<td>{$data.sku_info.vendor_desc|default:'-'}</td>
									</tr>
									{if $branch_got_gst}
										<tr style="background-color:white;">
											<td><b>Input GST:</b></td>
											<td>{$data.sku_info.input_gst_code|default:'-'} ({$data.sku_info.input_gst_rate|default:'0'}%)</td>
										</tr>
										<tr>
											<td><b>Output GST:</b></td>
											<td>{$data.sku_info.output_gst_code|default:'-'} ({$data.sku_info.output_gst_rate|default:'0'}%)</td>
										</tr>
									{/if}
								</table>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td height="100%" align="center">
				<div class="ui-corner-all div_container stdframe">
					<h3 align="center">KPI</h3>
					<table width="70%" style="font-size:14px;" >
						<tr style="border:1px solid #999; padding:5px; background-color:#cccccc;">
							<th width="260">Index</th>
							<th colspan="2" >Value</th>
						</tr>
						{if $branch_got_gst}
							<tr style="background-color:white;">
								<td><b>Current Selling Price Before GST</b></td>
								<td> </td>
								<td><div style="float:left">{$config.arms_currency.symbol}</div>
								<div style="float:right">{$data.sku_info.selling_price_before_tax|number_format:2}</div></td>
							
							</tr>
							
							<tr>
								<td><b>Current GST ({$data.sku_info.output_gst_rate|default:'0'}%)</b></td>
								<td> </td>
								<td><div style="float:left">{$config.arms_currency.symbol}</div>
								<div style="float:right">{$data.sku_info.gst_amt|number_format:2}</div></td>
								
							</tr>
							
							<tr style="background-color:white;">
								<td><b>Current Selling Price Inclusive GST</b></td>
								<td align="center" class="small">[<a href="#" onclick="SKU_SUMMARY.toggle_sku_sp_history_dialog();">History</a>]</td>
								<td><div style="float:left">{$config.arms_currency.symbol}</div>
								<div style="float:right">{$data.sku_info.selling_price_after_gst|number_format:2}</div></td>
					
							</tr>
						{else}
							<tr style="background-color:white;">
								<td><b>Current Selling Price</b></td>
								<td align="center" class="small">[<a href="#" onclick="SKU_SUMMARY.toggle_sku_sp_history_dialog();">History</a>]</td>
								<td><div style="float:left"> {$config.arms_currency.symbol}</div>
								<div style="float:right">{$data.sku_info.selling_price|number_format:2}</div> </td>
							</tr>
						{/if}
						
						{if $sessioninfo.privilege.SHOW_COST}
							<tr>
								<td><b>Current Cost</b></td>
								<td> </td>
								<td> <div style="float:left">{$config.arms_currency.symbol} </div>
								<div style="float:right">{$data.sku_info.cost|number_format:$config.global_cost_decimal_points} </div>
								</td>
							</tr>
							
							<tr style="background-color:white;">
								<td><b>Gross Profit</b></td>
								<td> </td>
								<td><div style="float:left">{$config.arms_currency.symbol}</div>
								<div style="float:right"> {$data.sku_info.gp|number_format:2}</div></td>
							</tr>
							
							<tr>
								<td><b>Gross Profit %</b></td>
								<td> </td>
								<td align="right"> {$data.sku_info.gp_per|number_format:2} %</td>
							</tr>
						{/if}
						
						<tr {if $sessioninfo.privilege.SHOW_COST}style="background-color:white;"{/if}>
							<td><b>Inventory Turnover (Last 30 Days)</b></td>
							<td> </td>
							<td align="right">{$data.sku_info.inventory_turnover|number_format:2|default:'0'} </td>
						</tr>
						
						<tr {if !$sessioninfo.privilege.SHOW_COST}style="background-color:white;"{/if}>
							<td><b>Price Range (Last 12 months)</b></td>
							<td><div style="float:left"> Min: </div> <div style="float:right">{$data.sku_info.min_sp|number_format:2}</div> </td>
							<td><div style="float:left"> Max: </div> <div style="float:right">{$data.sku_info.max_sp|number_format:2} </div></td>
						</tr>
						
						{if $sessioninfo.privilege.SHOW_COST}
							<tr style="background-color:white;">
								<td><b>Cost Range (Last 12 months)</b></td>
								<td><div style="float:left"> Min: </div> <div style="float:right"> {$data.sku_info.min_cost|number_format:$config.global_cost_decimal_points}</div></td>
								<td><div style="float:left"> Max: </div> <div style="float:right"> {$data.sku_info.max_cost|number_format:$config.global_cost_decimal_points}</div></td>
							</tr>
						{/if}
						
							<tr>
								<td><b>Total Purchase Qty </b></td>
								<td><div style="float:left"> MTD: </div> <div style="float:right"> {$data.sku_info.total_pur_m|qty_nf}</div></td>
								<td><div style="float:left"> YTD: </div> <div style="float:right"> {$data.sku_info.total_pur_y|qty_nf}</div></td>
							</tr>
							
							<tr style="background-color:white;">
								<td><b>Total Sales Qty </b></td>
								<td><div style="float:left"> MTD: </div> <div style="float:right"> {$data.sku_info.total_s_m|qty_nf}</div></td>
								<td><div style="float:left"> YTD: </div> <div style="float:right"> {$data.sku_info.total_s_y|qty_nf}</div></td>
							</tr>
							<tr>
								<td><b>Quotation Cost <span class="small">[<a href="#" onclick="SKU_SUMMARY.toggle_quotation_cost_dialog();">More</a>]</span></b></td>
								<td> </td>
								<td><div style="float:left"> {$config.arms_currency.symbol}</div> <div style="float:right"> {$data.sku_info.quotation_cost|number_format:$config.global_cost_decimal_points}</div></td>
							</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
	
	{*<br /><br />
	<input type="button" value="Compare Parent / Child" style="font:bold 20px Arial; background-color:#f90; color:#fff; border-style:outset;" onClick="SKU_SUMMARY.show_select_compare();" />*}
	
	<br /><br />
	
	<div id="div_chart_filter">
		<form name="f_chart_sales" onSubmit="return false;">
			<b>Chart Period: </b>
			<select name="period">
				{foreach from=$period_list key=k item=v}
					<option value="{$k}">{$v}</option>
				{/foreach}
			</select>
			
			<input class="btn btn-primary" type="button" Value="Refresh" onClick="SKU_SUMMARY.reload_sales_chart();" />
			<input class="btn btn-primary" type="button" Value="Compare" onClick="SKU_SUMMARY.show_sales_compare();" />
		</form>
		<br />
	</div>
					
	<table width="100%">
		<tr>
			<td width="33%">
				<div class="ui-corner-all div_chart" id="div_chart_sales">
					<div id="div_chart_sales_content" class="div_chart_content"><img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...</div>
				</div>
			</td>
			<td width="33%" align="center">
				<div class="ui-corner-all div_chart" id="div_chart_gp" style="text-align:left;">
					<div id="div_chart_gp_content" class="div_chart_content"><img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...</div>
				</div>
			</td>
			<td width="33%" align="right">
				<div class="ui-corner-all div_chart" id="div_chart_purchase" style="text-align:left;">
					<div id="div_chart_purchase_content" class="div_chart_content"><img src="ui/clock.gif" align="absmiddle" /> Loading, Please wait...</div>
				</div>
			</td>
		</tr>
	</table>
	
{/if}

<script>SKU_SUMMARY.initialize();</script>

{include file='footer.tpl'}