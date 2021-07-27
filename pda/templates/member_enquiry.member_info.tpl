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
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.MEMBER_ENQUIRY}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="member_enquiry.php">{$LNG.MEMBER_ENQUIRY}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Member Information -->
<div class="container">
	<div class="row">
		<div class="col-md-3">
			<div class="card shadow ">
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
									<th>{$LNG.NAME}</th>
									<td>{$member_data.name}</td>
								</tr>
								<tr>
									<th>{$LNG.NRIC}</th>
									<td>{$member_data.nric}</td>
								</tr>
								<tr>
									<th>{$LNG.GENDER}</th>
									<td>{$member_data.gender}</td>
								</tr>
								<tr>
									<th>{$LNG.BIRTHDAY}</th>
									<td>{$member_data.dob}</td>
								</tr>
							</tbody>
						</table>
					</div>
					{*[<a href="{$smarty.server.PHP_SELF}?a=view_purchase_history&nric={$member_data.nric|urlencode}">View Purchase History</a>]*}
					<button onClick="document.location='{$smarty.server.PHP_SELF}?a=view_purchase_history&nric={$member_data.nric|urlencode}'" class="btn btn-info btn-block">{$LNG.PURCHASE_HISTORY}</button>
				</div>
			</div>
			<!-- QR Code -->
			{if $member_data.member_no_qrcode}
			<div class="card shadow mt-3 ">
				<div class="card-body">
					<div class="d-flex justify-content-center align-items-center">
						<div class="border" style="width: 150px; height: 150px;">
							<img src="../thumb.php?img={$member_data.member_no_qrcode|urlencode}&h=100&w=100"/>
						</div>
					</div>
					<h6 class="fs-08 text-center font-weight-bold mt-3">{$LNG.MEMBER_QR_CODE}</h6>
				</div>
			</div>
			{/if}
			<!-- /QR CODE -->
		</div>
		<div class="col-lg-8">
			<div class="card shadow ">
				<div class="card-body">
					<div class="mt-3">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<th>{$LNG.POINT_ACCUMULATED}</th>
									<td>
										{if !$member_data.parent_nric}
											{$member_data.points} <span id="span_points_{$member_data.nric}" style="color:red; font-size:16px; font-weight:bold;{if !$member_data.points_changed} display:none;{/if}">*</span>
										{else}
											<a href="member_enquiry.php?a=get_member_info&nric={$member_data.parent_nric}"  target="_blank">{$LNG.REFER_TO_PRINCIPLE_CARD}</a>
										{/if}
									</td>
								</tr>
								<tr>
									<th>{$LNG.POINT_UPDATE}</th>
									<td>{if $member_data.points_update > 0}{$member_data.points_update}{/if}</td>
								</tr>
								<tr>
									<th>{$LNG.CURRENT} {$config.membership_cardname} {$LNG.NUMBER}</th>
									<td>{$member_data.card_no}</td>
								</tr>
								<tr>
									<th>{$LNG.ISSUE_BRANCH}</th>
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
												<div class="alert alert-light">- {$LNG.NO_DATA} -</div>
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
	<div class="card shadow ">
		<div class="card-body">
			<div class="card-title">{$LNG.FAVOURITE_PRODUCT}</div>
			{if $product_history}
			<div class="alert alert-info"><i class="fas fa-star"></i> {$LNG.TOP_100_PRODUCTS_WILL_SHOW_MSG}</div>
			<div class="table-responsive">
				<table class="table mb-0 text-md-nowrap">
					<thead class="">
						<th>{$LNG.ART_NO}/MCODE/<br />{$LNG.ARMS_CODE}<br />{$config.link_code_name}<br />
							Item Description
						</th>
						<th>{$LNG.TOTAL_QTY}</th>
						<th>{$LNG.TOTAL_PRICE}</th>
						<th>{$LNG.LAST_PURCHASE}</th>
					</thead>
					<tbody>
						{foreach from=$product_history item=h}
							{assign var=total_qty value=$total_qty+$h.qty}
							{assign var=total_p value=$total_p+$h.price}
							<tr>
								<td>
									{if $h.artno neq ''}
										{$LNG.ART_NO}: {$h.artno|default:"&nbsp;"}<br>
									{elseif $h.mcode neq ''}
										{$LNG.MCODE}: {$h.mcode|default:"&nbsp;"}<br>
									{else}
										{$LNG.ARMS_CODE}: {$h.sku_item_code}<br>
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
							<td><b>{$LNG.TOTAL}</b></td>
							<td>{$total_qty|number_format:2}</td>
							<td>{$total_p|number_format:2}</td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
			{else}
				<div class="bg-light p-3 text-center rounded">
					{$LNG.NO_DATA}
				</div>
			{/if}
		</div>
	</div>
</div>
{include file="footer.tpl"}