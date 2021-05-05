{*
10/22/2015 9:55 AM Andy
- Fix to only show edit icon for own branch CN.

5/16/2017 14:42 Qiu Ying
- Enhanced to return multiple invoice

7/3/2017 10:04 Qiu Ying
- Enhanced to set Customer Info to the left
*}

{if !$cnList}
	<p align="center"> &nbsp; * No Data Found *</p>
{else}
    {if $total_page >1}
		<div style="padding:2px;float:left;">
		Page
		<select onChange="CNOTE.page_change();" id="sel_cn_page">
			{section loop=$total_page name=s}
				<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
			{/section}
		</select>
		</div>
	{/if}
	
	 <table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
			<th width="100">CN No</th>
			<th width="100">CN Date</th>
			<th width="100">Invoice No</th>
			<th width="100">Invoice Date</th>
			<th>Customer Info</th>
			<th>Amount</th>
			<th>Qty</th>
			
			<th>Created By</th>
			<th>Last Update</th>
			<th>Adjustment Docs</th>
			<th>Return Type</th>
		</tr>
		
		{foreach from=$cnList item=cn}
			<tr bgcolor="{cycle values='#ffffff,#eeeeee'}">
				{assign var=url_edit value="cnote.php?a=open&branch_id=`$cn.branch_id`&id=`$cn.id`"}
				{assign var=url_view value="cnote.php?a=view&branch_id=`$cn.branch_id`&id=`$cn.id`"}
				
				<td align="center">
					{if $cn.active == 1 and ($cn.status == 0 or $cn.status == 2) and $cn.approved==0 and $cn.branch_id eq $sessioninfo.branch_id}
						<a href="{$url_edit}"><img src="ui/ed.png" title="Open / Edit" border="0" /></a>
					{else}
						<a href="{$url_view}"><img src="ui/view.png" title="View" border="0" /></a>
					{/if}
					
					{if $cn.active eq 1 and $cn.status eq 1 and $cn.approved eq 1}
						<a href="?a=print_cn&branch_id={$cn.branch_id}&id={$cn.id}" target="_blank"><img src="ui/print.png" title="Print CN" border="0" /></a>
					{/if}
				</td>
				<td>{$cn.cn_no}
					{if preg_match('/\d/',$cn.approvals)}
						<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$cn.approvals aorder_id=$cn.approval_order_id}</font></div>
					{/if}
				</td>
				<td align="center">{$cn.cn_date}</td>
				{if $cn.return_type eq "multiple_inv"}
					{assign var=cnote_id value=$cn.id}
					{assign var=itemCount value=$cnItemList.$cnote_id|@count}
					<td colspan="2">
						{foreach name=cnItems from=$cnItemList.$cnote_id item=cn_items}
							{$cn_items}{if $smarty.foreach.cnItems.iteration neq $itemCount},{/if}
						{/foreach}
					</td>
				{else}
					<td>{$cn.inv_no|default:'-'}</td>
					<td>{if $cn.inv_date != '0000-00-00'}{$cn.inv_date}{else}-{/if}</td>
				{/if}
				<td>{$cn.cust_name}{if $cn.cust_brn}({$cn.cust_brn}){/if}</td>
				<td align="right">{$cn.total_amount|number_format:2}</td>
				<td align="right">{$cn.total_qty|qty_nf}</td>
				<td align="center">{$cn.created_u|default:'-'}</td>
				<td align="center">{$cn.last_update|default:'-'}</td>
				
				<td>
					{if $cn.adj_id_list}
						{foreach from=$cn.adj_id_list item=adj_id name=fadj}
							{if !$smarty.foreach.fadj.first}, {/if}
							<a href="adjustment.php?a=view&branch_id={$cn.branch_id}&id={$adj_id}" target="_blank">ID#{$adj_id}</a>
						{/foreach}
					{else}
						&nbsp;
					{/if}
				</td>
				<td>{if $cn.return_type eq "multiple_inv"}Multiple Invoice{else}Single Invoice{/if}</td>
			</tr>
		{/foreach}
	</table>
{/if}