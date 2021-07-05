{*
10/18/2011 3:50:12 PM Justin
- Removed the mi(this) from qty field.
- Added new function to change the qty field to enable/disable decimal points key in base on sku item's doc_allow_decimal.

4/5/2012 11:01:12 AM Justin
- Enhanced to show error message when found sku item id is empty.
- Added a hidden field to capture sku item id.
- Modified the check_code to use document submit instead of using ajax.

6/14/2012 4:31:34 PM Justin
- Added new option "Add item when match one result".
- Enhanced to show item added info once added a item.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

5/24/2013 11:56 AM Justin
- Enhanced to activate save function while press enter on date, location or shelf.

2/24/2014 4:24 PM Andy
- Fix the variable bug. (sometime "loc" sometime "location").
- Added link to allow user travel to "item list" or "Scan".

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
*}

{include file='header.tpl'}
<script type="text/javascript">
var php_self = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
{literal}
function save_data(){
	if(document.scan['code'].value==''){
		alert('Please enter Code.');
		return false;
	}else if(document.scan['description'].value=='')
	{
	  alert('Please key in Description');
		return false;
	}else if(document.scan['sell_price'].value=='')
	{
	  alert('Please key in Selling Price');
		return false;
	}
	else if(document.scan['qty'].value=='')
	{
	  alert('Please key in Quantity');
		return false;
	}else if(document.scan['qty'].value > 1000000)
	{
	  alert("Invalid Quantity");
	  return false;
	}
	$('#submit_btn').attr('disabled', 'disabled');
	document.scan.submit();
}

function back_stock_take(){
  window.location="stock_take.php?a=stock_take";
}

function checkkey(event){
   if (event == undefined) event = window.event;
   if(event.keyCode==13){  // enter
    checkcode();
   }
}


function checkcode(){
    /*var cod = document.scan['code'].value;
    $.post(php_self, 
  	{a:'validate_code',code:cod}, function(msg){
    	if($.trim(msg.status)=='ok'){
			document.scan['qty'].focus();
			document.scan['sku_item_id'].value = $.trim(msg.sku_item_id);
			document.scan['description'].value = $.trim(msg.description);
			document.scan['sell_price'].value = parseFloat($.trim(msg.selling_price)).toFixed(2);
			//document.scan['qty'].onchange = "";
			if($.trim(msg.doc_allow_decimal) == 1){
				$('.qty').bind("change.aaa", function(){ this.value = float(round(this.value, global_qty_decimal_points)); });
			}else{
				$('.qty').bind("change.aaa", function(){ mi(this); });
				document.scan['qty'].value = int(document.scan['qty'].value);
			}
			return;
		}else{   
		  alert("Invalid Sku Code");
		  document.scan['code'].focus();
		  //document.scan['qty'].value = "";
		  //document.scan['sku_item_id'].value = "";
		  //document.scan['description'].value = "";
		  //document.scan['sell_price'].value = "";
		  document.scan.reset();
		  
		  return;
		}
	},'json');
    
    return;*/
	document.scan['a'].value = "validate_code";
	document.scan.submit();
}

// function when user press enter
function qty_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		this.pagenum = 1;
		
		save_data();
	}
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
			<a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">Stock Take</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=show_scan">Scan Item</a>
		</li>
		<li class="breadcrumb-item">
			<a href="stock_take.php?a=view_items">View Items</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Error Message -->
{if $errm}
	{foreach from=$errm item=e}
	<div class="alert alert-danger mg-b-0 animated fadeInDown" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->
{if $smarty.request.auto_add}
	<div class="alert alert-success my-1"><img src="/ui/approved.png" title="Item Added"> Item added</div> 
{/if}
<!-- row -->
<div class="row animated fadeInLeft">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="scan" onSubmit="return false" method="post" action="stock_take.php">
				<input type="hidden" name="a" value="save_scanning">
				<div class="card-body">
					<div class=" ml-4 checkbox">
						<div class="custom-checkbox custom-control">
							<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-2" value="1" name="auto_add_item" {if $smarty.request.auto_add}checked{/if}>
							<label for="checkbox-2" class="custom-control-label mt-1">Auto add item with one qty</label>
						</div>
					</div>
					<div class="pd-15 pd-sm-20">
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Code</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="code" onKeyPress="checkkey(event)" value="{$si_info.code}">
								<input type="hidden" name="sku_item_id" value="{$si_info.sku_item_id}">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Description</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="description" size=30 value="{$si_info.description}" readonly>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Selling Price</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" value="{$si_info.selling_price}" readonly>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">Qty</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="qty"  size="10" class="qty" onkeypress="qty_keypress(event);">
							</div>
						</div>
						<button class="btn btn-primary btn-block-sm" id="submit_btn" value="Save" onClick="save_data();">Save</button>
						<button class="btn btn-success btn-block-sm mt-2 mt-md-0 mt-xl-0 mt-lg-0" onClick="back_stock_take();">Finish</button>
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->

<script>
{if $si_info.sku_item_id}
	document.scan['qty'].focus();
{else}
	document.scan['code'].focus();
{/if}
</script>

{include file='footer.tpl'}
