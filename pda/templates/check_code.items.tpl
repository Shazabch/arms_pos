{*
2/27/2013 3:20 PM Fithri
- show stock balance - deduct unfinalized qty (stock with un-finalize sales)

6/17/2013 4:14 PM Justin
- Enhanced show selling price in bigger font size.
- Modified to make all selling price by branch to align by row.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

4/26/2017 11:02 AM Khausalya
- Enhanced changes from RM to use config setting. 

11/06/2020 9:55 AM Sheila
- Fixed table css

06/10/2021 06:00 PM Ed Au
- Enhance to add POS Photo, Unfinalise POS Qty, Sales Order Reserve Qty and change Unfinalsed Stock Balance formula
*}

{assign var=n value=1}
{if $msg}
	<div class="alert alert-info animated fadeInDown">{$msg}</div>
{/if}	
{foreach from=$items item=item max=50}
<div class="card animated fadeInLeft">
	<div class="card-body">
		<div class="card-category bg-danger-opacity fs-08 text-left pl-1"><span>{$n++}.</span> <span>{$item.description}</span></div>
		<div class="row mt-3">
			<div class="col-md-8">
				 <div class="table-responsive">
					<table class="table table-sm table-borderless">
						<tbody>
							<tr>
								<th>ARMS Code:</th>
								<td>{$item.sku_item_code}</td>
							</tr>
							<tr>
								<th>{$config.link_code_name}:</th>
								<td>{$item.link_code}</td>
							</tr>
							<tr>
								<th>Artno/MCode:</th>
								<td>{$item.artno|default:"-"}/{$item.mcode|default:"-"}</td>
							</tr>
							<tr>
								<th>Vendor:</th>
								<td>{$item.vendor}</td>
							</tr>
							<tr>
								<th>Brand:</th>
								<td>{$item.brand}</td>
							</tr>
							<tr>
								<th>SKU Type:</th>
								<td>{$item.sku_type}</td>
							</tr>
							<tr>
								<th>Selling Price:</th>
								<td>{$config.arms_currency.symbol}{$item.selling_price|number_format:2}</td>
							</tr>
							<tr>
								<th></th>
								<td>
									{if $item.price}
										{foreach from=$item.price key=branch item=price}
											<small>{$branch}</small> - {$config.arms_currency.symbol}{$price.price|number_format:2} <br>
										{/foreach}
									{/if}	
								</td>
							</tr>
							{if $config.check_code_show_balance}
								<tr>
									<th>Location:</th>
									<td>{$item.location}</td>
								</tr>
								<tr>
									<th>Stock Balance:</th>
									<td>{$item.qty}</td>
								</tr>
								<tr>
									<th>Unfinalised POS Qty:</th>
									<td>{$item.unfinalise_pos_qty}</td>
								</tr>
								<tr>
									<th>Sales Order Reserve Qty:</th>
									<td>{$item.sales_order_reserve_qty}</td>
								</tr>
								<tr>
									<th>Unfinalised Stock Balance:</th>
									<td>{$item.unfinalize_qty}</td>
								</tr>
							{/if}
							{if $config.enable_replacement_items and $item.ri_id}
							<tr>
								<th>Replacement Item Group:</th>
								<td><a href="javascript:void(show_replacement_items('{$item.id}'));">{$item.ri_group_name|default:'-'}</a></td>
							</tr>
							{/if}
							{if $item.batch_items}
							<tr>
								<th>Batch No:</th>
								<td>
									{$item.batch_no}&nbsp;&nbsp; Expired Date: {$item.expired_date}
									{foreach from=$item.batch_items key=branch item=batch_item name=bn_list}
										{if $branch ne $BRANCH_CODE}&nbsp; {$branch} {/if}
									{/foreach}
								</td>
							</tr>
							{/if}
						</tbody>
					</table>
				</div>	
			</div>
			<div class="col-md-4 d-flex justify-content-end align-items-start">
				{if $item.got_pos_photo > 0}
					{*{capture assign=p}../{$item.photo_promotion[0]}{/capture}*}
						{*<img width="200" height="145" id="sku_photo_display" align="absmiddle" vspace="4" hspace="4" src="thumb.php?w=200&h=145&img={$p|urlencode}" border="1">*}
					<div class="border" style="width : 200px; height: 200px;">
						<img src="../{$item.photo_promotion[0]}" id="sku_photo_display" style="width: auto; height: 100%;">
					</div>
				{else}
					<div class="border d-flex justify-content-center align-items-center" style="width : 200px; height: 200px;">
						<div class="text-muted">No image</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>

{/foreach}

{literal}
<style>


.div_sku_image{
    position: relative;
	width: 200px; 
	height: 200px;
	
}

.div_sku_image img{
    position: absolute;
	border: 1px solid black;
	width:auto;
	height:100%;
	
}


.div_no_image{
	position: absolute;
	width: 200px; 
	height: 200px;
	border: 1px solid black;
	text-align:center;
	line-height:200px;
	font-weight:bold;
	
}

</style>
{/literal}
