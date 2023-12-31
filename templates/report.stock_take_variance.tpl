{*
8/17/2009 11:31:02 AM Andy
- add pagination and cost

9/28/2009 3:52 PM Andy
- use new templates and hide group by sku checkbox

11/11/2009 10:42:56 AM Andy
- Change lable of column "Cost" to "Stock Take Cost"

12/16/2009 4:15:05 PM Andy
- add selection to let user choose sku condition

12/21/2009 1:36:06 PM Andy
- Show Shelf No "from and to" for user to choose if select "Only Stock Take Item"

4/8/2010 10:12:42 AM Andy
- Stock Take Variance Report show Department column if got config

4/15/2010 5:49:30 PM Andy
- Add sku type filter

11/19/2010 4:13:50 PM Andy
- Fix if no "show department config" will cause total row running alignment.

6/16/2011 3:56:13 PM Andy
- Add show decimal point for stock balance qty and variance qty.

6/24/2011 11:59:27 AM Andy
- Make report stock take qty can show decimal.

10/13/2011 5:17:21 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

12/14/2011 11:27:19 AM Andy
- Modify "Stock Take Variance Report" to allow branch access.

8/29/2012 5:47 PM Andy
- Fix group by sku bug.

9/4/2012 12:03 PM Justin
- Added new filter to have skip zero variance.

10/3/2012 3:30:00 PM Fithri
- stock take report can select item (Stock Take Variance Report)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

4/12/2017 9:00 AM Qiu Ying
- Enhanced to change "Group by SKU" to "Sort by SKU" and if stock take is N/A, should not calculate variance

10/1/2018 9:17 AM Justin
- Enhanced to have "Location" filter.

12/10/2018 3:10 PM Justin
- Enhanced report to have pre stock take feature.

3/15/2019 4:26 PM Andy
- Enhanced to have Brand, Vendor and Department filter.
- Rearrange the filter position.

7/29/2019 9:04 AM William
- Enhanced to have "Show auto fill zero item" filter.

8/9/2019 5:30 PM William
- Change Price Variance display number format.

06/30/2020 02:55 PM Sheila
- Updated button css.

12/03/2020 1:46 PM Rayleen
- Add Link/Old Code Column after Art No
- Put MCode after Arms Code
*}

{include file=header.tpl}
{if !$no_header_footer}
<style>
{literal}

{/literal}
</style>


<script type="text/javascript">
var sort_by = '{$smarty.request.sort_by}';
var phpself = '{$smarty.server.PHP_SELF}';
var use_default_page = true;

{literal}
function change_sort_by(ele){
	if(ele.value=='')   $('span_sort_order').hide();
	else    $('span_sort_order').show();
}

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

	if(use_default_page)    document.f_a['selected_page'].value = 0;
	passArrayToInput();
}

function page_change(){
	document.f_a['selected_page'].value = $('sel_selected_page').value;
	use_default_page = false;
	document.f_a['show_report'].click();
}
function toggle_group_sku(){
	var chx = document.f_a['group_by_sku'].checked;
	//document.f_a['sort_by'].disabled = chx;
	//document.f_a['sort_order'].disabled = chx;
}

function change_sku_with(){
    var sku_with = document.f_a['sku_with'].value;
	
	if(sku_with=='selected_sku') $('sku_items_autocomplete').show();
	else $('sku_items_autocomplete').hide();
    
    if(sku_with=='sc'){
		$('span_location').show();
		$('span_shelf_no').show();
		load_location();
	}else{
		$('span_location').hide();
		$('span_shelf_no').hide();
	}
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

function toggle_all_location(){
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
			<input type=hidden name=report_title value="{$report_title}">
			<input type=hidden name=title value="Stock Take Variance Report">
			<input type="hidden" name="selected_page" value="{$smarty.request.selected_page}" />
			<p>

			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> <select class="form-control" name="branch_id" onChange="change_branch();">
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
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
				{/if}
				</div>
	
				<div class="col-md-4">
					<div class="form-label form-inline">
						<input type="radio" name="stock_take_type" value="1" {if $smarty.request.stock_take_type eq 1 || !$smarty.request.show_report}checked {/if} onclick="load_location();" />
				<b>&nbsp;Use Real Stock Take</b>
					</div>
				<span id="span_st_date">
					{include file='report.stock_take_variance.date_sel.tpl' date_list=$date}
				</span>
				</div>
				
				<div class="col-md-4">
				<div class="form-label form-inline">
					<input type="radio" name="stock_take_type" value="2" {if $smarty.request.stock_take_type eq 2}checked {/if} onclick="load_location();" />
					<b >&nbsp;Use Pre Stock Take</b>
				</div>
				<span id="span_pre_st_date">
					{include file='report.stock_take_variance.date_sel.tpl' date_list=$pre_date sel_name='pre_date'}
				</span>
				</div>
			</div>

			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label mt-2">SKU with</b>
			<select class="form-control" name="sku_with" onChange="change_sku_with();">
				<option value="show_all_sku" {if $smarty.request.sku_with eq 'show_all_sku'}selected {/if}>Show All Item</option>
				<option value="sb" {if $smarty.request.sku_with eq 'sb' or !$smarty.request.sku_with}selected {/if}>Stock Take or Stock Balance</option>
				<option value="sc" {if $smarty.request.sku_with eq 'sc'}selected {/if}>Only Stock Take Item</option>
				<option value="selected_sku" {if $smarty.request.sku_with eq 'selected_sku'}selected {/if}>Select SKU..</option>
			</select>
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
			</p>
			
			<div id="sku_items_autocomplete" {if $smarty.request.sku_with ne 'selected_sku'}style="display:none;"{/if}>
				<div class="col-md-4">
					{include file="sku_items_autocomplete_multiple.tpl"}
				</div>
			</div>
			
				<div class="col-md-4">
					<span>
						<b class="form-label mt-2">Vendor</b>
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
						<b class="form-label mt-2">Brand</b>
						<select class="form-control" name="brand_id">
							<option value="">-- All --</option>
							<option value="-1" {if $smarty.request.brand_id eq -1}selected {/if}>UN-BRANDED</option>
							{foreach from=$brands_list key=brand_id item=r}
								<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>
				</div>
			
			</div>
			
			<p>
				<div class="row">
					<div class="col">
						<span>
							<b class="form-label">Department</b>
							<select class="form-control" name="dept_id">
								<option value="">-- All --</option>
								{foreach from=$departments item=r}
									<option value="{$r.id}" {if $smarty.request.dept_id eq $r.id}selected {/if}>{$r.description}</option>
								{/foreach}
							</select>
						</span>
					</div>
					
					<div class="col">
						<span>
							<b class="form-label">SKU Type</b>
							<select class="form-control" name="sku_type">
								<option value="">-- All --</option>
								{foreach from=$sku_type item=r}
									<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
								{/foreach}
							</select>
						</span>
					</div>
				
					<div class="col">
						<span>
							<b class="form-label">Sort by</b>
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
							<select class="form-control" name="sort_order">
								<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
								<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
							</select>
							</span>
						</span>
					</div>
				</div>
			</p>
			
			
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			
			<span class="form-label mt-2" >
			<input type="checkbox" name="group_by_sku" id="group_by_sku_id" {if $smarty.request.group_by_sku}checked {/if} onChange="toggle_group_sku();"> <label for="group_by_sku_id"><b>&nbsp;Group by SKU</b></label>
			
			<input type="checkbox" name="skip_zero_variance" id="skip_zero_variance" {if $smarty.request.skip_zero_variance}checked {/if}> <b>&nbsp;Skip Zero Variance</b>
			
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</span>
			<span class="form-label" id="show_auto_fill_zero_item_span" {if $smarty.request.stock_take_type eq 2}style="display:none;"{/if}>
			<input type="checkbox" id="show_auto_fill_zero_item" name="show_auto_fill_zero_item" {if $smarty.request.stock_take_type eq 2}disabled{/if} value="1" {if $smarty.request.show_auto_fill_zero_item}checked{/if} /><b>&nbsp;Show auto fill zero item</b>
			</span>
			
			{*<input type="checkbox" name="show_all_sku" {if $smarty.request.show_all_sku}checked {/if}> Show All SKU*}
			
		</form>
	</div>
</div>
{/if}

{if !$no_header_footer}
{literal}

<script>
	if(sort_by!='') $('span_sort_order').show();
	toggle_group_sku();
</script>
{/literal}
{/if}

{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
	{*Branch: {$branches[$smarty.request.branch_id].code|default:$branch_name} 
	Date: {$smarty.request.selected_date}*}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		{if !$no_header_footer}
	{if $total_pages>1}
		<p><b class="form-label">Page</b>
		<select class="form-control" id="sel_selected_page" onChange="page_change();">
		    {section loop=$total_pages name=s}
				<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.selected_page eq ($smarty.section.s.iteration-1)}selected {/if}>{$smarty.section.s.iteration}</option>
		    {/section}
		</select></p>
	{/if}
{/if}
{if $sessioninfo.privilege.SHOW_COST}
{assign var=cspan value=5}
{else}
{assign var=cspan value=3}
{/if}
<div class="table-responsive">
	<table width="100%" class="report_table">
		<thead class="bg-gray-100">
			<tr class="header">
				<th rowspan=2>&nbsp;</th>
				  <th rowspan=2>ARMS Code</th>
				  <th rowspan=2>MCode</th>
				  <th rowspan=2>Art.No</th>
				  <th rowspan=2>{$config.link_code_name}</th>
				  {if $config.stock_take_variance_report_show_dept}
					  <th rowspan=2>Dept</th>
				  {/if}
				  <th rowspan=2>Description</th>
				  <th rowspan=2>Stock Balance</th>
				  <th rowspan=2>Stock Take Quantity</th>
			  <th colspan={$cspan} align=center>Variance</th>
			  </tr>
			  <tr class="header">
				  <th>(+/-)</th>
				  <th>Selling Price</th>
				  <th>Price Variance </th>
				  {if $sessioninfo.privilege.SHOW_COST}
					  <th>Stock Take Cost</th>
					  <th>Total Cost</th>
				  {/if}
			  </tr>
		</thead>
		{if $smarty.request.group_by_sku}
			{foreach from=$group_data key=sku_id item=pc}
				<div class="tbody fs-08">
					<tr>
						{assign var=item_counter value=$item_counter+1}
						{assign var=sid value=$pc.parent}
						<td>{$item_counter}</td>
						{*<td align="left" width="150">{$pc.parent.sku_item_code}</td>
						<td>{$pc.parent.mcode|default:'-'}</td>
						<td>{$pc.parent.artno|default:'-'}</td>
						<td>{$pc.parent.link_code|default:'-'}l</td>
						<td>{$pc.parent.description|default:'-'}</td>*}
						<td>{$sku_items_info.$sid.sku_item_code|default:'-'}</td>
						<td>{$sku_items_info.$sid.mcode|default:'-'}</td>
						<td>{$sku_items_info.$sid.artno|default:'-'}</td>
						<td>{$sku_items_info.$sid.link_code|default:'-'}</td>
						{if $config.stock_take_variance_report_show_dept}
							<td>{$sku_items_info.$sid.dept_desc|default:'-'}</td>
						{/if}
						<td>{$sku_items_info.$sid.description|default:'-'}</td>
						<td class="r">{$table2.$sid.stock_balance|qty_nf}</td>
						<td class="r">
						{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
						{$table2.$sid.stock_take_qty|qty_nf}{/if}</td>
						<td class="r">
						{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
						{if $table2.$sid.variance>0}+{/if}{$table2.$sid.variance|qty_nf}{/if}
						</td>
						<td class="r">
						{if !$table2.$sid.selling_price && $smarty.request.show_auto_fill_zero_item}
							<span style="color:red;">N/A</span>
						{else}
							{$table2.$sid.selling_price|number_format:2}
						{/if}
						</td>
						<td class="r">
						{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
						{$table2.$sid.price_variance|number_format:2}{/if}</td>
						{if $sessioninfo.privilege.SHOW_COST}
							{if !$table2.$sid.sc_date}
								<td class="r"><span style="color:red;">N/A</span></td>
								<td class="r"><span style="color:red;">N/A</span></td>
							{else}
								<td class="r">{$table2.$sid.cost|number_format:$config.global_cost_decimal_points}</td>
								<td class="r">{$table2.$sid.total_cost|number_format:$config.global_cost_decimal_points}</td>
							{/if}
						{/if}
					</tr>
				</div>
				{foreach from=$pc.child item=sid}
					<div class="tbody fs-08">
						<tr>
							{*<td class="r" colspan="2">{$r.sku_item_code}</td>
							<td>{$r.mcode|default:'-'}</td>
							<td>{$r.artno|default:'-'}</td>
							<td>{$r.link_code|default:'-'}</td>
							<td>{$r.description|default:'-'}</td>*}
							<td class="r" colspan="2">{$sku_items_info.$sid.sku_item_code|default:'-'}</td>
							<td>{$sku_items_info.$sid.mcode|default:'-'}</td>
							<td>{$sku_items_info.$sid.artno|default:'-'}</td>
							<td>{$sku_items_info.$sid.link_code|default:'-'}</td>
							{if $config.stock_take_variance_report_show_dept}
								<td>{$sku_items_info.$sid.dept_desc|default:'-'}</td>
							{/if}
							<td>{$sku_items_info.$sid.description|default:'-'}</td>
							<td class="r">{$table2.$sid.stock_balance|qty_nf}</td>
							<td class="r">
							{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
							{$table2.$sid.stock_take_qty|qty_nf}{/if}</td>
							<td class="r">
							{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
							{if $table2.$sid.variance>0}+{/if}{$table2.$sid.variance|qty_nf}{/if}
							</td>
							<td class="r">{$table2.$sid.selling_price|number_format:2}</td>
							<td class="r">
							{if !$table2.$sid.sc_date}<span style="color:red;">N/A</span>{else}
							{$table2.$sid.price_variance|number_format:2}{/if}
							</td>
							{if $sessioninfo.privilege.SHOW_COST}
								{if !$table2.$sid.sc_date}
									<td class="r"><span style="color:red;">N/A</span></td>
									<td class="r"><span style="color:red;">N/A</span></td>
								{else}
									<td class="r">{$table2.$sid.cost|number_format:$config.global_cost_decimal_points}</td>
									<td class="r">{$table2.$sid.total_cost|number_format:$config.global_cost_decimal_points}</td>
								{/if}
							{/if}
						</tr>
					</div>
				{/foreach}
			{/foreach}
		{else}
			{foreach from=$table item=r name=i}
				{assign var=item_counter value=$item_counter+1}
				{assign var=sid value=$r.sku_item_id}
				<div class="tbody fs-08">
					<tr>
						<td>{$item_counter}</td>
						{*<td>{$r.sku_item_code|default:'-'}</td>
						<td>{$r.mcode|default:'-'}</td>
						<td>{$r.artno|default:'-'}</td>
						<td>{$r.link_code|default:'-'}</td>
						<td>{$r.description|default:'-'}</td>*}
						<td>{$sku_items_info.$sid.sku_item_code|default:'-'}</td>
						<td>{$sku_items_info.$sid.mcode|default:'-'}</td>
						<td>{$sku_items_info.$sid.artno|default:'-'}</td>
						<td>{$sku_items_info.$sid.link_code|default:'-'}</td>
						{if $config.stock_take_variance_report_show_dept}
							<td>{$sku_items_info.$sid.dept_desc|default:'-'}</td>
						{/if}
						<td>{$sku_items_info.$sid.description|default:'-'}</td>
						<td class="r">{$r.stock_balance|qty_nf}</td>
						<td class="r">{if !$r.sc_date}<span style="color:red;">N/A</span>{else}
						{$r.stock_take_qty|qty_nf}{/if}</td>
						<td class="r">{if !$r.sc_date}<span style="color:red;">N/A</span>{else}
						{if $r.variance>0}+{/if}{$r.variance|qty_nf}{/if}</td>
						<td class="r">{$r.selling_price|number_format:2}</td>
						<td class="r">{if !$r.sc_date}<span style="color:red;">N/A</span>{else}
						{$r.price_variance|number_format:2}{/if}</td>
						{if $sessioninfo.privilege.SHOW_COST}
							{if !$r.sc_date}
								<td class="r"><span style="color:red;">N/A</span></td>
								<td class="r"><span style="color:red;">N/A</span></td>
							{else}
								<td class="r">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
								<td class="r">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
							{/if}
						{/if}
					</tr>
				</div>
			{/foreach}
		{/if}
		<tr class="header">
			{assign var=colspan value=6}
			{if $config.stock_take_variance_report_show_dept}{assign var=colspan value=$colspan+1}{/if}
			<th class="r" colspan="{$colspan}">Total</th>
			<td class="r">{$total.balance|qty_nf}</td>
			<td class="r">
				{if $is_available}
					{$total.stock_take_qty|qty_nf}
				{else}
					<span style="color:red;">N/A</span>
				{/if}
			</td>
			<td class="r">
				{if $is_available}
					{if $total.variance>0}+{/if}{$total.variance|qty_nf}
				{else}
					<span style="color:red;">N/A</span>
				{/if}
			</td>
			<td class="r">-</td>
			<td class="r">
				{if $is_available}
					{$total.price_variance|number_format:2}
				{else}
					<span style="color:red;">N/A</span>
				{/if}
			</td>
			{if $sessioninfo.privilege.SHOW_COST}
			<td class="r">-</td>
			<td class="r">
				{if $is_available}
					{$total.total_cost|number_format:$config.global_cost_decimal_points}
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

{if !$no_header_footer}
{literal}
<script type="text/javascript">
	reset_sku_autocomplete();
</script>
{/literal}
{/if}

{include file=footer.tpl}
