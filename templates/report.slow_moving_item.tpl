{*
10/17/2011 9:50:12 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/14/2011 5:17:53 PM Andy
- Add vendor filter

08/28/2012 10:22:50 AM Fithri
- Add percentage column for all branches

12/19/2012 6:00 PM Justin
- Added new filter option "Zero Sales with Stocks".

2/4/2013 5:34 PM Justin
- Converted this report to extend module instead of report.
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/6/2014 4:10 PM Justin
- Enhanced to have "Use GRN".

08/12/2016 16:30 Edwin
- Enhanced on show "stock balance" in report.

3/17/2017 3:37 PM Andy
- Reconstruct program structure.

06/30/2020 02:42 PM Sheila
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
function toggle_sub(tbody_id, el)
{
	if ($(tbody_id).style.display=='none')
	{
	    el.src='/ui/collapse.gif';
	    $(tbody_id).style.display='';
	}
	else
	{
	    el.src='/ui/expand.gif';
	    $(tbody_id).style.display='none';
	}
}

function check_use_grn(){
	var allow_use_grn = true;
	
	if(document.f_a['branch_id']){
		if(!document.f_a['branch_id'].value || document.f_a['branch_id'].value<0)	{ //all branch selected
			allow_use_grn = false;
		}
	}
	
	if(!document.f_a['vendor_id'].value)	allow_use_grn = false;
	
	if(allow_use_grn){
		$('use_grn').disabled=false;
	}else{
		$('use_grn').checked=false;
		$('use_grn').disabled=true;
	}
}

function show_percentage_info() {
    alert("Sales quantity / Total Sales quanity in category * 100");
}

var SLOW_MOVING_ITEMS = {
	f: undefined,
	initilize: function(){
		this.f = document.f_a;
		check_use_grn();
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
	},
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		this.f.submit();
	}
}
</script>
<style>
.c1 { background:#fff; }
.c2 { background:#eee; }
.c3 { background:#efefef; }
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
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

{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">

			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="export_excel" />
			<input type="hidden" name="page" value="0" />
				
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">Date From</b> 
				<div class="form-inline">
					<input class="form-control" size=17 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=17 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Sales Quantity <= </b>
				<input class="form-control" type="text" name="quantity" value="{$smarty.request.quantity}" size="10">
				</div>
				
				{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-3">
					<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id" onchange="check_use_grn();">
						<option value="">-- All --</option>
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
							<optgroup label='Branch Group'>
							{foreach from=$branch_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
								{foreach from=$branch_group.items.$bgid item=r}
									<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							{/foreach}
							</optgroup>
						{/if}
						{if $config.consignment_modules && $config.masterfile_branch_region}
							<optgroup label='Region'>
							{foreach from=$config.masterfile_branch_region key=type item=f}
								{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
									{assign var=curr_type value="REGION_`$type`"}
									<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
								{/if}
							{/foreach}
							</optgroup>
						{/if}
					</select>
				</div>
				{/if}
			</div>
			
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>
			
			<p>
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">Vendor</b>
					<select class="form-control" name="vendor_id" onchange="check_use_grn();">
						<option value="">-- All --</option>
						{foreach from=$vendor key=vid item=v}
							<option value="{$vid}" {if $vid eq $smarty.request.vendor_id}selected {/if}>{$v.description}</option>
						{/foreach}
					</select>
				</div>
					
					<div class="col-md-3">
						<div class="form-label form-inline mt-4">
							<input type="checkbox" id="use_grn" name="use_grn" value="1" {if $smarty.request.use_grn}checked{/if}> <b>&nbsp;Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
						</div>
					</div>
			</div>
			</p>
			
			<input class="btn btn-primary mt-2" type="button" value='Show Report' onClick="SLOW_MOVING_ITEMS.submit_report();" />
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" onClick="SLOW_MOVING_ITEMS.submit_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
			<div class="form-label form-inline mt-2">
				<input type="checkbox" name="group_sku" {if $smarty.request.group_sku}checked {/if} value="1" /> <b>&nbsp;Group by SKU</b>
			&nbsp;&nbsp;
			<input type="checkbox" name="zero_sales_with_stock" {if $smarty.request.zero_sales_with_stock}checked {/if} value="1" /> <b>&nbsp;Zero Sales with Stocks</b>
			&nbsp;&nbsp;
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			</form>
	</div>
</div>
{/if}
{if !$table}
	{if $smarty.request.show_report && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

	{foreach from=$table key=cat_id item=s}
		<h2>{$category.$cat_id.cname}</h2>
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
								<th width="10%">ARMS Code</th>
								<th width="10%">MCode</th>
								<th width="5%">Art No.</th>
								<th>Description</th>
								<th width="10%">Sales Quantity</th>
								<th width="2%">% {if !$no_header_footer}[<a href="javascript:void(show_percentage_info());">?</a>]{/if}</th>
								<th width="10%">Stock Balance</th>
							</tr>
						</thead>
						{foreach from=$s key=sku_key item=r}
							{cycle values="c2,c1" assign=row_class name=row1}
							<tbody class="fs-08">
								<tr class="{$row_class}">
									<td>{$sku.$sku_key.sku_item_code}</td>
									<td class="c">{$sku.$sku_key.mcode|default:'-'}</td>
									<td align="center">{$sku.$sku_key.artno|default:'-'}</td>
									<td>{$sku.$sku_key.description}</td>
									<td class="r">{$r.qty|qty_nf|ifzero:'-'}</td>
									<td class="r">{if $total.$cat_id.qty > 0}{$r.qty/$total.$cat_id.qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</td>
									<td class="r">{$r.stock_bal|qty_nf|ifzero:'-'}</td>
								</tr>
							</tbody>
						{/foreach}
						<tr class="header">
							<th colspan="4" class="r">Total</th>
							<th class="r">{$total.$cat_id.qty|qty_nf|ifzero:'-'}</th>
							<th class="r">{if $total.$cat_id.qty > 0}{$total.$cat_id.qty/$total.$cat_id.qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
							<th class="r">{$total.$cat_id.stock_bal|qty_nf|ifzero:'-'}</th>
						</tr>
						</table>
				</div>
			</div>
		</div>
	{/foreach}
{/if}
{if !$no_header_footer}
	{literal}
	<script type="text/javascript">
		SLOW_MOVING_ITEMS.initilize();
	</script>
	{/literal}
{/if}
{include file=footer.tpl}

