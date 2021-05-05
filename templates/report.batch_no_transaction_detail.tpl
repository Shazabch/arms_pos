{*
9/10/2020 11:42 AM William
- Bug fixed Batch No Transaction Details Report.
*}
{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{if !$no_header_footer}
{literal}
<style>
.negative{
	color:red;
}
</style>
<script>

//check form filter
function check_form(){
	var category_id = document.f_a['category_id'];
	if(category_id.value =='' && document.f_a['all_category'].checked == false){
		alert('Please select Category.');
		return false;
	}
	
	var days = document.f_a['days'];
	if(days.value < 0){
		alert("Not allow enter negative days.");
		return false;
	}
}

</script>
{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li><b>{$e}</b></li>
	    {/foreach}
	</ul>
{/if}

<form name="f_a" onsubmit="return check_form();" class="form" method="post">
<input type="hidden" name="a" value="show_report" />
{if !$no_header_footer}
	<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
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
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input name="branch_id" type="hidden" value="{$sessioninfo.branch_id}" />
	{/if}

	<b>Date</b><input size=10 type="text" name="date" value="{$form.date}" id="date">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Expired</b>
		<select name="view_type">
			<option {if $smarty.request.view_type eq '1'}selected {/if} value="1">More Than</option>
			<option {if $smarty.request.view_type eq '2'}selected {/if} value="2">After</option>
			<option {if $smarty.request.view_type eq '3'}selected {/if} value="3">Within</option>
		</select>
		<input type="text" size="5" name="days" maxlength="5" style="text-align:right;" value="{$smarty.request.days}" onchange="mi(this);"> Day(s)&nbsp;&nbsp;&nbsp;&nbsp;
		<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
	</p>

	<p>{include file="category_autocomplete.tpl" all=true}</p>

	<p>
		<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
{/if}
</form>


{if !$table}
	{if $smarty.request.a eq 'show_report' && !$err}<p>-- No Data --</p>{/if}
{else}
<h2>{$report_header}</h2>
{foreach from=$table key=parent_sid item=batch_items}
	<h4>SKU Code: {$batch_items.parent_sku_info.sku_item_code} / Description: {$batch_items.parent_sku_info.description} / MCode: {$batch_items.parent_sku_info.mcode|default:'-'} / Art No: {$batch_items.parent_sku_info.artno|default:'-'}</h4>
	{foreach from=$batch_items.sku_item_list key=sku_item_id item=sku_item_list}
		{foreach from=$sku_item_list key=expired_date item=r}
		<p>
			<b>GRN Date:</b> {$r.batch_info.grn_date|default:'-'}&nbsp;&nbsp;&nbsp;&nbsp;
			<b>GRN Document:</b> <a href="{if !$no_header_footer}{$r.batch_info.grn_doc_link}{/if}" target="_blank">{$r.batch_info.grn_doc_no|default:'-'}</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Batch Qty:</b> {$r.batch_info.batch_qty|default:'-'}&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Batch No:</b> {$r.batch_info.batch_no|default:'-'}&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Expired Date:</b> {$r.batch_info.expired_date|default:'-'}&nbsp;&nbsp;&nbsp;&nbsp;
			{if $r.batch_info.day_expired}
				<b>Days Expired:</b> {$r.batch_info.day_expired|default:'-'}
			{else if $r.batch_info.day_remaining}
				<b>Days Remaining:</b> {$r.batch_info.day_remaining|default:'-'}
			{/if}
		</p>
		<table class="report_table" width="100%" id="report_tbl">
			<tr class="header">
				<th>#</th>
				<th>Branch</th>
				<th>Date</th>
				<th>SKU Item Code</th>
				<th>Doc Type</th>
				<th>Doc Number</th>
				<th>Stock In</th>
				<th>Stock Out</th>
				<th>Balance</th>
			</tr>
			<tbody>
			{assign var=balance value=0}
			{foreach from=$r.data_list item=r2 name=t}
				{assign var=balance value=$balance+$r2.stock_in-$r2.stock_out}
				<tr class="r">
					<td align="left">{$smarty.foreach.t.iteration}.</td>
					<td align="center">{$r2.b_code}</td>
					<td align="left">{$r2.date}</td>
					<td align="left">{$r2.sku_item_code}</td>
					<td align="left">{$r2.doc_type}</td>
					<td align="left">
						{if $r2.doc_link}<a href="{if !$no_header_footer}{$r2.doc_link}{/if}" target="_blank">{$r2.doc_no}</a>{else}{$r2.doc_no}{/if}
					</td>
					<td align="right">{$r2.stock_in}</td>
					<td align="right">{$r2.stock_out}</td>
					<td align="right" class="{if $balance < 0}negative{/if}">{$balance|default:''}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{/foreach}
	{/foreach}
	<br/><br/><br/>
{/foreach}
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
    });
</script>
{/literal}
{/if}
{include file=footer.tpl}
