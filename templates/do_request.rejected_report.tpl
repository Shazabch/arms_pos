{*
2/18/2013 5:03 PM Justin
- Bug fixed on Delivered qty always zero.

2/15/2017 3:47 PM Zhi Kai
- Change wording of 'Order By' to 'Sort By'.

06/23/2020 05:15 Sheila
- Updated button css.
*}

{include file="header.tpl"}

{if !$no_header_footer}

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

</style>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var DO_REQUEST_REJECTED_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// setup calendar
		Calendar.setup({
	        inputField     :    "inp_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_from",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	
	    Calendar.setup({
	        inputField     :    "inp_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_to",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	},
	// function to check form before submit
	check_form: function(){
		// check date
		// date from
		var dt1 = this.f['date_from'].value;
		// date to
		var dt2 = this.f['date_to'].value;
		
		var diff = day_diff(dt1, dt2);
		if(diff>90){
			alert('Report maximum show data in 90 days only.');
			return false;
		}
		
		return true;
	},
	// function when user click show report
	show_report: function(t){
		if(!this.check_form())	return false;
		
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){	// export excel
			this.f['export_excel'].value = 1;
		}
		this.f.submit();
		
	}
};
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
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<div class="alert alert-danger rounded">
			<li> {$e}</li>
		</div>
	{/foreach}
	</ul></div></div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" method="post">
			<input type="hidden" name="load_report" value="1" />
			<input type="hidden" name="export_excel" />
			
			{if $BRANCH_CODE eq 'HQ'}
				<div class="row">
				<div class="col-md-4">
					<b class="form-label">Request From Branch: </b>
					<select class="form-control" name="branch_id">
						<option value="">-- All --</option>
						{foreach from=$branches_list key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				</div>
			{/if}
			
			<div class="col-md-4">
				<b class="form-label">Request Date: </b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1"  />
			&nbsp;&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</div>
			</div>
			
			<div class="col-md-4">
				<b class="form-label">To</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1"  />
		&nbsp;&nbsp;	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
			</div>
			</div>
			
			
				</div>
				<b class="form-label mt-2">Sort by</b>
				<div class="row">
					
			<div class="col-md-4">
				<select class="form-control" name="sort_by">
					{foreach from=$sort_by_list key=k item=v}
						<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$v}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-4">
				<select class="form-control" name="sort_order">
					<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
					<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
				</select>
			</div>
			</div>
			<p>
				<input class="btn btn-primary mt-2" type="button" value="{#SHOW_REPORT#}" onClick="DO_REQUEST_REJECTED_REPORT.show_report();" />
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info mt-2" onClick="DO_REQUEST_REJECTED_REPORT.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
			</p>
			<ul>
				<li> Report maximum show 90 days of data.</li>
			</ul>
		</form>
	</div>
</div>
<script type="text/javascript">DO_REQUEST_REJECTED_REPORT.initialize();</script>
{/if}

{if $smarty.request.load_report && !$err}
	<br />
	{if !$data}
		* No Data *
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
				<table width="100%" class="report_table">
					<thead class="bg-gray-100">
						<tr class="header">
							<th>ARMS Code</th>
							<th>Art No.</th>
							<th>MCode</th>
							<th>Description</th>
							<th>Request by</th>
							<th>From Branch</th>
							<th>Request To Branch</th>
							<th>Expected <br />Delivery Date</th>
							<th>Delivered Qty</th>
							<th>Current Request Qty</th>
							<th>Reject By</th>
							<th>Remark</th>
							<th>Last Update</th>
						</tr>
					</thead>
					
					{foreach from=$data.item_list item=r}
						<tbody class="fs-08">
							<tr>
								<td>{$r.sku_item_code}</td>
								<td>{$r.artno|default:'-'}</td>
								<td>{$r.mcode|default:'-'}</td>
								<td>{$r.description|default:'-'}</td>
								<td>{$r.request_by_u|default:'-'}</td>
								<td>{$r.request_from_bcode|default:'-'}</td>
								<td>{$r.request_to_bcode|default:'-'}</td>
								<td>{$r.expect_do_date|ifzero:'-'}</td>
								<td class="r">{$r.total_do_qty|number_format}</td>
								<td class="r">{$r.request_qty|number_format}</td>
								<td>{$r.reject_by_u|default:'-'}</td>
								<td>{$r.reason|default:'-'}</td>
								<td>{$r.last_update|default:'-'}</td>
							</tr>
						</tbody>
					{/foreach}
				</table>
			</div>
		</div>
	</div>
	{/if} 
{/if}

{include file="footer.tpl"}
