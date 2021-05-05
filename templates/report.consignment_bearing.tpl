{*
12/8/2010 3:15:34 PM Alex
- change to 3 sections normal non-discoount, bearing, nett sales
- fixed column width
- summary details change x ->y and y -> x
- summary column change color by price type, discount, nett sales
- find limit of searching
- add show all branches
- make flexible: can show all brand, all department, all vendor
- add show all price types in 1 page
- add department, vendor or brand filter
- add print function
9/19/2011 10:20:50 AM Alex
- fix branch checking bugs
9/23/2011 3:51:34 PM Alex
- change type to brand and vendor
11/23/2011 6:36:12 PM Alex
- change calculation of nett profit
12/8/2011 12:36:39 PM Alex
- add storing temporarily data of department id, brand id, vendor id
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items
06/30/2020 10:41 AM Sheila
- Updated button css.
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
<script>
{/literal}
var phpself = '{$smarty.server.PHP_SELF}';

//department
var dept_id = '{$smarty.request.department_id}';
var rtype = '{$smarty.request.r_type}';
var vendorid = '{$smarty.request.vendor_id}';
var brandid = '{$smarty.request.brand_id}';

{literal}
load_data = function (v){
	if (v=='summary'){
		$('code_id').hide();
	}
	else if (v=='details'){
		$('code_id').show();
	}
}

function print_report(){
	document.f_a.target="_blank";
}

function reset_report(){
	document.f_a.target="";
}

function load_department(bid){

	$('loading').update(_loading_);

	new Ajax.Updater('department_id',phpself,{
		parameters:{
			a: 'ajax_load_department',
			branch_id: bid,
			department_id: dept_id
		},
		onComplete: function(msg){
	        $('loading').update("");
			if ($('department_id').value != "All")
				load_r_type_vendor_brand($('department_id').value);
			else{
		        $('r_type').hide();
		        $('vendor').hide();
		        $('brand').hide();
			}
	        disenable_select();
		},
		evalScripts: true
	});
}

function load_r_type_vendor_brand(dept_id){

	if (dept_id == "All"){
		$('r_type').style.display="none";
		$('vendor').style.display="none";
		$('brand').style.display="none";
	}else if (dept_id !=''){
        $('loading').update(_loading_);

     	// insert new row
		new Ajax.Request(phpself,{
			method:'post',
			parameters: {
				a: 'ajax_load_r_type_vendor_brand',
				branch_id: $('branch_id').value,
				dept_id: dept_id,
				r_type: rtype,
				vendor_id: vendorid,
				brand_id: brandid
			},
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onComplete: function(m){

				var option = eval("("+m.responseText+")");
		        $('loading').update("");
	            //$('r_type_id').update(option.r_type);
				$('r_type').style.display="";
                $('brand_id').update(option.brand);
	            $('vendor_id').update(option.vendor);

				toggle_brand_vendor($('r_type_id').value);
			}
		});
	}
}

function toggle_brand_vendor(r_type){

	if (r_type == "vendor"){
		$('vendor').style.display="";
	    $('brand').style.display="none";
	}
	else if (r_type == "brand"){
        $('vendor').style.display="none";
		$('brand').style.display="";
	}
	else{
        $('vendor').style.display="none";
        $('brand').style.display="none";
	}
	disenable_select();
}


function disenable_select(){
	$$(".disenable_select").each(function(ele,obj){
	    if ($(ele).style.display=="none")
			$(ele).getElementsByTagName("select")[0].disable();
		else
		    $(ele).getElementsByTagName("select")[0].enable();
	});
}


</script>
<style>

.bg_yellow{
	background-color: yellow;
}

.bg_lyellow{
	background-color: #ffffcc;
}

.bg_pink{
	background-color: #cca0c0;
}

.bg_lblue{
	background-color: #aaaaff;
}

.bg_grey{
	background-color: #dddddd;
}

.subtotal{
	background-color: #aaffaa;
}

.detail_width{
	width: 140px;
}

.summary_top{
	width: 95px;
}

.left{
	float: Left;
}

.right{
	float: Right;
}

.div_top_left{
	position: absolute;
	top:0px;
	left:0px;
}

.div_top_right{
	position: absolute;
	top:0px;
	right:0px;
}

.rpt_type tbody:nth-child(even){
   	background-color: #ffffcc;
}

.bold{
	font-weight:bold;
}

</style>
{/literal}
{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e} </li>
{/foreach}
</ul>
{/if}
{if !$no_header_footer}
	<form name="f_a" method=post class="form">
	<input type=hidden name=submit value=1>
	<input type="hidden" name="ajax" value="1">
	<input type="hidden" name="a" value="print_report">
	<input type=hidden name=report_title value="{$report_title}">
	<input name="tmp_department_id" type="hidden" value="{$smarty.request.department_id}">
	<input name="tmp_vendor_id" type="hidden" value="{$smarty.request.vendor_id}">
	<input name="tmp_brand_id" type="hidden" value="{$smarty.request.brand_id}">

	{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id" id="branch_id" onchange="load_department(this.value)">
        <option value="All" {if $smarty.request.branch_id eq "All"}selected {/if}>All</option>
	    {foreach from=$branches item=b}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
		
			{if !$skip_this_branch}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
			{/if}
			
	    {/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input name="branch_id" id="branch_id" value="{$sessioninfo.branch_id}" type="hidden">
	{/if}

    <span class="disenable_select" id="department">
		<b>Department</b>
	    <select name="department_id" id="department_id" onchange="load_r_type_vendor_brand(this.value);">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
    <span class="disenable_select" id="r_type" {if !$smarty.request.department_id or $smarty.request.department_id eq "All"} style="display:none;" {/if}>
	    <b>Type</b>
	    <select name="r_type" id="r_type_id" onchange="toggle_brand_vendor(this.value);">
			<option value="brand" {if $smarty.request.r_type eq "brand"} selected {/if}>Brand</option>
			<option value="vendor" {if $smarty.request.r_type eq "vendor"} selected {/if}>Vendor</option>
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>

    <span class="disenable_select" id="vendor" {if !$smarty.request.r_type or $smarty.request.r_type eq "All" or $smarty.request.r_type eq "brand"}style="display:none;" {/if}>
	    <b>Vendor</b>
	    <select name="vendor_id" id="vendor_id">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>

    <span class="disenable_select" id="brand" {if !$smarty.request.r_type or $smarty.request.r_type eq "All" or $smarty.request.r_type eq "vendor"}style="display:none;" {/if}>
	    <b>Brand</b>
	    <select name="brand_id" id="brand_id">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>
    <span id="loading"></span>
    <p>	</p>
	<b>Year</b> {dropdown name=year values=$years selected=$smarty.request.year key=year value=year}
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Month</b>
	<select name="month">
		<option value="All" {if $smarty.request.month eq 'All'}selected {/if}>All</option>
		{foreach from=$months key=k item=mon}
		    <option value="{$k}" {if $smarty.request.month eq $k}selected {/if}>{$mon}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;

    <b>Show by</b>
	<input type=radio name="show_by" value="summary" id="show_by_s" onchange="load_data(this.value)"
		{if $smarty.request.show_by eq 'summary' or !$smarty.request.show_by} checked {/if} ><label for="show_by_s">Summary</label> &nbsp;&nbsp;&nbsp;&nbsp;

	<input type=radio name="show_by" value="details" id="show_by_p" onchange="load_data(this.value)"
		{if $smarty.request.show_by eq 'details'} checked {/if} ><label for="show_by_p">Price Type</label> &nbsp;&nbsp;&nbsp;&nbsp;

	<!---------Code Type------------>
	<span id="code_id" style="display:none;">
	<b>Discount Code</b>
	<select name="code_type">
	    <option value="All" {if $smarty.request.code_type eq "All"}selected{/if}>All</option>
		{foreach from=$code_type item=code}
		    <option value="{$code.code_type}" {if $smarty.request.code_type eq $code.code_type}selected{/if}>{$code.code_type}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
	&nbsp;&nbsp;&nbsp;&nbsp;

		<button class="btn btn-primary" name=a value="show_report" onclick="reset_report();">{#SHOW_REPORT#}</button>	&nbsp;&nbsp;&nbsp;&nbsp;
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name=a value="output_excel" onclick="reset_report();">{#OUTPUT_EXCEL#}</button>&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
	
		<input class="btn btn-primary" name=print type="submit" value="Print Report" onclick="print_report();">
	</form>
{/if}
{if $table}
	<h2>{$report_title}</h2>
	{$report_cache}
{else}

	-- No Data --

{/if}
<script>

//branch
{if $BRANCH_CODE eq 'HQ'}
	var branch_id=$('branch_id').value;
{else}
    var branch_id='{$sessioninfo.branch_id}';
{/if}

load_data('{$smarty.request.show_by}');
load_department(branch_id);
disenable_select();

</script>
{if !$no_header_footer}
{include file=footer.tpl}
{/if}
