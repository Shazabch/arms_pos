{*
5/14/2010 11:48:21 AM Andy
- Modified some words
- column width change to 15%

3/31/2011 3:36:59 PM Justin
- Replaced the AKAD no with member card name from config.

06/29/2020 04:26 PM Sheila
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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	color:red;
}

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

<script>
{literal}
function view_type_check(){
	if($('date_from').value > $('date_to').value){
		alert('Date Start cannot be late than Date End');
		return false;
	}
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
<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">
<p>
	{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id">
		 <option value="">-- All --</option>
	     {foreach from=$branches key=bid item=b}
	        {if !$branches_group.have_group.$bid}
		    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
		    {/if}
		{/foreach}
		{foreach from=$branches_group.header key=bgid item=bg}
	        <optgroup label="{$bg.code}">
	            {foreach from=$branches_group.items.$bgid key=bid item=b}
	                <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
	            {/foreach}
	        </optgroup>
	    {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}

	<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
	<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
	<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
</p>

<p>
	<b>User</b>
	<select name=user_id>
		<option value=0>-- All --</option>
        {foreach from=$user_list key=uid item=r}
            <option value="{$uid}" {if $smarty.request.user_id eq $uid}selected {/if}>{$r.u}</option>
        {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Type</b>
	<select name=point_type>
		<option value=0>-- All --</option>
		{foreach from=$points_type item=b}
			<option value="{$b.type}" {if $smarty.request.point_type eq $b.type}selected{/if}>{$b.type}</option>
		{/foreach}
	</select>
	
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
		<tr class="header">
			<th width="3%">#</th>
		    <th width="10%">{if $config.membership_cardname}{$config.membership_cardname}{else}Card{/if} No</th>
		    <th width="10%">Branch</th>
		    <th width="51%">Remarks</th>
		    <th width="6%">Date</th>
		    <th width="7%">User</th>
		    <th width="7%">Type</th>
		    <th width="5%">Points</th>
		    {if count($table)>$report_row}
		    	<th width="6" nowrap>&nbsp;</th>
		    {/if}
		</tr>
		
	<tbody {if count($table)>$report_row}class=testing style="height:600;overflow-y:auto;overflow-x:hidden;"{/if}>
	{foreach from=$table key=mp_key item=m_p name=mp}
		<tr>
			<td>{$smarty.foreach.mp.iteration}.</td>
			<td>{$table.$mp_key.card_no|default:'-'}</td>
			<td align="center">{$table.$mp_key.branch_code|default:'-'}</td>
			<td>{$table.$mp_key.remark|default:'-'}</td>
			<td align="center">{$table.$mp_key.date|default:'-'}</td>
			<td align="center">{$table.$mp_key.username|default:'-'}</td>
			<td align="center">{$table.$mp_key.type|default:'-'}</td>
			<td align="right">{$table.$mp_key.points|number_format:0|ifzero:'-'}</td>
			{if count($table)>$report_row}
				<td>&nbsp;</td>
			{/if}
		</tr>
	{/foreach}
	</tbody>
		<tr class="header">      
		    <th class="r" colspan=7>Total</th>
			<th class="r">{$total|number_format:0|ifzero:'-'}</th>
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
