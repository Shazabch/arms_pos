{*
REVISION HISTORY
++++++++++++++++
9/27/2007 12:38:36 PM gary
- added mark-on column.

10/1/2007 5:00:53 PM gary
-add show details when printing.

3/15/2012 5:56:07 PM Andy
- Fix branch code does not show when branch print.

6/26/2012 4:42 PM Andy
- Change "No. of GRN" to "No. of Items".

7/24/2012 10:35 AM Andy
- Add print and export excel function.

11/18/2014 4:42 PM Justin
- Enhanced to have GST Info.

4/25/2018 10:50 AM Justin
- Enhanced to show foreign currency.

9/7/2018 4:42 PM Justin
- Enhanced to load GST information base on cost instead of selling price.
*}

<div class="noscreen">
<h3>
Branch : 
{if $BRANCH_CODE eq 'HQ'}
	{if $smarty.request.branch_id}
		{section name=i loop=$branch}
			{if $smarty.request.branch_id eq $branch[i].id}{$branch[i].code}{/if}
		{/section}
	{else}
		All
	{/if}
{else}
	{$BRANCH_CODE}
{/if}

&nbsp;&nbsp;&nbsp;
GRN Date : {$smarty.request.from} - {$smarty.request.to}
</h3>
</div>
<br />

{if $grn}
<table border=0 cellspacing=1 cellpadding=4 style="padding:1px;border:1px solid #000">
<tr bgcolor="#ffee99">
	<th {if $fc_list}rowspan="2"{/if}>Department</th>
	<th {if $fc_list}rowspan="2"{/if}>Total Selling ({$config.arms_currency.symbol})</th>
	{if $sessioninfo.show_cost}
		 {if $fc_list}
			{assign var=fc_colspan value=$fc_list|@count}
			<th colspan="{$fc_colspan}">Foreign Amount</th>
		 {/if}
		<th {if $fc_list}rowspan="2"{/if}>GRN Amount ({$config.arms_currency.symbol})</th>
		{if $is_under_gst}
			<th {if $fc_list}rowspan="2"{/if}>GST ({$config.arms_currency.symbol})</th>
			<th {if $fc_list}rowspan="2"{/if}>GRN Amount<br />Include GST ({$config.arms_currency.symbol})</th>
		{/if}
	{/if}
	{if $sessioninfo.show_report_gp}
		<th {if $fc_list}rowspan="2"{/if}>GP (%)</th>
	{/if}
	<th {if $fc_list}rowspan="2"{/if}>No. of Items</th>
</tr>

	{if $sessioninfo.show_cost && $fc_list}
		<tr bgcolor="#ffee99">
			{foreach from=$fc_list key=curr_code item=code}
				<th>{$code}</th>
			{/foreach}
		</tr>
	{/if}

{assign var=total0 value=0}
{assign var=total1 value=0}
{assign var=total2 value=0}

{foreach from=$grn key=dept_id item=r}
<tr bgcolor="{cycle values='#fffff,#eeeeee'}">
<td>{if !$no_header_footer}<a href="javascript:void(zoom_dept({$dept_id}))">{$r.dept}</a>
	{else}
		{$r.dept}
	{/if}

</td>
<td align=right>{$r.total_selling|number_format:2}</td>
{if $sessioninfo.show_cost}
	{if $fc_list}
		{foreach from=$fc_list key=curr_code item=code}
			<td align="right">{$r.foreign_amt.$code|number_format:2|default:'-'}</td>
		{/foreach}
	{/if}
	<td align=right {if $curr_code_dept_list.$dept_id}class="converted_base_amt"{/if}>{$r.final_amount|number_format:2}{if $curr_code_dept_list.$dept_id}*{/if}</td>
	{if $is_under_gst}
		<td align=right>{$r.total_gst|number_format:2}</td>
		<td align=right {if $curr_code_dept_list.$dept_id}class="converted_base_amt"{/if}>{$r.total_gst_amount|number_format:2}{if $curr_code_dept_list.$dept_id}*{/if}</td>
	{/if}
{/if}
{if $sessioninfo.show_report_gp}
	{if $r.total_selling>0}
		{assign var=m value=$r.total_selling-$r.final_amount}
		{assign var=m value=$m/$r.total_selling*100}
	{/if}
	<td align=right>{$m|number_format:2}</td>
{/if}
<td align=right>{$r.cnt}</td>
</tr>
{/foreach}

<tr bgcolor="#ffee99">
<td><b>Total</b></th>
<td align=right>{$total.total_selling|number_format:2}</td>
{if $sessioninfo.show_cost}
	{if $fc_list}
		{foreach from=$fc_list key=curr_code item=code}
			<td align="right">{$total.foreign_amt.$code|number_format:2|default:'-'}</td>
		{/foreach}
	{/if}
	<td align=right {if $fc_list}class="converted_base_amt"{/if}>{$total.final_amount|number_format:2}{if $fc_list}*{/if}</td>
	{if $is_under_gst}
		<td align=right>{$total.total_gst|number_format:2}</td>
		<td align=right {if $fc_list}class="converted_base_amt"{/if}>{$total.total_gst_amount|number_format:2}{if $fc_list}*{/if}</td>
	{/if}
{/if}
{if $sessioninfo.show_report_gp}
	{if $total.total_selling>0}
		{assign var=t value=$total.total_selling-$total.final_amount}
		{assign var=t value=$t/$total.total_selling*100}
	{/if}
	<td align=right>{$t|number_format:2}</td>
{/if}
<td align=right>{$total.cnt}</td>
</tr>
</table>
{else}
** no data **
{/if}

