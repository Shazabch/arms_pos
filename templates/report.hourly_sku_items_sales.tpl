{*
7/17/2012 4:21:34 PM Justin
- Enhanced to show mcode column.

11/2/2012 5:09 PM Justin
- Bug fixed on the row and grand total not tally.

03/10/2016 15:40 Edwin
- Enhanced to enable select child branch group in branch selection

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

option.bg {
	font-weight:bold;
	padding-left:10px;
}

option.bg_item {
	padding-left:20px;
}
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
			
			<div class="mt-2">
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
<h2>{$report_title}</h2>
{capture assign=hrcount}{count var=$hour}{/capture}
<table class=report_table width=100%>
<tr class=header>
	<th>ARMS Code</th>
	<th>MCode</th>
	<th>Description</th>
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
{foreach from=$category key=code item=c}
	{assign var=row_ttl value=0}
    {cycle values="c2,c1" assign=row_class}
    <tr class="{$row_class}">
		<td>{$code}</td>
		<td>{$c.mcode}</td>
		<td>{$c.description}</td>
		{foreach from=$hour key=hr item=h}
		    <td class=r>{$table.$hr.$code.total|number_format:2|ifzero:"-"}</td>
			{assign var=row_ttl value=$row_ttl+$table.$hr.$code.total}
		{/foreach}
		<td class=r>{$row_ttl|number_format:2|ifzero:"-"}</td>
		<td class=r>{$row_ttl/$hrcount|number_format:2|ifzero:"-"}</td>
    </tr>
{/foreach}
<tr class=header>
	<th colspan=3 class=r>Total</th>
		{foreach from=$hour key=hr item=h}
		    <td class=r>{$table.$hr.total.total|number_format:2|ifzero:"-"}</td>
			{assign var=grand_ttl value=$grand_ttl+$table.$hr.total.total}
		{/foreach}
		<td class=r>{$grand_ttl|number_format:2|ifzero:"-"}</td>
		<td class=r>{$grand_ttl/$hrcount|number_format:2|ifzero:"-"}</td>
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
