{*
7/26/2011 1:06:26 PM Justin
- Added Back to Search feature.

6/14/2012 4:31:34 PM Justin
- Added new option "Add item when match one result".
- Enhanced to show item added info once added a item.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

7/31/2012 9:51:24 AM Justin
- Enhanced to disable the checkbox for "Add item that matched 1 result" when scan by GRN barcoder.

8/8/2012 3:57:12 PM Justin
- Modified the grn_barcode_type from 1 become 0 and 2 become 1.

9/7/2012 12:17 PM Justin
- Enhanced to tick barcode scan choice on ARMS Code while user is in GRN module.

9/7/2012 4:04 PM Andy
- Fix module won't auto tick on search ARMS Code if create new GRN or continue last GRN.

11/30/2012 2:52:PM Fithri
- PDA - GRA Module

12/14/2012 2:17:00 PM Fithri
- remove config checking on scan barcode

12/26/2012 12:01 PM Justin
- Enhanced to memorize the current barcode type.

3/7/2013 3:55 PM Justin
- Bug fixed on system allow user to tick on auto add item when user search item with GRN barcoder.
- Enhanced to allow user can tick on auto add item while they are searching item with ARMS Code, MCode or etc.

4/6/2018 6:06 PM Justin
- Enhanced to show/hide scan GRN barcoder base on config.

4/10/2018 11:03 AM Andy
- Fixed "Add item when match one result" checkbox cannot be tick when first time access.

9/22/2020 11:35 AM William
- Enhanced error message able to enter to next line.

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/04/2020 4:03 PM Rayleen
-Modified H1 title, add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

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

function check_barcode_type(obj){
	if(obj.value == 0){
		document.f_a['auto_add_item'].disabled = true;
		document.f_a['auto_add_item'].checked = false;
	}else document.f_a['auto_add_item'].disabled = false;
	
	document.f_a['product_code'].focus();
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.scan_product.name} {$LNG.NEW_BATCH}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInLeft">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
		{if $form.search_var}
		<li class="breadcrumb-item">
			<a href="{$smarty.server.PHP_SELF}?a=open&find_{$module_name|lower}={$form.search_var}">{$LNG.BACK_TO _SEARCH}</a>
		</li>
		{/if}
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

{if !$is_grn_module || $smarty.session.$module.barcode_type eq 0}
	{assign var=use_barcode_type value=0}
{/if}
{if $is_grn_module || $smarty.session.$module.barcode_type eq 1 || !$config.enable_grn_barcoder}
	{assign var=use_barcode_type value=1}
{/if}

{if $top_include}{include file=$top_include }{/if}
 
<!-- Search form -->
<div class="row mt-2 animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				{assign var=module value=$smarty.session.scan_product.type|strtolower}
				<input type="hidden" name="type" value="{$smarty.session.scan_product.type}" />
				<input type="hidden" name="a" value="show_scan_product" />
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>{$LNG.SCAN}</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text"name="product_code">
								{if $config.enable_grn_barcoder}
								<label class="rdiobox mt-2"><input type="radio" name="grn_barcode_type" value="0" {if $use_barcode_type eq 0}checked{/if} onChange="check_barcode_type(this);"> <span>{$LNG.GRN_BARCODER}</span></label>
								{/if}
								<label class="rdiobox mt-2"><input type="radio" name="grn_barcode_type" value="1" {if $use_barcode_type eq 1}checked{/if} onChange="check_barcode_type(this);"> <span class="fs-08">{$LNG.ARMS_CODE} / {$LNG.MCODE} / {$LNG.ART_NO} / {$config.link_code_name}</span></label>
							</div>
							<div class="col-md-2 mt-2 mt-md-0 mt-xl-0 mt-lg-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="{$LNG.ENTER}">
							</div>
						</div>
						<div class="container-flex mt-3">
							{*if $auto_add && !$err}<br /><img src="/ui/approved.png" title="Item Added" border=0> Item added<br /><br />{/if*}
							<div class="d-flex flex-row mb-2 mt-3">
								<label class="ckbox mr-2"><input type="checkbox" value="1" name="auto_add_item" {if $auto_add}checked{/if} {if ($form.find_grn || !$is_grn_module) && $use_barcode_type ne 1 && !$is_grn_module}disabled{/if}><span></span></label>
								<span class="">{$LNG.ADD_ITEM_WHEN_MATCH_ONE_RESULT} {if is_item_check}({$LNG.ALLOW_DUPLICATE}){/if}</span>
							</div>
							

								<a href="batch_barcode.php?a=import_csv" class="text-warning"><i class="fas fa-file-import mr-1"></i>{$LNG.IMPORT_SKU_BY_CSV}</a>

							{if $smarty.session.scan_product.type == 'GRA'}
							<div class="row row-xs align-items-center mg-b-20 mt-2">
								<div class="col-md-2">
									<label class="font-weight-bold mg-b-0">{$LNG.RETURN_TYPE}:</label>
								</div>
								<div class="col-md-6 mg-t-5 mg-md-t-0">
									<select class="form-control select2" name="return_type">
										<option value="" label="-- Please Select --"></option>
											{foreach from=$return_type_list item=rt}
												<option>{$rt}</option>
											{/foreach}
									</select>
								</div>
							</div>
							{/if}
						</div>
					</div>
					{if $btm_include}{include file=$btm_include}{/if}
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->
<script>
{literal}
document.f_a['product_code'].select();
{/literal}
</script>
{include file='footer.tpl'}
