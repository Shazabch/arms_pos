{*
*}
{config_load file=site.conf}
<div id="udiv" class="stdframe">
<input type="hidden" name="sa_id" value="{$form.sa_id}" />
<input type="hidden" name="year" value="{$form.year}" />
<input type="hidden" name="a" value="" />
<table border="0" cellpadding="4" cellspacing="1" width="100%">
	<tr>
		<th bgcolor="{#TB_COLHEADER#}">KPI</th>
		{foreach from=$mth_list key=mth item=mth_desc}
			<th bgcolor="{#TB_COLHEADER#}">{$mth_desc}</th>
		{/foreach}
	</tr>
	{assign var=allow_save_confirm value=0}
	{foreach from=$kpi_items_list item=kpi}
		{assign var=kpi_id value=$kpi.id}
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
			<th align="left">
				{$kpi.description}<br />
				<div>{$kpi.additional_description}</div>
				<div style="color:blue;">Max Scores: {$kpi.scores|number_format:2}</div>
				<input type="hidden" name="kpi_scores[{$kpi_id}]" value="{$kpi.scores}" />
				{if $err_list.$kpi_id}
					<div id="err">
						<div class="errmsg">
							<ul>
								{foreach from=$err_list.$kpi_id item=e}
									<li> {$e}</li>
								{/foreach}
							</ul>
						</div>
					</div>
				{/if}
			</th>
			{foreach from=$mth_list key=mth item=mth_desc}
				<input type="hidden" name="sa_kpi_month_status[{$mth}]" value="{$sa_kpi_data.$mth.status}" />
				{if $form.curr_mth && $form.curr_mth < $mth}
					<td>&nbsp;</td>
				{else}
					{if !$sa_kpi_data.$mth.status}
						<td align="center" nowrap>
							<input type="text" class="r" name="sa_kpi_data[{$mth}][{$kpi_id}]" size="5" value="{$sa_kpi_data.$mth.kpi_data.$kpi_id}" onchange="mf(this); SALES_AGENT_KPI_RATING_MODULE.scores_validate(this, {$kpi.scores});" />
						</td>
						{assign var=allow_save_confirm value=1}
					{else}
						<td class="r">
							{$sa_kpi_data.$mth.kpi_data.$kpi_id|default:0|number_format:2}
							<input type="hidden" name="sa_kpi_data[{$mth}][{$kpi_id}]" value="{$sa_kpi_data.$mth.kpi_data.$kpi_id}" />
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

{if $kpi_items_list && $allow_save_confirm}
	<br />
	<div align="center">
		<input type="button" value="Save" name="save_btn" onclick="SALES_AGENT_KPI_RATING_MODULE.update_kpi('save_sa_kpi');" />&nbsp;&nbsp;&nbsp;
		<input type="button" value="Confirm & Close" name="confirm_btn" onclick="SALES_AGENT_KPI_RATING_MODULE.update_kpi('confirm_sa_kpi');" />
	</div>
{/if}