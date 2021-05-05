{*
06/29/2020 11:23 AM Sheila
- Updated button css.
*}
{foreach from=$branch_list item=branch name=blist}
	{assign var=branch_id value=$branch.id}
	<table width="100%" border="0">
		<tr>
			<th width="10%" align="left">
				{$branch.code}:
				{if $branch.sac_id > 0}
					<div id="span_branch_sac_active_{$branch_id}" class="small" {if $branch.sac_active}style="display:none;"{/if}>(Inactive)</div>
				{/if}
				</th>
			<td width="90%">
				<input id="branch_commission_title_{$branch_id}" name="branch_commission_title[{$branch_id}]" size=50 onchange="uc(this);" value="{if $branch.sac_active}{$branch.sac_title}{else}-- None --{/if}" title="{$branch.sac_title}" readonly>
				<img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="SA_COMMISSION_MODULE.search_branch_commission({$branch_id}, '');"> 
				<button class="btn btn-primary" id="toggle_bsac_btn_{$branch_id}" onclick="SA_COMMISSION_MODULE.toggle_branch_commission_status({$branch_id}, this);"{if !$branch.sac_id}style="display:none;"{/if}>{if $branch.sac_active || !$branch.sac_id}Deactivate{else}Activate{/if}</button>
				<input type="hidden" id="branch_sac_{$branch_id}" name="branch_sac[{$branch_id}]" value="{$branch.sac_id}" />
				<input type="hidden" id="branch_prv_sac_{$branch_id}" name="branch_prv_sac[{$branch_id}]" value="{$branch.sac_id}" />
				<input type="hidden" id="branch_sac_active_{$branch_id}" name="branch_sac_active[{$branch_id}]" value="{$branch.sac_active}" />
				<input type="hidden" id="branch_sac_setting_{$branch_id}" name="branch_sac_setting[{$branch_id}]" value="{$branch.sac_setting_id}" />
			</td>
		</tr>
		<tr id="commission_list_{$branch_id}" style="display:none;">
			<td>&nbsp;</td>
			<td>
				<div id="div_branch_commission_{$branch_id}" style="border:1px solid #000; padding: 2px;"></div>
			</td>
		</tr>
	</table>
	{if !$smarty.foreach.blist.last}<br />{/if}
{foreachelse}
	<p>-- No Branches found --</p>
{/foreach}
<p align="center">
	<button class="btn btn-success" id="sac_save_btn">Save</button>
	<button class="btn btn-error" id="sac_close_btn">Close</button>
</p>

<script>
SA_COMMISSION_MODULE.initialize();
</script>