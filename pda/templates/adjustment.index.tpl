{*
8/17/2012 9:30 AM Justin
- Bug fixed on adjustment cannot get branch ID.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 3:11PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles
	-Remove class small in table and added cellspacing and cellpadding
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/05/2020 11:48 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
	if(document.f_a['adjustment_date'].value==''){
		notify('error','Please enter Adjustment Date','center')
		return false;
	}

	if(document.f_a['adjustment_type'].value==''){
		notify('error','Please enter Adjustment Type','center')
		return false;
	}
	
	if(document.f_a['dept_id'].value==''){
		notify('error','Please select Department','center')
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_dept(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_dept_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['dept_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['dept_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['dept_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
			$("select[name='dept_id']").val(opt.value);
			$("select[name='dept_id']").trigger('change');
		}else{ // no data found
			notify('error',desc+' not found in Department list','center')
		}
	}
}

function adj_type_changed(obj){
	if(obj.value != "") document.f_a['adjustment_type'].value = obj.value;
}

{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.SETTING} - {if $form.id}({$form.report_prefix}{$form.id|string_format:"%05d"}){else}{if $form.id}({$form.report_prefix}{$form.id}){else}New {$module_name}{/if}{/if}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a  href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $form.find_adjustment}
		<li class="breadcrumb-item">
			<a href="adjustment.php?a=open&find_adjustment={$form.find_adjustment}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
	</ol>
</nav>
<!-- /BreadCrumbs -->

{if $form.id&&$form.branch_id}{include file='adjustment.top_include.tpl'}<br /><br />{/if}

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

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

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
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DATE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="inp_date" name="adjustment_date" value="{$form.adjustment_date|default:$smarty.now|date_format:'%Y-%m-%d'}" size="10">
								<small class="help-block text-muted pl-2">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.TYPE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" size="30" name="adjustment_type" value="{$form.adjustment_type}">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.PRESET_TYPE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="preset_type" onChange="adj_type_changed(this);">
									<option value="" label="-- Please Select --"></option>
									{foreach from=$config.adjustment_type_list item=type_item}
						                <option value="{$type_item.name|upper}" {if $form.adjustment_type eq $type_item.name|upper}selected {/if}>{$type_item.name|upper}</option>
						            {/foreach}
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DEPARTMENT}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="dept_id">
									<option value="" label="-- Please Select --"></option>
									{foreach from=$dept key=r item=d}
						                <option value="{$d.id}" {if $form.dept_id eq $d.id}selected {/if}>{$d.description}</option>
						            {/foreach}
								</select>
							</div>
						</div>
						<!-- <div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.SEARCH_DEPARTMENT}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="search_dept_desc" onKeyUp="search_dept(event);">
							</div>
						</div> -->
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.REMARK}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<textarea class="form-control" name="remark" placeholder="Textarea" rows="2">{$form.remark}</textarea>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.BRANCH}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								{if $form.id}
									{assign var=bid value=$form.branch_id}
									{$form.branch_code} - {$form.description}
								{else}
									{if $BRANCH_CODE eq "HQ"}
								<select class="form-control select2" name="branch_id">
									<option value="" label="-- Please Select --"></option>
										{foreach from=$branches key=bid item=b}
											<option value="{$bid}">{$b.code}</option>
										{/foreach}
								</select>
									{else}
										{assign var=bid value=$sessioninfo.branch_id}
										{$branches.$bid.code}
									{/if}
								{/if}
							</div>
						</div>
						<input type="button" class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" value="{$LNG.SAVE}" onClick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->
{include file='footer.tpl'}
