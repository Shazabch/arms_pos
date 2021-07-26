{*
3/29/2011 5:08:14 PM Andy
- Add can search debtor.

10/3/2011 5:11:43 PM Justin
- Fixed the search vendor not working properly.

10/4/2011 5:56:32 PM Justin
- Removed all the "*" as to indicate required fields (backend will return error msg).
- Resized Vendor & Dept drop-down list to fit into PDA window.
- Added new field "PO No" to allow search by PO No and auto insert Dept and Vendor when PO No is existed.

9/6/2012 5:46 PM Justin
- Enhanced vendor searching function to be more flexible.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

2/25/2014 4:41 PM Justin
- Bug fixed the label of "searching..." never disappear while info not found.
- Enhanced the search document able to search by PO or DO.

04/11/2020 10:21AM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	-Remove class small and added cellspacing and cellpadding in GRR list

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
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
		desc_length = desc.length;

		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			var vd_desc = $(document.f_a['vendor_id'].options[i]).attr('vd_desc').toLowerCase();
			if(desc_length == 1){
				if(vd_desc.indexOf(desc)==0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}else{
				if(vd_desc.indexOf(desc)>=0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}
		}
		
		if(opt == undefined){
			for(var i=1; i<opt_length; i++){    // loop options, skip the first
				var vd_desc = $(document.f_a['vendor_id'].options[i]).text().toLowerCase();
				var vd_desc_split = vd_desc.split(" - ", 2);
				if(desc_length == 1){
					if(vd_desc_split[0].indexOf(desc)==0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}else{
					if(vd_desc_split[0].indexOf(desc)>=0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}
			}
		}
		
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			notify('error',desc+' not foundnot found in Vendor list','center')
		}
	}
}

function search_document(event){
	var k = event.keyCode;
	var doc_no = $.trim(document.f_a['doc_no'].value).toUpperCase();
	var doc_type = $('input[name=doc_type]:checked').val();

	if(doc_no=='' || k!=13) return;

	$('#loading_area').text("Searching...");
	$.get("goods_receiving_record.php", { a: "search_document", doc_no: doc_no, doc_type: doc_type },
		function(data){
			if(data.err_msg!=undefined) alert(data.err_msg);
			else{
				if(data.department_id!=undefined){
					var opt = undefined;

					var opt_length = document.f_a['department_id'].length;
					//var i = $(document.f_a['vendor_id']).children();  // get all options
					for(var i=1; i<opt_length; i++){    // loop options, skip the first
						if(document.f_a['department_id'].options[i].value==data.department_id){ // if found contain the search string
							opt = document.f_a['department_id'].options[i];   // grap this and break
							break;
						}
					}
					
					if(opt){ // got row found
						$(opt).attr('selected', true);
					}
				}else alert("No department was found for this "+doc_type+".");
				
				if(doc_type=="PO"){
					if(data.vendor_id!=undefined){
						var opt = undefined;

						var opt_length = document.f_a['vendor_id'].length;
						//var i = $(document.f_a['vendor_id']).children();  // get all options
						for(var i=1; i<opt_length; i++){    // loop options, skip the first
							if(document.f_a['vendor_id'].options[i].value==data.vendor_id){ // if found contain the search string
								opt = document.f_a['vendor_id'].options[i];   // grap this and break
								break;
							}
						}
						
						if(opt){ // got row found
							$(opt).attr('selected', true);
						}
					}else alert("No vendor was found for this "+doc_type+".");
				}else{
					$(document.f_a['vendor_id'].options[0]).attr('selected', true);
				}
			}
			$('#loading_area').text("");
		}, "json");
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Setting - {if $form.id}({$LNG.GRR}#{$form.id}){else}{$LNG.NEW_GRR}{/if}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $form.find_grr}
			<li class="breadcrumb-item">
				<a href="goods_receiving_record.php?a=open&find_grr={$form.find_grr}">{$LNG.BACK_TO_SEARCH}</a>
			</li>
		{/if}
	</ol>
</nav>

{if $form.id&&$form.branch_id}{include file='goods_receiving_record.top_include.tpl'}<br><br>{/if}

{if $err}
    {foreach from=$err item=e}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
	  {$e}
	  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	  </button>
	</div>
    {/foreach}
{/if}

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}

{if $smarty.request.id && $smarty.request.t}
<div class="alert alert-success animated fadeInDown">
	<img src="../ui/icons/accept.png" align="absmiddle" title="Required Field"> {$LNG.GRR}#{$smarty.request.id} 
	{if $smarty.request.t eq 'insert'}
		{$LNG.INSERTED}
	{else}
		{$LNG.UPDATED}
	{/if}
</div>
{/if}


<!-- row -->
<div class="row animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" value="save_setting">
				<input type="hidden" name="id" value="{$form.id}">
				<input type="hidden" name="branch_id" value="{$branch_id}">
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.SEARCH}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="doc_no" name="doc_no" value="" onKeyUp="search_document(event);" size="10">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.PO} / {$LNG.DO}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<div class="row ml-3 mt-2">
									<label class="font-weight-boldrdiobox mr-3"><input type="radio"  name="doc_type" value="PO" checked> <span>{$LNG.PO}</span></label>
									<label class="font-weight-boldrdiobox"><input type="radio"  name="doc_type" value="DO"> <span>{$LNG.DO}</span></label>
								</div>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.RECEIVED_DATE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="rcv_date" name="rcv_date" value="{$form.rcv_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="9">
								<small class="help-block text-muted">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.VENDOR}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2"  name="vendor_id">
									<option value="" label="-- Please Select --"></option>
									{foreach from=$vendor key=did item=r}
										<option value="{$did}" {if $form.vendor_id eq $did}selected {/if} vd_desc="{$r.description|escape:'html'}">{$r.code} - {$r.description}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.SEARCH_VENDOR}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" name="search_vendor_desc" id="search_vendor_desc" onKeyUp="search_vendor(event);">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DEPARTMENT}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="department_id">
									<option value=0 label="-- Select Department --">
									</option>
									{section name=i loop=$dept}
										<option value={$dept[i].id} {if $form.department_id == $dept[i].id}selected{/if}>{$dept[i].description}</option>
									{/section}
								</select>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.LORRY_NO}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="transport" onchange="ucz(this)" value="{$form.transport}" size=10 maxlength=10>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.RECEIVED_BY}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="rcv_by">
									{section name=i loop=$rcv}
										<option value="{$rcv[i].id}" {if ((!$form.rcv_by && $rcv[i].id eq $sessioninfo.id) || ($form.rcv_by && $rcv[i].id eq $form.rcv_by))}selected{/if}>{$rcv[i].u}</option>
									{/section}
								</select>
							</div>
						</div>
						<input type="submit" class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" value="{$LNG.SAVE}" onclick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->
{include file='footer.tpl'}
