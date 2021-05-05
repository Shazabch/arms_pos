{*
REVISION HISTORY
================
3/25/2008 3:45:38 PM gary
- lorry to vehicle

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

5/23/2011 12:10:59 PM Justin
- Modified the grand total amount to round by default 2 decimal points instead of follow config set.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

7/31/2012 4:28:14 PM Justin
- Enhanced to show branch code column when search result from HQ.

7/4/2013 2:38 PM Justin
- Modified to re-align the pagination to left instead of center.

7/2/2013 11:35 AM Justin
- Enhanced to show current approver for cancelled/terminated tab.

2/10/2014 5:24 PM Justin
- Bug fixed on loading wrong GRA items while access GRA that created from subbranch at HQ.

5/13/2014 6:03 PM Justin
- Enhanced saved & completed tabs to Show D/N No and D/N Amt by config.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/20/2015 4:04 PM Justin
- Enhanced the total amount of GRA always include amount from items not in ARMS.

4/29/2015 4:51 PM Justin
- Enhanced to pickup remark.

5/21/2015 9:12 AM Justin
- Bug fixed on gra amount has calculate wrongly.

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/7/2018 9:47 AM Justin
- Enhanced to have Rounding Adjust.

12/11/2018 3:30 PM Justin
- Bug fixed on Amount is incorrect while GRA list contains rounding adjust.

12/12/2018 10:44 AM Justin
- Changed the field title.

5/17/2019 3:42 PM William
- Enhance "GRA" word to use report_prefix.
*}

{if !$gra_list}
<p align=center>-- No Record --</p>
{else}
<div align="left">
{$pagination}
</div>
<table border=0 cellspacing=1 cellpadding=4 width=100%>
<tr bgcolor=#ffee99>
<th>&nbsp;</th>
<th>GRA</th>
{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
	<th>Branch</th>
{/if}
<th>Vendor Code</th>
{if $config.enable_vendor_account_id}
	<th>Account ID</th>
{/if}
<th>Vendor</th>
<th>Vehicle No</th>
<th>SKU Type</th>
{if $config.gra_show_dn_info}
<th>DN No</th>
<th>DN Amount</th>
{/if}
{if $got_rounding_adj}
	<th>Amount<br />Before Round</th>
	<th>Rounding<br />Adjust</th>
{/if}
<th>Amount</th>
<th>Added</th>
<th>Last Update</th>
{if $mode=='cancel'}
<th>Reason</th>
{/if}
</tr>

<tbody>
{section name=i loop=$gra_list}
<tr bgcolor="{cycle values="#eeeeee,"}">
    <td nowrap>
	<textarea id="remark_{$gra_list[i].id}_{$gra_list[i].branch_id}" style="display:none;">{$gra_list[i].remark2|escape:'html'}</textarea>
    {if $gra_list[i].status==0}
    {if $gra_list[i].returned==0 && !$gra_list[i].not_allow_checkout}
	<a href="?a=open&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}"><img src=ui/lorry_go.png border=0 title="Check Out"></a>
	<!--a href="javascript:void(do_print_checklist({$gra_list[i].id},{$gra_list[i].branch_id}))"><img src=ui/report_edit.png border=0 title="Print Checklist"></a-->
	{else}
	<a href="?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}"><img src=ui/view.png border=0 title="View"></a>
	{/if}
	{if $gra_list[i].returned}
		<a href="javascript:void(do_print({$gra_list[i].id},{$gra_list[i].branch_id}))"><img src=ui/print.png border=0 title="Print"></a>
		{if $gra_list[i].generate_arms_dn}
			<img src="ui/icons/page_add.png" border="0" title="Generate ARMS DN" onclick="toggle_dn_printing_menu('{$gra_list[i].id}', '{$gra_list[i].branch_id}');">
		{elseif $gra_list[i].print_arms_dn}
			<a href="?a=print_arms_dn&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}" target="_blank">  		
				<img src="ui/icons/page_copy.png" border="0" title="Print ARMS DN">
			</a>
		{/if}
	{/if}
	{else}
	<img src=ui/rejected.png border=0 title="Cancel">
	{/if}
	</td>
	<td>{$gra_list[i].report_prefix}{$gra_list[i].id|string_format:"%05d"}</td>
	{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
		<td>{$gra_list[i].branch_code}</td>
	{/if}
	<td>{$gra_list[i].vendor_code}</td>
	{if $config.enable_vendor_account_id}
		<td>{$gra_list[i].account_id}</td>
	{/if}
	<td>
		{$gra_list[i].vendor}
		{if preg_match('/\d/',$gra_list[i].approvals)}
			<div class=small>Approvals: <font color="#0000ff">{get_user_list list=$gra_list[i].approvals aorder_id=$gra_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	<td>{$gra_list[i].transport|upper}</td>
	<td align=center>{$gra_list[i].sku_type}</td>
	{if $config.gra_show_dn_info}
		<td align=center>{$gra_list[i].misc_info.dn_no|default:"-"}</td>
		<td align=right>
			{if $gra_list[i].misc_info.dn_amount}
				{if !$gra_list[i].currency_code}
					{$gra_list[i].misc_info.dn_amount|number_format:2}
				{else}
					{$gra_list[i].currency_code} {$gra_list[i].misc_info.dn_amount|number_format:2}
					<br />
					{assign var=base_grr_amount value=$gra_list[i].misc_info.dn_amount*$gra_list[i].currency_rate}
					{assign var=base_grr_amount value=$base_grr_amount|round2}
					<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
				{/if}
			{else}
				-
			{/if}
		</td>
	{/if}
	{if $got_rounding_adj}
		<td class="r">
			{$gra_list[i].amount-$gra_list[i].rounding_adjust|number_format:2}
		</td>
		<td class="r">
			{$gra_list[i].rounding_adjust|number_format:2}
		</td>
	{/if}
	<td align=right>
		{if !$gra_list[i].currency_code}
			{$gra_list[i].amount|number_format:2}
		{else}
			{$gra_list[i].currency_code} {$gra_list[i].amount|number_format:2}
			<br />
			{assign var=base_grr_amount value=$gra_list[i].amount*$gra_list[i].currency_rate}
			{assign var=base_grr_amount value=$base_grr_amount|round2}
			<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
		{/if}
	</td>
	<td align=center>{$gra_list[i].added}</td>
	<td align=center>{$gra_list[i].last_update}</td>
	{if $mode=='cancel'}
	<td align=center>{$gra_list[i].remark}</td>
	{/if}
</tr>
{/section}
</table>
{/if}
