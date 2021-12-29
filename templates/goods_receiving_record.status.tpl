{*
REVISION HISTORY
==================
1/24/2008 3:48:59 PM gary
- add all grr_items doc_no.
- status -ALL.
- add owner column.

3/5/2012 3:33:31 PM Justin
- Added to show GRN list column.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

1/2/2015 10:59 AM Justin
- Bug fixed on some times shows empty GRR information while click on GRR no.

12/18/2015 5:18 PM DingRen
- disable auto load on page load

3/3/2016 1:51 PM Andy
- Fix no data issue.

5/23/2019 9:51 AM William
- Enhance "GRR" word to use report_prefix.

06/24/2020 4:04 PM Sheila
- Updated button css
*}
{include file=header.tpl}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				GRR Status Report
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<form class="noprint" action="{$smarty.server.PHP_SELF}" method=get >
			<p>
			
			<div class="row">
			<div class="col-md-4">
				<div class="form-inline">
					<b class="form-label">GRR Date From&nbsp;</b>
				<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 />
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp;
				</div>
			</div>
			
			<div class="col-md-4">
			<div class="form-inline">
				<b class="form-label">To&nbsp;</b>
				<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> 
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
			</div>
			</div>
			</div>
			
			
			<!-- calendar stylesheet -->
			<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
			
			<!-- main calendar program -->
			<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
			
			<!-- language for the calendar -->
			<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
			
			<!-- the following script defines the Calendar.setup helper function, which makes
			   adding a calendar a matter of 1 or 2 lines of code. -->
			<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
			
			{literal}
			<script type="text/javascript">
			
			
				Calendar.setup({
					inputField     :    "added1",     // id of the input field
					ifFormat       :    "%Y-%m-%d",      // format of the input field
					button         :    "t_added1",  // trigger for the calendar (button ID)
					align          :    "Bl",           // alignment (defaults to "Bl")
					singleClick    :    true
					//,
					//onUpdate       :    load_data
				});
			
				Calendar.setup({
					inputField     :    "added2",     // id of the input field
					ifFormat       :    "%Y-%m-%d",      // format of the input field
					button         :    "t_added2",  // trigger for the calendar (button ID)
					align          :    "Bl",           // alignment (defaults to "Bl")
					singleClick    :    true
					//,
					//onUpdate       :    load_data
				});
			
			</script>
			{/literal}
			&nbsp;
			</p>
			<div class="row">
				<p>
					<!--input type=hidden name=a value="list"-->
					<div class="col-md-4">
						{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label">Branch</b>
					<select class="form-control" name=branch_id>
					{section name=i loop=$branch}
					<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
					{/section}
					</select>
			
					{/if}
					</div>
					<div class="col-md-4">
						<b class="form-label">Department</b>
					<select class="form-control" name=department_id>
					<option value="">-- All --</option>
					{section name=i loop=$dept}
					<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
					{/section}
					</select>
					</div>
				
					<div class="col-md-4">
						<b class="form-label">By Status</b>
					<select class="form-control" name=status>
					<option value="">-- All --</option>
					<option value=completed {if $smarty.request.status eq 'completed'}selected{/if}>Completed</option>
					<option value=incomplete {if $smarty.request.status eq 'incomplete'}selected{/if}>Incomplete</option>
					</select>
					</div>
					
					</p>
		
					<p>
					<div class="col-md-4">
						<b class="form-label">Vendor</b>
					<select class="form-control" name=vendor_id>
					<option value="">-- All --</option>
					{section name=i loop=$vendor}
					{if $vendor[i].id}
					<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
					{/if}
					{/section}
					</select>
					</div>
			
					
					</p>
			</div>
			<div class="row">
				<input class="btn btn-primary mt-2 ml-2" name=submit type=submit value="Refresh">
					 <input class="btn btn-info mt-2 ml-2" name=submit type=submit value="Print">
			</div>
			</form>
	</div>
</div>

<br>
{if $smarty.request.submit}
{php}
show_report();
{/php}
{/if}

{if $grr}
{assign var=nr_colspan value=6}
<div class="card mx-3">
	<div class="card-body">
		<table id=t1 class="sortable" width=100% >
			<thead class="bg-gray-100">
				<tr>
					<th>&nbsp;</th>
					<th>GRR #</th>
					<th>GRN #</th>
					<th>Owner</th>
					<th>Vendor Code</th>
					{if $config.enable_vendor_account_id}
						<th>Account ID</th>
						{assign var=nr_colspan value=$nr_colspan+1}
					{/if}
					<th>Vendor</th>
					<!--th>Department</th-->
					<th>PO</th>
					<th>Used PO</th>
					<th>INV</th>
					<th>Used INV</th>
					<!--th>DO</th>
					<th>Used DO</th>
					<th>Others</th>
					<th>Used Others</th-->
					<th>Received</th>
					<th>Last Update</th>
				</tr>
			</thead>
			<tbody class="fs-08">
			{section name=i loop=$grr}
			{assign var=t1 value=$t1+$grr[i].po_count}
			{assign var=t2 value=$t2+$grr[i].po_used_count}
			{assign var=t3 value=$t3+$grr[i].inv_count}
			{assign var=t4 value=$t4+$grr[i].inv_used_count}
			{assign var=t5 value=$t5+$grr[i].do_count}
			{assign var=t6 value=$t6+$grr[i].do_used_count}
			{assign var=t7 value=$t7+$grr[i].misc_count}
			{assign var=t8 value=$t8+$grr[i].misc_used_count}
			
			<tr {cycle values="bgcolor=#eeeeee,"} align=center>
				<td align=right>{$smarty.section.i.iteration}.</td>
				<td>
				<a href="goods_receiving_record.php?a=view&id={$grr[i].grr_id}&branch_id={$grr[i].branch_id}" target="_blank">
				{$grr[i].report_prefix}{$grr[i].grr_id|string_format:"%05d"}
				</a>
				</td>
				<td>
					{foreach from=$grr[i].grn_list key=r item=grn name=grn_list}
						<a href="goods_receiving_note.php?a=view&id={$grn.id}&branch_id={$grn.branch_id}" target="_blank">{$grr[i].report_prefix}{$grn.id|string_format:"%05d"}</a>{if !$smarty.foreach.grn_list.last},{/if}
					{/foreach}
				</td>
				<td align=left>{$grr[i].user}</td>
				<td align=left>{$grr[i].vendor_code}</td>
				{if $config.enable_vendor_account_id}
					<td align=left>{$grr[i].account_id}</td>
				{/if}
				<td align=left>{$grr[i].vendor}<br>
				<font class="small" color=#009900>
				{$grr[i].all_doc_no}
				</font>
				</td>
				<!--td>{$grr[i].department}</td-->
				<td>{$grr[i].po_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].po_used_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].inv_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].inv_used_count|ifzero:"&nbsp;"}</td>
				<!--td>{$grr[i].do_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].do_used_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].misc_count|ifzero:"&nbsp;"}</td>
				<td>{$grr[i].misc_used_count|ifzero:"&nbsp;"}</td-->
				<td>{$grr[i].rcv_date}</td>
				<td>{$grr[i].last_update}</td>
			</tr>
			{assign var=total1 value=$total1+$grr[i].grr_amount}
			{/section}
			</tbody>
			<tr class=sortbottom bgcolor=#ffee99 height=24>
				<th colspan="{$nr_colspan}" align=right>Total</th>
				<th>{$t1|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t2|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t3|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t4|number_format|ifzero:"&nbsp;"}</th>
				<!--th>{$t5|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t6|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t7|number_format|ifzero:"&nbsp;"}</th>
				<th>{$t8|number_format|ifzero:"&nbsp;"}</th-->
				<td colspan=2>&nbsp;</td>
			</tr>
			</table>
	</div>
</div>
{else}
{if $smarty.get.from}
No Data
{/if}
{/if}

{include file=footer.tpl}
{if $smarty.request.submit eq 'Print'}
<script>
window.print();
</script>
{/if}
