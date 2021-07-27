{*
11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/05/2020 11:51 AM Sheila
- Fixed breadcrumbs

*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){

	if(document.f_a['owner_id'].value==''){
		notify('error','Please select Vendor','center')
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.NEW_PROMOTION}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=promotion">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

{if $form.id}{include file='promotion.top_include.tpl'}<br /><br />{/if}

<!-- Error Message -->
{if $err}
Error
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

<!-- row -->
<div class="row animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<div class="card-header"><h4 class="border-bottom pb-2">{$LNG.SETTING} - {if $form.promotion_no}({$LNG.PROMOTION}/{$form.promotion_no}){else}{if $form.id}({$LNG.PROMOTION}#{$form.id}){else}{$LNG.NEW_PROMOTION}{/if}{/if}</h4></div>
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" value="save_setting" />
				<input type="hidden" name="id" value="{$promo_id}" />
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.TITLE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" size="30" name="title" value="{$form.title}">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.OWNER}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="owner_id">
									<option value="" label="-- Please Select --"></option>
										{foreach from=$owners item=r}
							                <option value="{$r.id}" {if $form.owner_id eq $r.id}selected {/if}>{$r.u}</option>
							            {/foreach}
								</select>
							</div>
						</div>
						<input type="button" class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" value="{$LNG.SAVE}" onclick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->
{include file='footer.tpl'}
