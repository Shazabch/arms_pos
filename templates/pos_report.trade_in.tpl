{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

06/30/2020 04:43 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

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
.col_pi_status1{
	color:green;
}
.col_pi_status2{
	color:red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function init_calendar(){
	Calendar.setup({
		inputField     :    "inp_date_from",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_from",
		align          :    "Bl",
		singleClick    :    true
	});
	Calendar.setup({
		inputField     :    "inp_date_to",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_to",
		align          :    "Bl",
		singleClick    :    true
	});
}

function submit_form(type){
	if(!document.f_a['branch_id'].value){
		alert('Please select branch.');
		return false;
	}
	
	document.f_a.submit();
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

{if $err}
	<div class="alert alert-danger mx-3 rounded">
		The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
		<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
			<input type="hidden" name="show_report" value="1" />
		
			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<span>
					<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id">
						<option value="">-- Please Select --</option>
						{foreach from=$branches key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				</span>
				{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
				{/if}
				</div>
				
				<div class="col-md-4">
					<b class="form-label">From</b>
				<div class="form-inline">
					<input class="form-control" name="date_from" id="inp_date_from" size="10" maxlength="10"  value="{$smarty.request.date_from|date_format:"%Y-%m-%d"}" />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />
				</div>
				</div>
				
			<div class="col-md-4">
					
				<b class="form-label">To</b>
				<div class="form-inline">
					<input class="form-control" name="date_to" id="inp_date_to" size="10" maxlength="10"  value="{$smarty.request.date_to|date_format:"%Y-%m-%d"}" />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
				</div>
			</div>
				
				
				<div class="col-md-4">
					<b class="form-label">Finalised</b>
				<select class="form-control" name="finalized_status">
					<option value="all">-- All --</option>
					<option value="yes" {if $smarty.request.finalized_status eq 'yes'}selected {/if}>YES</option>
					<option value="no" {if $smarty.request.finalized_status eq 'no'}selected {/if}>NO</option>
				</select>
				</div>
	
				<div class="col-md-4">
					<b class="form-label">Status</b>
				<select class="form-control" name="status">
					{foreach from=$status_list key=k item=label}
						<option value="{$k}" {if $smarty.request.status eq $k}selected {/if}>{$label}</option>
					{/foreach}
				</select>
				</div>
			</div>
			
			<button class="btn btn-primary mt-2" onClick="submit_form();">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
			{/if}
		</form>
	</div>
</div>
<script>
init_calendar();
</script>
<br />
{if $smarty.request.show_report && !$err}
	{if !$data}
		
		-- No Data --
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
				<div class="table-responsive" >
					<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
								<th rowspan="2">Date</th>
								<th rowspan="2">Counter</th>
								<th rowspan="2">Receipt No.</th>
								<th colspan="6">Trade In Info</th>
								<th colspan="4">Verified Info</th>
								<th colspan="4">Status</th>
							</tr>
							<tr class="header">
								<!-- trade in -->
								<th>Barcode</th>
								<th>Description</th>
								<th>Serial No.</th>
								<th>Qty</th>
								<th>Total Price</th>
								<th>Approved by</th>
								
								<!-- verify -->
								<th>ARMS Code</th>
								<th>MCode</th>
								<th>{$config.link_code_name}</th>
								<th>Description</th>
								
								<!-- status -->
								<th>Finalised</th>
								<th>Status</th>
								<th>By</th>
								<th>Timestamp</th>
							</tr>
						</thead>
						
						{foreach from=$data key=date item=counter_data}
							<tr>
								<td colspan="17" bgcolor="#ccffff">{$date}</td>
							</tr>
							{foreach from=$counter_data key=cid item=pi_list}
								{foreach from=$pi_list item=pi}
									<tbody class="fs-08">
										<tr>
											<td>{$pi.date}</td>
											<td>{$pi.network_name}</td>
											<td>{receipt_no_prefix_format branch_id=$pi.branch_id counter_id=$cid receipt_no=$pi.receipt_no}</td>
											
											<!-- Trade in info -->
											<td>{$pi.barcode|default:'-'}</td>
											<td>{$pi.sku_description|default:'-'}</td>
											<td>
												{$pi.more_info.trade_in.serial_no|default:'-'}
												{if $pi.pos_items_sn}
													<img src="/ui/approved.png" align="absmiddle" title="Serial number generated" />
												{/if}
											</td>
											<td align="right">{$pi.qty}</td>
											<td align="right">{$pi.price|number_format:2}</td>
											<td>{$pi.trade_in_by_u|default:'-'}</td>
											
											<!-- verify info -->
											<td>{$pi.sku_item_code|default:'-'}</td>
											<td>{$pi.mcode|default:'-'}</td>
											<td>{$pi.link_code|default:'-'}</td>
											<td>{$pi.description|default:'-'}</td>
											
											<!-- status -->
											<td>{if $pi.finalized}YES{else}NO{/if}</td>
											{assign var=pi_status value=0}
											{assign var=pi_status_u value=''}
											{assign var=pi_status_timestamp value=''}
											{if $pi.sku_item_id and $pi.verify_code_by}
												{assign var=pi_status value=1}	<!-- verified -->
												{assign var=pi_status_u value=$pi.verify_code_by_u}
												{assign var=pi_status_timestamp value=$pi.verify_timestamp}
											{elseif $pi.writeoff_by}
												{assign var=pi_status value=2} <!-- write-off -->
												{assign var=pi_status_u value=$pi.writeoff_by_u}
												{assign var=pi_status_timestamp value=$pi.writeoff_timestamp}
											{/if}
											<td class="col_pi_status{$pi_status}">
												{if $pi_status eq 1}
													Verified
												{elseif $pi_status eq 2}
													Write-off
												{else}
													New
												{/if}
											</td>
											<td class="col_pi_status{$pi_status}">{$pi_status_u|default:'-'}</td>
											<td class="col_pi_status{$pi_status}">{$pi_status_timestamp|ifzero:'-'}</td>
										</tr>
									</tbody>
								{/foreach}
							{/foreach}
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	{/if}
{/if}

{include file='footer.tpl'}