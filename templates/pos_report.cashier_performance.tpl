{*
8/10/2010 10:20:11 AM Andy
- Add "Open Price" and "Item Discount" Info.

10/7/2010 1:44:56 PM Andy
- Add branches filter.
- Add filter by item discount, receipt discount, cancel bills, goods return and open price.

3/29/2011 3:39:21 PM Justin
- Modified the transaction detail to redirect to counter_collection.php

3/31/2011 6:08:03 PM Justin
- Redirect the item detail to use counter collection.

5/11/2011 10:04:52 AM Alex
- add label for each filtering check boxes

10/20/2011 5:30:47 PM Andy
- Show receipt discount & mix and match discount in cashier performance/abnormal report.

11/11/2011 12:24:14 PM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

1/12/2012 10:13:43 AM Justin
- Added Prune Count column.

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

9/14/2018 5:51 PM Andy
- Enhanced to hide column "Variance" if got filter.
- Enhanced the amount column to show active transaction amount and cancelled transaction amount.

11/16/2018 4:06 PM Justin
- Enhanced to have Allow Cancelled Bill and Prune Bill, Allow and Count for Deleted Items.
- Bug fixed on the Allow countings where it sum up wrongly when filter or click on specific cashier.
*}

{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.span_cancelled_amount{
	color: grey;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var date_from = '{$smarty.request.date_from}';
var date_to = '{$smarty.request.date_to}';

var submmited_parameters = {literal}{{/literal}
	branch_id: '{$smarty.request.branch_id}',
	date_from: '{$smarty.request.date_from}',
	date_to: '{$smarty.request.date_to}',
	user_id: '{$smarty.request.user_id}',
	filter_item_discount: '{$smarty.request.filter_item_discount}',
	filter_receipt_discount: '{$smarty.request.filter_receipt_discount}',
	filter_cancel_bill: '{$smarty.request.filter_cancel_bill}',
	filter_goods_return: '{$smarty.request.filter_goods_return}',
	filter_open_price: '{$smarty.request.filter_open_price}',
	filter_mm_discount: '{$smarty.request.filter_mm_discount}'
{literal}}{/literal}

</script>
{literal}
<style>
#div_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_content{
	width:100%;
	height:95%;
	overflow-y:auto;
	overflow-x:auto;
}
#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}

#div_sales_content,#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
	overflow-x:hidden;
}
.allow_open_price{
	color: red;
}
.allow_item_discount{
	color: blue;
}
.allow_receipt_discount{
	color:green;
}
.allow_cancelled_bill{
	color: #6c3483;
}
.allow_deleted_items{
	color: #d35400;
}
.allow_prune_bill{
	color: #641e16;
}
</style>
<script>
var LOADING = '<img src="/ui/clock.gif" />';
function load_details(branch_id,user_id){

	curtain(true);
    center_div($('div_details'));

    $('div_details').show()
	$('div_content').update(LOADING+' Please wait...');
	
	var params = $H(submmited_parameters).toQueryString()+'&branch_id='+branch_id+'&user_id='+user_id;
	new Ajax.Updater('div_content',phpself+'?a=load_details',
	{
	    method: 'post',
	    parameters: params
	});
}

function curtain_clicked()
{
	if($('div_sales_details').style.display==''){
        hidediv('div_sales_details');
		hidediv('div_item_details');
	}else{
        curtain(false);
		hidediv('div_details');
		hidediv('div_sales_details');
		hidediv('div_item_details');
	}
}

function tran_details(user_id,branch_id,date){
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');

    var params = $H(submmited_parameters).toQueryString()+'&branch_id='+branch_id+'&user_id='+user_id+'&date='+date;
	new Ajax.Updater('div_sales_content',phpself+'?a=tran_details',
	{
	    method: 'post',
	    parameters: params
	});
}

function trans_detail(counter_id,cashier_id,date,id,branch_id){
	$('div_item_details').style.left = $('div_sales_details').style.left;
    $('div_item_details').style.top = $('div_sales_details').style.top;

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

function toggle_filter(ele){
	c = ele.checked;
	
	$$('#fset_filter input.inp_filter').each(function(chx){
		chx.checked = c;
	});
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<!-- overall Details -->
<div id="div_details" style="display:none;width:1100px;height:450px;" class="curtain_popup">
<div style="float:right;padding-bottom:5px;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_content">
</div>
</div>

<!-- Sales Details-->
<div id="div_sales_details" style="display:none;width:1100px;height:450px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Sales Details-->

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:1100px;height:450px;">
<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->

<form method="post" name="myForm" class="form stdframe">
<input type="hidden" name="load_report" value="1" />
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch:</b>
	<select name="branch_id">
	    <option value="">-- All --</option>
		{foreach from=$branches key=bid item=r}
		    <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r.code} - {$r.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Cashier</b> <select name="user_id">
	<option value="">-- All --</option>
	{foreach from=$cashier key=cid item=r}
	    <option value="{$cid}" {if $smarty.request.user_id eq $cid}selected {/if}>{$r.u}</option>
	{/foreach}
</select>

<p>
<fieldset id="fset_filter">
	<legend><b>Filter</b></legend>
	<input type="checkbox" id="all_id" onChange="toggle_filter(this);" /> <label for="all_id">All</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" class="inp_filter" name="filter_item_discount" id="filter_item_discount_id" value="1" {if $smarty.request.filter_item_discount}checked {/if} /> <label for="filter_item_discount_id">Item Discount</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" class="inp_filter" name="filter_cancel_bill" id="filter_cancel_bill_id" value="1" {if $smarty.request.filter_cancel_bill}checked {/if} /> <label for="filter_cancel_bill_id">Cancel Bills</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" class="inp_filter" name="filter_goods_return" id="filter_goods_return_id" value="1" {if $smarty.request.filter_goods_return}checked {/if} /> <label for="filter_goods_return_id">Goods Return</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" class="inp_filter" name="filter_open_price" id="filter_open_price_id" value="1" {if $smarty.request.filter_open_price}checked {/if} /> <label for="filter_open_price_id">Open Price</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" class="inp_filter" name="filter_receipt_discount" id="filter_receipt_discount_id" value="1" {if $smarty.request.filter_receipt_discount}checked {/if} /> <label for="filter_receipt_discount_id">Receipt Discount</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	{*{if $config.enable_mix_and_match_promotion}{/if}*}
	<input type="checkbox" class="inp_filter" name="filter_mm_discount" id="filter_mm_discount_id" value="1" {if $smarty.request.filter_mm_discount}checked {/if} /> <label for="filter_mm_discount_id">Mix & Match Discount</label>
	&nbsp;&nbsp;&nbsp;&nbsp;
</fieldset>
</p>

<input type="submit" name="submits" value="{#SHOW_REPORT#}" />
</form>


{if isset($smarty.request.submits)}
{if !$table}
No data
{else}
<span class="allow_open_price">* User who make allow open price.</span><br />
<span class="allow_item_discount">* User who make allow item discount.</span><br />
<span class="allow_receipt_discount">* User who make Allow Receipt discount.</span><br />
<span class="allow_cancelled_bill">* User who make Allow Cancelled Bills.</span><br />
<span class="allow_deleted_items">* User who make Allow Deleted Items.</span><br />
<span class="allow_prune_bill">* User who make Allow Prune Bills.</span><br />
<span class="span_cancelled_amount">C: Cancelled Transaction</span>

{foreach from=$table key=bid item=p}
<h3>{$branches.$bid.code}: {count var=$p} cashier(s)</h3>
<table width="100%" class="sortable report_table small_printing" id="table_{$bid}">
<tr class="header">
	<th rowspan="3">No.</th>
	<th rowspan="3">Cashier Name</th>
	<th rowspan="3">Work Day</th>
	
	{assign var=cols value=22}
	{if $got_mm_discount}{assign var=cols value=$cols+2}{/if}
	{if $show_variances}{assign var=cols value=$cols+1}{/if}
	<th colspan="{$cols}">Total</th>
	<th colspan="3">AVG</th>
</tr>
<tr class="header">
    <th rowspan="2">Amount</th>
    <th rowspan="2">Transaction [<a href="javascript:void(alert('Active Transaction Only'))">?</a>]</th>
    <th colspan="2">Open Price</th>
    <th colspan="3">Item Discount</th>
	<th colspan="3">Receipt Discount</th>
	{if $got_mm_discount}
		<th colspan="2">Mix & Match Discount</th>
	{/if}
    <th rowspan="2">Open Drawer</th>
    <th colspan="2">Cancelled Bills</th>
    <th colspan="2">Prune Bills</th>
    <th colspan="2">Delete Items</th>
    <th rowspan="2">Goods Return</th>
	{if $show_variances}
		<th rowspan="2">Variance</th>
	{/if}
    <th colspan="2">Member Sales</th>
    <th colspan="2">Non Member sales</th>
    <th rowspan="2">time per-transaction [<a href="javascript:void(alert('Including Cancelled Transaction'))">?</a>]</th>
    <th colspan="2">Daily</th>
</tr>
<tr class="header">
	<!-- Open Price -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Item Discount -->
	<th>Allow</th>
	<th>Count</th>
	<th>Amt</th>
	
	<!-- Receipt Discount -->
	<th>Allow</th>
	<th>Count</th>
	<th>Amt</th>
	
	<!-- Cancel Bill -->
	<th>Allow</th>
	<th>Count</th>

	<!-- Prune Bill -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Delete Items -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Mix and match discount -->
	{if $got_mm_discount}
		<th>Count</th>
		<th>Amt</th>
	{/if}
	<th>Transaction</th>
	<th>Amount</th>
	<th>Transaction</th>
	<th>Amount</th>
	<th>Transaction [<a href="javascript:void(alert('Including Cancelled Transaction'))">?</a>]</th>
	<th>Amount</th>
</tr>
	{foreach from=$p key=cid item=r name=f}
	    <tr class="clickable thover" onClick="load_details('{$bid}','{$cid}');">
	        <td>{$smarty.foreach.f.iteration}</td>
	        <td>{$r.u}
            	{if $r.allow_open_price}<span class="allow_open_price">*</span>{/if}
				{if $r.allow_item_discount}<span class="allow_item_discount">*</span>{/if}
				{if $r.allow_receipt_discount}<span class="allow_receipt_discount">*</span>{/if}
				{if $r.allow_cancelled_bill}<span class="allow_cancelled_bill">*</span>{/if}
				{if $r.allow_deleted_items}<span class="allow_deleted_items">*</span>{/if}
				{if $r.allow_prune_bill}<span class="allow_prune_bill">*</span>{/if}
			</td>
	        <td class="r">{count var=$r.day_work}</td>
	        <td class="r" nowrap>
				{$r.amount|number_format:2}
				{if $r.cancelled_amount}
					<br />
					<span class="span_cancelled_amount small" title="Cancelled Amount: {$r.cancelled_amount|number_format:2}">C: {$r.cancelled_amount|number_format:2}</span>
				{/if}
			</td>
	        <td class="r">
				{$r.tran_count|number_format}
			</td>
	        
	        <!-- Open Price-->
	        <td class="r">{$r.allow_open_price|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.open_price|number_format|ifzero:'-'}</td>
	        
	        
	        <!-- Item Discount-->
	        <td class="r">{$r.allow_item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount_amt|number_format:2|ifzero:'-'}</td>
	        
	        <!-- Receipt Discount-->
	        <td class="r">{$r.allow_receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount_amt|number_format:2|ifzero:'-'}</td>
	        
	        <!-- Mix and match discount -->
	        {if $got_mm_discount}
	        	<td class="r">{$r.mm_discount|number_format|ifzero:'-'}</td>
	        	<td class="r">{$r.mm_discount_amt|number_format:2|ifzero:'-'}</td>
	        {/if}
	        
			<!-- Open Drawer -->
	        <td class="r">{$r.drawer_open_count|number_format}</td>
			
			<!-- Cancel Bills -->
	        <td class="r">{$r.allow_cancelled_bill|number_format}</td>
	        <td class="r">{$r.cancelled_bill|number_format}</td>
			
			<!-- Prune Bills -->
	        <td class="r">{$r.allow_prune_bill|number_format}</td>
	        <td class="r">{$r.prune_bill|number_format}</td>
			
			<!-- Delete Items -->
	        <td class="r">{$r.allow_deleted_items|number_format}</td>
	        <td class="r">{$r.deleted_items|number_format}</td>
			
			<!-- Goods Return -->
	        <td class="r">{$r.total_goods_return|number_format}</td>
			{if $show_variances}
				<td class="r">{$r.variances|number_format:2}</td>
			{/if}
	        <td class="r">{$r.member_sells.qty|number_format}</td>
	        <td class="r">{$r.member_sells.amount|number_format:2}</td>
	        <td class="r">{$r.non_member_sells.qty|number_format}</td>
	        <td class="r">{$r.non_member_sells.amount|number_format:2}</td>
	        <td class="r">
			{if $r.avg_tran_time_hour}{$r.avg_tran_time_hour} hours{/if}
			{if $r.avg_tran_time_min}{$r.avg_tran_time_min} mins{/if}
			{if $r.avg_tran_time_sec}{$r.avg_tran_time_sec} secs{/if}
			</td>
			<td class="r">{$r.avg_qty|number_format}</td>
			<td class="r" nowrap>
				{$r.avg_amount|number_format:2}
				{if $r.avg_cancelled_amount}
					<br />
					<span class="span_cancelled_amount small" title="Cancelled Amount: {$r.avg_cancelled_amount|number_format:2}">C: {$r.avg_cancelled_amount|number_format:2}</span>
				{/if}
			</td>
	    </tr>
	{/foreach}
</table>
{/foreach}
{/if}
{/if}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}
{include file=footer.tpl}
