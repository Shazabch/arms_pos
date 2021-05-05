{*
11/22/2016 5:32 PM Andy
- Fixed "Maximum Points Use" got warning message.

10/18/2019 2:24 PM William
- Fixed config "voucher_show_advanced_options" display when config is not activated.
- Hide column "Points Accumulated" cause no data store for calculate "Points Accumulated".

06/29/2020 11:05 AM Sheila
- Updated button css.
*}

{include file="header.tpl"}

<style>

{literal}
.negative{
	color:red;
}
{/literal}
</style>

<script type="text/javascript">

{literal}
function cancel_his(){
	if(!confirm('Are you sure? This will revert back the points to member'))	return false;
	
	document.f_a['a'].value = 'cancel_his';
	document.f_a.submit();
}
{/literal}
</script>


<h1>{$PAGE_TITLE} History - #{$form.id}</h1>

{if $smarty.request.just_done}
	<p style="background-color:#89ff87;padding:5px;">
		<img src="ui/info.png" align="absmiddle" /> Voucher Auto Redemption was successfully generate and activated. Please manually print the Voucher using "Print Panel".
	</p>
{/if}

{if $err}
	<ul style="color:red;">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{assign var=print_format value=$form.form_settings.print_format}

<form name="f_a" style="display:none;" method="post">
	<input type="hidden" name="a" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="his_id" value="{$form.id}" />
</form>

<div class="stdframe">	
	<table width="100%">
		<tr>
			<td colspan="2">
				<h4>Voucher Settings</h4>
				<table class="report_table" style="background-color:white;">
					<tr class="header">
						<th>Voucher Value</th>
						<th>Points<br>per Voucher</th>
						<th>Limit</th>
					</tr>
					
					{foreach from=$form.form_settings.voucher_use key=k item=dummy}
						<tr>
							<td class="r">{$form.form_settings.voucher_value.$k}</td>
							<td class="r">{$form.form_settings.points_use.$k|ifzero:'-'}</td>
							<td class="r">{$form.form_settings.max_qty.$k|ifzero:'-'}</td>
						</tr>
					{/foreach}
				</table>
			</td>
			<td align="right" width="50%" valign="top">
				<h4>Print Panel</h4>
				<table class="report_table" style="min-width:50%;">
					<tr class="header">
						<th>&nbsp;</th>
						<th>Batch No</th>
					</tr>
					{foreach from=$form.batch_list item=batch_no}
						<tr>
							<td align="center">
								{if $form.batch_info.$batch_no.data.cancel_status}
									<img src="ui/del.png" align="absmiddle" title="This batch already cancelled." />
								{else}
									{if $config.voucher_member_redeem_print_template.$print_format}
									<a href="masterfile_voucher.php?a=print_voucher&p=&branch_id={$form.branch_id}&batch_no={$batch_no}&from_code={$form.batch_info.$batch_no.from_code}&to_code={$form.batch_info.$batch_no.to_code}&member_redeem_print_format={$print_format}" target="_blank">
										<img src="/ui/print.png" align="absmiddle" title="Print Voucher" border="0" />
									</a>
									{else}
										<img src="ui/messages.gif" align="absmiddle" title="Printing Format Unavailable" />
									{/if}
								{/if}								
							</td>
							<td align="center">
								<a href="masterfile_voucher.php?batch_no={$batch_no}" target="_blank">
									{$batch_no}
								</a>
							</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
		<tr>
			<td><b>Maximum Points Use</b></td>
			<td>{$form.form_settings.max_points_use|default:0|number_format|ifzero:'-'}</td>
		</tr>
		
		<tr>
			<td colspan="2"><h4>Print Settings</h4></td>
		</tr>
		<tr>
			<td><b>Code Start at</b></td>
			<td>{$form.form_settings.code_start}</td>
		</tr>
		
		<tr>
			<td><b>Format</b></td>
			<td>
				
				{if $config.voucher_member_redeem_print_template.$print_format}
					{$config.voucher_member_redeem_print_template.$print_format.description}
				{else}
					<span style="color:red;">Cannot find Printing Format '{$print_format}'.</span>
				{/if}
			</td>
		</tr>
		
		<tr>
			<td colspan="2"><h4>Activation Settings</h4></td>
		</tr>
		
		<tr>
			<td><b>Interbranch</b></td>
			<td>
				{foreach from=$form.form_settings.interbranch item=bid name=fbranch}
					{if !$smarty.foreach.fbranch.first}, {/if}
					{$branches.$bid.code|default:'Branch In-activated'}
				{/foreach}
			</td>
		</tr>
		{if $config.voucher_show_advanced_options}
		<tr>
			<td valign="top"><b>More Options</b></td>
			<td>
				<input type="checkbox" {if $form.form_settings.disallow_disc_promo}checked {/if} disabled /> Disallow to use with discounts/promotions 
				<br />
				<input type="checkbox" {if $form.form_settings.disallow_other_voucher}checked {/if} disabled /> Disallow to use with other vouchers 
			</td>
		</tr>
		{/if}
		<tr>
			<td colspan="2"><h4>Batch Settings</h4></td>
		</tr>
		
		<tr>
			<td colspan="2">
				<table class="report_table" style="background-color:white;">
					<tr class="header">
						<th>Batch</th>
						<th>Date From</th>
						<th>Date To</th>
					</tr>
					<tbody id="tbody_batch_list">
						{foreach from=$form.form_settings.batch_date_from key=k item=batch_date_from name=f_batch}
							{assign var=batch_date_to value=$form.form_settings.batch_date_to.$k}
							<tr>
								<td class="r" nowrap="nowrap">
									{$smarty.foreach.f_batch.iteration}.
								</td>
								<td nowrap="nowrap">
									{$batch_date_from}
								</td>
								<td nowrap="nowrap">
									{$batch_date_to}
								</td>
							</tr>
						{/foreach}
					</tbody>					
				</table>
			</td>
		</tr>
	</table>
</div>

<h2>Member Points Summary</h2>
<div class="stdframe">
	<table class="report_table" width="100%" style="background-color:white;">
		<tr class="header">
			<th rowspan="2">No.</th>
			<th rowspan="2">NRIC</th>
			<th rowspan="2">Card No</th>
			{*<th rowspan="2">Points Accumulated</th>*}
			<th rowspan="2">Points Used</th>
			<th rowspan="2">Points Left</th>
			<th colspan="{count var=$form.form_settings.voucher_use offset=1}">Voucher Value</th>
		</tr>
		<tr class="header">
			{foreach from=$form.form_settings.voucher_use key=k item=dummy}
				<th>{$form.form_settings.voucher_value.$k}</th>
			{/foreach}
			<th>Total</th>
		</tr>
		{assign var=total_point_before value=0}
		{foreach from=$form.member_info key=nric item=m name="fmem"}
			<tr>
				<td>{$smarty.foreach.fmem.iteration}.</td>
				<td>
					<a href="membership.php?a=i&t=history&nric={$nric}" target="_blank">
						{$nric}
					</a>
				</td>
				<td>{$m.card_no}</td>
				{*<td class="r">{$m.points_before|number_format}</td>*}
				<td class="r {if $m.points<0}negative{/if}">{$m.points|number_format}</td>
				<td class="r">{$m.points_left|number_format}</td>
				
				{foreach from=$form.form_settings.voucher_use key=k item=dummy}
					{assign var=voucher_value value=$form.form_settings.voucher_value.$k}
					<td align="center">{$m.voucher_data.$voucher_value|ifzero:'&nbsp;'}</td>
				{/foreach}
				<td align="center">{$m.total_voucher_get|ifzero:'&nbsp;'}</td>
			</tr>
			
			{assign var=total_point_before value=$total_point_before+$m.points_before}
		{/foreach}
		<tr class="header">
			<td class="r" colspan="3"><b>Total</b></td>
			{*<td class="r"><b>{$total_point_before|number_format}</b></td>*}
			<td class="r {if $form.total_points_used*-1<0}negative{/if}"><b>{$form.total_points_used*-1|number_format}</b></td>
			
			{assign var=total_point_left value=$total_point_before-$form.total_points_used}
			<td class="r"><b>{$total_point_left|number_format}</b></td>
			
			{foreach from=$form.form_settings.voucher_use key=k item=dummy}
				{assign var=voucher_value value=$form.form_settings.voucher_value.$k}
				<td align="center">{$form.total_data.voucher_data.$voucher_value|ifzero:'&nbsp;'}</td>
			{/foreach}
			<td align="center">{$form.total_data.total_voucher_get|ifzero:'&nbsp;'}</td>
		</tr>
	</table>
</div>

<p align="center">
	<input class="btn btn-warning" type="button" value="Cancel" onclick="cancel_his();" />
	<input class="btn btn-error" type="button" value="Close" onclick="document.location='{$smarty.server.PHP_SELF}?a=his_list'" />
</p>
{include file="footer.tpl"}