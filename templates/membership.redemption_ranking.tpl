{*
8/18/2010 11:11:07 AM Justin
- Modified to have print report and export to excel format.

8/27/2010 3:51:03 PM Justin
- Added Cash column and total.
- Added Cash filter.

10/12/2011 3:29:08 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

1/14/2013 2:39 PM Justin
- Enhanced to display Voucher value and codes.

07/23/2013 04:07 PM Justin
- Added cost column by privilege.
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
        document.f_a['a'].value = 'show_ranking';
	}else if(a=='print'){
        document.f_a.target = '_blank';
		document.f_a['a'].value = 'print_report';
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

.report_table .body:nth-child(odd){
	background-color: #ffffcc;
}

</style>
{/literal}
{else}
{literal}
<style>

.report_table{
	border:0px solid #000;
	border-top:1px solid #000;
	border-left:1px solid #000;
}

.report_table td, th{
	border-bottom:1px solid #000;
	border-right:1px solid #000;
	padding:6px 4px;
}
</style>
{/literal}
{assign var=hdr value=3}
{/if}
<body {if $skip_header}onload="window.print()"{/if}>
<h{$hdr|default:1}>{$PAGE_TITLE}</h{$hdr|default:1}>

{if !$no_header_footer}
<form name="f_a" class="noprint" method="post" style="border:1px solid #eee;padding:5px;white-space:nowrap;">
	<input type="hidden" name="a" value="show_ranking" />

	<p>
	<b>Redemption Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>PostCode</b>
	<input type="text" size="5" name="postcode" value="{$smarty.request.postcode}" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>State</b>
	<input type="text" size="20" name="state" value="{$smarty.request.state}" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>City</b>
	<input type="text" size="20" name="city" value="{$smarty.request.city}" />
	</p>
	
	<p>
	<select name="top_or_btm">
	    <option value="top" {if $smarty.request.top_or_btm eq 'top'}selected {/if}>Top</option>
	    <option value="btm" {if $smarty.request.top_or_btm eq 'btm'}selected {/if}>Bottom</option>
	</select>
	<input type="text" size="5" name="show_rows" value="{$smarty.request.show_rows}" /> (Max 1000)
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>By</b>
	<select name="sort_by">
	    <option value="qty" {if $smarty.request.sort_by eq 'qty'}selected {/if}>Qty</option>
	    <option value="pt_need" {if $smarty.request.sort_by eq 'pt_need'}selected {/if}>Point</option>
	    <option value="cash_need" {if $smarty.request.sort_by eq 'cash_need'}selected {/if}>Cash</option>
	</select>
	{if $BRANCH_CODE eq 'HQ'}
	    &nbsp;&nbsp;&nbsp;&nbsp;
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
	    	{/if}
	    	</optgroup>
		</select>
	{/if}
	</p>
	<p>
		<input type="button" value="Refresh" onClick="submit_form('refresh');">
		<input type="button" value="Print" onClick="submit_form('print');" />
		<input type="button" value="Export" onClick="submit_form('export_excel');" />
	</p>
</form>
{/if}
<br />

{if !$table}
	{if $smarty.request.sub}No Data{/if}
{else}
	<table width="100%" class="report_table" {if $skip_header}cellpadding=0 cellspacing=0 border=0{/if}>
	    <tr class="header">
	        <th width="50">&nbsp;</th>
	        <th width="100">ARMS Code</th>
	        <th>Art No</th>
	        <th>MCode</th>
	        <th>Description</th>
			{if $sessioninfo.show_cost}
				<th>Cost</th>
			{/if}
	        <th>Qty</th>
	        <th>Points</th>
	        <th>Cash</th>
	    </tr>
	    {foreach from=$table item=r name=f}
	        {assign var=sid value=$r.sku_item_id}
	        <tr class="body">
	            <td>{$smarty.foreach.f.iteration|default:"&nbsp;"}.</td>
	            <td>{$items_details.$sid.sku_item_code|default:"&nbsp;"}</td>
	            <td>{$items_details.$sid.artno|default:"&nbsp;"}</td>
	            <td>{$items_details.$sid.mcode|default:"&nbsp;"}</td>
	            <td>
					{$items_details.$sid.description|default:"&nbsp;"}
					{if $items_details.$sid.is_voucher && $items_details.$sid.voucher_code}
						<div style="color:blue;">
							Voucher Value: {$items_details.$sid.voucher_value|number_format:2}<br />
							Voucher Codes: {$items_details.$sid.voucher_code}
						</div>
					{/if}
				</td>
				{if $sessioninfo.show_cost}
					<td align="right">{$items_details.$sid.cost|number_format:$config.global_cost_decimal_points}</td>
				{/if}
	            <td align="right" class="r">{$r.qty|qty_nf|default:"&nbsp;"}</td>
                <td align="right" class="r">{$r.pt_need|number_format|default:"&nbsp;"}</td>
                <td align="right" class="r">{$r.cash_need|number_format:2|default:"&nbsp;"}</td>
	        </tr>
	        
	        {assign var=total_qty value=$total_qty+$r.qty}
	        {assign var=total_pt value=$total_pt+$r.pt_need}
	        {assign var=total_cash value=$total_cash+$r.cash_need}
	    {/foreach}
		<tr class="header">
			{assign var=colspan value="5"}
			{if $sessioninfo.show_cost}
				{assign var=colspan value=$colspan+1}
			{/if}
		    <th align="right" colspan="{$colspan}" class="r">Total</th>
		    <th align="right" class="r">{$total_qty|qty_nf|default:"&nbsp;"}</th>
		    <th align="right" class="r">{$total_pt|number_format|default:"&nbsp;"}</th>
		    <th align="right" class="r">{$total_cash|number_format:2|default:"&nbsp;"}</th>
		</tr>
	</table>
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
</script>
{/if}
{include file='footer.tpl'}
