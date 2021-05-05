{*
8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in an abnormal way when containing special characters
*}

{include file="header.tpl"}
<script type="text/javascript">
var phpself = "{$smarty.server.PHP_SELF}";
var can_edit = int("{$can_edit}");
var branch_code = "{$smarty.const.BRANCH_CODE}";

{literal}
var CUSTOM_ACC_AND_GST_SETTING = {
	initialize: function(){
		this.f_a = document.f_a;
		var THIS = this;
	},
	save: function(){
		if(!this.check_form()){
			return false;
		}
		this.f_a['a'].value = 'save';
        this.f_a.submit();
	},
	check_form: function(){
		var use_own = false;
		if (branch_code != 'HQ'){
			use_own = document.getElementById('use_own_branch').value;
		}
		if (use_own == true || branch_code == 'HQ'){
			var inputs_acc_name = document.getElementsByClassName("inputs_acc_name");
			var inputs_acc_code = document.getElementsByClassName("inputs_acc_code");
			var inputs_desc = document.getElementsByClassName("inputs_desc");
			
			for(i = 0; i < inputs_acc_name.length; i++) 
			{
				var acc_name = inputs_acc_name[i];
				var acc_code = inputs_acc_code[i];
				
				if (!acc_name.value){
					if (acc_code.value){
						acc_name.focus();
						alert("Account Name " + inputs_desc[i].innerHTML + " Cannot Be Empty");
						return false;
					}
				}
				
				if (!acc_code.value){
					if (acc_name.value){
						acc_code.focus();
						alert("Account Code " + inputs_desc[i].innerHTML + " Cannot Be Empty");
						return false;
					}
				}
				
				if (acc_name.value.length > 100){
					acc_name.focus();
					alert("Account Name " + inputs_desc[i].innerHTML + " Cannot More Than 100 Characters");
					return false;
				}
				
				if (acc_code.value.length > 50){
					acc_code.focus();
					alert("Account Code " + inputs_desc[i].innerHTML + " Cannot More Than 50 Characters");
					return false;
				}
			}
			
			var inputs_gst_acc_name = document.getElementsByClassName("inputs_gst_acc_name");
			var inputs_gst_acc_code = document.getElementsByClassName("inputs_gst_acc_code");
			var inputs_gst_desc = document.getElementsByClassName("inputs_gst_desc");
			
			for(i = 0; i < inputs_gst_acc_name.length; i++) 
			{
				var gst_acc_name = inputs_gst_acc_name[i];
				var gst_acc_code = inputs_gst_acc_code[i];
				
				if (!gst_acc_name.value){
					if (gst_acc_code.value){
						gst_acc_name.focus();
						alert("GST Account Name " + inputs_gst_desc[i].innerHTML + " Cannot Be Empty");
						return false;
					}
				}
				
				if (!gst_acc_code.value){
					if (gst_acc_name.value){
						gst_acc_code.focus();
						alert("GST Account Code " + inputs_gst_desc[i].innerHTML + " Cannot Be Empty");
						return false;
					}
				}
				
				if (gst_acc_name.value.length > 100){
					gst_acc_name.focus();
					alert("GST Account Name " + inputs_gst_desc[i].innerHTML + " Cannot More Than 100 Characters");
					return false;
				}
				
				if (gst_acc_code.value.length > 50){
					gst_acc_code.focus();
					alert("GST Account Code " + inputs_gst_desc[i].innerHTML + " Cannot More Than 50 Characters");
					return false;
				}
			}
		}
		return true;
	},
	toggle_branch_setting: function(){
		if (branch_code != 'HQ'){
			var use_own = document.getElementById('use_own_branch').value;
			
			if (use_own == true){
				document.getElementById('data-form-details').style.display = "inline";
			}else{
				document.getElementById('data-form-details').style.display = "none";
			}
		}
	}
};
{/literal}
</script>
<p style="color:red">{$status}</p>
<h1>Custom Account Setting</h1>
<form name="f_a"  method="post">
    <input type="hidden" name="a" value="save">
	{if $smarty.const.BRANCH_CODE!='HQ'}
		<b>Use Own Branch Settings</b>
		<select name="use_own_branch" onchange="CUSTOM_ACC_AND_GST_SETTING.toggle_branch_setting()" id="use_own_branch">
			<option value="0" {if !$use_own_branch}selected{/if}>No</option>
			<option value="1" {if $use_own_branch}selected{/if}>Yes</option>
		</select>
		<br/><br/>
	{/if}
	<div id="data-form-details" {if $smarty.const.BRANCH_CODE!="HQ" && !$use_own_branch}style="display:none;"{/if}>
		<p style="color:blue">Note: <br/>
			- Account Code only can accept maximum 50 characters.<br/>
			- Account Name only can accept maximum 100 characters.
		</p>
		<table class="table" cellpadding="5" border="1">
			<tr>
				<th>Description</th>
				<th>Account Code</th>
				<th>Account Name</th>
				<th>Description</th>
				<th>Account Code</th>
				<th>Account Name</th>
			</tr>
			<tbody>
				{foreach from=$acc_list key=k item=item name=acc_list}
					{if $smarty.foreach.acc_list.iteration %2 != 0}
						<tr>
					{/if}
					<td class="inputs_desc">{$item}</td>
					<td><input type="text" class="inputs_acc_code" name="data[acc][{$k}][account_code]" value="{$acc_setting[$k].account_code|escape:'html'}" maxlength="50"/></td>
					<td><input type="text" class="inputs_acc_name" name="data[acc][{$k}][account_name]" value="{$acc_setting[$k].account_name|escape:'html'}" maxlength="100"/></td>
					{if $smarty.foreach.acc_list.iteration % 2 == 0}
						</tr>
					{elseif $smarty.foreach.acc_list.iteration % 2 != 0 && $smarty.foreach.acc_list.last == $smarty.foreach.acc_list.iteration}
						<td></td>
						<td></td>
						<td></td>
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>

		<br />

		<h1>Custom Account GST Setting</h1>
		<p style="color:blue">Note: <br/>
			- GST Account Code only can accept maximum 50 characters.<br/>
			- GST Account Name only can accept maximum 100 characters.
		</p>
		<table class="table" cellpadding="5" border="1">
			<tr>
				<th>Description</th>
				<th>Account Code</th>
				<th>Account Name</th>
				<th>Description</th>
				<th>Account Code</th>
				<th>Account Name</th>
			</tr>
			<tbody>
				{foreach from=$gst_list key=k item=item name=gst_list}
					{if $smarty.foreach.gst_list.iteration %2 != 0}
						<tr>
					{/if}
					<td class="inputs_gst_desc">{$item.code} @{$item.rate}%</td>
					<td><input type="text" class="inputs_gst_acc_code" name="data[gst][{$item.id}][account_code]" value="{$gst_setting[$item.id].account_code|escape:'html'}" maxlength="50"/></td>
					<td><input type="text" class="inputs_gst_acc_name" name="data[gst][{$item.id}][account_name]" value="{$gst_setting[$item.id].account_name|escape:'html'}" maxlength="100"/></td>
					{if $smarty.foreach.gst_list.iteration % 2 == 0}
						</tr>
					{elseif $smarty.foreach.gst_list.iteration % 2 != 0 && $smarty.foreach.gst_list.last == $smarty.foreach.gst_list.iteration}
						<td></td>
						<td></td>
						<td></td>
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</form>

<div id="saving" style="position:fixed;bottom:0;background:#ddd;width:100%;text-align:center;left:0;padding:3px;opacity:0.9;">
	<input id="btnSave" name="btnSave" type=button value="Save" onclick="CUSTOM_ACC_AND_GST_SETTING.save();">
</div>
{include file='footer.tpl'}

<script type="text/javascript">
{literal}
	CUSTOM_ACC_AND_GST_SETTING.initialize();
{/literal}
</script>
