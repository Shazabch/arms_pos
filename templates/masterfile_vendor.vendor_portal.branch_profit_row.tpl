{*
11/15/2012 12:03 PM Andy
- Add Report Profit other % (category and sku).

12/17/2012 4:50 PM Andy
- Add can copy/paste report profit other %.
*}

<tr id="tr_branch_profit_row-{$bid}-{$row_no}" class="tr_branch_profit_row" valign="top">
	<td align="center">
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.delete_branch_profit_row_clicked('{$bid}', '{$row_no}');" />
	</td>
	<td nowrap align="center">
		<input type="text" size="10" id="inp_profit_date_to-{$bid}-{$row_no}" name="sales_report_profit_by_date[{$bid}][{$row_no}][date_to]" value="{$profit_data.date_to}" readonly class="inp_profit_date_to required" title="Report Profit Date To" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_profit_date_to-{$bid}-{$row_no}" style="cursor: pointer;" title="Select Date" />
	</td>
	
	{* % *}
	<td align="center">
		<input type="text" size="3" name="sales_report_profit_by_date[{$bid}][{$row_no}][profit_per]" value="{$profit_data.profit_per|default:0|number_format:2:".":""|ifzero:''}" onChange="mfz(this);" class="required" title="Report Profit %" />
	</td>
	
	{* More Breakdown %*}
	<td>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>&nbsp;</th>
				<th>Type</th>
				<th>Info [<a href="javascript:void(alert('SKU: ARMS Code/MCode/ArtNo\nCATEGORY: Description '))">?</a>]</th>
				<th>%</th>
			</tr>
			<tbody id="tbody_branch_profit_row_breakdown-{$bid}-{$row_no}">
				{foreach from=$profit_data.profit_per_by_type item=profit_per_by_type name=fprofit_per_by_type}
					{assign var=type_row_no value=$smarty.foreach.fprofit_per_by_type.iteration}
					
					{include file="masterfile_vendor.vendor_portal.branch_profit_row.breakdown_percent_row.tpl"}
				{/foreach}
			</tbody>			
		</table>
		<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_clicked('{$bid}', '{$row_no}');">+</button>
		<button onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_copy_clicked('{$bid}', '{$row_no}');" id="btn_add_branch_profit_row_more_percent_copy-{$bid}-{$row_no}" class="btn_add_branch_profit_row_more_percent_copy">Copy</button>
		<button onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_paste_clicked('{$bid}', '{$row_no}');" class="btn_add_branch_profit_row_more_percent_paste">Paste</button>
		
		<span id="span_branch_profit_row_breakdown_loading-{$bid}-{$row_no}" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</td>
</tr>