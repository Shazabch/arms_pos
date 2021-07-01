{*
3/29/2011 5:08:14 PM Andy
- Add can search debtor.

10/3/2011 5:11:43 PM Justin
- Fixed the search vendor not working properly.

10/11/2011 11:50:11 AM Justin
- Reduced the width of Debtor drop down list.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu), then link to module menu page
	-Remove class small in table and added cellspacing and cellpadding

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
	if(document.f_a['order_date'].value==''){
		alert('Please enter Order Date.');
		return false;
	}

	if(document.f_a['debtor_id'].value==''){
		alert('Please select To Debtor.');
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_debtor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_debtor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['debtor_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['debtor_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['debtor_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in Debtor list');
		}
	}
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Setting - {if $form.order_no}(SO/{$form.order_no}){else}{if $form.id}(SO#{$form.id}){else}New SO{/if}{/if}</h4>
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

{if $form.id&&$form.branch_id}{include file='sales_order.top_include.tpl'}<br /><br />{/if}


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

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}
<!-- row -->
<div class="row animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false;">
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<input type="hidden" name="a" value="save_setting" />
						<input type="hidden" name="id" value="{$form.id}" />
						<input type="hidden" name="branch_id" value="{$branch_id}" />
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Order Date</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="inp_date" name="order_date" value="{$form.order_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="10">
								<small class="help-block text-muted">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">From</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								{$branches.$branch_id.code} - {$branches.$branch_id.description}
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Batch Code</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" size="15" name="batch_code" value="{$form.batch_code}">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Customer PO</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" size="15" name="cust_po" value="{$form.cust_po}">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">To Debtor</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="debtor_id">
									<option value="" label="-- Please Select --"></option>
									{foreach from=$debtors key=did item=r}
					                	<option value="{$did}" {if $form.debtor_id eq $did}selected {/if}>{$r.code} - {$r.description}</option>
					            	{/foreach}
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Search Debtor</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="search_debtor_desc" onKeyUp="search_debtor(event);">
							</div>
						</div>
						<input type="submit" class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" value="Save" onClick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->

{include file='footer.tpl'}
