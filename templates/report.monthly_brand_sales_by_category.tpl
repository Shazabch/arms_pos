{*
10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 11:47 AM Sheila
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

function close_sub(tbody_id,img_id){
    $(tbody_id).style.display = 'none';
    $(img_id).src = '/ui/expand.gif';
}
{/literal}
</script>

<style>
{literal}
.r1 { background:#f9a; }
.r2 { background:#6cf; }
.c1 { background:#ff9; }
.c2 { background:#aff; }
.c3 { background:#faf; }
{/literal}
</style>
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
<form method=post class=form name=report_form>
<input type=hidden name=report_title value="{$report_title}">
<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	    <option value="">-- All --</option>
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
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
<p>

{include file="category_autocomplete.tpl"  all=true autocomplete_callback="get_brand($('category_id').value,'')"}

<b>Brand</b>
<span id="brand_select"></span>
&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
</p>
Note: Report Maximum Shown 1 Year
</form>
<script>
//get_brand(document.report_form.department_id.value,'{$smarty.request.brand_id}');
get_brand($('category_id').value,'{$smarty.request.brand_id}');
</script>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h2>
{$report_title}

<!--Branch: {$branch_name} &nbsp;&nbsp;&nbsp;&nbsp; Brand: {$brand_name}--></h2>
<table class=report_table width=100%>
<tr class=header>
	<th>ARMS Code</th>
	<th>Description</th>
	{foreach from=$label item=lbl}
	    <th>{$lbl}</th>
	{/foreach}
	<th>Total</th>
	<th>Contribution<br>(%)</th>
</tr>

{foreach from=$table item=t key=c}
<tbody style="display:none" id="tbody_{$c}">
	<tr>
		<th colspan=2 class=r1>{$category.$c.name}
		{if !$no_header_footer}
		<img src=/ui/collapse.gif onclick="close_sub('tbody_{$c}','img_{$c}')">
		{/if}
		</th>
		<th colspan="{count var=$label}" class=r1></th>
		<th colspan=2 class=r1></th>
	</tr>
	{foreach from=$t item=r}
	<tr>
		<td rowspan=2 class=c1>{$r.sku_item_code}</td>
		<td rowspan=2>{$r.description}</td>
		{foreach from=$label key=date item=lbl}
		    <td class="c2 r">{$r.quantity.$date|qty_nf|ifzero:"-"}</td>
		{/foreach}
		<td class="c2 r">{$r.quantity.total|qty_nf|ifzero:"-"}</td>
		<td class="c2 r">{$r.quantity.total/$category.$c.quantity.total*100|number_format:2|ifzero:"-":"%"}</td>
	</tr>
	<tr>
	    {foreach from=$label key=date item=lbl}
		    <td class="c3 r">{$r.amount.$date|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class="c3 r">{$r.amount.total|number_format:2|ifzero:"-"}</td>
		<td class="c3 r">{$r.amount.total/$category.$c.amount.total*100|number_format:2|ifzero:"-":"%"}</td>
	</tr>
	{/foreach}
</tbody>
<tbody>
	<tr>
	    <th colspan=2 rowspan=2 class="r2 r"><!--Sub Total of--> {$category.$c.name}
		{if !$no_header_footer}
		<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$c}',this)" id='img_{$c}'>
		{/if}
		</th>
	    {foreach from=$label key=date item=lbl}
		    <td class="c2 r">{$category.$c.quantity.$date|qty_nf|ifzero:"-"}</td>
		{/foreach}
		<td class="c2 r">{$category.$c.quantity.total|qty_nf|ifzero:"-"}</td>
		<td class="c2 r">{$category.$c.quantity.total/$category.total.quantity.total*100|number_format:2|ifzero:"-":"%"}</td>
	</tr>
	<tr>
	    {foreach from=$label key=date item=lbl}
		    <td class="c3 r">{$category.$c.amount.$date|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td class="c3 r">{$category.$c.amount.total|number_format:2|ifzero:"-"}</td>
		<td class="c3 r">{$category.$c.amount.total/$category.total.amount.total*100|number_format:2|ifzero:"-":"%"}</td>
	</tr>
</tbody>
{/foreach}
<tr>
    <th colspan=2 rowspan=2 class="c1 r">Total of {$dept_name}</th>
    {foreach from=$label key=date item=lbl}
		<td class="c2 r">{$category.total.quantity.$date|qty_nf|ifzero:"-"}</td>
	{/foreach}
	<td class="c2 r">{$category.total.quantity.total|qty_nf|ifzero:"-"}</td>
</tr>
<tr>
    {foreach from=$label key=date item=lbl}
	    <td class="c3 r">{$category.total.amount.$date|number_format:2|ifzero:"-"}</td>
	{/foreach}
	<td class="c3 r">{$category.total.amount.total|number_format:2|ifzero:"-"}</td>
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

