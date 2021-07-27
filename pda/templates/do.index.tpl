{*
10/11/2011 10:42:12 AM Justin
- Added the search debtor and branch features.
- Re-aligned the form layout to fill under PDA screen.

7/31/2012 10:28:34 AM Justin
- Bug fixed of user can choose to deliver same branch as branch deliver.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

10/29/2020 5:00 PM Sheila
- Fixed title, table and form css

11/04/2020 4:30 PM Rayleen
- Modified title, add submenu in breadcrumbs (Dashboard>SubMenu)

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}

{include file='header.tpl'}

<script>
var do_type = '{$do_type}';
var do_branch_id = "";
{if $form.id}
	var branch_id = '{$form.branch_id}';
	var do_branch_id = '{$form.do_branch_id}';
{else}
	var branch_id = '{$sessioninfo.branch_id}';
{/if}

{literal}
function submit_form(){
	if(document.f_a['do_date'].value==''){
		alert('Please enter DO Date.');
		return false;
	}

	if(do_type=='open'){
	    if(document.f_a['open_info[name]'].value==''){
			alert('Please key in Company Name');
			return false;
		}
		if(document.f_a['open_info[address]'].value==''){
            alert('Please key in Address');
			return false;
		}
	}else if(do_type=='credit_sales'){
        if(document.f_a['debtor_id'].value==''){
			alert('Please select Debtor.');
			return false;
		}
	}else{  // transfer
        if(document.f_a['do_branch_id'].value==''){
			alert('Please select Deliver To Branch.');
			return false;
		}
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
			$("select[name='debtor_id']").val(opt.value);
			$("select[name='debtor_id']").trigger('change');
		}else{ // no data found
			alert(desc+' not found in Debtor list');
		}
	}
}

function search_branch(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_branch_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['do_branch_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['do_branch_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['do_branch_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
			$("select[name='do_branch_id']").val(opt.value);
			$("select[name='do_branch_id']").trigger('change');
		}else{ // no data found
			alert(desc+' not found in Branch list');
		}
	}
}

function branch_check(obj){
	if(obj.value == branch_id){
		alert("Cannot deliver to same branch!");
		obj.value = do_branch_id;
	}
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{if $do_type eq 'open'} {$LNG.CASH_SALES}
{elseif $do_type eq 'credit_sales'}{$LNG.CREDIT_SALES}
{else}{$LNG.TRANSFER} {/if} {$LNG.DO}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=do">{$LNG.DO}</a>
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

{if $form.id&&$form.branch_id}{include file='do.top_include.tpl'}<br /><br />{/if}

<h3 class="">{$LNG.SETTING} - {if $form.do_no}({$LNG.DO}/{$form.do_no}){else}{if $form.id}({$LNG.DO}#{$form.id}){else}{$LNG.NEW_DO}{/if}{/if}</h3>

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}

<!-- row -->
<div class="row ">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false;">
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<input type="hidden" name="a" value="save_setting" />
						<input type="hidden" name="id" value="{$form.id}" />
						<input type="hidden" name="branch_id" value="{$branch_id}" />
						<input type="hidden" name="do_type" value="{$do_type}" />
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DO_DATE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="inp_do_date" name="do_date" value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="10">
								<small class="help-block text-muted">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DELIVER_FROM}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								{$branches.$branch_id.code} - {$branches.$branch_id.description}
							</div>
						</div>
						{if $do_type eq 'open'}
						<div class="row row-xs  mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DELIVER_TO}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<label>{$LNG.COMPANY_NAME}</label>
								<input type="text" class="form-control " onchange="this.value=this.value.toUpperCase();" value="{$form.open_info.name}" name="open_info[name]">
								<label>{$LNG.ADDRESS}</label>
								<textarea class="form-control " rows="2" cols="8" name="open_info[address]">{$form.open_info.address}</textarea>
							</div>
						</div>
						{elseif $do_type eq 'credit_sales'}
						<div class="row row-xs  mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DELIVER_TO}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<label>{$LNG.DEBTOR}</label>
								<select class="form-control select2" name="debtor_id">
									<option value="" label="-- Please Select --"></option>
										{foreach from=$debtors item=r}
											<option value="{$r.id}" {if $form.debtor_id eq $r.id}selected {/if}>{$r.code}</option>
										{/foreach}
								</select>
								<!-- <label>{$LNG.SEARCH_DEBTOR}</label>
								<input type="text" class="form-control " name="search_debtor_desc" onKeyUp="search_debtor(event);"> -->
							</div>
						</div>
						{else}
						<div class="row row-xs  mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DELIVER_TO}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="do_branch_id" onChange="branch_check(this);">
									<option value="" label="-- Please Select --"></option>
										{foreach from=$branches_group.header key=bgid item=bg}
						                <optgroup label="{$bg.code}">
						                    {foreach from=$branches_group.items.$bgid key=bid item=b}
						                        <option value="{$bid}" {if $form.do_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
						                    {/foreach}
						                </optgroup>
						                {/foreach}
						                {foreach from=$branches key=bid item=b}
							                {if !$branches_group.have_group.$bid}
							                    <option value="{$bid}" {if $form.do_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
							                {/if}
							            {/foreach}
								</select>
							</div>
						</div>
						<!-- <div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.SEARCH_BRANCH}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="search_branch_desc" onKeyUp="search_branch(event);">
							</div>
						</div> -->
						{/if}
						<input class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" type="button" value="{$LNG.SAVE}" onClick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->
{include file='footer.tpl'}
