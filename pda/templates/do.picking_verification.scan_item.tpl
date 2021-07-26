{*
05/11/2020 1:33PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	
11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['product_code'].value.trim()=='')   return false;
	return true;
}
{/literal}
</script>
{assign var=search_var value=$smarty.session.do_picking_verification.search_var}
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$form.do_title}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=do">{$module_name}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="{$smarty.server.PHP_SELF}?a=open&do_no={$search_var}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

{if $smarty.session.$module.barcode_type eq 0}
	{assign var=use_barcode_type value=0}
{/if}
{if $smarty.session.$module.barcode_type eq 1 || !$config.enable_grn_barcoder}
	{assign var=use_barcode_type value=1}
{/if}

<!-- Search form -->
<div class="row mt-2">
	<div class="col-lg-12 col-md-12 animated fadeInDown">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				{assign var=module value=$smarty.session.scan_product.type|strtolower}
				<input type="hidden" name="a" value="scan_item" />
				<input type="hidden" name="id" value="{$form.id}" />
				<input type="hidden" name="branch_id" value="{$form.branch_id}" />
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-1 text-center">
								<label><b>{$LNG.SCAN}</b></label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="product_code">
								{if $config.enable_grn_barcoder}
								<label class="rdiobox mt-2"><input type="radio" name="grn_barcode_type" value="0" {if $use_barcode_type eq 0}checked{/if}> <span>{$LNG.GRN_BARCODER}</span></label>
								{/if}
								<label class="rdiobox mt-2"><input type="radio" name="grn_barcode_type" value="1" {if $use_barcode_type eq 1}checked{/if}> <span class="fs-08">{$LNG.ARMS_CODE} / {$LNG.MCODE} / {$LNG.ART_NO} / {$config.link_code_name}</span></label>
							</div>
							<div class="col-md-2 mt-2 mt-md-0 mt-xl-0 mt-lg-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="{$LNG.ENTER}">
							</div>
						</div>
					</div>
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
				</div>
			</form>
		</div>
	</div>
	<div class="col-lg-12 col-md-12">
		{if $result}
			{if $result.not_found}
				<div class="alert alert-danger animated fadeInDown">{$LNG.NOT_FOUND}</div>
			{elseif $result.data}
				<div class="alert alert-success animated fadeInDown text-center"><b>{$LNG.FOUND}</b></div>
				<div class="card animated fadeInLeft">
					<div class="card-body">
						<div class="card-category fs-08">{$result.data.description} <span class="badge bg-danger-opacity p-1">{$result.data.packing_uom_code|default:'EACH'}</span></div>
						<!--Table-->
						<div class="table-responsive mt-2">
							<table class="table table-hover mb-0 text-md-nowrap">
								<thead>
									<tr class="text-center">
										<th>{$LNG.DO} {$LNG.UOM}</th>
										<th>{$LNG.CTN}</th>
										<th>{$LNG.PCS}</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td align="center">{$result.data.uom_code}</td>
										<td align="center">{$result.data.ctn}</td>
										<td align="center">{$result.data.pcs}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!-- /Table -->
						
					</div>
				</div>
			{/if}
		{/if}
	</div>
</div>
<!-- / Search form -->
<script>
{literal}
document.f_a['product_code'].select();
{/literal}
</script>
{include file='footer.tpl'}
