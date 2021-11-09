{*
12/30/2011 2:57:43 PM Justin
- Added checking for show cost base on sessioninfo.

1/3/2012 9:54:21 AM Andy
- Add size and color column.

1/31/2012 3:18:58 PM Andy
- Reconstruct report layout to show by category instead of SKU.
- Enhance report to able to on page load sub-category.

2/8/2012 2:14:44 PM Andy
- Remove color/size by row, change to show by matrix table.
- Add "Average" column.
- Add "Qty Matrix Table".

4/9/2012 5:21:34 PM Justin
- Modified to have multiple selection for branch filter.

9/5/2012 3:30:35 PM Fithri
- Add price range filter

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour".

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on
- change some words to use British english

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

06/30/2020 11:31 AM Sheila
- Updated button css.
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


{literal}
<style>
.div_multi_select{
	border:1px solid grey;
	overflow:auto;
	height:250px;
	width:200px;
	padding: 2px;
}

.color_header{
	min-width:60px;
}
</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
var show_tran_count = int('{$show_tran_count}');

{literal}
function init_calendar(){
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
}

function toggle_group_chx(chx){
	var ul = chx.parentNode.parentNode;
	var all_li = $(ul).getElementsBySelector("input");
	
	for(var i=0; i<all_li.length; i++){
		all_li[i].checked = chx.checked;
	}
}

function show_sub(cat_id){
	document.f_a['category_id'].value = cat_id;
	document.f_a.submit();
}

function expand_sub(cat_id, indent, img){
	if(img.src.indexOf('clock')>0)   return;
	
	var tr_cat_row = $('tbody_cat_row-'+cat_id);
	img.src = '/ui/clock.gif';
	
	var params = {};
	
	params['a'] = 'ajax_expand_sub';
	params['indent'] = indent;
	
	// use back form info
	//if(document.f_a['branch_id'])	params['branch_id'] = document.f_a['branch_id'].value;
	params['sku_type'] = document.f_a['sku_type'].value;
	params['from'] = document.f_a['from'].value;
	params['to'] = document.f_a['to'].value;

	// clone brand id
	var branch_id_list = [];
	for(var i=0; i<document.f_a['branch_id[]'].length; i++){
		if(document.f_a['branch_id[]'][i].checked){
			branch_id_list.push(document.f_a['branch_id[]'][i].value);
		}
	}
	params['branch_id[]'] = branch_id_list;	
	
	// clone brand id
	var brand_id_list = [];
	for(var i=0; i<document.f_a['brand_id[]'].length; i++){
		if(document.f_a['brand_id[]'][i].checked){
			brand_id_list.push(document.f_a['brand_id[]'][i].value);
		}
	}
	params['brand_id[]'] = brand_id_list;	
	
	// clone color
	var color_list = [];
	for(var i=0; i<document.f_a['color[]'].length; i++){
		if(document.f_a['color[]'][i].checked){
			color_list.push(document.f_a['color[]'][i].value);
		}
	}
	params['color[]'] = color_list;
	
	// clone size
	var size_list = [];
	for(var i=0; i<document.f_a['size[]'].length; i++){
		if(document.f_a['size[]'][i].checked){
			size_list.push(document.f_a['size[]'][i].value);
		}
	}
	params['size[]'] = size_list;
	
	// clone price range
	var price_range_list = [];
	for(var i=0; i<document.f_a['price_range[]'].length; i++){
		if(document.f_a['price_range[]'][i].checked){
			price_range_list.push(document.f_a['price_range[]'][i].value);
		}
	}
	params['price_range[]'] = price_range_list;
	
	// use new info
	params['category_id'] = cat_id;


	new Ajax.Request(phpself, {
		method:'post',
		parameters: params,
		onComplete: function(e){
			new Insertion.After(tr_cat_row, e.responseText);
			img.remove();
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
		<div class="noprint stdframe">
			<form name="f_a" method="post">
				<input type="hidden" name="subm" value="1" />
			
				<div class="row">
					{*if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-3">
						<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id">
					{foreach from=$branches key=bid item=r}
						{if !$branches_group.have_group.$bid}
							<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
					{/foreach}
					{if $branches_group.header}
						{foreach from=$branches_group.header key=bgid item=bg}
							<optgroup label='{$bg.code}'>
								{foreach from=$branches_group.items.$bgid item=r}
									<option value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					{/if}
					</select>
					</div>
				{/if*}
			
				<div class="col-md-3">
					<b class="form-label">SKU Type</b>
					<select class="form-control" name="sku_type">
						<option value="">-- All --</option>
						{foreach from=$sku_type item=r}
							<option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
						{/foreach}
					</select>
					
				</div>
				<div class="col-md-3">
					<b class="form-label">Date From</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</div>
				
				</div>
				<div class="col-md-3">
					<b class="form-label">To</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
				</div>
				
				</div>
				<div class="col-md-3">
					<div class="form-inline form-label">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
				</div>
				
				<p>
					{if $config.allow_all_sku_branch_for_selected_reports}
					{include file='category_autocomplete.tpl' all=true}
					{else}
					{include file='category_autocomplete.tpl' all=false}
					{/if}
				</p>
				
				
				<p>
					<table>
						<tr>
							{if $BRANCH_CODE eq 'HQ'}
								<td><b class="form-label">Branch</b></td>
							{/if}
							<td><b class="form-label">Brand</b></td>
							<td><b class="form-label">Colour</b></td>
							<td><b class="form-label">Size</b></td>
							{if $price_range}
							<td><b class="form-label">Price Range</b></td>
							{/if}
						</tr>
						<tr>
							{if $BRANCH_CODE eq 'HQ'}
								<!-- Branch -->
								<td>
									<div class="div_multi_select">
										<ul style="list-style:none;">
											<li><input type="checkbox" onChange="toggle_group_chx(this);" /> <b>All</b></li>
											{foreach from=$branches key=branch_id item=r}
											
												{if $config.sales_report_branches_exclude}
												{if in_array($r.code,$config.sales_report_branches_exclude)}
												{assign var=skip_this_branch value=1}
												{else}
												{assign var=skip_this_branch value=0}
												{/if}
												{/if}
											
												{if !$skip_this_branch}
												<li>
													<input type="checkbox" name="branch_id[]" value="{$branch_id}" {if is_array($smarty.request.branch_id) and in_array($branch_id, $smarty.request.branch_id)}checked {/if} /> {$r.code}
												</li>
												{/if}
												
											{/foreach}
										</ul>
									</div>
								</td>
							{/if}
			
							<!-- Brand -->
							<td>
								<div class="div_multi_select">
									<ul style="list-style:none;">
										<li><input type="checkbox" onChange="toggle_group_chx(this);" /> <b>All</b></li>
										<li><input type="checkbox" name="brand_id[]" value="0" {if is_array($smarty.request.brand_id) and in_array(0, $smarty.request.brand_id)}checked {/if} /> UN-BRANDED</li>
										{if $brand_group}
										<li><br /><b>Brand Group</b></li>
										{foreach from=$brand_group key=bgk item=bgv}
											<li>
												<input type="checkbox" name="brand_group[]" value="{$bgk}" {if is_array($smarty.request.brand_group) and in_array($bgk, $smarty.request.brand_group)}checked {/if} /> {$bgv}
											</li>
										{/foreach}
										{/if}
										<li><br /><b>Brand</b></li>
										{foreach from=$brands key=brand_id item=r}
											<li>
												<input type="checkbox" name="brand_id[]" value="{$brand_id}" {if is_array($smarty.request.brand_id) and in_array($brand_id, $smarty.request.brand_id)}checked {/if} /> {$r.description}
											</li>
										{/foreach}
									</ul>
								</div>
							</td>
							
							<!-- Color -->
							<td>
								<div class="div_multi_select">
									<ul style="list-style:none;">
										<li><input type="checkbox" onChange="toggle_group_chx(this);" /> <b>All</b></li>
										<li><input type="checkbox" name="color[]" value="NOTSET" {if is_array($smarty.request.color) and in_array('NOTSET', $smarty.request.color)}checked {/if} /> NOT SET</li>
										{foreach from=$colors item=c}
											<li>
												<input type="checkbox" name="color[]" value="{$c}" {if is_array($smarty.request.color) and in_array($c, $smarty.request.color)}checked {/if} /> {$c}
											</li>
										{/foreach}
									</ul>
								</div>
							</td>
							
							<!-- Size -->
							<td>
								<div class="div_multi_select">
									<ul style="list-style:none;">
										<li><input type="checkbox" onChange="toggle_group_chx(this);" /> <b>All</b></li>
										<li><input type="checkbox" name="size[]" value="NOTSET" {if is_array($smarty.request.size) and in_array('NOTSET', $smarty.request.size)}checked {/if} /> NOT SET</li>
										{foreach from=$sizes item=s}
											<li>
												<input type="checkbox" name="size[]" value="{$s}" {if is_array($smarty.request.size) and in_array($s, $smarty.request.size)}checked {/if} /> {$s}
											</li>
										{/foreach}
									</ul>
								</div>
							</td>
							<!-- Price range -->
							{if $price_range}
							<td>
								<div class="div_multi_select">
									<ul style="list-style:none;">
										<li><input type="checkbox" onChange="toggle_group_chx(this);" /> <b>All</b></li>
										{foreach from=$price_range item=s}
											<li>
												<input type="checkbox" name="price_range[]" value="{$s.from}-{$s.to}" {if is_array($smarty.request.price_range) and in_array($s.from, $smarty.request.price_range)}checked {/if} /> {$s.from} - {$s.to}
											</li>
										{/foreach}
									</ul>
								</div>
							</td>
							{/if}
						</tr>
					</table>
					<br style="clear:both;">
				</p>
				
				<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info" name="output_excel">{#OUTPUT_EXCEL#}</button>
				{/if}
			
			</form>
			</div>
	</div>
</div>


<script>
init_calendar();
</script>
{/if}
 

{if !$data}
	{if $smarty.request.subm && !$err}<p align=center>-- No data --</p>{/if}
{else}
	
<br />
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if !$no_header_footer}
    <p>
		<div class="card mx-3">
			<div class="card-body">
				&#187; <!--<a href="javascript:void(show_sub(0));">ROOT</a>-->ROOT /
		{if $selected_cat_info}
		    {foreach from=$selected_cat_info.cat_tree_info item=ct name=f}
				{if !$smarty.foreach.f.first}
		        <a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a>
				{else}
					{$ct.description}
				{/if}
				/
		    {/foreach}
		    {$selected_cat_info.description} /
		{/if}
			</div>
		</div>
	</p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="2">Category</th>
						<th rowspan="2">Qty</th>
						<th rowspan="2">Amt</th>
						{if $sessioninfo.show_cost}
							<th rowspan="2">Cost</th>
						{/if}
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<th rowspan="2">GP</th>
							<th rowspan="2">GP %</th>
						{/if}
				
					
						{assign var=cols value=1} 
						{if $sessioninfo.show_cost}{assign var=cols value=$cols+1}{/if}
						{if $sessioninfo.privilege.SHOW_REPORT_GP}{assign var=cols value=$cols+1}{/if}
					
						<th colspan="{$cols}" nowrap>Average</th>
						<th colspan="4" nowrap>Sales Trend</th>
						<th rowspan="2" width="50">Qty Matrix Table</th>
					</tr>
					<tr class="header">
						<!-- Average -->
						<th>Amt</th>
						{if $sessioninfo.show_cost}<th>Cost</th>{/if}
						{if $sessioninfo.privilege.SHOW_REPORT_GP}<th>GP%</th>{/if}
						
						<!-- Sales Trend -->
						<th>1M</th>
						<th>3M</th>
						<th>6M</th>
						<th>12M</th>
					</tr>
				</thead>
				
				{include file='report.category_brand_color_size.row.tpl'}
					
				<tr class="header">
					<th rowspan="2" class="r">Total</th>
					<th rowspan="2" class="r">{$total.qty|qty_nf}</th>
					<th rowspan="2" class="r">{$total.amount|number_format:2}</th>
					{if $sessioninfo.show_cost}
						<th rowspan="2" class="r">{$total.cost|number_format:$config.global_cost_decimal_points}</th>
					{/if}
					{if $sessioninfo.privilege.SHOW_REPORT_GP}
						<th rowspan="2" class="r">{$total.gp|number_format:2}</th>
						<th rowspan="2" class="r">{$total.gp_per|number_format:2}%</th>
					{/if}
					
					<!-- Average -->
					<th rowspan="2" class="r">{$total.avg_amt|number_format:2}</th>
					{if $sessioninfo.show_cost}
						<th rowspan="2" class="r">{$total.avg_cost|number_format:$config.global_cost_decimal_points}</th>
					{/if}
					{if $sessioninfo.privilege.SHOW_REPORT_GP}
						<th rowspan="2" class="r">{$total.avg_gp_per|number_format:2}%</th>
					{/if}
					<!-- Sales Trend -->
					<th class="r">{$total.sales_trend.qty.1|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.3|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.6|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.12|qty_nf:".":""|ifzero}</th>
				</tr>
				<tr class="header">
					<th class="r">{$total.sales_trend.qty.1|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.3/3|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.6/6|qty_nf:".":""|ifzero}</th>
					<th class="r">{$total.sales_trend.qty.12/12|qty_nf:".":""|ifzero}</th>
				</tr>
			</table>
		</div>
	</div>
</div>
{/if}

{include file='footer.tpl'}