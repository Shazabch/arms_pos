{*
1/5/2011 3:35:10 PM Andy
- Remove all counter all branches selection.
- Fix counters dropdown bugs, it cannot select back the last submited selection.

3/31/2011 6:15:58 PM Justin/Andy
- Fixed the item detail window out of range problem.

5/18/2011 3:51:35 PM Andy
- Add pass document FORM name when include the autocomplete templates

6/27/2011 5:41:51 PM Andy
- Add header to every page printing.

6/28/2011 9:38:31 AM Andy
- Show print button for user when report got data. 

11/11/2011 11:30:46 AM Andy
- Transaction Details Add mix and match info.

11/28/2011 5:43:32 PM Justin
- Added new filter "FOC".

9/21/2012 3:12 PM Andy
- Add checking to limit to only load 30 days of data.
- Add notice for user to know the limitation length of data.

10/3/2012 11:16 AM Justin
- Enhanced to show prune status.

10/8/2012 9:59 AM Justin
- Enhnanced to have filter of "Pruned" status.

10/8/2012 4:22 PM Andy
- Add to show transaction cancelled/pruned by.

10/9/2012 4:12 PM Andy
- Change row information to include in sub tpl.
- Re-write javascript by using object oriented style.
- Limit report to only show 1000 pos at a time, need user to click "show more" to load additional pos.

10/11/2012 10:53 Am Andy
- Add legend to tell user report maximumn show 1000 transaction at a time.

10/18/2012 2:33 PM Justin
- Enhanced to have filter for refund receipt.

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

7/4/2013 2:36 PM Andy
- Enhance to show cancel at backend in transaction list.

3/7/2014 5:34 PM Justin
- Enhanced to remove the number limit of receipt no.

4/22/2015 11:36 AM Andy
- Increase the width of transaction details.

05/06/2016 17:00 Edwin
- Add new table column "Receipt Remark" at Transaction Details.

3/2/2017 9:59 AM Justin
- Enhanced to include deposit deduction on total amount.

4/19/2017 4:33 PM Justin
- Enhanced to show Receipt Ref No. column.

7/13/2018 4:08 PM Justin
- Enhanced payment type filter to have foreign currency selection.

5/3/2019 9:42 PM William
- Add new select of cashier filter. 

5/5/2020 5:15 PM Justin
- Enhanced to have receipt amount filter.

6/10/2020 12:55 PM William
- Enhanced to change "Receipt No" to dropdown and able filter by receipt no or receipt ref no.
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
<script>var phpself = '{$smarty.server.PHP_SELF}';</script>
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

.sup_cancel_at_backend{
	color: red;
	background-color: yellow;
}
</style>
{/literal}

<script type="text/javascript">
var LOADING = '<img src="/ui/clock.gif" />';
var max_row_no = 0;
var filter_last_date = '{$filter_last_date}';
var filter_last_time = '{$filter_last_time}';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

var POS_TRAN_DETAILS = {
	f: undefined,
	filter_last_date: filter_last_date,
	filter_last_time: filter_last_time,
	last_submit_params: '',
	initialize: function(){
		this.f = document.myForm;
		
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
	    
	    this.toggleTimeFilter();
	    
	    this.last_submit_params = $(this.f).serialize();
	},
	// function to check and whether enable/disable time from/to
	toggleTimeFilter: function(){
		if($('filter_time').checked){
			this.showTime(false);
		}else{
	        this.showTime(true);
		}
	},
	// function to enable/disable time from/to
	showTime: function(isShow){
		document.myForm.from_time_Hour.disabled = isShow;
		document.myForm.from_time_Minute.disabled = isShow;
		document.myForm.to_time_Hour.disabled = isShow;
		document.myForm.to_time_Minute.disabled = isShow;
	},
	// function when user click print
	do_print: function (){
		window.print();
	},
	// function when user click show report
	show_report: function(){
		for(var i=0; i<$('sku_code_list').length; i++){
		    $('sku_code_list').options[i].selected = true;
		}
		
		this.f.submit();
	},
	// function when user click receipt no
	items_details: function (branch_id,counter_id,id,date){
		
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
				branch_id: branch_id,
				pos_id: id,
				date: date
			}
		});
	},
	// function when user click show more 
	show_more_clicked: function(bid){
		var THIS = this;
		var params = this.last_submit_params+'&a=ajax_show_more_data&filter_last_date='+this.filter_last_date+'&filter_last_time='+this.filter_last_time+'&filter_bid='+bid;
		params += '&max_row_no='+max_row_no;
		
		var btn_show_more = $('btn_show_more');
		$('span_show_more_loading').show();
		
		btn_show_more.disabled = true;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				// hide the loading icon
			    btn_show_more.disabled = false;
			    $('span_show_more_loading').hide();
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
	                	if(ret['html']){
	                		new Insertion.Bottom('tbody_pos_list-'+bid, ret['html']);
	                		max_row_no = ret['max_row_no'];
	                		
	                		THIS.filter_last_date = ret['filter_last_date'];
	                		THIS.filter_last_time = ret['filter_last_time'];
	                		
	                		THIS.recal_total(bid);
	                	}else{
	                		alert('There is no more item to load.');
	                		$(btn_show_more).hide();
	                	}
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
	// function recalculate total tran, total qty and total amt
	recal_total: function(bid){
		var row_count = 0;
		var total_qty = 0;
		var total_amt = 0;
		
		$$('#tbody_pos_list-'+bid+' tr.tr_pos_row').each(function(tr){
			row_count++;
			
			var span_pos_qty = $(tr).getElementsBySelector('span.span_pos_qty');
			var span_pos_amt = $(tr).getElementsBySelector('span.span_pos_amt');
			
			total_qty += float(span_pos_qty[0].innerHTML);
			total_amt += float(span_pos_amt[0].innerHTML);
		});
		// total tran
		$('span_branch_pos_count-'+bid).update(row_count);
		
		// total qty
		$('span_branch_total_qty-'+bid).update(total_qty);
		
		// total amt
		$('span_branch_total_amt-'+bid).update(number_format(total_amt,2));
	},
	
	receipt_amt_type_changed: function(obj){
		if(obj.value == "between"){
			$('default_receipt_amt').hide();
			$('between_receipt_amt').show();
			this.f['default_receipt_amt_val'].value = "";
		}else{
			$('default_receipt_amt').show();
			$('between_receipt_amt').hide();
			this.f['min_receipt_amt_val'].value = "";
			this.f['max_receipt_amt_val'].value = "";
			
		}
	}
}

function sortable_callback(tbl){
	var bid = tbl.id.split("-")[1];
	
	// fix after sort table the footer jump into tbody, move it back to tfooter
	$('tfoot_pos_list-'+bid).appendChild($('tr_pos_list_footer-'+bid));
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


<!-- Item Details -->
<div id="div_item_details" style="display:none;width:750px;height:450px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form method="post" name="myForm" class="form" onSubmit="return false();">
			<input type="hidden" name="a" value="load_table" />
			<p nowrap>
				
			<div class="row">
				<div class="col-md-3">
					<b class="form-label mt-2">From</b> 
				<div class="form-inline">
					<input class="form-control" size=17 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label mt-2">To</b> 
				<div class="form-inline">
					<input class="form-control" size=17 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-6">
					<b class="form-label mt-2">Counter</b> 
				<div class="form-inline">
					<select class="form-control" name="counters">
						{*<option value="all">-- All --</option>*}
						{foreach from=$counters item=r}
							{capture assign=counter_all}{$r.branch_id}|all{/capture}
							{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
							{if $last_bid ne $r.branch_id}
								<option value="{$counter_all}" {if $smarty.request.counters eq $counter_all}selected {/if}>{$r.code}</option>
								{assign var=last_bid value=$r.branch_id}
							{/if}
							<option value="{$counter_item}" {if $smarty.request.counters eq $counter_item}selected {/if}>
								{$r.network_name}
							</option>
						{/foreach}
						</select>
						
						
						&nbsp;<select class="form-control" name="receipt_type">
							<option {if $smarty.request.receipt_type eq 'receipt_ref_no'}selected{/if} value="receipt_ref_no">Receipt Ref No</option>
							<option {if $smarty.request.receipt_type eq 'receipt_no'}selected{/if} value="receipt_no">Receipt No</option>
						</select> 
						&nbsp;<input class="form-control" type="text" name="receipt_no" value="{$smarty.request.receipt_no}" />
						&nbsp;<input type="checkbox" name="filter_time" id="filter_time" onChange="POS_TRAN_DETAILS.toggleTimeFilter();" {if $smarty.request.filter_time} checked {/if} />
				</div>
			</div>
			</div>
		<div class="row">
			<div class="col-md-3">
				<b class="form-label mt-2">Time from</b>
			{html_select_time use_24_hours=true display_seconds=false prefix=from_time_ time=$smarty.request.from_time}
			</div>
			
			<div class="col-md-3">
				<b class="form-label mt-2">To</b>
			{html_select_time use_24_hours=true display_seconds=false prefix=to_time_ time=$smarty.request.to_time}
			
			</div>

			<div class="col-md-3">
				<b class="form-label mt-2">FOC</b>
			<select class="form-control" name="foc">
				<option value="">-- All --</option>
				<option value="yes" {if $smarty.request.foc eq 'yes'}selected{/if}>Yes</option>
				<option value="no" {if $smarty.request.foc eq 'no'}selected{/if}>No</option>
			</select>
			</div>
		</div>
			</p>
			<p>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Payment Type</b> 
			<select class="form-control" name="payment_type">
			<option value="all">-- All --</option>
				{foreach from=$pos_config.payment_type item=p}
					<option value="{$p}" {if $smarty.request.payment_type eq $p}selected {/if}>{$p}</option>
					{if $p eq 'Credit Cards'}
						{foreach from=$pos_config.credit_card item=c}
							<option value="{$c}" {if $smarty.request.payment_type eq $c}selected {/if}>&nbsp;{$c}</option>
						{/foreach}
					{/if}
				{/foreach}
				{if $config.foreign_currency}
					<option value="Foreign Currency" {if $smarty.request.payment_type eq 'Foreign Currency'}selected {/if}>Foreign Currency</option>
					{foreach from=$config.foreign_currency key=curr_code item=curr_info}
						<option value="{$curr_code}" {if $smarty.request.payment_type eq $curr_code}selected {/if}>&nbsp;{$curr_code}</option>
					{/foreach}
				{/if}
			</select>
				</div>

			<div class="col-md-4"><b class="form-label">Transaction Status</b> 
				<select class="form-control" name="tran_status">
				<option value="all">-- All --</option>
				{foreach from=$transaction_status key=status item=t}
					<option value="{$status}" {if $smarty.request.tran_status eq $status}selected {/if}>{$t}</option>
				{/foreach}
				</select></div>

		<div class="col-md-4">
			<b class="form-label">Transaction Type</b> 
			<select class="form-control" name="tran_type">
			<option value="all">-- All --</option>
			{foreach from=$transaction_type key=type item=t}
				<option value="{$type}" {if $smarty.request.tran_type eq $type}selected {/if}>{$t}</option>
			{/foreach}
			</select>
		</div>
			
			<div class="col-md-4">
				<b class="form-label">Transaction Filter</b> 
			<select class="form-control" name="other_filter">
			<option value="all" {if !$smarty.request.other_filter || $smarty.request.other_filter eq 'all'}selected {/if}>-- All --</option>
			{foreach from=$trans_filter key=type item=desc}
				<option value="{$type}" {if $smarty.request.other_filter eq $type}selected {/if}>{$desc}</option>
			{/foreach}
			</select>
			</div>
			
			<div class="col-md-4">
				<b class="form-label">Cashier</b> 
			<select class="form-control" name="cashier_id">
				<option value="" >-- All --</option>
				{foreach from=$cashier key=cashier_id item=r}
					<option value="{$cashier_id}" {if $smarty.request.cashier_id eq $cashier_id}selected {/if}>{$r.u}</option>
				{/foreach}
			</select>
			</div>
			
			<div class="col-md-8">
				<b class="form-label">Receipt Amount</b>
			<div class="form-inline">
				<select class="form-control" name="receipt_amt_type" onchange="POS_TRAN_DETAILS.receipt_amt_type_changed(this);">
					{foreach from=$receipt_amt_type_list key=ra_type item=ra_desc}
						<option value="{$ra_type}" {if $smarty.request.receipt_amt_type eq $ra_type}selected{/if}>{$ra_desc}</option>
					{/foreach}
				</select>
				&nbsp;<span id="default_receipt_amt" {if $smarty.request.receipt_amt_type eq 'between'}style="display:none;"{/if}>
					<input class="form-control" type="text" name="default_receipt_amt_val" value="{$smarty.request.default_receipt_amt_val}" onchange="mf(this);" />
				</span>
				&nbsp;<span id="between_receipt_amt" {if $smarty.request.receipt_amt_type ne 'between'}style="display:none;"{/if}>
					<input class="form-control" type="text" name="min_receipt_amt_val" value="{$smarty.request.min_receipt_amt_val}" onchange="mf(this);" />
					&nbsp;To&nbsp;
					<input class="form-control" type="text" name="max_receipt_amt_val" value="{$smarty.request.max_receipt_amt_val}" onchange="mf(this);" />
				</span>
			</div>
			</div>
			</div>
			</p>
			{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.myForm'}
			<input class="btn btn-primary" type="button" value="{#SHOW_REPORT#}" onClick="POS_TRAN_DETAILS.show_report();" />
			{if $table}
				<input type="button" onclick="POS_TRAN_DETAILS.do_print();" value="Print" />
			{/if}
			
			<div class="alert alert-primary rounded mt-2" style="max-width: 500px;">
				<ul>
					<li> Report maximum show 30 days of transaction.</li>
					<li> Report maximum show {$sheet_size} transactions at a time.</li>
				</ul>
			</div>
			</form>
	</div>
</div>

{literal}
<script type="text/javascript">
    POS_TRAN_DETAILS.initialize();
</script>
{/literal}

<!--{$branch_id} {$counter_id}-->
{if $smarty.request.a eq 'load_table'}
{if !$table}
No data
{else}
{foreach from=$table key=bid item=p}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$branches.$bid}: 
		<span id="span_branch_pos_count-{$bid}">{count var=$p}</span> transaction(s)
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table width="100%" class="sortable report_table table mb-0 text-md-nowrap  table-hover" id="tbl_pos_list-{$bid}">
					<thead class="bg-gray-100">
						<tr class="header">
							<th>No.</th>
							<th>Receipt No.</th>
							<th>Receipt Ref No.</th>
							<th>date</th>
							<th>Counter</th>
							<th>Cashier</th>
							<th>Time</th>
							<th>Payment Type</th>
							<th>Transaction Status</th>
							<th>Transaction type</th>
							 <th>Goods Return</th>
							 <th>Open Price</th>
							<th>Mix & Match</th>
							<th> Receipt Remark</th>
							 {if $smarty.request.payment_type ne 'all'}<th>Payment Amount </th>{/if}
							 <th>Qty</th>
							 <th>Receipt Amount</th>
						</tr>
					</thead>
					<tbody class="fs-08" id="tbody_pos_list-{$bid}">
						{foreach from=$p item=p name=f}
							{assign var=row_no value=$smarty.foreach.f.iteration}
							{include file="pos_report.tran_details.row.tpl"}
						{/foreach}
					</tbody>	
					<tfoot class="sortbottom tfoot_pos_list fs-08" id="tfoot_pos_list-{$bid}">
					<tr class="header sortbottom" id="tr_pos_list_footer-{$bid}">
						{assign var=cols value=14}
						{if $smarty.request.payment_type ne 'all'}{assign var=cols value=$cols+1}{/if}
						<td colspan="{$cols}">
							{if $can_show_more}
								<input type="button" value="Show More" class="noprint" id="btn_show_more" onClick="POS_TRAN_DETAILS.show_more_clicked('{$bid}');" />
								<span id="span_show_more_loading" style="display:none;background: yellow;padding:2px;">
									<img src="/ui/clock.gif" align="absmiddle" /> Loading…
								</span>
							{/if}
							<div style="float:right;font-weight:bold;">Total</div>
						</td>
						<td class="r"><span id="span_branch_total_qty-{$bid}">{$total.$bid.total_qty|number_format}</span></td>
						<td class="r"><span id="span_branch_total_amt-{$bid}">{$total.$bid.amount-$total.$bid.deposit_amount|number_format:2}</span></td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		max_row_no = '{$row_no}';
	</script>
{/foreach}
{/if}
{/if}

{include file=footer.tpl}
