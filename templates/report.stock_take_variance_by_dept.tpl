{*
5/14/2010 2:38:24 PM Andy
- Add total stock take cost column.
- Fix a bugs that make alignment run due to no show_cost privilege

9/22/2011 5:37:32 PM Andy
- Add "Group by SKU" when view single department.

10/13/2011 5:17:21 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/29/2012 11:40:43 AM Justin
- Added hidden field for branch ID when logged in as sub branch.

9/4/2012 12:03 PM Justin
- Enhanced to have skip zero variance filter.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

4/12/2017 9:00 AM Qiu Ying
- Enhanced to change "Group by SKU" to "Sort by SKU" and if stock take is N/A, should not calculate variance

4/27/2017 16:09 Qiu Ying
- Bug fixed on negative quantity variance does not show in red color 

8/17/2017 10:05 AM Justin
- Enhanced to move Selling Price next to SKU Description.
- Enhanced extra information such as stock balance cost and total cost.

10/1/2018 9:17 AM Justin
- Enhanced to have "Location" filter.

12/10/2018 3:10 PM Justin
- Enhanced report to have pre stock take feature.

3/19/2019 4:53 PM Andy
- Enhanced to have Brand and Vendor filter.
- Rearrange the filter position.

7/29/2019 9:04 AM William
- Enhanced to have "Show auto fill zero item" filter.

8/9/2019 5:19 PM William
- Fixed bug location cannot auto load when change date.

06/30/2020 02:55 PM Sheila
- Updated button css.

12/02/2020 05:08 PM Rayleen
- Add link/old code column after Art No 
- Change Total column Colspan
- Put MCode after Arms Code
*}

{include file='header.tpl'}
{if !$no_header_footer}
<style>
{literal}
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var sort_by = '{$smarty.request.sort_by}';

{literal}
function change_branch(){
	var bid = document.f_a['branch_id'].value;
	var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
	
	$('span_st_date').update(_loading_);
	$('span_pre_st_date').update(_loading_);
	document.f_a['show_report'].disabled = true;
	if(document.f_a['output_excel'] != undefined) document.f_a['output_excel'].disabled = true;
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_reload_date',
			ajax: 1,
			'branch_id': bid
		},
		onComplete: function(m){
		    eval("var json = "+m.responseText);
		    
		    $('span_st_date').update(json['st_date']);
		    $('span_pre_st_date').update(json['pre_st_date']);
            document.f_a['show_report'].disabled = false;
			if(document.f_a['output_excel'] != undefined) document.f_a['output_excel'].disabled = false;
			load_location();
		}
	});
}

function load_location(){
    var bid = document.f_a['branch_id'].value;
	var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
	
	//hide and disable "Show auto fill zero item" filter when select Pre Stock Take.
	var show_auto_fill_zero_item_span = $('show_auto_fill_zero_item_span');
	var show_auto_fill_zero_item = $('show_auto_fill_zero_item');
	if(stock_take_type == 1){
		show_auto_fill_zero_item_span.show();
		show_auto_fill_zero_item.disabled = false;
	}else{
		show_auto_fill_zero_item_span.hide();
		show_auto_fill_zero_item.disabled = true;
	}

	if(stock_take_type == 1) var d = document.f_a['date'].value; // imported stock take date
	else var d = document.f_a['pre_date'].value; // pre stock take date

    var sku_with = document.f_a['sku_with'].value;

    if(sku_with!='sc')  return;

	$('span_location').update(_loading_);
	new Ajax.Updater('span_location',phpself,{
		parameters:{
			a: 'load_location',
			ajax: 1,
			branch_id: bid,
			d: d,
			stock_take_type: stock_take_type
		},
		onComplete: function(m){
			load_shelf_no();
		}
	});
}

function load_shelf_no(){
    var bid = document.f_a['branch_id'].value;
	var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
	if(stock_take_type == 1) var d = document.f_a['date'].value; // imported stock take date
	else var d = document.f_a['pre_date'].value; // pre stock take date
	var all_location = 0;
	if(document.f_a['all_location'].checked == true) all_location = 1;
	var location_from = document.f_a['location_from'].value;
	var location_to = document.f_a['location_to'].value;

    var sku_with = document.f_a['sku_with'].value;

    if(sku_with!='sc')  return;

	$('span_shelf_no').update(_loading_);
	new Ajax.Updater('span_shelf_no',phpself,{
		parameters:{
			a: 'load_shelf_no',
			ajax: 1,
			branch_id: bid,
			d: d,
			all_loc: all_location,
			loc_from: location_from,
			loc_to: location_to,
			stock_take_type: stock_take_type
		}
	});
}

function toggle_all_location(refresh_shelf_no){
	var c = document.f_a['all_location'].checked;

	if(c){
		document.f_a['location_from'].disabled = true;
		document.f_a['location_to'].disabled = true;
	}else{
        document.f_a['location_from'].disabled = false;
		document.f_a['location_to'].disabled = false;
	}
}

function toggle_all_shelf_no(){
	var c = document.f_a['all_shelf_no'].checked;

	if(c){
		document.f_a['shelf_no_from'].disabled = true;
		document.f_a['shelf_no_to'].disabled = true;
	}else{
        document.f_a['shelf_no_from'].disabled = false;
		document.f_a['shelf_no_to'].disabled = false;
	}
}

function change_sku_with(){
    var sku_with = document.f_a['sku_with'].value;

    if(sku_with=='sc'){
		$('span_location').show();
		$('span_shelf_no').show();
		load_location();
	}else{
		$('span_location').hide();
		$('span_shelf_no').hide();
	}
}

function change_sort_by(ele){
	if(ele.value=='')   $('span_sort_order').hide();
	else    $('span_sort_order').show();
}

function check_form(){
	var bid = document.f_a['branch_id'].value;

	if(bid==''){
		alert('Please Select Branch.');
        return false;
	}
	
	var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
	if((stock_take_type == 1 && document.f_a['date'].value=='') || (stock_take_type == 2 && document.f_a['pre_date'].value=='')){
		if(stock_take_type == 1) alert('Please Select Real Stock Take Date.');
		else alert('Please Select Pre Stock Take Date.');
        return false;
	}
	return true;
}

function show_dept(dept_id){
	document.f_a['dept_id'].value = dept_id;
	var ret = check_form();
	if(ret) document.f_a.submit();
}

function dept_changed(){
	var dept_id = document.f_a['dept_id'].value;
	
	if(dept_id>0)	$('span_group_by_sku').show();
	else	$('span_group_by_sku').hide();
}

function date_changed(){
	document.f_a['all_location'].checked = false;
	document.f_a['all_shelf_no'].checked = false;

	load_location();
}

function change_stock_take_type(clicked_sel){
	// check current clicked element name
	var t = clicked_sel.name=='pre_date' ? 2 : 1;
	
	// get the stock take radio list
	var chx = document.f_a['stock_take_type'];
	
	// loop and checked
	for(var i=0; i<chx.length; i++){
		if(chx[i].value==t){
			chx[i].checked = true;
			load_location();
			return;
		}
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
		<form method=post class=form name="f_a" onSubmit="return check_form();">

			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label">Branch</b> 
					<select class="form-control" name="branch_id" onchange="change_branch();">
					<option value="">-- Please Select --</option>
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
							{foreach from=$branch_group.header key=bgid item=r}
							<optgroup label="{$r.code}">
								{foreach from=$branch_group.items.$bgid key=bid item=b}
								<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
								{/foreach}
							</optgroup>
							{/foreach}
						{/if}
					</select>
				{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
				{/if}
				</div>
				
				<div class="col-md-4">
					<div class="form-label">
						<input type="radio" name="stock_take_type" value="1" {if $smarty.request.stock_take_type eq 1 || !$smarty.request.show_report}checked {/if} onclick="load_location();" />
				<b>&nbsp;Use Real Stock Take</b>
					</div>
				<span id="span_st_date">
					{include file='report.stock_take_variance.date_sel.tpl' date_list=$date}
				</span>
				</div>
				
				<div class="col-md-4">
					<div class="form-label">
						<input type="radio" name="stock_take_type" value="2" {if $smarty.request.stock_take_type eq 2}checked {/if} onclick="load_location();" />
				<b>&nbsp;Use Pre Stock Take</b>
				</div>
	
				<span id="span_pre_st_date">
					{include file='report.stock_take_variance.date_sel.tpl' date_list=$pre_date sel_name='pre_date'}
				</span>
				</div>
			</div>

			</p>
			
			<div class="row">
				
				<div class="col-md-4">
					<span>
						<b class="form-label">SKU with</b>
						<select class="form-control" name="sku_with" onChange="change_sku_with();">
							<option value="show_all_sku" {if $smarty.request.sku_with eq 'show_all_sku'}selected {/if}>Show All Item</option>
							<option value="sb" {if $smarty.request.sku_with eq 'sb' or !$smarty.request.sku_with}selected {/if}>Stock Take or Stock Balance</option>
							<option value="sc" {if $smarty.request.sku_with eq 'sc'}selected {/if}>Only Stock Take Item</option>
						</select>
						</span>
				</div>
				
				
					<span id="span_location" {if $smarty.request.sku_with ne 'sc'}style="display:none;"{/if}>
						<div class="col-md-4">
							{include file='report.stock_take_variance.location.tpl'}
						</div>
					</span>
				
				
				
					<span id="span_shelf_no" {if $smarty.request.sku_with ne 'sc'}style="display:none;"{/if}>
						<div class="col-md-4">
							{include file='report.stock_take_variance.shelf_no.tpl'}
						</div>
					</span>
				
			
				<div class="col-md-4">
					<span>
						<b class="form-label">Vendor</b>
						<select class="form-control" name="vendor_id">
							<option value="">-- All --</option>
							{foreach from=$vendor key=vendor_id item=r}
								<option value="{$vendor_id}" {if $smarty.request.vendor_id eq $vendor_id}selected {/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>
				</div>
				
				<div class="col-md-4">
					<span>
						<b class="form-label">Brand</b>
						<select class="form-control" name="brand_id">
							<option value="">-- All --</option>
							<option value="-1" {if $smarty.request.brand_id eq -1}selected {/if}>UN-BRANDED</option>
							{foreach from=$brands_list key=brand_id item=r}
								<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>
				</div>
		
				<div class="col-md-4">
					<span>
						<b class="form-label mt-2">Department</b>
						<select class="form-control" name="dept_id" onChange="dept_changed();">
							<option value="">-- All --</option>
						{foreach from=$departments item=r}
							<option value="{$r.id}" {if $smarty.request.dept_id eq $r.id}selected {/if}>{$r.description}</option>
						{/foreach}
						</select>
						</span>
				</div>
			
			<div class="col-md-4">
				<span>
					<b class="form-label mt-2">SKU Type</b>
					<select class="form-control" name="sku_type">
						<option value="">-- All --</option>
						{foreach from=$sku_type item=r}
							<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
						{/foreach}
					</select>
					</span>
			</div>
			
			
			<div class="col-md-4">
				
			<b class="form-label mt-2">Sort by</b>
			<select class="form-control" name="sort_by" onChange="change_sort_by(this);">
				<option value="">--</option>
				<option value="si.sku_item_code" {if $smarty.request.sort_by eq 'si.sku_item_code'}selected {/if}>ARMS Code</option>
				<option value="si.artno" {if $smarty.request.sort_by eq 'si.artno'}selected {/if}>Art No</option>
				<option value="si.mcode" {if $smarty.request.sort_by eq 'si.mcode'}selected {/if}>MCode</option>
				<option value="si.link_code" {if $smarty.request.sort_by eq 'si.link_code'}selected {/if}>{$config.link_code_name}</option>
				{*<option value="linkcode" {if $smarty.request.sort_by eq 'linkcode'}selected {/if}>CM Code</option>*}
				<option value="si.description" {if $smarty.request.sort_by eq 'si.description'}selected {/if}>Description</option>
				<option value="dept.description" {if $smarty.request.sort_by eq 'dept.description'}selected {/if}>Department</option>
			</select>
			<span id="span_sort_order" style="display:none;">
			<select class="form-control mt-1" name="sort_order">
				<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
				<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
			</select>
			</span>
			</div>
			
			
			</div>
			<input type=hidden name=subm value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			
			<div class="form-label form-inline">
				<span id="span_group_by_sku" style="{if !$smarty.request.dept_id}display:none;{/if}">
					<input type="checkbox" name="group_by_sku" id="inp_group_by_sku" {if $smarty.request.group_by_sku}checked {/if} value="1" /> 
					<label for="inp_group_by_sku"><b>Group by SKU</b></label>
				</span>
			</div>
			
			
			<div class="form-label form-inline">
				<input type="checkbox" name="skip_zero_variance" id="skip_zero_variance" {if $smarty.request.skip_zero_variance}checked {/if}> <b>&nbsp;Skip Zero Variance</b>
			&nbsp;&nbsp;
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			<span id="show_auto_fill_zero_item_span" {if $smarty.request.stock_take_type eq 2}style="display:none;"{/if}>&nbsp;&nbsp;
			<input type="checkbox" id="show_auto_fill_zero_item" name="show_auto_fill_zero_item" {if $smarty.request.stock_take_type eq 2}disabled{/if} value="1" {if $smarty.request.show_auto_fill_zero_item}checked {/if}> <b>&nbsp;Show auto fill zero item</b>
			</span>
			</div>
			</form>
	</div>
</div>

{literal}
<script>
	if(sort_by!='') $('span_sort_order').show();
</script>
{/literal}
{/if}

{if !$table}
{if $smarty.request.subm && !$err}-- No data --{/if}
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
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
				{assign var=cols value=2}
				{if $sessioninfo.privilege.SHOW_COST}{assign var=cols value=$cols+1}{/if}
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan=2 width="20">&nbsp;</th>
						{if $smarty.request.dept_id}
						  <th rowspan=2>ARMS Code</th>
						  <th rowspan=2>MCode</th>
						  <th rowspan=2>Art.No</th>
						  <th rowspan=2>{$config.link_code_name}</th>			
					  {/if}
					  <th rowspan=2>{if $smarty.request.dept_id}Description{else}Department{/if}</th>
					  {if $smarty.request.dept_id}<th rowspan=2>Selling Price</th>{/if}
					  <th {if $smarty.request.dept_id && $sessioninfo.privilege.SHOW_COST}colspan="3"{else}rowspan="2"{/if}>Stock Balance</th>
					  {if !$smarty.request.dept_id || !$sessioninfo.privilege.SHOW_COST}
						  <th rowspan=2>Stock Take Quantity</th>
						  {if $sessioninfo.privilege.SHOW_COST}
							  <th rowspan="2">Total Stock Take Cost</th>
						  {/if}
					  {else}
						  <th colspan="3">Stock Take</th>
					  {/if}
					  <th colspan="{$cols}" align=center>Stock Take Variance</th>
				  </tr>
				  <tr class="header">
					  {if $smarty.request.dept_id && $sessioninfo.privilege.SHOW_COST}
						  <th>Qty</th>
						  <th>Cost</th>
						  <th>Total Cost</th>
						  <th>Qty</th>
						  <th>Cost</th>
						  <th>Total Cost</th>
					  {/if}
					  <th>Qty (+/-)</th>
					  <th>Total<br />Selling Price Variance </th>
					  {if $sessioninfo.privilege.SHOW_COST}
						  <th>Total<br />Cost Variance</th>
					  {/if}
				  </tr>
				</thead>
				{foreach from=$table key=k item=r name=f}
					<tbody class="fs-08">
						<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
							<td>{$smarty.foreach.f.iteration}.</td>
							{if $smarty.request.dept_id}
								<td>{$sku_items.$k.sku_item_code}</td>
								<td>{$sku_items.$k.mcode}</td>
								<td>{$sku_items.$k.artno}</td>
								<td>{$sku_items.$k.link_code}</td>
								<td>{$sku_items.$k.description}</td>
							{else}
								<td>
									{if !$no_header_footer}
										<a href="javascript:void(show_dept('{$k}'));">
											{$departments.$k.description}
										</a>
									{else}
										{$departments.$k.description}
									{/if}
								</td>
							{/if}
				
							<!-- Selling Price -->
							{if $smarty.request.dept_id}
								<td class="r">{$r.selling|number_format:2}</td>
							{/if}
							
							<td class="r">{$r.stock_balance|qty_nf}</td>
							{if $smarty.request.dept_id && $sessioninfo.privilege.SHOW_COST}
								<td class="r">{$r.sb_cost|number_format:$config.global_cost_decimal_points}</td>				
								<td class="r">{$r.sb_total_cost|number_format:$config.global_cost_decimal_points}</td>				
							{/if}
							<td class="r">
								{if $r.got_sc}
									{$r.stock_take_qty|qty_nf}
								{else}
									<span style="color:red;">N/A</span>
								{/if}
							</td>
							
							<!-- Cost -->
							{if $smarty.request.dept_id and $sessioninfo.privilege.SHOW_COST}
								<td class="r">
									{if $r.got_sc}
										{$r.cost|number_format:$config.global_cost_decimal_points}
									{else}
										<span style="color:red;">N/A</span>
									{/if}						
								</td>
							{/if}
							
							<!-- Total Stock Take Cost -->
							{if $sessioninfo.privilege.SHOW_COST}
								<td class="r">
									{if $r.got_sc}
										{$r.sc_total_cost|number_format:$config.global_cost_decimal_points}
									{else}
										<span style="color:red;">N/A</span>
									{/if}
								</td>
							{/if}
							
							<!-- Variances -->
							<td class="r">
								{if $r.got_sc}
									<span class="{if $r.row_variances>0}positive{elseif $r.row_variances<0}negative{/if}">
										{if $r.row_variances>0}+{/if}{$r.row_variances|qty_nf}
									</span>
								{else}
									<span style="color:red;">N/A</span>
								{/if}
							</td>
							
							<!-- Selling Price Variance -->
							<td class="r">
								{if $r.got_sc}
									{$r.row_sp_variance|number_format:2}
								{else}
									<span style="color:red;">N/A</span>
								{/if}
							</td>
							
							{if $sessioninfo.privilege.SHOW_COST}
								<!-- Cost Variance -->
								<td class="r">
									{if $r.got_sc}
										{$r.row_cost_variance|number_format:$config.global_cost_decimal_points}
									{else}
										<span style="color:red;">N/A</span>
									{/if}
								</td>
							{/if}
						</tr>
					</tbody>
					
			
					
					{assign var=sku_id value=$sku_items.$k.sku_id}
					{foreach from=$child_data.$sku_id key=k2 item=r2}
						<tbody class="fs-08">
							<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
								<td></td>
								{if $smarty.request.dept_id}
									<td align="right">{$sku_items.$k2.sku_item_code}</td>
									<td>{$sku_items.$k2.mcode}</td>
									<td>{$sku_items.$k2.artno}</td>
									<td>{$sku_items.$k2.link_code}</td>
									<td>{$sku_items.$k2.description}</td>
								{else}
									<td>
										- <!-- should no come here -->
									</td>
								{/if}
								
								<!-- Selling Price -->
								{if $smarty.request.dept_id}
									<td class="r">{$r2.selling|number_format:2}</td>
								{/if}
								
								<td class="r">{$r2.stock_balance|qty_nf}</td>
								{if $smarty.request.dept_id && $sessioninfo.privilege.SHOW_COST}
									<td class="r">{$r2.sb_cost|number_format:$config.global_cost_decimal_points}</td>				
									<td class="r">{$r2.sb_total_cost|number_format:$config.global_cost_decimal_points}</td>				
								{/if}
								<td class="r">
									{if $r2.got_sc}
										{$r2.stock_take_qty|qty_nf}
									{else}
										<span style="color:red;">N/A</span>
									{/if}
								</td>
								
								<!-- Cost -->
								{if $smarty.request.dept_id and $sessioninfo.privilege.SHOW_COST}
									<td class="r">
										{if $r2.got_sc}
											{$r2.cost|number_format:$config.global_cost_decimal_points}
										{else}
											<span style="color:red;">N/A</span>
										{/if}
									</td>
								{/if}
								
								<!-- Total Stock Take Cost -->
								{if $sessioninfo.privilege.SHOW_COST}
									<td class="r">
										{if $r2.got_sc}
											{$r2.sc_total_cost|number_format:$config.global_cost_decimal_points}
										{else}
											<span style="color:red;">N/A</span>
										{/if}
									</td>
								{/if}
								
								<!-- Variances -->
								<td class="r">
									{if $r2.got_sc}
										<span class="{if $r2.row_variances>0}positive{elseif $r2.row_variances<0}negative{/if}">
											{if $r2.row_variances>0}+{/if}{$r2.row_variances|qty_nf}
										</span>
									{else}
										<span style="color:red;">N/A</span>
									{/if}
								</td>
								
								<!-- Selling Price Variance -->
								<td class="r">
									{if $r2.got_sc}
										{$r2.row_sp_variance|number_format:2}
									{else}
										<span style="color:red;">N/A</span>
									{/if}
								</td>
								
								{if $sessioninfo.privilege.SHOW_COST}
									<!-- Cost Variance -->
									<td class="r">
										{if $r2.got_sc}
											{$r2.row_cost_variance|number_format:$config.global_cost_decimal_points}
										{else}
											<span style="color:red;">N/A</span>
										{/if}
									</td>
								{/if}
							</tr>
						</tbody>
					{/foreach}
				{/foreach}
				<tr class="header">
					{if $smarty.request.dept_id}
						{assign var=colspan value=3}
					{else}
						{assign var=colspan value=2}
					{/if}
				
					{if $smarty.request.dept_id}{assign var=colspan value=$colspan+4}{/if}
					<th class="r" colspan="{$colspan}">Total</th>
					<td class="r">{$total.total_sb_qty|qty_nf}</td>
					{if $smarty.request.dept_id && $sessioninfo.privilege.SHOW_COST}
						<td>&nbsp;</td>
						<td class="r">{$total.total_sb_cost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
					<td class="r">
						{if $is_available}
							{$total.total_sc_qty|qty_nf}
						{else}
							<span style="color:red;">N/A</span>
						{/if}
					</td>
			
					{if $smarty.request.dept_id and $sessioninfo.privilege.SHOW_COST}
						<td class="r">-</td>
					{/if}
					{if $sessioninfo.privilege.SHOW_COST}
						<td class="r">
							{if $is_available}
								{$total.total_sc_cost|number_format:$config.global_cost_decimal_points}
							{else}
								<span style="color:red;">N/A</span>
							{/if}
						</td>
					{/if}
					
					<td class="r">
						{if $is_available}
							<span class="{if $total.total_variance>0}positive{elseif $total.total_variance<0}negative{/if}">
								{if $total.total_variance>0}+{/if}{$total.total_variance|qty_nf}
							</span>
						{else}
							<span style="color:red;">N/A</span>
						{/if}
					</td>
					
					<td class="r">
						{if $is_available}
							{$total.total_sp_variance|number_format:2}
						{else}
							<span style="color:red;">N/A</span>
						{/if}
					</td>
					{if $sessioninfo.privilege.SHOW_COST}
						<td class="r">
							{if $is_available}
								{$total.total_cost_variance|number_format:$config.global_cost_decimal_points}
							{else}
								<span style="color:red;">N/A</span>
							{/if}
						</td>
					{/if}
				</tr>
			</table>
		</div>
	</div>
</div>
{/if}

{include file='footer.tpl'}
