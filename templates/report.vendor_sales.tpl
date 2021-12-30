	{*
4/18/2015 10:43 AM Andy
- Enhanced to have report title.

4/21/2015 11:40 AM Andy
- Enhanced the printing to have branch details.

4:28 PM 4/29/2015 Andy
- Change the remark for better understanding.

06/24/2016 11:30 Edwin
- Enhanced on show GST and Amount Include GST in report.

12/12/2016 10:26 AM Andy
- Enhanced to have information popup for In & Out.

7/3/2017 08:33 AM Qiu Ying
- Bug fixed on numbering not in sequence

9/26/2018 2:53 PM Justin
- Bug fixed on not to show GST columns if the selected date range does not have GST data.

10/15/2020 4:41 PM William
- Added new tax checking.
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

@media screen {
   #div_vendor_info {display: none;}
}
			  
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

.got_multi_vendor{
	color: blue;
}
{/literal}
</style>
<script type="text/javascript">

{literal}

var VENDOR_REPORT = {
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
    
		// check use grn
		this.check_can_use_grn();
	},
	// function when user change use grn checkbox
	use_grn_changed: function(){
		// check use grn
		this.check_can_use_grn();
	},
	// function when user change branch
	branch_id_changed: function(){
		// check use grn
		this.check_can_use_grn();
	},
	// function when user change show all items
	show_all_sku_changed: function(){
		// check use grn
		this.check_can_use_grn();
	},
	// function to check whether enable/disable use grn
	check_can_use_grn: function(){
		var allow_use_grn = true;
		
		// got branch id
		if(this.f['branch_id']){
			// not allow for branch group
			if(this.f['branch_id'].value.indexOf('bg')>=0 || !this.f['branch_id'].value)	allow_use_grn = false;
		}
		
		//if(allow_use_grn && $('chx_show_all_item').checked)	allow_use_grn = false;
		
		var chx_use_grn = $('chx_use_grn');
		
		if(allow_use_grn){
			chx_use_grn.disabled = false;
		}else{
			chx_use_grn.disabled = true;
			chx_use_grn.checked = false;
		}
		
		/*var chx_show_all_item = $('chx_show_all_item').disabled = (chx_use_grn.checked);
		
		if(chx_show_all_item.disabled){
			chx_show_all_item.checked = false;
		}*/
	},
	// function to validate form before submit
	check_form: function(){
		if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch');
				return false;
			}
		}
		
		// got show all items & all dept
		/*if($('chx_show_all_item').checked && !this.f['dept_id'].value){
			alert('Show All Items cannot use for All Department. Please select single Department.');
			return false;
		}*/
		
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
			<div class="col-md-3">
				{if $BRANCH_CODE eq 'HQ'}
				{* Branch *}
				<b class="form-label mt-2">Branch </b>
				<select class="form-control" name="branch_id" onChange="VENDOR_REPORT.branch_id_changed();">
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
						{*<optgroup label="Branch Group">
							{foreach from=$branch_group.header key=bgid item=bg}
								<option value="bg,{$bgid}" class="bg" {if $smarty.request.branch_id eq "bg,`{$bgid}`"}selected {/if}>{$bg.code}</option>
								{foreach from=$branch_group.items.$bgid key=bid item=r}
									<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$branch_list.$bid.code} - {$branch_list.$bid.description}</option>
								{/foreach}
							{/foreach}
						</optgroup>*}
					{/if}
				</select>
			{/if}
			</div>
			
			<div class="col-md-3">
				{* Vendor *}
			<b class="form-label mt-2">Vendor</b>
			<select class="form-control" name="vendor_id">
				{foreach from=$vendor_list key=vid item=r}
					<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected {/if}>{$r.description}</option>
				{/foreach}
			</select>
			</div>
			
			
			<div class="col-md-3">
				{* SKU Type *}
				<b class="form-label mt-2">SKU Type</b>
				<select class="form-control" name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type_list item=r}
						<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
					{/foreach}
				</select>
			</div>
				
			<div class="col-md-3">
				{* Price Type *}
				<b class="form-label mt-2">Price Type</b>
				<select class="form-control" name="price_type">
					<option value="">-- All --</option>
					{foreach from=$price_type_list item=r}
						<option value="{$r.price_type}" {if $smarty.request.price_type eq $r.price_type}selected {/if}>{$r.price_type}</option>
					{/foreach}
				</select>
				
			</div>
				
			<div class="col-md-3">
				{* Department *}
				<b class="form-label mt-2">Department</b>
				<select class="form-control" name="dept_id">
					<option value="">-- All --</option>
					{foreach from=$dept_list item=r}
						<option value="{$r.id}" {if $smarty.request.dept_id eq $r.id}selected {/if}>{$r.description}</option>
					{/foreach}
				</select>
			
			</div>
			
			<div class="col-md-3">
				{* Date From *}
			<b class="form-label mt-2">Date From</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="23" />
			&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</div>
			</div>
			
			<div class="col-md-3">
				{* Date To *}
			<b class="form-label mt-2">To</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="23" />
			&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
			</div>
			</div>
			
			<div class="col-md-6">
				<div class="row ml-1 mt-3">
					{* Group Monthly *}
				<div class="form-label form-inline">
					<input type="checkbox" name="by_monthly" id="chx_by_monthly" {if $smarty.request.by_monthly}checked {/if} value="1" /> <label for="chx_by_monthly"><b>&nbsp;Group Monthly</b></label>&nbsp;&nbsp; 
				</div>
				
				{* Use GRN *}
				<div class="form-label form-inline">
					<input type="checkbox" name="use_grn" id="chx_use_grn" onChange="VENDOR_REPORT.use_grn_changed();" {if $smarty.request.use_grn}checked {/if} value="1" /> <label for="chx_use_grn"><b>&nbsp;Use GRN</b></label> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]&nbsp;&nbsp; 
				</div>
				
				{* Show All Items *}
				{*<input type="checkbox" name="show_all_items" id="chx_show_all_item" {if $smarty.request.show_all_items}checked {/if} onChange="VENDOR_REPORT.show_all_sku_changed();" value="1" /> <label for="chx_show_all_item"><b>&nbsp;Show All Items</b></label>&nbsp;&nbsp; *}
				
				<!-- Group by SKU -->
				<div class="form-label form-inline">
					<input type="checkbox" name="group_by_sku" id="chx_group_by_sku" {if $smarty.request.group_by_sku}checked {/if} value="1" /> <label for="chx_group_by_sku"><b>&nbsp;Group by SKU</b></label>&nbsp;&nbsp;
				</div>
				
				<!-- Show Balance -->
				<div class="form-label form-inline">
					<input type="checkbox" name="show_balance" id="chx_show_bal" {if $smarty.request.show_balance}checked {/if} value="1" /> <label for="chx_show_bal"><b>&nbsp;Show Balance</b></label>&nbsp;&nbsp; 
				</div>
				</div>
			</div>
		
			
				<div class="col-md-3">
					{* Report Type *}
				<b class="form-label mt-2">Report Type: </b>
				<label><input name="report_type" type="radio" value="qty" {if $smarty.request.report_type eq 'qty' or $smarty.request.report_type eq ''}checked {/if} />Sales Qty</label>
				<label><input name="report_type" type="radio" value="amt" {if $smarty.request.report_type eq 'amt'}checked {/if} />Sales Amount</label>
				{if $config.enable_gst || $config.enable_tax}
					<label><input name="report_type" type="radio" value="gst" {if $smarty.request.report_type eq 'gst'}checked {/if} />Tax Amount</label>
					<label><input name="report_type" type="radio" value="amt_inc_gst" {if $smarty.request.report_type eq 'amt_inc_gst'}checked {/if} />Sales Amount Included Tax</label>
				{/if}
				
				</div>
		</div>
				
				<input type="button" class="btn btn-info mt-2 mt-md-0" value='Show Report' onClick="VENDOR_REPORT.submit_form();" /> 
				<input type="button" class="btn btn-primary mt-2 mt-md-0" value="Print" onclick="VENDOR_REPORT.do_print();" />
				
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<input type=button class="btn btn-info mt-2 mt-md-0" value="Export to Excel" onclick="VENDOR_REPORT.submit_form('excel');" />
				{/if}
			
			
		</form>
	</div>
</div>
<script type="text/javascript">VENDOR_REPORT.initialize();</script>
{/if}

{if $smarty.request.show_report}
	{if !$data}
		<br />
		* No Data *
	{else}
		<div id="div_vendor_info">
			<h1>{$b_info.description} {if $b_info.company_no}({$b_info.company_no}){/if}</h1>
			{$b_info.address|nl2br}<br>
			Tel: {$b_info.phone_1}{if $b_info.phone_2} / {$b_info.phone_2}{/if} &nbsp;&nbsp; Fax: {$b_info.phone_3}
		</div>
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
	<div class="alert alert-primary mx-3 rounded">
			
		<ul style="list-style-type:none;">
			<li> Item mark with <span class="not_up_to_date">*</span> is not up to date, stock balance may incorrect.</li>
			{if $smarty.request.use_grn}
				<li><span class="got_multi_vendor">*</span> Got GRN by multiple vendor. sales qty may not fully show under your selected vendor.</li>
			{/if}
		</ul>
	</div>
		
		{assign var=show_balance value=$smarty.request.show_balance}
		{assign var=show_type value=$smarty.request.report_type}
		
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
												<th>Opening<br />Qty</th>
											{/if}
											
											{assign var=yr_is_under_gst value=0}
											{foreach from=$from_to_info.date_info.ym_list key=ym item=ym_info}
												<th>{$month_list[$ym_info.m]}</th>
												
												{if $is_under_gst.$ym}
													{assign var=yr_is_under_gst value=1}
												{/if}
											{/foreach}
											
											<th title="Total Sales Qty">T.Qty</th>
											<th title="Total Sales Amount">T.Amount</th>
											{if $yr_is_under_gst}
												<th> Tax</th>
												<th>Amount Inc Tax</th>
											{/if}
											
											{if $show_balance}
												{* In *}
												<th title="In Qty">In [<a href="javascript:void(VENDOR_REPORT.show_balance_in_info())">?</a>]</th>
												
												{* Out *}
												<th title="Out Qty">Out [<a href="javascript:void(VENDOR_REPORT.show_balance_out_info())">?</a>]</th>
												
												{* Closing *}
												<th title="Closing Qty">Closing</th>
											{/if}
											
											{* AVG Price *}
											<th title="Average Selling Price">AVG Price</th>
										</tr>
									</thead>
									
									{* Loop for item sales *}
									{assign var=count_no value=0}
									{foreach from=$data.item_sales key=item_key item=item_sales_info name=fitem}
										{* this item got sales in this year *}							
										{if (!$smarty.request.group_by_sku && $from_to_info.sid_list.$item_key) || ($smarty.request.group_by_sku && $from_to_info.sku_id_list.$item_key)}
											{assign var=item_info value=$data.item_info.$item_key.info}
											{assign var=item_total_info value=$data.item_total.$item_key.by_year.$y}
											{assign var=balance_info value=$from_to_info.balance_info.$item_key.total}
											{capture assign=count_price_type}{count var=$from_to_info.item_price_type_list.$item_key}{/capture}
											{assign var=count_no value=$count_no+1}
											<tbody class="fs-08">
												<tr>
													<td rowspan="{$count_price_type}">{$count_no}.</td>
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
															
															{* Total Sales Amt *}
															<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt|number_format:2}</td>
															
															{if $yr_is_under_gst}
																{* Total GST Amt*}
																<td align="right" rowspan="{$count_price_type}">{$item_total_info.gst|number_format:2}</td>
												
																{* Total Amount Include GST*}
																<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt_inc_gst|number_format:2}</td>
															{/if}
															
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
												
											</tbody>
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
										{/if}
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
										
										{* Total Sales Amt *}
										<td align="right">{$sales_info.total.amt|number_format:2}</td>
										
										{if $yr_is_under_gst}
											{* Total GST Amt*}
											<td align="right">{$sales_info.total.gst|number_format:2}</td>
											
											{* Total Amount Include GST*}
											<td align="right">{$sales_info.total.amt_inc_gst|number_format:2}</td>
										{/if}
										
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
				
				{* Only show if got data *}
				{if $from_to_info.got_data}
					<h3>{$month_list.$m} {$y}</h3>
					
					<table width="100%" class="report_table small">
						<tr class="header">
							<th>&nbsp;</th>
							<th>ARMS Code</th>
							<th title="SKU Type">S.T</th>
							<th>Art No/MCode</th>
							<th>SKU Description</th>
							<th title="Price Type">P.T</th>
							
							{* Opening *}
							{if $show_balance}
								<th>Opening<br />Qty</th>
							{/if}
							
							{foreach from=$from_to_info.date_info.date_list item=dt}
								<th>{$dt|date_format:"%d"}</th>
							{/foreach}
							
							<th title="Total Sales Qty">T.Qty</th>
							<th title="Total Sales Amount">T.Amount</th>
							{if $is_under_gst.$ym}
								<th>Tax</th>
								<th>Amount Inc Tax</th>
							{/if}
							
							{if $show_balance}
								{* In *}
								<th title="In Qty">In [<a href="javascript:void(VENDOR_REPORT.show_balance_in_info())">?</a>]</th>
								
								{* Out *}
								<th title="Out Qty">Out [<a href="javascript:void(VENDOR_REPORT.show_balance_out_info())">?</a>]</th>
								
								{* Closing *}
								<th title="Closing Qty">Closing</th>
							{/if}
							
							{* AVG Price *}
							<th title="Average Selling Price">AVG Price</th>
							
							{if $show_balance}
								{* Closing Amt *}
								{*<th title="Closing Qty x Average Selling Price">Closing<br />Amt</th>*}
							{/if}
						</tr>
						
						{* Loop for item sales *}
						{assign var=count_no value=0}
						{foreach from=$data.item_sales key=item_key item=item_sales_info name=fitem}
							{* check this month got sales or not *}
							{if $item_sales_info.$ym}
								{assign var=sales_info value=$item_sales_info.$ym}
								{assign var=item_info value=$data.item_info.$item_key.info}
								{assign var=item_total_info value=$data.item_total.$item_key.$ym}
								{assign var=balance_info value=$from_to_info.balance_info.$item_key.total}
								{capture assign=count_price_type}{count var=$sales_info}{/capture}
								{assign var=count_no value=$count_no+1}
								<tr>
									<td rowspan="{$count_price_type}">{$count_no}.</td>
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
											
											{* Total Sales Amt *}
											<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt|number_format:2}</td>
											
											{if $is_under_gst.$ym}
												{* Total GST Amt*}
												<td align="right" rowspan="{$count_price_type}">{$item_total_info.gst|number_format:2}</td>
												
												{* Total Amount Include GST*}
												<td align="right" rowspan="{$count_price_type}">{$item_total_info.amt_inc_gst|number_format:2}</td>
											{/if}
											
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
							{/if}
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
							
							{* Total Sales Amt *}
							<td align="right">{$sales_info.total.amt|number_format:2}</td>
							
							{if $is_under_gst.$ym}
								{* Total GST Amt*}
								<td align="right">{$sales_info.total.gst|number_format:2}</td>
												
								{* Total Amount Include GST*}
								<td align="right">{$sales_info.total.amt_inc_gst|number_format:2}</td>
							{/if}
											
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
				{/if}
			{/foreach}
		{/if}
	{/if}
{/if}

{include file="footer.tpl"}
