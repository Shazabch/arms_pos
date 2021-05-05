{*
*}

{$pagination}
<table class=sortable id=do_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>DO No.</th>
	<th>Deliver To</th>
	<th>DO Date</th>
	<th>Added Date</th>
	<th>Last Update</th>
</tr>

{foreach from=$do_list name=i item=r}
<tr bgcolor={cycle values=",#eeeeee"}>
	<td align="center">
		{if $r.active && !$r.status}
			{if $sessioninfo.branch_id eq $r.branch_id || $sessioninfo.branch_id eq $r.do_branch_id || $config.consignment_modules}
				{assign var=action value="open"}
				{assign var=icon_type value="ed"}
			{else}
				{assign var=action value="view"}
				{assign var=icon_type value="view"}
			{/if}
			<a href="do.simple.php?a={$action}&id={$r.id}&branch_id={$r.branch_id}&do_type={$r.do_type}">
				<img src="ui/{$icon_type}.png" title="{$action} this DO" border="0">
			</a>
		{else}
			<a href="do.simple.php?a=view&id={$r.id}&branch_id={$r.branch_id}&do_type={$r.do_type}">
				<img src="ui/cancel.png" title="Open this DO" border="0">
			</a>
		{/if}
	</td>
	<td>
		{if $r.do_no}
			{$r.do_no}
			<br>
			<font class="small" color=#009900>
				{$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
			</font>
		{else}
			{$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
		{/if}
	</td>
	<td>
		{if $r.do_type eq 'credit_sales'}
			{assign var=debtor_id value=$r.debtor_id}
			Debtor: {$debtor.$debtor_id.code}
			{if $debtor.$debtor_id.description}
				<br />
				<span class="small" style="color:blue;">({$debtor.$debtor_id.description})</span>
			{/if}
		{else}
			{if $r.do_branch_id}
				{$r.branch_name_2}
			{elseif $r.open_info.name}
				{$r.open_info.name}
			{/if}
			{foreach from=$r.d_branch.name item=pn name=pn}
				{if $smarty.foreach.pn.iteration>1} ,{/if}
				{$pn}
			{/foreach}
		{/if}
	</td>
	<td align="center">{$r.do_date|date_format:"%d-%m-%Y"}</td>
	<td align="center">{$r.added}</td>
	<td align="center">{$r.last_update}</td>
</tr>
{foreachelse}
<tr>
	<td colspan="6" align="center">- no record -</td>
</tr>
{/foreach}
</table>
<script>
ts_makeSortable($('do_tbl'));
</script>
