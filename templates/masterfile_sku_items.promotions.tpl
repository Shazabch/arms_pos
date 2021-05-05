{*
3/18/2011 4:20:46 PM Andy
- Enhance "Active Promotion" to show mix and match promotion. (move find_overlap_promo() to promotion.include.php)
- Show consignment bearning info at "active promotion".
- Add discount target filter. (sku type, price type, price range)

7/14/2011 12:00:51 PM Andy
- Add different background color for old, current or future promotion at promotion history/active promotion.

3/1/2012 10:56:13 AM Andy
- Show member type info for promotion history and SKU change price active promotion.

4/21/2014 4:48 PM Justin
- Bug fixed on showing discount type as "Invalid Discount" where it is SKU Group.
*}

<style>
{literal}
.is_old{
	background-color: #efefef;
}
.is_current{
	
}
.is_future{
	background-color: #d0fff0;
}
{/literal}
</style>

{assign var=show_member_col value=1}
{if $config.promotion_hide_member_options}
    {assign var=show_member_col value=0}
{/if}

{if !$data}
	<p align=center>-- No Data --</p>
{else} 
	{if $data.discount}
	    <!-- Discount Promotion -->
	    <h2>Discount Promotion</h2>
	    <span class="is_old" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Old Promotion&nbsp;&nbsp;
	    <span class="is_current" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Current Promotion&nbsp;&nbsp;
	    <span class="is_future" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Incoming / Future Promotion
		<table border="0" cellspacing="1" cellpadding="2" width="100%">
		<tr height="24" bgcolor="#ffee99">
			<th rowspan="2">ARMS Code</th>
			<th rowspan="2">Description</th>
			<th colspan="3">Promotion</th>
			{if $show_member_col}
				<th colspan="7">Member</th>
			{/if}
			<th colspan="5">Non-Member</th>
		</tr>
		<tr height=24 bgcolor=#ffee99>
			<th>Title</th>
			<th>Date</th>
			<th>Time</th>
			
			<!-- member -->
			{if $show_member_col}
				<th>Member Type</th>
				<th>Disc</th>
				<th>Price</th>
				<th>Min</th>
				<th>From</th>
				<th>To</th>
				<th>Limit</th>
			{/if}
			
			<!-- non member -->
			<th>Disc</th>
			<th>Price</th>
			<th>Min</th>
			<th>From</th>
			<th>To</th>
		</tr>
		{foreach from=$data.discount item=d}
			{foreach from=$d.promos name=k item=p}
			<tr class="{$p.promo_status}" valign="top">
				{if $smarty.foreach.k.index==0}
				<td>{$d.item.sku_item_code}</td>
				<td>{$d.item.description}</td>
				{else}
				<td>&nbsp;</td><td>&nbsp;</td>
				{/if}
				<td><a href="/promotion.php?a=view&id={$p.promo_id}&branch_id={$p.branch_id}&highlight_item_id={$p.sku_item_id}" target=_blank>{$p.title}</a></td>
				<td>{$p.date_from}<br>{$p.date_to}</td>
				<td>{$p.time_from}<br>{$p.time_to}</td>

				<!-- member -->
				{if $show_member_col}
					<td align="center">
						{if !isset($p.allowed_member_type.member_type)}
							All
						{else}
							{foreach from=$p.allowed_member_type.member_type key=mt item=mtr name=fmt}
								{if !$smarty.foreach.fmt.first}, {/if}
								{$mt}
							{/foreach}
						{/if}
					</td>
					{if $p.consignment_bearing eq 'yes'}
					    <td colspan="2">
					        {$p.member_trade_code}(P:{$p.member_prof_p}%,D:{$p.member_disc_p|ifzero:"0%"},{if $p.member_use_net eq 'yes'}N{else}B{/if}: {$p.member_net_bear_p}%)
						</td>
					{else}
						<td class="r">{$p.member_disc_p|ifzero:'-'}</td>
						<td class="r">{$p.member_disc_a|number_format:2|ifzero:'-'}</td>
					{/if}
					<td class="r">{$p.member_min_item|ifzero:'-'}</td>
					<td class="r">{$p.member_qty_from|ifzero:'-'}</td>
					<td class="r">{$p.member_qty_to|ifzero:'-'}</td>
					<td class="r">{$p.member_limit|ifzero:'-'}</td>
				{/if}
				
				<!-- non member -->
				{if $p.consignment_bearing eq 'yes'}
				    <td colspan="2">
				        {$p.non_member_trade_code}(P:{$p.non_member_prof_p}%,D:{$p.non_member_disc_p|ifzero:"0%"},{if $p.non_member_use_net eq 'yes'}N{else}B{/if}:{$p.non_member_net_bear_p}%)
				    </td>
				{else}
					<td class="r">{$p.non_member_disc_p|ifzero:'-'}</td>
					<td class="r">{$p.non_member_disc_a|number_format:2|ifzero:'-'}</td>
				{/if}
				<td class="r">{$p.non_member_min_item|ifzero:'-'}</td>
				<td class="r">{$p.non_member_qty_from|ifzero:'-'}</td>
				<td class="r">{$p.non_member_qty_to|ifzero:'-'}</td>
			</tr>
			{/foreach}
		{/foreach}
		</table>
	{/if}
	
	{if $data.mix_n_match}
	    <h2>Mix and Match Promotion</h2>
	    <span class="is_old" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Old Promotion&nbsp;&nbsp;
	    <span class="is_current" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Current Promotion&nbsp;&nbsp;
	    <span class="is_future" style="border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Incoming / Future Promotion
	    <table border="0" cellspacing="1" cellpadding="2" width="100%">
	        <thead bgcolor="#ffee99">
	            <tr>
	                <th width="20">#</th>
	                <th>Title</th>
	                <th>Date</th>
	                <th>Time</th>
				    <th width="300">Discount Target</th>
					<th>Condition</th>
					<th width="100">Discount by</th>
					<th width="80">Others</th>
					<th>Receipt<br />Description</th>
				</tr>
			</thead>
			{foreach from=$data.mix_n_match key=sid item=promo_item_list}
			    {foreach from=$promo_item_list item=promo_item name=fp}
				    <tr class="{$promo_item.promo_status}" valign="top">
				        <td>{$smarty.foreach.fp.iteration}.</td>
				        <td>{$promo_item.title}</td>
				        <td>{$promo_item.date_from}<br />{$promo_item.date_to}</td>
				        <td>{$promo_item.time_from}<br />{$promo_item.time_to}</td>
				        <!-- Discount Target -->
				        <td>
				            <a href="/promotion.mix_n_match.php?a=view&id={$promo_item.promo_id}&branch_id={$promo_item.branch_id}&highlight_promo_item_id={$promo_item.id}" target=_blank>
				            {if $promo_item.disc_target_type eq 'receipt'}
							    <!-- Receipt Discount -->
								<b>Receipt</b>
							{elseif $promo_item.disc_target_type eq 'sku'}
							    <!-- SKU Discount -->
								<b>SKU:</b>
								{$promo_item.item_info.sku_item_code|default:'-'} /
								{$promo_item.item_info.artno|default:'-'} /
								{$promo_item.item_info.description|default:'-'}
								<br>
								Selling: {$promo_item.disc_target_info.selling_price|default:'0'|number_format:2}<br>
								Cost: {$promo_item.disc_target_info.cost_price|default:'0'|number_format:2}<br>

								<input type="hidden" name="disc_target_info{$element_name_extend}[selling_price]" value="{$promo_item.disc_target_info.selling_price}" />
								<input type="hidden" name="disc_target_info{$element_name_extend}[cost_price]" value="{$promo_item.disc_target_info.cost_price}" />
							{elseif $promo_item.disc_target_type eq 'brand'}
							    <!-- Brand Discount -->
								<b>Brand:</b>
								{$promo_item.item_info.description|default:'UN-BRANDED'}
				            {elseif $promo_item.disc_target_type eq 'category'}
							    <!-- Category Discount -->
								<b>Category:</b>
								{$promo_item.item_info.description|default:'UN-CATEGORIZE'}
				            {elseif $promo_item.disc_target_type eq 'category_brand'}
							    <!-- Category + Brand Discount -->
								<b>Category:</b>
								{$promo_item.item_info.cat_desc|default:'UN-CATEGORIZE'}
								<br />+<br />
								<b>Brand:</b>
								{$promo_item.item_info.brand_desc|default:'UN-BRANDED'}
							{elseif $promo_item.disc_target_type eq 'sku_group'}
								<b>SKU Group</b>
								{if $promo_item.item_info.code}
									{$promo_item.item_info.code} - 
								{/if}
								{$promo_item.item_info.description|default:'-'}
							{else}
							    <img src="/ui/messages.gif" align="absmiddle" /> <b>Invalid Discount</b>
							{/if}
							
							<!-- SKU Type -->
							{if $promo_item.disc_target_sku_type}
								<br /><nobr><b>SKU Type:</b> {$promo_item.disc_target_sku_type}</nobr>
							{/if}
							
							<!-- Price Type -->
							{if $promo_item.disc_target_price_type}
								<br /><nobr><b>Price Type:</b> {$promo_item.disc_target_price_type}</nobr>
							{/if}
							
							<!-- Price Range -->
							{if $promo_item.disc_target_price_range_from>0 || $promo_item.disc_target_price_range_to>0}
								<br />
								<nobr>
									<b>Price:</b>
								{if $promo_item.disc_target_price_range_from > 0 and !$promo_item.disc_target_price_range_to}
									more than {$promo_item.disc_target_price_range_from|number_format:2}
								{elseif !$promo_item.disc_target_price_range_from and $promo_item.disc_target_price_range_to > 0}
									less than {$promo_item.disc_target_price_range_to|number_format:2}
								{else}
									from 
									{$promo_item.disc_target_price_range_from|number_format:2}
									to
									{$promo_item.disc_target_price_range_to|number_format:2}
								{/if}
								</nobr>
							{/if}
							</a>
				        </td>
				        
				        <!-- Discount Condition -->
						<td>
						    <ul style="margin-bottom:0;padding-bottom:0;padding-left:20px;">
						    {foreach from=$promo_item.disc_condition key=conditon_row_num item=promo_disc_condition}
							    <li>
							        {if $promo_disc_condition.rule eq 'over_equal'}Over or equal
							        {else}{$promo_disc_condition.rule|capitalize}
							        {/if}
							        {if $promo_disc_condition.condition_type eq 'amt'}Amount
									{elseif $promo_disc_condition.condition_type eq 'qty'}Quantity
									{/if}
									of
									<b>{$promo_disc_condition.condition_value|default:'0'|num_format:2}</b>
									<br />
									for
									{if $promo_disc_condition.item_type eq 'receipt'}
									    <b>Receipt</b>
									{else}
									    {if $promo_disc_condition.item_type eq 'sku'}
									        <b>SKU</b>
									        {$promo_disc_condition.item_info.sku_item_code|default:'-'} /
											{$promo_disc_condition.item_info.artno|default:'-'} /
											{$promo_disc_condition.item_info.description|default:'-'}
								        {elseif $promo_disc_condition.item_type eq 'brand'}
									        <b>Brand</b>
									        {$promo_disc_condition.item_info.description|default:'UN-BRANDED'}
								        {elseif $promo_disc_condition.item_type eq 'category'}
									        <b>Category</b>
									        {$promo_disc_condition.item_info.description|default:'UN-CATEGORIZE'}
										{elseif $promo_disc_condition.item_type eq 'category_brand'}
								            <!-- Category + Brand Discount -->
											<b>Category:</b>
											{$promo_disc_condition.item_info.cat_desc|default:'UN-CATEGORIZE'}
											<b>+</b>
											<b>Brand:</b>
											{$promo_disc_condition.item_info.brand_desc|default:'UN-BRANDED'}
										{elseif $promo_disc_condition.item_type eq 'sku_group'}
											<b>SKU Group</b>
											{if $promo_disc_condition.item_info.code}
												{$promo_disc_condition.item_info.code} - 
											{/if}
											{$promo_disc_condition.item_info.description|default:'-'}
										{else}
										    <b>Unknown Type</b>
									    {/if}
									{/if}
							    </li>

						    {/foreach}
						    </ul>
						</td>
						
						<!-- Discount by -->
						<td valign="top">
							<nobr>
								By
								{$discount_by_type[$promo_item.disc_by_type]}
								<!-- dont show discount value if it is FOC -->
								{if $promo_item.disc_by_type ne 'foc'}
									: {$promo_item.disc_by_value|default:'0'|num_format:2}
								{/if}
							</nobr>
							<br />
							<nobr>
								Discount Qty : {$disc_by_qty_type[$promo_item.disc_by_qty]|default:$promo_item.disc_by_qty}
							</nobr>
						</td>

						<!-- Qty Limit -->
						<td>
                            {if $promo_item.qty_from}
                                <div style="white-space: nowrap;">Qty from {$promo_item.qty_from}</div>
                            {/if}
                            {if $promo_item.disc_limit}
                                <div style="white-space: nowrap;">Qty Limit {$promo_item.disc_limit}</div>
                            {/if}
                            {if $promo_item.loop_limit}
                            	<nobr>
                            		Loop Limit {$promo_item.loop_limit}
                            	</nobr>
                            {/if}
                            &nbsp;
                        </td>

                        <!-- Receipt Description-->
                        <td valign="top">{$promo_item.receipt_description|default:'-'}</td>
				    </tr>
			    {/foreach}
			{/foreach}
	    </table>
	{/if}
{/if}
