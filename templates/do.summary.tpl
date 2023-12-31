{*
7/24/2009 4:52:56 PM Andy
- Use total_amount2 for total

4/22/2010 4:15:43 PM Andy
- Add debtor filter for credit sales DO summary

5/11/2010 10:17:09 AM Andy
- Debtor dropdown list add description

5/14/2010 11:18:22 AM Andy
- Add Sales Person Name filter in DO Summary.

5/20/2010 10:38:56 AM Andy
- DO summary under "deliver to", add description for debtor.

2/6/2012 2:38:43 PM Justin
- Added new filter option "Sales Agent", this filter will replace the existing "Sales Person" as if found config.
- Added to show cost, gp and gp % columns base on config.

2/14/2012 3:37:54 PM Justin
- Fixed the GP% bugs.

2/21/2012 10:12:43 AM Justin
- Fixed the amount and invoice amount that is not having any number format.

3/26/2012 5:05:32 PM Justin
- Added new filter and column "Price Type" for consignment modules customers.

2/2/2015 3:09 PM Andy
- Fix DO Summary table layout.

5/27/2015 2:39 PM Justin
- Enhanced to show GST information.

7/21/2015 9:32 AM Joo Chia
- Add in rows to show GST detail by GST code for each invoice.
- Fix table row background color to prevent showing unwanted color when export.

7/21/2015 4:40 PM Andy
- Add nowrap for expand gst details icon.

7/22/2015 11:21 AM Joo Chia
- Enhanced to have total gst.
- Show GST Error for record under GST but do not have GST id.

7/23/2015 4:04 PM Joo Chia
- Add expand all and collapse all links.

8/13/2019 10:05 AM William
- Fixed bug table column have problem when filter by status "Do summary" and "Invoice summary".

06/23/2020 05:19 Sheila
- Updated button css.

12/09/2020 2:39 Rayleen
- Display Sales Agent if masterfile_enable_sa is enabled, if not follow config for do_cash_sales_show_sales_person_name and do_credit_sales_show_sales_person_name
- Disable Sales Agent field if Transfer DO is chosen
- Fix colspan count in table footer
- Remove invoice number from the expanded DO list
*}

{include file=header.tpl}
{if $smarty.request.invoice_type and $smarty.request.invoice_type neq 'transfer'}
	{if $config.masterfile_enable_sa}
		{assign var=show_sales_person_name value=1}
	{else}
		{if ($smarty.request.invoice_type eq 'open' and $config.do_cash_sales_show_sales_person_name) or ($smarty.request.invoice_type eq 'credit_sales' and $config.do_credit_sales_show_sales_person_name)}
			{assign var=show_sales_person_name value=1}
		{/if}
	{/if}
{/if}


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if !$no_header_footer}
{literal}
<style>
.sortable_col a{
	color:black;
}
</style>
{/literal}

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
var masterfile_enable_sa = int('{$config.masterfile_enable_sa}');
var do_credit_sales_show_sales_person_name = int('{$config.do_credit_sales_show_sales_person_name}');
var do_cash_sales_show_sales_person_name = int('{$config.do_cash_sales_show_sales_person_name}');

{literal}
function summary_type_changed(){
	var s = document.f1['p'].value;
	if(s==''){
		$('span_do_status').show();
		$('span_invoice_status').show();
	}else if(s=='do'){
        $('span_do_status').show();
		$('span_invoice_status').hide();
	}else if(s=='invoice'){
        $('span_do_status').hide();
		$('span_invoice_status').show();
	}
}

function show_paid_status(val)
{
	// paid status
    if(val == 'open'){
        $('span_paid_status').style.display = '';
	}else{
        $('span_paid_status').style.display = 'none';
	}
        
    // debtor list
    if(val=='credit_sales') $('div_debtor_list').show();
    else    $('div_debtor_list').hide();
    
    // sales person list
    $('sales_person_name').enable();
    if(masterfile_enable_sa && val!='transfer'){
    	$('span_sales_person_name').show();
    }else{
    	if((do_cash_sales_show_sales_person_name&&val=='open')||(do_credit_sales_show_sales_person_name&&val=='credit_sales')){    
    		$('span_sales_person_name').show();
    	}else{
	    	$('sales_person_name').disable();
	    	$('span_sales_person_name').hide();
		}
	}
}

function toggle_gst_details(bid, do_id, mode){
	var tr_gst_detail = $("gst_detail_"+bid+'_'+do_id);
	var tr_do = $('tr_do-'+bid+'-'+do_id);
	var tr_img = $('img_gst_dtl_'+bid+'_'+do_id);
	
	if(tr_gst_detail && tr_do && tr_img){
	
		if(tr_gst_detail.style.display == "none"){
			if((mode=="")||(mode=="expand")){
				// open
				
				// get the next tr
				var next_tr = $(tr_do).next();
				
				// check whether next tr is own gst details
				if(next_tr != tr_gst_detail){
					// found not own gst details
					// clone the html to put after own tr
					new Insertion.After(tr_do, tr_gst_detail.outerHTML);
					// remove the old gst details tr from table
					$(tr_gst_detail).remove();
					// re-assign the variable
					tr_gst_detail = $("gst_detail_"+bid+'_'+do_id);
				}
				
				tr_img.src = "/ui/collapse.gif";
				tr_img.title = "Hide Detail";
				tr_gst_detail.style.display = "";
			}			
		}else{
			if((mode=="")||(mode=="collapse")){
				// hide
				tr_img.src = "/ui/expand.gif";
				tr_img.title = "Show Detail";
				tr_gst_detail.style.display = "none";
			}	
		}
	}
	
	
}

function expand_collapse_all(mode){
	var all_tr_do = $('tbl_do').getElementsBySelector('tr.tr_do');
	for(var i=0,len=all_tr_do.length; i<len; i++){
		var bid = all_tr_do[i].id.split('-')[1];
		var do_id = all_tr_do[i].id.split('-')[2];
		
		toggle_gst_details(bid, do_id, mode);
	}
}

{/literal}
</script>




<div class="card mx-3">
	<div class="card-body">
		<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method="post" style="padding:5px;white-space:nowrap;">
			<input type=hidden name=a value="show">
			
			<div class="row">
				<p>
					{if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-3">
						<b class="form-label mt-2">Branch</b>
					<select class="form-control" name=branch_id>
					<option value="">-- All --</option>
					{section name=i loop=$branch}
					<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
					{/section}
					</select>
					</div>
				
					{/if}
					
					<div class="col-md-3">
						<b class="form-label mt-2">DO Date From</b>
					<div class="form-inline">
						<input type="text" class="form-control" name="from" value="{$smarty.request.from}" id="added1" readonly="1"  />
						<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/>
					</div>
					</div>
				   <div class="col-md-3">
					<b class="form-label mt-2">To</b>
					<div class="form-inline">
						<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1"  /> 
					<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
					</div>
				   </div>
					
				<div class="col-md-3">
					<b class="form-label mt-2">By user</b>
					<select  class="form-control" name=user_id>
					<option value=0>-- All --</option>
					{section name=i loop=$user}
					<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
					{/section}
					</select>
				</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">Show</b>
					<select class="form-control " name="p" onChange="summary_type_changed();">
						<option value="">-- All --</option>
						<option value="do" {if $smarty.request.p eq 'do'}selected {/if}>DO Summary</option>
						<option value="invoice" {if $smarty.request.p eq 'invoice'}selected {/if}>Invoice Summary</option>
					</select>
					</div>
					</p>
					
					<p>
					<!--input type=hidden name=a value="list"-->
				<div class="col-md-3">
					<b class="form-label mt-2">Deliver To</b>
					<select class="form-control" name=deliver_to>
					<option value="">-- All --</option>
					{section name=i loop=$branch}
					<option value="{$branch[i].id}" {if $smarty.request.deliver_to eq $branch[i].id}selected{/if}>{$branch[i].code}</option>
					{/section}
					</select>
				</div>
					
					<div class="col-md-3">
						<span id="span_do_status">
					
							<b class="form-label mt-2">DO Status</b>
							<select class="form-control" name=status>
							<option value=0 {if $smarty.request.status == 0}selected{/if}>All</option>
							<option value=1 {if $smarty.request.status == 1}selected{/if}>Draft / Waiting for Approval</option>
							<option value=2 {if $smarty.request.status == 2}selected{/if}>Approved</option>
							<option value=3 {if $smarty.request.status == 3}selected{/if}>Checkout</option>
							</select>
							</span>
					</div>
					{*<input name=status value=3 type=hidden>*}
					
				<div class="col-md-3">
					<span id="span_invoice_status">
				
						<b class="form-label mt-2">Invoice Status</b>
						<select class="form-control" name=markup>
						<option value=0 {if $smarty.request.markup == 0}selected{/if}>All</option>
						<option value=1 {if $smarty.request.markup == 1}selected{/if}>With Markup</option>
						<option value=2 {if $smarty.request.markup == 2}selected{/if}>Without Markup</option>
						</select>
						</span>
				</div>
					
					<div class="col-md-3">
						<span id="span_invoice_type">
							<b class="form-label  mt-2">Invoice Type</b>
							<select class="form-control" name=invoice_type onchange=show_paid_status(this.value)>
							<option value='transfer' {if $smarty.request.invoice_type eq 'transfer'}selected{/if}>Transfer DO</option>
							<option value='open' {if $smarty.request.invoice_type eq 'open'}selected{/if}>Cash Sales DO</option>
							<option value='credit_sales' {if $smarty.request.invoice_type eq 'credit_sales'}selected{/if}>Credit Sales DO</option>
							</select>
							</span>
					</div>
					
					{if $config.consignment_modules}
						
					<div class="col-md-3">
						<b class="form-label mt-2">Price Type</b>
						<select class="form-control" name="price_type">
							<option value="">-- All --</option>
							{foreach from=$pt_list item=r}
								<option value="{$r.code|escape}" {if $smarty.request.price_type eq $r.code}selected {/if}>{$r.code}</option>
							{/foreach}
						</select>	
					</div>
					{/if}
					
					</p>
					<p>
					<div class="col-md-3">
						<span id="span_paid_status" {if $smarty.request.invoice_type ne 'open'}style="display:none"{/if}>
							<b class="form-label">Paid Status</b>
							<select class="form-control" name=paid_status>
							<option value='all' {if $smarty.request.paid_status eq 'all'}selected{/if}>All</option>
							<option value='1' {if $smarty.request.paid_status eq '1'}selected{/if}>Paid</option>
							<option value='0' {if $smarty.request.paid_status eq '0'}selected{/if}>Unpaid</option>
							</select>&nbsp;&nbsp;&nbsp;&nbsp;
							</span>
					</div>
					
					<div class="col-md-3">
						<span id="div_debtor_list" {if $smarty.request.invoice_type ne 'credit_sales'}style="display:none"{/if}>
							<b class="form-label">Debtor</b>
								<select class="form-control" name="debtor_id">
									<option value="">-- All --</option>
									{foreach from=$debtors item=r}
										<option value="{$r.id}" {if $r.id eq $smarty.request.debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
									{/foreach}
								</select>&nbsp;&nbsp;&nbsp;&nbsp;
							</span>
					</div>
					
					
				<div class="col-md-3">
					<span id="span_sales_person_name" {if !$show_sales_person_name}style="display:none"{/if}>
						<b class="form-label">Sales {if $config.masterfile_enable_sa}Agent{else}Person Name{/if}</b>
						<select class="form-control" name="sales_person_name" id="sales_person_name">
							<option value="">-- All --</option>
							{foreach from=$sales_agent_list item=r}
								<option value="{$r.id|escape}" {if $smarty.request.sales_person_name eq $r.id}selected {/if}>{$r.sales_person_name}</option>
							{/foreach}
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
					</span>
				</div>
					</p>
					
					<p>
			</div>
			<input class="btn btn-primary mt-3" type="submit" name="submit" value="Refresh">
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-3" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			
			</form>
	</div>
</div>
{/if}
<br>
{if !$do_list}
{if $smarty.request.submit}- No record -{/if}
{else}
{if $config.consignment_modules}
	<p>
	<b><font color="red">Note:</font><br />
	* Price Type column with "-" indicates having more than 2 price types</b>
	</p>
{/if}
{assign var=n value=1}
<!-- start -->

{if !$no_header_footer}
<p>
<a href="javascript:void(expand_collapse_all('expand'))"><img src="/ui/expand.gif" width="10" title="Expand All" class="clickable"> Expand All </a>&nbsp;|&nbsp;<a href="javascript:void(expand_collapse_all('collapse'))"><img src="/ui/collapse.gif" width="10" title="Collapse All" class="clickable"> Collapse All </a>
</p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width=100% class="report_table table mb-0 text-md-nowrap  table-hover" id="tbl_do">
				<thead class="bg-gray-100">
					<tr>
						<th>&nbsp;</th>
						<th class="sortable_col">DO No.</th>
						<th class="sortable_col">Create By</th>
						<th class="sortable_col">PO No.</th>
						<th class="sortable_col">Deliver To</th>
						<th class="sortable_col">DO Date</th>
						{if $config.consignment_modules}
							<th class="sortable_col">Price Type</th>
						{/if}
						
						{if $smarty.request.p eq 'do' or !$smarty.request.p}
							<th class="sortable_col">{if $config.enable_gst && !$config.consignment_modules}Gross{/if} Amount</th>
						{/if}
						{if $sessioninfo.show_cost}
							<th class="sortable_col">Cost</th>
						{/if}
						{if $sessioninfo.show_report_gp and ($smarty.request.p eq 'do' or !$smarty.request.p)}
							<th class="sortable_col">GP</th>
							<th class="sortable_col">GP(%)</th>
						{/if}
						{if $smarty.request.p eq 'invoice' or !$smarty.request.p}
							<th class="sortable_col">Invoice No.</th>
							<!--th class="sortable_col">Invoice Markup</th-->
							<th class="sortable_col">Invoice Amount</th>
							{if $is_under_gst}
								<th class="sortable_col">GST</th>
								<th class="sortable_col">Invoice Amount<br />Incl. GST</th>
							{/if}
						{/if}
						
					</tr>
				</thead>
				{section name=i loop=$do_list}
				<tr bgcolor='{cycle values="#ffffff,#eeeeee"}' id="tr_do-{$do_list[i].branch_id}-{$do_list[i].id}" class="tr_do">
					<td>{$n++}.</td>
					<td><a href="/do.php?a=view&branch_id={$do_list[i].branch_id}&id={$do_list[i].id}" target=_blank>
						{if $do_list[i].approved}
							{if $do_list[i].do_no}
								{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].do_no}
							{else}
								{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
							{/if}
						{elseif $do_list[i].status<1}
							{if $do_list[i].do_no}
								{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].do_no}(DD)
							{else}
								{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
							{/if}
						{elseif $do_list[i].status eq '1'}
							{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
						{elseif $do_list[i].status>1}
							{if $smarty.request.p eq 'do'}DO/{/if}{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
						{/if}
						</a>
						 {if preg_match('/\d/',$do_list[i].approvals) and $smarty.request.p eq 'do'}
						<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$do_list[i].approvals}</font></div>
						{/if}
					</td>
					<td>{$do_list[i].user_name}</td>
					<td align=center>{$do_list[i].po_no|default:"-"}</td>
					<td>
					{if $do_list[i].do_type eq 'credit_sales'}
						{assign var=debtor_id value=$do_list[i].debtor_id}
						Debtor: {$do_list[i].debtor_code} - {$do_list[i].debtor_desc}
					{elseif $do_list[i].do_branch_id}
						{$do_list[i].branch_name_2}
						- {$do_list[i].do_branch_description}
					{elseif $do_list[i].open_info.name}	
						{$do_list[i].open_info.name}
					{/if}
					{foreach from=$do_list[i].d_branch.name item=pn name=pn}
						{if $smarty.foreach.pn.iteration>1} ,{/if}
						{$pn}
					{/foreach}
					</td>
					{assign var=row_do_amt value=$do_list[i].do_total_gross_amt}
					{if !$row_do_amt}
						{assign var=row_do_amt value=$do_list[i].total_amount-$do_list[i].do_total_gst_amt}
					{/if}
					{*if $smarty.request.p eq 'do' or !$smarty.request.p*}
						{assign var=do_total value=$do_total+$row_do_amt}
						{if $sessioninfo.show_cost}
							{assign var=cost_total value=$cost_total+$do_list[i].cost}
						{/if}
					{*/if*}
					
					{if $smarty.request.p eq 'invoice' or !$smarty.request.p}
						{if $do_list[i].checkout and $do_list[i].inv_no}
							{assign var=invoice_gross_total value=$invoice_gross_total+$do_list[i].inv_total_gross_amt}
							{assign var=invoice_gst_amt_total value=$invoice_gst_amt_total+$do_list[i].inv_total_gst_amt}
							{assign var=invoice_total value=$invoice_total+$do_list[i].total_amount2}
						{/if}
					{/if}
				
					<td align=center>{$do_list[i].do_date|date_format:$config.dat_format}</td>
					{assign var=val_colspan value=0}
					{if $config.consignment_modules}
						{assign var=val_colspan value=$val_colspan+1}
						<td align=center>{$do_list[i].sheet_price_type|default:"-"}</td>
					{/if}
					{if $smarty.request.p eq 'do' or !$smarty.request.p}
						{assign var=val_colspan value=$val_colspan+1}
						<td align=right>{$row_do_amt|round2|number_format:2}</td>
					{/if}
					{if $sessioninfo.show_cost}
						{assign var=val_colspan value=$val_colspan+1}
						<td align=right>{$do_list[i].cost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
					{if $sessioninfo.show_report_gp and ($smarty.request.p eq 'do' or !$smarty.request.p)}
						{assign var=val_colspan value=$val_colspan+2}
						{assign var=gp value=$row_do_amt-$do_list[i].cost}
						<td align=right>{$gp|number_format:2}</td>
						{if $row_do_amt>0}
							{assign var=gp_per value=$gp/$row_do_amt*100}
						{else}
							{assign var=gp_per value=0}
						{/if}
						<td align=right>{$gp_per|number_format:2}</td>
					{/if}
					{if $smarty.request.p eq 'invoice' or !$smarty.request.p}
						{assign var=val_colspan value=$val_colspan+1}
						<td align="center" nowrap>{$do_list[i].inv_no} {if $do_list[i].inv_no != "" && $do_list[i].gst_detail && $do_list[i].is_under_gst}&nbsp;<img src="/ui/expand.gif" width="10" id="img_gst_dtl_{$do_list[i].branch_id}_{$do_list[i].id}" onclick="toggle_gst_details({$do_list[i].branch_id},{$do_list[i].id},'');" title="Show Detail" class="clickable">{else}&nbsp;&nbsp;&nbsp;&nbsp;{/if}</td>
						{if $do_list[i].checkout and $do_list[i].inv_no}
							<!--td align=center>{if $do_list[i].invoice_markup > 0}{$do_list[i].invoice_markup}%{/if}&nbsp;</td-->
							{if $is_under_gst}
								{assign var=val_colspan value=$val_colspan+2}
								<td align=right>{$do_list[i].inv_total_gross_amt|round2|number_format:2}&nbsp;</td>
								<td align=right>{$do_list[i].inv_total_gst_amt|round2|number_format:2}&nbsp;</td>
							{/if}
							{assign var=val_colspan value=$val_colspan+1}
							<td align=right>{$do_list[i].total_amount2|round2|number_format:2}&nbsp;</td>
						{else}
							{assign var=val_colspan value=$val_colspan+1}
							<td>&nbsp;</td>
							<!--td>&nbsp;</td-->
							{if $is_under_gst}
								{assign var=val_colspan value=$val_colspan+2}
								<td>&nbsp;</td><td>&nbsp;</td>
							{/if}
						{/if}
					{/if}
				
				</tr>
				
				
				{if $do_list[i].is_under_gst && $do_list[i].gst_detail}
				
					<tr id="gst_detail_{$do_list[i].branch_id}_{$do_list[i].id}" style="display:none;" bgcolor="#dfedfe">
						{assign var=ttl_colspan value=$val_colspan+2}
						<td colspan="{$ttl_colspan}"></td>		
						<td align=center>
						{foreach from=$do_list[i].gst_detail item=r} 	
							{$r.gst_code} {if $r.gst_code != "GST Error"}({$r.gst_rate}%){/if}<br />
						{/foreach}
						</td>
						{if $is_under_gst}
						<td align=right>
						{foreach from=$do_list[i].gst_detail item=r} 
							{$r.ttl_gross_amt|round2|number_format:2}&nbsp;<br />
						{/foreach}
						</td>
						<td align=right>
						{foreach from=$do_list[i].gst_detail item=r} 
							{$r.ttl_gst_amt|round2|number_format:2}&nbsp;<br />
						{/foreach}
						</td>
						{/if}
						<td align=right>
						{foreach from=$do_list[i].gst_detail item=r} 
							{$r.ttl_line_amt|round2|number_format:2}&nbsp;<br />
						{/foreach}
						</td>
					</tr>
				
				{/if}
				
				
				{/section}
				
				<tr bgcolor=#ffee99 class="sortbottom">
					{assign var=colspan value=6}
					{if $config.consignment_modules}
						{assign var=colspan value=$colspan+1}
					{/if}
					<td colspan="{$colspan}" align=right><b>Total</b></td>
					{if $smarty.request.p eq 'do' or !$smarty.request.p}
						<td align=right>{$do_total|number_format:2}</td>
					{/if}
					
					{if $sessioninfo.show_cost}
						<td align=right>{$cost_total|number_format:$config.global_cost_decimal_points}</td>
					{/if}
				
					{if $sessioninfo.show_report_gp and ($smarty.request.p eq 'do' or !$smarty.request.p)}
						{assign var=gp_total value=$do_total-$cost_total}
						<td align=right>{$gp_total|number_format:2}</td>
						{if $gp_total>0}
							{assign var=gp_per_total value=$gp_total/$do_total*100}
						{else}
							{assign var=gp_per_total value=0}
						{/if}
						<td align=right>{$gp_per_total|number_format:2}</td>
					{/if}
						
					{if $smarty.request.p eq 'invoice' or !$smarty.request.p}
						<th>&nbsp;</th>
						{if $is_under_gst}
							<td align=right>{$invoice_gross_total|number_format:2}&nbsp;</td>
							<td align=right>{$invoice_gst_amt_total|number_format:2}&nbsp;</td>
						{/if}
						<td align=right>{$invoice_total|number_format:2}&nbsp;</td>
					{/if}
				</tr>
				{if $smarty.request.p eq 'invoice' or !$smarty.request.p}
				{if $is_under_gst}
				
					{assign var=ttl_colspan2 value=$val_colspan+2}
					{foreach from=$total_gst_list item=t}
						<tr bgcolor=#ffee99 class="sortbottom">
							<td colspan="{$ttl_colspan2}"></td>		
							<td align=center>{$t.g_gst_code} ({$t.g_gst_rate}%)</td>
							<td align=right>{$t.g_ttl_gross_amt|round2|number_format:2}&nbsp;</td>
							<td align=right>{$t.g_ttl_gst_amt|round2|number_format:2}&nbsp;</td>
							<td align=right>{$t.g_ttl_line_amt|round2|number_format:2}&nbsp;</td>
						</tr>
					{/foreach}
				
					{if $total_gst_error}
						<tr bgcolor="#ffee99" class="sortbottom">
							<td colspan="{$ttl_colspan2}"></td>		
							<td align=center>GST Error</td>
							<td align=right>{$total_gst_error.g_ttl_gross_amt|round2|number_format:2}&nbsp;</td>
							<td align=right>{$total_gst_error.g_ttl_amt|round2|number_format:2}&nbsp;</td>
							<td align=right>{$total_gst_error.g_ttl_gst_amt|round2|number_format:2}&nbsp;</td>
						</tr>
					{/if}
				
					{if $total_non_gst}
						<tr bgcolor="#ffee99" class="sortbottom">
							<td colspan="{$ttl_colspan2}"></td>		
							<td align=center>Non GST</td>
							<td align=right>{$total_non_gst.inv_amt|round2|number_format:2}&nbsp;</td>
							<td align=right>-&nbsp;</td>
							<td align=right>{$total_non_gst.inv_amt|round2|number_format:2}&nbsp;</td>
						</tr>
					{/if}
				{/if}
				{/if}
				</table>
		</div>
	</div>
</div>

 <!-- end -->
{/if}

<script>
{if $do_list}
    ts_makeSortable($('tbl_do'));
{/if}

{literal}
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
    summary_type_changed();
{/literal}
</script>
{include file=footer.tpl}