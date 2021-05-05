{if !$max_series_date}
	{assign var=max_series_date value=$form.propose_st_date}
{/if}

<form name="f_clone" onSubmit="return false;">
	<table width="100%" class="report_table" style="background-color: #fff;">
		<tr>
			<td width="200" class="col_header"><b>Document No</b></td>
			<td>{$form.doc_no}</td>
		</tr>
		
		<tr>
			<td class="col_header">
				<b>Select Clone Method</b>
			</td>
			<td>
				<input type="radio" name="clone_method" value="normal" checked onChange="CC_CLONE_DIALOG.clone_method_changed();" /> Normal&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="clone_method" value="advanced" onChange="CC_CLONE_DIALOG.clone_method_changed();" /> Advanced
			</td>
		</tr>
	</table>
</form><br />

<div id="div_clone_tab" class="tab" style="height:20px;white-space:nowrap;">
	<a href="javascript:void(CC_CLONE_DIALOG.tab_changed('settings'))" id="clone_tab-settings" class="a_tab active">Settings</a>
	<a href="javascript:void(CC_CLONE_DIALOG.tab_changed('series'))" id="clone_tab-series" class="a_tab">Series</a>
</div>
<div id="div_clone_contain" style="border:1px solid #000; background-color: #fff;height:290px; overflow-y:auto;">
	<div id="div_clone_details-settings" class="div_clone_details">
		<div id="div_clone_settings-normal" class="div_clone_settings">
			<ul>
				<li>Clone another one cycle count with exactly same content.</li>
				<li>Will not mark the relationship with the new cycle count.</li>
			</ul>
			
			<form name="f_clone_settings_normal" onSubmit="return false;">
				<input type="hidden" name="branch_id" value="{$form.branch_id}" />
				<input type="hidden" name="id" value="{$form.id}" />
				<input type="hidden" name="clone_type" value="normal" />
			</form>
			
			<input type="button" value="Start Clone Now" class="btn_process" onClick="CC_CLONE_DIALOG.start_clone_clicked('normal');" />
		</div>
		
		<div id="div_clone_settings-advanced" class="div_clone_settings" style="display:none;">
			<ul>
				<li>Clone multiple cycle count with different stock take date.</li>
				<li>Will mark the relationship with those new cycle count.</li>
				<li>The new cycle count will be tracked as a series for this cycle count.</li>
			</ul>
			
			<form name="f_clone_settings_advanced" onSubmit="return false;">
				<input type="hidden" name="branch_id" value="{$form.branch_id}" />
				<input type="hidden" name="id" value="{$form.id}" />
				<input type="hidden" name="clone_type" value="advanced" />
				
				<table>
					{* Method *}
					<tr>
						<td><b>Clone Copy</b></td>
						<td>
							<input type="text" name="clone_copy" value="1" style="text-align: right; width:50px;" onChange="CC_CLONE_DIALOG.clone_copy_changed();" />
							(Max 10)
						</td>
					</tr>
					
					{* Durtaion *}
					<tr>
						<td><b>Durtaion for each Cycle Count</b></td>
						<td>
							{* Duration Value *}
							<input type="text" name="duration_value" style="text-align: right; width:50px;" value="1" onChange="CC_CLONE_DIALOG.duration_value_changed();" />
							
							{* Duration Type *}
							<select name="duration_type" onChange="CC_CLONE_DIALOG.duration_type_changed();">
								<option value="m">Month</option>
								<option value="w">Week</option>
								<option value="d">Day</option>
								<option value="manual">Manual</option>
							</select>
						</td>
					</tr>
					
					{* Max Series Date *}
					<tr>
						<td><b>Max Series Date</b></td>
						<td>
							{$max_series_date}
							<input type="hidden" name="max_series_date" value="{$max_series_date}" />
						</td>
					</tr>
				</table>
				
				<div style="display:none;">
					<input type="text" class="inp_propose_st_date" id="tmp_inp_propose_st_date" readonly onChange="CC_CLONE_DIALOG.propose_st_date_changed(this);" maxlength="10" />
				</div>
				
				<br />
				Below Propose Stock Take will be created
				<div class="stdframe" id="div_propose_st_date_list">
					
				</div>
			</form>
			
			<input type="button" value="Start Clone Now" class="btn_process" onClick="CC_CLONE_DIALOG.start_clone_clicked('advanced');" />
		</div>
		
		
	</div>
	
	<div id="div_clone_details-series" class="div_clone_details" style="display:none;">
		<h3>Series</h3>
		<table class="report_table" width="100%">
			<tr class="headeR">
				<th>Doc No</th>
				<th>Propose Stock Take Date</th>
				<th>Last Update</th>
			</tr>
			
			{foreach from=$form.series_cc_list item=cc}
				<tr class="{if $cc.doc_no eq $form.doc_no}highlight_row{/if}">
					<td align="center">
						<a href="?a=view&branch_id={$cc.branch_id}&id={$cc.id}" target="_blank">
							{$cc.doc_no}
						</a>
					</td>
					<td align="center">{$cc.propose_st_date}</td>
					<td align="center">{$cc.last_update}</td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>
<div style="position:fixed;" id="div_processing"></div>
<p style="text-align:center;">
	<input type="button" value="Close" class="btn_process" onClick="CC_CLONE_DIALOG.close();" />
	
</p>
