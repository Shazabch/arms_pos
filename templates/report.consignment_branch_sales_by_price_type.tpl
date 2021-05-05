{*
2/14/2011 11:26:01 AM Andy
- Remove sortable from report table which cause table rowspan broken.

4/12/2011 11:33:19 AM Justin
- Added use custom format to show all branches group by "Region".
- Added sub total for each Region.

6/17/2011 3:46:47 PM Andy
- Add show gross sales.
- Fix region total bugs.
- Fix custom region if untick "Group by Region" it will still group by region.

10/17/2011 3:41:44 PM Alex
- Modified the Ctn and Pcs round up to base on config set. 

11/21/2011 3:53:59 PM Andy
- Add class "report_table" for table.

11/2/2012 11:49:00 PM Fithri
- enhance to show report by monthly

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

12/2/2014 5:03 PM Justin
- Add a legend to let user know the selling/cost price is before GST.
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
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.got_sc{
	color:red;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var REPORT_MAIN_MODULE = {
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
            REPORT_MAIN_MODULE.submit_form();
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
		{if $config.consignment_modules && $config.masterfile_branch_region}
		    <optgroup label='Region'>
			{foreach from=$config.masterfile_branch_region key=type item=f}
				{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
					{assign var=curr_type value="REGION_`$type`"}
					<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
				{/if}
		    {/foreach}
			</optgroup>
		{/if}
	</select>
	{if $USE_CUSTOM && $config.masterfile_branch_region}
		<input type="checkbox" name="use_region" value="1" {if !$smarty.request.load_report || $smarty.request.use_region} checked{/if}> <b>Group by Region</b> 
		<input type="checkbox" name="group_by_date" value="1" {if !$smarty.request.load_report || $smarty.request.group_by_date} checked{/if}> <b>Group by Month</b> 
	{/if}
	&nbsp;&nbsp;&nbsp;&nbsp;

	<input type="button" value="Refresh" id="btn_refresh" />
	{if $config.enable_gst}
		<p>
			* Amount show in this report is GST exclusive.
		</p>
	{/if}
</form>

<script>
    REPORT_MAIN_MODULE.initialize();
</script>

<br />
{assign var=region_rowspan value=$price_type_arr|@count}
{assign var=region_rowspan value=$region_rowspan*3+5}

{if !$data}
	{if $smarty.request.load_report and !$err}- No record -{/if}
{elseif $USE_CUSTOM and $group_by_date}
	<h3>{$report_header}</h3>
	{foreach from=$data key=ci_date item=datedata name=fp}
	
	{assign var=curr_ttl_row value=0}
	{assign var=newdate value=true}
	{assign var=prev_region value='newdate'}
	
	<h2>Month : {$ci_date}</h2>
    <table class="report_table" width="100%" id="tbl_data">
        <thead bgcolor="#ffee99">
			<tr>
				<th rowspan="2">&nbsp;</th>
				<th rowspan="2">Branch</th>
				{foreach from=$price_type_arr item=pt}
				    <th colspan="3">{$pt|default:'NONE'}</th>
				{/foreach}
				<th colspan="3">Total</th>
			</tr>
			<tr>
			    {foreach from=$price_type_arr item=pt}
			    	<th>Qty</th>
			    	<th>Gross<br>Amt</th>
			        <th>Amt</th>
			    {/foreach}
			    <th>Qty</th>
			    <th>Gross<br>Amt</th>
			    <th>Amt</th>
			</tr>
	    </thead>
	    
	    {foreach from=$datedata key=bid item=bdata name=f}
	    	{assign var=curr_ttl_row value=$curr_ttl_row+1}
	    	{if $USE_CUSTOM && $config.masterfile_branch_region and $use_region}
			
	    		{assign var=curr_region_title value=$region_title.$ci_date.$bid.region}
	    		{assign var=curr_region value=$bdata.region_code}
	    		
	    		{if $curr_region ne $prev_region and $prev_region ne 'newdate'}
		    		{if $prev_region}
			    		<tr bgcolor="#adffea">
			    			<th colspan="2" class="r">Sub Total</th>
			            {foreach from=$price_type_arr item=pt}
			    			<th class="r">{$region_col_total.$ci_date.$prev_region.$pt.qty|qty_nf}</th>
			    			<th class="r">{$region_col_total.$ci_date.$prev_region.$pt.gross_amt|num_format:2}</th>
			                <th class="r">{$region_col_total.$ci_date.$prev_region.$pt.amt|number_format:2}</th>
			            {/foreach}
			            	<th class="r">{$region_row_total.$ci_date.$prev_region.qty|qty_nf}</th>
			            	<th class="r">{$region_row_total.$ci_date.$prev_region.gross_amt|number_format:2}</th>
		            		<th class="r">{$region_row_total.$ci_date.$prev_region.amt|number_format:2}</th>
			            </tr>
			    		<tr><td colspan="{$region_rowspan}">&nbsp;</td></tr>
			    		{assign var=curr_ttl_row value=1}
		    		{/if}
	    		{/if}
				{if $curr_region_title and ($curr_region_title ne $prev_region_title or $prev_region == 'newdate')}
				<tr bgcolor="#edffed">
					<td colspan="{$region_rowspan}"><b>{$curr_region_title}</b></td>
				</tr>
				{/if}
	    		{assign var=prev_region_title value=$curr_region_title}
	    		{assign var=prev_region value=$curr_region}
				
	    	{/if}
	        <tr bgcolor="{cycle values=",#eeeeee"}">
	            <td>{$curr_ttl_row}.</td>
	            <td width="50%">{$branches.$bid.code} - {$branches.$bid.description}</td>
	            
	            {foreach from=$price_type_arr item=pt}
	                <td class="r">{$bdata.$pt.qty|qty_nf}</td>
	                <td class="r">{$bdata.$pt.gross_amt|number_format:2}</td>
	                <td class="r">{$bdata.$pt.amt|number_format:2}</td>
	            {/foreach}
	            <td class="r">{$row_total.$ci_date.$bid.qty|qty_nf}</td>
	            <td class="r">{$row_total.$ci_date.$bid.gross_amt|number_format:2}</td>
	            <td class="r">{$row_total.$ci_date.$bid.amt|number_format:2}</td>
	        </tr>
			{assign var=newdate value=false}
	    {/foreach}
	    {if $USE_CUSTOM && $config.masterfile_branch_region && $use_region and $prev_region}
	    	<tr bgcolor="#adffea">
		    	<th colspan="2" class="r">Sub Total</th>
	            {foreach from=$price_type_arr item=pt}
	    			<th class="r">{$region_col_total.$ci_date.$prev_region.$pt.qty|qty_nf}</th>
	    			<th class="r">{$region_col_total.$ci_date.$prev_region.$pt.gross_amt|number_format:2}</th>
	                <th class="r">{$region_col_total.$ci_date.$prev_region.$pt.amt|number_format:2}</th>
	            {/foreach}
	        	<th class="r">{$region_row_total.$ci_date.$prev_region.qty|qty_nf}</th>
	        	<th class="r">{$region_row_total.$ci_date.$prev_region.gross_amt|number_format:2}</th>
	    		<th class="r">{$region_row_total.$ci_date.$prev_region.amt|number_format:2}</th>
		    </tr>
		{/if}
	    <tfoot bgcolor="#ffee99">
	        <tr>
	            <th colspan="2" class="r">Total</th>
	            {foreach from=$price_type_arr item=pt}
	    			<th class="r">{$col_total.$ci_date.$pt.qty|qty_nf}</th>
	    			<th class="r">{$col_total.$ci_date.$pt.gross_amt|number_format:2}</th>
	                <th class="r">{$col_total.$ci_date.$pt.amt|number_format:2}</th>
	            {/foreach}
	            <th class="r">{$grand_total.$ci_date.qty|qty_nf}</th>
	            <th class="r">{$grand_total.$ci_date.gross_amt|number_format:2}</th>
	            <th class="r">{$grand_total.$ci_date.amt|number_format:2}</th>
	        </tr>
	    </tfoot>
	</table>
	<br /><br />
	
	{/foreach}
{else}
	<h3>{$report_header}</h3>
    <table class="report_table" width="100%" id="tbl_data">
        <thead bgcolor="#ffee99">
			<tr>
				<th rowspan="2">&nbsp;</th>
				<th rowspan="2">Branch</th>
				{foreach from=$price_type_arr item=pt}
				    <th colspan="3">{$pt|default:'NONE'}</th>
				{/foreach}
				<th colspan="3">Total</th>
			</tr>
			<tr>
			    {foreach from=$price_type_arr item=pt}
			    	<th>Qty</th>
			    	<th>Gross<br>Amt</th>
			        <th>Amt</th>
			    {/foreach}
			    <th>Qty</th>
			    <th>Gross<br>Amt</th>
			    <th>Amt</th>
			</tr>
	    </thead>
	    {foreach from=$data key=bid item=bdata name=f}
	    	{assign var=curr_ttl_row value=$curr_ttl_row+1}
	    	{if $USE_CUSTOM && $config.masterfile_branch_region and $use_region}
	    		{assign var=curr_region_title value=$region_title.$bid.region}
	    		{assign var=curr_region value=$bdata.region_code}
	    		
	    		{if $curr_region ne $prev_region}
		    		{if $prev_region}
			    		<tr bgcolor="#adffea">
			    			<th colspan="2" class="r">Sub Total</th>
			            {foreach from=$price_type_arr item=pt}
			    			<th class="r">{$region_col_total.$prev_region.$pt.qty|qty_nf}</th>
			    			<th class="r">{$region_col_total.$prev_region.$pt.gross_amt|num_format:2}</th>
			                <th class="r">{$region_col_total.$prev_region.$pt.amt|number_format:2}</th>
			            {/foreach}
			            	<th class="r">{$region_row_total.$prev_region.qty|qty_nf}</th>
			            	<th class="r">{$region_row_total.$prev_region.gross_amt|number_format:2}</th>
		            		<th class="r">{$region_row_total.$prev_region.amt|number_format:2}</th>
			            </tr>
			    		<tr><td colspan="{$region_rowspan}">&nbsp;</td></tr>
			    		{assign var=curr_ttl_row value=1}
		    		{/if}
		    		<tr bgcolor="#edffed">
		    			<td colspan="{$region_rowspan}"><b>{$curr_region_title}</b></td>
		    		</tr>
	    		{/if}
	    		{assign var=prev_region value=$curr_region}
	    	{/if}
	        <tr bgcolor="{cycle values=",#eeeeee"}">
	            <td>{$curr_ttl_row}.</td>
	            <td width="50%">{$branches.$bid.code} - {$branches.$bid.description}</td>
	            
	            {foreach from=$price_type_arr item=pt}
	                <td class="r">{$bdata.$pt.qty|qty_nf}</td>
	                <td class="r">{$bdata.$pt.gross_amt|number_format:2}</td>
	                <td class="r">{$bdata.$pt.amt|number_format:2}</td>
	            {/foreach}
	            <td class="r">{$row_total.$bid.qty|qty_nf}</td>
	            <td class="r">{$row_total.$bid.gross_amt|number_format:2}</td>
	            <td class="r">{$row_total.$bid.amt|number_format:2}</td>
	        </tr>
	    {/foreach}
	    {if $USE_CUSTOM && $config.masterfile_branch_region && $use_region and $prev_region}
	    	<tr bgcolor="#adffea">
		    	<th colspan="2" class="r">Sub Total</th>
	            {foreach from=$price_type_arr item=pt}
	    			<th class="r">{$region_col_total.$prev_region.$pt.qty|qty_nf}</th>
	    			<th class="r">{$region_col_total.$prev_region.$pt.gross_amt|number_format:2}</th>
	                <th class="r">{$region_col_total.$prev_region.$pt.amt|number_format:2}</th>
	            {/foreach}
	        	<th class="r">{$region_row_total.$prev_region.qty|qty_nf}</th>
	        	<th class="r">{$region_row_total.$prev_region.gross_amt|number_format:2}</th>
	    		<th class="r">{$region_row_total.$prev_region.amt|number_format:2}</th>
		    </tr>
		{/if}
	    <tfoot bgcolor="#ffee99">
	        <tr>
	            <th colspan="2" class="r">Total</th>
	            {foreach from=$price_type_arr item=pt}
	    			<th class="r">{$col_total.$pt.qty|qty_nf}</th>
	    			<th class="r">{$col_total.$pt.gross_amt|number_format:2}</th>
	                <th class="r">{$col_total.$pt.amt|number_format:2}</th>
	            {/foreach}
	            <th class="r">{$grand_total.qty|qty_nf}</th>
	            <th class="r">{$grand_total.gross_amt|number_format:2}</th>
	            <th class="r">{$grand_total.amt|number_format:2}</th>
	        </tr>
	    </tfoot>
	</table>
{/if}
{include file='footer.tpl'}
