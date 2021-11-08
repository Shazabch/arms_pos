{*
3/5/2018 3:53 PM Justin
- Bug fixed on showing wrong calculation method for "S.P. excl. GST" column.

06/30/2020 10:41 AM Sheila
- Updated button css.

10/16/2020 12:56 PM William
- Enhanced to add tax checking.
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

<script>
var phpself = "{$smarty.server.PHP_SELF}";

{literal}

function show_sku_list(vid, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.vd_sku_list_"+vid);
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
	
	var q = $(document.f_a).serialize();
	
	var params = {
		"a": "ajax_load_sku_list",
		vendor_id: vid
	}
	q += '&'+$H(params).toQueryString();
	
	new Ajax.Request(phpself, {
		parameters: q,
		method: 'post',
		onComplete: function(e){
			new Insertion.After($('tr_vendor_'+vid), e.responseText);
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


{if !$no_header_footer}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" class="form">
			<p>
				<div class="row">
					<div class="col-md-3">
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label">Branch</b>
						<select class="form-control" name="branch_id">
							<option value="" {if !$smarty.request.branch_id}selected{/if}>-- All --</option>
							{foreach from=$branches key=id item=branch}
								<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
							{/foreach}
						</select>
					{/if}
					</div>
			
				<div class="col-md-3">
					<b class="form-label">Date From</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="added1" readonly="1" size=23> &nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
					
			
					<div class="col-md-3">
						<b class="form-label">To</b>
					<div class="form-inline">
						<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="added2" readonly="1" size=23>&nbsp; <img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
					</div>
					</div>
				
			
					<div class="col-md-3">
						<b class="form-label">Vendor</b>
					<select class="form-control" name="vendor_id">
						<option value="" {if !$smarty.request.vendor_id}selected{/if}>-- All --</option>
						{foreach from=$vendors key=id item=v}
							<option value="{$v.id}" {if $smarty.request.vendor_id eq $v.id}selected{/if}>{$v.description}</option>
						{/foreach}
					</select> 
					</div>
				</div>
			</p>
		
			<p>
			<button class="btn btn-primary mt-2" name="a" value="show_report" >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" name="a" value="output_excel" >{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			<p>
				<div class="alert alert-primary rounded" style="max-width: 500px;">
					<b>Note:<br />
						{if !$config.sku_consign_selling_deduct_discount_as_cost}
							<font color="red">
								<br /><b>* Found config 'sku_consign_selling_deduct_discount_as_cost' is not turned on, therefore it will not calculate the consignment cost.</b>
							</font><br />
						{/if}
						* Report show in maximum 1 Month.<br />
						* Report shows Consignment SKU and finalised sales only.<br />
						* Consignment cost from this report is calculated based on [Amt excl. Tax * (100 - Trade Discount) * 0.01].<br />
						* Please take note Consignment cost will not exactly the same as finalised cost from other reports.
						</b>
				</div>
			</p>
		</form>
	</div>
</div>

{/if}


{if $table.details}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	{foreach from=$table.sub_total key=bid item=vid_list}
		{foreach from=$vid_list key=vid item=dept_list}
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h5 class="content-title mb-0 my-auto ml-4 text-primary">
						{$branches.$bid.code} [{$vendors.$vid.code} - {$vendors.$vid.description}]
					</h5><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
			<div class="card mx-3">
				<div class="card-body">
					<table class="report_table table mb-0 text-md-nowrap  table-hover" id="report_tbl" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
							<th width="3%" rowspan="2">No</th>
							<th rowspan="2">SKU Item Code</th>
							<th rowspan="2">Art No</th>
							<th rowspan="2">MCode</th>
							<th rowspan="2">Description</th>
							<th colspan="4">Sales</th>
							<th colspan="3">Average</th>
							<th colspan="3" nowrap>Consignment</th>
							<!--th colspan="3" nowrap>Actual</th-->
						</tr>
						<tr class="header">
							<th width="5%">Qty <sup><font color="blue">[a]</font></sup></th>
							<th width="5%">Amt incl.<br />Tax <sup><font color="blue">[b]</font></sup></th>
							<th width="5%">Tax <sup><font color="blue">[c]</font></sup></th>
							<th width="5%">Amt excl.<br />Tax <sup><font color="blue">[d]</font></sup></th>
							<th width="5%">S.P incl.<br />Tax [<a href="javascript:void(alert('b / a'));">?</a>]</th>
							<th width="5%">Unit<br />Tax [<a href="javascript:void(alert('c / a'));">?</a>]</th>
							<th width="5%">S.P excl.<br />Tax [<a href="javascript:void(alert('d / a'));">?</a>]</th>
							<th width="5%">Cost</th>
							<th width="5%">GP</th>
							<th width="5%">GP %</th>
							<!--th width="5%">Cost</th>
							<th width="5%">GP</th>
							<th width="5%">GP %</th-->
						</tr>
						</thead>
						<tbody class="fs-08">
							{foreach from=$dept_list key=dept_id item=price_list}
								{foreach from=$price_list key=price_type item=disc_rate_list}
									{foreach from=$disc_rate_list key=disc_rate item=st}
										<tr bgcolor="#AFF380">
											<td colspan="4"><b>{$st.dept_name}</b></td>
											<td><b>{$price_type|default:'N/A'} - {$disc_rate|ifzero:'0'|number_format:2}%</b></td>
											<td class="r">{$st.sales_qty|ifzero:'0'|qty_nf}</td>
											<td class="r">{$st.sales_amt|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.tax_amt|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.nett_sales_amt|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.avg_sp|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.avg_gst|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.avg_nett_sp|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
											<td class="r">{$st.consign_gp|ifzero:'0'|number_format:2}</td>
											<td class="r">{$st.consign_gp_perc|ifzero:'0'|number_format:2}%</td>
										</tr>
										{assign var=count value=0}
										{foreach from=$table.details.$bid.$vid.$dept_id.$price_type.$disc_rate key=sid item=r}
											<!--{$count++}-->
											<tr>
												<td nowrap>{$count}.</td>
												<td>{$r.sku_item_code}</td>
												<td>{$r.artno}</td>
												<td>{$r.mcode}</td>
												<td>{$r.sku_desc}</td>
												<td class="r">{$r.sales_qty|ifzero:'0'|qty_nf}</td>
												<td class="r">{$r.sales_amt|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.tax_amt|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.nett_sales_amt|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.avg_sp|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.avg_gst|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.avg_nett_sp|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
												<td class="r">{$r.consign_gp|ifzero:'0'|number_format:2}</td>
												<td class="r">{$r.consign_gp_perc|ifzero:'0'|number_format:2}%</td>
												<!--td class="r">{$r.actual_cost_amt|number_format:$config.global_cost_decimal_points|ifzero}</td>
												<td class="r">{$r.actual_gp|number_format:2|ifzero}</td>
												<td class="r">{$r.actual_gp_perc|number_format:2|ifzero}%</td-->
											</tr>
										{/foreach}
									{/foreach}
								{/foreach}
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		{/foreach}
	{/foreach}

	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Total by Vendor</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>

	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table table mb-0 text-md-nowrap  table-hover"  id="report_tbl" width="100%">
					<div class="thead bg-gray-100">
						<tr class="header">
							<th rowspan="2" width="20%">Vendor</th>
							<th colspan="4" width="5%">Sales</th>
							<th rowspan="2" width="5%">Consignment<br />Cost</th>
							<th rowspan="2" width="5%">GP</th>
							<th rowspan="2" width="5%">GP %</th>
							<th colspan="{$sales_gst_list|@count}"> Sales Amt by Tax Type</th>
							<th colspan="{$sales_gst_list|@count}"> Consignment Cost by Tax Type</th>
						</tr>
						
						<tr class="header">
							<th width="5%">Qty</th>
							<th width="5%">Amt incl. Tax</th>
							<th width="5%">Tax</th>
							<th width="5%">Amt excl. Tax</th>
							{foreach from=$sales_gst_list key=gcode item=gst_code}
								<th width="5%">{$gst_code}</th>
							{/foreach}
							{foreach from=$sales_gst_list key=gcode item=gst_code}
								<th width="5%">{$gst_code}</th>
							{/foreach}
						</tr>
					</div>
					
						{foreach from=$table.total.vendor key=vid item=v}
							<div class="tbody fs-08">
								<tr>
									<td>{$vendors.$vid.code} - {$vendors.$vid.description}</td>
									<td class="r">{$v.sales_qty|ifzero:'0'|qty_nf}</td>
									<td class="r">{$v.sales_amt|ifzero:'0'|number_format:2}</td>
									<td class="r">{$v.tax_amt|ifzero:'0'|number_format:2}</td>
									<td class="r">{$v.nett_sales_amt|ifzero:'0'|number_format:2}</td>
									<td class="r">{$v.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
									<td class="r">{$v.consign_gp|ifzero:'0'|number_format:2}</td>
									<td class="r">{$v.consign_gp_perc|ifzero:'0'|number_format:2}%</td>
									{foreach from=$sales_gst_list key=gcode item=gst_code}
										<td class="r">{$v.gst_list.$gst_code.nett_sales_amt|ifzero:'0'|number_format:2}</td>
									{/foreach}
									{foreach from=$sales_gst_list key=gcode item=gst_code}
										<td class="r">{$v.gst_list.$gst_code.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
									{/foreach}
								</tr>
							</div>
						{/foreach}
					</table>
			</div>
		</div>
	</div>

	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Total by Branch</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	
<div class="card mx-3">
	<div class="card-body">
		<table class="report_table table mb-0 text-md-nowrap  table-hover" id="report_tbl" width="100%">
			<div class="thead bg-gray-100">
				<tr class="header">
					<th rowspan="2" width="20%">Branch</th>
					<th colspan="4" width="5%">Sales</th>
					<th rowspan="2" width="5%">Consignment<br />Cost</th>
					<th rowspan="2" width="5%">GP</th>
					<th rowspan="2" width="5%">GP %</th>
					<th colspan="{$sales_gst_list|@count}"> Sales Amt by Tax Type</th>
					<th colspan="{$sales_gst_list|@count}"> Consignment Cost by Tax Type</th>
				</tr>
				
				<tr class="header">
					<th width="5%">Qty</th>
					<th width="5%">Amt incl. Tax</th>
					<th width="5%">Tax</th>
					<th width="5%">Amt excl. Tax</th>
					{foreach from=$sales_gst_list key=gcode item=gst_code}
						<th width="5%">{$gst_code}</th>
					{/foreach}
					{foreach from=$sales_gst_list key=gcode item=gst_code}
						<th width="5%">{$gst_code}</th>
					{/foreach}
				</tr>
			</div>
		
			{foreach from=$table.total.branch key=bid item=b}
				<tbody class="fs-08">
					<tr>
						<td>{$branches.$bid.code} - {$branches.$bid.description}</td>
						<td class="r">{$b.sales_qty|ifzero:'0'|qty_nf}</td>
						<td class="r">{$b.sales_amt|ifzero:'0'|number_format:2}</td>
						<td class="r">{$b.tax_amt|ifzero:'0'|number_format:2}</td>
						<td class="r">{$b.nett_sales_amt|ifzero:'0'|number_format:2}</td>
						<td class="r">{$b.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
						<td class="r">{$b.consign_gp|ifzero:'0'|number_format:2}</td>
						<td class="r">{$b.consign_gp_perc|ifzero:'0'|number_format:2}%</td>
						{foreach from=$sales_gst_list key=gcode item=gst_code}
							<td class="r">{$b.gst_list.$gst_code.nett_sales_amt|ifzero:'0'|number_format:2}</td>
						{/foreach}
						{foreach from=$sales_gst_list key=gcode item=gst_code}
							<td class="r">{$b.gst_list.$gst_code.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
						{/foreach}
					</tr>
				</tbody>
			{/foreach}
		</table>
	</div>
</div>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Total by Price Type</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

	<div class="card mx-3">
		<div class="card-body">
			<table class="report_table  table mb-0 text-md-nowrap  table-hover" id="report_tbl" width="100%">
				<div class="thead bg-gray-100">
					<tr class="header">
						<th rowspan="2" width="20%">Price Type</th>
						<th colspan="4" width="5%">Sales</th>
						<th rowspan="2" width="5%">Consignment<br />Cost</th>
						<th rowspan="2" width="5%">GP</th>
						<th rowspan="2" width="5%">GP %</th>
					</tr>
					
					<tr class="header">
						<th width="5%">Qty</th>
						<th width="5%">Amt incl. Tax</th>
						<th width="5%">Tax</th>
						<th width="5%">Amt excl. Tax</th>
					</tr>
				</div>
			
				{foreach from=$table.total.price_type key=ptype item=pt}
					<tbody class="fs-08">
						<tr>
							<td>{$pt.price_type|default:'N/A'} - {$pt.discount_rate|default:'0'}%</td>
							<td class="r">{$pt.sales_qty|ifzero:'0'|qty_nf}</td>
							<td class="r">{$pt.sales_amt|ifzero:'0'|number_format:2}</td>
							<td class="r">{$pt.tax_amt|ifzero:'0'|number_format:2}</td>
							<td class="r">{$pt.nett_sales_amt|ifzero:'0'|number_format:2}</td>
							<td class="r">{$pt.consign_cost_amt|ifzero:'0'|number_format:$config.global_cost_decimal_points}</td>
							<td class="r">{$pt.consign_gp|ifzero:'0'|number_format:2}</td>
							<td class="r">{$pt.consign_gp_perc|ifzero:'0'|number_format:2}%</td>
						</tr>
					</tbody>
				{/foreach}
			</table>
		</div>
	</div>
{else}
	- No Data -
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
