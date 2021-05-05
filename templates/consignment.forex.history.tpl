{if !$history}
- No History for this Currency Code - 
{else}
<table cellpadding="2" cellspacing="1" border="0" width="100%">
	<tr height="24" bgcolor="#ffee99">
		<th>Date/Time</th>
		<th>Exchange Rate</th>
		<th>User</th>
		<th width="16">&nbsp;</th>
	</tr>
	<tbody id="history_id" style="height:255px;overflow:auto;">
		{section name=i loop=$history}
			<tr {if $smarty.section.i.iteration > 3} class="hidden"{/if}>
				<td>{$history[i].added}</td>
				<td class="r">{$history[i].exchange_rate}</td>
				<td align="center">{$history[i].user|default:"<font color='green'>System</font>"}</td>
				<td>&nbsp;</td>
			</tr>
		{/section}
		{if count($history) > 3}
			<div id="show_result_id" class="bottom"><a href="javascript:void(show_more_result());">Show more results</a></div>
		{/if}
	</tbody>
</table>
{/if}