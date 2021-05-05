{*
3/5/2012 9:51:34 AM Justin
- Added important notes.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

4/21/2017 11:34 AM Justin
- Enhanced to have DO amt and qty columns.
- Enhanced to split GRN qty into vendor and IBT qty.
- Enhanced to have DO date (from/to) filtering.

06/30/2020 02:42 PM Sheila
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
.rpt_table tr.child{
	background-color:#FFF380;
}

.rpt_table tr.rows:nth-child(even){
	background-color:#eeeeee;
}

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

</style>
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var category_id = "{$smarty.request.category_id}";
var category = "{$smarty.request.category}";
var vendor_id = "{$smarty.request.vendor_id}";

{literal}
function toggle_sku_group(id, obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#rpt_table tr.sku_items_"+id);
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
}

function check_use_grn(obj){
	if(obj.value != "") document.f_a.use_grn.disabled = false;
	else document.f_a.use_grn.disabled = true;
}
{/literal}
</script>

{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class="err">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}
{if !$no_header_footer}
<form name="f_a" method="post" class="form" action="{$smarty.server.PHP_SELF}">

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

<b>Apply Date From</b> <input size="10" type="text" name="apply_date_from" value="{$smarty.request.apply_date_from}{$form.date_from}" id="apply_date_from">
<img align="absmiddle" src="ui/calendar.gif" id="ta_added1" style="cursor: pointer;" title="Select Apply Date From">
<b>To</b>
<input size="10" type="text" name="apply_date_to" value="{$smarty.request.apply_date_to}" id="apply_date_to">
<img align="absmiddle" src="ui/calendar.gif" id="ta_added2" style="cursor: pointer;" title="Select Apply Date To">
&nbsp;&nbsp;&nbsp;&nbsp;

<b>Received Date From</b> <input size="10" type="text" name="rcv_date_from" value="{$smarty.request.rcv_date_from}{$form.date_from}" id="rcv_date_from">
<img align="absmiddle" src="ui/calendar.gif" id="tr_added1" style="cursor: pointer;" title="Select Received Date From">
<b>To</b>
<input size="10" type="text" name="rcv_date_to" value="{$smarty.request.rcv_date_to}" id="rcv_date_to">
<img align="absmiddle" src="ui/calendar.gif" id="tr_added2" style="cursor: pointer;" title="Select Received Date To">&nbsp;&nbsp;&nbsp;&nbsp;

<b>Sales Date From</b> <input size="10" type="text" name="sales_date_from" value="{$smarty.request.sales_date_from}{$form.date_from}" id="sales_date_from">
<img align="absmiddle" src="ui/calendar.gif" id="ts_added1" style="cursor: pointer;" title="Select Sales Date From">
<b>To</b>
<input size="10" type="text" name="sales_date_to" value="{$smarty.request.sales_date_to}" id="sales_date_to">
<img align="absmiddle" src="ui/calendar.gif" id="ts_added2" style="cursor: pointer;" title="Select Sales Date To">&nbsp;&nbsp;&nbsp;&nbsp;

<b>DO Date From</b> <input size="10" type="text" name="do_date_from" value="{$smarty.request.do_date_from}{$form.date_from}" id="do_date_from">
<img align="absmiddle" src="ui/calendar.gif" id="do_added1" style="cursor: pointer;" title="Select DO Date From">
<b>To</b>
<input size="10" type="text" name="do_date_to" value="{$smarty.request.do_date_to}" id="do_date_to">
<img align="absmiddle" src="ui/calendar.gif" id="do_added2" style="cursor: pointer;" title="Select DO Date To">
</p>
<p>
<b>Department</b>
<select name="department_id">
   <option value="">-- All --</option>
    {foreach from=$departments item=dept}
        <option value="{$dept.id}" {if $smarty.request.department_id eq $dept.id}selected {/if}>{$dept.description}</option>
    {/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
<b>Brand</b>
<select name="brand_id">
   <option value="">-- All --</option>
   <option value="0" {if $smarty.request.brand_id eq '0'}selected {/if}>UNBRANDED</option>
   {if $brand_groups}
   <optgroup label="Brand Group">
    {foreach from=$brand_groups key=bgk item=bgv}
        <option value="{$bgk}" {if $smarty.request.brand_id eq $bgk}selected {/if}>{$bgv}</option>
    {/foreach}
   </optgroup>
   {/if}
   {if $brands}
    <optgroup label="Brand">
    {foreach from=$brands item=brand}
        <option value="{$brand.id}" {if $smarty.request.brand_id eq $brand.id}selected {/if}>{$brand.description}</option>
    {/foreach}
	</optgroup>
	{/if}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
<b>Master Vendor</b>
<select name="vendor_id" style="width:300px;" onchange="check_use_grn(this);">
   <option value="">-- All --</option>
    {foreach from=$vendors item=vd}
        <option value="{$vd.id}" {if $smarty.request.vendor_id eq $vd.id}selected {/if}>{$vd.description}</option>
    {/foreach}
</select>
<!--input type="checkbox" name="use_grn" {if $smarty.request.use_grn}checked{/if} {if !$smarty.request.vendor_id}disabled{/if} /> <label for="GRN_id"><b>Use GRN</b></label> [<a href="javascript:void(0)" onclick="alert('<? print jsstring($LANG['USE_GRN_INFO']);?>')">?</a>]-->
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="group_by_sku" {if $smarty.request.group_by_sku}checked{/if} />
<b>Group By SKU</b>
&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
</p>

<p><b>
Important: <br>
* Report Maximum Shown in 1 Month for Apply Date. <br />
* Vendor filter was based on masterfile SKU vendor.<br />
* Balance Qty = GRN - POS - DO
</b></p>

<input type="hidden" name="subm" value="1">
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
<br>
</form>

{/if}
{if !$sku_table}
{if $smarty.request.subm && !$err}<p align="center">-- No Data --</p>{/if}
{else}
<h2>{$report_title}</h2>
<table class="rpt_table" id="rpt_table" width=100% cellspacing=0 cellpadding=0>
	<tr class="header">
		{assign var=colspan value=2}
		{if $sessioninfo.show_cost}
			{assign var=colspan value=$colspan+1}
		{/if}
		{if $sessioninfo.show_report_gp}
			{assign var=colspan value=$colspan+2}
		{/if}
		<th width="2%" rowspan="2">#</th>
		<th width="5%" rowspan="2">SKU Item Code</th>
		<th width="30%" rowspan="2">Description</th>
		<th width="5%" rowspan="2">MCode</th>
		<th width="7%" colspan="2">GRN</th>
		<th width="5%" colspan="{$colspan}">POS</th>
		<th width="5%" colspan="2">DO</th>
		<th width="5%" rowspan="2">Balance<br />Qty</th>
	</tr>
	<tr class="header">
		<th width="5%">Vendor<br />Qty</th>
		<th width="5%">IBT<br />Qty</th>
		<th width="5%">Qty</th>
		<th width="5%">Amount</th>
		{if $sessioninfo.show_cost}
			<th width="5%">Cost</th>
		{/if}
		{if $sessioninfo.show_report_gp}
			<th width="5%">GP</th>
			<th width="5%">GP %</th>
		{/if}
		<th width="5%">Qty</th>
		<th width="5%">Amount</th>
	</tr>
	<tbody>
	{foreach from=$sku_table key=id item=sku name=sku_list}
		{if $smarty.request.group_by_sku}
			<tr class="rows">
				<td>
					{$smarty.foreach.sku_list.iteration}.
					{if $table.$id}
						<img src="/ui/expand.gif" onclick="javascript:void(toggle_sku_group('{$id}', this));" align="absmiddle">
					{/if}
				</td>
				<td align="center">{$sku.sku_item_code|default:"&nbsp;"}</td>
				<td nowrap>{$sku.description|default:"&nbsp;"}</td>
				<td>{$sku.mcode|default:"&nbsp;"}</td>
				<td align="right">{$sku.vd_qty|qty_nf}</td>
				<td align="right">{$sku.ibt_qty|qty_nf}</td>
				<td align="right">{$sku.sales_qty|qty_nf}</td>
				<td align="right">{$sku.sales_amt|number_format:2}</td>
				{if $sessioninfo.show_cost}
					<td align="right">{$sku.cost|number_format:$config.global_cost_decimal_points}</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					{assign var=gp value=$sku.sales_amt-$sku.cost}
					<td align=right>{$gp|number_format:2}</td>
					{if $sku.sales_amt>0}
						{assign var=gp_per value=$gp/$sku.sales_amt*100}
					{else}
						{assign var=gp_per value=0}
					{/if}
					<td align=right>{$gp_per|number_format:2}</td>
				{/if}
				<td align="right">{$sku.do_qty|qty_nf}</td>
				<td align="right">{$sku.do_amt|number_format:2}</td>
				<td align="right">{$sku.vd_qty+$sku.ibt_qty-$sku.sales_qty-$sku.do_qty|qty_nf}</td>
			</tr>
		{/if}
		{foreach from=$table.$id key=sid item=si name=si_list}
			<tr class="{if $smarty.request.group_by_sku}child sku_items_{$id}{else}rows{/if}" {if $smarty.request.group_by_sku}style="display:none;"{/if} id="sku_items_{$id}">
				<td>
					{if !$smarty.request.group_by_sku}
						{assign var=si_rows value=$si_rows+1}
						{$si_rows}.
					{else}
						&nbsp;
					{/if}
				</td>
				<td align="center">{$si.sku_item_code|default:"&nbsp;"}</td>
				<td nowrap>{$si.description|default:"&nbsp;"}</td>
				<td>{$si.mcode|default:"&nbsp;"}</td>
				<td align="right">{$si.vd_qty|qty_nf}</td>
				<td align="right">{$si.ibt_qty|qty_nf}</td>
				<td align="right">{$si.sales_qty|qty_nf}</td>
				<td align="right">{$si.sales_amt|number_format:2}</td>
				{if $sessioninfo.show_cost}
					<td align="right">{$si.cost|number_format:$config.global_cost_decimal_points}</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					{assign var=gp value=$si.sales_amt-$si.cost}
					<td align=right>{$gp|number_format:2}</td>
					{if $si.sales_amt>0}
						{assign var=gp_per value=$gp/$si.sales_amt*100}
					{else}
						{assign var=gp_per value=0}
					{/if}
					<td align=right>{$gp_per|number_format:2}</td>
				{/if}
				<td align="right">{$si.do_qty|qty_nf}</td>
				<td align="right">{$si.do_amt|number_format:2}</td>
				<td align="right">{$si.vd_qty+$si.ibt_qty-$si.sales_qty-$si.do_qty|qty_nf}</td>
			</tr>
		{/foreach}
	{/foreach}
	</tbody>
</table>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "apply_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "ta_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "apply_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "ta_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "sales_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "ts_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "sales_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "ts_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "rcv_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "tr_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "rcv_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "tr_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

	   Calendar.setup({
        inputField     :    "do_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "do_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "do_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "do_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}
{/if}

{include file=footer.tpl}

