{*
1/16/2020 2:51 PM Andy
- Enhanced to show IP for each scan record.
*}

<form name="f_daily_record" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_user_daily_record" />
	<input type="hidden" name="is_new" value="{$is_new}" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="user_id" value="{$form.user_id}" />
	
	
	<table width="100%">
		{* User *}
		<tr>
			<td><b>User</b></td>
			<td colspan="2">
				{$user.u}
			</td>
		</tr>
		
		{* Date *}
		<tr>
			<td><b>Date</b></td>
			<td colspan="2">
				{if $is_new}
					<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size="12" class="required" title="Date" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date"/> &nbsp;
				{else}
					{$form.date}
					<input type="hidden" name="date" value="{$form.date}" />
				{/if}
			</td>
		</tr>
		
		{* Shift *}
		<tr>
			<td><b>Shift</b></td>
			<td colspan="2">
				<select name="shift_id" onChange="DAILY_RECORD_DIALOG.shift_changed();" class="required" title="Shift">
					<option value="">-- Please Select --</option>
					{foreach from=$shift_list key=shift_id item=shift}
						<option value="{$shift_id}" {if $form.shift_id eq $shift_id}selected {/if} start_time="{$shift.start_time|date_format:'%H:%M'}" end_time="{$shift.end_time|date_format:'%H:%M'}" break_1_start_time="{$shift.break_1_start_time|date_format:'%H:%M'|ifzero:''}" break_1_end_time="{$shift.break_1_end_time|date_format:'%H:%M'|ifzero:''}" break_2_start_time="{$shift.break_2_start_time|date_format:'%H:%M'|ifzero:''}" break_2_end_time="{$shift.break_2_end_time|date_format:'%H:%M'|ifzero:''}" shift_color="{$shift.shift_color}" shift_code="{$shift.code}" shift_description="{$shift.description}">{$shift.code} - {$shift.description}</option>
					{/foreach}
					{if $form.shift_id and !$shift_list.$shift_id}
						<option value="{$form.shift_id}" selected start_time="{$form.start_time|date_format:'%H:%M'}" end_time="{$form.end_time|date_format:'%H:%M'}" break_1_start_time="{$form.break_1_start_time|date_format:'%H:%M'|ifzero:''}" break_1_end_time="{$form.break_1_end_time|date_format:'%H:%M'|ifzero:''}" break_2_start_time="{$form.break_2_start_time|date_format:'%H:%M'|ifzero:''}" break_2_end_time="{$form.break_2_end_time|date_format:'%H:%M'|ifzero:''}" shift_color="{$form.shift_color}" shift_code="{$form.shift_code}" shift_description="{$form.shift_description}">{$form.shift_code} - {$form.shift_description}</option>
					{/if}
				</select>
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
				
				<input type="text" name="shift_code" value="{$form.shift_code}" placeholder="Code" title="Shift Code" style="width: 50px;" />
				<input type="text" name="shift_description" value="{$form.shift_description}" title="Shift Description" placeholder="Shift Description" />
			</td>
		</tr>
		
		
		{* Color *}
		<tr>
			<td><b>Color</b></td>
			<td colspan="2">
				
				<input type="hidden" name="shift_color" value="{$form.shift_color}" />
				<div id="div_shift_color" style="width: 20px;background-color: #{$form.shift_color}">&nbsp;</div>
			</td>
		</tr>
		
		{* Working *}
		<tr>
			<td><b>Working</b></td>
			<td>
				From (hh:mm)
				<input type="text" name="start_time" size="10" maxlength="5" value="{$form.start_time|date_format:'%H:%M'}" class="required" title="Start Time" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			</td>
			<td>
				to (hh:mm)
				<input type="text" name="end_time" size="10" maxlength="5" value="{$form.end_time|date_format:'%H:%M'}" class="required" title="End Time" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			</td>
		</tr>
		
		{* Break 1 *}
		<tr>
			<td><b>Break 1</b></td>
			<td>
				From (hh:mm)
				<input type="text" name="break_1_start_time" size="10" maxlength="5" value="{$form.break_1_start_time|date_format:'%H:%M'|ifzero:''}" title="Break 1 Start Time" />	
			</td>
			<td>
				to (hh:mm)
				<input type="text" name="break_1_end_time" size="10" maxlength="5" value="{$form.break_1_end_time|date_format:'%H:%M'|ifzero:''}" title="Break 1 End Time" />
			</td>
		</tr>
		
		{* Break 2 *}
		<tr>
			<td><b>Break 2</b></td>
			<td>
				From (hh:mm)
				<input type="text" name="break_2_start_time" size="10" maxlength="5" value="{$form.break_2_start_time|date_format:'%H:%M'|ifzero:''}" title="Break 2 Start Time" />	
			</td>
			<td>
				to (hh:mm)
				<input type="text" name="break_2_end_time" size="10" maxlength="5" value="{$form.break_2_end_time|date_format:'%H:%M'|ifzero:''}" title="Break 2 End Time" />
			</td>
		</tr>
	</table>
	
	<h4>Scan Records</h4>
	<div class="stdframe" style="background-color: #fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th width="40">Delete</th>
				<th>Scan Time</th>
				<th>New Scan Time</th>
			</tr>
			
			{section loop=6 name=st}
				{assign var=scan_no value=$smarty.section.st.index}
				{assign var=row_no value=$smarty.section.st.iteration}
				{assign var=scan_record value=$form.scan_record_list.$scan_no}
				<tr id="tr_delete_scan_time-{$row_no}">
					<td>
						{* Delete *}
						<input type="checkbox" name="delete_scan[{$row_no}]" {if !$scan_record}disabled{/if} onChange="DAILY_RECORD_DIALOG.del_scan_changed('{$row_no}');" />
					</td>
					<td align="center">
						<input type="hidden" name="org_scan_time[{$row_no}]" value="{$scan_record.scan_time}" />
						{$scan_record.scan_time}
						{if $scan_record.ip}
							<i class="small">({$scan_record.ip})</i>
						{/if}
					</td>
					<td align="center" nowrap>
						<input type="text" placeholder="{$form.date|default:'YYYY-MM-DD'}" name="new_scan_date[{$row_no}]" style="width:100px;" />
						<input type="text" placeholder="HH:mm:ss" name="new_scan_time[{$row_no}]" />
					</td>
				</tr>
			{/section}
		</table>
	</div>
</form>

<p id="p_action" align="center">
	<input type="button" value="Save" id="btn_save" onClick="DAILY_RECORD_DIALOG.save_clicked();" />
	<input type="button" value="Close" onClick="DAILY_RECORD_DIALOG.close();" />
</p>