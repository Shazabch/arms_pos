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
    if($(ele).attr('checked')){
		$(ele).parent().parent().css('background-color','yellow');
	}else{
        $(ele).parent().parent().css('background-color','#fff');
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0){
			alert('Please checked at least one item.');
			return false;
		}
		if(!confirm('Click OK to confirm delete.')) return false;
		
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
			<a href="home.php">Back to home</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a>
		</li>
		{if $smarty.request.find_batch_barcode}
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">Back to search</a>
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

{include file='batch_barcode.top_include.tpl'}<br><br>
<div class="card animated fadeInDown">
	<div class="card-body">
		{if $items}
		<div class="d-flex justify-content-between align-items-center py-2">
			<div class="badge badge-pill badge-light p-2 border">{count var=$items} item(s)</div>
			<div class="">
				<button class="btn btn-danger btn-sm" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
				<button class="btn btn-success btn-sm" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
			</div>
		</div>
		<!--Table-->
		<div class="col-xl-12 mt-2">
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" />
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-hover mb-0 text-md-nowrap">
								<thead>
									<tr>
										<th>#</th>
								        <th>
								        	<div class="checkbox">
												<div class="custom-checkbox custom-control">
													<input type="checkbox" data-checkboxes="mygroup" class="toggle_chx custom-control-input" id="checkbox-2">
													<label for="checkbox-2" class="custom-control-label">DEL</label>
												</div>
											</div>
								        </th>
								        <th>ARMS Code</th>
								        <th>Description</th>
										<th>Qty</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$items key=row item=r name=i}
								        <tr>
								        	<td>{$smarty.foreach.i.iteration}.</td>
								            <td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
								            <td>{$r.sku_item_code}</td>
								            <td>{$r.sku_description}</td>
											<td>
												<input type="text" name="qty[{$r.id}]" class="qty items r form-control form-control-sm" size="3" value="{$r.qty}" />
											</td>
								        </tr>
								    {/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- /Table -->
		<div class="d-flex justify-content-end align-items-center py-2">
			<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
			<button class="btn btn-success" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
		</div>
		{else}
			<div class="alert alert-danger">
				No Item
			</div>
		{/if}
	</div>
</div>


<script>
{literal}
    $('input.item_chx').click(function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').click(function(){
		$('input.item_chx').attr('checked',$(this).attr('checked')).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
