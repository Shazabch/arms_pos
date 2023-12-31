{*
10/11/2010 12:24:09 PM Andy
- Add race filter for report.
- Make the report able to choose all branch, single branch or branch group.

06/30/2020 02:25 PM Sheila
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
.c1 { background:#9ff; }
.c2 { background:#f9f; }
.c3 { background:#f99; }
.c4 { background:#9f9; }
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
{/literal}
</style>
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
		<form method=post class=form>
			<div class="row">
				
			{if $BRANCH_CODE eq 'HQ'}
			<div class="col-md-3">
				<b class="form-label">Branch</b> 
			<select class="form-control" name="branch_id">
					<option value=''>-- All --</option>
					{foreach from=$branches key=bid item=r}
						{if !$branches_group.have_group.$bid}
							<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
					{/foreach}
					{if $branches_group.header}
						<optgroup label='Branch Group'>
						{foreach from=$branches_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
								{foreach from=$branches_group.items.$bgid item=r}
									<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							{/foreach}
						</optgroup>
					{/if}
					</select>
			</div>
			{/if}
			
			<div class="col-md-3">
				<input type=hidden name=report_title value="{$report_title}">
			<b class="form-label">From</b> 
			<div class="form-inline">
				<input class="form-control" size=15 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col-md-3">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=15 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			
			<div class="col-md-3">
				<b class="form-label">Race</b> 
			<select class="form-control" name="race">
				<option value="">-- All --</option>
				{foreach from=$race_list key=k item=race}
					<option value="{$k}" {if $smarty.request.race eq $k}selected {/if}>{$race}</option>
				{/foreach}
			</select>
			</div>
			
			</div>
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				<b>Note:</b> Report Maximum Shown 365 Days
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

<!--Branch: {$branch_name}
Date: from {$smarty.request.date_from} to {$smarty.request.date_to}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th rowspan="2">Date</th>
						<th rowspan=2>Day</th>
						<th rowspan=2>Actual</th>
						<th colspan=2>Member Sales</th>
						<th colspan=4>Member Sales Race Contribution</th>
						<th colspan=2>Non-Member Sales</th>
						<th colspan=4>Non-Member Sales Race Contribution</th>
						<th colspan=2>Transaction Count</th>
						<th colspan=2>Buying Power</th>
					</tr>
					<tr class=header>
						<th>Amount</th>
						<th>%</th>
						<th>Malay</th>
						<th>Chinese</th>
						<th>Indian</th>
						<th>Others</th>
						<th>Amount</th>
						<th>%</th>
						<th>Malay</th>
						<th>Chinese</th>
						<th>Indian</th>
						<th>Others</th>
						<th>Member</th>
						<th>Non-Member</th>
						<th>Member</th>
						<th>Non-Member</th>
					</tr>
				</thead>
				{foreach from=$table key=date item=r}
				<tbody class="fs-08">
					<tr>
						<td nowrap>{$r.dmy.date}</td>
						<td>{$r.day}</td>
						<td class=r>{$r.amount.total|number_format:2|ifzero:"-"}</td>
						<td class="r c1">{$r.amount.MEMBER|number_format:2|ifzero:"-"}</td>
						<td class="r c1">{$r.amount.MEMBER/$r.amount.total*100|number_format:2|ifzero:"-"}%</td>
						<td class="r c1">{$r.MEMBER.M.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c1">{$r.MEMBER.C.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c1">{$r.MEMBER.I.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c1">{$r.MEMBER.O.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c2">{$r.amount.NON_MEMBER|number_format:2|ifzero:"-"}</td>
						<td class="r c2">{$r.amount.NON_MEMBER/$r.amount.total*100|number_format:2|ifzero:"-"}%</td>
						<td class="r c2">{$r.NON_MEMBER.M.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c2">{$r.NON_MEMBER.C.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c2">{$r.NON_MEMBER.I.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c2">{$r.NON_MEMBER.O.amount|number_format:2|ifzero:"-"}</td>
						<td class="r c3">{$r.transaction_count.MEMBER|number_format|ifzero:"-"}</td>
						<td class="r c3">{$r.transaction_count.NON_MEMBER|number_format|ifzero:"-"}</td>
						<td class="r c4">{if $r.transaction_count.MEMBER>0}{$r.amount.MEMBER/$r.transaction_count.MEMBER|number_format:2|ifzero:"-"}{else}-{/if}</td>
						<td class="r c4">{if $r.transaction_count.NON_MEMBER>0}{$r.amount.NON_MEMBER/$r.transaction_count.NON_MEMBER|number_format:2|ifzero:"-"}{else}-{/if}</td>
					</tr>
				</tbody>
				{/foreach}
				{array_sum_by_key array=$table keys='amount,total' assign=v1}
				{array_sum_by_key array=$table keys='amount,MEMBER' assign=v2}
				{array_sum_by_key array=$table keys='MEMBER,M,amount' assign=v3}
				{array_sum_by_key array=$table keys='MEMBER,C,amount' assign=v4}
				{array_sum_by_key array=$table keys='MEMBER,I,amount' assign=v5}
				{array_sum_by_key array=$table keys='MEMBER,O,amount' assign=v6}
				{array_sum_by_key array=$table keys='amount,NON_MEMBER' assign=v7}
				{array_sum_by_key array=$table keys='NON_MEMBER,M,amount' assign=v8}
				{array_sum_by_key array=$table keys='NON_MEMBER,C,amount' assign=v9}
				{array_sum_by_key array=$table keys='NON_MEMBER,I,amount' assign=v10}
				{array_sum_by_key array=$table keys='NON_MEMBER,O,amount' assign=v11}
				{array_sum_by_key array=$table keys='transaction_count,MEMBER' assign=v12}
				{array_sum_by_key array=$table keys='transaction_count,NON_MEMBER' assign=v13}
				
				
				<tr class=header>
					<td colspan=2 class=r>Total</td>
					<td class=r>{$v1|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v2|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v2/$v1*100|number_format:2|ifzero:"-"}%</td>
					<td class=r>{$v3|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v4|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v5|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v6|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v7|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v7/$v1*100|number_format:2|ifzero:"-"}%</td>
					<td class=r>{$v8|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v9|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v10|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v11|number_format:2|ifzero:"-"}</td>
					<td class=r>{$v12|number_format|ifzero:"-"}</td>
					<td class=r>{$v13|number_format|ifzero:"-"}</td>
					<td class=r>{if $v12 >0}{$v2/$v12|number_format:2|ifzero:"-"}{else}-{/if}</td>
					<td class=r>{if $v13 >0}{$v7/$v13|number_format:2|ifzero:"-"}{else}-{/if}</td>
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

