{*
7/16/2019 10:51 AM William
- Added new module "Stock Take Inquiry".

8/9/2019 10:17 AM William
- Remove extra javascript function.

06/30/2020 02:55 PM Sheila
- Updated button css.
*}
{include file=header.tpl}

{if !$no_header_footer}
<style>
{literal}
.tb tr td,.tb tr th{
	padding:6px;
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
{literal}
<script type="text/javascript">
	//check filter before submit
	function check_form(){
		var branch_id = document.f_a['branch_id'].value;
		if(branch_id == ''){
			alert('Please Select Branch.');
			return false;
		}
		var sku_code_list = $('sku_code_list').length;
		if(sku_code_list <= 0){
			alert('Please Select SKU.');
			return false;
		}
	}
</script>
{/literal}
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
<form method=post class=form id="f_a" name="f_a" onsubmit="return check_form()">
	<input type="hidden" name="a" value="show_report" />
<p>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	<option value="">-- Please Select --</option>
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
</p>


<p>
<div id="sku_items_autocomplete">
{include file="sku_items_autocomplete_multiple.tpl"}
</div>
</p>

<p>
<input name="group_by_sku" type="checkbox" {if $smarty.request.group_by_sku}checked{/if} value="1" />&nbsp;
<b>Group By SKU</b>
</p>

	<input type=hidden name=submit value=1>
	<button class="btn btn-primary" name="show_report" onclick="passArrayToInput();" >{#SHOW_REPORT#}</button>
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button class="btn btn-primary" name="a" value="output_excel" onclick="passArrayToInput();">{#OUTPUT_EXCEL#}</button>
	{/if}
</form>
{/if}


{if !$table}
	{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h3>{$report_title}</h3>
<table  cellspacing="0" width="100%" class="tb">
	<thead>		
		<tr class="thd">
			<th rowspan="2">Sku Item Code</th>
			<th rowspan="2">Art No<br>Date</th>
			<th rowspan="2">MCode
				{if !$smarty.request.group_by_sku}<br>Location{/if}
			</th>
			<th rowspan="2">Old Code
				{if !$smarty.request.group_by_sku}<br>Shelf{/if}
			</th>
			<th rowspan="2">Description</th>
			<th rowspan="2">
				Stock Balance
			</th>
			<th rowspan="2">Stock Take Quantity</th>
			{if !$smarty.request.group_by_sku}
			<th rowspan="2">Selling Price</th>
			<th rowspan="2">Stock Take Cost</th>
			
			{/if}
			<th colspan="3">Variance</th>
		</tr>
		<tr class="thd">
			<th>(+/-)</th>
			<th>Price Variance </th>
			<th>Cost Variance</th>
		</tr>
	</thead>	
	
	<tbody>
		{foreach from=$table item=r}
			<tr style="background-color: #E0FFFF;">
				<td nowrap align=left>
					{$r.sku_item_code}
				</td>
				<td nowrap align=left>{$r.artno}</td>
				<td nowrap align=left>{$r.mcode}</td>
				<td nowrap align=left>{$r.link_code}</td>
				<td nowrap align=left>{$r.description}</td>
				<td nowrap align=right></td>
				<td nowrap align=right></td>
				{if !$smarty.request.group_by_sku}
				<td nowrap align=right></td>
				<td nowrap align=right></td>
				{/if}
				<td nowrap align=right></td>
				<td nowrap align=right></td>
				<td nowrap align=right></td>
				
				
				{foreach from=$r.stock_take item=r1}
					<tr>
						<td nowrap align=left></td>
						<td nowrap align=left>{$r1.date}</td>
						<td nowrap align=left>
							{if !$smarty.request.group_by_sku} {$r1.location} {/if}
						</td>
						<td nowrap align=left>
							{if !$smarty.request.group_by_sku} {$r1.shelf_no} {/if}
						</td>
						<td nowrap align=right></td>
						<td nowrap align=right>{$r1.stock_balance}</td>
						<td nowrap align=right>{$r1.qty}</td>
						{if !$smarty.request.group_by_sku}
						<td nowrap align=right>{$r1.selling_price|number_format:2}</td>
						<td nowrap align=right>{$r1.cost|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						<td nowrap align=right>{if $r1.variance > 0}+{/if}{$r1.variance|qty_nf}</td>
						<td nowrap align=right>{$r1.price_variance|number_format:2}</td>
						<td nowrap align=right>
							{$r1.total_cost|number_format:$config.global_cost_decimal_points}
						</td>
					</tr>
				{/foreach}				
			</tr>
		{/foreach}
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

