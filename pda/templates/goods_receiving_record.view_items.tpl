{*
8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 5:54:21 PM Justin
- Fixed the jquery that does not working properly for change document type.

10/7/2011 5:59:21 PM Justin
- Amended to adjust some fields to become 2nd row in order for user can see them in one screen.
- Removed common js functions since it is already assigned at forms.js.

7/25/2012 5:08:43 PM Justin
- add row number for items

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

4/22/2015 5:11 PM Justin
- Enhanced to have GST information.

9/25/2018 1:56 PM Justin
- Enhanced to have "Document Date" and it is compulsory.

10/11/2018 4:45 PM Andy
- Enhanced to prompt warning if users is going to perform delete but there are new unsaved item.

04/17/2020 04:35 PM Sheila
- Modified layout to compatible with new UI.

04/11/2020 5:14 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page

*}
{include file='header.tpl'}

<script>

var row_count = '{$items|@count|default:0}';
var is_under_gst = '{$form.is_under_gst|default:0}';
{literal}

function change_row_color(ele){
    if($(ele).is(":checked")){
		$(ele).parent().parent().parent().addClass('table-warning');
		$(ele).parent().parent().parent().next().addClass('table-warning');
	}else{
        $(ele).parent().parent().parent().removeClass('table-warning');
        $(ele).parent().parent().parent().next().removeClass('table-warning');
	}
}

function submit_items(act){
	if(act=='delete'){
		// check selected item
		if($('input.item_chx:checked').get().length<=0){
			notify('error','Please checked at least one item.','center');
			return false;
		}
	
		check_delete_status();
		
		if(!confirm('Click OK to confirm delete.')) return false;
        document.f_a['a'].value = 'delete_items';
	}else{
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
        document.f_a['a'].value = 'save_items';
	}

	document.f_a.submit();
}

function check_type(id){
	if(id == undefined || id == "") return;
	
	if(document.f_a.elements['type['+id+']'].value == "PO"){
		document.f_a.elements['ctn['+id+']'].value = "";
		document.f_a.elements['ctn['+id+']'].readOnly = true;
		document.f_a.elements['pcs['+id+']'].value = "";
		document.f_a.elements['pcs['+id+']'].readOnly = true;
		document.f_a.elements['amount['+id+']'].value = "";
		document.f_a.elements['amount['+id+']'].readOnly = true;
		if(is_under_gst == 1 && document.f_a.elements['gst_amount['+id+']'] != undefined){
			document.f_a.elements['gst_amount['+id+']'].value = "";
			document.f_a.elements['gst_amount['+id+']'].readOnly = true;
		}
	}else{
		document.f_a.elements['ctn['+id+']'].readOnly = false;
		document.f_a.elements['pcs['+id+']'].readOnly = false;
		document.f_a.elements['amount['+id+']'].readOnly = false;
		if(is_under_gst == 1 && document.f_a.elements['gst_amount['+id+']'] != undefined){
			document.f_a.elements['gst_amount['+id+']'].readOnly = false;
		}
	}
}

function add_row(){
	var d = new Date();
	var id = d.getTime();
	var new_tr1 = $('#temp_gi_row1').clone().attr('id', '').attr('class', '').addClass('is_new');
	var new_tr2 = $('#temp_gi_row2').clone().attr('id', '').attr('class', '');
	var new_tr_html1 =$(new_tr1).html();
	var new_tr_html2 =$(new_tr2).html();
	row_count=int(row_count)+1;
	new_tr_html1 = new_tr_html1.replace(/__row_num__/g,row_count);
	new_tr_html1 = new_tr_html1.replace(/__id__/g, id);
	new_tr_html2 = new_tr_html2.replace(/__id__/g, id);
	
	$(new_tr1).html(new_tr_html1);
	$(new_tr2).html(new_tr_html2);
	$('#gi_list').append(new_tr1);
	$('#gi_list').append(new_tr2);
	$('span.item_count').html(float($('span.item_count').html())+1);
}

function check_delete_status(){
	var need_prompt_unsaved = false;
	
	$('#gi_list input.item_chx').each(function(){
		if(!this.checked){
			var tr_parent = $(this).parent().parent();
			if(tr_parent.hasClass('is_new')){
				need_prompt_unsaved = true;
			}
		}
	});
	
	if(need_prompt_unsaved){
		alert('Attention: You have new unsaved item, proceeding to delete will lost the new item.');
	}
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.scan_product.name}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">{$LANG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $smarty.request.find_grr}
		<li class="breadcrumb-item">
			<a href="goods_receiving_record.php?a=open&find_grr={$smarty.request.find_grr}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
	</ol>
</nav>
<!-- /BreadCrumbs -->

<!-- Error Message -->
{if $err.top}
	{foreach from=$err.top item=e}
	<div class="alert alert-danger mg-b-0 animated fadeInDown mb-2" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		 {$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message --> 

{include file='goods_receiving_record.top_include.tpl'}<br>

<table style="display: none;">
	<tr id="temp_gi_row1" class="temp_gi_row1">
		<td>__row_num__</td>
		<td>
			<input type="checkbox" name="item_chx[__id__]" class="item_chx" />
			<input type="hidden" name="gi_id[__id__]" />
		</td>
		<td>
			<div class="row">
				<label class="col-2">{$LNG.NO}:</label>
				<input type="text" class="form-control form-control-sm min-w-100 col" name="doc_no[__id__]" size="8">
			</div>
			<div class="row mt-1">
				<label class="col-2">{$LNG.DATE}:</label>
				<input type="text" class="form-control form-control-sm min-w-100 col" name="doc_date[__id__]" size="8">
			</div>
			<input type="hidden" name="prev_doc_no[__id__]" />
			<input type="hidden" name="prev_type[__id__]" />
		</td>
		<td>
			<select name="type[__id__]" class="item_type form-control form-control-sm min-w-100 " onchange="check_type(__id__);">
				<option value="PO" selected>{$LNG.PO}</option>
				<option value="INVOICE">{$LNG.INV}</option>
				<option value="DO">{$LNG.DO}</option>
				<option value="OTHER">{$LNG.OTH}</option>
			</select>
		</td>
		<td><input type="text" name="ctn[__id__]" value="" size="5" class="r form-control form-control-sm min-w-100" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" readonly /></td>
		<td><input type="text" name="pcs[__id__]" value="" size="5" class="r form-control form-control-sm min-w-100" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" readonly /></td>
	</tr>
	<tr id="temp_gi_row2" class="temp_gi_row2">
		<td colspan="6">
			<div class="d-flex flex-row justify-content-end align-items-center">
				<label class="mr-2">{$LNG.AMOUNT}</label>
				<input type="text" name="amount[__id__]" value="" size="6" class="r form-control min-w-100 max-w-200" onChange="mf(this);" readonly />
			</div>			
			{if $form.is_under_gst}
				<div class="d-flex flex-row justify-content-end align-items-center mt-1">
					<label class="mr-2">{$LNG.GST_AMOUNT}</label>
					<input type="text" name="gst_amount[__id__]" value="" size="6" class="r form-control min-w-100 max-w-200" onChange="mf(this);" readonly />
				</div>	
			{/if}
			<div class="d-flex flex-row justify-content-end align-items-center mt-1">
				<label class="mr-2">{$LNG.REMARK}</label>
				<input type="text" name="remark[__id__]" class="form-control min-w-100 max-w-200" value="" size="20" />
			</div>	
		</td>
	</tr>
</table>
<div class="alert alert-info col-md-4 animated fadeInLeft"><i class="fas fa-bullhorn"></i> Document Date in YYYY-MM-DD format.</div>
<div class="card animated fadeInLeft">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center py-2">
			<div class="badge badge-pill badge-light p-2 border">{count var=$items} {$LNG.ITEMS}</div>
			<div class="">
				<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-info" onClick="add_row();"><i class="fas fa-plus"></i> {$LNG.ADD_ROW}</button>
				<button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			</div>
		</div>
		<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th rowspan="2">#</th>
								<th width="20" rowspan="2">
									<div class="custom-checkbox custom-control">
										<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="toggle_chx custom-control-input" id="checkbox-[{$r.id}]">
										<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1">{$LNG.DEL}</label>
									</div>
								</th>
								<th rowspan="2">{$LNG.DOC_NO_DATE}</th>
								<th rowspan="2">{$LNG.DOC_TYPE}</th>
								<th colspan="2" class="text-center">{$LNG.QTY}</th>
							</tr>
							<tr class="text-center">
								<th>{$LNG.CTN}</th>
								<th>{$LNG.PCS}</th>
							</tr>
						</thead>
						<tbody id="gi_list">
							{foreach from=$items item=r name=i}
								<tr>
									<td>{$smarty.foreach.i.iteration}.</td>
									<td>
										<div class="custom-checkbox custom-control">
											<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
											<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
										</div>
										<input type="hidden" name="gi_id[{$r.id}]" value="{$r.id}" />
									</td>
									<td>
										<div class="row">
											<label class="col-2">{$LNG.NO}:</label>
											<input type="text" class="form-control form-control-sm min-w-100 col" name="doc_no[{$r.id}]" value="{$r.doc_no}" size="8">
										</div>
										<div class="row mt-1">
											<label class="col-2">{$LNG.DATE}:</label>
											<input type="text" class="form-control form-control-sm col min-w-100" name="doc_date[{$r.id}]" value="{$r.doc_date}" size="8">
										</div>				
										<input type="hidden" name="prev_doc_no[{$r.id}]" value="{$r.prev_doc_no|default:$r.doc_no}" />
										<input type="hidden" name="prev_type[{$r.id}]" value="{$r.prev_type|default:$r.type}" />
									</td>
									<td>
										<select name="type[{$r.id}]" class="item_type txt-width form-control form-control-sm min-w-100 select2" onchange="check_type({$r.id});">
											<option value="PO" {if $r.type eq 'PO'}selected{/if}>{$LNG.PO}</option>
											<option value="INVOICE" {if $r.type eq 'INVOICE'}selected{/if}>{$LNG.INV}</option>
											<option value="DO" {if $r.type eq 'DO'}selected{/if}>{$LNG.DO}</option>
											<option value="OTHER" {if $r.type eq 'OTHER'}selected{/if}>{$LNG.OTH}</option>
										</select>
									</td>
									<td align="center"><input type="text" name="ctn[{$r.id}]" value="{$r.ctn}" size="5" class="r form-control form-control-sm min-w-100" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" {if $r.type eq 'PO'}readonly{/if} /></td>
									<td align="center"><input type="text" name="pcs[{$r.id}]" value="{$r.pcs}" size="5" class="r form-control form-control-sm min-w-100" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" {if $r.type eq 'PO'}readonly{/if} /></td>
								</tr>
								<tr>
									<td colspan="6">
										<div class="d-flex flex-row justify-content-end align-items-center">
											<label class="mr-2">{$LNG.AMOUNT}</label>
											<input type="text" name="amount[{$r.id}]" value="{$r.amount}" class="r form-control min-w-100 max-w-200" onChange="mf(this);" {if $r.type eq 'PO'}readonly{/if} />
										</div>
										{if $form.is_under_gst}
											<div class="d-flex flex-row justify-content-end align-items-center mt-1">
												<label class="mr-2">{$LNG.GST_AMOUNT}</label>
												<input type="text" name="gst_amount[{$r.id}]" value="{$r.gst_amount}" class="r form-control min-w-100 max-w-200" onChange="mf(this);" {if $r.type eq 'PO'}readonly{/if}  />
											</div>
										{/if}
										<div class="d-flex flex-row justify-content-end align-items-center mt-1">
											<label class="mr-2">{$LNG.REMARK}</label>
											<input type="text" name="remark[{$r.id}]" value="{$r.remark}" class="txt-width-30 form-control min-w-100 max-w-200" />
										</div>	
									</td>
								</tr>
								{if $err[$r.id]}
									<tr>
										<td colspan="8">
											<div class="d-flex justify-content-center align-items-center">
												<div class="alert alert-danger col-md-6">
													{foreach from=$err[$r.id] item=e}
													<button aria-label="Close" class="close" data-dismiss="alert" type="button">
														<span aria-hidden="true">&times;</span>
													</button>
														&middot;{$e}
													{/foreach}
												</div>
											</div>
										</td>
									</tr>
								{/if}
							{/foreach}
						</tbody>
					</table>
				</div>
			</form>
			<div class="d-flex justify-content-end align-items-center py-2">
					<button class="btn btn-danger mr-1" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
					<button class="btn btn-info mr-1" onClick="add_row();"><i class="fas fa-plus"></i> {$LNG.ADD_ROW}</button>
					<button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			</div>
	</div>
</div>
<br />
<script>
{literal}
    $('input.item_chx').on('click', function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').on('click', function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
