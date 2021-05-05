{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.
*}

{include file="header.tpl"}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var POS_RETURN_ITEM = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
	        inputField     :    "inp_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_from",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });
	
	    Calendar.setup({
	        inputField     :    "inp_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_to",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });
	},
	show_report: function(){
		for(var i=0; i<$('sku_code_list').length; i++){
		    $('sku_code_list').options[i].selected = true;
		}
		
		this.f.submit();
	},
	// function when user click print
	do_print: function (){
		window.print();
	},
	// function when user click receipt no
	items_details: function (branch_id,counter_id,id,date){
		
		curtain(true);
	    center_div($('div_item_details'));
	
	    $('div_item_details').show()
		$('div_item_content').update(_loading_);
	
		new Ajax.Updater('div_item_content','counter_collection.php',
		{
		    method: 'post',
		    parameters:{
				a: 'item_details',
				counter_id: counter_id,
				branch_id: branch_id,
				pos_id: id,
				date: date
			}
		});
	}
};

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:700px;height:450px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}


<form method="post" name="f_a" class="form" onSubmit="return false();">
	<input type="hidden" name="load_data" value="1" />
	<p nowrap>
		<b>From</b> 
		<input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Counter</b> 
		<select name="counters">
			{foreach from=$counters item=r}
				{capture assign=counter_all}{$r.branch_id}|all{/capture}
				{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
				{if $last_bid ne $r.branch_id}
				    <option value="{$counter_all}" {if $smarty.request.counters eq $counter_all}selected {/if}>{$r.code}</option>
				    {assign var=last_bid value=$r.branch_id}
				{/if}
				<option value="{$counter_item}" {if $smarty.request.counters eq $counter_item}selected {/if}>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$r.network_name}
				</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Receipt No</b> 
		<input type="text" name="receipt_no" value="{$smarty.request.receipt_no}" size="8" />
	</p>
	
	<p>
		<b>Transaction Status</b> 
		<select name="tran_status">
			<option value="all">-- All --</option>
			{foreach from=$transaction_status_list key=status item=t}
				<option value="{$status}" {if $smarty.request.tran_status eq $status}selected {/if}>{$t}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Transaction type</b> 
		<select name="tran_type">
		<option value="all">-- All --</option>
		{foreach from=$transaction_type_list key=type item=t}
			<option value="{$type}" {if $smarty.request.tran_type eq $type}selected {/if}>{$t}</option>
		{/foreach}
		</select>
	</p>
	
	{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a'}
	
	<input type="button" value="{#SHOW_REPORT#}" onClick="POS_RETURN_ITEM.show_report();" />
	{if $data}
		<input type="button" onclick="POS_RETURN_ITEM.do_print();" value="Print" />
	{/if}
	
	<ul>
		<li> Report maximum show 30 days of transaction.</li>
	</ul>
</form>

<script type="text/javascript">
	POS_RETURN_ITEM.initialize();
</script>

{if $smarty.request.load_data and !$err}
	{if !$data}-- No Data --
	{else}
		<h3>{$report_title}</h3>
		
		<table width="100%" class="report_table sortable" id="tbl_content">
			<thead>
				<tr class="header">
					<th width="20">No.</th>
					<th>Receipt No.</th>
					<th>Date</th>
					<th>Counter</th>
					<th>Cashier</th>
					<th>Time</th>
					<th>Transaction Status</th>
					<th>Transaction Type</th>
					<th>ARMS Code</th>
					<th>MCode</th>
					<th>Art No</th>
					<th>Description</th>
					<th>Qty</th>
					<th>Amount</th>
				</tr>
			</thead>
			
			
			{foreach from=$data.pi_list item=pi name=f_pi}
				<tr>
					<td>{$smarty.foreach.f_pi.iteration}.</td>
					<td><a href="javascript:void(POS_RETURN_ITEM.items_details('{$pi.branch_id}','{$pi.counter_id}','{$pi.pos_id}','{$pi.date}'))">{receipt_no_prefix_format branch_id=$pi.branch_id counter_id=$pi.counter_id receipt_no=$pi.receipt_no}</a></td>
					<td>{$pi.date}</td>
					<td>{$pi.network_name}</td>
					<td>{$pi.cashier_u}</td>
					<td>{$pi.pos_time|date_format:'%H:%M:%S'}</td>
					<td>
						{if !$pi.cancel_status}
							Valid 
						{else}
							{if $pi.prune_status && $pi.cancel_status}
								Pruned
							{else}
								Cancelled
							{/if}
							{if $pi.cancelled_by_u}
								<br /><span class="small" style="color:blue;">(by {$pi.cancelled_by_u})</span>
							{/if}
						{/if}</td>
				    <td>{if $pi.member_no}Member {else}Non-member{/if}</td>
				    <td>{$pi.sku_item_code|default:'-'}</td>
				    <td>{$pi.mcode|default:'-'}</td>
				    <td>{$pi.artno|default:'-'}</td>
				    <td>{$pi.description|default:'-'}</td>
				    <td class="r">{$pi.qty}</td>
				    <td class="r">{$pi.amt|number_format:2}</td>
				</tr>
			{/foreach}
			
			<tfoot class="sortbottom tfoot_pi_list">
			<tr class="header sortbottom">
				{assign var=cols value=12}
			    <td colspan="{$cols}">
			    	<div style="float:right;font-weight:bold;">Total</div>
			    </td>
			    <td class="r">{$data.total.qty}</td>
			    <td class="r">{$data.total.amt|number_format:2}</td>
			</tr>
			</tfoot>
		</table>
	{/if}
	
{/if}

{include file="footer.tpl"}