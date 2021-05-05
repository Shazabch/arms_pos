{*
*}

{include file='header.tpl'}
<script>
{literal}
function update_attendance_setting(){
	//checking 
	if(document.f_a['in_early'].value =='' || document.f_a['in_early'].value < 0){
		alert("Invalid Early In value.");
		return false;
	}
	if(document.f_a['in_late'].value =='' || document.f_a['in_late'].value < 0){
		alert("Invalid Late In value.");
		return false;
	}
	if(document.f_a['out_early'].value =='' || document.f_a['out_early'].value < 0){
		alert("Invalid Early Exit value");
		return false;
	}
	if(document.f_a['out_late'].value =='' || document.f_a['out_late'].value < 0){
		alert("Invalid Late Exit value.");
		return false;
	}
	if(!confirm('Are you sure?')) return false;
	return true;
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.updated}
	<img src="ui/approved.png" /> Settings Updated.<br /><br />
{/if}
<form name="f_a" onSubmit="return update_attendance_setting();" class="noprint stdframe" method="post">
	<input type="hidden" name="a" value="update_setting" />
	{if $err}
		<ul class="errmsg">
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
		</ul>
	{/if}
	<table>
		<tr>
			<td><b>Early In</b></td>
			<td>
				<input type="text" name="in_early" value="{$smarty.request.in_early|default:$system_settings.in_early}" size="5" onChange="mi(this);" /> mins before start time
			</td>
		</tr>
		<tr>
			<td><b>Late In</b></td>
			<td>
				<input type="text" name="in_late" value="{$smarty.request.in_late|default:$system_settings.in_late}" size="5" onChange="mi(this);" /> mins after start time
			</td>
		</tr>
		<tr>
			<td><b>Early Exit</b></td>
			<td>
				<input type="text" name="out_early" value="{$smarty.request.out_early|default:$system_settings.out_early}" size="5" onChange="mi(this);" /> mins before end time
			</td>
		</tr>
		<tr>
			<td><b>Late Exit</b></td>
			<td>
				<input type="text" name="out_late" value="{$smarty.request.out_late|default:$system_settings.out_late}" size="5" onChange="mi(this);" /> mins after end time
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="update_setting" value="Update Setting" /></td>
		</tr>
	</table>
</form>
{include file='footer.tpl'}