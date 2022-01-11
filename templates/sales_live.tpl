{*
3/4/2011 11:33:35 AM Justin
- Set timesout not to refresh in 30 seconds if found the sales is not from current day.

3/10/2011 3:12:26 PM Justin
- Redirect all the current receipt detail ajax call to use from counter collection.

3/29/2011 3:39:21 PM Justin
- Modified the transaction detail to redirect to counter_collection.php 

1/20/2016 4:40 PM Qiu Ying
- Delete onchange="LoadError();"

8/16/2016 10:07 AM Andy
- Added deposit notification.
- Change to load transaction details list from counter collection.

06/30/2020 04:43 PM Sheila
- Updated button css.

10/5/2020 4:18 PM Shane
- Renamed Sales to Sales Amt for Hourly Sales, Department Sales, Member Sales, Payment Type Sales
- Enhance list_hourly, list_department, list_member function to use data-id attribute for resetting tabpane
- Added Sales Qty tab for Hourly Sales, Department Sales, Member Sales
*}

{include file=header.tpl}
<script type="text/javascript" src="js/json2.js"></script>
<script type="text/javascript" src="js/swfobject.js"></script>


<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>var branch_id='{$smarty.request.branch_id}';</script>
{literal}
<style>
.hsort{
	cursor:pointer;
}
.hsort th:hover{
	color:#ce0000;
}
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:800px;
	height:600px;
	position:absolute;
	z-index:30000;
}
#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
}
</style>
{/literal}

<script>
var REFRESH_INTERVAL = 30000;
var phpself = '{$smarty.server.PHP_SELF}';
var date = '{$smarty.request.date}';
var curr_date = '{$curr_date}';
var chart_level_priv = '{$level_priv}';
var LOADING = '<img src="/ui/clock.gif" />';

{literal}

if(date != curr_date){
	REFRESH_INTERVAL = float(REFRESH_INTERVAL)*10000;
}

var hourly_timeout = false;
function list_hourly(h, level, lid, lvl_desc){
	var i;

	// set the branch ID get from level 1 when it is level 2
	if(level == 2){
		var hourly_lvl2_bid = lid;
	}else{
		var hourly_lvl2_bid = $('hourly_lvl2_bid').value;
	}

	for(i=1;i<=5;i++){
		if (i==h){
			tab_slctd = i;
		    $('hourly_lst'+i).addClassName('selected');
		}else{
		    $('hourly_lst'+i).removeClassName('selected');
		}
	}

	new Effect.Opacity($('hourly_list'), { to: 0.7, duration: 0.5 });

	// reset the tab panes with new ID and levels
	$$("#div_list_hourly a").each(function(u,i){
		var idx = $(u).getAttribute('data-id');
		$(u).href = 'javascript:list_hourly('+(idx)+', '+level+', "'+lid+'")';
	});
	 
	if(hourly_timeout) clearTimeout(hourly_timeout);

	new Ajax.Request(
	    "sales_live.php",
	    {
			method:'post',
			parameters: 'a=ajax_hourly_list&branch_id='+branch_id+'&date='+date+'&h='+h+'&level='+level+'&lid='+lid+'&lvl_desc='+encodeURIComponent(lvl_desc)+'&hourly_lvl2_bid='+hourly_lvl2_bid+'&tab_slctd='+tab_slctd,
		    evalScripts: true,
			onFailure: function(oR) {
				alert('Error while updating Hourly Sales table, please try again!');
			},
			onSuccess: function(oR) {
				if(oR.responseText != ''){
					$('hourly_list').update(oR.responseText);
					hourly_timeout = setTimeout('list_hourly('+h+','+level+',"'+lid+'")',REFRESH_INTERVAL);
				}
	            new Effect.Opacity($('hourly_list'), { to: 1, duration: 0.5 }); 
	    	}
		}
	);
}

var dept_timeout = false;
function list_department(d, level, lid, col_level_id, cat_level){
	var i;

	// set the branch ID get from level 1 when it is level 2
	if(level == 2){
		var department_lvl2_bid = lid;
	}else{
		var department_lvl2_bid = $('department_lvl2_bid').value;
	}

	for(i=1;i<=5;i++){
		if (i==d){
			tab_slctd = i;
		    $('department_lst'+i).addClassName('selected');
		}else{
		    $('department_lst'+i).removeClassName('selected');
		}
	}

	if(lid == '' && cat_level == ''){
		col_level_id = '';
	}

	new Effect.Opacity($('department_list'), { to: 0.7, duration: 0.5 });

	// reset the tab panes with new ID and levels
	$$("#div_list_department a").each(function(u,i){
		var idx = $(u).getAttribute('data-id');
		$(u).href = 'javascript:list_department('+(idx)+', '+level+', "'+lid+'", "'+col_level_id+'", "'+cat_level+'")';
	});

	if(dept_timeout) clearTimeout(dept_timeout);
	
	new Ajax.Request(
	    "sales_live.php",
	    {
			method:'post',
			parameters: 'a=ajax_department_list&branch_id='+branch_id+'&date='+date+'&d='+d+'&level='+level+'&lid='+lid+'&department_lvl2_bid='+department_lvl2_bid+'&tab_slctd='+tab_slctd+'&col_level_id='+col_level_id+'&cat_level='+cat_level,
		    evalScripts: true,
			onFailure: function(oR) {
				alert('Error while updating Department Sales table, please try again!');
			},
			onSuccess: function(oR) {
				if(oR.responseText != ''){
					$('department_list').update(oR.responseText);
					dept_timeout = setTimeout('list_department('+d+','+level+',"'+lid+'", "'+col_level_id+'", "'+cat_level+'")',REFRESH_INTERVAL);
				}
	            new Effect.Opacity($('department_list'), { to: 1, duration: 0.5 }); 
	    	}
		}
	);
}

var member_timeout = false;
function list_member(m, level, lid){
	var i;
	
	// set the branch ID get from level 1 when it is level 2
	if(level == 2){
		var member_lvl2_bid = lid;
	}else{
		var member_lvl2_bid = $('member_lvl2_bid').value;
	}
	
	for(i=1;i<=5;i++){
		if (i==m){
			tab_slctd = i;
		    $('member_lst'+i).addClassName('selected')
		}else{
		    $('member_lst'+i).removeClassName('selected');
		}
	}

	new Effect.Opacity($('member_list'), { to: 0.7, duration: 0.5 });

	// reset the tab panes with new ID and levels
	$$("#div_list_member a").each(function(u,i){
		var idx = $(u).getAttribute('data-id');
		$(u).href = 'javascript:list_member('+(idx)+', '+level+', "'+lid+'")';
	});

	if (member_timeout) clearTimeout(member_timeout);

	new Ajax.Request(
	    "sales_live.php",
	    {
			method:'post',
			parameters: 'a=ajax_member_list&branch_id='+branch_id+'&date='+date+'&m='+m+'&level='+level+'&lid='+lid+'&member_lvl2_bid='+member_lvl2_bid+'&tab_slctd='+tab_slctd,
		    evalScripts: true,
			onFailure: function(oR) {
				alert('Error while updating Member Sales table, please try again!');
			},
			onSuccess: function(oR) {
				if(oR.responseText != ''){
					$('member_list').update(oR.responseText);
					member_timeout = setTimeout('list_member('+m+','+level+',"'+lid+'")',REFRESH_INTERVAL);
				}
	            new Effect.Opacity($('member_list'), { to: 1, duration: 0.5 }); 
	    	}
		}
	);
}

var ptype_timeout = false;
function list_payment_type(pt, level, lid){
	var i;
	
	// set the branch ID get from level 1 when it is level 2
	if(level == 2){
		var payment_type_lvl2_bid = lid;
	}else{
		var payment_type_lvl2_bid = $('payment_type_lvl2_bid').value;
	}
	
	for(i=1;i<=2;i++){
		if (i==pt){
			tab_slctd = i;
		    $('payment_type_lst'+i).addClassName('selected');
		}else{
		    $('payment_type_lst'+i).removeClassName('selected');
		}
	}

	new Effect.Opacity($('payment_type_list'), { to: 0.7, duration: 0.5 });

	// reset the tab panes with new ID and levels
	$$("#div_list_payment_type a").each(function(u,i){
		$(u).href = 'javascript:list_payment_type('+(i+1)+', '+level+', "'+lid+'")';
	});

	if (ptype_timeout) clearTimeout(ptype_timeout);

	new Ajax.Request(
	    "sales_live.php",
	    {
			method:'post',
			parameters: 'a=ajax_payment_type_list&branch_id='+branch_id+'&date='+date+'&pt='+pt+'&level='+level+'&lid='+lid+'&payment_type_lvl2_bid='+payment_type_lvl2_bid+'&tab_slctd='+tab_slctd,
		    evalScripts: true,
			onFailure: function(oR) {
				alert('Error while updating Payment Type Sales table, please try again!');
			},
			onSuccess: function(oR) {
				if(oR.responseText != ''){
					$('payment_type_list').update(oR.responseText);
					ptype_timeout = setTimeout('list_payment_type('+pt+','+level+',"'+lid+'")',REFRESH_INTERVAL);
				}
	            new Effect.Opacity($('payment_type_list'), { to: 1, duration: 0.5 }); 
	    	}
		}
	);
}

var hour_chart_data, department_chart_data, member_chart_data_pc1, member_chart_data_pc2, member_chart_data_pc3, ptype_chart_data_pc1, ptype_chart_data_pc2;

function ofc_ready(){
	hourly_timeout = setTimeout('list_hourly("1",'+chart_level_priv+',"")',REFRESH_INTERVAL);
	dept_timeout = setTimeout('list_department("1",'+chart_level_priv+',"", "", "")',REFRESH_INTERVAL);
	member_timeout = setTimeout('list_member("1",'+chart_level_priv+',"")',REFRESH_INTERVAL);
	ptype_timeout = setTimeout('list_payment_type("1",'+chart_level_priv+',"")',REFRESH_INTERVAL);
}

function update_hourly_chart(){
	var so = findSWF("hourly_chart");
	if (so!=undefined) { 
		so.load(JSON.stringify(hour_chart_data));
	}
}

function update_department_chart(){
	var so = findSWF("department_chart");
	if (so!=undefined) { 
		so.load(JSON.stringify(department_chart_data)); 
	}
}

function update_member_chart(){
	var so1 = findSWF("member_chart_pc1");
	var so2 = findSWF("member_chart_pc2");
	var so3 = findSWF("member_chart_pc3");
	if (so1!=undefined) { 
		so1.load(JSON.stringify(member_chart_data_pc1)); 
	}
	if (so2!=undefined) { 
		so2.load(JSON.stringify(member_chart_data_pc2)); 
	}
	if (so3!=undefined) { 
		so3.load(JSON.stringify(member_chart_data_pc3)); 
	}
}

function update_member_chart(){
	var so1 = findSWF("member_chart_pc1");
	var so2 = findSWF("member_chart_pc2");
	var so3 = findSWF("member_chart_pc3");
	if (so1!=undefined) { 
		so1.load(JSON.stringify(member_chart_data_pc1)); 
	}
	if (so2!=undefined) { 
		so2.load(JSON.stringify(member_chart_data_pc2)); 
	}
	if (so3!=undefined) { 
		so3.load(JSON.stringify(member_chart_data_pc3)); 
	}
}

function update_ptype_chart(){
	var so1 = findSWF("ptype_chart_pc1");
	var so2 = findSWF("ptype_chart_pc2");
	if (so1!=undefined) { 
		so1.load(JSON.stringify(ptype_chart_data_pc1)); 
	}
	if (so2!=undefined) { 
		so2.load(JSON.stringify(ptype_chart_data_pc2)); 
	}
}

function findSWF(movieName){
	if(navigator.appName.indexOf("Microsoft")!= -1) {
		return window[movieName];
	}else{
		return document[movieName];
	}
}


function tran_details(branch_id,counter_id, category_id){
    curtain(true);
    hidediv('div_item_details');
    center_div('div_tran_details');
    
    $('div_tran_details').show();
	$('div_trans_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_trans_content','counter_collection.php',
	{
	    method: 'post',
	    parameters: 'a=sales_details&branch_id='+branch_id+'&counter_id='+counter_id+'&category_id='+category_id+'&date='+date,
	    evalScripts: true,
	});
	
	/*new Ajax.Updater('div_trans_content',phpself,
	{
	    method: 'post',
	    parameters: 'a=tran_details&branch_id='+branch_id+'&counter_id='+counter_id+'&category_id='+category_id+'&date='+date,
	    evalScripts: true,
	});*/
}

function trans_detail(counter_id,cashier_id,date,id,branch_id){
	curtain(true);
    center_div($('div_item_details'));

    $('div_item_details').show()
	$('div_item_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			cashier_id: cashier_id,
			date: date,
			branch_id: branch_id,
			pos_id: id
		}
	});
}

function curtain_clicked(type){
	if(type != undefined){
		$(type).hide();
	}else{
	    $('div_tran_details').hide();
	    $('div_item_details').hide();
	    curtain(false);
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
	<div class="card-body">
		<div class=stdframe >
			<form method="post" name="f_sales_live">
			<div class="row">
				{if $BRANCH_CODE eq 'HQ'}
			<div class="col-md-3">
				<b class="form-label">Branch: </b>
			<select class="form-control" name="branch_id" id="branch_id">
				<option value="">-- All --</option>
				{foreach from=$branch_list item=r}
					<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id} selected {/if}>{$r.code}</option>
					{if $smarty.request.branch eq $r.id}
						{assign var=bcode value=$r.code}
					{/if}
				{/foreach}
			</select>
		
			</div>
			{/if}
			<div class="col-md-3">
				<b class="form-label">Date</b> 
			<div class="form-inline">
				<input class="form-control" size=22 type=text name=date value="{$smarty.request.date}" id="date">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			
		<div class="col-md-3">
			<input class="btn btn-primary  mt-4" type="submit" name="submits" value="Load">
		</div>
			</div>
			
			<br />
			{if $got_deposit}
				<div class="alert alert-primary rounded" style="max-width: 650px;">
					<br />* Please take note Deposit may make the Payment Amount and Sales not tally.
				<br />* This is because the Cash Payment received by Deposit is in transaction A, but later the items was purchased in transaction B. Transaction A & B could be in different date.
				</div>
			{/if}
			</form>
			</div>
	</div>
</div>

<div id="div_tran_details" style="overflow:hidden;position:absolute;left:0;top:0;display:none;width:600px;height:410px;padding:10px;border:1px solid #000;background:#fff;z-index:20000;">
	<div style="float:right;"><img onclick="curtain_clicked('div_tran_details'); curtain(false);" src="/ui/closewin.png" /></div>
	<div id="div_trans_content"></div>
</div>

<div id="div_item_details" style="display:none;width:800px;height:550px;">
	<div style="float:right;"><img onclick="curtain_clicked('div_item_details');" src="/ui/closewin.png" /></div>
	<h3 align="center">Items Details</h3>
	<div id="div_item_content"></div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<table border="0" cellpadding="12">
			<tr>
				<td width="50%" align=center valign=top>
					<div style="padding:10px 0;">
						<h2 align="left" class="text-primary" style="padding-bottom:10px;" nowrap>Hourly Sales</h2>
						<div class="tab row mx-3 mb-3" style="white-space:nowrap;" id="div_list_hourly"  align=left>
						
							<a href="javascript:list_hourly(1, '{$level_priv}', '')" data-id=1 id=hourly_lst1 class="btn btn-outline-primary btn-rounded">Sales Amt</a>
							&nbsp;<a href="javascript:list_hourly(5, '{$level_priv}', '')" data-id=5 id=hourly_lst5 class="btn btn-outline-primary btn-rounded">Sales Qty</a>
							&nbsp;<a href="javascript:list_hourly(2, '{$level_priv}', '')" data-id=2 id=hourly_lst2 class="btn btn-outline-primary btn-rounded">Promotion</a>
							&nbsp;<a href="javascript:list_hourly(3, '{$level_priv}', '')" data-id=3 id=hourly_lst3 class="btn btn-outline-primary btn-rounded">Count</a>
							&nbsp;<a href="javascript:list_hourly(4, '{$level_priv}', '')" data-id=4 id=hourly_lst4 class="btn btn-outline-primary btn-rounded">Buying Power</a>
						</div>
						<div id=hourly_list style="border: 0px;">
							{include file=sales_live.hourly.tpl}
						</div>
					</div>
				</td>
				<td width="50%" align=left valign=top>
					<h2 align="left" style="padding-bottom:10px;" nowrap>&nbsp;<br>&nbsp;</h2>
					<div id="hourly_chart">-</div>
				</td>
			</tr>
			<tr>
				<td align=center valign=top>
					<div style="padding:10px 0;">
						<h2 align="left" class="text-primary" style="padding-bottom:10px;" nowrap>Department Sales</h2>
						<div class="tab mx-3 mb-3" style="white-space:nowrap;" id="div_list_department" align=left>
						
							<a href="javascript:list_department(1, '{$level_priv}', '', '', '')" data-id=1 id=department_lst1 class="btn btn-outline-primary btn-rounded">Sales Amt</a>
							&nbsp;<a href="javascript:list_department(5, '{$level_priv}', '', '', '')" data-id=5 id=department_lst5 class="btn btn-outline-primary btn-rounded">Sales Qty</a>
							&nbsp;<a href="javascript:list_department(2, '{$level_priv}', '', '', '')" data-id=2 id=department_lst2 class="btn btn-outline-primary btn-rounded">Promotion</a>
							&nbsp;<a href="javascript:list_department(3, '{$level_priv}', '', '', '')" data-id=3 id=department_lst3 class="btn btn-outline-primary btn-rounded">Count</a>
							&nbsp;<a href="javascript:list_department(4, '{$level_priv}', '', '', '')" data-id=4 id=department_lst4 class="btn btn-outline-primary btn-rounded">Buying Power</a>
						</div>
						<div id=department_list style="border: 0px;">
						{include file=sales_live.department.tpl}
						</div>
					</div>
					</ol>
				</td>
				<td align=left valign=top>
					<h2 align="left" style="padding-bottom:10px;" nowrap>&nbsp;<br>&nbsp;</h2>
					<div id="department_chart">-</div>
				</td>
			</tr>
			<tr>
				<td align=center valign=top>
					<div style="padding:10px 0;">
						<h2 align="left" class="text-primary" style="padding-bottom:10px;" nowrap>Member Sales</h2>
						<div class="tab mx-3 mb-3" style="white-space:nowrap;" id="div_list_member"  align=left>
						
							<a href="javascript:list_member(1, '{$level_priv}', '')" data-id=1 id=member_lst1 class="btn btn-outline-primary btn-rounded">Sales Amt</a>
							&nbsp;<a href="javascript:list_member(5, '{$level_priv}', '')" data-id=5 id=member_lst5 class="btn btn-outline-primary btn-rounded">Sales Qty</a>
							&nbsp;<a href="javascript:list_member(2, '{$level_priv}', '')" data-id=2 id=member_lst2 class="btn btn-outline-primary btn-rounded">Promotion</a>
							&nbsp;<a href="javascript:list_member(3, '{$level_priv}', '')" data-id=3 id=member_lst3 class="btn btn-outline-primary btn-rounded">Count</a>
							&nbsp;<a href="javascript:list_member(4, '{$level_priv}', '')" data-id=4 id=member_lst4 class="btn btn-outline-primary btn-rounded">Buying Power</a>
						</div>
						<div id=member_list style="border: 0px;">
						{include file=sales_live.member.tpl}
						</div>
					</div>
					</ol>
				</td>
				<td align="left" valign=top>
					<h2 align="left" style="padding-bottom:10px;" nowrap>&nbsp;</h2>
					<table>
						<tr>
							<td><div id="member_chart_pc1" style="float: left;">-</div></td>
							<td><div id="member_chart_pc2" style="float: left;">-</div></td>
						</tr>
						<tr>
							<td><br><div id="member_chart_pc3" style="float: left;">-</div></td>
						</tr>
					</table>
				</td>
			<tr>
			</tr>
				<td align=center valign=top>
					<div style="padding:10px 0;">
						<h2 align="left" class="text-primary" style="padding-bottom:10px;" nowrap>Payment Type Sales</h2>
						<div class="tab row mx-3 mb-3" style="white-space:nowrap;" id="div_list_payment_type"  align=left>
						
							<a href="javascript:list_payment_type(1, '{$level_priv}', '')" id=payment_type_lst1 class="btn btn-outline-primary btn-rounded">Sales Amt</a>
							&nbsp;<a href="javascript:list_payment_type(2, '{$level_priv}', '')" id=payment_type_lst2 class="btn btn-outline-primary btn-rounded">Count</a>
						</div>
						<div id=payment_type_list style="border: 0px;">
						{include file=sales_live.payment_type.tpl}
						</div>
					</div>
					</ol>
				</td>
				<td align=left valign=top>
					<h2 align="left" style="padding-bottom:10px;" nowrap>&nbsp;</h2>
					<table>
						<tr>
							<td><div id="ptype_chart_pc1" style="float: left;">-</div></td>
							<td><div id="ptype_chart_pc2" style="float: left;">-</div></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}

{include file=footer.tpl}



{literal}
<script type="text/javascript">
var params = {        
    wmode: "transparent"
};
swfobject.embedSWF("open-flash-chart.swf", "hourly_chart", "600", "250", "9.0.0", '', {"get-data":"get_data1"}, params);
swfobject.embedSWF("open-flash-chart.swf", "department_chart", "600", "250", "9.0.0", '', {"get-data":"get_data2"}, params);
swfobject.embedSWF("open-flash-chart.swf", "member_chart_pc1", "320", "180", "9.0.0", '', {"get-data":"get_data3_1"}, params);
swfobject.embedSWF("open-flash-chart.swf", "member_chart_pc2", "320", "180", "9.0.0", '', {"get-data":"get_data3_2"}, params);
swfobject.embedSWF("open-flash-chart.swf", "member_chart_pc3", "320", "180", "9.0.0", '', {"get-data":"get_data3_3"}, params);
swfobject.embedSWF("open-flash-chart.swf", "ptype_chart_pc1", "320", "180", "9.0.0", '', {"get-data":"get_data4_1"}, params);
swfobject.embedSWF("open-flash-chart.swf", "ptype_chart_pc2", "320", "180", "9.0.0", '', {"get-data":"get_data4_2"}, params);

function get_data1()
{
	return JSON.stringify(hour_chart_data);
}
function get_data2()
{
	return JSON.stringify(department_chart_data);
}
function get_data3_1()
{
	return JSON.stringify(member_chart_data_pc1);
}
function get_data3_2()
{
	return JSON.stringify(member_chart_data_pc2);
}
function get_data3_3()
{
	return JSON.stringify(member_chart_data_pc3);
}
function get_data4_1()
{
	return JSON.stringify(ptype_chart_data_pc1);
}
function get_data4_2()
{
	return JSON.stringify(ptype_chart_data_pc2);
}
</script>
{/literal}
