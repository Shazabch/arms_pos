{*
3/7/2011 3:04:11 PM Alex
- created by me

4/20/2011 10:54:10 AM Alex
- change use counter name, will found duplicate key if use all branch and counter id

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

2/21/2017 3:32 PM Justin
- Bug fixed on voucher value options is not showing as per voucher setup.

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

<script>
function trans_detail(counter_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('div_item_details');
    $('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			branch_id: branch_id,
			date: date
		}
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

function get_counter_name(val){
	var branch_id=val;
  var counter_name=document.f_a['counter_name'].value;
	$('counter_name_id').update(_loading_);

	new Ajax.Updater('counter_name_id', 'report.voucher.details.php',
		{
		  method: 'post',
		  parameters:{
			a: 'get_counter_name',
			branch_id: branch_id,
			counter_name: counter_name
		}
	});
}

</script>

<style>
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
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
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
	<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
	<div id="div_item_content">
	</div>
</div>
<!-- End of Item Details-->

{if $err}
<div class="alert alert-danger rounded mx-3">
	<b>The following error(s) has occured:</b>
<ul style="list-style-type: none;" class=err>
{foreach from=$err item=e}
<li style="color:red;"> {$e}</li>
{/foreach}
</ul>
</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="get" class="form" >
			<p>
			<div class="row">
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b>
				<select class="form-control" name="branch_id" id="branch_id" onchange='get_counter_name(this.value)'>
					<option value="all" {if $smarty.request.branch_id eq 'All'}selected {/if}>--All--</option>
					{foreach from=$branches key=id item=branch}
					<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
					{/foreach}
				</select> &nbsp;&nbsp;
			{/if}
				</div>
		
			<div class="col-md-3">
				<b class="form-label">Counter</b>
			<span id="counter_name_id" name="counter_name">
			<select   class="form-control"  >
				{if !$counters}
					<option value=''>No Data</option>
				{else}
					{foreach name=counter_total from=$counters item=c}
					{/foreach}
		
					{if $smarty.foreach.counter_total.total >1 }
						<option value='all' {if $smarty.request.counter_name eq "all"} selected {/if} >-- All --</option>
					{/if}
				  {foreach from=$counters key=cn  item=c}
					  <option value="{$c.network_name}" {if $smarty.request.counter_name eq $cn}selected {/if} >{$c.network_name}</option>
				  {/foreach}
			  {/if}
			</select>
			</span>
			</div>
		
			<div class="col-md-3">
				<b class="form-label">Voucher Amount</b>
			<select class="form-control" name="voucher_amount">
				<option value="all" {if $smarty.request.voucher_amount eq 'all'} Selected {/if} >All</option>
				{foreach from=$vs_list item=voucher_value}
					<option value="{$voucher_value}" {if $smarty.request.voucher_amount eq $voucher_value} selected {/if}>{$voucher_value}</option>
				{/foreach}
			</select>
			</div>
		
			<div class="col-md-3">
				<b class="form-label">Status</b>
			<select class="form-control" name="status">
				<option value="all" {if $smarty.request.status eq 'all'} Selected {/if} >All</option>
				<option value="normal" {if $smarty.request.status eq 'normal'} Selected {/if} >Normal</option>
				<option value="abnormal" {if $smarty.request.status eq 'abnormal'} Selected {/if} >Abnormal</option>
			</select>
			</div>
			
			<div class="col-md-3">
				<b class="form-label">POS Date From</b>
			<div class="form-inline">
			<input class="form-control" type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1">&nbsp;&nbsp; <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>

			<div class="col-md-3">
				<b class="form-label">To</b>
			<div class="form-inline">	
			<input class="form-control" type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1">&nbsp;&nbsp; <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col-md-3">
				<b class="form-label">Voucher Code: </b><input class="form-control" name="search_code" value="{$smarty.request.search_code}"> (Optional)
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
{foreach from=$top_head key=bid item=b_code}
	<h3>Branch: {$b_code}</h3>

	{foreach from=$mid_head.$bid key=count_id item=count_name}
		{assign var=total_used value=0}
		{assign var=total_amount value=0}

		<h4>Counter: {$count_name}</h4>
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="" class="report_table table mb-0 text-md-nowrap  table-hover" width="100%" >
						<thead class="bg-gray-100">
							<tr class=header>
								<th>Date</th>
								<th>Receipt No</th>
								<th>Voucher Code</th>
								<th>Voucher Used</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Approved by</th>
							</tr>
						</thead>
			
						{foreach from=$detail.$bid.$count_id key=date item=dcrdata}
							{foreach from=$dcrdata key=receipt_no item=crdata}
								{foreach from=$crdata key=voucher_code item=data}
									<tbody class="fs-08">
										<tr {if $data.status ne "OK"}style="color:red;"{/if} >
											<td>{$date}</td>
											<td><a onclick="trans_detail('{$count_id}','{$date}','{$data.pos_id}','{$bid}');" href="javascript:void(0)">{receipt_no_prefix_format branch_id=$bid counter_id=$count_id receipt_no=$receipt_no}</a></td>
											<td>{$voucher_code}</td>
											<td class="r">{$data.used}</td>
											<td class="r">{$data.amount|ifzero|number_format:2}</td>
											<td>{$data.status}</td>
											<td>{$data.approved_by|default:'-'}</td>
										</tr>
									</tbody>
									{assign var=total_used value=$total_used+$data.used}
									{assign var=total_amount value=$total_amount+$data.amount}
								{/foreach}
							{/foreach}
						{/foreach}
						<tr class=header>
							<th colspan='3'>Total</th>
							<td class="r">{$total_used}</td>
							<td class="r">{$total_amount|number_format:2}</td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	{/foreach}
{/foreach}
{else}
	{if $table}	- No Data - {/if}
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

if ($('branch_id')) get_counter_name($('branch_id').value);
</script>
{/literal}
{/if}
{include file=footer.tpl}
