{*
8/26/2013 10:51 AM Andy
- Change "Date" to "Deposit Date".
- Add popup information for "Account" filter.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

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
/*.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}*/

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.b_header th{
	background:#edffed;
	padding:6px 4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.rpt_table tr.sub_total th{
	background:#adffea;
	padding:6px 4px;
}

#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}

<script type="text/javascript">
{literal}
	
function trans_detail(counter_id,cashier_id,date,pos_id,branch_id){
	curtain(true);
	center_div('div_item_details');
	
	$('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
		method: 'post',
		parameters:{
			a: 'item_details',
			branch_id: branch_id,
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function curtain_clicked(){
	hidediv('div_item_details');
	curtain(false);
}

{/literal}
</script>
{/if}

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">
			<p>
				<div class="row">
					<div class="col-md-4">
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label">Branch</b>
						<select class="form-control" name="branch_id">
							<option value="">-- All --</option>
							{foreach from=$branches item=b}
								<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/foreach}
							{if $branch_group.header}
								<optgroup label="Branch Group">
									{foreach from=$branch_group.header item=r}
										{capture assign=bgid}bg,{$r.id}{/capture}
										<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
									{/foreach}
								</optgroup>
							{/if}
						</select>
					{/if}
					</div>
					<div class="col-md-4">
						<b class="form-label">Deposit Date From</b> 
					<div class="form-inline">
						<input class="form-control" size="22" type="text" name="date_from" value="{$smarty.request.date_from}{$form.from}" id="date_from">
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
					</div>
					</div>
					
					<div class="col-md-4">
						<b class="form-label">To</b> 
					<div class="form-inline">
						<input class="form-control" size="22" type="text" name="date_to" value="{$smarty.request.date_to}{$form.to}" id="date_to">
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
					</div>
					</div>
					
					<div class="col-md-4">
						<b class="form-label">Account</b>
					<select class="form-control" name="view_type">
						<option value="ap" {if $smarty.request.view_type eq 'ap'}selected{/if}>Payable</option>
						<option value="ar" {if $smarty.request.view_type eq 'ar'}selected{/if}>Receivable</option>
					</select>
					[<a href="javascript:void(alert('- Payable: Deposit receive by your branch but customer redeem at other branch, you need to pay that branch.\n- Receivable: Deposit receive by other branch but customer redeem at your branch, you need to claim the amount from that branch.'));">?</a>]
				
					</div>
					<!--b>Counter</b>
					<select name="counter_id">
						<option value="">-- All --</option>
						{foreach from=$counter_list item=c}
							<option value="{$c.id}" {if $smarty.request.counter_id eq $c.id}selected{/if}>{$c.network_name}</option>
						{/foreach}
					</select>
					-->
					<div class="col-md-4">
						<b class="form-label">Cashier</b>
					<select class="form-control" name="cashier_id">
						<option value="">-- All --</option>
						{foreach from=$user_list item=u}
							<option value="{$u.id}" {if $smarty.request.cashier_id eq $u.id}selected{/if}>{$u.u}</option>
						{/foreach}
					</select>
					</div>
					
				
					<div class="col-md-4">
						<b class="form-label">Transaction Status</b>
					<select class="form-control" name="tran_status">
						<option value="">-- All --</option>
						{foreach from=$transaction_status key=status item=t}
							<option value="{$status}" {if $smarty.request.tran_status eq $status}selected {/if}>{$t}</option>
						{/foreach}
					</select>
					</div>
				</div>
				
			</p>
			<p>
			<div class="alert alert-primary mt-2" style="max-width:300px;">
				* View in maximum 1 month
			</div>
			</p>
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="rpt_table table mb-0 text-md-nowrap  table-hover" width=100% >
					<thead class="bg-gray-100">
						<tr class="header">
							<th width="3%">#</th>
							<th width="7%">Branch</th>
							<th width="7%">Date</th>
							<th width="7%">Receipt No</th>
							<th width="7%">Cashier</th>
							<th width="10%">Approved By</th>
							<th width="10%">Status</th>
							<th width="10%">Amount</th>
						</tr>
					</thead>
					<tbody class="fs-08">
					{foreach from=$branch_list item=b key=f_bid name=branch}
						<tr class="b_header">
							<th colspan="8" align="left">{$b.branch_code} - {$b.description}</th>
						</tr>
						{foreach from=$table.$f_bid item=d name=deposit}
							<tr>
								<td>{$smarty.foreach.deposit.iteration}.</td>
								<td align="center">{$d.owe_branch_code}</td>
								<td align="center">{$d.date}</td>
								<td align="center">
									{if $d.have_item_list}
										<a onclick="trans_detail('{$d.counter_id}','{$d.cashier_id}','{$d.date}','{$d.pos_id}','{$d.rcv_branch_id}')" class="clickable">{receipt_no_prefix_format branch_id=$d.rcv_branch_id counter_id=$d.counter_id receipt_no=$d.receipt_no}</a>
									{else}
										{receipt_no_prefix_format branch_id=$d.rcv_branch_id counter_id=$d.counter_id receipt_no=$d.receipt_no}
									{/if}
								</td>
								<td align="center">{$d.cashier_name}</td>
								<td align="center">{$d.approved_name|default:'-'}</td>
								<td align="center">{if $d.status eq 1}Cancelled{else}Active{/if}</td>
								<td align="right">{$d.deposit_amount|number_format:2}</td>
							</tr>
							{assign var=ttl_deposit_amount value=$ttl_deposit_amount+$d.deposit_amount}
							{assign var=sub_ttl_deposit_amount value=$sub_ttl_deposit_amount+$d.deposit_amount}
						{/foreach}
						{if count($branch_list) > 1 && $BRANCH_CODE eq 'HQ'}
							<tr class="sub_total">
								<th class="r" colspan="7">Sub Total</th>
								<th align="right">{$sub_ttl_deposit_amount|number_format:2|ifzero:'-'}</th>
							</tr>
							{assign var=sub_ttl_deposit_amount value=0}
						{/if}
					{/foreach}
					</tbody>
					<tr class="header">
						<th class="r" colspan="7">Total</th>
						<th align="right">{$ttl_deposit_amount|number_format:2|ifzero:'-'}</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
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
	
	new Draggable('div_item_details');
</script>
{/literal}
{/if}

{include file=footer.tpl}
