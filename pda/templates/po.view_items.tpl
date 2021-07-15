{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

04/17/2020 04:16 PM Sheila
- Modified layout to compatible with new UI.

9/18/2020 5:07 PM William
- Enhanced to show error message.

*}
{include file='header.tpl'}
<script>
{literal}

function change_row_color(ele){
    if($(ele).is(":checked")){
		$(ele).parent().parent().parent()..addClass('table-warning');
	}else{
        $(ele).parent().parent().parent()..removeClass('table-warning');
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0){
			notify('error','Please checked at least one item.','center');
			return false;
		}
		if(!confirm('Click OK to confirm delete.')) return false;
		
        document.f_a['a'].value = 'delete_items';
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
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=po">{$module_name}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="po.php?a=open">Back to Search</a>
		</li>
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

{include file='po.top_include.tpl'}<br><br>

<div class="card animated fadeInLeft">
	<div class="card-body">
		{if $items}
			<div class="d-flex justify-content-between align-items-center">
				<div class="badge badge-pill badge-light p-2">{count var=$items} item(s)</div>
				<div>
					<button class="btn btn-danger" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
					<button class="btn btn-success" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
				</div>
			</div>
			<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table mb-0 text-md-nowrap">
						<thead>
						    <tr>
						    	<th>#</th>
						        <th>
						        	<div class="custom-checkbox custom-control">
										<input type="checkbox" class="toggle_chx custom-control-input" id="checkbox-del0">
										<label for="checkbox-del0" class="custom-control-label mt-1">Del</label>
									</div>
						        </th>
						        <th>Description</th>
								{if $sessioninfo.branch_id eq 1 && $items[0].branch_code}
									<th>Branch</th>
								{/if}
						        <th>Qty<small>(pcs)</small></th>
								<th>Foc<small>(pcs)</small></th>
						    </tr>
						</thead>
					    {assign var="no" value=1}
					    {foreach from=$items item=r name=i}
					        <tr>
					        	<td >{$smarty.foreach.i.iteration}.</td>
					            <td >
					            	<div class="custom-checkbox custom-control">
										<input type="checkbox" name="item_chx[{$r.id}]" class="custom-control-input item_chx" id="checkbox-[{$r.id}]">
										<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
									</div>
					            </td>
					            <td >{$r.sku_description}</td>
								{if $sessioninfo.branch_id eq 1 && $r.branch_code}
								<td >
									<table>
										{if $r.branch_code}
											{foreach from=$r.branch_code item=b_code}
												<tr><td>{$b_code}</td></tr>
											{/foreach}
										{/if}
									</table>
								</td>
								{/if}
								<td>
									<table>
									{if $r.multi_bid}
										{foreach from=$r.multi_bid item=bid}
											<tr><td><input type="text" class="form-control min-w-100" name="item_qty[{$r.id}][{$bid}]" value="{$r.qty_pcs.$bid}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
										{/foreach}
									{else}
										<tr><td><input type="text" class="form-control min-w-100" name="qty_loose[{$r.id}]" value="{$r.qty_loose}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
									{/if}
									</table>
								</td>
								
								<td >
									<table>
									{if $r.multi_bid}
										{foreach from=$r.multi_bid item=bid}
											<tr><td><input type="text" class="form-control min-w-100" name="foc_qty[{$r.id}][{$bid}]" value="{$r.foc_pcs.$bid}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
										{/foreach}
									{else}
										<tr><td><input type="text" name="foc_loose[{$r.id}]" value="{$r.foc_loose}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
									{/if}
									</table>
								</td>
					        </tr>
					    {/foreach}
					</table>
				</div>
			</form>
			
			<div class="d-flex justify-content-end">
		        <button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
				<button class="btn btn-success" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
			</div>
		{else}
			<div class="row">
				<div class="col-lg-12" style="max-height: 80vh;">
					<div class=" mg-b-20 text-center">
						<div class=" h-100">
							<img src="../../assets/img/svgicons/note_taking.svg" alt="" class="wd-35p" style="max-height: 50vh;">
							<h5 class="mg-b-10 mg-t-15 tx-18">Its Empty In Here</h5>
							<a href="#" class="text-muted">No Item</a>
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
	
	$('input.toggle_chx').click(function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}