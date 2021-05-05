{*
3/25/2014 2:33 PM Andy
- New Custom Report (Stock Movement Status Report) for Sbakery.

3/25/2014 3:04 PM Andy
- Fix wrong total opening amt.
- Add red color for negative value.
- Add in missing value indicator.

3/27/2014 3:48 PM Andy
- Add print report feature.
*}

{include file="header.tpl"}

{if !$no_header_footer}

<style>

{literal}
.negative, .item_outdated{
	color:red;
	font-weight: bold;
}

@media print {
	td div.crop {
		line-height:1em;
		height:2em;
		overflow:hidden;
		font-size: 80%;
	}
}

.printarea {
	clear:both;
	page-break-after: always;
}
{/literal}
</style>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">

{literal}
var REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
	        inputField     :    "inp_date",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	},
	// function when user change sku filter 
	sku_filter_changed: function(){
		var t = getRadioValue(this.f['sku_filter']);
		
		$('div_search-cat').hide();
		$('div_search-sku').hide();
		
		$('div_search-'+t).show();
	},
	// function to validate form before submit
	check_form: function(){
		// got branch filter
		if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch.');
				this.f['branch_id'].focus();
				return false;
			}
		}
		
		// sku filter
		/*var sku_filter = getRadioValue(this.f['sku_filter']);
		if(sku_filter == 'sku'){
			if($('sku_code_list').length<=0){
				alert('Please search and add at least 1 SKU.');
				$('autocomplete_sku').focus();
				return false;
			}
		}else if(sku_filter == 'cat'){
			if(!$('all_category').checked && !$('category_id').value){
				alert('Please search and select category.');
				$('autocomplete_category').focus();
				return false;
			}
		}*/
		
		return true;
	},
	// function when user click show report
	submit_form: function(t){
		this.f['export_excel'].value = '';
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		if(!this.check_form())	return false;
		
		for(var i=0; i<$('sku_code_list').length; i++){
		    $('sku_code_list').options[i].selected = true;
		}
		
		this.f.submit();
	},
	// function when user change sort by
	sort_by_changed: function(){
		var sort_by = this.f['sort_by'].value;
		if(sort_by){
			$('span_sort_order').show();
		}else{
			$('span_sort_order').hide();
		}
	}
}
{/literal}
</script>
{/if}



<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li>{$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}

<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="export_excel" value="" />
	
	{* Branch *}
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch: </b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=b}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	{* Date *}
	<b>Date: </b>
	<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" /> &nbsp;&nbsp;&nbsp;&nbsp;
	
	{* Brand *}
	<b>Brand: </b>
	<select name="brand_id">
		<option value="">-- All --</option>
		{foreach from=$brands key=bid item=b}
			<option value="{$bid}" {if $smarty.request.brand_id eq $bid}selected {/if}>{$b.description}</option>
		{/foreach}
	</select>
	
	
	<p>
		<b>SKU Selection: </b>
		<input type="radio" name="sku_filter" value="cat" {if !$smarty.request.sku_filter or $smarty.request.sku_filter eq 'cat'}checked {/if} onChange="REPORT.sku_filter_changed();" /> By Category
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sku_filter" value="sku" {if $smarty.request.sku_filter eq 'sku'}checked {/if} onChange="REPORT.sku_filter_changed();" /> By SKU
		
		<div>
			<div id="div_search-cat" style="{if $smarty.request.sku_filter eq 'sku'}display:none;{/if}">
				{include file="category_autocomplete.tpl" all=true}
			</div>
			
			<div id="div_search-sku" style="{if !$smarty.request.sku_filter or $smarty.request.sku_filter eq 'cat'}display:none; {/if}">
				{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a'}
			</div>
		</div>
	</p>
	
	<b>Display all SKU in the selection?</b>
	<select name="sku_narrow_down">
		{foreach from=$sku_narrow_down_list key=k item=v}
			<option value="{$k}" {if $smarty.request.sku_narrow_down eq $k}selected {/if}>{$v}</option>
		{/foreach}
	</select>
	
	<p>
		<b>Sort by</b>
		<select name="sort_by" onChange="REPORT.sort_by_changed();">
			<option value="">-- NONE --</option>
			{foreach from=$sort_list key=k item=v}
				<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$v}</option>
			{/foreach}
		</select>
		<span id="span_sort_order" style="{if !$smarty.request.sort_by}display:none;{/if}">
			<select name="sort_order">
				<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
				<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
			</select>
		</span>
	</p>
	
	<p>
		<input type="button" value='Show Report' onClick="REPORT.submit_form();" /> 

		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button type="button" name="output_excel" onClick="REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button> 
		{/if}
		
		<input type="button" value='Print' onClick="window.print();" />
	</p>
	
	
	<ul>
		<li> Report will take longer time to process and may reach memory limit and return a blank page if all category has been choose.</li>
		<li> The item marked with <span class="item_outdated">*</span> indicate the items stock balance is not yet updated, please try to view back the report after 30 minutes.</li>
		<li> Column marked with <sup>1</sup> indicate the value is using opening cost.</li>
		<li> Column marked with <sup>2</sup> indicate the value is using the cost captured at the time when the item is added to that document.</li>
		<li> Column marked with <sup>3</sup> indicate the value is using closing cost.</li>
	</ul>
</form>
<script type="text/javascript">REPORT.initialize();</script>
{/if}

{if $smarty.request.load_report and !$err}
	
	<br class="noprint" />
	{if !$data.si_list}
		* No Data *
	{else}
		<h2>{$report_title}</h2>
		
		{capture assign="table_header"}
			<thead>
				<tr class="header">
					<th rowspan="2">No.</th>
					<th rowspan="2">ARMS Code / MCode</th>
					<th rowspan="2">Art No.</th>
					<th rowspan="2">Description</th>
					<th rowspan="2">UOM</th>
					<th rowspan="2">Unit Cost <sup>1</sup></th>
					<th rowspan="2">Selling Price</th>
					
					<th colspan="2">Opening</th>
					<th colspan="2">Stock In</th>
					<th colspan="2">POS</th>
					<th colspan="2">Stock Out</th>
					<th colspan="2">Closing</th>
				</tr>
				
				<tr class="header">
					<th>Qty</th>
					<th>Amt<sup>1</sup></th>
					<th>Qty</th>
					<th>Amt<sup>2</sup></th>
					<th>Qty</th>
					<th>Amt<sup>2</sup></th>
					<th>Qty</th>
					<th>Amt<sup>2</sup></th>
					<th>Qty</th>
					<th>Amt<sup>3</sup></th>
				</tr>
			</thead>
		{/capture}
		
		{assign var=row_counter value=0}
		{assign var=page_num value=0}
		{foreach from=$data.si_list key=sid item=r name=f}
			{if !$row_counter}
				{assign var=page_num value=$page_num+1}
				
				<div class="printarea">
				<h5 align="right">Page {$page_num}</h5>
				<table width="100%" class="report_table">
					{$table_header}
			{/if}
			
			{* Content *}
			<tr>
				<td rowspan="2">{$smarty.foreach.f.iteration} {if $r.info.changed}<span class="item_outdated">*</span>{/if}</td>
				
				<td>{$r.info.sku_item_code}</td>
				<td rowspan="2">{$r.info.artno|default:'&nbsp;'}</td>
				<td rowspan="2"><div class="crop">{$r.info.sku_desc|default:'&nbsp;'}</div></td>
				<td rowspan="2">{$r.info.packing_uom_code|default:'&nbsp;'}</td>
				
				<td rowspan="2" align="right" class="{if $r.info.cost<0}negative{/if}">{$r.info.cost|number_format:$config.global_cost_decimal_points}</td>
				<td rowspan="2" align="right" class="{if $r.info.selling<0}negative{/if}">{$r.info.selling|number_format:2}</td>
				
				{* Opening *}
				<td rowspan="2" align="right" class="{if $r.info.opening.qty<0}negative{/if}">{$r.info.opening.qty|qty_nf}</td>
				<td rowspan="2" align="right" class="{if $r.info.opening.total_cost<0}negative{/if}">{$r.info.opening.total_cost|number_format:$config.global_cost_decimal_points}</td>
				
				{* Stock In *}
				<td rowspan="2" align="right" class="{if $r.data.stock_in.qty<0}negative{/if}">{$r.data.stock_in.qty|qty_nf|ifzero:'&nbsp;'}</td>
				<td rowspan="2" align="right" class="{if $r.data.stock_in.cost<0}negative{/if}">{$r.data.stock_in.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
				
				{* POS *}
				<td rowspan="2" align="right" class="{if $r.data.pos.qty<0}negative{/if}">{$r.data.pos.qty|qty_nf|ifzero:'&nbsp;'}</td>
				<td rowspan="2" align="right" class="{if $r.data.pos.cost<0}negative{/if}">{$r.data.pos.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
				
				{* Stock Out *}
				<td rowspan="2" align="right" class="{if $r.data.stock_out.qty<0}negative{/if}">{$r.data.stock_out.qty|qty_nf|ifzero:'&nbsp;'}</td>
				<td rowspan="2" align="right" class="{if $r.data.stock_out.cost<0}negative{/if}">{$r.data.stock_out.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
				
				{* Closing *}
				<td rowspan="2" align="right" class="{if $r.info.closing.qty<0}negative{/if}">{$r.info.closing.qty|qty_nf}</td>
				<td rowspan="2" align="right" class="{if $r.info.closing.total_cost<0}negative{/if}">{$r.info.closing.total_cost|number_format:$config.global_cost_decimal_points}</td>
			</tr>
			<tr>
				<td>{$r.info.mcode|default:'&nbsp;'}</td>
			</tr>
			{assign var=row_counter value=$row_counter+1}
			
			{if $row_counter>=$print_per_page or $smarty.foreach.f.last}
				{* Last Row *}
				{if $smarty.foreach.f.last}
					<tr class="header">
						<th colspan="7" align="right">Total</th>
						
						{* Opening *}
						<td align="right" class="{if $data.total.opening.qty<0}negative{/if}">{$data.total.opening.qty|qty_nf}</td>
						<td align="right" class="{if $data.total.opening.total_cost<0}negative{/if}">{$data.total.opening.total_cost|number_format:$config.global_cost_decimal_points}</td>
						
						{* Stock In *}
						<td align="right" class="{if $data.total.stock_in.qty<0}negative{/if}">{$data.total.stock_in.qty|qty_nf|ifzero:'&nbsp;'}</td>
						<td align="right" class="{if $data.total.stock_in.cost<0}negative{/if}">{$data.total.stock_in.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
						
						{* POS *}
						<td align="right" class="{if $data.total.pos.qty<0}negative{/if}">{$data.total.pos.qty|qty_nf|ifzero:'&nbsp;'}</td>
						<td align="right" class="{if $data.total.pos.cost<0}negative{/if}">{$data.total.pos.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
						
						{* Stock Out *}
						<td align="right" class="{if $data.total.stock_out.qty<0}negative{/if}">{$data.total.stock_out.qty|qty_nf|ifzero:'&nbsp;'}</td>
						<td align="right" class="{if $data.total.stock_out.cost<0}negative{/if}">{$data.total.stock_out.cost|number_format:$config.global_cost_decimal_points|ifzero:'&nbsp;'}</td>
						
						{* Closing *}
						<td align="right" class="{if $data.total.closing.qty<0}negative{/if}">{$data.total.closing.qty|qty_nf}</td>
						<td align="right" class="{if $data.total.closing.total_cost<0}negative{/if}">{$data.total.closing.total_cost|number_format:$config.global_cost_decimal_points}</td>
					</tr>
				{/if}
				
				</table>
				</div>
				{assign var=row_counter value=0}
			{/if}
		{/foreach}
	{/if}
{/if}

{include file="footer.tpl"}
