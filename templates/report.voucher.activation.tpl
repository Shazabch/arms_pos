{*
2/10/2012 4:29:48 PM Alex
- Move voucher value to last column and add grand total 

2/13/2012 9:59:58 AM Alex
- add calculate pcs

5/14/2012 5:20:43 PM Justin
- Modified the report's header to base on standard format.
- Modified to show Voucher "Used Date" and "Used Branch".

5/30/2012 11:07:43 AM Justin
- Added new filter option "Redeem in Branch" to show only those vouchers from related branch from POS.
- Re-aligned the filter menu to for the new filter option.

7/5/2012 6:09:23 PM Justin
- Enabled "Redeem in Branch" filter for user while in sub branch.

06/29/2020 10:53 AM Sheila
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
#content_id tr:nth-child(odd){
	background-color: #eeeeee;
}
</style>

{/literal}
{/if}

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
		<form name="f_a" method=post class="form">
			<p>
			<div class="row">
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label mt-2">Issued Branch</b>
				<select class="form-control" name="branch_id">
					<option value="all" {if $smarty.request.branch_id eq 'All'}selected {/if}>--All--</option>
					{foreach from=$branches key=id item=branch}
					<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
					{/foreach}
				</select>
			{/if}
				</div>

				<div class="col-md-3">
					<b class="form-label mt-2">Redeem in Branch</b>
					<select class="form-control" name="used_branch_id">
						<option value="all" {if $smarty.request.used_branch_id eq 'All'}selected {/if}>--All--</option>
						{foreach from=$branches key=id item=branch}
						<option value="{$branch.id}" {if $smarty.request.used_branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
						{/foreach}
					</select>
				</div>
			
			<div class="col-md-3">
				<b class="form-label mt-2">Status</b>
			<select class="form-control" name="status">
				<option value="All" {if $smarty.request.status eq 'All'}selected {/if}>--All--</option>
				<option value="1" {if $smarty.request.status eq '1'}selected {/if}>Actived</option>
				<option value="2" {if $smarty.request.status eq '2'}selected {/if}>Cancelled</option>
				<option value="3" {if $smarty.request.status eq '3'}selected {/if}>Used</option>
			</select>
			</div>
		
			<div class="col-md-3">
				<b class="form-label mt-2">Date From</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" > <img class="ml-0 ml-md-2" align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			
			</div>
		
			<div class="col-md-3">
				<b class="form-label mt-2">To</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" > <img class="ml-0 ml-md-2" align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col-md-3">
				<b class="form-label mt-2">Actived Remark</b>
			<select class="form-control" name="active_remark">
				<option value="" {if $smarty.request.active_remark eq ''} selected {/if} >All</option>
				{foreach from=$config.voucher_active_remark_prefix item=a_r}
					<option value="{$a_r}" {if $smarty.request.active_remark eq $a_r} selected {/if} >{$a_r}</option>
				{/foreach}
			</select>
			</div>

			<div class="col-md-3">
				<b class="form-label mt-2">Voucher Code: </b><input class="form-control" name="search_code" value="{$smarty.request.search_code}"> (Optional)
			</div>
			</div>
		
			
			<p>
			<button class="btn btn-primary mt-2" name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
			{/if}
		
			</p>
		
		</form>
	</div>
</div>

{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $detail}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover" id="report_tbl">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="2">No.</th>
						<th rowspan="2">Branch</th>
						<th rowspan="2">Code</th>
						<th rowspan="2">Voucher Value</th>
						<th colspan="3">Activation</th>
						<th colspan="2">Voucher Used</th>
						<th colspan="3">Cancellation</th>
						<th colspan="2">Creation</th>
					</tr>
					
					<tr class="header">
						<th>Date</th>
						<th>Remark</th>
						<th>By</th>
						<th>Date</th>
						<th>Branch</th>
						<th>Date</th>
						<th>Reason</th>
						<th>By</th>
						<th>Date</th>
						<th>By</th>
					</tr>
				</thead>
				
				{assign var=total value=0}
				<tbody id="content_id" class="fs-08">
				{foreach name=voucher_detail from=$detail item=data}
					<tr>
						<td>{$smarty.foreach.voucher_detail.iteration}.</td>
						<td>{$data.branch_code}</td>
						<td>{$data.code}</td>
						<td class="r">{$data.voucher_value|number_format:2|ifzero}</td>
						<td>{$data.activated|ifzero}</td>
						<td>{$data.active_remark}</td>
						<td>{$data.activator}</td>
						<td>{$data.used_time}</td>
						<td>{$data.used_branch_code}</td>
						<td>{$data.cancelled|ifzero}</td>
						<td>{$data.cancel_remark}</td>
						<td>{$data.cancellator}</td>
						<td>{$data.added}</td>
						<td>{$data.creator}</td>
						{assign var=total value=$total+$data.voucher_value}
					</tr>
				{/foreach}
				</tbody>
				<tr class="header">
					<th colspan="3" class="r">Total Value:</th>
					<th class="r">{$total|number_format:2|ifzero}</th>
					<th colspan="9" class="r">Total Pcs:</th>
					<th class="r">{$smarty.foreach.voucher_detail.total}</th>
				</tr>
				
				
			</table>
		</div>
	</div>
</div>
{else}
	{if $table}- No Data -{/if}
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
</script>
{/literal}
{/if}
{include file=footer.tpl}
