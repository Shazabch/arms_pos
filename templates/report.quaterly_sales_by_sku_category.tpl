{*
3/8/2010 10:32:25 AM Andy
- Fix don't show expand image if category have no more sub-category
- Fix incorrect total due to decimal qty problem

4/28/2010 10:55:20 AM Andy
- Change Report to use pos and pos_items to find the highest and lowest selling price.
- Fix wrong gross profit percent bugs
- Fix main category cannot show CM figures bugs

10/14/2011 11:03:32 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/22/2014 4:13 PM Justin
- Enhanced to have export feature for itemise table.

06/30/2020 02:08 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var filter = "{$filter}";
var branch_id = "{$smarty.request.branch_id}";
var start_date = "{$start_date}";
var end_date = "{$end_date}";
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

function close_sub(tbody_id,img_id){
    $(tbody_id).style.display = 'none';
    $(img_id).src = '/ui/expand.gif';
}

function changeSpanCat(){
	var el = document.report_form.filter_cat;
	
	if(el.checked){
		$('span_cat').style.display='';
		document.report_form.span_status.value='on';
	}else{
        $('span_cat').style.display='none';
        document.report_form.span_status.value='';
	}
}

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


	new Ajax.Request(phpself+'?'+Form.serialize(document.report_form)+"&"+p.toQueryString(),
	{
		onComplete: function(e) {

			new Insertion.After(obj.parentNode.parentNode.parentNode, e.responseText);
			//$('r_'+m+'_'+qn), e.responseText);
			//obj.remove();
			obj.src = 'ui/collapse.gif';
		},
	});
}

function load_sku(code,el){
	document.sku_form.hidden_code.value = code;
	document.f_export_itemise_info.hidden_code.value = code;
	document.sku_form.submit();
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
.c5_2 { background:#f0e07f; }

.r1 { background:#c0fff0;}
.r2 { background:#f0f0f0;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}

.h1 { background:#00f0ff !important;}
{/literal}
</style>
{/if}

<div class="stdframe" style="display:none;">
	<form name="f_export_itemise_info" method="post">
		<input type="hidden" name="hidden_filter" value="{$filter}">
		<input type="hidden" name="hidden_branch_id" value="{$smarty.request.branch_id}">
		<input type="hidden" name="hidden_start_date" value="{$start_date}">
		<input type="hidden" name="hidden_end_date" value="{$end_date}">
		<input type="hidden" name="hidden_code">
		<input type="hidden" name="hidden_date_msg" value="{$date_msg}">
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
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	    <option value="">-- All --</option>
	    {foreach from=$branches item=b}
	        {if !$branch_group.have_group[$b.id]}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
	        {/if}
	    {/foreach}
	    {if $branch_group.header}
	        <optgroup label="Branch Group">
				{foreach from=$branch_group.header item=r}
				    {capture assign=bgid}bg,{$r.id}{/capture}
					<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
				{/foreach}
			</optgroup>
		{/if}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{else}
<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}"/>
{/if}
<b>Month</b> <select name=month>
{section loop=12 name=i}
	<option value="{$smarty.section.i.iteration}" {if $smarty.request.month eq $smarty.section.i.iteration}selected{/if}>{$months[$smarty.section.i.iteration]}</option>
{/section}
</select>&nbsp;
<b>Year</b> <select name=year>
{foreach from=$years item=y}
    <option value="{$y.year}" {if $smarty.request.year eq $y.year}selected{/if}>{$y.year}</option>
{/foreach}
</select>
&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
</p>
<p>
{include file="category_autocomplete.tpl" all=true}
</p>

<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>
Note:	Report will Shown 1 year
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<form name=sku_form method=post action="?a=view_sku_by_second_last&ajax=1" target="_blank">

<input type=hidden name=hidden_filter value="{$filter}">
<input type=hidden name=hidden_branch_id value="{$smarty.request.branch_id}">
<input type=hidden name=hidden_start_date value="{$start_date}">
<input type=hidden name=hidden_end_date value="{$end_date}">
<input type=hidden name=hidden_code>
<input type=hidden name=hidden_date_msg value="{$date_msg}">
</form>
<h2>
{$report_title}
<br>
<!--Branch: {$branch_name} &nbsp;&nbsp;&nbsp;&nbsp; From: {$date_msg}-->
</h2>
<table class=report_table width=100%>
<tr class=header>
	<th rowspan=2>Category</th>
	<th rowspan=2>Last 9 Mth</th>
	<th colspan=4>Quantity</th>
	<th rowspan=2 class=h1>Last 9 Mth</th>
	<th colspan=4 class=h1>Amount</th>
	<th colspan=2>Sales Contribution</th>
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<th colspan=4 class=h1>Gross Profit</th>
	{/if}
	<th colspan=2>AVG S.price</th>
	<th colspan=4 class=h1>S.Price</th>
</tr>
<tr class=header>
    <!-- Qty -->
    {assign var=last9 value=0}
	{foreach from=$label item=lbl}
	    {assign var=last9 value=$last9+1}
	    {if $last9 > 9}
	        <th>{$lbl}</th>
	    {/if}
	{/foreach}
	<th>Total</th>
	<!-- End Qty -->

	<!-- Amount -->
	{assign var=last9 value=0}
	{foreach from=$label item=lbl}
	    {assign var=last9 value=$last9+1}
	    {if $last9 > 9}
	        <th class=h1>{$lbl}</th>
	    {/if}
	{/foreach}
	<th class=h1>Total</th>
	<!-- End Amount -->
	<th>C.Month</th>
	<th>Accum</th>
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<th class=h1>CM.Amt</th>
		<th class=h1>CM %</th>
		<th class=h1>Accum.Amt</th>
		<th class=h1>Accum %</th>
	{/if}
	<th>CM</th>
	<th>Accum</th>
	<th class=h1>CM High</th>
	<th class=h1>CM Low</th>
	<th class=h1>AM High</th>
	<th class=h1>AM Low</th>
</tr>
{foreach from=$table key=code item=c}
{cycle values="c2,c1" assign=row_class}
<tbody id="r_{$code}">
<tr>
	<td class="{$row_class}" nowrap>
	    {if $code eq 0}
	        {$category.$code.name}
	    {else}
	        {if !$no_header_footer}<img src='/ui/icons/table.png' onclick="load_sku({$code},this);" align=absmiddle>{/if}
	        {$category.$code.name}
        	{if !$no_header_footer}
				{if $category.$code.got_parent}
					<img src="/ui/expand.gif" onclick="load_child({$code},this,1);" align=absmiddle>
			    {/if}
			{/if}
        {/if}
	</td>

	<!-- Qty -->
    {assign var=last9 value=''}
    {assign var=temp value=''}
	{foreach from=$label key=lbl item=day}
	    {assign var=last9 value=$last9+1}
	    {assign var=temp value=$category.$code.qty.$lbl+$temp}
	    {if $last9 eq 9}
	        <td class="{$row_class} r">{$temp|qty_nf|ifzero:'-'}</td>
	    {elseif $last9 > 9}
	        <td class="{$row_class} r">{$category.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
	    {/if}
	{/foreach}
	<td class="{$row_class} r">{$category.$code.qty.total|qty_nf|ifzero:'-'}</td>
	<!-- End Qty -->
	
	<!-- Amount -->
	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
    {assign var=last9 value=''}
    {assign var=temp value=''}
	{foreach from=$label key=lbl item=day}
	    {assign var=last9 value=$last9+1}
	    {assign var=temp value=$category.$code.amount.$lbl+$temp}
	    {if $last9 eq 9}
	        <td class="{$amt_class} r">{$temp|number_format:2|ifzero:'-'}</td>
	    {elseif $last9 > 9}
	        <td class="{$amt_class} r">{$category.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
	    {/if}
	{/foreach}
	{assign var=last_lbl value=$lbl}
	<td class="{$amt_class} r">{$category.$code.amount.total|number_format:2|ifzero:'-'}</td>
	<!-- End Amount -->
	<!-- C. Month -->
	{if $category.total.amount.$last_lbl ne 0}
		{assign var=temp value=$category.$code.amount.$last_lbl/$category.total.amount.$last_lbl}
	{else}
	    {assign var=temp value=0}
	{/if}
	<td class="{$row_class} r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
    <!-- End of C. Month -->
    <!-- Accum -->
	{assign var=temp value=$category.$code.amount.total/$category.total.amount.total}
	<td class="{$row_class} r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
	<!-- End of Accum -->

    {if $sessioninfo.privilege.SHOW_REPORT_GP}
		<!-- Gross Profit -->
		{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
		<td class="{$amt_class} r">{$category.$code.cost.cm.$last_lbl|number_format:2|ifzero:'-'}</td>
		{if $category.total.amount.$last_lbl ne 0}
		{assign var=temp value=$category.$code.cost.cm.$last_lbl/$category.total.amount.$last_lbl}
		{else}
		{assign var=temp value=0}
		{/if}
		<td class="{$amt_class} r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		<td class="{$amt_class} r">{$category.$code.cost.accum|number_format:2|ifzero:'-'}</td>
		{if $category.$code.amount.total ne 0}
		{assign var=temp value=$category.$code.cost.accum/$category.$code.amount.total}
		{else}
		{assign var=temp value=0}
		{/if}
		<td class="{$amt_class} r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		<!-- End of Gross Profit -->
	{/if}
	<!-- Avg S.Price-->
	<td class="{$row_class} r">{$category.$code.avg.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$row_class} r">{$category.$code.avg.total|number_format:2|ifzero:'-'}</td>
	<!-- End of Avg S.Price-->
	<!-- S.Price-->
	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
	<td class="{$amt_class} r">{$category.$code.highest_sales.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.$code.lowest_sales.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.$code.highest_sales.total|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.$code.lowest_sales.total|number_format:2|ifzero:'-'}</td>
	<!-- End of S.Price-->
</tr>
</tbody>
{/foreach}
{cycle values="c2,c1" assign=row_class}
<tbody id="r_total">
<tr>
	<td class="{$row_class} r">Total</td>

	<!-- Qty -->
    {assign var=last9 value=''}
    {assign var=temp value=''}
	{foreach from=$label key=lbl item=day}
	    {assign var=last9 value=$last9+1}
	    {assign var=temp value=$category.total.qty.$lbl+$temp}
	    {if $last9 eq 9}
	        <td class="{$row_class} r">{$temp|qty_nf|ifzero:'-'}</td>
	    {elseif $last9 > 9}
	        <td class="{$row_class} r">{$category.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
	    {/if}
	{/foreach}
	<td class="{$row_class} r">{$category.total.qty.total|qty_nf|ifzero:'-'}</td>
	<!-- End Qty -->

	<!-- Amount -->
	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
    {assign var=last9 value=''}
    {assign var=temp value=''}
	{foreach from=$label key=lbl item=day}
	    {assign var=last9 value=$last9+1}
	    {assign var=temp value=$category.total.amount.$lbl+$temp}
	    {if $last9 eq 9}
	        <td class="{$amt_class} r">{$temp|number_format:2|ifzero:'-'}</td>
	    {elseif $last9 > 9}
	        <td class="{$amt_class} r">{$category.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
	    {/if}
	{/foreach}
	<td class="{$amt_class} r">{$category.total.amount.total|number_format:2|ifzero:'-'}</td>
	<!-- End Amount -->
	<!-- sales contribution -->
	<td class="{$row_class} r">{*{if $category.total.amount.$last_lbl ne 0}100.00%{else}-{/if}*}-</td>
	<td class="{$row_class} r">{*100.00%*}-</td>
	<!-- end of sales contribution -->
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<!-- Gross Profit -->
		{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
		<td class="{$amt_class} r">{$category.total.cost.cm.$last_lbl|number_format:2|ifzero:'-'}</td>
		<td class="{$amt_class} r">
			{if $category.total.amount.$last_lbl ne 0}
                {$category.total.cost.cm.$last_lbl/$category.total.amount.$last_lbl*100|number_format:2}%
			{else}-{/if}
		</td>
		<td class="{$amt_class} r">{$category.total.cost.accum|number_format:2|ifzero:'-'}</td>
		{if $category.total.amount.total ne 0}
		{assign var=temp value=$category.total.cost.accum/$category.total.amount.total}
		{else}
		{assign var=temp value=0}
		{/if}
		<td class="{$amt_class} r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		<!-- End of Gross Profit -->
	{/if}
	<!-- Avg S.Price-->
	<td class="{$row_class} r">{$category.total.avg.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$row_class} r">{$category.total.avg.total|number_format:2|ifzero:'-'}</td>
	<!-- End of Avg S.Price-->
	<!-- S.Price-->
	{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
	<td class="{$amt_class} r">{$category.total.highest_sales.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.total.lowest_sales.$last_lbl|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.total.highest_sales.total|number_format:2|ifzero:'-'}</td>
	<td class="{$amt_class} r">{$category.total.lowest_sales.total|number_format:2|ifzero:'-'}</td>
	<!-- End of S.Price-->
</tr>
</tbody>
</table>
{/if}
{include file=footer.tpl}

