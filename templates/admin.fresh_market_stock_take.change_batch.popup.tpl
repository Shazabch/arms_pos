<form name="f_change_batch" onSubmit="return false;">
	<input type="hidden" name="a" value="batch_update" />
	<input type="hidden" name="branch_id" value="{$data.branch_id}" />
	<input type="hidden" name="o_date" value="{$data.date}" />
	<input type="hidden" name="o_loc" value="{$data.loc}" />
	<input type="hidden" name="o_shelf" value="{$data.shelf}" />
	
	<fieldset>
		<legend>Selected Stock Info</legend>
		<table>
			<tr>
				<td width="100"><b>Branch</b></td>
				<td>{$data.branch_code}</td>
			</tr>
			<tr>
				<td><b>Date</b></td>
				<td>{$data.date}</td>
			</tr>
			<tr>
				<td><b>Location</b></td>
				<td>{$data.loc|default:'-- All --'}</td>
			</tr>
			<tr>
				<td><b>Shelf</b></td>
				<td>{$data.shelf|default:'-- All --'}</td>
			</tr>
			<tr>
				<td><b>Total Item</b></td>
				<td>{$data.item_count|number_format}</td>
			</tr>
		</table>
	</fieldset>
	
	<p align="center">
		<img src="/ui/icons/arrow_down.png" />
	</p>
	
	<fieldset>
		<legend>New Stock Info</legend>
		
		<table>
			<tr>
				<td width="100"><b>Branch</b></td>
				<td>{$data.branch_code}</td>
			</tr>
			<tr>
				<td><b>Date</b></td>
				<td>
					<input name="n_date" id="inp_n_date" size="12" value="{$data.date}" readonly /> 
					<img align="absmiddle" src="/ui/calendar.gif" id="img_n_date" style="cursor: pointer;" title="Select Date" />
				</td>
			</tr>
			<tr>
				<td><b>Location</b></td>
				<td>
					<input name="n_loc" size="12" value="{$data.loc}" readonly /> 
					<input type="checkbox" name="keep_o_loc" value="1" checked onChange="toggle_new_stock_info_chx('n_loc', this);" /> Keep existing
				</td>
			</tr>
			<tr>
				<td><b>Shelf</b></td>
				<td>
					<input name="n_shelf" size="12" value="{$data.shelf}" readonly /> 
					<input type="checkbox" name="keep_o_shelf" value="1" checked onChange="toggle_new_stock_info_chx('n_shelf', this);" /> Keep existing
				</td>
			</tr>
		</table>
	</fieldset>
	
	<p align="center">
		<input type="button" value="Update" onClick="start_change_batch();" />
	</p>
</form>

<script>
	init_change_batch_calendar();
</script>