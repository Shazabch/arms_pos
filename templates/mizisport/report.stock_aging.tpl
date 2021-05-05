{*
7/19/2010 10:06:58 AM Andy
- Fix stock aging report when no data it wont show "No Data".

8/3/2010 12:59:07 PM Justin
- Created GRA, DO, Adjustment, DN and CN qty columns and totals of GRA, DO, Adjustment, CN and DN.
- Check config first while process CN and DN queries.
- Added config check on the report printing for CN and DN columns.
- Amended the Total Balance to add GRA, DO, Adjustment, CN and DN qty.

10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/15/2011 4:09:47 PM Andy
- Fix toggle "Use GRN" checkbox error.

11/24/2011 2:46:40 PM Andy
- Change "Use GRN" popup information message.

3/23/2012 11:04:16 AM Andy
- Fix Balance (Pcs) legend notice.

8/28/2012 14:55:00 PM Fithri
 - Add 'Added' column
 
9/12/2012 12:52 PM Justin
- Enhanced the pagination.

9/27/2012 10:19:00 AM Fithri
- stock aging report add can filter by added date, can choose no filter

10/9/2012 5:25 PM Justin
- Bug fixed on percentage were calculated wrongly.

10/11/2012 4:23 PM Justin
- Added new filter "Launch Date".
- Added to show Launch Date column.
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
.c3 { background:#ff9; }
.c4 { background:#f99; }
.c5 { background:#9f9; }
.c6 { background:#99f; }
.sun {color:#f00; }
td.day2 { font-size:20px; }
td.tline { border-bottom:none !important;}
td.tline2 { border-bottom:none !important; border-top:1px solid #000 !important;}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = "{$smarty.request.branch_id}";
var month = "{$smarty.request.month}";
var year = "{$smarty.request.year}";
var stock_age = "{$smarty.request.stock_age}";
var sku_group = "{$smarty.request.sku_group}";
var category_id = "{$smarty.request.category_id}";
var category = "{$smarty.request.category}";
var vendors = "{$smarty.request.vendors}";
var brands = "{$smarty.request.brands}";
var sku_type = "{$smarty.request.sku_type}";
var price_type = "{$smarty.request.price_type}";
var last_prev = "{$smarty.request.last_prev}";
var filter_by_date = "{$smarty.request.filter_by_date}";
var sku_date_from = "{$smarty.request.sku_date_from}";
var sku_date_to = "{$smarty.request.sku_date_to}";

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

function msg_info(){
	if(confirm('This Report will takes around longer times to process depends on your database sizes. \n (Estimated 5 minutes above) \n\n Do you wish to continue?') == true){
		alert('System will process now... Press \'OK\' to continue.');
	}else{
		return false;
	}
}

function page_navigation(prev, next){
	url = "custom/mizisport/report.stock_aging.php?subm=1&prev="+prev+"&next="+next+"&branch_id="+branch_id+"&month="+month+"&year="+year+"&filter_by_date="+filter_by_date+"&sku_date_from="+sku_date_from+"&sku_date_to="+sku_date_to+"&stock_age="+stock_age+"&sku_group="+sku_group+"&vendors="+vendors+"&brands="+brands+"&sku_type="+encodeURIComponent(sku_type)+"&price_type="+encodeURIComponent(price_type)+"&time_id="+document.f['time_id'].value+"&pagination=1";
	if(sku_group == ""){
		url+="&category_id="+category_id+"&category="+encodeURIComponent(category);
	}
	
	if(next){
		var page_no = int(document.f['page_no'].value) + 1;
	}else{
		var page_no = int(document.f['page_no'].value) - 1;
	}
	url += "&page_no="+page_no;
	
	window.location = url;
}

function check_use_grn(){

	if (document.f['branch_id'].value == '') {
		$('chk_group_by_branch').style.display = '';
		//$('group_by_branch').checked=true;
	}
	else {
		$('group_by_branch').checked=false;
		$('chk_group_by_branch').style.display = 'none';
	}
	
	var allow_use_grn = true;
	
	if(document.f['branch_id']){
		if(document.f['branch_id'].value.indexOf('bg')>=0 || !document.f['branch_id'].value)	allow_use_grn = false;
	}
	
	if(!$('vendors').value)	allow_use_grn = false;
	
	if(allow_use_grn){
		$('use_grn').disabled=false;
	}
	else{
		$('use_grn').checked=false;	
		$('use_grn').disabled=true;	
	}
}
function toggle_date_filter(){
	if ($('filter_by_date').checked) $('chk_filter_by_date').style.display = '';
	else $('chk_filter_by_date').style.display = 'none';
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
<form name=f method=post class=form onSubmit="hide_filter();">

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id" onChange="check_use_grn();">
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
{else}
{/if}&nbsp;&nbsp;&nbsp;&nbsp;

<b>Year</b>
<select name="year">
	{foreach from=$years item=r}
	    <option {if $smarty.request.year eq $r.year}selected {/if} value="{$r.year}">{$r.year}</option>
	{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<b>Month</b>
<select name="month">
    {foreach from=$months key=m item=month}
        <option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
    {/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<b>Stock Age</b>&nbsp;
<select name="stock_age" id="stock_age">
{foreach from=$sa_val key=m item=stock_age}
	<option value="{$m}" {if $smarty.request.stock_age eq $m}selected {/if}>{$sa_desc[$m]}</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<b>SKU Group</b>&nbsp;
<select name="sku_group" id="sku_group" onchange="javascript:void(hide_filter());">
	<option value=""> Please Select </option>
{foreach from=$sku_group item=r}
	<option value="{$r.sku_group_id}|{$r.branch_id}|{$r.user_id}" {if $sku_group_id eq $r.sku_group_id and $branch_id eq $r.branch_id and $user_id eq $r.user_id}selected {/if}>{$r.description} ( {$r.u})</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
{$pagination}
<p></p>

<div id="2nd_filter" class="2nd_filter" {if $sku_group_id and $branch_id and $user_id} style="display:none;"{/if}>

<p>
{include file="category_autocomplete.tpl" all=false}
</p>
<b>Vendor</b>
<select name="vendors" id="vendors" onChange="check_use_grn();">
    <option value="">-- All --</option>
    {foreach from=$vendor item=r}
        <option value="{$r.id}" {if $smarty.request.vendors eq $r.id}selected {/if}>{$r.description}</option>
    {/foreach}
</select>&nbsp;&nbsp;
<input type=checkbox id=use_grn name=use_grn {if $smarty.request.use_grn}checked{/if} {if $smarty.request.vendors == ''}disabled{/if}> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;&nbsp;&nbsp;
<span id="chk_group_by_branch"><input type=checkbox id=group_by_branch name=group_by_branch {if $smarty.request.group_by_branch}checked{/if} /> <b>Group by branch</b></span>
&nbsp;&nbsp;&nbsp;&nbsp;

<input type=checkbox id=filter_by_date name=filter_by_date {if $smarty.request.filter_by_date}checked{/if} onclick="toggle_date_filter();" /> <b>Filter SKU Added Date</b>
&nbsp;&nbsp;
<span id="chk_filter_by_date" style="display:none">
<b>From</b> <input size=10 type=text name=sku_date_from value="{$smarty.request.sku_date_from}" id="sku_date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=sku_date_to value="{$smarty.request.sku_date_to}" id="sku_date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
</span>

<p></p>

<b>Brand</b>
<select name="brands">
<option value=''>-- All --</option>
<option value=0>UN-BRANDED</option>
	{foreach from=$brand item=b}
	<option value="{$b.id}" {if $smarty.request.brands eq $b.id}selected{/if}>{$b.description}</option>
	{/foreach}
</select> &nbsp;&nbsp;

<b>SKU Type</b>
<select name="sku_type">
	<option value="">-- All --</option>
	{foreach from=$sku_type item=t}
	<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
	{/foreach}
</select>&nbsp;&nbsp;

<b>Price Type</b>
<select name="price_type">
	<option value="">-- All --</option>
	{foreach from=$price_type item=p}
	<option value="{$p.type}" {if $smarty.request.price_type eq $p.type}selected {/if}>{$p.type}</option>
	{/foreach}
</select>&nbsp;&nbsp;

<b>Launch Date</b>
<input size="10" type="text" name="launch_date_from" value="{$smarty.request.launch_date_from}" id="launch_date_from">
<img align="absmiddle" src="ui/calendar.gif" id="t_added3" style="cursor: pointer;" title="Select Launch Date From">
<input size="10" type="text" name="launch_date_to" value="{$smarty.request.launch_date_to}" id="launch_date_to">
<img align="absmiddle" src="ui/calendar.gif" id="t_added4" style="cursor: pointer;" title="Select Launch Date To">
</div>
<p>
<font color="red">Important:</font> <br>
<sup>[1]</sup> Is the Balance of Total Received (Pcs) - Total Sales (Pcs) - GRA - DO + ADJ
</p>
<input type="hidden" name="subm" value="1">
<input type="hidden" name="time_id" value="{$smarty.request.time_id}">
<input type="hidden" name="page_no" value="{$smarty.request.page_no|default:$page_no}">
<button name="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name="output_excel" onclick="return msg_info();">{#OUTPUT_EXCEL#}</button>

{/if}
<br>
</form>
<script>check_use_grn();</script>
{/if}
{if !$table}
{if $smarty.request.subm && !$err}<p align=center>-- No Data --</p>{/if}
{else}
<h2>{$report_title}</h2>
<p align=center>
{if !$print_excel}
{if $prev}
<button name="prev_btn" title="Previous Page" onclick="page_navigation('{$prev}', '')">Previous</button>
{/if}
{if $next}
&nbsp;&nbsp;&nbsp;&nbsp;
<button name="next_btn" title="Next Page" onclick="page_navigation('', '{$next}')">Next</button></td>
{/if}
{/if}
</p>
<table class="report_table" width=100% cellpadding=0 cellspacing=0 style="border-bottom:1px solid #000">
	<tr class="header">
		{if $smarty.request.group_by_branch}
	    <th width=10%>Branch Code</th>
		<th width=20%>Branch Description</th>
		{else}
	    <th width=5%>ARMS Code</th>
	    <th width=25%>Description</th>
	    <th width=5%>Launch Date</th>
	    <th width=5%>Added</th>
		{/if}
	    <th width=4%>1 Month</th>
	    <th width=2%>%</th>
	    <th width=4%>2 Months</th>
	    <th width=2%>%</th>
	    <th width=4%>3 Months</th>
	    <th width=2%>%</th>
	    <th width=4%>4 Months</th>
	    <th width=2%>%</th>
	    <th width=4%>5 Months</th>
	    <th width=2%>%</th>
	    <th width=4%>6 Months</th>
	    <th width=2%>%</th>
	    <th width=4%>6 Months & Above</th>
	    <th width=2%>%</th>
	    <th width=5%>Total Received (Pcs)</th>
		<th width=5%>Total Sold (Pcs)</th>
	    <th width=5%>Total GRA (Pcs)</th>
	    <th width=5%>Total DO (Pcs)</th>
	    <th width=5%>Total Adjustment (Pcs)</th>
	    {if $config.consignment_modules}
		    <th width=5%>Total CN (Pcs)</th>
		    <th width=5%>Total DN (Pcs)</th>
		{/if}
		<th width=5%>Balance (Pcs) <font color='#3333ff'><sup>[1]</sup></font></th>
	</tr>
{foreach from=$table key=sku_key item=d name=i}
	{assign var=qty_sold value=$d.qty_sold}
	{if ($d.above_180_qty_rcv-$qty_sold)<0}
		{assign var=above_180_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.above_180_qty_rcv}
	{else}
		{assign var=above_180_days value=$d.above_180_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.180_qty_rcv-$qty_sold)<0}
		{assign var=180_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.180_qty_rcv}
	{else}
		{assign var=180_days value=$d.180_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.150_qty_rcv-$qty_sold)<0}
		{assign var=150_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.150_qty_rcv}
	{else}
		{assign var=150_days value=$d.150_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.120_qty_rcv-$qty_sold)<0}
		{assign var=120_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.120_qty_rcv}
	{else}
		{assign var=120_days value=$d.120_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.90_qty_rcv-$qty_sold)<0}
		{assign var=90_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.90_qty_rcv}
	{else}
		{assign var=90_days value=$d.90_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.60_qty_rcv-$qty_sold)<0}
		{assign var=60_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.60_qty_rcv}
	{else}
		{assign var=60_days value=$d.60_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{if ($d.30_qty_rcv-$qty_sold)<0}
		{assign var=30_days value=0}
		{assign var=qty_sold value=$qty_sold-$d.30_qty_rcv}
	{else}
		{assign var=30_days value=$d.30_qty_rcv-$qty_sold}
		{assign var=qty_sold value=0}
	{/if}
	{assign var=ttlr_rcv_qty value=$d.30_qty_rcv+$d.60_qty_rcv+$d.90_qty_rcv+$d.120_qty_rcv}
	{assign var=ttlr_rcv_qty value=$ttlr_rcv_qty+$d.150_qty_rcv+$d.180_qty_rcv}
	{assign var=ttlr_rcv_qty value=$ttlr_rcv_qty+$d.above_180_qty_rcv}
	{assign var=ttlpr_rcv_qty value=$30_days+$60_days+$90_days+$120_days}
	{assign var=ttlpr_rcv_qty value=$ttlpr_rcv_qty+$150_days+$180_days}
	{assign var=ttlpr_rcv_qty value=$ttlpr_rcv_qty+$above_180_days}
	<tr class="r">
		{if $smarty.request.group_by_branch}
		{assign var=bid value=$d.branch_id}
		<td align="left">{$branches1.$bid.code}</td>
		<td align="left">{$branches1.$bid.description}</td>
		{else}
		<td align="left">{$d.sku_item_code}</td>
		<td align="left">{$d.description}</td>
		<td align="left" nowrap>{$d.launch_date}</td>
		<td align="left" nowrap>{$d.added}</td>
		{/if}
		<td>{$30_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$30_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$60_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$60_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$90_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$90_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$120_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$120_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$150_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$150_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$180_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$180_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$above_180_days|qty_nf|ifzero:'-'}</td>
		<td>{if $ttlpr_rcv_qty}{$above_180_days/$ttlpr_rcv_qty*100|percentage_nf|ifzero:'-'}{/if}</td>
		<td>{$ttlr_rcv_qty|qty_nf|ifzero:'-'}</td>
		<td>{$d.qty_sold|qty_nf|ifzero:'-'}</td>
		<td>{$d.gra_qty|qty_nf|ifzero:'-'}</td>
		<td>{$d.do_qty|qty_nf|ifzero:'-'}</td>
		<td>{$d.adj_qty|qty_nf|ifzero:'-'}</td>
	    {if $config.consignment_modules}
			<td>{$d.cn_qty|qty_nf|ifzero:'-'}</td>
			<td>{$d.dn_qty|qty_nf|ifzero:'-'}</td>
			{assign var=ttl_cn_qty value=$ttl_cn_qty+$d.cn_qty}
			{assign var=ttl_dn_qty value=$ttl_dn_qty+$d.dn_qty}
		{/if}
		<td {if ($ttlr_rcv_qty-$d.qty_sold-$d.gra_qty-$d.do_qty+$d.adj_qty+$d.cn_qty-$d.dn_qty) < 0}style="color:red"{/if}>{$ttlr_rcv_qty-$d.qty_sold-$d.gra_qty-$d.do_qty+$d.adj_qty+$d.cn_qty-$d.dn_qty|qty_nf|ifzero:'-'}</td>
	</tr>
	{assign var=ttl_30_days value=$ttl_30_days+$30_days}
	{assign var=ttl_60_days value=$ttl_60_days+$60_days}
	{assign var=ttl_90_days value=$ttl_90_days+$90_days}
	{assign var=ttl_120_days value=$ttl_120_days+$120_days}
	{assign var=ttl_150_days value=$ttl_150_days+$150_days}
	{assign var=ttl_180_days value=$ttl_180_days+$180_days}
	{assign var=ttl_above_180_days value=$ttl_above_180_days+$above_180_days}
	{assign var=ttl_rcv_qty value=$ttl_rcv_qty+$ttlr_rcv_qty}
	{assign var=ttl_prcv_qty value=$ttl_prcv_qty+$ttlpr_rcv_qty}
	{assign var=ttl_qty_sold value=$ttl_qty_sold+$d.qty_sold}
	{assign var=ttl_gra_qty value=$ttl_gra_qty+$d.gra_qty}
	{assign var=ttl_do_qty value=$ttl_do_qty+$d.do_qty}
	{assign var=ttl_adj_qty value=$ttl_adj_qty+$d.adj_qty}
{/foreach}
	<tr class="header">      
	    <th class="r">Total</th>
		{if $smarty.request.group_by_branch}
	    <th class="r"></th>
		{else}
	    <th class="r"></th>
	    <th class="r"></th>
	    <th class="r"></th>
		{/if}
	    <th class="r">{$ttl_30_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_30_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
	    <th class="r">{$ttl_60_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_60_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
		<th class="r">{$ttl_90_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_90_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
		<th class="r">{$ttl_120_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_120_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
	    <th class="r">{$ttl_150_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_150_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
	    <th class="r">{$ttl_180_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_180_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
		<th class="r">{$ttl_above_180_days|qty_nf|ifzero:'-'}</th>
	    <th class="r">{if $ttl_rcv_qty}{$ttl_above_180_days/$ttl_prcv_qty*100|percentage_nf|ifzero:'-'}{else}-{/if}</th>
		<th class="r">{$ttl_rcv_qty|qty_nf|ifzero:'-'}</th>
		<th class="r">{$ttl_qty_sold|qty_nf|ifzero:'-'}</th>
		<th class="r">{$ttl_gra_qty|qty_nf|ifzero:'-'}</th>
		<th class="r">{$ttl_do_qty|qty_nf|ifzero:'-'}</th>
		<th class="r">{$ttl_adj_qty|qty_nf|ifzero:'-'}</th>
		{if $config.consignment_modules}
			<th class="r">{$ttl_cn_qty|qty_nf|ifzero:'-'}</th>
			<th class="r">{$ttl_dn_qty|qty_nf|ifzero:'-'}</th>
		{/if}
		<th class="r" {if ($ttl_rcv_qty-$ttl_qty_sold-$ttl_gra_qty-$ttl_do_qty+$ttl_adj_qty+$ttl_cn_qty+$ttl_dn_qty) < 0}style="color:red"{/if}>{$ttl_rcv_qty-$ttl_qty_sold-$ttl_gra_qty-$ttl_do_qty+$ttl_adj_qty+$ttl_cn_qty+$ttl_dn_qty|qty_nf|ifzero:'-'}</th>
	</tr>
</table>
{/if}
<p align=center>
{if !$print_excel}
{if $prev}
<button name="prev_btn" title="Previous Page" onclick="page_navigation('{$prev}', '')">Previous</button>
{/if}
{if $next}
&nbsp;&nbsp;&nbsp;&nbsp;
<button name="next_btn" title="Next Page" onclick="page_navigation('', '{$next}')">Next</button></td>
{/if}
{/if}
</p>

{if !$no_header_footer}
{literal}
<script type="text/javascript">

    Calendar.setup({
        inputField     :    "sku_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "sku_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
	
	Calendar.setup({
        inputField     :    "launch_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added3",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

	Calendar.setup({
        inputField     :    "launch_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added4",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
	
	toggle_date_filter();
</script>
{/literal}
{/if}

{include file=footer.tpl}
