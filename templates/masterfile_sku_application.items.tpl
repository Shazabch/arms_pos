{if $items}
	{section name=i loop=$items}
		{assign var="item_n" value=`$smarty.section.i.iteration-1`}
		<div id="item[{$item_n}]" class="stdframe {if $smarty.section.i.last}active{/if}" style="margin-bottom:10px">

	<!--- show approval log --->
{if $form.approval_history_items}
	<h4>Application Status</h4>
	<div style="background-color:#fff; border:1px dashed #00f; padding:5px;">
	{foreach from=$form.approval_history_items item=aitem}
	<div>
	<font class="small" color=##006600>{$aitem.timestamp} by {$aitem.u}</font><br>
	{if $aitem.status == 1}
	<img src=ui/checked.gif vspace=2 align=absmiddle> <b>{$aitem.log[$item_n]}</b>
	{elseif $aitem.status == 2}
	{foreach from=$aitem.log[$item_n] item=log key=log_k}
	{if $log ne 'Others'}
	<img src=ui/deact.png vspace=2 align=absmiddle> {$log}<br>
	{/if}
	{/foreach}
	{else}
	<img src=ui/del.png vspace=2 align=absmiddle> <b>{$aitem.log[$item_n]}</b>
	{/if}
	</div>
	{/foreach}
	</div>
{/if}

		<input type=hidden name=subid[{$item_n}] value="{$items[$item_n].id|default:0}">
		{if $items[$item_n].item_type eq 'variety'}
		{include file=masterfile_sku_application.atom_variety.tpl}
		{else}
		{include file=masterfile_sku_application.atom_matrix.tpl}
		{/if}
		</div>
	{/section}
	<script>
	total_item = {$smarty.section.i.total-1};
	</script>
{elseif $item_type}
<div id="item[{$item_n|default:0}]" class="stdframe {if $smarty.section.i.last}active{/if}" style="margin-bottom:10px;display:none">
{if $item_type eq 'variety'}
{include file=masterfile_sku_application.atom_variety.tpl}
{else}
{include file=masterfile_sku_application.atom_matrix.tpl}
{/if}
</div>
{/if}
