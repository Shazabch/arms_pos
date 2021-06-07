{*
Revision History
================
4/19/2017 3:09 PM Khausalya 
- Enhanced changes from RM to use config setting. 

5/21/2019 4:17 PM William
- Enhance "GRN" word to use report_prefix.

04/08/2020 4:07 PM Sheila
- Modified layout to compatible with new UI.

*}

<!-- Price Change Notify -->
{if $price_history}

<!-- History Popup Start -->
<div class="modal fade" id="history_popup" data-backdrop="false">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo">
			<div class="modal-header">
				<h6 class="modal-title">Modal Header</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="history_popup_content"></div>
			</div>
		</div>
	</div>
</div>
<!-- History Popup End-->

<!-- <div id="history_popup" style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
	<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
	<div id="history_popup_content"></div>
</div> -->

<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="fas fa-tag"></i> Price Change History</div>
		<div class="mb-2">
			<span class="fs-07 text-muted">Last 25 price change items</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height:200px;">
			{section name=i loop=$price_history}
			<li  class="fs-08">
				<span class="text-muted fs-06">{$price_history[i].last_update} - {$price_history[i].branch}</span><br>
				<strong class="fs-09">
					<i class="fas fa-search"  data-toggle="modal" href="#history_popup" onclick="price_history(this,{$price_history[i].id},{$price_history[i].branch_id})" role="button"></i>
					{if $config.notification_price_change_show_artno}
						{$price_history[i].artno}
					{else}
						{$price_history[i].sku_item_code}
					{/if}
				</strong>= {$config.arms_currency.symbol}{$price_history[i].price|number_format:2}<br>
				<span class="fs-06 text-secondary">{$price_history[i].description}</span>
			</li>
			{/section}
		</ul>
	</div>
</div>

{/if}
<!-- Price Change Notify  End -->

<!-- Batch Price Change notification -->
{if $batch_price_change.ok eq 1}
<div id="div_bpc" {if $sessioninfo.level eq 0}style="display:none;"{/if}>
	<h5>
	{*<img src="/ui/info.png" align=absmiddle border=0>*}
	<i class="icofont-price icofont"></i> Batch Price Change</h5>
	<div class="ntc">
		The following Price Change item(s) soon will be updated (Show last 100 items)
	</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		<div id="div_bpc_items">{$batch_price_change.html}</div>
	</div>	
</div>
{/if}

<!-- SKU Items Lock Price-->
{if $temp_price_history}
<h5>
{*<img src="/ui/info.png" align="absmiddle" border="0">*}
<i class="icofont-price icofont"></i> Temp Price Items</h5>
<div class="ntc">Last 25 temp price items</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
{section name=i loop=$temp_price_history}
<div style="border-bottom:1px solid #eee">
<font color="#666666" class="small">
{$temp_price_history[i].lastupdate} - {$temp_price_history[i].branch}
</font><br />
<font class="temp_item" color="#d00000">
{if $config.notification_price_change_show_artno}
    {$temp_price_history[i].artno}
{else}
	{$temp_price_history[i].sku_item_code}
{/if}
</font>
=
<font class="temp_item" color="blue">{$config.arms_currency.symbol}{$temp_price_history[i].temp_price|number_format:2}</font><br />
<font class="small">{$temp_price_history[i].description}</font><br />
<font>{$temp_price_history[i].username} : {$temp_price_history[i].reason}</font>
</div>
{/section}
</div>
{/if}

<!--PO Overdue-->
{if $po_overdue}
<h5><img src=/ui/store.png align=absmiddle border=0> PO Delivery Overdue</h5>
<div class=ntc>The following PO had Overdue</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
{foreach from=$po_overdue key=id item=r}
	{foreach from=$po_overdue.$id key=branch_id item=r2}
		<div style="border-bottom:1px solid #eee">
		<font color=#666666 class=small>
		PO No: <a href="/po.php?a=view&id={$id}&branch_id={$branch_id}">{$r2.po_no}</a>({$r2.department|default:"-"}/<font color=blue>{$r2.user}</font>)<br>
		Created: <font color=blue>{$r2.po_date|date_format:$config.dat_format}</font><br>
		Delivery Date: <font color=blue>{$r2.delivered_date|date_format:$config.dat_format}</font>
		Cancelation Date: <font color=blue>{$r2.cancel_date|date_format:$config.dat_format}</font>
		</font>
		</div>
	{/foreach}
{/foreach}
</div>
{/if}

<!-- New SKU Notify -->
{if $new_sku}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09" ><i class="fas fa-tag"></i> New SKU</div>
		<div class="mb-2 fs-07">
			<span class="text-muted">Last 25 new SKU items</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 300px;">
			{section name=i loop=$new_sku}
			<li  class="fs-08">
				<a href="masterfile_sku.php?a=view&id={$new_sku[i].sku_id}" target="_blank" class="text-reset">
					<span class="text-muted fs-06">{$new_sku[i].added}</span><br>
					<strong class="fs-09">
						{if $config.notification_price_change_show_artno}
						    {$new_sku[i].artno}
						{else}
							{$new_sku[i].mcode|default:$new_sku[i].sku_item_code}
						{/if}
					</strong>= {$config.arms_currency.symbol}{$new_sku[i].selling_price|number_format:2}<br>
					<span class="text-secondary fs-06">{$new_sku[i].description}</span>
				</a>
			</li>
			{/section}
		</ul>
	</div>
</div>
{/if}
<!-- New SKU Notify  End-->

<!-- GRA Notify -->

{if $last_gra}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="far fa-building"></i> Gra Status</div>
		<div class="fs-07 mb-2 text-muted">
			<span>The following GRA has been pending for more than a week</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 200px;">
			{section name=i loop=$last_gra}
			<li class="fs-08">
				<a href="/goods_return_advice.php?a=view&id={$last_gra[i].id}" target="_blank" class="text-reset">
					<strong class="fs-08">{$last_gra[i].vendor}</strong><br>
					<span class="text-secondary fs-06">Created: <span class="text-muted"> {$last_gra[i].added}</span></span><br>
					<span class="text-secondary fs-06">Last Update: <span class="text-muted"> {$last_gra[i].last_update}</span></span>
				</a>
			</li>
			{/section}
		</ul>
	</div>
</div>
{/if}
<!-- GRA Notify End -->

<!-- GRR Notify -->
{if $grr_notify}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="far fa-building"></i> GRR Status</div>
		<div class="mb-2 fs-07 text-muted">
			<span>The following GRR has been pending for more than {$config.grr_incomplete_notification|default:3} days</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 200px;">
			{section name=i loop=$grr_notify}
			<li class="fs-08">
				<a href="/goods_receiving_record.php?a=view&id={$grr_notify[i].id}&branch_id={$grr_notify[i].branch_id}" target="_blank" class="text-reset">
					<strong class="fs-08">{$grr_notify[i].vendor}</strong><br>
					<span class="text-secondary fs-06">Received Date : <span class="text-muted"></span> {$grr_notify[i].rcv_date}</span>
				</a>
			</li>
			{/section}
		</ul>
	</div>
</div>
{/if}
<!-- GRR Notify End -->

<!-- Redemption item Notify -->
{if $redemption_items}
<h5><img src=/ui/store.png align=absmiddle border=0> Redemption Item Status</h5>
<div class=ntc>The following Redemption item(s) will be expired within {$config.membership_redemption_expire_days} days</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
<a href="/membership.redemption_setup.php">Go to Redemption Item Setup</a>
{section name=i loop=$redemption_items}
<div style="border-bottom:1px solid #eee"> 
<br>
<font color=#666666 class=small>
Item : {$redemption_items[i].sku_item_code} [ {$redemption_items[i].days_left} day(s) left ]<br>
</font>
</div>
{/section}
</div>
{/if}

<!-- GRN Distribution Status -->
{if $grn_deliver_monitor.grn}
	<h5>
	{*<img src="/ui/store.png" align="absmiddle" border="0">*}
	<i class="icofont-building icofont"></i>GRN Distribution Status</h5>
	<div class="ntc">The following GRN are slow in DO to others branches (below {$grn_deliver_monitor.info.min_do_qty_percent}% after {$grn_deliver_monitor.info.monitor_after_day} days)</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
	
	{foreach from=$grn_deliver_monitor.grn item=grn}
		<div style="border-bottom:1px solid #eee" id="div_grn_distribution-{$grn.branch_id}-{$grn.id}">
			{if $sessioninfo.level>=9999}
				<a href="javascript:void(delete_grn_distribution('{$grn.branch_id}', '{$grn.id}'))">
					<img src="/ui/del.png" align="absmiddle" border="0" title="Delete this notify" id="img_delete_grn_distribution-{$grn.branch_id}-{$grn.id}" />
				</a>
			{/if} 
			<a href="/goods_receiving_note.php?a=view&id={$grn.id}&branch_id={$grn.branch_id}" target="_blank">{$grn.report_prefix}{$grn.id|string_format:'%05d'}</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<font class="small">
				<a href="goods_receiving_note.distribution_report.php?load_report=1&grn_bid_id={$grn.branch_id}_{$grn.id}" target="_blank">
					Delivered {$grn.do_per|num_format:2}%
				</a>
			</font> 
			<br />
			<font color="#666666" class="small">
				Received Date : {$grn.rcv_date}<br>
			</font>
		</div>
	{/foreach}
	{if $grn_deliver_monitor.have_more}
		<div style="text-align:center;">
			<a href="goods_receiving_note.distribution_report.php?a=view_status">Click here to view more</a>
		</div>
	{/if}
	</div>
{/if}

<!-- Stock Reorder -->
{if $stock_reorder_data}
	<h5>
	{*<img src="/ui/store.png" align="absmiddle" border="0">*}
	<i class="icofont-user-suited icofont"></i> Vendor Stock Reorder</h5>
	<div class="ntc">Belows are some pre-generated reorder list by vendor and department.</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		{foreach from=$stock_reorder_data key=vendor_id item=tmp_vendor_data}
			<div style="border-bottom:1px solid #eee"> 
				{foreach from=$tmp_vendor_data key=category_id item=r name=f_st}
					{if $smarty.foreach.f_st.first}
						{$r.v_desc}
						<br />
					{/if}
					
					<div>
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" /> 
						<a href="/report.stock_reorder.php?load_report=1&category_id={$r.category_id}&vendor_id={$r.vendor_id}&use_pregen_sku=1&reorder_type={$r.reorder_type}&by_last_vendor=1" target="_blank">
						{$r.c_desc}
						</a>
						<br />
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" />
						<font color="006600">(Est: {count var=$r.sku_id_list} SKU)</font>
						<br />
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" />
						<font color="#666666" class="small">
							Pregen at: {$r.added|date_format:'%Y-%m-%d %H:%M'}
						</font>
					</div>
				{/foreach}
			</div>
		{/foreach}
	</div>
{/if}