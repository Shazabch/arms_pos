{*
10/11/2011 11:50:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

7/25/2012 5:08:43 PM yinsee
- add row number for items

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

9/21/2020 9:42 AM William
- Enhanced to show error message.

05/11/2020 1:38PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	-Remove class small in table and added cellspacing and cellpadding

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
			notify('error','Please checked at least one item','center')
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
			<a href="home.php?a=menu&id=do">DO</a>
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

{include file='do.top_include.tpl'}<br><br>

<div class="card animated fadeInLeft">
	<div class="card-body">
	{if $items}
		<div class="d-flex justify-content-between align-items-center mb-2">
			<div class="badge badge-pill badge-light p-2">{count var=$items} item(s)</div>
			<div>
		    	<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
		    	<button class="btn btn-success"  onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
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
					        <th>ARMS Code</th>
					        <th>Description</th>
					        <th>Qty<br />(pcs)</th>
					    </tr>
				    </thead>
				    {assign var="no" value=1}
				    {foreach from=$items item=r name=i}
				        <tr>
				        	<td>{$smarty.foreach.i.iteration}.</td>
				            <td>
				            	<div class="custom-checkbox custom-control">
									<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
									<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
								</div>
				            </td>
				            <td>{$r.sku_item_code}</td>
				            <td>{$r.sku_description}</td>
				            <td><input type="text" class="form-control form-control-sm min-w-80"name="item_qty[{$r.id}]" value="{$r.pcs}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
				        </tr>
				    {/foreach}
				</table>
			</div>
		</form>
		<div class="d-flex justify-content-end align-items-center mt-2">
			<button class="btn btn-danger mr-1 " onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
		    <button class="btn btn-success"  onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
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
