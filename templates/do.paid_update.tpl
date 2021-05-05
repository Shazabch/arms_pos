{*
8/5/2020 9:33 AM William
- Enhanced to change payment type input to selection.

8/10/2020 2:13 PM William
- Remove "-- Please Select --" option.
*}
{literal}
<style>
.calendar{
	z-index: 10001 !important;
}
</style>
{/literal}

<script>
{literal}

var PAID_UPDATE ={
	initialise: function(){
		PAID_UPDATE.checkbox_paid_onchange();
		PAID_UPDATE.init_calendar();
	},
	checkbox_paid_onchange: function(){
		if(document.f_paid['paid'].checked == true){
			$('span_payment_date').show();
			document.f_paid['payment_date'].disabled = false;
			document.f_paid['payment_type'].disabled = false;
			document.f_paid['payment_remark'].disabled = false;
		}else{
			$('span_payment_date').hide();
			document.f_paid['payment_date'].disabled = true;
			document.f_paid['payment_type'].disabled = true;
			document.f_paid['payment_remark'].disabled = true;
		}
	},
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "payment_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "p_added1",
			align          :    "Bl",
			singleClick    :    true
		});
	},
}
{/literal}
</script>
<form name="f_paid" method="post">
	<input type="hidden" name="a" value="ajax_update_paid">
	<input type="hidden" value="{$form.branch_id}" name="bid" />
	<input type="hidden" value="{$form.id}" name="id" />
	
	<h2>Update Paid Status</h2>
	<label><input name="paid" type="checkbox" value="1" onchange="PAID_UPDATE.checkbox_paid_onchange()" {if $form.paid}checked{/if}/><b>  Paid</b></label>
	<hr>
	<table>
		<tr>
			<td><b>Payment Date </b></td>
			<td>
				<input name="payment_date" id="payment_date" size="12"  value="{$form.payment_date}">
				<span id="span_payment_date"><img  align="absmiddle" src="ui/calendar.gif" id="p_added1" style="cursor: pointer;" title="Select Date"></span>
			</td>
		</tr>
		<tr>
			<td><b>Payment Type </b></td>
			<td>
				<select name="payment_type">
				{foreach from=$payment_type item=desc}
					<option value="{$desc}" {if $form.payment_type eq $desc}selected {/if}>{$desc}</option>
				{/foreach}
				{if $form.payment_type neq '' && !$form.payment_type|in_array:$payment_type}
				<option value="{$form.payment_type}" selected>{$form.payment_type}</option>
				{/if}
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top"><b>Remark </b></td>
			<td><textarea rows="5" cols="30" name="payment_remark">{$form.payment_remark|escape}</textarea></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input id="btn_upd_paid" type="button" value="Update" onclick="update_paid_status()">
				<input type="button" value="Cancel" onclick="curtain_clicked()">
			</td>
		</tr>
	</table>
</form>

<script>
{literal}
PAID_UPDATE.initialise();
{/literal}
</script>