{*
1/21/2011 9:58:45 AM Andy
- Add can search rejected, canceled/terminated for invoice and CN/DN.
- Add status column for summary report.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.

5/25/2015 9:57 AM Justin
- Bug fixed on column out of range.
- Bug fixed on total amount is calculated wrongly.
*}

{include file='header.tpl'}

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
<style>

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

CN_SUMMARY_MAIN_MODULE = {
	form_element: undefined,
	initialize: function(){
		this.form_element = document.f_a;
		
		// store form into variable
		if(!this.form_element){
			alert('Module failed to initialized!');
			return false;
		}
		
		// initial calendar
		Calendar.setup({
	        inputField     :    "inp_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_from",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });

	    Calendar.setup({
	        inputField     :    "inp_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_to",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	    
	    // event when user click refresh
	    $('btn_refresh').observe('click', function(){
            CN_SUMMARY_MAIN_MODULE.submit_form();
		});
	},
	// function to validate form
	check_form: function(){
		if(!this.form_element['date_from'].value){
			alert('Please select date from');
			return false;
		}
		if(!this.form_element['date_to'].value){
			alert('Please select date to');
			return false;
		}
		return true;
	},
	// function to submit/refresh form
	submit_form: function(){
		if(!this.check_form())  return false;
		
		// validate success
		this.form_element.submit();
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="load_report" value="1" />
	
	<b>Date From</b>
	<input type="text" name="date_from" id="inp_date_from" value="{$smarty.request.date_from}" readonly size="12" />
	<img align="absmiddle" src="/ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From"/>
	&nbsp;
	<b>to</b>
	<input type="text" name="date_to" id="inp_date_to" value="{$smarty.request.date_to}" readonly size="12" />
	<img align="absmiddle" src="/ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date to"/>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>By user</b>
	<select name="user_id">
	<option value="">-- All --</option>
	{foreach from=$users key=uid item=r}
	<option value="{$uid}" {if $smarty.request.user_id eq $uid}selected {/if}>{$r.u}</option>
	{/foreach}
	</select>
	<br />
	<p>
		<b>Invoice To</b>
		<select name="to_branch_id">
		    <option value="">-- All --</option>
		    {foreach from=$branches key=bid item=b}
	    	    {if !$branch_group.have_group.$bid}
	    	    	<option value="{$bid}" {if $smarty.request.to_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				{/if}
	    	{/foreach}
	    	{foreach from=$branch_group.header key=bgid item=bg}
	    	    <optgroup label="{$bg.code}">
	    	    {foreach from=$branch_group.items.$bgid item=r}
	    	        <option value="{$r.branch_id}" {if $smarty.request.to_branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
	    	    {/foreach}
                </optgroup>
	    	{/foreach}
		</select>
		
		&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Status</b>
		<select name="status">
			<option value="">-- All --</option>
			<option value="1" {if $smarty.request.status == 1}selected {/if}>Draft / Waiting for Approval</option>
			<option value="2" {if $smarty.request.status == 2}selected {/if}>Approved</option>
			<option value="3" {if $smarty.request.status == 3}selected {/if}>Rejected</option>
			<option value="4" {if $smarty.request.status == 4}selected {/if}>Cancelled/Terminated</option>
		</select>
		
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" value="Refresh" id="btn_refresh" />
	</p>
</form>

<script>
    CN_SUMMARY_MAIN_MODULE.initialize();
</script>

<br />
{if !$data}
	{if $smarty.request.load_report and !$err}- No record -{/if}
{else}
    <table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:1px;border:1px solid #000" class="sortable" id="tbl_data">
        <thead>
			<tr bgcolor="#ffee99">
				<th>&nbsp;</th>
				<th>Invoice No.</th>
				<th>Status</th>
				<th>Created By</th>
				<th>Invoice To</th>
				<th>Invoice Total</th>
				{if $is_under_gst}
					<th>Invoice Total<br />Incl. GST</th>
				{/if}
				<th>Discount (%)</th>
				<th>Amount</th>
				{if $is_under_gst}
					<th>GST</th>
					<th>Amount<br />Incl. GST</th>
				{/if}
				<th>Invoice Date</th>
			</tr>
	    </thead>
	    {assign var=total_amt value=0}
	    {foreach from=$data item=r name=f}
	        <tr bgcolor="{cycle values=",#eeeeee"}">
	            <td>{$smarty.foreach.f.iteration}.</td>
	            <td>
	                <a href="/consignment.{if $sheet_type eq 'dn'}debit{else}credit{/if}_note.php?a=view&branch_id=1&id={$r.id}" target=_blank>
				    {strip}
				    {if $r.inv_no}
						{$r.inv_no}
					{else}
						{$r.branch_prefix}{$r.id|string_format:"%05d"}
					{/if}
					
					{if $r.approved}
						
					{elseif !$r.status}
						(DD)
					{elseif $r.status >=1}
						(PD)
					{/if}
					{/strip}
					</a>
				 	{if preg_match('/\d/',$r.approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$r.approvals aorder_id=$r.approval_order_id}</font></div>
					{/if}
	            </td>
	            <td>
					{if $r.status eq 1 and $r.approved eq 1}
					    Fully Approved
					{elseif $r.status eq 1 and $r.approved eq 0}
					    Waiting for approval
			        {elseif $r.status eq 0 and $r.approved eq 0}
			            Draft
			        {elseif $r.status eq 2 and $r.approved eq 0}
			            Rejected
			        {elseif $r.status eq 4 or $r.status eq 5}
			            Cancelled/Terminated
					{/if}
				</td>
	            <td>{$r.user_name}</td>
	            <td>{$r.branch_code_2} - {$r.to_branch_description}</td>
				{if $r.is_under_gst}
					<td class="r">{$r.sub_total_gross_amt|number_format:2}</td>
					<td class="r">{$r.total_amount|number_format:2}</td>
				{else}
					<td class="r">{$r.total_amount|number_format:2}</td>
				{/if}
	            <td class="r">{$r.discount|default:'-'}</td>
				{assign var=row_amt value=$r.total_amount|round2}
				{if $r.is_under_gst}
					<td class="r">{$r.total_gross_amt|number_format:2}</td>
					<td class="r">{$r.total_gst_amt|number_format:2}</td>
					<td class="r">{$row_amt|number_format:2}</td>
					{assign var=total_amt value=$total_amt+$r.total_gross_amt}
					{assign var=total_gst value=$total_gst+$total_gst_amt}
					{assign var=total_gst_amt value=$total_gst_amt+$row_amt}
				{else}					
					<td class="r">{$row_amt|number_format:2}</td>
					{if $is_under_gst}
						<td align="center">-</td>
						<td align="center">-</td>
					{/if}
					{assign var=total_amt value=$total_amt+$row_amt}
				{/if}
	            <td align="center">{$r.date}</td>
	        </tr>
	    {/foreach}
	    
	    <tfoot>
		    <tr bgcolor="#ffee99" class="sortbottom">
				{assign var=ttl_colspan value=7}
				{if $is_under_gst}
					{assign var=ttl_colspan value=$ttl_colspan+1}
				{/if}
					
				<td colspan="{$ttl_colspan}" align=right><b>Total</b></td>
				<td class="r">{$total_amt|number_format:2}</td>
				{if $is_under_gst}
					<td class="r">{$total_gst|number_format:2}</td>
					<td class="r">{$total_gst_amt|number_format:2}</td>
				{/if}
				<td>&nbsp;</td>
			</tr>
		</tfoot>
	</table>
{/if}


{include file='footer.tpl'}
