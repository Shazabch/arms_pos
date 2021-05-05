{*
1/18/2011 4:34:12 PM Justin
- Changed the year list to take from DO instead of pos.

5/27/2015 3:30 PM Justin
- Enhanced to have GST information.

2/26/2016 9:50 AM Qiu Ying
- Enhance to have show by

06/23/2020 05:05 Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
	
	
{literal}
<style>
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var requested_info = {literal}{{/literal}
	branch_id: '{$smarty.request.branch_id}',
	view_type: '{$smarty.request.view_type}',
	year: '{$smarty.request.year}',
	month: '{$smarty.request.month}'
{literal}}{/literal}

{literal}
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

function show_branch_details(date,showby,is_gst,obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.b_"+date);
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

	new Ajax.Request(phpself+'?a=ajax_show_branch&ajax=1&date='+date+'&show_by='+showby+'&is_gst='+is_gst,{
		parameters: requested_info,
		onComplete:function(e){
			new Insertion.After('b_'+date, e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

function show_total_details(showby,is_gst,obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.total");
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

	new Ajax.Request(phpself+'?a=ajax_show_branch&ajax=1&count=1&show_by='+showby+'&is_gst='+is_gst,{
		parameters: requested_info,
		onComplete:function(e){
			new Insertion.After('total', e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}



function change_view_type(){
	var t = document.f_a['view_type'].value;
	if(t == 'month')    $('span_month').hide();
	else    $('span_month').show();
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
{/if}

<form method=post class=form name="f_a">
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	    	<option value="">-- All --</option>
	    	{foreach from=$branches key=bid item=b}
	    	    {if !$branches_group.have_group.$bid}
	    	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				{/if}
	    	{/foreach}
	    	{if $branches_group.header}
	    	<optgroup label="Branches Group">
		    	{foreach from=$branches_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
		    	    {foreach from=$branches_group.items.$bgid item=r}
		    	        <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
		    	    {/foreach}
		    	{/foreach}
	    	{/if}
	    	</optgroup>
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<b>View Type</b>
<select name="view_type" onChange="change_view_type();">
<option value="day" {if $smarty.request.view_type eq 'day'}selected {/if}>Day</option>
<option value="month" {if $smarty.request.view_type eq 'month'}selected {/if}>Month</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Show By</b>
<select name="show_by">
<option value="do_amt" {if $smarty.request.show_by eq 'do_amt'}selected {/if}>DO Amount</option>
<option value="inv_amt" {if $smarty.request.show_by eq 'inv_amt'}selected {/if}>Invoice Amount</option>
<option value="printed_inv" {if $smarty.request.show_by eq 'printed_inv'}selected {/if}>Invoice Amount (Invoice Printed)</option>
</select>

<p>
<input type=hidden name=report_title value="{$report_title}">
<b>Year</b> {dropdown name=year values=$do_year selected=$smarty.request.year key=year value=year}
	&nbsp;&nbsp;&nbsp;&nbsp;
	

<span id="span_month" {if $smarty.request.view_type eq 'month'}style="display:none;"{/if}>
<b>Month</b>
<select name="month">
  {foreach from=$months key=k item=r}
    <option value="{$k}" {if $smarty.request.month eq $k}selected{/if}>{$r}</option>
  {/foreach}
  </select>

&nbsp;&nbsp;&nbsp;&nbsp;
</span>

<input type=hidden name=submit value=1>
<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>


<h1>{$report_title}</h1>


{if !$table}
{if $smarty.request.submit && !$err}<p align=center>--No Data--</p>{/if}
{else}

{assign var=transfer_qty value=0}
{assign var=open_qty value=0}
{assign var=credit_sales_qty value=0}
{assign var=all_qty value=0}
{assign var=transfer_total value=0}
{assign var=open_total value=0}
{assign var=credit_sales_total value=0}
{assign var=all_total value=0}

<table class=report_table width=100% id=report_tbl>
	<tr class=header>
		{if $is_under_gst}
			{assign var=colspan value=4}
		{else}
			{assign var=colspan value=2}
		{/if}
		<th colspan="2">Date</th>
		<th colspan="{$colspan}">Transfer</th>
		<th colspan="{$colspan}">Cash Sales</th>
		<th colspan="{$colspan}">Credit Sales</th>
		<th colspan="{$colspan}">Total</th>
	</tr>

	<tr class=header>
	    <th width="10%">From</th>
	    <th width="10%">To</th>
	    <th>DO</th>
		{if $smarty.request.show_by eq 'inv_amt' || $smarty.request.show_by eq 'printed_inv'}
			{assign var = "title" value="Invoice Amount" }
		{else}
			{assign var = "title" value="DO Amount" }
		{/if}
	    <th>{$title}</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Total Amount<br />Incl. GST</th>
		{/if}
	    <th>DO</th>
	    <th>{$title}</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Total Amount<br />Incl. GST</th>
		{/if}
	    <th>DO</th>
	    <th>{$title}</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Total Amount<br />Incl. GST</th>
		{/if}
	    <th>DO</th>
	    <th>{$title}</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Total Amount<br />Incl. GST</th>
		{/if}
	</tr>
	
	
{foreach from=$table key=d item=t}
	<tr id=b_{$t.date}>
		<td colspan=2>
		    {if !$t.transfer.qty && !$t.transfer.amt && !$t.open.qty && !$t.open.amt && !$t.credit_sales.qty && !$t.credit_sales.amt}
		    	<img src="/ui/pixel.gif" align=absmiddle width="10">
		    {else}
				<img src="/ui/expand.gif" onclick="javascript:void(show_branch_details('{$t.date}','{$smarty.request.show_by}','{$is_under_gst}', this));" align=absmiddle>
			{/if}
			{$t.row_label}
		</td>
		<td class='r'>{$t.transfer.qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$t.transfer.gross_amt|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$t.transfer.gst_amt|number_format:2|ifzero:"-"}</td>
		{/if}
	    <td class='r'>{$t.transfer.amt|number_format:2|ifzero:"-"}</td>
		<td class='r'>{$t.open.qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$t.open.gross_amt|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$t.open.gst_amt|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$t.open.amt|number_format:2|ifzero:"-"}</td>
		<td class='r'>{$t.credit_sales.qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$t.credit_sales.gross_amt|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$t.credit_sales.gst_amt|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$t.credit_sales.amt|number_format:2|ifzero:"-"}</td>
  		
  		{assign var=row_qty value=$t.transfer.qty+$t.open.qty+$t.credit_sales.qty}
  		{assign var=row_total value=$t.transfer.amt+$t.open.amt+$t.credit_sales.amt}
  		{assign var=row_gst_total value=$t.transfer.gst_amt+$t.open.gst_amt+$t.credit_sales.gst_amt}
  		{assign var=row_gross_total value=$t.transfer.gross_amt+$t.open.gross_amt+$t.credit_sales.gross_amt}
  		
		<td class='r'>{$row_qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$row_gross_total|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$row_gst_total|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$row_total|number_format:2|ifzero:"-"}</td>

        {assign var=all_qty value=$all_qty+$row_qty}
        {assign var=all_total value=$all_total+$row_total}
        {assign var=all_gst_total value=$all_gst_total+$row_gst_total}
        {assign var=all_gross_total value=$all_gross_total+$row_gross_total}
	</tr>
	{assign var=transfer_qty value=$transfer_qty+$t.transfer.qty}
	{assign var=open_qty value=$open_qty+$t.open.qty}
	{assign var=credit_sales_qty value=$credit_sales_qty+$t.credit_sales.qty}
	
	{assign var=transfer_total value=$transfer_total+$t.transfer.amt}
	{assign var=transfer_gst_total value=$transfer_gst_total+$t.transfer.gst_amt}
	{assign var=transfer_gross_total value=$transfer_gross_total+$t.transfer.gross_amt}
	{assign var=open_total value=$open_total+$t.open.amt}
	{assign var=open_gst_total value=$open_gst_total+$t.open.gst_amt}
	{assign var=open_gross_total value=$open_gross_total+$t.open.gross_amt}
	{assign var=credit_sales_total value=$credit_sales_total+$t.credit_sales.amt}
	{assign var=credit_sales_gst_total value=$credit_sales_gst_total+$t.credit_sales.gst_amt}
	{assign var=credit_sales_gross_total value=$credit_sales_gross_total+$t.credit_sales.gross_amt}
	
{/foreach}


	<tr class=header id=total>
	    <td colspan= 2>
		    {if !$transfer_qty && !$transfer_total && !$open_qty && !$open_total && !$credit_sales_qty && !$credit_sales_total}
		    	<img src="/ui/pixel.gif" align=absmiddle width="10">
		    {else}
				<img src="/ui/expand.gif" onclick="javascript:void(show_total_details('{$smarty.request.show_by}','{$is_under_gst}',this));" align=absmiddle>
			{/if}
			<b>Total</b>
		</td>
	    <td class='r'>{$transfer_qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$transfer_gross_total|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$transfer_gst_total|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$transfer_total|number_format:2|ifzero:"-"}</td>
		<td class='r'>{$open_qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$open_gross_total|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$open_gst_total|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$open_total|number_format:2|ifzero:"-"}</td>
		<td class='r'>{$credit_sales_qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$credit_sales_gross_total|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$credit_sales_gst_total|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$credit_sales_total|number_format:2|ifzero:"-"}</td>
		<td class='r'>{$all_qty|ifzero:"-"}</td>
		{if $is_under_gst}
			<td class='r'>{$all_gross_total|number_format:2|ifzero:"-"}</td>
			<td class='r'>{$all_gst_total|number_format:2|ifzero:"-"}</td>
		{/if}
		<td class='r'>{$all_total|number_format:2|ifzero:"-"}</td>
	</tr>


</table>
{/if}

{include file=footer.tpl}
