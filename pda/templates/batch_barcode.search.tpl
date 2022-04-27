{*
3/13/2013 10:42 AM Justin
- Bug fixed on showing icons not in one row.
- Bug fixed on the "delete" icon does not show properly.

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/05/2020 11:50 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}


{include file='header.tpl'}
<script>
{literal}
function check_form(){
	if(document.f_a['find_batch_barcode'].value=='') return false;
	
	return true;
}

function delete_confirmation(){
	if(!confirm("{/literal}{$LNG.DELETE_CONFIRMATION_MSG}{literal}?")) return false;
	else return true;
}

function toggle_bb_list(){

	if($('#batch_barcode_list').get(0).style.display == "none") $('#batch_barcode_list').get(0).style.display = "";
	else $('#batch_barcode_list').get(0).style.display = "none";
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.OPEN_BATCH_BARCODE}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 " role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<!-- Search form -->
<div class="row mt-2 ">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form >
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>{$LNG.BATCH_BARCODE_NO}</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="find_batch_barcode" onChange="mi(this);" value="{$smarty.request.find_batch_barcode}">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="{$LNG.SAVE}">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->

{if !$smarty.request.find_batch_barcode && $bb_list}
	<button onclick="toggle_bb_list();" class="btn btn-indigo btn-rounded btn-sm ">{$LNG.SHOW_HIDE_BATCH_BARCODE_LIST}</button>
{/if}

<div id="batch_barcode_list" {if !$smarty.request.find_batch_barcode || !$bb_list}style="display:none;"{/if}>
	<!--Table-->
	<div class="col-xl-12 mt-3 animated fadeInDown">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th></th>
								<th>{$LNG.ID}</th>
								<th>{$LNG.TOTAL_ITEMS}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$bb_list item=r}
							<tr>
								<td>
									<a href="{$smarty.server.PHP_SELF}?a=change_batch_barcode&id={$r.id}&branch_id={$r.branch_id}&find_batch_barcode={$smarty.request.find_batch_barcode}" data-placement="right" data-toggle="tooltip" title="{$LNG.OPEN}"><i class="fas fa-pen"></i></a>&nbsp;
									<a href="{$smarty.server.PHP_SELF}?a=delete_batch_barcode&id={$r.id}&branch_id={$r.branch_id}&find_batch_barcode={$smarty.request.find_batch_barcode}" data-placement="right" data-toggle="tooltip" title="{$LNG.DELETE}" onclick="return delete_confirmation();"><i class="text-danger fas fa-trash-alt"></i></a>
								</td>
								<td>#{$r.id}</td>
								<td>{$r.total_items}</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- /Table -->
</div>

<script>
{literal}
document.f_a['find_batch_barcode'].select();
{/literal}
</script>
{include file='footer.tpl'}
