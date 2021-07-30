{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

9/22/2020 9:06 AM William
- Enhanced to show error message.

11/04/2020 6:00 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page
	-Add cellspacing in table
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
		Swal.fire({
		  title: '{/literal}{$LNG.ARE_YOU_SURE}{literal}?',
		  text: "{/literal}{$LNG.WONT_REVERT_THIS}{literal}",
		  icon: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: '{/literal}{$LNG.YES_DELETE_IT}{literal}!'
		}).then((result) => {
		  if (result.isConfirmed) {

		   document.f_a['a'].value = 'delete_items';
		   document.f_a.submit();
		   
		  }
		})

	}else{
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
        document.f_a['a'].value = 'save_items';
        document.f_a.submit();
	}
	
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
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $form.adj_no}
		<li class="breadcrumb-item">
			<a href="adjustment.php?a=open&adj_no={$form.adj_no}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
		{if $form.find_adjustment}
		<li class="breadcrumb-item">
			<a href="adjustment.php?a=open&find_adjustment={$form.find_adjustment}">{$LNG.BACK_TO_SEARCH}</a>
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

{include file='adjustment.top_include.tpl'}<br><br>
<div class="card ">
	<div class="card-body">
		{if $items}
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div class="badge badge-pill badge-light p-2 ">{count var=$items} {$LNG.ITEMS}</div>
				<div>
					<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i>
					{$LNG.DELETE}</button>
					<button class="btn btn-success" id="submit_btn1" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
				</div>
			</div>
			<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table mb-0 text-md-nowrap">
					    <thead>
					    	<tr>
						    	<th rowspan="2">#</th>
						        <th rowspan="2" class="text-left">
						        	<div class="custom-checkbox custom-control">
										<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input toggle_chx" id="checkbox-delete1">
										<label for="checkbox-delete1" class="custom-control-label mt-2">{$LNG.DEL}</label>
									</div>
						        </th>
						        <th rowspan="2">{$LNG.ARMS_CODE}</th>
						        <th rowspan="2">{$LNG.DESCRIPTION}</th>
						        <th colspan="2" class="text-center">{$LNG.QTY} ({$LNG.PCS})</th>
						    </tr>
							<tr>
								{if !$form.adj_type || $form.adj_type eq "+"}
									<th class="text-center">(+)</th>
								{/if}
								{if !$form.adj_type || $form.adj_type eq "-"}
									<th class="text-center">(-)</th>
								{/if}
							</tr>
					    </thead>
					    {foreach from=$items item=r name=i}
					        <tr>
					        	<td>{$smarty.foreach.i.iteration}.</td>
					            <td class="text-left">
									<div class="custom-checkbox custom-control">
										<input type="checkbox" name="item_chx[{$r.id}]" data-checkboxes="mygroup" class="custom-control-input item_chx" id="checkbox-[{$r.id}]">
										<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
									</div>
									<input type="hidden" name="item[{$r.id}]" value="{$r.id}" />
								</td>
					            <td>{$r.sku_item_code}</td>
					            <td>{$r.sku_description}</td>
								{if !$form.adj_type || $form.adj_type eq "+"}
									<td class="min-w-80">
										<input type="text" name="p_item_qty[{$r.id}]" value="{if $r.qty > 0}{$r.qty}{/if}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r form-control form-control-sm" />
									</td>
								{/if}
								{if !$form.adj_type || $form.adj_type eq "-"}
									<td class="min-w-80">
										<input type="text" name="n_item_qty[{$r.id}]" value="{if $r.qty < 0}{$r.qty|abs}{/if}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r form-control form-control-sm" />
									</td>
								{/if}
					        </tr>
					    {/foreach}
					</table>
				</div>
			</form>
			<div class="d-flex justify-content-end align-items-center mt-2">
				<button class="btn btn-danger mr-1" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-success" id="submit_btn2" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
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
<br style="clear:both;">
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
