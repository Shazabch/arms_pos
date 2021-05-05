<h2>{$smarty.request.server}</h2>

{if !$compare_result}No Table to check
{else}
	<table width="100%" border="1">
		<tr>
			<th width="50">Table Name</th>
			<th>Result</th>
		</tr>
		{foreach from=$compare_result key=tbl_name item=result}
			<tr>
				<td>{$tbl_name}</td>
				<td>
					{if $result.ok} OK <!-- Everything OK -->
					{else}
						{if $result.error.msg}	<!-- Show Error Message -->
							<span class="tbl_err">{$result.error.msg}</span>
						{else}
							{if $result.error.col}	<!-- Got column error -->
								<table width="100%" border="1">
									<tr>
										<th>Column Nane</th>
										<th>Error Info</th>
										<th>Our Details</th>
										<th>Client Details</th>
									</tr>
								{foreach from=$result.error.col key=col_name item=wrong_info}
									<tr>
										<td><b>{$col_name}</b></td>
										<td><span class="tbl_err">{$wrong_info}</span></td>
										<td>
											{if $wrong_info eq 'No Column'}
												&nbsp;
											{else}
												{$result.own.$col_name.$wrong_info|default:'&nbsp;'}
											{/if}
										</td>
										<td>
											{if $wrong_info eq 'No Column'}
												&nbsp;
											{else}
												{$result.client.$col_name.$wrong_info|default:'&nbsp;'}
											{/if}
										</td>
									</tr>
								{/foreach}
								</table>
							{/if}
						{/if}
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
{/if}