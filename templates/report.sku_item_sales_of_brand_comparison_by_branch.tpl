{*
10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 02:08 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
<style>
{literal}
.c1 { background:#ff9; }
.c2 { background:none; }
.c3 { background:#aff; }
.c4 { background:#faf; }
.c5 { background:#aaffaa; }
.c6 { background:#33ffaa; }
.r1 { background:#33ff99;}
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
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
var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';

function get_brand(val,selected_id)
{
	var param = "";
	if ($('all_category').checked)  param = '&view_all=all_brand&selected='+selected_id;
	else	param = '&category_id='+val+'&selected='+selected_id;
	$('brand_select').innerHTML = LOADING;
//	new Ajax('report.get_brand.php?brand_id='+val+'&selected_code='+selected_code,{evalScripts:true, update:$('brand_select')}).request();
	new Ajax.Updater('brand_select','?a=ajax_get_brand_by_category_id'+param);

}

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
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>{$PAGE_TITLE}</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
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
		<form method=post class=form name="f_a">
			<input type=hidden name=report_title value="{$report_title}">

			<div class="row">
			<div class="col-md-4">
				<b>Date</b>
				<input type=text name=date id=date value="{$smarty.request.date|ifzero:$smarty.now|date_format:'%Y-%m-%d'}" size=12>
				<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			
			<div class="col-md-4">
				{if $branch_group.header}
			<b class="form-label">Branch Group</b>
				<select class="form-control" name="branch_group">
				<option value="">-- All --</option>
				{foreach from=$branch_group.header item=r}
					<option value="{$r.id}" {if $smarty.request.branch_group eq $r.id}selected {/if}>{$r.code}</option>
				{/foreach}
				</select>
			{/if}
			</div>
			
			<div class="col-md-4">
				<div class="form-label form-inline">
					<input type=radio name=filter_date value="mtd" {if $smarty.request.filter_date ne 'ytd'}checked{/if}><b>MTD</b>
				<input type=radio name=filter_date value="ytd" {if $smarty.request.filter_date eq 'ytd'}checked{/if}><b>YTD</b>
				</div>
			</div>
			</div>
			
			<p>
			{include file="category_autocomplete.tpl"  all=true autocomplete_callback="get_brand($('category_id').value,'')"}
			
			<b class="form-label mt-2">Brand</b>
			<span id="brand_select"></span>
			<input type=hidden name=submit value=1>

			<button class="btn btn-primary mt-2 mt-md-0" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary mt-2 mt-md-0" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="form-label form-inline">
				<input type="checkbox" name="group_sku" id="group_sku_id" {if $smarty.request.group_sku}checked {/if}> <label for='group_sku_id'><b>&nbsp;Group by SKU</b></label>
			&nbsp;&nbsp;<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			</p>
			</form>
	</div>
</div>
<script>
//get_brand(document.report_form.department_id.value,'{$smarty.request.brand_id}');
get_brand($('category_id').value,'{$smarty.request.brand_id}');
</script>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}

{assign var=show_type value='amount'}


<h2>
{$report_title}

<!--Start Date: {$start_date}&nbsp;&nbsp;&nbsp;&nbsp;
End Date: {$end_date} &nbsp;&nbsp;&nbsp;&nbsp;
Brand: {$brand_name}
{if $smarty.request.branch_group}
Branch Group: {$branch_group.header[$smarty.request.branch_group].code}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}-->
</h2>
<table class=report_table width=100%>
<tr class=header>
	<th>{if $smarty.request.group_sku}SKU ID{else}ARMS Code{/if}</th>
	<th>Description</th>
	{foreach from=$label item=branch_name}
	    <th>{$branch_name}</th>
	{/foreach}
	<th>Total</th>
	<th>Contribution %</th>
</tr>
{foreach from=$table key=code item=c}
<tr>
	<td class=r4 rowspan=2 colspan=2>{if $category2.$code.description ne ''}{$category2.$code.description}{else}{$category2.root.description}{/if}
	{if !$no_header_footer}
	<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
	{/if}
	</td>
	{foreach from=$label key=lbl item=branch_name}
	    <td class="r c3">{$category2.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
	{/foreach}
	<td class="r c3">{$category2.$code.qty.total|qty_nf|ifzero:'-'}</td>
	{if $category2.total.qty.total > 0}
       {assign var=contribution value=$category2.$code.qty.total/$category2.total.qty.total}
       {assign var=contribution value=$contribution*100}
	{else}
	    {assign var=contribution value=''}
	{/if}
	<td class="r c3">{$contribution|number_format:2|ifzero:'-':'%'}</td>
</tr>
<tr>
    {foreach from=$label key=lbl item=branch_name}
	    <td class="r c4">{$category2.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
	{/foreach}
	<td class="r c4">{$category2.$code.amount.total|qty_nf|ifzero:'-'}</td>
	{if $category2.total.amount.total > 0}
       {assign var=contribution value=$category2.$code.amount.total/$category2.total.amount.total}
       {assign var=contribution value=$contribution*100}
	{else}
	    {assign var=contribution value=''}
	{/if}
	<td class="r c4">{$contribution|number_format:2|ifzero:'-':'%'}</td>
</tr>
<tbody style="display:none" id="tbody_{$code}">
    {foreach from=$c key=s item=r}
        {cycle values="c2,c1" assign=row_class}
        <tr>
            <td class="{$row_class}" rowspan=2>{if $smarty.request.group_sku}{$sku.$s.sku_id}{else}{$sku.$s.sku_item_code}{/if}</td>
	        <td class="{$row_class}" rowspan=2>{$sku.$s.description}</td>
	        {foreach from=$label key=lbl item=branch_name}
	            <td class="r3 r">{$r.qty.$lbl|qty_nf|ifzero:'-'}</td>
	        {/foreach}
	        <td class="r3 r">{$r.qty.total|qty_nf|ifzero:'-'}</td>
	        {if $category2.$code.qty.total > 0}
	            {assign var=contribution value=$r.qty.total/$category2.$code.qty.total}
	            {assign var=contribution value=$contribution*100}
			{else}
			    {assign var=contribution value=''}
	        {/if}
			<td class="r3 r">{$contribution|number_format:2|ifzero:'-':'%'}</td>
        </tr>
        <tr>
            {foreach from=$label key=lbl item=branch_name}
	            <td class="c5 r">{$r.amount.$lbl|number_format:2|ifzero:'-'}</td>
	        {/foreach}
	        <td class="c5 r">{$r.amount.total|number_format:2|ifzero:'-'}</td>
	        {if $category2.$code.amount.total > 0}
	            {assign var=contribution value=$r.amount.total/$category2.$code.amount.total}
	            {assign var=contribution value=$contribution*100}
			{else}
			    {assign var=contribution value=''}
	        {/if}
			<td class="c5 r">{$contribution|number_format:2|ifzero:'-':'%'}</td>
        </tr>
    {/foreach}
</tbody>
{/foreach}
<tr>
    <td class="r4 r" rowspan=2 colspan=2>Total</td>
    {foreach from=$label key=lbl item=branch_name}
        <td class="r c3">{$category2.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
    {/foreach}
    <td class="r c3">{$category2.total.qty.total|qty_nf|ifzero:'-'}</td>
    <td class="r c3">100.00%</td>
</tr>
<tr>
    {foreach from=$label key=lbl item=branch_name}
        <td class="r c4">{$category2.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
    {/foreach}
    <td class="r c4">{$category2.total.amount.total|number_format:2|ifzero:'-'}</td>
    <td class="r c4">100.00%</td>
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
</script>
{/literal}
{/if}
{include file=footer.tpl}
