{*
*}

<div id="udiv" class="stdframe">

<table class="sortable"  id="return_policy_tbl" border="0" cellpadding="4" cellspacing="1" width="100%">
	<tr>
		<th bgcolor="{#TB_CORNER#}">&nbsp;</th>
		<th bgcolor="{#TB_COLHEADER#}">Title</th>
		<th bgcolor="{#TB_COLHEADER#}">Duration<br />Condition</th>
		<th bgcolor="{#TB_COLHEADER#}">Durations</th>
		<th bgcolor="{#TB_COLHEADER#}">Expiry<br />Durations</th>
		<th bgcolor="{#TB_COLHEADER#}">Charges<br />Condition</th>
		<th bgcolor="{#TB_COLHEADER#}">Charges</th>
		<th bgcolor="{#TB_COLHEADER#}">Receipt Remark</th>
	</tr>
	{foreach from=$rp_list key=r item=rp}
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" valign="top">
			<td bgcolor="{#TB_ROWHEADER#}" align="center" nowrap>
				<a onclick="RETURN_POLICY_MODULE.edit('{$rp.id}', '{$rp.branch_id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
				<a onclick="RETURN_POLICY_MODULE.activation('{$rp.id}', '{$rp.branch_id}', {if $rp.active}0);"><img src="ui/deact.png" title="Deactivate" border="0">{else}1);"><img src="ui/act.png" title="Activate" border="0">{/if}</a>
			</td>
			<td>{$rp.title}{if !$rp.active}<br /><span class="small">(inactive)</span>{/if}</td>
			<td>{if $rp.duration_condition eq 1}More Than{else}Every{/if}</td>
			<td>
				{foreach from=$rp.durations key=row item=di name=duration_list}	
					{assign var=n value=$smarty.foreach.duration_list.iteration-1}
					{if $di.durations}{$di.durations}{else}Beginning{/if} {if $di.type eq 1}Day{if $di.durations}(s){/if}{elseif $di.type eq 2}Week(s){else}Month(s){/if}, Return Rate: {$di.rate}
					{if !$smarty.foreach.duration_list.last}<br />{/if}
				{/foreach}
			</td>
			<td align="center">
				{if $rp.expiry_durations}
					{$rp.expiry_durations} {if $rp.expiry_type eq 1}Day(s){elseif $rp.expiry_type eq 2}Week(s){else}Month(s){/if}
				{/if}
			</td>
			<td>{if $rp.charges_condition eq 1}More Than{else}Every{/if}</td>
			<td>
				{if $rp.charges}
					{foreach from=$rp.charges key=row item=ci name=charges_list}	
						{assign var=n value=$smarty.foreach.charges_list.iteration-1}
						{if $ci.durations}{$ci.durations}{else}Beginning{/if} {if $ci.type eq 1}Day{if $ci.durations}(s){/if}{elseif $ci.type eq 2}Week(s){else}Month(s){/if}, Return Rate: {$ci.rate}
						{if !$smarty.foreach.charges_list.last}<br />{/if}
					{/foreach}
				{/if}
			</td>
			<td>{$rp.receipt_remark}</td>
		</tr>
	{foreachelse}
		<tr><td colspan="10" align="center">- No Data -</td></tr>
	{/foreach}
</table>
</div>

<script>
	ts_makeSortable($('return_policy_tbl'));
</script>