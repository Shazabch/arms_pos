{*
10/3/2011 11:45:37 AM Andy
- Add column "Current Login User".
- Add can unset "current login user".

11/18/2011 10:10:29 AM Andy
- Add checking for privilege "POS_FORCE_LOGOUT" to allow user to unset "Login User".

1/12/2012 10:13:43 AM Justin
- Added Prune Count column and sorting feature for it.

3/29/2012 3:31:16 PM Andy
- Add Total Discount Amount column.

9/18/2012 1:46 PM Justin
- Enhanced to highlight counter if the last ping for each counter has > 30 minutes.

1/13/2014 5:52 PM Fithri
- show db outdated (sync error), if any, for counter
- fix bug unset status 'Current Login User' when viewing from branch

3/2/2017 4:08 PM Andy
- Remove "Last Error" column in counter list.
- Rename column "Last User" to "Last Transaction User".
- Rename column "Current Login User" to "Login User".

9/24/2019 5:36 PM Andy
- Added column "Counter Version" and "OS".

12/29/2020 11:57 AM William
- Enhanced to add new "OS" type.
*}

{if $smarty.request.branch_id}
	{assign var=bid value=$smarty.request.branch_id}
{else}
	{assign var=bid value=$sessioninfo.branch_id}
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="mb-2">Last Refreshed: {$smarty.now|date_format:"%Y-%m-%d %H:%M%p"}</div>
<div class="table-responsive">
	<table class="sorttable report_table table mb-0 text-md-nowrap  table-hover" width="100%" id="all_counter_table">
		<thead class="bg-gray-100">
			<tr class="header hsort">
				<th onclick="sort_reloadTable('network_name','counter')">Counter {darrow col='network_name' grp='counter'}</th>
				<th onclick="sort_reloadTable('total_amount','counter')">Total Sales {darrow col='total_amount' grp='counter'}</th>
				<th onclick="sort_reloadTable('total_disc_amt','counter')">Total Discount {darrow col='total_disc_amt' grp='counter'}</th>
				<th onclick="sort_reloadTable('total_transaction','counter')">Total Transaction {darrow col='total_transaction' grp='counter'}</th>
				<th onclick="sort_reloadTable('user_u','counter')">Last Transaction User {darrow col='user_u' grp='counter'}</th>
				<th onclick="sort_reloadTable('user_cu','counter')">Login User {darrow col='user_cu' grp='counter'}</th>
				<th onclick="sort_reloadTable('cash_in_drawer','counter')">Cash in drawer {darrow col='cash_in_drawer' grp='counter'}</th>
				<th onclick="sort_reloadTable('last_tran','counter')">Last Transaction Time {darrow col='last_tran' grp='counter'}</th>
				<th onclick="sort_reloadTable('drawer_open_count','counter')">Drawer Open Count {darrow col='drawer_open_count' grp='counter'}</th>
				<th onclick="sort_reloadTable('cancelled_bill','counter')">Cancelled {darrow col='cancelled_bill' grp='counter'}</th>
				<th onclick="sort_reloadTable('prune_count','counter')">Prune Count {darrow col='prune_count' grp='counter'}</th>
				<th>Counter Version</th>
				<th>OS</th>
				<th>Last Ping</th>
				<th>Status</th>
				{*<th>Last Error</th>*}
			</tr>
		</thead>
		
		{foreach from=$counter item=r}
			{assign var=cid value=$r.id}
		<tbody class="fs-08">
			<tr id="counter_{$r.id}" title="{$r.network_name}" bgcolor="{if $r.need_highlight}#e1faae{else}{cycle values='#ffffff,#eeeeee'}{/if}" class="thover clickable" onClick="show_context_menu(this,'{$r.branch_id}','{$r.id}')">
				<td>{$r.network_name}</td>
				<td class="r">{$table.$cid.total_amount|number_format:2|ifzero:'-'}{if $table.$cid.over}<br><small style="color:{if $table.$cid.over}#00f{else}#f00{/if};">Over: {$table.$cid.over}</small>{/if}</td>
				<td class="r">{$table.$cid.total_disc_amt|number_format:2|ifzero:'-'}</td>
				<td class="r">{$table.$cid.total_transaction|number_format|ifzero:'-'}</td>
				<td class="r">{$table.$cid.user_u|default:'-'}</td>
				<td class="r" id="td_user_cu-{$bid}-{$cid}">{$r.user_cu|default:'-'} 
					{if $r.user_cu and $sessioninfo.privilege.POS_FORCE_LOGOUT}
						(<span class="small link" onClick="unset_login_status('{$bid}','{$cid}', event)">
							Unset Status
						</span>)
					{/if}
				</td>
				<td class="r">{$table.$cid.cash_in_drawer|number_format:2|ifzero:'-'}</td>
				<td class="r">{$table.$cid.last_tran|default:'-'}</td>
				<td class="r">{$table.$cid.drawer_open_count|number_format|ifzero:'-'}</td>
				<td class="r">{$table.$cid.cancelled_bill|number_format|ifzero:'-'}</td>
				<td class="r">{$table.$cid.prune_count|number_format|ifzero:'-'}</td>
				
				{* Version *}
				<td nowrap align="center">
					{if $r.revision}
						{$r.program_type} {$r.revision}
					{/if}
				</td>
				
				{* OS *}
				<td nowrap align="center">
					{if $r.os_type eq '(W)'}
						Windows
					{elseif $r.os_type eq '(L)'}
						Ubuntu
					{elseif $r.os_type eq '(A)'}
						Android
					{/if}
				</td>
				
				<td nowrap>{$r.lastping|default:'&nbsp;'}</td>
				<td align="center">
					<span class="{if $r.status eq 'login'}status_login{elseif $r.status eq 'offline'}status_offline{/if}">
						{$r.status|default:'&nbsp;'}
					</span>
				</td>
				{*<td>{$r.lasterr|default:'&nbsp;'}</td>*}
			</tr>
		</tbody>
		
		{/foreach}
		<tr class="header">
			<th>Total</th>
			<td class="r">{$table.total.total_amount|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.total.total_disc_amt|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.total.total_transaction|number_format|ifzero:'-'}</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="r">{$table.total.cash_in_drawer|number_format:2|ifzero:'-'}</td>
			<td>&nbsp;</td>
			<td class="r">{$table.total.drawer_open_count|number_format|ifzero:'-'}</td>
			<td class="r">{$table.total.cancelled_bill|number_format|ifzero:'-'}</td>
			<td class="r">{$table.total.prune_count|number_format|ifzero:'-'}</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{*<td>&nbsp;</td>*}
		</tr>
		</table>
</div>
	</div>
</div>

