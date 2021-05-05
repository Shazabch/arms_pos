{*
3/16/2011 2:32:42 PM Alex / Unknown
- add label for transaction and amount
- Add link to membership info

5/21/2018 5:00 pm Kuan Yeh
- Bug fixed of logo shown on excel export

2/1/2019 2:38 PM Andy
- Fixed branch group cannot show data.
- Fixed incorrect column display when show all branch data.

2/15/2019 5:29 PM Andy
- Fixed Post Code and City filter not working.

06/30/2020 02:25 PM Sheila
- Updated button css.

7/17/2020 10:05 AM William
- Bug fix change the word "Kad History" to "Card History".
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
<form method=post class=form>
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
					<option value="{$bgid}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					
					{foreach from=$branches_group.items[$r.id] key=bid item=bgi}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;{$bgi.code}</option>
					{/foreach}
				{/foreach}
			</optgroup>
		{/if}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<b>Post Code</b> <input size=10 type=text name=post_code value="{$smarty.request.post_code}" />
&nbsp;&nbsp;&nbsp;&nbsp;
<b>City</b> <input size="30" type=text name=city value="{$smarty.request.city}" />

<br><b>Transaction Count: Min</b> <input size=5 type=text name=min_tran value="{$smarty.request.min_tran}">&nbsp;&nbsp;&nbsp;&nbsp;<b>Max</b> <input size=5 type=text name=max_tran value="{$smarty.request.max_tran}"><br />

<b>Amount: Min</b> <input size=5 type=text name=min_amount value="{$smarty.request.min_amount}">&nbsp;&nbsp;&nbsp;&nbsp;<b>Max</b> <input size=5 type=text name=max_amount value="{$smarty.request.max_amount}"><br />
<select name="order_type">
	<option value="top" {if $smarty.request.order_type eq 'top'}selected {/if}>Top</option>
	<option value="bottom" {if $smarty.request.order_type eq 'bottom'}selected {/if}>Bottom</option>
</select>
&nbsp;<input size=5 type=text name=top_num value="{$top_number|default:10}"> (Max 1000)
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>Note: Report Maximum Shown 1 Year
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h2>
{$report_title}
<!--Branch: {$branch_name}--></h2>
<table class=report_table width=100%>
<tr class="header c">
	<th></th>
	<th>Info</th>
	<th>Card No / NRIC</th>
	<th>Description</th>
	<th>&nbsp;</th>
	{foreach from=$label item=r}
	<th>{$r}</th>
	{/foreach}
	<th>Total</th>
</tr>

{foreach from=$table item=r name=i}
    <tr>
        <td rowspan=2>{$smarty.foreach.i.iteration}</td>
        <td rowspan=2 align="center">
			{if !$no_header_footer}
			 	<a href="membership.php?t=view&a=i&nric={$r.nric}" title="Member Information" target="_blank">
			 		<img border=0 src="ui/icons/user.png">
				</a>&nbsp;&nbsp;&nbsp;&nbsp;
			 	<a href="membership.php?t=history&a=i&nric={$r.nric}" title="Card History" target="_blank">
					<img border=0 src="ui/icons/book_open.png">
				</a>
			{/if}
		</td>
		<td rowspan=2 class=c1>{$r.nric}<br>{$r.card_no}</td>
		<td rowspan=2>{$r.name}</td>
		<th class="c2">Transaction</th>
		{foreach from=$label key=date item=r2}
		    <td class="c2 r">{$r.transaction.$date|number_format}</td>
		{/foreach}
		<td class="c2 r">{$r.total.transaction|number_format}</td>
	</tr>
	<tr>
		<th class="c3">Amount</th>
	{foreach from=$label key=date item=r2}
	    <td class="c3 r">{$r.amount.$date|number_format:2}</td>
	{/foreach}
	<td class="c3 r">{$r.total.amount|number_format:2}</td>
	</tr>
{/foreach}
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

