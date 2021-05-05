{*
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

#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}
<script>
var phpself = "{$smarty.server.PHP_SELF}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var view_type = "{$smarty.request.view_type}";
var category_id = "{$smarty.request.category_id}";

{literal}

function show_details(cid, bid, level, mst_cid, prev_cid, obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.row_details_"+bid+"_"+mst_cid+"_"+level+"_"+prev_cid);

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
			a: 'ajax_show_details',
			ajax: 1,
			date_from: date_from,
			date_to: date_to,
			view_type: view_type,
			category_id: category_id,
			cid: cid,
			branch_id: bid,
			level: level
		},
		onComplete: function(e){
			var root_level = int(level) - 1;
			if($('row_header_'+bid+"_"+mst_cid+"_"+root_level) != undefined) var curr_tr = $("row_header_"+bid+"_"+mst_cid+"_"+root_level);
			else var curr_tr = $("row_details_"+bid+"_"+mst_cid+"_"+root_level+"_"+prev_cid);
			new Insertion.After(curr_tr, e.responseText);
			obj.src = '/ui/collapse.gif';
			obj.hide();
		}
	});
}

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a">
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
	{/if}
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>Show By</b>
	<select name="view_type">
		{foreach from=$view_type key=val item=desc}
			<option value="{$val}" {if $smarty.request.view_type eq $val}selected {/if}>{$desc}</option>
		{/foreach}
	</select>
</p>
<p>
	{include file='category_autocomplete.tpl'}
</p>
<p>
* View in maximum 1 year<br />
* Report view in longer date range will consumes times in showing result.
</b></p>
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align="center">-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th>Branch</th>
			<th>
				{assign var=curr_view_type value=$smarty.request.view_type}
				Category by {$view_type.$curr_view_type}
			</th>
			<th>Total Sales</th>
			<th>Stock Balance</th>
			<th>Sell Thru (%)</th>
		</tr>
		<tbody>
			{foreach from=$table key=bid item=b_list}
				{foreach from=$b_list item=item}
					<tr id="row_header_{$bid}_{$item.category_id}_{$level}">
						{include file="../templates/mizisport/report.sell_thru_by_category.row.tpl" is_header=1}
					</tr>
					{assign var=ttl_amount value=$ttl_amount+$item.amount}
					{assign var=ttl_sb_amount value=$ttl_sb_amount+$item.sb_amount}
				{/foreach}
			{/foreach}
		</tbody>
		<tr class="header">
			<th colspan="2" align="right">Total</th>
			<th align="right">{$ttl_amount|number_format:2}</th>
			<th align="right">{$ttl_sb_amount|number_format:2}</th>
			<th align="right">
				{if $ttl_amount && $ttl_sb_amount}
					{assign var=ttl_sell_thru_perc value=$ttl_amount/$ttl_sb_amount*100}
					{$ttl_sell_thru_perc|number_format:2}%
				{else}
					0.00%
				{/if}
			</th>
		</tr>
	</table>
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
