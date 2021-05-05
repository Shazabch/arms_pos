{*
10/9/2019 9:21 AM William
 - Remove (DD) from do multi checkout when do has do_no.

6/23/2020 04:22 PM Sheila
- Updated button css
*}
Found {$form.do_matched|number_format} record(s) of {if $form.process_type eq "checkout"}approved{else}saved{/if} DO. <br /><br />
{if count($form.do_list) > 0 && $form.process_type eq "checkout"}
	<input type="checkbox" name="use_same_do_date" value="1" {if $form.use_same_do_date}checked{/if} onclick="DO_MULTI_CONFIRM_CHECKOUT.use_same_do_date_clicked();" /> <b>Use Same DO Date</b>&nbsp;&nbsp;
	<span id="span_use_same_do_date" {if !$form.use_same_do_date}style="display:none;"{/if}>
		<input type="text" name="same_do_date" id="same_do_date" value="{if $form.same_do_date}{$form.same_do_date}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}" size="12" /><img align="absmiddle" src="ui/calendar.gif" id="t_same_do_date" style="cursor: pointer;" title="Select Date">
	</span>
	<b>Shipment Method</b> &nbsp;&nbsp;<input type="text" name="shipment_method" value="{$form.shipment_method|escape:'html'}" size="20" onchange="uc(this);" />&nbsp;&nbsp;&nbsp;
	<b>Tracking Code</b> &nbsp;&nbsp;<input type="text" name="tracking_code" value="{$form.tracking_code|escape:'html'}" size="25" onchange="uc(this);" /><br /><br />
	<b>Lorry No.</b> &nbsp;<input type="text" name="checkout_info[lorry_no]" value="{$form.checkout_info.lorry_no|escape:'html'}" size="12" onchange="uc(this);" /> <button id="load_di_btn" onclick="DO_MULTI_CONFIRM_CHECKOUT.load_driver_info();" title="Load Last Driver Info"><img src="ui/icons/lorry_go.png" align="absmiddle"></button>&nbsp;&nbsp;&nbsp;
	<b>Driver Name</b> &nbsp;&nbsp;<input type="text" name="checkout_info[name]" value="{$form.checkout_info.name|escape:'html'}" size="40" onchange="uc(this);" />&nbsp;&nbsp;&nbsp;
	<b>IC No.</b> &nbsp;&nbsp;<input type="text" name="checkout_info[nric]" value="{$form.checkout_info.nric|escape:'html'}" size="15" onchange="uc(this);" /><br /><br />
	<div style="vertical-align:top;"><b>Additional Remark</b></div>
	<textarea style="border:1px solid #000;width:50%;height:100px;" name="checkout_remark">{$form.checkout_remark|escape}</textarea>
	<br /><br />
{/if}
{assign var=colspan value=8}
<table width="100%" class="report_table" id="tbl_do_list">
	<tr class="header">
		<th><input type="checkbox" onclick="DO_MULTI_CONFIRM_CHECKOUT.check_all_do(this);" value="1" /> </th>
		<th class="sortable_col">DO No.</th>
		<th class="sortable_col">Inv No.</th>
		{if !$form.do_type}
			<th class="sortable_col">DO Type</th>
			{assign var=colspan value=$colspan+1}
		{/if}
		<th class="sortable_col">PO No.</th>
		<th class="sortable_col">Deliver To</th>
		<th class="sortable_col">
			Amount
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}({$config.arms_currency.symbol})</th>
				<th class="sortable_col">Foreign Amount
				{assign var=colspan value=$colspan+1}
			{/if}
		</th>
		{if $config.do_transfer_have_discount || $config.do_cash_sales_have_discount || $config.do_credit_sales_have_discount}
			<th class="sortable_col">
				Invoice Amount
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}({$config.arms_currency.symbol})</th>
					<th width="100">Foreign Invoice Amount
					{assign var=colspan value=$colspan+1}
				{/if}
			</th>
			{assign var=colspan value=$colspan+1}
		{/if}
		<th class="sortable_col">DO Date</th>
		<th class="sortable_col">Create By</th>
	</tr>
	{foreach from=$form.do_list key=dummy name=do item=do}
		{assign var=do_id value=$do.id}
		{assign var=bid value=$do.branch_id}
		<tr id="tr_item-{$do.id}-{$do.branch_id}" class="tr_item {if $do_err_list.$bid.$do_id}highlight_row{/if}">
			<td width="50" nowrap>
				<input type="checkbox" name="chk_do_list[{$do.branch_id}][{$do.id}]" class="do_checkbox" onclick="DO_MULTI_CONFIRM_CHECKOUT.do_checkbox_clicked({$do.id}, {$do.branch_id});" />
				{$smarty.foreach.do.iteration}.
			</td>
			<td><a href="/do.php?a=view&branch_id={$do.branch_id}&id={$do.id}" target="_blank">
				{if $do.approved}
					{if $do.do_no}
						{$do.do_no}
					{else}
						{$do.branch_prefix}{$do.id|string_format:"%05d"}(DD)
					{/if}
				{elseif $do.status<1}
					{if $do.do_no}
						{$do.do_no}
					{else}
						{$do.branch_prefix}{$do.id|string_format:"%05d"}(DD)
					{/if}
				{elseif $do.status eq '1'}
					{$do.branch_prefix}{$do.id|string_format:"%05d"}(PD)
				{elseif $do.status>1}
					{$do.branch_prefix}{$do.id|string_format:"%05d"}(PD)
				{/if}
				</a>
				{if preg_match('/\d/',$do.approvals)}
					<div class="small">Approvals: <font color=#0000ff>{get_user_list list=$do.approvals}</font></div>
				{/if}
			</td>
			<td align="center" nowrap>{$do.inv_no}</td>
			
			{* show do type when user does not filter do type *}
			{if !$form.do_type}
				{assign var=do_type value=$do.do_type}
				<td align="center">{$do_type_list.$do_type}</td>
			{/if}
			
			<td align="center">{$do.po_no|default:"-"}</td>
			<td>			
				{if $do.do_type eq 'credit_sales'}
					{assign var=debtor_id value=$do.debtor_id}
					Debtor: {$debtor_list.$debtor_id.code}
					{if $debtor_list.$debtor_id.description}
						<br />
						<span class="small" style="color:blue;">({$debtor_list.$debtor_id.description})</span>
					{/if}
				{else}
					{if $do.do_branch_id}
						{$do.db_name}
					{elseif $do.open_info.name}
						{$do.open_info.name}
					{/if}
					{foreach from=$do.d_branch.name item=pn name=pn}
						{if $smarty.foreach.pn.iteration>1} ,{/if}
						{$pn}
					{/foreach}
				{/if}
				
			</td>
			
			<td align="right">
				{if $do.amt_need_update}
					<img src="/ui/messages.gif" align="absmiddle" title="Please open and save again to correct the amount." />
				{/if}
				{$do.total_amount|number_format:2}
			</td>
			
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
				<td align="right">{$do.total_foreign_amount|number_format:2}</td>
			{/if}
			
			{if $config.do_transfer_have_discount || $config.do_cash_sales_have_discount || $config.do_credit_sales_have_discount}
				<td align="right">{$do.total_inv_amt|number_format:2}</td>
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
					<td align="right">{$do.total_foreign_inv_amt|number_format:2}</td>
				{/if}
			{/if}
			<td align="center">
				{if $form.process_type eq "checkout"}
					<span class="span_do_date_list" {if $form.use_same_do_date}style="display:none;"{/if}>
						<input type="text" name="do_date[{$do.branch_id}][{$do.id}]" id="do_date_{$do.branch_id}_{$do.id}" value="{if $do.first_checkout_date || $do_err_list.$bid.$do_id}{$do.do_date}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}" size="12" /><img align="absmiddle" src="ui/calendar.gif" id="t_do_date_{$do.branch_id}_{$do.id}" style="cursor: pointer;" title="Select Date">
					</span>
					
					<span class="span_use_above_do_date" {if !$form.use_same_do_date}style="display:none;"{/if}>&#60;&#60; Follow Above DO Date &#62;&#62;</span>
					
					{* do date calendar selection *}
					{literal}
					<script>
						function init_calendar(){
							Calendar.setup({
								inputField     :    'do_date_{/literal}{$do.branch_id}_{$do.id}{literal}',
								ifFormat       :    "%Y-%m-%d",
								button         :    "t_do_date_{/literal}{$do.branch_id}_{$do.id}{literal}",
								align          :    "Bl",
								singleClick    :    true
							});   
						}
						
						init_calendar();
					</script>
					{/literal}
				{else}
					{$do.display_do_date|date_format:"%Y-%m-%d"}
				{/if}
			</td>
			<td>{$do.user_name}</td>
		</tr>
		{if $do_err_list.$bid.$do_id}
			<tr {if $do_err_list.$bid.$do_id}class="highlight_row"{/if}>
				<td colspan="{$colspan}">
					{if $do_err_list.$bid.$do_id}
						<div class="errmsg">Error:</div>
						<ul style="list-style-type:none; spacing:0; padding:0;padding-top:5px;">
							{foreach from=$do_err_list.$bid.$do_id key=dummy item=e name=err_list}
								<li><font color="red">- {$e}</font></li>
							{/foreach}
						</ul>
					{/if}
				</td>
			</tr>
		{/if}
	{foreachelse}
		<tr>
			<td colspan="{$colspan}" align="center">-- No Saved DO Found --</td>
		</tr>
	{/foreach}
</table>

{if count($form.do_list) > 0}
	<div style="position:fixed;bottom:0;background:#ddd;width:100%;text-align:center;left:0;padding:3px;">
		<input style="margin: 5px 0" type="button" class="btn btn-primary" name="submit" value="{if $form.process_type eq 'checkout'}Checkout{else}Confirm{/if}" onclick="DO_MULTI_CONFIRM_CHECKOUT.confirm_checkout_clicked();" />
	</div>
	<input type="hidden" name="do_type" value="{$form.do_type}" />
	<input type="hidden" name="deliver_to" value="{$form.deliver_to}" />
	<input type="hidden" name="debtor_id" value="{$form.debtor_id}" />
	
	{if $form.process_type eq "checkout"}
		{* do date calendar selection *}
		{literal}
		<script>
			function init_calendar(){
				Calendar.setup({
					inputField     :    'same_do_date',
					ifFormat       :    "%Y-%m-%d",
					button         :    "t_same_do_date",
					align          :    "Bl",
					singleClick    :    true
				});   
			}
			
			init_calendar();
		</script>
		{/literal}
	{/if}
{/if}