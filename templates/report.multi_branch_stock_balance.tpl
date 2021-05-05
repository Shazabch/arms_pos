{*
4/5/2017 17:00 Qiu Ying
- Enhanced to add print feature

5/4/2017 9:24 AM Justin
- Bug fixed on the border's line is missing while do printing.
- Enhanced to have split out MCode, Artno and Link Code checkbox.

11/7/2018 2:21 PM Andy
- Enhanced to have "Grand Total".

3/6/2019 2:48 PM Andy
- Enhanced to have Page Selection at the bottom of page.

7/3/2018 4:13 PM William
- Removed the brand code of the "brand filter".

06/30/2020 02:25 PM Sheila
- Updated button css.

3/30/2021 4:04 PM Sin Rou
- Add "Previous" and "Next" page button.
*}

{include file='header.tpl'}

<style>
{literal}
td.td_branch_code{
	background-color: #ccffcc;
	text-align: center;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var MULTI_BRANCH_STOCK_BALANCE = {
    f: undefined,
    initialize: function() {
        this.f = document.f_a;
	},
	// select/de-select branch
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	},
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		// select all sku code
		toggle_select_all_opt(this.f['sku_code_list[]'], true);
		this.f.submit();
	},
	page_changed: function(n){
		var sel_id = 'sel_page_num';
		if(n)	sel_id += '-'+n;
		this.f['page'].value = $(sel_id).value;
		this.submit_report();
	},
	previous_page: function(){
		var curr_page = int($('sel_page_num').value);
		this.f['page'].value = curr_page - 1;	
		this.submit_report();
	},
	next_page: function(){
		var curr_page = int($('sel_page_num').value);
		this.f['page'].value = curr_page + 1;	
		this.submit_report();
	},
	do_print: function (){
		//window.print();
		
		var divToPrint = document.getElementById('rpt_contents');
		newWin = window.open("");
		newWin.document.write(divToPrint.outerHTML);
		newWin.print();
		newWin.close();
		
	},
	prompt_grand_total_alert: function(t){
		if(!t)	t = 'alert';
		
		if(t == 'confirm'){
			var v = confirm('SHOW GRAND TOTAL is choose.\nThis will cause the report to take extra long time to load.\n\nAre You Sure?');
			return v;
		}else{
			alert('This will cause the report to take extra long time to load.');
		}
		
	},
	// function when users change "Show Grand Total"
	show_grand_total_changed: function(){
		var inp = this.f['show_grand_total'];
		if(inp.checked){
			if(!this.prompt_grand_total_alert('confirm')){
				inp.checked = false;
			}
		}
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" class="noprint stdframe" method="post" onSubmit="return false;">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="export_excel" />
	<input type="hidden" name="page" value="0" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<div>
			<b>Select Branch By:</b>
			<select id="sel_brn_grp" >
				<option value="">-- All --</option>
				{foreach from=$branch_group.header key=bgid item=bg}
					<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
				{/foreach}
			</select>&nbsp;&nbsp;
			<input class="btn btn-success" type="button" style="width:70px;" value="Select " onclick="MULTI_BRANCH_STOCK_BALANCE.check_branch_by_group(true);" />&nbsp;
			<input class="btn btn-error" type="button" style="width:70px;" value="De-select" onclick="MULTI_BRANCH_STOCK_BALANCE.check_branch_by_group(false);" /><br /><br />
			
			<div id="div_branch_list" style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
				<table>
				{foreach from=$branches key=bid item=b}
					{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
					<tr>
						<td>
							<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />&nbsp;
							<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
						</td>
					</tr>
				{/foreach}
				</table>
			</div>
		</div>
	{/if}
	<span>
		<b>Brand: </b>
		<select name="brand_id">
			<option value="all" {if $smarty.request.brand_id eq 'all' or !isset($smarty.request.brand_id)}selected {/if}>-- All --</option>
			<option value="0" {if isset($smarty.request.brand_id) and $smarty.request.brand_id eq '0'}selected {/if}>UN-BRANDED</option>
			{foreach from=$brands key=bid item=r}
				{if !$brand_group.have_group.$bid}
					<option value="{$bid}" {if $bid eq $smarty.request.brand_id}selected {/if}>{$r.description}</option>
				{/if}
			{/foreach}
			{if $brand_group.header}
				{capture assign=bg_item_padding}{section loop=5 name=i}&nbsp;{/section}{/capture}
				<optgroup label='Brand Group'>
				{foreach from=$brand_group.header key=bgid item=bg}
						<option class="bg" value="{$bgid*-1}"{if $smarty.request.brand_id eq ($bgid*-1)}selected {/if}>{$bg.code} - {$bg.description}</option>
						{foreach from=$brand_group.items.$bgid item=r}
							<option class="bg_item" value="{$r.brand_id}" {if $smarty.request.brand_id eq $r.brand_id}selected {/if}>{$bg_item_padding}{$r.code} - {$r.description}</option>							
						{/foreach}
					{/foreach}
				</optgroup>
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	<span>
		<b>SKU Type: </b>
		<select name="sku_type">
			<option value="">-- All --</option>
			{foreach from=$sku_types item=r}
				<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected{/if}>{$r.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	<span>
		<b>Status: </b>
		<select name="filter_active">
			<option value="">-- All --</option>
			<option value="1" {if $smarty.request.filter_active eq '1' or !isset($smarty.request.filter_active)}selected{/if}>Active</option>
			<option value="-1" {if $smarty.request.filter_active eq '-1'}selected{/if}>Inactive</option>
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	<span>
		<b>Vendor: </b>
		<select name="vendor_id">
			<option value="">-- All --</option>
			{foreach from=$vendors key=vid item=r}
				<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected{/if}>{$r.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<p>
		{include file="category_autocomplete.tpl" all=true}
	</p>
	<p>
		{include file="sku_items_autocomplete_multiple_add2.tpl"}
	</p>
	
	<p>
		<span>
			<b>Stock Filter: </b>
			<select name="stock_filter_type">
				{foreach from=$stock_filter_type_list key=k item=v}
					<option value="{$k}" {if $smarty.request.stock_filter_type eq $k}selected {/if}>{$v}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		<span>
			<b>Sort By: </b>
			<select name="sort_by">
				{foreach from=$order_by_list key=k item=v}
					<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$v}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		
	</p>
	
	{if $BRANCH_CODE eq 'HQ'}
		<p>
			<b>Number of Branch Per Line: </b>
			<input type="text" name="branch_per_line" value="{$smarty.request.branch_per_line|default:$branch_per_line}" />
		</p>
	{/if}
	
	<span>
		<input type="checkbox" name="group_by_sku" value="1" {if $smarty.request.group_by_sku}checked {/if} /> <b>Group by SKU</b>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	<span>
		<input type="checkbox" name="split_field" value="1" {if $smarty.request.split_field}checked {/if} /> <b>Split MCode, ArtNo and {$config.link_code_name}</b>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	<span>
		<input type="checkbox" name="show_grand_total" value="1" {if $smarty.request.show_grand_total}checked {/if} onChange="MULTI_BRANCH_STOCK_BALANCE.show_grand_total_changed();" /> <b>Show Grand Total</b> [<a href="javascript:void(MULTI_BRANCH_STOCK_BALANCE.prompt_grand_total_alert())">?</a>]&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<p>
		<button class="btn btn-primary" onClick="MULTI_BRANCH_STOCK_BALANCE.submit_report();">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary" onClick="MULTI_BRANCH_STOCK_BALANCE.submit_report('excel');">{#OUTPUT_EXCEL#}</button>
		{/if}
		<input class="btn btn-primary" type="button" onclick="MULTI_BRANCH_STOCK_BALANCE.do_print();" value="Print" />&nbsp;
	</p>
	
	<p>
		* Maximum show {$page_limit} item per page.<br />
	</p>
</form>

<div id="rpt_contents">
<style>
{literal}
#rpt_contents table th, #rpt_contents table td {
    border-color: black;
    border-style: solid;
    border-width: 1px 1px 1px 1px;
}

#rpt_contents th, #rpt_contents table th {
    -moz-user-select: none;
    text-align: center;
}

#rpt_contents table {
    border-collapse: collapse;
}
{/literal}
</style>

{if $smarty.request.show_report and !$err}
	<h2>{$report_title}</h2>
	
	{if !$data.data}
		* No Data
	{else}
		{if $data.total_page > 1}
			<div class="noprint">
				{if $data.curr_page >= 1}
					<input class="btn btn-primary" type="button" value="Previous" onclick="MULTI_BRANCH_STOCK_BALANCE.previous_page()"/>&nbsp;
				{/if}
				<b>Go To Page: </b>
				<select id="sel_page_num" onChange="MULTI_BRANCH_STOCK_BALANCE.page_changed();">
					{section loop=$data.total_page name=s_page}
						<option value="{$smarty.section.s_page.index}" {if $smarty.request.page eq $smarty.section.s_page.index}selected {/if}>{$smarty.section.s_page.iteration}</option>
					{/section}
				</select>
				{if $data.curr_page < $data.total_page - 1}
					<input class="btn btn-primary" type="button" value="Next" onclick="MULTI_BRANCH_STOCK_BALANCE.next_page()"/>&nbsp;
				{/if}	
			</div>
		{/if}
		
		{include file='report.multi_branch_stock_balance.table.tpl'}
		
		{if $data.total_page > 1}
			<br />
			<div class="noprint">
				{if $data.curr_page >= 1}
					<input class="btn btn-primary" type="button" value="Previous" onclick="MULTI_BRANCH_STOCK_BALANCE.previous_page()"/>&nbsp;
				{/if}
				<b>Go To Page: </b>
				<select id="sel_page_num-2" onChange="MULTI_BRANCH_STOCK_BALANCE.page_changed('2');">
					{section loop=$data.total_page name=s_page}
						<option value="{$smarty.section.s_page.index}" {if $smarty.request.page eq $smarty.section.s_page.index}selected {/if}>{$smarty.section.s_page.iteration}</option>
					{/section}
				</select>
				{if $data.curr_page < $data.total_page - 1}
					<input class="btn btn-primary" type="button" value="Next" onclick="MULTI_BRANCH_STOCK_BALANCE.next_page()"/>&nbsp;
				{/if}		
			</div>
		{/if}
	{/if}
{/if}
</div>

<script type="text/javascript">
{literal}
MULTI_BRANCH_STOCK_BALANCE.initialize();
{/literal}
</script>

{include file='footer.tpl'}