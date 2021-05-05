{*
3/29/2012 3:31:08 PM Andy
- Add Total Discount Amount column.

3/7/2017 2:41 PM Andy
- Added Last Refreshed Timestamp.

5/12/2017 8:21 AM Qiu Ying
- Enhanced to show all counter sales by branch
*}
<table>
	<tr>
		<td nowrap>Last Refreshed: {$smarty.now|date_format:"%Y-%m-%d %H:%M%p"}</td>
		<td nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:showMore();" title="Click here to view all counter sales by branch">More Details</a></td>
	</tr>
</table>

<table class="sorttable report_table" id="all_branchs_table">
<tr class="header hsort">
	<th onclick="sort_reloadTable('code','branch')">Branch {darrow col='code' grp='branch'}</th>
	<th onclick="sort_reloadTable('amount','branch')">Total Sales {darrow col='amount' grp='branch'}</th>
	<th onclick="sort_reloadTable('disc_amt','branch')">Total Discount {darrow col='disc_amt' grp='branch'}</th>
	<th onclick="sort_reloadTable('tran','branch')">Total Transaction {darrow col='tran' grp='branch'}</th>
</tr>

{foreach from=$branch_list item=r}
<tr id="branch_{$r.id}" title="{$r.code}" bgcolor="{cycle values='#ffffff,#eeeeee'}" class="thover clickable" onClick="show_context_menu(this,'{$r.id}')">
	{assign var=branch_id value=$r.id}
	<td>{$r.code}</td>
	<td class="r">{$all_branchs.$branch_id.amount|number_format:2|ifzero:'-'}</td>
	<td class="r">{$all_branchs.$branch_id.disc_amt|number_format:2|ifzero:'-'}</td>
	<td class="r">{$all_branchs.$branch_id.tran|number_format|ifzero:'-'}</td>
</tr>

{/foreach}
<tr class="header">
	<th>Total</th>
	<th class="r">{$all_branchs.total.amount|number_format:2|ifzero:'-'}</th>
	<th class="r">{$all_branchs.total.disc_amt|number_format:2|ifzero:'-'}</th>
	<th class="r">{$all_branchs.total.tran|number_format|ifzero:'-'}</th>
</tr>
</table>
