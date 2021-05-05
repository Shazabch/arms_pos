{*
3/26/2018 5:23 PM Justin
- Amended the Notes for Use GRN.

4/25/2019 2:56 PM Andy
- Enhanced to can select all branch.

12/18/2019 9:14 AM William
- Enhanced to disabled filter "Use GRN" when vendor and branch select all.
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

</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var requested_info = {literal}{{/literal}
	date: '{$smarty.request.date}',
	branch_id: '{$smarty.request.branch_id}',
	vendor_id: '{$smarty.request.vendor_id}',
	sku_type: '{$smarty.request.sku_type}',
	sort_by: '{$smarty.request.sort_by}',
	show_by: '{$smarty.request.show_by}',
	order_by: '{$smarty.request.order_by}',
	hq_cost: '{$smarty.request.hq_cost}',
	blocked_po: '{$smarty.request.blocked_po}',
	status: '{$smarty.request.status}',
	input_tax_filter: '{$smarty.request.input_tax_filter}',
	output_tax_filter: '{$smarty.request.output_tax_filter}',
	got_closing_sc: '{$got_closing_sc}',
{literal}}{/literal}
{literal}
//var category_autocompleter = null;

function do_print(){
	window.print();
}

function check_show_by(){
	/*var v = document.f_d['vendor_id'].value;
	if(v==''){
		document.f_d['show_by'].disabled = false;
		//$('use_grn').checked=false;
		//$('use_grn').disabled=true;
	}else{
        document.f_d['show_by'].disabled = true;
        document.f_d['show_by'].value = 'cat';
        //$('use_grn').disabled=false;
	}*/
	
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
	
	window.open('report.closing_stock_by_sku.php?a=show_report&'+$H(requested_info).toQueryString()+'&category_id='+k+'&use_grn='+use_grn);
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
	
	var fixedSortBy=['sb_to','sb_to_val'];
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
			allow_use_grn = false;
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

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}
{if !$no_header_footer}

<div class="noprint stdframe" style="background:#fff;">
<form name="f_d">
	<input type="hidden" name="a" value="show_report" />
	<input type="hidden" name="type" value="{$form.type}">
	{if $BRANCH_CODE eq 'HQ'}
	    <b>Branch</b>
		<select name="branch_id" onChange="check_use_grn();">
	    	<option value="">-- All --</option>
	    	{foreach from=$branches key=bid item=b}
	    	    {if !$branch_group_list.have_group.$bid}
	    	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				{/if}
	    	{/foreach}
	    	{if $branch_group_list.group}
				<optgroup label="Branches Group">
					{foreach from=$branch_group_list.group key=bgid item=bg}
						<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
						{foreach from=$bg.itemList item=r}
							<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;{$r.code} - {$r.description}</option>
						{/foreach}
					{/foreach}
				</optgroup>
	    	{/if}
			{* if $config.consignment_modules && $config.masterfile_branch_region}
				<optgroup label='Region'>
					{foreach from=$config.masterfile_branch_region key=type item=f}
						{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
							{assign var=curr_type value="REGION_`$type`"}
							<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
						{/if}
					{/foreach}
				</optgroup>
			{/if *}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	<b>Vendor</b>
	<select name="vendor_id" onChange="check_show_by();">
	    <option value="">-- All --</option>
	    {foreach from=$vendors item=r}
	        <option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;
	
	<b>SKU Type</b>
	<select name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=t}
		    <option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;
	
	<b>Sort by</b>
	<select name="sort_by" onChange="change_sort_by();">
	    <option id="opt_reset_sort_by" value="">--</option>
	    {foreach from=$sort_arr key=k item=s}
	        <option id="opt_sort_by_{$k}" class="class_sort_by" value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$s}</option>
	    {/foreach}
	</select>
	<span id="span_order_by" {if !$smarty.request.sort_by}style="display:none;"{/if}>
	    <select name="order_by">
	        <option value="asc" {if $smarty.request.order_by eq 'asc'}selected {/if}>Ascending</option>
	        <option value="desc" {if $smarty.request.order_by eq 'desc'}selected {/if}>Descending</option>
	    </select>
	</span>
	<p>
		<b>Blocked Item in PO:</b>
		<select name="blocked_po">
			<option value="">-- No Filter --</option>
			<option value="yes" {if $smarty.request.blocked_po eq 'yes'}selected {/if}>Yes</option>
			<option value="no" {if $smarty.request.blocked_po eq 'no'}selected {/if}>No</option>
		</select> &nbsp;&nbsp;
		
		<b>Status</b>
		<select name="status">
			<option value="all" {if $smarty.request.status eq 'all'}selected {/if}>All</option>
			<option value="1" {if !isset($smarty.request.a) or $smarty.request.status eq '1'}selected {/if}>Active</option>
			<option value="0" {if $smarty.request.status eq '0'}selected {/if}>Inactive</option>
		</select>
		
		{if $config.enable_gst}
			&nbsp;&nbsp;
			<span>
				<b>Input Tax</b>
				<select name="input_tax_filter">
					<option value="">-- All --</option>
					{foreach from=$input_tax_list key=rid item=r}
						<option value="{$r.id}" {if $smarty.request.input_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
					{/foreach}
				</select>
			</span>
			&nbsp;&nbsp;
			<span>
				<b>Output Tax</b>
				<select name="output_tax_filter">
					<option value="">-- All --</option>
					{foreach from=$output_tax_list key=rid item=r}
						<option value="{$r.id}" {if $smarty.request.output_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
					{/foreach}
				</select>
			</span>
		{/if}
	</p>
	<p>
		{include file='category_autocomplete.tpl' all=true}
	</p>
	<p>
		<b>Date</b>
		<input type="text" name="date" value="{$smarty.request.date}" id="added1" readonly="1" size="12"> <img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select a Date">
		&nbsp;
		&nbsp;&nbsp;
		
		<b>Show by</b>
		<select name="show_by" onchange="change_sort_by_select();">
		    <option id="show_by_cat" value="cat" {if $smarty.request.show_by eq 'cat'}selected {/if}>Category</option>
		    <option id="show_by_vendor" value="vendor" {if $smarty.request.show_by eq 'vendor'}selected {/if}>Vendor</option>
		    <option id="show_by_branch" style="display:none" value="branch" {if $smarty.request.show_by eq 'branch'}selected {/if}>Branch</option>
		</select>
		&nbsp;&nbsp;

		<input type="checkbox" id="use_grn" name="use_grn" {if $smarty.request.use_grn}checked{/if}> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.SD_USE_GRN_INFO|escape:javascript}\n- BOM SKU will not show when Show by Vendor option is selected due to no masterfile vendor or receive vendor.')">?</a>]
&nbsp;&nbsp;
		{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
			<b>HQ Cost</b>
			<input type="checkbox" id="hq_cost" name="hq_cost" {if $smarty.request.hq_cost eq 'on'}checked {/if}>
		{/if}
	</p>
	<p>
		<button name="a" value="show_report">Show Report</button>
		<!--<input type=hidden name=submit value=1>-->
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
		{/if}
		<input type="button" onclick="do_print()" value="Print">
	</p>
</form>
<script>
check_show_by();
change_sort_by_select();
</script>
</div>

<br />
{/if}

<ul>
	<li> <span class="got_sc">*</span> = Got Stock Check</li>
	<li> <span class="not_up_to_date">*</span> = Not Up-to-date</li>
	<li> Closing Balance's Value = Cost * Qty.</li>
	<li> Closing Balance's Sales Value = Selling Price * Qty.</li>
</ul>

{if !$table}
	{if $smarty.request.subm}<p>No Data</p>{/if}
	
{else}
	{if $report_header}<h2>{$report_header}</h2>{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=colspan value=3}
	{else}
		{assign var=colspan value=2}
	{/if}

	{if $got_closing_sc}
		{assign var=colspan value=$colspan+1}
	{/if}
	

	<table width="100%" class="report_table" id="tbl_report">
		<thead>		
		    <tr class="header">
				<th rowspan="2">{if $smarty.request.show_by eq 'vendor'}Vendor{elseif $smarty.request.show_by eq 'branch'}Branch{else}Category{/if}</th>
				{if $got_range_sc}
					<th rowspan="2">Stock Take<br />Adjust</th>
				{/if}
				<th colspan="{$colspan}">Closing Balance by last cost</th>
		    </tr>
		    <tr class="header">
				{if $got_closing_sc}
					<th>Stock Take Adjust</th>
				{/if}
		        <th>Qty</th>
				{if $sessioninfo.privilege.SHOW_COST}
					<th>Value</th>
				{/if}
				<th>Sales Value</th>
		    </tr>
	    </thead>
	    {include file='report.closing_stock.row.tpl'}
	    <tr class="header">
	        <th class="r">Total</th>
			{if $got_closing_sc}
				<th class="r">{$total.sc_adj_to|qty_nf}</th>
			{/if}
	        <th class="r">{$total.sb_to|qty_nf}</th>
   			{if $sessioninfo.privilege.SHOW_COST}
	        	<th class="r">{$total.sb_to_val|number_format:$config.global_cost_decimal_points}</th>
	        {/if}
			<th class="r">{$total.sales_value_to|number_format:2}</th>			
	    </tr>
	</table>
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
</script>
{/literal}
{/if}
{include file='footer.tpl'}
