<tr id="tr_si_active_date_row-{$sid}-{$row_no}" class="tr_si_active_date_row">
	<td align="center">
		<img src="ui/del.png" title="Delete" class="clickable" onClick="DC_MODULE.delete_tr_sid_active_date_row_clicked('{$sid}', '{$row_no}');" />
	</td>
	
	{* Date From *}
	<td>
		<input type="text" size="10" id="inp_si_active_date_from-{$sid}-{$row_no}" name="from_date[{$sid}][{$row_no}]" value="{$si_date_row.from_date}" class="inp_si_active_date_from required" title="Date From" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_si_active_date_from-{$sid}-{$row_no}" style="cursor: pointer;" title="Select Date" />
	</td>
	
	{* Date To *}
	<td>
		<input type="text" size="10" id="inp_si_active_date_to-{$sid}-{$row_no}" name="to_date[{$sid}][{$row_no}]" value="{$si_date_row.to_date}" class="inp_si_active_date_to required" title="Date To" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_si_active_date_to-{$sid}-{$row_no}" style="cursor: pointer;" title="Select Date" />
	</td>
</tr>
