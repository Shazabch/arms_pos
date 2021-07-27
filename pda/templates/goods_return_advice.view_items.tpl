{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

4/24/2015 10:54 AM Justin
- Enhanced to capture Document No. and GST information.

7/21/2017 11:50 AM Justin
- Enhanced to keep sku_item_id and selling_price as hidden for save data usage.

8/7/2017 1:47 PM Justin
- Enhanced to store gst selling price onto the hidden field.

04/17/2020 04:28 PM Sheila
- Modified layout to compatible with new UI.

11/04/2020 3:38 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)  and link to module menu page
	-Add cellspacing in table
*}

{include file='header.tpl'}

<script>
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
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">
				{if $smarty.session.scan_product.name}
					$smarty.session.scan_product.name
				{else}
					{$LNG.ITEMS_LIST}
				{/if}
			</h4>
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
	</ol>
</nav>
<!-- /BreadCrumbs -->

{include file='goods_return_advice.top_include.tpl'}<br><br>

<div class="card ">
	<div class="card-body">
	{if $items}
		<div class="d-flex justify-content-between align-items-center">
			<div class="badge badge-pill badge-light p-2 border">{count var=$items} item(s)</div>
			<div>
		    	<button class="btn btn-danger" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
		    	<button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
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
									<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="toggle_chx custom-control-input" id="checkbox-del0">
									<label for="checkbox-del0" class="custom-control-label mt-1">{$LNG.DEL}</label>
								</div>
					        </th>
					        <th>{$LNG.ARMS_CODE}</th>
					        <th>{$LNG.DESCRIPTION}</th>
					        <th>{$LNG.QTY} <small>({$LNG.PCS})</small></th>
					        <th>{$LNG.PRICE}</th>
					    </tr>
				    </thead>
				    {foreach from=$items item=r name=i}
				        <tbody>
				        	<tr>
					        	<td>{$smarty.foreach.i.iteration}.</td>
					            <td>
									<div class="custom-checkbox custom-control">
										<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.id}]" class="item_chx custom-control-input" id="checkbox-[{$r.id}]">
										<label for="checkbox-[{$r.id}]" class="custom-control-label mt-1"></label>
									</div>
									<input type="hidden" name="sku_item_id[{$r.id}]" value="{$r.sku_item_id}" />
									<input type="hidden" name="selling_price[{$r.id}]" value="{$r.selling_price}" />
									<input type="hidden" name="gst_selling_price[{$r.id}]" value="{$r.gst_selling_price}" />
								</td>
					            <td>{$r.sku_item_code}</td>
					            <td>{$r.sku_description}</td>
					            <td><input type="number" name="item_qty[{$r.id}]" value="{$r.qty}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r form-control  min-w-80" /></td>
					            <td><input type="text" name="item_price[{$r.id}]" value="{$r.cost}" size="10" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points}));" class="r form-control  min-w-80 " /></td>
					        </tr>
							<tr>
								<td nowrap colspan="6" style="padding:5px">
									{$LNG.INV}/{$LNG.DO_NO} <input type="text" name="doc_no[{$r.id}]" class="form-control form-control-sm min-w-80 " value="{$r.doc_no}" /> 
									{if $form.is_under_gst}
										<br/><br/>&nbsp;&nbsp;
										GST Code
										<select class="form-control select2 min-w-100" name="gst_id[{$r.id}]">
											{foreach from=$gst_list key=dummy item=gst}
												<option value="{$gst.id}" {if $r.gst_id eq $gst.id}selected{/if}>{$gst.code} ({$gst.rate|default:'0'}%)</option>
											{/foreach}
										</select>
										<input type="hidden" name="old_gst_id[{$r.id}]" class="txt-width-30" value="{$r.gst_id}" />
									{/if}
								</td>
							</tr>
				        </tbody>
				    {/foreach}
				</table>
			</div>
		</form>
		
		<div class="d-flex justify-content-end align-items-center mt-2">
		    <button class="btn btn-danger mr-1" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
		    <button class="btn btn-success" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
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
	
	$('input.toggle_chx').click(function(){
		var checked=$(this).is(':checked');
		$('input.item_chx').prop('checked',checked).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
