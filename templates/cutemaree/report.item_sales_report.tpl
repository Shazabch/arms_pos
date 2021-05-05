{*
5/15/2013 2:51 PM Andy
- Fix show cost problem.
- Add total row.
*}

{include file="header.tpl"}

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

<style>
{literal}

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
{/literal}
</style>

<script type="text/javascript">

var sort_by = '{$smarty.request.sort_by}';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		reset_sku_autocomplete();
		
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
	},
	// function when user change order by
	change_sort_by: function(){
		var ele = this.f['sort_by'];
		
		if(ele.value=='')   $('span_sort_order').hide();
		else    $('span_sort_order').show();
	},
	// function when user change filter by
	filter_by_changed: function(){
		var filter_by = getRadioValue(this.f['filter_by']);
		
		if(filter_by == 'sku'){
			$('p_cat_autocomplete').hide();
			$('div_sku_items_autocomplete').show();
		}else{
			$('div_sku_items_autocomplete').hide();
			$('p_cat_autocomplete').show();
		}
	},
	// function to check form before submit
	check_form: function(){
	
		if(getRadioValue(this.f['filter_by']) == 'sku'){
			if(this.f['sku_code_list'].length<=0){
				alert('Please select at least 1 item.');
				$('autocomplete_sku').focus();
				return false;
			}else{
				
			}
		}else{
			if(!this.f['all_category'].checked){	// no select all category
				if(!this.f['category_id'].value){
					alert('Please select category.');
					this.f['category'].focus();
					return false;
				}
			}			
		}
		
		return true;
	},
	// function when user click show report or export
	submit_form: function(t){
		this.f['export_excel'].value = 0;
		
		if(!this.check_form())	return false;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;	
		}

		passArrayToInput();
		
		this.f.submit();
	}
}

{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<form name="f_a" class="stdframe" style="background-color:#fff;" onSubmit="return false;" method="post">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="export_excel" />
	
	<p>
		<b>Branch</b>
		<select name="branch_id" id="branch_id">
			<option value=''>-- All --</option>
			{foreach from=$branches key=bid item=r}
				{if !$branches_group.have_group.$bid}
					<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
				{/if}
			{/foreach}
			{if $branches_group.header}
				<optgroup label='Branch Group'>
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
		</select>&nbsp;&nbsp;
		
		<b>Sort by</b>
		<select name="sort_by" onChange="REPORT.change_sort_by(this);">
			<option value="">--</option>
			<option value="sku_item_code" {if $smarty.request.sort_by eq 'sku_item_code'}selected {/if}>ARMS Code</option>
		    <option value="artno" {if $smarty.request.sort_by eq 'artno'}selected {/if}>Art No</option>
		    {*<option value="mcode" {if $smarty.request.sort_by eq 'mcode'}selected {/if}>MCode</option>*}
		    <option value="description" {if $smarty.request.sort_by eq 'description'}selected {/if}>Description</option>
		</select>
		<span id="span_sort_order" {if !$smarty.request.sort_by}style="display:none;"{/if}>
		<select name="sort_order">
			<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
			<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
		</select>
		</span>
	</p>
	
	<p>
		<b>Filter by: </b>
		<input type="radio" name="filter_by" value="cat" {if $smarty.request.filter_by eq 'cat' or !$smarty.request.filter_by}checked {/if} onChange="REPORT.filter_by_changed();" /> Category &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="filter_by" value="sku" {if $smarty.request.filter_by eq 'sku'}checked {/if} onChange="REPORT.filter_by_changed();" /> SKU
	</p>
	
	<div>
		<p id="p_cat_autocomplete" style="{if $smarty.request.filter_by eq 'sku'}display:none;{/if}">{include file="category_autocomplete.tpl" all=true}</p>
		
		<div id="div_sku_items_autocomplete" style="{if $smarty.request.filter_by eq 'cat' or !$smarty.request.filter_by}display:none;{/if}">
			{include file="sku_items_autocomplete_multiple.tpl"}
		</div>
	</div>
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
	
	{if $sessioninfo.privilege.SHOW_COST}
		<input type="checkbox" name="show_cost" value="1" {if $show_cost}checked {/if}/> Show Cost
	{/if}
	
	<p>
		<input type="button" value='Show Report' onClick="REPORT.submit_form();" /> &nbsp;&nbsp;

		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button name="output_excel" onClick="REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
		{/if}
	</p>
	
</form>
<script type="text/javascript">REPORT.initialize();</script>
{/if}

{if $smarty.request.show_report}
	<h3>{$report_title}</h3>
	
	{if !$data}
		{if !$err}No Data{/if}
	{else}
		
		<table width="100%" class="report_table">
			<tr class="header">
				<th rowspan="3">ARMS Code</th>
				<th rowspan="3">Art No</th>
				<th rowspan="3">Description</th>
				
				{assign var=cols value=2}
				{if $show_cost}{assign var=cols value=$cols+1}{/if}
				<th rowspan="2" colspan="{$cols}">GRN from Vendor</th>
				
				
				<th colspan="2" rowspan="2">Sales</th>
				{assign var=cols value=2}
				{if $show_cost}{assign var=cols value=$cols+2}{/if}
				
				<th colspan="{$cols}">Stock Balance at {$smarty.request.date_to}</th>
			</tr>
			
			<tr class="header">
				{assign var=cols value=1}
				{if $show_cost}{assign var=cols value=$cols+1}{/if}
				
				<th colspan="{$cols}">HQ</th>
				<th colspan="{$cols}">Branches</th>
			</tr>
			
			<tr class="header">
				<th>Date</th>
				<th>Qty</th>
				{if $show_cost}<th>Amt</th>{/if}
				
				<th>Qty</th>
				<th>%</th>
				
				<th>Qty</th>
				{if $show_cost}<th>Cost</th>{/if}
				
				<th>Qty</th>
				{if $show_cost}<th>Cost</th>{/if}
			</tr>
			
			{foreach from=$data item=item}
				{cycle values="#ffffff,#f0f0f0" assign="row_color"}
				
				{foreach from=$item.grn key=dt item=grn name=fgrn}
					<tr bgcolor="{$row_color}">
						{if $smarty.foreach.fgrn.first}
							{* First row *}
							{count var=$item.grn assign=main_rows}
							{if $main_rows<=0}{assign var=main_rows value=1}{/if}
							
							<td rowspan="{$main_rows}">{$item.info.sku_item_code|default:'-'}</td>
							<td rowspan="{$main_rows}">{$item.info.artno|default:'-'}</td>
							<td rowspan="{$main_rows}">{$item.info.description|default:'-'}</td>
						{/if}
						
						{* GRN INFO *}
						<td align="center">{$dt|ifzero:'-'}</td>
						<td align="right">{$grn.qty|qty_nf}</td>
						
						{if $show_cost}
							<td align="right">{$grn.cost|default:0|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						
						{if $smarty.foreach.fgrn.first}
							{* Sales *}
							<td align="right" rowspan="{$main_rows}">{$item.sales.total.qty|qty_nf}</td>
							<td align="right" rowspan="{$main_rows}">
								{assign var=per value=0}
								{if $item.sales.total.qty and $item.grn_total.qty}
									{assign var=per value=$item.sales.total.qty/$item.grn_total.qty*100}
								{/if}
								{$per|number_format:2}%
							</td>
							
							{* Stock Balance *}
							{* HQ *}
							<td align="right" rowspan="{$main_rows}">{$item.stock_balance.hq.qty|qty_nf}</td>
							{if $show_cost}
								<td align="right" rowspan="{$main_rows}">{$item.stock_balance.hq.cost|number_format:$config.global_cost_decimal_points}</td>
							{/if}
							
							{* Branches *}
							<td align="right" rowspan="{$main_rows}">{$item.stock_balance.branch.qty|qty_nf}</td>
							{if $show_cost}
								<td align="right" rowspan="{$main_rows}">{$item.stock_balance.branch.cost|number_format:$config.global_cost_decimal_points}</td>
							{/if}
						{/if}
					</tr>
				{/foreach}
			{/foreach}
			
			<tr class="header">
				<th colspan="3" align="right">Total</th>
				
				{* GRN *}
				<th>-</th>
				<td align="right"><b>{$total.grn.qty|qty_nf}</b></td>
				{if $show_cost}
					<td align="right"><b>{$total.grn.cost|default:0|number_format:$config.global_cost_decimal_points}</b></td>
				{/if}
				
				{* Sales *}
				<td align="right"><b>{$total.sales.qty|qty_nf}</b></td>
				<td align="right"><b>{$total.sales.per|number_format:2}%</b></td>
				
				{* Stock Balance *}
				{* HQ *}
				<td align="right"><b>{$total.stock_balance.hq.qty|qty_nf}</b></td>
				{if $show_cost}
					<td align="right"><b>{$total.stock_balance.hq.cost|number_format:$config.global_cost_decimal_points}</b></td>
				{/if}
				
				{* Branches *}
				<td align="right"><b>{$total.stock_balance.branch.qty|qty_nf}</b></td>
				{if $show_cost}
					<td align="right"><b>{$total.stock_balance.branch.cost|number_format:$config.global_cost_decimal_points}</b></td>
				{/if}
			</tr>
		</table>
	{/if}
{/if}




{include file="footer.tpl"}
