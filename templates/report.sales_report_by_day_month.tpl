{*
5/14/2010 11:48:21 AM Andy
- Modified some words
- column width change to 15%


7/12/2010 10:26:04 AM Alex
- add privilege show cost

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5:06 PM 1/29/2014 Fithri
- add column mix match discount amt

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 11:31 AM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}
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
	background-color:#F2F2F2;
	color:red;
}
</style>
{/literal}

<script>
{literal}
function view_type_changed(){
	var view_type = getRadioValue(document.f_a['view_type']);
	if(view_type=='month')    $('span_month').hide();
	else    $('span_month').show();
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
<form method="post" class="form" name="f_a">
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>
<select name="branch_id">
     {foreach from=$branches key=bid item=b}
	 
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
	 
        {if !$branches_group.have_group.$bid and !$skip_this_branch}
	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
	    {/if}
	{/foreach}
	{foreach from=$branches_group.header key=bgid item=bg}
        <optgroup label="{$bg.code}">
            {foreach from=$branches_group.items.$bgid key=bid item=b}
			
				{if $config.sales_report_branches_exclude}
				{if in_array($b.code,$config.sales_report_branches_exclude)}
				{assign var=skip_this_branch value=1}
				{else}
				{assign var=skip_this_branch value=0}
				{/if}
				{/if}
			
				{if !$skip_this_branch}
                <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/if}
            {/foreach}
        </optgroup>
    {/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}


<b>Year</b>
<select name="year">
	{foreach from=$years item=r}
	    <option {if $smarty.request.year eq $r.year}selected {/if} value="{$r.year}">{$r.year}</option>
	{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<span id="span_month">
<b>Month</b>
<select name="month">
    {foreach from=$months key=m item=month}
        <option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
    {/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
</span>

<b>View By</b>
<input type="radio" name="view_type" value="day" {if !$smarty.request.view_type or $smarty.request.view_type eq 'day'}checked {/if} onChange="view_type_changed();" /> Day
<input type="radio" name="view_type" value="month" {if $smarty.request.view_type eq 'month'}checked {/if} onChange="view_type_changed();" /> Month
&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
&nbsp;&nbsp;
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
<br />
Note : 'Exclude inactive SKU' does not apply to Mix & Match Discount
</form>
<script>view_type_changed();</script>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>{$report_title}</h2>

<table class="report_table small_printing" width="100%">
	<tr class="header">
	    <th width="15%">Date</th>
	    <th width="13%">Sales</th>
		{if	$sessioninfo.privilege.SHOW_COST }
	    <th width="13%">Cost</th>
	    <th width="13%">Mix & Match Discount</th>
	    <th width="13%">GP</th>
	    <th width="13%">GP %</th>
		{/if}
	    <th width="7%">Total Transaction Count</th>
	    <th width="13%">AVG Sales Per Transaction</th>
	</tr>
    {foreach from=$date_label key=date_key item=d}
	    <tr class="{if $d.day==='0' or $d.day eq 6}weekend{/if}">
	        <td {if $smarty.request.view_type eq 'day'}align="center"{/if}>{$d.date}</td>
	        <td class="r">{$table.$date_key.selling|number_format:2}</td>
   			{if	$sessioninfo.privilege.SHOW_COST }
	        <td class="r">{$table.$date_key.cost|number_format:2}</td>
	        <td class="r">{$table.$date_key.mix_match|number_format:2}</td>
	        <td class="r">{$table.$date_key.gp|number_format:2}</td>
	        <td class="r">{$table.$date_key.gp_per|number_format:2}%</td>
			{/if}
	        <td class="r">{$table.$date_key.transaction_count|number_format}</td>
	        {if $table.$date_key.transaction_count}
	            {assign var=avg_tran value=$table.$date_key.selling/$table.$date_key.transaction_count}
			{else}
			    {assign var=avg_tran value=0}
	        {/if}
	        <td class="r">{$avg_tran|number_format:2}</td>
		</tr>
		
		{assign var=total_selling value=$total_selling+$table.$date_key.selling}
		{assign var=total_cost value=$total_cost+$table.$date_key.cost}
		{assign var=total_tran value=$total_tran+$table.$date_key.transaction_count}
		{assign var=total_mix_match value=$total_mix_match+$table.$date_key.mix_match}
	{/foreach}
	<tr class="header">
	    {assign var=total_gp value=$total_selling-$total_cost}
	    {if $total_selling}{assign var=total_gp_per value=$total_gp/$total_selling*100}{/if}
        {if $total_tran}
            {assign var=total_avg_tran value=$total_selling/$total_tran}
		{else}
		    {assign var=total_avg_tran value=0}
        {/if}
	        
	    <th class="r">Total</th>
	    <th class="r">{$total_selling|number_format:2}</th>
  		{if	$sessioninfo.privilege.SHOW_COST }
	    <th class="r">{$total_cost|number_format:2}</th>
	    <th class="r">{$total_mix_match|number_format:2}</th>
	    <th class="r">{$total_gp|number_format:2}</th>
	    <th class="r">{$total_gp_per|number_format:2}%</th>
		{/if}
	    <th class="r">{$total_tran|number_format}</th>
	    <th class="r">{$total_avg_tran|number_format:2}</th>
	</tr>
</table>
{/if}
{include file=footer.tpl}
