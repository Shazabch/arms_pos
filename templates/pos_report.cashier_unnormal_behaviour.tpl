{*
8/9/2010 4:29:36 PM Andy
- Add "Open Price" and "Item Discount" Info.

10/24/2011 1:20:42 PM Andy
- Add show Receipt Discount info.

1/12/2012 10:13:43 AM Justin
- Added Prune Count column.

8/7/2014 5:02 PM Fithri
- add two new columns, Deleted Items Count and Over 30 Minutes Transaction
- figures in some columns is made clickable, clicking on them will show transaction details (receipt).

12/11/2015 3:58 PM DingRen
- Add Special Exempt
- delete service charge
- Open drawer show by type
- show/highlight the info of like how many denom did vs open drawer of denom

11/19/2018 11:38 AM Justin
- Enhanced to have Allow Cancelled Bills, Deleted Items and Prune Bills columns.

5/6/2019 11:17 AM William
- Enhanced Column "Transaction", add a remark to tell users it is including Active + Cancelled Transaction.

7/15/2019 1:36 PM Andy
- Fixed when show details info the data not tally.
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
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var date_from = '{$smarty.request.date_from}';
var date_to = '{$smarty.request.date_to}';
var LOADING = '<img src="/ui/clock.gif" />';
</script>
{literal}
<style>
.allow_open_price{
	color: red;
}
.allow_item_discount{
	color: blue;
}
.allow_receipt_discount{
	color: green;
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
<script>
function show_info(t,bid,uid,content_id) {
    curtain(true);
    hidediv('div_item_details');
    center_div('div_tran_details');
    
    $('div_tran_details').show();
	if (content_id!=undefined) {
        $('div_trans_content').update($(content_id).innerHTML);
    }
	else{
	$('div_trans_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_trans_content',phpself,
	{
	    method: 'post',
	    parameters: 'a=show_info&type='+t+'&bid='+bid+'&uid='+uid+'&date_from='+date_from+'&date_to='+date_to,
	    evalScripts: true,
	});
	}
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
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<!-- Item Details -->
<div id="div_details" style="display:none;width:800px;height:400px;">
<div style="float:right;padding-bottom:5px;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_content">
</div>
</div>

<form method="post" name="myForm" class="form">
<input type="hidden" name="a" value="load_table" />
<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Cashier</b> <select name="cashier_id">
	<option value="all">-- All --</option>
	{foreach from=$cashier key=cid item=r}
	    <option value="{$cid}" {if $smarty.request.cashier_id eq $cid}selected {/if}>{$r.u}</option>
	{/foreach}
</select><br />
<p>
<b>Cancelled Bill at least</b> <input type="text" name="cancelled_bill" value="{$smarty.request.cancelled_bill}" size="5" />
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Goods Return at least</b> <input type="text" name="goods_return" value="{$smarty.request.goods_return}" size="5" />
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Open Draw Count - Transaction</b>
<select name="diff_type">
    <option value="not_set" {if $smarty.request.diff_type eq 'not_set'}selected {/if}>Not Set</option>
	<option value="more" {if $smarty.request.diff_type eq 'more'}selected {/if}>More Than</option>
	<option value="less" {if $smarty.request.diff_type eq 'less'}selected {/if}>Less Than</option>
</select>
<input type="text" name="diff_open_tran" value="{$smarty.request.diff_open_tran}" size="5" />
</p>
<p style="margin:0;">
	<b>Note:</b>
	<ul style="padding:0;">
		<li style="padding:0;">Transaction column including Active and Cancelled Transaction</li>
	</ul>
</p>
<input type="submit" name="submits" value="{#SHOW_REPORT#}" />
</form>

<div id="div_tran_details" style="overflow:hidden;position:absolute;left:0;top:0;display:none;width:600px;height:410px;padding:10px;border:1px solid #000;background:#fff;z-index:20000;">
	<div style="float:right;"><img onclick="curtain_clicked('div_tran_details'); curtain(false);" src="/ui/closewin.png" /></div>
	<div id="div_trans_content"></div>
</div>

<div id="div_item_details" style="display:none;width:800px;height:550px;">
	<div style="float:right;"><img onclick="curtain_clicked('div_item_details');" src="/ui/closewin.png" /></div>
	<h3 align="center">Items Details</h3>
	<div id="div_item_content"></div>
</div>

{if isset($smarty.request.submits)}
{if !$table}
No data
{else}
<span class="allow_open_price">* User who make Allow Open Price.</span><br />
<span class="allow_item_discount">* User who make Allow Item Discount.</span><br />
<span class="allow_receipt_discount">* User who make Allow Receipt discount.</span><br />
<span class="allow_cancelled_bill">* User who make Allow Cancelled Bills.</span><br />
<span class="allow_deleted_items">* User who make Allow Deleted Items.</span><br />
<span class="allow_prune_bill">* User who make Allow Prune Bills.</span>

{foreach from=$table key=bid item=p}
<h3>{$branches.$bid}: {count var=$p} cashier(s)</h3>
<table width="100%" class="sortable report_table small_printing" id="table_{$bid}">
<tr class="header">
    <th>No.</th>
	<th>Cashier Name</th>
	<th>Allow Cancelled Bills</th>
	<th>Cancelled Bills</th>
	<th>Allow Deleted Items</th>
	<th>Deleted Items</th>
	<th>Over 30 Minutes Transaction</th>
	<th>Allow Prune Bills</th>
	<th>Prune Bills</th>
	<th>Goods Return</th>
	<th>Transaction</th>
	<th>Special Exempt</th>
	<th>Remove Service Charge</th>
	<th>Open Drawer</th>
	<th>Different</th>
	<th>Allow Open Price</th>
	<th>Open Price</th>
	<th>Allow Item Discount</th>
	<th>Item Discount</th>
	<th>Item Discount Amt</th>
	<th>Allow Receipt Discount</th>
	<th>Receipt Discount</th>
	<th>Receipt Discount Amt</th>
</tr>
    {foreach from=$p key=cid item=r name=f}
        <tr>
            <td>{$smarty.foreach.f.iteration}</td>
	        <td>{$r.u|default:'-'}
				{if $r.allow_open_price}<span class="allow_open_price">*</span>{/if}
				{if $r.allow_item_discount}<span class="allow_item_discount">*</span>{/if}
				{if $r.allow_receipt_discount}<span class="allow_receipt_discount">*</span>{/if}
				{if $r.allow_cancelled_bill}<span class="allow_cancelled_bill">*</span>{/if}
				{if $r.allow_deleted_items}<span class="allow_deleted_items">*</span>{/if}
				{if $r.allow_prune_bill}<span class="allow_prune_bill">*</span>{/if}
			</td>
			<td class="r">
				{$r.allow_cancelled_bill|number_format}
	        </td>
	        <td class="r">
				{if $r.cancelled_bill}
					<a href="javascript:;" onclick="javascript:show_info('cancelled_bill','{$r.branch_id}','{$r.user_id}')">
					{$r.cancelled_bill|number_format}
					</a>
				{else}
					{$r.cancelled_bill|number_format}
				{/if}
	        </td>
	        <td class="r">
				{$r.allow_deleted_items|number_format}
	        </td>
	        <td class="r">
				{if $r.deleted_items}
					<a href="javascript:;" onclick="javascript:show_info('deleted_items','{$r.branch_id}','{$r.user_id}')">
					{$r.deleted_items|number_format}
					</a>
				{else}
					{$r.deleted_items|number_format}
				{/if}
	        </td>
	        <td class="r">
				{if $r.over_30min}
					<a href="javascript:;" onclick="javascript:show_info('over_30min','{$r.branch_id}','{$r.user_id}')">
					{$r.over_30min|number_format}
					</a>
				{else}
					{$r.over_30min|number_format}
				{/if}
	        </td>
	        <td class="r">
				{$r.allow_prune_bill|number_format}
	        </td>
	        <td class="r">
				{if $r.prune_bill}
					<a href="javascript:;" onclick="javascript:show_info('prune_bill','{$r.branch_id}','{$r.user_id}')">
					{$r.prune_bill|number_format}
					</a>
				{else}
					{$r.prune_bill|number_format}
				{/if}
	        </td>
	        <td class="r">
				{if $r.total_goods_return}
					<a href="javascript:;" onclick="javascript:show_info('total_goods_return','{$r.branch_id}','{$r.user_id}')">
					{$r.total_goods_return|number_format}
					</a>
				{else}
					{$r.total_goods_return|number_format}
				{/if}
	        </td>
	        <td class="r">{$r.tran_count|number_format}</td>
			<td class="r">
				{if $r.special_exemption_count}
					<a href="javascript:;" onclick="javascript:show_info('special_exemption','{$r.branch_id}','{$r.user_id}')">
					{$r.special_exemption_count|number_format}
					</a>
				{else}
					{$r.special_exemption_count|number_format}
				{/if}
			</td>
			<td class="r">
				{if $r.remove_service_charges_count}
					<a href="javascript:;" onclick="javascript:show_info('remove_service_charges','{$r.branch_id}','{$r.user_id}')">
					{$r.remove_service_charges_count|number_format}
					</a>
				{else}
					{$r.remove_service_charges_count|number_format}
				{/if}
			</td>
	        <td class="r">
				{if $r.drawer_open_count}
					<a href="javascript:;" onclick="javascript:show_info('remove_service_charges','{$r.branch_id}','{$r.user_id}','drawer_open_{$r.branch_id}_{$r.user_id}')">
						{$r.drawer_open_count|number_format}
					</a>
					{if $r.drawer_open}
					<div id="drawer_open_{$r.branch_id}_{$r.user_id}" style="display:none;">
						<h1>Open Drawer</h1>
						<table class="tb" width="100%" cellspacing="0" cellpadding="4" border="0">
							<tr class="header" style="background:#fe9">
								<th>Type</th>
								<th>Open Count / Complete Count</th>
							</tr>
							{foreach from=$r.drawer_open key=type item=c}
							<tr>
								<td>{$type}</td>
								<td>{$c.count}{if $c.actual_count} / {$c.actual_count}{/if}</td>
							</tr>
							{/foreach}
						</table>
					</div>
					{/if}
				{else}
					{$r.drawer_open_count|number_format}
				{/if}
			</td>
	        <td class="r">{$r.diff_open_tran|number_format}</td>
	        <td class="r">{$r.allow_open_price|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.open_price|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.allow_item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount_amt|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.allow_receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount_amt|number_format:2|ifzero:'-'}</td>
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
