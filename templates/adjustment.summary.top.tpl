<div class="noscreen">
<h4>{$p_branch.description}<br>{$title}</h4>
</div>

{if $adj}
<table border=0 cellspacing=1 cellpadding=4 style="padding:1px;border:1px solid #000">
<tr bgcolor="#ffee99">
	<th {if $sessioninfo.privilege.SHOW_COST}rowspan=2 {/if}>Department</th>
	<th {if $sessioninfo.privilege.SHOW_COST}colspan=2 {/if}>Total Adj In</th>
	<th {if $sessioninfo.privilege.SHOW_COST}colspan=2 {/if}>Total Adj Out</th>
	<th {if $sessioninfo.privilege.SHOW_COST}rowspan=2 {/if}>No. of Adj</th>
</tr>

{if $sessioninfo.privilege.SHOW_COST}<tr bgcolor="#ffee99"><th>Qty</th><th>Cost</th><th>Qty</th><th>Cost</th></tr>{/if}
{assign var=sum_total_in value=0}
{assign var=sum_total_out value=0}
{assign var=sum_no_adj value=0}

{foreach from=$adj item=i key=dt}
<tr bgcolor="{cycle values=",#eeeeee"}">
<td>
<a href="javascript:void(zoom_dept({$i.dept_id}))">{$i.dept}</a>
</td>
<td align=right>{$i.adj_in}</td>
{if $sessioninfo.privilege.SHOW_COST}<td align=right>{if $i.positif_cost}{$i.positif_cost}
{assign var=sum_cost_in value=$sum_cost_in+$i.positif_cost}
{/if}</td>{/if}
<td align=right>{$i.adj_out}</td>
{if $sessioninfo.privilege.SHOW_COST}<td align=right>{if $i.negatif_cost}{$i.negatif_cost}
{assign var=sum_cost_out value=$sum_cost_out+$i.negatif_cost}
{/if}</td>{/if}
<td align=right>{$i.no_adj}</td>
</tr>

{assign var=sum_total_in value=$sum_total_in+$i.adj_in}
{assign var=sum_total_out value=$sum_total_out+$i.adj_out}
{assign var=sum_no_adj value=$sum_no_adj+$i.no_adj}
{/foreach}

<tr bgcolor="#ffee99" align=right>
	<th>&nbsp;</th>
	<th>{$sum_total_in}</th>
	{if $sessioninfo.privilege.SHOW_COST}<th>{$sum_cost_in}</th>{/if}
	<th>{$sum_total_out}</th>
	{if $sessioninfo.privilege.SHOW_COST}<th>{$sum_cost_out}</th>{/if}
	<th>{$sum_no_adj}</th>
</tr>
</table>

{else}
** no data **
{/if}
