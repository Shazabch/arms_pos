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
	}else{
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
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
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $form.adj_no}
		<li class="breadcrumb-item">
			<a href="adjustment.php?a=open&adj_no={$form.adj_no}">Back to search</a>
		</li>
		{/if}
		{if $form.find_adjustment}
		<li class="breadcrumb-item">
			<a href="adjustment.php?a=open&find_adjustment={$form.find_adjustment}">Back to search</a>
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

{include file='adjustment.top_include.tpl'}<br><br>
<div class="card animated fadeInLeft">
	<div class="card-body">
		{if $items}
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div class="badge badge-pill badge-light p-2 ">{count var=$items} item(s)</div>
				<div>
					<button class="btn btn-danger"><i class="fas fa-trash-alt" onClick="submit_items('delete');"></i> Delete</button>
					<button class="btn btn-success"><i class="fas fa-save" onClick="submit_items('save');"></i> Save</button>
				</div>
			</div>
			<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table mb-0 text-md-nowrap">
					    <thead>
					    	<tr>
						    	<th rowspan="2">#</th>
						        <th rowspan="2"><input type="checkbox" class="toggle_chx" /> DEL</th>
						        <th rowspan="2">ARMS Code</th>
						        <th rowspan="2">Description</th>
						        <th colspan="2" class="text-center">Qty (pcs)</th>
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
					            <td>
									<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" />
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
				<button class="btn btn-danger mr-1"><i class="fas fa-trash-alt" onClick="submit_items('delete');"></i> Delete</button>
				<button class="btn btn-success"><i class="fas fa-save" onClick="submit_items('save');"></i> Save</button>
			</div>
		{else}
			<div class="alert alert-danger">No Item</div>
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
		$('input.item_chx').attr('checked',$(this).attr('checked')).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
