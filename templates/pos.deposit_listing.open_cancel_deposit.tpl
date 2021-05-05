{*
3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

<h2>Please choose cancellation method</h2>

<form name="f_cancel_deposit" onSubmit="return false;">
	<input type="hidden" name="bid" value="{$deposit.branch_id}" />
	<input type="hidden" name="date" value="{$deposit.date}" />
	<input type="hidden" name="counter_id" value="{$deposit.counter_id}" />
	<input type="hidden" name="pos_id" value="{$deposit.pos_id}" />
	
	<div class="stdframe" style="background-color:#fff;">
		<input type="radio" name="cancel_type" value="1" checked />
		<b>Method 1: </b><br />
		- Direct cancel receipt for <b>{$deposit.date}</b>, required counter collection ({$deposit.date}) in un-finalise status.
	</div>
	
	<br />
	
	<div class="stdframe" style="background-color:#fff;">
		<input type="radio" name="cancel_type" value="2" />
		<b>Method 2: </b><br />
		- Generate new receipt at other date, the selected date counter collection must in un-finalise status.
		
		<table>
			<tr>
				<td>Cancellation date:</td>
				<td>
					<input id="inp_cancel_date" name="cancel_date" size="10" value="{$smarty.now|date_format:"%Y-%m-%d"}" /> 
					<img align="absbottom" src="ui/calendar.gif" id="img_cancel_date" style="cursor: pointer;" title="Select Date" />
				
				</td>
			</tr>
			
			<tr>
				<td>Please select counter:</td>
				<td>
					<select name="cancel_counter_id">
						<option value="">-- Please Select --</option>
						{foreach from=$counter_list item=r}
							<option value="{$r.id}">{$r.network_name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		</table>
	</div>
	
	<p align="center" id="p_cancel_action">
		<input type="button" value="Confirm Cancel Deposit" onClick="DEPOSIT_LISTING.confirm_cancel_deposit();" />
		<input type="button" value="Close" onClick="default_curtain_clicked();" />
		<br />
		<span id="span_processing_cancel_deposit" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</p>
	
</form>
