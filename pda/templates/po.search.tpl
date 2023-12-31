{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

 11/04/2020 10:28 AM Sheila
- Fixed title, table and form css

11/05/2020 11:57 AM Sheila
- Fixed breadcrumbs

*}
{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['po_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.OPEN_PURCHASE_ORDER}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a  href="home.php?a=menu&id=po">{$module_name}</a>
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
			<form name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="main-content-label mg-b-5">{$LNG.OPEN_PO}</div>
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>{$LNG.PO_NO}</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="po_no" value="{$smarty.request.po_no}">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="{$LNG.ENTER}">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->

{if $po_list}
<!--Table-->
<div class="col-xl-12 ">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
							<th>{$LNG.PO_NO}</th>
							<th>{$LNG.DELIVER_BRANCH}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$po_list item=po}
						<tr>
							<td>
								{if $po.deliver_to}
									<a href="po.php?a=change_po&id={$po.id}&branch_id={$po.branch_id}" data-placement="right" data-toggle="tooltip" title="{$LNG.OPEN}"><i class="fas fa-pen"></i></a>
								{else}
									<a href="po.php?a=change_po&id={$po.id}&branch_id={$po.branch_id}" data-placement="right" data-toggle="tooltip" title="{$LNG.OPEN}"><i class="fas fa-pen"></i></a>
								{/if}
							</td>
							<td>{$LNG.PO}#{$po.id}</td>
							<td>
								{if $po.deliver_to}
									{$po.deliver_to}
								{else}
									{$po.branch_code}
								{/if}
							</td>
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
document.f_a['po_no'].select();
{/literal}
</script>
{include file='footer.tpl'}