{*
8/29/2013 1:36 PM Andy
- change sms information popup message,

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

10/22/2018 4:26 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

6/23/2020 11:23 AM Sheila
- Updated button css
*}

<form name="f_af" onSubmit="return false;">
	<input type="hidden" name="id" value="{$form.id}" />
	
	<div class="table-responsive">
		<table class="table table-hover mb-0 text-md-nowrap table-sm table-borderless">
			<tr>
				{* Branch *}
				<td width="100"><b>Branch </b><span id="rq_img1"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
				<td>
					<select name="branch_id" class="form-control">
						<option value="">-- Please Select --</option>
						{foreach from=$branch_list key=bid item=b}
							{if $bid eq $form.branch_id or $b.active}
								<option value="{$bid}" {if $bid eq $form.branch_id}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
					</select>
				</td>
				
				{* Flow Type *}
				<td width="100"><b>Flow Type </b><span id="rq_img2"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
				<td>
					<select name="type" onChange="APPROVAL_FLOW.flow_type_changed();" class="form-control">
						<option value="">-- Please Select --</option>
						{foreach from=$flow_set item=r}
							<option value="{$r.type}" {if $r.type eq $form.type}selected {/if}>{if $r.description}{$r.description}{else}{$r.type}{/if}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			
			<tr>
				{* Department *}
				<td width="100"><b>Department </b><span id="rq_img3"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
				<td>
					<select name="sku_category_id" class="form-control">
						<option value="">-- Please Select --</option>
						{foreach from=$dept_list key=dept_id item=r}
							{if $dept_id eq $form.sku_category_id or $r.active}
								<option value="{$dept_id}" {if $dept_id eq $form.sku_category_id}selected {/if}>{$r.description}</option>
							{/if}
						{/foreach}
					</select>
				</td>
				
				{* SKU Type *}
				<td width="100"><b>SKU Type </b><span id="rq_img4"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
				<td>
					<select name="sku_type" class="form-control">
						<option value="">-- Please Select --</option>
						{foreach from=$sku_type_list key=st item=r}
							<option value="{$st}" {if $form.sku_type eq $st}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			
			{* Approval Order *}
			<tr>
				<td width="100"><b>Approval Order </b><span id="rq_img5"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
				<td>
					<select name="aorder" onChange="APPROVAL_FLOW.aorder_changed();" class="form-control">
						<option value="">-- Please Select --</option>
						{foreach from=$aorder key=aorder_id item=r}
							<option value="{$aorder_id}" {if $aorder_id eq $form.aorder}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			
			{* Search User *}
			<tr>
				<td colspan="4">
					<b>Search Username : </b>
					<div class="d-flex my-2">
						<input type="text" class="form-control mr-2" readonly id="inp_selected_username" style="width:80px;"/>
					<input type="text" class="form-control" id="inp_search_username" style="width:300px;" placeholder="Please search username" onFocus="this.select();" />
					<input type="hidden" id="inp_selected_user_id" />
					</div>
					
					<input class="btn btn-success btn-sm" type="button" value="Add to Approval" id="btn_add_appvoral" onClick="APPROVAL_FLOW.add_autocomplete_user_clicked('approval');" />
					<input class="btn btn-primary btn-sm" type="button" value="Add to Notify" id="btn_add_notify" onClick="APPROVAL_FLOW.add_autocomplete_user_clicked('notify');" />
					<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
					
					<br />
					<div id="div_autocomplete_username" class="autocomplete" style="display:none;height:150px !important;width:200px !important;overflow:auto !important;z-index:100"></div>
				</td>
			</tr>
		</table>
	</div>
	
	{* Approvals *}
	<div id="div_approvals">
		<h3>Approvals
			<span id="span_approval_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
		</h3>
		<div style="height:150px;background-color:white;border:2px inset black;overflow:auto;">
			<table width="100%" class="report_table" id="tbl_approvals">
				<tr class="header">
					<th width="20">&nbsp;</th>
					<th width="50">&nbsp;</th>
					<th>Username</th>
					<th width="70">Default Branch</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','pm', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','pm', false);" />
						<br />
						PM
					</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','email', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','email', false);" />
						<br />
						<span style="white-space:nowrap;">
							Email
							{if !$config.notification_send_email}
								<img src="ui/icons/information.png" align="absmiddle" onClick="alert('You need to turn on Email Config to use this.');" />
							{/if}
						</span>						
					</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','sms', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('approval','sms', false);" />
						<br />
						<span style="white-space:nowrap;">
							SMS
							{if !$config.notification_send_sms}
								<img src="ui/icons/information.png" align="absmiddle" onClick="alert('To enable SMS notification, please contact your account manager for the purchase of SMS credit.');" />
							{/if}
						</span>
					</th>
					<th width="100">
						<img src="ui/icons/textfield_rename.png" title="Assign all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_min_doc_amt();" />
						<br />
						Min. Document Amt
						<img src="ui/icons/information.png" align="absmiddle" onClick="alert('This will not affect those module without amount, eg: SKU APPLICATION, PROMOTION');" />
					</th>
				</tr>
				
				<tbody id="tbody_approval_list">
					{foreach from=$form.approvals_info key=user_id item=r}
						{include file="approval_flow.open.approval_user.tpl"}
					{/foreach}
				</tbody>				
			</table>
		</div>

		<div id="div_approval_curtain" style="{if $form.aorder ne 4}display:none;{/if}">
			<div style="position:absolute;margin-top:-153px;height:153px;background-color:grey;width:100%;opacity:0.5;text-align:center;vertical-align:middle;"></div>
			<div style="position:absolute;background-color:white;margin-top:-100px;margin-left:200px;text-align:center;width:300px;border:1px solid black;"><h1>No Approval is Needed</h1></div>
		</div>
		
	</div>
	
	{* Notify Users *}
	<div id="div_approvals">
		<h3>Notify Users
			<span id="span_notify_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
		</h3>
		<div style="height:150px;background-color:white;border:2px inset black;overflow:auto;">
			<table width="100%" class="report_table" id="tbl_notify">
				
				<tr class="header">
					<th width="20">&nbsp;</th>
					<th width="50">&nbsp;</th>
					<th>Username</th>
					<th width="70">Default Branch</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','pm', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','pm', false);" />
						<br />
						PM
					</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','email', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','email', false);" />
						<br />
						<span style="white-space:nowrap;">
							Email
							{if !$config.notification_send_email}
								<img src="ui/icons/information.png" align="absmiddle" onClick="alert('You need to turn on Email Config to use this.');" />
							{/if}
						</span>						
					</th>
					<th width="50">
						<img src="ui/checkall.gif" title="Check all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','sms', true);" />
						<br>
						<img src="ui/uncheckall.gif" title="Uncheck all" class="clickable" onClick="APPROVAL_FLOW.update_all_user_approval_notify_settings('notify','sms', false);" />
						<br />
						<span style="white-space:nowrap;">
							SMS
							{if !$config.notification_send_sms}
								<img src="ui/icons/information.png" align="absmiddle" onClick="alert('To enable SMS notification, please contact your account manager for the purchase of SMS credit.');" />
							{/if}
						</span>
					</th>
				</tr>
				
				<tbody id="tbody_notify_list">
					{foreach from=$form.notify_users_info key=user_id item=r}
						{include file="approval_flow.open.notify_user.tpl"}
					{/foreach}
				</tbody>				
			</table>
		</div>
	</div>

	{* Owner *}
	<h3>Owner: 
		<input type="checkbox" name="approval_settings[owner][pm]" value="1" title="PM" {if $form.approval_settings.owner.pm}checked {/if} /> PM
		&nbsp;&nbsp;
		<input type="checkbox" name="approval_settings[owner][email]" value="1" title="Email" {if $form.approval_settings.owner.email}checked {/if} /> Email
		&nbsp;&nbsp;
		<input type="checkbox" name="approval_settings[owner][sms]" value="1" title="SMS" {if $form.approval_settings.owner.sms}checked {/if} /> SMS
	</h3>
	<br />
	<div align="center">
		<div id="div_approval_flow_processing">
			{if !$form.id}
				<input class="btn btn-primary" type="button"value="Add" onClick="APPROVAL_FLOW.update_clicked();" /> 
			{else}
		
				<input class="btn btn-primary" type="button" value="Update" onClick="APPROVAL_FLOW.update_clicked();" /> 
				<input class="btn btn-success" type="button" value="Save As" onClick="APPROVAL_FLOW.save_as_clicked();" /> 	
			{/if}
			
			<input class="btn btn-error" type="button" value="Close" onclick="default_curtain_clicked();" />
		</div>
		
		<span id="span_approval_flow_processing" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Processing. . .</span>
	</div>
	
</form>
