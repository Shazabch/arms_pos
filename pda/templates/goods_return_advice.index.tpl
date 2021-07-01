{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 10:40AM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small in table and added cellspacing and cellpadding 

11/04/2020 3:08PM Rayleen
- Modified page style/layout. 
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){

	if(document.f_a['vendor_id'].value==''){
		alert('Please select Vendor.');
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_vendor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_vendor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['vendor_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['vendor_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['vendor_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in vendor list');
		}
	}
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Settings - {if $form.gra_no}(GRA/{$form.gra_no}){else}{if $form.id}(GRA#{$form.id}){else}New GRA{/if}{/if}</h4>
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

{if $form.id&&$form.branch_id}{include file='goods_return_advice.top_include.tpl'}<br /><br />{/if}

<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}
<!-- row -->
<div class="row animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return false;">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<input type="hidden" name="a" value="save_setting" />
						<input type="hidden" name="id" value="{$form.id}" />
						<input type="hidden" name="branch_id" value="{$branch_id}" />
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="form-label mg-b-0">Vendor</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="vendor_id">
									<option value="" label="--Please Select--"></option>
									{foreach from=$vendors key=vid item=r}
						                <option value="{$vid}" {if $form.vendor_id eq $vid}selected {/if}>{$r.code} - {$r.description}</option>
						            {/foreach}
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="form-label mg-b-0">Search Vendor</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="search_vendor_desc" onKeyUp="search_vendor(event);">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="form-label mg-b-0">SKU Type</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="sku_type">
									<option value="" label="--Please Select--"></option>
									<option value="OUTRIGHT" {if $form.sku_type eq 'OUTRIGHT'}selected {/if}>Outright</option>
		                			<option value="CONSIGN" {if $form.sku_type eq 'CONSIGN'}selected {/if}>Consignment</option>
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="form-label mg-b-0">Department</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="dept_id">
									<option value="" label="--Please Select--"></option>
									{foreach from=$departments key=did item=r}
						                <option value="{$did}" {if $form.dept_id eq $did}selected {/if}>{$r.description}</option>
						            {/foreach}
								</select>
							</div>
						</div>
						<input type="button" class="btn btn-main-primary" value="Save" name="submit_btn" value="Save" onClick="submit_form();">
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /row -->
{include file='footer.tpl'}
