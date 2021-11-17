{*
07/31/2013 04:43 PM Justin
- Enhanced to have cashier filter.

08/01/2013 04:35 PM Justin
- Enhanced to have show date details by cashier.

3/21/2014 3:57 PM Justin
- Enhanced to show custom payment type label if found it is set.
- Modified the wording from "Finalize" to "Finalise".

6/2/2015 4:31 PM Justin
- Enhanced to have total cash advance column.

4/25/2017 9:57 AM Khausalya
- Enhanced changes from RM to use config setting. 

7/10/2017 11:12 Qiu Ying
- Bug fixed on cashier variance report filter by counter

2017-09-14 15:04 PM Qiu Ying
- Enhanced to split by branch when in HQ

01/04/2019 04:47 PM Justin
- Revamped the report.
- Enhanced to include the missing foreign currency info.

06/30/2020 04:43 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}

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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.bold{
	font-weight: bold;
}
.weekend{
	color:red;
}
.col_foreign_curr{
    background: #ffc;
}
.col_variance{
    background: #F0FF00 !important;	
}

.date_details{
	background: #C6DEFF !important;
}

.date_details_variance{
    background: #C0FF00 !important;	
}
</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var branch_id = "{$smarty.request.branch_id}";
var split_counter = "{$smarty.request.split_counter}";
var tmp_counter_id = "{$smarty.request.counter_id}";
var payment_type_list = [];
var foreign_currency_list = [];
{foreach from=$normal_payment_type item=payment_type}
    var payment_type = '{$payment_type|urldecode}';
	payment_type_list.push(payment_type);
{/foreach}

{if $foreign_currency_list}
	{foreach from=$foreign_currency_list key=fc_code item=fc_rate}
		var fc_code = '{$fc_code|urldecode}';
		foreign_currency_list.push(fc_code);
	{/foreach}
{/if}

{literal}
function view_type_check(){
	if($('date_from').value > $('date_to').value){
		alert('Date Start cannot be late than Date End');
		return false;
	}
}

function get_counter_name(val){
	var branch_id=val;
	
	if(val == "all"){
		document.f_a["counter_id"].selectedIndex = "0";
		document.f_a["cashier_id"].selectedIndex = "0";
		$('span_counter').hide();
		$('span_cashier').hide();
		$('span_split_counter').hide();
		return;
	}else{
		$('span_counter').show();
		$('span_cashier').show();
		$('span_split_counter').show();
	}
	
	$('counter_id').update(_loading_);
	
	new Ajax.Updater('counter_id', 'report.daily_counter_collection.php',
		{
	    method: 'post',
	    parameters:{
			a: 'get_counter_name',
			branch_id: branch_id
		}
	});
}

function show_date_details(parent_cashier_id, parent_cid, parent_bid, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$(".tr_cashier_child_"+parent_cashier_id+"_"+parent_cid+"_"+parent_bid);
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).show();
		}
		
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).hide();
		}
	}
	
	if(all_tr.length>0)	return false;
	
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself, {
		method: 'post',
		parameters: {
			a: 'ajax_show_date_details',
			ajax: 1,
			counter_id: parent_cid,
			cashier_id: parent_cashier_id,
			date_from: date_from,
			date_to: date_to,
			branch_id: parent_bid,
			split_counter: split_counter,
			tmp_counter_id: tmp_counter_id,
			'payment_type_list[]': payment_type_list,
			'foreign_currency_list[]': foreign_currency_list
		},
		onComplete: function(e){
			new Insertion.After($('tr_cashier_'+parent_cashier_id+'_'+parent_cid+'_'+parent_bid), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

{/literal}
</script>
{/if}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul>
</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">

			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id" id="branch_id" onchange='get_counter_name(this.value)'>
						<option value='all' {if $smarty.request.branch_id eq 'all'}selected{/if}>- All -</option>
						{foreach from=$branches item=b}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
				{/if}
				</div>
				
				
					<span id="span_counter" {if (!$smarty.request.branch_id || $smarty.request.branch_id eq 'all') && $BRANCH_CODE eq 'HQ'}style="display:none;"{/if}>
						<div class="col-md-4">
						<b class="form-label">Counter</b>
						<span id="counter_id">
						<select class="form-control" name="counter_id" >
							{if !$counters}
								<option value=''>No Data</option>
							{else}
								{foreach name=counter_total from=$counters item=c}
								{/foreach}
					
								{if $smarty.foreach.counter_total.total >1 }
									<option value='all'>- All -</option>
								{/if}
								{foreach from=$counters item=c}
									<option value="{$c.counter_id}" {if $smarty.request.counter_id eq $c.counter_id}selected {/if}>{$c.network_name}</option>
								{/foreach}
							{/if}
						</select>
						</span>
					</div>
					</span>
				
				
				
					<span id="span_cashier" {if (!$smarty.request.branch_id || $smarty.request.branch_id eq 'all') && $BRANCH_CODE eq 'HQ'}style="display:none;"{/if}>
						<div class="col-md-4">
						<b class="form-label">Cashier</b>
						<span id="cashier_id">
						<select class="form-control" name="cashier_id" >
							{if !$cashiers}
								<option value=''>No Data</option>
							{else}
								{foreach name=cashier_total from=$cashiers item=c}
								{/foreach}
					
								{if $smarty.foreach.cashier_total.total >1 }
									<option value='all'>- All -</option>
								{/if}
								{foreach from=$cashiers item=c}
									<option value="{$c.id}" {if $smarty.request.cashier_id eq $c.id}selected {/if}>{$c.u}</option>
								{/foreach}
							{/if}
						</select>
						</span>
					</div>
					</span>
				
				
				<div class="col-md-4">
					<b class="form-label">Date</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
				</div>
				</div>
				
				<div class="col-md-4">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
				</div>
				<span id="span_split_counter" {if (!$smarty.request.branch_id || $smarty.request.branch_id eq 'all') && $BRANCH_CODE eq 'HQ'}style="display:none;"{/if}>
					<input id="split_counter_id" name="split_counter" type="checkbox" value=1 {if $smarty.request.split_counter}checked {/if}> <label for="split_counter_id"><b>Split by counter</b></label>
				</span>
				</div>
			</div>
			
			<p>
				<input type="hidden" name="submit" value="1" />
				<button class="btn btn-primary mt-2" name="a" value="show_report">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
				{/if}
			</p>
			</form>
	</div>
</div>
{/if}

{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}

<div class="alert alert-primary rounded mx-3">
	<b>Note:</b> *Variance of sales only showed when current counter collection is finalised.
</div>
{foreach from=$data key=bid item=ret}
	{foreach from=$ret key=counter_id item=d}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">
					{if $BRANCH_CODE eq "HQ"}Branch: {$branch_list.$bid.branch_code}{/if}{if $counter_id}Counters: {$counters.$counter_id.network_name}{/if}
				</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="report_table table mb-0 text-md-nowrap  table-hover" id="report_tbl" width=100%>
						<thead class="bg-gray-100">
							<tr class="header">
								<th>Cashier</th>
								<!-- Normal Payment Method -->
								{foreach name=dum from=$normal_payment_type item=payment_type}
									<th>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</th>
								{/foreach}
								
								<!-- Foreign Currency -->
								{if $foreign_currency_list}
									<th>Nett Total<br />({$config.arms_currency.symbol})</th>
									{foreach from=$foreign_currency_list key=curr_type item=curr_rate name=curr}
										<th>{$curr_type}</th>
									{/foreach}
								{/if}
								<th>Total Cash Advance</th>
								<th>Total Collection</th>
								<th class="col_variance">Variance</th>
							</tr>
						</thead>
			
						<col span='{$smarty.foreach.dum.total+2}'>
						<col span='{$smarty.foreach.curr.total+2}' class='col_foreign_curr'>
			
						{foreach from=$d key=cashier_id item=r}
						<tbody class="fs-08">
							<tr id="tr_cashier_{$cashier_id}_{$counter_id}_{$bid}">
								<th align="left" nowrap>{$cashiers.$cashier_id.u|default:'Unnamed'} <img src="/ui/expand.gif" onclick="javascript:void(show_date_details('{$cashier_id|default:0}', '{$counter_id|default:0}','{$bid|default:0}', this));" align=absmiddle></th>
				
								<!-- Normal Payment Method -->
								{foreach from=$normal_payment_type item=payment_type}
									<td class="r {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
										{$r.cash_domination.$payment_type.amt|number_format:2}
										{if $r.cash_domination.$payment_type.amt<>$r.cash_domination.$payment_type.o_amt}
											<br />
											<span class="small" style="color:black;">{$r.cash_domination.$payment_type.o_amt|number_format:2}</span>
										{/if}
										<br />
										{if $payment_type eq 'Cash'}
											<span class="small" style="color:grey;">
											C:{$r.cash_domination.Float.amt+$r.cash_domination.Cash.amt|number_format:2}
											/ F:{$r.cash_domination.Float.amt|number_format:2}
											</span>
										{/if}
									</td>
								{/foreach}
				
								<!-- Foreign Currency -->
								{if $foreign_currency_list}
									<td class="r {if $r.cash_domination.sub_total.amt<0}negative{/if}">{$r.cash_domination.sub_total.amt|number_format:2}</td>
									{foreach from=$foreign_currency_list key=curr_type item=curr_rate name=curr}
										{assign var=payment_type value=$curr_type}
										<td class="r {if $r.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} ">
											{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
				
											<!-- Currency Float -->
											<br />
											<span class="small" style="color:grey;">
											C:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
											/ F:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
											</span>
										</td>
									{/foreach}
								{/if}
								<td class="r">{$r.cash_advance.amt|number_format:2}</td>
								{assign var=total_row value=$r.cash_domination.sub_total.amt+$r.cash_advance.amt}
								
								<td class="r" nowrap>
									{if $foreign_currency_list}{$config.arms_currency.symbol}{/if}<span {if $total_row<0}class="negative"{/if}>{$total_row|number_format:2}
									{if $foreign_currency_list}
										<br />
										{foreach from=$foreign_currency_list key=curr_type item=curr_rate name=fc}
											{assign var=payment_type value=$curr_type}
											{assign var=curr_fc_amt value=$r.cash_domination.foreign_currency.$payment_type.foreign_amt}
											{$payment_type}<span {if $curr_fc_amt<0}class="negative"{/if}>{$curr_fc_amt|number_format:2}
											{if !$smarty.foreach.fc.last}<br />{/if}
										{/foreach}
									{/if}
								</td>
				
								<td class="r col_variance {if $r.variance.amt<0}negative{/if}">{$r.variance.amt|number_format:2}</td>
							</tr>
						</tbody>
						{/foreach}
			
						<!-- Total each counter -->
						<tr class='header'>
							<th>Total{if !$foreign_currency_list} ({$config.arms_currency.symbol}){/if}</th>
							<!-- Normal Payment Method -->
							{foreach from=$normal_payment_type item=payment_type}
								<td class="r bold {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
									{$total.$bid.$counter_id.cash_domination.$payment_type.amt|number_format:2}
			
									{if $total.$bid.$counter_id.cash_domination.$payment_type.amt<>$total.$bid.$counter_id.cash_domination.$payment_type.o_amt}
										<br />
										<span class="small" style="color:black;">{$total.$bid.$counter_id.cash_domination.$payment_type.o_amt|number_format:2}</span>
									{/if}
									<br />
									{if $payment_type eq 'Cash'}
										<span class="small" style="color:grey;">
										C:{$total.$bid.$counter_id.cash_domination.Float.amt+$total.$bid.$counter_id.cash_domination.Cash.amt|number_format:2}
										/ F:{$total.$bid.$counter_id.cash_domination.Float.amt|number_format:2}
										</span>
									{/if}
								</td>
							{/foreach}
			
							<!-- Foreign Currency -->
							{if $foreign_currency_list}
								<td class="r {if $total.$bid.$counter_id.total.amt<0}negative{/if}">{$total.$bid.$counter_id.total.amt|number_format:2}</td>
								{foreach from=$foreign_currency_list key=curr_type item=curr_rate name=curr}
									{assign var=payment_type value=$curr_type}
									<td class="r bold {if $total.$bid.$counter_id.foreign_currency.$payment_type.foreign_amt<0}negative{/if} ">
										{$total.$bid.$counter_id.foreign_currency.$payment_type.foreign_amt|number_format:2}
			
										<!-- Currency Float -->
										<br />
										<span class="bold small" style="color:grey;">
										C:{$total.$bid.$counter_id.foreign_currency.$payment_type.foreign_amt+$total.$bid.$counter_id.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
										/ F:{$total.$bid.$counter_id.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
										</span>
									</td>
								{/foreach}
							{/if}
			
							<td class='r bold'>{$total.$bid.$counter_id.cash_advance.amt|number_format:2}</td>
							{assign var=total_amt value=$total.$bid.$counter_id.total.amt+$total.$bid.$counter_id.cash_advance.amt}
							<td class='r bold' nowrap>
								{if $foreign_currency_list}{$config.arms_currency.symbol}{/if}<span {if $total_amt<0}class="negative"{/if}>{$total_amt|number_format:2}
								{if $foreign_currency_list}
									<br />
									{foreach from=$foreign_currency_list key=fc_code item=fc_rate name=fc}
										{assign var=payment_type value=$fc_code}
										{assign var=curr_fc_amt value=$total.$bid.$counter_id.foreign_currency.$payment_type.foreign_amt}
										{$payment_type}<span {if $curr_fc_amt<0}class="negative"{/if}>{$curr_fc_amt|number_format:2}
										{if !$smarty.foreach.fc.last}<br />{/if}
									{/foreach}
								{/if}
							</td>
							<td class='r bold col_variance {if $total.$bid.$counter_id.variance.amt<0}negative{/if}'>{$total.$bid.$counter_id.variance.amt|number_format:2}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	{/foreach}
{/foreach}
{/if}

{if !$no_header_footer}
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
{/if}

{include file=footer.tpl}
