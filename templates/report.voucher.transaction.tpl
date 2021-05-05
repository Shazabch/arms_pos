{*
3/7/2011 3:04:11 PM Alex
- created by me

4/13/2011 3:39:34 PM Alex
- add show amount

4/28/2011 3:53:41 PM Alex
- add show group monthly

1/31/2013 1:56 PM Andy
- commit the changes done by alex.

06/29/2020 10:53 AM Sheila
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
	<b>POS Date From</b>
	<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	<b>To</b>
	<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	<input type="checkbox" name="by_monthly" id="by_monthly_id" value="1" {if $smarty.request.by_monthly}checked {/if} />
	<label for="by_monthly_id"><b>Group Monthly</b></label>

	<br>
	<b>Voucher Code: </b><input name="search_code" value="{$smarty.request.search_code}">

	</p>
	<p>
	<button class="btn btn-primary" name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
	{/if}
	</p>

</form>
{/if}
<font color="red">*</font> Red color indicate not belong to ARMS code
<h2>{$report_title}</h2>

{if $tb}

<table class="tb" cellspacing="0" cellpadding="2" border="0" id="tbl_cat">
    <tr>
		<th align="left">Voucher Code</th>
		<th align="left">Amt</th>
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
	
	{assign var=colspan value=$columnspan+5}
	
	{if $code_amount}
		<tr>
			<td colspan="{$colspan}" style="background:#0afaff"><b>{$code_qty} ARMS Voucher</b></td>
		</tr>
	    {foreach from=$code_amount name=arms_voucher key=id item=r}
		   	{include file='report.voucher.transaction.row.tpl' row=$tb.$id.data vc_code=$id type="arms"}
	    {/foreach}
	    {assign var=id value=''}
	    {include file='report.voucher.transaction.row.tpl' row=$total_arms_voucher type="total"}
	{/if}
	{if $unknown_code}
		<tr style="background:#ffaaa0">
			<td colspan="{$colspan}"><b>{$unknown_qty} Not ARMS Voucher</b></td>
		</tr>
	    {foreach from=$unknown_code name=unknown_voucher key=id item=r}
		   	{include file='report.voucher.transaction.row.tpl' row=$tb.$id.data vc_code=$id type="unknown"}
	    {/foreach}
	    {assign var=id value=''}
	    {include file='report.voucher.transaction.row.tpl' row=$total_not_arms_voucher type="total"}
	{/if}

	<tr class=sortbottom style="background:#8888ff">
		<th align="right">Grand Total</th>
        <th align="left">&nbsp;</th>
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
				<td class="small" align="right" title="{$tooltip}">{$qty}<br />{$val|number_format:2}</td>
			{else}
			    <td class="small" align="right">&nbsp;</td>
			{/if}
		{/foreach}

		<td class="small" align="right">{$tb_total.total.total.used}</td>
		<td class="small" align="right">{$tb_total.total.total.amt|number_format:2}</td>
	</tr>
</table>

{else}
	{if $table}- No Data -{/if}
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
</script>
{/literal}
{/if}
{include file=footer.tpl}
