{*
11/15/2012 12:03 PM Andy
- Add Bonus other % (category only).

12/20/2012 11:38 AM Andy
- Add can copy/paste bonus table.
*}

<div id="div_branch_bonus-{$bid}-{$y}-{$m}">
	<table id="tbl_branch_bonus-{$bid}-{$y}-{$m}" class="tbl_branch_bonus report_table" cellpadding="2" cellspacing="0">
		<tr class="tr_header2">
			<th width="50">{$m}/{$y}</th>
			<th width="120">Amount >=</th>
			<th width="50">% [<a href="javascript:void(alert('This % use for all category all sku.'))">?</a>]</th>
			<th>Other % [<a href="javascript:void(alert('This % can use to assign sepcified rate for certain category. \n* Please note global % will still be calculate and may cause overlaped result in total %.'))">?</a>]</th>
			<th><img src="ui/del.png" class="clickable" title="Delete Group" onClick="VENDOR_PORTAL.delete_branch_bonus_group_clicked('{$bid}', '{$y}', '{$m}')" /></th>
		</tr>
		
		<tbody id="tbody_branch_bonus-{$bid}-{$y}-{$m}">
			{foreach from=$bonus_data_list item=bonus_data name=fbonus}
				{assign var=row_no value=$smarty.foreach.fbonus.iteration}
				
				{include file="masterfile_vendor.vendor_portal.branch_bonus_row.tpl"}
			{/foreach}
		</tbody>
	</table>
	<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_bonus_row_clicked('{$bid}', '{$y}', '{$m}');">+</button>
	
	{* Copy *}
	<button onClick="VENDOR_PORTAL.add_branch_bonus_row_copy_clicked('{$bid}', '{$y}', '{$m}');" id="btn_add_branch_bonus_row_copy-{$bid}-{$y}-{$m}" class="btn_add_branch_bonus_row_copy">Copy</button>
	
	{* Paste *}
	<button onClick="VENDOR_PORTAL.add_branch_bonus_row_paste_clicked('{$bid}', '{$y}', '{$m}');" class="btn_add_branch_bonus_row_paste">Paste</button>
	
	<span id="span_branch_bonus_row_loading-{$bid}-{$y}-{$m}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /><br /> Loading...</span>
</div>
