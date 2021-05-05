{*
REVISION HISTORY
+++++++++++++++
9/20/2007 10:52:51 AM gary
- get the all splited branch po_no for approved hq po tab.

10/2/2007 11:52:59 AM gary
- level>=9999 only can change PO owner

11/1/2007 5:30:35 PM gary
- add print dialog to print distribution list.
*}

{$pagination}
<table class=sortable id=po_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>PO No.</th>
	<th>Branch</th>
	<th>Vendor</th>
	<th>Department</th>
	<th>Amount</th>
	<th>Last Update</th>
	<th>Print</th>
</tr>
{section name=i loop=$po_list}
<tr bgcolor={cycle values=",#eeeeee"}>
	<td nowrap>
		{if !$po_list[i].status}
		    {if $po_list[i].branch_id!=$sessioninfo.branch_id}
				<a href="purchase_order.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}"><img src="ui/approved.png" title="Open this PO" border=0></a>
				{if $sessioninfo.level>=9999 || $sessioninfo.id==$po_list[i].user_id}
				<a href="javascript:void(po_chown({$po_list[i].id},{$po_list[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
				{/if}
			{else}
				<a href="purchase_order.php?a=open&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}"><img src="ui/ed.png" title="Open this PO" border=0></a>
				{if $sessioninfo.level>=9999 || $sessioninfo.id==$po_list[i].user_id}
				<a href="javascript:void(po_chown({$po_list[i].id},{$po_list[i].branch_id}))"><img src="ui/chown.png" title="Change Owner" border=0></a>
				{/if}
			{/if}
        {elseif $po_list[i].status==2}
		<a href="purchase_order.php?a=open&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/rejected.png" title="Open this PO" border=0></a>
		{elseif $po_list[i].status==4 or $po_list[i].status==5}
		<a href="purchase_order.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/cancel.png" title="Open this PO" border=0></a>
		{else}
		<a href="purchase_order.php?a=view&id={$po_list[i].id}&branch_id={$po_list[i].branch_id}" target=_blank><img src="ui/approved.png" title="Open this PO" border=0></a>
		{/if}

		{if $po_list[i].active}
		<a href="javascript:void(do_print({$po_list[i].id},{$po_list[i].branch_id}))"><img src="ui/print.png" title="Print this PO" border=0></a>
		{if $po_list[i].approved}
		<a href="purchase_order.label_print.php?po_no={$po_list[i].po_no}&send_to=barcode/{$po_list[i].po_no}.txt" target=ifprint><img src="ui/barcode.png" title="Print Barcode" border=0></a>
		{/if}
		{elseif $po_list[i].branch_id==1 && $po_list[i].approved}
		<!--a href="purchase_order.php?a=print_distribution&branch_id={$po_list[i].branch_id}&id={$po_list[i].id}"  target=ifprint><img src="ui/icons/printer_add.png" title="Print distribution HQ PO list" border=0></a-->
		<img src="ui/icons/printer_add.png" title="Print distribution HQ PO list" border=0 onclick="do_print_distribution({$po_list[i].id},{$po_list[i].branch_id});">
		{/if}
		
		{if $po_list[i].delivered}
		<a href="goods_receiving_note.php?t=0&s={$po_list[i].po_no}"><img src="/ui/lorry.png" border=0 title="PO delivered. Click to search GRN Record"></a>
		{/if}
		</td>
	<!--td>{$po_list[i].id}</td-->
	<td>
	{if $po_list[i].status==0}
		{$po_list[i].report_prefix}{$po_list[i].id|string_format:"%05d"}(DP)
	{elseif $po_list[i].po_no eq ''}
		{$po_list[i].report_prefix}{$po_list[i].id|string_format:"%05d"}(PP)
	{else}
		{$po_list[i].po_no}
		<br><font class="small" color=#009900>
		{$po_list[i].report_prefix}{if $po_list[i].hq_po_id}{$po_list[i].hq_po_id|string_format:"%05d"}{else}{$po_list[i].id|string_format:"%05d"}{/if}(PP)
		</font>
	{/if}
	</td>
	<td>{$po_list[i].po_branch|default:$po_list[i].branch}</td>
	<td>{$po_list[i].vendor}
		{if preg_match('/\d/',$po_list[i].approvals)}
			<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$po_list[i].approvals}</font></div>
		{/if}
		{if $po_list[i].branch_id=='1' && $po_list[i].approved}
			<div class=small>
			<font color=#0000ff>
			{foreach from=$po_list[i].po_no_list item=pn name=pn}
			{if $smarty.foreach.pn.iteration>1} ,{/if}
			<a href="/purchase_order.php?a=view&id={$pn.po_id}&branch_id={$pn.branch_id}" target="_blank">
			{$pn.po_no} {if $pn.b_name}({$pn.b_name}){/if}
			</a>
			{/foreach}
			</font>
			</div>
		{/if}		
	</td>
	<td>{$po_list[i].dept}</td>
	{strip}
	<td align=right>{$po_list[i].po_amount|number_format:2}</td>
	{/strip}
	<td align=right>{$po_list[i].last_update}</td>
	<td align=center>{$po_list[i].print_counter}</td>
</tr>
{sectionelse}
<tr>
	<td colspan=6>- no record -</td>
</tr>
{/section}
</table>
<script>
ts_makeSortable($('po_tbl'));
</script>
