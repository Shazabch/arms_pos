{*

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

7/6/2010 9:37:18 AM edward
- add member_receipt_amt and non_member_receipt_amt

7/12/2010 4:56:57 PM edward
- remove member_receipt_amt and non_member_receipt_amt

12/16/2010 1:59:39 PM Andy
- Add block customer to buy more if already hit promotion items limit.

1/10/2011 10:55:38 AM Andy
- Show sku price type in promotion setup.

3/22/2011 2:43:40 PM Alex
- remove price type select all if consignment bearing mode

3/22/2011 3:09:13 PM Andy
- Add show/hide overlap promotion.

4/4/2011 6:36:56 PM Andy
- Add checking for $config['promotion_hide_member_options'], if found will hide member column.
- Add information popup for control type.

6/8/2011 6:11:01 PM Alex
- add new column for consignment bearing

5/7/2012 11:57:44 AM Andy
- Add promotion can set allowed member type.

8/23/2012 3:02 PM Justin
- Changed the wording "V149" into "V168".

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items
- if the same parent/child item put together, the 2nd item row color will change, until a new group sku

9/6/2013 3:40 PM Fithri
- add search item by vendor
- change brand search to autocomplete

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item
*}

{literal}
<style>
#sku_listing {
  background-color:transparent;
  margin:0px;
  padding:0px;
  font: bold 12px Arial, "Sans Serif";
  color: #A00;
}
#sku_listing ul {
  list-style-type:none;
  margin:0px;
  padding:0px;
}
#sku_listing ul li {
  list-style-type:none;
  display:block;
  margin:0;
  padding:2px;
  cursor:pointer;
}
#sku_listing ul li.selected { background-color: #ccc;}
#sku_listing ul li .informal { font: 10px Arial, "Sans Serif"; color: #666; }
</style>
{/literal}

<a href="javascript:void(toggle_overlap_promo(1));"><img src="/ui/expand.gif" /> Show all overlap promotion</a> |
<a href="javascript:void(toggle_overlap_promo(0));"><img src="/ui/collapse.gif" /> Hide all overlap promotion</a>
	
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width=100%  class="input_no_border small body table mb-0 text-md-nowrap  table-hover	" id=tbl_items>
				<thead class="bg-gray-100">
				<tr >
				<th rowspan=2 {if $allow_edit}colspan="2"{/if}>#</th>
				
				{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen and $config.promotion_approval_allow_reject_by_items}
				<th rowspan=2>Reject</th>
				{/if}
				
				<th rowspan=2>Arms Code</th>
				<th rowspan=2>Mcode</th>
				<th rowspan=2>Art No</th>
				<th rowspan=2>Desciption</th>
				<th rowspan="2">
					Control<br />Type
					[<a href="#" onClick="alert('Limit day or period only available to member');">?</a>]
				</th>
				<th rowspan=2>Cost</th>
				<th rowspan=2>Selling<br>Price</th>
				<th rowspan=2>Stock<br>Balance</th>
				<th rowspan="2">Price Type</th>
				
				{assign var=member_cols value=8}
				{assign var=non_member_cols value=6}
				
				<th colspan="{$member_cols}" {if $config.promotion_hide_member_options}style="display:none;"{/if}>Member</th>
				<th colspan="{$non_member_cols}">Non Member</th>
				</tr>
				<tr bgcolor=#ffffff>
					<!-- member -->
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Member<br />Type
						<a href="javascript:void(alert('This feature only available at counter BETA v168.'));">
							<img src="/ui/icons/information.png" align="absmiddle" />
						</a>
					</th>
					{if $form.consignment_bearing eq 'yes'}	<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Bearing / Nett Sales</th>  {/if}
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Discount</th>
					{if $form.consignment_bearing ne 'yes'}	<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Price</th>  {/if}
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Min Items</th>
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Qty From</th>
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Qty To</th>
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if}>Limit</th>
					<th {if $config.promotion_hide_member_options}style="display:none;"{/if} title="Block member to buy more if already hit item limit or over qty to.">Block<br />Normal<br />
						[<a href="#" onClick="alert('Block member to buy more if already hit item limit or over qty to.');">?</a>]
				
					</th>
					
					<!-- non member -->
					{if $form.consignment_bearing eq 'yes'}	<th>Bearing / Nett Sales</th>  {/if}
					<th>Discount</th>
					{if $form.consignment_bearing ne 'yes'}	<th>Price</th>  {/if}
					<th>Min Items</th>
					<th>Qty From</th>
					<th>Qty To</th>
					<th title="Block non-member to buy more if already over qty to.">Block<br />Normal<br />
						[<a href="#" onClick="alert('Block non-member to buy more if already over qty to.');">?</a>]
					</th>
				</tr>
				</thead>
				
				
				{include file=promotion.new.promotion_row.tpl}
				
				<tfoot id="tbl_footer">
				<tr class="normal" bgcolor="{#TB_ROWHEADER#}" id="add_sku_row" style="{if !$allow_edit}display:none; {/if}">
					<td colspan="25" nowrap>
					{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 include_all_sku_item=1}
					</td>
				</tr>
				
				<tbody class="fs-08">
					<tr class="normal" bgcolor="{#TB_ROWHEADER#}" id="add_sku_row" style="{if !$allow_edit}display:none; {/if}">
				
						<td colspan="25" nowrap>
					<div class="form-inline">
							<b class="form-label mt-4">Search Category</b>
						<input class="form-control mt-4" readonly id=category_id name=category_id size=3 value="{$smarty.request.category_id}">
						<input type=hidden id=category_tree name=category_tree value="{$smarty.request.category_tree}">
						<input class="form-control mt-4" id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus=this.select() size=50>
						<span id="brand_select">
						<b class="form-label ml-1">Brand</b>
						<input class="form-control ml-1" id=autocomplete_brand name=brand value="" onfocus=this.select() size=50>
						<input name="brand_id" id="brand_id" type="hidden" value="" />
						</span>
						<span id="sku_type_select">
						<b class="form-label ml-1">Sku Type</b>
						<select class="form-control ml-1" name=sku_type>
						<option value='All' selected>-- All --</option>
						{foreach from=$sku_type item=st}
						<option value="{$st.sku_type}">{$st.sku_type}</option>
						{/foreach}
						</select>
						</span>
						<span id="price_type_select">
						<b class="form-label ml-1">Price Type</b>
						<select class="form-control ml-1" name=price_type>
						{if $form.consignment_bearing ne 'yes'}
						<option value='All' selected>-- All --</option>
						{/if}
						{foreach from=$price_type item=pt}
						<option value="{$pt.price_type}">{$pt.price_type}</option>
						{/foreach}
						</select>
						</span>
						<span id="vendor_select">
						<b class="form-label ml-1">Vendor</b>
						<input class="form-control ml-1" id=autocomplete_vendor name=vendor value="" onfocus=this.select() size=50>
						<input name="vendor_id" class="form-control" id="vendor_id" type="hidden" value="" />
						{*
						<select class="form-control" name=vendor>
						{if $form.consignment_bearing ne 'yes'}
						<option value='All' selected>-- All --</option>
						{/if}
						{foreach from=$vendors item=v}
						<option value="{$v.id}">{$v.description}</option>
						{/foreach}
						</select>
						*}
						</span>
						
						<input class="btn btn-primary" type=button value="List" onclick='list_sku();'>
					</div>
						<br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
						<div id=autocomplete_category_choices class=autocomplete style="display:none;width:600px !important"></div>
						<div id=autocomplete_brand_choices class=autocomplete style="display:none;width:600px !important"></div>
						<div id=autocomplete_vendor_choices class=autocomplete style="display:none;width:600px !important"></div>
						<div id=sku_listing style="z-index:100"></div>
						</td>
					</tr>
				</tbody>
				
				</tfoot>
				</table>
		</div>
	</div>
</div>

{if $allow_edit}
<script>
init_autocomplete();
</script>
{/if}
