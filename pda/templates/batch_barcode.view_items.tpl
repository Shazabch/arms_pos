{*

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

10/23/2020 2:05 PM William
- Enhanced to addd new qty column and button "save".

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

12/17/2020 9:56 AM Andy
- Fixed spelling mistake.
*}

{include file='header.tpl'}

<script>
{literal}

function change_row_color(ele){
    if($(ele).is(":checked")){
		$(ele).parent().parent().parent().addClass('table-warning');
	}else{
        $(ele).parent().parent().parent().removeClass('table-warning');
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0){
			notify('error','{/literal}{$LNG.PLEASE_CHECK_AT_LEAST_ONE_ITEM}{literal}','center');
			return false;
		}
		if(!confirm('{/literal}{$LNG.DELETE_CONFIRMATION_MSG}{literal}?')) return false;
		
        document.f_a['a'].value = 'delete_items';
	}else if(act=='save'){
		var qty_list = $('input.qty').get();
		for(var i=0; i < qty_list.length; i++){
			if(qty_list[i].value <= 0){
				alert('Invalid Qty');
				qty_list[i].focus();
				return false;
			}
		}
		document.f_a['a'].value = 'save';
	}else{
        document.f_a['a'].value = 'save_items';
	}
	document.f_a.submit();
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.scan_product.name}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.BACK_TO_HOME}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
		{if $smarty.request.find_batch_barcode}
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
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

{include file='batch_barcode.top_include.tpl'}<br>
<div class="card ">
	<div class="card-body">
		{if $items}
		<div class="d-flex justify-content-between align-items-center py-2">
			<div class="badge badge-pill badge-light p-2 border">{count var=$items} {$LNG.ITEMS}</div>
			<div class="">
				<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-success " onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			</div>
		</div>
		<!--Table-->
		<div class="col-xl-12 mt-2">
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th>#</th>
						        <th>
						        	<div class="custom-checkbox custom-control">
										<input type="checkbox" class="toggle_chx custom-control-input" id="checkbox-2">
										<label for="checkbox-2" class="custom-control-label">{$LNG.DEL}</label>
									</div>
						        </th>
						        <th>{$LNG.ARMS_CODE}</th>
						        <th>{$LNG.DESCRIPTION}</th>
								<th>{$LNG.QTY}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$items key=row item=r name=i}
						        <tr>
						        	<td>{$smarty.foreach.i.iteration}.</td>
						            <td>
						            	<div class="custom-checkbox custom-control">
											<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
											<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
										</div>
						            </td>
						            <td>{$r.sku_item_code}</td>
						            <td>{$r.sku_description}</td>
									<td>
										<input type="text" name="qty[{$r.id}]" class="qty items r form-control form-control-sm min-w-100" size="3" value="{$r.qty}" />
									</td>
						        </tr>
						    {/foreach}
						</tbody>
					</table>
				</div>
			</form>
		</div>
		<!-- /Table -->
		<div class="d-flex justify-content-end align-items-center py-2">
			<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
			<button class="btn btn-success" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
		</div>
		{else}
			<div class="row">
				<div class="col-lg-12" style="max-height: 80vh;">
					<div class=" mg-b-20 text-center">
						<div class=" h-100">
							<img src="../../assets/img/svgicons/note_taking.svg" alt="" class="wd-35p" style="max-height: 50vh;">
							<h5 class="mg-b-10 mg-t-15 tx-18">{$LNG.ITS_EMPTY_HERE}</h5>
							<a href="#" class="text-muted">{$LNG.NO_ITEM}</a>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
</div>


<script>
{literal}
    $('input.item_chx').click(function(){
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
