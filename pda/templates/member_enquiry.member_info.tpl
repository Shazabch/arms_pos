{*
1/29/2021 12:45 PM William
- Enhanced to show member qr code image.

5/4/2021 1:25 PM Andy
- Modified the layout of "Favourite Product" table.
- Added "Purchase History".
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

.span_sku_desc{
	color: blue;
}
</style>
{/literal}
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Member Enquiry</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="member_enquiry.php">Member Enquiry</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Member Information -->
<div class="container">
	<div class="row">
		<div class="col-md-3">
			<div class="card shadow animated fadeInDown">
				<div class="card-body">
					<div class="d-flex justify-content-center align-items-center">
						<div class="shadow" style="width: 150px; height: 150px;">
							<img src="" style="width: 100%; height: auto; ">
						</div>
					</div>
					<div class="border-top mt-3">
						<table class="table table-sm table-borderless mt-3">
							<tbody>
								<tr>
									<th>Name</th>
									<td>{$member_data.name}</td>
								</tr>
								<tr>
									<th>NRIC</th>
									<td>{$member_data.nric}</td>
								</tr>
								<tr>
									<th>Gender</th>
									<td>{$member_data.gender}</td>
								</tr>
								<tr>
									<th>Birthday</th>
									<td>{$member_data.dob}</td>
								</tr>
							</tbody>
						</table>
					</div>
					{*[<a href="{$smarty.server.PHP_SELF}?a=view_purchase_history&nric={$member_data.nric|urlencode}">View Purchase History</a>]*}
					<button onClick="document.location='{$smarty.server.PHP_SELF}?a=view_purchase_history&nric={$member_data.nric|urlencode}'" class="btn btn-info btn-block">Purchase History</button>
				</div>
			</div>
			<!-- QR Code -->
			{if $member_data.member_no_qrcode}
			<div class="card shadow mt-3 animated fadeInDown">
				<div class="card-body">
					<div class="d-flex justify-content-center align-items-center">
						<div class="border" style="width: 150px; height: 150px;">
							<img src="../thumb.php?img={$member_data.member_no_qrcode|urlencode}&h=100&w=100"/>
						</div>
					</div>
					<h6 class="fs-08 text-center font-weight-bold mt-3">Member QR Code</h6>
				</div>
			</div>
			{/if}
			<!-- /QR CODE -->
		</div>
		<div class="col-lg-8">
			<div class="card shadow animated fadeInDown">
				<div class="card-body">
					<div class="mt-3">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<th>Point Accumulated</th>
									<td>
										{if !$member_data.parent_nric}
											{$member_data.points} <span id="span_points_{$member_data.nric}" style="color:red; font-size:16px; font-weight:bold;{if !$member_data.points_changed} display:none;{/if}">*</span>
										{else}
											<a href="member_enquiry.php?a=get_member_info&nric={$member_data.parent_nric}"  target="_blank">Refer to Principal Card</a>
										{/if}
									</td>
								</tr>
								<tr>
									<th>Point Update</th>
									<td>{if $member_data.points_update > 0}{$member_data.points_update}{/if}</td>
								</tr>
								<tr>
									<th>Current {$config.membership_cardname} Number</th>
									<td>{$member_data.card_no}</td>
								</tr>
								<tr>
									<th>Issue Branch</th>
									<td>{$member_data.branch_code}</td>
								</tr>
								{if $config.membership_pmr}
									<tr>
										<th>{$config.membership_pmr_name}</th>
										<td>
											{if $member_data.pmr}
												<span style="max-height: 100px; display: block; overflow: auto;{if $member_data.pmr}border: 1px solid #ccc;{/if}">
													{$member_data.pmr|nl2br}
												</span>
											{else}
												<div class="alert alert-light">- NO Data -</div>
											{/if}	
										</td>
									</tr>
								{/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Member Information -->
<div class="container">
	<div class="card shadow animated fadeInLeft">
		<div class="card-body">
			<div class="card-title">Favourite Product</div>
			{if $product_history}
			<div class="alert alert-info"><i class="fas fa-star"></i> Only the top 100 products will be show</div>
			<div class="table-responsive">
				<table class="table mb-0 text-md-nowrap">
					<thead class="">
						<th>Artno/MCode/<br />ARMS Code<br />{$config.link_code_name}<br />
							Item Description
						</th>
						<th>Total Qty</th>
						<th>Total Price</th>
						<th>Last Purchase</th>
					</thead>
					<tbody>
						{foreach from=$product_history item=h}
							{assign var=total_qty value=$total_qty+$h.qty}
							{assign var=total_p value=$total_p+$h.price}
							<tr>
								<td>
									{if $h.artno neq ''}
										ArtNo: {$h.artno|default:"&nbsp;"}<br>
									{elseif $h.mcode neq ''}
										MCode: {$h.mcode|default:"&nbsp;"}<br>
									{else}
										ARMS Code: {$h.sku_item_code}<br>
									{/if}
									{if $h.link_code}{$config.link_code_name}: {$h.link_code}<br>{/if}
									<span class="span_sku_desc small">{$h.receipt_description}</span>
								</td>
								{*<td>{$h.receipt_description}</td>*}
								<td align=right>{$h.qty|number_format:2}</td>
								<td align=right>{$h.price|number_format:2}</td>
								<td align=center>{$h.dt|default:"&nbsp;"}</td>
							</tr>
						{/foreach}
						<tr>
							<td><b>Total</b></td>
							<td>{$total_qty|number_format:2}</td>
							<td>{$total_p|number_format:2}</td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
			{else}
				<div class="bg-light p-3 text-center rounded">
					No Data
				</div>
			{/if}
		</div>
	</div>
</div>
{include file="footer.tpl"}