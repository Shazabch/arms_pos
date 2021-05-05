<tr id="tr_server-{$server_type}-{$server_key}" class="tr_server tr_server-{$server_type} {if $server_info.status eq 'err'}server_err{/if}" style="">
	<td nowrap>
		{if $server_type eq 'main'}
			{$server.code}
		{else}
			{$server.name}
			<sup>{$server.branch_code}</sup>
		{/if}
	</td>
	
	{* Status *}
	<td nowrap="" id="td_server_status-{$server_type}-{$server_key}">
		<span id="span_server_status_loading-{$server_type}-{$server_key}" style="display:none;">
			<img src="ui/clock.gif" align="absmiddle" /> Loading
			[<span class="link" onClick="SYNC_STATUS.stop_reload_server_clicked('{$server_type}', '{$server_key}');">Stop</span>]
		</span>
		<span id="span_server_status-{$server_type}-{$server_key}">
			{if $server_info}
				{if $server_info.status eq 'ok'}
					<img src="ui/approved.png" align="absmiddle" /> OK
				{elseif $server_info.status eq 'err'}
					<img src="/ui/icons/exclamation.png" align="absmiddle" /> Error
				{/if}
			{/if}
		</span>
		<span id="span_server_error-{$server_type}-{$server_key}" style="display:none;">
			<img src="ui/icons/exclamation.png" align="absmiddle" /> Error
		</span>
		<span id="span_reload_server-{$server_type}-{$server_key}" style="{if !$server_info}display:none;{/if}">
			[<span class="link" onClick="SYNC_STATUS.reload_server_clicked('{$server_type}', '{$server_key}');">Reload</span>]
		</span>
	</td>
	
	{if !$server_info}
		<td colspan="20"><span id="span_info-{$server_type}-{$server_key}">&nbsp;</span></td>
	{else}
		{* Master *}
		
		{* position / logbin *}
		<td align="center" nowrap>
			{if $server_info.master}
				{$server_info.master.Position|default:'-'} ({$server_info.master.File|default:'-'})
			{else}
				-
			{/if}
		</td>
		
		{* Slave *}
		{if $server_info.slave}
			{* Running *}
			<td align="center">
				{if $server_info.slave.running}
					Yes
				{else}
					{*<span class="critical">No</span>*}
					
					<ul>
						{foreach from=$server_info.slave.not_running_reason item=v}
							<li> {$v}</li>
						{/foreach}
					</ul>
				{/if}
			</td>
		
			{* Read position (logbin) *}
			<td align="center" nowrap>{$server_info.slave.Read_Master_Log_Pos|default:'-'} ({$server_info.slave.Master_Log_File|default:'-'})</td>
			
			{* Exec position (logbin) *}
			<td align="center" nowrap>{$server_info.slave.Exec_Master_Log_Pos|default:'-'} ({$server_info.slave.Relay_Master_Log_File|default:'-'})</td>
		{else}
			<td colspan="3" align="center">-</td>
		{/if}
		
		{* Info *}
		<td>
			<span id="span_info-{$server_type}-{$server_key}">
				{if $server_info.status eq 'err'}
					<span class="critical">
						{$server_info.slave.Last_Error|htmlentities}
					</span>
				{else}
					-
				{/if}
			</span>			
		</td>
	{/if}
</tr>