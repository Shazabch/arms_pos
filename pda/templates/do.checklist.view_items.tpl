{*

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

05/11/2020 3:01PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu), then link to module menu page
	-Added cellspacing in table
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

function save(){
	if(!confirm("Click 'OK' to save.")) return;
	document.f_a.submit();
}

function add_row(){
	var $tr = $('.temp_row');
    var $clone = $tr.clone();
    $clone.find(':text').val('');
	$clone.removeClass('temp_row');
	$clone.find(".tmp_row_no").removeClass().addClass('row_no');
	$clone.find(".tmp_item_chx").removeClass().addClass('item_chx');
	$('#item_tbl tr:last').after($clone);
	reset_row_no();
}

function reset_row_no(){
	var row_no = 0;
	$(".row_no").each(function() {
		row_no += 1;
		$(this).html(row_no+".");
	});
}

function delete_row(){
	if(!confirm("Are you sure want to delete?\nNOTE: this will delete the items permanently.")) return;
	document.f_a['a'].value = "delete_checklist_items";
	
	document.f_a.submit();
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
			<a href="home.php?a=menu&id=do">{$LNG.DO}</a>
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

{include file='do.checklist.top_include.tpl'}<br />

<div class="card ">
	<div class="card-body">
		<table style="display:none;">
			<tr class="temp_row">
				<td class="tmp_row_no"></td>
				<td><input type="checkbox" name="item_chx[]" class="tmp_item_chx" /></td>
				<td align="center"><input type="text" class="form-control min-w-100" name="barcode[]" value="" size="20" /></td>
				<td align="center"><input type="text" name="qty[]" class="r form-control min-w-100" value="{$r.qty}" size="5" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
			</tr>
		</table>

		{if $items}
		<div class="d-flex justify-content-end align-items-center">
			<button class="btn btn-success mr-1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			<!-- <button class="btn btn-primary mr-1" value="Add Row" onClick="add_row();"><i class="fas fa-plus"></i> Add Row</button> -->
			<button class="btn btn-danger" value="Delete" onClick="delete_row();"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
		</div>

		<form name="f_a" method="post" onSubmit="return false;">

		<input type="hidden" name="a" value="save_checklist_items" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
			<div class="table-responsive">
				<table id="item_tbl" class="item_tbl table mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th>#</th>
							<th>{$LNG.DEL}<br /><input type="checkbox" class="toggle_chx" /></th>
							<th>{$LNG.BARCODE}</th>
							<th>{$LNG.QTY} <small>({$LNG.PCS})</small></th>
						</tr>
					</thead>
					{foreach from=$items key=row item=r name=i}
						<tr>
							<td class="row_no">{$smarty.foreach.i.iteration}.</td>
							<td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
							<td ><input type="text" name="barcode[{$r.id}]" class="form-control min-w-100" value="{$r.barcode}" size="20" /></td>
							<td ><input type="text" name="qty[{$r.id}]" class="r form-control min-w-100" value="{$r.qty}" size="5" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
						</tr>
					{/foreach}
				</table>
			</div>
		</form>

		<div class="d-flex justify-content-end align-items-center mt-2">
			<button class="btn btn-success mr-1" value="Save" onClick="submit_items('save');"><i class="fas fa-save"></i> {$LNG.SAVE}</button>
			<!-- <button class="btn btn-primary mr-1" value="Add Row" onClick="add_row();"><i class="fas fa-plus"></i> Add Row</button> -->
			<button class="btn btn-danger" value="Delete" onClick="delete_row();"><i class="fas fa-trash-alt"></i> {$LNG.DELETE}</button>
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
		$('input.item_chx').attr('checked',$(this).attr('checked')).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
