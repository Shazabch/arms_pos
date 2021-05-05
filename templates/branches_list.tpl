{include file='header.tpl' no_menu_templates=1}

<script>
{literal}
function toggle_branch_group(ele,bg_id){
	var status = 'open';
	if(ele.alt=='close')	status = 'close';
	
	if(status=='open'){
		ele.src = '/ui/collapse.gif';
		ele.title = 'Collapse Group';
		ele.alt = 'close';
		$('tbody_'+bg_id).show();
	}else{
		ele.src = '/ui/expand.gif';
		ele.title = 'Expand Group';
		ele.alt = 'open';
		$('tbody_'+bg_id).hide();
	}
}
{/literal}
</script>

{if $branches_group}
	<table border="1" width="100%">
	{foreach from=$branches_group key=bg_id item=bg}
		<tr style="background-color:#fe9;"><th colspan="2">{$bg_info.$bg_id.code} - {$bg_info.$bg_id.description}
		<img src="/ui/collapse.gif" onClick="toggle_branch_group(this,'{$bg_id}');" title="Collapse Group" alt="close" class="clickable" />
		</th></tr>
		<tbody id="tbody_{$bg_id}">
			{foreach from=$bg key=bid item=b}
				<tr {if !$branches_info.$bid.active}style="color:grey;"{/if}>
					<td>{$branches_info.$bid.code}</td>
					<td>{$branches_info.$bid.description}</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}
	</table>
{/if}

{if count($branches_info) > count($branches_have_group)}
	<br /><hr />
	<table border="1" width="100%">
		<tr style="background-color:#fe9;"><th colspan="2">Branches
		<img src="/ui/collapse.gif" onClick="toggle_branch_group(this,'0');" title="Collapse Group" alt="close" class="clickable" />
		</th></tr>
		<tbody id="tbody_0">
		{foreach from=$branches_info key=bid item=b}
			<tr {if !$b.active}style="color:grey;"{/if}>
				<td>{$b.code}</td>
				<td>{$b.description}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}
