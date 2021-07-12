{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/4/2011 4:46:32 PM Justin
- Modified the edit image to use gif instead of png extension.

11/25/2015 9:20 AM Qiu Ying
- PDA GRN can search GRR

04/11/2020 10:02AM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small and added cellspacing and cellpadding in GRN list

11/04/2020 3:02PM Rayleen
- Modified page style/layout. 
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page
	
11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['find_grn'].value=="") return false;
	
	return true;
}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Open By GRN No. / GRR No. </h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 animated fadeInDown" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<!-- Row -->
<div class="row mt-2 animated fadeInDown">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>GRN No. / GRR No.</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="find_grn" class="txt-width-50" value="{$smarty.request.find_grn}">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="Enter">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Row -->

{if $grn_list}
<!--Table-->
<div class="col-xl-12">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
							<th>GRN NO.</th>
							<th>GRR NO.</th>
							<th>Vendor</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$grn_list item=grn}
						<tr>
							<td><a href="{$smarty.server.PHP_SELF}?a=change_grn&id={$grn.id}&branch_id={$grn.branch_id}&find_grn={$smarty.request.find_grn}" data-toggle="tooltip" title="Open"><i class="fas fa-pen"></i></a></td>
							<td>GRN#{$grn.id}</td>
							<td>GRR#{$grn.grr_id}</td>
							<td>{$grn.vendor_code} - {$grn.vendor_desc}</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- /Table -->
{/if}
</div>
<script>
{literal}
document.f_a['find_grn'].select();
{/literal}
</script>
{include file='footer.tpl'}
