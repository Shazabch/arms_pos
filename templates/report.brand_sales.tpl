{*
4/18/2015 10:43 AM Andy
- Enhanced to have report title.

4:28 PM 4/29/2015 Andy
- Change the remark for better understanding.

12/12/2016 10:26 AM Andy
- Enhanced to have information popup for In & Out.

6/7/2019 2:55 PM William
- Added two column Gross sales and Discount.
- Fix column colspan.

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

12/02/2020 3:35 PM Rayleen
- tpl file add total per brand type
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

.not_up_to_date{
	color: red;
}

.brand_row td{
	background-color: #E0FFFF;
}
.brand_total_row{
	background-color: #eaeaea;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var BRAND_REPORT = {
	f: undefined,
	initialize: function(){
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
	// function to validate form before submit
	check_form: function(){
		if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch');
				return false;
			}
		}
						
		return true;
	},
	// function when user click show report
	submit_form: function(t){
		if(!this.check_form())	return false;
		
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		this.f.submit();
	},
	// function when user click print form
	do_print: function(){
		alert('Note: This report should be printed on A4 Lanscape');
		window.print();
	},
	// function when user click [?] for In
	show_balance_in_info: function(){
		alert('Including:\n- Adjustment In, GRN, Stock Take Adjust In');
	},
	// function when user click [?] for Out
	show_balance_out_info: function(){
		alert('Including:\n- Adjustment Out, GRA, DO, Stock Take Adjust Out');
	}
};
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
		<div><div class="errmsg"><ul>
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
			</ul></div></div>
	</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="noprint stdframe" style="background-color:#fff" method="post" onSubmit="return false;">
			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="export_excel" />
			
		<div class="row">
			<div class="col-md-4">
				{if $BRANCH_CODE eq 'HQ'}
			{* Branch *}
			<b class="form-label">Branch </b>
			<select class="form-control" name="branch_id">
				<option value="">-- Please Select --</option>
				{foreach from=$branch_list key=bid item=b}
					{if !$branch_group.have_group.$bid}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
					{/if}
				{/foreach}
				{if $branch_group}
					{foreach from=$branch_group.header key=bgid item=bg}
						<optgroup label="{$bg.code}">
							{foreach from=$branch_group.items.$bgid key=bid item=r}
								<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$branch_list.$bid.code} - {$branch_list.$bid.description}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/if}
			</select>
		{/if}
			</div>
		
		<div class="col-md-4">
			{* Brand *}
		<b class="form-label">Brand</b>
		<select class="form-control" name="brand_id">
			<option value="">-- All --</option>
			{foreach from=$brand_list key=brand_id item=r}
				{if !$brand_group.have_group.$brand_id}
					<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
				{/if}
			{/foreach}
			<optgroup label="Brand Group">
				{foreach from=$brand_group.header key=bgid item=bg}
					{if $brand_group.items.$bgid}
						{capture assign="brground_id"}group,{$bgid}{/capture}
						<option class="bg" value="group,{$bgid}" {if $brground_id eq $smarty.request.brand_id}selected {/if}>{$bg.description}</option>
						{foreach from=$brand_group.items.$bgid key=brand_id item=r}
							<option class="bg_item" value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$brand_list.$brand_id.description}</option>
						{/foreach}
					{/if}			
				{/foreach}
			</optgroup>
		</select>
		</div>
		
		
			<div class="col-md-4">
				{* SKU Type *}
			<b class="form-label">SKU Type</b>
			<select class="form-control" name="sku_type">
				<option value="">-- All --</option>
				{foreach from=$sku_type_list item=r}
					<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
				{/foreach}
			</select>
			</div>
			
			<div class="col-md-4">
				{* Price Type *}
			<b class="form-label">Price Type</b>
			<select class="form-control" name="price_type">
				<option value="">-- All --</option>
				{foreach from=$price_type_list item=r}
					<option value="{$r.price_type}" {if $smarty.request.price_type eq $r.price_type}selected {/if}>{$r.price_type}</option>
				{/foreach}
			</select>
			</div>
			
			<div class="col-md-4">
				{* Department *}
			<b class="form-label">Department</b>
			<select class="form-control" name="dept_id">
				<option value="">-- All --</option>
				{foreach from=$dept_list item=r}
					<option value="{$r.id}" {if $smarty.request.dept_id eq $r.id}selected {/if}>{$r.description}</option>
				{/foreach}
			</select>
			</div>
		
		</div>
			
		<div class="row">
			<div class="col-md-4">
				{* Date From *}
			<b class="form-label">Date From</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="23" />
			&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</div>
			</div>
			
		<div class="col-md-4">
			{* Date To *}
			<b class="form-label">To</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="23" />
			&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
			</div>
		</div>
			
			<div class="col-md-4">
			<div class="from-inline form-label mt-4">
				{* Group Monthly *}
				<input type="checkbox" name="by_monthly" id="chx_by_monthly" {if $smarty.request.by_monthly}checked {/if} value="1" /> <label for="chx_by_monthly"><b>&nbsp;Group Monthly</b></label>&nbsp;&nbsp; 
				
				<!-- Group by SKU -->
				<input type="checkbox" name="group_by_sku" id="chx_group_by_sku" {if $smarty.request.group_by_sku}checked {/if} value="1" /> <label for="chx_group_by_sku"><b>&nbsp;Group by SKU</b></label>&nbsp;&nbsp;
				
				<!-- Show Balance -->
				<input type="checkbox" name="show_balance" id="chx_show_bal" {if $smarty.request.show_balance}checked {/if} value="1" /> <label for="chx_show_bal"><b>&nbsp;Show Balance</b></label>&nbsp;&nbsp; 
			</div>
			</div>
		
		</div>
			<p>
				<div class="row">
				<div class="col-4">
					{* Report Type *}
					<b class="form-label">Report Type: </b>
					<label><input name="report_type" type="radio" value="qty" {if $smarty.request.report_type ne 'amt'}checked {/if} />&nbsp;Sales Qty</label>
					<label><input name="report_type" type="radio" value="amt" {if $smarty.request.report_type eq 'amt'}checked {/if} />&nbsp;Sales Amount</label>
					
				</div>
				
				<div class="col-4">
					<input type="button" class="btn btn-info" value='Show Report' onClick="BRAND_REPORT.submit_form();" /> 
				<input type="button" class="btn btn-primary" value="Print" onclick="BRAND_REPORT.do_print();" />
				
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<input type=button class="btn btn-info" value="Export to Excel" onclick="BRAND_REPORT.submit_form('excel');" />
				{/if}
				</div>
				</div>
			</p>
		</form>
	</div>
</div>
<script type="text/javascript">BRAND_REPORT.initialize();</script>
{/if}

{if $smarty.request.show_report}
	{if !$data}
		<br />
		* No Data *
	{else}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	
		<div class="alert alert-primary mx-3 rounded">
			<ul style="list-style-type: none;">
				<li> Item mark with <span class="not_up_to_date">*</span> is not up to date, stock balance may incorrect.</li>
			</ul>
		</div>
		
		{assign var=show_balance value=$smarty.request.show_balance}
		{assign var=show_type value=$smarty.request.report_type}
		{assign var=count_colspan value=11}
		{if $smarty.request.by_monthly}
			{* Show by Monthly *}
			{foreach from=$date_from_to_list key=y item=from_to_info}
				{if $from_to_info.got_data}
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$y}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
						</div>
					</div>
				</div>
					
					<div class="card mx-3">
						<div class="card-body">
							<div class="table-responsive">
								<table width="100%" class="report_table small table mb-0 text-md-nowrap  table-hover">
									<thead class="bg-gray-100">
										<tr class="header">
											<th>&nbsp;</th>
											<th>ARMS Code</th>
											<th title="SKU Type">S.T</th>
											<th>Art No/MCode</th>
											<th>SKU Description</th>
											<th title="Price Type">P.T</th>
											
											{* Opening *}
											{if $show_balance}
												{assign var=count_colspan value=$count_colspan+1}
												<th>Opening<br />Qty</th>
											{/if}
											
											{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
												{capture assign=count_month_list}{count var=$from_to_info.date_info.ym_list}{/capture}
												<th>{$month_list[$ym_info.m]}</th>
											{/foreach}
											
											<th title="Total Sales Qty">T.Qty</th>
											<th title="Gross Amount">Gross Amount</th>
											<th title="Total Discount of the item">Discount</th>
											<th title="Total Sales Amount">T.Amount</th>
											
											{if $show_balance}
												{assign var=count_colspan value=$count_colspan+3}
												{* In *}
												<th title="In Qty">In [<a href="javascript:void(BRAND_REPORT.show_balance_in_info())">?</a>]</th>
												
												{* Out *}
												<th title="Out Qty">Out [<a href="javascript:void(BRAND_REPORT.show_balance_out_info())">?</a>]</th>
												
												{* Closing *}
												<th title="Closing Qty">Closing</th>
											{/if}
											
											{* AVG Price *}
											<th title="Average Selling Price">AVG Price</th>
										</tr>
									</thead>
									
									{assign var=fitem value=0}
									{foreach from=$from_to_info.brand_info.brand_id_list key=brand_id item=brand_sales_info}
										{* Loop for each brand *}
										<tr class="brand_row">
											<td colspan="{$count_colspan+$count_month_list}">{$brand_list.$brand_id.code} - {$brand_list.$brand_id.description}</td>
										</tr>
										
										{foreach from=$brand_sales_info.item_list key=item_key item=dummy}
											{assign var=fitem value=$fitem+1}
									
											{assign var=item_sales_info value=$data.item_sales.$item_key}
											{assign var=item_info value=$data.item_info.$item_key.info}
											{assign var=item_total_info value=$data.item_total.$item_key.by_year.$y}
											{assign var=balance_info value=$from_to_info.balance_info.$item_key.total}
											{capture assign=count_price_type}{count var=$from_to_info.item_price_type_list.$item_key}{/capture}
										
											<tr>
												<td rowspan="{$count_price_type}">{$fitem}.</td>
												<td rowspan="{$count_price_type}" nowrap>
													{$item_info.sku_item_code}
													{if $data.item_info.$item_key.changed}<span class="not_up_to_date">*</span>{/if}
													{if $data.item_info.$item_key.multi_vendor}<span class="got_multi_vendor">*</span>{/if}
												</td>
												<td rowspan="{$count_price_type}">{$item_info.sku_type|substr:0:1}</td>
												<td rowspan="{$count_price_type}">{$item_info.artno_mcode|default:'-'}</td>
												<td rowspan="{$count_price_type}">{$item_info.description|default:'-'}</td>
												
												{foreach from=$from_to_info.item_price_type_list.$item_key item=price_type name=fds}
													{if $smarty.foreach.fds.first}
														{* Price Type *}
														{if $price_type eq $no_price_type}
															<td>-</td>
														{else}
															<td>{$price_type}</td>
														{/if}
														
														{* Opening *}
														{if $show_balance}
															<td align="right" rowspan="{$count_price_type}">{$balance_info.opening.qty|qty_nf}</td>
														{/if}
														
														{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
															{assign var=daily_sales value=$item_sales_info.$ym.$price_type}
															<td align="right">
																{if $show_type eq 'qty'}
																	{$daily_sales.total.$show_type|qty_nf|ifzero:'&nbsp;'}
																{else}
																	{$daily_sales.total.$show_type|number_format:2|ifzero:'&nbsp;'}
																{/if}
															</td>
														{/foreach}
														
														{* Total Sales Qty *}
														<td align="right" rowspan="{$count_price_type}">{$item_total_info.qty|qty_nf}</td>
														{* Total Discount and Gross Amount *}
														
														
														<td align="right" rowspan="{$count_price_type}">{$item_total_info.gross_amt|number_format:2}</td>
														<td align="right" rowspan="{$count_price_type}">{$item_total_info.discount|number_format:2}</td>
														{* Total Sales Amt *}
														<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt|number_format:2}</td>
														
														{if $show_balance}
															{* In *}
															<td align="right" rowspan="{$count_price_type}">{$balance_info.in.qty|qty_nf}</td>
															
															{* Out *}
															<td align="right" rowspan="{$count_price_type}">{$balance_info.out.qty|qty_nf}</td>
															
															{* Closing *}
															<td align="right" rowspan="{$count_price_type}">{$balance_info.closing.qty|qty_nf}</td>
														{/if}
														
														{* AVG Selling *}
														<td align="right" rowspan="{$count_price_type}">{$item_total_info.avg_selling|number_format:2}</td>
													{/if}
												{/foreach}
											</tr>
											
											{foreach from=$from_to_info.item_price_type_list.$item_key item=price_type name=fds}
												{if !$smarty.foreach.fds.first}
													<tr>
														{* Price Type *}
														{if $price_type eq $no_price_type}
															<td>-</td>
														{else}
															<td>{$price_type}</td>
														{/if}
														
														{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
															{assign var=daily_sales value=$item_sales_info.$ym.$price_type}
															<td align="right">
																{if $show_type eq 'qty'}
																	{$daily_sales.total.$show_type|qty_nf|ifzero:'&nbsp;'}
																{else}
																	{$daily_sales.total.$show_type|number_format:2|ifzero:'&nbsp;'}
																{/if}
															</td>
														{/foreach}
													</tr>
													
												{/if}
											{/foreach}
										{/foreach}
			
										{* Total Per Brand *}
										<tr class="brand_total_row">
								
											{assign var=balance_info_brand value=$from_to_info.balance_info.brand.$brand_id}
											{assign var=sales_brand_info value=$from_to_info.group_total.sales}
											
											<td align="right" colspan="6"><b>Brand Total</b></td>
											
											{* Opening *}
											{if $show_balance}
												<td align="right">{$balance_info_brand.opening.qty|qty_nf}</td>
											{/if}
											
											{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
												<td align="right">
													{if $show_type eq 'qty'}
														{$sales_brand_info.$ym.$brand_id.$show_type|qty_nf|ifzero:'&nbsp;'}
													{else}
														{$sales_brand_info.$ym.$brand_id.$show_type|number_format:2|ifzero:'&nbsp;'}
													{/if}
												</td>
											{/foreach}
											
											{* Total Sales Qty *}
											<td align="right">{$sales_brand_info.$brand_id.qty|qty_nf}</td>
											{* Total Discount and Gross Amount *}			
											<td align="right">{$sales_brand_info.$brand_id.gross_amt|number_format:2}</td>
											<td align="right">{$sales_brand_info.$brand_id.discount|number_format:2}</td>
											{* Total Sales Amt *}
											<td align="right">{$sales_brand_info.$brand_id.amt|number_format:2}</td>
											
											{if $show_balance}
												{* In *}
												<td align="right">{$balance_info_brand.in.qty|qty_nf}</td>
												
												{* Out *}
												<td align="right">{$balance_info_brand.out.qty|qty_nf}</td>
												
												{* Closing *}
												<td align="right">{$balance_info_brand.closing.qty|qty_nf}</td>
											{/if}
											
											{* AVG Selling *}
											<td align="right">{$sales_brand_info.total.avg_selling|number_format:2}</td>
			
										</tr>
			
									{/foreach}
									
									{* Total *}
									<tr class="header">
										{assign var=balance_info value=$from_to_info.balance_info.total.total}
										{assign var=sales_info value=$from_to_info.group_total.sales}
										
										<td align="right" colspan="6"><b>Total</b></td>
										
										{* Opening *}
										{if $show_balance}
											<td align="right">{$balance_info.opening.qty|qty_nf}</td>
										{/if}
										
										{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
											<td align="right">
												{if $show_type eq 'qty'}
													{$sales_info.$ym.$show_type|qty_nf|ifzero:'&nbsp;'}
												{else}
													{$sales_info.$ym.$show_type|number_format:2|ifzero:'&nbsp;'}
												{/if}
											</td>
										{/foreach}
										
										{* Total Sales Qty *}
										<td align="right">{$sales_info.total.qty|qty_nf}</td>
										{* Total Discount and Gross Amount *}			
										<td align="right">{$sales_info.total.gross_amt|number_format:2}</td>
										<td align="right">{$sales_info.total.discount|number_format:2}</td>
										{* Total Sales Amt *}
										<td align="right">{$sales_info.total.amt|number_format:2}</td>
										
										{if $show_balance}
											{* In *}
											<td align="right">{$balance_info.in.qty|qty_nf}</td>
											
											{* Out *}
											<td align="right">{$balance_info.out.qty|qty_nf}</td>
											
											{* Closing *}
											<td align="right">{$balance_info.closing.qty|qty_nf}</td>
										{/if}
										
										{* AVG Selling *}
										<td align="right">{$sales_info.total.avg_selling|number_format:2}</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		{else}
			{* Show by normal / daily *}
			{foreach from=$date_from_to_list key=ym item=from_to_info}
				{assign var=y value=$from_to_info.y}
				{assign var=m value=$from_to_info.m}
				{assign var=count_colspan value=11}
				{* Only show if got data *}
				{if $from_to_info.got_data}
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$month_list.$m} {$y}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
						</div>
					</div>
				</div>
				
				<div class="card mx-3">
					<div class="card-body">
						<div class="table-responsive">
							<table width="100%" class="report_table small table mb-0 text-md-nowrap  table-hover">
								<thead class="bg-gray-100">
									<tr class="header">
										<th>&nbsp;</th>
										<th>ARMS Code</th>
										<th title="SKU Type">S.T</th>
										<th>Art No/MCode</th>
										<th>SKU Description</th>
										<th title="Price Type">P.T</th>
										
										{* Opening *}
										{if $show_balance}
											{assign var=count_colspan value=$count_colspan+1}
											<th>Opening<br />Qty</th>
										{/if}
										
										{foreach from=$from_to_info.date_info.date_list item=dt}
											{capture assign=count_date_list}{count var=$from_to_info.date_info.date_list}{/capture}
											<th>{$dt|date_format:"%d"}</th>
										{/foreach}
										
										<th title="Total Sales Qty">T.Qty</th>
										<th title="Gross Amount">Gross Amount</th>
										<th title="Total Discount of the item">Discount</th>
										<th title="Total Sales Amount">T.Amount</th>
										
										{if $show_balance}
										{assign var=count_colspan value=$count_colspan+3}
											{* In *}
											<th title="In Qty">In [<a href="javascript:void(BRAND_REPORT.show_balance_in_info())">?</a>]</th>
											
											{* Out *}
											<th title="Out Qty">Out [<a href="javascript:void(BRAND_REPORT.show_balance_out_info())">?</a>]</th>
											
											{* Closing *}
											<th title="Closing Qty">Closing</th>
										{/if}
										
										{* AVG Price *}
										<th title="Average Selling Price">AVG Price</th>
										
										{if $show_balance}
											{* Closing Amt *}
											{assign var=count_colspan value=$count_colspan+1}
											{*<th title="Closing Qty x Average Selling Price">Closing<br />Amt</th>*}
										{/if}
									</tr>
								</thead>
								
								{assign var=fitem value=0}
								{foreach from=$from_to_info.brand_info.brand_id_list key=brand_id item=brand_sales_info}
									{* Loop for each brand *}
									<tr class="brand_row">
										<td colspan="{$count_colspan+$count_date_list}">{$brand_list.$brand_id.code} - {$brand_list.$brand_id.description}</td>
									</tr>
									
									{foreach from=$brand_sales_info.item_list key=item_key item=dummy}
										{assign var=fitem value=$fitem+1}
										{assign var=sales_info value=$data.item_sales.$item_key.$ym}
										
										{assign var=item_info value=$data.item_info.$item_key.info}
										{assign var=item_total_info value=$data.item_total.$item_key.$ym}
										{assign var=balance_info value=$from_to_info.balance_info.$item_key.total}
										{capture assign=count_price_type}{count var=$sales_info}{/capture}
										<tr>
											<td rowspan="{$count_price_type}">{$fitem}.</td>
											<td rowspan="{$count_price_type}" nowrap>
												{$item_info.sku_item_code}
												{if $data.item_info.$item_key.changed}<span class="not_up_to_date">*</span>{/if}
												{if $data.item_info.$item_key.multi_vendor}<span class="got_multi_vendor">*</span>{/if}
											</td>
											<td rowspan="{$count_price_type}">{$item_info.sku_type|substr:0:1}</td>
											<td rowspan="{$count_price_type}">{$item_info.artno_mcode|default:'-'}</td>
											<td rowspan="{$count_price_type}">{$item_info.description|default:'-'}</td>
											
											{foreach from=$sales_info key=price_type item=daily_sales name=fds}
												{if $smarty.foreach.fds.first}
													{* Price Type *}
													{if $price_type eq $no_price_type}
														<td>-</td>
													{else}
														<td>{$price_type}</td>
													{/if}
													
													{* Opening *}
													{if $show_balance}
														<td align="right" rowspan="{$count_price_type}">{$balance_info.opening.qty|qty_nf}</td>
													{/if}
													
													{* Date *}
													{foreach from=$from_to_info.date_info.date_list item=dt}
														<td align="right">
															{if $show_type eq 'qty'}
																{$daily_sales.$dt.$show_type|qty_nf|ifzero:'&nbsp;'}
															{else}
																{$daily_sales.$dt.$show_type|number_format:2|ifzero:'&nbsp;'}
															{/if}
														</td>
													{/foreach}
													
													{* Total Sales Qty *}
													<td align="right" rowspan="{$count_price_type}">{$item_total_info.qty|qty_nf}</td>
													{* Total Discount and Gross Amount *}
													<td align="right" rowspan="{$count_price_type}">{$item_total_info.gross_amt|number_format:2}</td>
													<td align="right" rowspan="{$count_price_type}">{$item_total_info.discount|number_format:2}</td>
													{* Total Sales Amt *}
													<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt|number_format:2}</td>
													
													{if $show_balance}
														{* In *}
														<td align="right" rowspan="{$count_price_type}">{$balance_info.in.qty|qty_nf}</td>
														
														{* Out *}
														<td align="right" rowspan="{$count_price_type}">{$balance_info.out.qty|qty_nf}</td>
														
														{* Closing *}
														<td align="right" rowspan="{$count_price_type}">{$balance_info.closing.qty|qty_nf}</td>
													{/if}
													
													{* AVG Selling *}
													<td align="right" rowspan="{$count_price_type}">{$item_total_info.avg_selling|number_format:2}</td>
												{/if}
											{/foreach}
										</tr>
										
										{if $count_price_type > 1}
											{foreach from=$sales_info key=price_type item=daily_sales name=fds}
												{if !$smarty.foreach.fds.first}
													{* Price Type *}
													{if $price_type eq $no_price_type}
														<td>-</td>
													{else}
														<td>{$price_type}</td>
													{/if}
													
													{* Date *}
													{foreach from=$from_to_info.date_info.date_list item=dt}
														<td align="right">
															{if $show_type eq 'qty'}
																{$daily_sales.$dt.$show_type|qty_nf|ifzero:'&nbsp;'}
															{else}
																{$daily_sales.$dt.$show_type|number_format:2|ifzero:'&nbsp;'}
															{/if}
														</td>
													{/foreach}
												{/if}
											{/foreach}
										{/if}
									{/foreach}
									{* Total Per Brand *}
									<tr class="brand_total_row">
										{assign var=balance_info_brand value=$from_to_info.balance_info.brand.$brand_id}
										{assign var=sales_brand_info value=$from_to_info.group_total.sales}
										<td align="right" colspan="6"><b>Brand Total</b></td>
		
										{* Opening *}
										{if $show_balance}
											<td align="right">{$balance_info_brand.opening.qty|qty_nf}</td>
										{/if}
		
										{* Date *}
										{foreach from=$from_to_info.date_info.date_list item=dt}
											<td align="right">
												{if $show_type eq 'qty'}
													{$sales_brand_info.$dt.$brand_id.$show_type|qty_nf|ifzero:'&nbsp;'}
												{else}
													{$sales_brand_info.$dt.$brand_id.$show_type|number_format:2|ifzero:'&nbsp;'}
												{/if}
											</td>
										{/foreach}
										
										{* Total Sales Qty *}
										<td align="right">{$sales_brand_info.$brand_id.qty|qty_nf}</td>
										{* Total Discount and Gross Amount *}
										<td align="right">{$sales_brand_info.$brand_id.gross_amt|number_format:2}</td>
										<td align="right">{$sales_brand_info.$brand_id.discount|number_format:2}</td>
										{* Total Sales Amt *}
										<td align="right">{$sales_brand_info.$brand_id.amt|number_format:2}</td>
										
										{if $show_balance}
										{* In *}
										<td align="right">{$balance_info_brand.in.qty|qty_nf}</td>
										
										{* Out *}
										<td align="right">{$balance_info_brand.out.qty|qty_nf}</td>
										
										{* Closing *}
										<td align="right">{$balance_info_brand.closing.qty|qty_nf}</td>
									{/if}
										
										{* AVG Selling *}
										<td align="right">{$sales_brand_info.$brand_id.avg_selling|number_format:2}</td>
										
									</tr>
								{/foreach}
								
								{* Total *}
								<tr class="header">
									{assign var=balance_info value=$from_to_info.balance_info.total.total}
									{assign var=sales_info value=$from_to_info.group_total.sales}
									
									<td align="right" colspan="6"><b>Total</b></td>
								
									{* Opening *}
									{if $show_balance}
										<td align="right">{$balance_info.opening.qty|qty_nf}</td>
									{/if}
									
									{* Date *}
									{foreach from=$from_to_info.date_info.date_list item=dt}
										<td align="right">
											{if $show_type eq 'qty'}
												{$sales_info.$dt.$show_type|qty_nf|ifzero:'&nbsp;'}
											{else}
												{$sales_info.$dt.$show_type|number_format:2|ifzero:'&nbsp;'}
											{/if}
										</td>
									{/foreach}
									
									{* Total Sales Qty *}
									<td align="right">{$sales_info.total.qty|qty_nf}</td>
									{* Total Discount and Gross Amount *}
									<td align="right">{$sales_info.total.gross_amt|number_format:2}</td>
									<td align="right">{$sales_info.total.discount|number_format:2}</td>
									{* Total Sales Amt *}
									<td align="right">{$sales_info.total.amt|number_format:2}</td>
									
									{if $show_balance}
										{* In *}
										<td align="right">{$balance_info.in.qty|qty_nf}</td>
										
										{* Out *}
										<td align="right">{$balance_info.out.qty|qty_nf}</td>
										
										{* Closing *}
										<td align="right">{$balance_info.closing.qty|qty_nf}</td>
									{/if}
									
									{* AVG Selling *}
									<td align="right">{$sales_info.total.avg_selling|number_format:2}</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				{/if}
			{/foreach}
		{/if}
	{/if}
{/if}

{include file="footer.tpl"}
