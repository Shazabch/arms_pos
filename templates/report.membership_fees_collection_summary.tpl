{*
3/27/2015 6:20 PM Justin
- Enhanced to have GST info.

9/13/2018 9:46 AM Justin
- Bug fixed on amount is zero when the membership renewal is no longer at GST.

06/29/2020 02:15 PM Sheila
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
/*.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}*/

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.clr_red {
	color:#FF0000;
}

.clr_blue {
	color:#306EFF;
}
</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var branch_id = "{$smarty.request.branch_id}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var sales_type = "{$smarty.request.sales_type}";
var sa_id = "{$smarty.request.sa_id}";
{literal}
function toggle_date_details(obj, said, bid, ym, target_sales_amt){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dtl_"+said+"_"+bid+"_"+ym);

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
			sa_id: said,
			bid: bid,
			ym: ym,
			target_sales_amt: target_sales_amt,
			sales_type: sales_type
		},
		onComplete: function(e){
			new Insertion.After($("mst_"+said+"_"+bid+"_"+ym), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
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
					{if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-4">
						<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id">
						<option value="">-- All --</option>
						{foreach from=$branches item=b}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
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
					</div>
				{/if}
				<div class="col-md-4">
					<b class="form-label">Date From</b> 
				<div class="form-inline">
					<input class="form-control" size="20" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
			&nbsp;	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
				</div>
				</div>
				
			<div class="col-md-4">
				<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size="20" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
				
			</div>
				</div>
			</div>
			</p>
			<p>
			* View in maximum 1 year
			</b></p>
			</p>
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align="center">-- No data --</p>{/if}
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
				<table class="rpt_table" width="100%" class=" table mb-0 text-md-nowrap  table-hover"  id="report_tbl">
					<thead class="bg-gray-100">
						<tr class="header">
							<th width="10%">Branch</th>
							<th width="5%">Date</th>
							<th width="10%">Transaction <br />Type</th>
							<th width="10%">Total No.<br />of Transaction</th>
							<th width="10%">Total Amount<br />Collected</th>
							{if $have_gst}
								<th width="10%">Total GST</th>
								<th width="10%">Total Amount<br />Collected<br />Incl. GST</th>
							{/if}
						</tr>
					</thead>
					<tbody class="fs-08">
						{foreach from=$table key=bid item=date_list}
							{assign var=show_branch value=0}
							{foreach from=$date_list key=date item=type_list}
								{assign var=show_date value=0}
								{foreach from=$type_list key=type item=f}
								<tr>
									{if !$show_branch}
										<td rowspan="{$branch_row_span.$bid}" align="center">{$branches.$bid.code}</td>
										{assign var=show_branch value=1}
									{/if}
									{if !$show_date}
										<td rowspan="{$date_row_span.$bid.$date}" align="center">{$date}</td>
										{assign var=show_date value=1}
									{/if}
									<td>
										{if $type eq 'N'}
											New Card
										{elseif $type eq 'L'}
											Lost Card &amp; Replacement
										{elseif $type eq 'R'}
											Renewal
										{elseif $type eq 'LR'}
											Lost &amp; Renewal
										{elseif $type eq 'UC'}
											Upgrade
										{elseif $type eq 'C'}
											Change Card
										{elseif $type eq 'U'}
											Change NRIC or Name
										{elseif $type eq 'ER'}
											Exchange &amp; Renew
										{else}
											{$type}
										{/if}
									</td>
									<td align="right">{$f.count}</td>
									<td align="right">
										{assign var=row_gross_amt value=$f.amount-$f.gst_amount}
										{$row_gross_amt|number_format:2}
										{assign var=ttl_gross_amt value=$ttl_gross_amt+$row_gross_amt}
									</td>
									{if $have_gst}
										{if $f.gst_amount}
											<td align="right">{$f.gst_amount|number_format:2}</td>
											<td align="right">{$f.amount|number_format:2}</td>
											{assign var=ttl_gst_amt value=$ttl_gst_amt+$f.gst_amount}
											{assign var=ttl_amt value=$ttl_amt+$f.amount}
										{else}
											<td align="right">0.00</td>
											<td align="right">{$f.gross_amount|number_format:2}</td>
											{assign var=ttl_amt value=$ttl_amt+$f.gross_amount}
										{/if}
									{/if}
									{assign var=prv_bid value=$bid}
									{assign var=prv_date value=$date}
									{assign var=ttl_cnt value=$ttl_cnt+$f.count}
								</tr>
								{/foreach}
							{/foreach}
						{/foreach}
					</tbody>
					<tr class="header">
						<th colspan="3" align="right">Total</th>
						<th align="right">{$ttl_cnt}</th>
						<th align="right">{$ttl_gross_amt|number_format:2}</th>
						{if $have_gst}
							<th align="right">{$ttl_gst_amt|number_format:2}</th>
							<th align="right">{$ttl_amt|number_format:2}</th>
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
</script>
{/literal}
{/if}

{include file=footer.tpl}
