{*
11/16/2017 11:08 AM Justin
- Enhanced to have "IBT GRN" column.
*}

{foreach from=$table key=key item=d}
	{if $smarty.request.view_type eq 'date'}
		{assign var=date_from value=$d.description}
		{assign var=date_to value=$d.description}
	{else}
		{assign var=date_from value=$smarty.request.date_from}
		{assign var=date_to value=$smarty.request.date_to}
		{assign var=dtl_vendor_id value=$d.key_id}
	{/if}
	<tr bgcolor=#FFF380 class="dept_chid_{$dept_id}">
		 <td>{$d.description}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.po_amt|number_format:2|ifzero:'-'}</td>
		 <td class="r" bgcolor="#ffffdd">{if $print_excel == ''}{if $d.po_count != 0}<a href="/purchase_order.summary.php?a=show&department_id={$dept_id}&from={$date_from}&to={$date_to}&branch_id={$smarty.request.branch_id}&vendor_id={$smarty.request.vendor_id|default:$dtl_vendor_id}&use_grn={$smarty.request.use_grn}&user_id={$smarty.request.owner_id}" target="_blank">{$d.po_count|default:0|ifzero:'-'}</a>{else}-{/if}{else}{$d.po_count|default:0|ifzero:'-'}{/if}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.drv_po_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.drv_po_count|number_format:0|ifzero:'-'}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.undrv_po_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.undrv_po_count|number_format:0|ifzero:'-'}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.exp_po_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r' bgcolor="#ffffdd">{$d.exp_po_count|number_format:0|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_ibt_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_ibt_count|number_format:0|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_wpo_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_wpo_count|number_format:0|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_wopo_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r'>{$d.grn_wopo_count|default:0|ifzero:'-'}</td>
		 <td class='r'>{$d.ttl_purchase_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r'>{if $print_excel == ''}{if $d.ttl_purchase_count != 0}<a href="/goods_receiving_note.summary.php?a=show&department_id={$dept_id}&from={$date_from}&to={$date_to}&branch_id={$smarty.request.branch_id}&vendor_id={$smarty.request.vendor_id|default:$dtl_vendor_id}&use_grn={$smarty.request.use_grn}" target="_blank">{$d.ttl_purchase_count|number_format:0|ifzero:'-'}</a>{else}-{/if}{else}{$d.ttl_purchase_count|number_format:0|ifzero:'-'}{/if}</td>
		 <td class='r'>{$d.sales_amt|number_format:2|ifzero:'-'}</td>
		 <td class='r' {if round($d.ttl_var,2) < 0}style="color:red"{/if}>{$d.ttl_var|number_format:2|ifzero:'-'}</td>
		 <td class='r' {if round($d.ttl_perc_var,2) < 0}style="color:red"{/if}>{$d.ttl_perc_var|number_format:2|ifzero:'-':'%'}</td>
	 </tr>
{/foreach}
