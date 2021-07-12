{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/10/2011 4:46:32 PM Justin
- Modified the edit image to use gif instead of png extension.

04/11/2020 3:29PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page
	-Remove class small in table and added cellspacing and cellpadding

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['order_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Open By Order No</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a  href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

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
<!-- Search form -->
<div class="row mt-2 animated fadeInDown">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>DO No.</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="order_no" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.order_no}">
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
<!-- / Search form --> 

{if $so_list}
<!--Table-->
<div class="col-xl-12 animated fadeInDown">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
							<th>Order No.</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$so_list item=so}
						<tr>
							<td><a href="{$smarty.server.PHP_SELF}?a=change_so&id={$so.id}&branch_id={$so.branch_id}" data-toggle="tooltip" title="Open"><i class="fas fa-pen"></i></a></td>
							<td>SO#{$so.id}</td>
							<td>{$so.debtor_code} - {$so.debtor_desc}</td>
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
document.f_a['order_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
