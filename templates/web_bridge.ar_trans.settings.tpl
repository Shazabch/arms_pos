{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function submit_form(){
	var inp_list = $(document.f_a).getElementsBySelector(".ar_settings");
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
		<!-- Posting Account Code -->
		<tr>
			<td><b>Posting Account Code (Transfer DO)</b></td>
			<td>
				<input type="hidden" name="ar_settings[posting_account_code_transfer][name]" value="posting_account_code_transfer" />
				<input type="hidden" name="ar_settings[posting_account_code_transfer][type]" value="str" />
				<input type="text" name="ar_settings[posting_account_code_transfer][value]" value="{$form.posting_account_code_transfer.value}" class="ar_settings" title="Posting Account Code (Transfer DO)" />
			</td>
		</tr>
		<tr>
			<td><b>Posting Account Code (Cash Sales DO)</b></td>
			<td>
				<input type="hidden" name="ar_settings[posting_account_code_open][name]" value="posting_account_code_open" />
				<input type="hidden" name="ar_settings[posting_account_code_open][type]" value="str" />
				<input type="text" name="ar_settings[posting_account_code_open][value]" value="{$form.posting_account_code_open.value}" class="ar_settings" title="Posting Account Code (Cash Sales DO)" />
			</td>
		</tr>
		<tr>
			<td><b>Posting Account Code (Credit Sales DO)</b></td>
			<td>
				<input type="hidden" name="ar_settings[posting_account_code_credit_sales][name]" value="posting_account_code_credit_sales" />
				<input type="hidden" name="ar_settings[posting_account_code_credit_sales][type]" value="str" />
				<input type="text" name="ar_settings[posting_account_code_credit_sales][value]" value="{$form.posting_account_code_credit_sales.value}" class="ar_settings" title="Posting Account Code (Credit Sales DO)" />
			</td>
		</tr>
		
		<!-- Project Code -->
		<tr>
			<td><b>Project Code</b></td>
			<td>
				<input type="hidden" name="ar_settings[project_code][name]" value="project_code" />
				<input type="hidden" name="ar_settings[project_code][type]" value="select" />
				<select name="ar_settings[project_code][value]">
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