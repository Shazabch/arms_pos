{*
11/06/2020 11:26 AM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)  and link to module menu page
*}

{include file='header.tpl'}

<script>
{literal}

function add_item(){
	if(document.f_a['barcode'].value.trim() == "" || document.f_a['qty'].value.trim() == "") return false;
	
	document.f_a.submit();
}

function check_key(obj, event){
	var THIS = this;
	if(event.keyCode==13){
		if(obj.name == "barcode") document.f_a['qty'].focus();
		else{
			add_item();
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

{include file='do.checklist.top_include.tpl'}<br />
{if $item_added}
<div class="alert alert-success animated fadeInDown my-1">
	<img src="/ui/approved.png" title="Item Added" border=0> Barcode has been added.
</div>
{/if}

<div class="card">
	<div class="card-body">
		<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" value="add_checklist_item" />
			<div class="table-responsive">
				<table id="item_tbl" class="item_tbl table mb-0 text-md-nowrap">
					<tr>
						<td><b>Barcode</b></td>
						<td><input type="text" name="barcode" class="form-control min-w-100" value="{$form.barcode}" size="20" onkeypress="check_key(this, event);" /></td>
					</tr>
					<tr>
						<td><b>Qty</b></td>
						<td><input type="text" name="qty" class="form-control min-w-100" value="{$form.qty}" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" onkeypress="check_key(this, event);" size="10" /></td>
					</tr>
					<tr>
						<td align="center" colspan="2"><input type="button" class="btn btn-primary" value="Add" onClick="add_item();" /></td>
					</tr>
				</table>
			</div>
		</form>

	<div class="badge badge-pill badge-light p-2">{$items_details.total_item|qty_nf} Item(s), {$items_details.total_pcs|qty_nf} pcs</div>
	</div>
</div>
<script>
	document.f_a['barcode'].focus();
</script>

{include file='footer.tpl'}
