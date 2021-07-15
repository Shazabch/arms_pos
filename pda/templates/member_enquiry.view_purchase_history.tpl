{*
5/4/2021 1:25 PM Andy
- Added "Purchase History".
*}

{include file=header.tpl}
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
			<a href="member_enquiry.php?a=get_member_info&nric={$member_data.nric|urlencode}">Member Enquiry ({$member_data.nric|escape:html})</a>
		</li>
		<li class="breadcrumb-item active">
			Purchase History
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

<div class="card animated fadeInLeft shadow">
	<div class="card-body">
		<div class="alert alert-info rounded"><i class="fas fa-sticky-note"></i> Only the latest 100 transactions will be show</div>
		<div class="table-responsive">
			<table class="table mb-0 text-md-nowrap">
				<thead>
					<th>Date</th>
					<th>Invoice No</th>
					<th>Amount</th>
				</thead>
				<tbody>
					{foreach from=$member_data.pos_list item=p}
						<tr>
							<td>{$p.date}</td>
							<td>
								<a href="/counter_collection.php?a=view_tran_details&receipt_ref_no={$p.receipt_ref_no}" target="_blank">
									{$p.receipt_ref_no}
								</a>
							</td>
							<td>{$p.final_amt|number_format:2}</td>
						</tr>
					{foreachelse}
						<tr>
							<td colspan="3">
								<div class="bg-light p-3 text-center rounded">
									No Data
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
{include file="footer.tpl"}