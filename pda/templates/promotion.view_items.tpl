{*

04/17/2020 04:28 PM Sheila
- Modified layout to compatible with new UI.

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
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
			notify('error','Please checked at least one item.','center');
			return false;
		}
		if(!confirm('Click OK to confirm delete.')) return false;
		
		document.f_a['a'].value = 'delete_items';
	}else{
		document.f_a['a'].value = 'save_items';
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
	}
	document.f_a.submit();
}
{/literal}
</script>
<!-- Get Current url before the 1st occuring of ? | For Pagination -->
{php}
$this->assign('ur',strtok($_SERVER["REQUEST_URI"],'?'));
{/php}
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
			<a href="home.php?a=menu&id=promotion">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

{include file='promotion.top_include.tpl'}<br><br>


<div class="card animated fadeInLeft">
	<div class="card-body" style="background:#fff">
		{if $items}
			<div class="d-flex justify-content-end align-items-center">
					<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
					{*<button class="btn btn-success btn-sm" onClick="submit_items('save');"><i class="fas fa-save"></i> Delete</button>*}
			</div>
			{if $total_rows>$records_per_page}
				<div class="row my-2 border-bottom pb-2">
					<div class="col">
						<ul class="pagination pagination-circled mb-0">
							{foreach from=$page_list item=p}
								{if $p eq $page}
									<li class="page-item active"><a class="page-link" href="javascript:void(0)">{$p}</a></li>
								{else}
									<li class="page-item"><a class="page-link" href="{$ur}?a=view_items&page={$p}">{$p}</a></li>
								{/if}
							{/foreach}
						</ul>
					</div>
					<div class="col d-none d-md-flex d-lg-flex d-xl-flex d-xxl-flex justify-content-end align-items-center">
						<div class="text-right text-muted py-auto my-auto fs-08">
							Showing items <b>{$start_row}-{$end_row}</b> from total of <b>{$total_rows}</b> items.
						</div>
					</div>
				</div>
			{/if}
			

			<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th>#</th>
								<th>
									<div class="custom-checkbox custom-control">
										<input type="checkbox" class="toggle_chx custom-control-input" id="checkbox-delasd">
										<label for="checkbox-delasd" class="custom-control-label mt-1">Del</label>
									</div>
								</th>
								<th>ARMS Code</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$items item=r name=i}
								<tr>
									<td>{$smarty.foreach.i.iteration+$start_row-1}.</td>
									<td>
										<div class="custom-checkbox custom-control">
											<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
											<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
										</div>
									</td>
									<td>{$r.sku_item_code}</td>
									<td>{$r.description}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</form>
			<div class="d-flex d-sm-none justify-content-start align-items-center mt-2">
				<div class="text-left text-muted py-auto my-auto fs-08">
					Showing items <b>{$start_row}-{$end_row}</b> from total of <b>{$total_rows}</b> items.
				</div>
			</div>
			<div class="d-flex justify-content-end align-items-center mt-2">
				<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
				{*<button class="btn btn-success " onClick="submit_items('save');"><i class="fas fa-save"></i> Delete</button>*}
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
	
	$('input.toggle_chx').on('click', function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
