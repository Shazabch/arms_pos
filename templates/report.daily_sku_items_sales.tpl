{*
4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

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
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
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
					<b class="form-label">From</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-4">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id">
						{if $config.allow_all_sku_branch_for_selected_reports}
						<option value="">-- All --</option>
						{/if}
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code} - {$b.description}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
							<optgroup label="Branch Group">
								{foreach from=$branch_group.header key=bgid item=bg}
									<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
									{foreach from=$branch_group.items.$bgid item=r}
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
			</div>
			
			
			<div class="mt-2 mb-2">
				<div class="row">
					<div class="col">
						{include file="sku_items_autocomplete_multiple.tpl"}
					</div>
				</div>
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="alert alert-primary rounded mt-2" style="max-width: 230px;">
				<b>Note:</b> Maximum 31 days
			</div>
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
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}

<!--Branch: {$branch_name}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{assign var=day_count value=''}
{assign var=cols_left value=''}
{assign var=rowspan value=1}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=report_table width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th {if $label|@count>16}rowspan=2{/if}>ARMS Code<br>M.Code<br>Art.No</th>
						<th {if $label|@count>16}rowspan=2{/if}>Description</th>
						{foreach from=$label item=d}
							{if $day_count eq 16}
								<th {if $label|@count>16}rowspan=2{/if}>Total</th>
								<th {if $label|@count>16}rowspan=2{/if}>Contribution</th>
								</tr>
								<tr class=header>
							{/if}
							<th>{$d}</th>
							{assign var=day_count value=$day_count+1}
							
						{/foreach}
						{if $day_count<=16}
							{assign var=cols_left value=16}
							{assign var=cols_left value=$cols_left-$day_count}
							<th>Total</th>
							<th>Contribution</th>
						{else}
							{assign var=rowspan value=2}
							{assign var=cols_left value=32}
							{assign var=cols_left value=$cols_left-$day_count}
							<th colspan="{$cols_left}"></th>
						{/if}
					
						</tr>
				</thead>
				</tr>
				{foreach from=$table key=code item=c}
				<tr>
					<td class=r4 rowspan="{$rowspan}" colspan=2>{$category2.$code.description}
					{if !$no_header_footer}
					<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
					{/if}
					</td>
					{assign var=day_count value=''}
					{foreach from=$label key=lbl item=day}
						{if $day_count eq 16}
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.$code.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.$code.amount.total|number_format:2|ifzero:'-'}
							</th>
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.$code.qty.total/$category2.total.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
								{$category2.$code.amount.total/$category2.total.amount.total*100|number_format:2|ifzero:'-':'%'}
							</th>
							</tr>
							<tr>
						{/if}
						<td class="{if $day_count < 16}r1{else}r2{/if} r">
							{$category2.$code.qty.$lbl|qty_nf|ifzero:'-'}<br>
							{$category2.$code.amount.$lbl|number_format:2|ifzero:'-'}
						</td>
						{assign var=day_count value=$day_count+1}
					{/foreach}
					{if $day_count<=16}
						<th rowspan="{$rowspan}" class="r r4">
								{$category2.$code.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.$code.amount.total|number_format:2|ifzero:'-'}
							</th>
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.$code.qty.total/$category2.total.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
								{$category2.$code.amount.total/$category2.total.amount.total*100|number_format:2|ifzero:'-':'%'}
							</th>
					{else}
						{assign var=cols_left value=32}
						{assign var=cols_left value=$cols_left-$day_count}
						<th colspan={$cols_left}></th>
					{/if}
					</tr>
												<!-- Inside Tbody-->
					<tbody class="fs-08" style="display:none" id="tbody_{$code}">
						{foreach from=$c key=s item=r}
							{cycle values="c2,c1" assign=row_class}
							<tr>
								<td class="{$row_class}" rowspan="{$rowspan}">{$sku.$s.sku_item_code}<br>mcode: {$sku.$s.mcode}<br>ArtNo: {$sku.$s.artno}</td>
								<td class="{$row_class}" rowspan="{$rowspan}">{$sku.$s.description}</td>
												<!-- Start of day Count-->
								{assign var=day_count value=''}
								{foreach from=$label key=lbl item=day}
									{if $day_count eq 16}
										<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total|qty_nf|ifzero:'-'}<br>
											{$r.amount.total|number_format:2|ifzero:'-'}
										</th>
										<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total/$category2.$code.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
											{$r.amount.total/$category2.$code.amount.total*100|number_format:2|ifzero:'-':'%'}
										</th>
										</tr>
										<tr>
									{/if}
									<td class="{if $day_count < 16}r1{else}r2{/if} r">
										{$r.qty.$lbl|qty_nf|ifzero:'-'}<br>
										{$r.amount.$lbl|number_format:2|ifzero:'-'}
									</td>
									{assign var=day_count value=$day_count+1}
								{/foreach}
											<!-- End of day Count-->
										{if $day_count<=16}
									<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total|qty_nf|ifzero:'-'}<br>
											{$r.amount.total|number_format:2|ifzero:'-'}
										</th>
										<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total/$category2.$code.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
											{$r.amount.total/$category2.$code.amount.total*100|number_format:2|ifzero:'-':'%'}
										</th>
								{else}
									{assign var=cols_left value=32}
									{assign var=cols_left value=$cols_left-$day_count}
									<th colspan="{$cols_left}"></th>
								{/if}
							</tr>
						{/foreach}
					</tbody>
												<!-- End of Tbody-->
				{/foreach}
				<tr>
					<td class="r4 r" rowspan="{$rowspan}" colspan=2>Total</td>
					{assign var=day_count value=''}
					{foreach from=$label key=lbl item=day}
						{if $day_count eq 16}
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.total.amount.total|number_format:2|ifzero:'-'}
							</th>
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total/$category2.total.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
								{$category2.total.amount.total/$category2.total.amount.total*100|number_format:2|ifzero:'-':'%'}
							</th>
							</tr>
							<tr>
						{/if}
						<td class="{if $day_count < 16}r1{else}r2{/if} r">
							{$category2.total.qty.$lbl|qty_nf|ifzero:'-'}<br>
							{$category2.total.amount.$lbl|number_format:2|ifzero:'-'}
						</td>
						{assign var=day_count value=$day_count+1}
					{/foreach}
					{if $day_count<=16}
						<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.total.amount.total|number_format:2|ifzero:'-'}
							</th>
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total/$category2.total.qty.total*100|number_format:2|ifzero:'-':'%'}<br>
								{$category2.total.amount.total/$category2.total.amount.total*100|number_format:2|ifzero:'-':'%'}
							</th>
					{else}
						{assign var=cols_left value=32}
						{assign var=cols_left value=$cols_left-$day_count}
						<th colspan="{$cols_left}"></th>
					{/if}
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
    
    reset_sku_autocomplete();
</script>
{/literal}
{/if}
{include file=footer.tpl}
