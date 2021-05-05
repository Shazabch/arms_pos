{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

06/30/2020 11:17 AM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
/*
function load_type(m, p1, p2, obj)
{
	obj.onClick = '';
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&a=load_type&ajax=1&m="+m+"&p1="+p1+"&p2="+p2,
	{
		onComplete: function(e) {
			new Insertion.After($('r_'+m+"_"+p1+"_"+p2), e.responseText);
			obj.remove();
		},
	});
}
*/
function load_child(m, obj, qn, q)
{
    parent_id = obj.parentNode.parentNode.parentNode.id;
    var under_num = 0;
	for(i=0; i<parent_id.length; i++){
		if(parent_id[i]=='_'){
            under_num++;
		}
	}
	
	var tbody_obj = document.getElementsByTagName('tbody');
	var found = false;
	for(var i=0; i<tbody_obj.length; i++){
		if(tbody_obj[i].id.indexOf(parent_id+'_')>=0){
                if(obj.src.indexOf('expand')>0){
                    var check_under_num = 0;
				    for(j=0; j<tbody_obj[i].id.length; j++){
						if(tbody_obj[i].id[j]=='_'){
				            check_under_num++;
						}
					}
					if(check_under_num-under_num==1){
						tbody_obj[i].style.display = '';
					}
				}else{
		            tbody_obj[i].style.display = 'none';
		             var img_obj = tbody_obj[i].getElementsByTagName('img');
					 img_obj[1].src = '/ui/expand.gif';
				}
            found = true;
		}
	}
	
	if(found){
        if(obj.src.indexOf('expand')>0){
            obj.src = '/ui/collapse.gif';
		}else{
            obj.src = '/ui/expand.gif';
		}
		return;
	}
	
	obj.onClick = '';
	obj.src = '/ui/clock.gif';
	
	var p = $H({
		ajax: 1,
		a: 'load_child',
		m: m,
		q: q,
		qn: qn,
		parent_id: parent_id
	});
	
	
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&"+p.toQueryString(),
	{
		onComplete: function(e) {
			
			new Insertion.After(obj.parentNode.parentNode.parentNode, e.responseText);
			//$('r_'+m+'_'+qn), e.responseText);
			//obj.remove();
			obj.src = '/ui/collapse.gif';
		},
	});
}
</script>
<style>
.coutright {
	background:#eff;
	font-size:10px;
	color:#00f;
}
.cconsign {
	background:#efa;
	font-size:10px;
	color:#00f;
}
.cmth {
	font-weight:bold;
}
.ccategory {
	background:#fc9;

}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
{/literal}
{/if}
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
<form name=f method=post class=form>
<input type=hidden name=report_title value="{$report_title}">
<b>Year</b> 
{dropdown name=year values=$years selected=$smarty.request.year key=year value=year}&nbsp;&nbsp;&nbsp;&nbsp; 
<b>Month</b>
{*dropdown name=month values=$months is_assoc=1 selected=$smarty.request.month*}
<select name="month">
	{foreach from=$months key=k item=r}
    <option value="{$k}" {if $smarty.request.month eq $k}selected{/if}>{$r}</option>
  {/foreach}
  </select>

 &nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> 
	<select name="branch_id">
	    <option value="">-- All --</option>
	    {foreach from=$branches item=b}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
			
	        {if !$branch_group.have_group[$b.id] and !$skip_this_branch}
				<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code} - {$b.description}</option>
	        {/if}
	        
	    {/foreach}
	    {if $branch_group.header}
	        <optgroup label="Branch Group">
				{foreach from=$branch_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
		    	    {foreach from=$branch_group.items.$bgid item=r}
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
<br>
{else}
<br>
{/if}
<b>Department</b>
<input type="checkbox" onclick="toggle_dept(this);" id=dept_all> All<br>
<div id="department_option">
{foreach from=$departments item=dept}
<div style="float:left;"><input type=checkbox name=department_id[] value={$dept.id} {if $smarty.request.department_id}{if in_array($dept.id,$smarty.request.department_id)}checked{/if}{/if} onclick="toggle_dept()"> {$dept.description}</div>
{/foreach}
</div>
<br style="clear:both;">
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>Note: Report Maximum Shown 24 Months
</form>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
<!--Year: {$smarty.request.year} &nbsp;&nbsp;&nbsp;&nbsp;
Month: {$months[$smarty.request.month]} &nbsp;&nbsp;&nbsp;&nbsp;
Branch: {$branch_code} &nbsp;&nbsp;&nbsp;&nbsp;
Department:
{assign var=c value=1}
{foreach from=$departments item=dept}
{if $smarty.request.department_id}{if in_array($dept.id,$smarty.request.department_id)}
{if $c ne 1} , {/if}{assign var=c value=2}
{$dept.description} {/if}{/if}
{/foreach}-->
</h2>
<table class="report_table small_printing" width=100% cellpadding=0 cellspacing=0>
<tr class=header>
	<th rowspan=2>Month</th>
	{foreach from=$data key=year item=r}
	<th colspan=4>{$year}</th>
	{/foreach}
</tr>
<tr class=header>
	{foreach from=$data key=year item=r}
		<th>Amount</th>
		<th>Target</th>
		<th>Var Amt</th>
		<th>Var %</th>
	{/foreach}
</tr>
{foreach from=$mth item=month}
	<tbody id="r_{$month}">
	<tr class=cmth>
	<td > {if !$no_header_footer}<img src="/ui/expand.gif" onclick="load_child({$month},this, 1);" align=absmiddle>{/if} {$month|str_month}</td>
	{foreach from=$data key=year item=r}
		{if $ttl_month.$year.$month > 0}
		{assign var=var_v value=$ttl_month.$year.$month-$sales_target.$year.$month}
		{assign var=var_p value=$var_v/$sales_target.$year.$month*100}
		{else}
		{assign var=var_v value=0}
		{assign var=var_p value=0}
		{/if}
		<td align=right>{$ttl_month.$year.$month|number_format:2|ifzero:"-"}</td>
		<td align=right>{$sales_target.$year.$month|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_v|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_p|number_format:2|ifzero:"-":'%'}</td>
	{/foreach}
	</tr>
	
	{foreach from=$ttl_sku_type key=sku_type item=ttl}
	<tr class="c{$sku_type|lower}">
	<td>{$sku_type}</td>
	{foreach from=$data key=year item=r}
		{if $ttl.$year.$month > 0}
		{assign var=var_v value=$ttl.$year.$month-$sales_target_sku_type.$sku_type.$year.$month}
		{assign var=var_p value=$var_v/$sales_target_sku_type.$sku_type.$year.$month*100}
		{else}
		{assign var=var_v value=0}
		{assign var=var_p value=0}
		{/if}
		<td align=right>{$ttl.$year.$month|number_format:2|ifzero:"-"}</td>
		<td align=right>{$sales_target_sku_type.$sku_type.$year.$month|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_v|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_p|number_format:2|ifzero:"-":'%'}</td>
	{/foreach}
	</tr>
	{/foreach}
	</tbody>
{/foreach}
	<tr>
		<td>Total</td>
		{foreach from=$data key=year item=r}
		{if $total_sales.$year > 0}
			{assign var=var_v value=$total_sales.$year-$total_sales_target.$year}
			{assign var=var_p value=$var_v/$total_sales_target.$year*100}
		{else}
			{assign var=var_v value=0}
			{assign var=var_p value=0}
		{/if}
		<td align=right>{$total_sales.$year|number_format:2|ifzero:"-"}</td>
		<td align=right>{$total_sales_target.$year|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_v|number_format:2|ifzero:"-"}</td>
		<td align=right>{$var_p|number_format:2|ifzero:"-":'%'}</td>
		{/foreach}
	</tr>
</table>
{/if}
{if !$no_header_footer}
<script>toggle_dept();</script>
{/if}
{include file=footer.tpl}

