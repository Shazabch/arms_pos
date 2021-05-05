{*
7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

4/22/2015 9:45 AM Justin
- Enhanced to have GST information.

11/30/2015 9:43 PM DingRen
- direct load gst amount from gra item instead of recalculate

5/16/2019 11:58 AM William
- Enhance "GRA" word to use report_prefix.

06/24/2020 4:22 PM Sheila
- Updated button css
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
var is_under_gst = "{$is_under_gst|default:0}";

{literal}
function toggle_details(obj, id, bid){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dtl_"+id+"_"+bid);

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
			id: id,
			bid: bid,
			is_under_gst: is_under_gst
		},
		onComplete: function(e){
			new Insertion.After($("mst_"+id+"_"+bid), e.responseText);
			obj.src = '/ui/collapse.gif';
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
</p>
<p>
	<b>Vendor</b>
	<select name="vendor_id">
	   <option value="">-- All --</option>
		{foreach from=$vendor_list item=vd}
			<option value="{$vd.id}" {if $smarty.request.vendor_id eq $vd.id}selected {/if}>{$vd.code} - {$vd.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	<b>SKU Type</b>
	<select name="sku_type">
	   <option value="">-- All --</option>
		{foreach from=$st_list item=st}
			<option value="{$st.code}" {if $smarty.request.sku_type eq $st.code}selected {/if}>{$st.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	<b>Department</b>
	<select name="department_id">
	   <option value="">-- All --</option>
		{foreach from=$dept_list item=dept}
			<option value="{$dept.id}" {if $smarty.request.department_id eq $dept.id}selected {/if}>{$dept.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
</p>
<p>
* View in maximum 1 year
</b></p>
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align="center">-- No data --</p>{/if}
{else}
	{assign var=nr_colspan value=7}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th>Doc No</th>
			<th>Created On</th>
			<th>Branch ID</th>
			<th>Vendor Code</th>
			{if $config.enable_vendor_account_id}
				<th>Account ID</th>
				{assign var=nr_colspan value=$nr_colspan+1}
			{/if}
			<th width="40%">Vendor</th>
			<th>SKU Type</th>
			<th>Department</th>
			<th>Amount</th>
			{if $is_under_gst}
				<th>GST</th>
				<th>Amount<br />Incl. GST</th>
			{/if}
		</tr>
		<tbody>
			{foreach from=$table item=gra key=r}
				<tr bgcolor="#eeeeee" id="mst_{$gra.id}_{$gra.branch_id}">
					<td>
						<img src="/ui/expand.gif" width="10" onclick="toggle_details(this, '{$gra.id}', '{$gra.branch_id}');" title="Show Detail" class="clickable">
						{$gra.report_prefix}{$gra.id|string_format:"%05d"}
					</td>
					<td align="center">{$gra.added|default:'-'}</td>
					<td align="center">{$gra.bcode|default:'-'}</td>
					<td>{$gra.vendor_code}</td>
					{if $config.enable_vendor_account_id}
						<td>{$gra.account_id|default:"&nbsp;"}</td>
					{/if}
					<td>{$gra.vd_desc|default:'-'}</td>
					<td align="center">{$gra.sku_type|default:'-'}</td>
					<td align="center">{$gra.dept_desc|default:'-'}</td>
					<td class="r">{$gra.amount|number_format:2}</td>
					{if $is_under_gst}
						<td class="r">{$gra.gst|number_format:2}</td>
						<td class="r">{$gra.amount_gst|number_format:2}</td>
						{assign var=ttl_gst value=$ttl_gst+$gra.gst}
						{assign var=ttl_gst_amt value=$ttl_gst_amt+$gra.amount_gst}
					{/if}
				</tr>
				{assign var=ttl_amt value=$ttl_amt+$gra.amount}
			{/foreach}
		</tbody>
		<tr class="header">
			<th align="right" colspan="{$nr_colspan}">Total</th>
			<th class="r">{$ttl_amt|number_format:2}</th>
			{if $is_under_gst}
				<th class="r">{$ttl_gst|number_format:2}</th>
				<th class="r">{$ttl_gst_amt|number_format:2}</th>
			{/if}
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
