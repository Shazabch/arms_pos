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

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $smarty.request.updated}
	<img src="ui/approved.png" /> Settings Updated.<br /><br />
{/if}
<form name="f_a" onSubmit="return update_attendance_setting();" class="noprint stdframe" method="post">
	<input type="hidden" name="a" value="update_setting" />
	{if $err}
		<ul class="errmsg">
			{foreach from=$err item=e}
				<div class="alert alert-danger mx-3">
					<li> {$e}</li>
				</div>
			{/foreach}
		</ul>
	{/if}
	<di class="card mx-3">
		<div class="card-body">
			<table>
				<tr>
					<td><b class="form-label">Early In</b></td>
					<td>
						<div class="form-inline">
							<input class="form-control" type="text" name="in_early" value="{$smarty.request.in_early|default:$system_settings.in_early}" size="5" onChange="mi(this);" />&nbsp;&nbsp; mins before start time
						</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Late In</b></td>
					<td>
					<div class="form-inline">
						<input class="form-control" type="text" name="in_late" value="{$smarty.request.in_late|default:$system_settings.in_late}" size="5" onChange="mi(this);" />&nbsp;&nbsp; mins after start time
					</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Early Exit</b></td>
					<td>
						<div class="form-inline">
						<input class="form-control" type="text" name="out_early" value="{$smarty.request.out_early|default:$system_settings.out_early}" size="5" onChange="mi(this);" />&nbsp;&nbsp; mins before end time
					</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Late Exit</b></td>
					<td>
				<div class="form-inline">
						<input class="form-control" type="text" name="out_late" value="{$smarty.request.out_late|default:$system_settings.out_late}" size="5" onChange="mi(this);" /> &nbsp;&nbsp; mins after end time
					</div>
					</td>
				</tr>
				<tr>
				
					<td><input type="submit" class="btn btn-primary mt-3" name="update_setting" value="Update Setting" /></td>
				</tr>
			</table>
		</div>
	</di>
</form>
{include file='footer.tpl'}