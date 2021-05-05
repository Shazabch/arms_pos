{*
11/15/2012 12:03 PM Andy
- Add Bonus other % (category only).

12/20/2012 3:18 PM Andy
- Add can copy/paste bonus other %.
*}

<tr id="tr_branch_bonus_row-{$bid}-{$y}-{$m}-{$row_no}" class="tr_branch_bonus_row">
	<td align="center">
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.delete_branch_bonus_row_clicked('{$bid}', '{$y}', '{$m}','{$row_no}');" />
	</td>
	<td nowrap align="center">
		<input type="text" size="10" name="sales_bonus_by_step[{$bid}][{$y}][{$m}][{$row_no}][amt_from]" value="{$bonus_data.amt_from|number_format:2:".":""|ifzero:''}" onChange="mfz(this);" class="inp_sales_bonus_by_step-amt_from required" title="Bonus Amount From" />
	</td>
	
	{* % *}
	<td align="center">
		<input type="text" size="3" name="sales_bonus_by_step[{$bid}][{$y}][{$m}][{$row_no}][bonus_per]" value="{$bonus_data.bonus_per|default:0|number_format:2:".":""|ifzero:''}" onChange="mfz(this);" title="Bonus %" />
	</td>
	
	{* More Breakdown %*}
	<td>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>&nbsp;</th>
				<th>Type</th>
				<th>Info</th>
				<th>%</th>
			</tr>
			<tbody id="tbody_branch_bonus_row_breakdown-{$bid}-{$y}-{$m}-{$row_no}">
				{foreach from=$bonus_data.bonus_per_by_type item=bonus_per_by_type name=fbonus_per_by_type}
					{assign var=type_row_no value=$smarty.foreach.fbonus_per_by_type.iteration}
					
					{include file="masterfile_vendor.vendor_portal.branch_bonus_row.breakdown_percent_row.tpl"}
				{/foreach}
			</tbody>			
		</table>
		<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_clicked('{$bid}', '{$y}', '{$m}', '{$row_no}');">+</button>
		{* Copy *}
		<button onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_copy_clicked('{$bid}', '{$y}', '{$m}', '{$row_no}');" id="btn_add_branch_bonus_row_more_percent_copy-{$bid}-{$y}-{$m}-{$row_no}" class="btn_add_branch_bonus_row_more_percent_copy">Copy</button>
	
		{* Paste *}
		<button onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_paste_clicked('{$bid}', '{$y}', '{$m}', '{$row_no}');" class="btn_add_branch_bonus_row_more_percent_paste">Paste</button>
	
		<span id="span_branch_bonus_row_breakdown_loading-{$bid}-{$y}-{$m}-{$row_no}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</td>
</tr>