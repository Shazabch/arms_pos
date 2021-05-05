{*
1/24/2011 5:13:20 PM Alex
- change branch group id from bg,$id to -$id

2/23/2011 2:37:33 PM Justin
- Modified the report note from 1 month to 3 months.

9/21/2011 2:40:47 PM Alex
- change span to div to avoid html error

10/14/2011 11:03:32 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.
*}

{include file=header.tpl}
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

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var filter = "{$filter}";
var branch_id = [];

{foreach from=$smarty.request.branch_id item=b}
	branch_id.push('{$b}');
{/foreach}
var serialized_bid = branch_id;
var start_date = "{$start_date}";
var end_date = "{$end_date}";

{literal}

function load_child(code,obj,ln)
{
	var src = obj.src;
	if(src.indexOf('clock')>0){
		alert('Please wait..');
		return;
	}
	obj.onClick = '';
	obj.src = '/ui/clock.gif';
	parent_id = obj.parentNode.parentNode.parentNode.id;

	var tobj = document.getElementsByTagName('tbody');
	var new_tbody_id = parent_id+'_';
	var found = false;
	
	var under_num = 0;
	for(i=0; i<new_tbody_id.length; i++){
		if(new_tbody_id[i]=='_'){
            under_num++;
		}
	}

	var max_tbody_length = new_tbody_id.length+4;
	
	for(var i=0; i<tobj.length; i++){
	    var tid = tobj[i].id;

	    if(tid.indexOf(new_tbody_id)==0){
	        found = true;
			if(src.indexOf('expand')>0){
			    this_under_num = 0;
			    for(j=0; j<tid.length; j++){
					if(tid[j]=='_'){
			            this_under_num++;
					}
				}

				if(this_under_num<=under_num){
                    tobj[i].style.display='';
				}
			}else{
                tobj[i].style.display='none';
                objimg = tobj[i].getElementsByTagName('img');
                if(objimg.length>=3){
                    if(objimg[2].src.indexOf('collapse')>0){
                        objimg[2].src = 'ui/expand.gif';
					}
				}
			}
		}
	}

	if(found){
	    if(src.indexOf('expand')>0){
            obj.src = 'ui/collapse.gif';
		}else{
            obj.src = 'ui/expand.gif';
		}
		return;
	}

	var p = $H({
		ajax: 1,
		a: 'load_child',
		code: code,
		filter: filter,
		branch_id: branch_id,
		start_date: start_date,
		end_date: end_date,
		ln: ln,
		parent_id: parent_id
	});

	/*new Ajax.Request(phpself+'?'+Form.serialize(document.report_form)+"&"+p.toQueryString(),
	{
		onComplete: function(e) {

			new Insertion.After(obj.parentNode.parentNode.parentNode, e.responseText);
			//$('r_'+m+'_'+qn), e.responseText);
			//obj.remove();
			obj.src = 'ui/collapse.gif';
		},
	});*/

	new Ajax.Request(phpself,
	{
	    parameters:{
            ajax: 1,
			a: 'load_child',
			code: code,
			filter: filter,
			'branch_id[]': branch_id,
			start_date: start_date,
			end_date: end_date,
			ln: ln,
			parent_id: parent_id
		},
		onComplete: function(e) {

			new Insertion.After(obj.parentNode.parentNode.parentNode, e.responseText);
			//$('r_'+m+'_'+qn), e.responseText);
			//obj.remove();
			obj.src = 'ui/collapse.gif';
		},
	});
}

function load_sku(code,el){
    document.sku_form.hidden_branch_id.value = $A(serialized_bid).toString();
	document.sku_form.hidden_code.value = code;
    document.f_export_itemise_info.hidden_branch_id.value = $A(serialized_bid).toString();
	document.f_export_itemise_info.hidden_code.value = code;
	document.sku_form.submit();
}

function checkBranch(el){
	var obj = document.report_form.elements["branch_id[]"];
	for(var i=0; i<obj.length; i++){
		obj[i].checked = el.checked;
	}
}

function load_cat(url){
	new Ajax.Updater('span_cat',url);
}

function load_cat2(root_id){
    load_cat('?a=load_cat&ajax=1&root_id='+root_id)
}

function change_input_all(input_name,el){
	if(!el.checked){
		document.report_form.elements[input_name].checked=false;
	}else{
        var obj = document.report_form.elements[el.name];
        for(var i=0; i<obj.length; i++){
			if(!obj[i].checked){
				return;
			}
		}
		document.report_form.elements[input_name].checked=true;
	}
}

function export_itemise_info(){
	document.f_export_itemise_info.a.value='export_itemise_info';
	//document.f_export_itemise_info.html.value=$('show_sku').innerHTML;
	document.f_export_itemise_info.target = 'if_itemise';
	//document.f_export_itemise_info.target = '_blank';
	document.f_export_itemise_info.submit();	
	curtain(false);
}
{/literal}
</script>

<style>
{literal}
.c1 { background:#ff9; }
.c2 { background:#fff; }

.c3 { background:#9f9; }
.c3_2 { background:#70ffc0; }

.c4 { background:#ffccff; }
.c4_2 { background:#ffa0f0; }
.c5 { background:#6699ff; }
.c5_2 { background:#00d0ff; }

.r1 { background:#c0fff0;}
.r2 { background:#f0f0f0;}
.r3 { background:#33ff00;}
.r4 { background:#fff0c0;}

.h1 { background:#00f0ff !important;}
{/literal}
</style>
{/if}

<div class="stdframe" style="display:none;">
	<form name="f_export_itemise_info" method="post">
		<input type="hidden" name="hidden_filter" value="{$filter}">
		<input type="hidden" name="hidden_branch_id">
		<input type="hidden" name="hidden_start_date" value="{$start_date}">
		<input type="hidden" name="hidden_end_date" value="{$end_date}">
		<input type="hidden" name="hidden_code">
		<input type="hidden" name="a" value="export_itemise_info" />
		<input type="hidden" name="ajax" value="1" />
	</form>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=if_itemise></iframe>

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}
{if !$no_header_footer}
<form method=post class=form name=report_form>
<input type=hidden name=report_title value="{$report_title}">
<p>
&nbsp;<b>Start from</b>&nbsp;
<input type=text name=date id=date value="{$smarty.request.date|ifzero:$smarty.now|date_format:'%Y-%m-%d'}" size=12>
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>SKU Type</b>
<select name="sku_type">
<option value="all">-- All --</option>
{foreach from=$sku_type item=r}
	<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code} selected {/if}>{$r.description}</option>
{/foreach}
</select>
</p>
<p>
<div id="span_cat">
{include file="category_multiple.tpl"}
</div>
</p>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>
	<input type=checkbox onChange="checkBranch(this)" name="all_branch" {if $smarty.request.all_branch} checked {/if}> <b>All</b>
	{*foreach from=$branches item=r}
	<input type=checkbox name=branch_id[] value="{$r.id}" {if $smarty.request.branch_id}{if in_array($r.id,$smarty.request.branch_id)} checked {/if}{/if} onChange="change_input_all('all_branch',this)"> {$r.code}
	{/foreach*}
	{foreach from=$branches item=b}
        {if !$branch_group.have_group[$b.id]}
        <input type=checkbox name=branch_id[] value="{$b.id}" {if $smarty.request.branch_id}{if in_array($b.id,$smarty.request.branch_id)} checked {/if}{/if} onChange="change_input_all('all_branch',this)"> {$b.code}
        {/if}
    {/foreach}
    {if $branch_group.header}
		{foreach from=$branch_group.header item=r}
		    {capture assign=bgid}{$r.id*-1}{/capture}
			<input type=checkbox name=branch_id[] value="{$bgid}" {if $smarty.request.branch_id}{if in_array($bgid,$smarty.request.branch_id)} checked {/if}{/if} onChange="change_input_all('all_branch',this)"> {$r.code}
		{/foreach}
	{/if}
{/if}

&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>

&nbsp;&nbsp;&nbsp;&nbsp;

<input type=hidden name=submit value=1>
<button name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>
Note: Report will Shown 3 months
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<form name=sku_form method=post action="?a=view_sku_by_second_last&ajax=1" target="_blank">
<input type=hidden name=hidden_filter value="{$filter}">
<input type=hidden name=hidden_branch_id>
<input type=hidden name=hidden_start_date value="{$start_date}">
<input type=hidden name=hidden_end_date value="{$end_date}">
<input type=hidden name=hidden_code>
</form>
<h2>{$report_title}<!--<br>From: {$start_date} to {$end_date}--></h2>
<table class=report_table width=100%>
<tr class=header>
	<th rowspan=2>Category</th>
	{foreach from=$label item=lbl}
	    <th colspan=2>{$lbl}</th>
	{/foreach}
	<th colspan=2>Total</th>
</tr>
<tr class=header>
    {foreach from=$label item=lbl}
        <th>Qty</th>
        <th>Sales</th>
	{/foreach}
	<th>Qty</th>
    <th>Sales</th>
</tr>

{foreach from=$table key=code item=c}
{cycle values="c2,c1" assign=row_class}
<tbody id="r_{$code}">
<tr>
	<td class="{$row_class}" nowrap>
	    {if $code eq 0}
	        {$category.$code.name}
	    {else}
	        {if !$no_header_footer}
	        <img src='/ui/icons/table.png' onclick="load_sku({$code},this);" align=absmiddle>
	        {/if}
	        {$category.$code.name}
	        {if !$no_header_footer}
        	<img src="/ui/expand.gif" onclick="load_child({$code},this,1);" align=absmiddle>
        	{/if}
        {/if}
	</td>
	{assign var=needchange value=1}
    {foreach from=$label key=lbl item=b}
        {if $needchange eq 1}
        	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
            {assign var=needchange value=0}
            <td class="{$amt_class} r">{$category.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
        	<td class="{$amt_class} r">{$category.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
        {else}
            {assign var=needchange value=1}
            <td class="{$row_class} r">{$category.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
        	<td class="{$row_class} r">{$category.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
        {/if}
    {/foreach}
    {if $row_class eq c1}{assign var=total_class value=r3}{else}{assign var=total_class value=r4}{/if}
    <td class="{$total_class} r">{$category.$code.qty.total|qty_nf|ifzero:'-'}</td>
    <td class="{$total_class} r">{$category.$code.amount.total|number_format:2|ifzero:'-'}</td>

</tr>
</tbody>
{/foreach}
{cycle values="c2,c1" assign=row_class}
<tbody id="r_total">
<tr>
	<td class="{$row_class} r">Total</td>
    {assign var=needchange value=1}
    {foreach from=$label key=lbl item=b}
        {if $needchange eq 1}
        	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
            {assign var=needchange value=0}
            <td class="{$amt_class} r">{$category.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
        	<td class="{$amt_class} r">{$category.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
        {else}
            {assign var=needchange value=1}
            <td class="{$row_class} r">{$category.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
        	<td class="{$row_class} r">{$category.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
        {/if}
    {/foreach}
    {if $row_class eq c1}{assign var=total_class value=r3}{else}{assign var=total_class value=r4}{/if}
    <td class="{$total_class} r">{$category.total.qty.total|qty_nf|ifzero:'-'}</td>
    <td class="{$total_class} r">{$category.total.amount.total|number_format:2|ifzero:'-'}</td>
</tr>
</tbody>
</table>
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

