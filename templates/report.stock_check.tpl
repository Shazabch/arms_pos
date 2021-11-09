{*
11/24/2009 3:57:51 PM Andy
- Fix if login at branch no auto load date selection

7/23/2010 12:16:57 PM Andy
- Change shelf no dropdown to lod based on branch and date selection, not always load all.

5/9/2012 2:34:43 PM Justin
- Added new filter "Group by Item".
- Added new JS function to toggle on/off for Group by Item filter while view type is equal to Detail.

10/1/2012 11:38 AM Justin
- Enhanced to have active filter.

10/23/2012 9:53:00 AM Fithri
- add filter to search by SKU

10/29/2012 4:05:00 PM Fithri
- add "All" option/checkbox for shelf from/to

9/27/2018 4:58 PM Justin
- Enhanced to add "Location" filter.

3/20/2019 9:22 AM Andy
- Reconstruct Stock Take Summary Report to use Module framework.
- Enhanced to have Brand, Vendor and Department filter.

5/9/2019 5:43 PM William
- Bug fixed on detail table broken.
- Enhanced single select department filter to multi section and Clear selected button.
- Add select all checkbox by department.

06/30/2020 02:55 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

{literal}
<style>
.div_department_list {
	width:300px;
	height:100px;
	overflow-y:auto;
	border:1px solid black;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function load_date(id)
{
	$('sel_bid').disable();
	document.f_a['all_shelf_no'].checked = false;
	toggle_all_shelf_no();
	var all_btn = $$('#f_a .btn_submit');
	for(var i=0; i<all_btn.length; i++){
        $(all_btn[i]).disabled = true;
	}
	$('div_ajax').update(_loading_);
	
    new Ajax.Updater('div_ajax', phpself, {
		parameters: 'a=ajax_load&branch_id='+id+'&ajax=1',
		onComplete: function(){
            $('sel_bid').enable();
            for(var i=0; i<all_btn.length; i++){
		        $(all_btn[i]).disabled = false;
			}
			
            load_location();
		}
    });
	document.f_a['location_from'].length = 1;
    document.f_a['location_to'].length = 1;
	
    document.f_a['shelf_no_from'].length = 1;
    document.f_a['shelf_no_to'].length = 1;
}

function date_changed(){
	document.f_a['all_location'].checked = false;
	document.f_a['all_shelf_no'].checked = false;

	load_location();
}

function load_location(){
	var bid = document.f_a['branch_id'].value;
	var date = document.f_a['date'].value;

	$('span_location').update(_loading_);
	new Ajax.Updater('span_location', phpself, {
		parameters:{
			'a': 'load_location',
			'ajax': 1,
			'branch_id': bid,
			'date': date
		},
		onComplete: function(){
			load_shelf_no();
		}
	});
}

function load_shelf_no(){
	var bid = document.f_a['branch_id'].value;
	var date = document.f_a['date'].value;
	var all_location = 0;
	if(document.f_a['all_location'].checked == true) all_location = 1;
	var location_from = document.f_a['location_from'].value;
	var location_to = document.f_a['location_to'].value;

	$('span_shelf_no').update(_loading_);
	new Ajax.Updater('span_shelf_no', phpself, {
		parameters:{
			'a': 'load_shelf_no',
			'ajax': 1,
			'branch_id': bid,
			'date': date,
			'all_location': all_location,
			'location_from': location_from,
			'location_to': location_to
		},
		onComplete: function(){
			//toggle_all_shelf_no();
		}
	});
}
//Select all department by checkbox 
function departmentselect_all(){
	var div_department_list = $('div_department_list');
	var inp_clear_department = $('inp_clear_department');
	var checkbox_department = $('checkbox_department');
	var check_department_list = $$('.check_department_list');
	var count_check_department_list = document.f_a['check_department_list[]'];
	var inp_department = $('inp_department');
	
	var is_department_checked = inp_department.value ==1 ? false:true;
	if(is_department_checked){
		//show department section
		checkbox_department.checked = false;
		$(div_department_list).show();
		$(inp_clear_department).show();
		inp_department.value=1;

	}else{
		//hide department section
		checkbox_department.checked = true;
		$(div_department_list).hide();
		$(inp_clear_department).hide();
		for(var i = 0; i < count_check_department_list.length; i++){
			
			check_department_list[i].checked = false;
		}
		inp_department.value='';
	}
}

// Clear department selection filter selected
function clear_department_from_list(){
	var check_department_list = $$('.check_department_list');
	var count_check_department_list = document.f_a['check_department_list[]'];
	var count_checked= 0;
	for(var i = 0; i < count_check_department_list.length; i++){
		if(check_department_list[i].checked == true){
			count_checked++;
		}
	}
	if(count_checked< 1){
		alert('Please select a department item from the list');
	}
	//Clear all selected of department selection filter
    for(var i = 0; i < check_department_list.length; i++){
		check_department_list[i].checked = false;
    }
}
//check filter before submit
function check_form(){
	var sel_bid_exist = typeof document.f_a['sel_bid']!=='undefined'?false:true;
	if(!sel_bid_exist){
		var sel_bid = document.f_a['sel_bid'].value;
		if(sel_bid == ''){
			alert('Please Select Branch.');
			return false;
		}
	}
	var date = document.f_a['date'].value;
	if(date==''){
		alert('Please Select Date.');
        return false;
	}
	var inp_department = $('inp_department');
	var is_department_checked = inp_department.value ==1 ? false:true;
	
	var check_department_list = $$('.check_department_list');
	var count_check_department_list = document.f_a['check_department_list[]'];
	var count_checked= 0;
	for(var i = 0; i < count_check_department_list.length; i++){
		if(check_department_list[i].checked == true){
			count_checked++;
		}
	}
	if(!is_department_checked&&count_checked < 1){
		alert('Please Select Department.');
        return false;
	}
}
function toggle_group_by_item(obj){
	if(obj.value == "summary"){
		document.f_a['group_by_item'].disabled = true;
		$('span_gbi').hide();
	}else{
		document.f_a['group_by_item'].disabled = false;
		$('span_gbi').show();
	}
}

function toggle_all_location() {
	if (document.f_a['all_location'].checked) {
		document.f_a['location_from'].disabled = true;
		document.f_a['location_to'].disabled = true;
	}
	else {
		document.f_a['location_from'].disabled = false;
		document.f_a['location_to'].disabled = false;
	}
}

function toggle_all_shelf_no() {
	if (document.f_a['all_shelf_no'].checked) {
		document.f_a['shelf_no_from'].disabled = true;
		document.f_a['shelf_no_to'].disabled = true;
	}
	else {
		document.f_a['shelf_no_from'].disabled = false;
		document.f_a['shelf_no_to'].disabled = false;
	}
}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
{if $err}
	<div class="alert alert-danger mx-3 rounded">
		<ul style="color:red;">
			{foreach from=$err item=e}
				<li>{$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<div class=stdframe >
			<form name="f_a" id="f_a" method="post" onSubmit="return check_form();">
			<input type=hidden name=report_title value="{$report_title}">
			<input type=hidden name=title value="Stock Check Report (Summary)">
			<p>
			
			<div class="row">
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id" onchange="load_date(this.value)" id="sel_bid">
					<option value="">-- Please Select --</option>
					{foreach from=$branch_list key=bid item=b}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
					{/foreach}
					</select>
				{else}<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
				{/if}
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Type</b>
				<select class="form-control" name="type" onchange="toggle_group_by_item(this);">
				<option value="summary" {if $smarty.request.type eq 'summary'}Selected{/if}>Summary</option>
				<option value="detail" {if $smarty.request.type eq 'detail'}selected{/if}>Detail</option>
				</select>
				</div>
				
				<span id="span_gbi" {if !$smarty.request.type || $smarty.request.type eq 'summary'}style="display:none;"{/if}>
					<div class="col-md-3">
						<div class="form-label mt-2">
							<input type="checkbox" name="group_by_item" value="1" {if $smarty.request.group_by_item}checked{/if} /> <b>&nbsp;Group By Item</b>&nbsp;
						</div>
					</div>
				</span>
				
				<div class="col-md-3">
					<span id="div_ajax">
						<b class="form-label">Date</b>
						<select class="form-control" name="date" id='sel_date' onChange='date_changed();'>
						<option value="">-- Please Select --</option>
						{section loop=$date name=i}
						<option value="{$date[i].date}" {if $smarty.request.date eq $date[i].date}selected{/if}>{$date[i].date}</option>
						{/section}
						</select>
					</span>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Status</b>
				<select class="form-control" name="active">
					<option value="" {if !$smarty.request.active}selected{/if}>All</option>
					<option value="1" {if $smarty.request.active eq 1}selected{/if}>Active</option>
					<option value="2" {if $smarty.request.active eq 2}selected{/if}>Inactive</option>
				</select>
				</div>
			</div>
			
			</p>
			
			
		<div class="row">
			<span id="span_location">
				<div class="col">
					{include file='report.stock_take_variance.location.tpl'}
				</div>
			</span>
			<span id="span_shelf_no">
				<div class="col">
					{include file='report.stock_take_variance.shelf_no.tpl'}
				</div>
			</span>
				<div class="col">
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
				
				<div class="col">
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
		</div>
			
			<p>
				<table>
					<tr>
						<td><b style="float:left;" class="form-label">Department</b></td>
						<td><input class="form-control" name='inp_department' type='hidden' id='inp_department' value="{$smarty.request.inp_department}" />&nbsp;<input type='checkbox' id="checkbox_department" name='all_department' {if $smarty.request.inp_department eq '' } checked{/if} onchange="departmentselect_all()" ><b>&nbsp;All</b></td>
					</tr>
					<tr>
						<td></td>
						<td><div class="div_department_list" id='div_department_list' {if $smarty.request.inp_department eq ''}style="display:none;padding:10px;"{/if}>
						{foreach from=$departments item=r}
							<span id='span_check_department' style='width:100%;float:left;'>
								<input value="{$r.id}" class='check_department_list' name="check_department_list[]" type='checkbox' {if $r.id|in_array:$smarty.request.check_department_list}checked{/if} />{$r.description}</span>
						{/foreach}
						</div></td>
					</tr>
					<tr>
						<td></td>
						<td><input type=button class="btn btn-primary" value="Clear" id="inp_clear_department"  onClick='clear_department_from_list();' {if $smarty.request.inp_department eq '' }style="display:none;"{/if} style="width:100px;"></td>
					</tr>
				</table>
			</p>
			
			<p>
				<span>
					<b class="form-label">Group Type</b>
					<select class="form-control" name="group_type">
						<option value="bydepartment" {if $smarty.request.group_type eq 'bydepartment'}selected{/if}>By Department</option>
						<option value="byshelf" {if $smarty.request.group_type eq 'byshelf'}selected{/if}>By Shelf </option>
					</select>
				</span>
				
			</p>
			
			<div id="sku_items_autocomplete">
			{include file="sku_items_autocomplete_multiple.tpl"}
			</div>
			
			<br />
			<div class="alert alert-primary rounded" style="max-width: 500px;">
				* This report only show item with Stock Take Quantity more than zero.
			</div>
			
			<p>
				<input type=hidden name=submit value=1>
				<button class="btn btn-primary" name=show_report onclick="passArrayToInput();" class="btn_submit">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info" name=output_excel class="btn_submit">{#OUTPUT_EXCEL#}</button>
				{/if}
			</p>
			
			</form>
			</div>
	</div>
</div>
{/if}
<br>
<script>
toggle_all_location();
toggle_all_shelf_no();
reset_sku_autocomplete();
</script>
{if !$table && $smarty.request.submit}
-- No Data --
{elseif !$table}

{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	
	{if $smarty.request.type eq 'summary'}
		{include file=report.stock_check.summary.tpl}
	{else}
		{include file=report.stock_check.detail.tpl}
	{/if}
{/if}

{include file=footer.tpl}
