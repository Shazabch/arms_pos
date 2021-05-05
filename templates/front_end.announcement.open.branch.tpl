{*
12/02/2020 11:57 AM  Sheila
- Fixed table and button css
*}

{if $max_counter_by_branch_group >= $max_counter_per_row}
	{assign var=colspan value=$max_counter_per_row}
{else}
	{assign var=colspan value=$max_counter_by_branch_group}
{/if}
&nbsp;You may select multiple branches <br>
<div style="overflow-y:auto; margin: 10px;">
<table class="small branch_table" border="1" cellspacing="0" cellpadding="4">
<tr>
	<td><label><input type="checkbox" id="all_branches_pu" name="all_branches_pu" value="1" onclick="toggle_all_branches(this,true);" /> All</label></td>
	<td nowrap><label><input type="checkbox" id="cbx_all_counters_pu" name="cbx_all_counters_pu" value="1" onclick="toggle_all_counters(this,true);" /> All</label></td>
	<td colspan="{$colspan}">Counter Name</td>
</tr>
{foreach from=$branch_list_by_group item=branch_code key=bid}
<tr>
	{math equation=ceil(x/y) x=$branch_counter.$bid|@count y=$max_counter_per_row assign=rowspan}
	<td rowspan="{$rowspan}" valign=top nowrap>
	<label>
		<input type=checkbox name="announcement_branch_id[]" value="{$bid}" class="branch branch_cb_pu" data-bid="{$bid}" onclick="toggle_branch_counter_all(this,true);" {if is_array($form.announcement_branch_id) and in_array($branch[i].id,$form.announcement_branch_id)}checked{/if} />&nbsp;{$branch_code}
	</label>
	</td>
	<td rowspan="{$rowspan}" valign=top nowrap><label><input type="checkbox" class="all_counters_pu" onclick="toggle_all_branch_counters(this,true);" data-bid="{$bid}" value="{$bid}" /> All</label></td>
	{assign var=ct value=0}
	{foreach from=$branch_counter.$bid item=c}
		{assign var=ct value=$ct+1}
		{if $ct > $max_counter_per_row}
			</tr>
			<tr>
			{assign var=ct value=1}
		{/if}
		<td nowrap><label><input type="checkbox" class="counter_id branch_counter_pu" onclick="toggle_counter(this,true);" data-bid="{$bid}" data-cid="{$c.id}" value="{$c.id}" /> {$c.network_name}</label></td>
	{/foreach}
	{if $ct < $colspan}
		{section name=bc start=$ct loop=$colspan}
			<td style="background-color:#d7d7d7;">&nbsp;</td>
		{/section}
	{elseif $max_counter_by_branch_group == 0}
		<td style="background-color:#d7d7d7;">&nbsp;</td>
	{/if}
</tr>
{/foreach}
</table>
</div>
<p id=submitbtn2 align=center style="position: absolute;top: 88%;right: 42%;">
	{if !$view_mode}
	<input class="btn btn-primary" type=button value="Save" onclick="do_save_branches_and_counters({$save_branch_group_id});">
	{/if}

	<input class="btn btn-error" type=button value="Close" onclick="BRANCH_DIALOG.close();">
</p>