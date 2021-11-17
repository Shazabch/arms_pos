{*
11/20/2018 2:48 PM Andy
- Change this report to show all sku_type of sku.
- Enhanced to have Trade Discount Percent, Gross Amt and Receipt Discount
- Enhanced to separate sales by "Consignment SKU - Brand Table", "Consignment SKU - Not Using Brand Table" and "Not Consignment SKU".

12/13/2018 9:29 AM Justin
- Enhanced the report to have option to view data by Brand or Vendor.

1/11/2019 6:16 PM Justin
- Enhanced to have department filter.

1/14/2019 11:07 AM Justin
- Added can filter "All" for department.

06/30/2020 02:25 PM Sheila
- Updated button css.
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

.brand_title{
	background-color: #B8FFFE;
}
.div_brand{
	padding-bottom: 20px;
}

.col_item_code{
	width: 100px;
}

.col_qty_amt{
	width: 50px;
}

.col_disc{
	width:70px;
}

.col_price_type{
	background-color: #ccf;
}

</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
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
	// function to check form
	validate_form: function(){
		if(this.f['branch_id']){
			if(this.f['branch_id'].value<=0){
				alert('Please select branch.');
				return false;
			}
		}
		
		if(!this.f['data_type'].value){
			alert('Please select show by Brand or Vendor.');
			return false;
		}
		
		if(this.f['data_type'].value == "brand" && !this.f['brand_id'].value){
			alert('Please select Brand.');
			return false;
		}
		
		if(this.f['data_type'].value == "vendor" && !this.f['vendor_id'].value){
			alert('Please select Vendor.');
			return false;
		}
		
		return true;
	},
	// function when users click on show report or export
	submit_form: function(t){
		this.f['export_excel'].value = 0;
		if(t == 'excel')	this.f['export_excel'].value = 1;
		
		// Check form
		if(!this.validate_form())	return false;
		
		this.f.submit();
	},
	
	onchange_data_type: function(obj){
		if(obj.value == "vendor"){
			this.f['brand_id'][0].update("-- All --");
			this.f['vendor_id'][0].update("-- Please Select --");
		}else if(obj.value == "brand"){
			this.f['brand_id'][0].update("-- Please Select --");
			this.f['vendor_id'][0].update("-- All --");
		}else{
			this.f['brand_id'][0].update("-- Please Select --");
			this.f['vendor_id'][0].update("-- Please Select --");
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
		<ul class="errmsg">
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}

{if !$no_header_footer}
<div class="noprint stdframe">
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="load_report" value="1" />
			<input type="hidden" name="export_excel" />
			
			<div class="row">
				{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-3">
					<span>
						<b class="form-label mt-2">Branch</b>
						<select class="form-control" name="branch_id">
							<option value="">-- Please Select --</option>
							{foreach from=$branches key=bid item=r}
								{if $config.sales_report_branches_exclude}
									{if in_array($r.code,$config.sales_report_branches_exclude)}
										{assign var=skip_this_branch value=1}
									{else}
										{assign var=skip_this_branch value=0}
									{/if}
								{/if}
							
								{if !$branches_group.have_group.$bid and !$skip_this_branch}
									<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/if}
							{/foreach}
							
							{if $branches_group.header}
								{foreach from=$branches_group.header key=bgid item=bg}					
									<optgroup label='{$bg.code}'>
										{foreach from=$branches_group.items.$bgid item=r}
											{if $config.sales_report_branches_exclude}
												{if in_array($r.code,$config.sales_report_branches_exclude)}
													{assign var=skip_this_branch value=1}
												{else}
													{assign var=skip_this_branch value=0}
												{/if}
											{/if}
										
											{if !$skip_this_branch}
												<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
											{/if}
										{/foreach}
									</optgroup>
								{/foreach}
							{/if}
						</select>
					</span>
				</div>
			{/if}
			
		
				<div class="col-md-3">
					<b class="form-label mt-2">Show By</b>
				<select class="form-control" name="data_type" onchange="BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT.onchange_data_type(this);">
					<option value="">-- Please Select --</option>
					{foreach from=$data_type_list key=dt item=dt_name}
						<option value="{$dt}" {if $dt eq $smarty.request.data_type}selected {/if}>{$dt_name}</option>
					{/foreach}
				</select>
				</div>
			
					<div class="col-md-3">
						<b class="form-label mt-2">Brand</b>
					<select class="form-control" name="brand_id">
						<option value="">-- {if $smarty.request.data_type eq 'brand'}Please Select{else}All{/if} --</option>
						{foreach from=$brands key=brand_id item=r}
							{if !$r.brgroup_id}
								<option value="{$brand_id}" {if $brand_id eq $smarty.request.brand_id}selected {/if}>{if $r.code}{$r.code} - {/if}{$r.description}</option>
							{/if}
						{/foreach}
						
						{if $brand_groups}
							<optgroup label='Brand Group'>
							{foreach from=$brand_groups key=brgroup_id item=r}
								<option class="bg" value="{$brgroup_id*-1}" {if $smarty.request.brand_id eq $brgroup_id*-1}selected {/if}>{$r.header.code} - {$r.header.description}</option>
								
								{foreach from=$r.items key=brand_id item=br}
									<option class="bg_item" value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{if $brands.$brand_id.code}{$brands.$brand_id.code} - {/if}{$brands.$brand_id.description}</option>
								{/foreach}
							{/foreach}
						{/if}
					</select>
					</div>
				

					<div class="col-md-3">
						<b class="form-label mt-2">Vendor</b>
					<select class="form-control" name="vendor_id">
						<option value="">-- {if $smarty.request.data_type eq 'vendor'}Please Select{else}All{/if} --</option>
						{foreach from=$vendors key=vendor_id item=r}
							<option value="{$vendor_id}" {if $vendor_id eq $smarty.request.vendor_id}selected {/if}>{if $r.code}{$r.code} - {/if}{$r.description}</option>
						{/foreach}
					</select>
					</div>
		
			
			
					<div class="col-md-3">
						<b class="form-label mt-2">Department</b>
					<select class="form-control" name="department_id">
						<option value="" {if !$smarty.request.department_id}selected{/if}>-- All --</option>
						{foreach from=$departments item=r}
							<option value="{$r.id}" {if $smarty.request.department_id eq $r.id}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
					</div>
				
					<div class="col-md-3">
						<b class="form-label mt-2">Date From</b>
					 <div class="form-inline">
						<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=12 />
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
					 </div>
					</div>
					
					<div class="col-md-3">
						<b class="form-label mt-2">To</b>
					<div class="form-inline">
						<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=12 />
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
					</div>
					</div>
			</div>
			
			<p>
				<span>
					<div class="alert alert-primary rounded mt-2" style="max-width: 350px;">
						<b>
							Note:<br />
							* Brand is mandatory if show report by Brand, whereas Vendor is optional.<br />
							* Vendor is mandatory if show report by Vendor, whereas Brand is optional.
						</b>
					</div>
				</span>
			</p>
			
			<p>
				<input class="btn btn-primary mt-2" type="button" value='Show Report' onClick="BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT.submit_form();" /> &nbsp;&nbsp;
		
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info mt-2" name="output_excel" onClick="BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
			</p>
		</form>
	</div>
</div>
</div>
{/if}

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
		
		{foreach from=$data.data_list key=data_id item=d}
			<div class="div_brand">
				<h4 class="brand_title">
					{if $smarty.request.data_type eq 'brand'}
						{if $brands.$data_id.code}{$brands.$data_id.code} - {/if}{$brands.$data_id.description}
					{else}
						{if $vendors.$data_id.code}{$vendors.$data_id.code} - {/if}{$vendors.$data_id.description}
					{/if}
				</h4>
				
				<div class="card mx-3">
					<div class="card-body">
						<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
							<thead class="bg-gray-100">
								<tr class="header">
									<th rowspan="2" class="col_item_code">ARMS Code</th>
									<th rowspan="2" class="col_item_code">MCode</th>
									<th rowspan="2" class="col_item_code">Art No</th>
									<th rowspan="2" class="col_item_code">{$config.link_code_name}</th>
									<th rowspan="2">Description</th>
									<th colspan="2" width="100">Trade Discount</th>
									<th rowspan="2" class="col_qty_amt">Qty</th>
									<th rowspan="2" class="col_qty_amt">Gross Amt {if !$no_header_footer}[<a href="javascript:void(alert('This is Gross Sales Amount (Before item discount and receipt discount)'))">?</a>]{/if}</th>
									<th colspan="{count var=$d.total.discount_list|default:1}">Item Discount (%)</th>
									<th rowspan="2" class="col_qty_amt">Total Item Discount</th>
									<th rowspan="2" class="col_qty_amt">Receipt / Mix & Match Discount</th>
									<th rowspan="2" class="col_qty_amt">Nett Amt {if !$no_header_footer}[<a href="javascript:void(alert('This is Nett Sales Amount (After item discount and receipt discount)'))">?</a>]{/if}</th>
								</tr>
								
								<tr class="header">
									<th width="50">Code</th>
									<th width="50">Rate(%)</th>
									
									{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
										<th class="col_disc">{$disc_percent} %</th>
									{foreachelse}
										<th class="col_disc">N/A</th>
									{/foreach}
								</tr>
								
							</thead>
							{* Items *}
							{foreach from=$section_type_list key=section_type item=section_type_desc}
								{if $d.$section_type}
									<tr class="header">
										{if $d.total.discount_list}
											{count var=$d.total.discount_list offset=12 assign=cols}
										{else}
											{assign var=cols value=13}
										{/if}
										<td colspan="{$cols}">{$section_type_desc}</td>
									</tr>
									
									{foreach from=$d.$section_type.price_type_list key=price_type item=price_type_data}
										{foreach from=$price_type_data.items_list key=sid item=r}						
											<tbody class="fs-08">
												<tr>
													<td>{$data.si_info.$sid.sku_item_code}</td>
													<td>{$data.si_info.$sid.mcode|default:'-'}</td>
													<td>{$data.si_info.$sid.artno|default:'-'}</td>
													<td>{$data.si_info.$sid.link_code|default:'-'}</td>
													<td>{$data.si_info.$sid.description|default:'-'}</td>
													
													{* Price Type *}
													<td align="center" class="col_price_type">{$price_type|default:'-'}</td>
													<td align="center">{$price_type_data.rate|default:'-'}</td>
													
													{* Qty *}
													<td align="right">{$r.qty|qty_nf}</td>
													
													{* Gross Amt *}
													<td align="right">{$r.gross_amt|number_format:2}</td>
			
													{* Item Discount *}								
													{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
														<td align="right">{$r.discount_list.$disc_percent|number_format:2}</td>
													{foreachelse}
														<td align="right">-</td>
													{/foreach}
													
													{* Total Item Discount *}
													<td align="right">{$r.total.disc_amt|number_format:2}</td>
													
													{* Receipt / Mix and Match Discount *}
													<td align="right">{$r.discount2|number_format:2}</td>
													
													{* Nett Amt *}
													<td align="right">{$r.amt|number_format:2}</td>
												</tr>
											</tbody>
										{/foreach}
										
										{* Price Type Total *}
										<tr class="header">
											<th align="right" colspan="5">Price Type Total</th>
											<td align="center">{$price_type|default:'-'}</td>
											<td align="center">-</td>
											
											{* Qty *}
											<td align="right">{$price_type_data.total.qty|qty_nf}</td>
											
											{* Gross Amt *}
											<td align="right">{$price_type_data.total.gross_amt|number_format:2}</td>
												
											{* Item Discount *}								
											{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
												<td align="right">{$price_type_data.total.discount_list.$disc_percent|number_format:2}</td>
											{foreachelse}
												<td align="right">-</td>
											{/foreach}
											
											{* Total Item Discount *}
											<td align="right">{$price_type_data.total.disc_amt|number_format:2}</td>
											
											{* Receipt / Mix and Match Discount *}
											<td align="right">{$price_type_data.total.discount2|number_format:2}</td>
											
											{* Nett Amt *}
											<td align="right">{$price_type_data.total.amt|number_format:2}</td>
										</tr>
									{/foreach}
								{/if}
							{/foreach}
							
							
							
							{* Brand Total *}
							<tr class="header">
								<th align="right" colspan="5">Brand Total</th>
								<td align="center" colspan="2">-</td>
								
								{* Qty *}
								<td align="right">{$d.total.qty|qty_nf}</td>
								
								{* Gross Amt *}
								<td align="right">{$d.total.gross_amt|number_format:2}</td>
								
								{* Item Discount *}								
								{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
									<td align="right">{$d.total.discount_list.$disc_percent|number_format:2}</td>
								{foreachelse}
									<td align="right">-</td>
								{/foreach}
								
								{* Total Item Discount *}
								<td align="right">{$d.total.disc_amt|number_format:2}</td>
								
								{* Receipt / Mix and Match Discount *}
								<td align="right">{$d.total.discount2|number_format:2}</td>
								
								{* Nett Amt *}
								<td align="right">{$d.total.amt|number_format:2}</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		{/foreach}
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">Summary</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
		
		{foreach from=$data.data_list key=data_id item=d}
			<div class="div_brand">
				<h4 class="brand_title">
					{if $smarty.request.data_type eq 'brand'}
						{if $brands.$data_id.code}{$brands.$data_id.code} - {/if}{$brands.$data_id.description}
					{else}
						{if $vendors.$data_id.code}{$vendors.$data_id.code} - {/if}{$vendors.$data_id.description}
					{/if}
					&nbsp;[Summary]
				</h4>
				
				<div class="card mx-3">
					<div class="card-body">
						<div class="table-responsive">
							<table class="report_table table mb-0 text-md-nowrap  table-hover">
								<thead class="bg-gray-100">
									<tr class="header">
										<th rowspan="2" width="50">Price Type</th>
										<th rowspan="2" class="col_qty_amt">Qty</th>
										<th rowspan="2" class="col_qty_amt">Gross Amt {if !$no_header_footer}[<a href="javascript:void(alert('This is Gross Sales Amount (Before item discount and receipt discount)'))">?</a>]{/if}</th>
										<th colspan="{count var=$d.total.discount_list|default:1}">Item Discount (%)</th>
										<th rowspan="2" class="col_qty_amt">Total Item Discount</th>
										<th rowspan="2" class="col_qty_amt">Receipt / Mix & Match Discount</th>
										<th rowspan="2" class="col_qty_amt">Nett Amt {if !$no_header_footer}[<a href="javascript:void(alert('This is Nett Sales Amount (After item discount and receipt discount)'))">?</a>]{/if}</th>
									</tr>
									
									<tr class="header">
										{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
											<th class="col_disc">{$disc_percent} %</th>
										{foreachelse}
											<th class="col_disc">N/A</th>
										{/foreach}
									</tr>
								</thead>
								
								{foreach from=$section_type_list key=section_type item=section_type_desc}
									{if $d.$section_type}
										<tr class="header">
											{if $d.total.discount_list}
												{count var=$d.total.discount_list offset=6 assign=cols}
											{else}
												{assign var=cols value=7}
											{/if}
											<td colspan="{$cols}">{$section_type_desc}</td>
										</tr>
										
										{foreach from=$d.$section_type.price_type_list key=price_type item=price_type_data}
											{* Price Type Total *}
											<tr>
												<td align="center">{$price_type|default:'-'}</td>
												
												{* Qty *}
												<td align="right">{$price_type_data.total.qty|qty_nf}</td>
												
												{* Gross Amt *}
												<td align="right">{$price_type_data.total.gross_amt|number_format:2}</td>
													
												{* Item Discount *}								
												{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
													<td align="right">{$price_type_data.total.discount_list.$disc_percent|number_format:2}</td>
												{foreachelse}
													<td align="right">-</td>
												{/foreach}
												
												{* Total Item Discount *}
												<td align="right">{$price_type_data.total.disc_amt|number_format:2}</td>
												
												{* Receipt / Mix and Match Discount *}
												<td align="right">{$price_type_data.total.discount2|number_format:2}</td>
												
												{* Nett Amt *}
												<td align="right">{$price_type_data.total.amt|number_format:2}</td>
											</tr>
										{/foreach}
									{/if}
								{/foreach}
								
								
								
								{* Brand Total *}
								<tr class="header">
									<th align="center">Total</th>
									
									{* Qty *}
									<td align="right">{$d.total.qty|qty_nf}</td>
									
									{* Gross Amt *}
									<td align="right">{$d.total.gross_amt|number_format:2}</td>
									
									{* Item Discount *}								
									{foreach from=$d.total.discount_list key=disc_percent item=disc_amt}
										<td align="right">{$d.total.discount_list.$disc_percent|number_format:2}</td>
									{foreachelse}
										<td align="right">-</td>
									{/foreach}
									
									{* Total Item Discount *}
									<td align="right">{$d.total.disc_amt|number_format:2}</td>
									
									{* Receipt / Mix and Match Discount *}
									<td align="right">{$d.total.discount2|number_format:2}</td>
									
									{* Nett Amt *}
									<td align="right">{$d.total.amt|number_format:2}</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
	{/if}
{/if}

{if !$no_header_footer}
<script>BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT.initialise();</script>
{/if}

{include file='footer.tpl'}