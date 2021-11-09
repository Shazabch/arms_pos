{*
3/5/2018 3:27 PM Justin
- Bug fixed on columns showing empty instead of zero when no figure.

3/30/2018 2:14 PM Andy
- Hide "No Data" message when user not yet submit the report.

06/30/2020 11:17 AM Sheila
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

function toggle_po_owner(obj){
	if(obj == undefined) return;

	$('tbl_form').getElementsByClassName("chkbx_po_owner").each(function(inp){
		if (obj.checked == true){
			inp.checked = true;
		}else inp.checked = false;
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
		<form name="f_a" method="post" class="form" id="tbl_form">
			<input type="hidden" name="load_data" value="1" />
			
			{if $errm}
				<div class="alert alert-primary rounded">
					<div id="err"><div class="errmsg"><ul>
						{foreach from=$errm item=e}
							<li> {$e} </li>
						{/foreach}
					</ul></div></div>
				</div>
			{/if}
		
			<p>
				<div class="row">
					<div class="col">
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
		
				<div class="col">
					<b class="form-label">Date From</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="added1" readonly="1" size=23> &nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				
				</div>
		
				<div class="col">
					<b class="form-label">To</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="added2" readonly="1" size=23>&nbsp; <img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
			
				</div>
				</div>
		
				<div>
					<b class="form-label mt-2">PO Owner</b>
					<div style="padding: 10px; width:20%;height:200px;border:1px solid #ddd;overflow:auto;">
						<input type="checkbox" name="tgl_po_owner" value="1" {if $smarty.request.tgl_po_owner}checked{/if} onclick="toggle_po_owner(this);">&nbsp;All<br />
						{foreach from=$owner_list item=owner}
							{assign var=owner_id value=$owner.id}
							<input type="checkbox" name="po_owner_list[{$owner_id}]" class="chkbx_po_owner" value="{$owner_id}" {if $smarty.request.po_owner_list.$owner_id}checked{/if}>&nbsp;{$owner.u}<br />
						{/foreach}
					</div>
				</div>
			</p>
		
			<p>
			<button class="btn btn-primary" name="a" value="show_report" >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info" name="a" value="output_excel" >{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
		
			<p><b>
			<div class="alert alert-primary rounded mt-2" style="max-width: 270px;">
				Note: <br>
			- Report Maximum Shown 1 Year
			</div>
			</b></p>
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

{if $smarty.request.load_data}
	{if $table}
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="report_table table mb-0 text-md-nowrap  table-hover" id="report_tbl" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
								<th width="3%" rowspan="2">No</th>
								<th colspan="2">Vendor</th>
								<th colspan="2">Sales</th>
								<th rowspan="2">Cost</th>
								<th rowspan="2">GP</th>
								<th rowspan="2">GP %</th>
							</tr>
							<tr class="header">
								<th width="5%">Code</th>
								<th width="30%">Name</th>
								<th width="5%">Qty</th>
								<th width="5%">Amount</th>
							</tr>
						</thead>
						<tbody class="fs-08">
							{foreach from=$table.details key=vid item=r name=i}
								<tr id="tr_vendor_{$r.vendor_id}">
									<td nowrap>
										{$smarty.foreach.i.iteration}.
										{if !$no_header_footer}
											<img src="/ui/expand.gif" onclick="javascript:void(show_sku_list('{$r.vendor_id}', this));" align="absmiddle">
										{/if}
									</td>
									<td>{$r.vd_code}</td>
									<td>{$r.vd_desc}</td>
									<td class="r">{$r.sales_qty|qty_nf}</td>
									<td class="r">{$r.sales_amt|default:0|number_format:2}</td>
									<td class="r">{$r.cost_amt|default:0|number_format:$config.global_cost_decimal_points}</td>
									<td class="r">{$r.gp|default:0|number_format:2}</td>
									<td class="r">{$r.gp_perc|default:0|number_format:2}%</td>
								</tr>
							{/foreach}
						</tbody>
						<tr class="header">
							<th class="r" colspan="3">Total</th>
							<td class="r">{$table.total.ttl_sales_qty|qty_nf}</td>
							<td class="r">{$table.total.ttl_sales_amt|default:0|number_format:2}</td>
							<td class="r">{$table.total.ttl_cost_amt|default:0|number_format:$config.global_cost_decimal_points}</td>
							<td class="r">{$table.total.ttl_gp|default:0|number_format:2}</td>
							<td class="r">{$table.total.ttl_gp_perc|default:0|number_format:2}%</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	{else}
		<div class="mx-3">
			- No Data -
		</div>
	{/if}
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
