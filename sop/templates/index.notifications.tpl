
{if $marketing_plan_approvals}
<!-- Yearly Marketing Plan Approvals -->
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Yearly Marketing Plan</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Yearly Marketing Plan waiting for approval.<br>
		<ul>
		    <li>
				<a href="yearly_marketing_plan.approval.php">
					Marketing Plan Approval ({$marketing_plan_approvals.sheet_count|default:'0'})
				</a>
			</li>
		</ul>
	</p>
</div>
{/if}

{if $festival_sheet_approvals}
<!-- Festival Sheet Approvals -->
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Festival Date</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Festival Sheet waiting for approval.<br>
		<ul>
		    <li>
				<a href="masterfile_festival_date.approval.php">
					Festival Sheet Approval ({$festival_sheet_approvals.sheet_count|default:'0'})
				</a>
			</li>
		</ul>
	</p>
</div>
{/if}
