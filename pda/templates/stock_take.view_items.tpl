{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

2/24/2014 4:24 PM Andy
- Fix the variable bug. (sometime "loc" sometime "location").
- Added link to allow user travel to "item list" or "Scan".

04/17/2020 03:50 PM Sheila
- Modified layout to compatible with new UI.

9/21/2020 9:07 AM William
- Enhanced to show error message.

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
*}
{include file='header.tpl'}

<script type="text/javascript">
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
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.st.title}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=show_scan">Stock Take</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=view_items">View Items</a>
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

<div class="card animated fadeInLeft">
	<div class="card-body" style="background:#fff">
		{if $items}
			<div class="d-flex justify-content-between align-items-center">
				<div class="badge badge-pill badge-light p-2">{count var=$items} item(s)</div>
				<div>
					<button class="btn btn-danger" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
					<button class="btn btn-success" id="submit_btn1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
				</div>
			</div>
			<form name="f_a" method="post" onSubmit="return false;">
			<div style="clear:both;"></div>

			<input type="hidden" name="a" />
				<div class="table-responsive">
					<table class="table mb-0 text-md-nowrap">
					    <thead>
					    	<tr>
						    	<th>#</th>
						        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
						        <th>ARMS Code</th>
						        <th>Description</th>
						        <th class="center">Qty <small>(pcs)</small></th>
						    </tr>
					    </thead>
					    <tbody>
					    	{foreach from=$items item=r name=i}
						        <tr>
						        	<td>{$smarty.foreach.i.iteration}.</td>
						            <td><input type="checkbox" name="item_chx[{$r.item_id}]" class="item_chx" /></td>
						            <td>
										{$r.sku_item_code}
										{if $r.mcode}
											<br /><font color="blue">{$r.mcode}</font>
										{/if}
										{if $r.artno}
											<br /><font color="brown">{$r.artno}</font>
										{/if}
									</td>
						            <td valign="top">{$r.description}</td>
						            <td><input type="text" name="qty[{$r.item_id}]" value="{$r.qty}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r form-control form-control-sm min-w-100" /></td>
						        </tr>
						    {/foreach}
					    </tbody>
					</table>
				</div>
			</form>
			<div class="d-flex justify-content-end align-items-center mt-2">
				<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
				<button class="btn btn-success" id="submit_btn2" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
			</div>
		{else}
			<div class="alert alert-danger">No Item</div>
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
