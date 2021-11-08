{*
10/14/2011 3:20:48 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

5/27/2015 11:13 AM Justin
- Enhanced to have DO Transfer filter if found config set.
- Enhanced to allow user can show report type by GST amount.

10/2/2017 5:03 PM Andy
- Fixed check show_report_gp privilege.

5/21/2018 4:00 pm Kuan Yeh
- Bug fixed of calendar method shown on excel export  

06/30/2020 10:57 AM Sheila
- Updated button css.

10/15/2020 8:16 PM William
- Enhanced to add new tax checking.
*}

{include file='header.tpl'}

{if !$no_header_footer}

{if $config.do_credit_sales_show_sales_person_name or $config.do_cash_sales_show_sales_person_name}
	{assign var=show_sales_person_name value=1}
{/if}

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
#show_sku {
	padding:10px 0;
}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
var do_credit_sales_show_sales_person_name = '{$config.do_credit_sales_show_sales_person_name}';
var do_cash_sales_show_sales_person_name = '{$config.do_cash_sales_show_sales_person_name}';

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

function show_sub(root_id){
	document.f.cat_id.value = root_id
	//document.f.a.value = 'category';
	document.f.submit();
}

function expand_sub(root_id, indent, el, root_per){
	if(el.src.indexOf('clock')>0)   return;
	
	el.onClick='';
	el.src = '/ui/clock.gif';
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_category&ajax=1&cat_id="+root_id+"&indent="+indent+'&root_per='+root_per,
	{
		onComplete: function(e) {
			new Insertion.After($('r'+root_id), e.responseText);
			el.remove();
		},
	});
}

function show_sku(root_id, img){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';
	
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('show_sku',phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_sku&cat_id="+root_id,{
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}

function show_debtor(val){    
    // debtor list
    if(val=='credit_sales') $('div_debtor_list').show();
    else    $('div_debtor_list').hide();
}

function export_itemise_info(root_id){
	document.f.is_itemise_export.value=1;
	document.f.itemise_cat_id.value=root_id;
	document.f.submit();

	document.f.is_itemise_export.value=0;
	document.f.itemise_cat_id.value=0;
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


{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<div class="noprint stdframe">
			<form name="f" method="get">
			<input type="hidden" name="subm" value="1" />
			<input type="hidden" name="itemise_cat_id" value="0" />
			<input type="hidden" name="is_itemise_export" value="0" />
			<input type="hidden" name="cat_id" value="{$smarty.request.cat_id}" />
			
			<div class="row">
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label mt-2">Branch</b>
					<select class="form-control" name="branch_id">
					<option value=''>-- All --</option>
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
						<optgroup label='Branch Group'>
						{foreach from=$branches_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
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
							{/foreach}
						</optgroup>
					{/if}
					</select>
				{/if}
				</div>
			
				<div class="col-md-3">
					<b class="form-label mt-2">SKU Type</b>
					<select class="form-control" name="sku_type">
						<option value="">-- All --</option>
						{foreach from=$sku_type item=r}
							<option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
						{/foreach}
					</select>
					
				</div>

				<div class="col-md-3">
					<b class="form-label mt-2">Date From</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label mt-2">To</b>
			<div class="form-inline">
				<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
			</div>
				</div>
				
				<div class="col-md-3">
					<div class="from-inline form-label mt-3">
						<input type="checkbox" name="by_monthly" value="1" {if $smarty.request.by_monthly}checked {/if} />
					<b>&nbsp;Group Monthly</b>
					</div>
				</div>
				
				<div class="col-md-3">
					<div class="from-inline form-label mt-3">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
				
			
			
				<div class="col-md-3">
					<b class="form-label mt-2">DO Type: </b>
				<select class="form-control" name="do_type" onchange="show_debtor(this.value)">
					<option value="all" >-- All --</option>
					<option value="open" {if $smarty.request.do_type eq 'open'}selected{/if}>Cash Sales</option>
					<option value="credit_sales" {if $smarty.request.do_type eq 'credit_sales'}selected{/if}>Credit Sales</option>
					{if $config.sales_report_include_transfer_do}
						<option value="transfer" {if $smarty.request.do_type eq 'transfer'}selected{/if}>Transfer</option>
					{/if}
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label mt-2">DO Status</b>
				<select class="form-control" name="do_status">
					<option value="0" {if $smarty.request.do_status eq '0'}selected{/if}>-- All --</option>
					<option value="1" {if $smarty.request.do_status eq '1'}selected{/if}>Approved</option>
					<option value="2" {if $smarty.request.do_status eq '2'}selected{/if}>Checkout</option>
				</select>
				</div>
				
				<div class="col-md-3">
					{if $show_sales_person_name}
					<span id="span_sales_person_name" {if !$config.do_cash_sales_show_sales_person_name && !$config.do_credit_sales_show_sales_person_name}style="display:none;"{/if}>
						<b class="form-label mt-2">Sales Person Name</b>
						<select class="form-control" name="sales_person_name">
							<option value="">-- All --</option>
							{foreach from=$sales_person_name_list item=r}
								<option value="{$r.sales_person_name|escape}" {if $smarty.request.sales_person_name eq $r.sales_person_name}selected {/if}>{$r.sales_person_name}</option>
							{/foreach}
						</select>
					</span>
				{/if}
				</div>
			
				
					<span id="div_debtor_list" {if $smarty.request.do_type ne 'credit_sales'}style="display:none"{/if}>
						<div class="col-md-3">
						<b class="form-label mt-2">Debtor</b>
							<select class="form-control" name="debtor_id">
								<option value="">-- All --</option>
								{foreach from=$debtors item=r}
									<option value="{$r.id}" {if $r.id eq $smarty.request.debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							</select>
						</div>	
						</span>
				
			
				<div class="col-md-3">
					<b class="form-label mt-2">Report Type: </b>
				<input type="radio" value="qty" name="report_type" {if $smarty.request.report_type eq 'qty' or !$smarty.request.report_type}checked {/if} /> Sales Qty 
				<input type="radio" value="amt" name="report_type" {if $smarty.request.report_type eq 'amt'}checked {/if} /> Sales Amount 
			<br>
				{if $config.enable_gst || $config.enable_tax}
					<input type="radio" value="gst_amt" name="report_type" {if $smarty.request.report_type eq 'gst_amt'}checked {/if} /> Tax Amount 
					<input type="radio" value="amt_inc_gst" name="report_type" {if $smarty.request.report_type eq 'amt_inc_gst'}checked {/if} /> Sales Amount Included Tax 
				{/if}
				
				{if $sessioninfo.show_report_gp}
				<input type="radio" value="gp" name="report_type" {if $smarty.request.report_type eq 'gp'}checked {/if} /> Gross Profit 
				<input type="radio" value="gp_pct" name="report_type" {if $smarty.request.report_type eq 'gp_pct'}checked {/if} /> GP (%) 
				{/if}
				</div>
			
			</div>
				 <input class="btn btn-primary mt-2" type="submit" value='Show Report' /> 
			
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info mt-2" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
			</form>
			</div>
	</div>
</div>
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if !$no_header_footer}
    <div class="card mx-3">
		<div class="card-body">
			<p>
				&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
				{if $cat_info}
					{foreach from=$cat_info.cat_tree_info item=ct}
						<a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
					{/foreach}
					{$cat_info.description} /
				{/if}
				<br /><font class="small">Click on a sub-category for further detail. Click <img src="/ui/icons/table.png" align="absmiddle" />  to display SKU in the category.</font>
			</p>
		</div>
	</div>
{/if}

{if !$tb}
	{if $smarty.request.subm}No Data{/if}
{else}
<div class="card mx-3">
	<div class="card-body">
		<table class="sortable tb table mb-0 text-md-nowrap  table-hover" >
		
				<thead class="bg-gray-100">
					<tr>
						<th align="left">&nbsp;</th>
				
						{assign var=lasty value=0}
						{assign var=lastm value=0}
						{foreach from=$uq_cols key=dt item=d}
							<th valign="bottom">
							{if $smarty.request.by_monthly}
								{if $lasty ne $d.y}
									<span class="small">{$d.y}</span><br />
									{assign var=lasty value=$d.y}
								{/if}
								{$d.m|str_month|truncate:3:''}
								</th>
							{else}
								{if $lastm ne $d.m or $lasty ne $d.y}
									<span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
									{assign var=lastm value=$d.m}
									{assign var=lasty value=$d.y}
								{/if}
								{$d.d}
								</th>
							{/if}
						{/foreach}
						<th>Total<br />Qty</th>
						<th>Amount</th>
						
						{if $config.enable_gst || $config.enable_tax}
							<th>Tax</th>
							<th>Amt Inc Tax</th>
						{/if}
						
						{if $sessioninfo.show_report_gp}
							<th>Cost</th>
							<th>GP</th>
							<th>GP(%)</th>
						{/if}
						<th>Contrib<br>(%)</th>
					</tr>
				</thead>
			
		
			{include file='report.category_cash_credit_sales.table.tpl'}
			
			<tr class=sortbottom>
			<th align="right">Total</th>
		
			{foreach from=$uq_cols key=dt item=d}
				{assign var=fmt value="%0.2f"}
				{if $smarty.request.report_type eq 'qty'}
					{assign var=fmt value="qty"}
					{assign var=val value=$tb_total.data.$dt.qty}
				{elseif $smarty.request.report_type eq 'amt'}
					{assign var=val value=$tb_total.data.$dt.amt}
				{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
					{assign var=val value=$tb_total.data.$dt.amt-$tb_total.data.$dt.cost}
				{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
					{assign var=fmt value="%0.2f%%"}
					{if $tb_total.data.$dt.amt eq 0}
						{assign var=val value=''}
					{else}
						{assign var=gp value=$tb_total.data.$dt.amt-$tb_total.data.$dt.cost}
						{assign var=val value=$gp/$tb_total.data.$dt.amt*100}
					{/if}
				{elseif $smarty.request.report_type eq 'gst_amt'}
					{assign var=val value=$tb_total.data.$dt.tax_amount}
				{elseif $smarty.request.report_type eq 'amt_inc_gst'}
					{assign var=val value=$tb_total.data.$dt.amt_inc_gst}
				{/if}
				{capture assign=tooltip}
					Qty:{$tb_total.data.$dt.qty|value_format:'qty'}  /  Amt:{$tb_total.data.$dt.amt|string_format:'%.2f'}  /  Cost:{$tb_total.data.$dt.cost|string_format:'%.3f'}
				{/capture}
				<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
			{/foreach}
		
			<td class="small" align="right">{$tb_total.data.total.qty|value_format:'qty'}</td>
			<td class="small" align="right">{$tb_total.data.total.amt|value_format:'%0.2f'}</td>
			
			{if $config.enable_gst || $config.enable_tax}
				{* GST *}
				<td class="small" align="right">{$tb_total.data.total.tax_amount|value_format:'%0.2f'}</td>
				
				{* Amt Inc GST *}
				<td class="small" align="right">{$tb_total.data.total.amt_inc_gst|value_format:'%0.2f'}</td>
			{/if}
			
			{if $sessioninfo.show_report_gp}
				<td class="small" align="right">{$tb_total.data.total.cost|value_format:'%0.2f'}</td>
				{assign var=gp value=$tb_total.data.total.amt-$tb_total.data.total.cost}
				<td class="small" align="right">{$gp|value_format:"%0.2f"}</td>
				{if $tb_total.data.total.amt>0}
					{assign var=gp_per value=$gp/$tb_total.data.total.amt*100}
				{else}
					{assign var=gp_per value=0}
				{/if}
				<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
			{/if}
			</tr>
		</table>
		
	</div>
</div>
<div id="show_sku"></div>
{/if}

{include file='footer.tpl'}

{if !$no_header_footer}
	<script>
		{literal}

		init_calendar();

		{/literal}
	</script>
{/if}