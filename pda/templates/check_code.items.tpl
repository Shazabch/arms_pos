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
*}

{assign var=n value=1}
{if $msg}
<div class="alert alert-info rounded px-2 col-sm-12 col-lg-8 col-xl-8 animated fadeInDown">{$msg}</div>	
{/if}	
{foreach from=$items item=item max=50}
<div class="d-flex flex-row">
	<div class="col-sm-12 col-lg-8 col-xl-8 animated fadeInLeft">
		<div class="card">
			<div class="card-body text-center pricing">
				<div class="card-category bg-danger-opacity fs-08 text-left pl-1"><span>{$n++}.</span> <span>{$item.description}</span></div>
				<dl class="row text-left pt-2 fs-08">
						<dt class="col-4">ARMS Code: </dt>
						<dd class="col-8">{$item.sku_item_code}</dd>

						<dt class="col-4">{$config.link_code_name}: </dt>
						<dd class="col-8">{$item.link_code}</dd>

						<dt class="col-4">Artno/MCode: </dt>
						<dd class="col-8">{$item.artno|default:"-"}/{$item.mcode|default:"-"}</dd>

						<dt class="col-4">Vendor: </dt>
						<dd class="col-8">{$item.vendor}</dd>

						<dt class="col-4">Brand: </dt>
						<dd class="col-8">{$item.brand}</dd>

						<dt class="col-4">SKU Type: </dt>
						<dd class="col-8">{$item.sku_type}</dd>

						<dt class="col-4">Selling Price: </dt>
						<dd class="col-8">{$config.arms_currency.symbol}{$item.selling_price|number_format:2}</dd>

						{if $item.price}
							{foreach from=$item.price key=branch item=price}
								<dt class="col-4">{$branch}</dt>
								<dd class="col-8">{$config.arms_currency.symbol}{$price.price|number_format:2}</dd>
							{/foreach}
						{/if}

						{if $config.check_code_show_balance}
						<dt class="col-4">Location:</dt>
						<dd class="col-8">{$item.location}</dd>

						<dt class="col-4">Stock Balance:</dt>
						<dd class="col-8">{$item.qty}</dd>

						<dt class="col-4">Unfinalised Stock Balance:</dt>
						<dd class="col-8">{$item.unfinalize_qty}</dd>
						{/if}

						{if $config.enable_replacement_items and $item.ri_id}
						<dt class="col-4">Replacement Item Group:</dt>
						<dd class="col-8"><a href="javascript:void(show_replacement_items('{$item.id}'));">{$item.ri_group_name|default:'-'}</a></dd>
						{/if}

						{if $item.batch_items}
							<dt class="col-4">Batch No: </dt>
							<dd class="col-8">{$item.batch_no}</dd>

							<dt class="col-4"> Expired Date: </dt>
							<dd class="col-8">{$item.expired_date}</dd>

							{foreach from=$item.batch_items key=branch item=batch_item name=bn_list}
								<dt class="col-4">{if $branch ne $BRANCH_CODE}</dt>
								<dd class="col-8">{$branch}</dd>{/if}
							{/foreach}
						{/if}
				</dl>
			</div>
		</div>
	</div>
</div>
{/foreach}
