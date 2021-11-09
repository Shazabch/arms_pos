{*
1/14/2011 6:53:20 PM Justin
- Added report can expand by either Date or Vendor.

1/25/2011 11:41:25 AM Justin
- Added the function to automate change the label show maximum period can be printed between date and vendor chosen by user.

11/16/2011 1:46:15 PM Andy
- Fix toggle "Use GRN" checkbox error.

11/24/2011 2:35:52 PM Andy
- Change "Use GRN" popup information message.

12/27/2011 11:01:32 AM Justin
- Fixed the bugs where system unable to show Report Maximum Shown 1 month/year after report has been generated.
- Fixed the important note labels and added a new important note "Sales based on masterfile vendors".

11/1/2012 4:03 PM Justin
- Bug fixed on Use GRN does not show up in filter option.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5/9/2013 4:58 PM Fithri
- vendor sort by A-Z

11/16/2017 11:08 AM Justin
- Enhanced to have "IBT GRN" column.

06/30/2020 11:31 AM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}

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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	color:red;
}
</style>

{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var branch_id = "{$smarty.request.branch_id}";
var vendor_id = "{$smarty.request.vendor_id}";
var view_type = "{$smarty.request.view_type}";
var use_grn = "{$smarty.request.use_grn}";
var owner_id = "{$smarty.request.owner_id}";

{literal}

function show_date_details(dept_id, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dept_chid_"+dept_id);
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).show();
		}
		
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).hide();
		}
	}
	
	if(all_tr.length>0)	return false;
	
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself, {
		parameters: {
			a: 'ajax_show_date_details',
			ajax: 1,
			dept_id: dept_id,
			date_from: date_from,
			date_to: date_to,
			view_type: view_type,
			branch_id: branch_id,
			vendor_id: vendor_id,
			use_grn: use_grn,
			owner_id: owner_id
		},
		onComplete: function(e){
			new Insertion.After($('tr_dept_'+dept_id), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

function chk_vd_filter(){
	var allow_use_grn = true;
	if(document.f_a['branch_id']){
		if(!document.f_a['branch_id'].value)	allow_use_grn = false;
	}
	
	if(!$('vendor_id').value)	allow_use_grn = false;

	if(allow_use_grn){
		$('use_grn').disabled=false;
	}
	else{
		$('use_grn').checked=false;	
		$('use_grn').disabled=true;	
	}
}

function change_date_label(obj){
	if(obj.value == "vendor") $('year_month_label').update("Year");
	else $('year_month_label').update("Month");
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
<div class="alert alert-danger rounded mx-3">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">
			<p>
			<div class="row">
				<div class="col">
					<b class="form-label">Date</b> 
				<div class="form-inline">
					<input  class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
				</div>
				</div>
				
				
				<div class="col">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
				</div>
				</div>
				
				
				<div class="col">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b>
				<select class="form-control" name="branch_id" id="branch_id" onChange="chk_vd_filter();">
					<option value="">-- All --</option>
					 {foreach from=$branches key=bid item=b}
					 
							{if $config.sales_report_branches_exclude}
							{if in_array($b.code,$config.sales_report_branches_exclude)}
							{assign var=skip_this_branch value=1}
							{else}
							{assign var=skip_this_branch value=0}
							{/if}
							{/if}
					 
						{if !$branches_group.have_group.$bid and !$skip_this_branch}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/if}
						
					{/foreach}
					{foreach from=$branches_group.header key=bgid item=bg}
						<optgroup label="{$bg.code}">
							{foreach from=$branches_group.items.$bgid key=bid item=b}
							
								{if $config.sales_report_branches_exclude}
								{if in_array($b.code,$config.sales_report_branches_exclude)}
								{assign var=skip_this_branch value=1}
								{else}
								{assign var=skip_this_branch value=0}
								{/if}
								{/if}
							
									{if !$skip_this_branch}
								<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
									{/if}
								
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				{/if}
				</div>
	
				<div class="col">
					<b class="form-label">View By</b>
				<input type="radio" name="view_type" onclick="change_date_label(this);" value="date" {if !$smarty.request.view_type || $smarty.request.view_type eq 'date'}checked{/if}> Date&nbsp;
				<input type="radio" name="view_type" onclick="change_date_label(this);" value="vendor"{if $smarty.request.view_type eq 'vendor'}checked{/if}> Vendor
			
				</div>
			</div>
		</p>
			<p>
			<div class="row">
				<div class="col">
					<b class="form-label">Vendor</b>
			<select class="form-control" name=vendor_id id=vendor_id onChange="chk_vd_filter();">
			<option value="">-- All --</option>
			{section name=i loop=$vendor1}
			{if $vendor1[i].id != ''}
			<option value="{$vendor1[i].id}" {if $smarty.request.vendor_id eq $vendor1[i].id}selected{/if}>{$vendor1[i].description}</option>
			{/if}
			{/section}
			</select>
				</div>
			
			
			<div class="col">
				<div class="form-inline form-label mt-2">
					<input type=checkbox id=use_grn name=use_grn {if $smarty.request.use_grn}checked{/if} {if $smarty.request.vendor_id == ''}disabled{/if}> <b>&nbsp;Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
				</div>
			</div>
			
			
			<div class="col">
				<b class="form-label">PO Owner</b>
			<select class="form-control" name=owner_id>
			<option value=0>-- All --</option>
			{section name=i loop=$user}
			<option value={$user[i].id} {if ($smarty.request.owner_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.owner_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
			{/section}
			</select>
			</div>
			</div>
			</p>
			
			<p>
			<div class="alert alert-primary rounded " style="max-width: 500px;">
				<b>Important:</b> <br>
			* Report Maximum Shown 1 <span id="year_month_label">{if !$smarty.request.view_type || $smarty.request.view_type eq 'date'}Month{else}Year{/if}</span>.
			<br>
			* PO is based on PO date. <br>
			* GRN is based on GRR Receive Date. <br>
			* PO Owner is use to filter for PO transactions only.
			{if $smarty.request.view_type eq 'vendor'}
			<br />* Sales based on masterfile vendors.
			
			{/if}
		</div>
			</p>
			
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</form>
	</div>
</div>
<script>chk_vd_filter();</script>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%" id="report_tbl">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="2" width="16%">Category</th>
						<th colspan="2" width="9%">Purchase Order</th>
						<th colspan="2" width="9%">Delivered</th>
						<th colspan="2" width="9%">Undelivered</th>
						<th colspan="2" width="9%">Expire PO</th>
						<th colspan="2" width="9%">IBT GRN</th>
						<th colspan="2" width="9%">Vendor GRN With PO</th>
						<th colspan="2" width="9%">Vendor GRN Without PO</th>
						<th colspan="2" width="9%">Total GRN</th>
						<th rowspan="2" width="5%">Total Sales</th>
						<th rowspan="2" width="5%">Variance</th>
						<th rowspan="2" width="3%">Variance %</th>
					</tr>
					<tr class="header">
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
						<th>Amount</th>
						<th>Count</th>
					</tr>
				</thead>
				{foreach from=$table key=date_key item=d}
					<tbody class="fs-08">
						<tr class="{if $d.day=='0' or $d.day eq 6}weekend{/if}" id="tr_dept_{$table.$date_key.id}">
							{assign var=pur_amt value=$table.$date_key.grn_ibt_amt+$table.$date_key.grn_wpo_amt+$table.$date_key.grn_wopo_amt}
							{assign var=pur_count value=$table.$date_key.grn_ibt_count+$table.$date_key.grn_wpo_count+$table.$date_key.grn_wopo_count}
							{assign var=var_amt value=$table.$date_key.sales_amt-$pur_amt}
							{if $pur_amt && $var_amt}
								  {assign var=var_perc value=$var_amt/$pur_amt*100}
							  {else}
								  {assign var=var_perc value=0}
							  {/if}
							<td>{if $print_excel == ''}<img src="/ui/expand.gif" onclick="javascript:void(show_date_details('{$table.$date_key.id|default:0}', this));" align=absmiddle>{/if} {$table.$date_key.description}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.po_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{if $print_excel == ''}{if $table.$date_key.po_count != 0}<a href="/purchase_order.summary.php?a=show&department_id={$table.$date_key.id}&from={$smarty.request.date_from}&to={$smarty.request.date_to}&branch_id={$smarty.request.branch_id}&vendor_id={$smarty.request.vendor_id}&use_grn={$smarty.request.use_grn}&user_id={$smarty.request.owner_id}" target="_blank">{$table.$date_key.po_count|default:0|ifzero:'-'}</a>{else}-{/if}{else}{$table.$date_key.po_count|default:0|ifzero:'-'}{/if}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.drv_po_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.drv_po_count|number_format:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.undrv_po_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.undrv_po_count|number_format:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.exp_po_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#c6deff>{$table.$date_key.exp_po_count|number_format:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_ibt_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_ibt_count|default:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_wpo_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_wpo_count|default:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_wopo_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$table.$date_key.grn_wopo_count|default:0|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{$pur_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#e0ffff>{if $print_excel == ''}{if $pur_amt != 0}<a href="/goods_receiving_note.summary.php?a=show&department_id={$table.$date_key.id}&from={$smarty.request.date_from}&to={$smarty.request.date_to}&branch_id={$smarty.request.branch_id}&vendor_id={$smarty.request.vendor_id}&use_grn={$smarty.request.use_grn}" target="_blank">{$pur_count|number_format:0|ifzero:'-'}</a>{else}-{/if}{else}{$pur_count|number_format:0|ifzero:'-'}{/if}</td>
							<td class="r" bgcolor=#addfff>{$table.$date_key.sales_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#addfff {if round($var_amt,2) < 0}style="color:red"{/if}>{$var_amt|number_format:2|ifzero:'-'}</td>
							<td class="r" bgcolor=#addfff {if round($var_perc,2) < 0}style="color:red"{/if}>{$var_perc|string_format:'%.2f'|ifzero:'-':'%'}</td>
						</tr>
						
					</tbody>
					{assign var=ttl_po_amt value=$ttl_po_amt+$table.$date_key.po_amt}
					{assign var=ttl_po_count value=$ttl_po_count+$table.$date_key.po_count}
					{assign var=ttl_drv_po_amt value=$ttl_drv_po_amt+$table.$date_key.drv_po_amt}
					{assign var=ttl_drv_po_count value=$ttl_drv_po_count+$table.$date_key.drv_po_count}
					{assign var=ttl_undrv_po_amt value=$ttl_undrv_po_amt+$table.$date_key.undrv_po_amt}
					{assign var=ttl_undrv_po_count value=$ttl_undrv_po_count+$table.$date_key.undrv_po_count}
					{assign var=ttl_exp_po_amt value=$ttl_exp_po_amt+$table.$date_key.exp_po_amt}
					{assign var=ttl_exp_po_count value=$ttl_exp_po_count+$table.$date_key.exp_po_count}
					{assign var=ttl_grn_ibt_amt value=$ttl_grn_ibt_amt+$table.$date_key.grn_ibt_amt}
					{assign var=ttl_grn_ibt_count value=$ttl_grn_ibt_count+$table.$date_key.grn_ibt_count}
					{assign var=ttl_grn_wpo_amt value=$ttl_grn_wpo_amt+$table.$date_key.grn_wpo_amt}
					{assign var=ttl_grn_wpo_count value=$ttl_grn_wpo_count+$table.$date_key.grn_wpo_count}
					{assign var=ttl_grn_wopo_amt value=$ttl_grn_wopo_amt+$table.$date_key.grn_wopo_amt}
					{assign var=ttl_grn_wopo_count value=$ttl_grn_wopo_count+$table.$date_key.grn_wopo_count}
					{assign var=ttl_pur_amt value=$ttl_pur_amt+$pur_amt}
					{assign var=ttl_pur_count value=$ttl_pur_count+$pur_count}
					{assign var=ttl_sales_amt value=$ttl_sales_amt+$table.$date_key.sales_amt}
					{assign var=ttl_var_amt value=$ttl_var_amt+$var_amt}
				{/foreach}
				<tr class="header">
					{assign var=total_gp value=$total_selling-$total_cost}
					{if $ttl_var_amt && $ttl_pur_amt}
						{assign var=ttl_var_perc value=$ttl_var_amt/$ttl_pur_amt*100}
					{/if}
						
					<th class="r">Total</th>
					<th class="r">{$ttl_po_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_po_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_drv_po_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_drv_po_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_undrv_po_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_undrv_po_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_exp_po_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_exp_po_count|number_format:0|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_ibt_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_ibt_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_wpo_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_wpo_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_wopo_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_grn_wopo_count|default:0|ifzero:'-'}</th>
					<th class="r">{$ttl_pur_amt|number_format:2|ifzero:'-'}</th>
					<th class="r">{$ttl_pur_count|number_format:0|ifzero:'-'}</th>
					<th class="r">{$ttl_sales_amt|number_format:2|ifzero:'-'}</th>
					<th class="r" {if round($ttl_var_amt,2) < 0}style="color:red"{/if}>{$ttl_var_amt|number_format:2|ifzero:'-'}</th>
					<th class="r" {if round($ttl_var_perc,2) < 0}style="color:red"{/if}>{$ttl_var_perc|string_format:'%.2f'|ifzero:'-':'%'}</th>
				</tr>
			</table>
		</div>
	</div>
</div>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">

    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

</script>
{/literal}
{/if}

{include file=footer.tpl}
