{*
11/18/2014 10:22 AM Fithri
- Enhanced to allow user can use last GRN without select vendor.

7/13/2015 5:58 PM Andy
- Remove additional selling price column.

07/01/2016 14:00 Edwin
- Show 'HQ Cost' when branch is HQ and $config.sku_listing_show_hq_cost is enabled

8/9/2017 10:21 AM Qiu Ying
- Enhanced to add sales value at opening and closing balance
- Enhanced to add category level filter in category autocomplete

10/10/2017 3:10 PM Justin
- Enhanced to sum up closing balance from accumulated cost (GRA, GRN, Adjustment and etc) when config is turned on.

10/16/2017 3:26 PM Justin
- Enhanced to all columns that showing both qty and value become showing either Qty or Cost base on "Show by Qty" or "Show by Cost" button.

10/26/2017 4:36 PM Justin
- Enhanced to show cost decimal points base on config set.

3/12/2018 6:06 PM HockLee
- Added filter by Input Tax and Output Tax.
- Show up if enable_gst is on.

7/5/2019 9:40 AM William
- Added new "Day Turnover" column to stock balance report.

12/18/2019 9:14 AM William
- Enhanced to disabled filter "Use GRN" when vendor select all.

5/27/2020 5:40 PM William
- Show '-' when the cost is 0 value.

06/30/2020 02:42 PM Sheila
- Updated button css.

8/3/2020 10:17 AM William
- Enhanced to show red color text for negative value.
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
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.got_sc{
	color:red;
}
.not_up_to_date{
	color:green;
}
.red{
	color:red;
}

</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var requested_info = {literal}{{/literal}
	from: '{$smarty.request.from}',
	to: '{$smarty.request.to}',
	branch_id: '{$smarty.request.branch_id}',
	vendor_id: '{$smarty.request.vendor_id}',
	sku_type: '{$smarty.request.sku_type}',
	sort_by: '{$smarty.request.sort_by}',
	show_by: '{$smarty.request.show_by}',
	order_by: '{$smarty.request.order_by}',
	hq_cost: '{$smarty.request.hq_cost}',
	blocked_po: '{$smarty.request.blocked_po}',
	status: '{$smarty.request.status}',
	got_opening_sc: '{$got_opening_sc}',
	got_range_sc: '{$got_range_sc}',
	type: '{$smarty.request.type}',
	input_tax: '{$smarty.request.input_tax}',
	output_tax: '{$smarty.request.output_tax}'
{literal}}{/literal}
{literal}
//var category_autocompleter = null;

function do_print(){
	window.print();
}

/*function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}
function init_autocomplete()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=0", {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		document.f_d.category_id.value = s[0];
		sel_category(obj,s[1]);
	}});
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	document.f_d.category_tree.value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}

function reset_autocomplete(){
    category_autocompleter.options.defaultParams = '';
}

function toggle_all_category(ele){
	if(ele.checked){
		$('sel_cat').hide();
		document.f_d['category_id'].value = '';
		document.f_d['category'].value = '';
	}else{
        $('sel_cat').show();
	}
}
*/
function check_show_by(){
	var v = document.f_d['vendor_id'].value;
	if(v==''){
		document.f_d['show_by'].disabled = false;
		//$('use_grn').checked=false;
		//$('use_grn').disabled=true;
	}else{
        document.f_d['show_by'].disabled = true;
        document.f_d['show_by'].value = 'cat';
        //$('use_grn').disabled=false;
	}
	
	check_use_grn();
}

function toggle_category_child(k,ele){
	if(ele.src.indexOf('clock')>0){
		alert('Loading... Please wait.');
		return;
	}
	
	var parent_tr = $(ele).parentNode.parentNode;
	var tree_lv = int($(parent_tr).readAttribute('alt'));
	var parent_tr_id = $(parent_tr).readAttribute('id');
	var child_not_yet_load = true;
	
	var all_child_tr = $$('#tbl_report tr[alt="'+(tree_lv+1)+'"]');
	
	if(ele.src.indexOf('collapse')>0){  // toggle collapse
        close_sub(tree_lv,parent_tr_id);
		ele.src = '/ui/expand.gif';
		return;
	}else{  // toggle show
        for(var i=0; i<all_child_tr.length; i++){
			if(all_child_tr[i].id.indexOf(parent_tr_id)==0){
				$(all_child_tr[i]).show();
				child_not_yet_load = false;
			}
		}
	}
	
	if(!child_not_yet_load){
		ele.src = '/ui/collapse.gif';
		return;
	}
	
	ele.src = '/ui/clock.gif';

	var tr_ele=ele.parentNode.parentNode;
	var bg_color=tr_ele.getAttribute("bgcolor").substr(1,7);

	//use grn
	if ($('use_grn').checked) var use_grn = 1;
	else    var use_grn = 0;

	new Ajax.Request(phpself+'?a=show_report&ajax=1&category_id='+k+'&tree_lv='+tree_lv+'&use_grn='+use_grn+'&bgcolor='+bg_color,{
		parameters: requested_info,
		onComplete:function(e){
			new Insertion.After(parent_tr, e.responseText);
			ele.src = '/ui/collapse.gif';
		}
	});
}

function close_sub(tree_lv,parent_tr_id){
    var all_child_tr = $$('#tbl_report tr[alt="'+(tree_lv+1)+'"]');
    for(var i=0; i<all_child_tr.length; i++){
		if(all_child_tr[i].id.indexOf(parent_tr_id)==0){
			$(all_child_tr[i]).hide();
			$(all_child_tr[i]).getElementsBySelector('img.img_expand').each(function(ele,s){
				ele.src = '/ui/expand.gif';
			});
		}
	}
	if(all_child_tr.length>0)   close_sub(tree_lv+1, parent_tr_id);
}

function load_sku(k,ele){
	if(requested_info['branch_id']<0){
		alert('Stock Balance Report not support branch group sku.');
		return;
	}
	
	if ($('use_grn').checked) var use_grn = 1;
	else    var use_grn = 0;
	
	window.open('report.stock_balance.php?a=show_report&'+$H(requested_info).toQueryString()+'&category_id='+k+'&use_grn='+use_grn);
}

function change_sort_by_select() {
	var sel_show_by = document.f_d['show_by'].value;
	
	$('opt_sort_by_cname').hide();
	$('opt_sort_by_vcode').hide();
	$('opt_sort_by_vname').hide();
	$('opt_sort_by_bcode').hide();
	
	if (sel_show_by == 'cat') {
		$('opt_sort_by_cname').show();
	}
	else if (sel_show_by == 'vendor') {
		$('opt_sort_by_vcode').show();
		$('opt_sort_by_vname').show();
	}
	else {
		$('opt_sort_by_bcode').show();
	}
	
	var fixedSortBy=['sb_from','sb_from_val','sb_to','sb_to_val'];
	if (in_array(document.f_d['sort_by'].value,fixedSortBy)) no_reset = true;
	else no_reset = false;
	
	if (!no_reset) {
		if (document.f_d['show_by'].value == 'cat' && document.f_d['sort_by'].value != 'cname') {$('opt_reset_sort_by').selected = true;}
		else if (document.f_d['show_by'].value == 'vendor' && (document.f_d['sort_by'].value != 'vcode' && document.f_d['sort_by'].value !='vname')) {$('opt_reset_sort_by').selected = true;}
		else if (document.f_d['show_by'].value == 'branch' && document.f_d['sort_by'].value != 'bcode') {$('opt_reset_sort_by').selected = true;}
	}
	
	change_sort_by();
}

function change_sort_by(){
	var s = document.f_d['sort_by'].value;
	if(s=='')   $('span_order_by').hide();
	else    $('span_order_by').show();
}

function check_use_grn(){
	var allow_use_grn = true;
	
	if(document.f_d['branch_id']){
		if(!document.f_d['branch_id'].value || document.f_d['branch_id'].value<0)	{ //all branch selected
			//allow_use_grn = false;
			$('show_by_branch').show();
		}
		else { // a branch is selected
			if (document.f_d['show_by'].value == 'branch') $('show_by_cat').selected = true;
			$('show_by_branch').hide();
		}
	}
	change_sort_by_select();
	
	if(!document.f_d['vendor_id'].value)	allow_use_grn = false;
	
	if(allow_use_grn){
		$('use_grn').disabled=false;
	}else{
		$('use_grn').checked=false;
		$('use_grn').disabled=true;
	}
	
}

function do_submit(mode){
	document.f_d.type.value=mode;
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
		<ul style="color:red;">
			{foreach from=$err item=e}
				<li>{$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}
<div class="card mx-3">
	<div class="card-body">
		{if !$no_header_footer}

<div class="noprint stdframe" >
<form name="f_d">
	<input type="hidden" name="a" value="show_report" />
	<input type="hidden" name="type" value="{$form.type}">
	<div class="row">
		{if $BRANCH_CODE eq 'HQ'}
	    <div class="col-md-3">
			<b class="form-label">Branch</b>
		<select class="form-control" name="branch_id" onChange="check_use_grn();">
	    	{*<option value="">-- All --</option>*}
	    	{foreach from=$branches key=bid item=b}
	    	    {if !$branches_group.have_group.$bid}
	    	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				{/if}
	    	{/foreach}
	    	{if $branches_group.header}
				<optgroup label="Branches Group">
					{foreach from=$branches_group.header key=bgid item=bg}
						<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
						{foreach from=$branches_group.items.$bgid item=r}
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
	<div class="col-md-3">
		<b class="form-label">Vendor</b>
	<select class="form-control" name="vendor_id" onChange="check_show_by();">
	    <option value="">-- All --</option>
	    {foreach from=$vendors item=r}
	        <option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
	    {/foreach}
	</select>
	</div>
	
	<div class="col-md-3">
		<b class="form-label">SKU Type</b>
	<select class="form-control" name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=t}
		    <option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
		{/foreach}
	</select>
	</div>
	
	<div class="col-md-3">
		<b class="form-label">Sort by</b>
	<select class="form-control" name="sort_by" onChange="change_sort_by();">
	    <option id="opt_reset_sort_by" value="">--</option>
	    {foreach from=$sort_arr key=k item=s}
	        <option id="opt_sort_by_{$k}" class="class_sort_by" value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$s}</option>
	    {/foreach}
	</select>
	</div>

	
		<span id="span_order_by" {if !$smarty.request.sort_by}style="display:none;"{/if}>
			<div class="col-md-3">
			<select class="form-control" name="order_by">
				<option value="asc" {if $smarty.request.order_by eq 'asc'}selected {/if}>Ascending</option>
				<option value="desc" {if $smarty.request.order_by eq 'desc'}selected {/if}>Descending</option>
			</select>
		</div>
		</span>
	

		<div class="col-md-3">
			<b class="form-label">Blocked Item in PO:</b>
		<select class="form-control" name="blocked_po">
			<option value="">-- No Filter --</option>
			<option value="yes" {if $smarty.request.blocked_po eq 'yes'}selected {/if}>Yes</option>
			<option value="no" {if $smarty.request.blocked_po eq 'no'}selected {/if}>No</option>
		</select> 
		</div>
		
		<div class="col-md-3">
			<b class="form-label">Status</b>
		<select class="form-control" name="status">
			<option value="all" {if $smarty.request.status eq 'all'}selected {/if}>All</option>
			<option value="1" {if !isset($smarty.request.a) or $smarty.request.status eq '1'}selected {/if}>Active</option>
			<option value="0" {if $smarty.request.status eq '0'}selected {/if}>Inactive</option>
		</select>
		</div>
	</div>
	
	<p>
		{*<b>Category</b>
		<input type=checkbox id=all_category name=all_category {if $smarty.request.all_category}checked{/if} onchange="toggle_all_category(this);"> <label for=all_category><b>All</b></label>
		<span id=sel_cat {if $smarty.request.all_category}style="display:none"{/if}>
		<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
		<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
		<input id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus="this.select();reset_autocomplete();" size=50><br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
  </span>
		<div id=autocomplete_category_choices class=autocomplete style="width:600px !important;display:none;"></div>
		*}
		{include file='category_autocomplete.tpl' all=true}
	</p>
	{if $config.enable_gst}
	<p>
		<b>Input Tax</b>
		<select name="input_tax">
			<option value="">-- All --</option>
			{foreach from=$input_tax_list key=rid item=r}
				<option value="{$r.id}" {if $smarty.request.input_tax eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
			{/foreach}
		</select>
		
		<b>Output Tax</b>
		<select name="output_tax">
			<option value="">-- All --</option>
			{foreach from=$output_tax_list key=rid item=r}
				<option value="{$r.id}" {if $smarty.request.output_tax eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
			{/foreach}
		</select>
	</p>
	{/if}
	<p>
		<div class="row">
			<div class="col-md-3">
				<b class="form-label">Date From</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12> 
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
	
			<div class="col-md-3">
				<b class="form-label">To</b>
		<div class="form-inline">
			<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12> 
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		</div>
			</div>
			
			
			<div class="col-md-3">
				<b class="form-label">Show by</b>
			<select class="form-control" name="show_by" onchange="change_sort_by_select();">
				<option id="show_by_cat" value="cat" {if $smarty.request.show_by eq 'cat'}selected {/if}>Category</option>
				<option id="show_by_vendor" value="vendor" {if $smarty.request.show_by eq 'vendor'}selected {/if}>Vendor</option>
				<option id="show_by_branch" style="display:none" value="branch" {if $smarty.request.show_by eq 'branch'}selected {/if}>Branch</option>
			</select>
			</div>
		
		

		<div class="col-md-3">
			<div class="form-label form-inline mt-4">
				<input type="checkbox" id="use_grn" name="use_grn" {if $smarty.request.use_grn}checked{/if}> <b>Use GRN&nbsp;</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}\n- BOM SKU will not show when Show by Vendor option is selected due to no masterfile vendor or receive vendor.')">?</a>]
	
			{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
				<b>&nbsp;HQ Cost&nbsp;</b>
				<input type="checkbox" id="hq_cost" name="hq_cost" {if $smarty.request.hq_cost eq 'on'}checked {/if}>
			{/if}
			</div>
		</div>
	</div>
	</p>
	
		
	<p>
		<button class="btn btn-primary" name="a" value="show_report" onclick="do_submit('qty')" >Show by Qty</button>
		{if $sessioninfo.privilege.SHOW_COST}
			<button class="btn btn-primary" name="a" value="show_report" onclick="do_submit('cost')">Show by Cost</button>
		{/if}
		<!--<input type=hidden name=submit value=1>-->
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="a" value="output_excel" onclick="do_submit('qty')">{#OUTPUT_EXCEL#} by Qty</button>
			{if $sessioninfo.privilege.SHOW_COST}
				<button class="btn btn-info" name="a" value="output_excel" onclick="do_submit('cost')">{#OUTPUT_EXCEL#} by Cost</button>
			{/if}
		{/if}
		<input type="button" class="btn btn-primary" onclick="do_print()" value="Print">
	</p>
</form>
<script>
check_show_by();
change_sort_by_select();
</script>
</div>

<br />

{/if}

	<div class="alert alert-primary rounded mt-2" style="max-width: 500px;">
		<ul>
			<li> <span class="got_sc">*</span> = Got Stock Check</li>
			<li> <span class="not_up_to_date">*</span> = Not Up-to-date</li>
			<li> <sup>1</sup> The value is from closing date.</li>
			<li> <sup>2</sup> The value is from start date.</li>
			<li> Opening / Closing Balance's Value = Cost * Qty.</li>
			<li> Opening / Closing Balance's Sales Value = Selling Price * Qty.</li>
			<li> Day Turnover Method:
				<ul>
					<li> Ratio = total sales / ((opening stock + closing stock) / 2)</li>
					<li> Days = 365 / ratio</li>
				</ul>
			</li>
		</ul>
	</div>
	</div>
</div>

{if !$table}
	{if $smarty.request.subm}<p>No Data</p>{/if}
	
{else}
	{if $report_header}<h2>{$report_header}</h2>{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=colspan value=2}
	{else}
		{assign var=colspan value=1}
	{/if}

	{if $got_opening_sc}
		{assign var=add_colspan value=1}
	{/if}
	
	{assign var=add_colspan2 value=1}
	

	<div class="card mx-3">
		<div class="card-body">
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover" id="tbl_report">
				<thead class="bg-gray-100">		
					<tr class="header">
						<th rowspan="2">{if $smarty.request.show_by eq 'vendor'}Vendor{elseif $smarty.request.show_by eq 'branch'}Branch{else}Category{/if}</th>
						<th colspan="{$colspan+$add_colspan+$add_colspan2}">Opening Balance</th>
						<th rowspan="2">GRN</th>
						<th rowspan="2">GRA</th>
						<th rowspan="2">POS</th>
						<th rowspan="2">DO</th>
						{if $got_range_sc}
							<th rowspan="2">Stock Take<br />Adjust</th>
						{/if}
						<th colspan="2">ADJ {if $form.type eq 'qty'}Qty{else}Value{/if}</th>
						{if $config.consignment_modules}
							<th rowspan="2">CN</th>
							<th rowspan="2">DN</th>
						{/if}
						<th colspan="{$colspan+$add_colspan2}">Closing Balance {if !$config.stock_balance_use_accumulate_last_cost}by last cost{/if}</th>
						<th colspan="2">Day Turnover</th>
					</tr>
					<tr class="header">
						{if $got_opening_sc}
							<th>Stock Take Adjust</th>
						{/if}
						<th>Qty</th>
						{if $sessioninfo.privilege.SHOW_COST}
							<th>Value<sup>2</sup></th>
						{/if}
						<th>Sales Value<sup>2</sup></th>
						<th>In</th>
						<th>Out</th>
						<th>Qty</th>
						{if $sessioninfo.privilege.SHOW_COST}
							<th>Value{if !$config.stock_balance_use_accumulate_last_cost}<sup>1</sup>{/if}</th>
						{/if}
						<th>Sales Value<sup>1</sup></th>
						<th>Ratio</th>
						<th>Days</th>
					</tr>
				</thead>
				{include file='report.stock_balance_summary.row.tpl'}
				<tr class="header">
					<th class="r">Total</th>
					{if $got_opening_sc}
						<th class="r {if $total.sc_adj_from < 0}red{/if}">{$total.sc_adj_from|qty_nf}</th>
					{/if}
					<th class="r {if $total.sb_from < 0}red{/if}">{$total.sb_from|qty_nf}</th>
					{if $sessioninfo.privilege.SHOW_COST}
						<th class="r {if $total.sb_from_val < 0}red{/if}">{$total.sb_from_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
					{/if} 
					<th class="r {if $total.sales_value_from < 0}red{/if}">{$total.sales_value_from|number_format:2}</th>
					<th class="r">
						{if $form.type eq 'qty'}
							<font {if $total.grn < 0} color="red" {/if}>{$total.grn|qty_nf}</font> {if $smarty.request.use_grn}{if $smarty.request.vendor_id}<font {if $total.grn_vendor_qty < 0} color="red" {/if}>({$total.grn_vendor_qty|qty_nf})</font>{/if}{/if}
							
						{else}
							<font {if $total.grn_cost < 0} color="red" {/if}>{$total.grn_cost|number_format:$config.global_cost_decimal_points|ifzero}</font> {if $smarty.request.use_grn}{if $smarty.request.vendor_id}<font {if $total.grn_vendor_cost < 0} color="red" {/if}>({$total.grn_vendor_cost|number_format:$config.global_cost_decimal_points})</font>{/if}{/if}
							
						{/if}
					</th>
					<th class="r {if ($form.type eq 'qty' && $total.gra < 0) || ($form.type neq 'qty' && $total.gra_cost < 0) }red{/if}">
						{if $form.type eq 'qty'}
							{$total.gra|qty_nf}
						{else}
							{$total.gra_cost|number_format:$config.global_cost_decimal_points|ifzero}
						{/if}
					</th>
					<th class="r {if ($form.type eq 'qty' && $total.pos < 0) || ($form.type neq 'qty' && $total.pos_cost < 0) }red{/if}">
						{if $form.type eq 'qty'}
							{$total.pos|qty_nf}
						{else}
							{$total.pos_cost|number_format:$config.global_cost_decimal_points|ifzero}
						{/if}
					</th>
					<th class="r {if ($form.type eq 'qty' && $total.do < 0) || ($form.type neq 'qty' && $total.do_cost < 0) }red{/if}">
						{if $form.type eq 'qty'}
							{$total.do|qty_nf}
						{else}
							{$total.do_cost|number_format:$config.global_cost_decimal_points|ifzero}
						{/if}
					</th>
					{if $got_range_sc}
						<th class="r {if ($form.type eq 'qty' && $total.sc_adj < 0) || ($form.type neq 'qty' && $total.sc_adj_cost < 0) }red{/if}">
							{if $form.type eq 'qty'}	
								{$total.sc_adj|qty_nf}
							{else}
								{$total.sc_adj_cost|number_format:$config.global_cost_decimal_points|ifzero}
							{/if}
						</th>
					{/if}
					<th class="r {if ($form.type eq 'qty' && $total.adj_in < 0) || ($form.type neq 'qty' && $total.adj_in_cost < 0) }red{/if}">
						{if $form.type eq 'qty'}		
							{$total.adj_in|qty_nf}
						{else}
							{$total.adj_in_cost|number_format:$config.global_cost_decimal_points|ifzero}
						{/if}
					</th>
					<th class="r {if ($form.type eq 'qty' && $total.adj_out < 0) || ($form.type neq 'qty' && $total.adj_out_cost < 0) }red{/if}">
						{if $form.type eq 'qty'}		
							{$total.adj_out|qty_nf}
						{else}
							{$total.adj_out_cost|number_format:$config.global_cost_decimal_points|ifzero}
						{/if}
					</th>
					{if $config.consignment_modules}
						<th class="r {if ($form.type eq 'qty' && $total.cn_qty < 0) || ($form.type neq 'qty' && $total.cn_val < 0) }red{/if}">
							{if $form.type eq 'qty'}	
								{$total.cn_qty|qty_nf}
							{else}
								{$total.cn_val|number_format:$config.global_cost_decimal_points|ifzero}
							{/if}
						</th>
						<th class="r {if ($form.type eq 'qty' && $total.dn_qty < 0) || ($form.type neq 'qty' && $total.dn_val < 0) }red{/if}">
							{if $form.type eq 'qty'}
								{$total.dn_qty|qty_nf}
							{else}
								{$total.dn_val|number_format:$config.global_cost_decimal_points|ifzero}
							{/if}
						</th>
					{/if}
					<th class="r {if $total.sb_to < 0}red{/if}">{$total.sb_to|qty_nf}</th>
					   {if $sessioninfo.privilege.SHOW_COST}
						<th class="r {if $total.sb_to_val < 0}red{/if}">{$total.sb_to_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
					{/if}
					<th class="r {if $total.sales_value_to < 0}red{/if}">{$total.sales_value_to|number_format:2}</th>			
					<th class="r {if $total.turnover_ratio < 0}red{/if}">{$total.turnover_ratio|number_format:2}</th>
					<th class="r {if $total.turnover_days < 0}red{/if}">{$total.turnover_days|number_format:2}</th>
				</tr>
			</table>
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
//init_autocomplete();
</script>
{/literal}
{/if}
{include file='footer.tpl'}
