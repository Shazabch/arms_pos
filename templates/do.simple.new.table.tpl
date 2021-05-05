{*
*}

{config_load file="site.conf"}

<!-- do item table -->
<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border small body" cellspacing="1" cellpadding="1">

	<!--START HEADER-->
	<thead>
		<tr bgcolor="#ffffff">
			<th rowspan="2" width="20">#</th>
			<th nowrap rowspan="2" width="100">ARMS Code</th>
			<th nowrap rowspan="2" width="80">Article No</th>
			<th nowrap rowspan="2" width="80">MCode</th>
			<th nowrap rowspan="2">SKU Description</th>
			<th nowrap rowspan="2">DO UOM</th>
			<th nowrap colspan={count multi=1 var=$form.deliver_branch}>Qty</th>
			<th rowspan="2" width="60">Total<br />Qty</th>
		</tr>
		<tr bgcolor="#ffffff">
			{if $form.deliver_branch}
				{foreach from=$branch name=i item=b}
					{if in_array($b.id,$form.deliver_branch)}
						<th nowrap id="{$b.id}" class="deliver_branch_list">
							{$b.code}<br />
							<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span>
						</th>
					{/if}
				{/foreach}
			{else}
				<th nowrap>
					<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> 
					<span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span>
				</th>
			{/if}
		</tr>
	</thead>
	<!--END TABLE HEADER -->

	<!--START TABLE ITEMS-->
	<tbody id="do_items">
		{assign var=total_ctn value=0}
		{assign var=total_pcs value=0}
		{assign var=item_id value=0}

		{foreach from=$do_items item=item name=fitem}
			{assign var=item_id value=$item_id+1}
			{foreach from=$branch name=i item=b}
				{assign var=bid value=`$b.id`}
				{assign var=total_ctn value=$total_ctn+$item.ctn.$bid}
				{assign var=total_pcs value=$total_pcs+$item.pcs.$bid}
			{/foreach}
			<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="titem{$item_id}">
			{include file=do.simple.new.items.tpl}
			</tr>
		{/foreach}
	</tbody>
	<!--END TABLE ITEMS-->


	<!-- START TABLE FOOTER-->
	<tfoot>
		<!-- total -->
		<tr bgcolor="#ffffff" class="normal" height="24" id="total">
			{assign var=colspan value=6}
			{if $form.deliver_branch}
				{capture assign=xtra_colspan}{count multi=1 var=$form.deliver_branch}{/capture}
			{else}
				{assign var=xtra_colspan value=1}
			{/if}
			<td colspan="{$colspan+$xtra_colspan}" nowrap align="right"><b>Total</b></td>
			<td width="80">
				<b>
				T.Ctn : <span id="t_ctn">{$form.total_ctn|default:$total_ctn}</span><br />
				T.Pcs : <span id="t_pcs">{$form.total_pcs|default:$total_pcs}</span>
				</b>
			</td>
			<input type="hidden" id="total_ctn" name="total_ctn" value="{$form.total_ctn|default:$total_ctn}">
			<input type="hidden" id="total_pcs" name="total_pcs" value="{$form.total_pcs|default:$total_pcs}">
		</tr>

	</tfoot>
<!-- END TABLE FOOTER-->
</table>
