{*
10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each cashier

4/18/2017 11:33 AM Qiu Ying
- Enhanced to add a remark in Cashier Setup
*}
<div class="card mx-3">
	<div class="card-body">
		{config_load file="site.conf"}
{if $status eq "waiting approval"}
	<div class="alert alert-primary rounded">
		<span><b>NOTE:</b> Require user level "Branch Manager and above" to approve.</span><br />
	</div>
{/if}
{if $BRANCH_CODE eq 'HQ' && $show_filter_branch}
	<b class="form-label">Branch: </b>
	<select class="form-control" id="sel_filter_bid" onChange="list_sel(1);">
		<option value="">-- All --</option>
		{foreach from=$branches key=bid item=r}
			<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r.code}</option>
		{/foreach}
	</select>
{/if}
		
<div class="table-responsive mt-3">
	<table class="table mb-0 text-md-nowrap  table-hover">
		<thead class="bg-gray-100" style="height: 25px;">
			<tr>
				<th {if $mprice_list}rowspan="2"{/if} width="40">&nbsp;</th>
				<th {if $mprice_list}rowspan="2"{/if} >Username</th>
				<th {if $mprice_list}rowspan="2"{/if} >Login Name</th>
				<th {if $mprice_list}rowspan="2"{/if} >Full Name</th>
				{if $config.user_profile_need_ic}
					<th {if $mprice_list}rowspan="2"{/if} >IC No.</th>
				{/if}
				<th {if $mprice_list}rowspan="2"{/if} >Email</th>
				<th {if $mprice_list}rowspan="2"{/if} >Discount Limit</th>
				<th {if $mprice_list}rowspan="2"{/if} >Branch</th>
				<th {if !$mprice_list}style="display:none;"{/if}colspan="{$total_mprice}" >Mprice List</th>
			</tr>
		</thead>
		<thead class="bg-gray-100">
			<tr {if !$mprice_list}style="display:none;"{/if}>
				{foreach from=$mprice_list item=val}
					<th >{$val}</th>
				{/foreach}
			</tr>
		</thead>
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
</div>
	</div>
</div>