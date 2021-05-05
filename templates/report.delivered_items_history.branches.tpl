{*
5/30/2011 10:01:02 AM Alex
- create by me

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

7/11/2014 11:53 AM Justin
- Bug fixed on report show zero data while in sub branch.
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
#branch_id option.bg{
	font-weight: bold;
	padding-left: 10px;
}

#branch_id option.bg_items{
	padding-left: 20px;
}

</style>

{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}

<form name="f_a" method=post class="form">
	<p>
		{if $BRANCH_CODE eq 'HQ'}
			<b>Deliver To</b>
			<select name="branch_id" id="branch_id">
				{foreach from=$branches key=bid item=b}
				    {if !$branch_group.have_group.$bid}
				    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				    {/if}
				{/foreach}
				{if $branches_group.header}
					<optgroup label="Branch Group">
						{foreach from=$branch_group.header key=bgid item=bg}
							<option class="bg" value="-{$bgid}" {if $smarty.request.branch_id eq "-$bgid"}selected {/if}>{$bg.code}</option>
							{foreach from=$branch_group.items.$bgid key=bid item=b}
								<option class="bg_items" value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
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
			</select>&nbsp;&nbsp;
		{else}
			<input type="hidden" name="branch_id" id="branch_id" value="{$sessioninfo.branch_id}" />
		{/if}
		
		<b>Date From</b>
		<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
	
		<b>To</b>
		<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
	</p>
	<p>
		<button name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
</form>

{/if}

<h2>{$report_title}</h2>

{if $detail}

<table class="report_table" id="report_tbl">
	<tr class=header>
		<th>Article No</th>
		{if $config.ci_use_split_artno}
		<th>Size</th>
		{/if}
		<th>ARMS Code</th>
		<th colspan=2>Description</th>
		{foreach from=$detail key=bid item=d}
			<th>{$bid}</th>
		{/foreach}
		<th>Total</th>
	</tr>
	
	{foreach from=$sku_items key=sid item=desc}
	<tr>
		<td>{$desc.artno}
		{if $config.ci_use_split_artno}
			</td><td>
		{/if}	
			{$desc.size}
		</td>
		<td>{$desc.sku_item_code}</td>
		<td>{$desc.description}</td>
		<th>Qty</th>
		{foreach from=$detail key=bid item=d}
		    <td class="r">{$detail.$bid.$sid.total_pcs}</td>
		{/foreach}
		<td class="r">{$total.sku_items.$sid.total_pcs}</td>
	</tr>
	{/foreach}
	<tr class=header>
		<td	colspan="{if $config.ci_use_split_artno}4{else}3{/if}"  class="r"><b>Total</b></td>
		<th>Qty</th>
		{foreach from=$detail key=bid item=d}
		    <td class="r">{$total.branch.$bid.total_pcs}</td>
		{/foreach}
		<td class="r">{$total.total.total_pcs}</td>
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
