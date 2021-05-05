{*
1/15/2010 4:28:53 PM Andy
- change approvers layout

4/1/2010 5:10:57 PM Andy
- Fix approval flow cannot show SKU Type when select INVOICE flow type

7/16/2013 4:49 PM Andy
- Enhance the edit approval to have selection of send PM/Email/SMS, and also minimum document amount.
- Change the search user to search by autocomplete.

1/11/2017 10:16 AM Andy
- Fixed sku type cannot show.
*}

{config_load file=site.conf}
{if $smarty.request.a ne 'ajax_reload_table'}
<div id="udiv" class="stdframe">
{/if}
<table  border=0 cellpadding=4 cellspacing=1>
<tr>
<th bgcolor="{#TB_CORNER#}" width=40>&nbsp;</th>
<th bgcolor="{#TB_COLHEADER#}">Branch</th>
<th bgcolor="{#TB_COLHEADER#}">Type</th>
<th bgcolor="{#TB_COLHEADER#}">Department</th>
<th bgcolor="{#TB_COLHEADER#}">SKU Type</th>
<th bgcolor="{#TB_COLHEADER#}">Order</th>
<th bgcolor="{#TB_COLHEADER#}">Approvers</th>
<th bgcolor="{#TB_COLHEADER#}">Notify Users</th>
</tr>
{* section name=i loop=$flows}
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td bgcolor="{#TB_ROWHEADER#}" nowrap>
			<a href="javascript:void(ed({$flows[i].id}))"><img src=ui/ed.png title="Edit" border=0></a>
			<a href="javascript:void(act({$flows[i].id},{if $flows[i].active}0))"><img src="ui/deact.png" title="Deactivate" border="0" />{else}1))"><img src="ui/act.png" title="Activate" border="0" />{/if}</a>
		</td>
		<td><b>{$flows[i].branch_code}</b>{if !$flows[i].active}<br><span class=small>(inactive)</span>{/if}</td>
		<td>{$flows[i].type}</td>
		<td>{$flows[i].dept}</td>
		<td>{if $flows[i].type eq 'SKU_APPLICATION' or $flows[i].type eq 'INVOICE'}{$flows[i].sku_type}{else}&nbsp;{/if}</td>
		<td>
		  {if $flows[i].aorder == 0}No Order{else}
		    {assign var=aorder_id value=$flows[i].aorder}
		    {$aorder.$aorder_id.description}
		  {/if}
		</td>
		<td>{if $aorder_id eq 4}-{else}{$flows[i].approvals}{/if}</td>
		<td>{$flows[i].notify_users}</td>
	</tr>
{/section *}

	{foreach from=$flows item=r}
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
			<td bgcolor="{#TB_ROWHEADER#}" nowrap>
				<a href="javascript:void(APPROVAL_FLOW.open('{$r.id}'))"><img src="ui/ed.png" title="Edit" border="0" /></a>
				<a href="javascript:void(act({$r.id},{if $r.active}0))"><img src="ui/deact.png" title="Deactivate" border="0" />{else}1))"><img src="ui/act.png" title="Activate" border="0" />{/if}</a>
				{*<a href="javascript:void(ed({$r.id}))"><img src=ui/ed.png title="Edit" border=0></a>*}
			</td>
			<td><b>{$r.branch_code}</b>{if !$r.active}<br><span class=small>(inactive)</span>{/if}</td>
			<td>{if $flow_desc[$r.type]}{$flow_desc[$r.type]}{else}{$r.type}{/if}</td>
			<td>{$r.dept}</td>
			<td>{$r.sku_type|default:'&nbsp;'}</td>
			<td>
			  {if $r.aorder == 0}No Order{else}
			    {assign var=aorder_id value=$r.aorder}
			    {$aorder.$aorder_id.description}
			  {/if}
			</td>
			<td>{if $aorder_id eq 4}-{else}{$r.approvals}{/if}</td>
			<td>{$r.notify_users}</td>
		</tr>
	{/foreach}
</table>
{if $smarty.request.a ne 'ajax_reload_table'}
</div>

<script>
parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
</script>
{/if}
