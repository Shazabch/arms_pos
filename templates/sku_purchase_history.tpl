{*
11/27/2018 9:19 AM Justin
- Reverted to the previous version where it does not have GRN enhancements.

06/24/2020 02:58 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

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
{literal}
.tr_item_row{
	background-color: #cfc;
}
.tr_item_details_header{
	background-color: #EBE8D6;
}
{/literal}
</style>

<script>

var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var SKU_PURCHASE_HISTORY = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
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
	// select/de-select branch
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	},
	// function to validate form
	check_form: function(){
		// Branch
		var total_branch_count = 0;
		var selected_branch_count = 0;
		$$('#div_branch_list input.inp_branch').each(function(ele){
			total_branch_count++;
			if(ele.checked)	selected_branch_count++;
		});
		
		if(total_branch_count>0){	// Got Branch Selection
			if(selected_branch_count<=0){
				alert('Please select at least one branch.')
				return false;
			}
		}
		
		// SKU
		if($('sku_code_list').length <= 0){
			alert('Please add at least one SKU.');
			$('autocomplete_sku').focus();
			return false;
		}
		
		return true;
	},
	// function when users click on show report or export
	submit_form: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel')	this.f['export_excel'].value = 1;
		
		// Validate
		if(!this.check_form())	return false;
		
		// Select all added sku
		for(var i=0; i<$('sku_code_list').length; i++){
		    $('sku_code_list').options[i].selected = true;
		}
		
		this.f.submit();
	},
	// function when user toggle item details
	toggle_item_details: function(sid){
		var img = $('img_toggle_item_details-'+sid);
		
		if(img.src.indexOf('expand')>0){
			// Expand
			this.change_item_row_visible(sid, true);
		}else{
			// Collapse
			this.change_item_row_visible(sid, false);
		}
	},
	// core function to show / hide item details
	change_item_row_visible: function(sid, is_show){
		if(is_show){
			// Expand
			$('img_toggle_item_details-'+sid).src = 'ui/collapse.gif';
			$('tbody_item_details-'+sid).show();
		}else{
			// Collapse
			$('img_toggle_item_details-'+sid).src = 'ui/expand.gif';
			$('tbody_item_details-'+sid).hide();
		}
	},
	// function to show / hide all items details
	change_all_item_details_visiable: function(is_show){
		var THIS = this;
		$$('tr.tr_item_row').each(function(tr){
			var sid = $(tr).id.split('-')[1];
			THIS.change_item_row_visible(sid, is_show);
		});
		
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
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<div class="alert alert-danger rounded mx-3">
				<li> {$e}</li>
			</div>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<div class="noprint stdframe">
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="load_report" value="1" />
				<input type="hidden" name="export_excel" value="0" />
				
				{if $BRANCH_CODE eq 'HQ'}
					<div>
						<div class="row">
							<div class="col-md-6">
								<b class="form-label">Select Branch By:</b>
						<select class="form-control" id="sel_brn_grp" >
							<option value="">-- All --</option>
							{foreach from=$branch_group.header key=bgid item=bg}
								<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
							{/foreach}
						</select>
							</div>
						<div class="col-md-6">
							<input  type="button" class="btn btn-success mt-4 "  value="Select " onclick="SKU_PURCHASE_HISTORY.check_branch_by_group(true);" />&nbsp;
						<input  type="button" class="btn btn-danger mt-4 "  value="De-select" onclick="SKU_PURCHASE_HISTORY.check_branch_by_group(false);" /><br /><br />
						
						</div>
						</div>
						<div id="div_branch_list" class="p-3" style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
							<table>
							{foreach from=$branches key=bid item=b}
								{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
								<tr>
									<td>
										<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />&nbsp;
										<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
									</td>
								</tr>
							{/foreach}
							</table>
						</div>
					</div>
				{/if}
			
				<p>
					<div class="form-inline">
						<b class="form-label">Date From</b>&nbsp;&nbsp;
					<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=12 />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
					&nbsp;&nbsp;&nbsp;
					<b class="form-label">To</b>&nbsp;&nbsp;
					<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=12 />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
					</div>
				</p>
				
				{include file='sku_items_autocomplete_multiple_add2.tpl'}
				
				<input type="button" class="btn btn-primary" value='Show Report' onClick="SKU_PURCHASE_HISTORY.submit_form();" /> &nbsp;&nbsp;
		
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info" onClick="SKU_PURCHASE_HISTORY.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
			</form>
		</div>
	</div>
</div>
{/if}

{if $smarty.request.load_report}
	<br />
	
	{if !$data}
		** No Data **
	{else}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
		{if !$no_header_footer}
			<a href="javascript:void(SKU_PURCHASE_HISTORY.change_all_item_details_visiable(true))">
				<img src="ui/expand.gif" /> Expand All 
			</a>
			| 
			<a href="javascript:void(SKU_PURCHASE_HISTORY.change_all_item_details_visiable(false))">
				<img src="ui/collapse.gif" /> Collapse All
			</a>
		{/if}
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table" width="100%">
					<tr class="header">
						<th>#</th>
						<th>ARMS Code</th>
						<th>MCode</th>
						<th>Art No</th>
						<th>{$config.link_code_name}</th>
						<th>Description</th>
						<th>Qty<br />(pcs)</th>
						<th>FOC<br />(pcs)</th>
						<th>Amount{if $got_foreign_currency}<br />({$config.arms_currency.code}){/if}</th>
						{if $got_gst}
							<th>GST</th>
							<th>Amount Incl GST</th>
						{/if}
					</tr>
					{foreach from=$data.si_info key=sid item=si name=fsi}
						<tr class="tr_item_row" id="tr_item_row-{$sid}">
							<td align="center">{$smarty.foreach.fsi.iteration}</td>
							<td align="center" nowrap>{$si.sku_item_code}
								{if !$no_header_footer}
									<img src="ui/expand.gif" title="View Details" onClick="SKU_PURCHASE_HISTORY.toggle_item_details('{$sid}')" class="img_toggle_item_details" id="img_toggle_item_details-{$sid}" />
								{/if}
							</td>
							<td align="center">{$si.mcode|default:'-'}</td>
							<td align="center">{$si.artno|default:'-'}</td>
							<td align="center">{$si.link_code|default:'-'}</td>
							<td>{$si.item_desc|default:'-'} {include file=details.uom.tpl uom=$si.packing_uom_code}</td>
							<td align="right">{$si.total_qty|qty_nf}</td>
							<td align="right">{$si.total_foc|qty_nf}</td>
							<td align="right">{$si.item_nett_amt|number_format:2}</td>
							
							{if $got_gst}
								<td align="right">{$si.item_gst_amt|number_format:2}</td>
								<td align="right">{$si.item_amt_incl_gst|number_format:2}</td>
							{/if}
						</tr>
						
						<tbody class="tbody_item_details" id="tbody_item_details-{$sid}" style="display:none;">
							<thead class="bg-gray-100">
								<tr class="tr_item_details_header">
									<th>Date</th>
									<th>Branch</th>
									<th>PO</th>
									<th>Cost</th>
									<th>Selling Price</th>
									<th>Remark / Remark 2</th>
									<th>Qty</th>
									<th>FOC</th>
									<th>Amount</th>
									{if $got_gst}
										<th>GST</th>
										<th>Amount Incl GST</th>
									{/if}
								</tr>
							</thead>
							{assign var=last_vendor_id value=0}
							{foreach from=$data.data.$sid item=r}
								{if $last_vendor_id ne $r.vendor_id}
									{assign var=cols value=10}
									{if $got_gst}{assign var=cols value=$cols+2}{/if}
									<tr bgcolor="#eeeeee">
										<td colspan="{$cols}">
											<h5>{$r.vendor_code} - {$r.vendor_desc}</h5>
										</td>
									</tr>
								{/if}
								
								<tr>
									<td align="center">{$r.po_date}</td>
									<td align="center">{$branches[$r.branch_id].code}</td>
									<td align="center">
										{if !$no_header_footer}
											<a href="po.php?a=view&branch_id={$r.branch_id}&id={$r.po_id}&highlight_po_item_id={$r.po_item_id}" target="_blank">
												{$r.po_no}
											</a>
										{else}
											{$r.po_no}
										{/if}
									</td>
									<td align="right">
										{if $r.currency_code}
											{$r.currency_code} {$r.order_price|number_format:$config.global_cost_decimal_points}
											<br /><span class="converted_base_amt">{$config.arms_currency.code}
										{/if}
										{$r.base_order_price|number_format:$config.global_cost_decimal_points}{if $r.currency_code}*</span>{/if}
									</td>
									<td align="right">{$r.selling_price|number_format:2}</td>
									<td>
										{if $r.remark}
											R: {$r.remark}
											{if $r.remark2}<br />{/if}
										{/if}
										{if $r.remark2}
											R2: {$r.remark2}
										{/if}
									</td>
									<td align="right">
										{if abs($r.qty)>0}{$r.qty|qty_nf}x{$r.po_uom_code} / {/if}{$r.qty_loose|qty_nf}							
									</td>
									<td align="right">
										{if abs($r.foc)>0}{$r.foc|qty_nf}x{$r.po_uom_code} / {/if}{$r.foc_loose|qty_nf}
									</td>
									<td align="right">{$r.base_nett_amt|number_format:2}</td>
									
									{if $got_gst}
										<td align="right">{$r.base_gst_amt|number_format:2}</td>
										<td align="right">{$r.base_amt_incl_gst|number_format:2}</td>
									{/if}
									
								</tr>
								
								{assign var=last_vendor_id value=$r.vendor_id}
							{/foreach}
						</tbody>
					{/foreach}
					
					<tr class="header">
						<th colspan="6" align="right">Total</th>
						<th align="right">{$data.total.total_qty|qty_nf}</th>
						<th align="right">{$data.total.total_foc|qty_nf}</th>
						<td align="right">{$data.total.base_nett_amt|number_format:2}</td>
						{if $got_gst}
							<td align="right">{$data.total.base_gst_amt|number_format:2}</td>
							<td align="right">{$data.total.base_amt_incl_gst|number_format:2}</td>
						{/if}
					</tr>
				</table>
			</div>
		</div>
	</div>
	{/if}
{/if}

{if !$no_header_footer}
	<script>SKU_PURCHASE_HISTORY.initialise();</script>
{/if}
{include file='footer.tpl'}