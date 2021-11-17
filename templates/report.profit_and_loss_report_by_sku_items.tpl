{*
10/14/2011 4:31:20 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

06/30/2020 11:47 AM Sheila
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
.c1 { background:#ff9; }
.c2 { background:none; }
.r1 { background:#33ff99;}
.r2 { background:#66ccff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
{/literal}
</style>

{literal}
<script>
function toggle_sub(tbody_id, el)
{
	if ($(tbody_id).style.display=='none')
	{
	    el.src='/ui/collapse.gif';
	    $(tbody_id).style.display='';
	}
	else
	{
	    el.src='/ui/expand.gif';
	    $(tbody_id).style.display='none';
	}
}

function change_filter_status(){
	var filter_by = getRadioValue(document.f_a.filter_type)
	
	if(filter_by == 'multi_sku'){
	    $('by_category').style.display='none';
		$('by_multi_sku').style.display='';
	}else{
        $('by_multi_sku').style.display='none';
        $('by_category').style.display='';
	}
}
</script>
{/literal}
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
		<form method=post class=form name="f_a" onSubmit="passArrayToInput()">
			<input type=hidden name=report_title value="{$report_title}">
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label mt-2">Date From</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				
				</div>
				
				<div class="col-md-4">
					<b class="form-label mt-2">To</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				
				</div>
				</div>
				
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label mt-2">Branch</b> <select class="form-control" name="branch_id">
					  <option value='' {if $smarty.request.branch_id eq ''} selected {/if}>-- All --</option>
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
							<optgroup label="Branch Group">
								{foreach from=$branch_group.header item=r}
									{capture assign=bgid}bg,{$r.id}{/capture}
									<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
								{/foreach}
							</optgroup>
						{/if}
					</select>
				{/if}
				</div>
				
				<div class="col-md-4">
					<b class="form-label mt-2">Minimum GP</b>
				<div class="form-inline">
					<input class="form-control" type=text name=min_gp value="{$smarty.request.min_gp|default:'0'}" size=22> <b>&nbsp;%</b>
				</div>
				
				</div>
				
				<div class="col-md-4">
					<b class="form-label mt-2">Filter By</b>
				<input type=radio name="filter_type" value="multi_sku" {if $smarty.request.filter_type eq 'multi_sku' or $smarty.request.filter_type eq ''}checked{/if} onClick="change_filter_status()">Multiple SKU Items 
				<input type=radio name="filter_type" value="category" {if $smarty.request.filter_type eq 'category'}checked{/if} onClick="change_filter_status()">Department/Category 
				
				</div>
			</div>
			
			</p>
			<p>
			<div id="by_multi_sku" style="display:none;">
			{include file="sku_items_autocomplete_multiple.tpl"}
			</div>
			
			<div id="by_category" style="display:none;">
			{if $config.allow_all_sku_branch_for_selected_reports}
			{include file="category_autocomplete.tpl" all=true}
			{else}
			{include file="category_autocomplete.tpl" all=false}
			{/if}
			</div>
			</p>
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}
				<!--Branch: {$branch_name|default:"All"}
From: {$smarty.request.date_from}
To: {$smarty.request.date_to}
Minimum GP: {$smarty.request.min_gp|default:'0'} %
Filter by: {if $smarty.request.filter_type eq 'category'}Category{else}Multiple SKU{/if}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=report_table width=100%>
				<div class="thead bg-gray-100" style="height: 25px;">
					<tr class=header>
						<th>ARMS Code</th>
						<th>Description</th>
						<th>Quantity</th>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<th>Cost</th>
						{/if}
						<th>Retail</th>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<th>GP %</th>
						{/if}
					</tr>
				</div>
				{assign var=last_category value=''}
				
				{foreach from=$table key=c item=g}
					{if $last_category ne $c}
						{if $last_category ne ''}
							</tbody>
						{/if}
						<tbody class="fs-08">
							<tr>
								<td colspan=2 class="r2">{$category2.$c.description}
								{if !$no_header_footer}
								<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$c}',this)">
								{/if}
								</td>
								<td class="r r1">{$category2.$c.qty|qty_nf|ifzero:'-'}</td>
								{if $sessioninfo.privilege.SHOW_REPORT_GP}
									<td class="r r1">{$category2.$c.cost_price|number_format:2|ifzero:'-'}</td>
								{/if}
								<td class="r r1">{$category2.$c.selling_price|number_format:2|ifzero:'-'}</td>
					
								{if $sessioninfo.privilege.SHOW_REPORT_GP}
									{assign var=gp value=$category2.$c.selling_price-$category2.$c.cost_price}
									{assign var=gp value=$gp/$category2.$c.selling_price}
									<td class="r r1">{$gp*100|number_format:2|ifzero:'-':'%'}</td>
								{/if}
								{assign var=last_category value=$c}
							</tr>
						</tbody>
						<tbody class="fs-08" style="display:none" id="tbody_{$c}">
					{/if}
					{foreach from=$g item=r}
						{cycle values="c2,c1" assign=row_class}
						<tr class="{$row_class}">
							<td>{$r.sku_item_code}</td>
							<td>{$r.description}</td>
							<td class=r>{$r.qty|qty_nf|ifzero:'-'}</td>
							{if $sessioninfo.privilege.SHOW_REPORT_GP}
								<td class=r>{$r.cost_price|number_format:2|ifzero:'-'}</td>
							{/if}
							<td class=r>{$r.selling_price|number_format:2|ifzero:'-'}</td>
							{if $sessioninfo.privilege.SHOW_REPORT_GP}
								<td class=r>{$r.gp|number_format:2|ifzero:'-':'%'}</td>
							{/if}
						</tr>
					{/foreach}
						{if $last_category eq ''}
						<tr>
							<td colspan=2 class="r3 r">Total</td>
							<td class="r r1">{$category2.$c.qty|qty_nf|ifzero:'-'}</td>
							{if $sessioninfo.privilege.SHOW_REPORT_GP}
								<td class="r r1">{$category2.$c.cost_price|number_format:2|ifzero:'-'}</td>
							{/if}
							<td class="r r1">{$category2.$c.selling_price|number_format:2|ifzero:'-'}</td>
				
							{if $sessioninfo.privilege.SHOW_REPORT_GP}
								{assign var=gp value=$category2.$c.selling_price-$category2.$c.cost_price}
								{assign var=gp value=$gp/$category2.$c.selling_price}
								<td class="r r1">{$gp*100|number_format:2|ifzero:'-':'%'}</td>
							{/if}
							{assign var=last_category value=$c}
						</tr>
						{/if}
				{/foreach}
				{if $last_category ne ''}
					</tbody>
					<tr>
						<td colspan=2 class="r3 r">Total {if $smarty.request.filter_type eq 'category'}of {$smarty.request.category}{/if}</td>
						<td class="r r1">{$total.qty|qty_nf|ifzero:'-'}</td>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<td class="r r1">{$total.cost_price|number_format:2|ifzero:'-'}</td>
						{/if}
						<td class="r r1">{$total.selling_price|number_format:2|ifzero:'-'}</td>
						
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							{assign var=gp value=$total.selling_price-$total.cost_price}
							{assign var=gp value=$gp/$total.selling_price}
							<td class="r r1">{$gp*100|number_format:2|ifzero:'-':'%'}</td>
						{/if}
					</tr>
				{/if}
				
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
    
    reset_sku_autocomplete();
    change_filter_status();
</script>
{/literal}
{/if}
{include file=footer.tpl}
