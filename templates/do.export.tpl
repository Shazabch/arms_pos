{*
8/7/2017 10:56 AM Justin
- Enhanced the excel format to include the master fraction from SKU description.
*}

<!-- print sheet -->
<div class=printarea>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr><td nowrap>Transfer DO</td><td nowrap>ID#{$form.id}</td></tr>
	<tr><td nowrap>Added Date</td><td nowrap>{$form.added|date_format:"%Y-%m-%d"}</td></tr>
	<tr><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:"%Y-%m-%d"}</td></tr>
</table>

<br>

<table border="0" cellspacing="0" cellpadding="4" width="100%">
	<tr bgcolor="#cccccc">
		<th rowspan="2">#</th>
		<th rowspan="2">MCode</th>
		<th rowspan="2">Art No</th>
		<th rowspan="2">SKU Description</th>
		<th rowspan="2"	>Stock<br />Balance ({$form.from_branch_name})</th>
		{if $form.deliver_branch && !$form.do_branch_id}
			{foreach from=$form.deliver_branch key=dummy item=bid}
				<th>{$branch_list.$bid.code}</th>
			{/foreach}
		{else}
			{assign var=to_bid value=$form.do_branch_id}
			<th>{$branch_list.$to_bid.code}</th>
		{/if}
		<th rowspan="2">Total<br />Qty</th>
	</tr>
	
	<tr bgcolor="#cccccc">
		{if $form.deliver_branch && !$form.do_branch_id}
			{foreach from=$form.deliver_branch key=dummy item=bid}
				<th>PCS</th>
			{/foreach}
		{else}		
			<th>PCS</th>
		{/if}
	</tr>

	{assign var=total_qty value=0}
	{foreach from=$do_items key=item_index item=r name=i}
		<tr>
			<td align="center" nowrap>
				{if !$page_item_info.$item_index.not_item}
					<!--{$item_no++}-->
					{$item_no}.
				{else}
					&nbsp;
				{/if}
			</td>
			<td align="center" nowrap>{$r.mcode|default:'&nbsp;'}</td>
			<td align="center" nowrap>
				{if $r.oi}
					{$r.artno_mcode|default:'&nbsp;'}
				{else}
					{$r.artno}
				{/if}
			</td>
			<td>{$r.description|default:'&nbsp;'} {if !$r.oi}{include file=details.uom.tpl uom=$r.master_uom_code}{/if}</td>
			<td align="right">{$r.stock_balance1|qty_nf}</td>
			{assign var=row_ttl_qty value=0}
			{if !$page_item_info.$item_index.not_item}
				{if $form.deliver_branch && !$form.do_branch_id}
					{foreach from=$form.deliver_branch key=dummy item=bid}
						{assign var=row_qty value=$r.ctn_allocation.$bid*$r.uom_fraction}
						{assign var=row_qty value=$row_qty+$r.pcs_allocation.$bid}
						{assign var=row_ttl_qty value=$row_ttl_qty+$row_qty}
						<td align="right">{$row_qty|qty_nf}</td>
					{/foreach}
				{else}
					{assign var=row_qty value=$r.ctn*$r.uom_fraction}
					{assign var=row_qty value=$row_qty+$r.pcs}
					{assign var=row_ttl_qty value=$row_qty}
					<td align="right">{$row_qty|qty_nf}</td>
				{/if}
				<td align="right">{$row_ttl_qty|qty_nf}</td>	

				{assign var=total_qty value=$total_qty+$row_ttl_qty}
			{else}
				{if $form.deliver_branch && !$form.do_branch_id}
					{foreach from=$form.deliver_branch key=dummy item=bid}
						<td>&nbsp;</td>
					{/foreach}
				{else}
					<td>&nbsp;</td>
				{/if}
				<td>&nbsp;</td>
			{/if}
		</tr>
	{/foreach}

	<!--tr class="total_row">
		<th align="right" colspan="5">Total</th>
		<th align="right">{$total_ctn|qty_nf}</th>
		<th align="right">{$total_pcs|qty_nf}</th>
		<th>&nbsp;</th>
	</tr-->
</table>
</div>

