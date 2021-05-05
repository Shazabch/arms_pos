{*

4/21/2017 10:28 AM Khausalya 
- Enhanced changes from RM to use config setting. 

1/26/2018 2:26 PM Justin
- Bug fixed on no option for user to setup new value if the user is first time using voucher.

2/7/2018 4:29 PM Justin
- Bug fixed on Voucher Setup module always show voucher prefix from config even after modifying the list and save.

10/14/2019 11:42 AM William
- Bug fix voucher value limit maximum value as 999.99

06/29/2020 10:34 AM Sheila
- Updated button css.
*}


{include file=header.tpl}
{literal}
<style>
</style>
{/literal}
<script>
var currency_symbol = '{$config.arms_currency.symbol}';
{literal}
function myCreateFunction(divName){
	divName.up('tr').insertAdjacentHTML('afterend', "<tr><td><img title=\"Add\" src=\"ui/icons/add.png\" align=\"absmiddle\" onclick=\"myCreateFunction(this);\"><input size=\"10\" name=\"mytext[]\" type=\"text\"/><img title=\"Delete\" src=\"ui/icons/delete.png\" align=\"absmiddle\"  onclick=\"removeText(this)\"></td></tr>");
}
		
function removeText(obj){
	if (confirm("Are you sure?")){
		var myControls = document.forms["f_a"].elements["mytext[]"];

		if (typeof myControls.length == "undefined")
			alert("This is the last one. Cannot be deleted.");
		else
			obj.up('tr').remove();
	}
}

function check_form(){
	var myControls = document.forms["f_a"].elements["mytext[]"];
	var decimalOnly = /^\s*-?[0-9]\d*(\.\d{1,2})?\s*$/;
	var valid = true;
	
	if (typeof myControls.length == "undefined"){
		if (!decimalOnly.test(myControls.value) || myControls.value==0)
			valid = false;
	}
	for (var i = 0; i < myControls.length; i++) {
		if (myControls[i].value == null || myControls[i].value == ""){
			valid = false;
			break;
		}else{
			if (!decimalOnly.test(myControls[i].value) || myControls[i].value==0){
				valid = false;
				break;
			}
		}
		if(myControls[i].value >= 1000){
			alert("Voucher maximum value is 999.99");
			return false;
		}
	}

	if (!valid) {
        alert("Value ("+currency_symbol+") must be filled out. Eg: 10.99");
        return false;
    }else{
		if (confirm("Are you sure you want to save?")){
			return true;
		}else{
			return false;
		}
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li> {$e}</li>
	    {/foreach}
	</ul>
{/if}
<p>
<form name="f_a" method="post">
	<div class="stdframe">
		<table>
			<tr>
				<th>Value ({$config.arms_currency.symbol})</th>
			</tr>
			{if $voucher}
				{foreach from=$voucher item=value}
					<tr>
						<td><img title="Add" src="ui/icons/add.png" align="absmiddle" onclick="myCreateFunction(this);"><input size="10" name="mytext[]" type="text" value="{$value}"/><img title="Delete" src="ui/icons/delete.png" align="absmiddle" onclick="removeText(this);"></td>
					</tr>
				{/foreach}
			{elseif $config.voucher_value_prefix}
				{foreach from=$config.voucher_value_prefix item=value}
					<tr>
						<td><img title="Add" src="ui/icons/add.png" align="absmiddle" onclick="myCreateFunction(this);"><input size="10" name="mytext[]" type="text" value="{$value}"/><img title="Delete" src="ui/icons/delete.png" align="absmiddle" onclick="removeText(this);"></td>
					</tr>
				{/foreach}
			{else}
				<tr>
					<td><img title="Add" src="ui/icons/add.png" align="absmiddle" onclick="myCreateFunction(this);"><input size="10" name="mytext[]" type="text" value=""/><img title="Delete" src="ui/icons/delete.png" align="absmiddle" onclick="removeText(this);"></td>
				</tr>
			{/if}
		</table>
	
    </div>
    <p>
		<button class="btn btn-success" name=a value="save_form" onclick="return check_form();">Save</button>
	</p>
</form>
</p>
{include file=footer.tpl}
