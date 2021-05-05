{*
2/23/2011 2:00:29 PM Alex
- Add consignment brearing

3/22/2011 4:56:35 PM Alex
- only show consignment bearing discount that match with price type

5/4/2011 9:29:04 AM Alex
- add line through for invalid 0 discount, 0 bearing or 0 net sales consignment bearing

9/6/2011 12:01:16 PM Alex
- add discount checking in consignment bearing mode
 
 9/14/2018 2:41 PM Andy
- Enhance promotion list sku to maximum list 1000 items.
*}

<div style='max-height:300px;overflow:auto;'>
	<ul>
	{foreach from=$items item=item}
		<li title="{$item.id},{$item.sku_item_code}"><input id=cb_ajax_sku_{$item.id} value="{$item.id},{$item.sku_item_code}" title="{$item.description}" type=checkbox> <label class=clickable for=cb_ajax_sku_{$item.id}>{$item.description}</label>
	 	<span class=informal> (Art No:{$item.artno}  MCode:{$item.mcode})</span>
	{/foreach}
	</ul>
	{if $reach_item_limit>0}
		<span style="color:blue;" class="small">* Maximum show {$reach_item_limit} items.</span>
	{/if}
</div>
	<input type=button value='Select All' onclick='sel_all(this);'>
	<table style='border:1px solid #999; padding:5px; background-color:#fe9' class='input_no_border small body' border=0 cellspacing=1 cellpadding=1>
	<tr bgcolor=#ffffff>
		<th colspan="5">Member</th>
		<th colspan="5">Non Member</th>
		<th rowspan=2>&nbsp;</th>
	</tr><tr bgcolor=#ffffff>
	<!--Member-->
{if $form.consignment_bearing eq 'yes'}	<th>Bearing / Nett Sales</th> {/if}
	<th>Discount</th>
{if $form.consignment_bearing ne 'yes'}	<th>Price</th>  {/if}
	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th>
	
	<!--Non Member-->
{if $form.consignment_bearing eq 'yes'}	<th>Bearing / Nett Sales</th> {/if}
	<th>Discount</th>
{if $form.consignment_bearing ne 'yes'}	<th>Price</th>  {/if}
	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th>
 </tr><tr>
	{if $form.consignment_bearing eq 'yes'}
	<td align=center>
	    <select name="m_consignment" id='m_consignment' onchange="check_disc(this,'select')" title="Code(Profit,Discount,Bearing/Net Sales)">
		    <option value="0,0,no,0," {if !$form.member_consignment } selected {/if}>-- No Discount --</option>
	    	{foreach name=cons from=$consignment item=cb}
		    	{if $form.price_type eq $cb.code}
		    		{assign var=m_profit value=$cb.profit}
					{assign var=m_discount value=$cb.discount}
	                {assign var=m_use_net value=$cb.use_net}
	                {assign var=m_bearing value=$cb.net_bearing}
	                {assign var=m_code value=$cb.code}
	                {assign var=m_val value=$m_profit,$m_discount,$m_use_net,$m_bearing,$m_code}
			    	<option value="{$cb.profit},{$cb.discount},{$cb.use_net},{$cb.net_bearing},{$cb.code}" 
					{if $m_discount eq '0' || $m_bearing eq '0'} style="text-decoration:line-through;"  {/if}
					{if $form.m_consignment eq $m_val } selected {/if}	 >{$cb.code}(P:{$cb.profit}%,{if $cb.discount ne ''}D:{$cb.discount},{/if}{if $cb.use_net eq 'yes'}N{else}B{/if}:{$cb.net_bearing}%)</option>
				{/if}
	    	{/foreach}
	    </select>
	</td>
	{/if}
	<td align=center>
		<input id=m_disc name=m_disc size=5 onchange="check_disc(this,'{if $form.consignment_bearing eq 'yes'}disc{/if}');">
	</td>
	<td align=center {if $form.consignment_bearing eq 'yes'} style="display:none"  {/if}><input id=m_price name=m_price size=5 onchange="check_disc(this);"></td>
	<td align=center><input name=m_min size=5></td>
	<td align=center><input name=m_from size=5></td>
	<td align=center><input name=m_to size=5></td>
	{if $form.consignment_bearing eq 'yes' }
	<td align=center>
	    <select name="nm_consignment" id='nm_consignment' onchange="check_disc(this,'select')" title="Code(Profit,Discount,Bearing)">
		    <option value="0,0,no,0," {if !$form.member_consignment} selected {/if}>-- No Discount --</option>
	    	{foreach name=cons from=$consignment item=cb}
		    	{if $form.price_type eq $cb.code}
		    		{assign var=nm_profit value=$cb.profit}
					{assign var=nm_discount value=$cb.discount}
	                {assign var=nm_use_net value=$cb.use_net}
	                {assign var=nm_bearing value=$cb.net_bearing}
	                {assign var=nm_code value=$cb.code}
	                {assign var=nm_val value=$nm_profit,$nm_discount,$nm_use_net,$nm_bearing,$nm_code}
			    	<option value="{$cb.profit},{$cb.discount},{$cb.use_net},{$cb.net_bearing},{$cb.code}"
					{if $nm_discount eq '0' || $nm_bearing eq '0'} style="text-decoration:line-through;"  {/if}
					{if $form.nm_consignment eq $nm_val} selected {/if}	 >{$cb.code}(P:{$cb.profit}%,{if $cb.discount ne ''}D:{$cb.discount},{/if}{if $cb.use_net eq 'yes'}N{else}B{/if}:{$cb.net_bearing}%)</option>
				{/if}
	    	{/foreach}
	    </select>
	</td>
	{/if}
	<td align=center>
		<input id=nm_disc name=nm_disc size=5 onchange="check_disc(this,'{if $form.consignment_bearing eq 'yes'}disc{/if}');">
	</td>
	<td align=center {if $form.consignment_bearing eq 'yes'} style="display:none"  {/if}><input id=nm_price name=nm_price size=5 onchange="check_disc(this);"></td>
	<td align=center><input name=nm_min size=5></td>
	<td align=center><input name=nm_from size=5></td>
	<td align=center><input name=nm_to size=5></td>
	<td align=center><input type=button value='Add' onclick='add_sku_listing();'></td>
	</tr></table>
{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}
	<script>check_disc($('m_consignment'),'select');</script>
	<script>check_disc($('nm_consignment'),'select');</script>
{/if}
