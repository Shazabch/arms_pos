{*
12/22/2020 9:26 AM William
- Enhance "DO Summary By Items" report every row of column "DO No", "DO Date", "DO Type" and "Status" to have data.
*}
{include file=header.tpl}
{if $smarty.request.do_type and $smarty.request.do_type neq 'transfer'}
	{if $config.masterfile_enable_sa}
		{assign var=show_sales_person_name value=1}
	{else}
		{if ($smarty.request.do_type eq 'open' and $config.do_cash_sales_show_sales_person_name) or ($smarty.request.do_type eq 'credit_sales' and $config.do_credit_sales_show_sales_person_name)}
			{assign var=show_sales_person_name value=1}
		{/if}
	{/if}
{/if}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{if !$no_header_footer}
{literal}
<style>

</style>
{/literal}

<script>

var masterfile_enable_sa =  int('{$config.masterfile_enable_sa}');
var do_credit_sales_show_sales_person_name =  int('{$config.do_credit_sales_show_sales_person_name}');
var do_cash_sales_show_sales_person_name =  int('{$config.do_cash_sales_show_sales_person_name}');
{literal}
function onchange_do_type(val){
	//paid status
    if(val == 'open')  $('span_paid_status').show();
	else  $('span_paid_status').hide();
	

    // debtor list
    if(val =='credit_sales') $('div_debtor_list').show();
    else  $('div_debtor_list').hide();
    
	
    // sales person list
    $('sales_person_name').enable();
    if(masterfile_enable_sa && (val!='transfer' && val!='')){
    	$('span_sales_person_name').show();
    }else{
    	if((do_cash_sales_show_sales_person_name&&val=='open')||(do_credit_sales_show_sales_person_name&&val=='credit_sales')){    
    		$('span_sales_person_name').show();
    	}else{
	    	$('sales_person_name').disable();
	    	$('span_sales_person_name').hide();
		}
	}
}
</script>
{/literal}
{/if}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <div class="alert alert-danger">
				<li><b>{$e}</b></li>
			</div>
	    {/foreach}
	</ul>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="form" method="post">
			<input type="hidden" name="a" value="show_report" />
			{if !$no_header_footer}
				<p>
					<div class="row">
						{if $BRANCH_CODE eq 'HQ'}
						<div class="col-md-3">
							<b class="form-label">Branch</b>
						<select class="form-control" name="branch_id">
							<option value="">-- All --</option>
							{foreach from=$branch item=r}
								<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected{/if}>{$r.code}</option>
							{/foreach}
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</div>
					{else}
						<input name="branch_id" type="hidden" value="{$sessioninfo.branch_id}" />
					{/if}
			
					<div class="col-md-3 ">
						<b class="form-label">Date From</b>
						<div class="form-inline">
							<input class="form-control"  type="text" name="date_from" value="{$form.date_from}" id="added1">
					&nbsp;&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
						</div>
					&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
					<div class="col-md-3">
					<b class="form-label">Date To</b>
					<div class="form-inline">
						<input class="form-control" type="text" name="date_to" value="{$form.date_to}" id="added2">
					&nbsp;&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
					</div>
					&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
			
				<div class="col-md-3">
					<b class="form-label">By user</b>
					<select class="form-control" name="user_id">
					<option value=0>-- All --</option>
					{foreach from=$users item=r}
						<option value={$r.id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $r.id) or ($smarty.request.user_id eq $r.id)}selected{/if}>{$r.u}</option>
					{/foreach}
					</select>
				</div>
				</p>
				
				<p>
					<div class="col-md-3">
						<b class="fomr-label">Deliver To</b>
					<select class="form-control" name=deliver_to>
						<option value="">-- All --</option>
						{foreach from=$branch item=r}
							<option value="{$r.id}" {if $smarty.request.deliver_to eq $r.id}selected{/if}>{$r.code}</option>
						{/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
				
					<div class="col-md-3">
						<b class="form-label">DO Status</b>
					<select class="form-control" name=status>
						<option value=0 {if $smarty.request.status == 0}selected{/if}>All</option>
						<option value=1 {if $smarty.request.status == 1}selected{/if}>Draft / Waiting for Approval</option>
						<option value=2 {if $smarty.request.status == 2}selected{/if}>Approved</option>
						<option value=3 {if $smarty.request.status == 3}selected{/if}>Checkout</option>
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
				
				<div class="col-md-3">
					<b class="form-label">DO Type</b>
					<select class="form-control" name="do_type" onchange="onchange_do_type(this.value)">
						<option value='' {if $smarty.request.do_type eq ''}selected{/if}>All</option>
						<option value='transfer' {if $smarty.request.do_type eq 'transfer'}selected{/if}>Transfer</option>
						<option value='open' {if $smarty.request.do_type eq 'open'}selected{/if}>Cash Sales</option>
						<option value='credit_sales' {if $smarty.request.do_type eq 'credit_sales'}selected{/if}>Credit Sales</option>
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
				</div>
				</p>
				
				<p>
				<div class="col-md-3">
					<span id="div_debtor_list" {if $smarty.request.do_type neq 'credit_sales'}style="display:none"{/if}>
						<b class="form-label">Debtor</b>
							<select class="form-control" name="debtor_id">
								<option value="">-- All --</option>
								{foreach from=$debtors item=r}
									<option value="{$r.id}" {if $r.id eq $smarty.request.debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</span>
				</div>
					
				<div class="col-md-3">
					<span id="span_paid_status" {if $smarty.request.do_type ne 'open'}style="display:none"{/if}>
						<b class="form-label">Paid Status</b>
						<select class="form-control" name="paid_status">
							<option value='all' {if $smarty.request.paid_status eq 'all' || $smarty.request.paid_status eq ''}selected{/if}>All</option>
							<option value='1' {if $smarty.request.paid_status eq '1'}selected{/if}>Paid</option>
							<option value='0' {if $smarty.request.paid_status eq '0'}selected{/if}>Unpaid</option>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</span>
				</div>

					
				<div class="col-md-3">
					<span id="span_sales_person_name" {if !$show_sales_person_name}style="display:none"{/if}>
						<b class="form-label">Sales {if $config.masterfile_enable_sa}Agent{else}Person Name{/if}</b>
						<select class="form-control" name="sales_person_name" id="sales_person_name">
							<option value="">-- All --</option>
							{foreach from=$sales_agent_list item=r}
								<option value="{$r.id|escape}" {if $smarty.request.sales_person_name eq $r.id}selected {/if}>{$r.sales_person_name}</option>
							{/foreach}
						</select>
					</span>
				</div>
					</div>
				</p>
				<p>
					<button class="btn btn-primary mt-3" name="show_report">{#SHOW_REPORT#}</button>
					{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info mt-3" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
					{/if}
				</p>
			{/if}
			</form>
	</div>
</div>

{if !$table}
	{if $smarty.request.a eq 'show_report' && !$err}<p>-- No Data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_header}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table" width="100%" id="report_tbl">
			<thead class="bg-gray-100">
				<tr class="header">
					<th>DO Date</th>
					<th>DO No</th>
					<th>DO Type</th>
					<th>Status</th>
					<th>ARMS Code</th>
					<th>Mcode</th>
					<th>Art No</th>
					<th>{$config.link_code_name}</th>
					<th>SKU Description</th>
					<th>UOM</th>
					<th>Ctn</th>
					<th>Pcs</th>
					<th>Unit Cost</th>
					<th>Cost Price</th>
					<th>Unit Selling Price {if !$no_header_footer}[<a href="javascript:void(alert('Unit Selling Price will show the average branch selling price when filter by all delivery branch.'))">?</a>]{/if}</th>
					<th>Total Cost</th>
					<th>Total DO Amount</th>
					<th>Total Selling</th>
				</tr>
			</thead>
				
				<tbody class="fs-08">
				{foreach from=$table key=do_id item=r}
					{foreach from=$r key=branch_id item=r1}
						{foreach from=$r1.do_items key=keys item=r2}
						<tr>
							<td align="center">{$r2.do_date}</td>
							<td align="left">
							{if !$no_header_footer}
								<a href="/do.php?a=view&branch_id={$r2.branch_id}&id={$r2.id}" target=_blank>{$r2.do_no}</a>
							{else}
								{$r2.do_no}
							{/if}
							</td>
							<td align="left">{$r2.do_type}</td>
							<td align="left">{$r2.status}</td>
			
							<td align="left">{$r2.sku_item_code}</td>
							<td align="left">{$r2.mcode}</td>
							<td align="left">{$r2.artno}</td>
							<td align="left">{$r2.link_code}</td>
							<td align="left">{$r2.description}</td>
							<td align="center">{$r2.code}</td>
							<td align="right">{$r2.ctn|default:0}</td>
							<td align="right">{$r2.pcs|default:0}</td>
							<td align="right">{$r2.cost|number_format:4}</td>
							<td align="right">{$r2.cost_price|number_format:4}</td>
							<td align="right">{$r2.selling_price|number_format:2}</td>
							<td align="right">{$r2.total_cost|number_format:4}</td>
							<td align="right">{$r2.total_amount|number_format:4}</td>
							<td align="right">{$r2.total_selling|number_format:2}</td>
						</tr>
						{/foreach}
					{/foreach}
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
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
    });
	
    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}
{/if}
{include file=footer.tpl}
