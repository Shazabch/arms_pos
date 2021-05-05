{*
2/28/2011 11:18:35 AM Andy
- Add "from qty".
- Add Discount Preference: "All discount target"
- Add receipt description.
- Add show/hide overlap promotion.

4/29/2011 5:42:45 PM Andy
- Remove "all discount target" from discount preferences.
- Add "Special FOC".
- Add Control Type for mix and match promotion.
- Add create promotion by wizard.

7/25/2011 10:31:33 AM Andy
- Extend description for receipt limit.

5/7/2012 11:49:54 AM Andy
- Add promotion can set allowed member type.
- Add can set different category reward point.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

8/23/2012 2:39 PM Justin
- Enhanced for drop down menu of Member Reward Point to base on privilege PROMOTION_MEMBER_POINT_REWARD.
- Changed the wording "V149" into "V168".

8/30/2012 5:45 PM Justin
- Changed the privilege name from "PROMOTION_MEMBER_POINT_REWARD" into "MEMBER_POINT_REWARD_EDIT".

12/26/2012 6:14 PM Justin
- Bug fixed on member control type hide/show wrongly.

11/5/2013 12:02 PM Andy
- Change div position from relative to normal.
- Remove the information popup about "member type control only available at BETA v168).

1/7/2013 2:33 PM Andy
- Add can tick "Prompt when available" for each group. (need config).
- Remove popup icon from member point reward.

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

4/19/2017 8:26 AM Khausalya
- Enhanced changes from RM to use onfig.setting.
*}

<div class="stdframe div_promo_group" style="background:#fff;margin-bottom:20px;" id="div_promo_group-{$group_id}">
 	<div style="float:right;">
	    {if $allow_edit}
		<img src="/ui/del.png" title="Delete whole group" class="clickable" onClick="delete_promo_group('{$group_id}');" id="img_delete_promo_group-{$group_id}" />
		{/if}
	</div>
	
	<div>
	    {if !$allow_edit and $form.label eq 'approved'}
			<span style="background:#091;"><input type="checkbox" name="group_selection[]" value="{$group_id}" align="absmiddle" style="margin:2px 2px 2px -2px;" onChange="group_checkbox_changed('{$group_id}');" id="inp_group_selection-{$group_id}" /></span>
		{/if}
	</div>
	
	{if ($allow_edit or ($smarty.request.a eq 'view' and $form.approved==0)) and $form.id < 1000000000 and $promo_group.header.extra_info.reject_reason}
	<img src="ui/info.png" width="15" height="15" />&nbsp;&nbsp;<span style="color:red;"><b>Reject reason : {$promo_group.header.extra_info.reject_reason}</b></span><br /><br />
	{/if}
	
	{if $allow_edit}
		<button onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.open('{$group_id}');">
			<img src="/ui/report_edit.png" align="absmiddle" />
			Create promotion data by using wizard.
		</button>
	{/if}
	
	{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen and $config.promotion_approval_allow_reject_by_items}
	<input type="checkbox" name="rejected_group_id[{$group_id}]" value="1" class="rejected_item rejected_item_cb" onchange="reject_cb_clicked(this);" /><b>Reject</b>
	<span style="display:none;">
	&nbsp;&nbsp;Reason : <input type="text" name="rejected_group_reason[{$group_id}]" size="30" class="rejected_item" placeholder="Reason" />
	</span>
	<br /><br />
	{/if}
	
	<table>
		<!-- Receipt Limit -->
	    <tr>
	        <td>
				<b>Limit qty discount per receipt /
					<br />
					If discount target is '<span style="color:red;">Receipt</span>', it will limit how many time receipt can be discount /
					<br>
					If discount qty is '<span style="color:red;">Group Total</span>', it will limit how many time the group can be discount
				</b>
			
			</td>
	        <td>
				<input type="text" name="receipt_limit[{$group_id}]" value="{$promo_group.header.receipt_limit}" size="5" onChange="miz(this);check_group_limit('{$group_id}');" />
				<span class="small">(zero means un-limited)</span>
			</td>
	    </tr>
	    
	    <!-- Discount Preference -->
	    <tr>
	        <td><b>Discount Preference</b></td>
	        <td>
	            <input type="radio" name="disc_prefer_type[{$group_id}]" value="0" {if $promo_group.header.disc_prefer_type eq 0}checked {/if} />
	            Least discount first
	            &nbsp;&nbsp;&nbsp;&nbsp;
	            <input type="radio" name="disc_prefer_type[{$group_id}]" value="1" {if $promo_group.header.disc_prefer_type eq 1 or !isset($promo_group.header.disc_prefer_type)}checked {/if} />
	            Most discount first
	            
	            &nbsp;&nbsp;&nbsp;&nbsp;
	            <input type="radio" name="disc_prefer_type[{$group_id}]" value="2" {if $promo_group.header.disc_prefer_type eq 2}checked {/if} />
	            Manual Select
	            <br />
	            <span style="color:blue;">
	            	(This will not affect those discount qty with "All Items", "Group Total".)
	            </span>
	        </td>
	    </tr>
	    
	    <!-- Discount follow item sequence -->
	    <tr>
	        <td><b>Discount Follow Item Sequence</b></td>
	        <td>
	            <input type="checkbox" name="follow_sequence[{$group_id}]" value="1" {if $promo_group.header.follow_sequence}checked {/if} />
	        </td>
	    </tr>
	    
	    {* Prompt Available *}
	    {if $config.mix_and_match_show_prompt_available}
	    <tr>
	    	<td><b>Prompt When Available</b>
	    		<a href="javascript:void(alert('This feature only available after counter BETA v218.\n\nExample: Counter will prompt for cashier to know the available mix and match promotion when scan related item.'));">
					<img src="/ui/icons/information.png" align="absmiddle" />
				</a>
	    	</td>
	    	<td>
	    		<input type="checkbox" name="prompt_available[{$group_id}]" value="1" {if $promo_group.header.prompt_available}checked {/if} />
	    	</td>
	    </tr>
	    {/if}
	    
	    {assign var=got_member value=0}
	    {if $promo_group.header.for_member}
	    	{assign var=got_member value=1}
	    {else}
			{foreach from=$config.membership_type key=mtype item=mtype_desc name=fmt}
				{if is_numeric($mtype)}
					{assign var=mt value=$mtype_desc}
				{else}
					{assign var=mt value=$mtype}
				{/if}
	    		{if $promo_group.header.for_member_type.$mt}
	    			{assign var=got_member value=1}		
	    		{/if}
	    	{/foreach}
	    {/if}
	    <tbody id="tbody_member_settings-{$group_id}" style="{if (!$got_member && !$is_first) || $config.promotion_hide_member_options}display:none;{/if}">
		    <!-- Override Member Reward Point by Group -->
		    <tr>
		    	<td valign="top"><b>Override Member Reward Point by Group</b>
					
				</td>
		    	<td>
					{if $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
						<select name="item_category_point_inherit_data[{$group_id}][inherit]" onChange="item_category_point_inherit_changed('{$group_id}');">
							{foreach from=$item_category_point_inherit_options key=k item=w}
								<option value="{$k}" {if $promo_group.header.item_category_point_inherit_data.inherit eq $k}selected {/if}>{$w}</option>
							{/foreach}
						</select>
					{else}
						<b>
							{foreach from=$item_category_point_inherit_options key=k item=w}
								{if $promo_group.header.item_category_point_inherit_data.inherit eq $k}{$w}{/if}
							{/foreach}
						</b>
						<input type="hidden" name="item_category_point_inherit_data[{$group_id}][inherit]" value="{$promo_group.header.item_category_point_inherit_data.inherit}">
					{/if}
					
					<div id="div_item_cat_point-{$group_id}" style="border:0px solid black;padding:5px;{if $promo_group.header.item_category_point_inherit_data.inherit ne 'set'}display:none;{/if}">
						Please enter how many {$config.arms_currency.symbol} for each point.
						
						<table class="report_table">
							<tr class="header">
								<td>&nbsp;</td>
								<td>({$config.arms_currency.symbol} <b>X</b> for 1 Point)</td>
							</tr>
							<tr>
								<td><b>Member</b></td>
								<td>
									<input type="text" name="item_category_point_inherit_data[{$group_id}][global]" value="{$promo_group.header.item_category_point_inherit_data.global}" size="3" onChange="category_point_value_changed(this);" {if !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}readonly{/if} />
								</td>
							</tr>
							{foreach from=$config.membership_type key=mtype item=mtype_desc name=fmt}
								{if is_numeric($mtype)}
									{assign var=mt value=$mtype_desc}
								{else}
									{assign var=mt value=$mtype}
								{/if}
								{if $smarty.foreach.fmt.first}
									<tr class="header">
										<th colspan="2">
											Member Type (Leave Empty will follow member)
										</th>
									</tr>
								{/if}
								<tr>
									<td><b>{$mtype_desc}</b></td>
									<td>
										<input type="text" name="item_category_point_inherit_data[{$group_id}][{$mt}]" size="3" onChange="category_point_value_changed(this)" value="{$promo_group.header.item_category_point_inherit_data.$mt}" {if !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}readonly{/if} />
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
		    	</td>
		    </tr>
		    
		    <!-- Control Type -->
		    <tr id="tr_control_type-{$group_id}">
		    	<td><b>Control Type</b></td>
		    	<td>
		    		<select name="control_type[{$group_id}]">
					    {foreach from=$control_type key=k item=t}
					        <option value="{$k}" {if $k eq $promo_group.header.control_type}selected {/if}>{$t}</option>
					    {/foreach}
					</select>
					<span class="small">(Only for member)</span>
		    	</td>
		    </tr>
	    </tbody>
	    
	    <!-- member / non member checkbox -->
	    <tr>
	        <td colspan="2">
	            <input type="checkbox" name="for_non_member[{$group_id}]" value="1" {if $promo_group.header.for_non_member eq 1 or $is_first}checked {/if} />
	            Non-member
	            
	            {if !$config.promotion_hide_member_options}
		            <input type="checkbox" name="for_member[{$group_id}]" value="1" {if $promo_group.header.for_member eq 1 or $is_first}checked {/if} onChange="MIX_MATCH_MAIN_MODULE.change_for_member('{$group_id}',this);" />
		            Member
		            &nbsp;&nbsp;
		            (
						{foreach from=$config.membership_type key=mtype item=mtype_desc name=fmt}
							{if is_numeric($mtype)}
								{assign var=mt value=$mtype_desc}
							{else}
								{assign var=mt value=$mtype}
							{/if}
							<input type="checkbox" name="for_member_type[{$group_id}][{$mt}]" value="{$mt}" onChange="MIX_MATCH_MAIN_MODULE.change_for_member('{$group_id}',this);" {if $promo_group.header.for_member eq 1 or  $promo_group.header.for_member_type.$mt || $is_first}checked {/if} class="inp_for_member_type-{$group_id}" /> {$mtype_desc} &nbsp;&nbsp;
						{/foreach}
					)
	            {/if}
	        </td>
	    </tr>
	</table>
	<a href="javascript:void(MIX_MATCH_MAIN_MODULE.toggle_group_overlap_promo('{$group_id}', 1));">
		<img src="/ui/expand.gif" /> Show all overlap promotion
	</a> 
	|
	<a href="javascript:void(MIX_MATCH_MAIN_MODULE.toggle_group_overlap_promo('{$group_id}', 0));">
		<img src="/ui/collapse.gif" /> Hide all overlap promotion
	</a>
	<table width=100% style="border:1px solid #999; padding:2px; background-color:#fe9" border="0" cellspacing="1" cellpadding="1" id="tbl_promo_group_items-{$group_id}">
		<thead bgcolor="#ffffff">
		    <tr>
			    <th colspan="2">#</th>
			    <th width="300">Discount Target</th>
				<th>Condition</th>
				<th width="100">Discount by</th>
				<th width="80">Others</th>
				<th width="200">Receipt Description</th>
			</tr>
		</thead>
		<tbody id="tbody_promo_group_items-{$group_id}">
			{foreach from=$promo_group.item_list item=promo_item name=fg}
			    {include file='promotion.mix_n_match.open.promo_item_row.tpl'}
			{/foreach}
		</tbody>
	</table>
	<p>
		{if $allow_edit}
			<button onClick="search_new_promo_item('{$group_id}');" id="btn_search_new_discount_item-{$group_id}">
				<img src="/ui/findcat_expand.png" align="absmiddle" />
				Search Discount Item
			</button>
			<button onClick="add_new_receipt_discount_item('{$group_id}');" id="btn_add_receipt_discount-{$group_id}">
			    <img src="/ui/inote16.png" align="absmiddle" />
				Add Receipt Discount
			</button>
			<button onClick="add_new_special_foc_item('{$group_id}');" id="btn_add_special_foc-{$group_id}">
			    <img src="/ui/note16.png" align="absmiddle" />
				Add Special FOC
			</button>
		{/if}
	        <span id="span_group_item_loading-{$group_id}" style="display:none;background: yellow;padding:2px;">
				<img src="/ui/clock.gif" align="absmiddle" /> Loading...
			</span>
</div>
