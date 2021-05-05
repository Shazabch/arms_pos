{*

1/13/2014 5:52 PM Fithri
- show db outdated (sync error), if any, for counter
- fix bug unset status 'Current Login User' when viewing from branch

3/2/2017 2:18 PM Andy
- Enhanced to combine counter collection error and counter error.

10/5/2017 9:53 AM Andy
- Enhanced to get counter sync error from pos_transaction_sync_server_counter_tracking.
- Added Sync Server Error.
*}

{if $counters_error}
	<h3>Counter Error</h3>
	<table class="report_table">
		<tr class="header">
			{if $BRANCH_CODE eq 'HQ' and !$no_branch_code}
				<th>Branch</th>
			{/if}
			<th>Counter</th>
			<th>Error</th>
			<th>Last Ping</th>
		</tr>
		{foreach from=$counters_error key=bid item=counter_list}
			{foreach from=$counter_list key=counter_id item=r}
				<tr>
					{if $BRANCH_CODE eq 'HQ' and !$no_branch_code}
						<td>{$r.info.branch_code}</td>
					{/if}
					<td>{$r.info.network_name}</td>
					<td>
						<ul style="color:red;">
							{foreach from=$r.error_list item=e}
								<li> {$e}</li>
							{/foreach}
						</ul>
					</td>
					<td>{$r.info.lastping}</td>
				</tr>
			{/foreach}
		{/foreach}
	</table>
{/if}

{if $ss_error}
	<h3>Sync Server Error</h3>
	<table class="report_table">
		<tr class="header">
			{if $BRANCH_CODE eq 'HQ' and !$no_branch_code}
				<th>Branch</th>
			{/if}
			<th>Error</th>
		</tr>
		{foreach from=$ss_error key=bid item=r}
			<tr>
				{if $BRANCH_CODE eq 'HQ' and !$no_branch_code}
					<td>{$r.info.branch_code}</td>
				{/if}
				<td>
					<ul style="color:red;">
						{foreach from=$r.error_list item=e}
							<li> {$e.time}: {$e.msg}</li>
						{/foreach}
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
