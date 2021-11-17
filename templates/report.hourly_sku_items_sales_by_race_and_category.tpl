{*
1/26/2011 9:52:52 AM Alex
- change to no use all branch

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

06/30/2020 11:47 AM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
<style>
{literal}
.c1 { background:#ff9; }
.c2 { background:none; }
.r1 { background:#33ff99;}
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
.r5 { background:#33ff99;}
.r6 { background:#ff99ff;}
{/literal}
</style>

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
					<b class="form-label">Date</b>
				 <div class="form-inline">
					<input class="form-control" type=text name=date id=date value="{$smarty.request.date|ifzero:$smarty.now|date_format:'%Y-%m-%d'}" size=22>
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				 </div>
				</div>
				
				
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id">
						<option value="">-- Please Select --</option>
						{if $config.allow_all_sku_branch_for_selected_reports}
						<option value="all" {if $smarty.request.branch_id eq 'all'}selected {/if}>All</option>
						{/if}
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
				
			</div>
			
			<div class="mt-2 mb-2">
				{include file="sku_items_autocomplete_multiple.tpl"}
			</div>
			
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
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
<!--Branch: {$branch_name}
Date: {$smarty.request.date}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{capture assign=hrcount}{count var=$hour}{/capture}
<table class=report_table width=100%>
<thead class="bg-gray-100">
	<tr class=header>
		<th>ARMS Code</th>
		<th>Description</th>
		<th>Race</th>
		<!--<th>9am</th>
		<th>10am</th>
		<th>11am</th>
		<th>12pm</th>
		<th>1pm</th>
		<th>2pm</th>
		<th>3pm</th>
		<th>4pm</th>
		<th>5pm</th>
		<th>6pm</th>
		<th>7pm</th>
		<th>8pm</th>
		<th>9pm</th>
		<th>10pm</th>
		<th>11pm</th>
		<th>12am</th>-->
	  {foreach from=$hour item=h}
		<th>{$h}</th>
	  {/foreach}
		<th>Total</th>
		<th>AVG Hour Amount</th>
	</tr>
</thead>
{foreach from=$category key=code item=c}
    {cycle values="c2,c1" assign=row_class}
    <tbody class="fs-08">
		<tr class="{$row_class}">
			<td>{$code}</td>
			<td>{$c.description}</td>
			<td> {if !$no_header_footer}
			<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
			{/if}
			</td>
			{foreach from=$hour key=hr item=h}
				<td class=r>{$table.$hr.$code.total|number_format:2|ifzero:"-"}</td>
			{/foreach}
			<td class=r>{$table.total.$code.total|number_format:2|ifzero:"-"}</td>
			<td class=r>{$table.total.$code.total/$hrcount|number_format:2|ifzero:"-"}</td>
		</tr>
	</tbody>
    <tbody class="fs-08" style="display:none" id="tbody_{$code}">
    {assign var=race_value value=1}
    {foreach from=$race item=race_lbl}
        <tr class="r{$race_value}">
	        <td colspan=3 class=r>{$race_lbl}</td>
			{foreach from=$hour key=hr item=h}
			    <td class=r>{$table.$hr.$code.$race_lbl|number_format:2|ifzero:"-"}</td>
			{/foreach}
			<td class=r>{$table.total.$code.$race_lbl|number_format:2|ifzero:"-"}</td>
			<td class=r>{$table.total.$code.$race_lbl/$hrcount|number_format:2|ifzero:"-"}</td>
	    </tr>
	    {assign var=race_value value=$race_value+1}
    {/foreach}
    <!--
    <tr class="r1">
        <td colspan=3 class=r>M</td>
		{foreach from=$hours item=h}
		    <td class=r>{$table.$h.$code.M|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class=r>{$table.total.$code.M|number_format:2|ifzero:"-"}</td>
		<td class=r>{$table.total.$code.M/16|number_format:2|ifzero:"-"}</td>
    </tr>
    <tr class=r2>
        <td colspan=3 class=r>C</td>
		{foreach from=$hours item=h}
		    <td class=r>{$table.$h.$code.C|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class=r>{$table.total.$code.C|number_format:2|ifzero:"-"}</td>
		<td class=r>{$table.total.$code.C/16|number_format:2|ifzero:"-"}</td>
    </tr>
    <tr class=r3>
        <td colspan=3 class=r>I</td>
		{foreach from=$hours item=h}
		    <td class=r>{$table.$h.$code.I|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class=r>{$table.total.$code.I|number_format:2|ifzero:"-"}</td>
		<td class=r>{$table.total.$code.I/16|number_format:2|ifzero:"-"}</td>
    </tr>
    <tr class=r4>
        <td colspan=3 class=r>O</td>
		{foreach from=$hours item=h}
		    <td class=r>{$table.$h.$code.O|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class=r>{$table.total.$code.O|number_format:2|ifzero:"-"}</td>
		<td class=r>{$table.total.$code.O/16|number_format:2|ifzero:"-"}</td>
    </tr>
    -->
    </tbody>
{/foreach}
<tr class=header>
	<th colspan=3 class=r>Total</th>
		{foreach from=$hour key=hr item=h}
		    <td class=r>{$table.$hr.total.total|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class=r>{$table.total.total.total|number_format:2|ifzero:"-"}</td>
		<td class=r>{$table.total.total.total/$hrcount|number_format:2|ifzero:"-"}</td>
</tr>
</table>
{/if}
{if !$no_header_footer}
{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
    
    reset_sku_autocomplete();
</script>
{/literal}
{/if}
{include file=footer.tpl}
