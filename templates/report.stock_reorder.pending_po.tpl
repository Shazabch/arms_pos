{*
6/23/2016 2:32 PM Andy
- Enhanced to able to show Pending DO Qty.

5/12/2017 3:58 PM Andy
- Enhanced to able to show PO and DO details by checking reorder branch.

6/14/2017 11:34 AM Andy
- Enhanced the calculation to include uncheckout gra.

7/6/2017 10:29 AM Andy
- Added generate to DO Request (Branch Only).

5/23/2019 9:51 AM William
- Enhance "GRA" word to use report_prefix value.
*}

{if $show_type eq 'do'}
	* Transfer DO only.
	<div style="background-color:#fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th>#</th>
				<th>DO No.</th>
				<th>Qty</th>
			</tr>
			{foreach from=$pending_do.do_data item=do name=fp}
				<tr>
					<td width="20">{$smarty.foreach.fp.iteration}.</td>
					<td>
						<a href="do.php?a=view&id={$do.do_id}&branch_id={$do.branch_id}&highlight_sku_id={$do.highlight_sku_id}" target="_blank">
						{if $do.do_no}
							{$do.do_no}
							<br><font class="small" color="#009900">
								{$do.report_prefix}{$do.do_id|string_format:"%05d"}(PD)
							</font>
						{elseif $do.status==0}
							{$do.report_prefix}{$do.do_id|string_format:"%05d"}(DD)
						{elseif $do.status == 1}
							{$do.report_prefix}{$do.do_id|string_format:"%05d"}(PD)
						{/if}
						</a>
					</td>
					
					{if !$reorder_by_branch}
						{assign var=do_qty value=$do.do_qty}
					{else}
						{if $show_bid}
							{assign var=do_qty value=$do.by_branch.$show_bid.do_qty}
						{else}
							{assign var=do_qty value=0}
							{foreach from=$submitted_reorder_bid item=bid}
								{assign var=do_qty value=$do_qty+$do.by_branch.$bid.do_qty}
							{/foreach}
						{/if}
					{/if}
					<td class="r">{$do_qty|qty_nf}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{elseif $show_type eq 'po'}
	<div style="background-color:#fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th>#</th>
				<th>PO No.</th>
				<th>Qty</th>
			</tr>
			{foreach from=$pending_po.po_data item=po name=fp}
				<tr>
					<td width="20">{$smarty.foreach.fp.iteration}.</td>
					<td>
						<a href="po.php?a=view&id={$po.po_id}&branch_id={$po.branch_id}&highlight_sku_id={$po.highlight_sku_id}" target="_blank">
						{if $po.status==0}
							{$po.report_prefix}{$po.po_id|string_format:"%05d"}(DP)
						{elseif $po.po_no eq ''}
							{$po.report_prefix}{$po.po_id|string_format:"%05d"}(PP)
						{else}
							{$po.po_no}
							<br><font class="small" color="#009900">
							{$po.report_prefix}{if $po.hq_po_id}{$po.hq_po_id|string_format:"%05d"}{else}{$po.po_id|string_format:"%05d"}{/if}(PP)
							</font>
						{/if}
						</a>
					</td>
					
					{if !$reorder_by_branch}
						{assign var=po_qty value=$po.po_qty}
					{else}
						{if $show_bid}
							{assign var=po_qty value=$po.by_branch.$show_bid.po_qty}
						{else}
							{assign var=po_qty value=0}
							{foreach from=$submitted_reorder_bid item=bid}
								{assign var=po_qty value=$po_qty+$po.by_branch.$bid.po_qty}
							{/foreach}
						{/if}
					{/if}
					
					<td class="r">
						{$po_qty|qty_nf}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{elseif $show_type eq 'gra'}
	<div style="background-color:#fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th>#</th>
				<th>Branch</th>
				<th>GRA.</th>
				<th>Qty</th>
			</tr>
			{foreach from=$pending_gra.gra_data item=r name=fp}
				{assign var=bid value=$r.branch_id}
				<tr>
					<td width="20">{$smarty.foreach.fp.iteration}.</td>
					<td>{$r.bcode}</td>
					<td>
						<a href="goods_return_advice.php?a=view&id={$r.gra_id}&branch_id={$r.branch_id}&highlight_sku_id={$r.highlight_sku_id}" target="_blank">
							{$r.report_prefix}{$r.gra_id|string_format:"%05d"}
						</a>
					</td>
					
					{if !$reorder_by_branch}
						{assign var=gra_qty value=$r.gra_qty}
					{else}
						{if $show_bid}
							{assign var=gra_qty value=$r.by_branch.$show_bid.gra_qty}
						{else}
							{assign var=gra_qty value=0}
							{foreach from=$submitted_reorder_bid item=bid}
								{assign var=gra_qty value=$gra_qty+$r.by_branch.$bid.gra_qty}
							{/foreach}
						{/if}
					{/if}
					
					<td class="r">
						{$gra_qty|qty_nf}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{elseif $show_type eq 'do_request'}
	<div style="background-color:#fff;">
		<table width="100%" class="report_table">
			<tr class="header">
				<th>#</th>
				<th>ARMS Code</th>
				<th>MCode</th>
				<th>Art No</th>
				<th>{$config.link_code_name}</th>
				<th>Description</th>
				<th>Qty</th>
			</tr>
			{foreach from=$do_request.item_list item=r name=fp}
				<tr>
					<td width="20">{$smarty.foreach.fp.iteration}.</td>
					<td align="center"><a href="do_request.php?highlight_sku_item_id={$r.sku_item_id}" target="_blank">{$r.sku_item_code}</a></td>
					<td>{$r.mcode}</td>
					<td>{$r.artno}</td>
					<td>{$r.link_code}</td>
					<td>{$r.description}</td>
					<td class="r">{$r.request_qty|qty_nf}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
