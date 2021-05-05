{include file='header.tpl'}

<h1>{$PAGE_TITLE} Modification History</h1>

<h3>{$report_title}</h3>

{if !$data}
	* No Modification History
{else}
	<table width="100%" class="report_table">
		<tr class="header">
			<th>Modified Timestamp</th>
			<th>Modified By User</th>
			<th>Attendance Date</th>
			{*<th>Old Shift Data</th>
			<th>New Shift Data</th>*}
			<th>Scan Records Modification</th>
		</tr>
		
		{foreach from=$data key=mguid item=m_data}
			<tr>
				<td align="center">{$m_data.added}</td>
				<td align="center">{$m_data.edit_by_user_u}</td>
				<td align="center">{$m_data.date}</td>
				
				{* Old Shift Data *}
				{*<td>
					{if $m_data.odata}
						<ul>
							{foreach from=$m_data.odata key=k item=v}
								<li> {$k} = {$v}</li>							
							{/foreach}
						<ul>
					{else}
						-
					{/if}
				</td>*}
				
				{* New Shift Data *}
				{*<td>
					{if $m_data.ndata}
						<ul>
							{foreach from=$m_data.ndata key=k item=v}
								<li> {$k} = {$v}</li>							
							{/foreach}
						<ul>
					{else}
						-
					{/if}
				</td>*}
				
				{* Scan Records Modification *}
				<td nowrap>
					<ul>
						{foreach from=$m_data.changed_items item=m_item}
							<li>
								{$m_item.oscan_time|ifzero:'N/A'}
								{if $m_item.oip}
									<i class="small">({$m_item.oip})</i>
								{/if}
								
								<span style="color: blue;font-weight: bold;">=></span>
								
								{$m_item.nscan_time|ifzero:'N/A'}
								{if $m_item.nip}
									<i class="small">({$m_item.nip})</i>
								{/if}
							</li>
						{/foreach}
					<ul>
				</td>
			</tr>
		{/foreach}
	</table>

{/if}

{include file='footer.tpl'}