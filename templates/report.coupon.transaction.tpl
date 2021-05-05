{*
3/7/2011 3:04:11 PM Alex
- created by me
4/15/2011 10:18:59 AM Alex
- add department, brand and vendor filter
4/28/2011 3:53:41 PM Alex
- add show group monthly

2017-08-24 09:24 AM Qiu Ying
- Bug fixed on the "Loading..." message is always shown when the user has no privilege on the vendor or brand

06/26/20 04:35 PM Sheila
- Updated button css
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

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var dept_id = '{$smarty.request.department_id}';
var rtype = '{$smarty.request.r_type}';
var vendorid = '{$smarty.request.vendor_id}';
var brandid = '{$smarty.request.brand_id}';

</script>

{literal}

<script>

function ajax_load_brand_vendor(ele){
	var dept_id=ele.value;
	
	if(dept_id != ""){
		$('loading').update(_loading_);
	}

 	// insert new row
	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: 'ajax_load_brand_vendor',
				dept_id: dept_id,
				r_type: rtype,
				vendor_id: vendorid,
				brand_id: brandid

		},
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
		},
		onComplete: function(m){

			var option = eval("("+m.responseText+")");
	        $('loading').update("");
            $('brand_id').update(option.brand);
            $('vendor_id').update(option.vendor);

            toggle_brand_vendor($('r_type_id').value);
		}
	});
}

function toggle_brand_vendor(r_type){

	if (r_type == "vendor"){
		$('vendor').style.display="";
	    $('brand').style.display="none";
	}
	else if (r_type == "brand"){
        $('vendor').style.display="none";
		$('brand').style.display="";
	}else{
        $('vendor').style.display="none";
		$('brand').style.display="none";
	}
}
/*
function change_report_mode(){
	//for hourly show data

	if ($('by_daily_id').checked){
		$('by_monthly_id').disable();
		$('to_date_id').hide();
	}else{
		$('by_monthly_id').enable();
		$('to_date_id').show();
	}
		
	if ($('by_monthly_id').checked){
		$('by_daily_id').disable();
	}else{
		$('by_daily_id').enable();
	}
	
}
*/
</script>

<style>
.red{
	color:red;
	text-decoration:none;
}
.blue{
    color:#000055;
    text-decoration:none;
}

</style>

{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li style="color:red;"> {$e} </li>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}

<form name="f_a" method=post class="form">
	<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
			<option value="all" {if $smarty.request.branch_id eq 'All'}selected {/if}>--All--</option>
			{foreach from=$branches key=id item=branch}
			<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
			{/foreach}
		</select> &nbsp;&nbsp;
	{/if}
	
	<b>Department</b>
	<select name="dept_id" onchange="ajax_load_brand_vendor(this)" id="dept_id">
		{if $dept_descrip}
			{foreach from=$dept_descrip key=did item=ddesc}
				<option value="{$did}" {if $smarty.request.dept_id eq $did} Selected {/if} >{$ddesc}</option>
			{/foreach}
		{else}
			<option value="">-- No Data --</option>
		{/if}
	</select>&nbsp;&nbsp;

    <b>Type</b>
	<select name="r_type" id="r_type_id" onchange="toggle_brand_vendor(this.value)";>
	    <option value="all" {if $smarty.request.r_type eq 'all'} selected {/if}>All</option>
	    <option value="brand" {if $smarty.request.r_type eq 'brand'} selected {/if}>Brand</option>
	    <option value="vendor" {if $smarty.request.r_type eq 'vendor'} selected {/if}>Vendor</option>
	</select>&nbsp;&nbsp;

	<span id=brand style="display:none;">
        <b>Brand</b>
        <select name="brand_id" id="brand_id">
        </select>&nbsp;&nbsp;
	</span>

	<span id=vendor style="display:none;">
        <b>Vendor</b>
        <select name="vendor_id" id="vendor_id">
        </select>&nbsp;&nbsp;
	</span>

	<span id="loading"></span>
{*
	<b>Show by</b>
	<input type="checkbox" name="by_daily" id="by_daily_id" value="1" {if $smarty.request.by_daily}checked {/if} onclick="change_report_mode();" />
	<label for="by_daily_id">Daily</label>
	<input type="checkbox" name="by_monthly" id="by_monthly_id" value="1" {if $smarty.request.by_monthly}checked {/if} onclick="change_report_mode();" />
	<label for="by_monthly_id">Monthly</label>
	&nbsp;&nbsp;
*}
	<b>POS Date From</b>
	<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;
	<span id="to_date_id">
	<b>To</b>
	<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;
	</span>

	<input type="checkbox" name="by_monthly" id="by_monthly_id" value="1" {if $smarty.request.by_monthly}checked {/if} />
	<label for="by_monthly_id"><b>Group Monthly</b></label>
	
	<br>
	<b>Coupon Code: </b><input name="search_code" value="{$smarty.request.search_code}">

	</p>
	<p>
	<button class="btn btn-primary" name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
	{/if}
	</p>

</form>
*Red color indicate invalid code
{/if}

<h2>{$report_title}</h2>

{if $tb}

<table class="tb" cellspacing="0" cellpadding="2" border="0" id="tbl_cat">
    <tr>
		<th align="left">Coupon Code</th>
		<th width="50px">Amt or %</th>
        <th align="left">&nbsp;</th>
		{assign var=lasty value=0}
		{assign var=lastm value=0}
		{foreach from=$uq_cols key=dt item=d}
		    <th valign="bottom">
			{if $smarty.request.by_monthly}
				{if $lasty ne $d.y}
					<span class="small">{$d.y}</span><br />
					{assign var=lasty value=$d.y}
				{/if}
				{$d.m|str_month|truncate:3:''}
				</th>
			{else}
				{if $lastm ne $d.m or $lasty ne $d.y}
				    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
				    {assign var=lastm value=$d.m}
					{assign var=lasty value=$d.y}
				{/if}
				{$d.d}
				</th>
			{/if}
		{/foreach}
		<th>Total<br />Qty</th>
		<th>Amount</th>
	</tr>
    {foreach from=$tb key=type item=tb_arr}
    	<tr style="background:yellow;text-align:left;">
    		<th colspan="{$day_col+5}">{$type}</th>
    	</tr>
	    {foreach from=$tb_arr key=id item=r}
		   	{include file='report.coupon.transaction.row.tpl' row=$r.data cp_code=$id}
	    {/foreach}
    {/foreach}

	<tr class=sortbottom>
		<th align="right">Total</th>
		<th align="right">&nbsp;</th>
		<th style="font-size:8pt">Qty<br>Amt</th>
		{foreach from=$uq_cols key=dt item=d}
			{assign var=fmt value="%0.2f"}
			{assign var=fmt value="%d"}
			{assign var=qty value=$tb_total.total.$dt.used}
  			{assign var=val value=$tb_total.total.$dt.amt}
			{capture assign=tooltip}
				Qty:{$qty|number_format}  /  Amt:{$val|string_format:'%.2f'}
			{/capture}
			{if $val}
				<td class="small" align="right" title="{$tooltip}">{$qty}<br>{$val|number_format:2}</td>
			{else}
			    <td class="small" align="right">&nbsp;</td>
			{/if}
		{/foreach}

		<td class="small" align="right">{$tb_total.total.total.used}</td>
		<td class="small" align="right">{$tb_total.total.total.amt|number_format:2}</td>
	</tr>
</table>
{else}
	{if $table}
		-- No Data --
	{/if}
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

ajax_load_brand_vendor($('dept_id'));
//change_report_mode();
</script>
{/literal}
{/if}
{include file=footer.tpl}
