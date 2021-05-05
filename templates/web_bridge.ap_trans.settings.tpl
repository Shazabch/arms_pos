{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function submit_form(){
	var inp_list = $(document.f_a).getElementsBySelector(".ap_settings");
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
			<td><b>Posting Account Code</b></td>
			<td>
				<input type="hidden" name="ap_settings[posting_account_code][name]" value="posting_account_code" />
				<input type="hidden" name="ap_settings[posting_account_code][type]" value="str" />
				<input type="text" name="ap_settings[posting_account_code][value]" value="{$form.posting_account_code.value}" class="ap_settings" title="Posting Account Code" />
			</td>
		</tr>
		
		<!-- Project Code -->
		<tr>
			<td><b>Project Code</b></td>
			<td>
				<input type="hidden" name="ap_settings[project_code][name]" value="project_code" />
				<input type="hidden" name="ap_settings[project_code][type]" value="select" />
				<select name="ap_settings[project_code][value]">
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