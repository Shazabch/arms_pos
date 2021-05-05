{*
7/28/2009 1:12:44 PM Andy
- Add ctn 1 and ctn 2

5/10/2009 5:01:17 PM yinsee
- add stock location

3/30/2010 10:18:45 AM yinsee
- show thumbail

5/24/2010 12:51:40 PM Andy
- Fix if show by branch, items column span too short.

6/3/2010 12:07:45 PM yinsee
- fix sku listing photo bug (point to branch to get the photo)

10/18/2010 6:50:46 PM Alex
- add cost checking at smarty_get_selling_cost_balance() to trigger display a red star

11/8/2010 5:24:06 PM yinsee
- change a .photo_hide to div.photo_icon .photo_hide

12/9/2010 12:18:20 PM Andy
- Change get sku apply photo method.

12/14/2010 10:59:05 AM Andy
- Temporary remove the "Delete sku apply photo feature".
- Change the way to get sku apply photo to use back the old method.

4/20/2011 11:06:57 AM Andy
- Add checking for sku photo path and change path to show the image. use_new_sku_photo_path() and get_image_path()

5/23/2011 10:46:20 AM Andy
- Add generate cache for thumb picture.

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache when view in popup.

6/2/2011 5:00:03 PM Andy
- Move CSS photo_icon and photo_hide class to screen.css

7/7/2011 2:01:46 PM Andy
- Change SKU Master File to load photo from ajax if found multi server mode and photo is not store at own server.

9/22/2011 3:17:53 PM Andy
- Significantly increase loading speed.
- Change sorting script label.

10/17/2011 11:51:59 AM Alex
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/24/2011 5:27:34 PM Andy
- Fix showing divide to zero warning.

6/29/2012 2:36:23 PM Justin
- Fixed bug of can't show out inactive info while cannot found the log info but got record.

9/21/2012 10:18 AM Justin
- Added to show indicator of "BOM" beside SKU item Code while found SKU is BOM.

12/19/2013 11:10 AM Andy
- Fix sku photo path if got special character will not able to show in popup.

4/16/2014 5:21 PM Justin
- Added new context menu "Set PO Re-order Qty by Branch" and ability to hide this menu while SKU is not allowed to set reorder by branch.

10/16/2014 3:53 PM Justin
- Bug fixed on CTN1/CTN2 have been wrongly round into decimal points.

7/30/2015 5:58 PM Joo Chia
- Enhance to show input tax, output tax, and inclusive tax in table.

12/04/2015 3:07PM DingRen
- fix wrong gp calculation

6/15/2017 13:45 Qiu Ying
- Enhanced to show total page record

2017-09-21 09:45 AM Qiu Ying
- Enhanced to add column "Packing UOM"
- Enhanced to add column Stock Reorder Min & Max Qty, if set by branch, then display under branch column

11/22/2017 9:29 AM Justin
- Optimised to take out on screen call for smarty function.
- Enhanced to have Group by SKU checkbox and able to sum up the stock balance while it is checked.
- Enhanced to hide SKU child items while Group by SKU checkbox is checked and allow user to show it by click on expand icon.

11/30/2017 6:03 PM Justin
- Bug fixed on the Bal Qty column for all items always show 0.
- Bug fixed when the Branch Group is selected, item details in HQ are not shown.

12/20/2017 5:22 PM Andy
- Fixed to show "N/A" for no inventory sku.

11/20/2018 3:51 PM Justin
- Enhanced to take out the clickable sorting feature since it has been moved to SKU filtering screen.

4/8/2019 10:48 AM Andy
- Fixed Stock Reorder Qty column cannot be show if no turn on GST.

8/8/2019 9:12 AM William
- Fixed bug item context menu cannot pop up when sku item inactive.

8/3/2020 1:55 PM William
- Enhanced to show promotion photo as 1st photo on sku listing table.

9/3/2020 5:43 PM William
- Enhanced to clear browser cache when diplay new upload image.
*}
{literal}
<style>

</style>
{/literal}

{if $smarty.request.branches_group >0}{assign var=branch value=$branch2}
{else}{assign var=branch value=$branch_list}{/if}
{if !$branch}{assign var=branch value=$branches}{/if}
{$item_from} - {$item_to} of {$total_item|number_format} SKU parents
<table id="tbl_si" width=100% cellpadding=2 cellspacing=1 border=0 style="padding:1px;border:1px solid #000;">
	<tr height=24 bgcolor=#ffee99>
		<td colspan={if $config.link_code_name}8{else}7{/if}>&nbsp;</td>
		<th rowspan=2>Packing UOM</th>
		{if $config.enable_gst}
			<th rowspan=2>Input Tax</th>
			<th rowspan=2>Output Tax</th>
			<th rowspan=2>Selling Price Inclusive Tax</th>
		{/if}
		{if $show_global_reorder_qty}
			<th colspan="2">PO Reorder Qty</th>
		{/if}
		{if BRANCH_CODE eq 'HQ'}
			{if $branch.1.code}
				{assign var=colspan value=2}
				{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SHOW_COST}
					{assign var=colspan value=$colspan+1}
				{/if}
				{if $config.masterfile_sku_enable_ctn}
					{assign var=colspan value=$colspan+2}
				{/if}
				<!-- -HQ -->
				{if $show_reorder_by_branch}
					{assign var=colspan value=$colspan+2}
				{/if}
				<th colspan="{$colspan}" title="{$branches.1.description}">{$branch.1.code}</th>
				{if $smarty.request.branches_group eq ''}
					{foreach from=$branches_group.header item=bg}
						<th bgcolor="#ffcc99;"><a href="javascript:showGroup('{$bg.id}')">{$bg.code}</a></th>
					{/foreach}
				{/if}
			{/if}
			
			{assign var=colspan value=2}
			{if $show_reorder_by_branch}
				{assign var=colspan value=$colspan+2}
			{/if}
			{if $config.masterfile_sku_enable_ctn}
				{assign var=colspan value=$colspan+2}
			{/if}
			{foreach from=$branch key=bid item=br}
				{if $bid ne 1}
					<th colspan="{$colspan}" title="{$br.description}">{$br.code}</th>
				{/if}
			{/foreach}
		{else}
			{assign var=cols value=8}
			{if $show_global_reorder_qty}
				{assign var=cols value=$cols+2}
			{/if}
			{if $show_reorder_by_branch}
				{assign var=cols value=$cols+2}
			{/if}
			{if $config.masterfile_sku_enable_ctn}{assign var=cols value=$cols+2}{/if}
			<th colspan="{$cols}">{$BRANCH_CODE}</th>
		{/if}
	</tr>
	<tr height=24 bgcolor=#ffee99>
		<th>&nbsp;</th>
		<th>ARMS Code</th>
		<th>Art&nbsp;No</th>
		<th>MCode</th>
		{if $config.link_code_name}<th>{$config.link_code_name}</th>{/if}
		<th>Brand</th>
		<th width="400">Description<br><img src="/ui/pixel.gif" width="400" height="1"></th>
		<th>Loc</th>
		{if $show_global_reorder_qty}
			<th nowrap>S. R.<br />Min Qty</th>
			<th nowrap>S. R.<br />Max Qty</th>
		{/if}

		{if BRANCH_CODE eq 'HQ'}
			{if $branch.1.code}
				<th>Bal<br>Qty</th>
				{if $config.masterfile_sku_enable_ctn}
					<th>Ctn1</th>
					<th>Ctn2</th>
				{/if}
				<th>Selling{if $sessioninfo.privilege.SHOW_COST}<br>Cost{/if}</th>
				{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SHOW_COST}
					<th>HQ Cost</th>
				{/if}
				{if $show_reorder_by_branch}
					<th nowrap>S. R.<br />Min Qty</th>
					<th nowrap>S. R.<br />Max Qty</th>
				{/if}
			{/if}
			{if $smarty.request.branches_group eq ''}
				{foreach from=$branches_group.header item=bg}
					<th bgcolor="#ffcc99;">Bal<br>Qty</th>
				{/foreach}
			{/if}
		{/if}

		{foreach from=$branch key=bid item=br}
			{if $bid ne 1}
				<th>Stock<br>Balance</th>
				{if $config.masterfile_sku_enable_ctn}
		            <th>Ctn1</th>
		            <th>Ctn2</th>
	            {/if}
				{if BRANCH_CODE ne 'HQ'}
					<th>S.Price</th>
					{if $sessioninfo.privilege.SHOW_COST}<th>GRN Cost</th>{/if}
				{else}
					<th>Selling{if $sessioninfo.privilege.SHOW_COST}<br>Cost{/if}</th>
				{/if}
				{if BRANCH_CODE ne 'HQ'}
					{if $sessioninfo.privilege.SHOW_COST}<th>AVG Cost</th>{/if}
						<th>3mth GRN</th>
						<th>3mth Sales</th>
						<th>1mth GRN</th>
						<th>1mth Sales</th>
					{/if}
					{if $show_reorder_by_branch}
						<th nowrap>S. R.<br />Min Qty</th>
						<th nowrap>S. R.<br />Max Qty</th>
					{/if}
				{/if}
		{/foreach}
	</tr>
	
	{section name=i loop=$sku}
		{assign var=sku_id value=$sku[i].sku_id}
		<tr bgcolor="{cycle name=r1 values=",#eeeeee"}" class="tr_sku_item {if !$sku[i].active}inactive{else} thover clickable{/if}" onclick="show_context_menu(this, {$sku[i].sku_id}, {$sku[i].id}, '{$sku[i].sku_item_code}')" po_reorder_by_child="{$sku[i].po_reorder_by_child}">
			<td nowrap>
				<a href="javascript:void(window.open('{$smarty.server.PHP_SELF}?a=view&id='+context_info.sku_id))"><img src="/ui/view.png" border="0" alt="Detail"></a>
				{if $sku[i].photo_count > 0 or $sku[i].photos or $sku[i].promotion_photos or ($sku[i].url_to_get_photo && !$config.single_server_mode)}
					<div class="photo_icon" id="div_photo_icon-{$sku[i].id}">
						<img src="/ui/icons/images.png" border="0" alt="Detail">
						<div class=photo_hide>

						{*
							{section name=loop start=1 loop=`$sku[i].photo_count+1`}
							{capture assign=p}{$sku[i].image_path}sku_photos/{$sku[i].sku_apply_items_id}/{$smarty.section.loop.iteration}.jpg{/capture}
							<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View">
							{/section}
						*}
						<!-- sku promotion photo -->
						{if $sku[i].promotion_photos}
							<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.loop.iteration}" src="/thumb.php?w=100&h=100&cache={if $sku[i].promotion_photos_time}{$sku[i].promotion_photos_time}{else}1{/if}&img={$sku[i].promotion_photos[0]|urlencode}" border="1" style="cursor:pointer;" title="View" class="sku_photo" onClick="show_sku_image_div('{$sku[i].promotion_photos[0]|escape:javascript}',{$sku[i].promotion_photos_time});" />
						{/if}

						<!-- get sku apply photo -->
						{if $sku[i].photo_count > 0 }
							{foreach from=$sku[i].images_list item=p name=loop}
								<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View" class="sku_photo" />
							{/foreach}
						{/if}

						<!-- sku actual photo -->
						{if $sku[i].photos}
							{foreach from=$sku[i].photos item=p name=i}
								<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border="1" style="cursor:pointer;" title="View" class="sku_photo" onClick="show_sku_image_div('{$p|escape:javascript}');" />
							{/foreach}
						{else}
							{if $sku[i].url_to_get_photo && !$config.single_server_mode}
								<!-- need ajax load -->
								<span class="span_loading_actual_photo" id="span_loading_actual_photo-{$sku[i].id}" url_to_get_photo="{$sku[i].url_to_get_photo}">
									<img src="/ui/clock.gif" align="absmiddle" />
									Loading Photo...
								</span>
							{/if}
						{/if}
						</div>
					</div>
				{/if}
				{if $sku_items_child.$sku_id && $smarty.request.group_by_sku}
					<img src="/ui/expand.gif" onclick="toggle_sku_items('{$sku_id}', this);event.stopPropagation();" border="0" width="12" alt="Show SKU Items List">
				{/if}
			</td>
			<td nowrap align=left>
				{if !$sku[i].is_parent}
					<img src=/ui/tree_m.png align=absmiddle>
				{/if}
				{if $smarty.request.status eq '0' && $sku[i].is_parent}
					{* when user select -no multics code- show only the sku id *}
					{$sku[i].sku_id}
				{else}
					{$sku[i].sku_item_code}
				{/if}
				{if $sku[i].is_bom}
					<font color="blue">(BOM)</font>
				{/if}
			</td>

			<td nowrap>{$sku[i].artno|default:"&nbsp;"}</td>
			<td>{$sku[i].mcode|default:"&nbsp;"}</td>
			{if $config.link_code_name}
				{if $sku[i].is_parent}
					{if $sessioninfo.privilege.MST_SKU_UPDATE && $config.sku_application_require_multics}
						<td nowrap id="td[{$sku[i].id}]">
						<input size=15 class=small id="link_code[{$sku[i].id}]" {if !$sku[i].link_code}style="color:#00f"{/if} value="{if $sku[i].link_code}{$sku[i].link_code}{else}2{$sku[i].multics_brand}{$sku[i].multics_category|substr:-2}{/if}" onClick="event.stopPropagation();"> <img src="ui/ok16.png" align=absmiddle onclick="save_linkcode('{$sku[i].id}','{$sku[i].sku_id}');event.stopPropagation();" class="clickable"></td>
					{else}
						<td>{$sku[i].link_code}</td>
					{/if}
				{else}
					<!-- not default item -->
					<td>{$sku[i].link_code}</td>
				{/if}
			{/if}

			<td nowrap>{$sku[i].brand|default:'<font color=grey>UN-BRANDED</font>'}</td>
			<td>
				{$sku[i].description}
				{if $sku[i].is_parent}<br><span class=small style="color:#00f">{get_category_tree id=$sku[i].category_id tree_str=$sku[i].tree_str} > {$sku[i].category|default:'<font color=red>This item is Uncategorized</font>'}</span>{/if}
				{if $sku[i].reason.id}
					<div class=small>Inactive Reason: <span style="color:green">{$sku[i].reason.log|default:'-'} ({$sku[i].reason.u} / {$sku[i].reason.timestamp})</span></div>
				{/if}
			</td>
			<td>{$sku[i].location|default:"&nbsp;"}</td>
			<td align="center">{$sku[i].packing_uom|default:"-"}</td>
			{if $config.enable_gst}
				<td align="center">{$sku[i].input_tax_code} ({$sku[i].input_tax_rate}%)</td>
				<td align="center">{$sku[i].output_tax_code} ({$sku[i].output_tax_rate}%)</td>
				<td align="center">{$sku[i].real_inclusive_tax|upper}</td>
			{/if}
			{if $show_global_reorder_qty}
				<td align="right">{if $sku[i].sku_prd_qty_min}{$sku[i].sku_prd_qty_min}{else}-{/if}</td>
				<td align="right">{if $sku[i].sku_prd_qty_max}{$sku[i].sku_prd_qty_max}{else}-{/if}</td>
			{/if}
			{assign var=sid value=$sku[i].id}
			{if BRANCH_CODE eq 'HQ'}
				{if $branch.1.code}
					<!-- HQ -->
					{assign var=bid value=1}
					
					{* Stock Balance *}
					<td align="right">
						{if $sku[i].no_inventory eq 'yes'}N/A
						{else}
							{$si_info_list.$bid.$sid.stock_balance|qty_nf|ifempty:"-"}
							{if $si_info_list.$bid.$sid.changed or !$si_info_list.$bid.$sid.got_cost}<font color="red">*</font>{/if}
						{/if}
					</td>
					{if $config.masterfile_sku_enable_ctn}
						<td align="right" nowrap>
							{if $sku[i].no_inventory eq 'yes'}-
							{else}
								{if ($sku[i].packing_uom_id eq $sku[i].ctn_1_uom_id) or !$sku[i].ctn_1_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction/$sku[i].ctn_1_fraction|intval}
									/
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction%$sku[i].ctn_1_fraction|qty_nf}
									<br />
									<span class="small" style="color:red;">{$sku[i].ctn_1_code}</span>
								{/if}
							{/if}
						</td>
						<td align="right" nowrap>
							{if $sku[i].no_inventory eq 'yes'}-
							{else}
								{if ($sku[i].packing_uom_id eq $sku[i].ctn_2_uom_id) or !$sku[i].ctn_2_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction/$sku[i].ctn_2_fraction|intval}
									/
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction%$sku[i].ctn_2_fraction|qty_nf}
									<br />
									<span class="small" style="color:red;">{$sku[i].ctn_2_code}</span>
								{/if}
							{/if}
						</td>
					{/if}
					<td align="right" nowrap>
						{if $si_info_list.$bid.$sid.trade_discount_code}{$si_info_list.$bid.$sid.trade_discount_code} /{/if}
						{$si_info_list.$bid.$sid.selling_price|number_format:2}
						{if $sessioninfo.privilege.SHOW_COST}
							<br>
							{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}
							<br>
							GP <font color={if $si_info_list.$bid.$sid.gp_perc<0}'red' {else}'blue' {/if}>{$si_info_list.$bid.$sid.gp_perc|number_format:2|ifzero:"-":"%"}</font>
						{/if}
					</td>
					{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SHOW_COST}
						<td align="right" nowrap>{$sku[i].hq_cost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
					{if $show_reorder_by_branch}
						<td align="right">{if $sku[i].po_reorder_by_child}{$sku[i].po_reorder_qty_min}{elseif $sku[i].prd_qty_by_branch.min.1}{$sku[i].prd_qty_by_branch.min.1}{elseif $sku[i].sku_prd_qty_min}{$sku[i].sku_prd_qty_min}{else}-{/if}</td>
						<td align="right">{if $sku[i].po_reorder_by_child}{$sku[i].po_reorder_qty_max}{elseif $sku[i].prd_qty_by_branch.max.1}{$sku[i].prd_qty_by_branch.max.1}{elseif $sku[i].sku_prd_qty_max}{$sku[i].sku_prd_qty_max}{else}-{/if}</td>
					{/if}
				{/if}
				{if $smarty.request.branches_group eq ''}
					{foreach from=$branches_group.header item=bg}
						<td align="right">
							{if $sku[i].no_inventory eq 'yes'}N/A
							{else}
								{if $branches_group_data[$bg.id][$sid].stock_balance}
									<a href="javascript:show_item_by_group('{$bg.id}','{$sid}');" onClick="event.stopPropagation();">
									{$branches_group_data[$bg.id][$sid].stock_balance|qty_nf|ifempty:"-"}
									</a>
								{else}
									{$branches_group_data[$bg.id][$sid].stock_balance|qty_nf|ifempty:"-"}
								{/if}
							{/if}
						</td>
					{/foreach}
				{/if}
			{else}
			
			{/if}

			{foreach from=$branch key=bid item=b}
				{if $bid ne 1}
					<td align="right">
						{if $sku[i].no_inventory eq 'yes'}N/A
						{else}
							{$si_info_list.$bid.$sid.stock_balance|qty_nf|ifempty:"-"}
							{if $si_info_list.$bid.$sid.changed or !$si_info_list.$bid.$sid.got_cost}<font color="red">*</font>{/if}
						{/if}
					</td>
					{if $config.masterfile_sku_enable_ctn}
						<td align="right">
							{if $sku[i].no_inventory eq 'yes'}-
							{else}
								{if ($sku[i].packing_uom_id eq $sku[i].ctn_1_uom_id) or !$sku[i].ctn_1_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction/$sku[i].ctn_1_fraction|intval}
									/
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction%$sku[i].ctn_1_fraction|qty_nf}
									<br />
									<span class="small" style="color:red;">{$sku[i].ctn_1_code}</span>
								{/if}
							{/if}
						</td>
						<td align="right">
							{if $sku[i].no_inventory eq 'yes'}-
							{else}
								{if ($sku[i].packing_uom_id eq $sku[i].ctn_2_uom_id) or !$sku[i].ctn_2_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction/$sku[i].ctn_2_fraction|intval}
									/
									{$si_info_list.$bid.$sid.stock_balance*$sku[i].packing_uom_fraction%$sku[i].ctn_2_fraction|qty_nf}
									<br />
									<span class="small" style="color:red;">{$scb.ctn_2_code}</span>
								{/if}
							{/if}
						</td>
					{/if}
					{if $BRANCH_CODE ne 'HQ'}
						<td align="right">
							{if $si_info_list.$bid.$sid.trade_discount_code}{$si_info_list.$bid.$sid.trade_discount_code} /{/if}
							{$si_info_list.$bid.$sid.selling_price|number_format:2}
						</td>
						{if $sessioninfo.privilege.SHOW_COST}
							<td align="right">{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
							<td align="right">{$si_info_list.$bid.$sid.avg_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
						{/if}
						<td align="right">{$si_info_list.$bid.$sid.l90d_grn|qty_nf|ifzero:"-"}</td>
						<td align="right">{$si_info_list.$bid.$sid.l90d_pos|qty_nf|ifzero:"-"}</td>
						<td align="right">{$si_info_list.$bid.$sid.l30d_grn|qty_nf|ifzero:"-"}</td>
						<td align="right">{$si_info_list.$bid.$sid.l30d_pos|qty_nf|ifzero:"-"}</td>
					{else}
						<td align="right" nowrap>{if $si_info_list.$bid.$sid.trade_discount_code}{$si_info_list.$bid.$sid.trade_discount_code} /{/if}
							{$si_info_list.$bid.$sid.selling_price|number_format:2}
							{if $sessioninfo.privilege.SHOW_COST}
								<br>
								{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}
								<br>
								GP <font color={if $si_info_list.$bid.$sid.gp_perc<0}'red' {else}'blue' {/if}>{$si_info_list.$bid.$sid.gp_perc|number_format:2|ifzero:"-":"%"}</font>
							{/if}
						</td>
					{/if}
					{if $show_reorder_by_branch}
						<td align="right">{if $sku[i].po_reorder_by_child}{$sku[i].po_reorder_qty_min}{elseif $sku[i].prd_qty_by_branch.min.$bid}{$sku[i].prd_qty_by_branch.min.$bid}{elseif $sku[i].sku_prd_qty_min}{$sku[i].sku_prd_qty_min}{else}-{/if}</td>
						<td align="right">{if $sku[i].po_reorder_by_child}{$sku[i].po_reorder_qty_max}{elseif $sku[i].prd_qty_by_branch.max.$bid}{$sku[i].prd_qty_by_branch.max.$bid}{elseif $sku[i].sku_prd_qty_max}{$sku[i].sku_prd_qty_max}{else}-{/if}</td>
					{/if}
				{/if}
			{/foreach}
		</tr>

		<!-- child sku -->
		{if $sku_items_child.$sku_id}
			{foreach from=$sku_items_child.$sku_id item=r}
			<tr bgcolor="{cycle name=r1 values=',#eeeeee'}" class="tr_sku_item tr_sku_item_{$sku_id} {if !$r.active}inactive{else}thover clickable{/if}" onclick="show_context_menu(this, {$r.sku_id}, {$r.id}, '{$r.sku_item_code}')" po_reorder_by_child="{$sku[i].po_reorder_by_child}" {if $smarty.request.group_by_sku}style="display:none;"{/if} >
				<td>
					<a href="javascript:void(window.open('{$smarty.server.PHP_SELF}?a=view&id='+context_info.sku_id))"><img src=/ui/view.png border=0 alt="Detail"></a>
					{if $r.photo_count > 0 or $r.photos or $r.promotion_photos or ($r.url_to_get_photo && !$config.single_server_mode)}
						<div class="photo_icon" id="div_photo_icon-{$r.id}">
							<img src="/ui/icons/images.png" border=0 alt="Detail">
							<div class=photo_hide>
							{*
							{section name=loop start=1 loop=`$r.photo_count+1`}
							{capture assign=p}{$r.image_path}sku_photos/{$r.sku_apply_items_id}/{$smarty.section.loop.iteration}.jpg{/capture}
							<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View">
							{/section}
							*}
							
							<!-- sku promotion photo -->
							{if $r.promotion_photos}
								<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=100&h=100&cache={if $r.promotion_photos_time}{$r.promotion_photos_time}{else}1{/if}&img={$r.promotion_photos[0]|urlencode}" border=1 style="cursor:pointer" onClick="show_sku_image_div('{$r.promotion_photos[0]|escape:javascript}',{$r.promotion_photos_time});" title="View" class="sku_photo" />
							{/if}
							
							<!-- get sku apply photo -->
							{if $r.photo_count > 0}
								{foreach from=$r.images_list item=p name=loop}
									<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View" class="sku_photo" />
								{/foreach}
							{/if}

							{if $r.photos}
								{foreach from=$r.photos item=p name=i}
								<img width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onClick="show_sku_image_div('{$p|escape:javascript}');" title="View" class="sku_photo" />
								{/foreach}
							{else}
								{if $r.url_to_get_photo && !$config.single_server_mode}
									<!-- need ajax load -->
									<span class="span_loading_actual_photo" id="span_loading_actual_photo-{$r.id}" url_to_get_photo="{$r.url_to_get_photo}">
										<img src="/ui/clock.gif" align="absmiddle" />
										Loading Photo...
									</span>
								{/if}
							{/if}
							</div>
						</div>
					{/if}
				</td>
				<td nowrap align=left>
				{if !$r.is_parent}
					<img src=/ui/tree_m.png align=absmiddle>
				{/if}
				{if $smarty.request.status eq '0' && $r.is_parent}
					{* when user select -no multics code- show only the sku id *}
					{$r.sku_id}
				{else}
					{$r.sku_item_code}
				{/if}
				</td>

				<td nowrap>{$r.artno|default:"&nbsp;"}</td>
				<td>{$r.mcode|default:"&nbsp;"}</td>

				{if $config.link_code_name}
					{if $r.is_parent}
						{if $sessioninfo.privilege.MST_SKU_UPDATE && $config.sku_application_require_multics}
							<td nowrap id="td[{$r.id}]">
							<input size=15 class="small" id="link_code[{$r.id}]" {if !$r.link_code}style="color:#00f"{/if} value="{if $r.link_code}{$r.link_code}{else}2{$r.multics_brand}{$r.multics_category|substr:-2}{/if}" onClick="event.stopPropagation();"> <img src="ui/ok16.png" align="absmiddle" onclick="save_linkcode('{$r.id}','{$r.sku_id}');event.stopPropagation();" class="clickable"></td>
						{else}
							<td>{$r.link_code}</td>
						{/if}
					{else}
					<!-- not default item -->
					<td>{$r.link_code}</td>
					{/if}
				{/if}

				<td nowrap>{$r.brand|default:'<font color="grey">UN-BRANDED</font>'}</td>
				<td>
					{$r.description}
					{if $r.reason.log <> ''}
						<br><font color="red" class="small">{$r.reason.log} by {$r.reason.u} on {$r.reason.timestamp}</font>
					{/if}
				</td>
				<td>{$r.location|default:"&nbsp;"}</td>
				<td align="center">{$r.packing_uom|default:"-"}</td>
				{if $config.enable_gst}
					<td align="center">{$r.input_tax_code} ({$r.input_tax_rate}%)</td>
					<td align="center">{$r.output_tax_code} ({$r.output_tax_rate}%)</td>
					<td align="center">{$r.real_inclusive_tax|upper}</td>					
				{/if}
				{if $show_global_reorder_qty}
					<td align="right">{if !$r.po_reorder_by_child}-{elseif $r.sku_prd_qty_min}{$r.sku_prd_qty_min}{else}-{/if}</td>
					<td align="right">{if !$r.po_reorder_by_child}-{elseif $r.sku_prd_qty_max}{$r.sku_prd_qty_max}{else}-{/if}</td>
				{/if}
				{assign var=sid value=$r.id}
				{if BRANCH_CODE eq 'HQ'}
					{if $branch.1.code}
						<!-- HQ -->
						{assign var=bid value=1}
						<td align="right">
							{if $sku[i].no_inventory eq 'yes'}N/A
							{else}
								{$si_info_list.$bid.$sid.stock_balance|qty_nf|ifempty:"-"}
								{if $si_info_list.$bid.$sid.changed or !$si_info_list.$bid.$sid.got_cost}<font color="red">*</font>{/if}
							{/if}
						</td>
						{if $config.masterfile_sku_enable_ctn}
							<td align="right">
								{if $sku[i].no_inventory eq 'yes'}-
								{else}
									{if ($r.packing_uom_id eq $r.ctn_1_uom_id) or !$r.ctn_1_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction/$r.ctn_1_fraction|intval}
										/
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction%$r.ctn_1_fraction|qty_nf}
										<br />
										<span class="small" style="color:red;">{$r.ctn_1_code}</span>
									{/if}
								{/if}
							</td>
							<td align="right">
								{if $sku[i].no_inventory eq 'yes'}-
								{else}
									{if ($r.packing_uom_id eq $r.ctn_2_uom_id) or !$r.ctn_2_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction/$r.ctn_2_fraction|intval}
										/
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction%$r.ctn_2_fraction|qty_nf}
										<br />
										<span class="small" style="color:red;">{$r.ctn_2_code}</span>
									{/if}
								{/if}
							</td>
						{/if}
						<td align="right" nowrap>{if $si_info_list.$bid.$sid.trade_discount_code}{$si_info_list.$bid.$sid.discount_code} /{/if}
							{$si_info_list.$bid.$sid.selling_price|number_format:2}
							{if $sessioninfo.privilege.SHOW_COST}
								<br>
								{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}
								<br>
								GP <font color={if $si_info_list.$bid.$sid.gp_perc<0}'red' {else}'blue' {/if}>{$si_info_list.$bid.$sid.gp_perc|number_format:2|ifzero:"-":"%"}</font>
							{/if}
						</td>
						{if $config.sku_listing_show_hq_cost and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SHOW_COST}
							<td align="right" nowrap>{$r.hq_cost|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						{if $show_reorder_by_branch}
							<td align="right">{if $sku[i].po_reorder_by_child}{$r.po_reorder_qty_min}{else}-{/if}</td>
							<td align="right">{if $sku[i].po_reorder_by_child}{$r.po_reorder_qty_max}{else}-{/if}</td>
						{/if}
					{/if}
					{if $smarty.request.branches_group eq ''}
						{foreach from=$branches_group.header item=bg}
							<td align="right">
								{if $sku[i].no_inventory eq 'yes'}N/A
								{else}
									{if $branches_group_data[$bg.id][$sid].stock_balance}
										<a href="javascript:show_item_by_group('{$bg.id}','{$sid}');" onClick="event.stopPropagation();">
										{$branches_group_data[$bg.id][$sid].stock_balance|qty_nf|ifempty:"-"}
										</a>
									{else}
										{$branches_group_data[$bg.id][$sid].stock_balance|qty_nf|ifempty:"-"}
									{/if}
								{/if}
							</td>
						{/foreach}
					{/if}
				{else}
				
				{/if}

				{foreach from=$branch key=bid item=b}
					{if $bid ne 1}
						<td align="right">
							{if $sku[i].no_inventory eq 'yes'}N/A
							{else}
								{$si_info_list.$bid.$sid.stock_balance|qty_nf|ifempty:"-"}
								{if $si_info_list.$bid.$sid.changed or !$si_info_list.$bid.$sid.got_cost}<font color=red>*</font>{/if}
							{/if}
						</td>
						{if $config.masterfile_sku_enable_ctn}
							<td align="right">
								{if $sku[i].no_inventory eq 'yes'}-
								{else}
									{if ($r.packing_uom_id eq $r.ctn_1_uom_id) or !$r.ctn_1_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction/$r.ctn_1_fraction|intval}
										/
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction%$r.ctn_1_fraction|qty_nf}
										<br />
										<span class="small" style="color:red;">{$r.ctn_1_code}</span>
									{/if}
								{/if}
							</td>
							<td align="right">
								{if $sku[i].no_inventory eq 'yes'}-
								{else}
									{if ($r.packing_uom_id eq $r.ctn_2_uom_id) or !$r.ctn_2_fraction or !$si_info_list.$bid.$sid.stock_balance}-{else}
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction/$r.ctn_2_fraction|intval}
										/
										{$si_info_list.$bid.$sid.stock_balance*$r.packing_uom_fraction%$r.ctn_2_fraction|qty_nf}
										<br />
										<span class="small" style="color:red;">{$r.ctn_2_code}</span>
									{/if}
								{/if}
							</td>
						{/if}
						{if $BRANCH_CODE ne 'HQ'}
							<td align="right">
								{if $si_info_list.$bid.$sid.trade_discount_code}{$si_info_list.$bid.$sid.trade_discount_code} /{/if}
								{$si_info_list.$bid.$sid.selling_price|number_format:2}
							</td>
							{if $sessioninfo.privilege.SHOW_COST}
								<td align="right">{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
								<td align="right">{$si_info_list.$bid.$sid.avg_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
							{/if}
							<td align="right">{$si_info_list.$bid.$sid.l90d_grn|qty_nf|ifzero:"-"}</td>
							<td align="right">{$si_info_list.$bid.$sid.l90d_pos|qty_nf|ifzero:"-"}</td>
							<td align="right">{$si_info_list.$bid.$sid.l30d_grn|qty_nf|ifzero:"-"}</td>
							<td align="right">{$si_info_list.$bid.$sid.l30d_pos|qty_nf|ifzero:"-"}</td>
						{else}
							<td align="right" nowrap>{if $scb.discount_code}{$scb.discount_code} /{/if}
								{$si_info_list.$bid.$sid.selling_price|number_format:2}
								{if $sessioninfo.privilege.SHOW_COST}
									<br>{$si_info_list.$bid.$sid.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}
									<br>
									GP <font color={if $si_info_list.$bid.$sid.gp_perc<0}'red' {else}'blue' {/if}>{$si_info_list.$bid.$sid.gp_perc|number_format:2|ifzero:"-":"%"}</font>
								{/if}
							</td>
						{/if}
						{if $show_reorder_by_branch}
							<td align="right">{if $r.po_reorder_by_child}{$r.po_reorder_qty_min}{else}-{/if}</td>
							<td align="right">{if $r.po_reorder_by_child}{$r.po_reorder_qty_max}{else}-{/if}</td>
						{/if}
					{/if}
				{/foreach}
			</tr>
			{/foreach}
		{/if}
	{/section}
</table>

<script>
{if !$config.single_server_mode}
	load_sku_item_actual_photo();
{/if}
</script>
