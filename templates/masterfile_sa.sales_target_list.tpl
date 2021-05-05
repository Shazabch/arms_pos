{*
06/29/2020 11:23 AM Sheila
- Updated button css.

11/2/2020 9:40 AM Andy
- Reduce sales target input size from 10 to 8.
*}

<div id="udiv" class="stdframe">
{foreach from=$branches_list item=r}
	{assign var=bid value=$r.id}
	<h4>{$r.code} {if $r.description}- {$r.description}{/if}</h4>
	<table>
		<tr>
			<td>Year :</td>
			<td colspan="11">
				<select name="yr[{$bid}]" onchange="SALES_AGENT_MODULE.load_sales_target_month_value('{$sa_id}', '{$bid}', this);">
					{foreach from=$yrs key=r item=yr}
						<option value="{$yr}" {if $yr eq $curr_yr}selected{/if}>{$yr}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			{section name=mth start=0 loop=12}
				{assign var=mth_count value=$smarty.section.mth.iteration}
				<td>{$smarty.section.mth.iteration|str_month}</td>
				<td><input type="text" name="mth[{$bid}][{$mth_count}]" size="8" class="r" onchange="mf(this);" value="{$mth_value.$bid.$mth_count}"></td>
				{if $mth_count%6 eq 0}
					</tr><tr>
				{/if}
			{/section}
		</tr>
	</table>
	<br />
{foreachelse}
	<div align="center">- No branch Found -</div>
{/foreach}
	<div align="center">
		<button class="btn btn-success" id="st_save_btn" onclick="SALES_AGENT_MODULE.save_sales_target();">Save</button>
		<button class="btn btn-error" id="st_close_btn" onclick="SA_COMMISSION_MODULE.commission_table_fade('div_st_table');">Close</button>
	</div>
</div>