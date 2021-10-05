{*
# REVISION HISTORY #
--------------------
8/20/2007 6:04:32 PM gary 
- filter by branch if not in HQ.

9/27/2007 11:15:05 AM gary
- branch also can view all sku status. (request by ah lee)
*}

{include file=header.tpl}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">SKU Application Status</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
	<div class="row">
		<div class="col-md-6">
			<form action="{$smarty.server.PHP_SELF}" method=get >
				<input type=hidden name=a value="list">
				<b class="form-label">Search SKU Application #ID</b> 
				<div class="form-inline">
					<input class="form-control" name=sku_id size=53 value="{$smarty.request.sku_id}">
				 &nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-info " value="Find">
				</div>
			</form>
		</div>
		<div class="col-md-6">
			<form action="{$smarty.server.PHP_SELF}" method=get >
				<input type=hidden name=a value="list">
				<b class="form-label">Search Code/Description</b> 
				<div class="form-inline">	
				<input class="form-control" name=search_code size=53 value="{$smarty.request.search_code}"> 
				&nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-info  " value="Find"></div>
			</form>
		</div>
	</div>
			<form action="{$smarty.server.PHP_SELF}" method=get style="padding:4px 0;">
			<input type=hidden name=a value="list">
			
			<b class="form-label">Filter by Branch</b>
			<select class="form-control" name=branch_id>
			<option value="">-- All --</option>
			{section name=i loop=$branch}
			<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{/if}>{$branch[i].code}</option>
			{/section}
			</select>
			
			<b class="form-label">Department</b>
			<select class="form-control" name=department_id>
			<option value="">-- All --</option>
			{section name=i loop=$dept}
			<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
			{/section}
			</select>
			
			
			<input type=hidden name=a value="list">
			<b class="form-label">Status</b>
			<select class="form-control" name=status>
			<option value=-1 {if $smarty.request.status==-1}selected{/if}>-- All --</option>
			<option value=0 {if $smarty.request.status==0}selected{/if}>New Application</option>
			<option value=1 {if $smarty.request.status==1}selected{/if}>In Approval Cycle</option>
			<option value=1a {if $smarty.request.status eq '1a'}selected{/if}>Fully Approved</option>
			<option value=2 {if $smarty.request.status==2}selected{/if}>Rejected</option>
			<option value=3 {if $smarty.request.status==3}selected{/if}>KIV (Pending)</option>
			<option value=4 {if $smarty.request.status==4}selected{/if}>In Terminate Cycle</option>
			<option value=4a {if $smarty.request.status eq '4a'}selected{/if}>Terminated</option>
			</select>
			{$pagination}
			<input type="submit" class="btn btn-primary mt-2" value="Refresh">
			</form>
	</div>
</div>

<div class="stdframe card mx-3" id=udiv>
<div class="card-body">
	<table  class="report_table table mb-0 text-md-nowrap  table-hover fs-08">
		<thead class="bg-gray-200" style="height: 30px;">
			<tr>
				<th>ID#</th>
				<th>Status</th>
				<th>Applying Branch</th>
				<th>Brand</th>
				<th>Apply By</th>
				<th>Submit Date</th>
				<th>Approved By</th>
				<th>Need Approval From</th>
				<th>Last Update</th>
				</tr>
		</thead>
		{section name=i loop=$sku}
		<tr>
		<th bgcolor=#ffffff rowspan=2>{$sku[i].id}<br>
		<a class=small href="{$smarty.server.PHP_SELF}?a=view&id={$sku[i].id}" target=_blank>view</a></th>
		<td align=center class=small bgcolor=#ffffff rowspan=2>
		{if $sku[i].status == 0}<img src=ui/notify_sku_new.png align=absmiddle><br>New Application
		{elseif $sku[i].status == 1}<img src=ui/notify_sku_approve.png align=absmiddle><br>
			{if $sku[i].approvals=='' or $sku[i].approvals=='|'}
				Fully Approved
			{else}
				In Approval Cycle
			{/if}
		{elseif $sku[i].status == 2}<img src=ui/notify_sku_reject.png align=absmiddle><br>Rejected
		{elseif $sku[i].status == 3}<img src=ui/notify_sku_pending.png align=absmiddle><br>KIV (Pending)
		{else}<img src=ui/notify_sku_terminate.png align=absmiddle><br>
			{if $sku[i].approvals=='' or $sku[i].approvals=='|'}
				Terminated
			{else}
				In Terminate Cycle
			{/if}
		{/if}
		</td>
		<td bgcolor=#ffffff class="small" colspan=7>{$sku[i].cat_tree}</td>
		</tr>
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td>{$sku[i].branch}</td>
		<td>{$sku[i].brand}</td>
		<td>{$sku[i].u}</td>
		<td class=small>{$sku[i].added}</td>
		<td>{$sku[i].approved_by|default:"-"}</td>
		<td>{if $sku[i].app_status ne 1}{$sku[i].approvals|default:"-"}{else}-{/if}</td>
		<td class=small>{$sku[i].timestamp}</td>
		</tr>
		{/section}
		</table>
</div>
</div>

{include file=footer.tpl}
