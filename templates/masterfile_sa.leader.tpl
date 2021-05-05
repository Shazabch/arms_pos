{assign var=sa_id value=$sa.id}

<div class="div_leader_container" id="div_leader_assign-{$sa_id}">
	<input type="hidden" name="leader_list[{$sa_id}]" value="{$sa_id}" />
	{$sa.code}
	<img src="ui/closewin.png" align="absmiddle" style="float:right;" class="clickable" title="Delete Leader" onClick="SALES_AGENT_MODULE.del_leader_assign('{$sa_id}')" />
</div>