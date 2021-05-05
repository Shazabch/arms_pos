{*
2/28/2011 11:18:35 AM Andy
- Add "from qty".
- Add Discount Preference: "All discount target"
- Move column "Remark" to last column.
- Add receipt description.

4/6/2011 2:40:42 PM Andy
- Add checking for $config['promotion_hide_member_options'], if found will hide member column.

5/4/2011 4:58:48 PM Andy
- Add "discount by qty" for each mix and match promotion row.
- Add "Bundled Price", discount qty must more than 1.
- Add "Special FOC".
- Add discount target filter. (sku type, price type, price range)

7/15/2011 2:56:37 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
- Add can Print by branch.

2/27/2019 2:02 PM Andy
- Added "Printed on".
*}


{assign var=show_member_col value=1}
{if $config.promotion_hide_member_options}
    {assign var=show_member_col value=0}
{/if}

{include file='header.print.tpl'}
<style>
{literal}

{/literal}
</style>

<script type="text/javascript">
var doc_no = '{$form.id}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
<div class="printarea">

<!-- first page header -->
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
	<tr>
		<td><img src="{get_logo_url mod='promotion'}" height="80" hspace="5" vspace="5" /></td>
		<td width="100%">
			{$branch_info.description} {if $branch_info.company_no}({$branch_info.company_no}){/if}<br />
			{$branch_info.address|nl2br}<br />
			Tel: {$branch_info.phone_1}{if $branch_info.phone_2} / {$branch_info.phone_2}{/if}
			{if $branch_info.phone_3}&nbsp;&nbsp; Fax: {$branch_info.phone_3}{/if}
		</td>
		<td rowspan="2" align="right" valign="top">
		    <table class="xlarge">
			<tr>
				<td colspan="2">
					<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Promotion</b></div>
					{if $form.status==0}
					<div class="small">This draft Promotion is for internal use only.</div>
					{elseif !$form.approved}
					<div class="small">This Promotion is not valid, official Promotion will be issued after approval.</div>
					{/if}
				</td>
			</tr>
		    <tr bgcolor="#cccccc"><td nowrap>Promotion No.</td><td nowrap>{$form.id}</td></tr>
			<tr><td nowrap>Issued By</td><td nowrap>{$form.fullname}</td></tr>
			<tr bgcolor="#cccccc">
				<td nowrap>Created on</td><td nowrap>{$form.added|date_format:"%d/%m/%Y"}</td>
			</tr>
			<tr>
				<td nowrap>Printed on</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td>
			</tr>
		  	</table>
		</td>
	</tr>
<tr>
	<td colspan="2">
		<table>
			<tr>
			<td><b>Title:</b> </td>
			<td>{$form.title}</td>
			</tr>
			<tr>
			<td valign="top" rowspan="2"><b>Promotion Period:</b> </td>
			<td>{$form.date_from} - {$form.date_to}</td>
			</tr>
			<tr><td>{$form.time_from} - {$form.time_to}</td></tr>
			<tr>
			<td><b>Branch(s):</b> </td>
			<td>
				{if $print_promo_bid}
					{$branches.$print_promo_bid.code}
				{else}
					{foreach name=i from=$form.promo_branch_id item=b}
						{$b}{if !$smarty.foreach.i.last},{/if}
					{/foreach}
				{/if}
			</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<!-- end of first page header -->

{if $items.group_list}
	{foreach from=$items.group_list key=group_id item=promo_group}
	    <table class="nobreak" width="100%" style="margin-bottom:20px;">
	        <tr>
	            <td>
					<table class="nobox">
				        <tr>
				            <td><b>Limit qty discount per receipt</b></td>
				            <td>: {$promo_group.header.receipt_limit|default:'-'}</td>
				        </tr>
				        <tr>
				            <td><b>Discount Preference</b></td>
				            <td>:
				                {if $promo_group.header.disc_prefer_type eq 1}
				                    Most discount first
				                {elseif $promo_group.header.disc_prefer_type eq 0}
				                    Least discount first
                                {elseif $promo_group.header.disc_prefer_type eq 2}
				                    Manual Select
                                {elseif $promo_group.header.disc_prefer_type eq 3}
				                    All Discount Target
				                {/if}
				            </td>
				        </tr>
				        <tr>
	        				<td><b>Discount Follow Item Sequence</b></td>
	        				<td>: {if $promo_group.header.follow_sequence}Yes{else}No{/if}</td>
						</tr>
				        <tr>
				            <td><b>Available for</b></td>
				            <td>:
								{if $show_member_col}
					                Member
					                [ {if $promo_group.header.for_member eq 1}Yes{else}No{/if} ]
					                &nbsp;&nbsp;&nbsp;&nbsp;
				                {/if}
				                Non-Member
				                [ {if $promo_group.header.for_non_member eq 1}Yes{else}No{/if} ]
				            </td>
				        </tr>
				    </table>
				</td>
			 </tr>
			 <tr>
			    <td>
					<table width=100% class="tbd">
						<thead bgcolor="#ffffff">
						    <th width="20">#</th>
						    <th width="150">Discount Target</th>
							<th>Condition</th>
							<th width="80">Discount by</th>
							<th width="40">Others</th>
							<th width="200">Receipt Description</th>
						</thead>
						{foreach from=$promo_group.item_list item=promo_item name=fg}
						    <tr>
						        <td>{$smarty.foreach.fg.iteration}.</td>
						        <!-- Discount Target -->
						        <td valign="top">
						            {if $promo_item.disc_target_type eq 'receipt'}
									    <!-- Receipt Discount -->
										<b>Receipt</b>
									{elseif $promo_item.disc_target_type eq 'special_foc'}
										<!-- Special FOC -->
										<b>Special FOC:</b>
										{$promo_item.disc_target_info.description}
									{elseif $promo_item.disc_target_type eq 'sku'}
									    <!-- SKU Discount -->
										<b>SKU:</b>
										{$promo_item.item_info.sku_item_code|default:'-'} /
										{$promo_item.item_info.artno|default:'-'} /
										{$promo_item.item_info.description|default:'-'}
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
										{* SKU Group *}
										<b>SKU Group</b>
										{if $promo_item.item_info.code}
											{$promo_item.item_info.code} - 
										{/if}
										{$promo_item.item_info.description|default:'-'}
									{else}
									    <b>Invalid Discount</b>
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
								</td>
								
								<!-- Discount Condition -->
								<td valign="top">
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
													{* SKU Group *}
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
									<br /> 
									{if $promo_item.disc_by_type eq 'bundled_price' and $is_under_gst}
										<nobr>
											Selling Price Inclusive Tax : {$discount_by_inclusive_tax_arr[$promo_item.disc_by_inclusive_tax]|default:'Not Set'}
										</nobr>
									{/if}
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
                                {*<!-- Remark -->
								<td valign="top">
								    {$promo_item.item_remark|default:'-'|nl2br}
								</td>*}
						    </tr>
						{/foreach}
					</table>
			    </td>
			 </tr>
	    </table>
	{/foreach}
	
	<br />
	<table width="100%">
	<tr>
		<td width="50%" valign="bottom" class="small">
			______________________________<br>
			Accepted By<br>
			Name:
		</td>
		<td align="right" nowrap>
			<h1>Internal Copy</h1>
		</td>
	</tr>
	</table>
{/if}
</div>
</body>
</html>
