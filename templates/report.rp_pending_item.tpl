{*
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
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.b_header th{
	background:#edffed;
	padding:6px 4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.rpt_table tr.sub_total th{
	background:#adffea;
	padding:6px 4px;
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

function expand_sub(cat_id, indent, obj){
	if(obj.src.indexOf('clock')>0) return;
	
	var tr_cat_row = $('tbody_cat_row-'+cat_id);
	obj.src = '/ui/clock.gif';
	
	var params = {};
	
	params['a'] = 'ajax_expand_sub';
	params['indent'] = indent;
	
	// use back form info
	if(document.f_a['branch_id']) params['branch_id'] = document.f_a['branch_id'].value;
	//params['sku_type'] = document.f_a['sku_type'].value;
	params['from'] = document.f_a['from'].value;
	params['to'] = document.f_a['to'].value;
	params['category_id'] = cat_id;
	//params['brand_id'] = document.f_a['brand_id'].value;
	//params['vendor_id'] = document.f_a['vendor_id'].value;
	
	// use new info
	params['category_id'] = cat_id;


	new Ajax.Request(phpself, {
		method:'post',
		parameters: params,
		onComplete: function(e){
			new Insertion.After(tr_cat_row, e.responseText);
			obj.remove();
		}
	});	
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<div class="noprint stdframe">
<form name="f_a" method="post">
	<input type="hidden" name="subm" value="1" />
	<input type="hidden" name="a" value="show_report" />

	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
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
		</select>&nbsp;&nbsp;
	{/if}

    
	<!--b>SKU Type</b>
	<select name="sku_type">
	    <option value="">-- All --</option>
	    {foreach from=$sku_type item=r}
	        <option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;-->
	
	<b>Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>View By</b>
	<select name="view_type">
		<option value="1" {if $smarty.request.view_type eq 1}selected{/if}>SKU</option>
		<option value="2" {if $smarty.request.view_type eq 2}selected{/if}>Category</option>
	</select>
	
	<p>
		{include file='category_autocomplete.tpl' all=true}
		<!--b>Brand</b>
		<select name="brand_id">
			<option value="">- All -</option>
			<option value="0">UNBRANDED</option>
			{foreach from=$brands key=brand_id item=r}
				<option value="{$brand_id}">{$r.description}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Vendor</b>
		<select name="vendor_id">
			<option value="" {if !$smarty.request.vendor_id}selected{/if}>- All -</option>
			{foreach from=$vendors key=vd_id item=r}
				<option value="{$vd_id}" {if $smarty.request.vendor_id eq $vd_id}selected{/if}>{$r.description}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;-->
	</p>
	
	<button name="a" value="show_report">{#SHOW_REPORT#}</button>
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
	{/if}

</form>
</div>


<script>
init_calendar();
</script>
{/if}
 

{if !$table}
	{if $smarty.request.subm && !$err}<p align=center>-- No data --</p>{/if}
{else}
	
<br />
<h2>{$report_title}</h2>

{if !$no_header_footer}
	{if $selected_cat_info}
		<p>
			&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
			{foreach from=$selected_cat_info.cat_tree_info item=ct}
				<a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
			{/foreach}
			{$selected_cat_info.description} /
		</p>
	{/if}
{/if}

<table width="100%" class="rpt_table" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="header">
			{if $smarty.request.view_type eq 1}
				<th rowspan="2">SKU Item Code</th>
				<th rowspan="2">Description</th>
				<th rowspan="2">MCode</th>
				{assign var=colspan value=3"}
			{else}
				<th rowspan="2">Category</th>
			{/if}
			<th colspan="2">Active</th>
			<th colspan="2">Expired</th>
		</tr>
		<tr class="header">
			<th>Refund</th>
			<th>Count</th>
			<th>Charges	</th>
			<th>Count</th>
		</tr>
	</thead>
	
	{include file='report.rp_pending_item.row.tpl'}
		
	<tr class="header">
		<th class="r" colspan="{$colspan}">Total</th>
		<th class="r">{$total.refund|number_format:2}</th>
		<th class="r">{$total.count|qty_nf}</th>
		<th class="r">{$total.charges|number_format:2}</th>
		<th class="r">{$total.expired_count|qty_nf}</th>
	</tr>
</table>
{/if}

{include file='footer.tpl'}