{*
3/6/2019 2:48 PM Andy
- Enhanced to have Page Selection at the bottom of page.

7/3/2019 5:34 PM William
- Removed the brand code of the "brand filter".

06/30/2020 02:25 PM Sheila
- Updated button css.

9/22/2020 5:21 PM William
- Enhanced to show grand total.

3/30/2021 4:04 PM Sin Rou
- Add "Previous" and "Next" page button.
*}

{include file='header.tpl'}


<style>
{literal}
td.td_branch_code{
	background-color: #ccffcc;
	text-align: center;
}
{/literal}
</style>
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var MULTI_BRANCH_SALES = {
	initialize: function(){
		this.f = document.f_a;
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
	},
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	},
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		// select all sku code
		toggle_select_all_opt(this.f['sku_code_list[]'], true);
		this.f.submit();
	},
	page_changed: function(n){
		var sel_id = 'sel_page_num';
		if(n)	sel_id += '-'+n;
		this.f['page'].value = $(sel_id).value;
		this.submit_report();
	},
	previous_page: function(){
		var curr_page = int($('sel_page_num').value);
		this.f['page'].value = curr_page - 1;	
		this.submit_report();
	},
	next_page: function(){
		var curr_page = int($('sel_page_num').value);
		this.f['page'].value = curr_page + 1;	
		this.submit_report();
	},
	do_print: function (){
		window.print();
	},
	prompt_grand_total_alert: function(t){
		if(!t)	t = 'alert';
		
		if(t == 'confirm'){
			var v = confirm('SHOW GRAND TOTAL is choose.\nThis will cause the report to take extra long time to load.\nThe Grand Total will be show on the last page of export file.\n\nAre You Sure?');
			return v;
		}else{
			alert('This will cause the report to take extra long time to load.\nThe Grand Total will be show on the last page of export file.');
		}
	},
	// function when users change "Show Grand Total"
	show_grand_total_changed: function(){
		var inp = this.f['show_grand_total'];
		if(inp.checked){
			if(!this.prompt_grand_total_alert('confirm')){
				inp.checked = false;
			}
		}
	}
}
{/literal}
</script>

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
	

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="noprint stdframe" method="post" onSubmit="return false;">
			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="export_excel" />
			<input type="hidden" name="print" />
			<input type="hidden" name="page" value="0" />
			
			{if $BRANCH_CODE eq 'HQ'}
				<div>
					<div class="form-inline mb-2">
						<b class="form-label">Select Branch By:</b>
					&nbsp;<select class="form-control" id="sel_brn_grp" >
						<option value="">-- All --</option>
						{foreach from=$branch_group.header key=bgid item=bg}
							<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
						{/foreach}
					</select>&nbsp;
					<input class="btn btn-success mt-2 mt-md-0 ml-0 ml-md-2" type="button" value="Select " onclick="MULTI_BRANCH_SALES.check_branch_by_group(true);" />&nbsp;
					<input class="btn btn-danger mt-2 mt-md-0 ml-1" type="button" value="De-select" onclick="MULTI_BRANCH_SALES.check_branch_by_group(false);" /><br /><br />
					
					</div>
					<div id="div_branch_list" style="padding: 10px; width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
						<table>
						{foreach from=$branches key=bid item=b}
							{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
							<tr>
								<td>
									<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />&nbsp;
									<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
								</td>
							</tr>
						{/foreach}
						</table>
					</div>
				</div>
			{/if}
			<div class="row mt-2">
				<div class="col-md-3">
					<span>
						<b class="form-label">Brand: </b>
						<select class="form-control" name="brand_id">
							<option value="all" {if $smarty.request.brand_id eq 'all' or !isset($smarty.request.brand_id)}selected {/if}>-- All --</option>
							<option value="0" {if isset($smarty.request.brand_id) and $smarty.request.brand_id eq '0'}selected {/if}>UN-BRANDED</option>
							{foreach from=$brands key=bid item=r}
								{if !$brand_group.have_group.$bid}
									<option value="{$bid}" {if $bid eq $smarty.request.brand_id}selected {/if}>{$r.description}</option>
								{/if}
							{/foreach}
							{if $brand_group.header}
								{capture assign=bg_item_padding}{section loop=5 name=i}&nbsp;{/section}{/capture}
								<optgroup label='Brand Group'>
								{foreach from=$brand_group.header key=bgid item=bg}
										<option class="bg" value="{$bgid*-1}"{if $smarty.request.brand_id eq ($bgid*-1)}selected {/if}>{$bg.code} - {$bg.description}</option>
										{foreach from=$brand_group.items.$bgid item=r}
											<option class="bg_item" value="{$r.brand_id}" {if $smarty.request.brand_id eq $r.brand_id}selected {/if}>{$bg_item_padding}{$r.code} - {$r.description}</option>							
										{/foreach}
									{/foreach}
								</optgroup>
							{/if}
						</select>
					</span>
				</div>
				<div class="col-md-3">
					<span>
						<b class="form-label">SKU Type: </b>
						<select class="form-control" name="sku_type">
							<option value="">-- All --</option>
							{foreach from=$sku_types item=r}
								<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected{/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>
				</div>
				<div class="col-md-3">
					<span>
						<b class="form-label">Status: </b>
						<select class="form-control" name="filter_active">
							<option value="">-- All --</option>
							<option value="1" {if $smarty.request.filter_active eq '1' or !isset($smarty.request.filter_active)}selected{/if}>Active</option>
							<option value="-1" {if $smarty.request.filter_active eq '-1'}selected{/if}>Inactive</option>
						</select>
					</span>
				</div>
				<div class="col-md-3">
					<span>
						<b class="form-label">Vendor: </b>
						<select class="form-control" name="vendor_id">
							<option value="">-- All --</option>
							{foreach from=$vendors key=vid item=r}
								<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected{/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>
				</div>
			</div>
			
			<p>
				{include file="category_autocomplete.tpl" all=true}
			</p>
			<p>
				{include file="sku_items_autocomplete_multiple_add2.tpl"}
			</p>
			
			<p>
				<div class="row">
					<div class="col">
						<span>
							<b class="form-label">Sales From: </b>
							<div class="form-inline">
								<input class="form-control" size="23" type="text" name="date_from" value="{$smarty.request.date_from}" id="date_from" readonly>
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;
							</div>
						</span>
					</div>
					<div class="col">
						<span>
							<b class="form-label">Sales To: </b>
							<div class="form-inline">
								<input class="form-control" size="23" type="text" name="date_to" value="{$smarty.request.date_to}" id="date_to" readonly>
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
							</div>
						</span>
					</div>
				
			
				<div class="col">
					<span>
						<b class="form-label">Sort By: </b>
						<select class="form-control" name="sort_by">
							{foreach from=$order_by_list key=k item=v}
								<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$v}</option>
							{/foreach}
						</select>
					</span>
				</div>
			</div>
				<div class="row mt-2">
					<div class="col-md-4">
						<span>
							<div class="form-label form-inline mt-4">
								<input type="checkbox" name="group_by_sku" value="1" {if $smarty.request.group_by_sku}checked {/if} /> <b>&nbsp;Group by SKU</b>
							</div>
						</span>
				
					</div>
				
				{if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-4">
						<p>
							<b class="form-label">Number of Branch Per Line: </b>
							<input class="form-control" type="text" name="branch_per_line" value="{$smarty.request.branch_per_line|default:$branch_per_line}" />
						</p>
					</div>
				{/if}
				
				<div class="col-md-4">
					<span>
						<div class="form-label form-inline mt-4">
							<label><input type="checkbox" name="show_grand_total" value="1" {if $smarty.request.show_grand_total}checked {/if} onChange="MULTI_BRANCH_SALES.show_grand_total_changed();" /> <b>&nbsp;Show Grand Total</b></label> [<a href="javascript:void(MULTI_BRANCH_SALES.prompt_grand_total_alert())">&nbsp;?</a>]
						</div>
					</span>
				</div>
				</div>
			
			<p>
				<button class="btn btn-primary" onClick="MULTI_BRANCH_SALES.submit_report();">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info" onClick="MULTI_BRANCH_SALES.submit_report('excel');">{#OUTPUT_EXCEL#}</button>
				{/if}
				<input class="btn btn-primary" type="button" onclick="MULTI_BRANCH_SALES.do_print();" value="Print" />
			</p>
			
			<p>
				<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
					* Maximum show 3 months.<br/>
				* Maximum show {$page_limit} item per page.
				</div>
			</p>
		</form>
		
	</div>
</div>
{if $smarty.request.show_report and !$err}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

	
	{if !$data.data}
		* No Data
	{else}
		{if $data.total_page > 1}
			<div class="noprint">
				{if $data.curr_page >= 1}
					<input class="btn btn-primary" type="button" value="Previous" onclick="MULTI_BRANCH_SALES.previous_page()"/>&nbsp;
				{/if}
				<b>Go To Page: </b>
				<select id="sel_page_num" onChange="MULTI_BRANCH_SALES.page_changed();">
					{section loop=$data.total_page name=s_page}
						<option value="{$smarty.section.s_page.index}" {if $smarty.request.page eq $smarty.section.s_page.index}selected {/if}>{$smarty.section.s_page.iteration}</option>
					{/section}
				</select>
				{if $data.curr_page < $data.total_page - 1}
					<input class="btn btn-primary" type="button" value="Next" onclick="MULTI_BRANCH_SALES.next_page()"/>&nbsp;
				{/if}
			</div>
		{/if}
		
		{include file='report.multi_branch_sales.table.tpl'}
		
		{if $data.total_page > 1}
			<br />
			<div class="noprint">
				{if $data.curr_page >= 1}
					<input class="btn btn-primary" type="button" value="Previous" onclick="MULTI_BRANCH_SALES.previous_page()"/>&nbsp;
				{/if}
				<b>Go To Page: </b>
				<select id="sel_page_num-2" onChange="MULTI_BRANCH_SALES.page_changed('2');">
					{section loop=$data.total_page name=s_page}
						<option value="{$smarty.section.s_page.index}" {if $smarty.request.page eq $smarty.section.s_page.index}selected {/if}>{$smarty.section.s_page.iteration}</option>
					{/section}
				</select>
				{if $data.curr_page < $data.total_page - 1}
					<input class="btn btn-primary" type="button" value="Next" onclick="MULTI_BRANCH_SALES.next_page()"/>&nbsp;
				{/if}
			</div>
		{/if}
	{/if}
{/if}


<script type="text/javascript">
	MULTI_BRANCH_SALES.initialize();
</script>

{include file='footer.tpl'}