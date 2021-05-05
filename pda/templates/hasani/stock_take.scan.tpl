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

3/19/2015 4:35 PM Justin
- Enhanced to have Cost Discount.
- Enhanced to have new ability to allow user choose cost price if found having 2 different costs for same item.

12:02 PM 3/20/2015 Justin
- Enhanced to take whatever user key in for Cost Price if it is not in percentage variable.
- Enhanced to have validation that cost cannot be greater than selling price.
- Enhanced to focus on qty field once chosen cost price.

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
	}else if(document.scan['description'].value==''){
		alert('Please key in Description');
		return false;
	}else if(document.scan['sell_price'].value==''){
		alert('Please key in Selling Price');
		return false;
	}else if(document.scan['qty'].value==''){
		alert('Please key in Quantity');
		return false;
	}else if(document.scan['qty'].value > 1000000){
		alert("Invalid Quantity");
		return false;
	}else if(document.scan['cost_disc'] != undefined){
		if(document.scan['cost_disc'].value.trim() == ""){
			alert("Please key in Cost Discount");
			return false;
		}		
	}else if(document.scan['cp_choice'] != undefined){
		var radios=document.scan['cp_choice'];
		var radio_check = false;
		
		for(i=0; i<radios.length; i++){
			if(radios[i].checked == true) radio_check = true;
		}

		if(radio_check == false){
			alert("Please choose a cost price.");
			return false;
		}
	}
	$('#submit_btn').attr('disabled', 'disabled');
	document.scan.submit();
}

function back_stock_take(){
	
  window.location="custom/hasani/stock_take.php?a=stock_take";
}

function checkkey(event){
   if (event == undefined) event = window.event;
   if(event.keyCode==13){  // enter
	document.scan['sku_item_id'].value = "";
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

function cost_disc_check(obj){
    var discount = obj.value.trim();
	discount = discount.regex(/[^0-9\.%+]/g,'');
    discount = discount.regex(/\+$/,'');

	obj.value = discount;
	
	var cost_price = document.scan['sell_price'].value;
	
	if(cost_price == 0) return;
	
	var disc1 = discount.split("+")[0];
	var disc2 = 0;
	if(discount.split("+").length>1) disc2 = discount.split("+")[1];
	
	if(disc1){
		if(disc1.indexOf("%") > 0){
			disc1 = float(disc1);
			disc_sp = float(round(cost_price*(disc1/100), 2));
			cost_price -= disc_sp;
		}else{
			cost_price = float(disc1);
		}
	}

	if(disc2){
		if(disc2.indexOf("%") > 0){
			disc2 = float(disc2);
			disc_sp = float(round(cost_price*(disc2/100), 2));
			cost_price -= disc_sp;
		}else{
			cost_price += float(disc2);
		}
	}
	
	if(cost_price < 0 || cost_price > document.scan['sell_price'].value){
		obj.value = "";
	}
	
	document.scan['qty'].focus();
}

function cp_choice_clicked(){
	document.scan['qty'].focus();
}

{/literal}
</script>

<h1>
{$smarty.session.st.title}
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="custom/hasani/stock_take.php?a=show_scan">Stock Take</a> > <a href="custom/hasani/stock_take.php?a=view_items">View Items</a></span>
<div style="margin-bottom: 10px"></div>

{if $errm}
	<div id=err><div class=errmsg><ul>
	{foreach from=$errm item=e}
	<li> {$e}
	{/foreach}
	</ul></div></div>
{/if}
{if $smarty.request.auto_add}<br /><img src="/ui/approved.png" title="Item Added" border=0> Item added<br />{/if}
<div class="stdframe" style="background:#fff">
<form name="scan" id="scan" onSubmit="return false" method="post" action="custom/hasani/stock_take.php">
<input type="hidden" name="a" value="save_scanning">
<input type="hidden" name="is_confirm" value="{$is_confirm}">
<input type="checkbox" value="1" name="auto_add_item" {if $smarty.request.auto_add}checked{/if} /> Auto add item with one qty<br />
<table width="100%" border="0" cellspacing="0" cellpadding="4">
<tr>
      <th align="left" valign="top">Code</th>
 		<td>
 		  <input type="text" name="code" onKeyPress="checkkey(event)" value="{$si_info.code}">
 		  <input type="hidden" name="sku_item_id" value="{$si_info.sku_item_id}">
 		</td>
 	</tr>
 	
	<tr>
      <th align="left" valign="top">Description</th>
 		<td>
 		  <input type="text" name="description" size=30 value="{$si_info.description}" readonly>
 		</td>
 	</tr>
 	
	<tr>
      <th align="left" valign="top">Selling Price</th>
 		<td>
 		   <input type="text" name="sell_price" value="{$si_info.selling_price|round2}" readonly>
 		</td>
 	</tr>
 
	<tr>
		<th align="left" valign="top">
			{if $cp_list}
				Cost Price <br />Selection
			{else}
				Cost Price
			{/if}
		</th>
		<td>
			{if $cp_list}
				{foreach from=$cp_list key=row item=r name=cp}
					<input type="radio" name="cp_choice" id="cp_choice" value="{$r.cost_price}" onclick="cp_choice_clicked();"> <input type="text" name="sample_cost_disc_{$row}" value="{$r.cost_price|round2}" readonly> (Disc: {$r.discount}%)
					{if !$smarty.foreach.cp.last}<br />{/if}
				{/foreach}
				<br /><input type="radio" name="cp_choice" id="cp_choice" value="{$si_info.cf_cost_price}"> Use Last Cost ({$si_info.cf_cost_price|number_format:2})
				<br /><b><font color="red">* This will change all cost price for same item.</font></b>
			{else}
				<input type="text" name="cost_disc" value="" onchange="cost_disc_check(this);" >
			{/if}
		</td>
	</tr>
 
	<tr>
    <th align="left" valign="top">Qty</th>
	<td>
	  <input type="text" name="qty"  size="10" class="qty" onkeypress="qty_keypress(event);" />
	</td>
</tr>
</table>
</form>
<input type="button" id="submit_btn" value="Save" onClick="save_data();" /> <input type="button" value="Finish" onClick="back_stock_take();" />
</div>
<script>
{if $si_info.sku_item_id}
	if(document.scan['cost_disc'] != undefined) document.scan['cost_disc'].focus();
	else document.scan['qty'].focus();
{else}
	document.scan['code'].focus();
{/if}
</script>

{include file='footer.tpl'}
