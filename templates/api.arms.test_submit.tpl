{*
10/20/2017 5:31 PM Andy
- Enhanced get_product to able to pass in start_from and limit_count.

11/22/2017 11:31 AM Andy
- Enhanced to have "create_order" and "check_order".

3/7/2018 11:26 AM Justin
- Enhanced to have SKU item ID filter for get_product function.

10/8/2018 3:45 PM Andy
- Enhanced get_product to accept new parameter "barcode" and return new value "selling_price" and "link_code".
*}

{include file=header.tpl}
<script>
{literal}
var API = {
	f: undefined,
	initialise: function(){
		this.f = document.f1;
		this.action_type_changed();
	},
	submit_data: function(){
		this.f.submit();
	},
	action_type_changed: function(){
		var div_tmp = $('div_tmp');
		var div_unused_tmp = $('div_unused_tmp');

		// move all to unused
		var div_action_content_list = $$("div.div_action_content");
		for(var i=0; i<div_action_content_list.length; i++){
			$('div_unused').insertBefore(div_action_content_list[i], div_unused_tmp);

		}

		// move the required div only
		var a = $('action').value;
		var div_action = $('div-'+a);
		if(div_action){
			$('div_extra_content').insertBefore(div_action, div_tmp);

		}
	}
}
{/literal}
</script>

<h1>Test Submit</h1>

<div>
	<form name="f1" target="ifdata" onSubmit="return false;">
		<table>
			<tr>
				<td width="150">Action</td>
				<td>
					<select name="action" id="action" onchange="API.action_type_changed();">
						<option value="get_product">Get Product</option>
						<option value="create_order">Create Order</option>
						<option value="check_order">Check Order</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input type="text" name="username" /></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="password" name="pass"/></td>
			</tr>
			
		</table>
		
		<div id="div_extra_content">
			<div id="div_tmp"></div>
		</div>
		
		<input type="button" value="Submit" onclick="API.submit_data();"/>
	</form>
</div>

<div id="div_unused" style="display:none;">
	<div id="div-get_product" class="div_action_content">
		<table>
			<tr>
				<td>SKU Item ID</td>
				<td><input type="text" name="sku_item_id"/></td>
			</tr>
			<tr>
				<td>Barcode</td>
				<td><input type="text" name="barcode"/></td>
			</tr>
			<tr>
				<td width="150">Start Count</td>
				<td><input type="text" name="start_from"/></td>
			</tr>
			<tr>
				<td>Limit Row</td>
				<td><input type="text" name="limit"/></td>
			</tr>
		</table>
	</div>
	
	<div id="div-create_order" class="div_action_content">
		<table>
			<tr>
				<td width="150">Order ID</td>
				<td><input type="text" name="order_id" /></td>
			</tr>
			<tr>
				<td>Order Date</td>
				<td><input type="text" name="order_date" /></td>
			</tr>
			<tr>
				<td>Billing Address</td>
				<td><textarea name="billing_address"></textarea></td>
			</tr>
			<tr>
				<td>Shipping Address</td>
				<td><textarea name="shipping_address"></textarea></td>
			</tr>
			<tr>
				<td>Delivery Method</td>
				<td><input type="text" name="delivery_method" /></td>
			</tr>
			<tr>
				<td>Purchaser Name</td>
				<td><input type="text" name="purchaser_name" /></td>
			</tr>
			<tr>
				<td>Purchaser Email</td>
				<td><input type="text" name="purchaser_email" /></td>
			</tr>
			<tr>
				<td>Purchaser Phone</td>
				<td><input type="text" name="purchaser_phone" /></td>
			</tr>
			<tr>
				<td>SKU Item Code</td>
				<td>
					<input type="text" name="sku_item_code[]" />
					Qty
					<input type="text" name="sku_quantity[]" />
				</td>
			</tr>
			<tr>
				<td>SKU Item Code</td>
				<td>
					<input type="text" name="sku_item_code[]" />
					Qty
					<input type="text" name="sku_quantity[]" />
				</td>
			</tr>
			<tr>
				<td>SKU Item Code</td>
				<td>
					<input type="text" name="sku_item_code[]" />
					Qty
					<input type="text" name="sku_quantity[]" />
				</td>
			</tr>
		</table>
	</div>
	<div id="div-check_order" class="div_action_content">
		<table>
			<tr>
				<td width="150">Order ID #1</td>
				<td><input type="text" name="order_id[]" /></td>
			</tr>
			<tr>
				<td>Order ID #2</td>
				<td><input type="text" name="order_id[]" /></td>
			</tr>
			<tr>
				<td>Order ID #3</td>
				<td><input type="text" name="order_id[]" /></td>
			</tr>
		</table>
	</div>
	<div id="div_unused_tmp"></div>
</div>

<iframe name="ifdata" width="100%" height="500px"></iframe/>

{literal}
<script>
	API.initialise();
</script>
{/literal}


