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
		if(!confirm('{/literal}{$LNG.DELETE_CONFIRMATION_MSG}{literal}')) return false;
		
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
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$smarty.session.st.title}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=show_scan">{$LNG.STOCK_TAKE}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=view_items">{$LNG.VIEW_ITEMS}</a>
		</li>
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

<div class="card ">
	<div class="card-body" style="background:#fff">
		{if $items}
			<div class="d-flex justify-content-between align-items-center">
				<div class="badge badge-pill badge-light p-2">{count var=$items} {$LNG.ITEMS}</div>
				<div>
					<button class="btn btn-danger" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
					<button class="btn btn-success" id="submit_btn1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
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
						        <th>
						        	<div class="custom-checkbox custom-control">
										<input type="checkbox" class="toggle_chx custom-control-input" id="checkbox-del0">
										<label for="checkbox-del0" class="custom-control-label mt-1">{$LNG.DEL}</label>
									</div>
						        </th>
						        <th>{$LNG.ARMS_CODE}</th>
						        <th>{$LNG.DESCRIPTION}</th>
						        <th class="center">{$LNG.QTY} <small>({$LNG.PCS})</small></th>
						    </tr>
					    </thead>
					    <tbody>
					    	{foreach from=$items item=r name=i}
						        <tr>
						        	<td>{$smarty.foreach.i.iteration}.</td>
						            <td>
						            	<div class="custom-checkbox custom-control">
										<input type="checkbox" data-checkboxes="mygroup" name="item_chx[{$r.item_id}]" class="item_chx custom-control-input" id="checkbox-[{$r.item_id}]">
										<label for="checkbox-[{$r.item_id}]" class="custom-control-label mt-1"></label>
									</div>
						            </td>
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
				<button class="btn btn-danger mr-1" value="Delete" onClick="submit_items('delete');"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
				<button class="btn btn-success" id="submit_btn2" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
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
