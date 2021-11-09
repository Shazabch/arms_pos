{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

6/5/2014 11:54 AM Justin
- Bug fixed of some info were missing after new method applied.

5/21/2018 2:00 pm Kuan Yeh
- Bug fixed of calendar method shown on excel export  

06/30/2020 11:11 AM Sheila
- Updated button css.
- Added padding to table
*}

{include file='header.tpl'}

{if !$no_header_footer}

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
<style>
#show_sku {
	padding:10px 0;
}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.fm_data{
    background-color: #fcf;
}

.use_grn_cost{
	background-color: #fcf;
}

.old_sku_items{
	background-color: #D0D0D0;
	padding: 4px;
	border-radius: 2px;
}
</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
var show_tran_count = int('{$show_tran_count}');

{literal}
function init_calendar(){
    Calendar.setup({
        inputField     :    "inp_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_date_from",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "inp_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_date_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
}

function show_sub(root_id){
	document.f.cat_id.value = root_id
	//document.f.a.value = 'category';
	document.f.submit();
}

function expand_sub(root_id, indent, el, new_si_root_per, old_si_root_per, is_fresh_market){
	if(el.src.indexOf('clock')>0)   return;
	
	el.onClick='';
	el.src = '/ui/clock.gif';
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_category&ajax=1&cat_id="+root_id+"&indent="+indent+'&new_si_root_per='+new_si_root_per+'&old_si_root_per='+old_si_root_per+'&is_fresh_market='+is_fresh_market,
	{
		onComplete: function(e) {
		    if(is_fresh_market){
                new Insertion.After($('tbody_fm_row-'+root_id), e.responseText);
			}else{
                new Insertion.After($('tbody_cat_row-'+root_id), e.responseText);
			}
			
			el.remove();
		},
	});
}

function show_sku(root_id, img, is_fresh_market, direct_under_cat){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';
	
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	var params = Form.serialize(document.f)+"&a=ajax_load_sku&cat_id="+root_id+'&is_fresh_market='+is_fresh_market;
	if(direct_under_cat)	params += '&direct_under_cat=1';
	
	new Ajax.Updater('show_sku',phpself,{
		parameters: params,
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}

function toggle_fm_row(ele){
	var show = (ele.src.indexOf('expand')>=0) ? true : false;
	
	$$('#tbl_cat tr.is_fresh_market_row').each(function(r){
		if(show)    $(r).show();
		else    $(r).hide();
	});
	
	if(show)  ele.src = '/ui/collapse.gif';
	else    ele.src = '/ui/expand.gif';
}

function export_itemise_info(root_id, is_fresh_market, direct_under_cat){
	document.f.is_itemise_export.value=1;
	document.f.itemise_cat_id.value=root_id;
	document.f.itemise_is_fresh_market.value=is_fresh_market;
	document.f.itemise_direct_under_cat.value=direct_under_cat;
	document.f.submit();

	document.f.is_itemise_export.value=0;
	document.f.itemise_cat_id.value=0;
	document.f.itemise_is_fresh_market.value=0;
	document.f.itemise_direct_under_cat.value=0;
}

{/literal}
</script>

{/if}

<div class="stdframe" style="display:none;">
	<form name="f_export_itemise_info" method="post">
		<input type="hidden" name="a" value="export_itemise_info" />
		<input type="hidden" name="html" value="" />
	</form>
</div>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<div class="noprint stdframe">
			<form name="f" method="get">
			<input type="hidden" name="subm" value="1" />
			<input type="hidden" name="is_itemise_export" value="0" />
			<input type="hidden" name="itemise_cat_id" value="0" />
			<input type="hidden" name="itemise_is_fresh_market" value="0" />
			<input type="hidden" name="itemise_direct_under_cat" value="0" />
			<input type="hidden" name="cat_id" value="{$smarty.request.cat_id}" />
			
				<div class="row">
					<div class="col-md-3">
						{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label mt-2">Branch</b>
					<select class="form-control" name="branch_id">
					<option value=''>-- All --</option>
					{foreach from=$branches key=bid item=r}
					
						{if $config.sales_report_branches_exclude}
						{if in_array($r.code,$config.sales_report_branches_exclude)}
						{assign var=skip_this_branch value=1}
						{else}
						{assign var=skip_this_branch value=0}
						{/if}
						{/if}
					
						{if !$branches_group.have_group.$bid and !$skip_this_branch}
							<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
					{/foreach}
					{if $branches_group.header}
						<optgroup label='Branch Group'>
						{foreach from=$branches_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
								{foreach from=$branches_group.items.$bgid item=r}
								
									{if $config.sales_report_branches_exclude}
									{if in_array($r.code,$config.sales_report_branches_exclude)}
									{assign var=skip_this_branch value=1}
									{else}
									{assign var=skip_this_branch value=0}
									{/if}
									{/if}
								
									{if !$skip_this_branch}
									<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
									{/if}
									
								{/foreach}
							{/foreach}
						</optgroup>
					{/if}
					</select>
				{/if}
					</div>
			
				<div class="col-md-3">
					<b class="form-label mt-2">SKU Type</b>
				<select class="form-control" name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type item=r}
						<option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
					{/foreach}
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label mt-2">Date From</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label mt-2">To</b>
				<div class="form-inline">
					<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=23 />
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
				</div>
				</div>
				
				<div class="col-md-3">
					<div class="form-label form-inline mt-2">
						<input type="checkbox" name="by_monthly" value="1" {if $smarty.request.by_monthly}checked {/if} />
					<b>&nbsp;Group Monthly</b>
					</div>
				</div>
				
				<div class="col-md-3">
					{if $sessioninfo.level>=9999}
					<div class="form-label form-inline mt-2">
						<input type="checkbox" name="use_report_server" value="1" {if $smarty.request.use_report_server or !$smarty.request.subm}checked {/if} />
					<b>&nbsp;Use report server</b>
					</div>
				{else}
					<input type="hidden" name="use_report_server" value="1" />
				{/if}
				
				</div>

				<div class="col-md-3">
					<b class="form-label mt-2">Report Type: </b>
				<input type="radio" value="qty" name="report_type" {if $smarty.request.report_type eq 'qty' or !$smarty.request.report_type}checked {/if} /> Sales Qty 
				<input type="radio" value="amt" name="report_type" {if $smarty.request.report_type eq 'amt'}checked {/if} /> Sales Amount 
				<br>
				{if $sessioninfo.show_report_gp}
				<input type="radio" value="gp" name="report_type" {if $smarty.request.report_type eq 'gp'}checked {/if} /> Gross Profit 
				<input type="radio" value="gp_pct" name="report_type" {if $smarty.request.report_type eq 'gp_pct'}checked {/if} /> GP (%) 
				{/if}
				</div>
			
				<div class="col-md-3">
					<b class="form-label mt-2">Item Age</b>
				<select class="form-control" name="item_age">
					<option value="1" {if $smarty.request.item_age eq 1}selected{/if}>1 Month</option>
					<option value="3" {if $smarty.request.item_age eq 3}selected{/if}>3 Months</option>
				
					<option value="6" {if $smarty.request.item_age eq 6}selected{/if}>6 Months</option>
					<option value="9" {if $smarty.request.item_age eq 9}selected{/if}>9 Months</option>
					<option value="12" {if $smarty.request.item_age eq 12}selected{/if}>1 Year</option>
				</select>
				</div>
				
			<div class="col-md-3">
				<div class="form-label form-inline">
					<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
				</div>
			</div>
				
				</div>
				
				 <input class="btn btn-primary" type="submit" value='Show Report' /> 
				
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-info" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
					{if $root_cat_info.level >= 2}
						<input type="checkbox" name="include_sub_cat" value="1" {if $smarty.request.include_sub_cat}checked {/if} />
						Export with all sub-category
					{/if}
				{/if}
			</form>
			</div>
	</div>
</div>
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
{if !$no_header_footer}
    <div class="card mx-3">
		<div class="card-body">
			<p>
				&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
				{if $root_cat_info}
					{foreach from=$root_cat_info.cat_tree_info item=ct}
						<a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
					{/foreach}
					{$root_cat_info.description} /
				{/if}
				<div class="alert alert-primary rounded">
					<ul >
						<li>Click on a sub-category for further detail. Click <img src="/ui/icons/table.png" align="absmiddle" />  to display SKU in the category.</li>
						<!--li>Transaction count can only view at line/department.</li-->
						<li>Rows that with background color <span class="old_sku_items" width="15">&nbsp;test&nbsp;</span> indicate as old items.</li>
					</ul>
				</div>
			</p>
		</div>
	</div>
{/if}

{if !$tb}
	{if $smarty.request.subm}No Data{/if}
{else}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="tb sortable table mb-0 text-md-nowrap  table-hover"  id="tbl_cat">
				<thead class="bg-gray-100">
					<tr>
						<th align="left">&nbsp;</th>
				
						{assign var=lasty value=0}
						{assign var=lastm value=0}
						{foreach from=$uq_cols key=dt item=d}
							<th valign="bottom">
							{if $smarty.request.by_monthly}
								{if $lasty ne $d.y}
									<span class="small">{$d.y}</span><br />
									{assign var=lasty value=$d.y}
								{/if}
								{$d.m|str_month|truncate:3:''}
							{else}
								{if $lastm ne $d.m or $lasty ne $d.y}
									<span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
									{assign var=lastm value=$d.m}
									{assign var=lasty value=$d.y}
								{/if}
								{$d.d}
							{/if}
							</th>
						{/foreach}
						<th>Total<br />Qty</th>
						<th>Amount</th>
						{*if $show_tran_count}
							<th>Tran.<br />Count</th>
							<th>Buying<br />Power</th>
						{/if*}
						
						{if $sessioninfo.show_cost}
							<th>Cost</th>
						{/if}
						{if $sessioninfo.show_report_gp}
							<th>GP</th>
							<th>GP(%)</th>
						{/if}
						<th>Contrib<br>(%)</th>
					</tr>
				</thead>
			
				{include file='report.category_old_vs_new_sku_items.table.tpl' curr_cat_info=$root_cat_info}
				{assign var=curr_root_cat_id value=$root_cat_info.id}
				
				<tr class=sortbottom>
					<th align="right">Sub Total</th>
					
					{foreach from=$uq_cols key=dt item=d}
						{assign var=fmt value="%0.2f"}
						{if $smarty.request.report_type eq 'qty'}
							{assign var=fmt value="qty"}
							{assign var=new_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.new.qty}
							{assign var=old_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.old.qty}
						{elseif $smarty.request.report_type eq 'amt'}
							{assign var=new_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.new.amt}
							{assign var=old_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.old.amt}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
							{assign var=new_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.new.amt-$tb_total.$curr_root_cat_id.sub_total.$dt.new.cost}
							{assign var=old_si_val value=$tb_total.$curr_root_cat_id.sub_total.$dt.old.amt-$tb_total.$curr_root_cat_id.sub_total.$dt.old.cost}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
							{assign var=fmt value="%0.2f%%"}
							{if $tb_total.$curr_root_cat_id.sub_total.$dt.new.amt eq 0 && $tb_total.$curr_root_cat_id.sub_total.$dt.old.amt eq 0}
								{assign var=new_si_val value=''}
								{assign var=old_si_val value=''}
							{else}
								{if $tb_total.$curr_root_cat_id.sub_total.$dt.new.amt ne 0}
									{assign var=new_si_gp value=$tb_total.$curr_root_cat_id.sub_total.$dt.new.amt-$tb_total.$curr_root_cat_id.sub_total.$dt.new.cost}
									{assign var=new_si_val value=$new_si_gp/$tb_total.$curr_root_cat_id.sub_total.$dt.new.amt*100}
								{/if}
								{if $tb_total.$curr_root_cat_id.sub_total.$dt.old.amt ne 0}
									{assign var=old_si_gp value=$tb_total.$curr_root_cat_id.sub_total.$dt.old.amt-$tb_total.$curr_root_cat_id.sub_total.$dt.old.cost}
									{assign var=old_si_val value=$old_si_gp/$tb_total.$curr_root_cat_id.sub_total.$dt.old.amt*100}
								{/if}
							{/if}
						{/if}
						{capture assign=new_si_tooltip}
							Qty:{$tb_total.$curr_root_cat_id.sub_total.$dt.new.qty|value_format:'qty'}  /  Amt:{$tb_total.$curr_root_cat_id.sub_total.$dt.new.amt|string_format:'%.2f'}  /  Cost:{$tb_total.$curr_root_cat_id.sub_total.$dt.new.cost|number_format:$config.global_cost_decimal_points}
						{/capture}
						{capture assign=old_si_tooltip}
							Qty:{$tb_total.$curr_root_cat_id.sub_total.$dt.old.qty|value_format:'qty'}  /  Amt:{$tb_total.$curr_root_cat_id.sub_total.$dt.old.amt|string_format:'%.2f'}  /  Cost:{$tb_total.$curr_root_cat_id.sub_total.$dt.old.cost|number_format:$config.global_cost_decimal_points}
						{/capture}
						<td class="small" align="right">
							<span title="{$new_si_tooltip}">{$new_si_val|value_format:$fmt|ifzero:'-'}</span>
							<div class="old_sku_items" title="{$old_si_tooltip}">{$old_si_val|value_format:$fmt|ifzero:'-'}</div>
						</td>
					{/foreach}
			
					<td class="small" align="right">
						{$tb_total.$curr_root_cat_id.sub_total.new.qty|value_format:'qty':'-'}
						<div class="old_sku_items">{$tb_total.$curr_root_cat_id.sub_total.old.qty|value_format:'qty':'-'}</div>
					</td>
					<td class="small" align="right">
						{$tb_total.$curr_root_cat_id.sub_total.new.amt|value_format:'%0.2f':'-'}
						<div class="old_sku_items">{$tb_total.$curr_root_cat_id.sub_total.old.amt|value_format:'%0.2f':'-'}</div>
					</td>
					{*if $show_tran_count}
						<td align="right">-</td><td align="right">-</td>
					{/if*}
					{if $sessioninfo.show_cost}
						<td class="small" align="right">
							{$tb_total.$curr_root_cat_id.sub_total.new.cost|value_format:'%0.2f':'-'}
							<div class="old_sku_items">{$tb_total.$curr_root_cat_id.sub_total.old.cost|value_format:'%0.2f':'-'}</div>
						</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=new_si_gp value=$tb_total.$curr_root_cat_id.sub_total.new.amt-$tb_total.$curr_root_cat_id.sub_total.new.cost}
						{assign var=old_si_gp value=$tb_total.$curr_root_cat_id.sub_total.old.amt-$tb_total.$curr_root_cat_id.sub_total.old.cost}
						<td class="small" align="right">
							{$new_si_gp|value_format:"%0.2f":'-'}
							<div class="old_sku_items">{$old_si_gp|value_format:"%0.2f":'-'}</div>
						</td>
						{if $tb_total.$curr_root_cat_id.sub_total.new.amt>0}
							{assign var=new_si_gp_per value=$new_si_gp/$tb_total.$curr_root_cat_id.sub_total.new.amt*100}
						{else}
							{assign var=new_si_gp_per value=0}
						{/if}
						{if $tb_total.$curr_root_cat_id.sub_total.old.amt>0}
							{assign var=old_si_gp_per value=$old_si_gp/$tb_total.$curr_root_cat_id.sub_total.old.amt*100}
						{else}
							{assign var=old_si_gp_per value=0}
						{/if}
						<td class="small" align="right">
							{$new_si_gp_per|value_format:'%0.2f%%':'-'}
							<div class="old_sku_items">{$old_si_gp_per|value_format:'%0.2f%%':'-'}</div>
						</td>
					{/if}
				</tr>
				<tr class=sortbottom>
					<th align="right">Total</th>
					
					{foreach from=$uq_cols key=dt item=d}
						{assign var=fmt value="%0.2f"}
						{if $smarty.request.report_type eq 'qty'}
							{assign var=fmt value="qty"}
							{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.qty}
						{elseif $smarty.request.report_type eq 'amt'}
							{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.amt}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
							{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.amt-$tb_total.$curr_root_cat_id.total.$dt.cost}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
							{assign var=fmt value="%0.2f%%"}
							{if $tb_total.$curr_root_cat_id.total.$dt.amt eq 0}
								{assign var=val value=''}
							{else}
								{assign var=gp value=$tb_total.$curr_root_cat_id.total.$dt.amt-$tb_total.$curr_root_cat_id.total.$dt.cost}
								{assign var=val value=$new_si_gp/$tb_total.$curr_root_cat_id.total.$dt.amt*100}
							{/if}
						{/if}
						{capture assign=tooltip}
							Qty:{$tb_total.$curr_root_cat_id.total.$dt.qty|value_format:'qty'}  /  Amt:{$tb_total.$curr_root_cat_id.total.$dt.amt|string_format:'%.2f'}  /  Cost:{$tb_total.$curr_root_cat_id.total.$dt.cost|number_format:$config.global_cost_decimal_points}
						{/capture}
						<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt|ifzero:'-'}</td>
					{/foreach}
			
					<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.qty|value_format:'qty':'-'}</td>
					<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.amt|value_format:'%0.2f':'-'}</td>
					{*if $show_tran_count}
						<td align="right">-</td><td align="right">-</td>
					{/if*}
					{if $sessioninfo.show_cost}
						<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.cost|value_format:'%0.2f':'-'}</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=gp value=$tb_total.$curr_root_cat_id.total.total.amt-$tb_total.$curr_root_cat_id.total.total.cost}
						<td class="small" align="right">{$gp|value_format:"%0.2f":'-'}</td>
						{if $tb_total.$curr_root_cat_id.total.total.amt>0}
							{assign var=gp_per value=$gp/$tb_total.$curr_root_cat_id.total.total.amt*100}
						{else}
							{assign var=gp_per value=0}
						{/if}
						<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
					{/if}
				</tr>
			</table>
		</div>
	</div>
</div>

<div id="show_sku"></div>
{/if}

{include file='footer.tpl'}

{if !$no_header_footer}
	<script>
		{literal}

			init_calendar();

			{/literal}
	</script>
{/if}