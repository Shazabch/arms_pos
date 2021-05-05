{if count($rp_list) >= 50}<div align="right">Record shown in maximum 50 items</div>{/if}
<ul id="tab">
	<li onclick="RP_CONFIGURATION_MODULE.set_return_policy({$id}, '{$mt}', this);" title="-- None --" value="">-- None --</li>
	{if $type ne 1}
		<li onclick="RP_CONFIGURATION_MODULE.set_return_policy({$id}, '{$mt}', this);" title="Inherit (Follow {if $is_parent}Category{else}SKU{/if})" rp_id="inherit" rp_branch_id="">Inherit (Follow {if $is_parent}Category{else}SKU{/if})</li>
	{/if}
	{foreach from=$rp_list key=r item=rp name=rp_list}
		<li onclick="RP_CONFIGURATION_MODULE.set_return_policy({$id}, '{$mt}', this);" title="{$rp.branch_code} - {$rp.title}" rp_id="{$rp.id}" rp_branch_id="{$rp.branch_id}">{$rp.branch_code} - {$rp.title}</li>
	{/foreach}
</ul>