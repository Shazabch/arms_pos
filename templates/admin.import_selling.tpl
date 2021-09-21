{*

9/20/2012 5:03 PM Drkoay
- add Import Selling Price by Type

5/22/2013 11:27 AM Andy
- Add remark to let user know the import will have impact on counter performance and suggest user to import it by multiple batch.

07/31/2013 02:06 PM Justin
- Enhanced to add "No Auto Sync to Counters" checkbox.

11/8/2014 1:17 PM Justin
- Enhanced to have import items's GST info (input, output and inclusive tax).
- Bug fixed on javascript error always return no branch selected even user have ticked one of the branch.

1/30/2015 3:32 PM Justin
- Enhanced to have new feature that can import into batch price change.

3/23/2015 5:04 PM Justin
- Enhanced to allow user update GST info for SKU items without the need to update selling price.
- Added new sample that shows update GST info only.

6/23/2020 11:23 AM Sheila
- Updated button css
*}

{include file='header.tpl'}
{literal}
<style>

</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function check_file(obj){
	switch(obj.tagName.toLowerCase()){
		case 'form':
			var filename = obj.elements['import_csv'].value;
		break;
		case 'input':
			var filename = obj.value;
		break;
	}
	
	// only accept csv file
	if(filename.indexOf('.csv')<0){
		alert('Please select csv file');
		return false;
	}
	return true;
}

function check_form(form,type){
	if(!check_file(form))   return false;   // check file extension
	
	switch(type){
		case 'branch':
			// check whether user got tick at least branch
			var got_branch_checked = false;
			//var all_chx = form['branch_id[]'];
			var all_chx = $('import_selling').getElementsByClassName('branches_chkbx');
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
		break;
		case 'price_type':
			// check whether user got tick at least branch
			var got_type_checked = false;
			var all_chx = form.elements['price_type[]'];
			for(var i=0; i<all_chx.length; i++){
				if(all_chx[i].checked){
					got_type_checked = true;
					break;
				}
			}
			// user no tick any branch
			if(!got_type_checked){
				alert('Please select at least one price type');
				return false;
			}
		break;
	}
	
	// ask final confirmation
	if(!confirm('Are you sure? the action cannot be undo!'))    return false;
	
	return true;    // no problem found
}
{/literal}
</script>

{if !$config.consignment_modules}
	
	<div class="container-fluid">
		<div class="card mx-3 mt-3" >
			<div class="card-body">
				<b class="text-danger">Warning: </b>
		<ul class="text-muted">
			<li> Please prevent to import at business hour. It will slow down all branches counter performance.</li>
			<li> It is recommended to import maximum 200 sku in a batch, and wait for 5 minutes for counter to sync.</li>
			<li> Import with "No Auto Sync to Counters" will requires counters to resync masterfile for related branches.</li>
		</ul>
			</div>
		</div>
		
	
	</div>
{/if}
<div class="container-fluid">
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div> 

{if $import_type eq 'by_branch'}
{if $err}
<div class="card mx-3">
	<div class="card-body">
		<b class="text-danger">The following error(s) has occured:</b>
	<ul class="err text-muted mt-1" >
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
</div>
{/if}
{/if}

	<div class="card mx-3">
		<div class="card-body">
			
<form name="f_a" id="import_selling" enctype="multipart/form-data" class="stdframe" method="post" onSubmit="return check_form(this,'branch');">
	<input type="hidden" name="a" value="import_selling" />
	<input type="hidden" name="import_method" value="selling" />
	
	<label class="mt-1"><b>Branch</b>
	{foreach from=$branches key=bid item=b}
		<span style="white-space:nowrap;margin-right:20px;">
		    <input type="checkbox" name="branch_id[]" class="branches_chkbx" {if is_array($smarty.request.branch_id) and in_array($bid, $smarty.request.branch_id)}checked {/if} value="{$bid}" />
			{$b.code}
		</span>
	{/foreach}
	</label><br>
<label class="mt-1"><b>Upload CSV</b> (<a href="?a=view_sample">View Sample</a>) <input type="file" name="import_csv"  onChange="check_file(this);" /></label>

	<label class="mt-1"><b>* To update GST info for SKU items without new selling price, please <a href="?a=view_sample&gst_only=1">click here</a> to view the sample.</b></label>

	<div class="alert alert-danger " style="max-width: 305px;">
		<b>Warning:</b> This action cannot be undo.
	</div>
	<input class="btn btn-primary" type="submit" value="Import" onclick="document.f_a['import_method'].value='selling';" /> 
	{if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE}
		<input class="btn btn-primary" type="submit" value="Generate Batch Price Change" onclick="document.f_a['import_method'].value='batch_selling';" />
	{/if}	<br>
	<input class="mt-3" type="checkbox" name="no_sync" {if $smarty.request.no_sync}checked {/if} value="1" /> <b>No Auto Sync to Counters</b>
 </form>
{if $import_type eq 'by_branch'}
	{if $import_method eq 'selling'}
		{if $import_success}<p style="color:blue;">Import Success! {$total_affected} item(s) imported</p>{/if}
	{else}
		{if $import_success}
			<p style="color:blue;">Import Success! 
			Batch Price Change ID created: 
			{foreach from=$fp_id_list name=fp key=dummy item=id}
				<a href="masterfile_sku_items.future_price.php?a=view&id={$id}&branch_id={$sessioninfo.branch_id}" target="_blank">#{$id}</a>
				{if !$smarty.foreach.fp.last},{/if}
			{/foreach}
			</p>
		{/if}
	{/if}
	{if $msg.warning}
		<ul>
			{foreach from=$msg.warning item=m}
				<li>{$m}</li>
			{/foreach}
		</ul>
	{/if}
{/if}

		</div>
	</div>
</div>
<div class="container-fluid">
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Import Selling Price by Type</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div> 
{if $import_type eq 'by_type'}
{if $err}
The following error(s) has occured:
	<ul class="err" style="color:red;">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}
{/if}

	<div class="card mx-3">
		<div class="card-body">
			<form name="f_b" enctype="multipart/form-data" class="stdframe" method="post" onSubmit="return check_form(this,'price_type');">
				<input type="hidden" name="a" value="import_selling_by_type" />
				<input type="hidden" name="import_method" value="selling" />
				<b class="">Price Type</b><br>
				<span style="white-space:nowrap;margin-right:10px;">
					<input type="checkbox" name="price_type[]" value="normal" />
					normal
				</span>
				{if $config.sku_multiple_selling_price}
					{foreach from=$config.sku_multiple_selling_price item=s}
						<span style="white-space:nowrap;margin-right:10px;">
							<input type="checkbox" name="price_type[]" value="{$s}" />
							{$s}
						</span>
					{/foreach}
				{/if}
				<br><br>
				<p class="text-dark">
					<b>Note:</b>
					Example for changing price type:  5.00/BB 
				</p>
				<b>Upload CSV</b> (<a href="?a=view_sample_by_type">View Sample</a>) <input type="file" name="import_csv" onChange="check_file(this);" />
				<br />
				<br>
				<div class="alert alert-danger" style="max-width: 305px;">
					<b>Warning:</b> This action cannot be undo.
				</div>
				<input class="btn btn-primary" type="submit" value="Import" onclick="document.f_b['import_method'].value='selling';" /> 
				{if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE}
					<input class="btn btn-primary" type="submit" value="Generate Batch Price Change" onclick="document.f_b['import_method'].value='batch_selling';" />
				{/if}<br>
				<input  class="mt-2" type="checkbox" name="no_sync" {if $smarty.request.no_sync}checked {/if} value="1" /> <b>No Auto Sync to Counters</b>
			</form>
			
		</div>
	</div>
</div>
{if $import_type eq 'by_type'}
<div class="card mx-3">
	<div class="card-body">
		
{if $import_method eq 'selling'}
		{if $import_success}
		<p class="text-success">Import Success! {$total_affected} item(s) imported</p>{/if}
		{else}
		{if $import_success}
			<p class="text-success">Import Success! 
			Batch Price Change ID created: 
			{foreach from=$fp_id_list name=fp key=dummy item=id}
				<a href="masterfile_sku_items.future_price.php?a=view&id={$id}&branch_id={$sessioninfo.branch_id}" target="_blank">#{$id}</a>
				{if !$smarty.foreach.fp.last},{/if}
			{/foreach}
			</p>
		{/if}
	{/if}
	{if $msg.warning}
		<ul class="text-muted">
			{foreach from=$msg.warning item=m}
				<li>{$m}</li>
			{/foreach}
		</ul>
	{/if}


	</div>
</div>
{/if}
{include file='footer.tpl'}
