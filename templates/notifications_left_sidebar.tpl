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
<h5>
{*<img src=/ui/notify_mm.png align=absmiddle border=0> *}
<i class="icofont-users-alt-2 icofont"></i> <a href="membership.php?t=verify">Membership Verification</a></h5>
<div class=ntc>There are records to be verified.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
{section name=i loop=$membership}
<li> <a href="membership.php?t=verify&branch_id={$membership[i].branch_id}">{$membership[i].branch_code}</a> ({$membership[i].count})
{/section}
</ul>
{/if}

<!-- membership blocked -->
{if $membership_blocked}
<h5>
{*<img src=/ui/notify_mm.png align=absmiddle border=0>*}
<i class="icofont-ui-block icofont"></i>
<a href="membership.php?t=verify">Blocked Membership</a></h5>
<div class=ntc>The following membership are blocked.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
{section name=i loop=$membership_blocked}
<li> <a href="membership.listing.php?branch_id={$membership_blocked[i].branch_id}">{$membership_blocked[i].branch_code}</a> ({$membership_blocked[i].count})
{/section}
</ul>
{/if}

<!-- Membership Summary -->
{if $sessioninfo.privilege.MEMBERSHIP_SUMM and $membership_summary}
	<h5>
	{*<img src="/ui/notify_mm.png" align="absmiddle" border="0">*}
	<i class="icofont-users-alt-2 icofont"></i>
	Membership Summary</h5>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	    <li> <a href="membership.listing.php">Total Member</a> ({$membership_summary.total|number_format})</li>
	    <li> <a href="membership.listing.php?verified=1&blocked=0&terminated=0">Total Verified Member</a> ({$membership_summary.verified|number_format})</li>
	    <li> <a href="membership.listing.php?verified=0&blocked=0&terminated=0">Total Unverified Member</a> ({$membership_summary.unverified|number_format})</li>
	    <li> <a href="membership.listing.php?blocked=1&terminated=0">Total Blocked Member</a> ({$membership_summary.blocked|number_format})</li>
	    <li> <a href="membership.listing.php?terminated=1">Total Terminated Member</a> ({$membership_summary.terminated|number_format})</li>
	</ul>
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
	<h5>
	{*<img src="/ui/notify_po_new.png" align=absmiddle border=0>*}
	<i class="icofont-tags icofont"></i> GRN</h5>
	{if $grn_confirmations}
		<p><h5><i class="icofont-check-circled icofont"></i>Confirmation:</h5></p>
		<div class=ntc>The following GRN requires your confirmation.</div>
		{foreach from=$grn_count_list name=grn_count key=r item=count}
			{if $r eq "doc_pending"}
				Document Pending
			{elseif $r eq "acc_verify"}
				Account Verification
			{else}
				SKU Manage
			{/if}
			: {$count} record(s)
			{if !$smarty.foreach.grn_count.last}<br />{/if}
		{/foreach}
		<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		{foreach from=$grn_confirmations item=grn key=row}
			<div style="border-bottom:1px solid #eee"> 
				<a href="/goods_receiving_note.php?a=open&id={$grn.grn_id}&branch_id={$grn.branch_id}&action={$grn.action}">
					{$grn.report_prefix}{$grn.grn_id|string_format:"%05d"} - 
					{if $grn.action eq 'verify'}
						SKU Manage
					{elseif $grn.action eq 'grr_edit'}
						Pending Document
					{else}
						Account Verification
					{/if}
				</a>
				<br>
				<font color=#666666 class=small>Received Date : {$grn.rcv_date}<br></font>
			</div>
		{/foreach}
		</div>
	{/if}
	{if $grn_approvals}
		<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
		<p><h5>Approval:</h5></p>
		<p>
		You have GRN waiting for approval.<br>
		<ul>
		{section name=i loop=$grn_approvals}
			<li><a href="login.php?server={$grn_approvals[i].code|urlencode}&redir=goods_receiving_note_approval.php">{$grn_approvals[i].code} ({$grn_approvals[i].count})
			</a></li>
		{/section}
		</ul>
		</p>
		</div>
	{/if}
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
	<h5>
	{*<img src="/ui/notify_po_new.png" align="absmiddle" border="0">*}
	<i class="icofont-tags icofont"></i> Stucked Document Approvals</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	Due to inactive users(s), these documents approval is currently stucked.<br />
	<ul>
	{foreach from=$stucked_docs key=k item=i}
		<li><a href="stucked_document_approvals.php?m={$k}" target="_blank">{$i.desc}</a> ({$i.count})</li>
	{/foreach}
	</ul>
	</p>
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