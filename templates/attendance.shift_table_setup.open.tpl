{*
06/25/2020 11:26 AM Sheila
- Updated button css
*}
<form name="f_shift" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save">
	<input type="hidden" name="id" value="{$form.id}" />

	<br />
	<table width="100%">
		{* Code *}
		<tr>
			<td><b>Code</b></td>
			<td colspan="2">
				<input type="text" name="code" size="10" maxlength="10" value="{$form.code}" class="required" title="Code" onChange="this.value=this.value.toUpperCase();" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			</td>
		</tr>
		
		{* Description *}
		<tr>
			<td><b>Description</b></td>
			<td colspan="2">
				<input type="text" name="description" size="50" maxlength="100" value="{$form.description}" class="required" title="Description" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
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
		
		{* Color *}
		<tr>
			<td><b>Color</b></td>
			<td colspan="2">
				<input type="hidden" name="shift_color" value="{$form.shift_color}" />
				<div id="div_shift_color" class="colorSelector" default_color="{$form.shift_color}" style="margin:0;padding:0;">
					<div title="{$form.shift_color}" style="background-color:{$form.shift_color}">&nbsp;</div>
				</div>
			</td>
		</tr>
	</table>
</form>

<p align="center" id="p_action">
	<input class="btn btn-success" type="button" value="Save" onClick="SHIFT_DIALOG.save_clicked();" id="btn_save" />
	<input class="btn btn-error" type="button" value="Close" onClick="SHIFT_DIALOG.close();" />
</p>