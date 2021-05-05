{*
10/17/2013 5:54 PM Justin
- Enhanced to load custom payment type.
- Enhanced the payment types to load from PHP instead of hardcoded.
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function submit_form(){
	var inp_list = $(document.f_a).getElementsBySelector(".cc_settings");
	for(var i=0; i<inp_list.length; i++){
		if(inp_list[i].value.trim()==''){
			alert("Please key in "+inp_list[i].title);
			inp_list[i].focus();
			return false;
		}
	}
	
	$('btn_update').disabled = true;
	var params = $(document.f_a).serialize();
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg == 'OK'){
				alert('Update Successfully');
			}else{
				alert(msg);
			}
			$('btn_update').disabled = false;
		}
	});
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<form name="f_a" method="post" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="a" value="save_settings">
	
	<table>
		<tr>
			<td colspan="2">
				<table border="0.5" class="tb" cellspacing="0" cellpadding="2">
					<tr>
						<th>Payment Type</th>
						<th>Payment Account Code</th>
						<th>Customer Code</th>
					</tr>
					
					{foreach from=$payment_type key=val item=desc}
						{assign var=payment_code value=payment_code_$val}
						{assign var=customer_code value=customer_code_$val}
						<tr>
							<td>{$desc}</td>
							<td>
								<input type="hidden" name="cc_settings[{$payment_code}][name]" value="{$payment_code}" />
								<input type="hidden" name="cc_settings[{$payment_code}][type]" value="str" />
								<input type="text" name="cc_settings[{$payment_code}][value]" value="{$form.$payment_code.value}" class="cc_settings" title="Payment Account Code ({$desc})" />
							</td>
							<td>
								<input type="hidden" name="cc_settings[{$customer_code}][name]" value="{$customer_code}" />
								<input type="hidden" name="cc_settings[{$customer_code}][type]" value="str" />
								<input type="text" name="cc_settings[{$customer_code}][value]" value="{$form.$customer_code.value}" class="cc_settings" title="Customer Account Code ({$desc})" />
							</td>
						</tr>
					{/foreach}
					
					<tr>
						<th colspan="3">Details Payment Type (If split credit card type)</th>
					</tr>
					
					{foreach from=$cc_type key=val item=desc}
						{assign var=payment_code value=payment_code_$val}
						{assign var=customer_code value=customer_code_$val}
						<tr>
							<td>{$desc}</td>
							<td>
								<input type="hidden" name="cc_settings[{$payment_code}][name]" value="{$payment_code}" />
								<input type="hidden" name="cc_settings[{$payment_code}][type]" value="str" />
								<input type="text" name="cc_settings[{$payment_code}][value]" value="{$form.$payment_code.value}" class="cc_settings" title="Payment Account Code ({$desc})" />
							</td>
							<td>
								<input type="hidden" name="cc_settings[{$customer_code}][name]" value="{$customer_code}" />
								<input type="hidden" name="cc_settings[{$customer_code}][type]" value="str" />
								<input type="text" name="cc_settings[{$customer_code}][value]" value="{$form.$customer_code.value}" class="cc_settings" title="Customer Account Code ({$desc})" />
							</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
		
		<!-- Project Code -->
		<tr>
			<td><b>Project Code</b></td>
			<td>
				<input type="hidden" name="cc_settings[project_code][name]" value="project_code" />
				<input type="hidden" name="cc_settings[project_code][type]" value="select" />
				<select name="cc_settings[project_code][value]">
					<option value="FOLLOW_BRANCH_CODE" {if $form.project_code.value eq 'FOLLOW_BRANCH_CODE'}selected {/if}>Follow Branch Code</option>
					<option value="----" {if $form.project_code.value eq '----'}selected {/if}>----</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="button" value="Update" onClick="submit_form();" id="btn_update" />
			</td>
		</tr>
	</table>
</form>

{include file='footer.tpl'}