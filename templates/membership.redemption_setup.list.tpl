{*
3/2/2010 3:26:37 PM Andy
- Add receipt date period checking
- Add option to allow user can one time toggle multiple redemption items to delete

3/12/2010 11:40:10 AM Andy
- Delete button change and multiple delete function.
- Toggle active button change and multiple active/deactive function.
- Fix item cannot approve due to have amount but no end date bugs.
- Add use current date feature for receipt control.

8/11/2010 12:28:32 PM Justin
- Added new column called Valid Start and End date.
- Placed error message on top of Redemption information instead of bottom of Redemption item list.

8/27/2010 3:13:52 PM Justin
- Added cash column.

9/22/2010 12:23:32 PM Justin
- Added Canceled by and Canceled Date columns.

9/27/2010 3:25:07 PM Justin
- Modified the label "User Current Date" become "Use Current Date".
- Modified status update function.
- Added a new column called Status.
- Changed the column alignments between Status and Checkbox.
- Added a notes for user understanding that highlighted row(s) indicate nearly expire items.

10/28/2010 4:29:25 PM Justin
- Hidden the expire days notification, Canceled By and Canceled Date columns whenever no config set.
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

11/12/2010 3:24:16 PM Justin
- Modified the Activate, Deactivate and Delete Selected Items hyperlinks become button and place beside Save button.

1/20/2011 2:09:25 PM Justin
- Added created and approved by columns.

4/27/2011 3:27:30 PM  Justin
- Set the colspan from 10 to 20 when no data found and set it align to center.

1/14/2013 1:56 PM Justin
- Enhanced to show header of Voucher Value if found config "membership_use_voucher".

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

06/29/2020 05:06 PM Sheila
- Updated button css.
*}
<p id="p_error_msg" style="color:red;"></p>
{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{else}
	{if $config.membership_redemption_expire_days}
	<div style="padding:2px;">
		Note: <label class="highlight_row_title"><img width="17" src="/ui/pixel.gif" /></label> Highlighted item(s) indicate nearly expire.
	</div>
	{/if}
   <div class="table-responsive">
	<table width=100% class="input_no_border small body table mb-0 text-md-nowrap  table-hover"
	id="tbl_items">
		<thead class="bg-gray-100">
			<tr >
				  <th rowspan=2>#</th>
				<th rowspan=2>Status</th>
				  <th rowspan=2><input type="checkbox" onChange="toggle_all_status(this);"></th>
				<th rowspan=2>Created By</th>
				<th rowspan=2>Available to</th>
				<th rowspan=2>Arms Code</th>
				<th rowspan=2>Description</th>
				<th rowspan=2>Cost</th>
				<th rowspan=2>Selling<br>Price</th>
				<th rowspan=2>Stock<br>Balance</th>
				<th rowspan=2>Point</th>
				<th rowspan=2>Cash</th>
				<th colspan=2>Valid</th>
				<th colspan="4">Receipt</th>
				{if $config.membership_redemption_use_enhanced}
					<th rowspan="2">Cancelled By</th>
					<th rowspan="2">Cancelled Date</th>
				{/if}
				<th rowspan=2>Created By</th>
				<th rowspan=2>Approved By</th>
				{if $config.membership_use_voucher}
					<th rowspan=2>Voucher Value</th>
				{/if}
			</tr>
			<tr >
				<th>Date Start</th>
				<th>Date End</th>
				<th>Amount</th>
				<th>Date Start</th>
				<th>Date End</th>
				<th>Use Current Date</th>
			</tr>
		</thead>
		<tbody class="fs-08" id="tbody_item_list">
			{foreach from=$redemption_items item=item name=fitem}
				{include file='membership.redemption_setup.list.row.tpl'}
				<script>items_row_count = int('{$smarty.foreach.fitem.iteration}');</script>
			{foreachelse}
				<tr id="tr_no_item">
					<td colspan="21" align="center"><p style="padding:10px;" class="large">-- No Item --</p></td>
				</tr>
			{/foreach}
			<tr id="tbl_footer">
			</tr>
		</tbody>
		</table>
   </div>
	
	<div id="div_save_area" {if !$redemption_items}style="display:none;"{/if}>
	    <p style="text-align:center;padding:10px;background:#fff;">
	    	<input class="btn btn-primary" name="act_btn" type="button" value="Activate Selected Item(s)" onclick="javascript:void(status_set_selected_item('1'));" >
	        <input class="btn btn-warning" name="dect_btn" type="button" value="Deactivate Selected Item(s)" onclick="javascript:void(status_set_selected_item('0'));" >
		    <input class="btn btn-success" name="save_btn" type="button" value="Save" onclick="save_redemption_items();" >        
			<input class="btn btn-error" name="del_btn" type="button" value="Delete Selected Item(s)" onclick="javascript:void(delete_selected_item());" >
	    </p>
	</div>
{/if}
