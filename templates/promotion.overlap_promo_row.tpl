{*
4/4/2011 6:36:56 PM Andy
- Add checking for $config['promotion_hide_member_options'], if found will hide member column.

7/8/2011 12:22:18 PM Andy
- Make overlap promotion info close at default.
- Touch up overlap promotion info mprice layout.

7/26/2011 11:12:56 AM Andy
- Fix category discount does not show in overlap promotion details if there is no other promotion,qprice and mprice is overlap.

3/1/2012 10:56:06 AM Andy
- Show member type info for promotion history and SKU change price active promotion.
- Add category discount by SKU info at overlap promotion info.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

11/13/2013 3:31 PM Andy
- Enhance to show SKU Group info in overlap info.

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price
*}

{if $from_template eq 'mix_n_match'}
	{assign var=is_mix_n_match value=1}
{/if}

{if $overlap_pi_discount or $overlap_pi_mix or $overlap_mprice or $overlap_qprice or $overlap_category_disc or $overlap_category_disc_by_sku}
	{assign var=total_overlap_pi_count value=0}
	{capture assign=overlap_pi_discount_count}{count var=$overlap_pi_discount}{/capture}
	{capture assign=overlap_pi_mix_count}{count var=$overlap_pi_mix}{/capture}
	{capture assign=overlap_mprice_count}{count var=$overlap_mprice}{/capture}
	{capture assign=overlap_qprice_count}{count var=$overlap_qprice}{/capture}
	{capture assign=overlap_category_disc_count}{count var=$overlap_category_disc}{/capture}
	{capture assign=overlap_category_disc_by_sku_count}{count var=$overlap_category_disc_by_sku}{/capture}
	
	{assign var=total_overlap_pi_count value=$overlap_pi_discount_count+$overlap_pi_mix_count}
<tr id="tr_overlap_pi-{$promo_item_id}">
	<td colspan="30">
	    <div style="border:1px solid black;opacity:1;">
	    
	    <div style="background-color:#fff;padding-left:10px;">
			<img src="/ui/expand.gif" align="absmiddle" title="Expand/Collapse overlap promotion info" onClick="togglediv('div_overlap_pi-{$promo_item_id}', this);" class="img_toggle_overlap_pi" /> 
			<b>
				Overlap Promotion Details ({$total_overlap_pi_count|number_format} item{if $total_overlap_pi_count>1}s{/if})
				{if $overlap_mprice_count}
					, MPrice ({$overlap_mprice_count})
				{/if}
				{if $overlap_qprice_count}
					, QPrice ({$overlap_qprice_count})
				{/if}
				{if $overlap_category_disc_count}
					, Category Discount ({$overlap_category_disc_count})
				{/if}
				{if $overlap_category_disc_by_sku_count}
					, Category Discount by SKU ({$overlap_category_disc_by_sku_count})
				{/if}
			</b>
		</div>
		
	    <div id="div_overlap_pi-{$promo_item_id}" class="div_overlap_pi_content" style="display:none;">
	    
	    <!-- promotion discount -->
	    {if $overlap_pi_discount}
	        <table width="100%" bgcolor="#ffffff">
	            <thead bgcolor="#f3f3f0">
		            <tr>
		                <th colspan="2" rowspan="2">Discount Promotion</th>
						<th rowspan="2">Cost</th>
						<th rowspan="2">Selling<br>Price</th>
						<th rowspan="2">Stock<br>Balance</th>

						{assign var=member_cols value=7}
						{assign var=non_member_cols value=5}
						<th colspan="{$member_cols}" {if $config.promotion_hide_member_options}style="display:none;"{/if}>Member</th>
						<th colspan="{$non_member_cols}">Non Member</th>
		            </tr>
		            <tr>
						<!-- member -->
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Member Type</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Discount</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Price</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Min Items</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Qty From</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Qty To</th>
						<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Limit</th>
						
						<!-- non member -->
						<th>Discount</th>
						<th>Price</th>
						<th>Min Items</th>
						<th>Qty From</th>
						<th>Qty To</th>
					</tr>
				</thead>
	            {foreach from=$overlap_pi_discount item=overlap_pi}
	                <tr>
	                    <!-- discount promotion -->
						<td>{$overlap_pi.title} <a href="/promotion.php?a=view&id={$overlap_pi.promo_id}&branch_id={$overlap_pi.branch_id}&highlight_item_id={$overlap_pi.sku_item_id}" target="_BLANK">Promo#{$overlap_pi.promo_id}, item#{$overlap_pi.id}</a>
						<font color=red>{strip}
						({if $overlap_pi.approved == 1 and $overlap_pi.status == 1}
						Approved
						{elseif $overlap_pi.status == 4 or $overlap_pi.status == 5}
						Cancelled
						{elseif ($overlap_pi.status == 1 or $overlap_pi.status == 3) and not $overlap_pi.approved}
						Wating for Approval
						{elseif not $overlap_pi.approved and $overlap_pi.status == 2}
						Rejected
						{else}
						Saved Promotion
						{/if}){/strip}</font>
						</td>
						
						<td><font color="black">{$overlap_pi.date_from} - {$overlap_pi.date_to} {$overlap_pi.time_from|date_format:"%H:%M"} - {$overlap_pi.time_to|date_format:"%H:%M"}</font></td>
						<td align="right">{$overlap_pi.grn_cost|number_format:2}</td>
						<td align="right">{$overlap_pi.selling_price|number_format:2|ifzero:"-"}</td>
						<td align="center">{$overlap_pi.qty}</td>
						
						<!-- member -->
						<td  align="center">
							{if !isset($overlap_pi.allowed_member_type.member_type)}
								All
							{else}
								{foreach from=$overlap_pi.allowed_member_type.member_type key=mt item=mtr name=fmt}
									{if !$smarty.foreach.fmt.first}, {/if}
									{$mt}
								{/foreach}
							{/if}
						</td>
						{if $overlap_pi.consignment_bearing eq 'yes'}
						<td align="center" colspan="2" {if $config.promotion_hide_member_options}style="display:none;"{/if}>
							{$overlap_pi.member_trade_code}(P:{$overlap_pi.member_prof_p}%,D:{$overlap_pi.member_disc_p|ifzero:"0%"},B:{$overlap_pi.member_net_bear_p}%)
						</td>
						{else}
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_disc_p|ifzero:"-"}</td>
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_disc_a|ifzero:"-"}</td>
						{/if}
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_min_item|ifzero:"-"}</td>
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_qty_from|ifzero:"-"}</td>
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_qty_to|ifzero:"-"}</td>
						<td align="center" {if $config.promotion_hide_member_options}style="display:none;"{/if}>{$overlap_pi.member_limit|ifzero:"-"}</td>

						<!-- non member -->
						{if $overlap_pi.consignment_bearing eq 'yes'}
						<td align="center" colspan="2">
							{$overlap_pi.non_member_trade_code}(P:{$overlap_pi.non_member_prof_p}%,D:{$overlap_pi.non_member_disc_p|ifzero:"0%"},B:{$overlap_pi.non_member_net_bear_p}%)
						</td>
						{else}
						<td align="center">{$overlap_pi.non_member_disc_p|ifzero:"-"}</td>
						<td align="center">{$overlap_pi.non_member_disc_a|ifzero:"-"}</td>
						{/if}
						<td align="center">{$overlap_pi.non_member_min_item|ifzero:"-"}</td>
						<td align="center">{$overlap_pi.non_member_qty_from|ifzero:"-"}</td>
						<td align="center">{$overlap_pi.non_member_qty_to|ifzero:"-"}</td>
	                </tr>
	            {/foreach}
	        </table>
	    {/if}
	    
	    <!-- promotion mix and match -->
		{if $overlap_pi_mix}
		    <table width="100%" bgcolor="#ffffff">
		        <thead bgcolor="#f3f3f0">
			        <tr>
		                <th colspan="30">Mix and Match Promotion</th>
		            </tr>
	            </thead>
	            {foreach from=$overlap_pi_mix item=overlap_pi}
	                <tr>
	                    <!-- mix and match promotion -->
						<td>{$overlap_pi.title} <a href="/promotion.mix_n_match.php?a=view&id={$overlap_pi.promo_id}&branch_id={$overlap_pi.branch_id}&highlight_promo_item_id={$overlap_pi.id}" target="_BLANK">#{$overlap_pi.promo_id}, item#{$overlap_pi.id}</a>
							<font color="red">{strip}
							({if $overlap_pi.approved == 1 and $overlap_pi.status == 1}
							Approved
							{elseif $overlap_pi.status == 4 or $overlap_pi.status == 5}
							Cancelled
							{elseif ($overlap_pi.status == 1 or $overlap_pi.status == 3) and not $overlap_pi.approved}
							Wating for Approval
							{elseif not $overlap_pi.approved and $overlap_pi.status == 2}
							Rejected
							{else}
							Saved Promotion
							{/if}){/strip}</font>
						</td>

						<td><font color="black">
								{$overlap_pi.date_from} - {$overlap_pi.date_to},
								 {$overlap_pi.time_from|date_format:"%H:%M"} - {$overlap_pi.time_to|date_format:"%H:%M"}
							 </font>
						</td>
						
						<td>
						    {if $overlap_pi.disc_target_type eq 'sku'}
							    <!-- SKU Discount -->
								<b>SKU:</b>
								{$overlap_pi.item_info.sku_item_code|default:'-'} /
								{$overlap_pi.item_info.artno|default:'-'} /
								{$overlap_pi.item_info.description|default:'-'}
							{elseif $overlap_pi.disc_target_type eq 'brand'}
							    <!-- Brand Discount -->
								<b>Brand:</b>
								{$overlap_pi.item_info.description|default:'UN-BRANDED'}
				            {elseif $overlap_pi.disc_target_type eq 'category'}
							    <!-- Category Discount -->
								<b>Category:</b>
								{$overlap_pi.item_info.description|default:'UN-CATEGORIZE'}
				            {elseif $overlap_pi.disc_target_type eq 'category_brand'}
							    <!-- Category + Brand Discount -->
								<b>Category:</b>
								{$overlap_pi.item_info.cat_desc|default:'UN-CATEGORIZE'}
								<br />+<br />
								<b>Brand:</b>
								{$overlap_pi.item_info.brand_desc|default:'UN-BRANDED'}
							{/if}
						</td>
						<td>
						    <ul style="margin-bottom:0;padding-bottom:0;padding-left:0;">
						    {foreach from=$overlap_pi.disc_condition key=conditon_row_num item=promo_disc_condition}
							    <li>
							        {if $promo_disc_condition.rule eq 'over_equal'}Over or equal
							        {else}{$promo_disc_condition.rule|capitalize}
							        {/if}
							        {if $promo_disc_condition.condition_type eq 'amt'}Amount
									{elseif $promo_disc_condition.condition_type eq 'qty'}Quantity
									{/if}
									of
									<b>{$promo_disc_condition.condition_value|default:'0'|num_format:2}</b>
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
						<td>
							<nobr>
							    Discount By
								{$discount_by_type[$overlap_pi.disc_by_type]}
								{if $overlap_pi.disc_by_type ne 'foc'}
									: {$overlap_pi.disc_by_value|default:'0'|num_format:2}
								{/if}
							</nobr>
							<br />
							<nobr>
								Discount Qty : {$disc_by_qty_type[$overlap_pi.disc_by_qty]|default:$overlap_pi.disc_by_qty}
							</nobr>
							<br />
							{if $overlap_pi.disc_by_type eq 'bundled_price' and $is_under_gst}
								<nobr>
									Selling Price Inclusive Tax : {$discount_by_inclusive_tax_arr[$overlap_pi.disc_by_inclusive_tax]|default:'Not Set'}
								</nobr>
							{/if}
						</td>
						<td>{if $overlap_pi.qty_from}
                                Qty from {$overlap_pi.qty_from}
                            {/if}
                            {if $overlap_pi.disc_limit}
                                Qty Limit {$overlap_pi.disc_limit}
                            {/if}
						</td>
	                </tr>
	            {/foreach}
			</table>
		{/if}
		
			<!-- MPrice -->
			{if $overlap_mprice_count}
				<table width="100%" bgcolor="#ffffff">
			        <thead bgcolor="#f3f3f0">
				        <tr>
			                <th colspan="30">MPrice</th>
			            </tr>
			            <tr>
			            	<th>Branch</th>
			            	{foreach from=$config.sku_multiple_selling_price item=mprice_type}
			            		<th>{$mprice_type}</th>
			            	{/foreach}
			            </tr>
		            </thead>
		            {foreach from=$branch item=b}
		            	{if $overlap_mprice[$b.id]}
			            	<tr>
			            		<td>{$b.code}</td>
			            		{foreach from=$config.sku_multiple_selling_price item=mprice_type}
			            			<td class="r">{$overlap_mprice[$b.id].$mprice_type.price|number_format:2}</td>
			            		{/foreach}
			            	</tr>
		            	{/if}
		            {/foreach}
				</table>
			{/if}
			
			<!-- QPrice -->
			{if $overlap_qprice_count}
				<table width="100%" bgcolor="#ffffff">
			        <thead bgcolor="#f3f3f0">
				        <tr>
			                <th colspan="30">QPrice</th>
			            </tr>
			            <tr>
			            	<th>Branch</th>
			            	<th>Min Qty (>=)</th>
			            	<th>Price</th>
			            </tr>
		            </thead>
		            {foreach from=$overlap_qprice item=oq}
		            	<tr>
		            		<td>{$oq.bcode}</td>
		            		<td class="r">{$oq.min_qty}</td>
		            		<td class="r">{$oq.price|number_format:2}</td>
		            	</tr>
		            {/foreach}
				</table>
			{/if}
			
			<!-- Category Discount -->
			{if $overlap_category_disc_count}
				<table width="100%" bgcolor="#ffffff" border="0.5" class="tb"  cellspacing="0">
			        <thead bgcolor="#f3f3f0">
				        <tr>
			                <th colspan="30">Category Discount (%)</th>
			            </tr>
			            <tr>
			            	<th>Code</th>
			            	<th>Category</th>
			            	<th>Type</th>
			            	
			            	{foreach from=$overlap_category_disc item=ocd}
			            		{if $ocd.category_disc_by_branch.0}
			            			{if !$cat_disc_branch.0}
			            				<th>All</th>
			            			{/if}
			            			{array_assign array_name="cat_disc_branch" key1=0 value="1"}
			            			
				            		<!-- member global -->
					            	{if $ocd.category_disc_by_branch.0.member.global}
			            				{array_assign array_name="cat_disc_mem_type" key1='member' key2='global' value=1}
			            			{/if}
			            			
			            			<!-- non-member global -->
			            			{if $ocd.category_disc_by_branch.0.nonmember.global}
			            				{array_assign array_name="cat_disc_mem_type" key1='nonmember' key2='global' value=1}
			            			{/if}
			            			
			            			<!-- member type -->
									{foreach from=$config.membership_type key=mtype item=mtype_desc}
										{if is_numeric($mtype)}
											{assign var=mt value=$mtype_desc}
										{else}
											{assign var=mt value=$mtype}
										{/if}
			            				{if $ocd.category_disc_by_branch.0.member.$mt}
			            					{array_assign array_name="cat_disc_mem_type" key1='member' key2=$mt value=1}
			            				{/if}
			            			{/foreach}
		            			{/if}
	            			{/foreach}
				            			
			            	{foreach from=$branch item=b}
			            		{assign var=tmp_bid value=$b.id}
			            		{foreach from=$overlap_category_disc item=ocd}
			            			<!-- check whether this branch got override discount-->
				            		{if $ocd.category_disc_by_branch.$tmp_bid}
				            			{array_assign array_name="cat_disc_branch" key1=$tmp_bid value="1"}
	
						            	<!-- member global -->
				            			{if $ocd.category_disc_by_branch.$tmp_bid.member.global}
				            				{array_assign array_name="cat_disc_mem_type" key1='member' key2='global' value=1}
				            			{/if}
				            			
				            			<!-- non-member global -->
				            			{if $ocd.category_disc_by_branch.$tmp_bid.nonmember.global}
				            				{array_assign array_name="cat_disc_mem_type" key1='nonmember' key2='global' value=1}
				            			{/if}
				            			
				            			<!-- member type -->
										{foreach from=$config.membership_type key=mtype item=mtype_desc}
											{if is_numeric($mtype)}
												{assign var=mt value=$mtype_desc}
											{else}
												{assign var=mt value=$mtype}
											{/if}
				            				{if $ocd.category_disc_by_branch.$tmp_bid.member.$mt}
				            					{array_assign array_name="cat_disc_mem_type" key1='member' key2=$mt value=1}
				            				{/if}
				            			{/foreach}
				            		{/if}
				            		
				            		
			            		{/foreach}
			            		{if $cat_disc_branch.$tmp_bid}
			            			<th>{$b.code}</th>
			            		{/if}
			            	{/foreach}
			            </tr>
		            </thead>
		            {foreach from=$overlap_category_disc item=ocd name=focd}		            	
		            	{capture assign=cat_info_html}
		            		<td bgcolor="#ffe100">{$ocd.code|default:'&nbsp;'}</td>
		            		<td bgcolor="#ffe100">{$ocd.description|default:'&nbsp;'}</td>
		            	{/capture}
		            	
		            	<!-- member -->
		            	{if !$config.promotion_hide_member_options}
			            	{if $cat_disc_mem_type.member.global}
			            		<tr>
			            			{$cat_info_html}
			            			{assign var=cat_info_html value='<td>&nbsp;</td><td>&nbsp;</td>'}
			            			
			            			<td>Member</td>
			            			
			            			{if $cat_disc_branch.0}
		            					<td class="r">
											{$ocd.category_disc_by_branch.0.member.global}&nbsp;
										</td>
		            				{/if}
			            				
			            			<!-- loop branch -->
			            			{foreach from=$branch item=b}
			            				{assign var=tmp_bid value=$b.id}
			            				{if $cat_disc_branch.$tmp_bid}
			            					<td class="r">
												{$ocd.category_disc_by_branch.$tmp_bid.member.global}&nbsp;
											</td>
			            				{/if}
			            			{/foreach}
			            		</tr>
			            	{/if}
		            	{/if}
		            	
		            	<!-- non-member -->
		            	{if $cat_disc_mem_type.nonmember.global}
		            		<tr>
		            			{$cat_info_html}
		            			{assign var=cat_info_html value='<td>&nbsp;</td><td>&nbsp;</td>'}
		            			
		            			<td>Non-Member</td>
		            			
		            			<!-- loop branch -->
		            			{if $cat_disc_branch.0}
	            					<td class="r">
										{$ocd.category_disc_by_branch.0.nonmember.global}&nbsp;
									</td>
	            				{/if}
		            				
		            			{foreach from=$branch item=b}
		            				{assign var=tmp_bid value=$b.id}
		            				{if $cat_disc_branch.$tmp_bid}
		            					<td class="r">
											{$ocd.category_disc_by_branch.$tmp_bid.nonmember.global}&nbsp;
										</td>
		            				{/if}
		            			{/foreach}
		            		</tr>
		            	{/if}
		            	
		            	{if !$config.promotion_hide_member_options}
		            		<!-- member type -->
							{foreach from=$config.membership_type key=mtype item=mtype_desc}
								{if is_numeric($mtype)}
									{assign var=mt value=$mtype_desc}
								{else}
									{assign var=mt value=$mtype}
								{/if}
		            			{if $cat_disc_mem_type.member.$mt}
			            			<tr>
			            				{$cat_info_html}
			            				{assign var=cat_info_html value='<td>&nbsp;</td><td>&nbsp;</td>'}
			            				
			            				<td>{$mtype_desc}</td>
			            				
			            				{if $cat_disc_branch.0}
			            					<td class="r">
												{$ocd.category_disc_by_branch.0.member.$mt}&nbsp;
											</td>
			            				{/if}
				            				
			            				<!-- loop branch -->
				            			{foreach from=$branch item=b}
				            				{assign var=tmp_bid value=$b.id}
				            				{if $cat_disc_branch.$tmp_bid}
				            					<td class="r">
													{$ocd.category_disc_by_branch.$tmp_bid.member.$mt}&nbsp;
												</td>
				            				{/if}
				            			{/foreach}
			            			</tr>
		            			{/if}
		            		{/foreach}
		            	{/if}
		            {/foreach}
				</table>
			{/if}
			
			<!-- Category Discount by SKU -->
			{if $overlap_category_disc_by_sku_count}
				<br />
				<table width="100%" bgcolor="#ffffff" border="0.5" class="tb"  cellspacing="0">
			        <thead bgcolor="#f3f3f0">
				        <tr>
			                <th colspan="30">Category Discount by SKU (%)</th>
			            </tr>
			            <tr>
			            	<th>ARMS Code</th>
			            	<th>MCode</th>
			            	<th>Description</th>
			            	<th>Type</th>
			            	
			            	{foreach from=$overlap_category_disc_by_sku item=ocd}
			            		{if $ocd.category_disc_by_branch_inherit.0}
			            			{if !$cat_disc_by_sku_branch.0}
			            				<th>All</th>
			            			{/if}
			            			{array_assign array_name="cat_disc_by_sku_branch" key1=0 value="1"}
			            			
				            		<!-- member global -->
					            	{if $ocd.category_disc_by_branch_inherit.0.member.global}
			            				{array_assign array_name="cat_disc_by_sku_mem_type" key1='member' key2='global' value=1}
			            			{/if}
			            			
			            			<!-- non-member global -->
			            			{if $ocd.category_disc_by_branch_inherit.0.nonmember.global}
			            				{array_assign array_name="cat_disc_by_sku_mem_type" key1='nonmember' key2='global' value=1}
			            			{/if}
			            			
			            			<!-- member type -->
									{foreach from=$config.membership_type key=mtype item=mtype_desc}
										{if is_numeric($mtype)}
											{assign var=mt value=$mtype_desc}
										{else}
											{assign var=mt value=$mtype}
										{/if}
			            				{if $ocd.category_disc_by_branch_inherit.0.member.$mt}
			            					{array_assign array_name="cat_disc_by_sku_mem_type" key1='member' key2=$mt value=1}
			            				{/if}
			            			{/foreach}
		            			{/if}
	            			{/foreach}
	            			
	            			{foreach from=$branch item=b}
			            		{assign var=tmp_bid value=$b.id}
			            		{foreach from=$overlap_category_disc_by_sku item=ocd}
			            			<!-- check whether this branch got override discount-->
				            		{if $ocd.category_disc_by_branch_inherit.$tmp_bid}
				            			{array_assign array_name="cat_disc_by_sku_branch" key1=$tmp_bid value="1"}
	
						            	<!-- member global -->
				            			{if $ocd.category_disc_by_branch_inherit.$tmp_bid.member.global}
				            				{array_assign array_name="cat_disc_by_sku_mem_type" key1='member' key2='global' value=1}
				            			{/if}
				            			
				            			<!-- non-member global -->
				            			{if $ocd.category_disc_by_branch_inherit.$tmp_bid.nonmember.global}
				            				{array_assign array_name="cat_disc_by_sku_mem_type" key1='nonmember' key2='global' value=1}
				            			{/if}
				            			
				            			<!-- member type -->
										{foreach from=$config.membership_type key=mtype item=mtype_desc}
											{if is_numeric($mtype)}
												{assign var=mt value=$mtype_desc}
											{else}
												{assign var=mt value=$mtype}
											{/if}
				            				{if $ocd.category_disc_by_branch_inherit.$tmp_bid.member.$mt}
				            					{array_assign array_name="cat_disc_by_sku_mem_type" key1='member' key2=$mt value=1}
				            				{/if}
				            			{/foreach}
				            		{/if}
				            		
				            		
			            		{/foreach}
			            		{if $cat_disc_by_sku_branch.$tmp_bid}
			            			<th>{$b.code}</th>
			            		{/if}
			            	{/foreach}
			            </tr>
			        </thead>
			        {foreach from=$overlap_category_disc_by_sku item=ocd name=focd}		            	
		            	{capture assign=sku_info_html}
		            		<td bgcolor="#ffe100">{$ocd.sku_item_code|default:'&nbsp;'}</td>
		            		<td bgcolor="#ffe100">{$ocd.mcode|default:'&nbsp;'}</td>
		            		<td bgcolor="#ffe100">{$ocd.description|default:'&nbsp;'}</td>
		            	{/capture}
		            	
		            	<!-- member -->
		            	{if !$config.promotion_hide_member_options}
			            	{if $cat_disc_by_sku_mem_type.member.global}
			            		<tr>
			            			{$sku_info_html}
			            			{assign var=sku_info_html value='<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>'}
			            			
			            			<td>Member</td>
			            			
			            			{if $cat_disc_by_sku_branch.0}
		            					<td class="r">
											{$ocd.category_disc_by_branch_inherit.0.member.global}&nbsp;
										</td>
		            				{/if}
			            				
			            			<!-- loop branch -->
			            			{foreach from=$branch item=b}
			            				{assign var=tmp_bid value=$b.id}
			            				{if $cat_disc_by_sku_branch.$tmp_bid}
			            					<td class="r">
												{$ocd.category_disc_by_branch_inherit.$tmp_bid.member.global}&nbsp;
											</td>
			            				{/if}
			            			{/foreach}
			            		</tr>
			            	{/if}
		            	{/if}
		            
		            	<!-- non-member -->
		            	{if $cat_disc_by_sku_mem_type.nonmember.global}
		            		<tr>
		            			{$sku_info_html}
		            			{assign var=sku_info_html value='<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>'}
		            			
		            			<td>Non-Member</td>
		            			
		            			<!-- loop branch -->
		            			{if $cat_disc_by_sku_branch.0}
	            					<td class="r">
										{$ocd.category_disc_by_branch_inherit.0.nonmember.global}&nbsp;
									</td>
	            				{/if}
		            				
		            			{foreach from=$branch item=b}
		            				{assign var=tmp_bid value=$b.id}
		            				{if $cat_disc_by_sku_branch.$tmp_bid}
		            					<td class="r">
											{$ocd.category_disc_by_branch_inherit.$tmp_bid.nonmember.global}&nbsp;
										</td>
		            				{/if}
		            			{/foreach}
		            		</tr>
		            	{/if}
		            	
		            	{if !$config.promotion_hide_member_options}
		            		<!-- member type -->
							{foreach from=$config.membership_type key=mtype item=mtype_desc}
								{if is_numeric($mtype)}
									{assign var=mt value=$mtype_desc}
								{else}
									{assign var=mt value=$mtype}
								{/if}
		            			{if $cat_disc_by_sku_mem_type.member.$mt}
			            			<tr>
			            				{$sku_info_html}
			            				{assign var=sku_info_html value='<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>'}
			            				
			            				<td>{$mtype_desc}</td>
			            				
			            				{if $cat_disc_by_sku_branch.0}
			            					<td class="r">
												{$ocd.category_disc_by_branch_inherit.0.member.$mt}&nbsp;
											</td>
			            				{/if}
				            				
			            				<!-- loop branch -->
				            			{foreach from=$branch item=b}
				            				{assign var=tmp_bid value=$b.id}
				            				{if $cat_disc_by_sku_branch.$tmp_bid}
				            					<td class="r">
													{$ocd.category_disc_by_branch_inherit.$tmp_bid.member.$mt}&nbsp;
												</td>
				            				{/if}
				            			{/foreach}
			            			</tr>
		            			{/if}
		            		{/foreach}
		            	{/if}
		            {/foreach}
			    </table>
			{/if}
		</div>
		</div>
	</td>
</tr>
{/if}
