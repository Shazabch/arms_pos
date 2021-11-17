{*
4/20/2017 11:35 AM Justin
- Enhanced to add Receipt Ref No column.

7/13/2018 4:08 PM Justin
- Enhanced payment type filter to have foreign currency selection.

06/30/2020 04:43 PM Sheila
- Updated button css.

10/13/2020 2:42 PM William
- Enhanced to change GST word to Tax.
*}

{include file=header.tpl}
{literal}
<style>
table.report_table tr.extra_info {
	background-color: #DDEEFF;
}
</style>
{/literal}
{if !$no_header_footer}
	<!-- calendar stylesheet -->
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
	<!-- main calendar program -->
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<!-- language for the calendar -->
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	<script>
	var phpself = '{$smarty.server.PHP_SELF}';
	{literal}
	var TRAN_DETAILS_ITEM_LISTING = {
		f: undefined,
		initialize: function() {
			this.f = document.f_a;
			
			Calendar.setup({
				inputField     :    "date_from",		// id of the input field
				ifFormat       :    "%Y-%m-%d",			// format of the input field
				button         :    "img_date_from",	// trigger for the calendar (button ID)
				align          :    "Bl",				// alignment (defaults to "Bl")
				singleClick    :    true
			});
		
			Calendar.setup({
				inputField     :    "date_to",		// id of the input field
				ifFormat       :    "%Y-%m-%d",		// format of the input field
				button         :    "img_date_to",	// trigger for the calendar (button ID)
				align          :    "Bl",			// alignment (defaults to "Bl")
				singleClick    :    true
			});
			
			this.toggleTimeFilter();
		},
		toggleTimeFilter: function() {
			var show = !$('time_filter').checked;
			document.f_a.from_time_Hour.disabled = show;
			document.f_a.from_time_Minute.disabled = show;
			document.f_a.to_time_Hour.disabled = show;
			document.f_a.to_time_Minute.disabled = show;
		},
		submit_form: function(t){
			this.f['export_excel'].value = 0;
			if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
			for(var i=0; i<$('sku_code_list').length; i++) {
				$('sku_code_list').options[i].selected = true;
			}
			this.f.submit();
		}
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
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}
{if !$no_header_footer}
	<div class="noprint stdframe">
	<div class="card mx-3">
		<div class="card-body">
			<form method="post" name="f_a" onSubmit="return false;">
				<input type="hidden" name="form_submit" value="1" />
				<input type="hidden" name="export_excel" />
				
				<p nowrap style="padding-left: 5px">
					<div class="row">
						<div class="col">
							<b class="title form-label">From </b>
						<div class="form-inline">
							<input class="form-control" type="text" name="date_from" id="date_from" size="10" value="{$form.date_from}" />
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" /> 
						</div>
						</div>
						
						<div class="col">
							<b class="title form-label">To </b>
						<div class="form-inline">
							<input class="form-control" type="text" name="date_to" id="date_to" size="10" value="{$form.date_to}" />
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" /> 
						</div>
						</div>
						
						<div class="col">
							<b class="title form-label">Counter : </b>
						<select class="form-control" name="counter">
							{foreach from=$counters item=r}
								{capture assign=counter_all}{$r.branch_id}|all{/capture}
								{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
								{if $last_bid ne $r.branch_id}
									<option value="{$counter_all}" {if $form.counter eq $counter_all}selected {/if}>{$r.code}</option>
									{assign var=last_bid value=$r.branch_id}
								{/if}
								<option value="{$counter_item}" {if $form.counter eq $counter_item}selected {/if}>
								&nbsp;&nbsp;&nbsp;{$r.network_name}
								</option>
							{/foreach}
						</select> 
						</div>
						
						<div class="col">
							<b class="title form-label">Receipt No : </b>
						<input class="form-control" type="text" name="receipt_num" value="{$form.receipt_num}"/> 
						</div>
					</div>
					
					
					<div class="col">
						<div class="row form-inline mt-2">
						<input type="checkbox" class="mb-1" id="time_filter" name="time_filter" onChange="TRAN_DETAILS_ITEM_LISTING.toggleTimeFilter();" {if $form.time_filter}checked{/if}/>
						<b class="title form-label">&nbsp;Time from&nbsp; </b>
						{html_select_time use_24_hours=true display_seconds=false prefix=from_time_ time=$form.from_time}
						<b class="title form-label">&nbsp;to&nbsp;</b>
						{html_select_time use_24_hours=true display_seconds=false prefix=to_time_ time=$form.to_time}
					</div>
					</div>
				</p>
				<p nowrap style="padding-left: 5px">
					<div class="row">
						<div class="col-md-3">
							<b class="title form-label">Payment Type : </b>
					<select class="form-control" name="payment_type">
						<option value="">-- All --</option>
						{foreach from=$pos_config.payment_type item=p}
							<option value="{$p}" {if $form.payment_type eq $p}selected {/if}>{$p}</option>
							{if $p eq 'Credit Cards'}
								{foreach from=$pos_config.credit_card item=c}
									<option value="{$c}" {if $form.payment_type eq $c}selected {/if}>{$c}</option>
								{/foreach}
							{/if}
						{/foreach}
						{if $config.foreign_currency}
							<option value="Foreign Currency" {if $smarty.request.payment_type eq 'Foreign Currency'}selected {/if}>Foreign Currency</option>
							{foreach from=$config.foreign_currency key=curr_code item=curr_info}
								<option value="{$curr_code}" {if $smarty.request.payment_type eq $curr_code}selected {/if}>{$curr_code}</option>
							{/foreach}
						{/if}
					</select> 
						</div>
					<div class="col-md-3">
						<b class="title form-label" >Transaction Status : </b>
					<select class="form-control" name="tran_status">
						<option value="">-- All --</option>
						{foreach from=$transaction_status key=status item=i}
							<option value="{$status}" {if $form.tran_status neq '' and $form.tran_status eq $status}selected{/if}>{$i}</option>
						{/foreach}
					</select> 
					</div>
					<div class="col-md-3">
						<b class="title form-label">Transaction Type : </b>
					<select class="form-control"  name="tran_type">
						<option value="">-- All --</option>
						{foreach from=$transaction_type key=type item=i}
							<option value="{$type}" {if $form.tran_type neq '' and $form.tran_type eq $type}selected{/if}>{$i}</option>
						{/foreach}	
					</select>
					</div>
					</div>
				</p>
				{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a'}
				<br/>
				<div id="button_box">
				<button class="btn btn-primary" id="button_show" onClick="TRAN_DETAILS_ITEM_LISTING.submit_form();">Show Report</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info" id="button_export" onClick="TRAN_DETAILS_ITEM_LISTING.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
				</div>
				<br/>
				<div class="alert alert-primary rounded" style="max-width: 500px;">
					<ul>
						<li> Report is allowed to show 30 days of transaction only.</li>
					</ul>
				</div>	
				</form>
		</div>
	</div>
	</div>
{/if}

{if $data}
	<br/>
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
				<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
					<thead class="bg-gray-100">
						<tr class="header_borderless header">
							<th align="left" colspan="2">Receipt No.</th>
							<th align="left">Receipt Ref No.</th>
							<th align="left">Date</th>
							<th align="left">Counter</th>
							{assign var=col value=5}
							{if $is_under_gst}
								{assign var=col value=$col+1}
							{/if}
							<th colspan="{$col}">&nbsp</th>
						</tr>
						<tr class="header">
							<th align="center">No</th>
							<th align="center">ARMS Code</th>
							<th align="center">Mcode</th>
							<th align="center">Art No</th>
							<th align="center">Description</th>
							<th align="center">UOM</th>
							<th align="center">Qty</th>
							<th align="center">Item Amt</th>
							<th align="center">Item Discount</th>
							<th align="center">Total Amount</th>
							{if $is_under_gst}
								<th align="center">Tax</th>
							{/if}
						</tr>
					</thead>
					{assign var=doc_count value=0}
					{assign var=total value=0}
					{foreach from=$data key=date item=d}
						{foreach from=$d item=i key=rcpt_ref_no}
							<tr class="header">
								<th align="left" colspan="2">{receipt_no_prefix_format branch_id=$i.header.branch_id counter_id=$i.header.counter_id receipt_no=$i.header.receipt_no}{if $i.header.status}<span style="color: maroon">&nbsp;&nbsp;&nbsp;{$i.header.status}{/if}</span></th>
								<th align="left">{$rcpt_ref_no}</th>
								<th align="left">{$date}</th>
								<th align="left">{$i.header.network_name}</th>
								<th align="right">Total</th>
								<th align="right">{$i.total.qty|default:0}</th>
								<th align="right">{$i.total.amt|number_format:2}</th>
								<th align="right">{$i.total.discount|number_format:2|ifzero:'-'}</th>
								<th align="right">{$i.total.total_amt|number_format:2}</th>
								{if $is_under_gst} <th>&nbsp</th> {/if}
							</tr>
							{assign var=doc_count value=$doc_count+1}
							{assign var=total value=$total+$i.total.total_amt}
							{foreach from=$i.items item=sku name=si}
								<tr>
									<td align="center">{$smarty.foreach.si.index+1}</td>
									<td>{$sku.sku_item_code}</td>
									<td>{$sku.mcode|default:'-'}</td>
									<td>{$sku.artno|default:'-'}</td>
									<td>{$sku.description}</td>
									<td align="center">{$sku.uom}</td>
									<td align="right">{$sku.qty}</td>
									<td align="right">{$sku.amt|number_format:2}</td>
									<td align="right">{$sku.discount|number_format:2|ifzero:'-'}</td>
									<td align="right">{$sku.amt-$sku.discount|number_format:2}</td>
									{if $is_under_gst}
										<td align="center">{$sku.tax_indicator|default:'-'}</td>
									{/if}
								</tr>
							{/foreach}
							{if $i.discount_list}
								{foreach from=$i.discount_list item=dl}
									<tr class="extra_info">
										<td align="right" colspan=9>{$dl.type}</td>
										<td align="right">{$dl.amount*-1|number_format:2}</td>
										{if $is_under_gst} <th>&nbsp</th> {/if}
									</tr>
								{/foreach}
							{/if}
							{if $i.service_charge}
								<tr class="extra_info">
									<td align="right" colspan=9>Service Charge{if $i.is_under_gst} Inc. Tax{/if}</td>
									<td align="right">{$i.service_charge|number_format:2}</td>
									{if $is_under_gst} <th>&nbsp</th> {/if}
								</tr>
							{/if}
							{if $i.rounding}
								<tr class="extra_info">
									<td align="right" colspan=9>{$i.rounding.type}</td>
									<td align="right">{$i.rounding.amount|number_format:2}</td>
									{if $is_under_gst} <th>&nbsp</th> {/if}
								</tr>
							{/if}
						{/foreach}
					{/foreach}
				<tr class="header">
					<th colspan=2 align="left">Receipt Count : </th><th align="right">{$doc_count}</th>
					<th colspan=5>&nbsp</th>
					<th align="right">Total : </th><th align="right">{$total|number_format:2}</th>
					{if $is_under_gst} <th>&nbsp</th> {/if}
				</tr>
				</table>
			</div>
		</div>
	</div>
	<br/>
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Final Summary by SKU Items</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
					<thead class="bg-gray-100">
						<tr class="header">
							<th align="left">ARMS Code</th>
							<th>UOM</th>
							<th>Qty</th>
							<th>Amount</th>
						</tr>
					</thead>
					{foreach from=$summary.item key=code item=s}
					<div class="tbody fs-08">
						<tr>
							<td>{$code}</td>
							<td align="center">{$s.uom}</td>
							<td align="center">{$s.qty}</td>
							<td align="right">{$s.amt|number_format:2}</td>
						</tr>
					</div>
					{/foreach}
					<tr class="header">
						<td colspan=2 align="right">Item Total</td>
						<td colspan=2 align="right">{$summary.total.amt}</td>
					</tr>
					{if $summary.total.item_disc}
						<tr class="subtotal">
							<td colspan=2 align="right">Item Discount</td>
							<td colspan=2 align="right">{$summary.total.item_disc*-1|number_format:2}</td>
						</tr>
					{/if}
					{if $summary.total.mix_match_disc}
						<tr class="subtotal">
							<td colspan=2 align="right">Mix & Match Total Disc</td>
							<td colspan=2 align="right">{$summary.total.mix_match_disc*-1|number_format:2}</td>
						</tr>
					{/if}
					{if $summary.total.disc}
						<tr class="subtotal">
							<td colspan=2 align="right">Discount</td>
							<td colspan=2 align="right">{$summary.total.disc*-1|number_format:2}</td>
						</tr>
					{/if}
					{if $summary.total.service_charge}
						<tr class="subtotal">
							<td colspan=2 align="right">Service Charge{if $is_under_gst} Inc. Tax{/if}</td>
							<td colspan=2 align="right">{$summary.total.service_charge|number_format:2}</td>
						</tr>
					{/if}
					{if $summary.total.rounding}
						<tr class="subtotal">
							<td colspan=2 align="right">Rounding</td>
							<td colspan=2 align="right">{$summary.total.rounding|number_format:2}</td>
						</tr>
					{/if}
					<tr class="header">
						<th colspan=2 align="right">Total</th>
						<th align="center">{$summary.total.qty}</th>
						<th align="right">{$total|number_format:2}</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
{else}
	{if $form.form_submit && !$err}
		<ul><li>No data</li></ul>
	{/if}
{/if}

{include file=footer.tpl}

<script type="text/javascript">
{literal}
TRAN_DETAILS_ITEM_LISTING.initialize();
{/literal}
</script>