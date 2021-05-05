{include file='header.tpl'}
{literal}
<style>

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function check_file(){
	var filename = document.f_a['import_csv'].value;
	// only accept csv file
	if(filename.indexOf('.csv')<0){
		alert('Please select csv file');
		return false;
	}
	return true;
}

function check_form(){
	if(!check_file())   return false;   // check file extension
	
	// check whether user got tick at least branch
	var got_branch_checked = false;
	var all_chx = document.f_a['branch_id[]'];
	for(var i=0; i<all_chx.length; i++){
        if(all_chx[i].checked){
            got_branch_checked = true;
            break;
		}
	}
	
	// user no tick any branch
	if(!got_branch_checked){
		alert('Please select at least one branch');
		return false;
	}
	
	// ask final confirmation
	if(!confirm('Are you sure? the action cannot be undo!'))    return false;
	
	return true;    // no problem found
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
	<ul class="err" style="color:red;">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" enctype="multipart/form-data" class="stdframe" method="post" onSubmit="return check_form();">
	<input type="hidden" name="a" value="update_pt" />
	
	<b>Branch</b>
	{foreach from=$branches key=bid item=b}
		<span style="white-space:nowrap;margin-right:10px;">
		    <input type="checkbox" name="branch_id[]" {if is_array($smarty.request.branch_id) and in_array($bid, $smarty.request.branch_id)}checked {/if} value="{$bid}" />
			{$b.code}
		</span>
	{/foreach}
	<br><br>
	<b>Upload CSV</b> (<a href="?a=view_sample">View Sample</a>) <input type="file" name="import_csv" onChange="check_file();" />
	<br />
	<br><span style="color:red;">Warning: This action cannot be undo.</span><br>
	<input type="submit" value="Import" />
 </form>

{if $import_success}<p style="color:blue;">Import Success! {$total_affected} item(s) imported</p>{/if}
{if $msg.warning}
	<ul>
		{foreach from=$msg.warning item=m}
			<li>{$m}</li>
		{/foreach}
	</ul>
{/if}


{include file='footer.tpl'}
