{include file='header.tpl'}

<style>
{literal}
.error{
	color:red;
}
{/literal}
</style>

<h1>{$PAGE_TITLE}</h1>

{foreach from=$integration_list key=sync_type item=sub_type_list}
	<table class="report_table">
		<tr class="header">
			<th>Integration Type</th>
			<th>Sub Type</th>
			<th>Status</th>
			<th>Error</th>
			<th>Start Time</th>
			<th>Finish Time</th>
		</tr>

		
		{foreach from=$sub_type_list key=sub_type item=r}
			<tr>
				<td>{$sync_type}</td>
				<td>{$sub_type}</td>
				<td>
					{if $r.status eq 0}
						Processing
					{elseif $r.status eq 1}
						Done
					{elseif $r.status eq 2}
						Done with Error
					{/if}
				</td>
				<td>
					{if $r.error_list}
						<span class="error">
						{foreach from=$r.error_list item=e}
							{$e}<br />
						{/foreach}
						</span>
					{else}
						-
					{/if}
				</td>
				<td align="center">{$r.start_time}</td>
				<td align="center">{$r.end_time}</td>
			</tr>
		{/foreach}
	</table>
	<br />
{/foreach}

{include file='footer.tpl'}