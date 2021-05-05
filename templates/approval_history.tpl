{*
8/20/2009 3:24:00 PM Andy
- add reset status remark

2/28/2012 5:06:43 PM Justin
- Added other status remarks.

7/26/2013 2:14 PM Andy
- Enhance approval history to show more info, such as directly approve due to no approval need for minimum document amt.
*}

{if $approval_history}
	<div class="stdframe" style="background:#fff;margin:1em 0;">
		<h4>Approval History</h4>
		<ul style="list-style-type:none;">
			{foreach from=$approval_history item=his}
				<li>
					<span style="margin-left:-20px;">
						{if $his.status==0}
							<img src="ui/notify_sku_reject.png" width="16" height="16" align="absmiddle" title="Reset" />
						{elseif $his.status==1}
							<img src="ui/approved.png" width="16" height="16" align="absmiddle" title="Approved" />
						{elseif $his.status==2}
							<img src="ui/rejected.png" width="16" height="16" align="absmiddle" title="Rejected" />
						{else}
							<img src="ui/terminated.png" width="16" height="16" align="absmiddle" title="Cancelled/Terminated" />
						{/if}
					</span>
					{$his.timestamp} by {$his.u}<br>
					{$his.log}
					
					{if $his.more_info.direct_approve_due_to_less_then_min_doc_amt}
						<span style="color:blue;">
							(Direct Approve due to document amount less than all approval minimum document amount)
						</span>
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
{/if}
