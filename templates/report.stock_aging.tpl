{*
8/26/2015 3:32 PM Andy
- Report re-write.

8/26/3:53 PM Andy
- Change stock icon to align absmiddle.

06/30/2020 02:42 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

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
option.opt_brand_group, .opt_branch_group{
	font-weight: bold;
}
option.opt_brand_group_items, .opt_branch_group_items{
	padding-left: 40px;
}
td.lastAgeRow{
	background-color: #fcf;
}
.tr_hover:hover{
	background-color: #98FB98 !important;
}

.report_table tr:nth-child(even) {
    background-color: #eee;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';


{literal}
var STOCK_AGING_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;

		// init calendar
		Calendar.setup({
	        inputField     :    "sku_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "t_added1",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });

	    Calendar.setup({
	        inputField     :    "sku_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "t_added2",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });
	},
	// function when user change filter sku added date
	filter_by_sku_added_date_changed: function(){
		var c = this.f['filter_by_sku_added_date'].checked;

		var container = $('span_filter_by_sku_added_date');

		if(c){
			container.show();
		}else{
			container.hide();
		}
	},
	// function when user change filter type
	filter_type_changed: function(){
		var filter_type = getRadioValue(this.f['filter_type']);

		$$('#div_all_filter_type div.div_filter_type').invoke('hide');
		$('div_filter_type-'+filter_type).show();
	},
	// function to do validation before submit
	validate_form: function(){
		// filter type
		var filter_type = getRadioValue(this.f['filter_type']);

		if(filter_type == 'cat'){
			// cat
			if(this.f['category_id'].value <= 0){
				if(!this.f['all_category'] || !this.f['all_category'].checked){
					alert('Please search and select category');
					return false;
				}
			}
		}else{
			// sku
			if($('sku_code_list').length <= 0){
				alert('Please search and add at least 1 sku.');
				return false;
			}
		}

		return true;
	},
	// function when user click show report
	submit_form: function(t){
		this.f['export_excel'].value = '';

		if(!this.validate_form())	return;

		if(t == 'excel') this.f['export_excel'].value = 1;

		var filter_type = getRadioValue(this.f['filter_type']);
		if(filter_type == 'sku'){
			for(var i=0; i<$('sku_code_list').length; i++){
			    $('sku_code_list').options[i].selected = true;
			}	
		}
		

		this.f.submit();
	},
	// function to show sku items inventory
	show_item_inventory: function(bid, sid){
		show_inventory('sku_item_id', sid, bid);
	},
	// function to close all popup
	close_popup: function(){
		var need_close = false;
		$$('.curtain_popup').each(function(ele){
			if(ele.style.display=='')	need_close = true;
		});
		if(need_close)	default_curtain_clicked();
	}
};

function curtain_clicked(){
	STOCK_AGING_REPORT.close_popup();
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
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" class="form" onSubmit="return false;">
			<input type="hidden" name="export_excel" />
			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="reportDataID" value="{$data.reportDataID}" />
			
			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
					<span>
						<b class="form-label">Branch</b> 
						<select class="form-control" name="branch_id">
							<option value="">-- All --</option>
							{foreach from=$branchesList key=bid item=b}
								{if !$branchGroupList.have_group.$bid}
									<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
								{/if}
							{/foreach}
							{if $branchGroupList.group}
								<optgroup label="Branch Group">
								{foreach from=$branchGroupList.group key=bgKey item=bg}
									{assign var=bgid value="bg,`$bgKey`"}
									<option value="{$bgid}" {if $smarty.request.branch_id eq $bgid}selected{/if} class="opt_branch_group">{$bg.code}</option>
									{foreach from=$bg.itemList key=bid item=b}
										<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if} class="opt_branch_group_items">{$b.code} - {$b.description}</option>
									{/foreach}
									
								{/foreach}
				
								</optgroup>
							{/if}
						</select>
					</span>
				{else}
				{/if}
				</div>
				
				<div class="col-md-4">
					<span>
						<b class="form-label">Stock at</b>
						<div class="form-inline">
							<select class="form-control" name="year">
								{foreach from=$appCore->getYearList() item=y}
									<option {if $smarty.request.year eq $y}selected {/if} value="{$y}">{$y}</option>
								{/foreach}
							</select>
							&nbsp;<select class="form-control" name="month">
								{foreach from=$appCore->monthsList key=m item=month}
									<option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
								{/foreach}
							</select>
						</div>
					</span>
				</div>
				
				<div class="col-md-4">
					<span>
						<b class="form-label">Filter have Stock Age at</b>
						<select class="form-control" name="stock_age">
							{foreach from=$stockAgeFilter key=k item=stock_age_label}
								<option value="{$k}" {if $smarty.request.stock_age eq $k}selected {/if}>{$stock_age_label}</option>
							{/foreach}
						</select>
					</span>
				</div>
			</div>
			
			<p>
				<span>
					<div class="form-label">
						<b>Filter Type: </b>
					<label><input type="radio" name="filter_type" value="sku" {if $smarty.request.filter_type eq 'sku'}checked {/if} onChange="STOCK_AGING_REPORT.filter_type_changed();" /> SKU</label>
					
					<label><input type="radio" name="filter_type" value="cat" {if $smarty.request.filter_type eq 'cat'}checked {/if} onChange="STOCK_AGING_REPORT.filter_type_changed();"/> Category</label>
					</div>
				</span>
			
				<div id="div_all_filter_type">
					<div id="div_filter_type-sku" class="div_filter_type" style="{if $smarty.request.filter_type ne 'sku'}display:none;{/if}">
						{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a'}
					</div>
			
					<div id="div_filter_type-cat" class="div_filter_type" style="{if $smarty.request.filter_type ne 'cat'}display:none;{/if}">
						{assign var=can_all value=false}
						{if $config.allow_all_sku_branch_for_selected_reports}
							{assign var=can_all value=true}
						{/if}
						{include file="category_autocomplete.tpl" all=$can_all}
					</div>	
				</div>
			</p>
			
			
			<p>
				<div class="row">
					<div class="col-md-4">
						<span>
							<b class="form-label">Vendor</b>
							<select class="form-control" name="vendor_id">
								<option value="">-- All --</option>
								{foreach from=$vendorList key=vid item=v}
									<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected {/if}>{$v.description}</option>
								{/foreach}
							</select>
						</span>
					</div>
					
					<div class="col-md-4">
						<span>
							<b class="form-label">Brand</b>
							<select class="form-control" name="brand_id">
								<option value='' {if $smarty.request.brand_id ===''}selected {/if}>-- All --</option>
								<option value="0" {if $smarty.request.brand_id ==='0'}selected {/if}>UN-BRANDED</option>
								{foreach from=$brandList key=brand_id item=r}
									<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected{/if}>{$r.description}</option>
								{/foreach}
					
								{if $brandGroupList}
									<optgroup label="Brand Group">
										{foreach from=$brandGroupList.group key=bgKey item=r}
											{assign var=bgid value="bg,`$bgKey`"}
											<option value="{$bgid}" {if $smarty.request.brand_id eq $bgid}selected{/if} class="opt_brand_group">{$r.code}</option>
											{foreach from=$r.itemList key=brand_id item=br}
												<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected{/if} class="opt_brand_group_items">{$br.code}</option>
											{/foreach}
										{/foreach}
									</optgroup>
								{/if}
								
							</select>
						</span>
					</div>
				
					<div class="col-md-4">
						<span>
							<b class="form-label">SKU Type</b>
							<select class="form-control" name="sku_type">
								<option value="">-- All --</option>
								{foreach from=$skuTypeList item=r}
								<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
								{/foreach}
							</select>
						</span>
					</div>
				</div>
			</p>
			
			<p>
				<div class="form-label form-inline">
					{if $BRANCH_CODE eq 'HQ'}
					{*<span>
						<label>
							<input type="checkbox" name="group_by_branch" value="1" {if $smarty.request.group_by_branch}checked{/if} /> <b>&nbsp;Group by branch</b>
						</label>
						
					</span>*}
				{/if}
			
				<span>
					<label>
						<input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b>
					</label>
					
				</span>
				</div>
			
				<span>
					<label>
						<div class="form-label">
							<input type="checkbox" name="filter_by_sku_added_date" value="1" {if $smarty.request.filter_by_sku_added_date}checked{/if} onChange="STOCK_AGING_REPORT.filter_by_sku_added_date_changed();" /> 
						<b>&nbsp;Filter SKU Added Date</b>
						</div>
					</label>
			
					<span id="span_filter_by_sku_added_date" style="{if !$smarty.request.filter_by_sku_added_date}display:none{/if}">
						&nbsp;&nbsp;
					<div class="form-inline">
						<b class="form-label">From&nbsp;</b> 
						<input class="form-control" size="10" type="text" name="sku_date_from" value="{$smarty.request.sku_date_from}" id="sku_date_from">
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
						
						<b class="form-label">&nbsp;To&nbsp;</b> 
						<input class="form-control" size="10" type="text" name="sku_date_to" value="{$smarty.request.sku_date_to}" id="sku_date_to">
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
					</div>
					</span>
					
				</span>
			</p>
			
			<button class="btn btn-primary" onClick="STOCK_AGING_REPORT.submit_form();">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info" onClick="STOCK_AGING_REPORT.submit_form('excel');">{#OUTPUT_EXCEL#}</button>
			{/if}
			<br>
			</form>
	</div>
</div>
{include file="popup.inventory_popups.tpl"}

<script>STOCK_AGING_REPORT.initialize();</script>
{/if}

{if $smarty.request.show_report and !$err}
	{if !$data.data}
		<p align=center>-- No Data --</p>
	{else}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$reportTitle}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
		{foreach from=$data.data.branchData key=bid item=branchData}
			<h2>{$branchesList[$bid].code}</h2>
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
								<th rowspan="2" width="20">No.</th>
								<th rowspan="2">ARMS Code</th>
								<th rowspan="2">MCode</th>
								<th rowspan="2">Art No.</th>
								<th rowspan="2">Description</th>
								<th rowspan="2">Added Date</th>
								{foreach from=$ageLabelList key=age item=ageLabel name=fage}
									<th colspan="2" nowrap>
										{$stockAgeFilter.$age} {if $smarty.foreach.fage.last and $age < 13}or above{/if}
										<br />
										<span class="small">({$appCore->getMonthLabel($ageLabel.m)} {$ageLabel.y} )</span>
									</th>
								{/foreach}
								<th rowspan="2">Balance at {$appCore->getMonthLabel($smarty.request.month)} {$smarty.request.year}</th>
							</tr>
							<tr class="header">
								{foreach from=$ageLabelList key=age item=ageLabel}
									<th>Qty</th>
									<th>%</th>
								{/foreach}
							</tr>
						</thead>
						{foreach from=$branchData.si_list key=sid item=r name=fsi}
							{getSKUItems sid=$sid assign=si}
							<tbody class="fs-08">
								<tr class="tr_hover">
									<td>{$smarty.foreach.fsi.iteration}</td>
									<td nowrap>
										{if !$no_header_footer}
											<a href="javascript:void(STOCK_AGING_REPORT.show_item_inventory('{$bid}', '{$sid}'))" class="noprint"><img src="/ui/icons/package.png" title="View Inventory" align="absmiddle" /></a>
										{/if}
										{$si.sku_item_code}
									</td>
									<td>{$si.mcode|default:'-'}</td>
									<td>{$si.artno|default:'-'}</td>
									<td>{$si.description|default:'-'}</td>
									<td>{$si.added|default:'-'}</td>
									{foreach from=$ageLabelList key=age item=ageLabel name=fage}
										<td align="right" {if $smarty.foreach.fage.last}class="lastAgeRow"{/if}>{$r.ageList.$age.qty}</td>
										<td align="right" {if $smarty.foreach.fage.last}class="lastAgeRow"{/if}>
											{if $r.ageList.$age.qty > 0}
												{assign var=qtyPer value=$r.ageList.$age.qty/$r.to_qty*100}
												{$qtyPer|number_format:2}%
											{else}
												&nbsp;
											{/if}
										</td>
									{/foreach}
			
									<td align="right">{$r.to_qty}</td>
								</tr>
							</tbody>
							{if $smarty.foreach.fsi.iteration%20 eq 0}
								{php}
									ob_flush();
								{/php}
							{/if}
						{/foreach}
						<tr class="header">
							<th colspan="6" align="right">Total</th>
							{foreach from=$ageLabelList key=age item=ageLabel name=fage}
								<th align="right">{$branchData.total.ageList.$age.qty}</th>
								<th align="right">
									{if $branchData.total.ageList.$age.qty > 0}
										{assign var=qtyPer value=$branchData.total.ageList.$age.qty/$branchData.total.to_qty*100}
										{$qtyPer|number_format:2}%
									{else}
										&nbsp;
									{/if}
								</th>
							{/foreach}
							<th align="right">{$branchData.total.to_qty}</th>
						</tr>
					</table>
				</div>
			</div>
		</div>
		{/foreach}
	{/if}
{/if}

<p align=center>
{if !$print_excel}
{if $prev}
<button class="btn btn-primary" name="prev_btn" title="Previous Page" onclick="page_navigation('{$prev}', '')">Previous</button>
{/if}
{if $next}

<button class="btn btn-primary" name="next_btn" title="Next Page" onclick="page_navigation('', '{$next}')">Next</button></td>
{/if}
{/if}
</p>

{include file=footer.tpl}
