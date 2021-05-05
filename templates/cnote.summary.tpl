{*
06/24/2020 04:46 PM Sheila
- Updated button css
*}
{include file=header.tpl}

{if !$no_header_footer}
<style>
{literal}
tr:nth-child(odd) {
	background: #ffffff;
}
tr:nth-child(even) {
	background: #eeeeee;
}
{/literal}
</style>
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/if}

<h1>{$PAGE_TITLE}</h1>
{if !$no_header_footer}
<form method=post class=form id="f_a" name="f_a">
	<input type="hidden" name="a" value="generate_report" />
<p>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	<option value="">-- All --</option>
	    {foreach from=$branches item=b}
	        {if !$branch_group.have_group[$b.id]}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
	        {/if}
	    {/foreach}
	    {if $branch_group.header}
	        {foreach from=$branch_group.header key=bgid item=r}
	        <optgroup label="{$r.code}">
				{foreach from=$branch_group.items.$bgid key=bid item=b}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/foreach}
			</optgroup>
			{/foreach}
		{/if}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{else}
	<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
{/if}

<span>
	<b>Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;
	<b>To</b>
	<input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;
</span>

&nbsp;&nbsp;&nbsp;&nbsp;
<span>
<b>By user</b>
<select name="user_id">
<option value=0>-- All --</option>
{section name=i loop=$user}
<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
{/section}
</select>
</span>

&nbsp;&nbsp;&nbsp;&nbsp;
<span>
<b>CN Status</b>
<select name="status">
	<option value="0" {if $smarty.request.status == 0}selected{/if}>-- All --</option>
	<option value="1" {if $smarty.request.status == 1}selected{/if}>Draft / Waiting for Approval</option>
	<option value="2" {if $smarty.request.status == 2}selected{/if}>Approved</option>
</select>
</span>
</p>

<input type="hidden" name="submit" value="1">
<button class="btn btn-primary" name="generate_report" >Refresh</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</form>
{/if}

{if !$table}
	{if $smarty.request.submit}-- No data --{/if}
{else}
{assign var=n value=1}
<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:1px;border:1px solid #000">
	<tbody>
	<thead>		
		<tr class="thd">
			<th></th>
			<th>CN No</th>
			<th>CN Date</th>
			<th>Invoice No</th>
			<th>Invoice Date</th>
			<th>Customer Info</th>
			<th>Amount</th>
			<th>Qty</th>
			<th>Create By</th>
			<th>Adjustment Docs</th>
			<th>Return Type</th>
		</tr>
	</thead>	
	
	
	{foreach from=$table item=r}
	<tr>
		<td>{$n++}.</td>
		<td align="left"><a href="/cnote.php?a=view&branch_id={$r.branch_id}&id={$r.id}" target="_blank">{$r.cn_no}</a></td>
		<td align="center">{$r.cn_date}</td>
		{if $r.return_type eq "multiple_inv"}
			{assign var=cnote_id value=$r.id}
			{assign var=itemCount value=$cnItemList.$cnote_id|@count}
			<td colspan="2">
				{foreach name=cnItems from=$cnItemList.$cnote_id item=cn_items}
					{$cn_items}{if $smarty.foreach.cnItems.iteration neq $itemCount},{/if}
				{/foreach}
			</td>
		{else}
			<td align="left">{$r.inv_no|default:'-'}</td>
			<td align="center">{if $r.inv_date != '0000-00-00'}{$r.inv_date}{else}-{/if}</td>
		{/if}
		<td align="left">{$r.cust_name}{if $r.cust_brn}({$r.cust_brn}){/if}</td>
		<td align="right">{$r.total_amount|number_format:2}</td>
		<td align="right">{$r.total_qty}</td>
		<td align="center">{$r.created_u}</td>
		<td align="left">
			{if $r.adj_id_list}
				{foreach from=$r.adj_id_list item=adj_id name=fadj}
					{if !$smarty.foreach.fadj.first}, {/if}
					<a href="adjustment.php?a=view&branch_id={$r.branch_id}&id={$adj_id}" target="_blank">ID#{$adj_id}</a>
				{/foreach}
			{else}
				&nbsp;
			{/if}
		</td>
		<td align="left">{if $r.return_type eq "multiple_inv"}Multiple Invoice{else}Single Invoice{/if}</td>
	</tr>
	{/foreach}
	
	{if $total}
	<tr class="thd">
		<td colspan="6" align="right"><b>Total</b></td>
		<td colspan="1" align="right">{$total.total_amount|number_format:2}</td>
		<td colspan="1" align="right">{$total.total_qty}</td>
		<td colspan="3"></td>
	</tr>
	{/if}
	</tbody>
</table>
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