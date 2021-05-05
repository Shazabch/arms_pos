{*
12/9/2014 11:58 AM Andy
- Move the old stock balance summary to custom report for smarthq.

07/01/2016 14:00 Edwin
- Show 'HQ Cost' when branch is HQ and $config.sku_listing_show_hq_cost is enabled
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

<script>
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
	got_range_sc: '{$got_range_sc}'
{literal}}{/literal}
{literal}
var category_autocompleter = null;

function do_print(){
	window.print();
}

function show_child(id)
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

function toggle_all_cat(ele){
	if(ele.checked){
		$('sel_cat').hide();
		document.f_d['category_id'].value = '';
		document.f_d['category'].value = '';
	}else{
        $('sel_cat').show();
	}
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
	
	window.open('report.stock_balance.php?a=show_report&type=qty&'+$H(requested_info).toQueryString()+'&category_id='+k+'&use_grn='+use_grn);
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
	{if $BRANCH_CODE eq 'HQ'}
	    <b>Branch</b>
		<select name="branch_id" onChange="check_use_grn();">
	    	<option value="">-- All --</option>
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
	</p>
	<p>
		<b>Category</b>
		<input type=checkbox id=all_cat name=all_cat {if $smarty.request.all_cat}checked{/if} onchange="toggle_all_cat(this);"> <label for=all_cat><b>All</b></label>
		<span id=sel_cat {if $smarty.request.all_cat}style="display:none"{/if}>
		<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
		<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
		<input id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus="this.select();reset_autocomplete();" size=50><br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
  </span>
		<div id=autocomplete_category_choices class=autocomplete style="width:600px !important;display:none;"></div>
	</p>
	<p>
		<b>Date From</b>
		<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		&nbsp;
		<b>To</b>
		<input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
		
		<b>Show by</b>
		<select name="show_by" onchange="change_sort_by_select();">
		    <option id="show_by_cat" value="cat" {if $smarty.request.show_by eq 'cat'}selected {/if}>Category</option>
		    <option id="show_by_vendor" value="vendor" {if $smarty.request.show_by eq 'vendor'}selected {/if}>Vendor</option>
		    <option id="show_by_branch" style="display:none" value="branch" {if $smarty.request.show_by eq 'branch'}selected {/if}>Branch</option>
		</select>
		&nbsp;&nbsp;

		<input type=checkbox id=use_grn name=use_grn {if $smarty.request.use_grn}checked{/if}> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;

		{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
		<b>HQ Cost</b>
		<input type=checkbox id=hq_cost name=hq_cost {if $smarty.request.hq_cost eq 'on'}checked {/if}>
		&nbsp;&nbsp;{/if}
		<input type="submit" name="subm" value="Refresh" />
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button name=a value=output_excel>{#OUTPUT_EXCEL#}</button>
		{/if}
		<input type=button onclick="do_print()" value="Print">

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
		<li> <sup>1</sup> The value is from closing date.</li>
		<li> <sup>2</sup> The Value is from start date.</li>
	</ul>

{if !$table}
	{if $smarty.request.subm}<p>No Data</p>{/if}
	
{else}
	{if $report_header}<h2>{$report_header}</h2>{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=colspan value=2}
	{else}
		{assign var=colspan value=1}
	{/if}

	{if $config.stock_balance_report_show_additional_selling}
		{assign var=add_colspan value=1}
	{/if}
	{if $got_opening_sc}
		{assign var=add_colspan value=1}
	{/if}
	

	<table width="100%" class="report_table" id="tbl_report">
	    <tr class="header">
			<th rowspan="2">{if $smarty.request.show_by eq 'vendor'}Vendor{elseif $smarty.request.show_by eq 'branch'}Branch{else}Category{/if}</th>
			<th colspan="{$colspan+$add_colspan}">Opening Balance</th>
			<th colspan="{$colspan}">GRN</th>
			<th colspan="{$colspan}">GRA</th>
			<th colspan="{$colspan}">POS</th>
			<th colspan="{$colspan}">DO</th>
			{if $got_range_sc}
				<th colspan="{$colspan}">Stock Take<br />Adjust</th>
			{/if}
			<th colspan="4">ADJ</th>
		    {if $config.consignment_modules}
		        <th colspan="{$colspan}">CN</th>
		        <th colspan="{$colspan}">DN</th>
		    {/if}
			<th colspan="{$colspan+$add_colspan}">Closing Balance by last cost</th>
	    </tr>
	    <tr class="header">
			{if $got_opening_sc}
				<th>Stock Take Adjust</th>
			{/if}
	        <th>Qty</th>
	    	{if $sessioninfo.privilege.SHOW_COST}
				<th>Value<sup>2</sup></th>
            {/if}
           	{if $config.stock_balance_report_show_additional_selling}
				<th>Selling<sup>2</sup></th>
			{/if}
       		<th>{if $smarty.request.use_grn}Total Qty (Qty for this vendor){else}Qty{/if}</th>
	    	{if $sessioninfo.privilege.SHOW_COST}
				<th>{if $smarty.request.use_grn}Total Value (Value for this vendor){else}Value{/if}</th>
            {/if}
            <th>Qty</th>
	    	{if $sessioninfo.privilege.SHOW_COST}
				<th>Value</th>
            {/if}
			<th>Qty</th>
	    	{if $sessioninfo.privilege.SHOW_COST}
				<th>Value</th>
            {/if}
			<th>Qty</th>
	    	{if $sessioninfo.privilege.SHOW_COST}
				<th>Value<sup>1</sup></th>
            {/if}
			{if $got_range_sc}
				<th>Qty</th>
				{if $sessioninfo.privilege.SHOW_COST}
					<th>Value</th>
				{/if}
			{/if}
	        <th>In Qty</th>
	        <th>Value</th>
	        <th>Out Qty</th>
	        <th>Value</th>
		    {if $config.consignment_modules}
				<th>Qty</th>
		    	{if $sessioninfo.privilege.SHOW_COST}
					<th>Value<sup>1</sup></th>
	            {/if}
				<th>Qty</th>
		    	{if $sessioninfo.privilege.SHOW_COST}
					<th>Value<sup>1</sup></th>
	            {/if}
			{/if}
	        <th>Qty</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th>Value<sup>1</sup></th>
			{/if}
           	{if $config.stock_balance_report_show_additional_selling}
           		<th>Selling<sup>1</sup></th>
			{/if}            
	    </tr>
	    {include file='smarthq/report.stock_balance_summary.row.tpl'}
	    <tr class="header">
	        <th class="r">Total</th>
			{if $got_opening_sc}
				<th class="r">{$total.sc_adj_from|qty_nf}</th>
			{/if}
	        <th class="r">{$total.sb_from|qty_nf}</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th class="r">{$total.sb_from_val|number_format:2}</th>
			{/if}
           	{if $config.stock_balance_report_show_additional_selling}
				<th class="r">{$total.sb_from_selling|number_format:2}</th>
			{/if}            
			<th class="r">{$total.grn|qty_nf} {if $smarty.request.use_grn}({$total.grn_vendor_qty|qty_nf}){/if}</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th class="r">{$total.grn_cost|number_format:2} {if $smarty.request.use_grn}({$total.grn_vendor_cost|number_format:2}){/if}</th>
			{/if}
			<th class="r">{$total.gra|qty_nf}</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th class="r">{$total.gra_cost|number_format:2}</th>
			{/if}
	        <th class="r">{$total.pos|qty_nf}</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th class="r">{$total.pos_cost|number_format:2}</th>
			{/if}
	        <th class="r">{$total.do|qty_nf}</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th class="r">{$total.do_cost|number_format:2}</th>
			{/if}
			{if $got_range_sc}
				<th class="r">{$total.sc_adj|qty_nf}</th>
				{if $sessioninfo.privilege.SHOW_COST}
					<th class="r">{$total.sc_adj_cost|number_format:2}</th>
				{/if}
			{/if}
	        <th class="r">{$total.adj_in|qty_nf}</th>
	        <th class="r">{$total.adj_in_cost|number_format:2}</th>
	        <th class="r">{$total.adj_out|qty_nf}</th>
	        <th class="r">{$total.adj_out_cost|number_format:2}</th>
		    {if $config.consignment_modules}
		        <th class="r">{$total.cn_qty|qty_nf}</th>
				{if $sessioninfo.privilege.SHOW_COST}
			        <th class="r">{$total.cn_val|number_format:2}</th>
				{/if}
		        <th class="r">{$total.dn_qty|qty_nf}</th>
				{if $sessioninfo.privilege.SHOW_COST}
		        	<th class="r">{$total.dn_val|number_format:2}</th>
				{/if}
	        {/if}
	        <th class="r">{$total.sb_to|qty_nf}</th>
   			{if $sessioninfo.privilege.SHOW_COST}
	        	<th class="r">{$total.sb_to_val|number_format:2}</th>
	        {/if}
           	{if $config.stock_balance_report_show_additional_selling}
	        	<th class="r">{$total.sb_to_selling|number_format:2}</th>
			{/if}            
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

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
init_autocomplete();
</script>
{/literal}
{/if}
{include file='footer.tpl'}
