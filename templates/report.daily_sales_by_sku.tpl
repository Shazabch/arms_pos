{*
1/27/2011 5:48:11 PM Andy
- Add remark for this report:'Note: This report is for weight scale item only.'

10/14/2011 12:09:58 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/22/2018 1:30 pm Kuan Yeh
- Bug fixed of logo shown on excel export  

06/30/2020 10:57 AM Sheila
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

{literal}
<style>

.c1 { background:#9ff; }
.c2 { background:#f9f; }
.c3 { background:#ff9; }
.c4 { background:#f99; }
.c5 { background:#9f9; }
.c6 { background:#99f; }
/*.sun {background:#999; }*/
</style>
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
		<form name=report_form method=post class=form>
			<input type=hidden name=report_title value="{$report_title}">
		<div class="row">
			<div class="col">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> 
			<select class="form-control" name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches item=b}
					
						{if $config.sales_report_branches_exclude}
						{if in_array($b.code,$config.sales_report_branches_exclude)}
						{assign var=skip_this_branch value=1}
						{else}
						{assign var=skip_this_branch value=0}
						{/if}
						{/if}
					
						{if !$branch_group.have_group[$b.id] and !$skip_this_branch}
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
			
			<div class="col">
				<b class="form-label">From</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
		<div class="col">
			<div class="form-label form-inline mt-2">
				<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} />&nbsp;Exclude inactive SKU</label>
			</div>
		</div>
			
		</div>
			<p>{include file="category_autocomplete.tpl" all=true}</p>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			<br>
			<div class="alert alert-primary mt-2 rounded" style="max-width: 350px;">
				Note: This report is for weight scale item only.
			</div>
			{/if}
			</form>
	</div>
</div>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
<br>
<!--Branch: {$branch_code} 
Date: {$smarty.request.date_from} to {$smarty.request.date_to} 
Category: {$cat_desc}-->
</h2>
<div class="card mx-3">
	<div class="card body">
		<div class="table-responsive">
			<table class="report_table small_printing" width=100% >
				<thead class="bg-gray-100">
					<tr class="header">
						<th>ARMS Code</th>
						<th>Description</th>
						<th>Unit Price</th>
						{foreach from=$alldate item=d}
							<th>{if $used_date ne $d|date_format:"%m/%Y"}{$d|date_format:"%m/%Y"}<br>{assign var=used_date value=$d|date_format:"%m/%Y"}{/if}{$d|date_format:"%d"}</th>
						{/foreach}
						<th>Total Qty</th>
						<th>Total Price</th>
					</tr>
				</thead>
				{assign var=grandtotal value=0}
				{assign var=grandtotal_price value=0}
				{foreach name=i from=$data key=sku_item_code item=d}
				<thead class="bg-gray-100">
					<tr onclick="show_detail('{$sku_item_code}')" class=clickable>
						<th>
							{if !$no_header_footer}
								<img id=toggle_{$sku_item_code} src="/ui/expand.gif">
							{/if}
							{$sku_item_code}
						</th>
						<td>{$datasub.$sku_item_code.description}</td>
						<td align=center>-</td>
						{assign var=total value=0}
						{foreach from=$alldate item=dt}
							{assign var=total value=$total+$d.$dt}
							<td align=center>{$d.$dt|qty_nf}</td>
						{/foreach}
						{assign var=grandtotal value=$grandtotal+$total}
						{assign var=grandtotal_price value=$grandtotal_price+$d.total_price}
						<td align=center>{$total|qty_nf}</td>
						<td align=right>{$d.total_price|round2|number_format:2:'.':','}</td>
					</tr>
				</thead>
				<tbody class="fs-08" id="tr_{$sku_item_code}" style="display:none"><tr><td colspan=100>Loading..</td></tr></tbody>
				{/foreach}
				<tr>
					<th colspan=3>Total</th>
					{foreach from=$alldate item=dt}
					{assign var=total value=$total+$d.$dt}
					<td align=center>{$totalqtybydate.$dt|qty_nf|number_format:0:'.':','}</td>
					{/foreach}
					<td align=center>{$grandtotal|round2|number_format:0:'.':','}</td>
					<td align=right>{$grandtotal_price|round2|number_format:2:'.':','}</td>
				</tr>
			</table>
		</div>
	</div>
</div>
{/if}
{if !$no_header_footer}
{literal}
<script type="text/javascript">

function show_detail(id)
{
	$('tr_'+id).innerHTML = '<tr><td colspan=100>Loading...</td></tr>';
	if ($('tr_'+id).style.display == 'none')
	{
		$('toggle_'+id).src = '/ui/collapse.gif';
		$('tr_'+id).style.display = '';
		new Ajax.Updater('tr_'+id,'/report.daily_sales_by_sku.php?'+Form.serialize(document.report_form)+'&a=ajax_load_detail&id='+id+'&ajax=1&view_type=price');
	}
	else
	{
		$('toggle_'+id).src = '/ui/expand.gif';
		$('tr_'+id).style.display = 'none';
	}
}

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

