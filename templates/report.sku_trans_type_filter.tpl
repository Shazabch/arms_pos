{*
06/30/2020 02:42 PM Sheila
- Updated button css.
*}
{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{if !$no_header_footer}
{literal}
<script>
function check_trans_type(filter_type,trans_type){
	var filter_type2 = '';
	if(filter_type == 'with')  filter_type2 = 'without';
	else  filter_type2 = 'with';
	
	var checkbox1 = document.f_d[filter_type+'_trans_type['+trans_type+']'];
	var checkbox2 = document.f_d[filter_type2+'_trans_type['+trans_type+']'];
	
	if(checkbox1.checked == true){
		checkbox2.disabled = true;
		checkbox2.checked = false;
	}else{
		checkbox2.disabled = false;
	}
}

//check form filter
function check_form(){
	var all_category = document.f_d['all_category'];
	var with_trans_type = $$('.with_trans_type');
	var without_trans_type = $$('.without_trans_type');
	var trans_type_checked = 0;
	if(all_category.checked == false && document.f_d['category_id'].value == ''){
		alert("Invalid Category");
		return false;
	}
	
	with_trans_type.forEach((with_trans_type) => {
		if(with_trans_type.checked) trans_type_checked+= 1;
	});
	without_trans_type.forEach((without_trans_type) => {
		if(without_trans_type.checked) trans_type_checked+= 1;
	});
	
	if(trans_type_checked == 0){
		alert("Must select at least one Transaction Type.");
		return false;
	}
}

</script>
{/literal}
{/if}

<div class="noprint">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
</div>

{if $err}
	<div class="alert alert-danger mx-3 rounded">
		<ul style="color:red;">
			{foreach from=$err item=e}
				<li><b>{$e}</b></li>
			{/foreach}
		</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_d" onsubmit="return check_form();"  method="post">
			<div class="noprint stdframe">
			<input type="hidden" name="a" value="show_report" />
			
			{if !$no_header_footer}
			<p>
			{include file='category_autocomplete.tpl' all=$config.allow_all_sku_branch_for_selected_reports}
			</p>
			
			<p>
				<div class="row">
					<div class="col-md-3">
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label mt-2">Branch</b>
						<select class="form-control" name="branch_id">
						{foreach from=$branch key=bid item=b}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
						</select> 
					{/if}
					</div>
				
					<div class="col-md-3">
						<b class="form-label mt-2">SKU Type</b>
					<select class="form-control" name="sku_type">
						<option value="">-- All --</option>
						{foreach from=$sku_type item=t}
							<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
						{/foreach}
					</select>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">SKU Status</b>
					<select class="form-control" name="sku_status">
						<option value="all" {if $smarty.request.sku_status eq 'all'}selected {/if}>-- All --</option>
						<option value="1" {if $smarty.request.sku_status eq '1' or $smarty.request.sku_status eq ''}selected {/if}>Active</option>
						<option value="0" {if $smarty.request.sku_status eq '0'}selected {/if}>Inactive</option>
					</select>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">Bom Type</b>
					<select class="form-control" name="is_bom">
						<option value="" {if $smarty.request.is_bom eq ''}selected {/if}>-- All --</option>
						<option value="1" {if $smarty.request.is_bom eq '1'}selected {/if}>Yes</option>
						<option value="0" {if $smarty.request.is_bom eq '0'}selected {/if}>No</option>
					</select>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">No Inventory</b>
					<select class="form-control" name="no_inventory">
						<option value="" {if $smarty.request.no_inventory eq ''}selected {/if}>All</option>
						<option value="1" {if $smarty.request.no_inventory eq '1'}selected {/if}>Yes</option>
						<option value="0" {if $smarty.request.no_inventory eq '0'}selected {/if}>No</option>
					</select>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">Date From</b> 
					<div class="form-inline">
						<input class="form-control" type="text" name="from" value="{$form.from}" id="added1" readonly="1" size=17> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"> 
					</div>
					</div>
					
					<div class="col-md-3">
						<b class="form-label mt-2">To</b> 
				<div class="form-inline">
					<input class="form-control" type="text" name="to" value="{$form.to}" id="added2" readonly="1" size=17> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
					</div>
				</div>
			</p>
			
			<p>
				<div class="row">
					<div class="col">
						<b class="form-label mt-2">With Transaction Type</b>
				{foreach from=$transaction_type key=type_key item=type}
				<input class="with_trans_type" type="checkbox" onclick="check_trans_type('with','{$type_key}')" name="with_trans_type[{$type_key}]" {if $smarty.request.without_trans_type[$type_key] eq $type_key}disabled{/if} value="{$type_key}" {if $smarty.request.with_trans_type[$type_key] eq $type_key}checked{/if} /> {$type}&nbsp;
				{/foreach}
					</div>
				
				<div class="col">
					<b class="form-label mt-2">Condition</b>
				<select class="form-control" name="condition">
					<option value="or" {if $smarty.request.condition eq 'or'}selected {/if}>Or</option>
					<option value="and" {if $smarty.request.condition eq 'and'}selected {/if}>And</option>
				</select>
				</div>
				</div>
			</p>
			<p>
					<div class="row">
						<div class="col">
							<b class="form-label mt-2">Without Transaction Type</b>&nbsp;
					{foreach from=$transaction_type key=type_key item=type}
					<input class="without_trans_type" type="checkbox" onclick="check_trans_type('without','{$type_key}')" name="without_trans_type[{$type_key}]" {if $smarty.request.with_trans_type[$type_key] eq $type_key}disabled{/if} value="{$type_key}" {if $smarty.request.without_trans_type[$type_key] eq $type_key}checked{/if} /> {$type}&nbsp;
					{/foreach}
						</div>
					
					<div class="col">
						<div class="form-label form-inline">
							<input type="checkbox" name="group_by_sku" {if $smarty.request.group_by_sku}checked{/if} />
						<b>&nbsp;Group By Parent SKU</b>
						</div>
					</div>
					</div>
			</p>
			<p>
				<button class="btn btn-primary mt-2" name="show_report">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-2" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
				{/if}
			</p>
			<p><div class="alert alert-primary rounded" style="max-width: 200px;">
				* Maximum show 1 year.
			</div></p>
			{/if}
			</div>
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
			<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="2">ARMS Code</th>
						<th rowspan="2">Mcode</th>
						<th rowspan="2">Artno </th>
						<th rowspan="2">Old Code</th>
						<th rowspan="2">Description</th>
						<th rowspan="2">BOM SKU</th>
						<th rowspan="2">Stock Check</th>
						<th rowspan="2">DO</th>
						<th rowspan="2">GRN</th>
						<th colspan="2" rowspan="1">Adjustment</th>
						<th rowspan="2">GRA</th>
						<th rowspan="2">POS</th>
						<th rowspan="2">Closing Stock</th>
					</tr>
					<tr class="header">
						<th rowspan="1">IN</th>
						<th rowspan="1">OUT</th>
					</tr>
				</thead>
				
				
			
				{foreach from=$table item=r}
				<tbody class="fs-08">
					<tr>
						<td align="left">{$r.sku_item_code}</td>
						<td align="left">{$r.mcode}</td>
						<td align="left">{$r.artno}</td>
						<td align="left">{$r.link_code}</td>
						<td align="left">{$r.description}</td>
						<td align="left">{if $r.is_bom eq 1}Yes{else}No{/if}</td>
						<td align="right">{$r.sc_qty|qty_nf|ifzero:"0"}</td>
						<td align="right">{$r.do_qty|qty_nf|ifzero}</td>
						<td align="right">{$r.grn_qty|qty_nf|ifzero}</td>
						<td align="right">{$r.adj_in|qty_nf|ifzero}</td>
						<td align="right">{$r.adj_out|qty_nf|ifzero}</td>
						<td align="right">{$r.gra_qty|qty_nf|ifzero}</td>
						<td align="right">{$r.pos_qty|qty_nf|ifzero}</td>
						<td align="right">{$r.sb_qty|qty_nf|ifzero}</td>
					</tr>
				</tbody>
				{/foreach}
				
				<tr class="header">
					<th colspan="6" align="right">Total</th>
					<th align="right">{$total.sc_qty|qty_nf|ifzero:"0"}</th>
					<th align="right">{$total.do_qty|qty_nf|ifzero}</th>
					<th align="right">{$total.grn_qty|qty_nf|ifzero}</th>
					<th align="right">{$total.adj_in|qty_nf|ifzero}</th>
					<th align="right">{$total.adj_out|qty_nf|ifzero}</th>
					<th align="right">{$total.gra_qty|qty_nf|ifzero}</th>
					<th align="right">{$total.pos_qty|qty_nf|ifzero}</th>
					<th align="right">{$total.sb_qty|qty_nf|ifzero}</th>
				</tr>
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