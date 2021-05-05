{*
4/3/2014 1:47 PM Andy
- Change "Dis-connected" to "Disconnected".
*}

<table width="100%" class="report_table" id="tbl_counter_status">
		<thead>
			<tr class="header">
				<th width="100">Counter Name</th>
				<th width="100">Status</th>
				<th width="100">IP</th>
				<th width="50">Revision</th>
				<th width="100">Cashier</th>
				<th>Last Ping</th>
				<th>Info</th>
			</tr>
		</thead>
		
		<tbody id="tbody_counter_status">
			{foreach from=$branch_list key=bid item=b}
				{if $b.counter_list}
					<tr class="header">
						<th colspan="7" align="left">{$b.code} 
							({$b.active_count} active)
						
							{if $b.err_count}
								({$b.err_count} error)
							{/if}
							
							{if $b.in_use_count}
								({$b.in_use_count} in use)
							{/if}
							
						</th>
					</tr>
					
					{foreach from=$b.counter_list key=cid item=c}						
						<tr class="{if $c.syncstatus eq 'sync_error'}server_err{/if}">
							<td>{$c.network_name}</td>
							
							{* Status *}
							<td nowrap>
								{if $c.syncstatus eq 'nvr_online'}
									<img src="/ui/icons/error.png" align="absmiddle" /> Never Ping
								{elseif $c.syncstatus eq 'ping_error'}
									<img src="/ui/icons/exclamation.png" align="absmiddle" /> Ping Error
								{elseif $c.syncstatus eq 'connected'}
									<img src="/ui/approved.png" align="absmiddle" /> Connected
								{elseif $c.syncstatus eq 'disconnected'}
									<img src="/ui/icons/disconnect.png" align="absmiddle" /> Disconnected
								{elseif $c.syncstatus eq 'sync_error'}
									<img src="/ui/icons/exclamation.png" align="absmiddle" /> Error
								{/if}
							</td>
							
							{* IP *}
							<td>{$c.info.ip|default:'&nbsp;'}</td>
							
							{* Revision *}
							<td align="right">{$c.info.revision|default:'&nbsp;'}</td>
							
							{* Cashier *}
							<td align="center">{$c.info.u|default:'&nbsp;'}</td>
							
							{* Last Ping *}
							<td align="center">
								{if $c.info.lastping}
									<span style="color:blue;">
										{if $c.info.total_sec>59}
											{if $c.info.lastping_duration.day}
												{$c.info.lastping_duration.day} d
											{/if}
											{if $c.info.lastping_duration.hour}
												{$c.info.lastping_duration.hour} hr
											{/if}
											{if $c.info.lastping_duration.min}
												{$c.info.lastping_duration.min} min
											{/if}
											ago
										{else}
											&lt; 1 min
										{/if}
									</span>
									
									
										
									
									({$c.info.lastping|default:'&nbsp;'})
								{else}
									-
								{/if}
							</td>
							
							{* Info *}
							<td>
								{if $c.info.lasterr}
									- {$c.info.lasterr}<br />
								{/if}
								{if $c.info.sync_error}
									- {$c.info.sync_error}<br />
								{/if}
								{if $c.err_list}
									{foreach from=$c.err_list item=e}
										- {$e}<br />
									{/foreach}
								{/if}
								&nbsp;
							</td>
						</tr>
					{/foreach}
				{/if}
			{/foreach}
		</tbody>
		
	</table>