<div>Last Refreshed: {$smarty.now|date_format:"%Y-%m-%d %H:%M%p"}</div>
<table class="report_table" width="100%" id="all_counter_table">
<tr class="header">
	<th>Branch</th>
	<th>Total Sales</th>
	<th>Total Discount</th>
	<th>Total Transaction</th>
	<th>Counter</th>
	<th>Status</th>
	<th>Variances</th>
</tr>

{assign var=old_bid value=""}
{assign var=is_first value=1}
{foreach from=$counter item=r name=counter_count}
	{assign var=cid value=$r.id}
	{assign var=bid value=$r.branch_id}
	
	{if $old_bid neq "" && $old_bid neq $bid}
		{assign var=is_first value=0}
	{/if}
	
	{if $old_bid neq $bid && !$is_first}
		<tr  bgcolor="#ffcc66">
			<td class="r">Total</td>
			<td class="r">{$table.$old_bid.total.total_amount|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.$old_bid.total.total_disc_amt|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.$old_bid.total.total_transaction|number_format|ifzero:'-'}</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="r">{$table.$old_bid.total.variance|number_format:2|ifzero:'-'}</td>
		</tr>
		{assign var=is_first value=1}
	{/if}
	
	<tr id="b_{$bid}counter_{$cid}" title="{$r.network_name}" bgcolor="{if $r.need_highlight}#e1faae{else}{cycle values='#ffffff,#eeeeee'}{/if}" class="thover">
		<td>{$r.code}</td>
		<td class="r">{$table.$bid.$cid.total_amount|number_format:2|ifzero:'-'}{if $table.$bid.$cid.over}<br><small style="color:{if $table.$bid.$cid.over}#00f{else}#f00{/if};">Over: {$table.$bid.$cid.over}</small>{/if}</td>
		<td class="r">{$table.$bid.$cid.total_disc_amt|number_format:2|ifzero:'-'}</td>
		<td class="r">{$table.$bid.$cid.total_transaction|number_format|ifzero:'-'}</td>
		<td>{$r.network_name}</td>
		<td align="center">
			<span class="{if $r.status eq 'login'}status_login{elseif $r.status eq 'offline'}status_offline{/if}">
				{$r.status|default:'&nbsp;'}
			</span>
		</td>
		<td class="r">{$table.$bid.$cid.variance|number_format:2|ifzero:'-'}</td>
	</tr>
	
	{if $smarty.foreach.counter_count.iteration eq $counter|@count}
		<tr  bgcolor="#ffcc66">
			<td class="r">Total</td>
			<td class="r">{$table.$bid.total.total_amount|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.$bid.total.total_disc_amt|number_format:2|ifzero:'-'}</td>
			<td class="r">{$table.$bid.total.total_transaction|number_format|ifzero:'-'}</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="r">{$table.$bid.total.variance|number_format:2|ifzero:'-'}</td>
		</tr>
	{/if}
	{assign var=old_bid value=$bid}
{/foreach}
<tr bgcolor="#ff9900">
	<th class="r">Grand Total</th>
	<th class="r">{$table.grand_total.total_amount|number_format:2|ifzero:'-'}</th>
	<th class="r">{$table.grand_total.total_disc_amt|number_format:2|ifzero:'-'}</th>
	<th class="r">{$table.grand_total.total_transaction|number_format|ifzero:'-'}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th class="r">{$table.grand_total.variance|number_format:2|ifzero:'-'}</th>
</tr>
</table>
<p style="color: blue"> NOTE: "Variances" will only be available after counter collection is finalised</p>
