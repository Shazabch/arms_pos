{*
1/4/2011 1:46:13 PM Andy
- Add "Invoice Total" and "Discount (%)".
- Fix show wrong approval order notice.

1/21/2011 9:58:45 AM Andy
- Add can search rejected, canceled/terminated for invoice and CN/DN.
- Add status column for summary report.

3/27/2012 11:58:45 AM Justin
- Added new filter and column "Price Type".

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

1/17/2015 12:12 PM Justin
- Enhanced to have GST information.
*}

{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="border:1px solid #eee;padding:5px;white-space:nowrap;">
<input type=hidden name=a value="show">
<p>
<b>DO Date From</b> <input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

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

</script>
{/literal}
&nbsp;&nbsp;&nbsp;&nbsp;
<b>By user</b>
<select name=user_id>
<option value=0>-- All --</option>
{section name=i loop=$user}
<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
{/section}
</select>
</p>

<p>
<b>Invoice To</b>
<select name=inv_to>
<option value="">-- All --</option>
<option value="Open" {if $smarty.request.inv_to eq 'Open'}selected{/if}>Open</option>
{section name=i loop=$branch}
<option value="{$branch[i].id}" {if $smarty.request.inv_to eq $branch[i].id}selected{/if}>{$branch[i].code}</option>
{/section}
</select>

&nbsp;&nbsp;&nbsp;&nbsp;
<b>Status</b>
<select name=status>
<option value="0" {if $smarty.request.status == 0}selected {/if}>All</option>
<option value="1" {if $smarty.request.status == 1}selected {/if}>Draft / Waiting for Approval</option>
<option value="2" {if $smarty.request.status == 2}selected {/if}>Approved</option>
<option value="3" {if $smarty.request.status == 3}selected {/if}>Rejected</option>
<option value="4" {if $smarty.request.status == 4}selected {/if}>Cancelled/Terminated</option>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;
<b>Price Type</b>
<select name="price_type">
	<option value="">-- All --</option>
	{foreach from=$pt_list item=r}
		<option value="{$r.code|escape}" {if $smarty.request.price_type eq $r.code}selected {/if}>{$r.code}</option>
	{/foreach}
</select>
</p>

<p>
<input type=submit name=submit value="Refresh">
</p>

</form>
<br>
{if !$ci_list}
{if $smarty.request.submit}- No record -{/if}
{else}
<p>
<b><font color="red">Note:</font><br />
* Price Type column with "-" indicates having more than 2 price types</b>
</p>
{assign var=n value=1}
<table width=100% cellpadding=4 cellspacing=1 border=0 style="padding:1px;border:1px solid #000" id="tbl_ci">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Invoice No.</th>
	<th>Status</th>
	<th>Create By</th>
	<th>Invoice To</th>
	<th>Price Type</th>
	<th>Invoice Total</th>
	<th>Discount (%)</th>
	<th>Amount</th>
	{if $is_under_gst}
		<th>GST</th>
		<th>Amount<br />Incl. GST</th>
	{/if}
	<th>Invoice Date</th>
</tr>
{section name=i loop=$ci_list}
<tr bgcolor={cycle values=",#eeeeee"}>
	<td>{$n++}.</td>
	<td><a href="/consignment_invoice.php?a=view&branch_id=1&id={$ci_list[i].id}" target=_blank>
	    CI/{strip}
		{if $ci_list[i].approved}
			{if $ci_list[i].ci_no}
				{$ci_list[i].ci_no}
			{else}
				{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(DD)
			{/if}
		{elseif $ci_list[i].status<1}
			{if $ci_list[i].ci_no}
				{$ci_list[i].ci_no}(DD)
			{else}
				{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(DD)
			{/if}
		{elseif $ci_list[i].status eq '1'}
			{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
		{elseif $ci_list[i].status>1}
			{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
		{/if}
		{/strip}
		</a>
	 	{if preg_match('/\d/',$ci_list[i].approvals)}
		<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$ci_list[i].approvals aorder_id=$ci_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	<td>
		{if $ci_list[i].status eq 1 and $ci_list[i].approved eq 1}
		    Fully Approved
		{elseif $ci_list[i].status eq 1 and $ci_list[i].approved eq 0}
		    Waiting for approval
        {elseif $ci_list[i].status eq 0 and $ci_list[i].approved eq 0}
            Draft
        {elseif $ci_list[i].status eq 2 and $ci_list[i].approved eq 0}
            Rejected
        {elseif $ci_list[i].status eq 4 or $ci_list[i].status eq 5}
            Cancelled/Terminated
		{/if}
	</td>
	<td>{$ci_list[i].user_name}</td>
	<td>
	{if $ci_list[i].ci_branch_id}
		{$ci_list[i].branch_name_2}
		- {$ci_list[i].ci_branch_description}
	{elseif $ci_list[i].open_info.name}	
		{$ci_list[i].open_info.name}
	{/if}
	{foreach from=$ci_list[i].d_branch.name item=pn name=pn}
		{if $smarty.foreach.pn.iteration>1} ,{/if}
		{$pn}
	{/foreach}
	</td>
	<td align="center">{$ci_list[i].sheet_price_type|default:'-'}</td>
	<td class="r">{$ci_list[i].sub_total_amt|number_format:2}</td>
	<td class="r">{$ci_list[i].discount_percent|default:'-'|ifzero:'-'}</td>
	{if $ci_list[i].is_under_gst}
		{assign var=total_gst value=$total_gst+$ci_list[i].total_gst_amt}
		{assign var=total_gst_amt value=$total_gst_amt+$ci_list[i].total_amount}
		<td align=right>{$ci_list[i].total_gross_amt|number_format:2}</td>
		<td align=right>{$ci_list[i].total_gst_amt|number_format:2}</td>
		<td align=right>{$ci_list[i].total_amount|number_format:2}</td>
		{assign var=total_amount value=$ci_list[i].total_gross_amt}
	{else}
		{assign var=total_amount value=$ci_list[i].total_amount}
		<td align=right>{$total_amount|number_format:2}</td>
		{if $is_under_gst}
			<td align="right">-</td>
			<td align="right">-</td>
		{/if}
	{/if}
	<td align=center>{$ci_list[i].ci_date|date_format:"%d-%m-%Y"}</td>
	{assign var=total value=$total_amount+$total}
</tr>
{/section}

<tr bgcolor=#ffee99 class="sortbottom">
	<td colspan="8" align=right><b>Total</b></td>
	<td align=right>{$total|number_format:2}</td>
	{if $is_under_gst}		
		<td align=right>{$total_gst|number_format:2}</td>
		<td align=right>{$total_gst_amt|number_format:2}</td>
	{/if}
	<td>&nbsp;</td>
</tr>

</table>
{/if}

<script>
{if $ci_list}
    ts_makeSortable($('tbl_ci'));
{/if}
</script>
{include file=footer.tpl}
