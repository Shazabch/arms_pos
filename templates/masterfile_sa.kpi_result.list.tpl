{*
12/19/2019 1:39 PM Justin
- Bug fixed on report doesn't show anything while there's no data found.
*}
{config_load file=site.conf}
{if $sa_kpi_list}
	<input type="hidden" name="sa_id" value="{$form.sa_id}" />
	<input type="hidden" name="year" value="{$form.year}" />
	<input type="hidden" name="selected_sa_leader_id" value="" />
	<input type="hidden" name="a" value="save_sa_kpi" />
	{foreach from=$sa_kpi_list key=sal_id item=linfo}
		<input type="hidden" name="sa_leader_id[{$sal_id}]" value="{$sal_id}" />
		{assign var=allow_save value=0}
		{assign var=allow_reset value=0}
		<h3>Reviewed by {$linfo.leader_name} ({$linfo.leader_code})</h3>
		<div id="udiv" class="stdframe">
			<div class="card mx-3">
				<div class="card-body">
					<div class="table-responsive">
						<table class=" table mb-0 text-md-nowrap  table-hover" width="100%">
							<thead class="bg-gray-100">
								<tr>
									<th bgcolor="{#TB_COLHEADER#}">KPI</th>
									{foreach from=$mth_list key=mth item=mth_desc}
										<th bgcolor="{#TB_COLHEADER#}">{$mth_desc}</th>
									{/foreach}
								</tr>
							</thead>
							{foreach from=$kpi_items_list item=kpi}
								{assign var=kpi_id value=$kpi.id}
								<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
									<th align="left">
										{$kpi.description}<br />
										<div>{$kpi.additional_description}</div>
										<div style="color:blue;">Max Scores: {$kpi.scores|number_format:2}</div>
										<input type="hidden" name="kpi_scores[{$sal_id}][{$kpi_id}]" value="{$kpi.scores}" />
										{if $err_list.$sal_id.$kpi_id}
											<div id="err">
												<div class="errmsg">
													<ul>
														{foreach from=$err_list.$sal_id.$kpi_id item=e}
															<li> {$e}</li>
														{/foreach}
													</ul>
												</div>
											</div>
										{/if}
									</th>
									{foreach from=$mth_list key=mth item=mth_desc}
										<input type="hidden" name="sa_kpi_month_status[{$sal_id}][{$mth}]" value="{$sa_kpi_list.$sal_id.$mth.status}" />
										{if $form.curr_mth && $form.curr_mth < $mth}
											<td>&nbsp;</td>
										{else}
											{if !$sa_kpi_list.$sal_id.$mth.status}
												<td align="center" nowrap><input type="text" class="r" name="sa_kpi_data[{$sal_id}][{$mth}][{$kpi_id}]" size="5" value="{$sa_kpi_list.$sal_id.$mth.kpi_data.$kpi_id}" onchange="SALES_AGENT_KPI_RESULT_MODULE.scores_validate(this, {$kpi.scores});" ></b></td>
												{assign var=allow_save value=1}
											{else}
												<td class="r">
													{$sa_kpi_list.$sal_id.$mth.kpi_data.$kpi_id|default:0|number_format:2}
													<input type="hidden" name="sa_kpi_data[{$sal_id}][{$mth}][{$kpi_id}]" value="{$sa_kpi_list.$sal_id.$mth.kpi_data.$kpi_id}" />
													{assign var=allow_reset value=1}
												</td>
											{/if}
										{/if}
									{/foreach}
								</tr>
							{foreachelse}
								<tr><td colspan="13" align="center">- No Data -</td></tr>
							{/foreach}
						</table>
					</div>
				</div>
			</div>
		</div>
		{if $allow_save || $allow_reset}
			<br />
			<div align="right">
				{if $allow_reset}
					<input type="button" value="Reset" name="confirm_btn" onclick="SALES_AGENT_KPI_RESULT_MODULE.reset_sa_kpi({$sal_id}, {$form.sa_id}, {$form.year}, this);" />
				{/if}
				{if $allow_save}
					<input type="button" value="Save" name="save_btn" onclick="SALES_AGENT_KPI_RESULT_MODULE.update_sa_kpi({$sal_id});" />&nbsp;&nbsp;&nbsp;
				{/if}
			</div>
		{/if}
	{/foreach}
{else}
	<div align="center">-- No Data --</div>
{/if}