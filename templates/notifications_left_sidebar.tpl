{*
Revision History
================
10/14/2015 9:59 AM Andy
- Enhance to load CNote approval.

10/27/2016 9:44 AM Andy
- Fixed load approval listing when got config adjustment_branch_selection.

5/21/2019 4:30 PM William
- Enhance "GRN" word to use report_prefix.

7/11/2019 5:06 PM Andy
- Added Cycle Count Approval.

04/08/2020 4:07 PM Sheila
- Modified layout to compatible with new UI.

*}

<!-- membership notification -->
{if $membership}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09" ><i class="fas fa-users"></i> Membership Verification</div>
		<div class="mb-2 text-muted fs-07">
			<span>There are records to be verified.</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 150px;">
			{section name=i loop=$membership}
			<li class="fs-08"><a href="membership.php?t=verify&branch_id={$membership[i].branch_id}" target="_blank" class="text-reset">{$membership[i].branch_code}</a><span class="badge  badge-info ml-2">{$membership[i].count}</span></li>
			{/section}
		</ul>
		<div class="text-center mt-4">
			<a href="membership.php?t=verify" class="btn btn-info btn-block">Verify</a>
		</div>
	</div>
</div>
{/if}

<!-- membership blocked -->
{if $membership_blocked}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="fas fa-users"></i> Blocked Membership</div>
		<div class="mb-2 text-muted fs-07">
			<span>The following membership are blocked.</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 150px;">
			{section name=i loop=$membership_blocked}
			<li class="fs-08"><a href="membership.listing.php?branch_id={$membership_blocked[i].branch_id}" target="_blank" class="text-reset">{$membership_blocked[i].branch_code}</a><span class="badge badge-info ml-2">{$membership_blocked[i].count}</span></li>
			{/section}
		</ul>
		<div class="text-center mt-4">
			<a href="membership.php?t=verify" class="btn btn-info btn-block">Verify</a>
		</div>
	</div>
</div>
{/if}

<!-- Membership Summary -->
{if $sessioninfo.privilege.MEMBERSHIP_SUMM and $membership_summary}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="fas fa-users"></i> Membership Summary</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 200px;">
			<li class="fs-08" class="fs-08">
				<a href="membership.listing.php"  class="text-reset">Total Member</a>
				<span class="badge badge-info ml-2">{$membership_summary.total|number_format}</span>
			</li>
			<li class="fs-08">
				<a href="membership.listing.php?verified=1&blocked=0&terminated=0" class="text-reset">Total Verified Member</a>
				<span class="badge badge-info ml-2">{$membership_summary.verified|number_format}</span>
			</li>
			<li class="fs-08">
				<a href="membership.listing.php?verified=0&blocked=0&terminated=0" class="text-reset">Total Unverified Member</a>
				<span class="badge badge-info ml-2">{$membership_summary.unverified|number_format}</span>
			</li>
			<li class="fs-08">
				<a href="membership.listing.php?blocked=1&terminated=0"  class="text-reset">Total Blocked Member</a>
				<span class="badge badge-info ml-2">{$membership_summary.blocked|number_format}</span>
			</li>
			<li class="fs-08">
				<a href="membership.listing.php?terminated=1" class="text-reset">Total Terminated Member</a>
				<span class="badge badge-info ml-2">{$membership_summary.terminated|number_format}</span>
			</li>
		</ul>
	</div>
</div>
{/if}

<!-- redemption notification -->
{if $membership_redemption}
<h5>
{*<img src=/ui/notify_mm.png align=absmiddle border=0>*}
<i class="icofont-gift-box icofont"></i>
<a href="membership.redemption_history.php?t=1&do_verify=1">Redemption Verification</a></h5>
<div class=ntc>Following redemption item from different branches require verify.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<br>
{section name=i loop=$membership_redemption}
{assign var=md_redir value="membership.redemption_history.php?do_verify=1"}
<li> <a href="login.php?server={$membership_redemption[i].branch_code|urlencode}&redir={$md_redir|escape:'html'}">{$membership_redemption[i].branch_code} ({$membership_redemption[i].count})</a>
{/section}
</ul>
{/if}

{if $membership_item_cfrm}
<h5>
{*<img src=/ui/store.png align=absmiddle border=0>*}
<i class="icofont-gift-box icofont"></i>
<a href="membership.redemption_item_approval.php">Redemption Item Approval</a></h5>
<div class=ntc>Following redemption item require to approval.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<br>
{section name=i loop=$membership_item_cfrm}
<li> <a href="membership.redemption_item_approval.php?branch_id={$membership_item_cfrm[i].branch_id}">{$membership_item_cfrm[i].branch_code} ({$membership_item_cfrm[i].count})</a>
{/section}
</ul>
{/if}

<!-- SKU approval notification -->
{if $sku_approvals}
<h5>
{*<img src=/ui/notify_sku_new.png align=absmiddle border=0>*}
SKU Applications</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="masterfile_sku_approval.php">You have {$sku_approvals} SKU Application waiting for approval.</a></p>
</div>
{/if}

<!-- DO approval notification -->
{*if $do_approvals}
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> Delivery Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="do_approval.php">You have {$do_approvals} DO waiting for approval.</a></p>
</div>
{/if*}

<!-- ADJUSTMENT approval notification -->
{*if $adj_approvals}
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> Adjustment</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="adjustment_approval.php">You have {$adj_approvals} Adjustment waiting for approval.</a></p>
</div>
{/if*}

<!-- MKT3 approval notification -->
{if $mkt3_approvals}
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT3</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt3_approval.php">You have {$mkt3_approvals} MKT3 waiting for approval.</a></p>
</div>
{/if}

<!-- MKT5 approval notification -->
{if $mkt5_approvals}
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT5</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt5_approval.php">You have {$mkt5_approvals} MKT5 waiting for approval.</a></p>
</div>
{/if}

<!-- MKT1 approval notification -->
{if $mkt1_approvals}
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT1</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt1_approval.php">You have {$mkt1_approvals} MKT1 waiting for approval.</a></p>
</div>
{/if}

<!-- PO approval notification -->
{if $po_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Purchase Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have PO waiting for approval.<br>
<ul>
{section name=i loop=$po_approvals}
<li>
{*
<a href="login.php?server={$po_approvals[i].code|urlencode}&redir=purchase_order_approval.php">
*}
<a href="login.php?server={$po_approvals[i].code|urlencode}&redir=po_approval.php">
{$po_approvals[i].code} ({$po_approvals[i].count})
</a>
<!--<li><a href="login.php?server={$po_approvals[i].code}&redir=po_approval.php">NEW {$po_approvals[i].code} ({$po_approvals[i].count})
</a>-->
{/section}
</ul>
</p>
</div>
{/if}

{if $promotion_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Promotion</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Promotion waiting for approval.<br>
<ul>
{section name=i loop=$promotion_approvals}
<li>
<a href="login.php?server={$promotion_approvals[i].code|urlencode}&redir=promotion_approval.php">
{$promotion_approvals[i].code} ({$promotion_approvals[i].count})
</a>
{/section}
</ul>
</p>
</div>
{/if}

<!-- ADJ approval notification -->
{if $adj_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Adjustment</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Adjustment waiting for approval.<br>
<ul>
{section name=i loop=$adj_approvals}
{if $config.adjustment_branch_selection}
	{if BRANCH_CODE eq 'HQ'}
		<li><a href="adjustment_approval.php?branch_id={$adj_approvals[i].branch_id}">{$adj_approvals[i].code} ({$adj_approvals[i].count})</a></li>
	{else}
		<li><a href="login.php?server={$adj_approvals[i].code|urlencode}&redir=adjustment_approval.php?branch_id={$adj_approvals[i].branch_id}">{$adj_approvals[i].code} ({$adj_approvals[i].count})</a></li>
	{/if}
{else}
	<li><a href="login.php?server={$adj_approvals[i].code|urlencode}&redir=adjustment_approval.php">{$adj_approvals[i].code} ({$adj_approvals[i].count})
	</a></li>
{/if}
{/section}
</ul>
</p>
</div>
{/if}

<!-- DO approval notification -->
{if $do_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Delivery Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Delivery Order waiting for approval.<br>
<ul>
{section name=i loop=$do_approvals}
{if $config.consignment_modules}
    <li><a href="do_approval.php?branch_id={$do_approvals[i].branch_id}">{$do_approvals[i].code} ({$do_approvals[i].count})</a></li>
{else}
	<li><a href="login.php?server={$do_approvals[i].code|urlencode}&redir=do_approval.php">{$do_approvals[i].code} ({$do_approvals[i].count})</a></li>
{/if}
{/section}
</ul>
</p>
</div>
{/if}

<!-- Sales Order approval notification -->
{if $so_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Sales Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Sales Order waiting for approval.<br>
<ul>
{section name=i loop=$so_approvals}
	<li><a href="login.php?server={$so_approvals[i].branch_code|urlencode}&redir=sales_order_approval.php">{$so_approvals[i].branch_code} ({$so_approvals[i].count})
	</a></li>
{/section}
</ul>
</p>
</div>
{/if}

<!-- DO Request notification -->
{if $do_request}
<h5>
{*<img src=/ui/notify_po_new.png align=absmiddle border=0>*}
<i class="icofont-tags icofont"></i> DO Request</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have DO Request waiting for process.<br>
<ul>
{foreach from=$do_request item=r}
	<li><a href="do_request.process.php?branch_id={$r.branch_id}">{$r.branch_code} ({$r.item_count})</a></li>
{/foreach}
</ul>
</p>
</div>
{/if}

<!-- CI approval notification -->
{if $ci_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Consignment Invoice</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Consignment Invoice waiting for approval.<br>
<ul>
{section name=i loop=$ci_approvals}
<li><a href="login.php?server={$ci_approvals[i].code|urlencode}&redir=consignment_invoice_approval.php">{$ci_approvals[i].code} ({$ci_approvals[i].count})
</a>
{/section}
</ul>
</p>
</div>
{/if}

<!-- GRN account notification -->
{if $grn_account_verify}
<h5>
{*<img src="/ui/notify_po_new.png" align=absmiddle border=0>*}
<i class="icofont-tags icofont"></i> GRN (Account Verification)</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have GRN waiting for verification.<br>
<ul>
{section name=i loop=$grn_account_verify}
<li><a href="login.php?server={$grn_account_verify[i].code|urlencode}&redir=goods_receiving_note_approval.account.php">{$grn_account_verify[i].code} ({$grn_account_verify[i].count})
</a>
{/section}
</ul>
</p>
</div>
{/if}

<!-- GRN approval notification -->
{if $grn_confirmations || $grn_approvals}
<div class="card">
	<div class="card-body text-center pricing overflow-auto" style="max-height: 300px;">
		<div class="card-category fs-09" ><i class="fas fa-tag"></i> GRN</div>
		{if $grn_confirmations}
			<h3 class="fs-08 text-info text-left mb-0 pb-0"><i class="far fa-check-circle"></i> Confirmation</h3>
			<div class="mb-2 text-muted text-left fs-06">
				<span>The following GRN requires your confirmation.</span>
			</div>

			{foreach from=$grn_count_list name=grn_count key=r item=count}
			<div class="fs-08 text-left mt-1">
				{if $r eq "doc_pending"}
					Document Pending
				{elseif $r eq "acc_verify"}
					Account Verification
				{else}
					SKU Manage
				{/if}
				<span class="badge badge-pill badge-success ml-1">
					{$count}
					{if !$smarty.foreach.grn_count.last}{/if}
				</span>
			</div>
			{/foreach}
			<ul class="list-unstyled leading-loose text-left overflow-auto">
				{foreach from=$grn_confirmations item=grn key=row}
				<li  class="fs-08">
					<a href="/goods_receiving_note.php?a=open&id={$grn.grn_id}&branch_id={$grn.branch_id}&action={$grn.action}" target="_blank" class="text-reset">
						<strong class="fs-08">
							{$grn.report_prefix}{$grn.grn_id|string_format:"%05d"} - 
							{if $grn.action eq 'verify'}
								SKU Manage
							{elseif $grn.action eq 'grr_edit'}
								Pending Document
							{else}
								Account Verification
							{/if}
						</strong><br>
						<span class="text-secondary" class="fs-06">Received Date : 
							<span class="text-danger"></span> {$grn.rcv_date}
						</span>
					</a>
				</li>
				{/foreach}
			</ul>
		{/if}
		{if $grn_approvals}
			<h3 class="fs-08 text-primary text-left mt-1 mb-0 pb-0"><i class="fas fa-tag"></i> Approval</h3>
			<div class=" mb-2 text-muted fs-06 text-left">
				<span>You have GRN waiting for approval</span>
			</div>
			<ul class="list-unstyled leading-loose text-left overflow-auto">
				{section name=i loop=$grn_approvals}
				<li class="fs-08">
					<a href="login.php?server={$grn_approvals[i].code|urlencode}&redir=goods_receiving_note_approval.php" target="_blank" class="text-reset">{$grn_approvals[i].code}</a>
					<span class="badge  badge-info ml-2">{$grn_approvals[i].count}</span>
				</li>
				{/section}
			</ul>
		{/if}
	</div>
</div>
{/if}

<!-- Credit Note approval notification -->
{if $cn_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Credit Note</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Credit Note waiting for approval.<br>
<ul>
{section name=i loop=$cn_approvals}
	<li><a href="login.php?server={$cn_approvals[i].branch_code|urlencode}&redir=consignment.credit_note.approval.php">{$cn_approvals[i].branch_code} ({$cn_approvals[i].count})
	</a></li>
{/section}
</ul>
</p>
</div>
{/if}

<!-- Credit Note approval notification -->
{if $dn_approvals}
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Debit Note</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Debit Note waiting for approval.<br>
<ul>
{section name=i loop=$dn_approvals}
	<li><a href="login.php?server={$dn_approvals[i].branch_code|urlencode}&redir=consignment.debit_note.approval.php">{$dn_approvals[i].branch_code} ({$dn_approvals[i].count})
	</a></li>
{/section}
</ul>
</p>
</div>
{/if}

<!-- Purchase Agreement approval notification -->
{if $purchase_agreement_approvals}
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> Purchase Agreement</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	You have Purchase Agreement waiting for approval.<br>
	<ul>
	{section name=i loop=$purchase_agreement_approvals}
		<li><a href="login.php?server={$purchase_agreement_approvals[i].branch_code|urlencode}&redir=po.po_agreement.approval.php">{$purchase_agreement_approvals[i].branch_code} ({$purchase_agreement_approvals[i].count})
		</a></li>
	{/section}
	</ul>
	</p>
	</div>
{/if}

<!-- Un-finalized POS notification -->
{if $unfinalized_pos}
<h5>
{*<img src=/ui/notify_po_new.png align=absmiddle border=0>*}
<i class="icofont-tags icofont"></i> Non-finalised POS</h5>
The following sales need to be finalised.
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
    <ul style="list-style:none;">
	{foreach from=$unfinalized_pos key=bid item=r}
	    <li>
	        {if $r.data_count>1}
			<img src="/ui/expand.gif" title="Show/Close Details" onClick="togglediv('ul_ufp_{$bid}', this);" class="clickable" /> {$r.branch_code} ({$r.data_count|number_format})
			<ul style="list-style:none;display:none;" id="ul_ufp_{$bid}">
			    {foreach from=$r.date item=d}
			        {strip}
			        {capture assign=target_url}
			            {if $config.counter_collection_server}
			                {$config.counter_collection_server}/counter_collection.php?remote=1&date_select={$d}
			            {else}
							login.php?server={$r.branch_code|urlencode}&redir={$config.counter_collection_server}/counter_collection.php?date_select={$d}
						{/if}
					{/capture}
					{/strip}
					 {if $config.counter_collection_server}
					    <li><a href="javascript:void(open_from_dc('{$target_url}','{$sessioninfo.id}','{$bid}', 'Counter Collection'));">{$d}</a></li>
					 {else}
			        	<li><a href="{$target_url}">{$d}</a></li>
			        {/if}
			    {/foreach}
			</ul>
			{else}
				{strip}
			    {capture assign=target_url}
			        {if $config.counter_collection_server}
			            {$config.counter_collection_server}/counter_collection.php?remote=1&date_select={$r.date.0}
			        {else}
						login.php?server={$r.branch_code|urlencode}&redir={$config.counter_collection_server}/counter_collection.php?date_select={$r.date.0}
					{/if}
				{/capture}
				{/strip}
			    {if $config.counter_collection_server}
			        <li>{$r.branch_code} (<a href="javascript:void(open_from_dc('{$target_url}','{$sessioninfo.id}','{$bid}', 'Counter Collection'));">{$r.date.0}</a>)</li>
			    {else}
			    	<li>{$r.branch_code} (<a href="{$target_url}">{$r.date.0}</a>)</li>
			    {/if}
			{/if}
		</li>
	{/foreach}
	</ul>
</div>
{/if}

<!-- Invalid SKU notification -->
{if $invalid_sku}
<h5>
{*<img src=/ui/notify_po_new.png align=absmiddle border=0>*}
<i class="icofont-tags icofont"></i> Invalid SKU</h5>
The following date got invalid SKU.
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
    <ul style="list-style:none;">
	{foreach from=$invalid_sku key=bid item=r}
	    <li>
	        {if $r.data_count>1}
			<img src="/ui/expand.gif" title="Show/Close Details" onClick="togglediv('ul_is_{$bid}', this);" class="clickable" /> {$r.branch_code} ({$r.data_count|number_format})
			<ul style="list-style:none;display:none;" id="ul_is_{$bid}">
			    {foreach from=$r.date item=d}
			        {strip}
			        {capture assign=target_url}
				        pos.invalid_sku.php?branch_id={$bid}&date_select={$d}&a=refresh_data
					{/capture}
					{/strip}

		        	<li><a href="{$target_url}">{$d}</a></li>

			    {/foreach}
			</ul>
			{else}
				{strip}
				    {capture assign=target_url}
					    pos.invalid_sku.php?branch_id={$bid}&date_select={$r.date.0}&a=refresh_data
					{/capture}
				{/strip}

			   	<li>{$r.branch_code} (<a href="{$target_url}">{$r.date.0}</a>)</li>
			{/if}
		</li>
	{/foreach}
	</ul>
</div>
{/if}

<!-- Future Change Price approval -->
{if $fp_approvals}
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> Batch Price Change</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Batch Price Change waiting for approval.<br>
<ul>
{section name=i loop=$fp_approvals}
	<li><a href="login.php?server={$fp_approvals[i].branch_code|urlencode}&redir=masterfile_sku_items.future_price_approval.php">{$fp_approvals[i].branch_code} ({$fp_approvals[i].count})</a></li>
{/section}
</ul>
</p>
</div>
{/if}

<!-- e-Form approval -->
{if $ed_approvals}
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> e-Form</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have e-Form waiting for approval.<br>
<ul>
{section name=i loop=$ed_approvals}
	<li><a href="login.php?server={$ed_approvals[i].branch_code|urlencode}&redir=eform.approval.php">{$ed_approvals[i].branch_code} ({$ed_approvals[i].count})</a></li>
{/section}
</ul>
</p>
</div>
{/if}

<!-- GRA approval -->
{if $gra_approvals}
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> GRA</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	You have GRA waiting for approval.<br>
	<ul>
	{section name=i loop=$gra_approvals}
		<li><a href="login.php?server={$gra_approvals[i].code|urlencode}&redir=goods_return_advice.approval.php">{$gra_approvals[i].code} ({$gra_approvals[i].count})
		</a></li>
	{/section}
	</ul>
	</p>
	</div>
{/if}

<!-- Stucked Docs -->
{if $stucked_docs}
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09" ><i class="fas fa-tag"></i> Stucked Document Approvals</div>
		<div class=" mb-2 text-muted fs-07">
			<span>Due to inactive user(s), these documents approval is currently stucked.</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="height: 100px;">
			{foreach from=$stucked_docs key=k item=i}
			<li class="fs-08"><a href="stucked_document_approvals.php?m={$k}" target="_blank" class="text-reset">{$i.desc}</a><span class="badge  badge-info ml-2">{$i.count}</span></li>
			{/foreach}
		</ul>
	</div>
</div>
{/if}

{* CNote approval notification *}
{if $cnote_approvals}
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Credit Note</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Credit Note waiting for approval.<br>
		<ul>
			{foreach from=$cnote_approvals item=r}
				<li>
					<a href="login.php?server={$r.branch_code|urlencode}&redir=cnote.approval.php">{$r.branch_code} ({$r.count})
					</a>
				</li>
			{/foreach}
		</ul>
	</p>
	</div>
{/if}

{* Cycle Count approval notification *}
{if $cycle_count_approvals}
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Cycle Count</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Cycle Count waiting for approval.<br>
		<ul>
			{foreach from=$cycle_count_approvals item=r}
				<li>
					<a href="login.php?server={$r.branch_code|urlencode}&redir=admin.cycle_count.approval.php">{$r.branch_code} ({$r.count})
					</a>
				</li>
			{/foreach}
		</ul>
	</p>
	</div>
{/if}