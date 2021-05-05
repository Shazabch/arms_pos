{*

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

7/12/2010 4:56:57 PM edward
- remove member_receipt_amt and non_member_receipt_amt

9/15/2010 10:08:12 AM Andy
- Fix when first time add promotion item, if stock balance is zero it will show blank problem.
- Fix some javascript issue, change onBlur to onChange for input checking.
- Fix when add new item, if got copy discount percent from last item, discount amount input still can edit bugs.
- Fix wrong item num bugs.

12/16/2010 1:59:39 PM Andy
- Add block customer to buy more if already hit promotion items limit.

1/3/2011 12:08:56 PM Andy
- Change "control type" only can edit in "Draft" and "Rejected" status.

1/10/2011 10:55:38 AM Andy
- Show sku price type in promotion setup.

3/18/2011 4:33:48 PM Andy
- Add highlight sku when click on the link to view other overlap promotion.
- Move all overlap promotion item script to new template.

3/22/2011 2:37:39 PM Alex
- only show consignment bearing discount that match with item price type

4/4/2011 6:36:56 PM Andy
- Add checking for $config['promotion_hide_member_options'], if found will hide member column.

5/4/2011 9:29:04 AM Alex
- add line through for invalid 0 discount, 0 bearing or 0 net sales consignment bearing 

6/8/2011 4:59:19 PM Alex
- split consignment bearing promotion into new column

9/6/2011 12:01:16 PM Alex
- add discount checking in consignment bearing mode

12/2/2011 12:07:01 PM Andy
- Add checking to Min Qty, Qty From/To and Qty Limit to only accept number.

12/9/2011 3:18:13 PM Alex
- change checking status to readonly when show consignment bearing words or selectbox

3/2/2012 2:47:59 PM Andy
- Add category discount by SKU info at overlap promotion info.
- Add promotion can set allowed member type.

8/3/2012 11:19 AM Andy
- Hide edit member type popup icon if no member type config.

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items
- if the same parent/child item put together, the 2nd item row color will change, until a new group sku

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

07/04/2016 17:30 Edwin
- Enhanced on disable block normal when qty_to and limit is 0 or null.

9/25/2017 6:00 PM Andy
- Fixed if turn on config.promotion_hide_member_options will hide the wrong column.
*}

{foreach from=$promotion_items item=item name=fitem}
<tbody class="tbinput group_colour" group_id="{$item.sku_id}" item_id="{$item.id}" id="promo_item_{$item.id}" {if $item.figure} style="color:red" {/if} >
<tr {if $smarty.request.highlight_item_id eq $item.sku_item_id}class=highlight_row{/if} >
<td align=center>
{if $form.status == 1 and $form.approved == 1 and $form.active == 1 and $promo_item_count > 1 and $sessioninfo.privilege.PROMOTION_CANCEL}
<input type=checkbox name=cancel_item[{$item.id}]>
{/if}
<!-- {$itemcount++} -->
	<span class="item_no">
	{if $itemcount}
	{$itemcount}.
	{else}
	{$smarty.foreach.fitem.iteration}.
	{/if}
	</span>
</td>
{if $allow_edit}
<td nowrap>
<a href="javascript:void(0)" onclick="delete_item({$item.id});"><img src="/ui/icons/cross.png" border=0></a>
</td>
{/if}

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen and $config.promotion_approval_allow_reject_by_items}
<td align="left">
	<input class="rejected_item rejected_item_cb" name="rejected_item_id[{$item.id}]" type="checkbox" value="1" onchange="reject_cb_clicked(this);" />
	<span style="display:none;"><input name="rejected_item_reason[{$item.id}]" class="rejected_item" type="text" size="10" placeholder="Reason" /></span>
</td>
{/if}

<td>{$item.sku_item_code|default:"-"}<input name=sku_id[] value="{$item.sku_item_id}" type=hidden></td>
<td>{$item.mcode|default:"-"}</td>
<td>{$item.artno|default:"-"}</td>
<td>
	{$item.description}
	{if ($allow_edit or ($smarty.request.a eq 'view' and $form.approved==0)) and $form.id < 1000000000 and $item.extra_info.reject_reason and $form.status==0}
	<br /><span style="color:red;"><b>( Reject reason : {$item.extra_info.reject_reason} )</b></span>
	{/if}
</td>

<td align="center">
	<select name="control_type[{$item.id}]" field_type="control_type" onchange="control_type_changed(this);" {if $form.status ne 0 and $form.status ne 2}disabled {/if}>
	    {foreach from=$control_type key=k item=t}
	        <option value="{$k}" {if $k eq $item.control_type}selected {/if}>{$t}</option>
	    {/foreach}
	</select>
	{if $item.control_type>0}
		{assign var=hide_inp value=1}
	{else}
	    {assign var=hide_inp value=0}
	{/if}
</td>

<td align=right>{$item.grn_cost|number_format:2}</td>
<td align=right>{$item.selling_price|number_format:2|ifzero:"-"}<input id=selling_price_{$item.id} name=selling_price[{$item.id}] value="{$item.selling_price}" type=hidden></td>

<td align=center>{$item.qty|num_format:2}</td>
<!-- Price type -->
<td align="center">
	{$item.item_price_type|default:'-'}
	<input type="hidden" name="item_price_type[{$item.id}]" value="{$item.item_price_type}" />
</td>

	<!-- member -->
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		{if $allow_edit and $form.status eq 0 and !$form.approved and $config.membership_type}
			<img src="/ui/ed.png" title="Edit Allowed Member Type" style="float:left;" class="clickable" onClick="show_choose_member_type_popup('{$item.id}');" />
		{/if}
		
		<div id="div_allowed_member_type-{$item.id}">
			{if !isset($item.allowed_member_type.member_type)}
				All
			{else}
				{foreach from=$item.allowed_member_type.member_type key=mt item=mtr name=fmt}
					<input type="hidden" name="allowed_member_type[{$item.id}][member_type][{$mt}]" value="{$mt}" class="allowed_member_type-{$item.id}" />
					{if !$smarty.foreach.fmt.first}, {/if}
					{$mt}
				{/foreach}
			{/if}
		</div>
	</td>
	
	{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		{if $readonly or $form.approved == 1}
		{$item.member_trade_code}(P:{$item.member_prof_p}%,{if $item.cb_member_disc_p ne ''}D:{$item.cb_member_disc_p|ifzero:"0%"},{/if}{if $item.member_use_net eq 'yes'}N{else}B{/if}: {$item.member_net_bear_p}%)
		{else}
		{if $item.figure.m}<img src="/ui/icons/exclamation.png" title="Figure Mismatch">{/if}
		{assign var=member_item_profit value=$item.member_prof_p}
		{assign var=member_item_discount value=$item.cb_member_disc_p}
        {assign var=member_item_use_net value=$item.member_use_net}
        {assign var=member_item_bearing value=$item.member_net_bear_p}
        {assign var=member_item_code value=$item.member_trade_code}
        {assign var=member_item_val value=$member_item_profit,$member_item_discount,$member_item_use_net,$member_item_bearing,$member_item_code}
	    <select name="member_consignment[{$item.id}]" id='member_consignment_{$item.id}' field_type="member_consignment" onchange="check_discount(this,{$item.id},'select')" title="Code(Profit,Discount,Bearing/Net Sales)">
		    <option value="0,0,no,0," {if !$form.member_consignment[$item.id] || $form.member_consignment[$item.id] eq "0,0,no,0," || $member_item_val eq "0,0,no,0,"} selected {/if}>-- No Discount --</option>
		    {assign var=member_val value=0}
	    	{foreach name=cons from=$consignment item=cb}
	    	    {if $item.item_price_type eq $cb.code}
		    		{assign var=member_profit value=$cb.profit}
					{assign var=member_discount value=$cb.discount}
	                {assign var=member_use_net value=$cb.use_net}
	                {assign var=member_bearing value=$cb.net_bearing}
	                {assign var=member_code value=$cb.code}
	                {assign var=member_val value=$member_profit,$member_discount,$member_use_net,$member_bearing,$member_code}
			    	<option value="{$cb.profit},{$cb.discount},{$cb.use_net},{$cb.net_bearing},{$cb.code}"
					{if $member_discount eq '0' || $member_bearing eq '0'} style="text-decoration:line-through;"  {/if}						
					{if $form.member_consignment[$item.id] eq $member_val || $form.member_selected_consignment eq $member_val || $member_item_val eq $member_val} selected {/if}>
						{$cb.code}(P:{$cb.profit}%,{if $cb.discount ne ''}D:{$cb.discount},{/if}{if $cb.use_net eq 'yes'}N{else}B{/if}:{$cb.net_bearing}%)
					</option>
				{/if}
	    	{/foreach}
	    </select>
	    {/if}
	</td>
	{/if}
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		<input size="5" class="member_disc_p"  id="member_disc_p_{$item.id}" name="member_disc_p[{$item.id}]" value="{if $predisc}{$predisc.0|ifzero:''}{else}{$item.member_disc_p|ifzero:''}{/if}" field_type="member_disc_p" onChange="check_discount(this,{$item.id},'{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}disc{/if}')" {if $item.member_disc_a <> 0}readonly {/if} />
		{*<img border=0 align='absmiddle' src='/ui/foc.png' onclick="show_consignment_table({$item.id},'member')">*}
	</td>
	<td align="center" {if $form.consignment_bearing eq 'yes' or $config.promotion_hide_member_options} style="display:none"  {/if} >  
		<input size="5" id="member_disc_a_{$item.id}" name="member_disc_a[{$item.id}]" value="{$item.member_disc_a|ifzero:''}" field_type="member_disc_a" onChange="check_discount(this,{$item.id})" {if $item.member_disc_p or $predisc.0}readonly {/if} />
	</td>
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		<input size=5 name=member_min_item[{$item.id}] class="with_no_control" field_type="member_min_item" {if $hide_inp}readonly {/if} value="{$item.member_min_item}" onChange="show_change_value_by_parent_dialog(this);" />
	</td>
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		<input size=5 name=member_qty_from[{$item.id}] class="with_no_control" field_type="member_qty_from" {if $hide_inp}readonly {/if} value="{$item.member_qty_from}" onChange="show_change_value_by_parent_dialog(this);" />
	</td>
	<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		<input size=5 name=member_qty_to[{$item.id}] class="with_no_control" field_type="member_qty_to" {if $hide_inp}readonly {/if} value="{$item.member_qty_to}" onChange="show_change_value_by_parent_dialog(this);" />
	</td>
    <td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
		<input size=5 name=member_limit[{$item.id}] class="with_control" field_type="member_limit" {if !$hide_inp}readonly {/if} value="{$item.member_limit}" onChange="show_change_value_by_parent_dialog(this);" />
	</td>
    <td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}><input type="checkbox" name="member_block_normal[{$item.id}]" {if $item.member_block_normal}checked {/if} {if !$item.member_qty_to and !$item.member_limit}disabled{/if} value="1" field_type="member_block_normal" onclick="show_change_value_by_parent_dialog(this);" /></td>
    <!--<td align=center><input size=5 name=member_receipt_amt[{$item.id}] class="with_no_control" {if $hide_inp}readonly {/if} value="{$item.member_receipt_amt}"></td>-->

	<!-- non member -->
	{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}
	<td align=center >
		{if $readonly or $form.approved == 1}
		{$item.non_member_trade_code}(P:{$item.non_member_prof_p}%,{if $item.cb_non_member_disc_p ne ''}D:{$item.cb_non_member_disc_p|ifzero:"0%"},{/if}{if $item.non_member_use_net eq 'yes'}N{else}B{/if}:{$item.non_member_net_bear_p}%)
		{else}
		{if $item.figure.nm}<img src="/ui/icons/exclamation.png" title="Figure Mismatch">{/if}
		{assign var=non_item_profit value=$item.non_member_prof_p}
		{assign var=non_item_discount value=$item.cb_non_member_disc_p}
        {assign var=non_item_use_net value=$item.non_member_use_net}
        {assign var=non_item_bearing value=$item.non_member_net_bear_p}
        {assign var=non_item_code value=$item.non_member_trade_code}
        {assign var=non_item_val value=$non_item_profit,$non_item_discount,$non_item_use_net,$non_item_bearing,$non_item_code}
	    <select name="non_consignment[{$item.id}]" id='non_consignment_{$item.id}' field_type="non_consignment" onchange="check_discount(this,{$item.id},'select')" title="Code(Profit,Discount,Bearing/Net Sales)">
		    <option value="0,0,no,0," {if (!$form.non_consignment[$item.id] || $form.non_consignment[$item.id] eq "0,0,no,0," || $non_item_val eq "0,0,no,0,")} selected {/if}>-- No Discount --</option>
	    	{foreach name=cons from=$consignment item=cb}
	    	    {if $item.item_price_type eq $cb.code}
		    		{assign var=non_profit value=$cb.profit}
					{assign var=non_discount value=$cb.discount}
					{assign var=non_use_net value=$cb.use_net}
	                {assign var=non_bearing value=$cb.net_bearing}
	                {assign var=non_code value=$cb.code}
	                {assign var=non_val value=$non_profit,$non_discount,$non_use_net,$non_bearing,$non_code}
			    	<option value="{$cb.profit},{$cb.discount},{$cb.use_net},{$cb.net_bearing},{$cb.code}" 
					{if $non_discount eq '0' || $non_bearing eq '0'} style="text-decoration:line-through;"  {/if}					
					{if $form.non_consignment[$item.id] eq $non_val || $form.non_selected_consignment eq $non_val || $non_item_val eq $non_val} selected {/if} >{$cb.code}(P:{$cb.profit}%,{if $cb.discount ne ''}D:{$cb.discount},{/if}{if $cb.use_net eq 'yes'}N{else}B{/if}:{$cb.net_bearing}%)</option>
				{/if}
	    	{/foreach}
	    </select>
		{/if}
	</td>
	{/if}
	<td align="center">
	<input size=5 class="non_member_disc_p with_no_control" id=non_member_disc_p_{$item.id} name=non_member_disc_p[{$item.id}] value="{if $predisc}{$predisc.1|ifzero:''}{else}{$item.non_member_disc_p|ifzero:''}{/if}" field_type="non_member_disc_p" onchange="check_discount(this,{$item.id},'{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}disc{/if}')"  {if $item.non_member_disc_a <> 0 or $hide_inp}readonly{/if}>
{*	<img border=0 align='absmiddle' src='/ui/foc.png' onclick="show_consignment_table({$item.id},'non_member')">    *}
	</td>
	<td align=center {if $form.consignment_bearing eq 'yes'} style="display:none"  {/if}><input size=5 id=non_member_disc_a_{$item.id} name=non_member_disc_a[{$item.id}] value="{$item.non_member_disc_a|ifzero:''}" field_type="non_member_disc_a" onchange="check_discount(this,{$item.id})" {if $predisc.1 or $item.non_member_disc_p or $hide_inp}readonly {/if} class="with_no_control" /></td>
	<td align=center>
		<input size=5 name=non_member_min_item[{$item.id}] class="with_no_control" field_type="non_member_min_item" {if $hide_inp}readonly {/if}  value="{$item.non_member_min_item}" onchange="show_change_value_by_parent_dialog(this);" />
	</td>
	<td align=center>
		<input size=5 name=non_member_qty_from[{$item.id}] class="with_no_control" field_type="non_member_qty_from" {if $hide_inp}readonly {/if} value="{$item.non_member_qty_from}" onchange="show_change_value_by_parent_dialog(this);" />
	</td>
	<td align=center>
		<input size=5 name=non_member_qty_to[{$item.id}] class="with_no_control" field_type="non_member_qty_to" {if $hide_inp}readonly {/if} value="{$item.non_member_qty_to}" onChange="show_change_value_by_parent_dialog(this);" />
	</td>
	<!--<td align=center><input size=5 name=non_member_receipt_amt[{$item.id}] class="with_no_control" {if $hide_inp}readonly {/if} value="{$item.non_member_receipt_amt}"></td>-->
	<td align="center"><input type="checkbox" name="block_normal[{$item.id}]" {if $item.block_normal}checked {/if} {if !$item.non_member_qty_to}disabled{/if} value="1" field_type="block_normal" onclick="show_change_value_by_parent_dialog(this);"/></td>
</tr>
{if $form.consignment_bearing eq 'yes' || $item.consignment_bearing eq 'yes'}
	<script>
		if ($('member_consignment_'+{$item.id}))
			check_discount($('member_consignment_'+{$item.id}),{$item.id},'select');
		
		if ($('non_consignment_'+{$item.id}))
			check_discount($('non_consignment_'+{$item.id}),{$item.id},'select');
	
	</script>
{/if}

    {include file='promotion.overlap_promo_row.tpl' overlap_pi_discount=$ditems.discount[$item.id] overlap_pi_mix=$ditems.mix_n_match[$item.id] promo_item_id=$item.id overlap_mprice=$ditems.mprice[$item.id] overlap_qprice=$ditems.qprice[$item.id] overlap_category_disc=$ditems.category_disc[$item.id] overlap_category_disc_by_sku=$ditems.category_disc_by_sku[$item.id]}
</tbody>
{/foreach}

