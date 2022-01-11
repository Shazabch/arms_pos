{*

7/1/2010 2:34:04 PM Justin
- Fixed the Use GRN bugs by applying new method while select Use GRN but without selecting Vendor. 

7/8/2010 5:11:36 PM Justin
- Replaced the current Branch Group into Branch selection.
  -> In this Branch option field, user are allowed to select either single branch or branch group.
- Added the checking features on the module to enable/disable Use GRN checkbox.
  -> Checkbox will be enabled whenever Branch is selected.
  -> Checkbox will be disabled if found do not select Branch.
- Amended the entire structure of queries to accept incoming branch or branch group selected by users.

8/11/2010 11:51:23 AM Justin
- Modified the report module allow user to use GRN when not under HQ mode.

10/10/2011 10:45:13 AM Andy
- Fix report javascript error cause "Use GRN" checkbox cannor be tick.

10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/11/2011 3:46:35 PM Andy
- Fix javascript error when checking whether to turn on/off "Use GRN" checkbox. 

11/24/2011 3:06:21 PM Andy
- Change "Use GRN" popup information message.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

8/7/2014 2:33 PM Justin
- Added new info "Cost Price" (need config).
p
5/21/2018 5:00 pm Kuan Yeh
- Bug fixed of logo shown on excel export  
- fix bug for MAC platform export excel format print out icon

3/4/2020 2:31 PM William
- Fixed bug show detail will not hide when click hide.
*}

{include file=header.tpl}
{if !$no_header_footer}

{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
.c1 { background:#ff9; }
.c2 { background:none; }
.c3 { background:#aff; }
.c4 { background:#faf; }
.c5 { background:#aaffaa; }
.c6 { background:#33ffaa; }
.r1 { background:#33ff99;}
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
.d1 { background:#aaeffc; }
.d2 { background:none; }
.d3 { background:#bfeeec; }
.d4 { background:#daeffc; }
.d5 { background:#bedffc; }
.d6 { background:#ffcbed; }
</style>
{/literal}

<script>

var phpself = "{$smarty.server.PHP_SELF}";
var date = "{$smarty.request.date}";
var branch_id = "{$smarty.request.branch_id}";
var filter_date = "{$smarty.request.filter_date}";
var sku_type = "{$smarty.request.sku_type}";
var filter_type = "{$smarty.request.filter_type}";
var category_id = "{$smarty.request.category_id}";
var GRN = "{$smarty.request.GRN}";
var order_type = "{$smarty.request.order_type}";
var exclude_inactive_sku = "{$smarty.request.exclude_inactive_sku}";

{literal}

function toggle_sub(tbody_id, el)
{
	if ($(tbody_id).style.display=='none')
	{
	    el.src='/ui/collapse.gif';
	    $(tbody_id).style.display='';
	}
	else
	{
	    el.src='/ui/expand.gif';
	    $(tbody_id).style.display='none';
	}
}

function change_filter_status(){
	var filter_by = getRadioValue(document.f_a.filter_type);

	if(filter_by == 'all'){
	    $('by_category').style.display='none';
		if($('branch_id') == undefined){
			//$('GRN').disabled=false;
		}else{
			/*if($('branch_id').value > 0){
				$('GRN').disabled=false;
				//$('GRN').checked=false;
			}else{
				$('GRN').disabled=true;
				$('GRN').checked=false;
			}*/
		}
	}else{
        $('by_category').style.display='';
        /*if($('branch_id') == undefined){
			$('GRN').disabled=false;
		}*/
	}
}

/*function check_grn(){
	val=$('branch_id').value;

	if(val){
		$('GRN').disabled=false;
	}
	else{
		$('GRN').checked=false;
		$('GRN').disabled=true;
	}
}*/

function show_detail(vendor_id, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$(".report_table tr.vendor_child_"+vendor_id);
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$('vendor_child1_'+vendor_id+'_'+(i+1)).show();
			$('vendor_child2_'+vendor_id+'_'+(i+1)).show();
			$('vendor_child3_'+vendor_id+'_'+(i+1)).show();
		}
		
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$('vendor_child1_'+vendor_id+'_'+(i+1)).hide();
			$('vendor_child2_'+vendor_id+'_'+(i+1)).hide();
			$('vendor_child3_'+vendor_id+'_'+(i+1)).hide();
		}
	}
	
	if(all_tr.length>0)	return false;
	
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself, {
		parameters: {
			a: 'ajax_show_details',
			ajax: 1,
			date: date,
			branch_id: branch_id,
			filter_date: filter_date,
			sku_type: sku_type,
			filter_type: filter_type,
			category_id: category_id,
			vendor_id: vendor_id,
			GRN: GRN,
			exclude_inactive_sku: exclude_inactive_sku
		},
		onComplete: function(e){
			new Insertion.After($('tr_vendor_'+vendor_id), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

function chk_vd_filter(){
	var allow_use_grn = false;
	if(document.f_a['vendor_id'].value>0){
		allow_use_grn = true;
	}
	
	if(allow_use_grn)	document.f_a['GRN'].disabled = false;
	else	document.f_a['GRN'].disabled = true;
}
</script>
{/literal}
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method=post class=form name="f_a">
			<input type=hidden name=report_title value="{$report_title}">
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Date</b>
				<div class="form-inline">
					<input class="form-control" type=text name=date id=date value="{$smarty.request.date|ifzero:$smarty.now|date_format:'%Y-%m-%d'}" size=23>
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id" id="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
					{/foreach}
					{if $branch_group.header}
						<optgroup label="Branch Group">
							{foreach from=$branch_group.header item=r}
								{capture assign=bgid}bg,{$r.id}{/capture}
								<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
							{/foreach}
						</optgroup>
					{/if}
				</select>
				{else}
				{/if}
				</div>
				
				<div class="col-md-4">
					<div class="form-label mt-4">
						<input type=radio name=filter_date value="mtd" {if $smarty.request.filter_date ne 'ytd'}checked{/if}><b>&nbsp;MTD</b>
					&nbsp;<input type=radio name=filter_date value="ytd" {if $smarty.request.filter_date eq 'ytd'}checked{/if}><b>&nbsp;YTD</b>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label mt-2">SKU Type</b>
			<select class="form-control" name="sku_type">
				<option value="">-- All --</option>
				{foreach from=$sku_type item=t}
				<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
				{/foreach}
			</select>
				</div>
			
			<div class="col-md-4">
				<p>
					<div class="form-label mt-4">
						<b >Department/Category</b>
					&nbsp;<input type=radio name="filter_type" value="all" {if $smarty.request.filter_type eq 'all' or $smarty.request.filter_type eq ''}checked{/if} onClick="change_filter_status()">All 
					<input type=radio name="filter_type" value="category" {if $smarty.request.filter_type eq 'category'}checked{/if} onClick="change_filter_status()">by Selection 
					</div>
					<div id="by_category" style="display:none;">
					{include file="category_autocomplete.tpl"}
					</div>
					</p>
			</div>
			</div>
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label mt-2">Vendor</b>
				<div class="form-inline">
					<select class="form-control"  name="vendor_id" id="vendor_id" onChange="chk_vd_filter();" style="width: 200px;">
						<option value='all' {if $smarty.request.vendor_id eq 'all'}selected{/if}>-- All --</option>
						{foreach from=$vendor item=r}
						<option value={$r.id} {if $smarty.request.vendor_id eq $r.id}selected{/if}>{$r.description} </option>
						{/foreach}
					</select>
					&nbsp;<input type=checkbox {if $smarty.request.GRN eq true}checked {/if} name="GRN" id="GRN" {if $smarty.request.vendor_id eq 'all' || !$smarty.request.vendor_id}disabled {/if} >
						
				</div>
				</div>
	
				<div class="col-md-4">
					<div class="form-inline form-label mt-2">
						<b class="">Use GRN</b>
				[<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>] 
					</div>
				
				<div class="form-inline">
					<select class="form-control" name=order_type>
						<option value=top {if $smarty.request.order_type eq 'top'}selected{/if}>Top</option>
						<option value=bottom {if $smarty.request.order_type eq 'bottom'}selected{/if}>Bottom</option>
						</select>
						&nbsp;<input class="form-control" size=5 type=text name=filter_number value="{$filter_number|default:10}">
						&nbsp;(Max 1000)
				</div>
				</div>
				
				<div class="col-md-4">
					<div class="form-label form-inline mt-4">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
			</div>
			
			
			
			
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
<script>
//get_brand(document.report_form.department_id.value,'{$smarty.request.brand_id}');
change_filter_status();

</script>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}

{assign var=show_type value='amount'}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
<br>
<!--Start Date: {$start_date}
End Date: {$end_date} 
Vendor: {$vendor_name}
{if $smarty.request.branch_group}
Branch Group: {$branch_group.header[$smarty.request.branch_group].code}
{/if}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=report_table width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th></th>
						<th>Vendor ID</th>
						<th colspan="2">Description</th>
						{foreach from=$label item=branch_name}
							<th>{$branch_name}</th>
							<th>%</th>
						{/foreach}
						<th>Total</th>
						<th>%</th>
					</tr>
				</thead>
				
				{section loop=$table name=i max=$filter_number}
				{assign var=temp value=''}
				{cycle values="c2,c1" assign=row_class}
				<tbody class="fs-08">
					<tr>
						<td rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" class="{$row_class}">	
							{if !$no_header_footer}
								<img src="/ui/expand.gif" onclick="javascript:void(show_detail('{$table[i].vendor_id|default:0}', this));" align=absmiddle> 
							{/if}
							{$smarty.section.i.iteration}
						</td>
						<td rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" class="{$row_class}">{$table[i].vendor_id}</td>
						<td rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" class="{$row_class}">{$table[i].description}</td>
						<td class="r"><b>Qty</b></td>
						{foreach from=$label key=code item=r}
							<td class="c3 r">{$table[i].qty.$code|qty_nf|ifzero:'-'}</td>
							{if $table2.qty.$code >0}
								{assign var=temp value=$table[i].qty.$code/$table2.qty.$code}
							{/if}
							
							<td class="c5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
							{assign var=temp value=''}
						{/foreach}
						<td class="c3 r">{$table[i].qty.total|qty_nf|ifzero:'-'}</td>
						{if $table2.qty.total >0}
							{assign var=temp value=$table[i].qty.total/$table2.qty.total}
						{/if}
						<td class="c5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
						{assign var=temp value=''}
					</tr>
					<tr {if !$sessioninfo.privilege.SHOW_COST}id="tr_vendor_{$table[i].vendor_id}"{/if}>
						<td class="r"><b>S.P</b></td>
						{foreach from=$label key=code item=r}
							<td class="c4 r">{$table[i].$show_type.$code|number_format:2|ifzero:'-'}</td>
							{if $table2.$show_type.$code >0}
								{assign var=temp value=$table[i].$show_type.$code/$table2.$show_type.$code}
							{/if}
							<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
							{assign var=temp value=''}
						{/foreach}
						<td class="c4 r">{$table[i].$show_type.total|number_format:2|ifzero:'-'}</td>
						{if $table2.$show_type.total >0}
							{assign var=temp value=$table[i].$show_type.total/$table2.$show_type.total}
						{/if}
						<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
						 {assign var=temp value=''}
					</tr>
				</tbody>
				{if $sessioninfo.privilege.SHOW_COST}
					<tr id="tr_vendor_{$table[i].vendor_id}">
						<td class="r"><b>C.P</b></td>
						{foreach from=$label key=code item=r}
							<td class="c4 r">{$table[i].cost.$code|number_format:2|ifzero:'-'}</td>
							{if $table2.cost.$code >0}
								{assign var=temp value=$table[i].cost.$code/$table2.cost.$code}
							{/if}
							<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
							{assign var=temp value=''}
						{/foreach}
						<td class="c4 r">{$table[i].cost.total|number_format:2|ifzero:'-'}</td>
						{if $table2.cost.total >0}
							{assign var=temp value=$table[i].cost.total/$table2.cost.total}
						{/if}
						<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
						{assign var=temp value=''}
					</tr>
				{/if}
				{/section}
				<tr>
					<th colspan=3 rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" class="c1 r">Total</th>
					<td class="c1 r"><b>Qty</b></td>
					{foreach from=$label key=code item=r}
						<td class="c3 r">{$table2.qty.$code|qty_nf|ifzero:'-'}</td>
						<td class="c5 r">{if $table2.qty.$code ne ''}100.00%{else}-{/if}</td>
					{/foreach}
					<td class="c3 r">{$table2.qty.total|qty_nf|ifzero:'-'}</td>
					<td class="c5 r">{if $table2.qty.total ne ''}100.00%{else}-{/if}</td>
				</tr>
				<tr>
					<td class="c1 r"><b>S.P</b></td>
					{foreach from=$label key=code item=r}
						<td class="c4 r">{$table2.$show_type.$code|number_format:2|ifzero:'-'}</td>
						<td class="c6 r">{if $table2.$show_type.$code ne ''}100.00%{else}-{/if}</td>
					{/foreach}
					<td class="c4 r">{$table2.$show_type.total|number_format:2|ifzero:'-'}</td>
					<td class="c6 r">{if $table2.$show_type.total ne ''}100.00%{else}-{/if}</td>
				</tr>
				{if $sessioninfo.privilege.SHOW_COST}
					<tr>
						<td class="c1 r"><b>C.P</b></td>
						{foreach from=$label key=code item=r}
							<td class="c4 r">{$table2.cost.$code|number_format:2|ifzero:'-'}</td>
							<td class="c6 r">{if $table2.cost.$code ne ''}100.00%{else}-{/if}</td>
						{/foreach}
						<td class="c4 r">{$table2.cost.total|number_format:2|ifzero:'-'}</td>
						<td class="c6 r">{if $table2.cost.total ne ''}100.00%{else}-{/if}</td>
					</tr>
				{/if}
				</table>
		</div>
	</div>
</div>
{/if}
{if !$no_header_footer}
	{literal}
		<script type="text/javascript">


    		Calendar.setup({
        		inputField     :    "date",     // id of the input field
        		ifFormat       :    "%Y-%m-%d",      // format of the input field
        		button         :    "t_added1",  // trigger for the calendar (button ID)
        		align          :    "Bl",           // alignment (defaults to "Bl")
        		singleClick    :    true
				//,
        		//onUpdate       :    load_data
    		});
		</script>
	{/literal}
{/if}
{include file=footer.tpl}

