{*
1/24/2013 3:35 PM Andy
- Add branch code.
*}

{include file="header.tpl"}

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
.td_lose{
	background-color: #F0FFF0;
}

.td_pack{
	background-color: #FFF5EE;
}
{/literal}
</style>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var REPACKING_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// init calendar
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
	},
	// function to submit form
	submit_form: function(){
		this.f.submit();
	}
};
{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

<form name="f_a" class="stdframe" onSubmit="return false;" method="post">
	<input type="hidden" name="show_report" value="1" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
			<option value="">-- All --</option>
			{foreach from=$branch_list key=bid item=b}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Vendor</b>
	<select name="vendor_id">
		<option value="">-- All --</option>
		{foreach from=$vendor_list key=vid item=v}
			<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected {/if}>{$v.code} - {$v.description}</option>
		{/foreach}
	</select>
	
	<p>
		<b>Status</b>
		<select name="status">
			<option value="">-- All --</option>
			{foreach from=$status_list key=k item=v}
				<option value="{$k}" {if $smarty.request.status eq $k}selected {/if}>{$v}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<input type="button" value="Show Report" onClick="REPACKING_REPORT.submit_form();" />
	</p>
</form>
<script type="text/javascript">REPACKING_REPORT.initialize();</script>

{if $smarty.request.show_report && !$err}
	<h2>{$report_title}</h2>
	
	{if !$data}
		* No Data *
	{else}
		<table class="report_table" width="100%">
			<tr class="header">
				<th rowspan="2">Branch</th>
				<th rowspan="2">Vendor</th>
				<th rowspan="2">Date</th>
				<th rowspan="2">Status</th>
				<th colspan="2">Lose Item</th>
				<th colspan="4">Pack Item</th>
				<th rowspan="2">Misc Cost</th>
				<th rowspan="2">Calculated<br />Unit Cost</th>
				<th rowspan="2">Total Cost</th>
			</tr>
			<tr class="header">
				{* Lose Item *}
				<th>Info</th>
				<th>Total Cost</th>
				
				{* Pack Item *}
				<th>ARMS Code</th>
				<th>MCode</th>
				<th>Description</th>
				<th>Qty</th>
			</tr>
			
			{foreach from=$data.data.rp_list key=rp_key item=rp name=frp}
				{foreach from=$rp.group_list key=group_id item=rpg}
					<tr>
						<td>{$rp.bcode}</td>
						<td>{$rp.vcode} - {$rp.v_desc}</td>
						<td>{$rp.repacking_date}</td>
						<td>
							{if !$rp.active}
								In-active
							{else}
								{if $rp.status eq 1 and $rp.approved eq 1}
									Completed
								{elseif $rp.status eq 0 and $rp.approved eq 0}
									Draft
								{else}
									-
								{/if}
							{/if}
						</td>
						
						{* Lose Item *}
						<td class="td_lose" nowrap="">
							<ul style="list-style:none;">
								{foreach from=$rpg.lose_item_list item=r}
									<li style="padding:0;margin:0;"> {$r.sku_item_code} ({$r.cost|number_format:$config.global_cost_decimal_points}) x {$r.qty}</li>
								{/foreach}
							</ul>
						</td>
						<td align="right" class="td_lose">{$rpg.total_lose_item_cost|number_format:$config.global_cost_decimal_points}</td>
						
						{* Pack Item *}
						<td class="td_pack">{$rpg.pack_item.sku_item_code}</td>
						<td class="td_pack">{$rpg.pack_item.mcode|default:'-'}</td>
						<td class="td_pack">{$rpg.pack_item.description|default:'-'}</td>
						<td class="td_pack" align="right">{$rpg.pack_item.qty}</td>
						
						{* misc cost *}
						<td align="right">{$rpg.pack_item.misc_cost|number_format:$config.global_cost_decimal_points}</td>
						
						{* Calculated Unit Cost *}
						<td align="right">{$rpg.pack_item.calc_cost|number_format:$config.global_cost_decimal_points}</td>
						
						{* Total Cost *}
						<td align="right">{$rpg.total_pack_item_cost|number_format:$config.global_cost_decimal_points}</td>
					</tr>
				{/foreach}
			{/foreach}
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
