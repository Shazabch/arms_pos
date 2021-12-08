{*
3/10/2011 3:12:26 PM Justin
- Redirect all the current receipt detail ajax call to use from counter collection.

3/29/2011 3:39:21 PM Justin
- Modified the transaction detail to redirect to counter_collection.php 

3/30/2011 11:14:57 AM Justin
- Fixed the receipt detail not to close transaction detail window when click on close button.

10/20/2011 5:10:15 PM Andy
- Fix unset user status cannot function in popup.

11/18/2011 10:10:36 AM Andy
- Add checking for privilege "POS_FORCE_LOGOUT" to allow user to unset "Login User".

3/29/2012 4:21:53 PM Andy
- Change show transaction details to use counter collection function.

3/2/2017 2:34 PM Andy
- Fixed periodicLoad bug.

5/12/2017 8:21 AM Qiu Ying
- Enhanced to show all counter sales by branch
*}

{if !$sqlonly and $smarty.request.branch_id >0}
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{config_load file="site.conf"}
{config_load file="common.conf"}
<meta NAME="Description" CONTENT="{#META_DESCRIPTION#}">
<title>{$BRANCH_CODE} | {#SITE_NAME#} | POS Live</title>
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<link rel="shortcut icon" href="/favicon.ico">
<script src="/js/prototype.js" language=javascript type="text/javascript"></script>
<script src="/js/scriptaculous.js" language=javascript type="text/javascript"></script>
<script src="/js/sorttable.js" type="text/javascript"></script>
<script src="/js/forms.js" language=javascript type="text/javascript"></script>
</head>

<body onmousemove = "mouse_trapper(event);" style="margin:20px">
<div id=curtain onclick="curtain_clicked()" style="position:absolute;display:none;z-index:9999;background:#fff;opacity:0.1;"></div>
{if !$counter}
	<br />No data
{/if}
<script>var bid='{$smarty.request.branch_id}';</script>
{literal}
<style>
.hsort{
	cursor:pointer;
}
.hsort th:hover{
	color:#ce0000;
}
</style>
<script>
var curr_sort_order = new Array();
var curr_sort = new Array();

function sort_reloadTable(col,grp)
{
	if (curr_sort[grp]==undefined || curr_sort[grp] != col)
	{
		curr_sort[grp] = col;
		curr_sort_order[grp] = 'asc';
	}
	else
	{
		curr_sort_order[grp] =  (curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
	}

	SetCookie('_tbsort_'+grp, curr_sort[grp],1);
	SetCookie('_tbsort_'+grp+'_order', curr_sort_order[grp],1);

	ajax_load_counter(bid);
}

function SetCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=1;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

</script>
{/literal}
{/if}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var t_date = '{$smarty.request.date}';
var LOADING = '<img src="/ui/clock.gif" />';
var context_info;
</script>

{literal}

<style>
.counter
{
	margin-right:10px;
	margin-bottom:10px;
	padding:10px;
	width:200px;
	height:200px;
	border:solid 1px #999999;
	float:left;
}

.counter_content
{
	width:100%;
	height:90%;
}
.class_counter
{
	width:100%;
	text-align:left;
}

.class_counter tr:hover
{
	background:#ccffff;
}

#div_sales_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_sales_content{
	width:100%;
	height:100%;
	overflow-y:auto;
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

.div_content{
	overflow-y:auto;
    width:100%;
	height:80%;
}
.status_login{
	color: green;
}
.status_offline{
	color: red;
}
</style>

<script>
function ajax_load_counter(branch_id){
	new Ajax.Updater('div_counters_table',phpself, {
		method: 'post',
		parameters:{
			a: 'load_counter_table',
			branch_id: branch_id,
			ajax: '1',
			date: t_date
		}
	});
}

function periodicLoadCounter(branch_id){
	new PeriodicalExecuter(function(){
		ajax_load_counter(branch_id);
	}, 30);
}

function payment_details(branch_id,counter_id){
    curtain(true);

    hidediv('div_item_details');
	center_div('div_sales_details');

	$('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_sales_content',phpself+'?a=payment_details',
	{
	    method: 'post',
	    parameters: context_info
	});

}

function tran_details(branch_id,counter_id){
    curtain(true);

    hidediv('div_item_details');
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');


	new Ajax.Updater('div_sales_content','counter_collection.php?a=sales_details&date='+t_date,
	//new Ajax.Updater('div_sales_content',phpself+'?a=tran_details&date='+t_date,
	{
	    method: 'post',
	    parameters: context_info
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
	hidediv('div_sales_details');
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

function show_context_menu(obj, branch_id, counter_id)
{
	context_info = { element: obj, branch_id: branch_id, counter_id: counter_id, date: t_date};

	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';

	$('item_context_menu').show();

	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}

	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function hide_context_menu()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;
	Element.hide('item_context_menu');
}

function unset_login_status(bid, cid, event){
	event.stopPropagation();
	
	var td_ele = $('td_user_cu-'+bid+'-'+cid);
	if(td_ele.innerHTML.indexOf('clock.gif')>=0){
		alert('Loading... Please wait.');
		return false;
	}
	if(!confirm('Are you sure? This only clear the login status, it will not force the user logout.'))	return false;
	
	var default_html = td_ele.innerHTML;
	$(td_ele).update(_loading_);
	new Ajax.Request(phpself, {
		parameters:{
			a: 'ajax_unset_login_status',
			bid: bid,
			cid: cid
		},
		onComplete: function(str){
			var msg = str.responseText.trim();
			
			if(msg == 'OK'){
				$(td_ele).update('-');	
			}else{
				alert(msg);
				td_ele.innerHTML = default_html;
			}
		}
	});
}
</script>

{/literal}


{if !$counter}
	{if isset($smarty.request.submits)}<br />No data{/if}
{else}
	{if !$load_all}
		<!-- Popup menu -->
		<div id="item_context_menu" style="display:none;position:absolute;">
		<ul id="ul_menu" class="contextmenu">
		<li><a href="javascript:payment_details();"><img src=/ui/icons/money.png align=absmiddle> Payment Details</a></li>
		<li><a href="javascript:tran_details();"><img src=/ui/icons/money.png align=absmiddle> Transaction Details</a></li>
		</ul>
		</div>

		<!-- Sales Details-->
		<div id="div_sales_details" style="display:none;width:600px;height:400px;">
		<div style="float:right;"><img onclick="hidediv('div_sales_details'); curtain(false);" src="/ui/closewin.png" /></div>
		<div id="div_sales_content">
		</div>
		</div>
		<!-- End of Sales Details-->

		<!-- Item Details -->
		<div id="div_item_details" style="display:none;width:800px;height:550px;">
			<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
			<h3 align="center">Items Details</h3>
			<div id="div_item_content"></div>
		</div>
		<!-- End of Item Details-->

		<h4 class="text-primary mx-3">Branch: {$bcode}
		</h4>
	{/if}
	
	<div id="div_counters_table">
		{if $load_all}
			{include file="pos_live.all_branch_all_counter.tpl"}
		{else}
			{include file="pos_live.counter.tpl"}
			<script>periodicLoadCounter('{$smarty.request.branch_id}')</script>
		{/if}
	</div>
{/if}
