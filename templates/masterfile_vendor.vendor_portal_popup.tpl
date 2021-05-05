{*
7/16/2012 5:09 PM Andy
- Add can select sku group for each access branch.

8/11/2012 yinsee
- add profit %

8/13/2012 4:59: PM Andy
- add link to debtor.

9/11/2012 3:24 PM Andy
- Enhance vendor login ticket,email, link to debtor to saved by branch.
*}
<p><b>{if $form.code}{$form.code} - {/if}{$form.description}</b></p>

<form name="f_vp" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_update_vendor_portal" />
	<input type="hidden" name="vendor_id" value="{$form.id}" />
	
	<table>
		<!-- Active -->
		<tr>
			<td width="200"><b>Active</b>
				[<a href="javascript:void(alert('Activate to allow vendor to access Vendor Portal'));">?</a>]
			</td>
			<td>
				<input type="radio" name="active_vendor_portal" value="1" {if $form.active_vendor_portal}checked {/if} onChange="VENDOR_PORTAL_POPUP.active_vendor_portal_changed();" /> Yes
				<input type="radio" name="active_vendor_portal" value="0" {if !$form.active_vendor_portal}checked {/if} onChange="VENDOR_PORTAL_POPUP.active_vendor_portal_changed();" /> No
				<span style="color:red;{if $form.active_vendor_portal}display:none;{/if}" id="span_active_vendor_portal_msg">
					(Vendor will not able to login even got login ticket)
				</span>
			</td>
		</tr>
		
		{*
		<!-- Allow to access branch -->
		<tr>
			<td valign="top"><b>Allow to access branch</b></td>
			<td>
				<div style="width:400px;height:300px;border:1px solid grey;overflow-y:auto;">
					<table>
						<tr>
							<td><input type="checkbox" id="inp_toggle_all_allowed_branches" onchange="VENDOR_PORTAL_POPUP.toggle_all_allowed_branches();" /></td>
							<td>All</td>
							<td>&nbsp;</td>
							<td>Report<br>Profit(%)</td>
						</tr>
						{foreach from=$branches_list key=bid item=b}
							<tr>
								<td><input type="checkbox" name="allowed_branches[{$bid}]" value="{$bid}" class="inp_allowed_branches" {if $form.allowed_branches.$bid}checked {/if} /></td>
								<td>{$b.code}</td>
								<td>
									<select name="sku_group_info[{$bid}]" style="width:250px">
										<option value="">-- Please Select SKU Group --</option>
										{foreach from=$sku_group_list item=sg}
											{capture assign=sg_id}{$sg.branch_id}|{$sg.sku_group_id}{/capture}
											<option value="{$sg_id}" {if $form.sku_group_info.$bid eq $sg_id}selected {/if}>{$sg.code} - {$sg.description}</option>
										{/foreach}
									</select>
								</td>
								<td><input name="sales_report_profit[{$bid}]" value="{$form.sales_report_profit.$bid}" size=3>%</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</td>
		</tr>
		
		<!-- Login Ticket -->
		<tr>
			<td><b>Login Ticket</b></td>
			<td>
				<input type="text" name="login_ticket" value="{$form.login_ticket}" size="12" readonly="" />
				<input type="button" id="btn_generate_ticket" value="{if $form.login_ticket}Clear{else}Generate{/if}" onClick="VENDOR_PORTAL_POPUP.generate_ticket_clicked();" style="width:100px;" />
			</td>
		</tr>
		
		<!-- Expire Date -->
		<tr>
			<td><b>Expire Date</b></td>
			<td>
				<span id="span_expire_date" style="{if $form.expire_date eq '9999-12-31'}display:none;{/if}">
					{assign var=default_expire_date value=$smarty.now+31536000}
					{if $form.expire_date eq '9999-12-31'}
						{assign var=expire_date value=$default_expire_date}
					{else}
						{assign var=expire_date value=$form.expire_date}
					{/if}
					{if !$expire_date}{assign var=expire_date value=$default_expire_date}{/if}
					
					<input type="text" name="expire_date" id="inp_expire_date" size="12" value="{$expire_date|date_format:"%Y-%m-%d"}" title="Expire Date" readonly />
					<img align="absmiddle" src="ui/calendar.gif" id="img_expire_date" style="cursor: pointer;" title="Select Date" />
				</span>
				
				
				<input type="checkbox" name="no_expire" value="1" {if $form.expire_date eq '9999-12-31'}checked {/if} id="inp_no_expire" onChange="VENDOR_PORTAL_POPUP.toggle_no_expire();" /> No Expire Date
			</td>
		</tr>
		
		<!-- Debtor Link -->
		<tr>
			<td><b>Link to Debtor</b></td>
			<td>
				<select name="link_debtor_id">
					<option value="">-- No Link --</option>
					{foreach from=$debtor_list key=debtor_id item=r}
						<option value="{$debtor_id}" {if $form.link_debtor_id eq $debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		*}
	</table>
	
	<br />
	<b>Branch Access Settings</b>
	<div style="width:98%;height:350px;border:2px inset grey;overflow-y:auto;">
		<table width="100%" cellpadding="0" cellspacing="0" class="report_table" style="background-color:#fff;">
			<tr class="header">
				<th><input type="checkbox" id="inp_toggle_all_allowed_branches" onchange="VENDOR_PORTAL_POPUP.toggle_all_allowed_branches();" /></th>
				<th>Branch</th>
				<th>SKU Group</th>
				<th>Report<br>Profit(%)</th>
				<th>Login<br />Ticket</th>
				<th>Expire Date</th>
			</tr>
			{foreach from=$branches_list key=bid item=b}
				<tr class="tr_allowed_branches">
					<td valign="top" rowspan="2"><input type="checkbox" name="allowed_branches[{$bid}]" value="{$bid}" id="inp_allowed_branches-{$bid}" class="inp_allowed_branches" {if $form.allowed_branches.$bid}checked {/if} /></td>
					<td valign="top">{$b.code}</td>
					
					<!-- sku group -->
					<td valign="top">
						<select name="sku_group_info[{$bid}]" style="width:250px">
							<option value="">-- Please Select SKU Group --</option>
							{foreach from=$sku_group_list item=sg}
								{capture assign=sg_id}{$sg.branch_id}|{$sg.sku_group_id}{/capture}
								<option value="{$sg_id}" {if $form.sku_group_info.$bid eq $sg_id}selected {/if}>{$sg.code} - {$sg.description}</option>
							{/foreach}
						</select>
					</td>
					
					<!-- Report Profit -->
					<td nowrap valign="top"><input name="sales_report_profit[{$bid}]" value="{$form.sales_report_profit.$bid}" size=3>%</td>
					
					<!-- Login Ticket -->
					<td nowrap>
						<input type="text" name="login_ticket[{$bid}]" value="{$form.branch_info.$bid.login_ticket}" size="12" readonly="" />
						<span id="span_clone_ticket-{$bid}" style="{if !$form.branch_info.$bid.login_ticket}display:none;{/if}">
							<img src="/ui/icons/application_tile_vertical.png" title="Use this ticket for all branches" align="absmiddle" class="clickable" onClick="VENDOR_PORTAL_POPUP.clone_ticket('{$bid}')" />
						</span>
						<br />
						<input type="button" id="btn_generate_ticket-{$bid}" value="{if $form.branch_info.$bid.login_ticket}Clear{else}Generate{/if}" onClick="VENDOR_PORTAL_POPUP.generate_ticket_clicked('{$bid}');" style="width:100px;" />
					</td>
					
					<!-- Expire Date -->
					<td valign="top">
						<span id="span_expire_date-{$bid}" style="{if $form.branch_info.$bid.expire_date eq '9999-12-31'}display:none;{/if}">
							{assign var=default_expire_date value=$smarty.now+31536000}
							{if $form.branch_info.$bid.expire_date eq '9999-12-31'}
								{assign var=expire_date value=$default_expire_date}
							{else}
								{assign var=expire_date value=$form.branch_info.$bid.expire_date}
							{/if}
							{if !$expire_date}{assign var=expire_date value=$default_expire_date}{/if}
							
							<input type="text" name="expire_date[{$bid}]" id="inp_expire_date-{$bid}" size="12" value="{$expire_date|date_format:"%Y-%m-%d"}" title="Expire Date" readonly class="inp_expire_date" />
							<img align="absmiddle" src="ui/calendar.gif" id="img_expire_date-{$bid}" style="cursor: pointer;" title="Select Date" />
							<br />
						</span>
						
						<input type="checkbox" name="no_expire[{$bid}]" value="1" {if $form.branch_info.$bid.expire_date eq '9999-12-31'}checked {/if} id="inp_no_expire-{$bid}" onChange="VENDOR_PORTAL_POPUP.toggle_no_expire('{$bid}');" /> No Expire Date
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<b> Email [<a href="javascript:void(alert('You can enter multiple email separate by \',\'. Sample:\n==================\nadmin@example.com,user@example.com'))">?</a>]:</b>
						<input type="text" name="contact_email[{$bid}]" value="{$form.branch_info.$bid.contact_email}" style="width:300px;" maxlength="200" />
					</td>
					<td colspan="3">
						<b>Link to Debtor: </b>
						<select name="link_debtor_id[{$bid}]">
							<option value="">-- No Link --</option>
							{foreach from=$debtor_list key=debtor_id item=r}
								<option value="{$debtor_id}" {if $form.branch_info.$bid.link_debtor_id eq $debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>						
	<p align="center" id="p_action_button">
		<input type="button" value="Update" onClick="VENDOR_PORTAL_POPUP.update_clicked();" id="btn_update_vendor_portal" />
		<input type="button" value="Close" onClick="VENDOR_PORTAL_POPUP.close();" />
	</p>
</form>
