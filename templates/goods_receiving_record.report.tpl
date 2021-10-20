{*
REVISION HISTORY
================
11/21/2007 11:15:41 AM gary
- split out if different branch.

11/22/2007 3:14:04 PM gary
- add selection detail in printing.

1/24/2008 3:51:54 PM gary
-add owner column.
-add all grr_item doc_no.
-add last_update column.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs not to round up fixed by 2 but base on config set.

7/24/2012 11:31 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

1/2/2015 10:59 AM Justin
- Bug fixed on some times shows empty GRR information while click on GRR no.

4/14/2015 10:17 AM Justin
- Enhanced to have GST information.

7/22/2015 4:30 PM Joo Chia
- Add in button and rows to show GST detail by GST code.
- Add in export to excel function.

7/23/2015 10:35 AM Andy
- Change to only select own branch when first time enter.

7/23/2015 4:04 PM Joo Chia
- Add expand all and collapse all links.

12/10/2015 4:12 AM DingRen
- Enhance to include filter for grn status
- add gross amount column

12/18/2015 5:18 PM DingRen
- disable auto load on page load

12/21/2015 11:40 AM Justin
- Bug fixed on no data issue.

04/27/2016 10:30 Edwin
- Bug fixed on total data dispaly error when more than one branch is selected
- Added on sorting feature

10/20/2016 11:42 AM Qiu Ying
- Enhanced to filter by invoice date and show with invoice only

4/24/2018 10:02 AM Justin
- Enhanced to show foreign currency.

5/15/2019 4:48PM William
- Enhance "GRR" and "GRN" to use "report_prefix" value.

6/28/2019 1:26 PM William
- Enhance to added new column "Inv no".

7/4/2019 9:33 AM William
- Fixed detail row.

06/24/2020 03:27 PM Sheila
- Updated button css
*}
{include file=header.tpl}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">GRR Report</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if !$no_header_footer}

<div class="card mx-3">
	<div class="card-body">
		<form class="noprint" action="{$smarty.server.PHP_SELF}" method="get" >
			<p>
				<div class="form-inline">
			<select class="form-control" name="date_type"onchange="change_date(this)" id="date_type">
				<option value="grr_date" {if $smarty.request.date_type eq "grr_date"}selected{/if}>GRR Date</option>
				<option value="inv_date" {if $smarty.request.date_type eq "inv_date"}selected{/if}>Invoice Date</option>
			</select>
		
				&nbsp;&nbsp;<b class="form-label">From</b>&nbsp;&nbsp;
			 <input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 />
			 &nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp;
			 &nbsp;&nbsp;<b class="form-label">To</b>&nbsp;&nbsp;
			    <input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> 
			   <img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
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
			
			
			function toggle_gst_details(bid, grr_id, mode){
				var tr_gst_detail = $("gst_detail_"+bid+'_'+grr_id);
				var tr_grr = $('tr_grr-'+bid+'-'+grr_id);
				var tr_img = $('img_gst_dtl_'+bid+'_'+grr_id);
				
				if(tr_gst_detail && tr_grr && tr_img) {
				
					if(tr_gst_detail.style.display == "none"){
						if((mode=="")||(mode=="expand")){
							// open
							
							// get the next tr
							var next_tr = $(tr_grr).next();
							
							// check whether next tr is own gst details
							if(next_tr != tr_gst_detail){
								// found not own gst details
								// clone the html to put after own tr
								new Insertion.After(tr_grr, tr_gst_detail.outerHTML);
								// remove the old gst details tr from table
								$(tr_gst_detail).remove();
								// re-assign the variable
								tr_gst_detail = $("gst_detail_"+bid+'_'+grr_id);
							}
						
							tr_img.src = "/ui/collapse.gif";
							tr_img.title = "Hide Detail";
							tr_gst_detail.style.display = "";
						}
					}else{
						if((mode=="")||(mode=="collapse")){
							// hide
							tr_img.src = "/ui/expand.gif";
							tr_img.title = "Show Detail";
							tr_gst_detail.style.display = "none";
						}
					}
				}
			}
			
			function expand_collapse_all(mode){
				var all_tr_grr = $(document.body).getElementsBySelector('tr.tr_grr');
				
				for(i=0,len=all_tr_grr.length; i<len; i++){
					var bid = all_tr_grr[i].id.split('-')[1];
					var grr_id = all_tr_grr[i].id.split('-')[2];
					
					toggle_gst_details(bid, grr_id, mode);
				}
			}
			
			function change_date(sel) {
				var value = sel.value;
				if (value == "inv_date"){
					$("inv_only").checked = true;
					$("inv_only").disabled = true;
				}else{
					$("inv_only").checked = false;
					$("inv_only").disabled= false;
				}
			}
			
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
					<select class="form-control" name="branch_id">
					<option value="">-- All --</option>
					{section name=i loop=$branch}
					<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id or (!isset($smarty.request.branch_id) and $branch[i].id eq $sessioninfo.branch_id)}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
					{/section}
					</select>
					{/if}
					</div>
					<div class="col-md-4">
						<b class="form-label">Department</b>
					<select class="form-control" name="department_id">
					<option value="">-- All --</option>
					{section name=i loop=$dept}
					<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
					{/section}
					</select>
					</div>
					<div class="col-md-4">
						<b class="form-label">Status</b>
					<select class="form-control" name="status">
						<option value="0">All</option>
						<option value="1" {if $smarty.request.status eq 1}selected{/if}>No GRN</option>
						<option value="2" {if $smarty.request.status eq 2}selected{/if}>Opened GRN</option>
						<option value="3" {if $smarty.request.status eq 3}selected{/if}>Completed</option>
					
						{*<option value="1" {if $smarty.request.status && $smarty.request.status eq 1}selected{/if}>Active</option>
						<option value="0" {if $smarty.request.status && $smarty.request.status eq 0}selected{/if}>In-active</option>*}
					</select>
					</div>
					</p>
					<p>
					<div class="col-md-4">
						<b class="form-label">Vendor</b>
					<select class="form-control" name="vendor_id">
					<option value="">-- All --</option>
					{section name=i loop=$vendor}
					{if $vendor[i].id}
					<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
					{/if}
					{/section}
					</select>
					</div>
					
				<div class="col-md-4">
					<b class="form-label">Sort By</b>
					<select class="form-control" name="sort_field">
						{foreach from=$sort_field_list key=k item=i}
							<option value="{$k}" {if $smarty.request.sort_field eq $k}selected{/if}>{$i}</option>
						{/foreach}
					</select>
					
				</div>
				<div class="col-md-4">
					<select class="form-control mt-4" name="sort_order">
						{foreach from=$sort_order_list key=k item=i}
							<option value="{$k}" {if $smarty.request.sort_order eq $k}selected{/if}>{$i}</option>
						{/foreach}
					</select>
				</div>
					</p>
					<br>
					
			</div>
			<div class="row">
				<p>
					<b class="form-inline form-label mt-2">
						&nbsp;&nbsp;&nbsp;<input name="inv_only" id="inv_only" type="checkbox" {if $smarty.request.date_type eq "inv_date"}disabled checked{elseif $smarty.request.date_type eq "grr_date" && $smarty.request.inv_only}checked{/if}> &nbsp;Show GRR with Invoive Only
					</b>
					</p>
					<p>
					
					</p>
			</div>
			<div class="row">
				<input class="btn btn-primary ml-1" name="submit" type="submit" value="Refresh"> 
				<input class="btn btn-primary ml-1" name="submit" type="submit" value="Print">
					{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info ml-1" name="output_excel">{#OUTPUT_EXCEL#}</button>
					{/if}
			</div>
			</form>
	</div>
</div>

{/if}

<br>
{if $smarty.request.submit or $is_export_excel}
	{php}
		show_report();
	{/php}
{/if}

{if $grr}

{if $config.foreign_currency}* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}{/if}

{if !$no_header_footer}


	<ul class="list-group list-group-flush">
		<div class="row mx-3">
		<div class="col-md-6">
			<li class="list-group-item list-group-item-action">
				<a href="javascript:void(expand_collapse_all('expand'))">
					<img src="/ui/expand.gif" width="10" title="Expand All" class="clickable"> Expand All </a>
			</li>
		</div>
		
		<div class="col-md-6">
			<li class="list-group-item list-group-item-action">
				<a href="javascript:void(expand_collapse_all('collapse'))">
					<img src="/ui/collapse.gif" width="10" title="Collapse All" class="clickable"> Collapse All </a>
			</li>
		</div>
	</div>
	</ul>

{/if}


<div class="noscreen">
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{if $smarty.request.branch_id || $BRANCH_CODE!='HQ'} Branch:{$form.branch}&nbsp;&nbsp; {/if}
From:{$smarty.request.from}
&nbsp;&nbsp; To:{$smarty.request.to}
&nbsp;&nbsp; Department:{if $_dp}{$_dp}{else}ALL{/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
</div>
{assign var=count value=0}
{assign var=nr_colspan value=7}

{if $have_fc}
	{assign var=nr_colspan value=$nr_colspan+2}
{/if}

{assign var=gst_colspan value=6}
{if $have_fc}
	{assign var=gst_colspan value=$gst_colspan+2}
{/if}

{section name=i loop=$grr}
{if $last_branch!=$grr[i].branch && $last_branch}
<tr class="sortbottom" bgcolor="#ffee99" height="24">
	<th colspan="{$nr_colspan}" align="right">Total</th>
	<th align="right">{$total1-$total_gst|number_format:2}</th>
	{if $is_under_gst}
		<th align="right">{$total_gst|number_format:2}</th>
		<th align="right">{$total1|number_format:2}</th>
	{/if}
	<td colspan=5>&nbsp;</td>
</tr>
{foreach from=$total_gst_list[$cur_brn_id] item=t}
	<tr bgcolor="#ffee99" class="sortbottom">
		<td colspan="{$gst_colspan}"></td>		
		<td align="center">{$t.g_gst_code} ({$t.g_gst_rate}%)</td>
		<td align="right">{$t.g_ttl_amt-$t.g_ttl_gst_amt|round2|number_format:2}</td>
		{if $is_under_gst}
			<td align="right">{$t.g_ttl_gst_amt|round2|number_format:2}</td>
			<td align="right">{$t.g_ttl_amt|round2|number_format:2}</td>
		{/if}
		<td colspan="5"></td>
	</tr>
{/foreach}
{if $total_gst_error.$cur_brn_id.g_ttl_amt}
	<tr bgcolor=#ffee99 class="sortbottom">
		<td colspan="{$gst_colspan}"></td>		
		<td align="center">GST Error</td>
		<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_amt-$total_gst_error.$cur_brn_id.g_ttl_gst_amt|round2|number_format:2}</td>
		{if $is_under_gst}
			<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_gst_amt|round2|number_format:2}</td>
			<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_amt|round2|number_format:2}</td>
		{/if}
		<td colspan="5"></td>
	</tr>
{/if}
{if $total_non_gst.$cur_brn_id.grr_amt}
	<tr bgcolor="#ffee99" class="sortbottom">
		<td colspan="{$gst_colspan}"></td>		
		<td align="center">Non GST</td>
		<td align="right">{$total_non_gst.$cur_brn_id.grr_amt|round2|number_format:2}</td>
		{if $is_under_gst}
			<td align="right">-</td>
			<td align="right">{$total_non_gst.$cur_brn_id.grr_amt|round2|number_format:2}</td>
		{/if}
		<td colspan="5"></td>
	</tr>
{/if}

</table>
<br>
{assign var=total1 value=0}
{assign var=total_gst value=0}
{assign var=count value=0}
{/if}

{assign var=count value=$count+1}
{if $last_branch!=$grr[i].branch}
{if !$smarty.request.branch_id && $BRANCH_CODE=='HQ'}
<h4>{$grr[i].branch}</h4>
{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sortable" width="100%" class="report_table table mb-0 text-md-nowrap  table-hover"
			>
				<thead class="bg-gray-100">
					<tr >
						<th>&nbsp;</th>
						<th>GRR #</th>
						<!--th>Branch</th-->
						<th>Owner</th>
						<th>Vendor Code</th>
						<th>Vendor</th>
						<th>Doc no</th>
						<th>Department</th>
						{if $have_fc}
							<th>Foreign Amount</th>
							<th>Exchange Rate</th>
						{/if}
						<th>Amount {if $have_fc}({$config.arms_currency.symbol}){/if}</th>
						{if $is_under_gst}
							<th>GST Amount {if $have_fc}({$config.arms_currency.symbol}){/if}</th>
							<th>Amount Inclusive<br />GST {if $have_fc}({$config.arms_currency.symbol}){/if}</th>
						{/if}
						<th>Ctn</th>
						<th>Pcs</th>
						<th>Received Date</th>
						{if $smarty.request.date_type eq "inv_date"}
							<th>Invoice Date</th>
						{/if}
						<th>Last Update</th>
						<th>GRN</th>
					</tr>
				</thead>
				{/if}
				<tr bgcolor='{cycle values="#eeeeee,#ffffff"}' class="fs-08" id="tr_grr-{$grr[i].branch_id}-{$grr[i].id}" class="tr_grr">
					<td align="right">{$smarty.section.i.iteration}.</td>
					<td nowrap>
						{if $is_export_excel}
							{$grr[i].report_prefix}{$grr[i].id|string_format:"%05d"}
						{else}
							<a href="/goods_receiving_record.php?a=view&id={$grr[i].id}&branch_id={$grr[i].branch_id}" target="_blank">
								{$grr[i].report_prefix}{$grr[i].id|string_format:"%05d"}
							</a>
						{/if}
						{if !$is_export_excel && $grr[i].gst_detail && $grr[i].is_under_gst}
							&nbsp;<img src="/ui/expand.gif" width="10" id="img_gst_dtl_{$grr[i].branch_id}_{$grr[i].id}" onclick="toggle_gst_details({$grr[i].branch_id},{$grr[i].id}, '');" title="Show Detail" class="clickable">
						{/if}
					<br/>
					</td>
					<!--td>{$grr[i].branch}</td-->
					<td>{$grr[i].user}</td>
					<td>{$grr[i].vendor_code}
					{if $config.enable_vendor_account_id}
						<br>{$grr[i].account_id}
					{/if}
					</td>
					<td>{$grr[i].vendor_desc}</td>
					<td>
						<font class="small" color="#009900">{$grr[i].all_doc_no}</font>
					</td>
					<td>{$grr[i].department}</td>
					{if $grr[i].currency_code}
						<td align="right">{$grr[i].currency_code} {$grr[i].grr_amount|number_format:2}</td>
						<td align="right">{$grr[i].currency_rate}</td>
						{assign var=row_myr_amt value=$grr[i].grr_amount*$grr[i].currency_rate}
					{else}
						{if $have_fc}
							<td align="right">-</td>
							<td align="right">-</td>
						{/if}
						{assign var=row_myr_amt value=$grr[i].grr_amount}
					{/if}
					<td {if $grr[i].currency_code}class="converted_base_amt"{/if} align="right">{$row_myr_amt-$grr[i].grr_gst_amount|number_format:2}{if $grr[i].currency_code}*{/if}</td>
					{if $is_under_gst}
						<td align="right">{$grr[i].grr_gst_amount|number_format:2}</td>
						<td {if $grr[i].currency_code}class="converted_base_amt"{/if} align="right">{$row_myr_amt|number_format:2}{if $grr[i].currency_code}*{/if}</td>
					{/if}
					<td align="right">{$grr[i].grr_ctn|qty_nf}</td>
					<td align="right">{$grr[i].grr_pcs|qty_nf}</td>
					<td align="center">{$grr[i].rcv_date}</td>
					{if $smarty.request.date_type eq "inv_date"}
						<td align="center">
							{$grr[i].all_inv_date}
						</td>
					{/if}
					<td align="center">{$grr[i].last_update}</td>
					<td>
						{if $grr[i].grn}
							{if $is_export_excel}
								{$grr[i].report_prefix}{$grr[i].grn.id|string_format:"%05d"}
							{else}
								<a href="goods_receiving_note.php?a=view&id={$grr[i].grn.id}&branch_id={$grr[i].grn.branch_id}" target="_blank">
									{$grr[i].report_prefix}{$grr[i].grn.id|string_format:"%05d"}
								</a>
							{/if}
							{if $grr[i].grn.completed}<br/><font class="small" color="blue">(Completed)</font>{/if}
						{else}
						-
						{/if}
					</td>
				</tr>
				
				{assign var=total1 value=$total1+$row_myr_amt}
				{assign var=total_gst value=$total_gst+$grr[i].grr_gst_amount}
				{assign var=last_branch value=$grr[i].branch}
				{assign var=cur_brn_id value=$grr[i].branch_id}
				{if $smarty.request.date_type eq "grr_date"}
					{assign var=num_colspan value = 5}
				{else}
					{assign var=num_colspan value = 6}
				{/if}
				{if $grr[i].gst_detail && $grr[i].is_under_gst}
				
					<tr id="gst_detail_{$grr[i].branch_id}_{$grr[i].id}" style="display:none;" bgcolor="#dfedfe">
						<td></td>
						<td>{$grr[i].report_prefix}{$grr[i].id|string_format:"%05d"}</td>
						<td {if $have_fc}colspan="6"{else}colspan="4"{/if}></td>
						<td align="center">
						{foreach from=$grr[i].gst_detail item=r} 	
							{$r.gst_code} {if $r.gst_code !="GST Error"}({$r.gst_rate}%){/if}<br />
						{/foreach}
						</td>
				
						<td align="right" colspan="2">
						{foreach from=$grr[i].gst_detail item=r} 
							{$r.ttl_gst_amt|round2|number_format:2}<br />
						{/foreach}
						</td>
						
						{if $is_under_gst}
							<td align="right">
							{foreach from=$grr[i].gst_detail item=r} 
								{$r.ttl_amt|round2|number_format:2}<br />
							{/foreach}
							</td>
						{/if}
						
						<td colspan="{$num_colspan+1}"></td>
					</tr>
				
				{/if}
				
				{/section}
				
				<tr class="sortbottom" bgcolor="#ffee99" height="24">
					<th colspan="{$nr_colspan}" align="right">Total</th>
					<th align="right">{$total1-$total_gst|number_format:2}</th>
					{if $is_under_gst}
						<th align="right">{$total_gst|number_format:2}</th>
						<th align="right">{$total1|number_format:2}</th>
					{/if}
					<td colspan="{$num_colspan}">&nbsp;</td>
				</tr>
				
				{foreach from=$total_gst_list[$cur_brn_id] item=t}
					<tr bgcolor="#ffee99" class="sortbottom">
						<td colspan="{$gst_colspan}"></td>		
						<td align="center">{$t.g_gst_code} ({$t.g_gst_rate}%)</td>
						<td align="right">{$t.g_ttl_amt-$t.g_ttl_gst_amt|round2|number_format:2}</td>
						{if $is_under_gst}
							<td align="right">{$t.g_ttl_gst_amt|round2|number_format:2}</td>
							<td align="right">{$t.g_ttl_amt|round2|number_format:2}</td>
						{/if}
						<td colspan="{$num_colspan}">&nbsp;</td>
					</tr>
				{/foreach}
				
				{if $total_gst_error.$cur_brn_id.g_ttl_amt}
					<tr bgcolor="#ffee99" class="sortbottom">
						<td colspan="{$gst_colspan}"></td>		
						<td align="center">GST Error</td>
						<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_amt-$total_gst_error.$cur_brn_id.g_ttl_gst_amt|round2|number_format:2}</td>
						{if $is_under_gst}
							<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_gst_amt|round2|number_format:2}</td>
							<td align="right">{$total_gst_error.$cur_brn_id.g_ttl_amt|round2|number_format:2}</td>
						{/if}
						<td colspan="{$num_colspan}">&nbsp;</td>
					</tr>
				{/if}
				
				{if $total_non_gst.$cur_brn_id.grr_amt}
					<tr bgcolor="#ffee99" class="sortbottom">
						<td colspan="{$gst_colspan}"></td>		
						<td align="center">Non GST</td>
						<td align="right">{$total_non_gst.$cur_brn_id.grr_amt|round2|number_format:2}</td>
						{if $is_under_gst}
							<td align="right">-</td>
							<td align="right">{$total_non_gst.$cur_brn_id.grr_amt|round2|number_format:2}</td>
						{/if}
						<td colspan="{$num_colspan}">&nbsp;</td>
					</tr>
				{/if}
				
				</table>
		</div>
	</div>
</div>
{else}
{if !$no_header_footer}
{if $smarty.get.from}
No Data
{/if}
{/if}
{/if}

{include file=footer.tpl}
{if $smarty.request.submit eq 'Print'}
<script>
window.print();
</script>
{/if}
