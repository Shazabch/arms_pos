{*

04/11/2020 3:10PM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small in table and added cellspacing and cellpadding
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['gra_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Open By GRA NO.</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a  href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
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
<!-- Form -->
<div class="row mt-2 animated fadeInDown">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form  name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>GRA No.</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="gra_no" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.gra_no}">
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
<!-- / Form -->

{if $gra_list}
<!--Table-->
<div class="col-xl-12">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
							<th>GRR NO.</th>
							<th>Vendor</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$gra_list item=gra}
						<tr>
							<td><a href="{$smarty.server.PHP_SELF}?a=change_gra&id={$gra.id}&branch_id={$gra.branch_id}" data-toggle="tooltip" title="Edit"><i class="fas fa-pen"></i></a></td>
							<td>GRA#{$gra.id}</td>
							<td>{$gra.vendor_code} - {$gra.vendor_desc}</td>
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

<script>
{literal}
document.f_a['gra_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
