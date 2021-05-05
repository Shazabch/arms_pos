{*
6/22/2011 5:07:11 PM Justin
- Added date filter.

10/17/2011 9:50:12 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/24/2011 2:30:24 PM Andy
- Change "Use GRN" popup information message.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 02:25 PM Sheila
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
.rpt_table tr:nth-child(even){
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
var branch_id = "{$smarty.request.branch_id}";
var category_id = "{$smarty.request.category_id}";
var category = "{$smarty.request.category}";
var vendors = "{$smarty.request.vendors}";

{literal}
function hide_filter(){
	if ($('sku_group').value != 0){
		$('2nd_filter').style.display = 'none';
		$('category_id').disabled = true;
		$('category_tree').disabled = true;
		$('autocomplete_category').disabled = true;
	}else{
		$('2nd_filter').style.display = '';
		$('category_id').disabled = false;
		$('category_tree').disabled = false;
		$('autocomplete_category').disabled = false;
	}
}

function chk_vd_filter(){
	val=$('vendors').value;

	if(val){
		//$('dept_id').disabled=true;
		$('use_grn').disabled=false;
	}
	else{
		//$('dept_id').disabled=false;
		$('use_grn').checked=false;	
		$('use_grn').disabled=true;	
	}
	

}

function msg_info(){
	if(confirm('This Report will takes around longer times to process depends on your database sizes. \n (Estimated 5 minutes above) \n\n Do you wish to continue?') == true){
		alert('System will process now... Press \'OK\' to continue.');
	}else{
		return false;
	}
}

function page_navigation(prv, fwd){
	url = "report.stock_aging.php?subm=1&prv="+prv+"&fwd="+fwd+"&branch_id="+branch_id+"&month="+month+"&year="+year+"&stock_age="+stock_age+"&sku_group="+sku_group+"&vendors="+vendors+"&brands="+brands+"&sku_type="+encodeURIComponent(sku_type)+"&price_type="+encodeURIComponent(price_type);
	if(sku_group == ""){
		url+="&category_id="+category_id+"&category="+encodeURIComponent(category);
	}
	window.location = url;
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
<form name=f method=post class=form action="report.batch_no.php" onSubmit="hide_filter();">

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
<b>Vendor</b>
<select name="vendors" id="vendors" onChange="chk_vd_filter();">
    <option value="">-- All --</option>
    {foreach from=$vendor item=r}
        <option value="{$r.id}" {if $smarty.request.vendors eq $r.id}selected {/if}>{$r.description}</option>
    {/foreach}
</select>&nbsp;&nbsp;
{*<input type=checkbox id=use_grn name=use_grn value="1" {if $smarty.request.use_grn}checked{/if} {if $smarty.request.vendors == ''}disabled{/if}> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]*}
</p>

<p>
{include file="category_autocomplete.tpl" all=true}
</p>

<p>
<b>Date</b> <input size=10 type=text name=date value="{$smarty.request.date}{$form.date}" id="date">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
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

<input type=hidden name=subm value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel onclick="return msg_info();">{#OUTPUT_EXCEL#}</button>
{/if}
<br>
</form>
{/if}
{if !$table}
{if $smarty.request.subm && !$err}<p align=center>-- No Data --</p>{/if}
{else}
<h2>{$report_title}</h2>
<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
	<tr class="header">
		<th width=3%>#</th>
	    <th width=8%>ARMS Code</th>
	    <th width=45%>Description</th>
	    <th width=9%>Location</th>
	    <th width=10%>Batch No</th>
	    <th width=5%>Expired<br />Date</th>
	    <th width=5%>{if $smarty.request.view_type eq '1'}Day(s) Expired{else}Day(s) Remaining{/if}</th>
	    <th width=5%>Batch Qty</th>
	    <th width=5%>Stock<br />Balance</th>
	    <th width=5%>Total Out</th>
	    {if count($table)>$report_row}
	    	<th width="6" nowrap>&nbsp;</th>
	    {/if}
	</tr>
	<tbody {if count($table)>$report_row}style="height:600;overflow-y:auto;overflow-x:hidden;"{/if}>
	{foreach from=$table key=sku_key item=d name=t}
		<tr class="r">
			<td align="left">{$smarty.foreach.t.iteration}.</td>
			<td align="center">{$table.$sku_key.sku_item_code|default:'&nbsp;'}</td>
			<td align="left">{$table.$sku_key.description|default:'&nbsp;'}</td>
			<td align="left">{$table.$sku_key.location|default:'&nbsp;'}</td>
			<td align="left">{$table.$sku_key.batch_no|default:'&nbsp;'}</td>
			<td align="center">{$table.$sku_key.expired_date|default:'&nbsp;'}</td>
			<td {if $smarty.request.view_type eq '1'}style="color:red;"{/if}>{$table.$sku_key.days_remain|default:0}</td>
			<td>{$table.$sku_key.batch_qty|qty_nf|ifzero:'-'}</td>
			<td>{$table.$sku_key.sb_qty|qty_nf|ifzero:'-'}</td>
			<td>{$table.$sku_key.batch_qty-$table.$sku_key.sb_qty|qty_nf|ifzero:'-'}</td>
			{if count($table)>$report_row}
				<td>&nbsp;</td>
			{/if}
		</tr>
		{assign var=ttl_batch_qty value=$ttl_batch_qty+$table.$sku_key.batch_qty}
		{assign var=ttl_sb_qty value=$ttl_sb_qty+$table.$sku_key.sb_qty}
	{/foreach}
	</tbody>
	<tr class="header">      
	    <th colspan="7" class="r">Total</th>
	    <th class="r">{$ttl_batch_qty|qty_nf}</th>
	    <th class="r">{$ttl_sb_qty|qty_nf}</th>
	    <th class="r">{$ttl_batch_qty-$ttl_sb_qty|qty_nf}</th>
	    {if count($table)>$report_row}
			<th>&nbsp;</th>
		{/if}
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
    });

</script>
{/literal}
{/if}

{include file=footer.tpl}

