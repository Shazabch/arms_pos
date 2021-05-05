{*
8/18/2010 11:11:07 AM Justin
- Modified to have print report and export to excel format.

8/27/2010 4:24:14 PM Justin
- Added Cash column and total.

10/12/2011 2:55:55 PM Alex
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- change row background color to use css

07/23/2013 04:07 PM Justin
- Added cost column by privilege.

11/14/2016 1:40 PM Andy
- Change print feature to use window.print()
- Enhanced to able to select report type. (Summary or Details)
*}

{include file='header.tpl'}

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
{literal}
function submit_form(a){
	if(a=='refresh'||!a){
        document.f_a.target = '';
        document.f_a['a'].value = 'show_summary';
	}else if(a=='print'){
        //document.f_a.target = '_blank';
		//document.f_a['a'].value = 'print_report';
		window.print();
		return;
	}else if(a == 'export_excel'){
        document.f_a.target = 'ifprint';
		document.f_a['a'].value = 'export_excel';
	}

	document.f_a.submit();
}
{/literal}
</script>

{literal}
<style>
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}

#tbl_content .sortmiddle:nth-child(odd){
	background-color: #eeeeee;
}
</style>
{/literal}
{/if}
<h1>{$PAGE_TITLE}</h1>
<iframe style="visibility:hidden;" width="1" height="1" name="ifprint"></iframe>
{if !$no_header_footer}
<form name="f_a" class="noprint" method="post" style="border:1px solid #eee;padding:5px;white-space:nowrap;">
	<input type="hidden" name="a" value="" />

	<p>
	<b>Redemption Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
	
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>By user</b>
	<select name="user_id">
	<option value="0">-- All --</option>
	{section name=i loop=$user}
	<option value="{$user[i].user_id}" {if $smarty.request.user_id eq $user[i].user_id}selected {/if}>{$user[i].u}</option>
	{/section}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>Status</b>
	<select name="status">
		<option value="">-- All --</option>
		{foreach from=$status_list key=k item=v}
			<option value="{$k}" {if $smarty.request.status eq $k}selected {/if}>{$v}</option>
		{/foreach}
	</select>
	</p>
	
	{if $BRANCH_CODE eq 'HQ'}
	    <b>Branch</b>
		<select name="branch_id">
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
            </optgroup>
	    	{/if}
	    	
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	<span>
		<b>Type: </b>
		{foreach from=$type_list key=k item=v}
			<input type="radio" name="show_type" value="{$k}" {if $smarty.request.show_type eq $k or (!$smarty.request.show_type and $k eq 1)}checked {/if}/> {$v}
		{/foreach}
	</span>
	<p>
		<input type="button" value="Refresh" onClick="submit_form('refresh');" />
		<input type="button" value="Print" onClick="submit_form('print');" />
		<input type="button" value="Export" onClick="submit_form('export_excel');" />
	</p>
</form>
{/if}
<br>
{if !$table}
{if $smarty.request.a}- No record -{/if}
{else}

<h2>{$report_title}</h2>
<table width="100%" id="tbl_content" class="report_table">
<tr class="header">
	<th width="50">&nbsp;</th>
	<th width="100">Redemption No</th>
	<th width="100">Created By</th>
	<th>Card No</th>
	<th>NRIC</th>
	<th>Date</th>
	{if $smarty.request.show_type eq 2}
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No.</th>
		<th>Description</th>
	{/if}
	<th>Points Redeem</th>
	<th>Qty</th>
	<th>Cash Needed</th>
	{if $smarty.request.show_type eq 1}
		<th>Print Count</th>
	{/if}
	<th>Cancelled</th>
	{if $sessioninfo.show_cost}
		<th>Cost</th>
	{/if}
</tr>
	{assign var=row_counter value=0}
	{foreach from=$table item=r name=f}
		{if $smarty.request.show_type eq 2}
			{foreach from=$r.item_list key=item_id item=r2}
				{assign var=row_counter value=$row_counter+1}
				<tr class="sortmiddle">
					<td>{$row_counter}.</td>
					<td>
						{if !$no_header_footer}
							<a href="membership.redemption_history.php?a=view&id={$r.id}&branch_id={$r.branch_id}&highlight_item_id={$item_id}" target="_blank">{$r.redemption_no|default:"&nbsp;"}</a>
						{else}
							{$r.redemption_no|default:"&nbsp;"}
						{/if}
					</td>
					<td>{$r.u|default:"&nbsp;"}</td>
					<td>{$r.card_no|default:"&nbsp;"}</td>
					<td>{$r.nric|default:"&nbsp;"}</td>
					<td align="center">{$r.date|default:"&nbsp;"}</td>
					
					
					<td>{$r2.sku_item_code}</td>
					<td>{$r2.mcode|default:'-'}</td>
					<td>{$r2.artno|default:'-'}</td>
					<td>{$r2.description|default:'-'}</td>
					<td align="right" class="r">{$r2.total_pt_need|number_format|default:"&nbsp;"}</td>
					<td align="right" class="r">{$r2.item_qty|qty_nf|default:"&nbsp;"}</td>
					<td align="right" class="r">{$r2.total_cash_need|number_format:2|default:"&nbsp;"}</td>
					
					<td>
						{if $r.status eq 1}
							<span style="color:red;">Cancel by {$r.cancel_by_u}</span>
						{else}
							&nbsp;
						{/if}
					</td>
					{if $sessioninfo.show_cost}
						<td align="right">{$r2.row_cost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
				</tr>
			{/foreach}
		{else}
			<tr class="sortmiddle">
	        <td>{$smarty.foreach.f.iteration}.</td>
	        <td>{if !$no_header_footer}<a href="membership.redemption_history.php?a=view&id={$r.id}&branch_id={$r.branch_id}" target="_blank">{$r.redemption_no|default:"&nbsp;"}</a>{else}{$r.redemption_no|default:"&nbsp;"}{/if}</td>
	        <td>{$r.u|default:"&nbsp;"}</td>
	        <td>{$r.card_no|default:"&nbsp;"}</td>
	        <td>{$r.nric|default:"&nbsp;"}</td>
	        <td align="center">{$r.date|default:"&nbsp;"}</td>
			<td align="right" class="r">{$r.total_pt_need|number_format|default:"&nbsp;"}</td>
			<td align="right" class="r">{$r.total_qty|qty_nf|default:"&nbsp;"}</td>
			<td align="right" class="r">{$r.total_cash_need|number_format:2|default:"&nbsp;"}</td>		    
		    <td align="right" class="r">{$r.print_count|number_format|default:"&nbsp;"}</td>
		    <td>
		        {if $r.status eq 1}
		            <span style="color:red;">Cancel by {$r.cancel_by_u}</span>
		        {else}
		        	&nbsp;
				{/if}
			</td>
			{if $sessioninfo.show_cost}
				<td align="right">{$r.row_cost|number_format:$config.global_cost_decimal_points}</td>
			{/if}
	    </tr>
		{/if}
	    
	{/foreach}
<tr class="sortbottom header">
	<th align="right" colspan="6" class="r">Total</th>
	{if $smarty.request.show_type eq 2}
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	{/if}
	<th align="right" class="r">{$total.total_pt_need|number_format|default:"&nbsp;"}</th>
	<th align="right" class="r">{$total.total_qty|qty_nf|default:"&nbsp;"}</th>
	<th align="right" class="r">{$total.total_cash_need|number_format:2|default:"&nbsp;"}</th>
	{if $smarty.request.show_type eq 1}
		<th align="right" class="r">{$total.print_count|number_format|default:"&nbsp;"}</th>
	{/if}
	<th align="right" class="r">{$total.cancel_count|number_format|default:"&nbsp;"}</th>
	{if $sessioninfo.show_cost}
		<td align="right">{$total.ttl_cost|number_format:$config.global_cost_decimal_points}</td>
	{/if}
</tr>
</table>
</body>
{/if}
{if !$no_header_footer}
<script>
{literal}
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
{/literal}

{if !$no_header_footer and $table}ts_makeSortable($('tbl_content'));{/if}
</script>
{/if}
{include file='footer.tpl'}
