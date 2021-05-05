{*

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

5/21/2018 10:00 pm Kuan Yeh
- Bug fixed of logo shown on excel export  

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
.col_1{
	background:yellow !important;
}
.col_2{
	background:rgb(204,255,204) !important;
}
.col_3{
	background:#ffffff !important;
}
.col_total{
	background:#ffb0c0 !important;
}
</style>
<script>
function toggleSub(ele,code){
	if($('tbody_'+code).style.display==''){
        $('tbody_'+code).style.display = 'none';
        ele.src = 'ui/expand.gif';
	}else{
        $('tbody_'+code).style.display = '';
        ele.src = 'ui/collapse.gif';
	}
}

function toggle_chk(chk,n)
{
	var c = $('fm').getElementsByClassName(n);

	for(i=0;i<c.length;i++)
	{
		if (chk.checked)
			c[i].checked = true;
		else
			c[i].checked = false;
	}
}

function check_chk(f)
{
	var bid = 0;
	$('fm').getElementsByClassName('branch').each( function (s) {
		if (s.checked) bid++;
	});
	$('fm').getElementsByClassName('branch_group').each( function (s) {
		if (s.checked) bid++;
	});

	if (bid == 0)
	{
		if ($('branch_all'))
		{
			$('branch_all').checked = true;
			$('branch_all').onclick();
		}
		if ($('branch_group_all'))
		{
			$('branch_group_all').checked = true;
			$('branch_group_all').onclick();
		}
	}
	return true;
}
</script>
{/literal}
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
<form id=fm method=post class=form onsubmit=" return check_chk(this)">
<input type=hidden name=report_title value="{$report_title}">
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>

<input id=branch_all type=checkbox onclick="toggle_chk(this,'branch')" {if $branches|@count == $smarty.request.branch_id|@count}checked{/if}> All
{foreach from=$branches item=b}
<input class=branch name=branch_id[] value="{$b.id}" type=checkbox {if is_array($smarty.request.branch_id)}{if in_array($b.id,$smarty.request.branch_id)}checked{/if}{/if}> {$b.code}
{/foreach}
<br>
{if $branch_group.header}
	<b>Branch Group</b>
	<input id=branch_group_all type=checkbox onclick="toggle_chk(this,'branch_group')" {if $branch_group.header|@count == $smarty.request.group_branch_id|@count}checked{/if}> All
	{foreach from=$branch_group.header item=r}
		{capture assign=bgid}bg,{$r.id}{/capture}
		<input class=branch_group name=group_branch_id[] value="{$bgid}" type=checkbox {if is_array($smarty.request.group_branch_id)}{if in_array($bgid,$smarty.request.group_branch_id)}checked{/if}{/if}> {$r.code}
	{/foreach}
	<br>
{/if}
{*<b>Branch</b> <select name="branch_id">
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
*}
{/if}
<b>SKU Group</b>&nbsp;
<select name="sku_group">
{foreach from=$sku_group item=r}
	<option value="{$r.sku_group_id}|{$r.branch_id}|{$r.user_id}" {if $sku_group_id eq $r.sku_group_id and $branch_id eq $r.branch_id and $user_id eq $r.user_id}selected {/if}>{$r.description} ( {$r.u})</option>
{/foreach}
</select>
&nbsp;
<b>Date</b>&nbsp;
<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<input type="checkbox" name="details" {if $smarty.request.details}checked {/if}> <b>Details</b>
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
{*Branch: {$branch_name}&nbsp;&nbsp;&nbsp;&nbsp;
SKU Group: {$sku_group_name}&nbsp;&nbsp;&nbsp;&nbsp;
Date: {$date_length}*}
</h2>
<table class="report_table small_printing" width="100%">
<tr class="header">
	<th rowspan="3">Arms Code</th>
	<th rowspan="3">Description</th>
	{if $smarty.request.details}
	<th rowspan="3">Balance as at {$date_to}</th>
	{/if}
	{assign var=col value=1}
	{foreach from=$label item=r}
	    {if $col > 3}
            {assign var=col value=1}
        {/if}
		<th colspan="{if $smarty.request.details}12{else}6{/if}" class="col_{$col}">{$r}</th>
		{assign var=col value=$col+1}
	{/foreach}
	<th colspan="{if $smarty.request.details}12{else}6{/if}" class="col_total">Total</th>
	{if $smarty.request.details}
	    {*<th rowspan="2">Openning Cost</th>
	    <th rowspan="2">Grn Cost</th>*}
	    <th rowspan="2">Opening Balance & In Stock Amt (Cost)</th>
	    <th rowspan="2">Sales Amt</th>
	    <th rowspan="2">Cost on Qty Sold</th>
	    <th rowspan="2">Profit Amt</th>
	    <th rowspan="2">Profit %</th>
	    <th rowspan="2">Closing Stock<br>(Amt)</th>
	    <th rowspan="2">% sold on Amt</th>
	    <th rowspan="2">Profit & Sales Weighted average</th>
	    <th rowspan="2">Sales - Purchase<br>(Amt)</th>
	{/if}
</tr>
<tr>
    {assign var=col value=1}
    {foreach from=$label item=r}
        {if $col > 3}
            {assign var=col value=1}
        {/if}

        <th colspan="{if $smarty.request.details}8{else}2{/if}" class="col_{$col}">Qty</th>
		<th colspan="2" class="col_{$col}">Sales</th>
		<th colspan="2" class="col_{$col}">Profit</th>
		{assign var=col value=$col+1}
	{/foreach}
	<th colspan="{if $smarty.request.details}8{else}2{/if}" class="col_total">Qty ({$date_length})</th>
	<th colspan="2" class="col_total">Sales</th>
	<th colspan="2" class="col_total">Profit</th>
</tr>
<tr class="header">
    {assign var=col value=1}
    {foreach from=$label item=r}
        {if $col > 3}
            {assign var=col value=1}
        {/if}

		{if $smarty.request.details}
        	<th class="col_{$col}">Openning<br />Balance</th>
        {/if}

        <th class="col_{$col}">IN</th>
		<th class="col_{$col}">Out</th>

		{if $smarty.request.details}
	        <th class="col_{$col}">GRA</th>
	        <th class="col_{$col}">ADJ</th>
	        <th class="col_{$col}">DO</th>
	        <th class="col_{$col}">Sales (%)</th>
	        <th class="col_{$col}">GRA (%)</th>
        {/if}

		<th class="col_{$col}">Cost</th>
		<th class="col_{$col}">Selling</th>
		<th class="col_{$col}">Amount</th>
		<th class="col_{$col}">%</th>
		{assign var=col value=$col+1}
	{/foreach}
    {if $smarty.request.details}
        <th class="col_total">Openning<br />Balance</th>
    {/if}

	<th class="col_total">IN</th>
	<th class="col_total">Out</th>

	{if $smarty.request.details}
	    <th class="col_total">GRA</th>
	    <th class="col_total">ADJ</th>
	    <th class="col_total">DO</th>
	    <th class="col_total">Sales (%)</th>
	    <th class="col_total">GRA (%)</th>
	{/if}

	<th class="col_total">Cost</th>
	<th class="col_total">Selling</th>
	<th class="col_total">Amount</th>
	<th class="col_total">%</th>

	{if $smarty.request.details}
	    {*<th></th><th></th>*}
		<th>A</th>
		<th>B</th>
		<th>C</th>
		<th>D = B - C</th>
	    <th>E = (D/B)*100</th>
		<th>F = A - C</th>
		<th>G = (C/A)*100</th>
		<th>H = E * G </th>
		<th>I</th>
	{/if}
</tr>
{assign var=total_opening_balance value=0}
{assign var=total_cost_on_qty value=0}
{assign var=total_profit value=0}
{foreach from=$table key=code item=r}
	{assign var=sid value=$r.sku_item_id}
	<tr>
	    <td nowrap>{$code}
		{if $show_branch && !$no_header_footer}
			<a href="javascript:">
				<img src="/ui/expand.gif" border="0" onClick="toggleSub(this,'{$code}');"> 
			</a>
		{/if}
		</td>
	    <td>{$r.description}</td>
	    {if $smarty.request.details}
	    <td class="r">{$r.balance.last.total|number_format|ifzero:'-'}</td>
	    {/if}
	    {assign var=col value=1}
	    {foreach from=$label key=lbl item=d}
	        {if $col > 3}
	            {assign var=col value=1}
	        {/if}
	        {if $smarty.request.details}
	        	<td class="col_{$col} r">{$r.balance.$lbl.total|number_format|ifzero:'-'}</td>
	        {/if}

	        <td class="col_{$col} r">{$r.qty_in.$lbl.total|number_format|ifzero:'-'}</td>
	        <td class="col_{$col} r">{$r.qty_out.$lbl.total|number_format|ifzero:'-'}</td>
	        {if $smarty.request.details}
		        <td class="col_{$col} r">{$r.gra.$lbl.total|number_format|ifzero:'-'}</td>
		        <td class="col_{$col} r">{$r.adj.$lbl.total|number_format|ifzero:'-'}</td>
	        	<td class="col_{$col} r">{$r.do.$lbl.total|number_format|ifzero:'-'}</td>
		        <td class="col_{$col} r">{$r.qty_sales_per.$lbl.total|number_format|ifzero:'-':'%'}</td>
		        <td class="col_{$col} r">{$r.gra_per.$lbl.total|number_format|ifzero:'-':'%'}</td>
	        {/if}

	        <td class="col_{$col} r">{$r.cost.$lbl.total|number_format:2|ifzero:'-'}</td>
	        <td class="col_{$col} r">{$r.selling.$lbl.total|number_format:2|ifzero:'-'}</td>
	        <td class="col_{$col} r">{$r.profit_amount.$lbl.total|number_format:2|ifzero:'-'}</td>
	        <td class="col_{$col} r">{$r.profit_per.$lbl.total|number_format:2|ifzero:'-':'%'}</td>
	        {assign var=col value=$col+1}
	    {/foreach}
	    {if $smarty.request.details}
        	<td class="col_total r">{$r.balance.$lbl.total|number_format|ifzero:'-'}</td>
        {/if}
	    <td class="col_total r">{$r.qty_in.total.total|number_format|ifzero:'-'}</td>
	    <td class="col_total r">{$r.qty_out.total.total|number_format|ifzero:'-'}</td>

	    {if $smarty.request.details}
		    <td class="col_total r">{$r.gra.total.total|number_format|ifzero:'-'}</td>
		    <td class="col_total r">{$r.adj.total.total|number_format|ifzero:'-'}</td>
	        <td class="col_total r">{$r.do.total.total|number_format|ifzero:'-'}</td>
	        {assign var=sales_per value=$r.qty_in.total.total+$r.balance.$lbl.total.total}
	        {if $sales_per ne 0}{assign var=sales_per value=$r.qty_out.total.total*100/$sales_per}{/if}
		    <td class="col_total r">{$sales_per|number_format|ifzero:'-':'%'}</td>
		    <td class="col_total r">{$r.gra_per.total.total|number_format|ifzero:'-':'%'}</td>
	    {/if}

	    <td class="col_total r">{$r.cost.total.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_total r">{$r.selling.total.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_total r">{$r.profit_amount.total.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_total r">{$r.profit_per.total.total|number_format:2|ifzero:'-':'%'}</td>

	    {if $smarty.request.details}
	        {*<td class="r">{$r.openning_bal.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.grn_cost.total.total|number_format:2|ifzero:'-'}</td>*}
	        {assign var=total_opening_balance value=$total_opening_balance+$r.balance_stock_amt.total}
	        {assign var=total_cost_on_qty value=$total_cost_on_qty+$r.cost_on_qty_sold.total}
	        {assign var=total_profit value=$total_profit+$r.profit_per2.total}
	        <td class="r">{$r.balance_stock_amt.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.sales_amt.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.cost_on_qty_sold.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.profit_amt.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.profit_per2.total|number_format:2|ifzero:'-':'%'}</td>
	        <td class="r">{$r.closing_stock_amt.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.sold_on_amt_per.total|number_format:2|ifzero:'-':'%'}</td>
	        <td class="r">{$r.profit_sales_avg.total|number_format:2|ifzero:'-'}</td>
	        <td class="r">{$r.sales_minus_perchase.total|number_format:2|ifzero:'-'}</td>
	    {/if}
	</tr>
	{if $show_branch}
        <tbody id="tbody_{$code}" style="display:none;">
		{foreach from=$show_branch key=bid item=b}
	        <tr>
	            <td colspan="2">{$b.code}</td>
	            {if $smarty.request.details}
			    <td class="r">{$r.balance.last.$bid|number_format|ifzero:'-'}</td>
			    {/if}
			    {assign var=col value=1}
			    {foreach from=$label key=lbl item=d}
			        {if $col > 3}
			            {assign var=col value=1}
			        {/if}
			        {if $smarty.request.details}
			        	<td class="col_{$col} r">{$r.balance.$lbl.$bid|number_format|ifzero:'-'}</td>
			        {/if}

			        <td class="col_{$col} r">{$r.qty_in.$lbl.$bid|number_format|ifzero:'-'}</td>
			        <td class="col_{$col} r">{$r.qty_out.$lbl.$bid|number_format|ifzero:'-'}</td>
			        {if $smarty.request.details}
				        <td class="col_{$col} r">{$r.gra.$lbl.$bid|number_format|ifzero:'-'}</td>
				        <td class="col_{$col} r">{$r.adj.$lbl.$bid|number_format|ifzero:'-'}</td>
			        	<td class="col_{$col} r">{$r.do.$lbl.$bid|number_format|ifzero:'-'}</td>
				        <td class="col_{$col} r">{$r.qty_sales_per.$lbl.$bid|number_format|ifzero:'-':'%'}</td>
				        <td class="col_{$col} r">{$r.gra_per.$lbl.$bid|number_format|ifzero:'-':'%'}</td>
			        {/if}

			        <td class="col_{$col} r">{$r.cost.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="col_{$col} r">{$r.selling.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="col_{$col} r">{$r.profit_amount.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="col_{$col} r">{$r.profit_per.$lbl.$bid|number_format:2|ifzero:'-':'%'}</td>
			        {assign var=col value=$col+1}
			    {/foreach}
			    {if $smarty.request.details}
		        	<td class="col_total r">{$r.balance.$lbl.$bid|number_format|ifzero:'-'}</td>
		        {/if}
			    <td class="col_total r">{$r.qty_in.total.$bid|number_format|ifzero:'-'}</td>
			    <td class="col_total r">{$r.qty_out.total.$bid|number_format|ifzero:'-'}</td>

			    {if $smarty.request.details}
				    <td class="col_total r">{$r.gra.total.$bid|number_format|ifzero:'-'}</td>
				    <td class="col_total r">{$r.adj.total.$bid|number_format|ifzero:'-'}</td>
			        <td class="col_total r">{$r.do.total.$bid|number_format|ifzero:'-'}</td>
			        {assign var=sales_per value=$r.qty_in.total.$bid+$r.balance.$lbl.total.$bid}
			        {if $sales_per ne 0}{assign var=sales_per value=$r.qty_out.total.$bid*100/$sales_per}{/if}
				    <td class="col_total r">{$sales_per|number_format|ifzero:'-':'%'}</td>
				    <td class="col_total r">{$r.gra_per.total.$bid|number_format|ifzero:'-':'%'}</td>
			    {/if}

			    <td class="col_total r">{$r.cost.total.$bid|number_format:2|ifzero:'-'}</td>
			    <td class="col_total r">{$r.selling.total.$bid|number_format:2|ifzero:'-'}</td>
			    <td class="col_total r">{$r.profit_amount.$bid.total|number_format:2|ifzero:'-'}</td>
			    <td class="col_total r">{$r.profit_per.total.$bid|number_format:2|ifzero:'-':'%'}</td>

			    {if $smarty.request.details}
			        {*<td class="r">{$r.openning_bal.$bid|number_format:2|ifzero:'-'}</td>
	        		<td class="r">{$r.grn_cost.total.$bid|number_format:2|ifzero:'-'}</td>*}
			        <td class="r">{$r.balance_stock_amt.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.sales_amt.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.cost_on_qty_sold.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.profit_amt.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.profit_per2.$bid|number_format:2|ifzero:'-':'%'}</td>
			        <td class="r">{$r.closing_stock_amt.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.sold_on_amt_per.$bid|number_format:2|ifzero:'-':'%'}</td>
			        <td class="r">{$r.profit_sales_avg.$bid|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$r.sales_minus_perchase.$bid|number_format:2|ifzero:'-'}</td>
			    {/if}
	        </tr>
	    {/foreach}
	    </tbody>
	{/if}
{/foreach}

<tr class="header">
	<th colspan="2" class="r">Total
        {if $BRANCH_CODE eq 'HQ' and $show_all}
			{if $no_header_footer}
				<a href="javascript:">
					<img src="/ui/expand.gif" border="0" onClick="toggleSub(this,'total');"> 
				</a>
			{/if}
		{/if}
	</th>
	{if $smarty.request.details}
	<td class="r">{$total.total.balance.last.total|number_format|ifzero:'-'}</td>
	{/if}
	{assign var=col value=1}
    {foreach from=$label key=lbl item=d}
	    {if $col > 3}
	        {assign var=col value=1}
	    {/if}

		{if $smarty.request.details}
	        <td class="col_{$col} r">{$total.total.balance.$lbl.total|number_format|ifzero:'-'}</td>
	    {/if}

	    <td class="col_{$col} r">{$total.total.qty_in.$lbl.total|number_format|ifzero:'-'}</td>
	    <td class="col_{$col} r">{$total.total.qty_out.$lbl.total|number_format|ifzero:'-'}</td>
	    {if $smarty.request.details}
		    <td class="col_{$col} r">{$total.total.gra.$lbl.total|number_format|ifzero:'-'}</td>
		    <td class="col_{$col} r">{$total.total.adj.$lbl.total|number_format|ifzero:'-'}</td>
	        <td class="col_{$col} r">{$total.total.do.$lbl.total|number_format|ifzero:'-'}</td>
		    <td class="col_{$col} r">{$total.total.qty_sales_per.$lbl.total|number_format|ifzero:'-':'%'}</td>
		    <td class="col_{$col} r">{$total.total.gra_per.$lbl.total|number_format|ifzero:'-':'%'}</td>
	    {/if}

	    <td class="col_{$col} r">{$total.total.cost.$lbl.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_{$col} r">{$total.total.selling.$lbl.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_{$col} r">{$total.total.profit_amount.$lbl.total|number_format:2|ifzero:'-'}</td>
	    <td class="col_{$col} r">{$total.total.profit_per.$lbl.total|number_format:2|ifzero:'-':'%'}</td>
	    {assign var=col value=$col+1}
	{/foreach}
    {if $smarty.request.details}
        <td class="col_total r">{$total.total.balance.$lbl.total|number_format|ifzero:'-'}</td>
    {/if}
	<td class="col_total r">{$total.total.qty_in.total.total|number_format|ifzero:'-'}</td>
	<td class="col_total r">{$total.total.qty_out.total.total|number_format|ifzero:'-'}</td>

	{if $smarty.request.details}
		<td class="col_total r">{$total.total.gra.total.total|number_format|ifzero:'-'}</td>
		<td class="col_total r">{$total.total.adj.total.total|number_format|ifzero:'-'}</td>
	    <td class="col_total r">{$total.total.do.total.total|number_format|ifzero:'-'}</td>
	    {assign var=sales_per value=$total.total.qty_in.total.total+$total.total.balance.$lbl.total}
	    {if $sales_per ne 0}{assign var=sales_per value=$total.total.qty_out.total.total*100/$sales_per}{/if}
		<td class="col_total r">{$sales_per|number_format|ifzero:'-':'%'}</td>
		<td class="col_total r">{$total.total.gra_per.total.total|number_format|ifzero:'-':'%'}</td>
	{/if}

	<td class="col_total r">{$total.total.cost.total.total|number_format:2|ifzero:'-'}</td>
	<td class="col_total r">{$total.total.selling.total.total|number_format:2|ifzero:'-'}</td>
	<td class="col_total r">{$total.total.profit_amount.total.total|number_format:2|ifzero:'-'}</td>
	<td class="col_total r">{$total.total.profit_per.total.total|number_format:2|ifzero:'-':'%'}</td>
{* last *}
	{if $smarty.request.details}
	    {*<td class="r">{$total.total.openning_bal.total|number_format:2|ifzero:'-'}</td>
	    <td class="r">{$total.total.grn_cost.total.total|number_format:2|ifzero:'-'}</td>*}
		{*$total.total.balance_stock_amt.total*}
		{*$total.total.closing_stock_amt.total*}
		{*$total.total.sold_on_amt_per.total*}
		{*$total.total.profit_sales_avg.total*}
		{*$total.total.sales_minus_perchase.total*}
        <td class="r">{$total_opening_balance|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total.total.sales_amt.total|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total.total.cost_on_qty_sold.total|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total.total.profit_amt.total|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total.total.profit_per2.total|number_format:2|ifzero:'-':'%'}</td>
        <td class="r">{$total_opening_balance-$total_cost_on_qty|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total_cost_on_qty/$total_opening_balance*100|number_format:2|ifzero:'-':'%'}</td>
        {assign var=e value=$total.total.profit_per2.total/100}
        {assign var=g value=$total_cost_on_qty/$total_opening_balance}
        <td class="r">{$e*$g*100|number_format:2|ifzero:'-'}</td>
        <td class="r">{$total.total.sales_amt.total-$total_opening_balance|number_format:2|ifzero:'-'}</td>
    {/if}
</tr>
    {if $show_branch}
        <tbody id="tbody_total" style="display:none;">
		{foreach from=$show_branch key=bid item=b}
		<tr>
		    <td colspan="2">{$b.code} </td>
		    {if $smarty.request.details}
			<td class="r">{$total.total.balance.last.$bid|number_format|ifzero:'-'}</td>
			{/if}
			{assign var=col value=1}
		    {foreach from=$label key=lbl item=d}
			    {if $col > 3}
			        {assign var=col value=1}
			    {/if}

				{if $smarty.request.details}
			        <td class="col_{$col} r">{$total.total.balance.$lbl.$bid|number_format|ifzero:'-'}</td>
			    {/if}

			    <td class="col_{$col} r">{$total.total.qty_in.$lbl.$bid|number_format|ifzero:'-'}</td>
			    <td class="col_{$col} r">{$total.total.qty_out.$lbl.$bid|number_format|ifzero:'-'}</td>
			    {if $smarty.request.details}
				    <td class="col_{$col} r">{$total.total.gra.$lbl.$bid|number_format|ifzero:'-'}</td>
				    <td class="col_{$col} r">{$total.total.adj.$lbl.$bid|number_format|ifzero:'-'}</td>
			        <td class="col_{$col} r">{$total.total.do.$lbl.$bid|number_format|ifzero:'-'}</td>
				    <td class="col_{$col} r">{$total.total.qty_sales_per.$lbl.$bid|number_format|ifzero:'-':'%'}</td>
				    <td class="col_{$col} r">{$total.total.gra_per.$lbl.$bid|number_format|ifzero:'-':'%'}</td>
			    {/if}

			    <td class="col_{$col} r">{$total.total.cost.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			    <td class="col_{$col} r">{$total.total.selling.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			    <td class="col_{$col} r">{$total.total.profit_amount.$lbl.$bid|number_format:2|ifzero:'-'}</td>
			    <td class="col_{$col} r">{$total.total.profit_per.$lbl.$bid|number_format:2|ifzero:'-':'%'}</td>
			    {assign var=col value=$col+1}
			{/foreach}
		    {if $smarty.request.details}
		        <td class="col_total r">{$total.total.balance.$lbl.$bid|number_format|ifzero:'-'}</td>
		    {/if}
			<td class="col_total r">{$total.total.qty_in.total.$bid|number_format|ifzero:'-'}</td>
			<td class="col_total r">{$total.total.qty_out.total.$bid|number_format|ifzero:'-'}</td>

			{if $smarty.request.details}
				<td class="col_total r">{$total.total.gra.total.$bid|number_format|ifzero:'-'}</td>
				<td class="col_total r">{$total.total.adj.total.$bid|number_format|ifzero:'-'}</td>
			    <td class="col_total r">{$total.total.do.total.$bid|number_format|ifzero:'-'}</td>
			    {assign var=sales_per value=$total.total.qty_in.total.$bid+$total.total.balance.$lbl.$bid}
			    {if $sales_per ne 0}{assign var=sales_per value=$total.total.qty_out.total.$bid*100/$sales_per}{/if}
				<td class="col_total r">{$sales_per|number_format|ifzero:'-':'%'}</td>
				<td class="col_total r">{$total.total.gra_per.total.$bid|number_format|ifzero:'-':'%'}</td>
			{/if}

			<td class="col_total r">{$total.total.cost.total.$bid|number_format:2|ifzero:'-'}</td>
			<td class="col_total r">{$total.total.selling.total.$bid|number_format:2|ifzero:'-'}</td>
			<td class="col_total r">{$total.total.profit_amount.total.$bid|number_format:2|ifzero:'-'}</td>
			<td class="col_total r">{$total.total.profit_per.total.$bid|number_format:2|ifzero:'-':'%'}</td>

			{if $smarty.request.details}
			    {*<td class="r">{$total.total.openning_bal.$bid|number_format:2|ifzero:'-'}</td>
	    		<td class="r">{$total.total.grn_cost.total.$bid|number_format:2|ifzero:'-'}</td>*}
		        <td class="r">{$total.total.balance_stock_amt.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.sales_amt.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.cost_on_qty_sold.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.profit_amt.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.profit_per2.$bid|number_format:2|ifzero:'-':'%'}</td>
		        <td class="r">{$total.total.closing_stock_amt.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.sold_on_amt_per.$bid|number_format:2|ifzero:'-':'%'}</td>
		        <td class="r">{$total.total.profit_sales_avg.$bid|number_format:2|ifzero:'-'}</td>
		        <td class="r">{$total.total.sales_minus_perchase.$bid|number_format:2|ifzero:'-'}</td>
		    {/if}
		</tr>
		{/foreach}
		</tbody>
	{/if}
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

