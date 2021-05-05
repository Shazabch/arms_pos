{*
06/25/2020 11:26 AM Sheila
- Updated button css
*}

<div class="stdframe">
	<h3>
		Branch: {$branch_list.$bid.code}
		&nbsp;&nbsp;&nbsp;&nbsp;
		Year: {$y}
	</h3>
	
	<form name="f_branch_shift">
		<input type="hidden" name="branch_id" value="{$bid}" />
		<input type="hidden" name="y" value="{$y}" />
	</form>
	
	<table width="100%" class="report_table" style="background-color: #fff;">
		<tr class="header">
			<th>&nbsp;</th>
			<th>Username</th>
			
			{foreach from=$appCore->monthsList key=m item=m_label}
				<th>{$m_label}</th>
			{/foreach}
		</tr>
		
		{foreach from=$data.user_list key=user_id item=user_data}
			<tr>
				<td>
					<a href="?a=open_shift_user&branch_id={$bid}&y={$y}&user_id={$user_id}">
						<img src="ui/ed.png" title="Edit" border="0" align="absmiddle" />
					</a>
				</td>
				<td>{$user_data.u}</td>
				
				{foreach from=$appCore->monthsList key=m item=m_label}
					<td nowrap align="center">
						{if $user_data.month_list.$m.shift_list}
							<div class="div_user_month" title="Edit {$m_label} {$y}" onClick="SHIFT_ASSIGNMENT.edit_user_month_clicked('{$user_id}', '{$m}');">
								<table class="tbl_shift_details">
									{foreach from=$user_data.month_list.$m.shift_list key=shift_id item=day_count}
										<tr>
											{if $shift_id}
												<td width="10">
													<div style="background-color: #{$shift_list.$shift_id.shift_color}" title="{$shift_list.$shift_id.description}">&nbsp;</div>
												</td>
												<td>
													{$shift_list.$shift_id.code}
												</td>
											{else}
												<td colspan="2" align="center" nowrap>
													[ Off ]
												</td>
											{/if}
											
											<td>: {$day_count}</td>
										</tr>							
									{/foreach}
								</table>
							</div>
						{else}
							<div class="div_user_month_empty btn btn-primary" title="Add {$m_label} {$y}" onClick="SHIFT_ASSIGNMENT.edit_user_month_clicked('{$user_id}', '{$m}');">
								<img src="/ui/add.png" class="img_add_shift"/>
							</div>
						{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
	
	<div>
		<hr />
		{include file='user_autocomplete.tpl' btn_add=1 btn_add_label="Add / Edit User for Year $y"}
	</div>
</div>

<script>
SHIFT_ASSIGNMENT.init_user_autocomplete();
</script>