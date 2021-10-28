{*
8/23/2012 3:02 PM Justin
- Changed the wording "V149" into "V168".

9/3/2012 4:34 PM Justin
- Enhanced to show more info on privilege requirement in order to edit category discount & member rewards.

1/18/2012 4:10 PM Andy
- Add Staff Discount (need privilege CATEGORY_STAFF_DISCOUNT_EDIT to edit).

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

19/8/2014 11:44 AM DingRen
- SKU Without Inventory and Is Fresh Market SKU, remove inherit option for level 1 category
- Add Input tax, Output tax and Inclusive Tax

9/9/2014 11:43 AM Fithri
- when edit / create new Department (category level 2), can select allowed user

9/25/2014 11:59 AM Justin
- Enhanced to show GST description from drop down list in full.

12/31/2014 9:17 AM Justin
- Enhanced to show inherit info.

2/26/2015 1:22 PM Justin
- Enhanced to have inherit option for SKU No Inventory & Is Fresh Market.

4/2/2015 3:03 PM Andy
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

9/28/2015 5:23 PM DingRen
- add new field "Can Auto load all po items for GRN"

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

9/7/2017 3:36 PM Justin
- Enhanced to have new feature "Use Matrix".

10/24/2018 2:58 PM Justin
- Enhanced the module to compatible with new SKU Type.

2/11/2020 10:37 AM William
- Enhanced to change default value "SKU Without Inventory" of create new category to "No" when category level is 1. 

06/25/2020 04:43 PM Sheila
- Updated button css

11/10/2020 9:32 AM Willliam
- Enhanced to add new checkbox "show_in_suite_pos".

01/05/2021 3:57 PM Rayleen
- Increase Category Code to 15 characters.

2/4/2021 4:05 PM Shane
- Added Promotion / POS Image upload.

2/9/2021 11:43 AM Shane
- Added Promotion / POS Image upload during create category.

4/5/2021 4:00 PM Shane
- Added "Hide category at POS" option.
*}

<form method="post" name="f_b" onSubmit="return false;">
	<div id=bmsg style="padding:10 0 10 0px;"></div>
	<input type="hidden" name="a" value="save_cat" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="level" value="{$form.level}" />
	<input type="hidden" name="root_id" value="{$form.root_id}" />
	<input type="hidden" name="tree_str" value="{$form.tree_str}" />
	<input type="hidden" name="got_pos_photo" value="{$form.got_pos_photo}" />
	<input type="hidden" name="has_tmp_photo" value="0" />
	<input type="hidden" name="tmp_photo" value="" />
	
	<table id="tb">
		<tr>
			<td><b class="form-label mt-2">Code</b> (Optional)</td>
			<td>
				<input class="form-control mt-2" onBlur="uc(this)" id="cat_code_id" name="code" size="17" maxlength="15" value="{$form.code}" />
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Description<span class="text-danger" title="Required Field"> *</span></b></td>
			<td>
				<input class="form-control" onBlur="uc(this)" name="description" size="50" value="{$form.description}" /> 
			</td>
		</tr>
		<tr>
			<td valign="top"><b class="form-label">Area</b> (Optional)</td>
			<td>
				<input class="form-control" onBlur="this.value=round2(this.value)" name="area" size="20" value="{$form.area}" />
			</td>
		</tr>
		{if $form.level<4}
			<tbody id="category_options">
				{* Category Discount *}
				<tr>
					<td valign="top">
						<b class="form-label">Discount</b>
						<a href="javascript:void(alert('Note: Branches and member type Settings only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All)\n\nRequire privilege CATEGORY_DISCOUNT_EDIT to use this.'));">
							<img src="/ui/icons/information.png" align="absmiddle" />
						</a>
						<br />
					</td>
					<td>
						{include file='masterfile_category.open.discount.tpl' is_edit=1}
					</td>
				</tr>
				
				{* Category Points *}
				<tr>
					<td valign="top">
						<b class="form-label">Reward Point</b>
						<a href="javascript:void(alert('Note: Branches and member type Settings only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All) \n\nRequire privilege MEMBER_POINT_REWARD_EDIT to use this.'));">
							<img src="/ui/icons/information.png" align="absmiddle" />
						</a>
						<br />
					</td>
					<td>
						{include file='masterfile_category.open.point.tpl' is_edit=1}
					</td>
				</tr>
				
				{* Category Staff Discount *}
				{if $config.membership_enable_staff_card and $config.membership_staff_type}
					<tr>
						<td valign="top">
							<b class="form-label">Staff Discount</b>
							<a href="javascript:void(alert('Note: Branches and Staff type Settings only available at counter alpha version.\n\nInherit: Staff Type (Branch) -> Staff Type (All) -> Staff (Branch) -> Staff (All)\n\nRequire privilege CATEGORY_STAFF_DISCOUNT_EDIT to use this.'));">
								<img src="/ui/icons/information.png" align="absmiddle" />
							</a>
							<br />
						</td>
						<td>
							{include file='masterfile_category.open.staff_discount.tpl' is_edit=1}
						</td>
					</tr>
				{/if}
			</tbody>
		{/if}
		{*if $form.level < 11}
			<!-- Return Policy Settings -->
			<tr>
				<td valign="top"><b class="form-label">Return Policy Settings</b></td>
				<td style="padding:2px;">
					<table class="report_table">
						{foreach from=$branch_list key=bid item=b}
							{if !$config.consignment_modules || ($config.consignment_modules && $bid eq 1)}
								<tr>
									<th>{$b.code}</th>
									<td>
										<select class="form-control" name="return_policy[{$bid}]">
											<option value="">No Return Policy</option>
											{if $form.level > 1}
												<option value="inherit" {if $form.return_policy.$bid eq 'inherit'}selected{/if}>Inherit (Follow Category)</option>
											{/if}
											{foreach from=$rp_list.$bid key=r item=i name=rp}
												{if $smarty.foreach.rp.first}
													<optgroup label="Return Policy">
												{/if}
												<option value="{$i.id}" {if $form.return_policy.$bid eq $i.id}selected{/if}>{$i.title}</option>
												{if $smarty.foreach.rp.first}
													</optgroup>
												{/if}
											{/foreach}
										</select>
									</td>
								</tr>
							{/if}
						{/foreach}
					</table>
				</td>
			</tr>
		{/if*}
		{if $form.level==2}
			<tbody id="grn_options">
				<tr>
					<td valign="top"><b class="form-label">Show PO Qty<br>in GRN Worksheet</b></td>
					<td>
						<select class="form-control" name="grn_po_qty">
							<option value="0">No</option>
							<option value="1" {if $form.grn_po_qty eq 1}selected {/if}>Yes</option>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top"><b class="form-label">GRN with Weight</b></td>
					<td>
						<select class="form-control" name="grn_get_weight">
							<option value="0">No</option>
							<option value="1" {if $form.grn_get_weight eq 1}selected {/if}>Yes</option>
						</select>
					</td>
				</tr>
			</tbody>
		{/if}
		{if $form.level<=2}
			<tr>
				<td colspan="2">
					<div id="photo_tb">
						<br>
						<b class="form-label">Photos required for SKU Application</b>
						<table>
							{foreach from=$sku_type key=k item=t}
								<tr>
									<td class="form-label">{$t.description}</td>
									<td>
										<select class="form-control" name="min_sku_photo[{$k}]">
											<option value="-1" {if $form.min_sku_photo.$k eq -1}selected {/if}>Inherit (Follow parent category)</option>
											<option value="0" {if $form.min_sku_photo.$k eq 0}selected {/if}>Not required</option>
											<option value="1" {if $form.min_sku_photo.$k eq 1}selected {/if}>At least 1</option>
											<option value="2" {if $form.min_sku_photo.$k eq 2}selected {/if}>At least 2</option>
											<option value="3" {if $form.min_sku_photo.$k eq 3}selected {/if}>At least 3</option>
											<option value="4" {if $form.min_sku_photo.$k eq 4}selected {/if}>At least 4</option>
											<option value="5" {if $form.min_sku_photo.$k eq 5}selected {/if}>At least 5</option>
										</select>
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</td>
			</tr>
		{/if}
		
		{if $config.enable_no_inventory_sku}
			<!-- No Inventory-->
			<tr>
				<td><b class="form-label">SKU Without Inventory</b></td>
				<td>
				    <select class="form-control" name="no_inventory">
						{if $form.level ne 1}
							<option value="inherit" {if !$form.no_inventory || $form.no_inventory eq "inherit"}selected {/if}>Inherit</option>
						{/if}
				        {foreach from=$inherit_options key=k item=val}
							{if $form.level eq 1 && $k eq 'inherit'}
							{else}
								<option value="{$k}" {if $form.no_inventory eq $k}selected {/if}>{$val}</option>
							{/if}
				        {/foreach}
				    </select>
				</td>
			</tr>
		{else}
			<input type="hidden" name="no_inventory" value="{if $form.level neq 1}inherit{else}no{/if}" />
		{/if}
	
		{if $config.enable_fresh_market_sku}
			<!-- Is Fresh Market SKU-->
			<tr>
				<td><b class="form-label">Is Fresh Market SKU</b></td>
				<td>
				    <select class="form-control" name="is_fresh_market">
						{if $form.level ne 1}
							<option value="inherit" {if !$form.is_fresh_market || $form.is_fresh_market eq "inherit"}selected {/if}>Inherit</option>
						{/if}
				        {foreach from=$inherit_options key=k item=val}
							{if $form.level eq 1 && $k eq 'inherit'}
							{else}
								<option value="{$k}" {if ($form.is_fresh_market eq $k) || (!$form.id and $form.level eq 1 and $k eq 'no')}selected {/if}>{$val}</option>
							{/if}
						{/foreach}
				    </select>
				</td>
			</tr>
		{else}
			<input type="hidden" name="is_fresh_market" value="inherit" />
		{/if}

		{if $is_gst}
		<tr>
			<td><b class="form-label">Input Tax</b></td>
			<td>
				<select class="form-control" name="input_tax">
					{if $form.level eq 1}
						<option value="-1">Inherit (Follow GST Setting: {$root_info.input_tax.code} [{$root_info.input_tax.rate}%])</option>
					{/if}
					{if $form.level gt 1 || $form.level > 10}
						<option value="-1">Inherit (Follow Parent Category: {$root_info.input_tax.code} [{$root_info.input_tax.rate}%])</option>
					{/if}
					{foreach from=$input_tax key=k item=val}
						<option value="{$val.id}" {if $form.input_tax eq $val.id}selected {/if}>{$val.code} - {$val.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>

		<tr>
			<td><b class="form-label">Output Tax</b></td>
			<td>
				<select class="form-control" name="output_tax">
					{if $form.level eq 1}
						<option value="-1">Inherit (Follow GST Setting: {$root_info.output_tax.code} [{$root_info.output_tax.rate}%])</option>
					{/if}
					{if $form.level gt 1 || $form.level > 10}
						<option value="-1">Inherit (Follow Parent Category: {$root_info.output_tax.code} [{$root_info.output_tax.rate}%])</option>
					{/if}
					{foreach from=$output_tax key=k item=val}
						<option value="{$val.id}" {if $form.output_tax eq $val.id}selected {/if}>{$val.code} - {$val.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>

		{if $form.inclusive_tax eq 'no' or $root_info.inclusive_tax eq 'no'}
			<tr>
				<td><b class="form-label">Selling Price Inclusive Tax</b></td>
				<td>
					<select class="form-control" name="inclusive_tax">
						<option value="inherit" {if $form.inclusive_tax eq 'inherit'}selected {/if}>
							Inherit (Follow {if $form.level eq 1}GST Settings{else}Parent Category{/if}: {$root_info.inclusive_tax|@ucwords})
						</option>
						{foreach from=$inherit_options key=k item=val}
							<option value="{$k}" {if $form.inclusive_tax eq $k}selected {/if}>{$val}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		{/if}
		
		{/if}
		{if $form.level gt 1}
		<tr>
			<td><b class="form-label">Can Auto load all po items for GRN</b></td>
			<td>
				<select class="form-control" name="grn_auto_load_po_items">
					<option value="0" {if $form.grn_auto_load_po_items eq "0"}selected {/if}>No</option>
					<option value="1" {if $form.grn_auto_load_po_items eq "1"}selected {/if}>Yes</option>
				</select>
			</td>
		</tr>
		{/if}
		
		{if $config.enable_one_color_matrix_ibt && $form.level >= 2}
			<tr>
				<td><b class="form-label">Use Matrix Settings</b></td>
				<td>
					<select class="form-control" name="use_matrix" onchange="matrix_changed();">
						{if $form.level >= 3}
							<option value="inherit" {if !$form.use_matrix || $form.use_matrix eq "inherit"}selected {/if}>Inherit</option>
						{/if}
						{foreach from=$inherit_options key=k item=val}
							<option value="{$k}" {if ($form.use_matrix eq $k) || (!$form.use_matrix and $form.level eq 2 and $k eq 'no')}selected {/if}>{$val}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					{include file='masterfile_category.open.matrix.tpl' is_edit=1 use_matrix=$form.use_matrix cat_matrix=$form.matrix}
				</td>
			</tr>
		{/if}
		
		{* if $config.enable_suite_device}
		<tr>
			<td><b class="form-label">Show in ARMS Suite POS Dashboard</b></td>
			<td><input class="form-control" name="show_in_suite_pos" value="1" type="checkbox" {if $form.show_in_suite_pos}checked{/if} /></td>
		</tr>
		{/if *}
		
		{if $user_selection}
		<tr>
			<td colspan="2"><br /><b>Allowed user</b></td>
		</tr>
		<tr>
			<td colspan="2">
				<div style="height:200px;overflow:auto;">
				{foreach from=$user_selection key=brcode item=userlist}
					<table width="100%">
						<tr><td width="100%" style="border:1px solid black;border-collapse:collapse;background-color:#b0c4de;">
							&nbsp;&nbsp;&nbsp;{$brcode}
							<img style="float:right;cursor:pointer;" src="ui/uncheckall.gif" onclick="checkall_user('cb_user-{$brcode}',false)" />
							<span style="float:right;">&nbsp;&nbsp;</span>
							<img style="float:right;cursor:pointer;" src="ui/checkall.gif" onclick="checkall_user('cb_user-{$brcode}',true)" />
						</td></tr>
						<tr>
							<td width="100%" style="">
								<table width="100%" border="0">
								
								{assign var=ct value=0}
								{foreach from=$userlist item=ul}
									{if $ul.is_arms_user}
										<td style="display:none;"><label><input name="allowed_user[{$ul.id}]" type="checkbox" checked />{$ul.u}</label></td>
									{else}
										{if $ct eq 0}<tr>{/if}
										<td><label><input name="allowed_user[{$ul.id}]" class="cb_user-{$brcode}" type="checkbox" {if $allowed_user[$ul.id]}checked{/if}/>{$ul.u}</label></td>
										{if $ct eq 4}</tr>{/if}
										
										{assign var=ct value=$ct+1}
										
										{if $ct eq 4}
										{assign var=ct value=0}
									{/if}
								{/if}
								
								{/foreach}
								
								</table>
							</td>
						</tr>
					</table>&nbsp;
				{/foreach}
				</div>
			</td>
		</tr>
		{/if}

		<tr>
			<td><b class="form-label">Hide category at POS</b></td>
			<td>
			    <select class="form-control" name="hide_at_pos">
					<option value="1" {if $form.hide_at_pos}selected {/if}>Yes</option>
					<option value="0" {if !$form.hide_at_pos}selected {/if}>No</option>
			    </select>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<h6>PROMOTION / POS IMAGE <img src="/ui/add.png" align=absmiddle onclick="add_image({$form.id})"></h6>
				<div id=cat_photo>
				{if $form.cat_photo}
				<div id="pos_img" class=imgrollover>
					<img width=110 height=100 align=absmiddle id="promotion_img" vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache={if $form.cat_photo_time}{$form.cat_photo_time}{else}1{/if}&img={$form.cat_photo|urlencode}" border=0 style="cursor:pointer" onClick="show_sku_image_div('{$form.cat_photo|escape:javascript}', {$form.cat_photo_time}, 10001);" title="View"><br>
					<img src="/ui/del.png" align=absmiddle onclick="if (confirm('Are you sure?'))del_image(this.parentNode,'{$form.cat_photo|urlencode}', {$form.id})"> Delete
				</div>
				{/if}
				</div>
			</td>
		</tr>
	
		<tr id="tr_btn_row">
			<td align="center" colspan="2">
				<br>
				<input class="btn btn-success" type="button" value="Save" onClick="save_category();" id="btn_save_cat" />  
				<input class="btn btn-danger" type="button" value="Close" onClick="default_curtain_clicked();" />
			</td>
		</tr>
	</table>
</form>

<!-- popup div -->
<div id=upload_popup   style="display:none;">
<form onsubmit="return upload_check()" name=upl target=_ifs enctype="multipart/form-data" method=post>
<h4>Select an image</h4>
&nbsp;&nbsp;&nbsp;<input type=hidden name=a value="add_photo">
<input type=hidden name=id value="0"> 
<input name=fnew type=file><br>
<ul>
	<li>Photo must be a valid JPEG image or JPG file.</li>
</ul>
<input type=submit value="Upload" class="btn btn-sm btn-primary">
 <input type=button value="Cancel" class="btn btn-sm btn-danger" onclick="curtain_clicked2()">
</form>
<iframe name=_ifs width=1 height=1 style="visibility:hidden"></iframe>
</div>
