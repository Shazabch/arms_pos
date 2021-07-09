{*
8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 5:58:11 PM Justin
- Modified the form layout to fill under PDA screen.
- Removed common js functions since it is already assigned at forms.js.

7/25/2012 4:05:32 PM yinsee
- add row number for items

7/25/2012 6:04:34 PM Justin
- Enhanced to have UOM control.

9/5/2012 11:20 AM Justin
- Enhanced to disable UOM selection while found config "doc_disable_edit_uom".

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/17/2020 04:28 PM Sheila
- Modified layout to compatible with new UI.

9/21/2020 11:31 AM William
- Enhanced to show error message.

04/11/2020 5:10 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page

*}

{include file='header.tpl'}

<script>

var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}

function change_row_color(ele){
    if($(ele).is(":checked")){
		$(ele).parent().parent().parent().addClass('bg-warning');
		$(ele).parent().parent().parent().next().addClass('bg-warning');
	}else{
        $(ele).parent().parent().parent().removeClass('bg-warning');
        $(ele).parent().parent().parent().next().removeClass('bg-warning');
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0 && $('input.isi_item_chx:checked').get().length<=0){
			notify('error','Please checked at least one item.','center');
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

function uom_changed(id){
	var fraction = $("#uom_"+id+" option:selected").attr("fraction");
	if(fraction == 1){
		document.f_a["ctn["+id+"]"].disabled = true;
	}else{
		document.f_a["ctn["+id+"]"].disabled = false;
	}
}

function bom_ratio_calculation(id){
	if(!id) return;
	
	if(document.f_a['bom_ref_num['+id+']'] && document.f_a['bom_ref_num['+id+']'].value.trim() > 0){
		// is bom package
		var bom_ref_num = document.f_a['bom_ref_num['+id+']'].value.trim();
		var bom_qty_ratio = float(document.f_a['bom_qty_ratio['+id+']'].value);
		var multiply_ratio = 0;
		var doc_allow_decimal = int(document.f_a['doc_allow_decimal['+id+']'].value);
		
		var ctn = 0;
		if(document.f_a['ctn['+id+']'] && document.f_a['ctn['+id+']'].value.trim() > 0){
			ctn = float(document.f_a['ctn['+id+']'].value);
		}
		var pcs = float(document.f_a['pcs['+id+']'].value);
		var uom_fraction = $("#uom_"+id+" option:selected").attr("fraction");
		var total_pcs = (ctn * uom_fraction) + pcs;
		
		multiply_ratio = float(round(total_pcs / bom_qty_ratio,4));
		
		var bom_ref_num_list = $('.bom_ref_num_grp_'+bom_ref_num);
		
		if(int(multiply_ratio) != multiply_ratio){	// not allow decimal
			var group_allow_decimal_qty = true;
		
			// loop to check item can decimal qty or not
			for(var i=0; i<bom_ref_num_list.length; i++){
				// get the row grn item id + doc type
				var item_id = $(bom_ref_num_list[i]).attr('item_id');
				var tmp_doc_allow_decimal = int(document.f_a['doc_allow_decimal['+item_id+']'].value);

				if(!tmp_doc_allow_decimal){
					group_allow_decimal_qty = false;
					break;
				}
			}
			
			if(!group_allow_decimal_qty) multiply_ratio = int(multiply_ratio);	// group cannot hv decimal, make int
		}
		
		// loop to update qty
		for(var i=0; i<bom_ref_num_list.length; i++){
			// get the row grn item id + doc type
			var item_id = $(bom_ref_num_list[i]).attr('item_id');
			
			var item_bom_qty_ratio = float(document.f_a['bom_qty_ratio['+item_id+']'].value);
			
			var item_uom_fraction = uom_fraction = $("#uom_"+item_id+" option:selected").attr("fraction");
			
			var item_ctn = 0;
			var item_pcs = 0;
			var item_total_pcs = item_bom_qty_ratio * multiply_ratio;
			 
			if(item_uom_fraction > 1){
				item_ctn = Math.floor(item_total_pcs / item_uom_fraction);
				item_pcs = item_total_pcs - (item_ctn * item_uom_fraction);
			}else{
				item_pcs = item_total_pcs;
			}
			
			if(document.f_a['ctn['+item_id+']'] && document.f_a['ctn['+item_id+']'].value.trim() > 0){
				if(item_uom_fraction > 1){
					document.f_a['ctn['+item_id+']'].value = item_ctn;
				}else{
					document.f_a['ctn['+item_id+']'].value = "";
				}
			}
			
			document.f_a['pcs['+item_id+']'].value = item_pcs;
			
			// recal row
			//if(tmp_po_item_id != po_item_id) row_recalc(tmp_po_item_id);
		}
	}
}

function check_by_bom_group(obj, bom_id){
	if(!bom_id) return;

	var bom_ref_num_list = $('.bom_ref_num_grp_'+bom_id);
	
	// loop to check item can decimal qty or not
	for(var i=0; i<bom_ref_num_list.length; i++){
		var item_id = $(bom_ref_num_list[i]).attr('item_id');
		if($(obj).attr('checked')){
			var row = document.f_a['item_chx['+item_id+']'];
			document.f_a['item_chx['+item_id+']'].checked = true;
			$(row).parent().parent().css('background-color','yellow');
			$(row).parent().parent().next().css('background-color','yellow');
		}else{
			var row = document.f_a['item_chx['+item_id+']'];
			document.f_a['item_chx['+item_id+']'].checked = false;
			$(row).parent().parent().css('background-color','#fff');
			$(row).parent().parent().next().css('background-color','#fff');
		}
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
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $smarty.request.find_grn}
		<li class="breadcrumb-item">
			<a href="goods_receiving_note.php?a=open&find_grn={$smarty.request.find_grn}">Back to Search</a>
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

{include file='goods_receiving_note.top_include.tpl'}<br><br>

<div class="card animated fadeInLeft">
	<div class="card-body">
	{if count($items) > 0 || count($non_sku_items.code) > 0}
	<div class="d-flex justify-content-end align-items-center">
		<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
		<button class="btn btn-success mr-1" id="submit_btn1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
	</div>

		<form name="f_a" method="post" onSubmit="return false;">
		<input type="hidden" name="a" />
		<input type="hidden" name="find_grn" value="{$smarty.request.find_grn}" />
				{if count($items) > 0}
					<div class="badge badge-pill badge-light p-2">{count var=$items} item(s)</div>
					<div class="table-responsive">
						<table class="table table-hover mb-0 text-md-nowrap">
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
									<th class="text-center">UOM</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=$items item=r name=i}
									<tr>
										<td>{$smarty.foreach.i.iteration}.</td>
										<td>
											<div class="custom-checkbox custom-control">
												<input type="checkbox" name="item_chx[{$r.id}]" class="custom-control-input item_chx" id="checkbox-[{$r.id}]" {if $r.bom_ref_num > 0}onchange="check_by_bom_group(this, {$r.bom_ref_num});"{/if}>
												<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
											</div>
										</td>
										<td>{$r.sku_item_code}</td>
										<td>{$r.sku_description} {if $r.bom_ref_num > 0}<font color="grey">(BOM)</font>{/if}</td>
										<td class="min-w-80">
											<select class="form-control select2 min-w-100" name="uom[{$r.id}]" id="uom_{$r.id}" onchange="uom_changed('{$r.id}');" {if (!$config.doc_allow_edit_uom && $r.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled{/if}>
												{foreach from=$uom key=row item=id}
													<option value="{$uom.$row.id}" {if $uom.$row.id eq $r.uom_id}selected{/if} fraction="{$uom.$row.fraction}">{$uom.$row.code}</option>
												{/foreach}
											</select>
											{if (!$config.doc_allow_edit_uom && $r.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}
												<input type="hidden" name="uom[{$r.id}]" value="{$r.uom_id}">asdsa
											{/if}
										</td>
									</tr>
									<tr>
										<td align="right" colspan="5">
											Ctn
											<input type="number" name="ctn[{$r.id}]" class="items r form-control form-control-sm max-w-100 min-w-80" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onchange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} {if $r.bom_ref_num > 0}bom_ratio_calculation({$r.id});{/if}" value="{$r.ctn}" {if $r.uom_fraction eq '1'}disabled{/if} />
											Pcs
											<input type="number" name="pcs[{$r.id}]" class="items r form-control form-control-sm max-w-100 min-w-80" {if $r.bom_ref_num > 0}bom_ref_num_grp_{$r.bom_ref_num}{/if}" item_id="{$r.id}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onchange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} {if $r.bom_ref_num > 0}bom_ratio_calculation({$r.id});{/if}" value="{$r.pcs}" />
										</td>
										<input type="hidden" name="bom_ref_num[{$r.id}]" value="{$r.bom_ref_num}" />
										<input type="hidden" name="bom_qty_ratio[{$r.id}]" value="{$r.bom_qty_ratio}" />
										<input type="hidden" name="doc_allow_decimal[{$r.id}]" value="{$r.doc_allow_decimal}" />
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				{/if}

			{if $non_sku_items.code}
				<div class="card-title pb-2 border-bottom">SKU Not In ARMS</div>
				<div class="badge badge-pill badge-light p-2">{count var=$non_sku_items.code} item(s)</div>
				<div class="table-responsive mt-2">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th>#</th>
								<th>
									<div class="custom-checkbox custom-control">
										<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="isi_toggle_chx custom-control-input" id="checkbox-d">
										<label for="checkbox-d" class="custom-control-label mt-1">Del</label>
									</div>
								</th>
								<th>Description</th>
								<th>Qty(pcs)</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$non_sku_items.code key=code item=r name=isi}
								{assign var=n value=$smarty.foreach.isi.iteration-1}
								<tr>
									<td>{$smarty.foreach.isi.iteration}.</td>
									<td>
										<input type="checkbox" name="isi_item_chx[{$n}]" class="isi_item_chx" />
										<div class="custom-checkbox custom-control">
											<input type="checkbox" data-checkboxes="mygroup" name="isi_item_chx[{$n}]" class="isi_item_chx custom-control-input" id="checkbox-[{$n}]">
											<label for="checkbox-[{$n}]" class="custom-control-label mt-1"></label>
										</div>
										<input type="hidden" name="isi_code[{$n}]" value="{$non_sku_items.code.$n}" />

									</td>
									<td>
										<input type="text" class="form-control form-control-sm" name="isi_desc[{$n}]" size="35" value="{$non_sku_items.description.$n}" onchange="this.value = this.value.toUpperCase().trim();" />
									</td>
									<td>
										<input type="number" name="isi_qty[{$n}]" class="items r form-control form-control-sm" size="3" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" value="{$non_sku_items.qty.$n}" />
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			{/if}
			</form>
			<div class="d-flex justify-content-end align-items-center mt-2">
				<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> Delete</button>
				<button class="btn btn-success mr-1" id="submit_btn1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> Save</button>
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

	$('input.isi_item_chx').click(function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').click(function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});

	$('input.isi_toggle_chx').click(function(){
		var checked=$(this).is(':checked');
		$('input.isi_item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
