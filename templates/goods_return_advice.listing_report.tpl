{*
REVISION HISTORY
==================
11/22/2007 11:05:42 AM gary
- remove vendor selection.

11/22/2007 3:14:04 PM gary
- add selection detail in printing.

11/1/2010 4:37:22 PM Alex
- add show cost privilege

9/15/2011 2:48:11 PM Justin
- Modified the status to have more options.
- Modified the status as below:
  => Saved - for those saved in GRA.
  => Completed (Not Returned) - for those already printed out the checklsit and awaiting for return.
  => Returned - for those already confirmed to return.
  
7/24/2012 11:31 AM Justin
- Added "Account ID" column and available when config is found.

9/4/2012 5:08 PM Justin
- Bug fixed on return date that show in wrong format.

4/20/2015 4:41 PM Justin
- Enhanced to have GST information.

02/29/2016 0946 Edwin
- Bugs fixed on status filter in GRA summary 

4/20/2017 9:35 AM Khausalya 
- Enhanced changes from RM to use config setting. 

6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

5/7/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/12/2018 11:33 AM Justin
- Enhanced to have Rounding Adjust.

5/16/2019 11:18AM William
- Enhance "GRA" word to use report_prefix word.

06/24/2020 4:22 PM Sheila
- Updated button css
*}
{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

<form class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="border:1px solid #eee;padding:5px;white-space:nowrap;">
<p>
<b>GRA Date From</b> <input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

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
&nbsp;
</p>
<p>
<!--input type=hidden name=a value="list"-->
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>
<select name=branch_id>
<option value="">-- All --</option>
{section name=i loop=$branch}
<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
{/section}
</select>
&nbsp;
{/if}
<b>Department</b>
<select name=department_id>
<option value="">-- All --</option>
{section name=i loop=$dept}
<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
{/section}
</select>
&nbsp;
<b>SKU Type</b>
<select name=sku_type>
<option value="">-- All --</option>
{section name=i loop=$sku_type}
<option value="{$sku_type[i].code}" {if $smarty.request.sku_type eq $sku_type[i].code}selected{/if}>{$sku_type[i].code}</option>
{/section}
</select>
&nbsp;
<b>Status [<a href="javascript:void(alert('Un-checkout: includes Saved & Waiting Approval and Approved GRA.'));">?</a>]</b>
{assign var=_st value='All'}
<select name=returned>
<option value="">-- All --</option>
<option value="0" {if $smarty.request.returned eq '0'}selected{assign var=_st value='Saved & Waiting Approval'}{/if}>Saved & Waiting Approval</option>
<option value="1" {if $smarty.request.returned eq '1'}selected{assign var=_st value='Approved'}{/if}>Approved</option>
<option value="2" {if $smarty.request.returned eq '2'}selected{assign var=_st value='Completed'}{/if}>Completed</option>
<option value="3" {if $smarty.request.returned eq '3'}selected{assign var=_st value='Un-checkout'}{/if}>Un-checkout</option>
</select>
&nbsp;
<input class="btn btn-primary" name=submit type=submit value="Refresh"> <input class="btn btn-primary" name=submit type=submit value="Print">
</p>

{if $config.foreign_currency}
	<p>
	* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}
	</p>
{/if}

<!--p>
<b>Vendor</b>
<select name=vendor_id>
{section name=i loop=$vendor}
{if $vendor[i].description}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/if}
{/section}
</select>
&nbsp;
<input name=submit type=submit value="Refresh"> <input name=submit type=submit value="Print">
</p-->

</form>

{php}
show_report();
{/php}

{if $gra}
<div class="noscreen">
<h4>{if $smarty.request.branch_id || $BRANCH_CODE!='HQ'} Branch:{$form.branch}&nbsp;&nbsp; {/if}
From:{$smarty.request.from}
&nbsp;&nbsp; To:{$smarty.request.to}
&nbsp;&nbsp; Department:{if $_dp}{$_dp}{else}ALL{/if}
&nbsp;&nbsp; SKU Type:{if $smarty.request.sku_type}{$smarty.request.sku_type}{else}ALL{/if}
&nbsp;&nbsp; Status:{if $_st}{$_st}{else}ALL{/if}
</h4>
</div>
{assign var=count value=0}
{assign var=nr_colspan value=8}
{if $config.enable_vendor_account_id}
	{assign var=nr_colspan value=$nr_colspan+1}
{/if}
{if $have_fc}
	{assign var=nr_colspan value=$nr_colspan+2}
{/if}
{if $got_rounding_adj}
	{assign var=nr_colspan value=$nr_colspan+2}
{/if}
{section name=i loop=$gra}

{if $last_branch!=$gra[i].branch && $last_branch}
	{if $sessioninfo.show_cost}
	<tr class=sortbottom bgcolor=#ffee99 height=24>
		<th colspan="{$nr_colspan}" align=right>Total</th>
		<th align=right>{$total1|number_format:2}</th>
		{if $is_under_gst}
			<th align=right>{$total_gst|number_format:2}</th>
			<th align=right>{$total_gst_amt|number_format:2}</th>
		{/if}
		<td>&nbsp;</td>
	</tr>
	{/if}
</table>
<br>
{assign var=total1 value=0}
{assign var=total_gst value=0}
{assign var=total_gst_amt value=0}
{assign var=count value=0}
{/if}
{assign var=count value=$count+1}
{if $last_branch!=$gra[i].branch}
{if !$smarty.request.branch_id && $BRANCH_CODE=='HQ'}
<h4>{$gra[i].branch}</h4>
{/if}
<table id=t1 class=sortable width=100% cellspacing=1 cellpadding=4 border=0 style="border:1px solid #000;padding:1px;">

<tr bgcolor=#ffee99 height=24>
    <th>No</th>
	<th>GRA #</th>
	<th>Vendor Code</th>
	{if $config.enable_vendor_account_id}
		<th>Account ID</th>
	{/if}
	<th>Vendor</th>
	<th>DN No.</th>
	<th>DN Amount</th>
	<th>Department</th>
	<th>SKU Type</th>
	{if $sessioninfo.show_cost}
		{if $got_rounding_adj}
			<th>Amount<br />Before Round</th>
			<th>Rounding<br />Adjust</th>
		{/if}
		{if $have_fc}
			<th>Foreign Amount</th>
			<th>Exchange Rate</th>
		{/if}
		<th>Amount ({$config.arms_currency.symbol})</th>
		{if $is_under_gst}
			<th>GST ({$config.arms_currency.symbol})</th>
			<th>Amount <br />Incl. GST ({$config.arms_currency.symbol})</th>
		{/if}
	{/if}
	<th>Returned Date</th>
</tr>
{/if}
<tr {cycle values="bgcolor=#eeeeee,"}>
	<td align=center>{$count}.</td>
	<td>{$gra[i].report_prefix}{$gra[i].id|string_format:"%05d"}</td>
	<td>{$gra[i].vendor_code}</td>
	{if $config.enable_vendor_account_id}
		<td>{$gra[i].account_id}</td>
	{/if}
	<td>{$gra[i].vendor}</td>
	<td align=center>{$gra[i].misc_info.dn_no|default:"-"}</td>
	<td align=right>{$gra[i].misc_info.dn_amount|default:0|number_format:2}</td>
	<td>{$gra[i].department}</td>
	<td align=center>{$gra[i].sku_type|upper}</td>
	{if $sessioninfo.show_cost}
		{if $got_rounding_adj}
			<td class="r">
				{if $gra[i].currency_code}{$gra[i].currency_code}{/if} {$gra[i].amount-$gra[i].rounding_adjust|number_format:2}
			</td>
			<td class="r">
				{$gra[i].rounding_adjust|number_format:2}
			</td>
		{/if}
		{if $gra[i].currency_code}
			<td align="right">{$gra[i].currency_code} {$gra[i].amount|number_format:2}</td>
			<td align="right">{$gra[i].currency_rate}</td>
			{assign var=row_myr_amt value=$gra[i].amount*$gra[i].currency_rate}
		{else}
			{if $have_fc}
				<td align="right">-</td>
				<td align="right">-</td>
			{/if}
			{assign var=row_myr_amt value=$gra[i].amount}
		{/if}
		{assign var=row_myr_amt value=$row_myr_amt|round2}
	
		<td align=right {if $gra[i].currency_code}class="converted_base_amt"{/if}>
			{$row_myr_amt|number_format:2}{if $gra[i].currency_code}*{/if}
		</td>
		{if $is_under_gst}
			{assign var=row_gst_amount value=$row_myr_amt+$gra[i].gst}
			{assign var=row_gst_amount value=$row_gst_amount|round2}
			<td align=right>{$gra[i].gst|number_format:2}</td>
			<td align=right {if $gra[i].currency_code}class="converted_base_amt"{/if}>
				{$row_myr_amt+$gra[i].gst|number_format:2}{if $gra[i].currency_code}*{/if}
			</td>
		{/if}
	{/if}
	<td align=center>
		{if $gra[i].return_timestamp > 0}
			{$gra[i].return_timestamp|date_format:$config.dat_format}
		{else}
			<font color="red">Not Returned</font>
		{/if}
	</td>
</tr>
{assign var=total1 value=$total1+$row_myr_amt}
{assign var=total_gst value=$total_gst+$gra[i].gst}
{assign var=total_gst_amt value=$total_gst_amt+$row_gst_amount}
{assign var=last_branch value=$gra[i].branch}
{/section}
{if $sessioninfo.show_cost}
<tr class=sortbottom bgcolor=#ffee99 height=24>
	<th colspan="{$nr_colspan}" align=right>Total</th>
	<th align=right>{$total1|number_format:2}</th>
	{if $is_under_gst}
		<th align=right>{$total_gst|number_format:2}</th>
		<th align=right>{$total_gst_amt|number_format:2}</th>
	{/if}
	<td>&nbsp;</td>
</tr>
{/if}
</table>
{/if}

{include file=footer.tpl}
{if $smarty.request.submit eq 'Print'}
<script>
window.print();
</script>
{/if}
