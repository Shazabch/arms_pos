{*
10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each cashier

4/18/2017 11:33 AM Qiu Ying
- Enhanced to add a remark in Cashier Setup
*}
{config_load file="site.conf"}
{if $status eq "waiting approval"}
	<span style="color:blue">NOTE: Require user level "Branch Manager and above" to approve.</span><br />
{/if}
{if $BRANCH_CODE eq 'HQ' && $show_filter_branch}
	<b>Branch: </b>
	<select id="sel_filter_bid" onChange="list_sel(1);">
		<option value="">-- All --</option>
		{foreach from=$branches key=bid item=r}
			<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r.code}</option>
		{/foreach}
	</select>
{/if}
		
<table  border="0" cellpadding="4" cellspacing="1">
	<tr>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_CORNER#}" width="40">&nbsp;</th>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Username</th>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Login Name</th>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Full Name</th>
		{if $config.user_profile_need_ic}
			<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">IC No.</th>
		{/if}
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Email</th>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Discount Limit</th>
		<th {if $mprice_list}rowspan="2"{/if} bgcolor="{#TB_COLHEADER#}">Branch</th>
		<th {if !$mprice_list}style="display:none;"{/if}colspan="{$total_mprice}" bgcolor="{#TB_COLHEADER#}">Mprice List</th>
	</tr>
	<tr {if !$mprice_list}style="display:none;"{/if}>
		{foreach from=$mprice_list item=val}
			<th bgcolor="{#TB_COLHEADER#}">{$val}</th>
		{/foreach}
	</tr>
	{if !$users && !$tmp_users}
		<tr>
			<td colspan="6">No Data</td>
		</tr>
	{/if}
	{foreach from=$users key=uid item=user}
		{include file='front_end.cashier_setup.list.row.tpl'}
	{/foreach}
	{foreach from=$tmp_users key=uid item=user}
		{include file='front_end.cashier_setup.list.row.tpl' is_tmp=1}
	{/foreach}
</table>