{*
1/29/2021 12:45 PM William
- Enhanced to show member qr code image.
*}

{include file=header.tpl}
{literal}
<style>
.td_text {
	max-width: 155px;
	display: block;
	overflow-inline: auto;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
{/literal}
<h1>
Member Enquiry
&nbsp;
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="member_enquiry.php">Member Enquiry</a></span>
<div style="margin-bottom: 10px"></div>

<div>
	<table>
		<tr>
			<td rowspan="4" valign="top">
				<div style="border: 1px solid #ccc;">
					<img {if $member_data.profile_image_url}src="../thumb.php?img={$member_data.profile_image_url|urlencode}&h=100&w=100"{/if} 
					height="100" width="100" />
				</div>
			</td>
			<td valign="top"><b>Point Accumulated</b></td>
			<td valign="top">
				{if !$member_data.parent_nric}
					{$member_data.points} <span id="span_points_{$member_data.nric}" style="color:red; font-size:16px; font-weight:bold;{if !$member_data.points_changed} display:none;{/if}">*</span>
				{else}
					<a href="member_enquiry.php?a=get_member_info&nric={$member_data.parent_nric}"  target="_blank">Refer to Principal Card</a>
				{/if}
			</td>
		</tr>
		<tr>
			<td valign="top"><b>Point Update</b></td>
			<td valign="top" class="td_text">{if $member_data.points_update > 0}{$member_data.points_update}{/if}</td>
		</tr>
		<tr>
			<td valign="top"><b>Current {$config.membership_cardname} Number</b></td>
			<td valign="top" class="td_text">{$member_data.card_no}</td>
		</tr>
		<tr>
			<td valign="top"><b>Issue Branch</b></td>
			<td valign="top" class="td_text">{$member_data.branch_code}</td>
		</tr>
		<tr>
			<td valign="top"><b>Name</b></td>
			<td valign="top" class="td_text">{$member_data.name}</td>
			{if $member_data.member_no_qrcode}
			<td valign="top">
				<b>Member Qrcode</b>
				<div style="position: absolute;"><img src="../thumb.php?img={$member_data.member_no_qrcode|urlencode}&h=100&w=100"/></div>
			</td>
			{/if}
		</tr>
		<tr>
			<td valign="top"><b>NRIC</b></td>
			<td valign="top" class="td_text">{$member_data.nric}</td>
		</tr>
		<tr>
			<td valign="top"><b>Gender</b></td>
			<td valign="top" class="td_text">{$member_data.gender}</td>
		</tr>
		<tr>
			<td valign="top"><b>Birthday</b></td>
			<td valign="top" class="td_text">{$member_data.dob}</td>
		</tr>
	</table>
	
	{if $config.membership_pmr}
	<span>
		<p><b>{$config.membership_pmr_name}</b></p>
		{if $member_data.pmr}
			<span style="max-height: 100px; width: 100%; display: block; overflow: auto;{if $member_data.pmr}border: 1px solid #ccc;{/if}">
				{$member_data.pmr|nl2br}
			</span>
		{else}
			<span align=center>- No Data -</span>
		{/if}
	</span>
	{/if}
	
	<p><b>Favourite Product</b></p>
	{if $product_history}
	<span>* Only the top 100 products will be show</span>
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
		<tr bgcolor="#ffee99">
			<th>Artno/MCode/ARMS Code/{$config.link_code_name}</th>
			<th>Item Description</th>
			<th>Total Qty</th>
			<th>Total Price</th>
			<th>Last Purchase</th>
		</tr>
		{foreach from=$product_history item=h}
			{assign var=total_qty value=$total_qty+$h.qty}
			{assign var=total_p value=$total_p+$h.price}
			<tr>
				<td>
				{if $h.artno neq '' || $h.mcode neq ''}
					Artno/Mcode: {$h.artno|default:$h.mcode|default:"&nbsp;"}</br>
				{else}
					ARMS Code: {$h.sku_item_code}</br>
				{/if}
				{if $h.link_code}{$config.link_code_name}: {$h.link_code}{/if}
				</td>
				<td>{$h.receipt_description}</td>
				<td align=right>{$h.qty|number_format:2}</td>
				<td align=right>{$h.price|number_format:2}</td>
				<td align=center>{$h.dt|default:"&nbsp;"}</td>
			</tr>
		{/foreach}
		<tr>
			<td colspan=2 align=right><b>Total</b></td>
			<td align=right>{$total_qty|number_format:2}</td>
			<td align=right>{$total_p|number_format:2}</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	{else}
		<span align=center>- No Data -</span>
	{/if}
</div>
{include file="footer.tpl"}